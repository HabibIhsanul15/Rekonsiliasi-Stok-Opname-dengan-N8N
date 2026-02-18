<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'StockOpname') }}</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        {{-- Background Effect --}}
        <div class="fixed inset-0 -z-10">
            <div class="absolute inset-0 bg-[#0a0e1a]"></div>
            <div class="absolute top-0 left-1/4 w-96 h-96 bg-indigo-600/10 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 right-1/4 w-96 h-96 bg-purple-600/10 rounded-full blur-3xl"></div>
            <div class="absolute top-1/2 left-1/2 w-64 h-64 bg-cyan-600/5 rounded-full blur-3xl"></div>
        </div>

        <div class="min-h-screen flex">
            @include('layouts.navigation')

            {{-- Main Content --}}
            <div class="flex-1 flex flex-col min-h-screen lg:ml-64">
                {{-- Top Bar --}}
                @isset($header)
                <header class="sticky top-0 z-30 border-b border-white/5" style="background: rgba(10, 14, 26, 0.8); backdrop-filter: blur(12px);">
                    <div class="max-w-7xl mx-auto px-6 py-4">
                        {{ $header }}
                    </div>
                </header>
                @endisset

                {{-- Page Content --}}
                <main class="flex-1 p-6">
                    {{ $slot }}
                </main>

                {{-- Footer --}}
                <footer class="border-t border-white/5 px-6 py-3">
                    <p class="text-xs text-slate-600 text-center">&copy; {{ date('Y') }} StockOpname System &middot; Built with Laravel</p>
                </footer>
            </div>
        </div>

        {{-- Flash Messages --}}
        @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="translate-x-full opacity-0"
             x-transition:enter-end="translate-x-0 opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="translate-x-0 opacity-100"
             x-transition:leave-end="translate-x-full opacity-0"
             class="fixed bottom-6 right-6 z-50 glass-card glow-border px-6 py-4 flex items-center gap-3"
             style="border-color: rgba(16,185,129,0.3);">
            <span class="text-2xl">&#x2705;</span>
            <span class="text-emerald-300 text-sm font-medium">{{ session('success') }}</span>
        </div>
        @endif
        @if(session('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="translate-x-full opacity-0"
             x-transition:enter-end="translate-x-0 opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="translate-x-0 opacity-100"
             x-transition:leave-end="translate-x-full opacity-0"
             class="fixed bottom-6 right-6 z-50 glass-card px-6 py-4 flex items-center gap-3"
             style="border-color: rgba(239,68,68,0.3);">
            <span class="text-2xl">&#x274C;</span>
            <span class="text-red-300 text-sm font-medium">{{ session('error') }}</span>
        </div>
        @endif

        @stack('scripts')
    </body>
</html>
