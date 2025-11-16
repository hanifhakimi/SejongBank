<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>SejongBank Login</title>
    <style>
        body {
            margin: 0;
            font-family: "Segoe UI", Arial, sans-serif;
            background: linear-gradient(135deg, #ff4d4d, #ffffff);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .login-container {
            width: 380px;
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(10px);
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
            animation: fadeIn 0.9s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logo {
            text-align: center;
            margin-bottom: 25px;
            font-size: 28px;
            font-weight: bold;
            color: #004b87;
        }

        .logo span {
            color: #ff4d4d;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
            font-size: 22px;
            font-weight: 600;
        }

        input {
            width: 100%;
            padding: 14px;
            margin: 12px 0;
            border-radius: 8px;
            border: 1px solid #bbb;
            font-size: 15px;
            transition: 0.3s;
            box-sizing: border-box;
        }

        input:focus {
            border-color: #004b87;
            outline: none;
            box-shadow: 0 0 5px rgba(0, 75, 135, 0.3);
        }


        button {
            width: 100%;
            background: #004b87;
            color: white;
            padding: 14px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: 0.25s ease;
            font-weight: bold;
            margin-top: 5px;
        }

        button:hover {
            background: #00345d;
            transform: translateY(-2px);
            box-shadow: 0 5px 12px rgba(0, 0, 0, 0.15);
        }

        .signup-link {
            text-align: center;
            margin-top: 18px;
            font-size: 14px;
        }

        .signup-link a {
            color: #ff4d4d;
            font-weight: bold;
            text-decoration: none;
            transition: 0.25s;
        }

        .signup-link a:hover {
            color: #c20000;
            text-decoration: underline;
        }
    </style>
</head>

<body>

    <div class="login-container">

        <div class="logo">Sejong<span>Bank</span></div>

        <h2>Welcome Back</h2>

        <form action="check_login.php" method="POST">
            <input type="email" name="email" placeholder="Email Address" required>
            <input type="password" name="password" placeholder="Password" required>

            <button type="submit">Login</button>
        </form>

        <div class="signup-link">
            Don't have an account? <a href="signup.php">Create one</a>
        </div>
    </div>

</body>

</html>