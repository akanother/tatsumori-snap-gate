<?php

namespace App\Http\Controllers\Ingest;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class GateController extends Controller
{
    public function enterFromQuery(Request $request)
    {
        $token = (string) $request->query('token', '');
        $nonce = (string) ($request->query('n', '') ?: $request->query('nonce', ''));
        if ($token === '' || $nonce === '') {
            abort(400, 'missing token or nonce');
        }

        $base = rtrim(config('ingest.b_base_url'), '/');
        $path = '/' . ltrim(config('ingest.introspect_path'), '/');
        $url  = $base . $path;

        $http = Http::timeout(10)->acceptJson();
        $host = parse_url($base, PHP_URL_HOST) ?? '';
        if ((bool) config('ingest.introspect_insecure') || app()->environment('local') || str_ends_with($host, '.test')) {
            $http = $http->withoutVerifying();
        }

        try {
            $res = $http->post($url, ['token' => $token, 'nonce' => $nonce]);
        } catch (\Throwable $e) {
            Log::error('introspect request failed', ['url' => $url, 'error' => $e->getMessage()]);
            abort(502, 'upstream error: ' . (config('app.debug') ? $e->getMessage() : ''));
        }

        if (!$res->ok()) {
            Log::warning('introspect non-200', ['status' => $res->status(), 'body' => $res->body()]);
            if ($res->status() >= 400 && $res->status() < 500) abort(403, 'invalid or expired link');
            abort(502, 'upstream error (non-200)');
        }

        $json = $res->json();
        if (!is_array($json) || empty($json['ok']) || empty($json['room'])) {
            Log::warning('introspect invalid json', ['json' => $json]);
            abort(403, 'invalid or expired link');
        }

        // A 側 TTL
        $ttlMin    = (int) config('ingest.session_ttl', 30);
        $issuedAt  = Carbon::now();
        $expiresAt = $issuedAt->clone()->addMinutes($ttlMin);

        // ★ ミドルウェアとフロントの両方が見るキーを必ず保存
        $request->session()->put('ingest.room', $json['room']);
        $request->session()->put('ingest.room_code', $json['room']); // Vue 用
        $request->session()->put('ingest.token', $token);
        $request->session()->put('ingest.nonce', $nonce);
        $request->session()->put('ingest.issued_at', $issuedAt->toIso8601String());
        $request->session()->put('ingest.expires_at', $expiresAt->toIso8601String());
        $request->session()->save();

        // （任意）セッション固定化対策
        // $request->session()->regenerate();

        return redirect()->route('gate.capture');
    }
}
