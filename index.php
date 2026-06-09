<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Ticketing System</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        body {
            background: #f4f6f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .login-container {
            background: #ffffff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 420px;
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-header h1 {
            color: #1e3a8a;
            font-size: 24px;
            margin-bottom: 8px;
        }
        .login-header p {
            color: #6b7280;
            font-size: 14px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 6px;
            color: #374151;
            font-weight: 6px;
            font-size: 14px;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            font-size: 15px;
            transition: border-color 0.2s;
        }
        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: #2563eb;
        }
        .login-btn {
            width: 100%;
            padding: 12px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.2s;
            margin-top: 10px;
        }
        .login-btn:hover {
            background: #1d4ed8;
        }
        .footer-text {
            text-align: center;
            margin-top: 25px;
            font-size: 13px;
            color: #9ca3af;
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="login-header">
        <h1>Support Portal</h1>
        <p>Campus & Corporate Ticketing System</p>
    </div>

    <form action="actions/login-process.php" method="POST">
        <div class="form-group">
            <label for="username">Username or Email</label>
            <input type="text" id="username" name="username" required placeholder="Enter your username">
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required placeholder="••••••••">
        </div>

        <div class="form-group">
            <label for="role">Portal Access Role</label>
            <select id="role" name="role" required>
                <option value="client">Client / Student / Staff</option>
                <option value="technician">IT Technician</option>
                <option value="admin">System Administrator</option>
            </select>
        </div>

        <button type="submit" class="login-btn">Sign In</button>
    </form>

    <div class="footer-text">
        &copy; <?php echo date('Y'); ?> Trust Academy. All rights reserved.
    </div>
</div>

</body>
</html>
