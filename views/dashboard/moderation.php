<?php /** @var array $rows */ /** @var int $total */ /** @var string $q */ /** @var string $filter */ /** @var int $page */ /** @var int $pages */
$qs = static fn(array $over = []) => '/admin/moderation?' . http_build_query(array_filter(array_merge(['q' => $q, 'filter' => $filter, 'page' => $page], $over), static fn($v) => $v !== '' && $v !== null && $v !== 1));
?>

<!-- Breadcrumbs -->
<nav class="flex items-center space-x-2 text-xs font-semibold text-slate-500 mb-2">
    <a href="/" class="hover:text-cyan-600 transition flex items-center space-x-1">
        <i data-lucide="home" class="w-3.5 h-3.5"></i>
        <span>Beranda</span>
    </a>
    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400"></i>
    <span class="text-maritime-700">Dashboard Moderasi Konten</span>
</nav>

<!-- Page Header Titling -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl sm:text-3xl font-extrabold font-heading text-slate-900 tracking-tight flex items-center space-x-2">
            <span>Dashboard Moderasi Komunitas</span>
            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                Staff Only
            </span>
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">
            Penegakan aturan layanan dan panduan komunitas pada riwayat ekstraksi media.
        </p>
    </div>

    <div class="flex items-center space-x-2">
        <span class="text-xs font-bold text-slate-600 bg-white border border-slate-200 px-3.5 py-2 rounded-xl shadow-xs">
            Total: <?= (int) $total ?> Cuitan Terdata
        </span>
    </div>
</div>

<!-- Toolbar Filter & Cari -->
<form method="get" action="/admin/moderation" class="p-4 rounded-2xl bg-white border border-slate-200/90 shadow-xs flex flex-col md:flex-row gap-3 items-stretch md:items-center justify-between">
    <div class="relative flex-1">
        <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-3.5 pointer-events-none"></i>
        <input type="search" name="q" value="<?= e($q) ?>" 
               placeholder="Cari berdasarkan @author, teks, atau Tweet ID..." 
               class="w-full pl-10 pr-4 py-2.5 text-xs sm:text-sm rounded-xl border-slate-300 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 placeholder-slate-400 transition bg-slate-50/50">
    </div>

    <div class="flex items-center gap-2">
        <div class="relative">
            <select name="filter" aria-label="Filter status moderasi" 
                    class="text-xs sm:text-sm rounded-xl border-slate-300 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 px-3.5 py-2.5 bg-slate-50/50 text-slate-700 font-medium pr-8 appearance-none">
                <option value="">Semua Unggahan</option>
                <option value="flagged" <?= $filter === 'flagged' ? 'selected' : '' ?>>Ditandai Pelanggaran (Flagged)</option>
                <option value="saved" <?= $filter === 'saved' ? 'selected' : '' ?>>Tersimpan di Server Lokal</option>
            </select>
            <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 absolute right-2.5 top-3 pointer-events-none"></i>
        </div>

        <button type="submit" class="inline-flex items-center space-x-1.5 px-4 py-2.5 rounded-xl font-bold text-xs sm:text-sm text-white bg-maritime-700 hover:bg-maritime-800 transition flex-shrink-0">
            <i data-lucide="filter" class="w-4 h-4"></i>
            <span>Filter</span>
        </button>

        <?php if ($q !== '' || $filter !== ''): ?>
            <a href="/admin/moderation" class="p-2.5 rounded-xl text-slate-500 hover:text-slate-700 bg-slate-100 transition" title="Reset Filter">
                <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
            </a>
        <?php endif; ?>
    </div>
</form>

<!-- Tabel Moderasi -->
<?php if (!$rows): ?>
    <div class="p-12 rounded-3xl bg-white border border-slate-200 text-center space-y-2">
        <i data-lucide="check-circle" class="w-10 h-10 text-emerald-500 mx-auto"></i>
        <h3 class="text-base font-bold text-slate-800">Tidak Ada Unggahan yang Perlu Dimoderasi</h3>
        <p class="text-xs text-slate-500">Semua cuitan bersih atau filter tidak menemukan hasil.</p>
    </div>
