<?php /** @var array $rows */ /** @var int $total */ /** @var string $q */ /** @var string $type */ /** @var int $page */ /** @var int $pages */ /** @var int $used */
$qs = static fn(array $over = []) => '/archive?' . http_build_query(array_filter(array_merge(['q' => $q, 'type' => $type, 'page' => $page], $over), static fn($v) => $v !== '' && $v !== null && $v !== 1));
$returnUrl = $qs();
$maxStorage = (int) config('max_storage_bytes');
$usedPercent = $maxStorage > 0 ? min(100, round(($used / $maxStorage) * 100, 1)) : 0;
?>

<!-- Breadcrumbs -->
<nav class="flex items-center space-x-2 text-xs font-semibold text-slate-500">
    <a href="/" class="hover:text-cyan-600 transition flex items-center space-x-1">
        <i data-lucide="home" class="w-3.5 h-3.5"></i>
        <span>Beranda</span>
    </a>
    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400"></i>
    <span class="text-maritime-700">Video Terunduh</span>
</nav>

<!-- Page Header Titling -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl sm:text-3xl font-extrabold font-heading text-slate-900 tracking-tight">
            Video Terunduh
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">
            Media X/Twitter yang telah di download pengguna melalui Kelautan.ID.
        </p>
    </div>

    <div class="flex items-center space-x-2">
        <span class="text-xs font-bold text-slate-700 bg-white border border-slate-200/90 px-3.5 py-2 rounded-xl shadow-xs">
            Total: <?= (int) $total ?> Berkas
        </span>
    </div>
</div>

<!-- Toolbar Pencarian & Filter -->
<form method="get" action="/archive" class="p-4 rounded-2xl bg-white border border-slate-200/90 shadow-xs flex flex-col md:flex-row gap-3 items-stretch md:items-center justify-between">
    <div class="relative flex-1">
        <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-3.5 pointer-events-none"></i>
        <input type="search" name="q" value="<?= e($q) ?>" 
               placeholder="Cari berdasarkan @username, teks cuitan, atau Tweet ID..." 
               class="w-full pl-10 pr-4 py-2.5 text-xs sm:text-sm rounded-xl border-slate-300 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 placeholder-slate-400 transition bg-slate-50/50">
    </div>

    <div class="flex items-center gap-2">
        <div class="relative">
            <select name="type" aria-label="Filter tipe media" 
                    class="text-xs sm:text-sm rounded-xl border-slate-300 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 px-3.5 py-2.5 bg-slate-50/50 text-slate-700 font-medium pr-8 appearance-none">
                <option value="">Semua Tipe Media</option>
                <option value="video" <?= $type === 'video' ? 'selected' : '' ?>>Video &amp; GIF</option>
                <option value="photo" <?= $type === 'photo' ? 'selected' : '' ?>>Foto / Gambar</option>
            </select>
            <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 absolute right-2.5 top-3 pointer-events-none"></i>
        </div>

        <button type="submit" class="inline-flex items-center space-x-1.5 px-4 py-2.5 rounded-xl font-bold text-xs sm:text-sm text-white bg-maritime-700 hover:bg-maritime-800 active:scale-95 shadow-md shadow-maritime-700/20 transition flex-shrink-0">
            <i data-lucide="filter" class="w-4 h-4"></i>
            <span>Terapkan</span>
        </button>

        <?php if ($q !== '' || $type !== ''): ?>
            <a href="/archive" class="inline-flex items-center space-x-1 px-3 py-2.5 rounded-xl font-semibold text-xs text-slate-600 bg-slate-100 hover:bg-slate-200 transition">
                <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                <span>Reset</span>
            </a>
        <?php endif; ?>
    </div>
</form>

