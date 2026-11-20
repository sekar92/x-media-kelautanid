<?php /** @var string $content */ /** @var string $pageTitle */ /** @var array $flashes */
$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$user = current_user();
$role = $user['role'] ?? null;
?><!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="referrer" content="no-referrer">
    <title><?= e(($pageTitle ? $pageTitle . ' — ' : '') . config('name')) ?></title>
    <meta name="description" content="Ekstrak video, GIF, dan gambar kualitas tertinggi dari unggahan X/Twitter. Bagian dari ekosistem Kelautan.ID.">
    <link rel="icon" type="image/svg+xml" href="/assets/img/logo-kelautan.svg">

    <!-- Google Fonts: Outfit & Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS (Konfigurasi Resmi Kelautan.ID) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        abyssal: {
                            950: '#060D18',
                            900: '#0A192F',
                            800: '#0F172A',
                            700: '#1E293B',
                        },
                        maritime: {
                            900: '#1E3A8A',
                            800: '#1E40AF',
                            700: '#1D4ED8',
                            600: '#2563EB',
                            500: '#0284C7',
                        },
                        ocean: {
                            400: '#38BDF8',
                            500: '#0EA5E9',
                            DEFAULT: '#06B6D4',
                            cyan: '#22D3EE',
                        },
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'sans-serif'],
                        heading: ['"Outfit"', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <!-- Embedded Critical CSS (Anti FOUC & High Contrast Guarantee) -->
    <style>
        html, body {
            background-color: #F8FAFC;
            color: #0F172A;
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            -webkit-font-smoothing: antialiased;
        }
        h1, h2, h3, h4, h5, h6, .font-heading {
            font-family: 'Outfit', sans-serif;
            letter-spacing: -0.02em;
        }
        .bg-oceanic-gradient {
            background: linear-gradient(135deg, #0A192F 0%, #1E3A8A 50%, #060D18 100%) !important;
            color: #ffffff !important;
        }
        .btn-copy.is-done {
            background-color: #059669 !important;
            color: #ffffff !important;
            border-color: #059669 !important;
        }
        button.is-loading {
            opacity: 0.75;
            cursor: wait;
            pointer-events: none;
        }
        .media-preview-container {
            background: #020617;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        .media-preview-container video,
        .media-preview-container img {
            max-height: 480px;
            width: 100%;
            object-fit: contain;
        }
    </style>

    <!-- Ikon Lucide -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Stylesheet Eksternal -->
    <link rel="stylesheet" href="/assets/css/kelautan.css">
</head>
<body class="flex flex-col min-h-screen bg-slate-50 text-slate-800 antialiased selection:bg-cyan-500 selection:text-white">

    <!-- 1. TOP HEADER -->
    <header class="sticky top-0 z-40 bg-[#0A192F] border-b border-slate-800 text-white shadow-md" style="background-color: #0A192F;">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Sisi Kiri: Brand Logo & Title -->
                <div class="flex items-center space-x-3">
                    <button id="mobile-menu-btn" type="button" class="md:hidden p-2 rounded-xl text-slate-300 hover:text-white hover:bg-slate-800 focus:outline-none" aria-label="Buka Menu">
                        <i data-lucide="menu" class="w-5 h-5"></i>
                    </button>
                    <a href="/" class="flex items-center space-x-3 group">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-cyan-400 to-maritime-700 flex items-center justify-center text-white shadow-md shadow-cyan-500/20 group-hover:scale-105 transition-transform">
                            <i data-lucide="waves" class="w-6 h-6 text-white"></i>
                        </div>
                        <div class="flex flex-col">
                            <span class="font-bold text-white text-lg font-heading tracking-tight leading-none group-hover:text-cyan-300 transition-colors">
                                Kelautan<span class="text-cyan-400">.ID</span>
                            </span>
                            <span class="text-[11px] font-semibold tracking-wider text-cyan-300 uppercase mt-0.5">
                                X-Media Extractor
                            </span>
                        </div>
                    </a>
                </div>

                <!-- Tengah: Quick Nav Desktop -->
                <nav class="hidden lg:flex items-center space-x-1 text-xs font-semibold">
                    <a href="/" class="px-3 py-1.5 rounded-xl transition flex items-center space-x-1.5 <?= $uri === '/' ? 'bg-cyan-950 text-cyan-300 border border-cyan-700/50' : 'text-slate-300 hover:text-white hover:bg-slate-800/80' ?>">
                        <i data-lucide="compass" class="w-3.5 h-3.5"></i>
                        <span>Beranda</span>
                    </a>
                    <a href="/archive" class="px-3 py-1.5 rounded-xl transition flex items-center space-x-1.5 <?= $uri === '/archive' ? 'bg-cyan-950 text-cyan-300 border border-cyan-700/50' : 'text-slate-300 hover:text-white hover:bg-slate-800/80' ?>">
                        <i data-lucide="film" class="w-3.5 h-3.5"></i>
                        <span>Video Terunduh</span>
                    </a>
                    <?php if (can_moderate()): ?>
                        <a href="/admin/moderation" class="px-3 py-1.5 rounded-xl transition flex items-center space-x-1.5 <?= str_starts_with($uri, '/admin/moderation') ? 'bg-cyan-950 text-cyan-300 border border-cyan-700/50' : 'text-amber-300 hover:bg-slate-800/80' ?>">
                            <i data-lucide="shield-alert" class="w-3.5 h-3.5"></i>
                            <span>Moderasi</span>
                        </a>
                        <a href="/admin/pages" class="px-3 py-1.5 rounded-xl transition flex items-center space-x-1.5 <?= str_starts_with($uri, '/admin/pages') ? 'bg-cyan-950 text-cyan-300 border border-cyan-700/50' : 'text-slate-300 hover:bg-slate-800/80' ?>">
                            <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                            <span>Regulasi</span>
                        </a>
                    <?php endif; ?>
                    <?php if (is_superadmin()): ?>
                        <a href="/admin/users" class="px-3 py-1.5 rounded-xl transition flex items-center space-x-1.5 <?= str_starts_with($uri, '/admin/users') ? 'bg-cyan-950 text-cyan-300 border border-cyan-700/50' : 'text-slate-300 hover:bg-slate-800/80' ?>">
                            <i data-lucide="users" class="w-3.5 h-3.5"></i>
                            <span>Pengguna</span>
                        </a>
                        <a href="/admin/settings" class="px-3 py-1.5 rounded-xl transition flex items-center space-x-1.5 <?= str_starts_with($uri, '/admin/settings') ? 'bg-cyan-950 text-cyan-300 border border-cyan-700/50' : 'text-slate-300 hover:bg-slate-800/80' ?>">
                            <i data-lucide="settings" class="w-3.5 h-3.5"></i>
                            <span>Pengaturan</span>
                        </a>
                    <?php endif; ?>
                </nav>

                <!-- Sisi Kanan: Akun Pengguna & Aksi -->
                <div class="hidden md:flex items-center space-x-2">
                    <?php if ($user): ?>
                        <?php if ($role === 'basic'): ?>
                            <a href="/upgrade" class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-xl font-bold text-xs text-amber-950 bg-amber-400 hover:bg-amber-300 shadow-xs transition" title="Upgrade ke User Premium">
                                <i data-lucide="crown" class="w-3.5 h-3.5 text-amber-900"></i>
                                <span>Upgrade</span>
                            </a>
                        <?php endif; ?>

                        <a href="/dashboard" class="inline-flex items-center space-x-2 px-3 py-1.5 rounded-xl bg-slate-800/90 hover:bg-slate-700 border border-slate-700 text-xs font-semibold text-slate-200 transition">
                            <i data-lucide="user" class="w-3.5 h-3.5 text-cyan-400"></i>
                            <span>@<?= e($user['username']) ?></span>
                            <span class="px-1.5 py-0.5 rounded text-[10px] uppercase font-bold <?= $role === 'superadmin' ? 'bg-purple-900 text-purple-200' : ($role === 'moderator' ? 'bg-amber-900 text-amber-200' : ($role === 'premium' ? 'bg-cyan-900 text-cyan-200' : 'bg-slate-700 text-slate-300')) ?>">
                                <?= e(user_role_label($role)) ?>
                            </span>
                        </a>

                        <form method="post" action="/logout" class="inline m-0">
                            <?= csrf_field() ?>
                            <button type="submit" class="p-2 rounded-xl text-slate-400 hover:text-rose-400 hover:bg-rose-950/30 transition" title="Keluar">
                                <i data-lucide="log-out" class="w-4 h-4"></i>
                            </button>
                        </form>
                    <?php else: ?>
                        <a href="/login" class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-300 hover:text-white transition">
                            Masuk
                        </a>
                        <a href="/register" class="inline-flex items-center space-x-1.5 px-4 py-2 rounded-xl font-bold text-xs text-white bg-maritime-700 hover:bg-maritime-800 active:scale-95 shadow-md shadow-maritime-700/20 transition">
                            <i data-lucide="user-plus" class="w-3.5 h-3.5"></i>
                            <span>Daftar Gratis</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Mobile Menu Drawer -->
        <div id="mobile-menu-drawer" class="hidden md:hidden border-t border-slate-800 bg-[#0A192F] px-4 pt-3 pb-4 space-y-2" style="background-color: #0A192F;">
            <a href="/" class="flex items-center space-x-2.5 px-3 py-2.5 rounded-xl text-sm font-semibold <?= $uri === '/' ? 'bg-cyan-950 text-cyan-300 border border-cyan-700/40' : 'text-slate-300 hover:bg-slate-800' ?>">
                <i data-lucide="compass" class="w-4 h-4"></i>
                <span>Beranda Ekstraksi</span>
            </a>
            <a href="/archive" class="flex items-center space-x-2.5 px-3 py-2.5 rounded-xl text-sm font-semibold <?= $uri === '/archive' ? 'bg-cyan-950 text-cyan-300 border border-cyan-700/40' : 'text-slate-300 hover:bg-slate-800' ?>">
                <i data-lucide="film" class="w-4 h-4"></i>
                <span>Video Terunduh</span>
            </a>

            <?php if ($user): ?>
                <a href="/dashboard" class="flex items-center space-x-2.5 px-3 py-2.5 rounded-xl text-sm font-semibold text-cyan-300 bg-slate-800/80">
                    <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                    <span>Dashboard Saya (@<?= e($user['username']) ?>)</span>
                </a>
                <?php if ($role === 'basic'): ?>
                    <a href="/upgrade" class="flex items-center space-x-2.5 px-3 py-2.5 rounded-xl text-sm font-bold text-amber-950 bg-amber-400">
                        <i data-lucide="crown" class="w-4 h-4"></i>
                        <span>Upgrade ke User Premium</span>
                    </a>
                <?php endif; ?>
                <?php if (can_moderate()): ?>
                    <a href="/admin/moderation" class="flex items-center space-x-2.5 px-3 py-2.5 rounded-xl text-sm font-semibold text-amber-300 hover:bg-slate-800">
                        <i data-lucide="shield-alert" class="w-4 h-4"></i>
                        <span>Dashboard Moderasi</span>
                    </a>
                    <a href="/admin/pages" class="flex items-center space-x-2.5 px-3 py-2.5 rounded-xl text-sm font-semibold text-slate-300 hover:bg-slate-800">
                        <i data-lucide="file-text" class="w-4 h-4"></i>
                        <span>Kelola Regulasi (ToS/Rules)</span>
                    </a>
                <?php endif; ?>
                <?php if (is_superadmin()): ?>
                    <a href="/admin/users" class="flex items-center space-x-2.5 px-3 py-2.5 rounded-xl text-sm font-semibold text-slate-300 hover:bg-slate-800">
                        <i data-lucide="users" class="w-4 h-4"></i>
                        <span>Manajemen Pengguna</span>
                    </a>
                    <a href="/admin/settings" class="flex items-center space-x-2.5 px-3 py-2.5 rounded-xl text-sm font-semibold text-slate-300 hover:bg-slate-800">
                        <i data-lucide="settings" class="w-4 h-4"></i>
                        <span>Pengaturan Aplikasi</span>
                    </a>
                <?php endif; ?>
            <?php endif; ?>

            <div class="pt-2 border-t border-slate-800 space-y-2">
                <?php if ($user): ?>
                    <div class="flex items-center justify-between px-3 py-2 text-sm text-slate-300">
                        <span class="font-semibold text-cyan-300"><?= e(user_role_label($role)) ?></span>
                        <form method="post" action="/logout" class="m-0">
                            <?= csrf_field() ?>
                            <button type="submit" class="text-rose-400 font-semibold hover:underline flex items-center space-x-1">
                                <i data-lucide="log-out" class="w-4 h-4"></i>
                                <span>Logout</span>
                            </button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-2 gap-2">
                        <a href="/login" class="flex items-center justify-center space-x-1 py-2.5 rounded-xl text-xs font-bold text-slate-300 bg-slate-800 text-center">
                            <span>Masuk</span>
                        </a>
                        <a href="/register" class="flex items-center justify-center space-x-1 py-2.5 rounded-xl text-xs font-bold text-white bg-maritime-700 text-center">
                            <span>Daftar Gratis</span>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- 2. KONTEN UTAMA -->
    <?php 
    $isDashboardArea = (bool) ($user && (
        str_starts_with($uri, '/dashboard') || 
        str_starts_with($uri, '/admin') || 
        $uri === '/upgrade'
    ));

    // Template Footer Reusable
    $renderFooter = function() {
    ?>
        <footer class="mt-auto bg-[#060D18] text-slate-400 border-t border-slate-800" style="background-color: #060D18;">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-12">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <!-- Kolom 1: Identitas & Visi -->
                    <div class="md:col-span-2 space-y-3">
                        <div class="flex items-center space-x-3">
                            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-cyan-400 to-maritime-700 flex items-center justify-center text-white shadow">
                                <i data-lucide="waves" class="w-5 h-5 text-white"></i>
                            </div>
                            <span class="font-bold text-white text-lg font-heading tracking-tight">
                                Kelautan<span class="text-cyan-400">.ID</span>
                            </span>
                        </div>
                        <p class="text-sm text-slate-300 leading-relaxed font-medium">
                            Portal Informasi, Riset &amp; Pembelajaran Maritim Terpadu Indonesia.
                        </p>
                        <p class="text-xs text-slate-400 leading-relaxed max-w-md">
                            Sub-aplikasi internal <strong>X-Media Extractor &amp; Archiver</strong> untuk ekstraksi video, GIF, dan gambar kualitas tertinggi dengan retensi otomatis dan moderasi komunitas.
                        </p>
                    </div>

                    <!-- Kolom 2: Regulasi & Kebijakan -->
                    <div class="space-y-3">
                        <h4 class="text-xs font-bold tracking-wider text-slate-300 uppercase font-heading">Regulasi &amp; Komunitas</h4>
                        <ul class="space-y-2 text-xs">
                            <li>
                                <a href="/terms" class="hover:text-cyan-300 transition flex items-center space-x-1.5">
                                    <i data-lucide="file-text" class="w-3.5 h-3.5 text-cyan-400"></i>
                                    <span>Syarat &amp; Ketentuan (ToS)</span>
                                </a>
                            </li>
                            <li>
                                <a href="/rules" class="hover:text-cyan-300 transition flex items-center space-x-1.5">
                                    <i data-lucide="shield-check" class="w-3.5 h-3.5 text-cyan-400"></i>
                                    <span>Aturan Layanan (Rules)</span>
                                </a>
                            </li>
                            <li>
                                <a href="/guidelines" class="hover:text-cyan-300 transition flex items-center space-x-1.5">
                                    <i data-lucide="users" class="w-3.5 h-3.5 text-cyan-400"></i>
                                    <span>Panduan Komunitas</span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Kolom 3: Kontak & Pengembang -->
                    <div class="space-y-3">
                        <h4 class="text-xs font-bold tracking-wider text-slate-300 uppercase font-heading">Kontak &amp; Dukungan</h4>
                        <ul class="space-y-2 text-xs">
                            <li class="flex items-center space-x-1.5">
                                <i data-lucide="mail" class="w-3.5 h-3.5 text-cyan-400"></i>
                                <a href="mailto:teknologikelautanid@gmail.com" class="hover:text-cyan-300 transition">teknologikelautanid@gmail.com</a>
                            </li>
                            <li class="flex items-start space-x-1.5 text-slate-400">
                                <i data-lucide="user" class="w-3.5 h-3.5 text-cyan-400 flex-shrink-0 mt-0.5"></i>
                                <span>Inisiator: <strong>Budhi Agung Prasetyo, S.Pi., M.Si.</strong></span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Garis Pembatas & Hak Cipta -->
                <div class="mt-8 pt-6 border-t border-slate-800/80 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-400 gap-4">
                    <p>© 2026 Kelautan.ID - Portal Maritim Indonesia.</p>
                    <div class="flex items-center space-x-4">
                        <a href="https://kelautan.id" target="_blank" rel="noopener noreferrer" class="hover:text-cyan-400 transition">Portal Utama</a>
                        <a href="https://journal.kelautan.id" target="_blank" rel="noopener noreferrer" class="hover:text-cyan-400 transition">Jurnal</a>
                        <a href="https://go.kelautan.id" target="_blank" rel="noopener noreferrer" class="hover:text-cyan-400 transition">Pemendek Link</a>
                    </div>
                </div>
            </div>
        </footer>
    <?php
    };
    ?>

    <?php if ($isDashboardArea): ?>
        <div class="flex-1 flex w-full min-h-[calc(100vh-4rem)]">
            <!-- Desktop Sidebar (layout_kelautanid_skills.md Section 4.2) -->
            <aside class="hidden md:flex flex-col w-64 bg-[#0A192F] border-r border-slate-800 sticky top-16 h-[calc(100vh-4rem)] flex-shrink-0 z-30 justify-between select-none" style="background-color: #0A192F;">
                <div class="p-4 space-y-6 overflow-y-auto flex-1">
                    <!-- User Profile Mini Box -->
                    <div class="p-3.5 rounded-2xl bg-slate-900/90 border border-slate-800 flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-cyan-400 to-maritime-700 flex items-center justify-center font-heading font-extrabold text-white text-sm shadow-xs flex-shrink-0">
                            <?= strtoupper(substr((string)($user['username'] ?? 'U'), 0, 1)) ?>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="font-bold text-white text-xs truncate">@<?= e($user['username']) ?></div>
                            <div class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold mt-0.5 <?= $role === 'superadmin' ? 'bg-purple-900 text-purple-200' : ($role === 'moderator' ? 'bg-amber-900 text-amber-200' : ($role === 'premium' ? 'bg-cyan-900 text-cyan-200' : 'bg-slate-800 text-slate-300')) ?>">
                                <?= e(user_role_label($role)) ?>
                            </div>
                        </div>
                    </div>

                    <!-- Navigation Groups -->
                    <div class="space-y-4">
                        <div class="space-y-1">
                            <div class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">Navigasi Utama</div>
                            <a href="/dashboard" class="flex items-center space-x-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition <?= $uri === '/dashboard' ? 'border-l-4 border-cyan-400 bg-gradient-to-r from-cyan-950/80 to-blue-950/50 text-cyan-300 font-bold' : 'text-slate-300 hover:text-white hover:bg-slate-800/60 border-l-4 border-transparent' ?>">
                                <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                                <span>Dashboard</span>
                            </a>
                            <a href="/archive" class="flex items-center space-x-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition <?= $uri === '/archive' ? 'border-l-4 border-cyan-400 bg-gradient-to-r from-cyan-950/80 to-blue-950/50 text-cyan-300 font-bold' : 'text-slate-300 hover:text-white hover:bg-slate-800/60 border-l-4 border-transparent' ?>">
                                <i data-lucide="film" class="w-4 h-4"></i>
                                <span>Video Terunduh</span>
                            </a>
                            <a href="/" class="flex items-center space-x-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition <?= $uri === '/' ? 'border-l-4 border-cyan-400 bg-gradient-to-r from-cyan-950/80 to-blue-950/50 text-cyan-300 font-bold' : 'text-slate-300 hover:text-white hover:bg-slate-800/60 border-l-4 border-transparent' ?>">
                                <i data-lucide="sparkles" class="w-4 h-4"></i>
                                <span>Ekstraksi Baru</span>
                            </a>
                            <?php if ($role === 'basic'): ?>
                                <a href="/upgrade" class="flex items-center space-x-2.5 px-3 py-2 rounded-xl text-xs font-bold transition <?= $uri === '/upgrade' ? 'bg-amber-400 text-amber-950 shadow-sm' : 'text-amber-300 hover:bg-amber-500/10' ?>">
                                    <i data-lucide="crown" class="w-4 h-4 text-amber-400"></i>
                                    <span>Upgrade Premium</span>
                                </a>
                            <?php endif; ?>
                        </div>

                        <?php if (can_moderate()): ?>
                            <div class="space-y-1 pt-3 border-t border-slate-800/80">
                                <div class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">Moderasi & Regulasi</div>
                                <a href="/admin/moderation" class="flex items-center space-x-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition <?= str_starts_with($uri, '/admin/moderation') ? 'border-l-4 border-cyan-400 bg-gradient-to-r from-cyan-950/80 to-blue-950/50 text-cyan-300 font-bold' : 'text-slate-300 hover:text-white hover:bg-slate-800/60 border-l-4 border-transparent' ?>">
                                    <i data-lucide="shield-alert" class="w-4 h-4 text-amber-400"></i>
                                    <span>Dashboard Moderasi</span>
                                </a>
                                <a href="/admin/pages" class="flex items-center space-x-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition <?= str_starts_with($uri, '/admin/pages') ? 'border-l-4 border-cyan-400 bg-gradient-to-r from-cyan-950/80 to-blue-950/50 text-cyan-300 font-bold' : 'text-slate-300 hover:text-white hover:bg-slate-800/60 border-l-4 border-transparent' ?>">
                                    <i data-lucide="file-text" class="w-4 h-4 text-cyan-400"></i>
                                    <span>Kelola Regulasi</span>
                                </a>
                            </div>
                        <?php endif; ?>

                        <?php if (is_superadmin()): ?>
                            <div class="space-y-1 pt-3 border-t border-slate-800/80">
                                <div class="px-3 text-[10px] font-bold uppercase tracking-wider text-slate-400">Super Admin</div>
                                <a href="/admin/users" class="flex items-center space-x-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition <?= str_starts_with($uri, '/admin/users') ? 'border-l-4 border-cyan-400 bg-gradient-to-r from-cyan-950/80 to-blue-950/50 text-cyan-300 font-bold' : 'text-slate-300 hover:text-white hover:bg-slate-800/60 border-l-4 border-transparent' ?>">
                                    <i data-lucide="users" class="w-4 h-4 text-purple-400"></i>
                                    <span>Manajemen Pengguna</span>
                                </a>
                                <a href="/admin/settings" class="flex items-center space-x-2.5 px-3 py-2 rounded-xl text-xs font-semibold transition <?= str_starts_with($uri, '/admin/settings') ? 'border-l-4 border-cyan-400 bg-gradient-to-r from-cyan-950/80 to-blue-950/50 text-cyan-300 font-bold' : 'text-slate-300 hover:text-white hover:bg-slate-800/60 border-l-4 border-transparent' ?>">
                                    <i data-lucide="settings" class="w-4 h-4 text-slate-400"></i>
                                    <span>Pengaturan Aplikasi</span>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Tombol Keluar Akun Tetap di Pojok Bawah Kiri Sidebar -->
                <div class="p-4 border-t border-slate-800 bg-slate-950/80 mt-auto">
                    <form method="post" action="/logout" class="m-0">
                        <?= csrf_field() ?>
                        <button type="submit" class="w-full flex items-center justify-center space-x-2 px-3 py-2.5 rounded-xl bg-slate-800/90 hover:bg-rose-950/40 text-slate-400 hover:text-rose-400 border border-slate-700/60 text-xs font-bold transition">
                            <i data-lucide="log-out" class="w-4 h-4"></i>
                            <span>Keluar Akun</span>
                        </button>
                    </form>
                </div>
            </aside>

            <!-- Kolom Konten Utama & Footer Sejajar Sidebar -->
            <div class="flex-1 flex flex-col min-w-0 bg-slate-50 min-h-[calc(100vh-4rem)]">
                <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
                    <!-- Flash Message Alerts -->
                    <?php foreach ($flashes as $f): 
                        $isSuccess = $f['type'] === 'success';
                        $isInfo = $f['type'] === 'info';
                        $badgeBg = $isSuccess ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : ($isInfo ? 'bg-cyan-50 border-cyan-200 text-cyan-800' : 'bg-rose-50 border-rose-200 text-rose-800');
                        $icon = $isSuccess ? 'check-circle-2' : ($isInfo ? 'info' : 'alert-triangle');
                        $iconColor = $isSuccess ? 'text-emerald-600' : ($isInfo ? 'text-cyan-600' : 'text-rose-600');
                    ?>
                        <div class="p-4 rounded-2xl border shadow-xs flex items-start space-x-3 <?= $badgeBg ?>" role="alert">
                            <i data-lucide="<?= $icon ?>" class="w-5 h-5 flex-shrink-0 mt-0.5 <?= $iconColor ?>"></i>
                            <div class="text-sm font-semibold leading-relaxed"><?= e($f['message']) ?></div>
                        </div>
                    <?php endforeach; ?>

                    <?= $content ?>
                </main>

                <!-- Footer Diletakkan di Halaman Utama (Tidak di Bawah Sidebar) -->
                <?php $renderFooter(); ?>
            </div>
        </div>
    <?php else: ?>
        <!-- Standard Canvas for Public Pages -->
        <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
            <!-- Flash Message Alerts -->
            <?php foreach ($flashes as $f): 
                $isSuccess = $f['type'] === 'success';
                $isInfo = $f['type'] === 'info';
                $badgeBg = $isSuccess ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : ($isInfo ? 'bg-cyan-50 border-cyan-200 text-cyan-800' : 'bg-rose-50 border-rose-200 text-rose-800');
                $icon = $isSuccess ? 'check-circle-2' : ($isInfo ? 'info' : 'alert-triangle');
                $iconColor = $isSuccess ? 'text-emerald-600' : ($isInfo ? 'text-cyan-600' : 'text-rose-600');
            ?>
                <div class="p-4 rounded-2xl border shadow-xs flex items-start space-x-3 <?= $badgeBg ?>" role="alert">
                    <i data-lucide="<?= $icon ?>" class="w-5 h-5 flex-shrink-0 mt-0.5 <?= $iconColor ?>"></i>
                    <div class="text-sm font-semibold leading-relaxed"><?= e($f['message']) ?></div>
                </div>
            <?php endforeach; ?>

            <?= $content ?>
        </main>

        <!-- Global Footer -->
        <?php $renderFooter(); ?>
    <?php endif; ?>

    <!-- Script Aplikasi & Inisialisasi Ikon -->
    <script src="/assets/js/app.js" defer></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (window.lucide && typeof window.lucide.createIcons === 'function') {
                window.lucide.createIcons();
            }
        });
    </script>
</body>
</html>
