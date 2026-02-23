<x-app-layout>

@section('page-title', 'User Aktif')

<div class="space-y-4 md:space-y-5">

    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
        <div>
            <h1 class="text-sm md:text-lg font-bold text-gray-800">User Aktif</h1>
            <p class="text-[11px] text-gray-400 mt-0.5">Daftar user yang sedang menggunakan jaringan hotspot</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-[11px] bg-green-100 text-green-600 px-2.5 py-1 rounded-full font-medium flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span>
                {{ count($users) }} user aktif
            </span>
            <a href="{{ route('hotspot.active') }}"
               class="text-[10px] md:text-xs text-gray-500 border border-gray-200 hover:border-green-300 hover:bg-green-50 rounded-lg px-2.5 py-1 transition flex items-center gap-1">
                <i class="fas fa-sync-alt"></i> Refresh
            </a>
        </div>
    </div>

    {{-- FLASH MESSAGES --}}
    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 text-xs rounded-lg px-3 py-2.5 flex items-center gap-2">
        <i class="fas fa-check-circle"></i> <span>{{ session('success') }}</span>
    </div>
    @endif
    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-600 text-xs rounded-lg px-3 py-2.5 flex items-center gap-2">
        <i class="fas fa-exclamation-circle"></i> <span>{{ session('error') }}</span>
    </div>
    @endif

    {{-- TABEL USER AKTIF --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-3 md:px-4 py-3 border-b border-gray-100">
            <h3 class="text-xs md:text-sm font-semibold text-gray-700 flex items-center gap-2">
                <i class="fas fa-wifi text-green-500"></i> User Aktif / Sedang Menggunakan Jaringan
                <span class="text-[10px] bg-green-100 text-green-600 px-2 py-0.5 rounded-full font-medium">{{ count($users) }}</span>
            </h3>
        </div>

        @if(count($users) > 0)
        {{-- DESKTOP TABLE --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 text-left">
                        <th class="px-4 py-2.5 text-[11px] font-semibold text-gray-400 uppercase tracking-wider">#</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Username</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold text-gray-400 uppercase tracking-wider">IP Address</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Uptime</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($users as $i => $user)
                    <tr class="hover:bg-gray-50/70 transition">
                        <td class="px-4 py-2.5 text-xs text-gray-400">{{ $i + 1 }}</td>
                        <td class="px-4 py-2.5">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 bg-gradient-to-br from-green-400 to-green-600 rounded-md flex items-center justify-center text-white text-[10px] font-bold">
                                    {{ strtoupper(substr($user['user'] ?? '-', 0, 1)) }}
                                </div>
                                <span class="text-sm font-medium text-gray-800">{{ $user['user'] ?? '-' }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-2.5">
                            <span class="text-xs text-gray-600 font-mono">{{ $user['address'] ?? '-' }}</span>
                        </td>
                        <td class="px-4 py-2.5">
                            <span class="text-xs text-gray-600">{{ $user['uptime'] ?? '-' }}</span>
                        </td>
                        <td class="px-4 py-2.5">
                            <form method="POST" action="{{ route('hotspot.cutoff', ['username' => $user['user'] ?? '']) }}" onsubmit="return confirm('Yakin cut off user ini dari jaringan?')">
                                @csrf
                                <button class="text-[11px] font-medium px-2.5 py-1 rounded-md text-red-600 bg-red-50 hover:bg-red-100 transition flex items-center gap-1">
                                    <i class="fas fa-plug-circle-xmark text-[10px]"></i> Cut Off
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- MOBILE CARD VIEW --}}
        <div class="md:hidden divide-y divide-gray-100">
            @foreach($users as $user)
            <div class="p-3 hover:bg-gray-50/50 transition">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2 min-w-0 flex-1">
                        <div class="w-8 h-8 bg-gradient-to-br from-green-400 to-green-600 rounded-lg flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                            {{ strtoupper(substr($user['user'] ?? '-', 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-gray-800 truncate">{{ $user['user'] ?? '-' }}</p>
                            <div class="flex items-center gap-1.5 mt-0.5 flex-wrap">
                                <span class="text-[10px] text-gray-500 font-mono">{{ $user['address'] ?? '-' }}</span>
                                <span class="text-[10px] text-gray-400">|</span>
                                <span class="text-[10px] text-gray-500">{{ $user['uptime'] ?? '-' }}</span>
                            </div>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('hotspot.cutoff', ['username' => $user['user'] ?? '']) }}" onsubmit="return confirm('Yakin cut off user ini?')">
                        @csrf
                        <button class="text-[10px] font-medium px-2.5 py-1.5 rounded-md text-red-600 bg-red-50 active:bg-red-100 transition flex items-center gap-1 flex-shrink-0">
                            <i class="fas fa-plug-circle-xmark text-[9px]"></i> Cut Off
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="py-8 text-center text-gray-400">
            <i class="fas fa-wifi text-2xl mb-2 opacity-30"></i>
            <p class="text-xs">Tidak ada user yang sedang menggunakan jaringan</p>
        </div>
        @endif
    </div>

</div>

</x-app-layout>