<!-- Galeri Arsip / State Kosong -->
<?php if (!$rows): ?>
    <div class="p-12 rounded-3xl bg-white border border-slate-200/90 shadow-xs text-center space-y-3">
        <div class="w-16 h-16 rounded-2xl bg-slate-100 text-slate-400 mx-auto flex items-center justify-center">
            <i data-lucide="archive-x" class="w-8 h-8 text-slate-400"></i>
        </div>
        <h3 class="text-lg font-bold font-heading text-slate-800">Belum Ada Media Tersimpan</h3>
        <p class="text-xs sm:text-sm text-slate-500 max-w-md mx-auto">
            <?= ($q !== '' || $type !== '') ? 'Tidak ada media yang cocok dengan filter pencarian Anda.' : 'Gunakan fitur Simpan ke Server pada halaman ekstraksi untuk mengarsipkan media secara permanen.' ?>
        </p>
        <?php if ($q !== '' || $type !== ''): ?>
            <a href="/archive" class="inline-flex items-center space-x-1.5 px-4 py-2 rounded-xl text-xs font-bold text-maritime-700 bg-cyan-50 hover:bg-cyan-100 transition mt-2">
                <span>Hapus Filter</span>
            </a>
        <?php else: ?>
            <a href="/" class="inline-flex items-center space-x-1.5 px-5 py-2.5 rounded-xl font-bold text-xs text-white bg-maritime-700 hover:bg-maritime-800 shadow transition mt-2">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Ekstrak &amp; Simpan Media Baru</span>
            </a>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
        <?php foreach ($rows as $r):
            $isPhoto = $r['media_type'] === 'photo';
            $link = app_url('/m/' . $r['uuid']);
            $cid = 'lnk-' . $r['uuid'];
        ?>
            <article class="group rounded-2xl bg-white border border-slate-200/90 shadow-xs hover:shadow-md hover:border-cyan-300 transition-all flex flex-col overflow-hidden">
                <!-- Preview Thumbnail / Player -->
                <a class="aspect-[16/10] w-full bg-slate-950 relative overflow-hidden flex items-center justify-center group/preview" 
                   href="/m/<?= e($r['uuid']) ?>" target="_blank" rel="noopener">
                    <?php if ($isPhoto): ?>
                        <img src="/m/<?= e($r['uuid']) ?>" alt="" loading="lazy" class="w-full h-full object-cover group-hover/preview:scale-105 transition-transform duration-300">
                    <?php elseif (!empty($r['thumbnail_url'])): ?>
                        <img src="<?= e($r['thumbnail_url']) ?>" alt="" loading="lazy" referrerpolicy="no-referrer" class="w-full h-full object-cover group-hover/preview:scale-105 transition-transform duration-300">
                    <?php else: ?>
                        <div class="flex items-center justify-center text-slate-500">
                            <i data-lucide="video" class="w-10 h-10"></i>
                        </div>
                    <?php endif; ?>

                    <!-- Type Badge -->
                    <span class="absolute top-2.5 left-2.5 inline-flex items-center space-x-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-abyssal-900/80 backdrop-blur-md text-cyan-300 border border-cyan-500/30">
                        <i data-lucide="<?= $isPhoto ? 'image' : ($r['media_type'] === 'gif' ? 'film' : 'play-circle') ?>" class="w-3 h-3"></i>
                        <span><?= $r['media_type'] === 'gif' ? 'GIF Animasi' : ($isPhoto ? 'Foto' : 'Video MP4') ?></span>
                    </span>

                    <!-- Size Badge -->
                    <span class="absolute bottom-2.5 right-2.5 inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold bg-black/70 backdrop-blur-xs text-white font-mono">
                        <?= format_bytes((int) $r['file_size_bytes']) ?>
                    </span>
                </a>

                <!-- Body Metadata -->
                <div class="p-4 flex flex-col flex-1 justify-between space-y-3">
                    <div class="space-y-1">
                        <div class="flex items-center justify-between text-xs font-bold">
                            <span class="text-maritime-700">@<?= e($r['author_handle'] ?? 'unknown') ?></span>
                            <span class="text-[11px] text-slate-400 font-normal">#<?= (int) $r['media_index'] + 1 ?></span>
                        </div>
                        <p class="text-xs text-slate-600 line-clamp-2 leading-relaxed">
                            <?= e($r['tweet_text'] ?? 'Tidak ada deskripsi.') ?>
                        </p>
                    </div>

                    <!-- Meta Info: Download count & date -->
                    <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
                        <span class="inline-flex items-center space-x-1">
                            <i data-lucide="download" class="w-3 h-3 text-slate-400"></i>
                            <span><?= (int) $r['download_count'] ?>&times; diunduh</span>
                        </span>
                        <span><?= e(date('d M Y', strtotime($r['created_at']))) ?></span>
                    </div>

                    <!-- Hidden Input for Copy -->
                    <input type="hidden" id="<?= e($cid) ?>" value="<?= e($link) ?>">

                    <!-- Tombol Aksi -->
                    <div class="flex items-center gap-1.5 pt-1">
                        <button type="button" class="btn-copy flex-1 inline-flex items-center justify-center space-x-1 py-1.5 rounded-lg text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 transition" 
                                data-copy="#<?= e($cid) ?>">
                            <i data-lucide="copy" class="w-3 h-3"></i>
                            <span>Salin</span>
                        </button>

                        <a href="/m/<?= e($r['uuid']) ?>?dl=1" class="flex-1 inline-flex items-center justify-center space-x-1 py-1.5 rounded-lg text-xs font-semibold text-white bg-maritime-700 hover:bg-maritime-800 transition" title="Unduh Langsung">
                            <i data-lucide="download" class="w-3 h-3"></i>
                            <span>Unduh</span>
                        </a>

                        <a href="/result/<?= e($r['tweet_id']) ?>" class="p-1.5 rounded-lg text-slate-500 hover:text-cyan-700 hover:bg-cyan-50 transition" title="Detail Tweet Asli">
                            <i data-lucide="external-link" class="w-4 h-4"></i>
                        </a>

                        <?php if (is_logged_in()): ?>
                            <form method="post" action="/archive/delete/<?= e($r['uuid']) ?>" data-confirm="Hapus berkas ini secara permanen dari server?" class="m-0">
                                <?= csrf_field() ?>
                                <input type="hidden" name="return" value="<?= e($returnUrl) ?>">
                                <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition" title="Hapus Berkas">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>

    <!-- Paginasi Berstandar Kelautan.ID -->
    <?php if ($pages > 1): ?>
        <nav class="flex items-center justify-center space-x-1.5 pt-6" aria-label="Navigasi Paginasi">
            <?php if ($page > 1): ?>
                <a href="<?= e($qs(['page' => $page - 1])) ?>" class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 transition flex items-center space-x-1">
                    <i data-lucide="chevron-left" class="w-3.5 h-3.5"></i>
                    <span>Sebelumnya</span>
                </a>
            <?php else: ?>
                <span class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-300 bg-slate-100 border border-slate-200 cursor-not-allowed flex items-center space-x-1">
                    <i data-lucide="chevron-left" class="w-3.5 h-3.5"></i>
                    <span>Sebelumnya</span>
                </span>
            <?php endif; ?>

            <?php for ($i = max(1, $page - 2); $i <= min($pages, $page + 2); $i++): ?>
                <?php if ($i === $page): ?>
                    <span class="w-9 h-9 rounded-xl text-xs font-bold text-white bg-maritime-700 flex items-center justify-center shadow-xs">
                        <?= $i ?>
                    </span>
                <?php else: ?>
                    <a href="<?= e($qs(['page' => $i])) ?>" class="w-9 h-9 rounded-xl text-xs font-semibold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 flex items-center justify-center transition">
                        <?= $i ?>
                    </a>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($page < $pages): ?>
                <a href="<?= e($qs(['page' => $page + 1])) ?>" class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 transition flex items-center space-x-1">
                    <span>Berikutnya</span>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                </a>
            <?php else: ?>
                <span class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-300 bg-slate-100 border border-slate-200 cursor-not-allowed flex items-center space-x-1">
                    <span>Berikutnya</span>
                    <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                </span>
            <?php endif; ?>
        </nav>
    <?php endif; ?>
<?php endif; ?>
