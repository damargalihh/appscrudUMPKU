<x-guest-layout>
    <div class="glass-card rounded-2xl p-5 sm:p-8">
        {{-- Logo --}}
        <div class="text-center mb-5 sm:mb-8">
            <img src="{{ asset('img/logotulisan.png') }}" alt="UMPKU" class="h-14 sm:h-20 w-auto mx-auto">
        </div>

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <!-- Name -->
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">Nama Lengkap</label>
                <div class="relative">
                    <i class="fas fa-user absolute left-4 top-1/2 -translate-y-1/2 text-[#4a4a6a]"></i>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                           class="w-full pl-11 pr-4 py-3 border-2 border-gray-200 rounded-xl text-sm text-[#1a1a2e] bg-white/90 focus:border-[#FBC02D] focus:ring-2 focus:ring-[#FBC02D]/30 transition placeholder:text-[#4a4a6a]"
                           placeholder="Masukkan nama lengkap">
                </div>
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <!-- Email Address -->
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
                <div class="relative">
                    <i class="fas fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-[#4a4a6a]"></i>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username"
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
                    <input id="password" type="password" name="password" required autocomplete="new-password"
                           class="w-full pl-11 pr-4 py-3 border-2 border-gray-200 rounded-xl text-sm text-[#1a1a2e] bg-white/90 focus:border-[#FBC02D] focus:ring-2 focus:ring-[#FBC02D]/30 transition placeholder:text-[#4a4a6a]"
                           placeholder="Buat password">
                </div>
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <!-- Confirm Password -->
            <div class="mb-5">
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1.5">Konfirmasi Password</label>
                <div class="relative">
                    <i class="fas fa-shield-alt absolute left-4 top-1/2 -translate-y-1/2 text-[#4a4a6a]"></i>
                    <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                           class="w-full pl-11 pr-4 py-3 border-2 border-gray-200 rounded-xl text-sm text-[#1a1a2e] bg-white/90 focus:border-[#FBC02D] focus:ring-2 focus:ring-[#FBC02D]/30 transition placeholder:text-[#4a4a6a]"
                           placeholder="Ulangi password">
                </div>
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>

            <!-- Submit -->
            <button type="submit" class="w-full py-3 bg-gradient-to-r from-[#FF8C00] to-[#E65100] text-white font-semibold rounded-xl hover:shadow-lg hover:shadow-orange-300/30 transition-all duration-300 flex items-center justify-center gap-2">
                <i class="fas fa-user-plus"></i> Daftar
            </button>
        </form>

        {{-- Footer Link --}}
        <div class="text-center mt-6">
            <p class="text-sm text-[#4a4a6a]">Sudah punya akun?
                <a href="{{ route('login') }}" class="text-[#FF8C00] font-semibold hover:text-[#E65100] transition">Masuk di sini</a>
            </p>
        </div>
    </div>
</x-guest-layout>
