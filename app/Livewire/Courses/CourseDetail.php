<?php

namespace App\Livewire\Courses;

use App\Models\Course;
use App\Models\Enrollment;
use Livewire\Component;

class CourseDetail extends Component
{
    public Course $course;
    public ?Enrollment $enrollment = null;
    public int $progressPercent    = 0;

    public function mount(Course $course): void
    {
        // Eager-load ordered lessons and enrollment count
        $course->load(['lessons' => fn ($q) => $q->ordered()])
               ->loadCount('enrollments');

        $this->course = $course;

        $this->authorize('view', $this->course);

        if (auth()->check()) {
            $this->enrollment    = auth()->user()->getEnrollmentFor($this->course);
            $this->progressPercent = $this->enrollment?->getProgressPercentage() ?? 0;
        }
    }

    public function render()
    {
        return view('livewire.courses.course-detail')->layout('layouts.app');
    }
}
