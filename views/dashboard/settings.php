<?php
/**
 * views/dashboard/settings.php
 * Super Admin Application Settings & Storage Quota Sliders (Items 2, 3, 4, 7)
 * Touch-friendly Mobile/Tablet sliders for all user levels
 */
$guestMb = (int) ($settings['guest_storage_mb'] ?? 1024);
$basicMb = (int) ($settings['basic_storage_mb'] ?? 500);
$premiumMb = (int) ($settings['premium_storage_mb'] ?? 5120);
$moderatorMb = (int) ($settings['moderator_storage_mb'] ?? 10240);
$superadminMb = (int) ($settings['superadmin_storage_mb'] ?? 51200);

$guestMaxExtractions = (int) ($settings['guest_max_extractions'] ?? 5);
$basicRetention = (int) ($settings['basic_retention_days'] ?? 7);
$premiumRetention = (int) ($settings['premium_retention_days'] ?? 90);
?>
<!-- Breadcrumbs -->
<nav class="flex items-center space-x-2 text-xs font-semibold text-slate-500 mb-2">
    <a href="/" class="hover:text-cyan-600 transition flex items-center space-x-1">
        <i data-lucide="home" class="w-3.5 h-3.5"></i>
        <span>Beranda</span>
    </a>
    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400"></i>
    <a href="/dashboard" class="hover:text-cyan-600 transition">Dashboard</a>
    <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-slate-400"></i>
    <span class="text-maritime-700">Pengaturan Aplikasi</span>
</nav>

<!-- Page Header Titling -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full text-xs font-bold bg-cyan-50 text-cyan-800 border border-cyan-200 mb-2">
            <i data-lucide="settings" class="w-3.5 h-3.5 text-cyan-600"></i>
            <span>Super Admin Panel</span>
        </div>
        <h1 class="text-2xl sm:text-3xl font-extrabold font-heading text-slate-900 tracking-tight">
            Pengaturan Aplikasi &amp; Alokasi Server
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">
            Konfigurasi ruang penyimpanan semua level pengguna, retensi otomatis berkas, limit tamu, dan integrasi WhatsApp.
        </p>
    </div>

    <div class="flex items-center space-x-2">
        <a href="/dashboard" class="inline-flex items-center space-x-1.5 px-4 py-2.5 rounded-xl font-bold text-xs text-slate-700 bg-white border border-slate-200/90 hover:bg-slate-50 shadow-xs transition">
            <i data-lucide="arrow-left" class="w-4 h-4 text-slate-500"></i>
            <span>Kembali ke Dashboard</span>
        </a>
    </div>
</div>

