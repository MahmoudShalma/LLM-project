<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Mini LMS') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans antialiased bg-white">
    <div class="min-h-screen flex">

        <!-- Left Panel — Brand -->
        <div class="hidden lg:flex lg:w-5/12 xl:w-1/2 bg-indigo-700 flex-col justify-between relative overflow-hidden" style="padding: 3rem 3.5rem;">

            <!-- Decorative circles -->
            <div class="absolute top-0 right-0 w-72 h-72 bg-indigo-600 rounded-full" style="transform: translate(40%, -40%);"></div>
            <div class="absolute bottom-0 left-0 w-64 h-64 bg-indigo-800 rounded-full" style="transform: translate(-30%, 30%);"></div>
            <div class="absolute top-1/2 right-8 w-40 h-40 bg-indigo-600 rounded-full opacity-40"></div>

            <!-- Logo -->
            <div class="relative z-10">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-3">
                    <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center shadow-md">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                    <span class="text-white font-bold text-xl tracking-tight">Mini LMS</span>
                </a>
            </div>

            <!-- Main content -->
            <div class="relative z-10">
                <h2 class="text-4xl font-bold text-white leading-snug mb-4">
                    Learn at your<br>own pace.
                </h2>
                <p class="text-indigo-200 text-base leading-relaxed mb-10 max-w-xs">
                    High-quality courses, progress tracking, and verified certificates — all in one place.
                </p>

                <div class="space-y-5">
                    <div class="flex items-center gap-4">
                        <div class="w-9 h-9 bg-white rounded-lg flex items-center justify-center flex-shrink-0 shadow-sm">
                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.847v6.306a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-white font-medium text-sm">Video lessons</p>
                            <p class="text-indigo-300 text-xs">Track started & completed time</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="w-9 h-9 bg-white rounded-lg flex items-center justify-center flex-shrink-0 shadow-sm">
                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-white font-medium text-sm">Completion certificates</p>
                            <p class="text-indigo-300 text-xs">Unique UUID, verifiable anytime</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="w-9 h-9 bg-white rounded-lg flex items-center justify-center flex-shrink-0 shadow-sm">
                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-white font-medium text-sm">Progress dashboard</p>
                            <p class="text-indigo-300 text-xs">See all your enrolled courses</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="relative z-10 text-indigo-400 text-xs">
                © {{ date('Y') }} Mini LMS
            </div>
        </div>

        <!-- Right Panel — Form -->
        <div class="flex-1 flex flex-col min-h-screen bg-gray-50">

            <!-- Top bar with back link -->
            <div class="flex items-center justify-between px-8 py-5 border-b border-gray-100 bg-white">
                <!-- Mobile logo -->
                <a href="{{ route('home') }}" class="flex items-center gap-2 lg:hidden">
                    <div class="w-8 h-8 bg-indigo-600 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                    <span class="font-bold text-indigo-600">Mini LMS</span>
                </a>
                <div class="hidden lg:block"></div>

                <a href="{{ route('home') }}" wire:navigate
                   class="flex items-center gap-1.5 text-sm text-gray-500 hover:text-indigo-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Back to courses
                </a>
            </div>

            <!-- Form area -->
            <div class="flex-1 flex items-center justify-center px-6 sm:px-12 py-12">
                <div class="w-full max-w-sm">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>

    @livewireScripts
</body>
</html>
