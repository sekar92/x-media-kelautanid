<?php /** @var array $user */ /** @var array $extractions */ /** @var int $total */ /** @var int $page */ /** @var int $pages */ /** @var int $usedBytes */ /** @var int $limitBytes */ /** @var float $percentUsed */ /** @var int $retentionDays */
$role = $user['role'] ?? 'basic';
$isBasic = $role === 'basic';
$isPremium = $role === 'premium';
?>

<!-- Breadcrumbs -->
<nav class="flex items-center space-x-2 text-xs font-semibold text-slate-500 mb-2">
    <a href="/" class="hover:text-cyan-600 transition flex items-center space-x-1">
        <i data-lucide="home" class="w-3.5 h-3.5"></i>
        <span>Beranda</span>
    </a>
    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400"></i>
    <span class="text-maritime-700">Dashboard Pengguna</span>
</nav>

<!-- Header Profil & Ringkasan Kuota -->
<section class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Kartu Profil Akun -->
    <div class="lg:col-span-2 p-6 rounded-3xl bg-white border border-slate-200/90 shadow-xs flex flex-col justify-between space-y-4">
        <div class="flex items-start justify-between">
            <div class="flex items-center space-x-4">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-cyan-400 to-maritime-700 flex items-center justify-center text-white font-heading font-extrabold text-xl shadow-md">
                    <?= strtoupper(substr((string) ($user['username'] ?? 'U'), 0, 1)) ?>
                </div>
                <div>
                    <div class="flex items-center space-x-2">
                        <h1 class="text-xl sm:text-2xl font-extrabold font-heading text-slate-900 leading-tight">
                            @<?= e($user['username']) ?>
                        </h1>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider <?= $role === 'superadmin' ? 'bg-purple-100 text-purple-800' : ($role === 'moderator' ? 'bg-amber-100 text-amber-800' : ($role === 'premium' ? 'bg-cyan-100 text-cyan-800 border border-cyan-300' : 'bg-slate-100 text-slate-700')) ?>">
                            <?= e(user_role_label($role)) ?>
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">
                        Terdaftar sejak <?= e(date('d M Y', strtotime($user['created_at'] ?? 'now'))) ?> &bull; <?= (int) $total ?> cuitan diekstrak
                    </p>
                </div>
            </div>

            <?php if ($isBasic): ?>
                <a href="/upgrade" class="inline-flex items-center space-x-1.5 px-4 py-2 rounded-xl font-bold text-xs text-amber-950 bg-amber-400 hover:bg-amber-300 shadow-xs transition">
                    <i data-lucide="crown" class="w-4 h-4 text-amber-900"></i>
                    <span>Upgrade ke Premium</span>
                </a>
            <?php endif; ?>
        </div>

        <!-- Bar Kapasitas Penyimpanan -->
        <div class="space-y-2 pt-2 border-t border-slate-100">
            <div class="flex items-center justify-between text-xs font-bold">
                <span class="flex items-center space-x-1.5 text-slate-700">
                    <i data-lucide="hard-drive" class="w-4 h-4 text-maritime-700"></i>
                    <span>Kapasitas Penyimpanan Server</span>
                </span>
                <span class="text-maritime-700 font-mono"><?= format_bytes($usedBytes) ?> / <?= format_bytes($limitBytes) ?> (<?= $percentUsed ?>%)</span>
            </div>
            <div class="w-full bg-slate-100 rounded-full h-3 overflow-hidden border border-slate-200">
                <div class="bg-gradient-to-r from-cyan-500 to-maritime-700 h-3 rounded-full transition-all duration-500" style="width: <?= $percentUsed ?>%"></div>
            </div>
            <div class="flex items-center justify-between text-[11px] text-slate-400">
                <span>Retensi Berkas &amp; Riwayat: <strong class="text-slate-700"><?= $retentionDays ?> Hari</strong></span>
                <span><?= $isBasic ? 'Tingkatkan ke 90 hari &bull; 5GB dengan Premium' : 'Masa aktif retensi diperpanjang' ?></span>
            </div>
        </div>
    </div>

    <!-- Banner Info / Upgrade CTA -->
    <div class="p-6 rounded-3xl bg-oceanic-gradient text-white shadow-md flex flex-col justify-between space-y-4 relative overflow-hidden" 
         style="background: linear-gradient(135deg, #0A192F 0%, #1E3A8A 100%);">
        <div class="space-y-2 relative z-10">
            <div class="inline-flex items-center space-x-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-white/10 border border-white/20 text-cyan-300">
                <i data-lucide="<?= $isPremium ? 'sparkles' : 'zap' ?>" class="w-3 h-3"></i>
                <span><?= $isPremium ? 'Status Akun Premium Aktif' : 'Tingkatkan Layanan' ?></span>
            </div>
            <h3 class="text-lg font-bold font-heading text-white">
                <?= $isPremium ? 'Nikmati Kuota 5GB &amp; Retensi 90 Hari' : 'Butuh Penyimpanan Lebih Besar?' ?>
            </h3>
            <p class="text-xs text-slate-200 leading-relaxed">
                <?= $isPremium 
                    ? 'Berkas video dan foto Anda disimpan hingga 90 hari di server internal dengan kecepatan unduh maksimal.' 
                    : 'User Premium mendapatkan ruang simpan 5GB, masa simpan berkas hingga 90 hari, dan prioritas unduhan.' ?>
            </p>
        </div>

        <div class="relative z-10 pt-2">
            <?php if ($isBasic): ?>
                <a href="/upgrade" class="inline-flex items-center justify-center space-x-2 w-full py-2.5 rounded-xl font-bold text-xs text-slate-950 bg-cyan-400 hover:bg-cyan-300 shadow-md transition">
                    <i data-lucide="arrow-up-circle" class="w-4 h-4"></i>
                    <span>Lihat Paket Upgrade Premium</span>
                </a>
            <?php else: ?>
                <a href="/archive" class="inline-flex items-center justify-center space-x-2 w-full py-2.5 rounded-xl font-bold text-xs text-white bg-white/20 hover:bg-white/30 transition">
                    <i data-lucide="film" class="w-4 h-4"></i>
                    <span>Buka Video Terunduh</span>
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php if (!empty($systemStats)): ?>
    <!-- STATISTIK SISTEM & SERVER (Super Admin & Moderator) (Item 8) -->
    <section class="space-y-3 pt-2">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <span class="w-2.5 h-2.5 rounded-full bg-cyan-500 animate-pulse"></span>
                <h2 class="text-base font-extrabold font-heading text-slate-900 tracking-tight">
                    Statistik Server &amp; Pengguna Sistem
                </h2>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-200">Khusus Staff</span>
            </div>
            <span class="text-xs text-slate-400 font-mono">Real-time Database</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- 1. Total Berkas Tersimpan -->
            <div class="p-5 rounded-2xl bg-white border border-slate-200/90 shadow-xs flex items-center space-x-4">
                <div class="w-12 h-12 rounded-xl bg-cyan-50 border border-cyan-100 flex items-center justify-center text-maritime-700 flex-shrink-0 shadow-2xs">
                    <i data-lucide="hard-drive" class="w-6 h-6"></i>
                </div>
                <div>
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Berkas Tersimpan</div>
                    <div class="text-2xl font-extrabold font-heading text-slate-900 font-mono"><?= number_format($systemStats['total_files']) ?></div>
                    <div class="text-[11px] text-slate-500">Total file di server</div>
                </div>
            </div>

            <!-- 2. Kapasitas Server Terpakai -->
            <?php 
            $serverMax = $systemStats['server_max_bytes'];
            $serverUsed = $systemStats['total_bytes'];
            $serverPct = $serverMax > 0 ? min(100, round(($serverUsed / $serverMax) * 100, 1)) : 0;
            ?>
            <div class="p-5 rounded-2xl bg-white border border-slate-200/90 shadow-xs flex items-center space-x-4">
                <div class="w-12 h-12 rounded-xl bg-blue-50 border border-blue-100 flex items-center justify-center text-maritime-700 flex-shrink-0 shadow-2xs">
                    <i data-lucide="server" class="w-6 h-6"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Penyimpanan Server</div>
                    <div class="text-lg font-extrabold font-heading text-slate-900 font-mono"><?= format_bytes($serverUsed) ?></div>
                    <div class="text-[11px] text-slate-500">dari <?= format_bytes($serverMax) ?> (<?= $serverPct ?>%)</div>
                </div>
            </div>

            <!-- 3. Total Pengguna Terdaftar -->
            <div class="p-5 rounded-2xl bg-white border border-slate-200/90 shadow-xs flex items-center space-x-4">
                <div class="w-12 h-12 rounded-xl bg-purple-50 border border-purple-100 flex items-center justify-center text-purple-700 flex-shrink-0 shadow-2xs">
                    <i data-lucide="users" class="w-6 h-6"></i>
                </div>
                <div>
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Pengguna</div>
                    <div class="text-2xl font-extrabold font-heading text-slate-900 font-mono"><?= (int)$systemStats['total_users'] ?></div>
                    <div class="text-[11px] text-slate-500">Akun terdaftar</div>
                </div>
            </div>

            <!-- 4. Breakdown Level Pengguna -->
            <div class="p-4 rounded-2xl bg-white border border-slate-200/90 shadow-xs flex flex-col justify-between">
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-1.5">Pengguna Per Level</div>
                <div class="grid grid-cols-2 gap-1.5 text-xs">
                    <span class="inline-flex items-center space-x-1 px-2 py-0.5 rounded-lg bg-purple-50 text-purple-800 text-[11px] font-semibold">
                        <span>Admin:</span> <strong class="font-bold"><?= (int)($systemStats['role_counts']['superadmin'] ?? 0) ?></strong>
                    </span>
                    <span class="inline-flex items-center space-x-1 px-2 py-0.5 rounded-lg bg-amber-50 text-amber-800 text-[11px] font-semibold">
                        <span>Mod:</span> <strong class="font-bold"><?= (int)($systemStats['role_counts']['moderator'] ?? 0) ?></strong>
                    </span>
                    <span class="inline-flex items-center space-x-1 px-2 py-0.5 rounded-lg bg-cyan-50 text-cyan-800 text-[11px] font-semibold">
                        <span>Prem:</span> <strong class="font-bold"><?= (int)($systemStats['role_counts']['premium'] ?? 0) ?></strong>
                    </span>
                    <span class="inline-flex items-center space-x-1 px-2 py-0.5 rounded-lg bg-slate-100 text-slate-700 text-[11px] font-semibold">
                        <span>Basic:</span> <strong class="font-bold"><?= (int)($systemStats['role_counts']['basic'] ?? 0) ?></strong>
                    </span>
                </div>
            </div>
        </div>
    </section>
