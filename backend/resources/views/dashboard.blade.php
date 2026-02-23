<x-app-layout>

@section('page-title', 'Dashboard')

{{-- MASTER CONTROLLER — fetch once, refresh manual, update only on change --}}
<div class="space-y-4 md:space-y-6"
     x-data="{
        // Data
        stats: { total: 0, online: 0, enabled: 0, disabled: 0, time: '' },
        profiles: [],
        actives: [],
        // Chart history
        labels: [], onlineData: [], totalData: [], maxPoints: 30,
        chartData: { labels: [], onlineData: [], totalData: [] },
        // UI state
        loading: true,
        refreshing: false,
        lastUpdated: null,
        dataCached: false,
        error: false,
        // Auto-refresh (realtime)
        autoRefresh: true,
        _refreshTimer: null,
        refreshIntervalSec: 20,
        countdown: 0,
        _countdownTimer: null,
        _fetching: false,

        // Deep compare helper
        hasChanged(oldData, newData) {
            return JSON.stringify(oldData) !== JSON.stringify(newData);
        },

        async fetchAll(isRefresh = false) {
            if (this._fetching && !isRefresh) return; // skip if auto-refresh overlaps
            this._fetching = true;
            if (isRefresh) this.refreshing = true;
            this.error = false;
            let anySuccess = false;
            try {
                const [statsResult, profilesResult, activesResult] = await Promise.allSettled([
                    fetch('{{ route('api.userStats') }}').then(r => r.ok ? r.json().then(d => ({data: d, headers: r.headers})) : Promise.reject()),
                    fetch('{{ route('api.profiles') }}').then(r => r.ok ? r.json().then(d => ({data: d, headers: r.headers})) : Promise.reject()),
                    fetch('{{ route('api.activeUsers') }}').then(r => r.ok ? r.json().then(d => ({data: d, headers: r.headers})) : Promise.reject()),
                ]);

                let isCached = false;

                if (statsResult.status === 'fulfilled') {
                    anySuccess = true;
                    const newStats = statsResult.value.data;
                    if (statsResult.value.headers.get('X-Data-Cached') === 'true') isCached = true;
                    if (this.hasChanged(this.stats, newStats)) {
                        this.stats = newStats;
                        const now = new Date();
                        const label = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                        this.labels.push(label);
                        this.onlineData.push(newStats.online);
                        this.totalData.push(newStats.total);
                        if (this.labels.length > this.maxPoints) {
                            this.labels.shift(); this.onlineData.shift(); this.totalData.shift();
                        }
                        this.chartData = { labels: [...this.labels], onlineData: [...this.onlineData], totalData: [...this.totalData] };
                        setTimeout(() => { updateChart(this.chartData); }, 0);
                    }
                }

                if (profilesResult.status === 'fulfilled') {
                    anySuccess = true;
                    if (profilesResult.value.headers.get('X-Data-Cached') === 'true') isCached = true;
                    const newProfiles = profilesResult.value.data;
                    if (this.hasChanged(this.profiles, newProfiles)) this.profiles = newProfiles;
                }

                if (activesResult.status === 'fulfilled') {
                    anySuccess = true;
                    if (activesResult.value.headers.get('X-Data-Cached') === 'true') isCached = true;
                    const newActives = activesResult.value.data;
                    if (this.hasChanged(this.actives, newActives)) this.actives = newActives;
                }

                this.dataCached = isCached;
                if (anySuccess) {
                    this.lastUpdated = new Date();
                    this.error = false;
                    window.dispatchEvent(new CustomEvent('dashboard-refresh'));
                } else {
                    this.error = true;
                }
            } catch (e) {
                this.error = true;
                console.warn('fetchAll error:', e.message);
            } finally {
                this.loading = false;
                this.refreshing = false;
                this._fetching = false;
            }
        },

        startAutoRefresh() {
            this.stopAutoRefresh();
            if (!this.autoRefresh) return;
            this.countdown = this.refreshIntervalSec;
            this._countdownTimer = setInterval(() => {
                this.countdown = Math.max(0, this.countdown - 1);
            }, 1000);
            this._refreshTimer = setInterval(async () => {
                this.countdown = this.refreshIntervalSec;
                await this.fetchAll(false);
            }, this.refreshIntervalSec * 1000);
        },

        stopAutoRefresh() {
            if (this._refreshTimer) { clearInterval(this._refreshTimer); this._refreshTimer = null; }
            if (this._countdownTimer) { clearInterval(this._countdownTimer); this._countdownTimer = null; }
            this.countdown = 0;
        },

        toggleAutoRefresh() {
            this.autoRefresh = !this.autoRefresh;
            if (this.autoRefresh) { this.startAutoRefresh(); } else { this.stopAutoRefresh(); }
        },

        formatTime() {
            if (!this.lastUpdated) return '-';
            return this.lastUpdated.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        },

        async init() {
            await this.fetchAll();
            this.startAutoRefresh();
        },

        destroy() {
            this.stopAutoRefresh();
        }
     }"
     x-init="init()"
     @beforeunload.window="destroy()"
     x-effect="if (!autoRefresh) stopAutoRefresh()">

    {{-- WELCOME BANNER --}}
    <div class="rounded-2xl bg-gradient-to-r from-[#FF8C00] via-[#FFA726] to-[#E65100] p-3 md:p-6 flex items-center justify-between shadow-xl border border-orange-200/40">
        <div>
            <h1 class="text-sm md:text-xl font-bold text-white drop-shadow">Halo, {{ auth()->user()->name }} <span class="inline-block animate-bounce">👋</span></h1>
            <p class="text-white/80 text-xs mt-1">Selamat datang di panel Admin Hotspot UMPKU.</p>
        </div>
        <div class="flex items-center gap-3">
            {{-- Auto-refresh toggle + Refresh button + last updated --}}
            <div class="flex flex-col items-end gap-1">
                <div class="flex items-center gap-2">
                    {{-- Auto-refresh toggle --}}
                    <button @click="toggleAutoRefresh()"
                            class="flex items-center gap-1.5 text-white text-[10px] font-medium px-2.5 py-1.5 rounded-lg transition"
                            :class="autoRefresh ? 'bg-green-500/40 hover:bg-green-500/50' : 'bg-white/10 hover:bg-white/20'">
                        <span class="relative flex h-2 w-2">
                            <span x-show="autoRefresh" class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-300 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2" :class="autoRefresh ? 'bg-green-400' : 'bg-gray-400'"></span>
                        </span>
                        <span x-text="autoRefresh ? 'Live' : 'Paused'"></span>
                        <span x-show="autoRefresh" class="text-white/60" x-text="'(' + countdown + 's)'"></span>
                    </button>
                    {{-- Manual refresh --}}
                    <button @click="fetchAll(true)" :disabled="refreshing"
                            class="flex items-center gap-1.5 bg-white/20 hover:bg-white/30 backdrop-blur text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition disabled:opacity-50">
                        <i class="fas fa-sync-alt text-[10px]" :class="refreshing && 'fa-spin'"></i>
                        <span x-text="refreshing ? 'Memuat...' : 'Refresh'"></span>
                    </button>
                </div>
                <span class="text-[10px] text-white/60" x-show="lastUpdated">
                    Terakhir: <span x-text="formatTime()"></span>
                </span>
            </div>
            <div class="hidden md:flex w-32 h-32 rounded-2xl items-center justify-center overflow-hidden bg-transparent">
                <img src="{{ asset('img/logoadmin.png') }}" alt="Admin" class="w-20 h-20 object-contain">
            </div>
        </div>
    </div>

    {{-- Loading overlay for initial load --}}
    <template x-if="loading">
        <div class="py-12 text-center text-gray-400">
            <i class="fas fa-spinner fa-spin text-3xl mb-3 text-orange-400"></i>
            <p class="text-sm">Memuat data dashboard...</p>
        </div>
    </template>

    {{-- Error state --}}
    <template x-if="!loading && error">
        <div class="bg-red-50 border border-red-200 text-red-600 text-sm rounded-xl px-4 py-3 flex items-center gap-2">
            <i class="fas fa-exclamation-triangle"></i>
            <span>Gagal memuat data. Silakan klik <strong>Refresh</strong> untuk mencoba lagi.</span>
        </div>
    </template>

    {{-- Cached data notice --}}
    <div x-show="!loading && dataCached" x-transition class="bg-amber-50 border border-amber-200 text-amber-700 text-xs rounded-xl px-4 py-2.5 flex items-center gap-2">
        <i class="fas fa-database"></i>
        <span>Menampilkan data tersimpan dari database. MikroTik tidak dapat dihubungi saat ini.</span>
        <button @click="fetchAll(true)" class="ml-auto text-amber-600 hover:text-amber-800 font-semibold underline text-xs">Coba lagi</button>
    </div>

    {{-- STAT CARDS --}}
    <div x-show="!loading" class="grid grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4">
        {{-- Total User --}}
        <div class="rounded-2xl p-4 md:p-5 bg-white text-gray-800 shadow-sm border border-gray-100">
            <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold">Total User</p>
            <p class="text-2xl md:text-4xl font-extrabold mt-2 md:mt-3 tracking-tight" x-text="stats.total">-</p>
            <p class="text-[10px] md:text-xs text-gray-400 mt-2 flex items-center gap-1">
                <i class="fas fa-users text-orange-500 text-[10px]"></i> Semua user terdaftar
            </p>
        </div>

        {{-- User Online --}}
        <div class="rounded-2xl p-4 md:p-5 bg-white text-gray-800 shadow-sm border border-gray-100">
            <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold">User Online</p>
            <p class="text-2xl md:text-4xl font-extrabold mt-2 md:mt-3 tracking-tight text-green-600" x-text="stats.online">-</p>
            <p class="text-[10px] md:text-xs text-gray-400 mt-2 flex items-center gap-1">
                <i class="fas fa-signal text-green-500 text-[10px]"></i> Sedang aktif sekarang
            </p>
        </div>

        {{-- Profile --}}
        <div class="rounded-2xl p-4 md:p-5 bg-white text-gray-800 shadow-sm border border-gray-100">
            <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold">Profile</p>
            <p class="text-2xl md:text-4xl font-extrabold mt-2 md:mt-3 tracking-tight" x-text="profiles.length">-</p>
            <p class="text-[10px] md:text-xs text-gray-400 mt-2 flex items-center gap-1">
                <i class="fas fa-layer-group text-amber-500 text-[10px]"></i> Profil hotspot tersedia
            </p>
        </div>

        {{-- Disabled --}}
        <div class="rounded-2xl p-4 md:p-5 bg-white text-gray-800 shadow-sm border border-gray-100">
            <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold">Disabled</p>
            <p class="text-2xl md:text-4xl font-extrabold mt-2 md:mt-3 tracking-tight text-red-500" x-text="stats.disabled">-</p>
            <p class="text-[10px] md:text-xs text-gray-400 mt-2 flex items-center gap-1">
                <i class="fas fa-user-slash text-red-400 text-[10px]"></i> User dinonaktifkan
            </p>
        </div>
    </div>

    {{-- THREE COLUMN: PROFILES + ACTIVE USERS + BANDWIDTH --}}
    <div x-show="!loading" class="grid grid-cols-1 lg:grid-cols-3 gap-3 md:gap-5">

        {{-- PROFILE HOTSPOT --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-4 md:px-5 py-3 md:py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-layer-group text-amber-500"></i> Profile
                </h3>
                <span class="text-xs text-gray-400" x-text="profiles.length"></span>
            </div>
            <div class="divide-y divide-gray-50 max-h-52 sm:max-h-72 overflow-y-auto">
                <template x-if="profiles.length === 0">
                    <div class="px-5 py-8 text-center text-gray-400 text-sm">
                        <i class="fas fa-inbox text-2xl mb-2"></i>
                        <p>Belum ada profile</p>
                    </div>
                </template>
                <template x-for="(p, i) in profiles" :key="i">
                    <div class="px-4 md:px-5 py-3 flex items-center justify-between hover:bg-gray-50/50 transition group">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-gradient-to-br from-amber-400 to-orange-500 rounded-lg flex items-center justify-center text-white text-xs font-bold" x-text="p.name.charAt(0).toUpperCase()"></div>
                            <div>
                                <p class="text-sm font-medium text-gray-800" x-text="p.name"></p>
                                <p class="text-[11px] text-gray-400">Rate: <span x-text="p.rateLimit"></span></p>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- USER AKTIF / MENGGUNAKAN JARINGAN --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-4 md:px-5 py-3 md:py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-wifi text-green-500"></i> Sedang Menggunakan Jaringan
                </h3>
                <span class="inline-flex items-center gap-1.5 text-xs text-green-600 bg-green-50 px-2.5 py-1 rounded-full">
                    <span x-text="actives.length"></span>
                </span>
            </div>
            <div class="divide-y divide-gray-50 max-h-52 sm:max-h-72 overflow-y-auto">
                <template x-if="actives.length === 0">
                    <div class="px-5 py-8 text-center text-gray-400 text-sm">
                        <i class="fas fa-wifi text-2xl mb-2 opacity-30"></i>
                        <p>Tidak ada user menggunakan jaringan</p>
                    </div>
                </template>
                <template x-for="(a, i) in actives" :key="i">
                    <div class="px-4 md:px-5 py-3 flex items-center justify-between hover:bg-gray-50/50 transition">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center text-white text-xs font-bold" x-text="a.user.charAt(0).toUpperCase()"></div>
                            <div>
                                <p class="text-sm font-medium text-gray-800" x-text="a.user"></p>
                                <p class="text-[11px] text-gray-400" x-text="a.address"></p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-xs text-gray-500 bg-gray-100 px-2 py-0.5 rounded font-mono" x-text="a.uptime"></span>
                            <div class="flex items-center gap-2 mt-1 text-[10px]">
                                <span class="text-blue-500"><i class="fas fa-arrow-up text-[8px]"></i> <span x-text="a.tx"></span></span>
                                <span class="text-green-500"><i class="fas fa-arrow-down text-[8px]"></i> <span x-text="a.rx"></span></span>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- BANDWIDTH MONITORING --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden"
             x-data="{
                queues: [], bwLoading: true, bwError: false,
                _lastBwJson: '',
                _bwTimer: null,
                _bwFetching: false,
                bwIntervalSec: 15,
                formatBytes(bytes) {
                    if (bytes >= 1048576) return (bytes / 1048576).toFixed(1) + 'M';
                    if (bytes >= 1024) return (bytes / 1024).toFixed(0) + 'K';
                    return bytes + 'b';
                },
                async fetchBandwidth() {
                    if (this._bwFetching) return;
                    this._bwFetching = true;
                    try {
                        const res = await fetch('{{ route('api.bandwidth') }}');
                        if (!res.ok) throw new Error();
                        const data = await res.json();
                        const json = JSON.stringify(data);
                        if (json !== this._lastBwJson) {
                            this.queues = data;
                            this._lastBwJson = json;
                        }
                        this.bwError = false;
                    } catch (e) { this.bwError = true; }
                    finally { this.bwLoading = false; this._bwFetching = false; }
                },
                startBwAutoRefresh() {
                    this.stopBwAutoRefresh();
                    this._bwTimer = setInterval(() => this.fetchBandwidth(), this.bwIntervalSec * 1000);
                },
                stopBwAutoRefresh() {
                    if (this._bwTimer) { clearInterval(this._bwTimer); this._bwTimer = null; }
                }
             }"
             x-init="await fetchBandwidth(); startBwAutoRefresh()"
             @dashboard-refresh.window="fetchBandwidth()"
             @beforeunload.window="stopBwAutoRefresh()">
            <div class="px-4 md:px-5 py-3 md:py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-tachometer-alt text-orange-500"></i> Bandwidth
                </h3>
                <span class="text-xs text-gray-400" x-text="queues.length + ' queue'"></span>
            </div>
            <div class="divide-y divide-gray-50 max-h-52 sm:max-h-72 overflow-y-auto">
                <template x-if="bwLoading">
                    <div class="px-5 py-8 text-center text-gray-400 text-sm">
                        <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                        <p>Memuat bandwidth...</p>
                    </div>
                </template>
                <template x-if="!bwLoading && bwError">
                    <div class="px-5 py-8 text-center text-red-400 text-sm">
                        <i class="fas fa-exclamation-triangle text-2xl mb-2"></i>
                        <p>Gagal memuat bandwidth</p>
                    </div>
                </template>
                <template x-if="!bwLoading && !bwError && queues.length === 0">
                    <div class="px-5 py-8 text-center text-gray-400 text-sm">
                        <i class="fas fa-tachometer-alt text-2xl mb-2 opacity-30"></i>
                        <p>Tidak ada queue aktif</p>
                    </div>
                </template>
                <template x-for="(q, i) in queues" :key="i">
                    <div class="px-5 py-2.5 flex items-center justify-between hover:bg-gray-50/50 transition">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 bg-orange-100 rounded-md flex items-center justify-center">
                                <i class="fas fa-network-wired text-orange-500 text-[10px]"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-800 truncate max-w-[110px]" x-text="q.name"></p>
                                <p class="text-[10px] text-gray-400" x-text="q.target"></p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 text-[11px]">
                            <span class="text-blue-500 font-medium"><i class="fas fa-arrow-up text-[9px]"></i> <span x-text="formatBytes(q.upload)"></span></span>
                            <span class="text-green-500 font-medium"><i class="fas fa-arrow-down text-[9px]"></i> <span x-text="formatBytes(q.download)"></span></span>
                        </div>
                    </div>
                </template>
            </div>
        </div>

    </div>

    {{-- TWO COLUMN: SYSTEM INFO + USER CHART --}}
    <div x-show="!loading" class="grid grid-cols-1 lg:grid-cols-5 gap-3 md:gap-5">

        {{-- SYSTEM INFO --}}
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden"
             x-data="{
                info: null, sysLoading: true, sysError: false,
                _lastSysJson: '',
                _sysTimer: null,
                _sysFetching: false,
                sysIntervalSec: 25,
                async fetchInfo() {
                    if (this._sysFetching) return;
                    this._sysFetching = true;
                    try {
                        const res = await fetch('{{ route('api.systemInfo') }}');
                        if (!res.ok) throw new Error();
                        const data = await res.json();
                        const json = JSON.stringify(data);
                        if (json !== this._lastSysJson) {
                            this.info = data;
                            this._lastSysJson = json;
                        }
                        this.sysError = false;
                    } catch (e) { this.sysError = true; }
                    finally { this.sysLoading = false; this._sysFetching = false; }
                },
                startSysAutoRefresh() {
                    this.stopSysAutoRefresh();
                    this._sysTimer = setInterval(() => this.fetchInfo(), this.sysIntervalSec * 1000);
                },
                stopSysAutoRefresh() {
                    if (this._sysTimer) { clearInterval(this._sysTimer); this._sysTimer = null; }
                }
             }"
             x-init="await fetchInfo(); startSysAutoRefresh()"
             @dashboard-refresh.window="fetchInfo()"
             @beforeunload.window="stopSysAutoRefresh()">>
            <div class="px-4 md:px-5 py-3 md:py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-server text-orange-500"></i> Sistem MikroTik
                </h3>
            </div>
            <div class="p-3 md:p-5">
                <template x-if="sysLoading">
                    <div class="py-8 text-center text-gray-400 text-sm">
                        <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                        <p>Memuat info sistem...</p>
                    </div>
                </template>
                <template x-if="!sysLoading && sysError">
                    <div class="py-8 text-center text-red-400 text-sm">
                        <i class="fas fa-exclamation-triangle text-2xl mb-2"></i>
                        <p>Gagal memuat info sistem</p>
                    </div>
                </template>
                <template x-if="!sysLoading && !sysError && info">
                    <div class="space-y-4">
                        <div class="flex items-center gap-3 pb-3 border-b border-gray-100">
                            <div class="w-10 h-10 bg-gradient-to-br from-orange-500 to-[#E65100] rounded-xl flex items-center justify-center text-white">
                                <i class="fas fa-microchip"></i>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-800" x-text="info.identity"></p>
                                <p class="text-[11px] text-gray-400"><span x-text="info.board"></span> &middot; RouterOS <span x-text="info.version"></span></p>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-500 flex items-center gap-2"><i class="fas fa-clock text-gray-400 w-4"></i> Uptime</span>
                            <span class="text-xs font-semibold text-gray-800 font-mono" x-text="info.uptime"></span>
                        </div>
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="text-xs text-gray-500 flex items-center gap-2"><i class="fas fa-microchip text-orange-400 w-4"></i> CPU</span>
                                <span class="text-xs font-semibold" :class="info.cpuLoad > 80 ? 'text-red-500' : info.cpuLoad > 50 ? 'text-amber-500' : 'text-green-600'" x-text="info.cpuLoad + '%'"></span>
                            </div>
                            <div class="w-full h-2 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-700" :class="info.cpuLoad > 80 ? 'bg-red-500' : info.cpuLoad > 50 ? 'bg-amber-400' : 'bg-green-500'" :style="'width:' + info.cpuLoad + '%'"></div>
                            </div>
                            <p class="text-[10px] text-gray-400 mt-1" x-text="info.cpu + ' (' + info.cpuCount + ' core) · ' + info.architecture"></p>
                        </div>
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="text-xs text-gray-500 flex items-center gap-2"><i class="fas fa-memory text-orange-400 w-4"></i> Memory</span>
                                <span class="text-xs font-semibold" :class="info.memPercent > 80 ? 'text-red-500' : info.memPercent > 50 ? 'text-amber-500' : 'text-green-600'" x-text="info.memPercent + '%'"></span>
                            </div>
                            <div class="w-full h-2 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-700" :class="info.memPercent > 80 ? 'bg-red-500' : info.memPercent > 50 ? 'bg-amber-400' : 'bg-orange-500'" :style="'width:' + info.memPercent + '%'"></div>
                            </div>
                            <p class="text-[10px] text-gray-400 mt-1" x-text="info.usedMemory + ' / ' + info.totalMemory"></p>
                        </div>
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="text-xs text-gray-500 flex items-center gap-2"><i class="fas fa-hdd text-orange-400 w-4"></i> Storage</span>
                                <span class="text-xs font-semibold" :class="info.hddPercent > 80 ? 'text-red-500' : info.hddPercent > 50 ? 'text-amber-500' : 'text-green-600'" x-text="info.hddPercent + '%'"></span>
                            </div>
                            <div class="w-full h-2 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-700" :class="info.hddPercent > 80 ? 'bg-red-500' : info.hddPercent > 50 ? 'bg-amber-400' : 'bg-orange-500'" :style="'width:' + info.hddPercent + '%'"></div>
                            </div>
                            <p class="text-[10px] text-gray-400 mt-1" x-text="info.usedHdd + ' / ' + info.totalHdd"></p>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- USER CHART --}}
        <div class="lg:col-span-3 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-4 md:px-5 py-3 md:py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-chart-area text-green-500"></i> Grafik Pengguna
                </h3>
                <div class="flex items-center gap-1 sm:gap-1.5 text-[10px] sm:text-[11px] flex-wrap">
                    <span class="inline-flex items-center gap-1 bg-green-50 text-green-600 px-1.5 sm:px-2 py-0.5 rounded-full font-medium">
                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span>
                        <span x-text="stats.online"></span> Online
                    </span>
                    <span class="inline-flex items-center gap-1 bg-orange-50 text-orange-600 px-1.5 sm:px-2 py-0.5 rounded-full font-medium">
                        <i class="fas fa-users text-[9px]"></i>
                        <span x-text="stats.total"></span> Total
                    </span>
                    <span class="inline-flex items-center gap-1 bg-red-50 text-red-500 px-1.5 sm:px-2 py-0.5 rounded-full font-medium">
                        <span class="w-1.5 h-1.5 bg-red-400 rounded-full"></span>
                        <span x-text="stats.disabled"></span> Off
                    </span>
                </div>
            </div>
            <div class="p-3 md:p-5">
                <div class="h-[200px] sm:h-[280px]">
                    <canvas id="userChart"></canvas>
                </div>
            </div>
        </div>

    </div>

