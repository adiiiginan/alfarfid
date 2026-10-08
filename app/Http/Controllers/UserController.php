<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($role = $request->input('role')) {
            $query->where('role', $role);
        }

        $users = $query->orderBy('full_name')->paginate(10)->withQueryString();

        return view('admin.users.index', [
            'users'  => $users,
            'search' => $search ?? '',
            'role'   => $role ?? '',
        ]);
    }

    // Form terpisah tidak dipakai (UI pakai modal di halaman index),
    // disediakan supaya route resource tidak error kalau diakses.
    public function create()
    {
        return redirect()->route('admin.users.index');
    }

    public function show(string $userId)
    {
        return redirect()->route('admin.users.index');
    }

    public function edit(string $userId)
    {
        return redirect()->route('admin.users.index');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:150',
            'email'     => 'required|email|max:150|unique:users,email',
            'password'  => 'required|string|min:8|confirmed',
            'role'      => 'required|in:' . implode(',', User::ROLES),
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('admin.users.index')
                ->withErrors($validator)
                ->withInput();
        }

        User::create([
            'full_name'     => $request->full_name,
            'email'         => $request->email,
            'password_hash' => Hash::make($request->password),
            'role'          => $request->role,
            'is_active'     => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User baru berhasil ditambahkan.');
    }

    public function update(Request $request, string $userId)
    {
        $user = User::findOrFail($userId);

        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:150',
            'email'     => 'required|email|max:150|unique:users,email,' . $userId . ',user_id',
            'password'  => 'nullable|string|min:8',
            'role'      => 'required|in:' . implode(',', User::ROLES),
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('admin.users.index')
                ->withErrors($validator)
                ->withInput();
        }

        // Cegah user menurunkan role dirinya sendiri keluar dari admin/super_admin (kunci diri sendiri)
        $adminLevelRoles = [User::ROLE_SUPER_ADMIN, User::ROLE_ADMIN];
        if (
            $user->user_id === auth()->id()
            && in_array($user->role, $adminLevelRoles, true)
            && !in_array($request->role, $adminLevelRoles, true)
        ) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', 'Anda tidak bisa mengubah role akun Anda sendiri.');
        }

        $user->full_name = $request->full_name;
        $user->email     = $request->email;
        $user->role      = $request->role;
        $user->is_active = $request->boolean('is_active', true);

        if ($request->filled('password')) {
            $user->password_hash = Hash::make($request->password);
        }

        $user->save();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Data user berhasil diperbarui.');
    }

    public function destroy(string $userId)
    {
        $user = User::findOrFail($userId);

        if ($user->user_id === auth()->id()) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', 'Anda tidak bisa menghapus akun Anda sendiri.');
        }

        $adminLevelRoles = [User::ROLE_SUPER_ADMIN, User::ROLE_ADMIN];
        if (
            in_array($user->role, $adminLevelRoles, true)
            && User::whereIn('role', $adminLevelRoles)->count() <= 1
        ) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', 'Tidak bisa menghapus Admin/Super Admin terakhir.');
        }

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User berhasil dihapus.');
    }
}
