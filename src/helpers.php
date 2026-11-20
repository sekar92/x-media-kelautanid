<?php
declare(strict_types=1);

function load_env(string $file): void
{
    if (!is_file($file)) {
        return;
    }
    foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) {
            continue;
        }
        [$k, $v] = explode('=', $line, 2);
        $k = trim($k);
        $v = trim($v);
        if (strlen($v) >= 2 && ($v[0] === '"' || $v[0] === "'") && substr($v, -1) === $v[0]) {
            $v = substr($v, 1, -1);
        } else {
            $v = trim(preg_replace('/\s+#.*$/', '', $v));
        }
        $_ENV[$k] = $v;
    }
}

function env(string $key, mixed $default = null): mixed
{
    $v = $_ENV[$key] ?? getenv($key);
    if ($v === false || $v === null) {
        return $default;
    }
    if (is_string($v)) {
        switch (strtolower($v)) {
            case 'true':  return true;
            case 'false': return false;
            case 'null':  return null;
        }
    }
    return $v;
}

function config(string $key, mixed $default = null): mixed
{
    static $cfg = null;
    $cfg ??= require BASE_PATH . '/config/app.php';
    return $cfg[$key] ?? $default;
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function app_url(string $path = ''): string
{
    $base = config('url');
    if ($base === '') {
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $base = $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
    }
    return $base . $path;
}

function redirect(string $path, int $status = 302): never
{
    header('Location: ' . $path, true, $status);
    exit;
}

// ---------- CSRF ----------
function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): bool
{
    $token = $_POST['_csrf'] ?? '';
    return is_string($token) && $token !== '' && hash_equals($_SESSION['_csrf'] ?? '', $token);
}

// ---------- Flash ----------
function flash(string $type, string $message): void
{
    $_SESSION['_flash'][] = ['type' => $type, 'message' => $message];
}

function pull_flash(): array
{
    $f = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return $f;
}

// ---------- Auth & Roles ----------
function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function is_logged_in(): bool
{
    return current_user() !== null;
}

function is_superadmin(): bool
{
    return (current_user()['role'] ?? '') === 'superadmin';
}

function is_moderator(): bool
{
    $r = current_user()['role'] ?? '';
    return $r === 'superadmin' || $r === 'moderator';
}

function can_moderate(): bool
{
    return is_moderator();
}

function is_premium(): bool
{
    $r = current_user()['role'] ?? '';
    return in_array($r, ['superadmin', 'moderator', 'premium'], true);
}

function user_role_label(?string $role): string
{
    return match ($role) {
        'superadmin' => 'Super Admin',
        'moderator'  => 'Moderator',
        'premium'    => 'User Premium',
        'basic'      => 'User Basic',
        default      => 'Tamu (Unregistered)',
    };
}

function app_setting(string $key, mixed $default = null): mixed
{
    static $settings = null;
    if ($settings === null) {
        $settings = [];
        try {
            $pdo = \App\Repositories\Database::pdo();
            $rows = $pdo->query('SELECT setting_key, setting_value FROM app_settings')->fetchAll();
            foreach ($rows as $r) {
                $settings[$r['setting_key']] = $r['setting_value'];
            }
        } catch (\Throwable) {
            // fallback
        }
    }
    return $settings[$key] ?? $default;
}

// ---------- Formatting ----------
function format_bytes(int|float $bytes): string
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = 0;
    while ($bytes >= 1024 && $i < count($units) - 1) {
        $bytes /= 1024;
        $i++;
    }
    return ($i === 0 ? (string) (int) $bytes : number_format($bytes, 1)) . ' ' . $units[$i];
}

function format_bitrate(?int $bps): string
{
    if (!$bps) {
        return '';
    }
    return $bps >= 1_000_000
        ? rtrim(rtrim(number_format($bps / 1_000_000, 1), '0'), '.') . ' Mbps'
        : (int) round($bps / 1000) . ' kbps';
}

function format_duration(?float $sec): string
{
    if (!$sec) {
        return '';
    }
    $s = (int) round($sec);
    return sprintf('%d:%02d', intdiv($s, 60), $s % 60);
}

// ---------- Logging ----------
function app_log(string $channel, string $message): void
{
    $line = sprintf("[%s] %s\n", date('Y-m-d H:i:s'), $message);
    @file_put_contents(config('log_path') . "/{$channel}.log", $line, FILE_APPEND | LOCK_EX);
}

// ---------- Views ----------
function render(string $view, array $data = [], string $title = ''): void
{
    $pageTitle = $title;
    $flashes = pull_flash();
    extract($data, EXTR_SKIP);
    ob_start();
    require BASE_PATH . '/views/' . $view . '.php';
    $content = ob_get_clean();
    require BASE_PATH . '/views/layouts/main.php';
}

function abort_page(int $status, string $title, string $message): never
{
    http_response_code($status);
    render('error', ['code' => $status, 'message' => $message], $title);
    exit;
}

function client_ip(): string
{
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}
