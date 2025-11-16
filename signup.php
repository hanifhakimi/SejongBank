<?php

function generateAccountNumber()
{
    // 12-digit random number (bank-style)
    $num = "";
    for ($i = 0; $i < 12; $i++) {
        $num .= rand(0, 9);
    }
    return $num;
}

include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $name = $_POST['name'];
    $ic = $_POST['ic'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $pass = $_POST['password'];

    // Hash password
    $hashed = hash("sha256", $pass);

    // Check if email already exists
    $check = mysqli_query($conn, "SELECT * FROM logins WHERE email='$email'");
    if (mysqli_num_rows($check) > 0) {
        echo "<script>alert('Email already registered!'); window.location='signup.php';</script>";
        exit;
    }

    // Insert into users table
    $insert_user = "INSERT INTO users (full_name, ic_passport, phone)
                    VALUES ('$name', '$ic', '$phone')";
    mysqli_query($conn, $insert_user);

    // Get new user ID
    $user_id = mysqli_insert_id($conn);

    // Insert login info
    $insert_login = "INSERT INTO logins (user_id, email, password_hash)
                     VALUES ($user_id, '$email', '$hashed')";
    mysqli_query($conn, $insert_login);

    // Create random account numbers
    $sejong_wallet_acc = generateAccountNumber();
    $saver_acc = generateAccountNumber();

    // Insert Sejong Wallet
    $insert_wallet = "INSERT INTO accounts (user_id, account_type, account_number, balance)
                  VALUES ($user_id, 'Sejong Wallet', '$sejong_wallet_acc', 0.00)";
    mysqli_query($conn, $insert_wallet);

    // Insert Personal Saver Account-i
    $insert_saver = "INSERT INTO accounts (user_id, account_type, account_number, balance)
                 VALUES ($user_id, 'Personal Saver Account-i', '$saver_acc', 0.00)";
    mysqli_query($conn, $insert_saver);


    echo "<script>alert('Account created successfully! Please login.'); window.location='login.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>SejongBank Signup</title>

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

        .signup-container {
            width: 420px;
            background: rgba(255, 255, 255, 0.88);
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
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
            font-size: 28px;
            font-weight: bold;
            color: #004b87;
            margin-bottom: 10px;
        }

        .logo span {
            color: #ff4d4d;
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
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
            box-sizing: border-box;
            transition: 0.3s;
        }

        input:focus {
            border-color: #004b87;
            outline: none;
            box-shadow: 0 0 6px rgba(0, 75, 135, 0.3);
        }

        button {
            width: 100%;
            padding: 14px;
            background: #004b87;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.25s ease;
            margin-top: 10px;
        }

        button:hover {
            background: #00345d;
            transform: translateY(-2px);
            box-shadow: 0 5px 12px rgba(0, 0, 0, 0.15);
        }

        .link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }

        .link a {
            color: #ff4d4d;
            font-weight: bold;
            text-decoration: none;
            transition: 0.25s;
        }

        .link a:hover {
            color: #c20000;
            text-decoration: underline;
        }
    </style>
</head>

<body>

    <div class="signup-container">
        <div class="logo">Sejong<span>Bank</span></div>
        <h2>Create Your Account</h2>

        <form action="" method="POST">
            <input type="text" name="name" placeholder="Full Name" required>
            <input type="text" name="ic" placeholder="IC / Passport" required>
            <input type="text" name="phone" placeholder="Phone Number" required>
            <input type="email" name="email" placeholder="Email Address" required>
            <input type="password" name="password" placeholder="Choose a Password" required>

            <button type="submit">Create Account</button>
        </form>

        <div class="link">
            Already have an account? <a href="login.php">Login here</a>
        </div>
    </div>

</body>

</html>