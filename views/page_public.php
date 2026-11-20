<?php
/**
 * views/page_public.php
 * Public Regulation Documents View (Item 10: ToS, Rules, Community Guidelines)
 * Clean Light Theme with High Contrast Typography
 */

// Helper to convert simple Markdown into styled HTML for light theme
function render_markdown_simple(string $text): string {
    $lines = explode("\n", $text);
    $html = [];
    $inList = false;

    foreach ($lines as $line) {
        $trimmed = trim($line);

        if ($trimmed === '') {
            if ($inList) {
                $html[] = '</ul>';
                $inList = false;
            }
            continue;
        }

        // Heading 3
        if (str_starts_with($trimmed, '### ')) {
            if ($inList) { $html[] = '</ul>'; $inList = false; }
            $h = htmlspecialchars(substr($trimmed, 4), ENT_QUOTES, 'UTF-8');
            $html[] = "<h3 class=\"text-lg font-heading font-extrabold text-slate-900 mt-6 mb-2\">{$h}</h3>";
            continue;
        }

        // Heading 2
        if (str_starts_with($trimmed, '## ')) {
            if ($inList) { $html[] = '</ul>'; $inList = false; }
            $h = htmlspecialchars(substr($trimmed, 3), ENT_QUOTES, 'UTF-8');
            $html[] = "<h2 class=\"text-xl font-heading font-extrabold text-maritime-800 mt-8 mb-3 pb-2 border-b border-slate-200\">{$h}</h2>";
            continue;
        }

        // Heading 1
        if (str_starts_with($trimmed, '# ')) {
            if ($inList) { $html[] = '</ul>'; $inList = false; }
            $h = htmlspecialchars(substr($trimmed, 2), ENT_QUOTES, 'UTF-8');
            $html[] = "<h1 class=\"text-2xl font-heading font-extrabold text-slate-900 mt-8 mb-4\">{$h}</h1>";
            continue;
        }

        // List item
        if (str_starts_with($trimmed, '- ') || str_starts_with($trimmed, '* ') || preg_match('/^\d+\.\s+/', $trimmed)) {
            if (!$inList) {
                $html[] = '<ul class=\"space-y-2 my-3 text-slate-700 text-sm leading-relaxed list-disc list-inside\">';
                $inList = true;
            }
            $clean = preg_replace('/^([*+-]|\d+\.)\s+/', '', $trimmed);
            $clean = htmlspecialchars($clean, ENT_QUOTES, 'UTF-8');
            // Bold parser
            $clean = preg_replace('/\*\*(.*?)\*\*/', '<strong class=\"text-slate-900 font-bold\">$1</strong>', $clean);
            $html[] = "<li class=\"text-slate-700\"><span>{$clean}</span></li>";
            continue;
        }

        if ($inList) {
            $html[] = '</ul>';
            $inList = false;
        }

        // Standard paragraph
        $p = htmlspecialchars($trimmed, ENT_QUOTES, 'UTF-8');
        $p = preg_replace('/\*\*(.*?)\*\*/', '<strong class=\"text-slate-900 font-bold\">$1</strong>', $p);
        $html[] = "<p class=\"text-slate-700 text-sm sm:text-base leading-relaxed my-3.5\">{$p}</p>";
    }

    if ($inList) {
        $html[] = '</ul>';
    }

    return implode("\n", $html);
}
?>
<!-- Breadcrumbs -->
<nav class="flex items-center space-x-2 text-xs font-semibold text-slate-500 mb-2">
    <a href="/" class="hover:text-cyan-600 transition flex items-center space-x-1">
        <i data-lucide="home" class="w-3.5 h-3.5"></i>
        <span>Beranda</span>
    </a>
    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400"></i>
    <span class="text-maritime-700"><?= e($page['title']) ?></span>
</nav>

<div class="max-w-4xl mx-auto space-y-6">

    <!-- Document Header & Body Card (Clean White Theme) -->
    <div class="rounded-3xl bg-white border border-slate-200/90 p-6 sm:p-10 shadow-xs relative">
        <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 pb-6 mb-6">
            <div>
                <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full text-xs font-bold bg-cyan-50 text-cyan-800 border border-cyan-200 mb-3">
                    <i data-lucide="shield-check" class="w-3.5 h-3.5 text-cyan-600"></i>
                    <span>Dokumen Resmi Kelautan.ID</span>
                </div>
                <h1 class="text-3xl sm:text-4xl font-extrabold font-heading text-slate-900 tracking-tight">
                    <?= e($page['title']) ?>
                </h1>
                <p class="text-xs text-slate-500 mt-2 flex items-center space-x-1.5">
                    <i data-lucide="clock" class="w-3.5 h-3.5 text-slate-400"></i>
                    <span>Terakhir diperbarui: <strong><?= date('d F Y, H:i', strtotime($page['updated_at'])) ?> WIB</strong></span>
                </p>
            </div>

            <!-- Quick Navigation between Regulation Pages -->
            <div class="flex flex-wrap items-center gap-2">
                <a href="/terms" class="px-3.5 py-2 rounded-xl text-xs font-bold transition <?= $page['slug'] === 'terms' ? 'bg-maritime-700 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                    Syarat & Ketentuan
                </a>
                <a href="/rules" class="px-3.5 py-2 rounded-xl text-xs font-bold transition <?= $page['slug'] === 'rules' ? 'bg-maritime-700 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                    Tata Tertib
                </a>
                <a href="/guidelines" class="px-3.5 py-2 rounded-xl text-xs font-bold transition <?= $page['slug'] === 'guidelines' ? 'bg-maritime-700 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?>">
                    Pedoman Komunitas
                </a>
            </div>
        </div>

        <!-- Rendered Document Body -->
        <div class="prose max-w-none text-slate-700">
            <?= render_markdown_simple($page['content']) ?>
        </div>

        <!-- Footer Notice -->
        <div class="mt-10 pt-6 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500">
            <div class="flex items-center space-x-2">
                <i data-lucide="info" class="w-4 h-4 text-cyan-600 flex-shrink-0"></i>
                <span>Kelautan.ID berhak memperbarui dokumen ini sewaktu-waktu sesuai ketentuan hukum dan norma maritim yang berlaku.</span>
            </div>
            <?php if (can_moderate()): ?>
                <a href="/admin/pages/<?= e($page['slug']) ?>" class="inline-flex items-center space-x-1.5 px-3.5 py-2 rounded-xl bg-amber-50 text-amber-800 hover:bg-amber-100 font-bold border border-amber-200 transition flex-shrink-0">
                    <i data-lucide="edit-3" class="w-3.5 h-3.5 text-amber-600"></i>
                    <span>Edit Halaman Ini</span>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Back to Extraction -->
    <div class="text-center pt-2">
        <a href="/" class="inline-flex items-center space-x-2 px-5 py-2.5 rounded-xl bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs border border-slate-200/90 shadow-xs transition">
            <i data-lucide="arrow-left" class="w-4 h-4 text-slate-500"></i>
            <span>Kembali ke Halaman Utama Ekstraktor</span>
        </a>
    </div>
</div>
