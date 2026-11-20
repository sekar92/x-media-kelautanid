<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\MediaRepository;

final class DashboardController
{
    public function __construct(private MediaRepository $repo)
    {
    }

    private function requireLogin(): array
    {
        $user = current_user();
        if (!$user) {
            flash('error', 'Silakan login terlebih dahulu untuk mengakses dashboard.');
            redirect('/login?next=' . rawurlencode($_SERVER['REQUEST_URI'] ?? '/dashboard'));
        }
        return $user;
    }

    private function requireModerator(): array
    {
        $user = $this->requireLogin();
        if (!can_moderate()) {
            abort_page(403, 'Akses Ditolak', 'Halaman ini khusus untuk Super Admin dan Moderator.');
        }
        return $user;
    }

    private function requireSuperAdmin(): array
    {
        $user = $this->requireLogin();
        if (!is_superadmin()) {
            abort_page(403, 'Akses Ditolak', 'Halaman pengaturan ini khusus untuk Super Admin.');
        }
        return $user;
    }

    // ---------- USER DASHBOARD (Item 8) ----------
    public function index(): void
    {
        $user = $this->requireLogin();
        $dbUser = $this->repo->findUserById((int) $user['id']) ?? $user;

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $res = $this->repo->userExtractions((int) $user['id'], $page, 12);

        // Kuota storage
        $role = $dbUser['role'] ?? 'basic';
        $customLimit = !empty($dbUser['storage_limit_mb']) ? (int) $dbUser['storage_limit_mb'] : null;
        $limitMb = $customLimit ?? match ($role) {
            'superadmin' => (int) app_setting('superadmin_storage_mb', 51200),
            'moderator'  => (int) app_setting('moderator_storage_mb', 10240),
            'premium'    => (int) app_setting('premium_storage_mb', 5120),
            default      => (int) app_setting('basic_storage_mb', 500),
        };
        $limitBytes = $limitMb * 1024 * 1024;
        $usedBytes = $this->repo->userStorageBytes((int) $user['id']);
        $percentUsed = $limitBytes > 0 ? min(100, round(($usedBytes / $limitBytes) * 100, 1)) : 0;

        // Statistik Sistem & Server untuk Super Admin & Moderator (Item 8)
        $systemStats = can_moderate() ? $this->repo->systemStats() : null;

        render('dashboard/user', [
            'user'         => $dbUser,
            'extractions'  => $res['rows'],
            'total'        => $res['total'],
            'page'         => $page,
            'pages'        => max(1, (int) ceil($res['total'] / 12)),
            'usedBytes'    => $usedBytes,
            'limitBytes'   => $limitBytes,
            'percentUsed'  => $percentUsed,
            'retentionDays'=> $role === 'premium' ? (int) app_setting('premium_retention_days', 90) : (int) app_setting('basic_retention_days', 7),
            'systemStats'  => $systemStats,
        ], 'Dashboard Pengguna');
    }

    // ---------- UPGRADE PAGE FOR BASIC USER (Item 7) ----------
    public function upgrade(): void
    {
        $user = current_user();
        $waNumber = preg_replace('/[^0-9]/', '', (string) app_setting('wa_number', '6281234567890'));
        $username = $user['username'] ?? 'Tamu';
        $template = (string) app_setting('wa_message', 'Halo Admin Kelautan.ID, saya ingin konfirmasi pembayaran upgrade ke User Premium untuk akun: {username}.');
        $text = str_replace('{username}', $username, $template);
        $waLink = "https://wa.me/{$waNumber}?text=" . rawurlencode($text);

        render('dashboard/upgrade', [
            'user'              => $user,
            'waLink'            => $waLink,
            'waNumber'          => app_setting('wa_number', '6281234567890'),
            'price'             => app_setting('premium_price', 'Rp 25.000 / bulan'),
            'basicStorageMb'    => app_setting('basic_storage_mb', '500'),
            'premiumStorageMb'  => app_setting('premium_storage_mb', '5120'),
            'basicRetention'    => app_setting('basic_retention_days', '7'),
            'premiumRetention'  => app_setting('premium_retention_days', '90'),
        ], 'Upgrade ke User Premium');
    }

    // ---------- MODERATION DASHBOARD (Item 9) ----------
    public function moderation(): void
    {
        $this->requireModerator();
        $q = trim((string) ($_GET['q'] ?? ''));
        $filter = in_array($_GET['filter'] ?? '', ['flagged', 'saved'], true) ? $_GET['filter'] : '';
        $page = max(1, (int) ($_GET['page'] ?? 1));

        $res = $this->repo->moderationList($q, $filter, $page, 15);
        $pages = max(1, (int) ceil($res['total'] / 15));

        render('dashboard/moderation', [
            'rows'   => $res['rows'],
            'total'  => $res['total'],
            'q'      => $q,
            'filter' => $filter,
            'page'   => min($page, $pages),
            'pages'  => $pages,
        ], 'Dashboard Moderasi');
    }

