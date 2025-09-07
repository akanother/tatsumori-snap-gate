<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;

class ClamAvService
{
    /** 設定取得: config()→env()→デフォルト の順で解決 */
    private function opt(string $key, $default = null) {
        // config('clamav.socket') が未定義なら null のままなので env() で補完
        switch ($key) {
            case 'socket':
                return config('clamav.socket') ?? env('CLAMAV_SOCKET', $default);
            case 'host':
                return config('clamav.host')   ?? env('CLAMAV_HOST',   $default);
            case 'port':
                return config('clamav.port')   ?? (int) env('CLAMAV_PORT', $default);
            case 'timeout':
                return config('clamav.timeout')?? (int) env('CLAMAV_TIMEOUT', $default);
            case 'quarantine':
                return config('clamav.quarantine') ?? env('CLAMAV_QUARANTINE', $default);
            default:
                return $default;
        }
    }

    public function scan(string $filePath): array
    {
        $socketPath = $this->opt('socket');
        $host       = $this->opt('host', '127.0.0.1');
        $port       = (int) $this->opt('port', 3310);
        $timeout    = (int) $this->opt('timeout', 30);

        Log::info('ClamAV Connect Debug', [
            'socket' => $socketPath,
            'host'   => $host,
            'port'   => $port,
        ]);

        if (!is_readable($filePath)) {
            throw new Exception("file not readable: {$filePath}");
        }

        // 優先: UNIXソケット → ダメなら TCP へフォールバック
        $errno = $errstr = null;
        $fp = null;
        $via = null;

        if ($socketPath) {
            $fp = @fsockopen("unix://{$socketPath}", 0, $errno, $errstr, $timeout);
            $via = "unix://{$socketPath}";
            if (!$fp) {
                Log::warning('clamav.unix.connect.failed', ['path'=>$socketPath,'errno'=>$errno,'err'=>$errstr]);
            }
        }
        if (!$fp) {
            $fp = @fsockopen($host, $port, $errno, $errstr, $timeout);
            $via = "tcp://{$host}:{$port}";
        }
        if (!$fp) {
            throw new Exception("ClamAV connect failed ({$via}): {$errstr} ({$errno})");
        }

        Log::info('clamav.open.ok', ['via' => $via]);

        stream_set_timeout($fp, $timeout);
        fwrite($fp, "INSTREAM\n"); // ← 正しいコマンド

        $h = fopen($filePath, 'rb');
        if (!$h) { fclose($fp); throw new Exception("open failed: {$filePath}"); }

        try {
            while (!feof($h)) {
                $chunk = fread($h, 8192);
                if ($chunk === false) break;
                fwrite($fp, pack('N', strlen($chunk)) . $chunk);
            }
            fwrite($fp, pack('N', 0)); // 終端

            $response = '';
            while (!feof($fp)) {
                $line = fgets($fp, 8192);
                if ($line === false) break;
                $response .= $line;
                if (strpos($line, 'OK') !== false || strpos($line, 'FOUND') !== false || strpos($line, 'ERROR') !== false) {
                    break;
                }
            }
        } finally {
            fclose($h);
            fclose($fp);
        }

        $clean     = (strpos($response, 'OK') !== false) && (strpos($response, 'FOUND') === false);
        $signature = null;
        if (!$clean && preg_match('/[: ]([A-Za-z0-9._-]+)\s+FOUND/', $response, $m)) {
            $signature = $m[1];
        }
        if (stripos($response, 'size limit exceeded') !== false) {
            Log::warning('clamav.instream.limit', ['raw' => trim($response)]);
        }

        return ['clean' => $clean, 'signature' => $signature, 'raw' => trim($response)];
    }

    public function quarantine(string $filePath, ?string $originalName = null): ?string
    {
        $dir = $this->opt('quarantine');
        if (!$dir) return null;
        if (!is_dir($dir)) @mkdir($dir, 0750, true);

        $name = ($originalName ?: basename($filePath));
        $dst  = rtrim($dir, '/').'/'.date('Ymd_His').'_'.bin2hex(random_bytes(4)).'_'.$name;

        if (@rename($filePath, $dst)) { @chmod($dst, 0640); return $dst; }
        return null;
    }
}