</div>

{{-- Chart.js CDN --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>

<script>
let chartInstance = null;

function initChart() {
    if (chartInstance) return;
    const ctx = document.getElementById('userChart').getContext('2d');
    const g1 = ctx.createLinearGradient(0, 0, 0, 250);
    g1.addColorStop(0, 'rgba(34,197,94,0.25)'); g1.addColorStop(1, 'rgba(34,197,94,0)');
    const g2 = ctx.createLinearGradient(0, 0, 0, 250);
    g2.addColorStop(0, 'rgba(249,115,22,0.12)'); g2.addColorStop(1, 'rgba(249,115,22,0)');
    chartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [
                { label: 'User Online', data: [], borderColor: '#22c55e', backgroundColor: g1, borderWidth: 2.5, fill: true, tension: 0.4, pointRadius: 3, pointBackgroundColor: '#22c55e', pointBorderColor: '#fff', pointBorderWidth: 2, pointHoverRadius: 5 },
                { label: 'Total User', data: [], borderColor: '#F97316', backgroundColor: g2, borderWidth: 2, fill: true, tension: 0.4, pointRadius: 2, pointBackgroundColor: '#F97316', pointBorderColor: '#fff', pointBorderWidth: 1.5, borderDash: [5,3] },
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            interaction: { intersect: false, mode: 'index' },
            plugins: {
                legend: { display: true, position: 'top', align: 'end', labels: { boxWidth: 12, boxHeight: 3, font: { size: 11 }, padding: 15 } },
                tooltip: { backgroundColor: 'rgba(0,0,0,0.8)', titleFont: { size: 11 }, bodyFont: { size: 12 }, padding: 10, cornerRadius: 8, displayColors: true }
            },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 10 }, color: '#9ca3af', maxRotation: 0 } },
                y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { font: { size: 10 }, color: '#9ca3af', stepSize: 1, precision: 0 } }
            },
            animation: { duration: 400 }
        }
    });
}

function updateChart(data) {
    if (!chartInstance) return;
    chartInstance.data.labels = data.labels;
    chartInstance.data.datasets[0].data = data.onlineData;
    chartInstance.data.datasets[1].data = data.totalData;
    chartInstance.update('none');
}

// Initialize chart after DOM load
document.addEventListener('DOMContentLoaded', () => {
    initChart();
});
</script>

</x-app-layout>
