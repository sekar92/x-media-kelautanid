<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\MediaRepository;

final class AuthController
{
    private const MAX_ATTEMPTS = 5;
    private const LOCK_SECONDS = 300;

    public function __construct(private MediaRepository $repo)
    {
    }

    public function showLogin(): void
    {
        if (is_logged_in()) {
            redirect('/dashboard');
        }
        render('login', ['next' => $this->safeNext($_GET['next'] ?? '')], 'Masuk Akun');
    }

    public function login(): void
    {
        if (!verify_csrf()) {
            flash('error', 'Sesi kedaluwarsa. Silakan coba lagi.');
            redirect('/login');
        }
        $lockedUntil = (int) ($_SESSION['_lock_until'] ?? 0);
        if ($lockedUntil > time()) {
            flash('error', 'Terlalu banyak percobaan. Coba lagi dalam ' . ($lockedUntil - time()) . ' detik.');
            redirect('/login');
        }

        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $user = null;
        try {
            $user = $this->repo->findUserByUsername($username);
        } catch (\Throwable $e) {
            app_log('error', 'login: ' . $e->getMessage());
            flash('error', 'Basis data tidak tersedia.');
            redirect('/login');
        }

        // Verifikasi tetap dijalankan walau user tidak ada (mitigasi timing)
        $hash = $user['password_hash'] ?? '$2y$10$usesomesillystringforsaltuQ3Yv0o6o6o6o6o6o6o6o6o6o6o6o6';
        if ($user && password_verify($password, $hash)) {
            session_regenerate_id(true);
            unset($_SESSION['_login_fail'], $_SESSION['_lock_until']);
            $_SESSION['user'] = [
                'id'       => (int) $user['id'], 
                'username' => $user['username'], 
                'role'     => $user['role'],
                'email'    => $user['email'] ?? null,
                'phone_wa' => $user['phone_wa'] ?? null,
            ];
            app_log('audit', "login sukses user={$user['username']} role={$user['role']} ip=" . client_ip());
            flash('success', 'Selamat datang kembali, ' . $user['username'] . ' (' . user_role_label($user['role']) . ').');
            redirect($this->safeNext($_POST['next'] ?? '') ?: '/dashboard');
        }
        password_verify($password, $hash);

        $_SESSION['_login_fail'] = (int) ($_SESSION['_login_fail'] ?? 0) + 1;
        if ($_SESSION['_login_fail'] >= self::MAX_ATTEMPTS) {
            $_SESSION['_lock_until'] = time() + self::LOCK_SECONDS;
            $_SESSION['_login_fail'] = 0;
        }
        app_log('audit', "login gagal user={$username} ip=" . client_ip());
        flash('error', 'Username atau kata sandi salah.');
        redirect('/login');
    }

    public function showRegister(): void
    {
        if (is_logged_in()) {
            redirect('/dashboard');
        }
        render('register', [
            'old' => $_SESSION['_old_register'] ?? [],
        ], 'Daftar Akun Pengguna');
        unset($_SESSION['_old_register']);
    }

    public function register(): void
    {
        if (!verify_csrf()) {
            flash('error', 'Sesi kedaluwarsa. Silakan coba lagi.');
            redirect('/register');
        }

        $username = trim((string) ($_POST['username'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $phoneWa = trim((string) ($_POST['phone_wa'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $passwordConfirm = (string) ($_POST['password_confirm'] ?? '');

        $_SESSION['_old_register'] = [
            'username' => $username,
            'email'    => $email,
            'phone_wa' => $phoneWa,
        ];

        // Validasi
        if (!preg_match('/^[a-zA-Z0-9_]{3,30}$/', $username)) {
            flash('error', 'Username harus berupa 3-30 karakter alfanumerik (huruf, angka, garis bawah).');
            redirect('/register');
        }

        if (strlen($password) < 8) {
            flash('error', 'Kata sandi minimal 8 karakter.');
            redirect('/register');
        }

        if ($password !== $passwordConfirm) {
            flash('error', 'Konfirmasi kata sandi tidak cocok.');
            redirect('/register');
        }

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('error', 'Alamat email tidak valid.');
            redirect('/register');
        }

        // Cek username unik
        if ($this->repo->findUserByUsername($username)) {
            flash('error', 'Username "' . $username . '" sudah digunakan. Silakan pilih username lain.');
            redirect('/register');
        }

        try {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $userId = $this->repo->createUser($username, $hash, 'basic', $email ?: null, $phoneWa ?: null);

            // Login otomatis
            session_regenerate_id(true);
            unset($_SESSION['_old_register'], $_SESSION['_login_fail'], $_SESSION['_lock_until']);
            $_SESSION['user'] = [
                'id'       => $userId,
                'username' => $username,
                'role'     => 'basic',
                'email'    => $email ?: null,
                'phone_wa' => $phoneWa ?: null,
            ];

            app_log('audit', "register sukses user={$username} id={$userId} ip=" . client_ip());
            flash('success', 'Akun berhasil dibuat! Selamat datang di Kelautan.ID sebagai User Basic.');
            redirect('/dashboard');
        } catch (\Throwable $e) {
            app_log('error', 'register error: ' . $e->getMessage());
            flash('error', 'Terjadi kendala saat mendaftarkan akun. Silakan coba lagi.');
            redirect('/register');
        }
    }

    public function logout(): void
    {
        $_SESSION = [];
        session_regenerate_id(true);
        flash('success', 'Anda telah keluar.');
        redirect('/');
    }

    private function safeNext(string $next): string
    {
        return ($next !== '' && $next[0] === '/' && !str_starts_with($next, '//') && !str_contains($next, '\\')) ? $next : '';
    }
}
