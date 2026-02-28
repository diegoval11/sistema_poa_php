<header class="bg-congress-blue-800 text-white shadow-lg sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">

            <div class="flex items-center gap-4">
                @php
                    $logoRoute = Auth::check() && Auth::user()->role === 'admin' 
                        ? route('admin.dashboard') 
                        : route('dashboard');
                @endphp
                <a href="{{ $logoRoute }}" class="flex items-center gap-3 hover:opacity-90 transition">
                    <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center shadow-inner overflow-hidden p-1">
                        <img src="{{ asset('images/logo_alcaldia.png') }}" alt="Logo Alcaldía" class="w-full h-full object-contain">
                    </div>
                    <div class="leading-tight hidden md:block">
                        <h1 class="font-bold text-lg tracking-wide">ALCALDIA MUNICIPAL DE SANTA ANA CENTRO</h1>
                        <span class="text-xs text-congress-blue-200 uppercase tracking-widest">Sistema POA</span>
                    </div>
                </a>
            </div>

            <nav class="hidden md:flex space-x-8">
                @php
                    $dashboardRoute = Auth::check() && Auth::user()->role === 'admin' 
                        ? route('admin.dashboard') 
                        : route('dashboard');
                    $isDashboardActive = request()->routeIs('dashboard') || request()->routeIs('admin.dashboard');
                @endphp
                <a href="{{ $dashboardRoute }}" class="text-white hover:text-congress-blue-200 px-3 py-2 text-sm font-medium transition {{ $isDashboardActive ? 'border-b-2 border-white' : '' }}">
                    Dashboard
                </a>
                {{-- Solo mostrar "Mis Proyectos" para usuarios con rol 'unidad' --}}
                @if(Auth::check() && Auth::user()->role === 'unidad')
                    <a href="{{ Route::has('poa.lista_proyectos') ? route('poa.lista_proyectos') : '#' }}" class="text-white hover:text-congress-blue-200 px-3 py-2 text-sm font-medium transition {{ request()->routeIs('poa.*') ? 'border-b-2 border-white' : '' }}">
                        Mis Proyectos
                    </a>
                @endif
            </nav>

            {{-- ── Botón Toggle Tema Claro/Oscuro ────────────────── --}}
            <button
                id="theme-toggle"
                type="button"
                title="Cambiar tema"
                class="btn btn-ghost btn-circle text-white hover:bg-congress-blue-700 transition-all"
            >
                {{-- Ícono Sol (visible en modo claro) --}}
                <svg id="icon-sun" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 3v1m0 16v1m8.66-9H20m-16 0H3m15.36-6.36-.71.71M6.34 17.66l-.71.71M17.66 17.66l-.71-.71M6.34 6.34l-.71-.71M12 7a5 5 0 100 10A5 5 0 0012 7z"/>
                </svg>
                {{-- Ícono Luna (visible en modo oscuro) --}}
                <svg id="icon-moon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 hidden" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M21 12.79A9 9 0 1111.21 3a7 7 0 009.79 9.79z"/>
                </svg>
            </button>

            {{-- ── Dropdown Usuario ────────────────────────────────── --}}
            <div class="dropdown dropdown-end">
                <div tabindex="0" role="button" class="btn btn-ghost btn-circle avatar online">
                    <div class="w-10 rounded-full border-2 border-congress-blue-400 bg-congress-blue-700 flex items-center justify-center text-white font-bold">
                        {{-- Iniciales del usuario --}}
                        {{ substr(Auth::user()->email ?? 'U', 0, 2) }}
                    </div>
                </div>
                <ul tabindex="0" class="mt-3 z-[1] p-2 shadow menu menu-sm dropdown-content bg-base-100 rounded-box w-52 text-gray-800">
                    <li class="menu-title text-gray-400">
                        {{ Auth::user()->email ?? 'Usuario' }}
                    </li>
                    <li>
                        {{-- display:contents elimina el <form> del flujo de layout sin afectar el DOM,
                             dejando al <button> como hijo directo del <li> para que DaisyUI
                             aplique correctamente todo el área clicable del ítem de menú. --}}
                        <form method="POST" action="{{ route('logout') }}" id="logout-form-header" style="display: contents;">
                            @csrf
                            <button
                                type="submit"
                                class="text-error font-bold"
                                onclick="
                                    var btn = this;
                                    if (btn.disabled) return false;
                                    btn.disabled = true;
                                    btn.innerHTML = '<span class=\'loading loading-spinner loading-xs\'></span> Cerrando…';
                                    btn.classList.add('opacity-60', 'cursor-not-allowed');
                                    btn.closest('form').submit();
                                    return false;
                                "
                            >Cerrar Sesión</button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</header>

<script>
(function () {
    var btn     = document.getElementById('theme-toggle');
    var sun     = document.getElementById('icon-sun');
    var moon    = document.getElementById('icon-moon');
    var html    = document.documentElement;
    var STORAGE = 'poa-theme';

    function applyTheme(theme) {
        html.setAttribute('data-theme', theme);
        localStorage.setItem(STORAGE, theme);
        if (theme === 'dark') {
            sun.classList.add('hidden');
            moon.classList.remove('hidden');
        } else {
            moon.classList.add('hidden');
            sun.classList.remove('hidden');
        }
    }

    // Sincronizar ícono con el tema actual (ya aplicado por el script anti-flash)
    applyTheme(localStorage.getItem(STORAGE) || 'light');

    btn.addEventListener('click', function () {
        var current = html.getAttribute('data-theme') || 'light';
        applyTheme(current === 'dark' ? 'light' : 'dark');
    });
})();
</script>

