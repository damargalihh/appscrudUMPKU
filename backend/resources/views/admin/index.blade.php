<x-app-layout>

@section('page-title', 'Manajemen Admin')

<div class="space-y-4 md:space-y-5">

    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
        <div>
            <h1 class="text-sm md:text-lg font-bold text-gray-800">Manajemen Admin</h1>
            <p class="text-[11px] text-gray-400 mt-0.5">Kelola akun admin yang memiliki akses ke panel web</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-[11px] bg-orange-100 text-orange-600 px-2.5 py-1 rounded-full font-medium">
                {{ $totalAdmins }} admin
            </span>
            <span class="text-[11px] bg-gray-100 text-gray-500 px-2.5 py-1 rounded-full font-medium">
                {{ $totalUsers }} total user
            </span>
        </div>
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

    {{-- FORM TAMBAH ADMIN --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-3 md:p-5">
        <h3 class="text-xs md:text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
            <i class="fas fa-user-shield text-orange-500"></i> Tambah Admin Baru
        </h3>
        <form method="POST" action="{{ route('admin.store') }}">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-2 md:gap-3">
                <div class="relative">
                    <i class="fas fa-user absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <input type="text" name="name" placeholder="Nama Lengkap" value="{{ old('name') }}"
                           class="w-full pl-8 pr-2 py-2 text-xs border border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-200 focus:border-orange-400 transition" required>
                </div>
                <div class="relative">
                    <i class="fas fa-envelope absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <input type="email" name="email" placeholder="Email" value="{{ old('email') }}"
                           class="w-full pl-8 pr-2 py-2 text-xs border border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-200 focus:border-orange-400 transition" required>
                </div>
                <div class="relative">
                    <i class="fas fa-tag absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <select name="role" class="w-full pl-8 pr-2 py-2 text-xs border border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-200 focus:border-orange-400 transition appearance-none bg-white">
                        <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="super_admin" {{ old('role') === 'super_admin' ? 'selected' : '' }}>Full Admin</option>
                    </select>
                </div>
                <div class="relative">
                    <i class="fas fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <input type="password" name="password" placeholder="Password"
                           class="w-full pl-8 pr-2 py-2 text-xs border border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-200 focus:border-orange-400 transition" required>
                </div>
                <div class="relative">
                    <i class="fas fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <input type="password" name="password_confirmation" placeholder="Konfirmasi Password"
                           class="w-full pl-8 pr-2 py-2 text-xs border border-gray-200 rounded-lg focus:ring-2 focus:ring-orange-200 focus:border-orange-400 transition" required>
                </div>
                <button type="submit" class="bg-gradient-to-r from-[#FF8C00] to-[#E65100] text-white text-xs font-semibold rounded-lg hover:shadow-md transition flex items-center justify-center gap-1.5 py-2">
                    <i class="fas fa-plus text-[10px]"></i> Tambah Admin
                </button>
            </div>
        </form>
    </div>

    {{-- TABEL ADMIN --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden"
         x-data="{ editId: null, editName: '', editEmail: '', editRole: '' }">

        {{-- HEADER TABEL --}}
        <div class="px-3 md:px-4 py-3 border-b border-gray-100 bg-white">
            <h3 class="text-xs md:text-sm font-semibold text-gray-700 flex items-center gap-2">
                <i class="fas fa-list text-orange-500"></i> Daftar Admin
                <span class="text-[10px] bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full font-medium">{{ $totalAdmins }}</span>
            </h3>
        </div>

        {{-- TABEL --}}
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 uppercase text-[10px] tracking-wider">
                        <th class="px-3 md:px-4 py-2.5 text-left font-semibold">#</th>
                        <th class="px-3 md:px-4 py-2.5 text-left font-semibold">Nama</th>
                        <th class="px-3 md:px-4 py-2.5 text-left font-semibold">Email</th>
                        <th class="px-3 md:px-4 py-2.5 text-left font-semibold">Role</th>
                        <th class="px-3 md:px-4 py-2.5 text-left font-semibold">Dibuat</th>
                        <th class="px-3 md:px-4 py-2.5 text-center font-semibold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse ($admins as $i => $admin)
                    <tr class="hover:bg-orange-50/30 transition">
                        {{-- Tampilan normal --}}
                        <template x-if="editId !== {{ $admin->id }}">
                            <td class="px-3 md:px-4 py-2.5 text-gray-400 font-medium">{{ $i + 1 }}</td>
                        </template>
                        <template x-if="editId !== {{ $admin->id }}">
                            <td class="px-3 md:px-4 py-2.5">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 bg-gradient-to-br from-orange-400 to-orange-600 rounded-full flex items-center justify-center text-white text-[10px] font-bold flex-shrink-0">
                                        {{ strtoupper(substr($admin->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-800">{{ $admin->name }}</p>
                                        @if($admin->id === auth()->id())
                                            <span class="text-[9px] text-orange-500 font-medium">(Anda)</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </template>
                        <template x-if="editId !== {{ $admin->id }}">
                            <td class="px-3 md:px-4 py-2.5 text-gray-600">{{ $admin->email }}</td>
                        </template>
                        <template x-if="editId !== {{ $admin->id }}">
                            <td class="px-3 md:px-4 py-2.5">
                                @if($admin->role === 'super_admin')
                                    <span class="inline-flex items-center gap-1 text-[10px] font-semibold px-2 py-0.5 rounded-full bg-purple-100 text-purple-700">
                                        <i class="fas fa-crown text-[8px]"></i> Full Admin
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-[10px] font-semibold px-2 py-0.5 rounded-full bg-blue-100 text-blue-700">
                                        <i class="fas fa-user-cog text-[8px]"></i> Admin
                                    </span>
                                @endif
                            </td>
                        </template>
                        <template x-if="editId !== {{ $admin->id }}">
                            <td class="px-3 md:px-4 py-2.5 text-gray-400">{{ $admin->created_at->format('d M Y, H:i') }}</td>
                        </template>
                        <template x-if="editId !== {{ $admin->id }}">
                            <td class="px-3 md:px-4 py-2.5 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <button @click="editId = {{ $admin->id }}; editName = '{{ addslashes($admin->name) }}'; editEmail = '{{ $admin->email }}'; editRole = '{{ $admin->role }}'"
                                            class="w-7 h-7 rounded-lg bg-blue-50 text-blue-500 hover:bg-blue-100 transition flex items-center justify-center" title="Edit">
                                        <i class="fas fa-pen text-[10px]"></i>
                                    </button>
                                    @if($admin->id !== auth()->id())
                                    <form method="POST" action="{{ route('admin.destroy', $admin) }}"
                                          onsubmit="return confirm('Yakin ingin menghapus admin {{ $admin->name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="w-7 h-7 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 transition flex items-center justify-center" title="Hapus">
                                            <i class="fas fa-trash text-[10px]"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </template>

                        {{-- Mode edit inline --}}
                        <template x-if="editId === {{ $admin->id }}">
                            <td class="px-3 md:px-4 py-2.5 text-gray-400 font-medium">{{ $i + 1 }}</td>
                        </template>
                        <template x-if="editId === {{ $admin->id }}">
                            <td class="px-3 md:px-4 py-1.5" colspan="4">
                                <form method="POST" action="{{ route('admin.update', $admin) }}" class="flex flex-wrap items-center gap-2">
                                    @csrf
                                    @method('PUT')
                                    <input type="text" name="name" x-model="editName" placeholder="Nama"
                                           class="text-xs border border-gray-200 rounded-lg px-2 py-1.5 focus:ring-2 focus:ring-orange-200 focus:border-orange-400 w-32 md:w-40" required>
                                    <input type="email" name="email" x-model="editEmail" placeholder="Email"
                                           class="text-xs border border-gray-200 rounded-lg px-2 py-1.5 focus:ring-2 focus:ring-orange-200 focus:border-orange-400 w-40 md:w-48" required>
                                    <select name="role" x-model="editRole"
                                            class="text-xs border border-gray-200 rounded-lg px-2 py-1.5 focus:ring-2 focus:ring-orange-200 focus:border-orange-400 w-28 appearance-none bg-white">
                                        <option value="admin">Admin</option>
                                        <option value="super_admin">Full Admin</option>
                                    </select>
                                    <input type="password" name="password" placeholder="Password baru (opsional)"
                                           class="text-xs border border-gray-200 rounded-lg px-2 py-1.5 focus:ring-2 focus:ring-orange-200 focus:border-orange-400 w-36 md:w-40">
                                    <input type="password" name="password_confirmation" placeholder="Konfirmasi"
                                           class="text-xs border border-gray-200 rounded-lg px-2 py-1.5 focus:ring-2 focus:ring-orange-200 focus:border-orange-400 w-28 md:w-36">
                                    <button type="submit" class="w-7 h-7 rounded-lg bg-green-50 text-green-600 hover:bg-green-100 transition flex items-center justify-center" title="Simpan">
                                        <i class="fas fa-check text-[10px]"></i>
                                    </button>
                                    <button type="button" @click="editId = null" class="w-7 h-7 rounded-lg bg-gray-100 text-gray-500 hover:bg-gray-200 transition flex items-center justify-center" title="Batal">
                                        <i class="fas fa-times text-[10px]"></i>
                                    </button>
                                </form>
                            </td>
                        </template>
                        <template x-if="editId === {{ $admin->id }}">
                            <td></td>
                        </template>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center">
                            <div class="flex flex-col items-center gap-2 text-gray-400">
                                <i class="fas fa-user-shield text-3xl text-gray-300"></i>
                                <p class="text-sm font-medium">Belum ada admin terdaftar</p>
                                <p class="text-xs">Tambahkan admin pertama menggunakan form di atas.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- INFO --}}
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
        <div class="flex items-start gap-2">
            <i class="fas fa-info-circle text-blue-400 mt-0.5 flex-shrink-0"></i>
            <div class="text-xs text-blue-600">
                <p class="font-semibold mb-1">Informasi Akses Admin</p>
                <ul class="list-disc list-inside space-y-0.5 text-blue-500">
                    <li><strong>Full Admin</strong> — akses penuh termasuk manajemen admin</li>
                    <li><strong>Admin</strong> — akses panel web tanpa manajemen admin</li>
                    <li>Hanya Full Admin yang dapat menambah, mengedit, atau menghapus admin lain</li>
                    <li>Anda tidak dapat menghapus akun admin milik sendiri</li>
                    <li>Minimal harus ada satu admin yang tersisa</li>
                </ul>
            </div>
        </div>
    </div>

</div>

</x-app-layout>
