<?php
declare(strict_types=1);

require dirname(__DIR__) . '/src/bootstrap.php';

use App\Controllers\{ArchiveController, AuthController, DashboardController, ExtractController, HomeController, MediaController};
use App\Repositories\MediaRepository;

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path = rawurldecode((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));

// Dev server (php -S): biarkan file statis dilayani langsung
if (PHP_SAPI === 'cli-server') {
    $file = __DIR__ . $path;
    if ($path !== '/' && is_file($file)) {
        return false;
    }
}

$path = '/' . trim($path, '/');

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');

$routes = [
    // Halaman Utama & Ekstraksi
    ['GET',  '#^/$#',                              HomeController::class,    'index'],
    ['POST', '#^/extract$#',                       ExtractController::class, 'extract'],
    ['GET',  '#^/result/(?<tweet_id>\d{1,32})$#',  ExtractController::class, 'result'],
    ['POST', '#^/extract/delete/(?<tweet_id>\d{1,32})$#', ExtractController::class, 'delete'], // Menu delete Super Admin & Moderator

    // Media & Unduhan
    ['GET',  '#^/download/(?<id>\d+)$#',           MediaController::class,   'download'],
    ['GET',  '#^/stream/(?<id>\d+)$#',             MediaController::class,   'stream'],
    ['POST', '#^/archive/save$#',                  ArchiveController::class, 'save'],
    ['GET',  '#^/archive$#',                       ArchiveController::class, 'index'],
    ['POST', '#^/archive/delete/(?<uuid>[0-9a-f-]{36})$#', ArchiveController::class, 'delete'],
    ['GET',  '#^/m/(?<uuid>[0-9a-f-]{36})$#',      MediaController::class,   'serve'],

    // Autentikasi Umum (Login & Register)
    ['GET',  '#^/login$#',                         AuthController::class,    'showLogin'],
    ['POST', '#^/login$#',                         AuthController::class,    'login'],
    ['GET',  '#^/register$#',                      AuthController::class,    'showRegister'],
    ['POST', '#^/register$#',                      AuthController::class,    'register'],
    ['GET',  '#^/logout$#',                        AuthController::class,    'logout'],
    ['POST', '#^/logout$#',                        AuthController::class,    'logout'],

    // Dashboard Pengguna & Upgrade WA
    ['GET',  '#^/dashboard$#',                     DashboardController::class, 'index'],
    ['GET',  '#^/upgrade$#',                       DashboardController::class, 'upgrade'],

    // Moderasi (Super Admin & Moderator)
    ['GET',  '#^/admin/moderation$#',              DashboardController::class, 'moderation'],
    ['POST', '#^/admin/flag/(?<tweet_id>\d{1,32})$#', DashboardController::class, 'flagTweet'],
    ['POST', '#^/admin/unflag/(?<tweet_id>\d{1,32})$#', DashboardController::class, 'unflagTweet'],

    // Pengaturan Aplikasi (Super Admin)
    ['GET',  '#^/admin/settings$#',                DashboardController::class, 'showSettings'],
    ['POST', '#^/admin/settings$#',                DashboardController::class, 'saveSettings'],

    // Manajemen Pengguna (Super Admin)
    ['GET',  '#^/admin/users$#',                   DashboardController::class, 'users'],
    ['POST', '#^/admin/users/create$#',            DashboardController::class, 'createUserAction'],
    ['POST', '#^/admin/users/(?<id>\d+)/edit$#',   DashboardController::class, 'editUserAction'],
    ['POST', '#^/admin/users/(?<id>\d+)/delete$#', DashboardController::class, 'deleteUserAction'],
    ['POST', '#^/admin/users/(?<id>\d+)/role$#',   DashboardController::class, 'updateUserRole'],

    // Kelola Halaman Regulasi (Super Admin & Moderator)
    ['GET',  '#^/admin/pages$#',                   DashboardController::class, 'showPages'],
    ['GET',  '#^/admin/pages/(?<slug>[a-z0-9_-]+)$#', DashboardController::class, 'showEditPage'],
    ['POST', '#^/admin/pages/(?<slug>[a-z0-9_-]+)$#', DashboardController::class, 'saveEditPage'],

    // Halaman Statis Publik (ToS, Rules, Community Guidelines)
    ['GET',  '#^/terms$#',                         DashboardController::class, 'viewPublicPage', ['slug' => 'terms']],
    ['GET',  '#^/rules$#',                         DashboardController::class, 'viewPublicPage', ['slug' => 'rules']],
    ['GET',  '#^/guidelines$#',                    DashboardController::class, 'viewPublicPage', ['slug' => 'guidelines']],
    ['GET',  '#^/p/(?<slug>[a-z0-9_-]+)$#',        DashboardController::class, 'viewPublicPage'],
];

try {
    $effective = $method === 'HEAD' ? 'GET' : $method;
    foreach ($routes as $routeDef) {
        $m = $routeDef[0];
        $regex = $routeDef[1];
        $class = $routeDef[2];
        $action = $routeDef[3];
        $fixedParams = $routeDef[4] ?? [];

        if ($m === $effective && preg_match($regex, $path, $matches)) {
            $params = array_merge(array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY), $fixedParams);
            (new $class(new MediaRepository()))->$action($params);
            exit;
        }
    }
    abort_page(404, 'Halaman Tidak Ditemukan', 'Halaman yang Anda cari tidak ada.');
} catch (\PDOException $e) {
    app_log('error', 'PDO: ' . $e->getMessage());
    abort_page(500, 'Basis Data Bermasalah', config('debug') ? $e->getMessage() : 'Basis data tidak dapat diakses. Hubungi administrator.');
} catch (\Throwable $e) {
    app_log('error', get_class($e) . ': ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
    abort_page(500, 'Galat Server', config('debug') ? $e->getMessage() : 'Terjadi galat tak terduga. Silakan coba lagi nanti.');
}
