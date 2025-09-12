<?php

namespace App\Http\Controllers\ShareFileRoom;

use Carbon\Carbon;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\DropBoxService;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipStream\ZipStream;
use ZipStream\OperationMode;


class DownloadController extends Controller
{
    private $bBaseUrl;
    private $dropbox;

    public function __construct(DropBoxService $dropbox)
    {
        $this->bBaseUrl = config('app.b_base_url', env('B_BASE_URL'));
        $this->dropbox = $dropbox;
    }

    /**
     * ROOM着地点
     */
    public function showRoom($token)
    {
        try {
            $response = Http::get("{$this->bBaseUrl}/api/share-room/validate/{$token}");
            $data = $response->json();

            if (empty($data['valid']) || $data['valid'] === false) {
                return Inertia::render('ShareFileRoom/Invalid', [
                    'message' => $data['message'] ?? '無効なURLです',
                ]);
            }

            // ====== ここで社内ROOMアクセスログを記録 ======
            if ($data['target_scope'] === 'internal') {
                Log::info('FileRoom Access', [
                    'user_id' => auth()->id(),               // Laravel認証ユーザーID
                    'ip' => request()->ip(),                 // アクセス元IP
                    'user_agent' => request()->userAgent(),  // ブラウザ情報
                    'room_code' => $data['room_code'],       // ROOM識別コード
                    'accessed_at' => now(),                  // アクセス時刻
                ]);
            }

            return Inertia::render('ShareFileRoom/RoomView', [
                'token' => $token,
                'room'  => [
                    'name' => $data['room_name'],
                    'requires_password' => $data['requires_password'],
                    'target_scope' => $data['target_scope'],
                ]
            ]);
        } catch (\Exception $e) {
            Log::error("ROOM検証エラー: {$e->getMessage()}");
            abort(500, 'システムエラーが発生しました');
        }
    }

    /**
     * ファイル一覧取得 (Dropbox直)
     */
    public function listFiles($token)
    {
        try {

            $response = Http::get("{$this->bBaseUrl}/api/share-room/validate/{$token}");
            $data = $response->json();

            if (empty($data['valid']) || $data['valid'] === false) {
                return response()->json([
                    'message' => $data['message'] ?? 'ROOM情報が取得できません',
                ], 404);
            }

            $dateFolder = \Carbon\Carbon::parse($data['created_at'])->format('Ymd');


            // ③ Dropboxパスを組み立て
            $folderPath = "/SNAPFILE/{$dateFolder}/{$data['room_code']}";

            // ④ Dropboxからファイル一覧を取得
            $entries = $this->dropbox->getDirList($folderPath);

            if (!is_array($entries) || empty($entries)) {
                Log::warning("Dropbox folder empty or not found", [
                    'folderPath'  => $folderPath,
                    'token'       => $token,
                    'room_code'   => $data['room_code'],
                ]);
                return response()->json(['files' => []]);
            }

            // ⑤ ファイル一覧を返却
            $files = collect($entries)
                ->filter(fn($entry) => isset($entry['.tag']) && $entry['.tag'] === 'file')
                ->map(fn($file) => [
                    'id'          => $file['id'],
                    'name'        => $file['name'],
                    'size'        => $file['size'],
                    'uploaded_at' => date('Y-m-d H:i:s', strtotime($file['server_modified'])),
                    'path'        => $file['path_display'],
                ])
                ->values()
                ->toArray();

            return response()->json(['files' => $files]);

        } catch (\Exception $e) {
            Log::error("Dropbox ファイル一覧取得エラー", [
                'token' => $token,
                'error' => $e->getMessage()
            ]);
            return response()->json(['message' => 'ファイル一覧が取得できませんでした'], 500);
        }
    }

    /**
     * ROOMパスワード認証
     */
    public function verifyPassword(Request $request)
    {
        $request->validate([
            'token'    => 'required|string',
            'password' => 'required|string',
        ]);

        try {
            // JetstreamのURLを正しく組み立てる
            $jetstreamUrl = rtrim(config('app.b_base_url', env('B_BASE_URL')), '/') . '/api/share-room/verify-password';

            $response = Http::post($jetstreamUrl, [
                'token'    => $request->input('token'),
                'password' => $request->input('password'),
            ]);

            $data = $response->json();

            if ($response->status() === 200) {
                return response()->json([
                    'status'  => 'ok',
                    'message' => $data['message'] ?? '認証成功',
                ]);
            }

            return response()->json([
                'status'  => 'error',
                'message' => $data['message'] ?? '認証に失敗しました',
            ], $response->status());

        } catch (\Throwable $e) {
            Log::error('ROOMパスワード認証エラー', [
                'token' => $request->input('token'),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'システムエラーが発生しました',
            ], 500);
        }
    }


