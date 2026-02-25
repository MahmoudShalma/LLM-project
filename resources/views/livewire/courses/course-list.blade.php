<div>
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ __('lms.courses') }}</h1>
        <p class="mt-2 text-gray-600 dark:text-gray-400">Explore our available courses and start learning today.</p>
    </div>

    <!-- Filters -->
    <div class="flex flex-col sm:flex-row gap-4 mb-8">
        <div class="flex-1">
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Search courses..."
                class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-white"
            />
        </div>
        <div>
            <select
                wire:model.live="level"
                class="rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:text-white"
            >
                <option value="">{{ __('lms.level') }}: All</option>
                <option value="beginner">{{ __('lms.beginner') }}</option>
                <option value="intermediate">{{ __('lms.intermediate') }}</option>
                <option value="advanced">{{ __('lms.advanced') }}</option>
            </select>
        </div>
    </div>

    <!-- Course Grid -->
    @if ($courses->isEmpty())
        <div class="text-center py-16 text-gray-500 dark:text-gray-400">
            <svg class="w-16 h-16 mx-auto mb-4 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
            </svg>
            <p class="text-lg">{{ __('lms.no_courses') }}</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($courses as $course)
                <a href="{{ route('courses.show', $course->slug) }}"
                   class="group bg-white dark:bg-gray-800 rounded-xl shadow-sm hover:shadow-md border border-gray-200 dark:border-gray-700 overflow-hidden transition-all duration-200 hover:-translate-y-1">

                    <!-- Course Image -->
                    <div class="aspect-video bg-gradient-to-br from-indigo-500 to-purple-600 relative overflow-hidden">
                        @if ($course->image_path)
                            <img src="{{ asset('storage/' . $course->image_path) }}"
                                 alt="{{ $course->title }}"
                                 class="w-full h-full object-cover">
                        @else
                            <div class="absolute inset-0 flex items-center justify-center">
                                <svg class="w-16 h-16 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M15 10l4.553-2.069A1 1 0 0121 8.847v6.306a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        @endif

                        <!-- Level Badge -->
                        <div class="absolute top-3 right-3">
                            <span @class([
                                'px-2 py-1 rounded-full text-xs font-semibold',
                                'bg-green-100 text-green-800' => $course->level === 'beginner',
                                'bg-yellow-100 text-yellow-800' => $course->level === 'intermediate',
                                'bg-red-100 text-red-800' => $course->level === 'advanced',
                            ])>
                                {{ __('lms.' . $course->level) }}
                            </span>
                        </div>
                    </div>

                    <!-- Course Info -->
                    <div class="p-5">
                        <h3 class="font-semibold text-lg text-gray-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors line-clamp-2">
                            {{ $course->title }}
                        </h3>
                        @if ($course->description)
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400 line-clamp-2">
                                {{ $course->description }}
                            </p>
                        @endif

                        <div class="mt-4 flex items-center justify-between text-sm text-gray-500 dark:text-gray-400">
                            <span class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.069A1 1 0 0121 8.847v6.306a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                </svg>
                                {{ $course->lessons_count }} {{ __('lms.lessons') }}
                            </span>
                            <span class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                {{ $course->enrollments_count }} {{ __('lms.students') }}
                            </span>
                        </div>

                        <div class="mt-4">
                            <span class="inline-block w-full text-center bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-4 rounded-lg text-sm font-medium transition-colors">
                                {{ __('lms.course_details') }} →
                            </span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>
