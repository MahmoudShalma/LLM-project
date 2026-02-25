<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Mini LMS</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #4F46E5; color: white; padding: 30px; border-radius: 8px 8px 0 0; text-align: center; }
        .body { background: #f9fafb; padding: 30px; border: 1px solid #e5e7eb; }
        .footer { background: #f3f4f6; padding: 20px; text-align: center; font-size: 12px; color: #6b7280; border-radius: 0 0 8px 8px; }
        .button { display: inline-block; background: #4F46E5; color: white; padding: 12px 24px; border-radius: 6px; text-decoration: none; font-weight: bold; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="header">
        <h1>🎓 Welcome to Mini LMS!</h1>
    </div>
    <div class="body">
        <p>Hello, <strong>{{ $user->name }}</strong>!</p>
        <p>We're thrilled to have you join our learning platform. Your account has been successfully created.</p>
        <p>You can now browse our courses and start your learning journey today.</p>
        <p style="text-align: center;">
            <a href="{{ url('/') }}" class="button">Browse Courses</a>
        </p>
        <p>If you have any questions, feel free to reach out to our support team.</p>
        <p>Happy Learning! 🚀</p>
    </div>
    <div class="footer">
        <p>&copy; {{ date('Y') }} Mini LMS. All rights reserved.</p>
    </div>
</body>
</html>
