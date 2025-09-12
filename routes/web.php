<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Csrf;
use App\Http\Middleware\EnsureIngestRoom;
use App\Http\Controllers\Ingest\GateController;
use App\Http\Controllers\Ingest\UploadController;
use App\Http\Controllers\ShareFileRoom\DownloadController;

//*******************************************************************
//
// Snap Gate Photo Uploader
//
//*******************************************************************
// 署名URLの着地点（無保護）
Route::get('/upload', [GateController::class, 'enterFromQuery'])->name('gate.enter.query');

Route::prefix('gate')->name('gate.')->group(function () {
    // 退出はセッション切れでも通す
    Route::post('logout', [UploadController::class, 'logout'])
        ->name('logout')->withoutMiddleware([Csrf::class]);

    // capture / upload はセッション検証を通す
    Route::middleware([EnsureIngestRoom::class])->group(function () {
        Route::get('capture', [UploadController::class, 'create'])->name('capture');
        Route::post('upload', [UploadController::class, 'store'])->name('upload');
        Route::get('ping', fn() => response()->noContent())->name('ping');
    });
});

Route::get('/gate/ended', fn() => Inertia::render('Ended', [
    'message' => 'アップロードセッションは終了しました。再開するには新しい招待リンクからアクセスしてください。',
]))->name('gate.ended');


//*******************************************************************
//
// Snap Gate Share File Downloader
//
//*******************************************************************
 Route::prefix('r')->name('r.')->group(function () {
     Route::get('/{token}', [DownloadController::class, 'showRoom'])->name('room.show');
     Route::post('/verify', [DownloadController::class, 'verifyPassword'])->name('room.verify');

     // ファイル一覧取得 (Dropbox直)
     Route::get('/files/{token}', [DownloadController::class, 'listFiles'])->name('files.list');

     // ファイルダウンロード
     Route::post('/f/{fileId}', [DownloadController::class, 'downloadFile'])->name('file.download');
     // ★ ZIP一括ダウンロード
     Route::post('/zip/{token}', [DownloadController::class, 'downloadZip'])->name('files.zip');
 });