    public function flagTweet(array $p): void
    {
        $mod = $this->requireModerator();
        if (!verify_csrf()) {
            flash('error', 'Sesi kedaluwarsa.');
            redirect('/admin/moderation');
        }
        $reason = trim((string) ($_POST['reason'] ?? 'Pelanggaran aturan komunitas'));
        $this->repo->flagTweet($p['tweet_id'], $reason, (int) $mod['id']);
        flash('success', "Unggahan ID {$p['tweet_id']} berhasil ditandai (flagged).");
        redirect('/admin/moderation');
    }

    public function unflagTweet(array $p): void
    {
        $this->requireModerator();
        if (!verify_csrf()) {
            flash('error', 'Sesi kedaluwarsa.');
            redirect('/admin/moderation');
        }
        $this->repo->unflagTweet($p['tweet_id']);
        flash('success', "Tanda pelanggaran pada unggahan ID {$p['tweet_id']} dicabut.");
        redirect('/admin/moderation');
    }

    // ---------- SUPER ADMIN SETTINGS (Item 7) ----------
    public function showSettings(): void
    {
        $this->requireSuperAdmin();
        $settings = $this->repo->allSettings();
        render('dashboard/settings', ['settings' => $settings], 'Pengaturan Aplikasi');
    }

    public function saveSettings(): void
    {
        $this->requireSuperAdmin();
        if (!verify_csrf()) {
            flash('error', 'Sesi kedaluwarsa.');
            redirect('/admin/settings');
        }

        $fields = [
            'wa_number'             => trim((string) ($_POST['wa_number'] ?? '')),
            'wa_message'            => trim((string) ($_POST['wa_message'] ?? '')),
            'guest_storage_mb'      => max(100, (int) ($_POST['guest_storage_mb'] ?? 1024)),
            'basic_storage_mb'      => max(50, (int) ($_POST['basic_storage_mb'] ?? 500)),
            'premium_storage_mb'    => max(500, (int) ($_POST['premium_storage_mb'] ?? 5120)),
            'moderator_storage_mb'  => max(500, (int) ($_POST['moderator_storage_mb'] ?? 10240)),
            'superadmin_storage_mb' => max(1000, (int) ($_POST['superadmin_storage_mb'] ?? 51200)),
            'guest_max_extractions' => max(1, (int) ($_POST['guest_max_extractions'] ?? 5)),
            'basic_retention_days'  => max(1, (int) ($_POST['basic_retention_days'] ?? 7)),
            'premium_retention_days'=> max(1, (int) ($_POST['premium_retention_days'] ?? 90)),
            'premium_price'         => trim((string) ($_POST['premium_price'] ?? 'Rp 25.000 / bulan')),
        ];

        $this->repo->updateSettings($fields);
        flash('success', 'Pengaturan aplikasi dan konfigurasi kuota berhasil disimpan.');
        redirect('/admin/settings');
    }

    // ---------- SUPER ADMIN USER MANAGEMENT (Item 5) ----------
    public function users(): void
    {
        $this->requireSuperAdmin();
        $q = trim((string) ($_GET['q'] ?? ''));
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $res = $this->repo->allUsers($q, $page, 15);
        $pages = max(1, (int) ceil($res['total'] / 15));

        render('dashboard/users', [
            'users' => $res['rows'],
            'total' => $res['total'],
            'q'     => $q,
            'page'  => min($page, $pages),
            'pages' => $pages,
        ], 'Manajemen Pengguna');
    }

    public function createUserAction(): void
    {
        $this->requireSuperAdmin();
        if (!verify_csrf()) {
            flash('error', 'Sesi kedaluwarsa.');
            redirect('/admin/users');
        }

        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $role = (string) ($_POST['role'] ?? 'basic');
        $email = trim((string) ($_POST['email'] ?? '')) ?: null;
        $phoneWa = trim((string) ($_POST['phone_wa'] ?? '')) ?: null;
        $storageLimitMb = !empty($_POST['storage_limit_mb']) ? (int) $_POST['storage_limit_mb'] : null;

        if (!preg_match('/^[A-Za-z0-9_]{3,30}$/', $username)) {
            flash('error', 'Username harus berupa 3-30 karakter (huruf, angka, _).');
            redirect('/admin/users');
        }

        if (strlen($password) < 6) {
            flash('error', 'Kata sandi minimal 6 karakter.');
            redirect('/admin/users');
        }

        if (!in_array($role, ['superadmin', 'moderator', 'premium', 'basic'], true)) {
            $role = 'basic';
        }

        if ($this->repo->findUserByUsername($username)) {
            flash('error', "Username '{$username}' sudah terdaftar.");
            redirect('/admin/users');
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);
        $this->repo->createUser($username, $hash, $role, $email, $phoneWa, $storageLimitMb);

        flash('success', "Pengguna baru '@{$username}' berhasil ditambahkan.");
        redirect('/admin/users');
    }