<?php else: ?>
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase tracking-wider font-semibold">
                    <tr>
                        <th class="px-4 py-3.5">Media</th>
                        <th class="px-4 py-3.5">Detail Cuitan &amp; Akun</th>
                        <th class="px-4 py-3.5">Diekstrak Oleh</th>
                        <th class="px-4 py-3.5">Status Moderasi</th>
                        <th class="px-4 py-3.5 text-right">Aksi Moderasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach ($rows as $t): 
                        $isFlagged = (bool) ($t['is_flagged'] ?? false);
                    ?>
                        <tr class="hover:bg-slate-50/70 transition <?= $isFlagged ? 'bg-rose-50/30' : '' ?>">
                            <!-- Thumbnail -->
                            <td class="px-4 py-3 w-24">
                                <a href="/result/<?= e($t['tweet_id']) ?>" target="_blank" class="block w-20 aspect-video rounded-lg bg-slate-900 overflow-hidden relative group">
                                    <?php if (!empty($t['thumb'])): ?>
                                        <img src="<?= e($t['thumb']) ?>" alt="" loading="lazy" referrerpolicy="no-referrer" class="w-full h-full object-cover group-hover:scale-105 transition">
                                    <?php else: ?>
                                        <div class="w-full h-full flex items-center justify-center text-slate-500"><i data-lucide="film" class="w-4 h-4"></i></div>
                                    <?php endif; ?>
                                    <span class="absolute bottom-1 right-1 text-[9px] font-bold text-white bg-black/70 px-1 rounded">
                                        <?= (int) $t['media_count'] ?>
                                    </span>
                                </a>
                            </td>

                            <!-- Info Cuitan -->
                            <td class="px-4 py-3 max-w-xs sm:max-w-md">
                                <div class="flex items-center space-x-1.5 font-bold text-slate-900">
                                    <span class="text-maritime-700">@<?= e($t['author_handle'] ?? 'unknown') ?></span>
                                    <span class="text-slate-400 font-normal">&bull;</span>
                                    <span class="text-[11px] font-mono text-slate-400">ID: <?= e($t['tweet_id']) ?></span>
                                </div>
                                <p class="text-xs text-slate-700 line-clamp-2 mt-0.5 leading-relaxed">
                                    <?= e($t['tweet_text'] ?? 'Tanpa teks') ?>
                                </p>
                                <span class="text-[10px] text-slate-400 mt-1 block">
                                    Ekstraksi: <?= e(date('d M Y H:i', strtotime($t['fetched_at']))) ?> &bull; <?= (int) $t['saved_count'] ?> file di server
                                </span>
                            </td>

                            <!-- Extractor -->
                            <td class="px-4 py-3 whitespace-nowrap">
                                <?php if (!empty($t['extractor_username'])): ?>
                                    <span class="font-bold text-slate-800">@<?= e($t['extractor_username']) ?></span>
                                    <span class="block text-[10px] text-slate-400"><?= e(user_role_label($t['extractor_role'])) ?></span>
                                <?php else: ?>
                                    <span class="text-slate-500 font-mono text-[11px]"><?= e($t['ip_address'] ?? 'Tamu (IP)') ?></span>
                                    <span class="block text-[10px] text-slate-400">Unregistered User</span>
                                <?php endif; ?>
                            </td>

                            <!-- Status Flag -->
                            <td class="px-4 py-3 whitespace-nowrap">
                                <?php if ($isFlagged): ?>
                                    <span class="inline-flex items-center space-x-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                        <i data-lucide="alert-octagon" class="w-3 h-3 text-rose-500"></i>
                                        <span>Dilaporkan</span>
                                    </span>
                                    <span class="block text-[10px] text-rose-600 mt-0.5 max-w-[140px] truncate" title="<?= e($t['flag_reason'] ?? '') ?>">
                                        <?= e($t['flag_reason'] ?? 'Pelanggaran') ?>
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center space-x-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        <span>Sesuai Aturan</span>
                                    </span>
                                <?php endif; ?>
                            </td>

                            <!-- Aksi Moderasi -->
                            <td class="px-4 py-3 text-right whitespace-nowrap space-x-1">
                                <a href="/result/<?= e($t['tweet_id']) ?>" target="_blank" class="p-1.5 rounded-lg text-slate-600 hover:text-cyan-700 hover:bg-slate-100 inline-flex items-center" title="Buka Detail">
                                    <i data-lucide="external-link" class="w-4 h-4"></i>
                                </a>

                                <?php if ($isFlagged): ?>
                                    <form method="post" action="/admin/unflag/<?= e($t['tweet_id']) ?>" class="inline m-0">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="px-2 py-1 rounded-lg text-[11px] font-bold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 transition" title="Cabut Status Pelanggaran">
                                            Unflag
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <form method="post" action="/admin/flag/<?= e($t['tweet_id']) ?>" class="inline m-0" onsubmit="var r = prompt('Alasan menandai (flag) cuitan ini:'); if(!r) return false; this.reason.value = r;">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="reason" value="">
                                        <button type="submit" class="p-1.5 rounded-lg text-amber-600 hover:bg-amber-50 transition" title="Tandai Pelanggaran (Flag)">
                                            <i data-lucide="flag" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <!-- Hapus Permanen (Item 1 & 9) -->
                                <form method="post" action="/extract/delete/<?= e($t['tweet_id']) ?>" 
                                      data-confirm="HAPUS PERMANEN cuitan ini beserta seluruh berkas media yang tersimpan di server?" class="inline m-0">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="return" value="/admin/moderation">
                                    <button type="submit" class="p-1.5 rounded-lg text-rose-600 hover:bg-rose-50 transition" title="Hapus Permanen">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Paginasi -->
    <?php if ($pages > 1): ?>
        <nav class="flex items-center justify-center space-x-1.5 pt-4">
            <?php for ($i = 1; $i <= $pages; $i++): ?>
                <a href="<?= e($qs(['page' => $i])) ?>" class="w-9 h-9 rounded-xl text-xs font-semibold <?= $i === $page ? 'bg-maritime-700 text-white font-bold' : 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-50' ?> flex items-center justify-center transition">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
        </nav>
    <?php endif; ?>
<?php endif; ?>
