<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\MediaRepository;

final class HomeController
{
    public function __construct(private MediaRepository $repo)
    {
    }

    public function index(): void
    {
        $recent = [];
        try {
            $recent = $this->repo->recentTweets(6);
        } catch (\Throwable $e) {
            app_log('error', 'recentTweets: ' . $e->getMessage());
        }
        render('home', [
            'recent' => $recent,
            'old'    => $_SESSION['_old_url'] ?? '',
        ], 'Beranda');
        unset($_SESSION['_old_url']);
    }
}
