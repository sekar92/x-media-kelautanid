<?php /** @var array $user */ /** @var string $waLink */ /** @var string $waNumber */ /** @var string $price */ /** @var string $basicStorageMb */ /** @var string $premiumStorageMb */ /** @var string $basicRetention */ /** @var string $premiumRetention */ ?>

<!-- Breadcrumbs -->
<nav class="flex items-center space-x-2 text-xs font-semibold text-slate-500 mb-2">
    <a href="/" class="hover:text-cyan-600 transition flex items-center space-x-1">
        <i data-lucide="home" class="w-3.5 h-3.5"></i>
        <span>Beranda</span>
    </a>
    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400"></i>
    <a href="/dashboard" class="hover:text-cyan-600 transition">Dashboard</a>
    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400"></i>
    <span class="text-maritime-700">Upgrade ke User Premium</span>
</nav>

<div class="text-center max-w-2xl mx-auto space-y-3 py-4">
    <div class="inline-flex items-center space-x-2 px-3.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-800 border border-amber-300">
        <i data-lucide="crown" class="w-3.5 h-3.5 text-amber-600"></i>
        <span>Paket Keanggotaan Kelautan.ID</span>
    </div>
    <h1 class="text-3xl sm:text-4xl font-extrabold font-heading text-slate-900 tracking-tight">
        Tingkatkan ke <span class="text-maritime-700">User Premium</span>
    </h1>
    <p class="text-sm text-slate-600 leading-relaxed">
        Dapatkan ruang penyimpanan server yang jauh lebih lega dan masa simpan arsip video maritim hingga <strong><?= e($premiumRetention) ?> hari</strong>.
    </p>
</div>

