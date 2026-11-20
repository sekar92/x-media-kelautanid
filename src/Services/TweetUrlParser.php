<?php
declare(strict_types=1);

namespace App\Services;

final class TweetUrlParser
{
    private const PATTERN = '#^https?://(?:www\.|mobile\.)?(?:twitter\.com|x\.com)/(?:i/web|[A-Za-z0-9_]+)/status/(\d+)#i';
    private const REDIRECT_HOSTS = ['t.co', 'twitter.com', 'www.twitter.com', 'mobile.twitter.com', 'x.com', 'www.x.com'];

    /** @return string tweet_id numerik */
    public function parse(string $input): string
    {
        $input = trim($input);
        // Ambil URL pertama jika pengguna menempelkan teks tambahan
        if (preg_match('#https?://\S+#i', $input, $m)) {
            $input = rtrim($m[0], ".,;)\"'");
        }
        if ($input === '' || !preg_match('#^https?://#i', $input)) {
            throw new ExtractionException('Tautan tidak valid. Tempelkan URL unggahan X/Twitter, contoh: https://x.com/username/status/123456789', true);
        }

        $host = strtolower((string) parse_url($input, PHP_URL_HOST));
        if ($host === 't.co') {
            $input = $this->resolveShortLink($input);
        }

        if (preg_match(self::PATTERN, $input, $m)) {
            return $m[1];
        }
        throw new ExtractionException('Tautan bukan unggahan X/Twitter yang valid (harus berformat .../status/{id}).', true);
    }

    /** Permintaan HEAD cURL dengan CURLOPT_FOLLOWLOCATION = true (maksimal 3 redirect, timeout 5 detik) */
    private function resolveShortLink(string $url): string
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_NOBODY         => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 3,
            CURLOPT_TIMEOUT        => 5,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_USERAGENT      => config('user_agent'),
            CURLOPT_PROTOCOLS      => CURLPROTO_HTTP | CURLPROTO_HTTPS,
        ]);
        curl_exec($ch);
        $effective = (string) curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch);

        if ($effective !== '' && preg_match(self::PATTERN, $effective)) {
            return $effective;
        }
        throw new ExtractionException('Tautan pendek t.co tidak mengarah ke unggahan X/Twitter yang valid.', true);
    }
}
