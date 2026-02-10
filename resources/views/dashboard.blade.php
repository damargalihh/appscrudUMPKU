<x-app-layout>

@section('page-title', 'Dashboard')

{{-- MASTER REALTIME CONTROLLER --}}
<div class="space-y-4 md:space-y-6"
     x-data="{
        // Stat card data
        stats: { total: 0, online: 0, enabled: 0, disabled: 0, time: '' },
        // Profiles
        profiles: [], profilesLoading: true,
        // Active users
        actives: [], activesLoading: true,
        // Chart
        labels: [], onlineData: [], totalData: [], maxPoints: 30, chartLoading: true,
        // Non-reactive chart data
        chartData: { labels: [], onlineData: [], totalData: [] },

        async fetchStats() {
            try {
                const res = await fetch('{{ route('api.userStats') }}');
                if (!res.ok) throw new Error();
                const data = await res.json();
                this.stats = data;
                // Use current time for label (client clock, more real-time)
                const now = new Date();
                const label = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
                this.labels.push(label);
                this.onlineData.push(data.online);
                this.totalData.push(data.total);
                if (this.labels.length > this.maxPoints) {
                    this.labels.shift(); this.onlineData.shift(); this.totalData.shift();
                }
                // Update non-reactive chart data
                this.chartData.labels = [...this.labels];
                this.chartData.onlineData = [...this.onlineData];
                this.chartData.totalData = [...this.totalData];
                // Update chart asynchronously
                setTimeout(() => {
                    updateChart(this.chartData);
                }, 0);
            } catch (e) { console.error(e); }
            finally { this.chartLoading = false; }
        },
        async fetchProfiles() {
            try {
                const res = await fetch('{{ route('api.profiles') }}');
                if (!res.ok) throw new Error();
                this.profiles = await res.json();
            } catch (e) { console.error(e); }
            finally { this.profilesLoading = false; }
        },
        async fetchActives() {
            try {
                const res = await fetch('{{ route('api.activeUsers') }}');
                if (!res.ok) throw new Error();
                this.actives = await res.json();
            } catch (e) { console.error(e); }
            finally { this.activesLoading = false; }
        },
        async init() {
            // Ambil data awal agar chart tidak kosong
            await this.fetchStats();
            await Promise.all([this.fetchProfiles(), this.fetchActives()]);
            // Update chart setiap detik
            setInterval(() => {
                this.fetchStats();
            }, 1000);
            // Update data lain tiap 5 detik
            setInterval(() => {
                this.fetchProfiles();
                this.fetchActives();
            }, 5000);
        }
     }"
     x-init="init()">

    {{-- WELCOME BANNER --}}
    <div class="rounded-2xl bg-gradient-to-r from-[#FF8C00] via-[#FFA726] to-[#E65100] p-3 md:p-6 flex items-center justify-between shadow-xl border border-orange-200/40">
        <div>
            <h1 class="text-sm md:text-xl font-bold text-white drop-shadow">Halo, {{ auth()->user()->name }} <span class="inline-block animate-bounce">👋</span></h1>
            <p class="text-white/80 text-xs mt-1">Selamat datang di panel Admin Hotspot UMPKU.</p>
        </div>
        <div class="hidden md:flex w-32 h-32 rounded-2xl items-center justify-center overflow-hidden bg-transparent">
            <img src="{{ asset('img/logoadmin.png') }}" alt="Admin" class="w-20 h-20 object-contain">
        </div>
    </div>

    {{-- STAT CARDS (REALTIME) --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4">
        <div class="bg-white rounded-xl p-3 md:p-4 shadow-sm border border-gray-100 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold">Total User</p>
                    <p class="text-xl md:text-2xl font-bold text-gray-800 mt-1" x-text="stats.total">-</p>
                </div>
                <div class="w-10 h-10 md:w-11 md:h-11 bg-blue-50 rounded-xl flex items-center justify-center">
                    <i class="fas fa-users text-blue-500"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl p-3 md:p-4 shadow-sm border border-gray-100 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold">User Online</p>
                    <p class="text-xl md:text-2xl font-bold text-green-600 mt-1" x-text="stats.online">-</p>
                </div>
                <div class="w-10 h-10 md:w-11 md:h-11 bg-green-50 rounded-xl flex items-center justify-center">
                    <i class="fas fa-signal text-green-500"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl p-3 md:p-4 shadow-sm border border-gray-100 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold">Profile</p>
                    <p class="text-xl md:text-2xl font-bold text-gray-800 mt-1" x-text="profiles.length">-</p>
                </div>
                <div class="w-10 h-10 md:w-11 md:h-11 bg-amber-50 rounded-xl flex items-center justify-center">
                    <i class="fas fa-layer-group text-amber-500"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl p-3 md:p-4 shadow-sm border border-gray-100 hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-400 uppercase tracking-wider font-semibold">Disabled</p>
                    <p class="text-xl md:text-2xl font-bold text-red-500 mt-1" x-text="stats.disabled">-</p>
                </div>
                <div class="w-10 h-10 md:w-11 md:h-11 bg-red-50 rounded-xl flex items-center justify-center">
                    <i class="fas fa-user-slash text-red-400"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- THREE COLUMN: PROFILES + ACTIVE USERS + BANDWIDTH --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- PROFILE HOTSPOT (REALTIME) --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-4 md:px-5 py-3 md:py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-layer-group text-amber-500"></i> Profile
                    <span class="inline-flex items-center gap-1 text-[10px] text-green-500 font-normal">
                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span> Live
                    </span>
                </h3>
                <span class="text-xs text-gray-400" x-text="profiles.length"></span>
            </div>
            <div class="divide-y divide-gray-50 max-h-72 overflow-y-auto">
                <template x-if="profilesLoading">
                    <div class="px-5 py-8 text-center text-gray-400 text-sm">
                        <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                        <p>Memuat profile...</p>
                    </div>
                </template>
                <template x-if="!profilesLoading && profiles.length === 0">
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
                        <form method="POST" :action="'/hotspot-profiles/' + p.id"
                              onsubmit="return confirm('Yakin hapus profile ini?')">
                            @csrf
                            @method('DELETE')
                            <button class="opacity-0 group-hover:opacity-100 text-xs text-red-400 hover:text-red-600 transition-all px-2 py-1 rounded hover:bg-red-50">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>
                    </div>
                </template>
            </div>
        </div>

        {{-- USER AKTIF / MENGGUNAKAN JARINGAN (REALTIME) --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-4 md:px-5 py-3 md:py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-wifi text-green-500"></i> Sedang Menggunakan Jaringan
                    <span class="inline-flex items-center gap-1 text-[10px] text-green-500 font-normal">
                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span> Live
                    </span>
                </h3>
                <span class="inline-flex items-center gap-1.5 text-xs text-green-600 bg-green-50 px-2.5 py-1 rounded-full">
                    <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span>
                    <span x-text="actives.length"></span>
                </span>
            </div>
            <div class="divide-y divide-gray-50 max-h-72 overflow-y-auto">
                <template x-if="activesLoading">
                    <div class="px-5 py-8 text-center text-gray-400 text-sm">
                        <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                        <p>Memuat user aktif...</p>
                    </div>
                </template>
                <template x-if="!activesLoading && actives.length === 0">
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

        {{-- BANDWIDTH MONITORING (REALTIME) --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden"
             x-data="{
                queues: [], bwLoading: true, bwError: false,
                formatBytes(bytes) {
                    if (bytes >= 1048576) return (bytes / 1048576).toFixed(1) + 'M';
                    if (bytes >= 1024) return (bytes / 1024).toFixed(0) + 'K';
                    return bytes + 'b';
                },
                async fetchBandwidth() {
                    try {
                        const res = await fetch('{{ route('api.bandwidth') }}');
                        if (!res.ok) throw new Error();
                        this.queues = await res.json();
                        this.bwError = false;
                    } catch (e) { this.bwError = true; }
                    finally { this.bwLoading = false; }
                }
             }"
             x-init="fetchBandwidth(); setInterval(() => fetchBandwidth(), 5000)">
            <div class="px-4 md:px-5 py-3 md:py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-tachometer-alt text-purple-500"></i> Bandwidth
                    <span x-show="!bwLoading" class="inline-flex items-center gap-1 text-[10px] text-green-500 font-normal">
                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span> Live
                    </span>
                </h3>
                <span class="text-xs text-gray-400" x-text="queues.length + ' queue'"></span>
            </div>
            <div class="divide-y divide-gray-50 max-h-72 overflow-y-auto">
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
                            <div class="w-7 h-7 bg-purple-100 rounded-md flex items-center justify-center">
                                <i class="fas fa-network-wired text-purple-500 text-[10px]"></i>
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

    {{-- TWO COLUMN: SYSTEM INFO + REALTIME USER CHART --}}
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

        {{-- SYSTEM INFO (REALTIME) --}}
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden"
             x-data="{
                info: null, sysLoading: true, sysError: false,
                async fetchInfo() {
                    try {
                        const res = await fetch('{{ route('api.systemInfo') }}');
                        if (!res.ok) throw new Error();
                        this.info = await res.json();
                        this.sysError = false;
                    } catch (e) { this.sysError = true; }
                    finally { this.sysLoading = false; }
                }
             }"
             x-init="fetchInfo(); setInterval(() => fetchInfo(), 10000)">
            <div class="px-4 md:px-5 py-3 md:py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-server text-indigo-500"></i> Sistem MikroTik
                    <span x-show="!sysLoading && !sysError" class="inline-flex items-center gap-1 text-[10px] text-green-500 font-normal">
                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span> Live
                    </span>
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
                            <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center text-white">
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
                                <span class="text-xs text-gray-500 flex items-center gap-2"><i class="fas fa-microchip text-blue-400 w-4"></i> CPU</span>
                                <span class="text-xs font-semibold" :class="info.cpuLoad > 80 ? 'text-red-500' : info.cpuLoad > 50 ? 'text-amber-500' : 'text-green-600'" x-text="info.cpuLoad + '%'"></span>
                            </div>
                            <div class="w-full h-2 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-700" :class="info.cpuLoad > 80 ? 'bg-red-500' : info.cpuLoad > 50 ? 'bg-amber-400' : 'bg-green-500'" :style="'width:' + info.cpuLoad + '%'"></div>
                            </div>
                            <p class="text-[10px] text-gray-400 mt-1" x-text="info.cpu + ' (' + info.cpuCount + ' core) · ' + info.architecture"></p>
                        </div>
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="text-xs text-gray-500 flex items-center gap-2"><i class="fas fa-memory text-purple-400 w-4"></i> Memory</span>
                                <span class="text-xs font-semibold" :class="info.memPercent > 80 ? 'text-red-500' : info.memPercent > 50 ? 'text-amber-500' : 'text-green-600'" x-text="info.memPercent + '%'"></span>
                            </div>
                            <div class="w-full h-2 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-700" :class="info.memPercent > 80 ? 'bg-red-500' : info.memPercent > 50 ? 'bg-amber-400' : 'bg-purple-500'" :style="'width:' + info.memPercent + '%'"></div>
                            </div>
                            <p class="text-[10px] text-gray-400 mt-1" x-text="info.usedMemory + ' / ' + info.totalMemory"></p>
                        </div>
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="text-xs text-gray-500 flex items-center gap-2"><i class="fas fa-hdd text-amber-400 w-4"></i> Storage</span>
                                <span class="text-xs font-semibold" :class="info.hddPercent > 80 ? 'text-red-500' : info.hddPercent > 50 ? 'text-amber-500' : 'text-green-600'" x-text="info.hddPercent + '%'"></span>
                            </div>
                            <div class="w-full h-2 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-700" :class="info.hddPercent > 80 ? 'bg-red-500' : info.hddPercent > 50 ? 'bg-amber-400' : 'bg-amber-500'" :style="'width:' + info.hddPercent + '%'"></div>
                            </div>
                            <p class="text-[10px] text-gray-400 mt-1" x-text="info.usedHdd + ' / ' + info.totalHdd"></p>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- REALTIME USER CHART --}}
        <div class="lg:col-span-3 bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-4 md:px-5 py-3 md:py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-chart-area text-green-500"></i> Grafik Pengguna Realtime
                    <span x-show="!chartLoading" class="inline-flex items-center gap-1 text-[10px] text-green-500 font-normal">
                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span> Live
                    </span>
                </h3>
                <div class="flex items-center gap-1.5 text-[11px]">
                    <span class="inline-flex items-center gap-1 bg-green-50 text-green-600 px-2 py-0.5 rounded-full font-medium">
                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span>
                        <span x-text="stats.online"></span> Online
                    </span>
                    <span class="inline-flex items-center gap-1 bg-blue-50 text-blue-600 px-2 py-0.5 rounded-full font-medium">
                        <i class="fas fa-users text-[9px]"></i>
                        <span x-text="stats.total"></span> Total
                    </span>
                    <span class="inline-flex items-center gap-1 bg-red-50 text-red-500 px-2 py-0.5 rounded-full font-medium">
                        <span class="w-1.5 h-1.5 bg-red-400 rounded-full"></span>
                        <span x-text="stats.disabled"></span> Off
                    </span>
                </div>
            </div>
            <div class="p-3 md:p-5">
                <template x-if="chartLoading">
                    <div class="py-12 text-center text-gray-400 text-sm">
                        <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                        <p>Memuat grafik...</p>
                    </div>
                </template>
                <div x-show="!chartLoading" style="height: 280px;">
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
    g2.addColorStop(0, 'rgba(59,130,246,0.12)'); g2.addColorStop(1, 'rgba(59,130,246,0)');
    chartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [
                { label: 'User Online', data: [], borderColor: '#22c55e', backgroundColor: g1, borderWidth: 2.5, fill: true, tension: 0.4, pointRadius: 3, pointBackgroundColor: '#22c55e', pointBorderColor: '#fff', pointBorderWidth: 2, pointHoverRadius: 5 },
                { label: 'Total User', data: [], borderColor: '#3b82f6', backgroundColor: g2, borderWidth: 2, fill: true, tension: 0.4, pointRadius: 2, pointBackgroundColor: '#3b82f6', pointBorderColor: '#fff', pointBorderWidth: 1.5, borderDash: [5,3] },
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
