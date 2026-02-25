<div>
    <!-- Breadcrumb -->
    <nav class="mb-6 text-sm">
        <a href="{{ route('home') }}" class="text-indigo-600 hover:underline">{{ __('lms.courses') }}</a>
        <span class="mx-2 text-gray-400">/</span>
        <span class="text-gray-600 dark:text-gray-400">{{ $course->title }}</span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Course Main Content -->
        <div class="lg:col-span-2">
            <!-- Course Header -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden mb-6">
                <div class="aspect-video bg-gradient-to-br from-indigo-500 to-purple-600 relative">
                    @if ($course->image_path)
                        <img src="{{ asset('storage/' . $course->image_path) }}" alt="{{ $course->title }}" class="w-full h-full object-cover">
                    @else
                        <div class="absolute inset-0 flex items-center justify-center">
                            <svg class="w-24 h-24 text-white/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M15 10l4.553-2.069A1 1 0 0121 8.847v6.306a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    @endif
                </div>
                <div class="p-6">
                    <div class="flex items-center gap-3 mb-3">
                        <span @class([
                            'px-2 py-1 rounded-full text-xs font-semibold',
                            'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' => $course->level === 'beginner',
                            'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400' => $course->level === 'intermediate',
                            'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' => $course->level === 'advanced',
                        ])>
                            {{ __('lms.' . $course->level) }}
                        </span>
                    </div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $course->title }}</h1>
                    @if ($course->description)
                        <p class="mt-3 text-gray-600 dark:text-gray-400 leading-relaxed">{{ $course->description }}</p>
                    @endif
                </div>
            </div>

            <!-- Progress Bar -->
            @if ($enrollment)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6"
                     x-data="{ progress: 0, target: {{ $progressPercent }} }"
                     x-init="setTimeout(() => { progress = target }, 300)">
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-3">{{ __('lms.course_progress') }}</h3>
                    <div class="flex justify-between text-sm text-gray-500 dark:text-gray-400 mb-2">
                        <span>{{ __('lms.in_progress') }}</span>
                        <span x-text="Math.round(progress) + '%'"></span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3 overflow-hidden">
                        <div class="h-3 rounded-full transition-all duration-1000 ease-out"
                             :class="progress >= 100 ? 'bg-green-500' : 'bg-indigo-600'"
                             :style="'width: ' + progress + '%'">
                        </div>
                    </div>
                    @if ($enrollment->isCompleted())
                        <div class="mt-3 flex items-center gap-2 text-green-600 dark:text-green-400 font-semibold">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                            </svg>
                            {{ __('lms.course_completed') }}
                        </div>
                        @if ($enrollment->certificate)
                            <a href="{{ route('certificates.show', $enrollment->certificate->uuid) }}"
                               class="mt-2 inline-flex items-center gap-2 text-indigo-600 hover:underline text-sm">
                                {{ __('lms.view_certificate') }} →
                            </a>
                        @endif
                    @endif
                </div>
            @endif

            <!-- Lesson List -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                        {{ __('lms.lessons') }} ({{ $course->lessons->count() }})
                    </h2>
                </div>

                <div x-data="{ openSection: 'all' }">
                    <!-- Accordion Button -->
                    <button @click="openSection = openSection === 'all' ? null : 'all'"
                            class="w-full flex items-center justify-between px-6 py-4 text-left hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <span class="font-medium text-gray-900 dark:text-white">Course Content</span>
                        <svg class="w-5 h-5 text-gray-500 transition-transform duration-200"
                             :class="openSection === 'all' ? 'rotate-180' : ''"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>

                    <!-- Accordion Content -->
                    <div x-show="openSection === 'all'"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 -translate-y-2">
                        <div class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse ($course->lessons as $lesson)
                                <div class="flex items-center gap-4 px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                                    <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-sm font-semibold
                                        {{ $enrollment ? 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400' }}">
                                        {{ $lesson->order_column }}
                                    </div>

                                    <div class="flex-1 min-w-0">
                                        @if ($lesson->is_free_preview || $enrollment)
                                            <a href="{{ route('lessons.show', [$course->slug, $lesson->slug]) }}"
                                               class="font-medium text-gray-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors truncate block">
                                                {{ $lesson->title }}
                                            </a>
                                        @else
                                            <span class="font-medium text-gray-500 dark:text-gray-400 truncate block">
                                                {{ $lesson->title }}
                                            </span>
                                        @endif
                                    </div>

                                    <div class="flex items-center gap-2 flex-shrink-0">
                                        @if ($lesson->is_free_preview)
                                            <span class="text-xs bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 px-2 py-0.5 rounded-full">
                                                {{ __('lms.free_preview') }}
                                            </span>
                                        @elseif (! $enrollment)
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                            </svg>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="px-6 py-8 text-center text-gray-400 dark:text-gray-500">
                                    No lessons available yet.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1">
            <div class="sticky top-4 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <div class="text-center mb-6">
                    <div class="text-3xl font-bold text-indigo-600 dark:text-indigo-400 mb-1">
                        {{ $course->lessons->count() }}
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('lms.lessons') }}</div>
                </div>

                @auth
                    @if ($enrollment)
                        <a href="{{ route('lessons.show', [$course->slug, $course->lessons->first()?->slug]) }}"
                           class="block w-full text-center bg-green-600 hover:bg-green-700 text-white py-3 px-6 rounded-lg font-semibold transition-colors">
                            {{ __('lms.start_learning') }} →
                        </a>
                        <p class="text-center text-sm text-gray-500 dark:text-gray-400 mt-2">{{ __('lms.enrolled') }} ✓</p>
                    @else
                        <form action="{{ route('courses.enroll', $course) }}" method="POST">
                            @csrf
                            <button type="submit"
                                    class="block w-full text-center bg-indigo-600 hover:bg-indigo-700 text-white py-3 px-6 rounded-lg font-semibold transition-colors">
                                {{ __('lms.enroll') }}
                            </button>
                        </form>
                    @endif
                @else
                    <a href="{{ route('login') }}"
                       class="block w-full text-center bg-indigo-600 hover:bg-indigo-700 text-white py-3 px-6 rounded-lg font-semibold transition-colors">
                        {{ __('lms.login_to_enroll') }}
                    </a>
                @endauth

                <div class="mt-6 space-y-3 text-sm text-gray-600 dark:text-gray-400">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        {{ __('lms.level') }}: {{ __('lms.' . $course->level) }}
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        {{ $course->enrollments_count }} {{ __('lms.students') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
