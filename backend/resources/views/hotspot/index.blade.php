<x-app-layout>

@section('page-title', 'Kelola User')

<div class="space-y-4 md:space-y-5" x-data="hotspotUsersTable()" x-init="start()">

    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
        <div>
            <h1 class="text-sm md:text-lg font-bold text-gray-800">Manajemen User Hotspot</h1>
            <p class="text-[11px] text-gray-400 mt-0.5">Tambah, edit, dan kelola user MikroTik</p>
        </div>
        <span class="text-[11px] bg-gray-100 text-gray-500 px-2.5 py-1 rounded-full font-medium" x-text="users.length + ' user terdaftar'"></span>
    </div>

    {{-- VALIDATION ERRORS --}}
    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg px-3 py-2.5">
            <div class="flex items-start gap-2">
                <i class="fas fa-exclamation-circle mt-0.5 flex-shrink-0"></i>
                <div>
                    <p class="font-semibold text-xs">Ada masalah pada input:</p>
                    <ul class="list-disc list-inside mt-1 text-xs">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    {{-- FORM TAMBAH USER --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-3 md:p-5">
        <h3 class="text-xs md:text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
            <i class="fas fa-user-plus text-orange-500"></i> Tambah User Baru
        </h3>
        <form method="POST" action="{{ route('hotspot.store') }}">
            @csrf
            <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-2 md:gap-3">
                <div class="relative">
                    <i class="fas fa-user absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <input type="text" name="name" placeholder="Username"
                           class="w-full pl-8 pr-2 py-2 text-xs border border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-200 focus:border-orange-400 transition" required>
                </div>
                <div class="relative">
                    <i class="fas fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <input type="password" name="password" placeholder="Password"
                           class="w-full pl-8 pr-2 py-2 text-xs border border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-200 focus:border-orange-400 transition" required>
                </div>
                <div class="relative">
                    <i class="fas fa-layer-group absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <select name="profile" class="w-full pl-8 pr-2 py-2 text-xs border border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-200 focus:border-orange-400 transition appearance-none bg-white" required>
                        <option value="">Pilih Profile</option>
                        <template x-for="profile in profiles" :key="profile.name">
                            <option :value="profile.name" x-text="profile.name"></option>
                        </template>
                    </select>
                </div>
                <button type="submit" class="bg-gradient-to-r from-[#FF8C00] to-[#E65100] text-white text-xs font-semibold rounded-lg hover:shadow-md transition flex items-center justify-center gap-1.5 py-2">
                    <i class="fas fa-plus text-[10px]"></i> Tambah
                </button>
            </div>
        </form>
    </div>

    {{-- UPLOAD CSV --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-3 md:p-5">
        <h3 class="text-xs md:text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
            <i class="fas fa-file-csv text-green-600"></i> Upload dari CSV
        </h3>
        <form method="POST" action="{{ route('hotspot.upload') }}" enctype="multipart/form-data">
            @csrf
            <div class="flex flex-col sm:flex-row gap-2 sm:items-center">
                <input type="file" name="file" accept=".csv"
                       class="w-full sm:w-auto text-xs border border-gray-200 rounded-lg px-3 py-2 bg-white focus:ring-2 focus:ring-green-200 focus:border-green-400" required>
                <button type="submit" class="bg-gradient-to-r from-green-600 to-emerald-600 text-white text-xs font-semibold rounded-lg hover:shadow-md transition flex items-center justify-center gap-1.5 px-4 py-2">
                    <i class="fas fa-upload text-[10px]"></i> Upload
                </button>
                <a href="{{ route('hotspot.downloadTemplate') }}" class="bg-gradient-to-r from-blue-500 to-blue-600 text-white text-xs font-semibold rounded-lg hover:shadow-md transition flex items-center justify-center gap-1.5 px-4 py-2">
                    <i class="fas fa-download text-[10px]"></i> Download Template
                </a>
            </div>
            <p class="text-[10px] text-gray-400 mt-1.5">Format CSV: username, email, password, profile (header di baris 1).</p>
        </form>
    </div>

    {{-- TABEL USER --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden"
         x-data="{ toast: '', toastType: 'success', toastTimer: null, showToast(msg, type = 'success') { this.toast = msg; this.toastType = type; clearTimeout(this.toastTimer); this.toastTimer = setTimeout(() => this.toast = '', 4000); } }"
         x-init="@if(session('success')) showToast('{{ session('success') }}', 'success') @endif @if(session('error')) showToast('{{ session('error') }}', 'error') @endif">
        {{-- SEARCH & FILTER BAR (sticky) --}}
        <div class="px-3 md:px-4 py-3 border-b border-gray-100 sticky top-0 z-10 bg-white">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
                <div class="flex items-center gap-1.5 flex-wrap">
                    <h3 class="text-xs md:text-sm font-semibold text-gray-700 flex items-center gap-2">
                        <i class="fas fa-list text-orange-500"></i> Daftar User
                        <span class="text-[10px] bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full font-medium" x-text="filteredUsers().length"></span>
                    </h3>
                    <button @click="toggleSelectAll()" class="text-[10px] md:text-xs border rounded-lg px-2 py-1 transition flex items-center gap-1"
                        :class="allSelected() ? 'text-orange-700 border-orange-400 bg-orange-100' : 'text-gray-500 border-gray-200 hover:border-orange-300 hover:bg-orange-50'">
                        <i class="fas" :class="allSelected() ? 'fa-square-check' : 'fa-square'"></i>
                        <span x-text="allSelected() ? 'Batal Semua' : 'Pilih Semua'"></span>
                    </button>
                    {{-- Hapus Terpilih --}}
                    <form x-show="selected.length > 0" x-transition method="POST" action="{{ route('hotspot.bulkDestroy') }}" onsubmit="return confirm('Yakin hapus user yang dipilih?')" class="flex items-center">
                        @csrf
                        <template x-for="id in selected" :key="id">
                            <input type="hidden" name="ids[]" :value="id">
                        </template>
                        <button type="submit" class="text-[10px] md:text-xs text-red-500 border border-red-200 hover:border-red-400 bg-red-50 hover:bg-red-100 rounded-lg px-2 py-1 transition flex items-center gap-1">
                            <i class="fas fa-trash-alt"></i>
                            <span>Hapus (<span x-text="selected.length"></span>)</span>
                        </button>
                    </form>
                    {{-- Inline toast notification --}}
                    <div x-show="toast" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-2" x-transition:enter-end="opacity-100 translate-x-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                         class="text-[10px] md:text-xs px-2.5 py-1 rounded-lg flex items-center gap-1.5 font-medium"
                         :class="toastType === 'success' ? 'bg-green-100 text-green-700 border border-green-200' : 'bg-red-100 text-red-600 border border-red-200'">
                        <i class="fas" :class="toastType === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'"></i>
                        <span x-text="toast"></span>
                        <button @click="toast = ''" class="ml-1 opacity-60 hover:opacity-100"><i class="fas fa-times text-[8px]"></i></button>
                    </div>
                </div>
                <div class="flex items-center gap-1.5 w-full sm:w-auto flex-wrap">
                <select x-model="filter" class="text-[11px] border border-gray-200 rounded-lg px-2 py-1.5 focus:ring-1 focus:ring-orange-300 bg-white flex-shrink-0">
                    <option value="all">Semua</option>
                    <option value="active">Active</option>
                    <option value="disabled">Disabled</option>
                </select>
                <select x-model="profileFilter" class="text-[11px] border border-gray-200 rounded-lg px-2 py-1.5 focus:ring-1 focus:ring-orange-300 bg-white flex-shrink-0">
                    <option value="all">Semua Profile</option>
                    <template x-for="p in profiles" :key="p.name">
                        <option :value="p.name.toLowerCase()" x-text="p.name"></option>
                    </template>
                </select>
                <div class="relative flex-1 min-w-0">
                    <i class="fas fa-search absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-[10px]"></i>
                    <input x-model="search" type="text" placeholder="Cari..."
                           class="w-full pl-7 pr-2 py-1.5 text-[11px] border border-gray-200 rounded-lg focus:ring-1 focus:ring-orange-300 transition">
                </div>
            </div>
            </div>
        </div>

        {{-- DESKTOP TABLE (hidden on mobile) --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 text-left">
                        <th class="pl-3 pr-1 py-2.5 w-8"></th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold text-gray-400 uppercase tracking-wider">#</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Username</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Profile</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-2.5 text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <template x-for="(user, i) in filteredUsers()" :key="user.id || user.name || i">
                        <tr class="hover:bg-gray-50/70 transition" :class="selected.includes(user.id) ? 'bg-red-100 border-l-[3px] border-l-red-500' : ''">
                            <td class="pl-3 pr-1 py-2.5">
                                <input type="checkbox" :value="user.id" :checked="selected.includes(user.id)" @click.stop @change="toggleSelect(user.id)" class="w-4 h-4 rounded border-gray-300 text-red-500 focus:ring-red-400 cursor-pointer">
                            </td>
                            <td class="px-4 py-2.5 text-xs text-gray-400" x-text="i + 1"></td>
                            <td class="px-4 py-2.5">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 bg-gradient-to-br from-orange-400 to-orange-600 rounded-md flex items-center justify-center text-white text-[10px] font-bold"
                                         x-text="(user.name || '-').charAt(0).toUpperCase()"></div>
                                    <span class="text-sm font-medium text-gray-800" x-text="user.name"></span>
                                </div>
                            </td>
                            <td class="px-4 py-2.5">
                                <span class="text-xs bg-amber-50 text-amber-700 px-2 py-0.5 rounded font-medium" x-text="user.profile || '-'"></span>
                            </td>
                            <td class="px-4 py-2.5">
                                <template x-if="user.disabled">
                                    <span class="inline-flex items-center gap-1 text-xs text-red-500 bg-red-50 px-2 py-0.5 rounded font-medium">
                                        <span class="w-1.5 h-1.5 bg-red-400 rounded-full"></span> Disabled
                                    </span>
                                </template>
                                <template x-if="!user.disabled">
                                    <span class="inline-flex items-center gap-1 text-xs text-green-600 bg-green-50 px-2 py-0.5 rounded font-medium">
                                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span> Active
                                    </span>
                                </template>
                            </td>
                            <td class="px-4 py-2.5">
                                <div class="flex items-center gap-1.5">
                                    <form method="POST" :action="`/hotspot-users/${user.id}/${user.disabled ? 'enable' : 'disable'}`">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <button class="text-[11px] font-medium px-2 py-1 rounded-md transition"
                                            :class="user.disabled ? 'text-green-600 bg-green-50 hover:bg-green-100' : 'text-amber-600 bg-amber-50 hover:bg-amber-100'">
                                            <span x-text="user.disabled ? 'Enable' : 'Disable'"></span>
                                        </button>
                                    </form>
                                    <form method="POST" :action="`/hotspot-users/${user.id}/reset-password`" class="flex items-center gap-1">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <input type="password" name="password" placeholder="New pass"
                                               class="w-20 px-2 py-1 text-[11px] border border-gray-200 rounded-md focus:ring-1 focus:ring-amber-300" required>
                                        <button class="text-[11px] font-medium px-2 py-1 rounded-md text-blue-600 bg-blue-50 hover:bg-blue-100 transition">Reset</button>
                                    </form>
                                    <form method="POST" :action="`/hotspot-users/${user.id}`" onsubmit="return confirm('Yakin hapus user ini?')">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <input type="hidden" name="_method" value="DELETE">
                                        <button class="text-[11px] font-medium px-2 py-1 rounded-md text-red-500 bg-red-50 hover:bg-red-100 transition">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        {{-- MOBILE CARD VIEW --}}
        <div class="md:hidden divide-y divide-gray-100">
            <template x-for="(user, i) in filteredUsers()" :key="user.id || user.name || i">
                <div class="p-3 hover:bg-gray-50/50 transition" :class="selected.includes(user.id) ? 'bg-red-100 border-l-[3px] border-l-red-500' : ''">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2 min-w-0 flex-1">
                            <input type="checkbox" :value="user.id" :checked="selected.includes(user.id)" @change="toggleSelect(user.id)" class="w-4 h-4 rounded border-gray-300 text-red-500 focus:ring-red-400 cursor-pointer flex-shrink-0">
                            <div class="w-8 h-8 bg-gradient-to-br from-orange-400 to-orange-600 rounded-lg flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                                 x-text="(user.name || '-').charAt(0).toUpperCase()"></div>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-gray-800 truncate" x-text="user.name"></p>
                                <div class="flex items-center gap-1.5 mt-0.5">
                                    <span class="text-[10px] bg-amber-50 text-amber-700 px-1.5 py-0.5 rounded font-medium" x-text="user.profile || '-'"></span>
                                    <template x-if="user.disabled">
                                        <span class="inline-flex items-center gap-1 text-[10px] text-red-500 bg-red-50 px-1.5 py-0.5 rounded font-medium">
                                            <span class="w-1 h-1 bg-red-400 rounded-full"></span> Off
                                        </span>
                                    </template>
                                    <template x-if="!user.disabled">
                                        <span class="inline-flex items-center gap-1 text-[10px] text-green-600 bg-green-50 px-1.5 py-0.5 rounded font-medium">
                                            <span class="w-1 h-1 bg-green-500 rounded-full animate-pulse"></span> On
                                        </span>
                                    </template>
                                </div>
                            </div>
                        </div>
                        {{-- Quick actions --}}
                        <div class="flex items-center gap-1 flex-shrink-0">
                            <form method="POST" :action="`/hotspot-users/${user.id}/${user.disabled ? 'enable' : 'disable'}`">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <button class="text-[10px] font-medium px-2 py-1 rounded-md transition"
                                    :class="user.disabled ? 'text-green-600 bg-green-50 active:bg-green-100' : 'text-amber-600 bg-amber-50 active:bg-amber-100'">
                                    <span x-text="user.disabled ? 'Enable' : 'Disable'"></span>
                                </button>
                            </form>
                            <form method="POST" :action="`/hotspot-users/${user.id}`" onsubmit="return confirm('Hapus user ini?')">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <input type="hidden" name="_method" value="DELETE">
                                <button class="text-[10px] font-medium w-7 h-7 rounded-md text-red-500 bg-red-50 active:bg-red-100 flex items-center justify-center transition">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                    {{-- Reset password row --}}
                    <form method="POST" :action="`/hotspot-users/${user.id}/reset-password`" class="flex items-center gap-1.5 mt-1.5 pl-10">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="password" name="password" placeholder="Password baru"
                               class="flex-1 px-2 py-1 text-[11px] border border-gray-200 rounded-md focus:ring-1 focus:ring-amber-300" required>
                        <button class="text-[10px] font-medium px-2.5 py-1 rounded-md text-blue-600 bg-blue-50 active:bg-blue-100 transition flex-shrink-0">
                            <i class="fas fa-key text-[9px]"></i> Reset
                        </button>
                    </form>
                </div>
            </template>
        </div>

        {{-- Loading indicator --}}
        <div class="py-8 text-center text-gray-400" x-show="loading">
            <i class="fas fa-spinner fa-spin text-2xl mb-2 text-orange-400"></i>
            <p class="text-xs">Memuat data user...</p>
        </div>

        <div class="py-8 text-center text-gray-400" x-show="!loading && users.length === 0">
            <i class="fas fa-user-slash text-2xl mb-2"></i>
            <p class="text-xs">Belum ada user hotspot terdaftar</p>
        </div>
    </div>

</div>

</x-app-layout>
