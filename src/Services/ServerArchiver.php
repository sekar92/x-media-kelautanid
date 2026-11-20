<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\MediaRepository;

/** Simpan media ke disk lokal: streaming cURL → file, batas ukuran, kuota, dedup SHA-256. */
final class ServerArchiver
{
    private const EXT_BY_MIME = [
        'video/mp4' => 'mp4', 'image/jpeg' => 'jpg', 'image/png' => 'png',
        'image/webp' => 'webp', 'image/gif' => 'gif',
    ];

    public function __construct(private MediaRepository $repo)
    {
    }

    /** @return array{uuid:string,existing:bool} */
    public function save(array $media, ?int $userId): array
    {
        // FR-07: dedup berdasarkan tweet_id + media_index (media_item)
        $existing = $this->repo->savedFileByMedia((int) $media['id']);
        if ($existing) {
            return ['uuid' => $existing['uuid'], 'existing' => true];
        }

        $maxStorage = (int) config('max_storage_bytes');
        $maxFile = (int) config('max_file_bytes');
        $used = $this->repo->totalStorageBytes();
        if ($used >= $maxStorage) {
            throw new \RuntimeException('Kuota total penyimpanan server penuh (' . format_bytes($maxStorage) . '). Hubungi administrator.');
        }

        // Pengecekan Kuota Penyimpanan Per Level Pengguna (Item 3, 4, 5)
        $userLimit = $maxStorage;
        if ($userId) {
            $user = $this->repo->findUserById($userId);
            $role = $user['role'] ?? 'basic';
            $customLimit = !empty($user['storage_limit_mb']) ? (int) $user['storage_limit_mb'] : null;
            $mb = $customLimit ?? match ($role) {
                'superadmin' => (int) app_setting('superadmin_storage_mb', 51200),
                'moderator'  => (int) app_setting('moderator_storage_mb', 10240),
                'premium'    => (int) app_setting('premium_storage_mb', 5120),
                default      => (int) app_setting('basic_storage_mb', 500),
            };
            $userLimit = $mb * 1024 * 1024;

            $userUsed = $this->repo->userStorageBytes($userId);
            if ($userUsed >= $userLimit) {
                if ($role === 'basic') {
                    throw new \RuntimeException('Kapasitas penyimpanan User Basic Anda penuh (' . format_bytes($userLimit) . '). Silakan hapus berkas lama atau upgrade ke User Premium.');
                }
                throw new \RuntimeException('Kapasitas penyimpanan akun ' . user_role_label($role) . ' Anda penuh (' . format_bytes($userLimit) . '). Silakan bersihkan beberapa berkas.');
            }
        } else {
            // Unregistered user (1GB limit)
            $guestMb = (int) app_setting('guest_storage_mb', 1024);
            $userLimit = $guestMb * 1024 * 1024;
        }

        $url = (string) $media['best_direct_url'];
        $ext = 'mp4';
        if (isset($media['variants'][0]['ext'])) {
            $ext = $media['variants'][0]['ext'];
        }
        UrlGuard::assertAllowed($url);

        $tmpDir = (string) config('tmp_path');
        if (!is_dir($tmpDir) && !@mkdir($tmpDir, 0750, true)) {
            throw new \RuntimeException('Direktori sementara tidak dapat dibuat.');
        }
        $tmp = tempnam($tmpDir, 'dl_');
        $fh = fopen($tmp, 'wb');
        if (!$fh) {
            throw new \RuntimeException('Tidak dapat menulis ke penyimpanan server.');
        }

        $remaining = $maxStorage - $used;
        $limit = min($maxFile, $remaining);
        $exceeded = null;

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_FILE             => $fh,
            CURLOPT_FOLLOWLOCATION   => false,
            CURLOPT_TIMEOUT          => 900,
            CURLOPT_CONNECTTIMEOUT   => 10,
            CURLOPT_USERAGENT        => config('user_agent'),
            CURLOPT_PROTOCOLS        => CURLPROTO_HTTPS,
            CURLOPT_FAILONERROR      => true,
            CURLOPT_NOPROGRESS       => false,
            CURLOPT_PROGRESSFUNCTION => static function ($ch, $dlTotal, $dlNow, $ulTotal = 0, $ulNow = 0) use ($limit, $maxFile, &$exceeded): int {
                if ($dlNow > $limit || $dlTotal > $limit) {
                    $exceeded = ($limit < $maxFile) ? 'quota' : 'size';
                    return 1; // batalkan unduhan
                }
                return 0;
            },
        ]);
        set_time_limit(0);
        $ok = curl_exec($ch);
        $mime = strtolower(trim(explode(';', (string) curl_getinfo($ch, CURLINFO_CONTENT_TYPE))[0]));
        $err = curl_error($ch);
        curl_close($ch);
        fclose($fh);

        if ($ok === false) {
            @unlink($tmp);
            if ($exceeded === 'quota') {
                throw new \RuntimeException('Berkas melebihi sisa kuota penyimpanan server.');
            }
            if ($exceeded === 'size') {
                throw new \RuntimeException('Berkas melebihi batas ukuran maksimal ' . format_bytes($maxFile) . '.');
            }
            app_log('archive', "gagal unduh {$url}: {$err}");
            throw new \RuntimeException('Gagal mengunduh media dari X/Twitter. Coba lagi nanti.');
        }

        $size = (int) filesize($tmp);
        if ($size <= 0) {
            @unlink($tmp);
            throw new \RuntimeException('Berkas yang diunduh kosong.');
        }
        if (isset(self::EXT_BY_MIME[$mime])) {
            $ext = self::EXT_BY_MIME[$mime];
        } elseif ($mime !== '' && $mime !== 'application/octet-stream' && $mime !== 'binary/octet-stream') {
            @unlink($tmp);
            throw new \RuntimeException('Tipe berkas tidak didukung: ' . $mime);
        }
        $storedMime = array_search($ext, self::EXT_BY_MIME, true) ?: ($media['mime_type'] ?? 'video/mp4');

        $sha = hash_file('sha256', $tmp);
        $uuid = self::uuidV4();

        if (empty($media['tweet_id']) || !isset($media['author_handle'])) {
            $item = $this->repo->findMediaItem((int) $media['id']);
            if ($item) {
                $media['tweet_id'] = $item['tweet_id'] ?? null;
                $media['author_handle'] = $item['author_handle'] ?? null;
            }
        }
        $tweetId = (string) ($media['tweet_id'] ?? 'tweet');
        $handle = preg_replace('/[^A-Za-z0-9_]/', '', (string) ($media['author_handle'] ?? 'unknown')) ?: 'unknown';
        $filename = sprintf('kelautanid_%s_%s_%d.%s', $handle, $tweetId, (int) ($media['media_index'] ?? 0) + 1, $ext);

        // Dedup berdasarkan hash: gunakan berkas fisik yang sama
        $dupe = $this->repo->savedFileByHash($sha);
        if ($dupe && is_file(config('media_path') . '/' . $dupe['relative_path'])) {
            @unlink($tmp);
            $relative = $dupe['relative_path'];
        } else {
            $relative = date('Y') . '/' . date('m') . '/' . $uuid . '.' . $ext;
            $dest = config('media_path') . '/' . $relative;
            if (!is_dir(dirname($dest)) && !@mkdir(dirname($dest), 0750, true)) {
                @unlink($tmp);
                throw new \RuntimeException('Direktori penyimpanan tidak dapat dibuat.');
            }
            if (!@rename($tmp, $dest)) {
                if (!@copy($tmp, $dest)) {
                    @unlink($tmp);
                    throw new \RuntimeException('Gagal menyimpan berkas ke disk.');
                }
                @unlink($tmp);
            }
            @chmod($dest, 0640);
        }

        $this->repo->createSavedFile([
            'uuid' => $uuid, 'media_item_id' => (int) $media['id'], 'relative_path' => $relative,
            'filename' => $filename, 'size' => $size, 'sha256' => $sha, 'user_id' => $userId,
        ]);
        app_log('archive', "tersimpan {$relative} ({$size} B) media_item={$media['id']} user=" . ($userId ?? '-'));

        return ['uuid' => $uuid, 'existing' => false];
    }

    public function delete(string $uuid): bool
    {
        $file = $this->repo->savedFileByUuid($uuid);
        if (!$file) {
            return false;
        }
        $this->repo->deleteSavedFile($uuid);
        // Hapus berkas fisik hanya jika tidak lagi direferensikan baris lain
        if ($this->repo->countByPath($file['relative_path']) === 0) {
            $path = config('media_path') . '/' . $file['relative_path'];
            if (is_file($path)) {
                @unlink($path);
            }
        }
        app_log('archive', "dihapus {$file['relative_path']} uuid={$uuid}");
        return true;
    }

    public static function uuidV4(): string
    {
        $b = random_bytes(16);
        $b[6] = chr((ord($b[6]) & 0x0f) | 0x40);
        $b[8] = chr((ord($b[8]) & 0x3f) | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($b), 4));
    }
}
