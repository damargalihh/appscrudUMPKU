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
                @elseif($log->role === 'hotspot_user')
                    <span class="inline-flex items-center gap-1 text-[10px] font-semibold px-2 py-0.5 rounded-full bg-orange-100 text-orange-700">
                        <i class="fas fa-wifi text-[8px]"></i> Hotspot User
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
