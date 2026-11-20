<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class MediaRepository
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? Database::pdo();
    }

    // ---------- Users ----------
    public function findUserByUsername(string $username): ?array
    {
        $st = $this->db->prepare('SELECT * FROM users WHERE username = ?');
        $st->execute([$username]);
        return $st->fetch() ?: null;
    }

    public function findUserById(int $id): ?array
    {
        $st = $this->db->prepare('SELECT * FROM users WHERE id = ?');
        $st->execute([$id]);
        return $st->fetch() ?: null;
    }

    public function createUser(string $username, string $hash, string $role = 'basic', ?string $email = null, ?string $phoneWa = null, ?int $storageLimitMb = null): int
    {
        $st = $this->db->prepare('INSERT INTO users (username, password_hash, role, email, phone_wa, storage_limit_mb) VALUES (?, ?, ?, ?, ?, ?)');
        $st->execute([$username, $hash, $role, $email, $phoneWa, $storageLimitMb]);
        return (int) $this->db->lastInsertId();
    }

    public function allUsers(string $q = '', int $page = 1, int $perPage = 20): array
    {
        $where = [];
        $params = [];
        if ($q !== '') {
            $like = '%' . addcslashes($q, '%_\\') . '%';
            $where[] = '(username LIKE :q1 OR email LIKE :q2 OR phone_wa LIKE :q3)';
            $params[':q1'] = $like;
            $params[':q2'] = $like;
            $params[':q3'] = $like;
        }
        $w = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $cntSt = $this->db->prepare("SELECT COUNT(*) FROM users $w");
        $cntSt->execute($params);
        $total = (int) $cntSt->fetchColumn();

        $st = $this->db->prepare(
            "SELECT u.*, 
                    (SELECT COALESCE(SUM(sf.file_size_bytes), 0) FROM saved_files sf WHERE sf.saved_by_user_id = u.id) AS used_storage,
                    (SELECT COUNT(*) FROM tweets t WHERE t.user_id = u.id) AS extraction_count
             FROM users u $w ORDER BY u.id DESC LIMIT :lim OFFSET :off"
        );
        foreach ($params as $k => $v) {
            $st->bindValue($k, $v);
        }
        $st->bindValue(':lim', $perPage, PDO::PARAM_INT);
        $st->bindValue(':off', max(0, ($page - 1) * $perPage), PDO::PARAM_INT);
        $st->execute();

        return ['rows' => $st->fetchAll(), 'total' => $total];
    }

    public function updateUserRole(int $userId, string $role): void
    {
        $st = $this->db->prepare('UPDATE users SET role = ? WHERE id = ?');
        $st->execute([$role, $userId]);
    }

    public function updateUserFull(int $userId, string $username, string $role, ?string $email = null, ?string $phoneWa = null, ?int $storageLimitMb = null, ?string $password = null): void
    {
        if ($password !== null && $password !== '') {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $st = $this->db->prepare('UPDATE users SET username = ?, password_hash = ?, role = ?, email = ?, phone_wa = ?, storage_limit_mb = ? WHERE id = ?');
            $st->execute([$username, $hash, $role, $email, $phoneWa, $storageLimitMb, $userId]);
        } else {
            $st = $this->db->prepare('UPDATE users SET username = ?, role = ?, email = ?, phone_wa = ?, storage_limit_mb = ? WHERE id = ?');
            $st->execute([$username, $role, $email, $phoneWa, $storageLimitMb, $userId]);
        }
    }

    public function updateUserStorageLimit(int $userId, ?int $limitMb): void
    {
        $st = $this->db->prepare('UPDATE users SET storage_limit_mb = ? WHERE id = ?');
        $st->execute([$limitMb, $userId]);
    }

    public function deleteUser(int $userId): void
    {
        $st = $this->db->prepare('DELETE FROM users WHERE id = ?');
        $st->execute([$userId]);
    }

    /** Statistik Sistem & Server untuk Super Admin & Moderator (Item 8) */
    public function systemStats(): array
    {
        $stFiles = $this->db->query('SELECT COUNT(*) as total_files, COALESCE(SUM(file_size_bytes), 0) as total_bytes FROM saved_files');
        $filesData = $stFiles->fetch() ?: ['total_files' => 0, 'total_bytes' => 0];

        $stUsers = $this->db->query('SELECT role, COUNT(*) as count FROM users GROUP BY role');
        $roleCounts = ['superadmin' => 0, 'moderator' => 0, 'premium' => 0, 'basic' => 0];
        $totalUsers = 0;
        foreach ($stUsers->fetchAll() as $r) {
            $roleCounts[$r['role']] = (int) $r['count'];
            $totalUsers += (int) $r['count'];
        }

        $stTweets = $this->db->query('SELECT COUNT(*) as total_tweets FROM tweets');
        $tweetsData = $stTweets->fetch() ?: ['total_tweets' => 0];

        return [
            'total_files'     => (int) $filesData['total_files'],
            'total_bytes'     => (int) $filesData['total_bytes'],
            'total_tweets'    => (int) $tweetsData['total_tweets'],
            'total_users'     => $totalUsers,
            'role_counts'     => $roleCounts,
            'server_max_bytes'=> (int) config('max_storage_bytes'),
        ];
    }

    // ---------- Settings (app_settings) ----------
    public function allSettings(): array
    {
        $rows = $this->db->query('SELECT setting_key, setting_value FROM app_settings')->fetchAll();
        $out = [];
        foreach ($rows as $r) {
            $out[$r['setting_key']] = $r['setting_value'];
        }
        return $out;
    }

    public function updateSettings(array $settings): void
    {
        $st = $this->db->prepare('INSERT INTO app_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
        foreach ($settings as $k => $v) {
            $st->execute([$k, (string) $v]);
        }
    }

    // ---------- Pages (ToS, Rules, Guidelines) ----------
    public function allPages(): array
    {
        return $this->db->query('SELECT * FROM pages ORDER BY slug ASC')->fetchAll();
    }

    public function findPage(string $slug): ?array
    {
        $st = $this->db->prepare('SELECT * FROM pages WHERE slug = ?');
        $st->execute([$slug]);
        return $st->fetch() ?: null;
    }

    public function updatePage(string $slug, string $title, string $content, ?int $userId = null): void
    {
        $st = $this->db->prepare(
            'INSERT INTO pages (slug, title, content, updated_by_user_id) 
             VALUES (?, ?, ?, ?) 
             ON DUPLICATE KEY UPDATE title = VALUES(title), content = VALUES(content), updated_by_user_id = VALUES(updated_by_user_id)'
        );
        $st->execute([$slug, $title, $content, $userId]);
    }

    // ---------- Tweets & Media ----------
    public function findTweet(string $tweetId): ?array
    {
        $st = $this->db->prepare('SELECT * FROM tweets WHERE tweet_id = ?');
        $st->execute([$tweetId]);
        $row = $st->fetch();
        if ($row && $row['raw_metadata']) {
            $row['meta'] = json_decode($row['raw_metadata'], true) ?: [];
        } elseif ($row) {
            $row['meta'] = [];
        }
        return $row ?: null;
    }

    public function isFresh(array $tweet, int $ttl): bool
    {
        $st = $this->db->prepare('SELECT COUNT(*) FROM tweets WHERE id = ? AND fetched_at >= (NOW() - INTERVAL ? SECOND)');
        $st->execute([$tweet['id'], $ttl]);
        return (int) $st->fetchColumn() > 0
            && (int) $this->db->query('SELECT COUNT(*) FROM media_items WHERE tweet_record_id = ' . (int) $tweet['id'])->fetchColumn() > 0;
    }

    /** Simpan hasil ekstraksi (upsert, ID media_items dipertahankan agar arsip tetap valid). */
    public function saveExtraction(array $d, ?int $userId = null, ?string $ip = null): int
    {
        $this->db->beginTransaction();
        try {
            $st = $this->db->prepare(
                'INSERT INTO tweets (tweet_id, user_id, ip_address, original_url, author_name, author_handle, tweet_text, source_engine, raw_metadata, fetched_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                 ON DUPLICATE KEY UPDATE 
                   user_id = COALESCE(VALUES(user_id), user_id),
                   ip_address = COALESCE(VALUES(ip_address), ip_address),
                   original_url = VALUES(original_url), 
                   author_name = VALUES(author_name),
                   author_handle = VALUES(author_handle), 
                   tweet_text = VALUES(tweet_text),
                   source_engine = VALUES(source_engine), 
                   raw_metadata = VALUES(raw_metadata), 
                   fetched_at = NOW()'
            );
            $st->execute([
                $d['tweet_id'],
                $userId,
                $ip,
                mb_substr($d['original_url'], 0, 512),
                $d['author_name'] !== null ? mb_substr($d['author_name'], 0, 150) : null,
                $d['author_handle'] !== null ? mb_substr($d['author_handle'], 0, 64) : null,
                $d['tweet_text'],
                $d['engine'],
                json_encode($d['meta'], JSON_UNESCAPED_UNICODE),
            ]);

            $st = $this->db->prepare('SELECT id FROM tweets WHERE tweet_id = ?');
            $st->execute([$d['tweet_id']]);
            $recordId = (int) $st->fetchColumn();

            $ins = $this->db->prepare(
                'INSERT INTO media_items (tweet_record_id, media_index, media_type, best_direct_url, thumbnail_url, mime_type, width, height, duration_sec, bitrate, variants_json)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE media_type = VALUES(media_type), best_direct_url = VALUES(best_direct_url),
                   thumbnail_url = VALUES(thumbnail_url), mime_type = VALUES(mime_type), width = VALUES(width),
                   height = VALUES(height), duration_sec = VALUES(duration_sec), bitrate = VALUES(bitrate),
                   variants_json = VALUES(variants_json)'
            );
            foreach ($d['media'] as $i => $m) {
                $ins->execute([
                    $recordId, $i, $m['type'], $m['best_url'], $m['thumbnail'], $m['mime'],
                    $m['width'], $m['height'], $m['duration'], $m['bitrate'],
                    json_encode($m['variants'], JSON_UNESCAPED_SLASHES),
                ]);
            }
            // Hapus media berlebih yang belum diarsipkan
            $del = $this->db->prepare(
                'DELETE mi FROM media_items mi LEFT JOIN saved_files sf ON sf.media_item_id = mi.id
                 WHERE mi.tweet_record_id = ? AND mi.media_index >= ? AND sf.id IS NULL'
            );
            $del->execute([$recordId, count($d['media'])]);

            $this->db->commit();
            return $recordId;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /** Menghapus tweet, media_items terkait, record saved_files, dan berkas fisik di disk */
    public function deleteTweet(string $tweetId): bool
    {
        $st = $this->db->prepare(
            'SELECT sf.relative_path, sf.uuid FROM saved_files sf
             JOIN media_items mi ON mi.id = sf.media_item_id
             JOIN tweets t ON t.id = mi.tweet_record_id
             WHERE t.tweet_id = ?'
        );
        $st->execute([$tweetId]);
        $filesToDelete = $st->fetchAll();

        $delSt = $this->db->prepare('DELETE FROM tweets WHERE tweet_id = ?');
        $delSt->execute([$tweetId]);
        $deleted = $delSt->rowCount() > 0;

        if ($deleted) {
            foreach ($filesToDelete as $f) {
                if ($this->countByPath($f['relative_path']) === 0) {
                    $path = config('media_path') . '/' . $f['relative_path'];
                    if (is_file($path)) {
                        @unlink($path);
                    }
                }
            }
        }
        return $deleted;
    }

    public function flagTweet(string $tweetId, string $reason, int $moderatorUserId): void
    {
        $st = $this->db->prepare(
            'UPDATE tweets SET is_flagged = 1, flag_reason = ?, moderated_at = NOW(), moderated_by_user_id = ? WHERE tweet_id = ?'
        );
        $st->execute([$reason, $moderatorUserId, $tweetId]);
    }

    public function unflagTweet(string $tweetId): void
    {
        $st = $this->db->prepare(
            'UPDATE tweets SET is_flagged = 0, flag_reason = NULL, moderated_at = NOW() WHERE tweet_id = ?'
        );
        $st->execute([$tweetId]);
    }

    public function mediaForTweet(int $tweetRecordId): array
    {
        $st = $this->db->prepare(
            'SELECT mi.*, sf.uuid AS saved_uuid, sf.file_size_bytes AS saved_size
             FROM media_items mi LEFT JOIN saved_files sf ON sf.media_item_id = mi.id
             WHERE mi.tweet_record_id = ? ORDER BY mi.media_index'
        );
        $st->execute([$tweetRecordId]);
        return array_map([$this, 'decodeMedia'], $st->fetchAll());
    }

    public function findMediaItem(int $id): ?array
    {
        $st = $this->db->prepare(
            'SELECT mi.*, t.tweet_id, t.author_handle, sf.uuid AS saved_uuid
             FROM media_items mi
             JOIN tweets t ON t.id = mi.tweet_record_id
             LEFT JOIN saved_files sf ON sf.media_item_id = mi.id
             WHERE mi.id = ?'
        );
        $st->execute([$id]);
        $row = $st->fetch();
        return $row ? $this->decodeMedia($row) : null;
    }

    private function decodeMedia(array $row): array
    {
        $row['variants'] = $row['variants_json'] ? (json_decode($row['variants_json'], true) ?: []) : [];
        return $row;
    }

    public function recentTweets(int $limit = 6): array
    {
        $st = $this->db->prepare(
            'SELECT t.*, (SELECT COUNT(*) FROM media_items WHERE tweet_record_id = t.id) AS media_count,
                    (SELECT thumbnail_url FROM media_items WHERE tweet_record_id = t.id ORDER BY media_index LIMIT 1) AS thumb
             FROM tweets t 
             WHERE t.is_flagged = 0
             ORDER BY t.fetched_at DESC LIMIT ?'
        );
        $st->bindValue(1, $limit, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll();
    }

    /** Daftar moderasi untuk Super Admin & Moderator */
    public function moderationList(string $q = '', string $filter = '', int $page = 1, int $perPage = 15): array
    {
        $where = [];
        $params = [];
        if ($q !== '') {
            $like = '%' . addcslashes($q, '%_\\') . '%';
            $where[] = '(t.author_handle LIKE :q1 OR t.tweet_text LIKE :q2 OR t.tweet_id = :q3)';
            $params[':q1'] = $like;
            $params[':q2'] = $like;
            $params[':q3'] = ltrim($q, '@');
        }
        if ($filter === 'flagged') {
            $where[] = 't.is_flagged = 1';
        } elseif ($filter === 'saved') {
            $where[] = 'EXISTS (SELECT 1 FROM media_items mi JOIN saved_files sf ON sf.media_item_id = mi.id WHERE mi.tweet_record_id = t.id)';
        }

        $w = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $cntSt = $this->db->prepare("SELECT COUNT(*) FROM tweets t $w");
        $cntSt->execute($params);
        $total = (int) $cntSt->fetchColumn();

        $st = $this->db->prepare(
            "SELECT t.*, u.username AS extractor_username, u.role AS extractor_role,
                    (SELECT COUNT(*) FROM media_items WHERE tweet_record_id = t.id) AS media_count,
                    (SELECT COUNT(*) FROM media_items mi JOIN saved_files sf ON sf.media_item_id = mi.id WHERE mi.tweet_record_id = t.id) AS saved_count,
                    (SELECT thumbnail_url FROM media_items WHERE tweet_record_id = t.id ORDER BY media_index LIMIT 1) AS thumb
             FROM tweets t 
             LEFT JOIN users u ON u.id = t.user_id
             $w ORDER BY t.fetched_at DESC LIMIT :lim OFFSET :off"
        );
        foreach ($params as $k => $v) {
            $st->bindValue($k, $v);
        }
        $st->bindValue(':lim', $perPage, PDO::PARAM_INT);
        $st->bindValue(':off', max(0, ($page - 1) * $perPage), PDO::PARAM_INT);
        $st->execute();

        return ['rows' => $st->fetchAll(), 'total' => $total];
    }

    /** Riwayat ekstraksi spesifik milik seorang user */
    public function userExtractions(int $userId, int $page = 1, int $perPage = 12): array
    {
        $cntSt = $this->db->prepare('SELECT COUNT(*) FROM tweets WHERE user_id = ?');
        $cntSt->execute([$userId]);
        $total = (int) $cntSt->fetchColumn();

        $st = $this->db->prepare(
            'SELECT t.*, (SELECT COUNT(*) FROM media_items WHERE tweet_record_id = t.id) AS media_count,
                    (SELECT COUNT(*) FROM media_items mi JOIN saved_files sf ON sf.media_item_id = mi.id WHERE mi.tweet_record_id = t.id) AS saved_count,
                    (SELECT thumbnail_url FROM media_items WHERE tweet_record_id = t.id ORDER BY media_index LIMIT 1) AS thumb
             FROM tweets t 
             WHERE t.user_id = :uid
             ORDER BY t.fetched_at DESC LIMIT :lim OFFSET :off'
        );
        $st->bindValue(':uid', $userId, PDO::PARAM_INT);
        $st->bindValue(':lim', $perPage, PDO::PARAM_INT);
        $st->bindValue(':off', max(0, ($page - 1) * $perPage), PDO::PARAM_INT);
        $st->execute();

        return ['rows' => $st->fetchAll(), 'total' => $total];
    }

    // ---------- Guest Extractions (Unregistered Users) ----------
    public function recordGuestExtraction(string $sessionId, string $ip, string $tweetId): void
    {
        $st = $this->db->prepare('INSERT INTO guest_extractions (session_id, ip_address, tweet_id) VALUES (?, ?, ?)');
        $st->execute([$sessionId, $ip, $tweetId]);
    }

    public function guestExtractionCount(string $sessionId, string $ip): int
    {
        // Hitung berdasarkan session ATAU IP dalam 24 jam terakhir
        $st = $this->db->prepare(
            'SELECT COUNT(DISTINCT tweet_id) FROM guest_extractions 
             WHERE (session_id = ? OR ip_address = ?) AND created_at >= (NOW() - INTERVAL 1 DAY)'
        );
        $st->execute([$sessionId, $ip]);
        return (int) $st->fetchColumn();
    }

    // ---------- Saved files & Storage ----------
    public function savedFileByMedia(int $mediaItemId): ?array
    {
        $st = $this->db->prepare('SELECT * FROM saved_files WHERE media_item_id = ?');
        $st->execute([$mediaItemId]);
        return $st->fetch() ?: null;
    }

    public function savedFileByUuid(string $uuid): ?array
    {
        $st = $this->db->prepare(
            'SELECT sf.*, mi.mime_type, mi.media_type FROM saved_files sf
             JOIN media_items mi ON mi.id = sf.media_item_id WHERE sf.uuid = ?'
        );
        $st->execute([$uuid]);
        return $st->fetch() ?: null;
    }

    public function savedFileByHash(string $sha256): ?array
    {
        $st = $this->db->prepare('SELECT * FROM saved_files WHERE sha256_hash = ? LIMIT 1');
        $st->execute([$sha256]);
        return $st->fetch() ?: null;
    }

    public function createSavedFile(array $f): void
    {
        $st = $this->db->prepare(
            'INSERT INTO saved_files (uuid, media_item_id, relative_path, filename, file_size_bytes, sha256_hash, saved_by_user_id)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $st->execute([$f['uuid'], $f['media_item_id'], $f['relative_path'], $f['filename'], $f['size'], $f['sha256'], $f['user_id']]);
    }

    public function totalStorageBytes(): int
    {
        return (int) $this->db->query(
            'SELECT COALESCE(SUM(sz), 0) FROM (SELECT MAX(file_size_bytes) AS sz FROM saved_files GROUP BY relative_path) x'
        )->fetchColumn();
    }

    public function userStorageBytes(int $userId): int
    {
        $st = $this->db->prepare('SELECT COALESCE(SUM(file_size_bytes), 0) FROM saved_files WHERE saved_by_user_id = ?');
        $st->execute([$userId]);
        return (int) $st->fetchColumn();
    }

    public function countByPath(string $relativePath): int
    {
        $st = $this->db->prepare('SELECT COUNT(*) FROM saved_files WHERE relative_path = ?');
        $st->execute([$relativePath]);
        return (int) $st->fetchColumn();
    }

    public function deleteSavedFile(string $uuid): void
    {
        $st = $this->db->prepare('DELETE FROM saved_files WHERE uuid = ?');
        $st->execute([$uuid]);
    }

    public function incrementDownload(string $uuid): void
    {
        $st = $this->db->prepare('UPDATE saved_files SET download_count = download_count + 1 WHERE uuid = ?');
        $st->execute([$uuid]);
    }

    public function archiveList(string $q, string $type, int $page, int $perPage, ?int $filterUserId = null): array
    {
        $where = [];
        $params = [];
        if ($q !== '') {
            $like = '%' . addcslashes(ltrim($q, '@'), '%_\\') . '%';
            $where[] = '(t.author_handle LIKE :q1 OR t.tweet_text LIKE :q2 OR t.tweet_id = :q3)';
            $params[':q1'] = $like;
            $params[':q2'] = $like;
            $params[':q3'] = ltrim($q, '@');
        }
        if ($type === 'video') {
            $where[] = "mi.media_type IN ('video','gif')";
        } elseif ($type === 'photo') {
            $where[] = "mi.media_type = 'photo'";
        }
        if ($filterUserId !== null) {
            $where[] = 'sf.saved_by_user_id = :uid';
            $params[':uid'] = $filterUserId;
        }

        $w = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $from = 'FROM saved_files sf JOIN media_items mi ON mi.id = sf.media_item_id JOIN tweets t ON t.id = mi.tweet_record_id';

        $st = $this->db->prepare("SELECT COUNT(*) $from $w");
        $st->execute($params);
        $total = (int) $st->fetchColumn();

        $st = $this->db->prepare(
            "SELECT sf.uuid, sf.filename, sf.file_size_bytes, sf.download_count, sf.created_at, sf.saved_by_user_id,
                    mi.media_type, mi.mime_type, mi.thumbnail_url, mi.media_index, t.tweet_id, t.author_handle, t.author_name, t.tweet_text
             $from $w ORDER BY sf.created_at DESC, sf.id DESC LIMIT :lim OFFSET :off"
        );
        foreach ($params as $k => $v) {
            $st->bindValue($k, $v);
        }
        $st->bindValue(':lim', $perPage, PDO::PARAM_INT);
        $st->bindValue(':off', max(0, ($page - 1) * $perPage), PDO::PARAM_INT);
        $st->execute();

        return ['rows' => $st->fetchAll(), 'total' => $total];
    }

    /** Pembersihan Otomatis Berdasarkan Retensi (Item 3: Basic 7 hari, Item 4: Premium 90 hari) */
    public function purgeExpired(): array
    {
        $basicDays = (int) app_setting('basic_retention_days', 7);
        $premiumDays = (int) app_setting('premium_retention_days', 90);
        $unregisteredDays = 1;

        $deletedCount = 0;

        // 1. Bersihkan files Basic user > basicDays
        $st = $this->db->prepare(
            'SELECT sf.uuid, sf.relative_path FROM saved_files sf
             JOIN users u ON u.id = sf.saved_by_user_id
             WHERE u.role = "basic" AND sf.created_at < (NOW() - INTERVAL ? DAY)'
        );
        $st->execute([$basicDays]);
        $expiredBasic = $st->fetchAll();
        foreach ($expiredBasic as $f) {
            $this->deleteSavedFile($f['uuid']);
            if ($this->countByPath($f['relative_path']) === 0) {
                @unlink(config('media_path') . '/' . $f['relative_path']);
            }
            $deletedCount++;
        }

        // 2. Bersihkan files Premium user > premiumDays
        $st = $this->db->prepare(
            'SELECT sf.uuid, sf.relative_path FROM saved_files sf
             JOIN users u ON u.id = sf.saved_by_user_id
             WHERE u.role = "premium" AND sf.created_at < (NOW() - INTERVAL ? DAY)'
        );
        $st->execute([$premiumDays]);
        $expiredPremium = $st->fetchAll();
        foreach ($expiredPremium as $f) {
            $this->deleteSavedFile($f['uuid']);
            if ($this->countByPath($f['relative_path']) === 0) {
                @unlink(config('media_path') . '/' . $f['relative_path']);
            }
            $deletedCount++;
        }

        // 3. Bersihkan files unregistered user > unregisteredDays
        $st = $this->db->prepare(
            'SELECT sf.uuid, sf.relative_path FROM saved_files sf
             WHERE sf.saved_by_user_id IS NULL AND sf.created_at < (NOW() - INTERVAL ? DAY)'
        );
        $st->execute([$unregisteredDays]);
        $expiredGuest = $st->fetchAll();
        foreach ($expiredGuest as $f) {
            $this->deleteSavedFile($f['uuid']);
            if ($this->countByPath($f['relative_path']) === 0) {
                @unlink(config('media_path') . '/' . $f['relative_path']);
            }
            $deletedCount++;
        }

        // 4. Bersihkan data riwayat guest_extractions lama (> 7 hari)
        $this->db->exec('DELETE FROM guest_extractions WHERE created_at < (NOW() - INTERVAL 7 DAY)');

        return ['deleted_files' => $deletedCount];
    }
}