<!-- Settings Form (Touch-Friendly Responsive Sliders) -->
<form action="/admin/settings" method="POST" class="space-y-6 max-w-5xl mx-auto">
    <?= csrf_field() ?>

    <!-- SECTION 1: Alokasi Ruang Penyimpanan Semua Level User (Item 2 & 3) -->
    <div class="rounded-3xl bg-white border border-slate-200/90 p-6 sm:p-8 shadow-xs space-y-6">
        <div class="flex items-start space-x-4 border-b border-slate-100 pb-5">
            <div class="w-12 h-12 rounded-2xl bg-cyan-50 border border-cyan-200 flex items-center justify-center text-maritime-700 flex-shrink-0 shadow-xs">
                <i data-lucide="hard-drive" class="w-6 h-6"></i>
            </div>
            <div>
                <h2 class="text-lg font-extrabold font-heading text-slate-900">Alokasi Ruang Penyimpanan (Semua Tingkat Pengguna)</h2>
                <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                    Gunakan slider layar sentuh atau masukkan angka langsung untuk menentukan batas kuota arsip per level pengguna.
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            <!-- 1. Tamu / Unregistered -->
            <div class="p-5 rounded-2xl bg-slate-50/70 border border-slate-200/80 flex flex-col justify-between space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-slate-400"></span>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">Tamu (Unregistered)</h3>
                    </div>
                    <span id="badge_guest" class="px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-xs font-mono font-bold text-slate-800">
                        <?= $guestMb ?> MB
                    </span>
                </div>

                <div class="space-y-2">
                    <input type="range" id="range_guest" min="100" max="5000" step="50" value="<?= $guestMb ?>"
                           class="w-full h-3 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-cyan-600 touch-pan-x">
                    <div class="flex items-center justify-between text-[11px] text-slate-400 font-mono">
                        <span>Min: 100 MB</span>
                        <span>Max: 5.000 MB (5 GB)</span>
                    </div>
                </div>

                <div class="pt-2 border-t border-slate-200/70 flex items-center justify-between">
                    <label class="text-xs text-slate-500 font-medium">Nilai Input (MB):</label>
                    <input type="number" id="input_guest" name="guest_storage_mb" value="<?= $guestMb ?>" min="100" max="100000" required
                           class="w-28 px-2.5 py-1.5 rounded-xl border border-slate-300 text-xs font-mono font-bold text-slate-900 text-right bg-white focus:border-cyan-500">
                </div>
            </div>

            <!-- 2. User Basic -->
            <div class="p-5 rounded-2xl bg-slate-50/70 border border-slate-200/80 flex flex-col justify-between space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-cyan-500"></span>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-cyan-900">User Basic</h3>
                    </div>
                    <span id="badge_basic" class="px-2.5 py-1 rounded-lg bg-cyan-50 border border-cyan-200 text-xs font-mono font-bold text-cyan-900">
                        <?= $basicMb ?> MB
                    </span>
                </div>

                <div class="space-y-2">
                    <input type="range" id="range_basic" min="100" max="10000" step="100" value="<?= $basicMb ?>"
                           class="w-full h-3 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-cyan-600 touch-pan-x">
                    <div class="flex items-center justify-between text-[11px] text-slate-400 font-mono">
                        <span>Min: 100 MB</span>
                        <span>Max: 10.000 MB (10 GB)</span>
                    </div>
                </div>

                <div class="pt-2 border-t border-slate-200/70 flex items-center justify-between">
                    <label class="text-xs text-slate-500 font-medium">Nilai Input (MB):</label>
                    <input type="number" id="input_basic" name="basic_storage_mb" value="<?= $basicMb ?>" min="50" max="100000" required
                           class="w-28 px-2.5 py-1.5 rounded-xl border border-slate-300 text-xs font-mono font-bold text-slate-900 text-right bg-white focus:border-cyan-500">
                </div>
            </div>

            <!-- 3. User Premium -->
            <div class="p-5 rounded-2xl bg-cyan-50/60 border border-cyan-200 flex flex-col justify-between space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-cyan-600"></span>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-maritime-900">User Premium</h3>
                    </div>
                    <span id="badge_premium" class="px-2.5 py-1 rounded-lg bg-white border border-cyan-300 text-xs font-mono font-bold text-maritime-900">
                        <?= $premiumMb ?> MB
                    </span>
                </div>

                <div class="space-y-2">
                    <input type="range" id="range_premium" min="500" max="50000" step="500" value="<?= $premiumMb ?>"
                           class="w-full h-3 bg-cyan-200/80 rounded-lg appearance-none cursor-pointer accent-maritime-700 touch-pan-x">
                    <div class="flex items-center justify-between text-[11px] text-cyan-800 font-mono">
                        <span>Min: 500 MB</span>
                        <span>Max: 50.000 MB (50 GB)</span>
                    </div>
                </div>

                <div class="pt-2 border-t border-cyan-200 flex items-center justify-between">
                    <label class="text-xs text-maritime-800 font-medium">Nilai Input (MB):</label>
                    <input type="number" id="input_premium" name="premium_storage_mb" value="<?= $premiumMb ?>" min="500" max="200000" required
                           class="w-28 px-2.5 py-1.5 rounded-xl border border-cyan-300 text-xs font-mono font-bold text-slate-900 text-right bg-white focus:border-maritime-700">
                </div>
            </div>

            <!-- 4. Moderator (Item 2) -->
            <div class="p-5 rounded-2xl bg-amber-50/60 border border-amber-200 flex flex-col justify-between space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-amber-900">Moderator</h3>
                    </div>
                    <span id="badge_moderator" class="px-2.5 py-1 rounded-lg bg-white border border-amber-300 text-xs font-mono font-bold text-amber-900">
                        <?= $moderatorMb ?> MB
                    </span>
                </div>

                <div class="space-y-2">
                    <input type="range" id="range_moderator" min="1000" max="100000" step="1000" value="<?= $moderatorMb ?>"
                           class="w-full h-3 bg-amber-200/80 rounded-lg appearance-none cursor-pointer accent-amber-600 touch-pan-x">
                    <div class="flex items-center justify-between text-[11px] text-amber-800 font-mono">
                        <span>Min: 1.000 MB</span>
                        <span>Max: 100.000 MB (100 GB)</span>
                    </div>
                </div>

                <div class="pt-2 border-t border-amber-200 flex items-center justify-between">
                    <label class="text-xs text-amber-800 font-medium">Nilai Input (MB):</label>
                    <input type="number" id="input_moderator" name="moderator_storage_mb" value="<?= $moderatorMb ?>" min="500" max="200000" required
                           class="w-28 px-2.5 py-1.5 rounded-xl border border-amber-300 text-xs font-mono font-bold text-slate-900 text-right bg-white focus:border-amber-600">
                </div>
            </div>

            <!-- 5. Super Admin (Item 2) -->
            <div class="p-5 rounded-2xl bg-purple-50/60 border border-purple-200 flex flex-col justify-between space-y-4 md:col-span-2 lg:col-span-2">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-purple-600"></span>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-purple-900">Super Admin</h3>
                    </div>
                    <span id="badge_superadmin" class="px-2.5 py-1 rounded-lg bg-white border border-purple-300 text-xs font-mono font-bold text-purple-900">
                        <?= $superadminMb ?> MB
                    </span>
                </div>

                <div class="space-y-2">
                    <input type="range" id="range_superadmin" min="1000" max="200000" step="1000" value="<?= $superadminMb ?>"
                           class="w-full h-3 bg-purple-200/80 rounded-lg appearance-none cursor-pointer accent-purple-600 touch-pan-x">
                    <div class="flex items-center justify-between text-[11px] text-purple-800 font-mono">
                        <span>Min: 1.000 MB (1 GB)</span>
                        <span>Max: 200.000 MB (200 GB)</span>
                    </div>
                </div>

                <div class="pt-2 border-t border-purple-200 flex items-center justify-between">
                    <span class="text-xs text-purple-800 font-medium">Kapasitas penyimpanan arsip bebas untuk akun operasional Super Admin</span>
                    <input type="number" id="input_superadmin" name="superadmin_storage_mb" value="<?= $superadminMb ?>" min="1000" max="500000" required
                           class="w-28 px-2.5 py-1.5 rounded-xl border border-purple-300 text-xs font-mono font-bold text-slate-900 text-right bg-white focus:border-purple-600">
                </div>
            </div>
        </div>
    </div>

    <!-- SECTION 2: Retensi Otomatis & Limit Ekstraksi (Item 3 Slider) -->
    <div class="rounded-3xl bg-white border border-slate-200/90 p-6 sm:p-8 shadow-xs space-y-6">
        <div class="flex items-start space-x-4 border-b border-slate-100 pb-5">
            <div class="w-12 h-12 rounded-2xl bg-amber-50 border border-amber-200 flex items-center justify-center text-amber-700 flex-shrink-0 shadow-xs">
                <i data-lucide="clock" class="w-6 h-6"></i>
            </div>
            <div>
                <h2 class="text-lg font-extrabold font-heading text-slate-900">Masa Retensi Otomatis &amp; Limit Ekstraksi</h2>
                <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                    Atur masa berlaku berkas sebelum auto-purge dan batas ekstraksi untuk pengguna tanpa akun.
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <!-- Limit Ekstraksi Tamu -->
            <div class="p-5 rounded-2xl bg-slate-50/70 border border-slate-200/80 flex flex-col justify-between space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-700">Limit Tamu</span>
                    <span id="badge_guest_limit" class="px-2.5 py-1 rounded-lg bg-white border border-slate-200 text-xs font-mono font-bold text-slate-800">
                        <?= $guestMaxExtractions ?> Kali
                    </span>
                </div>
                <div class="space-y-2">
                    <input type="range" id="range_guest_limit" min="1" max="50" step="1" value="<?= $guestMaxExtractions ?>"
                           class="w-full h-3 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-cyan-600 touch-pan-x">
                    <div class="flex items-center justify-between text-[11px] text-slate-400 font-mono">
                        <span>1x</span>
                        <span>50x</span>
                    </div>
                </div>
                <div class="pt-2 border-t border-slate-200/70 flex items-center justify-between">
                    <label class="text-xs text-slate-500 font-medium">Maksimal (kali):</label>
                    <input type="number" id="input_guest_limit" name="guest_max_extractions" value="<?= $guestMaxExtractions ?>" min="1" max="100" required
                           class="w-20 px-2.5 py-1.5 rounded-xl border border-slate-300 text-xs font-mono font-bold text-slate-900 text-right bg-white focus:border-cyan-500">
                </div>
            </div>

            <!-- Retensi File User Basic -->
            <div class="p-5 rounded-2xl bg-slate-50/70 border border-slate-200/80 flex flex-col justify-between space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-cyan-900">Retensi Basic</span>
                    <span id="badge_basic_retention" class="px-2.5 py-1 rounded-lg bg-cyan-50 border border-cyan-200 text-xs font-mono font-bold text-cyan-900">
                        <?= $basicRetention ?> Hari
                    </span>
                </div>
                <div class="space-y-2">
                    <input type="range" id="range_basic_retention" min="1" max="30" step="1" value="<?= $basicRetention ?>"
                           class="w-full h-3 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-cyan-600 touch-pan-x">
                    <div class="flex items-center justify-between text-[11px] text-slate-400 font-mono">
                        <span>1 Hari</span>
                        <span>30 Hari</span>
                    </div>
                </div>
                <div class="pt-2 border-t border-slate-200/70 flex items-center justify-between">
                    <label class="text-xs text-slate-500 font-medium">Masa Simpan (Hari):</label>
                    <input type="number" id="input_basic_retention" name="basic_retention_days" value="<?= $basicRetention ?>" min="1" max="365" required
                           class="w-20 px-2.5 py-1.5 rounded-xl border border-slate-300 text-xs font-mono font-bold text-slate-900 text-right bg-white focus:border-cyan-500">
                </div>
            </div>

            <!-- Retensi File User Premium -->
            <div class="p-5 rounded-2xl bg-cyan-50/60 border border-cyan-200 flex flex-col justify-between space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-maritime-900">Retensi Premium</span>
                    <span id="badge_premium_retention" class="px-2.5 py-1 rounded-lg bg-white border border-cyan-300 text-xs font-mono font-bold text-maritime-900">
                        <?= $premiumRetention ?> Hari
                    </span>
                </div>
                <div class="space-y-2">
                    <input type="range" id="range_premium_retention" min="7" max="365" step="1" value="<?= $premiumRetention ?>"
                           class="w-full h-3 bg-cyan-200/80 rounded-lg appearance-none cursor-pointer accent-maritime-700 touch-pan-x">
                    <div class="flex items-center justify-between text-[11px] text-cyan-800 font-mono">
                        <span>7 Hari</span>
                        <span>365 Hari (1 Thn)</span>
                    </div>
                </div>
                <div class="pt-2 border-t border-cyan-200 flex items-center justify-between">
                    <label class="text-xs text-maritime-800 font-medium">Masa Simpan (Hari):</label>
                    <input type="number" id="input_premium_retention" name="premium_retention_days" value="<?= $premiumRetention ?>" min="7" max="3650" required
                           class="w-20 px-2.5 py-1.5 rounded-xl border border-cyan-300 text-xs font-mono font-bold text-slate-900 text-right bg-white focus:border-maritime-700">
                </div>
            </div>
        </div>
    </div>

    <!-- SECTION 3: WhatsApp Manual Payment Settings (Item 7) -->
    <div class="rounded-3xl bg-white border border-slate-200/90 p-6 sm:p-8 shadow-xs space-y-6">
        <div class="flex items-start space-x-4 border-b border-slate-100 pb-5">
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 border border-emerald-200 flex items-center justify-center text-emerald-700 flex-shrink-0 shadow-xs">
                <i data-lucide="message-circle" class="w-6 h-6"></i>
            </div>
            <div>
                <h2 class="text-lg font-extrabold font-heading text-slate-900">Konfigurasi Pembayaran Manual WhatsApp</h2>
                <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                    Nomor WhatsApp tujuan konfirmasi pembayaran dan format pesan otomatis saat User Basic menekan tombol Upgrade.
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                    Nomor WhatsApp Penerima (Format Internasional)
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i data-lucide="phone" class="w-4 h-4"></i>
                    </span>
                    <input type="text"
                           name="wa_number"
                           value="<?= e($settings['wa_number'] ?? '6281234567890') ?>"
                           required
                           placeholder="Contoh: 6281234567890"
                           class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-300 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 text-sm text-slate-900 font-mono transition bg-slate-50/50">
                </div>
                <p class="text-[11px] text-slate-500 mt-1.5">Gunakan kode negara tanpa simbol +, contoh: 6281234567890.</p>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                    Label Harga Paket Premium
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i data-lucide="tag" class="w-4 h-4"></i>
                    </span>
                    <input type="text"
                           name="premium_price"
                           value="<?= e($settings['premium_price'] ?? 'Rp 25.000 / bulan') ?>"
                           required
                           placeholder="Contoh: Rp 25.000 / bulan"
                           class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-300 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 text-sm text-slate-900 transition bg-slate-50/50">
                </div>
                <p class="text-[11px] text-slate-500 mt-1.5">Teks harga yang ditampilkan pada halaman penawaran upgrade.</p>
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                    Template Pesan WhatsApp Otomatis
                </label>
                <textarea name="wa_message"
                          rows="3"
                          required
                          placeholder="Halo Admin Kelautan.ID, saya ingin upgrade..."
                          class="w-full px-4 py-2.5 rounded-xl border border-slate-300 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 text-sm text-slate-900 transition bg-slate-50/50 leading-relaxed"><?= e($settings['wa_message'] ?? 'Halo Admin Kelautan.ID, saya ingin upgrade ke User Premium untuk akun: {username}. Mohon informasi pembayaran.') ?></textarea>
                <p class="text-[11px] text-slate-500 mt-1.5">
                    Gunakan placeholder <code class="px-1.5 py-0.5 rounded bg-slate-100 text-maritime-800 font-mono text-xs font-bold">{username}</code> untuk otomatis menyisipkan username pemohon.
                </p>
            </div>
        </div>
    </div>

    <!-- Submit Button -->
    <div class="flex items-center justify-end pt-2">
        <button type="submit"
                class="inline-flex items-center space-x-2 px-8 py-3.5 rounded-xl bg-maritime-700 hover:bg-maritime-800 text-white font-bold text-sm shadow-md shadow-maritime-700/20 active:scale-95 transition cursor-pointer">
            <i data-lucide="save" class="w-4 h-4"></i>
            <span>Simpan Seluruh Pengaturan</span>
        </button>
    </div>
