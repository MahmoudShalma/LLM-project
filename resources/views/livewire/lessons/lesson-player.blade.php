<div>
    <!-- Breadcrumb -->
    <nav class="mb-6 text-sm">
        <a href="{{ route('home') }}" class="text-indigo-600 hover:underline">{{ __('lms.courses') }}</a>
        <span class="mx-2 text-gray-400">/</span>
        <a href="{{ route('courses.show', $course->slug) }}" class="text-indigo-600 hover:underline">{{ $course->title }}</a>
        <span class="mx-2 text-gray-400">/</span>
        <span class="text-gray-600 dark:text-gray-400">{{ $lesson->title }}</span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2">
            <!-- Video Player -->
            <div class="bg-black rounded-xl overflow-hidden mb-6"
                 x-data="{
                     player: null,
                     init() {
                         this.player = new Plyr(this.$refs.videoEl, {
                             controls: ['play-large', 'play', 'progress', 'current-time', 'mute', 'volume', 'fullscreen'],
                             ratio: '16:9'
                         });
                         this.player.on('play', () => {
                             @this.lessonStarted();
                         });
                         this.player.on('ended', () => {
                             if (! @json($isCompleted)) {
                                 @this.requestCompletion();
                             }
                         });
                     }
                 }">
                @if ($lesson->video_url)
                    @php
                        // Extract YouTube video ID
                        preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/)([a-zA-Z0-9_-]+)/', $lesson->video_url, $matches);
                        $videoId = $matches[1] ?? null;
                    @endphp
                    @if ($videoId)
                        <div class="plyr__video-embed" x-ref="videoEl">
                            <iframe
                                src="https://www.youtube.com/embed/{{ $videoId }}?origin={{ config('app.url') }}&amp;iv_load_policy=3&amp;modestbranding=1&amp;playsinline=1&amp;showinfo=0&amp;rel=0&amp;enablejsapi=1"
                                allowfullscreen
                                allowtransparency
                                allow="autoplay"
                            ></iframe>
                        </div>
                    @else
                        <video x-ref="videoEl" playsinline controls>
                            <source src="{{ $lesson->video_url }}" type="video/mp4">
                        </video>
                    @endif
                @else
                    <div class="aspect-video flex items-center justify-center bg-gray-900 text-gray-400">
                        <div class="text-center">
                            <svg class="w-16 h-16 mx-auto mb-2 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M15 10l4.553-2.069A1 1 0 0121 8.847v6.306a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                            <p>No video available for this lesson</p>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Lesson Title & Controls -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $lesson->title }}</h1>
                        @if ($lesson->content)
                            <div class="mt-4 prose prose-sm dark:prose-invert max-w-none text-gray-600 dark:text-gray-300">
                                {!! nl2br(e($lesson->content)) !!}
                            </div>
                        @endif
                    </div>

                    <!-- Mark Complete -->
                    @auth
                        @if ($enrollment)
                            <div x-data="{ showModal: $wire.entangle('showConfirmModal') }" class="flex-shrink-0">
                                @if ($isCompleted)
                                    <div class="flex items-center gap-2 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 px-4 py-2 rounded-lg">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span class="font-medium text-sm">{{ __('lms.completed') }}</span>
                                    </div>
                                @else
                                    <button wire:click="requestCompletion"
                                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        {{ __('lms.mark_complete') }}
                                    </button>
                                @endif

                                <!-- Confirmation Modal -->
                                <div x-show="showModal"
                                     x-transition:enter="transition ease-out duration-150"
                                     x-transition:enter-start="opacity-0"
                                     x-transition:enter-end="opacity-100"
                                     x-transition:leave="transition ease-in duration-100"
                                     x-transition:leave-start="opacity-100"
                                     x-transition:leave-end="opacity-0"
                                     class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
                                     @keydown.escape.window="$wire.cancelCompletion()">
                                    <div x-show="showModal"
                                         x-transition:enter="transition ease-out duration-150"
                                         x-transition:enter-start="opacity-0 scale-95"
                                         x-transition:enter-end="opacity-100 scale-100"
                                         class="bg-white dark:bg-gray-800 rounded-xl shadow-xl p-6 max-w-sm w-full mx-4">
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                                            {{ __('lms.confirm_complete') }}
                                        </h3>
                                        <p class="text-gray-500 dark:text-gray-400 text-sm mb-6">
                                            {{ __('lms.confirm_complete_msg') }}
                                        </p>
                                        <div class="flex gap-3">
                                            <button wire:click="confirmCompletion"
                                                    class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-2 px-4 rounded-lg text-sm font-medium transition-colors">
                                                {{ __('lms.yes_complete') }}
                                            </button>
                                            <button @click="$wire.cancelCompletion()"
                                                    class="flex-1 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 py-2 px-4 rounded-lg text-sm font-medium transition-colors">
                                                {{ __('lms.cancel') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endauth
                </div>
            </div>

            <!-- Navigation -->
            <div class="flex justify-between">
                @if ($prevLesson)
                    <a href="{{ route('lessons.show', [$course->slug, $prevLesson->slug]) }}"
                       class="flex items-center gap-2 text-indigo-600 hover:text-indigo-700 font-medium text-sm">
                        ← {{ __('lms.prev_lesson') }}
                    </a>
                @else
                    <div></div>
                @endif

                @if ($nextLesson)
                    <a href="{{ route('lessons.show', [$course->slug, $nextLesson->slug]) }}"
                       class="flex items-center gap-2 text-indigo-600 hover:text-indigo-700 font-medium text-sm">
                        {{ __('lms.next_lesson') }} →
                    </a>
                @endif
            </div>
        </div>

        <!-- Sidebar: Lesson List with Progress -->
        <div class="lg:col-span-1">
            <!-- Progress Bar -->
            @if ($enrollment)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-4"
                     x-data="{ progress: 0, target: {{ $progressPercent }} }"
                     x-init="setTimeout(() => { progress = target }, 500)">
                    <div class="flex justify-between text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        <span>{{ __('lms.course_progress') }}</span>
                        <span x-text="Math.round(progress) + '%'"></span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                        <div class="h-2 rounded-full transition-all duration-1000 ease-out"
                             :class="progress >= 100 ? 'bg-green-500' : 'bg-indigo-600'"
                             :style="'width: ' + progress + '%'">
                        </div>
                    </div>
                </div>
            @endif

            <!-- Lesson List Accordion -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700"
                 x-data="{ open: true }">
                <button @click="open = !open"
                        class="w-full flex items-center justify-between p-4 font-semibold text-gray-900 dark:text-white hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors rounded-t-xl">
                    <span>{{ __('lms.lessons') }}</span>
                    <svg class="w-4 h-4 text-gray-500 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>

                <div x-show="open" x-transition class="divide-y divide-gray-100 dark:divide-gray-700 max-h-96 overflow-y-auto">
                    @foreach ($lessons as $l)
                        @php $canAccess = $enrollment || $l->is_free_preview; @endphp
                        @if ($canAccess)
                            <a href="{{ route('lessons.show', [$course->slug, $l->slug]) }}"
                               class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors {{ $l->id === $lesson->id ? 'bg-indigo-50 dark:bg-indigo-900/20 border-l-2 border-indigo-600' : '' }}">
                        @else
                            <div class="flex items-center gap-3 px-4 py-3 opacity-50 cursor-not-allowed">
                        @endif
                            <div class="flex-shrink-0 w-6 h-6 rounded-full flex items-center justify-center text-xs font-semibold
                                @if (in_array($l->id, $completedLessonIds))
                                    bg-green-500 text-white
                                @elseif ($l->id === $lesson->id)
                                    bg-indigo-600 text-white
                                @else
                                    bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400
                                @endif">
                                @if (in_array($l->id, $completedLessonIds))
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                @else
                                    {{ $l->order_column }}
                                @endif
                            </div>
                            <span class="text-sm truncate flex-1 {{ $l->id === $lesson->id ? 'font-semibold text-indigo-700 dark:text-indigo-400' : 'text-gray-700 dark:text-gray-300' }}">
                                {{ $l->title }}
                            </span>
                            @if (in_array($l->id, $completedLessonIds))
                                <span class="text-xs text-green-500 flex-shrink-0 font-medium">{{ __('lms.completed') }}</span>
                            @elseif ($l->is_free_preview && ! $enrollment)
                                <span class="text-xs text-green-600 dark:text-green-400 flex-shrink-0">Free</span>
                            @elseif (! $canAccess)
                                <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                            @endif
                        @if ($canAccess)
                            </a>
                        @else
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
