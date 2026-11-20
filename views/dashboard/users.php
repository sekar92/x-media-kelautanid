<?php
/**
 * views/dashboard/users.php
 * Super Admin Multi-Level User Management with Add, Edit, Delete (Items 2, 4, 5)
 * Clean Light Theme matching Kelautan.ID design system
 */
$currentUser = current_user();
$currentUserId = (int) ($currentUser['id'] ?? 0);
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
    <span class="text-maritime-700">Manajemen Pengguna</span>
</nav>

<!-- Page Header & Action Bar -->
<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
    <div>
        <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full text-xs font-bold bg-cyan-50 text-cyan-800 border border-cyan-200 mb-2">
            <i data-lucide="users" class="w-3.5 h-3.5 text-cyan-600"></i>
            <span>Super Admin Panel</span>
        </div>
        <h1 class="text-2xl sm:text-3xl font-extrabold font-heading text-slate-900 tracking-tight">
            Manajemen Pengguna
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">
            Tambah pengguna baru, ubah level akses &amp; kuota penyimpanan, atau hapus akun pengguna.
        </p>
    </div>

    <!-- Right Actions: Add User Button & Search Toolbar -->
    <div class="flex flex-wrap items-center gap-2.5">
        <button type="button" onclick="openAddModal()"
                class="inline-flex items-center space-x-2 px-4 py-2.5 rounded-xl bg-maritime-700 hover:bg-maritime-800 active:scale-95 text-white text-xs font-bold shadow-md shadow-maritime-700/20 transition cursor-pointer">
            <i data-lucide="user-plus" class="w-4 h-4"></i>
            <span>Tambah Pengguna</span>
        </button>

        <form action="/admin/users" method="GET" class="flex items-center gap-2">
            <div class="relative w-48 sm:w-60">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-2.5 pointer-events-none"></i>
                <input type="text"
                       name="q"
                       value="<?= e($q ?? '') ?>"
                       placeholder="Cari user / email / WA..."
                       class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-300 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 text-slate-900 bg-white placeholder-slate-400 transition">
            </div>
            <?php if (!empty($q)): ?>
                <a href="/admin/users" class="px-3 py-2 rounded-xl bg-slate-100 text-slate-600 hover:bg-slate-200 text-xs font-semibold transition" title="Reset pencarian">
                    Reset
                </a>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Users Table Card -->
