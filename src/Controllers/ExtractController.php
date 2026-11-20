<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\MediaRepository;
use App\Services\ExtractionException;
use App\Services\MediaQualityResolver;
use App\Services\TweetUrlParser;
use App\Services\TwitterExtractor;

final class ExtractController
{
    public function __construct(private MediaRepository $repo)
    {
    }

    public function extract(): void
    {
        if (!verify_csrf()) {
            flash('error', 'Sesi kedaluwarsa. Silakan coba lagi.');
            redirect('/');
        }
        $input = trim((string) ($_POST['url'] ?? ''));
        $_SESSION['_old_url'] = mb_substr($input, 0, 500);

        $user = current_user();
        $ip = client_ip();
        $sessId = session_id();

        // Pengecekan Batas Ekstraksi Unregistered User (Item 5: max 5x)
        if (!$user) {
            $guestCount = $this->repo->guestExtractionCount($sessId, $ip);
            $maxGuest = (int) app_setting('guest_max_extractions', 5);
            if ($guestCount >= $maxGuest) {
                flash('error', 'Batas maksimal ' . $maxGuest . 'x ekstraksi untuk Pengguna Tamu telah tercapai. Silakan Daftar Akun Gratis (User Basic) untuk menikmati akses penuh!');
                redirect('/register');
            }
        }

        try {
            if ($input === '' || mb_strlen($input) > 500) {
                throw new ExtractionException('Tempelkan URL unggahan X/Twitter terlebih dahulu.', true);
            }
            $tweetId = (new TweetUrlParser())->parse($input);

            $cached = $this->repo->findTweet($tweetId);
            $userId = $user ? (int) $user['id'] : null;

            if (!$cached || !$this->repo->isFresh($cached, (int) config('cache_ttl'))) {
                $data = (new TwitterExtractor(new MediaQualityResolver()))->extract($tweetId);
                $this->repo->saveExtraction($data, $userId, $ip);
            }

            if (!$user) {
                $this->repo->recordGuestExtraction($sessId, $ip, $tweetId);
            }

            // Jalankan pembersihan retensi berkas kedaluwarsa (Item 3 & 4)
            try {
                $this->repo->purgeExpired();
            } catch (\Throwable $e) {
                app_log('purge', 'Purge expired error: ' . $e->getMessage());
            }

            unset($_SESSION['_old_url']);
            redirect('/result/' . $tweetId);
        } catch (ExtractionException $e) {
            flash('error', $e->getMessage());
        } catch (\PDOException $e) {
            app_log('error', 'DB: ' . $e->getMessage());
            flash('error', 'Basis data tidak tersedia. Hubungi administrator.');
        } catch (\Throwable $e) {
            app_log('error', get_class($e) . ': ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
            flash('error', config('debug') ? $e->getMessage() : 'Terjadi galat tak terduga saat memproses tautan.');
        }
        redirect('/');
    }

    public function result(array $p): void
    {
        $tweet = $this->repo->findTweet($p['tweet_id']);
        if (!$tweet) {
            abort_page(404, 'Tidak Ditemukan', 'Hasil ekstraksi belum ada. Tempelkan tautan unggahan pada halaman beranda.');
        }
        $media = $this->repo->mediaForTweet((int) $tweet['id']);
        render('result', ['tweet' => $tweet, 'media' => $media], 'Hasil Ekstraksi @' . ($tweet['author_handle'] ?? ''));
    }

    /** Menu Hapus Riwayat Ekstraksi oleh Super Admin atau Moderator (Item 1) */
    public function delete(array $p): void
    {
        if (!can_moderate()) {
            flash('error', 'Akses ditolak. Hanya Super Admin atau Moderator yang berwenang.');
            redirect('/');
        }
        if (!verify_csrf()) {
            flash('error', 'Sesi kedaluwarsa. Silakan coba lagi.');
            redirect('/');
        }

        $tweetId = $p['tweet_id'];
        $ok = $this->repo->deleteTweet($tweetId);
        app_log('audit', "Tweet {$tweetId} dihapus oleh " . (current_user()['username'] ?? 'unknown'));
        flash($ok ? 'success' : 'error', $ok ? 'Riwayat ekstraksi dan berkas terkait berhasil dihapus.' : 'Riwayat tidak ditemukan.');

        $ret = (string) ($_POST['return'] ?? '/');
        redirect($ret !== '' && $ret[0] === '/' ? $ret : '/');
    }
}
