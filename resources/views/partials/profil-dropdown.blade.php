@php
    $navUser = Auth::user();
    $navNama = mb_convert_case($navUser?->nama_lengkap ?? 'Pengguna', MB_CASE_TITLE, 'UTF-8');
    $navNamaSingkat = implode(' ', array_slice(explode(' ', $navNama), 0, 2));
    $navInisial = mb_strtoupper(mb_substr($navNama, 0, 1, 'UTF-8'), 'UTF-8');
    $navFoto = $navUser?->foto_profil ? asset('uploads/profil/' . $navUser?->foto_profil) : null;
    $navLabel = ($navUser?->peran === 'admin') ? 'Admin' : ($navUser?->nomor_induk ?? '-');
@endphp

<!-- Profil Dropdown -->
<div class="relative shrink-0" id="profile-dropdown-wrapper">
    <button type="button" id="profile-dropdown-btn" aria-haspopup="true" aria-expanded="false"
        class="flex items-center gap-2.5 pl-2 pr-3 py-1.5 rounded-xl hover:bg-gray-100 transition">
        @if($navFoto)
            <img src="{{ $navFoto }}" alt="Foto profil" class="w-9 h-9 rounded-full object-cover shrink-0">
        @else
            <div class="w-9 h-9 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-lg leading-none shrink-0">
                {{ $navInisial }}
            </div>
        @endif
        <span class="hidden md:block text-sm font-bold max-w-[140px] truncate text-gray-800">
            {{ $navNamaSingkat }}
        </span>
        <svg id="profile-chevron" class="w-4 h-4 text-gray-400 hidden md:block transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    <div id="profile-dropdown-menu" class="hidden absolute right-0 top-full mt-2 w-64 bg-white rounded-xl shadow-lg border border-gray-200 py-2 z-50">
        <div class="px-4 py-3 border-b border-gray-100">
            <p class="text-sm font-bold text-gray-900 truncate">{{ $navNama }}</p>
            <span class="inline-block mt-1.5 bg-blue-50 text-blue-700 text-xs font-bold px-2.5 py-1 rounded-full">
                {{ $navLabel }}
            </span>
        </div>

        <a href="{{ route('profil.show') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm font-bold text-blue-600 hover:bg-blue-50 transition">
            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            Profil Saya
        </a>

        <div class="border-t border-gray-100 my-1"></div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full flex items-center gap-3 px-4 py-2.5 text-sm font-bold text-red-600 hover:bg-red-50 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                Logout
            </button>
        </form>
    </div>
</div>

<script>
    (function () {
        var wrapper = document.getElementById('profile-dropdown-wrapper');
        var tombol = document.getElementById('profile-dropdown-btn');
        var menu = document.getElementById('profile-dropdown-menu');
        var chevron = document.getElementById('profile-chevron');
        if (!wrapper || !tombol || !menu) return;

        function tutup() {
            menu.classList.add('hidden');
            chevron.classList.remove('rotate-180');
            tombol.setAttribute('aria-expanded', 'false');
        }

        tombol.addEventListener('click', function () {
            var tersembunyi = menu.classList.toggle('hidden');
            chevron.classList.toggle('rotate-180', !tersembunyi);
            tombol.setAttribute('aria-expanded', tersembunyi ? 'false' : 'true');
        });

        document.addEventListener('click', function (e) {
            if (!wrapper.contains(e.target)) tutup();
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') tutup();
        });
    })();
</script>