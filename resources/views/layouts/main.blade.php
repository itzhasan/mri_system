<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MRI Archive System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-50 min-h-screen">
    @php
        $navLink = function ($route, $label, $iconPath, $active) {
            return [
                'route' => $route,
                'label' => $label,
                'iconPath' => $iconPath,
                'active' => $active,
            ];
        };

        $navLinks = [
            [
                'route' => 'dashboard',
                'label' => 'Dashboard',
                'iconPath' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
                'show' => true,
            ],
            [
                'route' => 'patients',
                'label' => 'Patients',
                'iconPath' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
                'show' => Auth::user()->isTechnician() || Auth::user()->isAdmin(),
            ],
            [
                'route' => 'scans',
                'label' => 'MRI Scans',
                'iconPath' => 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z',
                'show' => true,
            ],
            [
                'route' => 'reports',
                'label' => 'Reports',
                'iconPath' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                'show' => Auth::user()->isDoctor() || Auth::user()->isAdmin(),
            ],
            [
                'route' => 'messages',
                'label' => 'Messages',
                'iconPath' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
                'show' => Auth::user()->isDoctor() || Auth::user()->isTechnician() || Auth::user()->isAdmin(),
            ],
            [
                'route' => 'users',
                'label' => 'Users',
                'iconPath' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
                'show' => Auth::user()->isAdmin(),
            ],
        ];
    @endphp

    <nav class="bg-white border-b border-gray-200 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center gap-8">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                        <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white shadow">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                            </svg>
                        </div>
                        <span class="text-lg font-bold text-gray-900 hidden sm:inline">MRI Archive</span>
                    </a>

                    <div class="hidden md:flex items-center gap-1">
                        @foreach($navLinks as $link)
                            @if($link['show'])
                                @php $active = request()->routeIs($link['route']); @endphp
                                <a href="{{ route($link['route']) }}"
                                    class="inline-flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-medium transition
                                        {{ $active ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100' }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $link['iconPath'] }}"></path>
                                    </svg>
                                    {{ $link['label'] }}
                                </a>
                            @endif
                        @endforeach
                    </div>
                </div>

                <div class="flex items-center gap-3" x-data="{ open: false }">
                    <button @click="open = !open" type="button"
                        class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-gray-100 transition">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-semibold text-sm">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                        <div class="hidden sm:block text-left">
                            <p class="text-sm font-semibold text-gray-900 leading-tight">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-gray-500 leading-tight">{{ ucfirst(str_replace('_', ' ', Auth::user()->role)) }}</p>
                        </div>
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>

                    <div x-show="open" x-transition @click.outside="open = false"
                        class="absolute right-4 top-14 w-56 bg-white border border-gray-200 rounded-lg shadow-lg py-2 z-50"
                        style="display: none;">
                        <div class="px-4 py-2 border-b border-gray-100">
                            <p class="text-sm font-semibold text-gray-900">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-gray-500">{{ Auth::user()->email }}</p>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="w-full flex items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                </svg>
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Mobile nav --}}
            <div class="md:hidden flex gap-1 pb-2 overflow-x-auto">
                @foreach($navLinks as $link)
                    @if($link['show'])
                        @php $active = request()->routeIs($link['route']); @endphp
                        <a href="{{ route($link['route']) }}"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium whitespace-nowrap
                                {{ $active ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-100' }}">
                            {{ $link['label'] }}
                        </a>
                    @endif
                @endforeach
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @yield('content')
    </main>

    @livewireScripts
</body>
</html>