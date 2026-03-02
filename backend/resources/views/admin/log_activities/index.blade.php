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
                <optgroup label="Admin">
                    <option value="login" @selected(request('action')=='login')>Login Admin</option>
                    <option value="logout" @selected(request('action')=='logout')>Logout</option>
                    <option value="change_password" @selected(request('action')=='change_password')>Ubah Password</option>
                    <option value="update_profile" @selected(request('action')=='update_profile')>Update Profil</option>
                    <option value="create_admin" @selected(request('action')=='create_admin')>Tambah Admin</option>
                    <option value="update_admin" @selected(request('action')=='update_admin')>Update Admin</option>
                    <option value="delete_admin" @selected(request('action')=='delete_admin')>Hapus Admin</option>
                </optgroup>
                <optgroup label="Hotspot Management">
                    <option value="create_hotspot_user" @selected(request('action')=='create_hotspot_user')>Tambah User Hotspot</option>
                    <option value="delete_hotspot_user" @selected(request('action')=='delete_hotspot_user')>Hapus User Hotspot</option>
                    <option value="bulk_delete_hotspot_user" @selected(request('action')=='bulk_delete_hotspot_user')>Bulk Hapus User Hotspot</option>
                    <option value="reset_hotspot_password" @selected(request('action')=='reset_hotspot_password')>Reset Password Hotspot</option>
                    <option value="disable_hotspot_user" @selected(request('action')=='disable_hotspot_user')>Nonaktifkan User</option>
                    <option value="enable_hotspot_user" @selected(request('action')=='enable_hotspot_user')>Aktifkan User</option>
                </optgroup>
                <optgroup label="Google OAuth Hotspot">
                    <option value="google_login" @selected(request('action')=='google_login')>Login Google Hotspot</option>
                </optgroup>
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

        <div id="logs-table" x-data="logFrontendPagination()">
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
                        <template x-for="log in paginatedLogs()" :key="log.id">
                        <tr class="hover:bg-orange-50/30 transition">
                            <td class="px-3 py-2.5 text-gray-400 font-medium" x-text="log.id"></td>
                            <td class="px-3 py-2.5" x-text="log.username ?? '-' "></td>
                            <td class="px-3 py-2.5">
                                <template x-if="log.role === 'super_admin'">
                                    <span class="inline-flex items-center gap-1 text-[10px] font-semibold px-2 py-0.5 rounded-full bg-purple-100 text-purple-700">
                                        <i class="fas fa-crown text-[8px]"></i> Full Admin
                                    </span>
                                </template>
                                <template x-if="log.role === 'admin'">
                                    <span class="inline-flex items-center gap-1 text-[10px] font-semibold px-2 py-0.5 rounded-full bg-blue-100 text-blue-700">
                                        <i class="fas fa-user-cog text-[8px]"></i> Admin
                                    </span>
                                </template>
                                <template x-if="log.role === 'hotspot_user'">
                                    <span class="inline-flex items-center gap-1 text-[10px] font-semibold px-2 py-0.5 rounded-full bg-orange-100 text-orange-700">
                                        <i class="fas fa-wifi text-[8px]"></i> Hotspot User
                                    </span>
                                </template>
                                <template x-if="log.role !== 'super_admin' && log.role !== 'admin' && log.role !== 'hotspot_user'">
                                    <span class="inline-flex items-center gap-1 text-[10px] font-semibold px-2 py-0.5 rounded-full bg-gray-100 text-gray-700">
                                        <i class="fas fa-user text-[8px]"></i> <span x-text="log.role"></span>
                                    </span>
                                </template>
                            </td>
                            <td class="px-3 py-2.5 text-gray-600 capitalize" x-text="log.action.replace(/_/g, ' ')"></td>
                            <td class="px-3 py-2.5">
                                <template x-if="log.status === 'success'">
                                    <span class="inline-flex items-center gap-1 text-[10px] font-semibold px-2 py-0.5 rounded-full bg-green-100 text-green-700">
                                        <i class="fas fa-check-circle text-[8px]"></i> Success
                                    </span>
                                </template>
                                <template x-if="log.status !== 'success'">
                                    <span class="inline-flex items-center gap-1 text-[10px] font-semibold px-2 py-0.5 rounded-full bg-red-100 text-red-600">
                                        <i class="fas fa-times-circle text-[8px]"></i> Failed
                                    </span>
                                </template>
                            </td>
                            <td class="px-3 py-2.5 text-gray-400" x-text="log.ip_address"></td>
                            <td class="px-3 py-2.5 text-gray-400" x-text="formatDate(log.created_at)"></td>
                        </tr>
                        </template>
                        <template x-if="paginatedLogs().length === 0">
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center">
                                <div class="flex flex-col items-center gap-2 text-gray-400">
                                    <i class="fas fa-list-check text-3xl text-gray-300"></i>
                                    <p class="text-sm font-medium">Tidak ada data log aktivitas</p>
                                    <p class="text-xs">Belum ada aktivitas yang tercatat.</p>
                                </div>
                            </td>
                        </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            <div class="px-3 md:px-4 py-3 border-t border-gray-100 bg-gray-50/50" x-show="lastPage > 1">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-2">
                    <p class="text-[11px] text-gray-500">
                        Menampilkan <span class="font-semibold text-gray-700" x-text="firstItem"></span>
                        - <span class="font-semibold text-gray-700" x-text="lastItem"></span>
                        dari <span class="font-semibold text-gray-700" x-text="total"></span> log aktivitas
                        <template x-if="lastPage > 1">
                            <span>(halaman <span x-text="currentPage"></span> dari <span x-text="lastPage"></span>)</span>
                        </template>
                    </p>
                    <nav class="flex items-center gap-1" x-show="lastPage > 1">
                        <button @click="prevPage()" :disabled="currentPage <= 1"
                                class="px-2 py-1 text-[11px] rounded-md border transition"
                                :class="currentPage <= 1 ? 'border-gray-100 text-gray-300 cursor-not-allowed' : 'border-gray-200 text-gray-600 hover:bg-green-50 hover:border-green-300'">
                            <i class="fas fa-chevron-left text-[9px]"></i>
                        </button>
                        <template x-for="page in visiblePages()" :key="'page-' + page">
                            <button @click="goToPage(page)" x-text="page"
                                    class="min-w-[28px] px-1.5 py-1 text-[11px] rounded-md border transition"
                                    :class="page === currentPage ? 'bg-green-500 text-white border-green-500 font-semibold' : (page === '...' ? 'border-transparent text-gray-400 cursor-default' : 'border-gray-200 text-gray-600 hover:bg-green-50 hover:border-green-300')"
                                    :disabled="page === '...' || page === currentPage"></button>
                        </template>
                        <button @click="nextPage()" :disabled="currentPage >= lastPage"
                                class="px-2 py-1 text-[11px] rounded-md border transition"
                                :class="currentPage >= lastPage ? 'border-gray-100 text-gray-300 cursor-not-allowed' : 'border-gray-200 text-gray-600 hover:bg-green-50 hover:border-green-300'">
                            <i class="fas fa-chevron-right text-[9px]"></i>
                        </button>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function logFrontendPagination() {
    return {
        logs: @json($logs),
        perPage: 10,
        currentPage: 1,
        get total() { return this.logs.length; },
        get lastPage() { return Math.ceil(this.total / this.perPage); },
        get firstItem() { return (this.currentPage - 1) * this.perPage + 1; },
        get lastItem() { return Math.min(this.currentPage * this.perPage, this.total); },
        paginatedLogs() {
            return this.logs.slice((this.currentPage - 1) * this.perPage, this.currentPage * this.perPage);
        },
        prevPage() {
            if (this.currentPage > 1) this.currentPage--;
        },
        nextPage() {
            if (this.currentPage < this.lastPage) this.currentPage++;
        },
        goToPage(page) {
            if (page !== '...' && page >= 1 && page <= this.lastPage) this.currentPage = page;
        },
        visiblePages() {
            const total = this.lastPage;
            const current = this.currentPage;
            const pages = [];
            if (total <= 7) {
                for (let i = 1; i <= total; i++) pages.push(i);
            } else {
                pages.push(1);
                if (current > 3) pages.push('...');
                const start = Math.max(2, current - 1);
                const end = Math.min(total - 1, current + 1);
                for (let i = start; i <= end; i++) pages.push(i);
                if (current < total - 2) pages.push('...');
                pages.push(total);
            }
            return pages;
        },
        formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleString('id-ID', {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
        }
    }
}
</script>
@endsection
