<?php /** @var array $recent */ /** @var string $old */ ?>

<!-- HERO SECTION MARITIM (High Contrast Dark Navy Background) -->
<section class="relative overflow-hidden rounded-3xl bg-[#0A192F] text-white p-6 sm:p-10 shadow-2xl border border-slate-800" 
         style="background: linear-gradient(135deg, #0A192F 0%, #1E3A8A 50%, #060D18 100%); color: #ffffff;">
    <!-- Ornamen Gelombang Samudra Halus -->
    <div class="absolute -right-16 -bottom-16 w-80 h-80 rounded-full bg-cyan-500/10 blur-3xl pointer-events-none"></div>

    <div class="relative z-10 max-w-3xl space-y-4">
        <!-- Badge Identitas -->
        <div class="inline-flex items-center space-x-2 px-3.5 py-1 rounded-full text-xs font-semibold bg-white/10 backdrop-blur-md border border-white/20 text-cyan-300">
            <span class="w-2 h-2 rounded-full bg-cyan-400 animate-pulse"></span>
            <span>Kelautan.ID &bull; X-Media Extractor</span>
        </div>

        <!-- Heading Display (Outfit Font) -->
        <h1 class="text-2xl sm:text-4xl font-extrabold font-heading text-white tracking-tight leading-tight">
            Ekstrak Media X/Twitter <span class="text-cyan-400">Kualitas Tertinggi</span>
        </h1>

        <!-- Deskripsi Singkat & Akurat -->
        <p class="text-sm sm:text-base text-slate-200 leading-relaxed font-normal max-w-2xl">
            Tempelkan tautan unggahan X/Twitter untuk mendapatkan direct URL video MP4, GIF animasi, dan foto resolusi asli (<code class="px-1.5 py-0.5 rounded bg-black/40 text-cyan-300 font-mono text-xs">name=orig</code>), lalu unduh ke perangkat atau simpan ke server internal.
        </p>

        <!-- Formulir Input URL Ekstraksi -->
        <form method="post" action="/extract" data-loading="Mengekstrak Media…" class="pt-2">
            <?= csrf_field() ?>
            <div class="bg-white p-2 rounded-2xl shadow-xl flex flex-col sm:flex-row gap-2 border border-slate-200">
                <div class="relative flex-1 flex items-center">
                    <i data-lucide="link-2" class="w-5 h-5 text-slate-400 absolute left-4 pointer-events-none"></i>
                    <input type="text" name="url" value="<?= e($old) ?>" 
                           placeholder="https://x.com/username/status/..." 
                           required maxlength="500" autocomplete="off" autofocus 
                           aria-label="URL unggahan X/Twitter" inputmode="url"
                           class="w-full pl-11 pr-4 py-3 text-sm sm:text-base rounded-xl text-slate-900 placeholder-slate-400 bg-transparent border-0 focus:ring-2 focus:ring-cyan-500 focus:outline-none font-medium">
                </div>
                <button type="submit" 
                        class="inline-flex items-center justify-center space-x-2 px-6 py-3.5 rounded-xl font-bold text-sm sm:text-base text-slate-950 bg-cyan-400 hover:bg-cyan-300 active:scale-95 shadow-md shadow-cyan-400/25 transition flex-shrink-0 cursor-pointer">
                    <i data-lucide="sparkles" class="w-4 h-4 text-slate-950"></i>
                    <span>Ekstrak Media</span>
                </button>
            </div>
        </form>

        <!-- Indikator Fitur Nyata Sesuai PRD -->
        <div class="flex flex-wrap gap-2 pt-2 text-xs">
            <span class="inline-flex items-center space-x-1.5 px-3 py-1 rounded-full bg-slate-800/80 border border-slate-700 text-slate-200">
                <i data-lucide="film" class="w-3.5 h-3.5 text-cyan-400"></i>
                <span>MP4 Kualitas Tertinggi</span>
            </span>
            <span class="inline-flex items-center space-x-1.5 px-3 py-1 rounded-full bg-slate-800/80 border border-slate-700 text-slate-200">
                <i data-lucide="image" class="w-3.5 h-3.5 text-cyan-400"></i>
                <span>Foto Original (orig)</span>
            </span>
            <span class="inline-flex items-center space-x-1.5 px-3 py-1 rounded-full bg-slate-800/80 border border-slate-700 text-slate-200">
                <i data-lucide="hard-drive" class="w-3.5 h-3.5 text-cyan-400"></i>
                <span>Simpan ke Server</span>
            </span>
        </div>
    </div>
