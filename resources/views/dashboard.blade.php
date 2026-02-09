<x-app-layout>

<div class="max-w-7xl mx-auto px-4">

    <h1 class="text-xl font-bold mb-4">Dashboard Admin Hotspot</h1>

    {{-- STATISTIK --}}
    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-white p-4 shadow rounded">
            Total User<br>
            <b>{{ count($users) }}</b>
        </div>
        <div class="bg-white p-4 shadow rounded">
            User Aktif<br>
            <b>{{ count($activeUsers) }}</b>
        </div>
        <div class="bg-white p-4 shadow rounded">
            Profile Paket<br>
            <b>{{ count($profiles) }}</b>
        </div>
    </div>

    {{-- USER HOTSPOT --}}
    <div class="bg-white shadow rounded p-4 mb-6">
        <h2 class="font-semibold mb-2">User Hotspot</h2>

        <table class="w-full border">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border px-2">User</th>
                    <th class="border px-2">Profile</th>
                    <th class="border px-2">Status</th>
                    <th class="border px-2">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $u)
                <tr>
                    <td class="border px-2">{{ $u['name'] }}</td>
                    <td class="border px-2">{{ $u['profile'] ?? '-' }}</td>
                    <td class="border px-2">
                        {{ ($u['disabled'] ?? 'false') === 'true' ? 'Disabled' : 'Active' }}
                    </td>
                    <td class="border px-2 space-x-1">

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

                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- USER AKTIF --}}
    <div class="bg-white shadow rounded p-4">
        <h2 class="font-semibold mb-2">User Aktif (Realtime)</h2>

        <table class="w-full border">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border px-2">User</th>
                    <th class="border px-2">IP</th>
                    <th class="border px-2">MAC</th>
                    <th class="border px-2">Uptime</th>
                </tr>
            </thead>
            <tbody>
                @foreach($activeUsers as $a)
                <tr>
                    <td class="border px-2">{{ $a['user'] }}</td>
                    <td class="border px-2">{{ $a['address'] }}</td>
                    <td class="border px-2">{{ $a['mac-address'] }}</td>
                    <td class="border px-2">{{ $a['uptime'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>

</x-app-layout>
