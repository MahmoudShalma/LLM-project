<?php

namespace App\Http\Middleware;

use App\Models\Course;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEnrolled
{
    /**
     * Ensure the authenticated user is enrolled in the course.
     * This middleware assumes the route has a {course} parameter.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $course = $request->route('course');

        if (! $course instanceof Course) {
            abort(404);
        }

        $lesson = $request->route('lesson');

        // Free preview lessons bypass enrollment check
        if ($lesson && $lesson->is_free_preview) {
            return $next($request);
        }

        if (! $request->user() || ! $request->user()->isEnrolledIn($course)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => __('lms.not_enrolled')], 403);
            }

            return redirect()->route('courses.show', $course->slug)
                ->with('error', __('lms.not_enrolled'));
        }

        return $next($request);
    }
}
