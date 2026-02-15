<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7f6;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }
        .header {
            background-color: #0d5c3e;
            padding: 30px;
            text-align: center;
        }
        .header img {
            max-height: 40px;
        }
        .content {
            padding: 40px;
            color: #333333;
            line-height: 1.6;
        }
        .content h1 {
            color: #0d5c3e;
            font-size: 24px;
            margin-top: 0;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #6c757d;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #0d5c3e;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="content">
            <h1>Hello, {{ $userName }}!</h1>
            <p>{!! nl2br(e($emailContent)) !!}</p>
            
            <p>If you have any questions, feel free to reply to this email or contact our support team.</p>
            
            <a href="{{ config('app.url') }}" class="button">Visit Dashboard</a>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} Smart Admin. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
