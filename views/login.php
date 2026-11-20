<?php /** @var string $next */ ?>

<div class="max-w-md w-full mx-auto my-8 sm:my-12">
    <!-- Breadcrumbs -->
    <nav class="flex items-center space-x-2 text-xs font-semibold text-slate-500 mb-4">
        <a href="/" class="hover:text-cyan-600 transition flex items-center space-x-1">
            <i data-lucide="home" class="w-3.5 h-3.5"></i>
            <span>Beranda</span>
        </a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400"></i>
        <span class="text-maritime-700">Masuk Akun</span>
    </nav>

    <!-- Kartu Login -->
    <div class="bg-white rounded-3xl border border-slate-200/90 shadow-xl p-6 sm:p-8 space-y-6">
        <div class="text-center space-y-2">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-cyan-400 to-maritime-700 flex items-center justify-center text-white mx-auto shadow-md shadow-cyan-500/20">
                <i data-lucide="shield-check" class="w-7 h-7 text-white"></i>
            </div>
            <h1 class="text-2xl font-extrabold font-heading text-slate-900 tracking-tight">
                Masuk ke Akun Anda
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 leading-relaxed">
                Akses dashboard ekstraksi, simpan arsip ke server, dan kelola media Anda.
            </p>
        </div>

        <form method="post" action="/login" class="space-y-4">
            <?= csrf_field() ?>
            <input type="hidden" name="next" value="<?= e($next) ?>">

            <div class="space-y-1.5">
                <label for="username" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                    Username
                </label>
                <div class="relative">
                    <i data-lucide="user" class="w-4 h-4 text-slate-400 absolute left-3.5 top-3.5 pointer-events-none"></i>
                    <input id="username" name="username" required autocomplete="username" autofocus
                           placeholder="Masukkan username Anda"
                           class="w-full pl-10 pr-4 py-2.5 text-sm rounded-xl border border-slate-300 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 placeholder-slate-400 transition bg-white text-slate-800 font-medium">
                </div>
            </div>

            <div class="space-y-1.5">
                <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                    Kata Sandi
                </label>
                <div class="relative">
                    <i data-lucide="key" class="w-4 h-4 text-slate-400 absolute left-3.5 top-3.5 pointer-events-none"></i>
                    <input id="password" name="password" type="password" required autocomplete="current-password"
                           placeholder="••••••••"
                           class="w-full pl-10 pr-4 py-2.5 text-sm rounded-xl border border-slate-300 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 placeholder-slate-400 transition bg-white text-slate-800 font-medium">
                </div>
            </div>

            <div class="pt-2">
                <button type="submit" 
                        class="w-full inline-flex items-center justify-center space-x-2 py-3 rounded-xl font-bold text-sm text-white bg-maritime-700 hover:bg-maritime-800 active:scale-95 shadow-md shadow-maritime-700/25 transition">
                    <i data-lucide="log-in" class="w-4 h-4"></i>
                    <span>Masuk</span>
                </button>
            </div>
        </form>

        <div class="pt-4 border-t border-slate-100 text-center space-y-2">
            <p class="text-xs text-slate-500">
                Belum memiliki akun? 
                <a href="/register" class="font-bold text-maritime-700 hover:text-cyan-600 transition underline">
                    Daftar Akun Gratis (User Basic)
                </a>
            </p>
            <p class="text-[11px] text-slate-400">
                Akun User Basic mendapatkan kuota penyimpanan dan retensi 7 hari gratis.
            </p>
        </div>
    </div>
</div>
