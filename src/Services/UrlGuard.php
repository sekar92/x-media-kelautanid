<?php
declare(strict_types=1);

namespace App\Services;

/** Perlindungan SSRF: hanya HTTPS ke host whitelist dengan IP publik. */
final class UrlGuard
{
    public static function assertAllowed(string $url): void
    {
        $parts = parse_url($url);
        if (!$parts || ($parts['scheme'] ?? '') !== 'https' || empty($parts['host'])) {
            throw new \RuntimeException('URL media tidak valid atau bukan HTTPS.');
        }
        if (isset($parts['user']) || isset($parts['pass'])) {
            throw new \RuntimeException('URL media tidak diizinkan.');
        }
        if (isset($parts['port']) && (int) $parts['port'] !== 443) {
            throw new \RuntimeException('Port URL media tidak diizinkan.');
        }
        $host = strtolower($parts['host']);
        if (!in_array($host, config('allowed_media_hosts'), true)) {
            throw new \RuntimeException('Host media tidak ada di whitelist.');
        }
        $ips = gethostbynamel($host);
        if (!$ips) {
            throw new \RuntimeException('Host media tidak dapat di-resolve.');
        }
        foreach ($ips as $ip) {
            if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                throw new \RuntimeException('Alamat IP privat/loopback ditolak.');
            }
        }
    }

    public static function isAllowed(string $url): bool
    {
        try {
            self::assertAllowed($url);
            return true;
        } catch (\RuntimeException) {
            return false;
        }
    }
}
