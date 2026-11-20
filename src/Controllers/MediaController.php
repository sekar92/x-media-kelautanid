<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\MediaRepository;
use App\Services\UrlGuard;

final class MediaController
{
    public function __construct(private MediaRepository $repo)
    {
    }

    /** GET /download/{media_item_id}?variant=idx — streaming proxy dari twimg. */
    public function download(array $p): void
    {
        $media = $this->repo->findMediaItem((int) $p['id']);
        if (!$media) {
            abort_page(404, 'Tidak Ditemukan', 'Media tidak ditemukan.');
        }
        $idx = isset($_GET['variant']) ? (int) $_GET['variant'] : 0;
        $variant = $media['variants'][$idx] ?? null;
        $url = $variant['url'] ?? $media['best_direct_url'];
        $ext = $variant['ext'] ?? ($media['media_type'] === 'photo' ? 'jpg' : 'mp4');

        try {
            UrlGuard::assertAllowed($url);
        } catch (\RuntimeException $e) {
            abort_page(403, 'Ditolak', 'URL media tidak diizinkan.');
        }

        $handle = preg_replace('/[^A-Za-z0-9_]/', '', (string) $media['author_handle']) ?: 'unknown';
        $filename = sprintf('kelautanid_%s_%s_%d.%s', $handle, $media['tweet_id'], (int) $media['media_index'] + 1, $ext);
        $mime = $ext === 'mp4' ? 'video/mp4' : match ($ext) {
            'png' => 'image/png', 'webp' => 'image/webp', 'gif' => 'image/gif', default => 'image/jpeg',
        };

        session_write_close();
        set_time_limit(0);
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $sent = false;
        $length = null;
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT        => 0,
            CURLOPT_USERAGENT      => config('user_agent'),
            CURLOPT_PROTOCOLS      => CURLPROTO_HTTPS,
            CURLOPT_HEADERFUNCTION => static function ($ch, string $line) use (&$length): int {
                if (stripos($line, 'content-length:') === 0) {
                    $length = (int) trim(substr($line, 15));
                }
                return strlen($line);
            },
            CURLOPT_WRITEFUNCTION  => static function ($ch, string $data) use (&$sent, &$length, $mime, $filename): int {
                if (!$sent) {
                    if ((int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE) !== 200) {
                        return 0; // batalkan
                    }
                    header('Content-Type: ' . $mime);
                    header('Content-Disposition: attachment; filename="' . $filename . '"');
                    header('X-Content-Type-Options: nosniff');
                    header('Cache-Control: private, no-store');
                    if ($length) {
                        header('Content-Length: ' . $length);
                    }
                    $sent = true;
                }
                echo $data;
                flush();
                return strlen($data);
            },
        ]);
        $ok = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if (!$sent) {
            app_log('error', "download proxy gagal {$url}: {$err}");
            abort_page(502, 'Gagal Mengunduh', 'Media tidak dapat diambil dari X/Twitter. Mungkin sudah dihapus atau tautannya kedaluwarsa. Coba ekstrak ulang.');
        }
        exit;
    }

    /** GET /stream/{id} — streaming proxy dengan dukungan Range & no-referrer untuk HTML5 Video player. */
    public function stream(array $p): void
    {
        $media = $this->repo->findMediaItem((int) $p['id']);
        if (!$media) {
            abort_page(404, 'Tidak Ditemukan', 'Media tidak ditemukan.');
        }

        // Cek apakah berkas sudah tersimpan di arsip lokal
        $saved = $this->repo->savedFileByMedia((int) $media['id']);
        if ($saved) {
            $path = config('media_path') . '/' . $saved['relative_path'];
            if (is_file($path)) {
                $this->serveFilePath($path, $media['mime_type'] ?: 'video/mp4', $saved['filename'] ?? 'video.mp4', false);
                return;
            }
        }

        // Jika belum tersimpan lokal, stream langsung dari URL Twitter dengan curl tanpa referer
        $url = $media['best_direct_url'];
        try {
            UrlGuard::assertAllowed($url);
        } catch (\RuntimeException $e) {
            abort_page(403, 'Ditolak', 'URL media tidak diizinkan.');
        }

        $mime = $media['mime_type'] ?: 'video/mp4';

        session_write_close();
        set_time_limit(0);
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $headers = [
            'User-Agent: ' . config('user_agent'),
            'Accept: */*',
            'Referer: ', // PENTING: Kosongkan referer agar CDN Twitter tidak menolak 403
        ];

        if (!empty($_SERVER['HTTP_RANGE'])) {
            $headers[] = 'Range: ' . $_SERVER['HTTP_RANGE'];
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT        => 0,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_PROTOCOLS      => CURLPROTO_HTTPS,
            CURLOPT_HEADERFUNCTION => static function ($ch, string $line): int {
                $trimmed = trim($line);
                if ($trimmed === '') {
                    return strlen($line);
                }
                $lower = strtolower($trimmed);
                if (str_starts_with($lower, 'content-type:') ||
                    str_starts_with($lower, 'content-length:') ||
                    str_starts_with($lower, 'content-range:') ||
                    str_starts_with($lower, 'accept-ranges:')) {
                    header($trimmed);
                }
                return strlen($line);
            },
            CURLOPT_WRITEFUNCTION  => static function ($ch, string $data): int {
                static $init = false;
                if (!$init) {
                    $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
                    if ($code >= 200 && $code < 300) {
                        http_response_code($code);
                    } else {
                        http_response_code(200);
                    }
                    header('Access-Control-Allow-Origin: *');
                    header('X-Content-Type-Options: nosniff');
                    header('Cache-Control: public, max-age=86400');
                    $init = true;
                }
                echo $data;
                flush();
                return strlen($data);
            },
        ]);
        curl_exec($ch);
        curl_close($ch);
        exit;
    }

    /** GET /m/{uuid} — sajikan berkas lokal dengan dukungan HTTP Range. */
    public function serve(array $p): void
    {
        $file = $this->repo->savedFileByUuid($p['uuid']);
        $path = $file ? config('media_path') . '/' . $file['relative_path'] : '';
        if (!$file || !is_file($path)) {
            abort_page(404, 'Tidak Ditemukan', 'Berkas arsip tidak ditemukan.');
        }

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mime = match ($ext) {
            'mp4' => 'video/mp4', 'png' => 'image/png', 'webp' => 'image/webp', 'gif' => 'image/gif', default => 'image/jpeg',
        };
        $download = isset($_GET['dl']);
        if ($download) {
            $this->repo->incrementDownload($file['uuid']);
        }

        $this->serveFilePath($path, $mime, $file['filename'] ?? 'file.' . $ext, $download);
    }

    private function serveFilePath(string $path, string $mime, string $filename, bool $download = false): void
    {
        $size = (int) filesize($path);
        $start = 0;
        $end = $size - 1;
        $status = 200;
        if (!empty($_SERVER['HTTP_RANGE'])) {
            if (!preg_match('/^bytes=(\d*)-(\d*)$/', trim($_SERVER['HTTP_RANGE']), $m) || ($m[1] === '' && $m[2] === '')) {
                http_response_code(416);
                header("Content-Range: bytes */{$size}");
                exit;
            }
            if ($m[1] === '') {                       // suffix: bytes=-N
                $start = max(0, $size - (int) $m[2]);
            } else {
                $start = (int) $m[1];
                if ($m[2] !== '') {
                    $end = min((int) $m[2], $size - 1);
                }
            }
            if ($start > $end || $start >= $size) {
                http_response_code(416);
                header("Content-Range: bytes */{$size}");
                exit;
            }
            $status = 206;
        }

        session_write_close();
        set_time_limit(0);
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        http_response_code($status);
        header('Content-Type: ' . $mime);
        header('Accept-Ranges: bytes');
        header('Access-Control-Allow-Origin: *');
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: public, max-age=86400');
        header('Content-Disposition: ' . ($download ? 'attachment' : 'inline') . '; filename="' . str_replace('"', '', $filename) . '"');
        $length = $end - $start + 1;
        header('Content-Length: ' . $length);
        if ($status === 206) {
            header("Content-Range: bytes {$start}-{$end}/{$size}");
        }

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'HEAD') {
            exit;
        }
        $fh = fopen($path, 'rb');
        fseek($fh, $start);
        $left = $length;
        while ($left > 0 && !feof($fh) && !connection_aborted()) {
            $chunk = fread($fh, min(8192 * 16, $left));
            if ($chunk === false || $chunk === '') {
                break;
            }
            echo $chunk;
            $left -= strlen($chunk);
            flush();
        }
        fclose($fh);
        exit;
    }
}
