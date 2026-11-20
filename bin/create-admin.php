<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit("Skrip ini hanya dapat dijalankan dari CLI.\n");
}

require dirname(__DIR__) . '/src/bootstrap.php';

use App\Repositories\MediaRepository;

// Penggunaan: php bin/create-admin.php [username] [admin|operator]
$username = $argv[1] ?? '';
$role = $argv[2] ?? 'admin';

if ($username === '') {
    fwrite(STDOUT, 'Username: ');
    $username = trim((string) fgets(STDIN));
}
if (!preg_match('/^[A-Za-z0-9_.-]{3,50}$/', $username)) {
    exit("Username harus 3-50 karakter (huruf, angka, _ . -).\n");
}
if (!in_array($role, ['admin', 'operator'], true)) {
    exit("Role harus 'admin' atau 'operator'.\n");
}

function read_password(string $prompt): string
{
    fwrite(STDOUT, $prompt);
    $tty = stripos(PHP_OS, 'WIN') !== 0 && function_exists('posix_isatty') && posix_isatty(STDIN);
    if ($tty) {
        shell_exec('stty -echo');
    }
    $pw = rtrim((string) fgets(STDIN), "\r\n");
    if ($tty) {
        shell_exec('stty echo');
        fwrite(STDOUT, "\n");
    }
    return $pw;
}

$pw = read_password('Kata sandi (min. 8 karakter): ');
if (strlen($pw) < 8) {
    exit("Kata sandi minimal 8 karakter.\n");
}
if ($pw !== read_password('Ulangi kata sandi: ')) {
    exit("Kata sandi tidak cocok.\n");
}

try {
    $repo = new MediaRepository();
    if ($repo->findUserByUsername($username)) {
        exit("Username '{$username}' sudah ada.\n");
    }
    $repo->createUser($username, password_hash($pw, PASSWORD_DEFAULT), $role);
    echo "Akun {$role} '{$username}' berhasil dibuat.\n";
} catch (Throwable $e) {
    exit('Gagal: ' . $e->getMessage() . "\n");
}
