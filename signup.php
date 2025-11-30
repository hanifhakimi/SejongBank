<?php

// Generate 12-digit account number
function generateAccountNumber()
{
    $num = "";
    for ($i = 0; $i < 12; $i++) {
        $num .= rand(0, 9);
    }
    return $num;
}

// Generate UNIQUE 12-digit card number
function generateUniqueCardNumber($conn)
{
    do {
        // 16-digit card number
        $card = "";
        for ($i = 0; $i < 16; $i++) {
            $card .= rand(0, 9);
        }

        $check = mysqli_query($conn, "SELECT user_id FROM cards WHERE card_number='$card'");
    } while ($check && mysqli_num_rows($check) > 0);

    return $card;
}


include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $name  = $_POST['name'];
    $ic    = $_POST['ic'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $pass  = $_POST['password'];
    $pin   = $_POST['pin']; // Card PIN

    // Validate 4-digit PIN
    if (!preg_match('/^[0-9]{4}$/', $pin)) {
        echo "<script>alert('PIN must be 4 digits!'); window.location='signup.php';</script>";
        exit;
    }

    // Hash login password
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

    // Get new user id
    $user_id = mysqli_insert_id($conn);

    // Insert login info
    $insert_login = "INSERT INTO logins (user_id, email, password_hash)
                     VALUES ($user_id, '$email', '$hashed')";
    mysqli_query($conn, $insert_login);

    // Create 2 default accounts
    $wallet = generateAccountNumber();
    $saver  = generateAccountNumber();

    mysqli_query($conn, "INSERT INTO accounts (user_id, account_type, account_number, balance)
                         VALUES ($user_id, 'Sejong Wallet', '$wallet', 0.00)");

    mysqli_query($conn, "INSERT INTO accounts (user_id, account_type, account_number, balance)
                         VALUES ($user_id, 'Personal Saver Account-i', '$saver', 0.00)");

    // Create virtual card
    $card_number = generateUniqueCardNumber($conn);
    $valid_years = rand(3, 5);
    $valid_until = date('m/Y', strtotime("+$valid_years years"));
    $cvc = str_pad(rand(0, 999), 3, '0', STR_PAD_LEFT);

    // Insert card
    mysqli_query($conn, "INSERT INTO cards (user_id, card_number, cardholder_name, valid_until, pin, cvc)
                         VALUES ($user_id, '$card_number', '$name', '$valid_until', '$pin', '$cvc')");

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
            font-family: Segoe UI, Arial, sans-serif;
            background: linear-gradient(135deg, #ff4d4d, #ffffff);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .signup-container {
            width: 420px;
            background: rgba(255,255,255,0.88);
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
            backdrop-filter: blur(10px);
            animation: fadeIn 0.9s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .logo {
            text-align: center;
            font-size: 28px;
            font-weight: bold;
            color: #004b87;
            margin-bottom: 10px;
        }

        .logo span { color: #ff4d4d; }

        h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #333;
            font-size: 22px;
        }

        input {
            width: 100%;
            padding: 14px;
            margin: 12px 0;
            border-radius: 8px;
            border: 1px solid #bbb;
            font-size: 15px;
        }

        input:focus {
            border-color: #004b87;
            outline: none;
            box-shadow: 0 0 6px rgba(0,75,135,0.3);
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
            margin-top: 10px;
            transition: 0.2s ease;
        }

        button:hover {
            background: #00345d;
            transform: translateY(-2px);
        }

        .link {
            margin-top: 20px;
            text-align: center;
        }

        .link a {
            color: #ff4d4d;
            font-weight: bold;
            text-decoration: none;
        }

        .link a:hover { text-decoration: underline; }
    </style>
</head>

<body>

<div class="signup-container">
    <div class="logo">Sejong<span>Bank</span></div>
    <h2>Create Your Account</h2>

    <form action="" method="POST">
        <input type="text" name="name" placeholder="Full Name" required>

        <!-- IC: 000000-00-0000 -->
        <input type="text"
               name="ic"
               id="ic"
               placeholder="IC / Passport"
               maxlength="14"
               required>

        <!-- Phone: 010-000-0000 -->
        <input type="text"
               name="phone"
               id="phone"
               placeholder="Phone Number"
               maxlength="12"
               required>

        <input type="email" name="email" placeholder="Email Address" required>

        <input type="password" name="password" placeholder="Choose a Password" required>

        <input type="password" name="pin" placeholder="4-digit Card PIN" pattern="\d{4}" required>

        <button type="submit">Create Account</button>
    </form>

    <div class="link">
        Already have an account? <a href="login.php">Login here</a>
    </div>
</div>

<script>
// Format IC as 000000-00-0000
function formatIC(value) {
    value = value.replace(/\D/g, '').substring(0, 12);

    let result = '';
    if (value.length > 0) result = value.substring(0, 6);
    if (value.length > 6) result += '-' + value.substring(6, 8);
    if (value.length > 8) result += '-' + value.substring(8);
    return result;
}

// Format phone as 010-000-0000
function formatPhone(value) {
    value = value.replace(/\D/g, '').substring(0, 10);

    let result = '';
    if (value.length > 0) result = value.substring(0, 3);
    if (value.length > 3) result += '-' + value.substring(3, 6);
    if (value.length > 6) result += '-' + value.substring(6);
    return result;
}

document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('ic').addEventListener('input', function () {
        this.value = formatIC(this.value);
    });

    document.getElementById('phone').addEventListener('input', function () {
        this.value = formatPhone(this.value);
    });
});
</script>

</body>
</html>