<div class="rounded-3xl bg-white border border-slate-200/90 overflow-hidden shadow-xs">
    <div class="p-4 sm:p-5 border-b border-slate-200 flex items-center justify-between bg-slate-50/50">
        <span class="text-xs font-bold text-slate-700">
            Total Pengguna Terdaftar: <strong class="text-maritime-800"><?= (int) $total ?></strong>
        </span>
        <span class="text-xs text-slate-500">Halaman <?= (int) $page ?> dari <?= (int) $pages ?></span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs min-w-[700px]">
            <thead>
                <tr class="bg-slate-50 text-slate-500 uppercase tracking-wider font-bold text-[11px] border-b border-slate-200">
                    <th class="py-3.5 px-5">Pengguna</th>
                    <th class="py-3.5 px-5">Kontak</th>
                    <th class="py-3.5 px-5">Level Akses (Role)</th>
                    <th class="py-3.5 px-5">Penyimpanan Terpakai</th>
                    <th class="py-3.5 px-5">Terdaftar</th>
                    <th class="py-3.5 px-5 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-800">
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="6" class="py-12 text-center text-slate-500">
                            <i data-lucide="user-x" class="w-8 h-8 mx-auto mb-2 opacity-40 text-slate-400"></i>
                            Tidak ada data pengguna yang sesuai kriteria pencarian.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $u): ?>
                        <?php
                        $role = $u['role'] ?? 'basic';
                        $storageUsed = (int) ($u['storage_used_bytes'] ?? 0);
                        $storageLimitMb = (int) ($u['storage_limit_mb'] ?? 0);
                        $isSelf = (int)$u['id'] === $currentUserId;
                        $badgeClass = match($role) {
                            'superadmin' => 'bg-purple-50 text-purple-800 border-purple-200',
                            'moderator'  => 'bg-amber-50 text-amber-800 border-amber-200',
                            'premium'    => 'bg-cyan-50 text-cyan-800 border-cyan-200',
                            default      => 'bg-slate-100 text-slate-700 border-slate-200',
                        };
                        ?>
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <!-- User info -->
                            <td class="py-4 px-5">
                                <div class="flex items-center space-x-3">
                                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-cyan-400 to-maritime-700 flex items-center justify-center font-heading font-extrabold text-white text-xs shadow-xs flex-shrink-0">
                                        <?= strtoupper(substr($u['username'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-900 text-sm flex items-center space-x-1.5">
                                            <span>@<?= e($u['username']) ?></span>
                                            <?php if ($isSelf): ?>
                                                <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-cyan-100 text-cyan-800">Anda</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-[11px] text-slate-400 font-mono">ID: #<?= (int) $u['id'] ?></div>
                                    </div>
                                </div>
                            </td>

                            <!-- Contact info -->
                            <td class="py-4 px-5 font-mono text-[11px]">
                                <div class="text-slate-800 font-medium"><?= e($u['email'] ?: '—') ?></div>
                                <div class="text-slate-400"><?= e($u['phone_wa'] ?: '—') ?></div>
                            </td>

                            <!-- Role badge -->
                            <td class="py-4 px-5">
                                <span class="inline-flex items-center space-x-1.5 px-3 py-1 rounded-full text-xs font-bold border <?= $badgeClass ?>">
                                    <i data-lucide="<?= $role === 'superadmin' ? 'shield-alert' : ($role === 'moderator' ? 'shield' : ($role === 'premium' ? 'crown' : 'user')) ?>" class="w-3.5 h-3.5"></i>
                                    <span><?= user_role_label($role) ?></span>
                                </span>
                            </td>

                            <!-- Storage info -->
                            <td class="py-4 px-5">
                                <div class="text-slate-900 font-bold font-mono"><?= format_bytes($storageUsed) ?></div>
                                <div class="text-[11px] text-slate-400">
                                    Limit: <?= $storageLimitMb > 0 ? $storageLimitMb . ' MB' : 'Mengikuti Global' ?>
                                </div>
                            </td>

                            <!-- Registered date -->
                            <td class="py-4 px-5 text-slate-500 text-xs">
                                <?= date('d M Y', strtotime($u['created_at'])) ?>
                            </td>

                            <!-- Actions: Edit & Delete (Item 5) -->
                            <td class="py-4 px-5 text-right">
                                <div class="inline-flex items-center space-x-2">
                                    <!-- Edit Button -->
                                    <button type="button" 
                                            onclick='openEditModal(<?= json_encode([
                                                'id' => (int) $u['id'],
                                                'username' => $u['username'],
                                                'role' => $u['role'],
                                                'email' => $u['email'] ?? '',
                                                'phone_wa' => $u['phone_wa'] ?? '',
                                                'storage_limit_mb' => (int) ($u['storage_limit_mb'] ?? 0),
                                            ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'
                                            class="inline-flex items-center space-x-1 px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition cursor-pointer">
                                        <i data-lucide="edit-2" class="w-3.5 h-3.5"></i>
                                        <span>Edit</span>
                                    </button>

                                    <!-- Delete Button (Item 5) -->
                                    <?php if (!$isSelf): ?>
                                        <form action="/admin/users/<?= (int) $u['id'] ?>/delete" method="POST" class="inline m-0"
                                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun @<?= e($u['username']) ?> secara permanen? Seluruh data terkait akan dihapus.');">
                                            <?= csrf_field() ?>
                                            <button type="submit"
                                                    class="inline-flex items-center space-x-1 px-2.5 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-xs border border-rose-200 transition cursor-pointer"
                                                    title="Hapus Akun Pengguna">
                                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                <span>Hapus</span>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($pages > 1): ?>
        <div class="p-4 border-t border-slate-200 flex items-center justify-between bg-slate-50/50">
            <span class="text-xs text-slate-500">Menampilkan halaman <?= (int) $page ?> dari <?= (int) $pages ?></span>
            <div class="flex items-center space-x-2">
                <?php if ($page > 1): ?>
                    <a href="/admin/users?page=<?= $page - 1 ?>&q=<?= urlencode($q) ?>" class="px-3 py-1.5 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-bold transition">
                        Sebelumnya
                    </a>
                <?php endif; ?>
                <?php if ($page < $pages): ?>
                    <a href="/admin/users?page=<?= $page + 1 ?>&q=<?= urlencode($q) ?>" class="px-3 py-1.5 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-bold transition">
                        Berikutnya
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- MODAL 1: Tambah Pengguna Baru (Item 5) -->
<div id="modal-add-user" class="fixed inset-0 z-50 hidden bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl w-full max-w-lg overflow-hidden animate-in fade-in zoom-in-95 duration-150">
        <div class="p-5 sm:p-6 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-cyan-50 border border-cyan-100 flex items-center justify-center text-maritime-700">
                    <i data-lucide="user-plus" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-base font-extrabold font-heading text-slate-900">Tambah Pengguna Baru</h3>
                    <p class="text-xs text-slate-500">Buat akun untuk user atau staf moderasi</p>
                </div>
            </div>
            <button type="button" onclick="closeAddModal()" class="p-2 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form action="/admin/users/create" method="POST" class="p-5 sm:p-6 space-y-4">
            <?= csrf_field() ?>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Username <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="username" required placeholder="misal: ahmad_maritim"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 text-xs font-medium text-slate-900 bg-white">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Level Akses (Role) <span class="text-rose-500">*</span>
                    </label>
                    <select name="role" required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 text-xs font-semibold text-slate-900 bg-white">
                        <option value="basic" selected>User Basic</option>
                        <option value="premium">User Premium</option>
                        <option value="moderator">Moderator</option>
                        <option value="superadmin">Super Admin</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Kata Sandi <span class="text-rose-500">*</span>
                </label>
                <input type="password" name="password" required minlength="6" placeholder="Minimal 6 karakter"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 text-xs font-medium text-slate-900 bg-white">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Email (Opsional)
                    </label>
                    <input type="email" name="email" placeholder="nama@email.com"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 text-xs font-medium text-slate-900 bg-white">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        No. WhatsApp / HP (Opsional)
                    </label>
                    <input type="text" name="phone_wa" placeholder="08123456789"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 text-xs font-medium text-slate-900 bg-white">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Batas Kuota Khusus MB (Opsional)
                </label>
                <input type="number" name="storage_limit_mb" placeholder="Kosongkan untuk mengikuti default role"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 text-xs font-mono font-bold text-slate-900 bg-white">
                <p class="text-[11px] text-slate-400 mt-1">Jika dikosongkan, kuota akan mengikuti pengaturan global.</p>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end space-x-2.5">
                <button type="button" onclick="closeAddModal()"
                        class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                    Batal
                </button>
                <button type="submit"
                        class="px-5 py-2.5 rounded-xl bg-maritime-700 hover:bg-maritime-800 text-white text-xs font-bold shadow-md shadow-maritime-700/20 transition cursor-pointer">
                    Simpan Pengguna
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 2: Edit Pengguna (Item 5) -->
<div id="modal-edit-user" class="fixed inset-0 z-50 hidden bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl border border-slate-200 shadow-2xl w-full max-w-lg overflow-hidden animate-in fade-in zoom-in-95 duration-150">
        <div class="p-5 sm:p-6 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-cyan-50 border border-cyan-100 flex items-center justify-center text-maritime-700">
                    <i data-lucide="edit-3" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-base font-extrabold font-heading text-slate-900">Edit Pengguna</h3>
                    <p class="text-xs text-slate-500">Perbarui data atau reset kata sandi</p>
                </div>
            </div>
            <button type="button" onclick="closeEditModal()" class="p-2 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form id="form-edit-user" action="" method="POST" class="p-5 sm:p-6 space-y-4">
            <?= csrf_field() ?>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Username <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="edit_username" name="username" required
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 text-xs font-medium text-slate-900 bg-white">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Level Akses (Role) <span class="text-rose-500">*</span>
                    </label>
                    <select id="edit_role" name="role" required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 text-xs font-semibold text-slate-900 bg-white">
                        <option value="basic">User Basic</option>
                        <option value="premium">User Premium</option>
                        <option value="moderator">Moderator</option>
                        <option value="superadmin">Super Admin</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        Email
                    </label>
                    <input type="email" id="edit_email" name="email"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 text-xs font-medium text-slate-900 bg-white">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                        No. WhatsApp / HP
                    </label>
                    <input type="text" id="edit_phone_wa" name="phone_wa"
                           class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 text-xs font-medium text-slate-900 bg-white">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Batas Kuota Khusus MB
                </label>
                <input type="number" id="edit_storage_limit_mb" name="storage_limit_mb" placeholder="0 = Mengikuti global"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 text-xs font-mono font-bold text-slate-900 bg-white">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                    Kata Sandi Baru (Opsional)
                </label>
                <input type="password" name="password" minlength="6" placeholder="Biarkan kosong jika tidak ingin mengubah sandi"
                       class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 focus:border-cyan-500 focus:ring-4 focus:ring-cyan-500/10 text-xs font-medium text-slate-900 bg-white">
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end space-x-2.5">
                <button type="button" onclick="closeEditModal()"
                        class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                    Batal
                </button>
                <button type="submit"
                        class="px-5 py-2.5 rounded-xl bg-maritime-700 hover:bg-maritime-800 text-white text-xs font-bold shadow-md shadow-maritime-700/20 transition cursor-pointer">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('modal-add-user').classList.remove('hidden');
}
function closeAddModal() {
    document.getElementById('modal-add-user').classList.add('hidden');
}
function openEditModal(user) {
    document.getElementById('form-edit-user').action = '/admin/users/' + user.id + '/edit';
    document.getElementById('edit_username').value = user.username || '';
    document.getElementById('edit_role').value = user.role || 'basic';
    document.getElementById('edit_email').value = user.email || '';
    document.getElementById('edit_phone_wa').value = user.phone_wa || '';
    document.getElementById('edit_storage_limit_mb').value = user.storage_limit_mb || '';
    document.getElementById('modal-edit-user').classList.remove('hidden');
}
function closeEditModal() {
    document.getElementById('modal-edit-user').classList.add('hidden');
}
</script>
