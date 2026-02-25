<?php

namespace App\Exceptions;

use Exception;

class CourseNotPublishedException extends Exception
{
    public function __construct(string $message = 'Cannot enroll in a course that is not published.')
    {
        parent::__construct($message);
    }
}
