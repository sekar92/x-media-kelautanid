<?php
declare(strict_types=1);

return [
    'name'             => (string) env('APP_NAME', 'Kelautan.ID X-Media Extractor'),
    'url'              => rtrim((string) env('APP_URL', ''), '/'),
    'debug'            => (bool) env('APP_DEBUG', false),
    'max_file_bytes'   => (int) env('MAX_FILE_SIZE_MB', 250) * 1024 * 1024,
    'max_storage_bytes'=> (int) ((float) env('MAX_STORAGE_GB', 10) * 1024 * 1024 * 1024),
    'public_archive'   => (bool) env('PUBLIC_ARCHIVE_GALLERY', true),
    'cache_ttl'        => (int) env('CACHE_TTL_SECONDS', 3600),
    'ytdlp_enabled'    => (bool) env('YTDLP_ENABLED', false),
    'ytdlp_path'       => (string) env('YTDLP_PATH', '/usr/local/bin/yt-dlp'),
    'user_agent'       => 'KelautanID-MediaExtractor/1.0 (+https://kelautan.id)',
    // Whitelist host untuk proxy download & penyimpanan (anti-SSRF)
    'allowed_media_hosts' => [
        'video.twimg.com',
        'video-s.twimg.com',
        'pbs.twimg.com',
        'ton.twimg.com',
    ],
    'media_path'       => BASE_PATH . '/storage/media',
    'tmp_path'         => BASE_PATH . '/storage/tmp',
    'log_path'         => BASE_PATH . '/storage/logs',
];
