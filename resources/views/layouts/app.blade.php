<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MRI Archive System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-gray-50">
    <nav class="sticky top-0 z-40 bg-blue-700/95 backdrop-blur border-b border-blue-500/40 text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-3">
                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-2">
                        <span class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-white/10 border border-white/20">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 4.5c-4.142 0-7.5 2.357-7.5 5.263 0 1.828 1.314 3.433 3.318 4.37L7 18.75l3.053-2.037c.624.09 1.27.138 1.947.138 4.142 0 7.5-2.357 7.5-5.263C19.5 6.857 16.142 4.5 12 4.5z" />
                            </svg>
                        </span>
                        <div>
                            <h1 class="text-lg sm:text-xl font-semibold leading-tight">MRI Archive System</h1>
                            <p class="hidden sm:block text-xs text-blue-100">Manage patients, scans, and diagnostic reports</p>
                        </div>
                    </div>

                    @auth
                        <div class="hidden md:flex items-center space-x-1 ml-6">
                            <a href="{{ route('dashboard') }}"
                               class="px-3 py-2 text-sm font-medium rounded-full transition
                                      {{ request()->routeIs('dashboard') ? 'bg-white text-blue-700 shadow-sm' : 'text-blue-100 hover:bg-blue-600/70' }}">
                                Dashboard
                            </a>

                            @if(Auth::user()->isTechnician() || Auth::user()->isAdmin())
                                <a href="{{ route('patients') }}"
                                   class="px-3 py-2 text-sm font-medium rounded-full transition
                                          {{ request()->routeIs('patients') ? 'bg-white text-blue-700 shadow-sm' : 'text-blue-100 hover:bg-blue-600/70' }}">
                                    Patients
                                </a>
                            @endif

                            <a href="{{ route('scans') }}"
                               class="px-3 py-2 text-sm font-medium rounded-full transition
                                      {{ request()->routeIs('scans') ? 'bg-white text-blue-700 shadow-sm' : 'text-blue-100 hover:bg-blue-600/70' }}">
                                MRI Scans
                            </a>

                            @if(Auth::user()->isDoctor() || Auth::user()->isAdmin())
                                <a href="{{ route('reports') }}"
                                   class="px-3 py-2 text-sm font-medium rounded-full transition
                                          {{ request()->routeIs('reports') ? 'bg-white text-blue-700 shadow-sm' : 'text-blue-100 hover:bg-blue-600/70' }}">
                                    Reports
                                </a>
                            @endif
                        </div>
                    @endauth
                </div>

                <div class="flex items-center space-x-3">
                    @auth
                        <div class="hidden sm:flex flex-col items-end">
                            <p class="font-semibold text-sm">{{ Auth::user()->name }}</p>
                            <p class="text-[11px] text-blue-100">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-blue-900/40 border border-blue-300/40">
                                    {{ ucfirst(str_replace('_', ' ', Auth::user()->role)) }}
                                </span>
                            </p>
                        </div>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                    class="inline-flex items-center gap-1 bg-red-500 hover:bg-red-600 px-3 sm:px-4 py-1.5 sm:py-2 rounded-full text-sm font-medium shadow-sm transition duration-150">
                                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H7a2 2 0 01-2-2V7a2 2 0 012-2h4a2 2 0 012 2v1" />
                                </svg>
                                <span class="hidden sm:inline">Logout</span>
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}"
                           class="text-sm font-medium px-3 py-1.5 rounded-full border border-white/40 hover:bg-white/10">
                            Login
                        </a>
                    @endauth
                </div>
            </div>

            @auth
                <div class="md:hidden flex items-center space-x-1 pb-3">
                    <a href="{{ route('dashboard') }}"
                       class="px-3 py-1.5 text-xs font-medium rounded-full transition
                              {{ request()->routeIs('dashboard') ? 'bg-white text-blue-700 shadow-sm' : 'text-blue-100 hover:bg-blue-600/70' }}">
                        Dashboard
                    </a>

                    @if(Auth::user()->isTechnician() || Auth::user()->isAdmin())
                        <a href="{{ route('patients') }}"
                           class="px-3 py-1.5 text-xs font-medium rounded-full transition
                                  {{ request()->routeIs('patients') ? 'bg-white text-blue-700 shadow-sm' : 'text-blue-100 hover:bg-blue-600/70' }}">
                            Patients
                        </a>
                    @endif

                    <a href="{{ route('scans') }}"
                       class="px-3 py-1.5 text-xs font-medium rounded-full transition
                              {{ request()->routeIs('scans') ? 'bg-white text-blue-700 shadow-sm' : 'text-blue-100 hover:bg-blue-600/70' }}">
                        Scans
                    </a>

                    @if(Auth::user()->isDoctor() || Auth::user()->isAdmin())
                        <a href="{{ route('reports') }}"
                           class="px-3 py-1.5 text-xs font-medium rounded-full transition
                                  {{ request()->routeIs('reports') ? 'bg-white text-blue-700 shadow-sm' : 'text-blue-100 hover:bg-blue-600/70' }}">
                            Reports
                        </a>
                    @endif
                </div>
            @endauth
        </div>
    </nav>

    <main class="w-full min-h-[calc(100vh-80px)] p-4 sm:p-6 lg:p-8">
        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>