</form>

<!-- Vanilla JS for Live Slider Synchronization (Mobile & Tablet Friendly) -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const pairs = [
        { range: 'range_guest', input: 'input_guest', badge: 'badge_guest', unit: 'MB', isStorage: true },
        { range: 'range_basic', input: 'input_basic', badge: 'badge_basic', unit: 'MB', isStorage: true },
        { range: 'range_premium', input: 'input_premium', badge: 'badge_premium', unit: 'MB', isStorage: true },
        { range: 'range_moderator', input: 'input_moderator', badge: 'badge_moderator', unit: 'MB', isStorage: true },
        { range: 'range_superadmin', input: 'input_superadmin', badge: 'badge_superadmin', unit: 'MB', isStorage: true },
        { range: 'range_guest_limit', input: 'input_guest_limit', badge: 'badge_guest_limit', unit: 'Kali', isStorage: false },
        { range: 'range_basic_retention', input: 'input_basic_retention', badge: 'badge_basic_retention', unit: 'Hari', isStorage: false },
        { range: 'range_premium_retention', input: 'input_premium_retention', badge: 'badge_premium_retention', unit: 'Hari', isStorage: false },
    ];

    function formatStorage(mb) {
        if (mb >= 1024) {
            const gb = (mb / 1024).toFixed(1).replace('.0', '');
            return mb.toLocaleString('id-ID') + ' MB (' + gb + ' GB)';
        }
        return mb.toLocaleString('id-ID') + ' MB';
    }

    pairs.forEach(function (p) {
        const r = document.getElementById(p.range);
        const inp = document.getElementById(p.input);
        const b = document.getElementById(p.badge);
        if (!r || !inp || !b) return;

        function updateBadge(val) {
            const num = parseInt(val, 10) || 0;
            if (p.isStorage) {
                b.textContent = formatStorage(num);
            } else {
                b.textContent = num.toLocaleString('id-ID') + ' ' + p.unit;
            }
        }

        r.addEventListener('input', function () {
            inp.value = r.value;
            updateBadge(r.value);
        });

        inp.addEventListener('input', function () {
            r.value = inp.value;
            updateBadge(inp.value);
        });

        updateBadge(inp.value);
    });
});
</script>
