<?php

namespace App\Services;

use Exception;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Spatie\Dropbox\Client as DropboxClient;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DropBoxService
{
    private string $appKey;
    private string $secretKey;
    private string $refreshToken;
    private string $tokenUrl = 'https://api.dropbox.com/oauth2/token';

    private string $sharedLinkUrl = 'https://api.dropboxapi.com/2/sharing/list_shared_links';
    private string $downloadUrl   = 'https://content.dropboxapi.com/2/files/download';

    private string $accessToken;        // ★ 自前で保持
    private DropboxClient $dropboxClient;
    private HttpClient $http;

    public function __construct()
    {
        $this->appKey       = (string) config('services.dropbox.app_key');
        $this->secretKey    = (string) config('services.dropbox.secret_key');
        $this->refreshToken = (string) config('services.dropbox.refresh_token');

        $this->http = new HttpClient(['timeout' => 30]);
        $this->accessToken   = $this->getAccessToken();        // ★ ここで取得（キャッシュつき）
        $this->dropboxClient = new DropboxClient($this->accessToken);
    }

    /** 先頭に / を強制 */
    private function normalizePath(string $path): string
    {
        $path = trim($path);
        return str_starts_with($path, '/') ? $path : '/'.$path;
    }

    /** アクセストークン取得（3hキャッシュ）＋更新用フック */
    private function getAccessToken(bool $forceRefresh = false): string
    {
        $cacheKey     = 'dropbox_access_token';
        $cacheSeconds = 60 * 60 * 3; // 3h

        if (!$forceRefresh) {
            if ($token = Cache::get($cacheKey)) {
                return $token;
            }
        }

        $res = $this->http->post($this->tokenUrl, [
            'auth'        => [$this->appKey, $this->secretKey],
            'form_params' => [
                'grant_type'    => 'refresh_token',
                'refresh_token' => $this->refreshToken,
            ],
            'headers'     => ['Content-Type' => 'application/x-www-form-urlencoded'],
        ]);
        $data  = json_decode((string) $res->getBody(), true);
        $token = (string) Arr::get($data, 'access_token');

        Cache::put($cacheKey, $token, $cacheSeconds);
        return $token;
    }

    /** 401 が返ったら 1 回だけトークンを更新してリトライする共通ヘルパ */
    private function apiRequest(callable $fn)
    {
        try {
            return $fn($this->accessToken);
        } catch (ClientException $e) {
            if ($e->getResponse()?->getStatusCode() === 401) {
                // アクセストークン更新
                $this->accessToken = $this->getAccessToken(true);
                $this->dropboxClient = new DropboxClient($this->accessToken);
                // リトライ
                return $fn($this->accessToken);
            }
            throw $e;
        }
    }

    /** 既存の共有リンクを取得（なければ作成） */
    public function getExistingSharedLink(string $path): string
    {
        $path = $this->normalizePath($path);

        try {
            // list_shared_links → 無ければ createSharedLinkWithSettings
            $data = $this->apiRequest(function (string $token) use ($path) {
                $res = $this->http->post($this->sharedLinkUrl, [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $token,
                        'Content-Type'  => 'application/json',
                    ],
                    'json' => ['path' => $path, 'direct_only' => true],
                ]);
                return json_decode((string) $res->getBody(), true);
            });

            if (!empty($data['links'])) {
                return (string) $data['links'][0]['url'];
            }

            $create = $this->dropboxClient->createSharedLinkWithSettings($path);
            return (string) $create['url'];
        } catch (Exception $e) {
            Log::warning('dropbox shared link failed', ['path' => $path, 'e' => $e->getMessage()]);
            // 失敗時は説明的なメッセージより null を返す方が扱いやすい
            return 'No shared link: ' . $e->getMessage();
        }
    }

    /** 直接ダウンロード可の一時リンク（数時間） */
    public function getTemporaryLink(string $path): ?string
    {
        $path = $this->normalizePath($path);

        try {
            // Spatie 経由も 401 がありうるのでラップ
            $res = $this->apiRequest(function () use ($path) {
                return $this->dropboxClient->getTemporaryLink($path);
            });
            return Arr::get($res, 'link');
        } catch (Exception $e) {
            Log::warning('dropbox getTemporaryLink failed', ['path' => $path, 'e' => $e->getMessage()]);
            return null;
        }
    }

    /** パス存在チェック */
    public function exists(string $path): bool
    {
        $path = $this->normalizePath($path);

        try {
            $this->apiRequest(function () use ($path) {
                $this->dropboxClient->getMetadata($path);
            });
            return true;
        } catch (Exception) {
            return false;
        }
    }

    /** 小さめファイルの全体ダウンロード */
    public function download(string $path)
    {
        $path = $this->normalizePath($path);

        try {
            return $this->apiRequest(function () use ($path) {
                return $this->dropboxClient->download($path);
            });
        } catch (Exception $e) {
            Log::warning('dropbox download failed', ['path' => $path, 'e' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * ストリーミング返却（大きめファイル向け）
     * - $inline=true ならブラウザ内表示（PDF/画像プレビュー）
     * - $inline=false なら通常ダウンロード
     */
    public function downloadStream(
        string $path,
        ?string $downloadName = null,
        ?string $forceContentType = null,
        bool $inline = false
    ): ?StreamedResponse {
        $path = $this->normalizePath($path);

        // 1) 一時リンクからストリーム
        $tmp = $this->getTemporaryLink($path);
        if ($tmp) {
            try {
                $client = new HttpClient(['stream' => true, 'timeout' => 0]);
                $res    = $client->get($tmp, ['stream' => true]);
                $body   = $res->getBody();

                $contentType = $forceContentType ?: ($res->getHeaderLine('Content-Type') ?: 'application/octet-stream');
                $filename    = $downloadName ?: basename($path);

                return response()->stream(function () use ($body) {
                    while (!$body->eof()) {
                        echo $body->read(8192);
                        if (function_exists('flush')) { flush(); }
                    }
                }, 200, [
                    'Content-Type'           => $contentType,
                    'Content-Disposition'    => $this->contentDispositionHeader($filename, $inline),
                    'Cache-Control'          => 'private, max-age=0, no-cache',
                    'X-Content-Type-Options' => 'nosniff',
                ]);
            } catch (\Throwable $e) {
                Log::warning('dropbox temp-link streaming failed', ['path' => $path, 'e' => $e->getMessage()]);
                // フォールバックに進む
            }
        }

        // 2) /2/files/download 直叩き（401時は apiRequest で再取得→再実行）
        try {
            $response = $this->apiRequest(function (string $token) use ($path) {
                $client = new HttpClient(['stream' => true, 'timeout' => 0]);
                return $client->post($this->downloadUrl, [
                    'stream'  => true,
                    'headers' => [
                        'Authorization'   => 'Bearer ' . $token,
                        'Dropbox-API-Arg' => json_encode(['path' => $path], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                    ],
                ]);
            });

            $body        = $response->getBody();
            $apiResult   = $response->getHeaderLine('Dropbox-Api-Result');
            $meta        = $apiResult ? json_decode($apiResult, true) : [];
            $filename    = $downloadName ?: ($meta['name'] ?? basename($path));
            $contentType = $forceContentType ?: ($response->getHeaderLine('Content-Type') ?: 'application/octet-stream');

            return response()->stream(function () use ($body) {
                while (!$body->eof()) { echo $body->read(8192); if (function_exists('flush')) { flush(); } }
            }, 200, [
                'Content-Type'           => $contentType,
                'Content-Disposition'    => $this->contentDispositionHeader($filename, $inline),
                'Cache-Control'          => 'private, max-age=0, no-cache',
                'X-Content-Type-Options' => 'nosniff',
            ]);
        } catch (\Throwable $e) {
            Log::error('dropbox direct download failed', ['path' => $path, 'e' => $e->getMessage()]);
            return null;
        }
    }

    /** Content-Disposition 生成（日本語名対応） */
    private function contentDispositionHeader(string $filename, bool $inline = false): string
    {
        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $filename) ?: 'download';
        $ascii = str_replace('"', '\\"', $ascii);
        $utf8  = rawurlencode($filename);
        $type  = $inline ? 'inline' : 'attachment';
        return "{$type}; filename=\"{$ascii}\"; filename*=UTF-8''{$utf8}";
    }

    /** Spatie 経由のアップロード（20MB以下前提ならこれでOK） */
    public function uploadFile(string $path, string $content)
    {
        $path = $this->normalizePath($path);

        try {
            // 401 対応のためラップ
            return $this->apiRequest(function () use ($path, $content) {
                // 第3引数: 'overwrite' を使うなら第4引数 true でクライアント側で書き込み
                return $this->dropboxClient->upload($path, $content, 'overwrite', true);
            });
        } catch (Exception $e) {
            Log::error('dropbox upload failed', ['path' => $path, 'e' => $e->getMessage()]);
            return $e->getMessage();
        }
    }

    /** ディレクトリ一覧 */
    public function getDirList(string $path)
    {
        $path = $this->normalizePath($path);

        try {
            return $this->apiRequest(function () use ($path) {
                $result = $this->dropboxClient->listFolder($path);
                return $result['entries'] ?? [];
            });
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /** メタデータ取得 */
    public function getMetadata(string $path)
    {
        $path = $this->normalizePath($path);

        try {
            return $this->apiRequest(function () use ($path) {
                return $this->dropboxClient->getMetadata($path);
            });
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /** 小さめファイルを文字列で取得（Base64等に流用） */
    public function downloadContent(string $path): ?string
    {
        $path = $this->normalizePath($path);

        try {
            return $this->apiRequest(function () use ($path) {
                return $this->dropboxClient->download($path);
            });
        } catch (Exception $e) {
            Log::warning('dropbox downloadContent failed', ['path' => $path, 'e' => $e->getMessage()]);
            return null;
        }
    }

    /** 削除 */
    public function delete(string $path)
    {
        $path = $this->normalizePath($path);

        try {
            return $this->apiRequest(function () use ($path) {
                return $this->dropboxClient->delete($path);
            });
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }

    /** （必要なら）Flysystem 経由ストリーム */
    public function stream(
        string $path,
        string $downloadName,
        string $mime = 'application/octet-stream',
        bool $inline = false
    ): StreamedResponse {
        $path   = $this->normalizePath($path);
        $stream = Storage::disk('dropbox')->readStream($path);
        if (!$stream) abort(404);

        $disposition = ($inline ? 'inline' : 'attachment') . '; filename="' . addslashes($downloadName) . '"';

        return new StreamedResponse(function () use ($stream) {
            while (!feof($stream)) echo fread($stream, 8192);
            if (is_resource($stream)) fclose($stream);
        }, 200, [
            'Content-Type'            => $mime,
            'Content-Disposition'     => $disposition,
            'X-Content-Type-Options'  => 'nosniff',
            'Cache-Control'           => 'private, max-age=0, must-revalidate',
        ]);
    }
}
