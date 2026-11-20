<?php /** @var array $tweet */ /** @var array $media */
$total = count($media); ?>

<!-- Breadcrumbs -->
<nav class="flex items-center space-x-2 text-xs font-semibold text-slate-500">
    <a href="/" class="hover:text-cyan-600 transition flex items-center space-x-1">
        <i data-lucide="home" class="w-3.5 h-3.5"></i>
        <span>Beranda</span>
    </a>
    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400"></i>
    <span>Hasil Ekstraksi</span>
    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400"></i>
    <span class="text-maritime-700 font-mono">@<?= e($tweet['author_handle'] ?? 'unknown') ?></span>
</nav>

<!-- Header Info Tweet -->
<article class="bg-white rounded-2xl border border-slate-200/90 shadow-xs p-6 space-y-4">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
        <div class="flex items-center space-x-3">
            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-cyan-400 to-maritime-700 flex items-center justify-center text-white font-heading font-bold text-lg shadow-sm">
                <?= strtoupper(substr((string) ($tweet['author_handle'] ?? 'X'), 0, 1)) ?>
            </div>
            <div>
                <h2 class="text-base sm:text-lg font-bold font-heading text-slate-900 leading-tight">
                    <?= e($tweet['author_name'] ?? 'Pengguna X') ?>
                </h2>
                <div class="flex items-center space-x-2 text-xs text-slate-500 mt-0.5">
                    <span class="font-bold text-maritime-700">@<?= e($tweet['author_handle'] ?? 'unknown') ?></span>
                    <span>&bull;</span>
                    <span class="font-mono">ID: <?= e($tweet['tweet_id']) ?></span>
                </div>
            </div>
        </div>

        <div class="flex items-center space-x-2">
            <span class="inline-flex items-center space-x-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-cyan-50 text-cyan-800 border border-cyan-200">
                <span class="w-1.5 h-1.5 rounded-full bg-cyan-500"></span>
                <span><?= $total ?> Item Media</span>
            </span>
            <a href="<?= e($tweet['original_url']) ?>" target="_blank" rel="noopener noreferrer" 
               class="inline-flex items-center space-x-1.5 px-3 py-1 rounded-full text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 transition">
                <span>Unggahan Asli</span>
                <i data-lucide="external-link" class="w-3 h-3"></i>
            </a>
        </div>
    </div>

    <?php if (!empty($tweet['tweet_text'])): ?>
        <p class="text-sm sm:text-base text-slate-800 whitespace-pre-line leading-relaxed break-words font-medium">
            <?= e($tweet['tweet_text']) ?>
        </p>
    <?php endif; ?>

    <div class="flex flex-wrap items-center gap-3 pt-2 text-xs text-slate-500">
        <span class="inline-flex items-center space-x-1">
            <i data-lucide="cpu" class="w-3.5 h-3.5 text-slate-400"></i>
            <span>Engine: <strong class="text-slate-700"><?= e($tweet['source_engine']) ?></strong></span>
        </span>
        <?php if (!empty($tweet['meta']['created_at'])): ?>
            <span class="inline-flex items-center space-x-1">
                <i data-lucide="calendar" class="w-3.5 h-3.5 text-slate-400"></i>
                <span>Diunggah: <?= e(date('d M Y, H:i', strtotime($tweet['meta']['created_at']))) ?></span>
            </span>
        <?php endif; ?>
    </div>

    <?php if (!empty($tweet['meta']['from_quote'])): ?>
        <div class="p-3.5 rounded-xl bg-cyan-50 border border-cyan-200 text-cyan-900 text-xs flex items-center space-x-2">
            <i data-lucide="quote" class="w-4 h-4 text-cyan-600 flex-shrink-0"></i>
            <span>Media berasal dari unggahan yang dikutip<?= !empty($tweet['meta']['quote_author']) ? ' (@' . e($tweet['meta']['quote_author']) . ')' : '' ?>.</span>
        </div>
    <?php endif; ?>
</article>

<!-- GRID MEDIA HASIL EKSTRAKSI -->
<div class="grid grid-cols-1 <?= $total > 1 ? 'md:grid-cols-2' : '' ?> gap-6">
<?php foreach ($media as $i => $m):
    $isPhoto = $m['media_type'] === 'photo';
    $variants = $m['variants'];
    $best = $variants[0] ?? null;
    $badgeText = $isPhoto
        ? strtoupper($best['ext'] ?? 'JPG') . ' &bull; Original Size'
        : ($m['media_type'] === 'gif' ? 'Animated GIF (MP4)' : 'MP4 &bull; ' . ($best['label'] ?? 'MP4') . ' &bull; Best Quality');
    $inputId = 'direct-' . (int) $m['id'];
