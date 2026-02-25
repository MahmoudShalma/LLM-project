<?php

namespace App\Livewire\Courses;

use App\Contracts\Repositories\CourseRepositoryInterface;
use Livewire\Component;

class CourseList extends Component
{
    public string $search = '';
    public string $level  = '';

    public function render(CourseRepositoryInterface $courses)
    {
        $courseList = $courses->getAllPublished();

        if ($this->search) {
            $courseList = $courseList->filter(
                fn ($course) => str_contains(strtolower($course->title), strtolower($this->search))
                    || str_contains(strtolower($course->description ?? ''), strtolower($this->search))
            );
        }

        if ($this->level) {
            $courseList = $courseList->filter(fn ($course) => $course->level === $this->level);
        }

        return view('livewire.courses.course-list', [
            'courses' => $courseList,
        ])->layout('layouts.app');
    }
}
