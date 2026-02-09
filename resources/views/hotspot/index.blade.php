<x-app-layout>

<div class="max-w-6xl mx-auto px-4">

    <div class="bg-white shadow rounded p-6">

        <div class="flex justify-between items-center mb-4">
            <h1 class="text-xl font-bold">Manajemen User Hotspot</h1>
        </div>

        {{-- FORM TAMBAH USER --}}
        <form method="POST" action="{{ route('hotspot.store') }}" class="mb-6">
            @csrf

            <div class="grid grid-cols-4 gap-4">
                <input type="text" name="name" placeholder="Username"
                       class="border rounded px-3 py-2" required>

                <input type="password" name="password" placeholder="Password"
                       class="border rounded px-3 py-2" required>

                {{-- DROPDOWN PROFILE --}}
                <select name="profile" class="border rounded px-3 py-2" required>
                    <option value="">-- Pilih Profile --</option>
                    @foreach($profiles as $profile)
                        <option value="{{ $profile['name'] }}">
                            {{ $profile['name'] }}
                        </option>
                    @endforeach
                </select>

                <button class="bg-blue-600 text-white rounded px-4">
                    + Tambah User
                </button>
            </div>
        </form>

        {{-- TABEL USER --}}
        <table class="w-full border">
            <thead class="bg-gray-100">
                <tr>
                    <th class="border px-3 py-2">Username</th>
                    <th class="border px-3 py-2">Profile</th>
                    <th class="border px-3 py-2">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                    <td class="border px-3 py-2">{{ $user['name'] }}</td>
                    <td class="border px-3 py-2">{{ $user['profile'] ?? '-' }}</td>
                    <td class="border px-3 py-2">
                        <form method="POST"
                              action="{{ route('hotspot.destroy', $user['.id']) }}">
                            @csrf
                            @method('DELETE')
                            <button class="text-red-600">Hapus</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

    </div>

</div>

</x-app-layout>
