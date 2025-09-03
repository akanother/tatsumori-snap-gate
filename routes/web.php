<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Ingest\GateController;
use App\Http\Controllers\Ingest\UploadController;
use App\Http\Middleware\EnsureIngestRoom;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Csrf;

//画面を描写

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
