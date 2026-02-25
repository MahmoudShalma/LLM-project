<?php

namespace App\Livewire\Lessons;

use App\Actions\Lessons\MarkLessonCompletedAction;
use App\Actions\Lessons\MarkLessonStartedAction;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use Livewire\Component;

class LessonPlayer extends Component
{
    public Course $course;
    public Lesson $lesson;
    public ?Enrollment $enrollment     = null;
    public ?LessonProgress $progress   = null;
    public bool $isCompleted           = false;
    public bool $showConfirmModal      = false;
    public int $progressPercent        = 0;

    public function mount(Course $course, Lesson $lesson): void
    {
        if ($lesson->course_id !== $course->id) {
            abort(404);
        }

        $this->course = $course;
        $this->lesson = $lesson;

        $this->authorize('view', $this->lesson);

        if (auth()->check()) {
            $this->enrollment = auth()->user()->getEnrollmentFor($this->course);
            if ($this->enrollment) {
                $this->progress   = LessonProgress::where('user_id', auth()->id())
                    ->where('lesson_id', $this->lesson->id)
                    ->first();
                $this->isCompleted      = $this->progress?->isCompleted() ?? false;
                $this->progressPercent  = $this->enrollment->getProgressPercentage();
            }
        }
    }

    public function lessonStarted(MarkLessonStartedAction $action): void
    {
        if (! $this->enrollment) {
            return;
        }

        $this->progress = $action->handle(
            auth()->user(),
            $this->lesson,
            $this->enrollment
        );
    }

    public function requestCompletion(): void
    {
        if (! $this->enrollment || $this->isCompleted) {
            return;
        }

        $this->showConfirmModal = true;
    }

    public function confirmCompletion(MarkLessonCompletedAction $action): void
    {
        $this->showConfirmModal = false;

        if (! $this->enrollment || $this->isCompleted) {
            return;
        }

        $this->authorize('complete', $this->lesson);

        $this->progress    = $action->handle(auth()->user(), $this->lesson, $this->enrollment);
        $this->isCompleted = true;

        $this->enrollment->refresh();
        $this->progressPercent = $this->enrollment->getProgressPercentage();

        $this->dispatch('lessonMarkedComplete');
    }

    public function cancelCompletion(): void
    {
        $this->showConfirmModal = false;
    }

    public function render()
    {
        $lessons = $this->course->lessons()->ordered()->get();

        $currentIndex = $lessons->search(fn ($l) => $l->id === $this->lesson->id);
        $prevLesson   = $currentIndex > 0 ? $lessons[$currentIndex - 1] : null;
        $nextLesson   = $currentIndex < $lessons->count() - 1 ? $lessons[$currentIndex + 1] : null;

        $completedLessonIds = $this->enrollment
            ? $this->enrollment->lessonProgress()->completed()->pluck('lesson_id')->all()
            : [];

        return view('livewire.lessons.lesson-player', compact('lessons', 'prevLesson', 'nextLesson', 'completedLessonIds'))
            ->layout('layouts.app');
    }
}
