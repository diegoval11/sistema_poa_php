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