<?php endif; ?>

<!-- RIWAYAT EKSTRAKSI SAYA (Item 8) -->
<section class="space-y-4 pt-4">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl sm:text-2xl font-bold font-heading text-slate-900">
                Riwayat Ekstraksi Video &amp; Media Saya
            </h2>
            <p class="text-xs sm:text-sm text-slate-500">
                Daftar unggahan X/Twitter yang pernah Anda proses melalui akun ini (otomatis dibersihkan setelah <?= $retentionDays ?> hari).
            </p>
        </div>
        <a href="/" class="inline-flex items-center space-x-1.5 px-4 py-2 rounded-xl font-bold text-xs text-white bg-maritime-700 hover:bg-maritime-800 transition">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Ekstrak Cuitan Baru</span>
        </a>
    </div>

    <?php if (!$extractions): ?>
        <div class="p-12 rounded-3xl bg-white border border-slate-200 shadow-xs text-center space-y-3">
            <div class="w-16 h-16 rounded-2xl bg-cyan-50 border border-cyan-100 text-maritime-700 mx-auto flex items-center justify-center">
                <i data-lucide="film" class="w-8 h-8 text-maritime-700"></i>
            </div>
            <h3 class="text-lg font-bold font-heading text-slate-800">Belum Ada Riwayat Ekstraksi</h3>
            <p class="text-xs sm:text-sm text-slate-500 max-w-md mx-auto">
                Anda belum pernah mengekstrak cuitan saat masuk akun. Tempelkan URL video atau foto X/Twitter untuk mulai menyimpan riwayat.
            </p>
            <a href="/" class="inline-flex items-center space-x-1.5 px-5 py-2.5 rounded-xl font-bold text-xs text-white bg-maritime-700 hover:bg-maritime-800 shadow transition mt-2">
                <i data-lucide="sparkles" class="w-4 h-4"></i>
                <span>Mulai Ekstrak Sekarang</span>
            </a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
            <?php foreach ($extractions as $t): ?>
                <div class="group rounded-2xl bg-white border border-slate-200/90 shadow-xs hover:shadow-md hover:border-cyan-300 transition-all flex flex-col overflow-hidden">
                    <!-- Thumbnail preview -->
                    <a href="/result/<?= e($t['tweet_id']) ?>" class="aspect-video w-full bg-slate-900 relative overflow-hidden flex items-center justify-center">
                        <?php if (!empty($t['thumb'])): ?>
                            <img src="<?= e($t['thumb']) ?>" alt="" loading="lazy" referrerpolicy="no-referrer" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        <?php else: ?>
                            <div class="flex items-center justify-center text-slate-500">
                                <i data-lucide="film" class="w-8 h-8"></i>
                            </div>
                        <?php endif; ?>
                        <span class="absolute top-2.5 right-2.5 inline-flex items-center space-x-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-abyssal-900/80 backdrop-blur-md text-cyan-300 border border-cyan-500/30">
                            <i data-lucide="layers" class="w-3 h-3"></i>
                            <span><?= (int) $t['media_count'] ?> media</span>
                        </span>
                    </a>

                    <div class="p-4 flex flex-col flex-1 justify-between space-y-3">
                        <div class="space-y-1">
                            <div class="flex items-center justify-between text-xs font-bold">
                                <span class="text-maritime-700">@<?= e($t['author_handle'] ?? 'unknown') ?></span>
                                <span class="text-[10px] text-slate-400 font-mono">ID: <?= e($t['tweet_id']) ?></span>
                            </div>
                            <p class="text-xs text-slate-600 line-clamp-2 leading-relaxed">
                                <?= e($t['tweet_text'] ?? 'Tidak ada deskripsi.') ?>
                            </p>
                        </div>

                        <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
                            <span><?= e(date('d M Y, H:i', strtotime($t['fetched_at']))) ?></span>
                            <span class="inline-flex items-center space-x-1 text-emerald-600 font-semibold">
                                <i data-lucide="check" class="w-3 h-3"></i>
                                <span><?= (int) $t['saved_count'] ?> tersimpan</span>
                            </span>
                        </div>

                        <div class="flex items-center gap-1.5 pt-1">
                            <a href="/result/<?= e($t['tweet_id']) ?>" class="flex-1 inline-flex items-center justify-center space-x-1 py-1.5 rounded-lg text-xs font-semibold text-white bg-maritime-700 hover:bg-maritime-800 transition">
                                <i data-lucide="external-link" class="w-3 h-3"></i>
                                <span>Buka Hasil</span>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($pages > 1): ?>
            <nav class="flex items-center justify-center space-x-1.5 pt-6">
                <?php for ($i = 1; $i <= $pages; $i++): ?>
                    <a href="/dashboard?page=<?= $i ?>" class="w-9 h-9 rounded-xl text-xs font-semibold <?= $i === $page ? 'bg-maritime-700 text-white font-bold' : 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-50' ?> flex items-center justify-center transition">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</section>
