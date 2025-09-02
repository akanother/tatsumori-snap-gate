<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureIngestRoom
{
    public function handle(Request $request, Closure $next)
    {
        $room  = session('ingest.room');
        $code  = session('ingest.room_code');
        $iat   = session('ingest.issued_at');     // timestamp(int) or datetime string
        $exp   = session('ingest.expires_at');    // iso string or null
        $ttl   = (int) config('services.snapgate.session_ttl', 30); // 分

        if (!$room || !$code || !$iat) {
            abort(403, 'ingest session not set');
        }

        $now = now();

        // 期限チェック（expires_at があればそれ優先）
        if ($exp && $now->gte(\Illuminate\Support\Carbon::parse($exp))) {
            abort(403, 'ingest session expired');
        }

        // issued_at + TTL でもチェック
        $issuedAt = is_numeric($iat) ? \Illuminate\Support\Carbon::createFromTimestamp($iat) : \Illuminate\Support\Carbon::parse($iat);
        if ($now->diffInMinutes($issuedAt) >= $ttl) {
            abort(403, 'ingest session ttl exceeded');
        }

        // ★ スライド更新：TTLの半分を超えたら issued_at を now に更新
        if ($now->diffInMinutes($issuedAt) >= max(1, intdiv($ttl, 2))) {
            session(['ingest.issued_at' => $now->timestamp]);
            // 任意で expires_at も伸ばしたい場合（招待を固定にしたいなら外す）
            if ($exp) {
                session(['ingest.expires_at' => $now->addMinutes($ttl)->toIso8601String()]);
            }
        }

        return $next($request);
    }
}
