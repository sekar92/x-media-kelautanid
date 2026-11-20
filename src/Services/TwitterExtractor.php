<?php
declare(strict_types=1);

namespace App\Services;

/** Multi-tier extractor: FxTwitter → VxTwitter → yt-dlp (opsional). */
final class TwitterExtractor
{
    public function __construct(private MediaQualityResolver $resolver)
    {
    }

    /**
     * @return array{tweet_id:string,original_url:string,author_name:?string,author_handle:?string,tweet_text:?string,engine:string,meta:array,media:array}
     */
    public function extract(string $tweetId): array
    {
        $tiers = [
            'fxtwitter' => fn() => $this->fxTwitter($tweetId),
            'vxtwitter' => fn() => $this->vxTwitter($tweetId),
        ];
        if (config('ytdlp_enabled')) {
            $tiers['yt-dlp'] = fn() => $this->ytDlp($tweetId);
        }

        $last = null;
        foreach ($tiers as $name => $run) {
            try {
                return $run();
            } catch (ExtractionException $e) {
                if ($e->isFinal()) {
                    throw $e;
                }
                $last = $e;
                app_log('extract', "tier {$name} gagal untuk {$tweetId}: " . $e->getMessage());
            } catch (\Throwable $e) {
                $last = new ExtractionException('Terjadi galat saat memproses unggahan.');
                app_log('extract', "tier {$name} error untuk {$tweetId}: " . $e->getMessage());
            }
        }
        throw $last ?? new ExtractionException('Layanan ekstraksi tidak tersedia saat ini. Coba lagi nanti.');
    }

    // ---------- Tier 1: FxTwitter ----------
    private function fxTwitter(string $id): array
    {
        [$status, $body] = $this->httpGet("https://api.fxtwitter.com/i/status/{$id}");
        $json = json_decode($body, true);
        $code = is_array($json) ? (int) ($json['code'] ?? $status) : $status;

        $this->assertCode($code);
        $tweet = $json['tweet'] ?? null;
        if (!is_array($tweet)) {
            throw new ExtractionException('Respons layanan ekstraksi tidak valid.');
        }

        $mediaSrc = $this->fxMediaList($tweet['media'] ?? []);
        $fromQuote = false;
        $quoteHandle = null;
        if (!$mediaSrc && !empty($tweet['quote']['media'])) {
            $mediaSrc = $this->fxMediaList($tweet['quote']['media']);
            $fromQuote = (bool) $mediaSrc;
            $quoteHandle = $tweet['quote']['author']['screen_name'] ?? null;
        }

        $media = [];
        foreach ($mediaSrc as $item) {
            $m = match ($item['type'] ?? '') {
                'photo' => $this->resolver->resolvePhoto((string) ($item['url'] ?? ''), $this->int($item['width'] ?? null), $this->int($item['height'] ?? null)),
                'video', 'gif' => $this->resolver->resolveVideo(
                    (string) $item['type'],
                    $item['url'] ?? null,
                    (array) ($item['variants'] ?? []),
                    $item['thumbnail_url'] ?? null,
                    $this->int($item['width'] ?? null),
                    $this->int($item['height'] ?? null),
                    isset($item['duration']) ? (float) $item['duration'] : null
                ),
                default => null,
            };
            if ($m) {
                $media[] = $m;
            }
        }
        $this->assertHasMedia($media, $mediaSrc !== []);

        return [
            'tweet_id'      => $id,
            'original_url'  => $tweet['url'] ?? "https://x.com/i/status/{$id}",
            'author_name'   => $tweet['author']['name'] ?? null,
            'author_handle' => $tweet['author']['screen_name'] ?? null,
            'tweet_text'    => $tweet['text'] ?? null,
            'engine'        => 'fxtwitter',
            'meta'          => [
                'from_quote'   => $fromQuote,
                'quote_author' => $quoteHandle,
                'created_at'   => $tweet['created_at'] ?? null,
            ],
            'media'         => $media,
        ];
    }

    private function fxMediaList(array $media): array
    {
        if (!empty($media['all']) && is_array($media['all'])) {
            return array_values($media['all']);
        }
        $out = [];
        foreach ((array) ($media['videos'] ?? []) as $v) {
            $out[] = $v + ['type' => $v['type'] ?? 'video'];
        }
        foreach ((array) ($media['photos'] ?? []) as $p) {
            $out[] = $p + ['type' => 'photo'];
        }
        return $out;
    }

