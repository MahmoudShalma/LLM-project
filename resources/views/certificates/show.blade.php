<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"
      x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }"
      x-bind:class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Certificate - {{ $certificate->course->title }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50 dark:bg-gray-900 min-h-screen py-8">
    <div class="max-w-3xl mx-auto px-4">
        <div class="mb-6 text-center">
            <a href="{{ route('home') }}" class="text-indigo-600 hover:underline text-sm">← Back to Mini LMS</a>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg overflow-hidden border-4 border-indigo-600">
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white text-center py-10 px-8">
                <div class="flex justify-center mb-4">
                    <svg class="w-16 h-16 text-yellow-300" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path>
                    </svg>
                </div>
                <h1 class="text-4xl font-bold mb-2">Certificate of Completion</h1>
                <p class="text-indigo-200 text-lg">Mini LMS</p>
            </div>

            <div class="p-10 text-center">
                <p class="text-gray-500 dark:text-gray-400 text-lg mb-2">This certifies that</p>
                <h2 class="text-4xl font-bold text-gray-900 dark:text-white mb-2">{{ $certificate->user->name }}</h2>
                <p class="text-gray-500 dark:text-gray-400 text-lg mb-4">has successfully completed</p>
                <h3 class="text-2xl font-bold text-indigo-600 dark:text-indigo-400 mb-6">{{ $certificate->course->title }}</h3>

                <div class="flex justify-center gap-8 text-sm text-gray-500 dark:text-gray-400 mb-8">
                    <div>
                        <div class="font-semibold text-gray-700 dark:text-gray-300">Issue Date</div>
                        <div>{{ $certificate->issued_at->format('F j, Y') }}</div>
                    </div>
                    <div>
                        <div class="font-semibold text-gray-700 dark:text-gray-300">Certificate ID</div>
                        <div class="font-mono text-xs">{{ $certificate->uuid }}</div>
                    </div>
                </div>

                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <p class="text-xs text-gray-400 dark:text-gray-500">
                        Verify this certificate at: <span class="font-mono">{{ url('/certificates/' . $certificate->uuid) }}</span>
                    </p>
                </div>
            </div>
        </div>

        <div class="mt-6 flex justify-center gap-4">
            <a href="{{ route('courses.show', $certificate->course->slug) }}"
               class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg text-sm font-medium transition-colors">
                Back to Course
            </a>
            <a href="{{ route('home') }}"
               class="bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 px-6 py-2 rounded-lg text-sm font-medium transition-colors">
                Browse More Courses
            </a>
        </div>
    </div>
</body>
</html>
