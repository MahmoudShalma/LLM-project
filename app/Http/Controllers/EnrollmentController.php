<?php

namespace App\Http\Controllers;

use App\Actions\Enrollment\EnrollUserAction;
use App\Exceptions\CourseNotPublishedException;
use App\Models\Course;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    public function __construct(
        private EnrollUserAction $enrollUser,
    ) {}

    public function store(Request $request, Course $course)
    {
        $this->authorize('enroll', $course);

        try {
            $this->enrollUser->handle($request->user(), $course);

            return redirect()
                ->route('courses.show', $course->slug)
                ->with('success', __('lms.enrollment_success'));
        } catch (CourseNotPublishedException) {
            return redirect()
                ->route('courses.show', $course->slug)
                ->with('error', __('lms.course_not_published'));
        }
    }
}
