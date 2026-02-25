<div>
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('lms.dashboard') }}</h1>
        <p class="text-gray-500 dark:text-gray-400 mt-1">{{ __('lms.my_courses') }}</p>
    </div>

    @if ($enrollments->isEmpty())
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-12 text-center">
            <svg class="w-16 h-16 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
            </svg>
            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-2">No enrolled courses yet</h3>
            <p class="text-gray-500 dark:text-gray-400 mb-6">Browse our courses and start learning today!</p>
            <a href="{{ route('home') }}"
               class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-3 rounded-lg font-semibold transition-colors">
                {{ __('lms.courses') }} →
            </a>
        </div>
    @else
        <!-- Stats Summary -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                <div class="text-sm text-gray-500 dark:text-gray-400">Enrolled Courses</div>
                <div class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $enrollments->count() }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('lms.completed') }}</div>
                <div class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">{{ $enrollments->where('completed_at', '!=', null)->count() }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                <div class="text-sm text-gray-500 dark:text-gray-400">{{ __('lms.in_progress') }}</div>
                <div class="text-2xl font-bold text-indigo-600 dark:text-indigo-400 mt-1">{{ $enrollments->where('completed_at', null)->count() }}</div>
            </div>
        </div>

        <!-- Course Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($enrollments as $enrollment)
                @php $course = $enrollment->course; @endphp
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden flex flex-col">
                    <!-- Course Image -->
                    <div class="aspect-video bg-gradient-to-br from-indigo-500 to-purple-600 relative">
                        @if ($course->image_path)
                            <img src="{{ asset('storage/' . $course->image_path) }}" alt="{{ $course->title }}" class="w-full h-full object-cover">
                        @else
                            <div class="absolute inset-0 flex items-center justify-center">
                                <svg class="w-12 h-12 text-white/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M15 10l4.553-2.069A1 1 0 0121 8.847v6.306a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        @endif
                        @if ($enrollment->isCompleted())
                            <div class="absolute top-3 right-3 bg-green-500 text-white text-xs font-bold px-2 py-1 rounded-full flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                </svg>
                                {{ __('lms.completed') }}
                            </div>
                        @endif
                    </div>

                    <!-- Course Info -->
                    <div class="p-5 flex-1 flex flex-col">
                        <div class="flex items-center gap-2 mb-2">
                            <span @class([
                                'px-2 py-0.5 rounded-full text-xs font-semibold',
                                'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' => $course->level === 'beginner',
                                'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400' => $course->level === 'intermediate',
                                'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' => $course->level === 'advanced',
                            ])>
                                {{ __('lms.' . $course->level) }}
                            </span>
                            <span class="text-xs text-gray-400">{{ $course->lessons->count() }} {{ __('lms.lessons') }}</span>
                        </div>

                        <h3 class="font-bold text-gray-900 dark:text-white mb-3">
                            <a href="{{ route('courses.show', $course) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                {{ $course->title }}
                            </a>
                        </h3>

                        <!-- Progress Bar -->
                        <div class="mt-auto">
                            <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mb-1.5">
                                <span>{{ __('lms.course_progress') }}</span>
                                <span class="font-semibold {{ $enrollment->progress_percent >= 100 ? 'text-green-600 dark:text-green-400' : 'text-indigo-600 dark:text-indigo-400' }}">
                                    {{ $enrollment->progress_percent }}%
                                </span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div class="h-2 rounded-full transition-all duration-500 {{ $enrollment->progress_percent >= 100 ? 'bg-green-500' : 'bg-indigo-600' }}"
                                     style="width: {{ $enrollment->progress_percent }}%">
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="mt-4">
                            @if ($enrollment->isCompleted() && $enrollment->certificate)
                                <div class="flex gap-2">
                                    <a href="{{ route('courses.show', $course) }}"
                                       class="flex-1 text-center bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 py-2 px-4 rounded-lg text-sm font-medium transition-colors">
                                        {{ __('lms.course_details') }}
                                    </a>
                                    <a href="{{ route('certificates.show', $enrollment->certificate->uuid) }}"
                                       class="flex-1 text-center bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-lg text-sm font-medium transition-colors">
                                        {{ __('lms.certificate') }}
                                    </a>
                                </div>
                            @else
                                <a href="{{ route('lessons.show', [$course->slug, $course->lessons->first()?->slug]) }}"
                                   class="block w-full text-center bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-4 rounded-lg text-sm font-medium transition-colors">
                                    {{ __('lms.start_learning') }} →
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
