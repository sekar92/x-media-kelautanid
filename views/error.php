<?php /** @var int $code */ /** @var string $message */ ?>

<div class="max-w-lg w-full mx-auto my-12 p-8 sm:p-12 rounded-3xl bg-white border border-slate-200/90 shadow-xl text-center space-y-4">
    <div class="w-16 h-16 rounded-2xl bg-cyan-50 border border-cyan-100 text-maritime-700 flex items-center justify-center mx-auto shadow-xs">
        <i data-lucide="compass" class="w-8 h-8 text-maritime-700"></i>
    </div>
    
    <div>
        <span class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700 font-mono tracking-wider mb-2">
            GALAT HTTP <?= (int) $code ?>
        </span>
        <h1 class="text-3xl sm:text-4xl font-extrabold font-heading text-slate-900 tracking-tight">
            <?= (int) $code === 404 ? 'Halaman Tidak Ditemukan' : 'Terjadi Kendala Sistem' ?>
        </h1>
        <p class="text-sm text-slate-500 mt-2 leading-relaxed">
            <?= e($message) ?>
        </p>
    </div>

    <div class="pt-4 flex items-center justify-center space-x-3">
        <a href="/" class="inline-flex items-center space-x-2 px-5 py-2.5 rounded-xl font-bold text-sm text-white bg-maritime-700 hover:bg-maritime-800 active:scale-95 shadow-md shadow-maritime-700/20 transition">
            <i data-lucide="home" class="w-4 h-4"></i>
            <span>Kembali ke Beranda</span>
        </a>
        <a href="/archive" class="inline-flex items-center space-x-2 px-5 py-2.5 rounded-xl font-semibold text-sm text-slate-700 bg-white hover:bg-slate-50 border border-slate-300 transition">
            <i data-lucide="hard-drive" class="w-4 h-4"></i>
            <span>Buka Arsip</span>
        </a>
    </div>
</div>
