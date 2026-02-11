<x-app-layout>

@section('page-title', 'Pengaturan Profil')

<div class="space-y-4 md:space-y-5">

    {{-- HEADER --}}
    <div>
        <h1 class="text-sm md:text-lg font-bold text-gray-800">Pengaturan Profil</h1>
        <p class="text-[11px] text-gray-400 mt-0.5">Kelola informasi akun, password, dan keamanan</p>
    </div>

    {{-- UPDATE PROFILE INFO --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 md:p-6">
        <div class="max-w-xl">
            @include('profile.partials.update-profile-information-form')
        </div>
    </div>

    {{-- UPDATE PASSWORD --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 md:p-6">
        <div class="max-w-xl">
            @include('profile.partials.update-password-form')
        </div>
    </div>

    {{-- DELETE ACCOUNT --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 md:p-6">
        <div class="max-w-xl">
            @include('profile.partials.delete-user-form')
        </div>
    </div>

</div>

</x-app-layout>
