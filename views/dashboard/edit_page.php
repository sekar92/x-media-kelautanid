<?php
/**
 * views/dashboard/edit_page.php
 * Editor form for Regulation Documents (Item 10)
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
    <a href="/admin/pages" class="hover:text-cyan-600 transition">Dokumen Regulasi</a>
    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400"></i>
    <span class="text-maritime-700">Edit /<?= e($page['slug']) ?></span>
</nav>

<!-- Page Header Titling (High Contrast Dark Text) -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full text-xs font-bold bg-cyan-50 text-cyan-800 border border-cyan-200 mb-2">
            <i data-lucide="edit-3" class="w-3.5 h-3.5 text-cyan-600"></i>
            <span>Editor Regulasi /<?= e($page['slug']) ?></span>
        </div>
        <h1 class="text-2xl sm:text-3xl font-extrabold font-heading text-slate-900 tracking-tight">
            Edit <?= e($page['title']) ?>
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">
            Perubahan akan langsung terlihat secara instan oleh seluruh pengunjung di tautan publik:
            <a href="/<?= e($page['slug']) ?>" target="_blank" class="text-maritime-700 font-semibold underline underline-offset-2 hover:text-cyan-600">
                kelautan.id/<?= e($page['slug']) ?>
            </a>
        </p>
    </div>

    <div class="flex items-center space-x-2">
        <a href="/admin/pages" class="inline-flex items-center space-x-1.5 px-4 py-2.5 rounded-xl font-bold text-xs text-slate-700 bg-white border border-slate-200/90 hover:bg-slate-50 shadow-xs transition">
            <i data-lucide="arrow-left" class="w-4 h-4 text-slate-500"></i>
            <span>Kembali ke Daftar</span>
        </a>
    </div>
</div>

<!-- Edit Form (Light Theme) -->
<form action="/admin/pages/<?= e($page['slug']) ?>" method="POST" class="space-y-6">
    <?= csrf_field() ?>

    <div class="rounded-3xl bg-white border border-slate-200/90 p-6 sm:p-8 shadow-xs space-y-5">
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                Judul Dokumen Resmi
            </label>
            <input type="text"
                   name="title"
                   value="<?= e($page['title']) ?>"
                   required
                   class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 text-slate-900 font-heading font-bold text-base transition bg-slate-50/50">
        </div>

        <div>
            <div class="flex items-center justify-between mb-2">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                    Isi Konten Dokumen (Markdown & Plain Text)
                </label>
                <span class="text-[11px] text-slate-400">Tekan enter 2x untuk paragraf baru</span>
            </div>
            <textarea name="content"
                      rows="18"
                      required
                      class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 text-slate-800 font-mono text-xs sm:text-sm leading-relaxed transition bg-slate-50/50"><?= e($page['content']) ?></textarea>
        </div>

        <!-- Format Helper Card -->
        <div class="p-4 rounded-2xl bg-cyan-50/60 border border-cyan-100 text-xs text-slate-700 space-y-1.5">
            <div class="font-bold text-maritime-900 flex items-center space-x-1.5">
                <i data-lucide="info" class="w-4 h-4 text-cyan-600"></i>
                <span>Panduan Format Markdown:</span>
            </div>
            <p class="text-slate-600">• Gunakan <code class="px-1.5 py-0.5 rounded bg-white text-maritime-800 font-mono font-bold text-xs"># Judul</code> atau <code class="px-1.5 py-0.5 rounded bg-white text-maritime-800 font-mono font-bold text-xs">## Sub Judul</code> untuk heading klausul.</p>
            <p class="text-slate-600">• Gunakan angka <code class="px-1.5 py-0.5 rounded bg-white text-maritime-800 font-mono font-bold text-xs">1.</code>, <code class="px-1.5 py-0.5 rounded bg-white text-maritime-800 font-mono font-bold text-xs">2.</code> atau bullet <code class="px-1.5 py-0.5 rounded bg-white text-maritime-800 font-mono font-bold text-xs">-</code> untuk butir-butir ketentuan.</p>
            <p class="text-slate-600">• Gunakan <code class="px-1.5 py-0.5 rounded bg-white text-maritime-800 font-mono font-bold text-xs">**teks tebal**</code> untuk menegaskan kata kunci.</p>
        </div>
    </div>

    <!-- Actions -->
    <div class="flex items-center justify-end space-x-3">
        <a href="/admin/pages" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
            Batal
        </a>
        <button type="submit"
                class="inline-flex items-center space-x-2 px-6 py-2.5 rounded-xl bg-maritime-700 hover:bg-maritime-800 text-white font-bold text-xs shadow-md shadow-maritime-700/20 transition cursor-pointer">
            <i data-lucide="save" class="w-4 h-4"></i>
            <span>Simpan Perubahan Dokumen</span>
        </button>
    </div>
</form>
