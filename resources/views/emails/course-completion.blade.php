<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Completed!</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #4F46E5, #7C3AED); color: white; padding: 30px; border-radius: 8px 8px 0 0; text-align: center; }
        .body { background: #f9fafb; padding: 30px; border: 1px solid #e5e7eb; }
        .footer { background: #f3f4f6; padding: 20px; text-align: center; font-size: 12px; color: #6b7280; border-radius: 0 0 8px 8px; }
        .button { display: inline-block; background: #4F46E5; color: white; padding: 12px 24px; border-radius: 6px; text-decoration: none; font-weight: bold; margin: 10px 5px; }
        .button.secondary { background: #6b7280; }
        .certificate-box { background: white; border: 2px solid #4F46E5; border-radius: 8px; padding: 20px; margin: 20px 0; text-align: center; }
        .uuid { font-family: monospace; font-size: 14px; color: #4F46E5; word-break: break-all; }
    </style>
</head>
<body>
    <div class="header">
        <h1>🎉 Congratulations!</h1>
        <p>You've completed the course</p>
    </div>
    <div class="body">
        <p>Hello, <strong>{{ $enrollment->user->name }}</strong>!</p>
        <p>🏆 You have successfully completed:</p>
        <h2 style="color: #4F46E5; text-align: center;">{{ $enrollment->course->title }}</h2>

        <p>A certificate has been issued in your name. You can view and download it using the link below.</p>

        <div class="certificate-box">
            <p><strong>Certificate ID:</strong></p>
            <p class="uuid">{{ $certificate->uuid }}</p>
            <p><strong>Issued on:</strong> {{ $certificate->issued_at->format('F j, Y') }}</p>
        </div>

        <p style="text-align: center;">
            <a href="{{ url('/certificates/' . $certificate->uuid) }}" class="button">View Certificate</a>
            <a href="{{ url('/') }}" class="button secondary">Browse More Courses</a>
        </p>

        <p>Keep up the great work and continue your learning journey!</p>
    </div>
    <div class="footer">
        <p>&copy; {{ date('Y') }} Mini LMS. All rights reserved.</p>
    </div>
</body>
</html>
