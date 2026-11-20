<?php
/**
 * views/dashboard/pages.php
 * Regulation Pages Management (Item 10: ToS, Rules, Community Guidelines)
 * Clean Light Theme matching Kelautan.ID design system
 */
?>
<!-- Breadcrumbs -->
<nav class="flex items-center space-x-2 text-xs font-semibold text-slate-500 mb-2">
    <a href="/" class="hover:text-cyan-600 transition flex items-center space-x-1">
        <i data-lucide="home" class="w-3.5 h-3.5"></i>
        <span>Beranda</span>
    </a>
    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400"></i>
    <a href="/admin/moderation" class="hover:text-cyan-600 transition">Moderasi</a>
    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400"></i>
    <span class="text-maritime-700">Dokumen Regulasi</span>
</nav>

<!-- Page Header Titling (High Contrast Dark Text) -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full text-xs font-bold bg-cyan-50 text-cyan-800 border border-cyan-200 mb-2">
            <i data-lucide="shield-check" class="w-3.5 h-3.5 text-cyan-600"></i>
            <span>Moderasi & Regulasi</span>
        </div>
        <h1 class="text-2xl sm:text-3xl font-extrabold font-heading text-slate-900 tracking-tight">
            Kelola Dokumen Regulasi
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">
            Perbarui konten Syarat & Ketentuan (ToS), Tata Tertib (Rules), dan Pedoman Komunitas secara dinamis.
        </p>
    </div>

    <div class="flex items-center space-x-2">
        <a href="/admin/moderation" class="inline-flex items-center space-x-1.5 px-4 py-2.5 rounded-xl font-bold text-xs text-slate-700 bg-white border border-slate-200/90 hover:bg-slate-50 shadow-xs transition">
            <i data-lucide="shield" class="w-4 h-4 text-slate-500"></i>
            <span>Dashboard Moderasi</span>
        </a>
    </div>
</div>

<!-- Pages List Cards (Light Theme Cards) -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <?php foreach ($pages as $p): ?>
        <?php
        $slug = $p['slug'];
        $icon = match($slug) {
            'terms' => 'scale',
            'rules' => 'book-open',
            'guidelines' => 'users-round',
            default => 'file-text',
        };
        ?>
        <div class="rounded-3xl bg-white border border-slate-200/90 p-6 shadow-xs flex flex-col justify-between hover:shadow-md hover:border-cyan-400 transition-all group">
            <div>
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-2xl bg-cyan-50 border border-cyan-100 flex items-center justify-center text-maritime-700 group-hover:scale-105 transition-transform">
                        <i data-lucide="<?= $icon ?>" class="w-6 h-6 text-maritime-700"></i>
                    </div>
                    <span class="px-2.5 py-1 rounded-lg bg-slate-100 border border-slate-200 font-mono text-[11px] font-semibold text-slate-700">
                        /<?= e($slug) ?>
                    </span>
                </div>

                <h3 class="text-lg font-extrabold font-heading text-slate-900 mb-2">
                    <?= e($p['title']) ?>
                </h3>
                <p class="text-xs text-slate-500 line-clamp-3 mb-4 leading-relaxed">
                    <?= e(mb_substr(strip_tags($p['content']), 0, 160)) ?>...
                </p>
            </div>

            <div class="pt-4 border-t border-slate-100 space-y-3">
                <div class="flex items-center justify-between text-[11px] text-slate-500">
                    <span>Pembaruan:</span>
                    <span class="font-medium text-slate-700"><?= date('d M Y, H:i', strtotime($p['updated_at'])) ?></span>
                </div>
                <?php if (!empty($p['editor_username'])): ?>
                    <div class="flex items-center justify-between text-[11px] text-slate-500">
                        <span>Editor:</span>
                        <span class="font-semibold text-cyan-700">@<?= e($p['editor_username']) ?></span>
                    </div>
                <?php endif; ?>

                <div class="grid grid-cols-2 gap-2 pt-2">
                    <a href="/<?= e($slug) ?>" target="_blank" class="inline-flex items-center justify-center space-x-1.5 px-3 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200/80 text-slate-700 text-xs font-bold transition">
                        <i data-lucide="external-link" class="w-3.5 h-3.5 text-slate-500"></i>
                        <span>Lihat Publik</span>
                    </a>
                    <a href="/admin/pages/<?= e($slug) ?>" class="inline-flex items-center justify-center space-x-1.5 px-3 py-2.5 rounded-xl bg-maritime-700 hover:bg-maritime-800 text-white text-xs font-bold shadow-xs transition">
                        <i data-lucide="edit-3" class="w-3.5 h-3.5 text-white"></i>
                        <span>Edit Konten</span>
                    </a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
