<?php

namespace App\Livewire;

use Livewire\Component;

class StudentDashboard extends Component
{
    public function render()
    {
        $enrollments = auth()->user()
            ->enrollments()
            ->with(['course.lessons' => fn ($q) => $q->ordered(), 'certificate'])
            ->latest('enrolled_at')
            ->get()
            ->map(function ($enrollment) {
                $enrollment->progress_percent = $enrollment->getProgressPercentage();
                return $enrollment;
            });

        return view('livewire.student-dashboard', compact('enrollments'))
            ->layout('layouts.app');
    }
}
