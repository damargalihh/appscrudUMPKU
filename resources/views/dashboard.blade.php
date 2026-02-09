<x-app-layout>

@section('page-title', 'Dashboard')

<div class="space-y-6">

    {{-- WELCOME BANNER --}}
    <div class="rounded-2xl bg-gradient-to-r from-[#1a1a2e] via-[#16213e] to-[#0f3460] p-6 flex items-center justify-between shadow-lg">
        <div>
            <h1 class="text-xl font-bold text-white">Halo, {{ auth()->user()->name }} <span class="inline-block animate-bounce">👋</span></h1>
            <p class="text-white/50 text-sm mt-1">Kelola jaringan WiFi hotspot kampus dari sini.</p>
            <div class="mt-3 inline-flex items-center gap-2 bg-green-500/15 text-green-400 text-xs font-medium px-3 py-1.5 rounded-full border border-green-500/20">
                <span class="w-1.5 h-1.5 bg-green-400 rounded-full animate-pulse"></span> Sistem Normal
            </div>
        </div>
        <div class="hidden md:flex w-24 h-24 rounded-2xl items-center justify-center overflow-hidden">
            <img src="{{ asset('img/logoadmin.png') }}" alt="Admin" class="w-full h-full object-contain">
        </div>
    </div>

    {{-- STAT CARDS --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold">Total User</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1">{{ count($users) }}</p>
                </div>
                <div class="w-11 h-11 bg-blue-50 rounded-xl flex items-center justify-center">
                    <i class="fas fa-users text-blue-500"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold">User Aktif</p>
                    <p class="text-2xl font-bold text-green-600 mt-1">{{ count($activeUsers) }}</p>
                </div>
                <div class="w-11 h-11 bg-green-50 rounded-xl flex items-center justify-center">
                    <i class="fas fa-signal text-green-500"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold">Profile</p>
                    <p class="text-2xl font-bold text-gray-800 mt-1">{{ count($profiles) }}</p>
                </div>
                <div class="w-11 h-11 bg-amber-50 rounded-xl flex items-center justify-center">
                    <i class="fas fa-layer-group text-amber-500"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold">Disabled</p>
                    <p class="text-2xl font-bold text-red-500 mt-1">{{ collect($users)->where('disabled', 'true')->count() }}</p>
                </div>
                <div class="w-11 h-11 bg-red-50 rounded-xl flex items-center justify-center">
                    <i class="fas fa-user-slash text-red-400"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- THREE COLUMN: PROFILES + ACTIVE USERS + BANDWIDTH --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- PROFILE HOTSPOT --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-layer-group text-amber-500"></i> Profile
                </h3>
                <span class="text-xs text-gray-400">{{ count($profiles) }}</span>
            </div>
            <div class="divide-y divide-gray-50 max-h-72 overflow-y-auto">
                @forelse($profiles as $profile)
                <div class="px-5 py-3 flex items-center justify-between hover:bg-gray-50/50 transition group">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-gradient-to-br from-amber-400 to-orange-500 rounded-lg flex items-center justify-center text-white text-xs font-bold">
                            {{ strtoupper(substr($profile['name'], 0, 1)) }}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-800">{{ $profile['name'] }}</p>
                            <p class="text-[11px] text-gray-400">Rate: {{ $profile['rate-limit'] ?? 'Unlimited' }}</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('hotspot.destroyProfile', $profile['.id']) }}"
                          onsubmit="return confirm('Yakin hapus profile {{ $profile['name'] }}?')">
                        @csrf
                        @method('DELETE')
                        <button class="opacity-0 group-hover:opacity-100 text-xs text-red-400 hover:text-red-600 transition-all px-2 py-1 rounded hover:bg-red-50">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </form>
                </div>
                @empty
                <div class="px-5 py-8 text-center text-gray-400 text-sm">
                    <i class="fas fa-inbox text-2xl mb-2"></i>
                    <p>Belum ada profile</p>
                </div>
                @endforelse
            </div>
        </div>

        {{-- USER AKTIF --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-signal text-green-500"></i> User Online
                </h3>
                <span class="inline-flex items-center gap-1.5 text-xs text-green-600 bg-green-50 px-2.5 py-1 rounded-full">
                    <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span> {{ count($activeUsers) }}
                </span>
            </div>
            <div class="divide-y divide-gray-50 max-h-72 overflow-y-auto">
                @forelse($activeUsers as $a)
                <div class="px-5 py-3 flex items-center justify-between hover:bg-gray-50/50 transition">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center text-white text-xs font-bold">
                            {{ strtoupper(substr($a['user'], 0, 1)) }}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-800">{{ $a['user'] }}</p>
                            <p class="text-[11px] text-gray-400">{{ $a['address'] }}</p>
                        </div>
                    </div>
                    <span class="text-xs text-gray-500 bg-gray-100 px-2 py-0.5 rounded font-mono">{{ $a['uptime'] }}</span>
                </div>
                @empty
                <div class="px-5 py-8 text-center text-gray-400 text-sm">
                    <i class="fas fa-wifi text-2xl mb-2 opacity-30"></i>
                    <p>Tidak ada user online</p>
                </div>
                @endforelse
            </div>
        </div>

        {{-- BANDWIDTH MONITORING (REAL-TIME) --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden"
             x-data="{
                queues: [],
                loading: true,
                error: false,
                lastUpdate: null,
                formatBytes(bytes) {
                    if (bytes >= 1048576) return (bytes / 1048576).toFixed(1) + ' Mbps';
                    if (bytes >= 1024) return (bytes / 1024).toFixed(1) + ' Kbps';
                    return bytes + ' bps';
                },
                async fetchBandwidth() {
                    try {
                        const res = await fetch('{{ route('api.bandwidth') }}');
                        if (!res.ok) throw new Error('Network error');
                        this.queues = await res.json();
                        this.error = false;
                        this.lastUpdate = new Date().toLocaleTimeString('id-ID');
                    } catch (e) {
                        this.error = true;
                    } finally {
                        this.loading = false;
                    }
                }
             }"
             x-init="fetchBandwidth(); setInterval(() => fetchBandwidth(), 5000)">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-tachometer-alt text-purple-500"></i> Bandwidth
                    <span x-show="!loading" class="inline-flex items-center gap-1 text-[10px] text-green-500 font-normal">
                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span> Live
                    </span>
                </h3>
                <div class="flex items-center gap-2">
                    <span x-show="lastUpdate" x-text="lastUpdate" class="text-[10px] text-gray-400"></span>
                    <span x-text="queues.length + ' queue'" class="text-xs text-gray-400"></span>
                </div>
            </div>
            <div class="divide-y divide-gray-50 max-h-72 overflow-y-auto">
                {{-- Loading --}}
                <template x-if="loading">
                    <div class="px-5 py-8 text-center text-gray-400 text-sm">
                        <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                        <p>Memuat data bandwidth...</p>
                    </div>
                </template>

                {{-- Error --}}
                <template x-if="!loading && error">
                    <div class="px-5 py-8 text-center text-red-400 text-sm">
                        <i class="fas fa-exclamation-triangle text-2xl mb-2"></i>
                        <p>Gagal memuat data bandwidth</p>
                    </div>
                </template>

                {{-- Empty --}}
                <template x-if="!loading && !error && queues.length === 0">
                    <div class="px-5 py-8 text-center text-gray-400 text-sm">
                        <i class="fas fa-tachometer-alt text-2xl mb-2 opacity-30"></i>
                        <p>Tidak ada queue aktif</p>
                    </div>
                </template>

                {{-- Queue Items --}}
                <template x-for="(q, i) in queues" :key="i">
                    <div class="px-5 py-3 hover:bg-gray-50/50 transition">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 bg-purple-100 rounded-md flex items-center justify-center">
                                    <i class="fas fa-network-wired text-purple-500 text-xs"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-800 truncate max-w-[120px]" x-text="q.name"></p>
                                    <p class="text-[10px] text-gray-400" x-text="q.target"></p>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 text-[11px]">
                            <div class="flex-1">
                                <div class="flex justify-between text-gray-400 mb-1">
                                    <span><i class="fas fa-arrow-up text-blue-400"></i> Up</span>
                                    <span class="font-medium text-blue-600" x-text="formatBytes(q.upload)"></span>
                                </div>
                                <div class="w-full h-1 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-blue-400 rounded-full transition-all duration-500" :style="'width:' + q.upPercent + '%'"></div>
                                </div>
                            </div>
                            <div class="flex-1">
                                <div class="flex justify-between text-gray-400 mb-1">
                                    <span><i class="fas fa-arrow-down text-green-400"></i> Down</span>
                                    <span class="font-medium text-green-600" x-text="formatBytes(q.download)"></span>
                                </div>
                                <div class="w-full h-1 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-green-400 rounded-full transition-all duration-500" :style="'width:' + q.downPercent + '%'"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

    </div>

    {{-- USER HOTSPOT TABLE WITH SEARCH --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden" x-data="{ search: '', filter: 'all', profileFilter: 'all' }">
        <div class="px-5 py-4 border-b border-gray-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <h3 class="text-sm font-semibold text-gray-800 flex items-center gap-2">
                <i class="fas fa-user-shield text-blue-500"></i> Semua User Hotspot
                <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full font-medium ml-1">{{ count($users) }}</span>
            </h3>
            <div class="flex items-center gap-2 w-full sm:w-auto flex-wrap">
                {{-- FILTER STATUS --}}
                <select x-model="filter" class="text-xs border border-gray-200 rounded-lg px-2.5 py-2 focus:ring-1 focus:ring-orange-300 focus:border-orange-300 bg-white">
                    <option value="all">Semua Status</option>
                    <option value="active">Active</option>
                    <option value="disabled">Disabled</option>
                </select>
                {{-- FILTER PROFILE --}}
                <select x-model="profileFilter" class="text-xs border border-gray-200 rounded-lg px-2.5 py-2 focus:ring-1 focus:ring-orange-300 focus:border-orange-300 bg-white">
                    <option value="all">Semua Profile</option>
                    @foreach($profiles as $p)
                        <option value="{{ strtolower($p['name']) }}">{{ $p['name'] }}</option>
                    @endforeach
                </select>
                {{-- SEARCH --}}
                <div class="relative flex-1 sm:flex-none">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <input x-model="search" type="text" placeholder="Cari user..."
                           class="w-full sm:w-48 pl-8 pr-3 py-2 text-xs border border-gray-200 rounded-lg focus:ring-1 focus:ring-orange-300 focus:border-orange-300 transition">
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 text-left">
                        <th class="px-5 py-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wider">User</th>
                        <th class="px-5 py-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Profile</th>
                        <th class="px-5 py-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-5 py-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($users as $u)
                    <tr class="hover:bg-gray-50/70 transition"
                        x-show="(search === '' || '{{ strtolower($u['name']) }}'.includes(search.toLowerCase()) || '{{ strtolower($u['profile'] ?? '') }}'.includes(search.toLowerCase()))
                                && (filter === 'all' || (filter === 'active' && '{{ $u['disabled'] ?? 'false' }}' !== 'true') || (filter === 'disabled' && '{{ $u['disabled'] ?? 'false' }}' === 'true'))
                                && (profileFilter === 'all' || '{{ strtolower($u['profile'] ?? '') }}' === profileFilter)"
                        x-transition>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-gradient-to-br from-blue-400 to-blue-600 rounded-lg flex items-center justify-center text-white text-xs font-bold">
                                    {{ strtoupper(substr($u['name'], 0, 1)) }}
                                </div>
                                <span class="text-sm font-medium text-gray-800">{{ $u['name'] }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-3">
                            <span class="text-xs bg-amber-50 text-amber-700 px-2 py-0.5 rounded font-medium">{{ $u['profile'] ?? '-' }}</span>
                        </td>
                        <td class="px-5 py-3">
                            @if(($u['disabled'] ?? 'false') === 'true')
                                <span class="inline-flex items-center gap-1 text-xs text-red-500 bg-red-50 px-2 py-0.5 rounded font-medium">
                                    <span class="w-1.5 h-1.5 bg-red-400 rounded-full"></span> Disabled
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 text-xs text-green-600 bg-green-50 px-2 py-0.5 rounded font-medium">
                                    <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span> Active
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <form method="POST" action="/hotspot-users/{{ $u['.id'] }}/{{ ($u['disabled'] ?? 'false') === 'true' ? 'enable' : 'disable' }}">
                                    @csrf
                                    <button class="text-[11px] font-medium px-2.5 py-1 rounded-md transition
                                        {{ ($u['disabled'] ?? 'false') === 'true'
                                            ? 'text-green-600 bg-green-50 hover:bg-green-100'
                                            : 'text-amber-600 bg-amber-50 hover:bg-amber-100' }}">
                                        <i class="fas {{ ($u['disabled'] ?? 'false') === 'true' ? 'fa-toggle-on' : 'fa-toggle-off' }} mr-0.5"></i>
                                        {{ ($u['disabled'] ?? 'false') === 'true' ? 'Enable' : 'Disable' }}
                                    </button>
                                </form>
                                <form method="POST" action="/hotspot-users/{{ $u['.id'] }}/reset-password" class="flex items-center gap-1">
                                    @csrf
                                    <input type="password" name="password" placeholder="New pass"
                                           class="w-20 px-2 py-1 text-[11px] border border-gray-200 rounded-md focus:ring-1 focus:ring-amber-300 focus:border-amber-300 transition" required>
                                    <button class="text-[11px] font-medium px-2.5 py-1 rounded-md text-blue-600 bg-blue-50 hover:bg-blue-100 transition">
                                        <i class="fas fa-key mr-0.5"></i> Reset
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('hotspot.destroy', $u['.id']) }}"
                                      onsubmit="return confirm('Yakin hapus user {{ $u['name'] }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-[11px] font-medium px-2.5 py-1 rounded-md text-red-500 bg-red-50 hover:bg-red-100 transition">
                                        <i class="fas fa-trash-alt"></i>
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

</div>

</x-app-layout>
