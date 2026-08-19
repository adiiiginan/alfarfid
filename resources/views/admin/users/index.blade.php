@extends('layouts.admin')

@section('title', 'Manajemen User')

@section('content')
    <div class="px-6 py-8 w-full">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="font-display font-semibold text-2xl text-slate-800">Manajemen User</h1>
                <p class="text-sm text-slate-500 mt-1">Kelola akun dan role pengguna sistem.</p>
            </div>
            <button onclick="openUserModal()"
                class="flex items-center gap-2 px-4 py-2.5 rounded-lg bg-[var(--copper)] text-white text-sm font-medium hover:opacity-90 transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M12 5v14M5 12h14" />
                </svg>
                Tambah User
            </button>
        </div>

        {{-- Alerts --}}
        @if (session('success'))
            <div class="mb-4 px-4 py-3 rounded-lg bg-green-50 text-green-700 text-sm border border-green-200">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mb-4 px-4 py-3 rounded-lg bg-red-50 text-[var(--red)] text-sm border border-red-200">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 px-4 py-3 rounded-lg bg-red-50 text-[var(--red)] text-sm border border-red-200">
                <p class="font-medium mb-1">Gagal menyimpan, periksa kembali:</p>
                <ul class="list-disc pl-5 space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    openUserModal();
                });
            </script>
        @endif

        {{-- ================= CARD: Daftar User ================= --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">

            {{-- Card header: judul + jumlah + filter --}}
            <div class="px-6 py-5 border-b border-slate-100">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="flex items-center gap-2.5">
                        <h2 class="font-display font-semibold text-base text-slate-800">Daftar User</h2>
                        <span class="text-[11px] font-mono px-2 py-0.5 rounded-full bg-slate-100 text-slate-500">
                            {{ $users->total() }} akun
                        </span>
                    </div>

                    <form method="GET" class="flex flex-wrap gap-2">
                        <input type="text" name="search" value="{{ $search }}"
                            placeholder="Cari nama atau email..."
                            class="w-full sm:w-56 px-3 py-2 rounded-lg border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[var(--copper)]/40">

                        <select name="role" class="px-3 py-2 rounded-lg border border-slate-200 text-sm">
                            <option value="">Semua Role</option>
                            <option value="{{ \App\Models\User::ROLE_SUPER_ADMIN }}"
                                {{ $role === \App\Models\User::ROLE_SUPER_ADMIN ? 'selected' : '' }}>Super Admin</option>
                            <option value="{{ \App\Models\User::ROLE_ADMIN }}"
                                {{ $role === \App\Models\User::ROLE_ADMIN ? 'selected' : '' }}>Admin</option>
                            <option value="{{ \App\Models\User::ROLE_OPERATOR }}"
                                {{ $role === \App\Models\User::ROLE_OPERATOR ? 'selected' : '' }}>Operator</option>
                            <option value="{{ \App\Models\User::ROLE_VIEWER }}"
                                {{ $role === \App\Models\User::ROLE_VIEWER ? 'selected' : '' }}>Viewer</option>
                        </select>

                        <button type="submit"
                            class="px-4 py-2 rounded-lg bg-slate-800 text-white text-sm font-medium hover:bg-slate-700 transition">
                            Filter
                        </button>
                        @if ($search || $role)
                            <a href="{{ route('admin.users.index') }}"
                                class="px-3 py-2 rounded-lg border border-slate-200 text-sm text-slate-500 hover:bg-slate-50 transition">
                                Reset
                            </a>
                        @endif
                    </form>
                </div>
            </div>

            {{-- Table --}}
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50/60 text-slate-500 text-xs uppercase tracking-wide">
                        <tr>
                            <th class="text-left px-6 py-3 font-medium">User</th>
                            <th class="text-left px-6 py-3 font-medium">Role</th>
                            <th class="text-left px-6 py-3 font-medium">Status</th>
                            <th class="text-left px-6 py-3 font-medium">Bergabung</th>
                            <th class="text-right px-6 py-3 font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($users as $user)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="px-6 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-8 h-8 rounded-full bg-[var(--copper)]/10 text-[var(--copper)] flex items-center justify-center text-xs font-semibold font-display shrink-0">
                                            {{ strtoupper(substr($user->full_name, 0, 1)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-medium text-slate-700 truncate">{{ $user->full_name }}</p>
                                            <p class="text-slate-400 font-mono text-[11px] truncate">{{ $user->email }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-3.5">
                                    @php
                                        $roleStyles = [
                                            \App\Models\User::ROLE_SUPER_ADMIN =>
                                                'bg-[var(--red)]/10 text-[var(--red)]',
                                            \App\Models\User::ROLE_ADMIN =>
                                                'bg-[var(--copper)]/10 text-[var(--copper)]',
                                            \App\Models\User::ROLE_OPERATOR => 'bg-blue-50 text-blue-600',
                                            \App\Models\User::ROLE_VIEWER => 'bg-slate-100 text-slate-600',
                                        ];
                                        $roleLabels = [
                                            \App\Models\User::ROLE_SUPER_ADMIN => 'Super Admin',
                                            \App\Models\User::ROLE_ADMIN => 'Admin',
                                            \App\Models\User::ROLE_OPERATOR => 'Operator',
                                            \App\Models\User::ROLE_VIEWER => 'Viewer',
                                        ];
                                    @endphp
                                    <span
                                        class="text-[11px] font-mono px-2 py-1 rounded-full {{ $roleStyles[$user->role] ?? 'bg-slate-100 text-slate-600' }}">
                                        {{ $roleLabels[$user->role] ?? $user->role }}
                                    </span>
                                </td>
                                <td class="px-6 py-3.5">
                                    @if ($user->is_active)
                                        <span
                                            class="inline-flex items-center gap-1.5 text-[11px] font-mono px-2 py-1 rounded-full bg-green-50 text-green-600">
                                            <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Aktif
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center gap-1.5 text-[11px] font-mono px-2 py-1 rounded-full bg-slate-100 text-slate-400">
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span> Nonaktif
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-3.5 text-slate-400 text-xs font-mono">
                                    {{ optional($user->created_at)->format('d M Y') ?? '—' }}
                                </td>
                                <td class="px-6 py-3.5">
                                    <div class="flex justify-end gap-2">
                                        <button type="button" onclick='openUserModal(@json($user))'
                                            class="px-3 py-1.5 rounded-lg border border-slate-200 text-xs text-slate-600 hover:bg-slate-50 transition">
                                            Edit
                                        </button>

                                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST"
                                            onsubmit="return confirm('Hapus user {{ $user->full_name }}? Tindakan ini tidak bisa dibatalkan.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="px-3 py-1.5 rounded-lg border border-red-200 text-xs text-[var(--red)] hover:bg-red-50 transition">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-14 text-center text-slate-400 text-sm">
                                    Belum ada user yang cocok dengan pencarian.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Card footer: pagination --}}
            @if ($users->hasPages())
                <div class="px-6 py-4 border-t border-slate-100">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- ================= MODAL TAMBAH / EDIT USER ================= --}}
    <div id="userModalBackdrop" class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50 px-4">
        <div class="bg-white rounded-xl w-full max-w-md p-6 shadow-xl">
            <div class="flex items-center justify-between mb-4">
                <h2 id="userModalTitle" class="font-display font-semibold text-lg text-slate-800">Tambah User</h2>
                <button onclick="closeUserModal()" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M6 6l12 12M18 6L6 18" />
                    </svg>
                </button>
            </div>

            <form id="userForm" method="POST" action="{{ route('admin.users.store') }}" class="space-y-4">
                @csrf
                <div id="methodFieldContainer"></div>

                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-1">Nama Lengkap</label>
                    <input type="text" name="full_name" id="field_full_name" required
                        class="w-full px-3 py-2 rounded-lg border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[var(--copper)]/40">
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-1">Email</label>
                    <input type="email" name="email" id="field_email" required
                        class="w-full px-3 py-2 rounded-lg border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[var(--copper)]/40">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Password</label>
                        <input type="password" name="password" id="field_password"
                            class="w-full px-3 py-2 rounded-lg border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[var(--copper)]/40">
                        <p id="passwordHint" class="text-[11px] text-slate-400 mt-1 hidden">Kosongkan jika tidak ingin
                            mengubah.</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Konfirmasi</label>
                        <input type="password" name="password_confirmation" id="field_password_confirmation"
                            class="w-full px-3 py-2 rounded-lg border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-[var(--copper)]/40">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-1">Role</label>
                    <select name="role" id="field_role" required
                        class="w-full px-3 py-2 rounded-lg border border-slate-200 text-sm">
                        <option value="{{ \App\Models\User::ROLE_SUPER_ADMIN }}">Super Admin</option>
                        <option value="{{ \App\Models\User::ROLE_OPERATOR }}">Operator</option>
                    </select>
                </div>

                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="is_active" id="field_is_active" value="1" checked
                        class="rounded border-slate-300 text-[var(--copper)] focus:ring-[var(--copper)]/40">
                    Akun aktif
                </label>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" onclick="closeUserModal()"
                        class="px-4 py-2 rounded-lg border border-slate-200 text-sm text-slate-500 hover:bg-slate-50 transition">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-4 py-2 rounded-lg bg-[var(--copper)] text-white text-sm font-medium hover:opacity-90 transition">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openUserModal(user) {
            const backdrop = document.getElementById('userModalBackdrop');
            const form = document.getElementById('userForm');
            const title = document.getElementById('userModalTitle');
            const methodContainer = document.getElementById('methodFieldContainer');
            const passwordHint = document.getElementById('passwordHint');
            const passwordField = document.getElementById('field_password');
            const confirmField = document.getElementById('field_password_confirmation');

            form.reset();
            methodContainer.innerHTML = '';

            if (user && user.user_id) {
                title.textContent = 'Edit User: ' + user.full_name;
                form.action = "{{ url('admin/users') }}/" + user.user_id;
                methodContainer.innerHTML = '<input type="hidden" name="_method" value="PUT">';

                document.getElementById('field_full_name').value = user.full_name ?? '';
                document.getElementById('field_email').value = user.email ?? '';
                document.getElementById('field_role').value = user.role ?? 'viewer';
                document.getElementById('field_is_active').checked = !!user.is_active;

                passwordField.required = false;
                confirmField.required = false;
                passwordHint.classList.remove('hidden');
            } else {
                title.textContent = 'Tambah User';
                form.action = "{{ route('admin.users.store') }}";

                passwordField.required = true;
                confirmField.required = true;
                passwordHint.classList.add('hidden');
            }

            backdrop.classList.remove('hidden');
            backdrop.classList.add('flex');
        }

        function closeUserModal() {
            const backdrop = document.getElementById('userModalBackdrop');
            backdrop.classList.add('hidden');
            backdrop.classList.remove('flex');
        }

        document.getElementById('userModalBackdrop').addEventListener('click', function(e) {
            if (e.target === this) closeUserModal();
        });
    </script>
@endsection
