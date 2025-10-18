<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SeferEt - Travel Management System</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            text-align: center;
            padding: 2rem;
        }
        .logo {
            font-size: 3rem;
            font-weight: bold;
            margin-bottom: 1rem;
        }
        .subtitle {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }
        .links {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn {
            padding: 0.75rem 1.5rem;
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.3);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s;
        }
        .btn:hover {
            background: rgba(255, 255, 255, 0.3);
            border-color: rgba(255, 255, 255, 0.5);
        }
        .status {
            margin-top: 2rem;
            padding: 1rem;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 8px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">SeferEt</div>
        <div class="subtitle">Travel Management System</div>
        
        <div class="links">
            <a href="{{ route('admin.login') }}" class="btn">Admin Login</a>
            <a href="{{ route('b2b.login') }}" class="btn">B2B Partner Login</a>
            <a href="/dashboard" class="btn">Dashboard</a>
        </div>
        
        <div class="status">
            <strong>✅ Laravel Installation Complete!</strong><br>
            AdminLTE integration is ready for use.<br>
            Framework: Laravel {{ app()->version() }}<br>
            PHP: {{ PHP_VERSION }}
        </div>
    </div>
</body>
</html>