</section>

<!-- RIWAYAT EKSTRAKSI TERBARU (Data Nyata Database) -->
<?php if ($recent): ?>
    <section class="space-y-4 pt-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl sm:text-2xl font-bold font-heading text-slate-900">Ekstraksi Terakhir</h2>
                <p class="text-xs sm:text-sm text-slate-500">Riwayat unggahan yang tersimpan pada sistem.</p>
            </div>
            <a href="/archive" class="inline-flex items-center space-x-1 text-xs font-semibold text-maritime-700 hover:text-cyan-600 transition">
                <span>Video Terunduh</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <?php foreach ($recent as $t): ?>
                <div class="group rounded-2xl bg-white border border-slate-200/90 shadow-xs hover:shadow-md hover:border-cyan-300 transition-all flex flex-col overflow-hidden relative">
                    <!-- Thumbnail preview -->
                    <a href="/result/<?= e($t['tweet_id']) ?>" class="aspect-video w-full bg-slate-900 relative overflow-hidden flex items-center justify-center">
                        <?php if (!empty($t['thumb'])): ?>
                            <img src="<?= e($t['thumb']) ?>" alt="" loading="lazy" referrerpolicy="no-referrer" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        <?php else: ?>
                            <div class="flex flex-col items-center justify-center text-slate-600">
                                <i data-lucide="film" class="w-8 h-8 mb-1 text-slate-500"></i>
                                <span class="text-[11px] font-medium text-slate-400">Media Preview</span>
                            </div>
                        <?php endif; ?>
                        <span class="absolute top-2.5 right-2.5 inline-flex items-center space-x-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-abyssal-900/80 backdrop-blur-md text-cyan-300 border border-cyan-500/30">
                            <i data-lucide="layers" class="w-3 h-3"></i>
                            <span><?= (int) $t['media_count'] ?> media</span>
                        </span>
                    </a>

                    <div class="p-4 flex flex-col flex-1 justify-between space-y-2">
                        <div>
                            <div class="flex items-center justify-between text-xs font-bold text-slate-900">
                                <a href="/result/<?= e($t['tweet_id']) ?>" class="text-maritime-700 hover:underline">
                                    @<?= e($t['author_handle'] ?? 'unknown') ?>
                                </a>
                                <span class="text-slate-400 font-normal truncate max-w-[120px]"><?= e($t['author_name'] ?? '') ?></span>
                            </div>
                            <p class="text-xs text-slate-600 mt-1 line-clamp-2 leading-relaxed">
                                <?= e($t['tweet_text'] ?? 'Tidak ada deskripsi teks.') ?>
                            </p>
                        </div>

                        <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
                            <span>ID: <?= e($t['tweet_id']) ?></span>
                            
                            <div class="flex items-center space-x-2">
                                <a href="/result/<?= e($t['tweet_id']) ?>" class="text-maritime-600 font-semibold hover:text-cyan-600 transition flex items-center space-x-1">
                                    <span>Lihat</span>
                                    <i data-lucide="chevron-right" class="w-3 h-3"></i>
                                </a>

                                <!-- Menu Hapus Riwayat untuk Super Admin & Moderator (Item 1) -->
                                <?php if (can_moderate()): ?>
                                    <form method="post" action="/extract/delete/<?= e($t['tweet_id']) ?>" 
                                          data-confirm="Hapus riwayat ekstraksi tweet ini beserta berkasnya dari server?" class="inline m-0">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="return" value="/">
                                        <button type="submit" class="p-1 rounded-md text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition" title="Hapus Riwayat (Super Admin / Moderator)">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>
