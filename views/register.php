<?php /** @var array $old */ ?>

<div class="max-w-md w-full mx-auto my-8 sm:my-12">
    <!-- Breadcrumbs -->
    <nav class="flex items-center space-x-2 text-xs font-semibold text-slate-500 mb-4">
        <a href="/" class="hover:text-cyan-600 transition flex items-center space-x-1">
            <i data-lucide="home" class="w-3.5 h-3.5"></i>
            <span>Beranda</span>
        </a>
        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400"></i>
        <span class="text-maritime-700">Daftar Akun Baru</span>
    </nav>

    <!-- Kartu Register -->
    <div class="bg-white rounded-3xl border border-slate-200/90 shadow-xl p-6 sm:p-8 space-y-6">
        <div class="text-center space-y-2">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-cyan-400 to-maritime-700 flex items-center justify-center text-white mx-auto shadow-md shadow-cyan-500/20">
                <i data-lucide="user-plus" class="w-7 h-7 text-white"></i>
            </div>
            <h1 class="text-2xl font-extrabold font-heading text-slate-900 tracking-tight">
                Registrasi Akun Gratis
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 leading-relaxed">
                Daftar sebagai <strong>User Basic</strong> untuk ekstraksi tanpa batas dan penyimpanan media server.
            </p>
        </div>

        <form method="post" action="/register" class="space-y-4">
            <?= csrf_field() ?>

            <div class="space-y-1.5">
                <label for="username" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                    Username <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <i data-lucide="user" class="w-4 h-4 text-slate-400 absolute left-3.5 top-3.5 pointer-events-none"></i>
                    <input id="username" name="username" value="<?= e($old['username'] ?? '') ?>" required autocomplete="username" autofocus
                           placeholder="3-30 karakter (huruf, angka, _)"
                           class="w-full pl-10 pr-4 py-2.5 text-sm rounded-xl border border-slate-300 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 placeholder-slate-400 transition bg-white text-slate-800 font-medium">
                </div>
            </div>

            <div class="space-y-1.5">
                <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                    Email <span class="text-slate-400 font-normal text-[11px]">(Opsional)</span>
                </label>
                <div class="relative">
                    <i data-lucide="mail" class="w-4 h-4 text-slate-400 absolute left-3.5 top-3.5 pointer-events-none"></i>
                    <input id="email" name="email" type="email" value="<?= e($old['email'] ?? '') ?>" autocomplete="email"
                           placeholder="nama@email.com"
                           class="w-full pl-10 pr-4 py-2.5 text-sm rounded-xl border border-slate-300 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 placeholder-slate-400 transition bg-white text-slate-800 font-medium">
                </div>
            </div>

            <div class="space-y-1.5">
                <label for="phone_wa" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                    Nomor WhatsApp / HP <span class="text-slate-400 font-normal text-[11px]">(Opsional)</span>
                </label>
                <div class="relative">
                    <i data-lucide="phone" class="w-4 h-4 text-slate-400 absolute left-3.5 top-3.5 pointer-events-none"></i>
                    <input id="phone_wa" name="phone_wa" type="tel" value="<?= e($old['phone_wa'] ?? '') ?>"
                           placeholder="081234567890"
                           class="w-full pl-10 pr-4 py-2.5 text-sm rounded-xl border border-slate-300 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 placeholder-slate-400 transition bg-white text-slate-800 font-medium">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div class="space-y-1.5">
                    <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                        Kata Sandi <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <i data-lucide="key" class="w-4 h-4 text-slate-400 absolute left-3.5 top-3.5 pointer-events-none"></i>
                        <input id="password" name="password" type="password" required autocomplete="new-password"
                               placeholder="Min. 8 karakter"
                               class="w-full pl-10 pr-4 py-2.5 text-sm rounded-xl border border-slate-300 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 placeholder-slate-400 transition bg-white text-slate-800 font-medium">
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label for="password_confirm" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                        Konfirmasi <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <i data-lucide="check" class="w-4 h-4 text-slate-400 absolute left-3.5 top-3.5 pointer-events-none"></i>
                        <input id="password_confirm" name="password_confirm" type="password" required autocomplete="new-password"
                               placeholder="Ulangi sandi"
                               class="w-full pl-10 pr-4 py-2.5 text-sm rounded-xl border border-slate-300 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 placeholder-slate-400 transition bg-white text-slate-800 font-medium">
                    </div>
                </div>
            </div>

            <p class="text-[11px] text-slate-500 leading-normal pt-1">
                Dengan mendaftar, Anda menyetujui <a href="/terms" class="text-maritime-700 underline font-semibold" target="_blank">Syarat &amp; Ketentuan</a> serta <a href="/rules" class="text-maritime-700 underline font-semibold" target="_blank">Aturan Layanan</a> Kelautan.ID.
            </p>

            <div class="pt-2">
                <button type="submit" 
                        class="w-full inline-flex items-center justify-center space-x-2 py-3 rounded-xl font-bold text-sm text-white bg-maritime-700 hover:bg-maritime-800 active:scale-95 shadow-md shadow-maritime-700/25 transition">
                    <i data-lucide="user-check" class="w-4 h-4"></i>
                    <span>Daftar Akun Sekarang</span>
                </button>
            </div>
        </form>

        <div class="pt-4 border-t border-slate-100 text-center">
            <p class="text-xs text-slate-500">
                Sudah memiliki akun? 
                <a href="/login" class="font-bold text-maritime-700 hover:text-cyan-600 transition underline">
                    Masuk di sini
                </a>
            </p>
        </div>
    </div>
</div>