    // ---------- Tier 2: VxTwitter ----------
    private function vxTwitter(string $id): array
    {
        [$status, $body] = $this->httpGet("https://api.vxtwitter.com/Twitter/status/{$id}");
        $json = json_decode($body, true);
        if ($status === 404) {
            throw new ExtractionException('Unggahan tidak ditemukan atau sudah dihapus.', true);
        }
        if (!is_array($json) || empty($json['tweetID'])) {
            throw new ExtractionException('Respons layanan cadangan tidak valid.');
        }

        $media = [];
        foreach ((array) ($json['media_extended'] ?? []) as $item) {
            $type = $item['type'] ?? '';
            $w = $this->int($item['size']['width'] ?? null);
            $h = $this->int($item['size']['height'] ?? null);
            $url = (string) ($item['url'] ?? '');
            if ($type === 'image') {
                $m = $this->resolver->resolvePhoto($url, $w, $h);
            } elseif ($type === 'video' || $type === 'gif') {
                $dur = isset($item['duration_millis']) ? round($item['duration_millis'] / 1000, 2) : null;
                $m = $this->resolver->resolveVideo($type, $url, [], $item['thumbnail_url'] ?? null, $w, $h, $dur);
            } else {
                $m = null;
            }
            if ($m) {
                $media[] = $m;
            }
        }
        $this->assertHasMedia($media, !empty($json['media_extended']));

        return [
            'tweet_id'      => $id,
            'original_url'  => $json['tweetURL'] ?? "https://x.com/i/status/{$id}",
            'author_name'   => $json['user_name'] ?? null,
            'author_handle' => $json['user_screen_name'] ?? null,
            'tweet_text'    => $json['text'] ?? null,
            'engine'        => 'vxtwitter',
            'meta'          => ['from_quote' => false, 'created_at' => $json['date'] ?? null],
            'media'         => $media,
        ];
    }

    // ---------- Tier 3: yt-dlp ----------
    private function ytDlp(string $id): array
    {
        $bin = (string) config('ytdlp_path');
        if (!is_executable($bin)) {
            throw new ExtractionException('yt-dlp tidak tersedia di server.');
        }
        $cmd = escapeshellarg($bin) . ' --dump-json --no-warnings --no-playlist ' . escapeshellarg("https://x.com/i/status/{$id}");
        $proc = proc_open($cmd, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
        if (!is_resource($proc)) {
            throw new ExtractionException('Gagal menjalankan yt-dlp.');
        }
        stream_set_blocking($pipes[1], false);
        $out = '';
        $start = time();
        while (true) {
            $chunk = fread($pipes[1], 65536);
            if ($chunk !== false && $chunk !== '') {
                $out .= $chunk;
            }
            $st = proc_get_status($proc);
            if (!$st['running']) {
                $out .= (string) stream_get_contents($pipes[1]);
                break;
            }
            if (time() - $start > 45) {
                proc_terminate($proc);
                throw new ExtractionException('yt-dlp melewati batas waktu.');
            }
            usleep(100000);
        }
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($proc);

        $media = [];
        $author = null; $handle = null; $text = null;
        foreach (preg_split('/\R/', trim($out)) ?: [] as $line) {
            $info = json_decode($line, true);
            if (!is_array($info)) {
                continue;
            }
            $author ??= $info['uploader'] ?? null;
            $handle ??= $info['uploader_id'] ?? null;
            $text ??= $info['description'] ?? $info['title'] ?? null;
            $variants = [];
            foreach ((array) ($info['formats'] ?? []) as $f) {
                if (($f['ext'] ?? '') === 'mp4' && in_array($f['protocol'] ?? '', ['https', 'http'], true)) {
                    $variants[] = ['url' => $f['url'], 'content_type' => 'video/mp4', 'bitrate' => isset($f['tbr']) ? (int) ($f['tbr'] * 1000) : null];
                }
            }
            $m = $this->resolver->resolveVideo('video', null, $variants, $info['thumbnail'] ?? null, $this->int($info['width'] ?? null), $this->int($info['height'] ?? null), isset($info['duration']) ? (float) $info['duration'] : null);
            if ($m) {
                $media[] = $m;
            }
        }
        $this->assertHasMedia($media, false);

        return [
            'tweet_id' => $id, 'original_url' => "https://x.com/i/status/{$id}",
            'author_name' => $author, 'author_handle' => $handle, 'tweet_text' => $text,
            'engine' => 'yt-dlp', 'meta' => ['from_quote' => false], 'media' => $media,
        ];
    }

    // ---------- Util ----------
    private function assertCode(int $code): void
    {
        match (true) {
            $code === 200 => null,
            $code === 401, $code === 403 => throw new ExtractionException('Unggahan berasal dari akun privat atau dibatasi, sehingga media tidak dapat diambil.', true),
            $code === 404 => throw new ExtractionException('Unggahan tidak ditemukan atau sudah dihapus.', true),
            default => throw new ExtractionException('Layanan ekstraksi sedang bermasalah (kode ' . $code . ').'),
        };
    }

    private function assertHasMedia(array $media, bool $hadSource): void
    {
        if (!$media) {
            throw new ExtractionException(
                $hadSource
                    ? 'Media pada unggahan ini tidak menyediakan tautan MP4 langsung.'
                    : 'Unggahan ini tidak memuat video, GIF, atau gambar.',
                !$hadSource
            );
        }
    }

    private function int(mixed $v): ?int
    {
        return is_numeric($v) && (int) $v > 0 ? (int) $v : null;
    }

    /** @return array{0:int,1:string} */
    private function httpGet(string $url): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_CONNECTTIMEOUT => 6,
            CURLOPT_USERAGENT      => config('user_agent'),
            CURLOPT_HTTPHEADER     => ['Accept: application/json'],
            CURLOPT_PROTOCOLS      => CURLPROTO_HTTPS,
        ]);
        $body = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if ($body === false) {
            throw new ExtractionException('Tidak dapat menghubungi layanan ekstraksi (' . ($err ?: 'timeout') . ').');
        }
        if ($status >= 500) {
            throw new ExtractionException('Layanan ekstraksi sedang bermasalah (HTTP ' . $status . ').');
        }
        return [$status, (string) $body];
    }
}
