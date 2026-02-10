<x-app-layout>

@section('page-title', 'Kelola User')

<div class="space-y-6" x-data='hotspotUsersTable(@json($users))' x-init="start()">

    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
        <div>
            <h1 class="text-base md:text-lg font-bold text-gray-800">Manajemen User Hotspot</h1>
            <p class="text-xs text-gray-400 mt-0.5">Tambah, edit, dan kelola user MikroTik hotspot</p>
        </div>
        <span class="text-xs bg-gray-100 text-gray-500 px-3 py-1 rounded-full font-medium" x-text="users.length + ' user terdaftar'"></span>
    </div>

    {{-- VALIDATION ERRORS --}}
    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl px-4 py-3">
            <div class="flex items-start gap-2">
                <i class="fas fa-exclamation-circle mt-0.5"></i>
                <div>
                    <p class="font-semibold">Ada masalah pada input:</p>
                    <ul class="list-disc list-inside mt-1">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    {{-- FORM TAMBAH USER --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 md:p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2">
            <i class="fas fa-user-plus text-orange-500"></i> Tambah User Baru
        </h3>
        <form method="POST" action="{{ route('hotspot.store') }}">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <div class="relative">
                    <i class="fas fa-user absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                    <input type="text" name="name" placeholder="Username"
                           class="w-full pl-9 pr-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-200 focus:border-orange-400 transition" required>
                </div>
                <div class="relative">
                    <i class="fas fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                    <input type="password" name="password" placeholder="Password"
                           class="w-full pl-9 pr-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-200 focus:border-orange-400 transition" required>
                </div>
                <div class="relative">
                    <i class="fas fa-layer-group absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                    <select name="profile" class="w-full pl-9 pr-3 py-2.5 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-200 focus:border-orange-400 transition appearance-none bg-white" required>
                        <option value="">Pilih Profile</option>
                        @foreach($profiles as $profile)
                            <option value="{{ $profile['name'] }}">{{ $profile['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="bg-gradient-to-r from-[#FF8C00] to-[#E65100] text-white text-sm font-semibold rounded-lg hover:shadow-md hover:shadow-orange-200 transition-all flex items-center justify-center gap-2 py-2.5">
                    <i class="fas fa-plus"></i> Tambah User
                </button>
            </div>
        </form>
    </div>

    {{-- UPLOAD XLSX --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 md:p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-4 flex items-center gap-2">
            <i class="fas fa-file-excel text-green-600"></i> Upload User dari XLSX
        </h3>
        <form method="POST" action="{{ route('hotspot.upload') }}" enctype="multipart/form-data">
            @csrf
            <div class="flex flex-col lg:flex-row gap-3 lg:items-center">
                <input type="file" name="file" accept=".xlsx"
                       class="w-full lg:w-auto text-sm border border-gray-200 rounded-lg px-3 py-2.5 bg-white focus:ring-2 focus:ring-green-200 focus:border-green-400" required>
                <button type="submit" class="bg-gradient-to-r from-green-600 to-emerald-600 text-white text-sm font-semibold rounded-lg hover:shadow-md hover:shadow-emerald-200 transition-all flex items-center justify-center gap-2 px-4 py-2.5">
                    <i class="fas fa-upload"></i> Upload XLSX
                </button>
            </div>
            <p class="text-xs text-gray-400 mt-2">Kolom wajib: username, email, password, profile (baris pertama sebagai header).</p>
        </form>
    </div>

    {{-- TABEL USER --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        {{-- SEARCH & FILTER BAR --}}
        <div class="px-4 md:px-5 py-4 border-b border-gray-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <h3 class="text-sm font-semibold text-gray-700 flex items-center gap-2">
                <i class="fas fa-list text-blue-500"></i> Daftar User
                <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full font-medium" x-text="users.length"></span>
            </h3>
            <div class="flex items-center gap-2 w-full sm:w-auto flex-wrap">
                <select x-model="filter" class="text-xs border border-gray-200 rounded-lg px-2.5 py-2 focus:ring-1 focus:ring-orange-300 focus:border-orange-300 bg-white">
                    <option value="all">Semua Status</option>
                    <option value="active">Active</option>
                    <option value="disabled">Disabled</option>
                </select>
                <select x-model="profileFilter" class="text-xs border border-gray-200 rounded-lg px-2.5 py-2 focus:ring-1 focus:ring-orange-300 focus:border-orange-300 bg-white">
                    <option value="all">Semua Profile</option>
                    @foreach($profiles as $p)
                        <option value="{{ strtolower($p['name']) }}">{{ $p['name'] }}</option>
                    @endforeach
                </select>
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
                        <th class="px-4 md:px-5 py-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wider">#</th>
                        <th class="px-4 md:px-5 py-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Username</th>
                        <th class="px-4 md:px-5 py-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Profile</th>
                        <th class="px-4 md:px-5 py-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-4 md:px-5 py-3 text-[11px] font-semibold text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <template x-for="(user, i) in filteredUsers()" :key="user.id || user.name || i">
                        <tr class="hover:bg-gray-50/70 transition">
                            <td class="px-4 md:px-5 py-3 text-xs text-gray-400" x-text="i + 1"></td>
                            <td class="px-4 md:px-5 py-3">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 bg-gradient-to-br from-blue-400 to-blue-600 rounded-md flex items-center justify-center text-white text-[10px] font-bold"
                                         x-text="(user.name || '-').charAt(0).toUpperCase()"></div>
                                    <span class="text-sm font-medium text-gray-800" x-text="user.name"></span>
                                </div>
                            </td>
                            <td class="px-4 md:px-5 py-3">
                                <span class="text-xs bg-amber-50 text-amber-700 px-2 py-0.5 rounded font-medium" x-text="user.profile || '-'" ></span>
                            </td>
                            <td class="px-4 md:px-5 py-3">
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
                            <td class="px-4 md:px-5 py-3">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    {{-- TOGGLE --}}
                                    <form method="POST" :action="`/hotspot-users/${user.id}/${user.disabled ? 'enable' : 'disable'}`">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <button class="text-[11px] font-medium px-2 py-1 rounded-md transition"
                                            :class="user.disabled
                                                ? 'text-green-600 bg-green-50 hover:bg-green-100'
                                                : 'text-amber-600 bg-amber-50 hover:bg-amber-100'">
                                            <span x-text="user.disabled ? 'Enable' : 'Disable'"></span>
                                        </button>
                                    </form>

                                    {{-- RESET PASSWORD --}}
                                    <form method="POST" :action="`/hotspot-users/${user.id}/reset-password`" class="flex items-center gap-1">
                                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                        <input type="password" name="password" placeholder="New pass"
                                               class="w-20 px-2 py-1 text-[11px] border border-gray-200 rounded-md focus:ring-1 focus:ring-amber-300 focus:border-amber-300" required>
                                        <button class="text-[11px] font-medium px-2 py-1 rounded-md text-blue-600 bg-blue-50 hover:bg-blue-100 transition">Reset</button>
                                    </form>

                                    {{-- DELETE --}}
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
        <div class="py-12 text-center text-gray-400" x-show="users.length === 0">
            <i class="fas fa-user-slash text-3xl mb-3"></i>
            <p class="text-sm">Belum ada user hotspot terdaftar</p>
        </div>
    </div>

</div>

</x-app-layout>
