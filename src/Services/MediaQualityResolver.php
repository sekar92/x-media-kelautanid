<?php
declare(strict_types=1);

namespace App\Services;

/**
 * Menentukan URL kualitas tertinggi untuk foto (name=orig) dan video/GIF (MP4 bitrate tertinggi).
 *
 * Bentuk media ternormalisasi:
 *  type(video|gif|photo), best_url, thumbnail, mime, width, height, duration, bitrate,
 *  variants[] = {url, bitrate, width, height, label, ext}   (index 0 = terbaik)
 */
final class MediaQualityResolver
{
    private const PHOTO_EXT = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    public function __construct(private bool $validatePhotos = true)
    {
    }

    // ---------- FOTO ----------
    public function resolvePhoto(string $url, ?int $w = null, ?int $h = null): array
    {
        $parts = parse_url($url);
        $path = $parts['path'] ?? '';
        parse_str($parts['query'] ?? '', $query);

        $base = basename($path);
        $ext = strtolower(pathinfo($base, PATHINFO_EXTENSION));
        $mediaId = $ext !== '' ? substr($base, 0, -(strlen($ext) + 1)) : $base;
        if (!in_array($ext, self::PHOTO_EXT, true)) {
            $ext = strtolower((string) ($query['format'] ?? 'jpg'));
            if (!in_array($ext, self::PHOTO_EXT, true)) {
                $ext = 'jpg';
            }
        }
        $dir = rtrim(dirname($path), '/');
        $host = $parts['host'] ?? 'pbs.twimg.com';
        $root = "https://{$host}{$dir}/{$mediaId}";

        // format= WAJIB sama persis dengan ekstensi asli agar tidak 404
        $candidates = [];
        foreach (['orig' => 'Original', '4096x4096' => '4096px', 'large' => 'Large'] as $name => $label) {
            $candidates[] = [
                'url'     => "{$root}?format={$ext}&name={$name}",
                'bitrate' => null,
                'width'   => $name === 'orig' ? $w : null,
                'height'  => $name === 'orig' ? $h : null,
                'label'   => $label,
                'ext'     => $ext === 'jpeg' ? 'jpg' : $ext,
            ];
        }

        // Validasi HEAD: jika orig ditolak (403/404), promosikan fallback yang valid
        if ($this->validatePhotos) {
            foreach ($candidates as $i => $cand) {
                $status = $this->headStatus($cand['url']);
                if ($status === 0 || ($status >= 200 && $status < 400)) {
                    if ($i > 0) {
                        $picked = array_splice($candidates, $i, 1);
                        array_unshift($candidates, $picked[0]);
                    }
                    break;
                }
            }
        }

        $mime = match ($candidates[0]['ext']) {
            'png'  => 'image/png',
            'webp' => 'image/webp',
            'gif'  => 'image/gif',
            default => 'image/jpeg',
        };

        return [
            'type'      => 'photo',
            'best_url'  => $candidates[0]['url'],
            'thumbnail' => "{$root}?format={$ext}&name=small",
            'mime'      => $mime,
            'width'     => $w,
            'height'    => $h,
            'duration'  => null,
            'bitrate'   => null,
            'variants'  => $candidates,
        ];
    }

    private function headStatus(string $url): int
    {
        if (!UrlGuard::isAllowed($url)) {
            return 0;
        }
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_NOBODY         => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_TIMEOUT        => 5,
            CURLOPT_CONNECTTIMEOUT => 4,
            CURLOPT_USERAGENT      => config('user_agent'),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_PROTOCOLS      => CURLPROTO_HTTPS,
        ]);
        curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);
        return $code; // 0 = galat jaringan (tidak dianggap penolakan)
    }

    // ---------- VIDEO / GIF ----------
    /**
     * @param array<int,array{url?:string,content_type?:string,bitrate?:int|null}> $rawVariants
     */
    public function resolveVideo(
        string $type,
        ?string $mainUrl,
        array $rawVariants,
        ?string $thumb,
        ?int $w,
        ?int $h,
        ?float $duration
    ): ?array {
        $seen = [];
        $mp4 = [];

        $consider = function (string $url, ?int $bitrate) use (&$seen, &$mp4): void {
            if (isset($seen[$url])) {
                return;
            }
            $seen[$url] = true;
            $mp4[] = ['url' => $url, 'bitrate' => $bitrate ?: 0];
        };

        foreach ($rawVariants as $v) {
            $url = (string) ($v['url'] ?? '');
            $ct = strtolower((string) ($v['content_type'] ?? ''));
            if ($url === '') {
                continue;
            }
            $isMp4 = $ct === 'video/mp4' || ($ct === '' && str_ends_with(strtolower((string) parse_url($url, PHP_URL_PATH)), '.mp4'));
            if ($isMp4) {
                $consider($url, isset($v['bitrate']) ? (int) $v['bitrate'] : null);
            }
        }
        if ($mainUrl && str_ends_with(strtolower((string) parse_url($mainUrl, PHP_URL_PATH)), '.mp4')) {
            $consider($mainUrl, null);
        }
        if (!$mp4) {
            return null; // tidak ada tautan MP4 langsung (mis. hanya HLS)
        }

        // Hanya host video yang valid (bukan thumbnail pbs.twimg.com)
        $mp4 = array_values(array_filter($mp4, static function (array $x): bool {
            $host = strtolower((string) parse_url($x['url'], PHP_URL_HOST));
            return in_array($host, ['video.twimg.com', 'video-s.twimg.com'], true);
        }));
        if (!$mp4) {
            return null;
        }

        foreach ($mp4 as &$x) {
            [$x['width'], $x['height']] = $this->dimsFromUrl($x['url']);
            $x['pixels'] = ($x['width'] ?? 0) * ($x['height'] ?? 0);
        }
        unset($x);

        usort($mp4, static fn($a, $b) => [$b['bitrate'], $b['pixels']] <=> [$a['bitrate'], $a['pixels']]);

        $variants = [];
        foreach ($mp4 as $i => $x) {
            if ($i === 0 && !$x['width'] && $w && $h) {
                $x['width'] = $w;
                $x['height'] = $h;
            }
            $variants[] = [
                'url'     => $x['url'],
                'bitrate' => $x['bitrate'] ?: null,
                'width'   => $x['width'],
                'height'  => $x['height'],
                'label'   => $this->variantLabel($x['width'], $x['height'], $x['bitrate']),
                'ext'     => 'mp4',
            ];
        }

        $best = $variants[0];
        return [
            'type'      => $type === 'gif' ? 'gif' : 'video',
            'best_url'  => $best['url'],
            'thumbnail' => $thumb,
            'mime'      => 'video/mp4',
            'width'     => $best['width'] ?? $w,
            'height'    => $best['height'] ?? $h,
            'duration'  => $duration,
            'bitrate'   => $best['bitrate'],
            'variants'  => $variants,
        ];
    }

    /** @return array{0:?int,1:?int} */
    private function dimsFromUrl(string $url): array
    {
        if (preg_match('#/(\d{2,5})x(\d{2,5})/#', $url, $m)) {
            return [(int) $m[1], (int) $m[2]];
        }
        return [null, null];
    }

    private function variantLabel(?int $w, ?int $h, int $bitrate): string
    {
        $parts = [];
        if ($w && $h) {
            $parts[] = min($w, $h) . 'p';
        }
        $br = format_bitrate($bitrate ?: null);
        if ($br !== '') {
            $parts[] = $br;
        }
        return $parts ? implode(' • ', $parts) : 'MP4';
    }
}
