<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;

class ClamAvService
{
    public function scan(string $filePath): array
    {
        Log::info('ClamAV Connect Debug', [
            'socket' => config('clamav.socket'),
            'host'   => config('clamav.host'),
            'port'   => config('clamav.port'),
        ]);

        if (!is_readable($filePath)) {
            throw new Exception("file not readable: {$filePath}");
        }

        $socketPath = config('clamav.socket');
        $timeout    = (int) config('clamav.timeout', 30);

        if ($socketPath) {
            $address = "unix://{$socketPath}";
            $errno = $errstr = null;
            $fp = @fsockopen($address, 0, $errno, $errstr, $timeout);
        } else {
            $host  = config('clamav.host', '127.0.0.1');
            $port  = (int) config('clamav.port', 3310);
            $errno = $errstr = null;
            $fp = @fsockopen($host, $port, $errno, $errstr, $timeout);
        }

        if (!$fp) {
            throw new Exception("ClamAV connect failed: {$errstr} ({$errno})");
        }

        stream_set_timeout($fp, $timeout);
        // INSTREAM モード
        fwrite($fp, "nINSTREAM\n");

        $h = fopen($filePath, 'rb');
        if (!$h) {
            fclose($fp);
            throw new Exception("open failed: {$filePath}");
        }

        try {
            while (!feof($h)) {
                $chunk = fread($h, 8192);
                $len   = pack('N', strlen($chunk));
                fwrite($fp, $len . $chunk);
            }
            // ストリーム終端
            fwrite($fp, pack('N', 0));

            $response = '';
            while (!feof($fp)) {
                $response .= fread($fp, 8192);
            }
        } finally {
            fclose($h);
            fclose($fp);
        }

        // 典型レスポンス例:
        // stream: OK\n  /  stream: Eicar-Test-Signature FOUND\n
        $clean     = (strpos($response, 'OK') !== false) && (strpos($response, 'FOUND') === false);
        $signature = null;

        if (!$clean) {
            // “xxxxx FOUND” の xxxxx 部分を抽出
            if (preg_match('/[: ]([A-Za-z0-9._-]+)\s+FOUND/', $response, $m)) {
                $signature = $m[1];
            }
        }

        return [
            'clean'     => $clean,
            'signature' => $signature,
            'raw'       => trim($response),
        ];
    }

    public function quarantine(string $filePath, ?string $originalName = null): ?string
    {
        $dir = config('clamav.quarantine');
        if (!$dir) return null;

        if (!is_dir($dir)) {
            @mkdir($dir, 0750, true);
        }
        $name = ($originalName ?: basename($filePath));
        $dst  = rtrim($dir, '/').'/'.date('Ymd_His').'_'.bin2hex(random_bytes(4)).'_'.$name;

        if (@rename($filePath, $dst)) {
            @chmod($dst, 0640);
            return $dst;
        }
        return null;
    }
}
