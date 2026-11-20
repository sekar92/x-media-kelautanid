<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\MediaRepository;
use App\Services\ServerArchiver;

final class ArchiveController
{
    private const PER_PAGE = 12;

    public function __construct(private MediaRepository $repo)
    {
    }

    private function requireLogin(): void
    {
        if (!is_logged_in()) {
            flash('error', 'Silakan login terlebih dahulu untuk melakukan aksi ini.');
            redirect('/login');
        }
    }

    public function index(): void
    {
        if (!config('public_archive')) {
            $this->requireLogin();
        }
        $q = trim((string) ($_GET['q'] ?? ''));
        $type = in_array($_GET['type'] ?? '', ['video', 'photo'], true) ? $_GET['type'] : '';
        $page = max(1, (int) ($_GET['page'] ?? 1));

        $res = $this->repo->archiveList(mb_substr($q, 0, 100), $type, $page, self::PER_PAGE);
        $pages = max(1, (int) ceil($res['total'] / self::PER_PAGE));

        render('archive', [
            'rows'  => $res['rows'],
            'total' => $res['total'],
            'q'     => $q,
            'type'  => $type,
            'page'  => min($page, $pages),
            'pages' => $pages,
            'used'  => $this->repo->totalStorageBytes(),
        ], 'Video Terunduh');
    }

    public function save(): void
    {
        $this->requireLogin();
        $back = $this->safeBack($_POST['tweet_id'] ?? '');
        if (!verify_csrf()) {
            flash('error', 'Sesi kedaluwarsa. Silakan coba lagi.');
            redirect($back);
        }
        $media = $this->repo->findMediaItem((int) ($_POST['media_item_id'] ?? 0));
        if (!$media) {
            flash('error', 'Media tidak ditemukan.');
            redirect($back);
        }
        try {
            $r = (new ServerArchiver($this->repo))->save($media, (int) (current_user()['id'] ?? 0) ?: null);
            flash('success', $r['existing']
                ? 'Media ini sudah tersimpan di server. Menggunakan arsip yang ada.'
                : 'Media berhasil disimpan ke server.');
        } catch (\RuntimeException $e) {
            flash('error', $e->getMessage());
        } catch (\Throwable $e) {
            app_log('error', 'save: ' . $e->getMessage());
            flash('error', config('debug') ? $e->getMessage() : 'Gagal menyimpan media ke server.');
        }
        redirect($back);
    }

    public function delete(array $p): void
    {
        $this->requireLogin();
        if (!verify_csrf()) {
            flash('error', 'Sesi kedaluwarsa. Silakan coba lagi.');
            redirect('/archive');
        }
        $ok = (new ServerArchiver($this->repo))->delete($p['uuid']);
        flash($ok ? 'success' : 'error', $ok ? 'Arsip berhasil dihapus.' : 'Arsip tidak ditemukan.');
        $ret = (string) ($_POST['return'] ?? '/archive');
        redirect(preg_match('#^/archive(\?[\w=&%+.-]*)?$#', $ret) ? $ret : '/archive');
    }

    private function safeBack(string $tweetId): string
    {
        return preg_match('/^\d{1,32}$/', $tweetId) ? '/result/' . $tweetId : '/archive';
    }
}
