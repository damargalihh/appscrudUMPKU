<x-app-layout>

@section('page-title', 'Dashboard')

<div class="space-y-6">

    {{-- WELCOME BANNER (matching hotspot accent style) --}}
    <div class="relative rounded-2xl overflow-hidden shadow-lg">
        <div class="absolute inset-0 bg-gradient-to-r from-[#1a1a2e] via-[#16213e] to-[#0f3460]"></div>
        <div class="relative p-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-white">Selamat Datang, {{ auth()->user()->name }}!</h1>
                <p class="text-white/60 mt-1 text-sm">Kelola jaringan WiFi hotspot kampus UMPKU Surakarta dari satu dashboard.</p>
                <div class="mt-3 connection-badge">
                    <i class="fas fa-circle text-[8px] text-green-400 animate-pulse"></i>
                    Sistem Berjalan Normal
                </div>
            </div>
            <div class="hidden md:block">
                <div class="w-20 h-20 bg-accent rounded-2xl flex items-center justify-center shadow-lg shadow-accent/30">
                    <i class="fas fa-wifi text-[#1a1a2e] text-3xl"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- STATISTIK CARDS --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        {{-- Total User --}}
        <div class="glass-card rounded-xl p-5 flex items-center gap-4 hover:shadow-lg transition-all duration-300 hover:-translate-y-0.5">
            <div class="w-14 h-14 bg-primary rounded-xl flex items-center justify-center shadow-md shadow-primary/20">
                <i class="fas fa-users text-white text-xl"></i>
            </div>
            <div>
                <p class="text-xs text-[#4a4a6a] uppercase tracking-wider font-medium">Total User</p>
                <p class="text-3xl font-bold text-[#1a1a2e]">{{ count($users) }}</p>
            </div>
        </div>

        {{-- User Aktif --}}
        <div class="glass-card rounded-xl p-5 flex items-center gap-4 hover:shadow-lg transition-all duration-300 hover:-translate-y-0.5">
            <div class="w-14 h-14 bg-success rounded-xl flex items-center justify-center shadow-md shadow-success/20">
                <i class="fas fa-wifi text-white text-xl"></i>
            </div>
            <div>
                <p class="text-xs text-[#4a4a6a] uppercase tracking-wider font-medium">User Aktif</p>
                <p class="text-3xl font-bold text-success">{{ count($activeUsers) }}</p>
            </div>
        </div>

<<<<<<< HEAD
        {{-- Profile Paket --}}
        <div class="glass-card rounded-xl p-5 flex items-center gap-4 hover:shadow-lg transition-all duration-300 hover:-translate-y-0.5">
            <div class="w-14 h-14 bg-accent rounded-xl flex items-center justify-center shadow-md shadow-accent/20">
                <i class="fas fa-box-open text-[#1a1a2e] text-xl"></i>
            </div>
            <div>
                <p class="text-xs text-[#4a4a6a] uppercase tracking-wider font-medium">Profile Paket</p>
                <p class="text-3xl font-bold text-[#1a1a2e]">{{ count($profiles) }}</p>
            </div>
        </div>
=======
                        {{-- ENABLE / DISABLE --}}
                        <form method="POST" action="/hotspot-users/{{ $u['.id'] }}/{{ ($u['disabled'] ?? 'false') === 'true' ? 'enable' : 'disable' }}" class="inline">
                            @csrf
                            <button class="text-sm text-blue-600">
                                {{ ($u['disabled'] ?? 'false') === 'true' ? 'Enable' : 'Disable' }}
                            </button>
                        </form>

                        {{-- RESET PASSWORD --}}
                        <form method="POST" action="/hotspot-users/{{ $u['.id'] }}/reset-password" class="inline">
                            @csrf
                            <input type="password" name="password" placeholder="New Pass"
                                   class="border px-1 text-sm" required>
                            <button class="text-sm text-red-600">Reset</button>
                        </form>

                        {{-- DELETE USER --}}
                        <form method="POST"
                              action="{{ route('hotspot.destroy', $u['.id']) }}"
                              class="inline"
                              onsubmit="return confirm('Yakin hapus user hotspot ini?')">
                            @csrf
                            @method('DELETE')
                            <button class="text-sm text-red-700">Delete</button>
                        </form>

                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
