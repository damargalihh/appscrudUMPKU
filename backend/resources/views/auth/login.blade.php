<x-guest-layout :title="'Admin UMPKU Surakarta'">
    <div class="glass-card rounded-2xl p-5 sm:p-8">
        {{-- Logo --}}
        <div class="text-center mb-5 sm:mb-8">
            <img src="{{ asset('img/logotulisan.png') }}" alt="UMPKU" class="h-14 sm:h-20 w-auto mx-auto">
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <!-- Email Address -->
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
                <div class="relative">
                    <i class="fas fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-[#4a4a6a]"></i>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                           class="w-full pl-11 pr-4 py-3 border-2 border-gray-200 rounded-xl text-sm text-[#1a1a2e] bg-white/90 focus:border-[#FBC02D] focus:ring-2 focus:ring-[#FBC02D]/30 transition placeholder:text-[#4a4a6a]"
                           placeholder="Masukkan email">
                </div>
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <!-- Password -->
            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
                <div class="relative">
                    <i class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-[#4a4a6a]"></i>
                    <input id="password" type="password" name="password" required autocomplete="current-password"
                           class="w-full pl-11 pr-4 py-3 border-2 border-gray-200 rounded-xl text-sm text-[#1a1a2e] bg-white/90 focus:border-[#FBC02D] focus:ring-2 focus:ring-[#FBC02D]/30 transition placeholder:text-[#4a4a6a]"
                           placeholder="Masukkan password">
                </div>
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <!-- Remember Me -->
            <div class="flex items-center justify-between mb-6">
                <label for="remember_me" class="inline-flex items-center">
                    <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-[#FF8C00] shadow-sm focus:ring-[#FBC02D]" name="remember">
                    <span class="ms-2 text-sm text-[#4a4a6a]">Ingat saya</span>
                </label>

                @if (Route::has('password.request'))
                    <a class="text-sm text-[#FF8C00] font-medium hover:text-[#E65100] transition" href="{{ route('password.request') }}">
                        Lupa password?
                    </a>
                @endif
            </div>

            <!-- Submit -->
            <button type="submit" class="w-full py-3 bg-gradient-to-r from-[#FF8C00] to-[#E65100] text-white font-semibold rounded-xl hover:shadow-lg hover:shadow-orange-300/30 transition-all duration-300 flex items-center justify-center gap-2">
                <i class="fas fa-sign-in-alt"></i> Masuk
            </button>
        </form>

        {{-- Footer Link --}}
        <div class="text-center mt-6">
            <p class="text-sm text-[#4a4a6a]">Belum punya akun?
                <a href="{{ route('register') }}" class="text-[#FF8C00] font-semibold hover:text-[#E65100] transition">Daftar Sekarang</a>
            </p>
        </div>
    </div>
</x-guest-layout>
