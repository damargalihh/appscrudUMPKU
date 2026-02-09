<!DOCTYPE html>
<html>
<head>
    <title>Daftar Hotspot</title>
</head>
<body>

<h3>Daftar Hotspot</h3>

{{-- NOTIFIKASI --}}
@if (session('success'))
    <p>{{ session('success') }}</p>
@endif

@if (session('error'))
    <p>{{ session('error') }}</p>
@endif

{{-- VALIDATION ERROR --}}
@if ($errors->any())
    <ul>
        @foreach ($errors->all() as $err)
            <li>{{ $err }}</li>
        @endforeach
    </ul>
@endif

<form method="POST" action="{{ route('hotspot.selfRegister') }}">
    @csrf

    <p>
        <input type="text"
               name="name"
               value="{{ old('name') }}"
               placeholder="Username"
               required>
    </p>

    <p>
        <input type="password"
               name="password"
               placeholder="Password"
               required>
    </p>

    <p>
        <select name="profile" required>
            <option value="">Pilih Profile</option>
            @foreach ($profiles as $p)
                <option value="{{ $p['name'] }}"
                    {{ old('profile') == $p['name'] ? 'selected' : '' }}>
                    {{ $p['name'] }}
                </option>
            @endforeach
        </select>
    </p>

    <button type="submit">Daftar</button>
</form>

</body>
</html>