    /**
     * 単一ファイルダウンロード (Dropbox直)
     */
    public function downloadFile(Request $request, $fileId)
    {
        try {
            $filePath = $request->input('path');

            if (empty($filePath)) {
                abort(400, 'ファイルパスが指定されていません');
            }

            // Dropbox上に存在するか確認
            if (!$this->dropbox->exists($filePath)) {
                abort(404, 'ファイルが存在しません');
            }

            // ファイル名をレスポンスヘッダに含めて返却
            return $this->dropbox->downloadStream($filePath, basename($filePath));
        } catch (\Exception $e) {
            Log::error("Dropbox ファイルDLエラー: {$e->getMessage()}", [
                'filePath' => $request->input('path'),
                'fileId' => $fileId,
            ]);
            abort(500, 'ファイルのダウンロード中にエラーが発生しました');
        }
    }

    /**
     * ZIP一括ダウンロード
     */
    public function downloadZip(Request $request, string $token)
    {
        try {
            // ① ROOM情報を取得
            $roomInfo = Http::get("{$this->bBaseUrl}/api/share-room/validate/{$token}")->json();

            if (empty($roomInfo['valid']) || $roomInfo['valid'] === false) {
                return response()->json(['message' => $roomInfo['message'] ?? 'ROOM情報が取得できません'], 404);
            }

            // ② 日付フォルダを取得
            $date = isset($roomInfo['created_at'])
                ? Carbon::parse($roomInfo['created_at'])->format('Y-m-d')
                : Carbon::now()->format('Y-m-d');

            // ③ ZIPファイル名を決定
            $zipFileName = "{$date}-tatsumori-ltd.zip";

            // ④ Dropbox から対象ファイルリストを取得
            $files = $this->getRoomFilesFromDropbox($token);
            if (empty($files)) {
                return response()->json(['message' => 'ファイルがありません'], 404);
            }

            // ⑤ StreamedResponse でZIPを生成・返却
            return new StreamedResponse(function () use ($files, $zipFileName) {
                if (ob_get_level()) {
                    ob_end_clean();
                }
                if (function_exists('header_remove')) {
                    header_remove('Content-Encoding');
                }

                // v3 の正しい初期化：outputName と sendHttpHeaders を named argument またはオプション配列で指定
                $zip = new ZipStream(
                    outputName: $zipFileName,
                    sendHttpHeaders: true
                );

                foreach ($files as $file) {
                    // ... 既存処理
                    $zip->addFileFromStream($file['name'], $this->dropbox->download($file['path_display']));
                    // etc.
                }

                $zip->finish();
            }, 200, []);


        } catch (\Throwable $e) {
            Log::error('ZIPダウンロードエラー', ['token' => $token, 'error' => $e->getMessage()]);
            return response()->json(['message' => 'ZIPダウンロード中にエラーが発生しました'], 500);
        }
    }

    /**
     * ROOMのDropboxフォルダからファイル一覧を取得
     */
    private function getRoomFilesFromDropbox(string $token): array
    {
        // ① JETSTREAM APIに問い合わせてROOM情報を取得
        $response = Http::get("{$this->bBaseUrl}/api/share-room/validate/{$token}");
        $data = $response->json();

        if (empty($data['valid']) || $data['valid'] === false) {
            throw new \Exception($data['message'] ?? 'ROOM情報が取得できません');
        }

        // ② created_at を Ymd に変換
        $dateFolder = Carbon::parse($data['created_at'])->format('Ymd');

        // ③ Dropboxフォルダパスを組み立て
        //    SNAPFILE/{日付}/{room_code}
        $folderPath = "/SNAPFILE/{$dateFolder}/{$data['room_code']}";

        // ④ Dropboxからファイル一覧を取得
        $entries = $this->dropbox->getDirList($folderPath);

        if (!is_array($entries) || empty($entries)) {
            Log::warning("Dropbox folder empty or not found", [
                'folderPath' => $folderPath,
                'token'      => $token,
                'room_code'  => $data['room_code'],
            ]);
            return [];
        }

        // ⑤ ファイル情報を整形して返す
        return collect($entries)
            ->filter(fn($entry) => isset($entry['.tag']) && $entry['.tag'] === 'file')
            ->map(fn($file) => [
                'id'           => $file['id'],
                'name'         => $file['name'],
                'size'         => $file['size'],
                'uploaded_at'  => date('Y-m-d H:i:s', strtotime($file['server_modified'])),
                'path_display' => $file['path_display'], // ZIP生成に使用
            ])
            ->values()
            ->toArray();
    }

}