?>
    <section class="media-card rounded-2xl bg-white border border-slate-200/90 shadow-sm overflow-hidden flex flex-col hover:border-cyan-300 transition-all">
        <!-- Preview Box -->
        <div class="media-preview-container bg-slate-950 relative flex items-center justify-center overflow-hidden min-h-[280px]">
            <?php if ($isPhoto): ?>
                <a href="<?= e($m['best_direct_url']) ?>" target="_blank" rel="noopener noreferrer" class="w-full flex items-center justify-center p-2 group">
                    <img src="<?= e($m['best_direct_url']) ?>" alt="Gambar <?= $i + 1 ?>" loading="lazy" referrerpolicy="no-referrer" 
                         class="max-h-[460px] w-auto max-w-full object-contain group-hover:scale-[1.01] transition-transform duration-200">
                </a>
            <?php elseif ($m['media_type'] === 'gif'): ?>
                <video poster="<?= e($m['thumbnail_url'] ?? '') ?>" controls loop muted playsinline preload="metadata" referrerpolicy="no-referrer" class="max-h-[460px] w-full">
                    <source src="<?= e($m['best_direct_url']) ?>" type="video/mp4">
                    <source src="/stream/<?= (int) $m['id'] ?>" type="video/mp4">
                    Browser Anda tidak mendukung pemutar video HTML5.
                </video>
            <?php else: ?>
                <video poster="<?= e($m['thumbnail_url'] ?? '') ?>" controls playsinline preload="metadata" referrerpolicy="no-referrer" class="max-h-[460px] w-full">
                    <source src="<?= e($m['best_direct_url']) ?>" type="video/mp4">
                    <source src="/stream/<?= (int) $m['id'] ?>" type="video/mp4">
                    Browser Anda tidak mendukung pemutar video HTML5.
                </video>
            <?php endif; ?>
        </div>

        <!-- Body & Tombol Aksi -->
        <div class="p-5 flex flex-col flex-1 space-y-4">
            <!-- Badges Bar -->
            <div class="flex flex-wrap items-center gap-1.5">
                <span class="inline-flex items-center space-x-1 px-3 py-1 rounded-full text-xs font-bold bg-cyan-50 text-cyan-800 border border-cyan-200 shadow-2xs">
                    <i data-lucide="sparkles" class="w-3.5 h-3.5 text-cyan-600"></i>
                    <span><?= $badgeText ?></span>
                </span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                    #<?= $i + 1 ?> dari <?= $total ?>
                </span>
                <?php if ($m['width'] && $m['height']): ?>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200 font-mono">
                        <?= (int) $m['width'] ?>&times;<?= (int) $m['height'] ?>
                    </span>
                <?php endif; ?>
                <?php if ($m['duration_sec']): ?>
                    <span class="inline-flex items-center space-x-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                        <i data-lucide="clock" class="w-3 h-3 text-slate-400"></i>
                        <span><?= e(format_duration((float) $m['duration_sec'])) ?></span>
                    </span>
                <?php endif; ?>
                <?php if ($m['saved_uuid']): ?>
                    <span class="inline-flex items-center space-x-1 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        <span>Tersimpan di Server</span>
                    </span>
                <?php endif; ?>
            </div>

            <!-- Pilihan Varian (Dropdown jika > 1) -->
            <?php if (count($variants) > 1): ?>
                <div class="space-y-1.5">
                    <label for="var-<?= (int) $m['id'] ?>" class="block text-xs font-bold text-slate-700">
                        <?= $isPhoto ? 'Pilihan Resolusi Gambar:' : 'Pilihan Kualitas Video (Bitrate):' ?>
                    </label>
                    <div class="relative">
                        <select id="var-<?= (int) $m['id'] ?>" data-variant-select 
                                class="w-full text-xs sm:text-sm rounded-xl border-slate-300 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 px-3.5 py-2.5 bg-white font-medium text-slate-800 shadow-xs appearance-none pr-8">
                            <?php foreach ($variants as $vi => $v): ?>
                                <option value="<?= $vi ?>" data-url="<?= e($v['url']) ?>" data-download="/download/<?= (int) $m['id'] ?>?variant=<?= $vi ?>">
                                    <?= e($v['label']) ?><?= $vi === 0 ? ' &bull; Kualitas Terbaik' : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 absolute right-3 top-3 pointer-events-none"></i>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Direct Link Box -->
            <div class="space-y-1.5">
                <label for="<?= e($inputId) ?>" class="block text-xs font-bold text-slate-600 uppercase tracking-wider">
                    Direct URL Asli:
                </label>
                <div class="flex items-center space-x-2 bg-slate-50 border border-slate-200 rounded-xl p-1.5 pl-3 focus-within:border-cyan-500 focus-within:ring-2 focus-within:ring-cyan-500/10 transition">
                    <i data-lucide="link" class="w-4 h-4 text-slate-400 flex-shrink-0"></i>
                    <input type="text" id="<?= e($inputId) ?>" class="js-direct w-full bg-transparent text-xs font-mono text-slate-700 outline-none select-all" 
                           value="<?= e($m['best_direct_url']) ?>" readonly aria-label="Direct URL">
                    <button type="button" class="btn-copy inline-flex items-center space-x-1 px-3 py-1.5 rounded-lg text-xs font-semibold text-slate-700 bg-white hover:bg-slate-100 border border-slate-300 shadow-2xs transition flex-shrink-0" 
                            data-copy="#<?= e($inputId) ?>">
                        <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                        <span>Salin</span>
                    </button>
                </div>
            </div>

            <!-- Tombol Aksi Utama -->
            <div class="flex flex-wrap items-center gap-2 pt-1">
                <a class="js-open inline-flex items-center space-x-1.5 px-4 py-2.5 rounded-xl font-bold text-xs text-slate-800 bg-white hover:bg-slate-50 border border-slate-300 shadow-2xs transition" 
                   href="<?= e($m['best_direct_url']) ?>" target="_blank" rel="noopener noreferrer">
                    <i data-lucide="external-link" class="w-3.5 h-3.5 text-slate-500"></i>
                    <span>Buka Langsung</span>
                </a>

                <a class="js-download inline-flex items-center space-x-1.5 px-4 py-2.5 rounded-xl font-bold text-xs text-white bg-maritime-700 hover:bg-maritime-800 active:scale-95 shadow-md shadow-maritime-700/20 transition" 
                   href="/download/<?= (int) $m['id'] ?>?variant=0">
                    <i data-lucide="download" class="w-3.5 h-3.5"></i>
                    <span>Unduh File</span>
                </a>

                <?php if ($m['saved_uuid']): ?>
                    <a class="inline-flex items-center space-x-1.5 px-4 py-2.5 rounded-xl font-bold text-xs text-white bg-emerald-600 hover:bg-emerald-700 active:scale-95 shadow-md shadow-emerald-600/20 transition" 
                       href="/m/<?= e($m['saved_uuid']) ?>" target="_blank" rel="noopener">
                        <i data-lucide="hard-drive" class="w-3.5 h-3.5"></i>
                        <span>Buka Link Server</span>
                    </a>
                <?php elseif (is_logged_in()): ?>
                    <form method="post" action="/archive/save" data-loading="Menyimpan…" class="m-0">
                        <?= csrf_field() ?>
                        <input type="hidden" name="media_item_id" value="<?= (int) $m['id'] ?>">
                        <input type="hidden" name="tweet_id" value="<?= e($tweet['tweet_id']) ?>">
                        <button class="inline-flex items-center space-x-1.5 px-4 py-2.5 rounded-xl font-bold text-xs text-white bg-cyan-600 hover:bg-cyan-700 active:scale-95 shadow-md shadow-cyan-600/20 transition" type="submit">
                            <i data-lucide="save" class="w-3.5 h-3.5"></i>
                            <span>Simpan ke Server</span>
                        </button>
                    </form>
                <?php else: ?>
                    <a class="inline-flex items-center space-x-1.5 px-4 py-2.5 rounded-xl font-semibold text-xs text-slate-600 bg-slate-100 hover:bg-slate-200 border border-slate-300 transition" 
                       href="/login?next=<?= e(rawurlencode('/result/' . $tweet['tweet_id'])) ?>" title="Login Operator Diperlukan">
                        <i data-lucide="lock" class="w-3.5 h-3.5 text-slate-400"></i>
                        <span>Simpan ke Server 🔒</span>
                    </a>
                <?php endif; ?>
            </div>

            <!-- Tautan Arsip Server (Jika Sudah Ada) -->
            <?php if ($m['saved_uuid']): $srvLink = app_url('/m/' . $m['saved_uuid']); ?>
                <div class="p-3 rounded-xl bg-emerald-50/70 border border-emerald-200 space-y-1.5 mt-2">
                    <div class="flex items-center justify-between text-xs text-emerald-800 font-bold">
                        <span class="flex items-center space-x-1">
                            <i data-lucide="check-circle" class="w-3.5 h-3.5 text-emerald-600"></i>
                            <span>Tautan Arsip Lokal Permanen</span>
                        </span>
                        <?php if ($m['saved_size']): ?>
                            <span class="text-[11px] font-normal text-emerald-700 font-mono"><?= format_bytes((int) $m['saved_size']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="flex items-center space-x-2 bg-white border border-emerald-300 rounded-lg p-1.5 pl-2.5">
                        <input type="text" id="srv-<?= (int) $m['id'] ?>" class="w-full bg-transparent text-xs font-mono text-emerald-900 outline-none select-all" 
                               value="<?= e($srvLink) ?>" readonly>
                        <button type="button" class="btn-copy inline-flex items-center space-x-1 px-2.5 py-1 rounded-md text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 transition flex-shrink-0" 
                                data-copy="#srv-<?= (int) $m['id'] ?>">
                            <i data-lucide="copy" class="w-3 h-3"></i>
                            <span>Salin</span>
                        </button>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Tabel Detail Semua Varian (Collapsible) -->
            <?php if (count($variants) > 1): ?>
                <details class="group pt-2 border-t border-slate-100 text-xs">
                    <summary class="flex items-center justify-between font-semibold text-maritime-700 cursor-pointer hover:text-cyan-600 transition py-1">
                        <span class="flex items-center space-x-1.5">
                            <i data-lucide="list" class="w-3.5 h-3.5"></i>
                            <span>Daftar Seluruh Varian Resolusi (<?= count($variants) ?>)</span>
                        </span>
                        <i data-lucide="chevron-down" class="w-4 h-4 group-open:rotate-180 transition-transform text-slate-400"></i>
                    </summary>
                    <div class="mt-2 overflow-x-auto rounded-xl border border-slate-200 bg-slate-50/70 p-1">
                        <table class="w-full text-left text-xs">
                            <thead class="text-slate-500 font-semibold border-b border-slate-200">
                                <tr>
                                    <th class="p-2">Resolusi / Bitrate</th>
                                    <th class="p-2 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200">
                                <?php foreach ($variants as $vi => $v): ?>
                                    <tr class="hover:bg-white transition">
                                        <td class="p-2 font-medium text-slate-800">
                                            <?= e($v['label']) ?>
                                            <?= $vi === 0 ? '<span class="ml-1 text-[10px] text-cyan-600 font-bold uppercase">(Kualitas Terbaik)</span>' : '' ?>
                                        </td>
                                        <td class="p-2 text-right space-x-2">
                                            <a href="<?= e($v['url']) ?>" target="_blank" rel="noopener noreferrer" class="text-slate-600 hover:text-maritime-700 font-semibold">Buka</a>
                                            <span class="text-slate-300">&bull;</span>
                                            <a href="/download/<?= (int) $m['id'] ?>?variant=<?= $vi ?>" class="text-maritime-700 hover:text-cyan-600 font-semibold">Unduh</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </details>
            <?php endif; ?>
        </div>
    </section>
<?php endforeach; ?>
</div>

<!-- Navigasi Kembali -->
<div class="pt-4 flex items-center justify-between">
    <a href="/" class="inline-flex items-center space-x-2 text-sm font-semibold text-maritime-700 hover:text-cyan-600 transition">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>
        <span>Ekstrak Tautan Lain</span>
    </a>
    <a href="/archive" class="inline-flex items-center space-x-2 text-sm font-semibold text-slate-600 hover:text-slate-900 transition">
        <i data-lucide="hard-drive" class="w-4 h-4"></i>
        <span>Buka Galeri Arsip</span>
    </a>
</div>
