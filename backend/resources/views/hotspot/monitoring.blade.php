<x-app-layout>

@section('page-title', 'Monitoring User')

<div class="space-y-4 md:space-y-5" x-data="monitoringUsers()" x-init="start()">

    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
        <div>
            <h1 class="text-sm md:text-lg font-bold text-gray-800">Monitoring User</h1>
            <p class="text-[11px] text-gray-400 mt-0.5">Pantau user yang sedang aktif menggunakan jaringan</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-[11px] bg-green-100 text-green-600 px-2.5 py-1 rounded-full font-medium flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span>
                <span x-text="activeUsers.length + ' user aktif'"></span>
            </span>
            <button @click="loading = true; refreshActiveUsers().then(() => loading = false)"
                    class="text-[10px] md:text-xs text-gray-500 border border-gray-200 hover:border-green-300 hover:bg-green-50 rounded-lg px-2.5 py-1 transition flex items-center gap-1">
                <i class="fas fa-sync-alt" :class="loading ? 'fa-spin' : ''"></i> Refresh
            </button>
        </div>
    </div>

    {{-- STATS CARDS --}}
    <div class="grid grid-cols-3 gap-3" x-show="!loading && activeUsers.length > 0" x-transition>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-3 py-2.5">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-green-50 rounded-lg flex items-center justify-center">
                    <i class="fas fa-users text-green-500 text-sm"></i>
                </div>
                <div>
                    <p class="text-[10px] text-gray-400 font-medium uppercase tracking-wider">Total Online</p>
                    <p class="text-sm font-bold text-gray-800" x-text="activeUsers.length"></p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-3 py-2.5">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center">
                    <i class="fas fa-arrow-down text-blue-500 text-sm"></i>
                </div>
                <div>
                    <p class="text-[10px] text-gray-400 font-medium uppercase tracking-wider">Total Download</p>
                    <p class="text-sm font-bold text-blue-600" x-text="statsTotalRx()"></p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-3 py-2.5">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-orange-50 rounded-lg flex items-center justify-center">
                    <i class="fas fa-arrow-up text-orange-500 text-sm"></i>
                </div>
                <div>
                    <p class="text-[10px] text-gray-400 font-medium uppercase tracking-wider">Total Upload</p>
                    <p class="text-sm font-bold text-orange-600" x-text="statsTotalTx()"></p>
                </div>
            </div>
        </div>
    </div>

    {{-- TOAST --}}
    @if(session('success'))
    <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 4000)"
         class="bg-green-50 border border-green-200 text-green-700 text-xs rounded-lg px-3 py-2.5 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <i class="fas fa-check-circle"></i>
            <span>{{ session('success') }}</span>
        </div>
        <button @click="show = false" class="opacity-60 hover:opacity-100"><i class="fas fa-times text-[10px]"></i></button>
    </div>
    @endif
    @if(session('error'))
    <div x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 4000)"
         class="bg-red-50 border border-red-200 text-red-600 text-xs rounded-lg px-3 py-2.5 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <i class="fas fa-exclamation-circle"></i>
            <span>{{ session('error') }}</span>
        </div>
        <button @click="show = false" class="opacity-60 hover:opacity-100"><i class="fas fa-times text-[10px]"></i></button>
    </div>
    @endif

    {{-- TABEL USER AKTIF --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">

        {{-- TOOLBAR --}}
        <div class="px-3 md:px-4 py-3 border-b border-gray-100 sticky top-0 z-10 bg-white space-y-2.5">
            {{-- Row 1: Title + Search + Per Page --}}
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
                <h3 class="text-xs md:text-sm font-semibold text-gray-700 flex items-center gap-2">
                    <i class="fas fa-wifi text-green-500"></i> User Aktif
                    <span class="text-[10px] bg-green-100 text-green-600 px-2 py-0.5 rounded-full font-medium" x-text="activeUsers.length"></span>
                </h3>
                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <div class="relative flex-1 sm:flex-initial">
                        <i class="fas fa-search absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-[10px]"></i>
                        <input x-model="search" type="text" placeholder="Cari username / IP..."
                               class="w-full sm:w-56 pl-7 pr-8 py-1.5 text-[11px] border border-gray-200 rounded-lg focus:ring-1 focus:ring-green-300 transition">
                        <button x-show="search.length > 0" @click="search = ''" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times text-[9px]"></i>
                        </button>
                    </div>
                    <button @click="toggleFilters()"
                            class="text-[11px] border rounded-lg px-2.5 py-1.5 transition flex items-center gap-1.5"
                            :class="showFilters ? 'border-green-400 bg-green-500 text-white shadow-sm' : 'border-gray-200 text-gray-500 hover:border-gray-300 hover:bg-gray-50'">
                        <i class="fas fa-sliders-h text-[10px]"></i>
                        <span class="hidden sm:inline" x-text="showFilters ? 'Filter Aktif' : 'Filter'"></span>
                    </button>
                </div>
            </div>

            {{-- Row 2: Filter Panel (collapsible) --}}
            <div x-show="showFilters" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1"
                 class="bg-gray-50/80 rounded-lg p-3 border border-gray-100 space-y-3">

                <div class="flex items-center justify-between">
                    <h4 class="text-[11px] font-semibold text-gray-600 flex items-center gap-1.5">
                        <i class="fas fa-filter text-[10px] text-gray-400"></i> Filter & Sortir
                    </h4>
                    <button @click="clearAllFilters()" x-show="activeFilterCount() > 0"
                            class="text-[10px] text-red-500 hover:text-red-700 font-medium flex items-center gap-1 transition">
                        <i class="fas fa-times-circle text-[9px]"></i> Reset Semua
                    </button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    {{-- Subnet Filter --}}
                    <div>
                        <label class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-1 block">
                            <i class="fas fa-network-wired text-[9px] text-gray-400 mr-0.5"></i> Subnet IP
                        </label>
                        <select x-model="subnetFilter"
                                class="w-full text-[11px] border rounded-lg px-2.5 py-1.5 focus:ring-1 focus:ring-green-300 transition bg-white"
                                :class="subnetFilter !== 'all' ? 'border-green-300 text-green-700 bg-green-50' : 'border-gray-200 text-gray-600'">
                            <option value="all">Semua Subnet</option>
                            <template x-for="subnet in uniqueSubnets()" :key="subnet">
                                <option :value="subnet" x-text="subnet + ' (' + subnetCount(subnet) + ')'"></option>
                            </template>
                        </select>
                    </div>

                    {{-- Sort --}}
                    <div>
                        <label class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-1 block">
                            <i class="fas fa-sort text-[9px] text-gray-400 mr-0.5"></i> Urutkan
                        </label>
                        <div class="flex gap-1.5">
                            <select x-model="sortBy"
                                    class="flex-1 text-[11px] border border-gray-200 rounded-lg px-2.5 py-1.5 focus:ring-1 focus:ring-green-300 transition bg-white text-gray-600">
                                <option value="user">Username</option>
                                <option value="address">IP Address</option>
                                <option value="uptime">Uptime</option>
                                <option value="rx">Download</option>
                                <option value="tx">Upload</option>
                            </select>
                        </div>
                    </div>

                    {{-- Tampilkan (Per Page) --}}
                    <div>
                        <label class="text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-1 block">
                            <i class="fas fa-list-ol text-[9px] text-gray-400 mr-0.5"></i> Tampilkan
                        </label>
                        <div class="flex gap-1.5">
                            <select x-model.number="perPage"
                                    class="flex-1 text-[11px] border border-gray-200 rounded-lg px-2.5 py-1.5 focus:ring-1 focus:ring-green-300 transition bg-white text-gray-600">
                                <option value="10">10 per halaman</option>
                                <option value="25">25 per halaman</option>
                                <option value="50">50 per halaman</option>
                                <option value="100">100 per halaman</option>
                            </select>
                            <button @click="sortDir = sortDir === 'asc' ? 'desc' : 'asc'"
                                    class="px-2.5 py-1.5 text-[11px] border border-gray-200 rounded-lg hover:bg-green-50 hover:border-green-300 transition flex items-center gap-1 text-gray-600">
                                <i :class="sortDir === 'asc' ? 'fas fa-sort-amount-up-alt' : 'fas fa-sort-amount-down-alt'"></i>
                                <span x-text="sortDir === 'asc' ? 'A-Z' : 'Z-A'" class="hidden sm:inline"></span>
                            </button>
                        </div>
                    </div>                  
                </div>

                {{-- Active Filter Tags --}}
                <div class="flex flex-wrap gap-1.5" x-show="activeFilterCount() > 0">
                    <template x-if="search.trim() !== ''">
                        <span class="inline-flex items-center gap-1 text-[10px] bg-green-100 text-green-700 px-2 py-0.5 rounded-full font-medium">
                            <i class="fas fa-search text-[8px]"></i>
                            "<span x-text="search"></span>"
                            <button @click="search = ''" class="hover:text-green-900 ml-0.5"><i class="fas fa-times text-[8px]"></i></button>
                        </span>
                    </template>
                    <template x-if="subnetFilter !== 'all'">
                        <span class="inline-flex items-center gap-1 text-[10px] bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full font-medium">
                            <i class="fas fa-network-wired text-[8px]"></i>
                            <span x-text="subnetFilter"></span>
                            <button @click="subnetFilter = 'all'" class="hover:text-blue-900 ml-0.5"><i class="fas fa-times text-[8px]"></i></button>
                        </span>
                    </template>
                </div>
            </div>
        </div>

        {{-- DESKTOP TABLE --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 text-left">
                        <th class="px-4 py-2.5 text-[11px] font-semibold text-gray-400 uppercase tracking-wider">#</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold text-gray-400 uppercase tracking-wider cursor-pointer select-none hover:text-gray-600 transition" @click="toggleSort('user')">
                            <span class="flex items-center gap-1">Username <i :class="sortIcon('user')" class="text-[9px]"></i></span>
                        </th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold text-gray-400 uppercase tracking-wider cursor-pointer select-none hover:text-gray-600 transition" @click="toggleSort('address')">
                            <span class="flex items-center gap-1">IP Address <i :class="sortIcon('address')" class="text-[9px]"></i></span>
                        </th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold text-gray-400 uppercase tracking-wider cursor-pointer select-none hover:text-gray-600 transition" @click="toggleSort('uptime')">
                            <span class="flex items-center gap-1">Uptime <i :class="sortIcon('uptime')" class="text-[9px]"></i></span>
                        </th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold text-gray-400 uppercase tracking-wider cursor-pointer select-none hover:text-gray-600 transition" @click="toggleSort('rx')">
                            <span class="flex items-center gap-1">Download <i :class="sortIcon('rx')" class="text-[9px]"></i></span>
                        </th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold text-gray-400 uppercase tracking-wider cursor-pointer select-none hover:text-gray-600 transition" @click="toggleSort('tx')">
                            <span class="flex items-center gap-1">Upload <i :class="sortIcon('tx')" class="text-[9px]"></i></span>
                        </th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <template x-for="(au, i) in paginatedUsers()" :key="au.id || au.user || i">
                        <tr class="hover:bg-gray-50/70 transition">
                            <td class="px-4 py-2.5 text-xs text-gray-400" x-text="pageStart() + i"></td>
                            <td class="px-4 py-2.5">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 bg-gradient-to-br from-green-400 to-green-600 rounded-md flex items-center justify-center text-white text-[10px] font-bold"
                                         x-text="(au.user || '-').charAt(0).toUpperCase()"></div>
                                    <span class="text-sm font-medium text-gray-800" x-text="au.user"></span>
                                </div>
                            </td>
                            <td class="px-4 py-2.5">
                                <span class="text-xs text-gray-600 font-mono" x-text="au.address"></span>
                            </td>
                            <td class="px-4 py-2.5">
                                <span class="text-xs text-gray-600" x-text="au.uptime"></span>
                            </td>
                            <td class="px-4 py-2.5">
                                <span class="text-xs text-blue-600 font-medium"><i class="fas fa-arrow-down text-[9px]"></i> <span x-text="au.rx"></span></span>
                            </td>
                            <td class="px-4 py-2.5">
                                <span class="text-xs text-orange-600 font-medium"><i class="fas fa-arrow-up text-[9px]"></i> <span x-text="au.tx"></span></span>
                            </td>
                            <td class="px-4 py-2.5">
                                <form method="POST" :action="`/hotspot-users/cutoff/${au.id}`" onsubmit="return confirm('Yakin cut off session ini dari jaringan?')">
                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                    <button class="text-[11px] font-medium px-2.5 py-1 rounded-md text-red-600 bg-red-50 hover:bg-red-100 transition flex items-center gap-1">
                                        <i class="fas fa-plug-circle-xmark text-[10px]"></i> Cut Off
                                    </button>
                                </form>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        {{-- MOBILE CARD VIEW --}}
        <div class="md:hidden divide-y divide-gray-100">
            <template x-for="(au, i) in paginatedUsers()" :key="au.id || au.user || i">
                <div class="p-3 hover:bg-gray-50/50 transition">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 min-w-0 flex-1">
                            <div class="w-8 h-8 bg-gradient-to-br from-green-400 to-green-600 rounded-lg flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                                 x-text="(au.user || '-').charAt(0).toUpperCase()"></div>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-gray-800 truncate" x-text="au.user"></p>
                                <div class="flex items-center gap-1.5 mt-0.5 flex-wrap">
                                    <span class="text-[10px] text-gray-500 font-mono" x-text="au.address"></span>
                                    <span class="text-[10px] text-gray-400">|</span>
                                    <span class="text-[10px] text-gray-500" x-text="au.uptime"></span>
                                </div>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <span class="text-[10px] text-blue-600"><i class="fas fa-arrow-down"></i> <span x-text="au.rx"></span></span>
                                    <span class="text-[10px] text-orange-600"><i class="fas fa-arrow-up"></i> <span x-text="au.tx"></span></span>
                                </div>
                            </div>
                        </div>
                        <form method="POST" :action="`/hotspot-users/cutoff/${au.id}`" onsubmit="return confirm('Yakin cut off session ini?')">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <button class="text-[10px] font-medium px-2.5 py-1.5 rounded-md text-red-600 bg-red-50 active:bg-red-100 transition flex items-center gap-1 flex-shrink-0">
                                <i class="fas fa-plug-circle-xmark text-[9px]"></i> Cut Off
                            </button>
                        </form>
                    </div>
                </div>
            </template>
        </div>

        {{-- Loading --}}
        <div class="py-8 text-center text-gray-400" x-show="loading">
            <i class="fas fa-spinner fa-spin text-2xl mb-2 text-green-400"></i>
            <p class="text-xs">Memuat user aktif...</p>
        </div>

        <div class="py-8 text-center text-gray-400" x-show="!loading && activeUsers.length === 0">
            <i class="fas fa-wifi text-2xl mb-2 opacity-30"></i>
            <p class="text-xs">Tidak ada user yang sedang menggunakan jaringan</p>
        </div>

        <div class="py-8 text-center text-gray-400" x-show="!loading && activeUsers.length > 0 && totalFiltered() === 0">
            <i class="fas fa-search text-2xl mb-2 opacity-30"></i>
            <p class="text-xs">Tidak ada user yang cocok dengan pencarian</p>
        </div>

        {{-- PAGINATION --}}
        <div class="px-3 md:px-4 py-3 border-t border-gray-100 bg-gray-50/50" x-show="!loading && totalFiltered() > 0">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-2">
                <p class="text-[11px] text-gray-500">
                    Menampilkan <span class="font-semibold text-gray-700" x-text="pageStart()"></span>
                    - <span class="font-semibold text-gray-700" x-text="pageEnd()"></span>
                    dari <span class="font-semibold text-gray-700" x-text="totalFiltered()"></span> user
                    <template x-if="activeFilterCount() > 0">
                        <span>(difilter dari <span class="font-semibold" x-text="activeUsers.length"></span> total)</span>
                    </template>
                </p>
                <nav class="flex items-center gap-1" x-show="totalPages() > 1">
                    <button @click="prevPage()" :disabled="currentPage <= 1"
                            class="px-2 py-1 text-[11px] rounded-md border transition"
                            :class="currentPage <= 1 ? 'border-gray-100 text-gray-300 cursor-not-allowed' : 'border-gray-200 text-gray-600 hover:bg-green-50 hover:border-green-300'">
                        <i class="fas fa-chevron-left text-[9px]"></i>
                    </button>
                    <template x-for="page in visiblePages()" :key="'page-' + page">
                        <button @click="page !== '...' && goToPage(page)"
                                class="min-w-[28px] px-1.5 py-1 text-[11px] rounded-md border transition"
                                :class="page === currentPage ? 'bg-green-500 text-white border-green-500 font-semibold' : (page === '...' ? 'border-transparent text-gray-400 cursor-default' : 'border-gray-200 text-gray-600 hover:bg-green-50 hover:border-green-300')">
                            <span x-text="page"></span>
                        </button>
                    </template>
                    <button @click="nextPage()" :disabled="currentPage >= totalPages()"
                            class="px-2 py-1 text-[11px] rounded-md border transition"
                            :class="currentPage >= totalPages() ? 'border-gray-100 text-gray-300 cursor-not-allowed' : 'border-gray-200 text-gray-600 hover:bg-green-50 hover:border-green-300'">
                        <i class="fas fa-chevron-right text-[9px]"></i>
                    </button>
                </nav>
            </div>
        </div>
    </div>

</div>

</x-app-layout>
