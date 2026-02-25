<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use App\Helpers\LogActivityHelper;

class AdminController extends Controller
{
    /**
     * Tampilkan halaman manajemen admin.
     */
    public function index()
    {
        $admins = User::where('is_admin', true)->get();
        $totalUsers = User::count();
        $totalAdmins = $admins->count();

        return view('admin.index', compact('admins', 'totalUsers', 'totalAdmins'));
    }

    /**
     * Tambah admin baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role'     => ['required', 'in:super_admin,admin'],
        ]);

        $admin = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'is_admin' => true,
            'role'     => $request->role,
        ]);
        LogActivityHelper::log('create_admin', $admin->email);
        return redirect()->route('admin.index')->with('success', 'Admin berhasil ditambahkan.');
    }

    /**
     * Update data admin.
     */
    public function update(Request $request, User $admin)
    {
        $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,' . $admin->id],
        ]);

        $data = [
            'name'  => $request->name,
            'email' => $request->email,
        ];

        // Update role jika diisi
        if ($request->filled('role')) {
            $request->validate([
                'role' => ['in:super_admin,admin'],
            ]);
            $data['role'] = $request->role;
        }

        // Update password jika diisi
        if ($request->filled('password')) {
            $request->validate([
                'password' => ['confirmed', Rules\Password::defaults()],
            ]);
            $data['password'] = Hash::make($request->password);
        }

        $admin->update($data);
        LogActivityHelper::log('update_admin', $admin->email);
        return redirect()->route('admin.index')->with('success', 'Data admin berhasil diperbarui.');
    }

    /**
     * Hapus admin.
     */
    public function destroy(User $admin)
    {
        // Cegah hapus diri sendiri
        if ($admin->id === auth()->id()) {
            return redirect()->route('admin.index')->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        // Cegah hapus admin terakhir
        if (User::where('is_admin', true)->count() <= 1) {
            return redirect()->route('admin.index')->with('error', 'Tidak dapat menghapus admin terakhir.');
        }

        $adminEmail = $admin->email;
        $admin->delete();
        LogActivityHelper::log('delete_admin', $adminEmail);
        return redirect()->route('admin.index')->with('success', 'Admin berhasil dihapus.');
    }
}