<!-- Kartu Perbandingan Paket -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-4xl mx-auto pt-2">
    <!-- Paket Basic (Saat Ini) -->
    <div class="p-8 rounded-3xl bg-white border border-slate-200/90 shadow-xs flex flex-col justify-between space-y-6">
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Paket Gratis</span>
                    <h3 class="text-2xl font-extrabold font-heading text-slate-900">User Basic</h3>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600">
                    Akun Standar
                </span>
            </div>

            <div class="text-3xl font-extrabold font-heading text-slate-900">
                Rp 0 <span class="text-xs font-normal text-slate-400">/ selamanya</span>
            </div>

            <ul class="space-y-3 text-xs text-slate-600 pt-2 border-t border-slate-100">
                <li class="flex items-center space-x-2">
                    <i data-lucide="check" class="w-4 h-4 text-emerald-600 flex-shrink-0"></i>
                    <span>Kapasitas Simpan Server: <strong><?= e($basicStorageMb) ?> MB</strong></span>
                </li>
                <li class="flex items-center space-x-2">
                    <i data-lucide="check" class="w-4 h-4 text-emerald-600 flex-shrink-0"></i>
                    <span>Masa Retensi Berkas: <strong><?= e($basicRetention) ?> Hari</strong></span>
                </li>
                <li class="flex items-center space-x-2">
                    <i data-lucide="check" class="w-4 h-4 text-emerald-600 flex-shrink-0"></i>
                    <span>Ekstraksi Video MP4 Bitrate Tertinggi</span>
                </li>
                <li class="flex items-center space-x-2 text-slate-400">
                    <i data-lucide="minus" class="w-4 h-4 text-slate-300 flex-shrink-0"></i>
                    <span>Retensi jangka panjang (90 hari)</span>
                </li>
            </ul>
        </div>

        <button disabled class="w-full py-2.5 rounded-xl font-bold text-xs text-slate-400 bg-slate-100 cursor-not-allowed">
            Paket Anda Saat Ini
        </button>
    </div>

    <!-- Paket Premium (Unggulan) -->
    <div class="p-8 rounded-3xl bg-white border-2 border-cyan-400 shadow-xl flex flex-col justify-between space-y-6 relative overflow-hidden">
        <div class="absolute top-0 right-0 bg-gradient-to-l from-amber-400 to-amber-500 text-amber-950 font-bold text-[10px] uppercase tracking-wider px-4 py-1 rounded-bl-xl shadow-xs">
            Rekomendasi
        </div>

        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-cyan-600">Fitur Lengkap</span>
                    <h3 class="text-2xl font-extrabold font-heading text-slate-900 flex items-center space-x-1.5">
                        <span>User Premium</span>
                        <i data-lucide="crown" class="w-5 h-5 text-amber-500"></i>
                    </h3>
                </div>
            </div>

            <div class="text-3xl font-extrabold font-heading text-slate-900">
                <?= e($price) ?>
            </div>

            <ul class="space-y-3 text-xs text-slate-700 pt-2 border-t border-slate-100 font-medium">
                <li class="flex items-center space-x-2">
                    <i data-lucide="check-circle-2" class="w-4 h-4 text-cyan-600 flex-shrink-0"></i>
                    <span>Kapasitas Simpan Server: <strong><?= e(format_bytes((int)$premiumStorageMb * 1024 * 1024)) ?> (<?= e($premiumStorageMb) ?> MB)</strong></span>
                </li>
                <li class="flex items-center space-x-2">
                    <i data-lucide="check-circle-2" class="w-4 h-4 text-cyan-600 flex-shrink-0"></i>
                    <span>Masa Simpan &amp; Retensi: <strong><?= e($premiumRetention) ?> Hari</strong></span>
                </li>
                <li class="flex items-center space-x-2">
                    <i data-lucide="check-circle-2" class="w-4 h-4 text-cyan-600 flex-shrink-0"></i>
                    <span>Ekstraksi Tanpa Batas Kuota Harian</span>
                </li>
                <li class="flex items-center space-x-2">
                    <i data-lucide="check-circle-2" class="w-4 h-4 text-cyan-600 flex-shrink-0"></i>
                    <span>Streaming Video Seek Berkecepatan Tinggi</span>
                </li>
                <li class="flex items-center space-x-2">
                    <i data-lucide="check-circle-2" class="w-4 h-4 text-cyan-600 flex-shrink-0"></i>
                    <span>Dukungan Prioritas Komunitas Kelautan.ID</span>
                </li>
            </ul>
        </div>

        <!-- Tombol Konfirmasi WhatsApp (Item 7) -->
        <a href="<?= e($waLink) ?>" target="_blank" rel="noopener noreferrer" 
           class="w-full inline-flex items-center justify-center space-x-2 py-3 rounded-xl font-bold text-sm text-white bg-emerald-600 hover:bg-emerald-500 active:scale-95 shadow-md shadow-emerald-600/30 transition">
            <i data-lucide="message-circle" class="w-4 h-4"></i>
            <span>Konfirmasi Pembayaran via WhatsApp</span>
        </a>
    </div>
</div>

<!-- Panduan Pembayaran Manual via WA -->
<div class="max-w-2xl mx-auto p-6 rounded-2xl bg-white border border-slate-200 shadow-xs space-y-3 mt-4 text-xs text-slate-600">
    <h4 class="font-bold text-sm text-slate-800 font-heading flex items-center space-x-2">
        <i data-lucide="info" class="w-4 h-4 text-maritime-700"></i>
        <span>Cara Pembayaran Manual &amp; Aktivasi:</span>
    </h4>
    <ol class="list-decimal pl-5 space-y-1.5 leading-relaxed">
        <li>Klik tombol <strong>Konfirmasi Pembayaran via WhatsApp</strong> di atas.</li>
        <li>Kirim pesan konfirmasi otomatis yang telah menyertakan username akun Anda (<strong>@<?= e($user['username'] ?? 'username') ?></strong>).</li>
        <li>Admin Kelautan.ID (WhatsApp: <strong>+<?= e($waNumber) ?></strong>) akan mengirimkan detail rekening transfer.</li>
        <li>Kirimkan bukti transfer pembayaran Anda, dan status akun Anda akan langsung di-upgrade oleh Super Admin menjadi <strong>User Premium</strong>.</li>
    </ol>
</div>
