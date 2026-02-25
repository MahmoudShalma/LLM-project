<?php

use App\Http\Controllers\CertificateController;
use App\Http\Controllers\EnrollmentController;
use App\Livewire\Courses\CourseDetail;
use App\Livewire\Courses\CourseList;
use App\Livewire\Lessons\LessonPlayer;
use App\Livewire\StudentDashboard;
use Illuminate\Support\Facades\Route;

Route::get('/', CourseList::class)->name('home');

Route::get('/courses/{course}', CourseDetail::class)->name('courses.show');

Route::get('/courses/{course}/lessons/{lesson}', LessonPlayer::class)
    ->name('lessons.show');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/courses/{course}/enroll', [EnrollmentController::class, 'store'])
        ->name('courses.enroll');

    Route::get('/certificates/{uuid}', [CertificateController::class, 'show'])
        ->name('certificates.show');

    Route::get('/dashboard', StudentDashboard::class)->name('dashboard');

    Route::view('/profile', 'profile')->name('profile');
});

require __DIR__ . '/auth.php';
