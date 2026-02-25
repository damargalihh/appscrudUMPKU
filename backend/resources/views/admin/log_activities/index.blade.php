@extends('layouts.app')

@section('page-title', 'Log Aktivitas Admin')

@section('content')
<div class="space-y-4 md:space-y-5">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
        <div>
            <h1 class="text-sm md:text-lg font-bold text-gray-800">Log Aktivitas Admin</h1>
            <p class="text-[11px] text-gray-400 mt-0.5">Riwayat semua aktivitas yang dilakukan oleh admin</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-3 md:p-5">
        <form method="GET" class="flex flex-wrap gap-2 mb-3 items-center">
            <select name="action" class="text-xs border border-gray-200 rounded-lg px-3 py-2 w-44">
                <option value="">Semua Aksi</option>
                <option value="login" @selected(request('action')=='login')>Login</option>
                <option value="login_failed" @selected(request('action')=='login_failed')>Login Gagal</option>
                <option value="logout" @selected(request('action')=='logout')>Logout</option>
                <option value="login_attempt" @selected(request('action')=='login_attempt')>Percobaan Login</option>
                <option value="update_password" @selected(request('action')=='update_password')>Perubahan Password</option>
                <option value="enable_user" @selected(request('action')=='enable_user')>Aktifkan User</option>
                <option value="disable_user" @selected(request('action')=='disable_user')>Nonaktifkan User</option>
                <option value="reset_password" @selected(request('action')=='reset_password')>Reset Password User</option>
                <option value="create_admin" @selected(request('action')=='create_admin')>Tambah Admin</option>
                <option value="update_admin" @selected(request('action')=='update_admin')>Update Admin</option>
                <option value="delete_admin" @selected(request('action')=='delete_admin')>Hapus Admin</option>
                <option value="create_hotspot_user" @selected(request('action')=='create_hotspot_user')>Tambah User Hotspot</option>
                <option value="delete_hotspot_user" @selected(request('action')=='delete_hotspot_user')>Hapus User Hotspot</option>
                <option value="bulk_delete_hotspot_user" @selected(request('action')=='bulk_delete_hotspot_user')>Bulk Hapus User Hotspot</option>
            </select>
            <select name="status" class="text-xs border border-gray-200 rounded-lg px-3 py-2 w-32">
                <option value="">Semua Status</option>
                <option value="success" @selected(request('status')=='success')>Success</option>
                <option value="failed" @selected(request('status')=='failed')>Failed</option>
            </select>
            <select name="username" class="text-xs border border-gray-200 rounded-lg px-3 py-2 w-36">
                <option value="">Semua Username</option>
                @foreach($usernames as $uname)
                    <option value="{{ $uname }}" @selected(request('username') == $uname)>{{ $uname }}</option>
                @endforeach
            </select>
            <input type="date" name="date" class="text-xs border border-gray-200 rounded-lg px-3 py-2 w-36" value="{{ request('date') }}">
            <button type="submit" class="bg-gradient-to-r from-[#FF8C00] to-[#E65100] text-white text-xs font-semibold rounded-lg px-4 py-2">Filter</button>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 uppercase text-[10px] tracking-wider">
                        <th class="px-3 py-2.5 text-left font-semibold">ID</th>
                        <th class="px-3 py-2.5 text-left font-semibold">Username</th>
                        <th class="px-3 py-2.5 text-left font-semibold">Role</th>
                        <th class="px-3 py-2.5 text-left font-semibold">Aksi</th>
                        <th class="px-3 py-2.5 text-left font-semibold">Status</th>
                        <th class="px-3 py-2.5 text-left font-semibold">IP Address</th>
                        <th class="px-3 py-2.5 text-left font-semibold">Waktu</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($logs as $log)
                    <tr class="hover:bg-orange-50/30 transition">
                        <td class="px-3 py-2.5 text-gray-400 font-medium">{{ $log->id }}</td>
                        <td class="px-3 py-2.5">{{ $log->username ?? '-' }}</td>
                        <td class="px-3 py-2.5">
                            @if($log->role === 'super_admin')
                                <span class="inline-flex items-center gap-1 text-[10px] font-semibold px-2 py-0.5 rounded-full bg-purple-100 text-purple-700">
                                    <i class="fas fa-crown text-[8px]"></i> Full Admin
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 text-[10px] font-semibold px-2 py-0.5 rounded-full bg-blue-100 text-blue-700">
                                    <i class="fas fa-user-cog text-[8px]"></i> Admin
                                </span>
                            @endif
                        </td>
                        <td class="px-3 py-2.5 text-gray-600 capitalize">{{ str_replace('_',' ', $log->action) }}</td>
                        <td class="px-3 py-2.5">
                            @if($log->status === 'success')
                                <span class="inline-flex items-center gap-1 text-[10px] font-semibold px-2 py-0.5 rounded-full bg-green-100 text-green-700">
                                    <i class="fas fa-check-circle text-[8px]"></i> Success
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 text-[10px] font-semibold px-2 py-0.5 rounded-full bg-red-100 text-red-600">
                                    <i class="fas fa-times-circle text-[8px]"></i> Failed
                                </span>
                            @endif
                        </td>
                        <td class="px-3 py-2.5 text-gray-400">{{ $log->ip_address }}</td>
                        <td class="px-3 py-2.5 text-gray-400">{{ $log->created_at }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center">
                            <div class="flex flex-col items-center gap-2 text-gray-400">
                                <i class="fas fa-list-check text-3xl text-gray-300"></i>
                                <p class="text-sm font-medium">Tidak ada data log aktivitas</p>
                                <p class="text-xs">Belum ada aktivitas yang tercatat.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $logs->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection
