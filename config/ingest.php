<?php

// config/ingest.php
return [
    'b_base_url'          => env('B_BASE_URL', 'https://jet.t1.tatsumori.co.jp'),
    'introspect_path'     => env('B_INTROSPECT_PATH', '/api/ingest/introspect'),
    'session_ttl'         => env('INGEST_SESSION_TTL', 30), // 分
    'introspect_insecure' => env('INGEST_INTROSPECT_INSECURE', false),

    // ★ 追加（既にお使いならそのまま）
    'max_files'           => env('SNAPGATE_MAX_FILES', 10),
    'max_mb'              => env('SNAPGATE_MAX_MB', 20),
    'dropbox_root'        => env('SNAPGATE_DROPBOX_ROOT', '/SNAPGATE'),
];

