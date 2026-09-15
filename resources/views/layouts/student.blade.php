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
        <div class="min-h-screen bg-slate-50 flex flex-col">
            <header class="bg-white border-b border-slate-200">
                <div class="max-w-xl mx-auto px-4 h-14 flex items-center justify-between">
                    <a href="{{ route('home') }}" class="font-bold text-primary-600">Kumpulin!</a>
                    <a href="{{ route('student.check') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-slate-600 hover:text-primary-600">
                        <i data-lucide="search" class="w-4 h-4"></i>
                        Cek Status
                    </a>
                </div>
            </header>

            <main class="flex-1 w-full max-w-xl mx-auto px-4 py-8">
                {{ $slot }}
            </main>

            <footer class="py-6 text-center text-xs text-slate-500">
                Kumpulin! — Kumpulkan tugas dengan mudah.
            </footer>
        </div>
        <x-toast />
    </body>
</html>