    public function editUserAction(array $p): void
    {
        $this->requireSuperAdmin();
        if (!verify_csrf()) {
            flash('error', 'Sesi kedaluwarsa.');
            redirect('/admin/users');
        }

        $userId = (int) $p['id'];
        $user = $this->repo->findUserById($userId);
        if (!$user) {
            flash('error', 'Pengguna tidak ditemukan.');
            redirect('/admin/users');
        }

        $username = trim((string) ($_POST['username'] ?? ''));
        $role = (string) ($_POST['role'] ?? 'basic');
        $email = trim((string) ($_POST['email'] ?? '')) ?: null;
        $phoneWa = trim((string) ($_POST['phone_wa'] ?? '')) ?: null;
        $storageLimitMb = !empty($_POST['storage_limit_mb']) ? (int) $_POST['storage_limit_mb'] : null;
        $password = trim((string) ($_POST['password'] ?? ''));

        if (!preg_match('/^[A-Za-z0-9_]{3,30}$/', $username)) {
            flash('error', 'Username harus berupa 3-30 karakter.');
            redirect('/admin/users');
        }

        $existing = $this->repo->findUserByUsername($username);
        if ($existing && (int)$existing['id'] !== $userId) {
            flash('error', "Username '{$username}' sudah digunakan oleh akun lain.");
            redirect('/admin/users');
        }

        if (!in_array($role, ['superadmin', 'moderator', 'premium', 'basic'], true)) {
            $role = $user['role'];
        }

        $this->repo->updateUserFull($userId, $username, $role, $email, $phoneWa, $storageLimitMb, $password ?: null);

        flash('success', "Data pengguna '@{$username}' berhasil diperbarui.");
        redirect('/admin/users');
    }

    public function deleteUserAction(array $p): void
    {
        $admin = $this->requireSuperAdmin();
        if (!verify_csrf()) {
            flash('error', 'Sesi kedaluwarsa.');
            redirect('/admin/users');
        }

        $userId = (int) $p['id'];
        if ($userId === (int) $admin['id']) {
            flash('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
            redirect('/admin/users');
        }

        $user = $this->repo->findUserById($userId);
        if (!$user) {
            flash('error', 'Pengguna tidak ditemukan.');
            redirect('/admin/users');
        }

        $this->repo->deleteUser($userId);
        flash('success', "Pengguna '@{$user['username']}' berhasil dihapus permanen.");
        redirect('/admin/users');
    }

    public function updateUserRole(array $p): void
    {
        $this->requireSuperAdmin();
        if (!verify_csrf()) {
            flash('error', 'Sesi kedaluwarsa.');
            redirect('/admin/users');
        }
        $userId = (int) $p['id'];
        $newRole = (string) ($_POST['role'] ?? 'basic');
        if (in_array($newRole, ['superadmin', 'moderator', 'premium', 'basic'], true)) {
            $this->repo->updateUserRole($userId, $newRole);
            flash('success', "Role pengguna berhasil diubah menjadi: " . user_role_label($newRole));
        }
        redirect('/admin/users');
    }

    // ---------- PAGES MANAGEMENT (Item 10: ToS, Rules, Guidelines) ----------
    public function showPages(): void
    {
        $this->requireModerator();
        $pages = $this->repo->allPages();
        render('dashboard/pages', ['pages' => $pages], 'Kelola Halaman Regulasi');
    }

    public function showEditPage(array $p): void
    {
        $this->requireModerator();
        $page = $this->repo->findPage($p['slug']);
        if (!$page) {
            abort_page(404, 'Halaman Tidak Ditemukan', 'Halaman yang dimaksud belum tersedia.');
        }
        render('dashboard/edit_page', ['page' => $page], 'Edit ' . $page['title']);
    }

    public function saveEditPage(array $p): void
    {
        $mod = $this->requireModerator();
        if (!verify_csrf()) {
            flash('error', 'Sesi kedaluwarsa.');
            redirect('/admin/pages');
        }
        $slug = $p['slug'];
        $title = trim((string) ($_POST['title'] ?? ''));
        $content = trim((string) ($_POST['content'] ?? ''));

        if ($title === '' || $content === '') {
            flash('error', 'Judul dan konten halaman tidak boleh kosong.');
            redirect('/admin/pages/' . $slug);
        }

        $this->repo->updatePage($slug, $title, $content, (int) $mod['id']);
        flash('success', "Halaman '{$title}' berhasil diperbarui.");
        redirect('/admin/pages');
    }

    // ---------- PUBLIC PAGES VIEW (Item 10) ----------
    public function viewPublicPage(array $p): void
    {
        $slug = $p['slug'];
        $page = $this->repo->findPage($slug);
        if (!$page) {
            abort_page(404, 'Halaman Tidak Ditemukan', 'Dokumen regulasi ini belum tersedia.');
        }
        render('page_public', ['page' => $page], $page['title']);
    }
}
