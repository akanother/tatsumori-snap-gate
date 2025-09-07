<?php

namespace App\Http\Controllers\Ingest;

use App\Http\Controllers\Controller;
use App\Services\DropBoxService; // ← ★ あなたのサービス（複数形）
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use App\Services\ClamAvService;
use Illuminate\Support\Facades\Log;

class UploadController extends Controller
{
    public function create()
    {
        return Inertia::render('Capture', [
            'roomCode'  => session('ingest.room_code'),
            'maxFiles'  => (int) config('services.snapgate.max_files', 10),
            'maxMB'     => (int) config('services.snapgate.max_mb', 20),
            'uploadUrl' => route('gate.upload'),
            'logoutUrl' => route('gate.logout'),
            'expiresAt' => session('ingest.expires_at'),
            'csrfToken' => csrf_token(),
        ]);
    }

    public function store(Request $request, DropBoxService $dropbox, ClamAvService $clam)
    {
        // 招待セッションチェック
        $roomCode = session('ingest.room_code');
        if (!$roomCode) {
            return response()->json(['ok' => false, 'message' => 'invalid session'], 403);
        }

        // バリデーション
        $maxMB    = (int) config('services.snapgate.max_mb', 20);
        $maxFiles = (int) config('services.snapgate.max_files', 10);
        $request->validate([
            'images'   => ['required','array','min:1','max:'.$maxFiles],
            'images.*' => ['file','mimes:jpeg,jpg,png,webp,heic,heif','max:'.($maxMB * 1024)], // MB→KB
        ]);

        $root = '/SNAPGATE';
        $date = now()->format('Ymd');

        $saved = [];
        foreach ($request->file('images', []) as $file) {

            // 1) まずスキャン（tmpにあるアップロード実体を直接スキャン）
            $tmpPath   = $file->getRealPath();
            $scan      = $clam->scan($tmpPath);

            if (!$scan['clean']) {
                // 任意：隔離
                $qpath = $clam->quarantine($tmpPath, $file->getClientOriginalName());
                Log::warning('upload blocked by ClamAV', [
                    'room'      => $roomCode,
                    'filename'  => $file->getClientOriginalName(),
                    'signature' => $scan['signature'],
                    'raw'       => $scan['raw'],
                    'quarantine'=> $qpath,
                ]);

                return response()->json([
                    'ok'      => false,
                    'message' => 'Virus detected',
                    'signature' => $scan['signature'],
                ], 422);
            }

            // 2) クリーンならDropboxへ保存
            $ext  = strtolower($file->getClientOriginalExtension() ?: 'jpg');
            $name = now()->format('His') . '_' . Str::random(8) . '.' . $ext;
            $path = "{$root}/rooms/{$roomCode}/{$date}/{$name}";

            // サイズが大きいなら file_get_contents を避けて、サービス側がstream対応なら切替推奨
            $result = $dropbox->uploadFile($path, file_get_contents($tmpPath));

            if (is_string($result)) {
                Log::error('dropbox upload failed', ['path' => $path, 'err' => $result]);
                return response()->json(['ok'=>false,'message'=>"dropbox upload failed"], 502);
            }

            $tmp = $dropbox->getTemporaryLink($path); // UIで即プレビューしたい場合のみ
            $saved[] = ['path' => $path, 'name' => $name, 'tmp' => $tmp];
        }

        return response()->json([
            'ok'        => true,
            'count'     => count($saved),
            'files'     => $saved,
            'room_code' => $roomCode,
        ]);
    }
    //public function store(Request $request, DropBoxService $dropbox)
    //{
    //    // 招待セッションチェック
    //    $roomCode = session('ingest.room_code');
    //    if (!$roomCode) {
    //        return response()->json(['ok' => false, 'message' => 'invalid session'], 403);
    //    }
    //
    //    // バリデーション（A側の制限）
    //    $maxMB    = (int) config('services.snapgate.max_mb', 20);
    //    $maxFiles = (int) config('services.snapgate.max_files', 10);
    //    $request->validate([
    //        'images'   => ['required','array','min:1','max:'.$maxFiles],
    //        'images.*' => ['file','mimes:jpeg,png,webp,heic,heif','max:'.($maxMB * 1024)], // MB→KB
    //    ]);
    //
    //    $root = '/SNAPGATE';
    //    $date = now()->format('Ymd');
    //
    //    $saved = [];
    //    foreach ($request->file('images', []) as $file) {
    //        $ext  = strtolower($file->getClientOriginalExtension() ?: 'jpg');
    //        $name = now()->format('His') . '_' . Str::random(8) . '.' . $ext;
    //        $path = "{$root}/rooms/{$roomCode}/{$date}/{$name}";
    //
    //        // あなたの DropBoxService をそのまま利用
    //        $result = $dropbox->uploadFile($path, file_get_contents($file->getRealPath()));
    //        // 失敗時はメッセージ文字列が返る可能性があるのでガード
    //        if (is_string($result)) {
    //            return response()->json(['ok'=>false,'message'=>"dropbox upload failed: {$result}"], 502);
    //        }
    //
    //        $tmp = $dropbox->getTemporaryLink($path); // なくてもOK（UI用）
    //        $saved[] = ['path' => $path, 'name' => $name, 'tmp' => $tmp];
    //    }
    //
    //    return response()->json([
    //        'ok'        => true,
    //        'count'     => count($saved),
    //        'files'     => $saved,
    //        'room_code' => $roomCode,
    //    ]);
    //}

    public function logout(Request $request)
    {
        $request->session()->forget([
            'ingest.room','ingest.room_code','ingest.token','ingest.nonce',
            'ingest.issued_at','ingest.expires_at',
        ]);
        $request->session()->save();
        return redirect()->route('gate.ended');
    }
}