>>>>>>> 2857adeaca85e4793c8997314e7f6b5fd42eb5a9
    </div>

    {{-- USER HOTSPOT TABLE --}}
    <div class="glass-card rounded-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-black/10 flex items-center justify-between">
            <div>
                <h2 class="text-base font-semibold text-[#1a1a2e] flex items-center gap-2">
                    <i class="fas fa-user-shield text-primary"></i> User Hotspot
                </h2>
                <p class="text-xs text-[#4a4a6a] mt-0.5">Daftar semua user yang terdaftar di MikroTik</p>
            </div>
            <span class="text-xs bg-primary/10 text-primary px-3 py-1.5 rounded-full font-semibold">
                <i class="fas fa-users text-[10px] mr-1"></i> {{ count($users) }} user
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50/80 text-left">
                        <th class="px-6 py-3 text-xs font-semibold text-[#4a4a6a] uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-xs font-semibold text-[#4a4a6a] uppercase tracking-wider">Profile</th>
                        <th class="px-6 py-3 text-xs font-semibold text-[#4a4a6a] uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-xs font-semibold text-[#4a4a6a] uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($users as $u)
                    <tr class="hover:bg-accent/5 transition-all duration-200">
                        <td class="px-6 py-3.5">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 bg-primary rounded-lg flex items-center justify-center text-white text-xs font-bold shadow-sm">
                                    {{ strtoupper(substr($u['name'], 0, 1)) }}
                                </div>
                                <span class="font-medium text-[#1a1a2e]">{{ $u['name'] }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-3.5">
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium bg-accent/15 text-amber-800">
                                <i class="fas fa-tag text-[8px]"></i> {{ $u['profile'] ?? '-' }}
                            </span>
                        </td>
                        <td class="px-6 py-3.5">
                            @if(($u['disabled'] ?? 'false') === 'true')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium bg-red-50 text-primary">
                                    <i class="fas fa-circle text-[6px]"></i> Disabled
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium bg-green-50 text-success">
                                    <i class="fas fa-circle text-[6px] animate-pulse"></i> Active
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-3.5">
                            <div class="flex items-center gap-2">
                                {{-- ENABLE / DISABLE --}}
                                <form method="POST" action="/hotspot-users/{{ $u['.id'] }}/{{ ($u['disabled'] ?? 'false') === 'true' ? 'enable' : 'disable' }}" class="inline">
                                    @csrf
                                    <button class="px-3 py-1.5 text-xs font-semibold rounded-lg transition-all duration-200 flex items-center gap-1
                                        {{ ($u['disabled'] ?? 'false') === 'true'
                                            ? 'bg-success/10 text-success hover:bg-success/20'
                                            : 'bg-secondary/10 text-secondary hover:bg-secondary/20' }}">
                                        <i class="fas {{ ($u['disabled'] ?? 'false') === 'true' ? 'fa-toggle-on' : 'fa-toggle-off' }} text-[10px]"></i>
                                        {{ ($u['disabled'] ?? 'false') === 'true' ? 'Enable' : 'Disable' }}
                                    </button>
                                </form>

                                {{-- RESET PASSWORD --}}
                                <form method="POST" action="/hotspot-users/{{ $u['.id'] }}/reset-password" class="inline flex items-center gap-1">
                                    @csrf
                                    <input type="password" name="password" placeholder="New pass"
                                           class="w-24 px-2.5 py-1.5 text-xs border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-accent/30 focus:border-accent transition" required>
                                    <button class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-primary/10 text-primary hover:bg-primary/20 transition-all duration-200 flex items-center gap-1">
                                        <i class="fas fa-key text-[10px]"></i> Reset
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- USER AKTIF (REALTIME) --}}
    <div class="glass-card rounded-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-black/10 flex items-center justify-between">
            <div>
                <h2 class="text-base font-semibold text-[#1a1a2e] flex items-center gap-2">
                    <i class="fas fa-signal text-success"></i> User Aktif (Realtime)
                </h2>
                <p class="text-xs text-[#4a4a6a] mt-0.5">User yang sedang terkoneksi ke jaringan hotspot</p>
            </div>
            <span class="connection-badge">
                <i class="fas fa-circle text-[8px] text-green-500 animate-pulse"></i>
                {{ count($activeUsers) }} online
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50/80 text-left">
                        <th class="px-6 py-3 text-xs font-semibold text-[#4a4a6a] uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-xs font-semibold text-[#4a4a6a] uppercase tracking-wider">IP Address</th>
                        <th class="px-6 py-3 text-xs font-semibold text-[#4a4a6a] uppercase tracking-wider">MAC Address</th>
                        <th class="px-6 py-3 text-xs font-semibold text-[#4a4a6a] uppercase tracking-wider">Uptime</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($activeUsers as $a)
                    <tr class="hover:bg-accent/5 transition-all duration-200">
                        <td class="px-6 py-3.5">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 bg-success rounded-lg flex items-center justify-center text-white text-xs font-bold shadow-sm">
                                    {{ strtoupper(substr($a['user'], 0, 1)) }}
                                </div>
                                <div>
                                    <span class="font-medium text-[#1a1a2e]">{{ $a['user'] }}</span>
                                    <p class="text-[10px] text-success font-medium"><i class="fas fa-circle text-[5px] animate-pulse mr-1"></i>Connected</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-3.5">
                            <span class="font-mono text-xs text-[#4a4a6a] bg-gray-100 px-2 py-1 rounded">{{ $a['address'] }}</span>
                        </td>
                        <td class="px-6 py-3.5">
                            <span class="font-mono text-xs text-[#4a4a6a] bg-gray-100 px-2 py-1 rounded">{{ $a['mac-address'] }}</span>
                        </td>
                        <td class="px-6 py-3.5">
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium bg-accent/15 text-amber-800">
                                <i class="fas fa-clock text-[10px]"></i> {{ $a['uptime'] }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>

</x-app-layout>
