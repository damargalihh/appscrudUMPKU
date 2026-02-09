<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Admin Hotspot') }}</title>

    <!-- Tailwind -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-100">

    <div class="min-h-screen">

        <!-- NAVBAR -->
        <nav class="bg-white border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 py-3 flex justify-between">
                <div class="font-bold">
                    Admin Hotspot
                </div>

                <div>
                    {{ auth()->user()->name }}
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button class="ml-4 text-red-600">Logout</button>
                    </form>
                </div>
            </div>
        </nav>

        <!-- PAGE CONTENT -->
        <main class="py-6">
            {{ $slot }}
        </main>

    </div>

</body>
</html>
