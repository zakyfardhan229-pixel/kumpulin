<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Kumpulin!') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-slate-900 antialiased">
        <div class="min-h-screen bg-slate-50 lg:flex">
            <!-- Sidebar -->
            <aside class="hidden lg:flex lg:w-64 lg:flex-col lg:shrink-0 bg-white border-r border-slate-200">
                <div class="flex items-center gap-2 px-6 h-16 border-b border-slate-200">
                    <a href="{{ route('admin.dashboard') }}" class="text-lg font-bold text-primary-600">Kumpulin!</a>
                </div>
                <nav class="flex-1 px-3 py-4 space-y-1 text-sm font-medium">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-[10px] {{ request()->routeIs('admin.dashboard') ? 'bg-primary-50 text-primary-700' : 'text-slate-700 hover:bg-slate-100' }}">
                        <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                        Dashboard
                    </a>
                    <a href="{{ route('admin.sessions.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-[10px] {{ request()->routeIs('admin.sessions.*') ? 'bg-primary-50 text-primary-700' : 'text-slate-700 hover:bg-slate-100' }}">
                        <i data-lucide="clipboard-list" class="w-5 h-5"></i>
                        Sessions
                    </a>
                    <a href="{{ route('admin.submissions.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-[10px] {{ request()->routeIs('admin.submissions.*') ? 'bg-primary-50 text-primary-700' : 'text-slate-700 hover:bg-slate-100' }}">
                        <i data-lucide="file-up" class="w-5 h-5"></i>
                        Submissions
                    </a>
                </nav>
                <div class="px-3 py-4 border-t border-slate-200">
                    <div class="flex items-center gap-3 px-3 py-2">
                        <div class="w-9 h-9 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center font-semibold">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold truncate">{{ Auth::user()->name }}</p>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="text-xs text-slate-500 hover:text-red-600">Log Out</button>
                            </form>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- Main column -->
            <div class="flex-1 min-w-0">
                <!-- Compact mobile header -->
                <header class="lg:hidden bg-white border-b border-slate-200">
                    <div class="flex items-center justify-between px-4 h-14">
                        <a href="{{ route('admin.dashboard') }}" class="font-bold text-primary-600">Kumpulin!</a>
                        <nav class="flex items-center gap-1 text-sm font-medium">
                            <a href="{{ route('admin.dashboard') }}" class="p-2 rounded-lg text-slate-600 hover:bg-slate-100" aria-label="Dashboard">
                                <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
                            </a>
                            <a href="{{ route('admin.sessions.index') }}" class="p-2 rounded-lg text-slate-600 hover:bg-slate-100" aria-label="Sessions">
                                <i data-lucide="clipboard-list" class="w-5 h-5"></i>
                            </a>
                            <a href="{{ route('admin.submissions.index') }}" class="p-2 rounded-lg text-slate-600 hover:bg-slate-100" aria-label="Submissions">
                                <i data-lucide="file-up" class="w-5 h-5"></i>
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="p-2 rounded-lg text-slate-600 hover:bg-slate-100" aria-label="Log out">
                                    <i data-lucide="log-out" class="w-5 h-5"></i>
                                </button>
                            </form>
                        </nav>
                    </div>
                </header>

                <!-- Page Heading -->
                @isset($header)
                    <header class="bg-white border-b border-slate-200">
                        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <!-- Page Content -->
                <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                    {{ $slot }}
                </main>
            </div>
        </div>
        <x-toast />
        <script>
        document.querySelectorAll('form[data-disable-on-submit]').forEach((form) => {
            form.addEventListener('submit', () => {
                form.querySelectorAll('button[type="submit"]').forEach((btn) => {
                    btn.disabled = true;
                    btn.classList.add('opacity-50');
                });
            });
        });
        </script>
    </body>
</html>
