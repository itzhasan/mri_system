<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MRI Archive System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-gray-100">
    <nav class="bg-blue-600 text-white shadow-lg">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-4">
                    <h1 class="text-2xl font-bold">MRI Archive System</h1>
                    <div class="flex space-x-4 ml-10">
                        <a href="{{ route('dashboard') }}" 
                           class="px-3 py-2 rounded-md hover:bg-blue-700 {{ request()->routeIs('dashboard') ? 'bg-blue-700' : '' }}">
                            Dashboard
                        </a>
                        
                        @if(Auth::user()->isTechnician() || Auth::user()->isAdmin())
                            <a href="{{ route('patients') }}" 
                               class="px-3 py-2 rounded-md hover:bg-blue-700 {{ request()->routeIs('patients') ? 'bg-blue-700' : '' }}">
                                Patients
                            </a>
                        @endif
                        
                        <a href="{{ route('scans') }}" 
                           class="px-3 py-2 rounded-md hover:bg-blue-700 {{ request()->routeIs('scans') ? 'bg-blue-700' : '' }}">
                            MRI Scans
                        </a>
                        
                        @if(Auth::user()->isDoctor() || Auth::user()->isAdmin())
                            <a href="{{ route('reports') }}" 
                               class="px-3 py-2 rounded-md hover:bg-blue-700 {{ request()->routeIs('reports') ? 'bg-blue-700' : '' }}">
                                Reports
                            </a>
                        @endif
                    </div>
                </div>
                
                <div class="flex items-center space-x-4">
                    <div class="text-right">
                        <p class="font-semibold">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-blue-200">{{ ucfirst(str_replace('_', ' ', Auth::user()->role)) }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded transition duration-200">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <main class="container mx-auto px-4 py-8">
        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>