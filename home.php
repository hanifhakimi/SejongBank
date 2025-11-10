<?php
//test1 : updating repositories
//test2 : updating repositories - HP
// Example placeholders for database connection (you’ll replace later)


//testtest
$mae_balance = "0.00";
$personal_saver_balance = "0.00";
$user_name = "user 1";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SejongBank | SJB</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            /* 🔴 Changed background to a red-white gradient */
            background: linear-gradient(135deg, #ff4d4d 0%, #ffffff 100%);
            color: #333;
            min-height: 100vh;
        }

        /* Header Section */
        .header {
            background-color: #fff;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 50px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .header-left h1 {
            color: #004b87;
            font-size: 22px;
            margin: 0;
        }

        .header-right a {
            text-decoration: none;
            color: #333;
            margin-left: 20px;
            font-weight: bold;
        }

        /* Sub-navigation bar */
        .nav {
            background-color: #fff;
            display: flex;
            justify-content: center;
            border-bottom: 1px solid #ddd;
        }

        .nav a {
            padding: 15px 25px;
            display: block;
            color: #333;
            text-decoration: none;
            font-weight: 500;
        }

        .nav a:hover {
            background-color: #f3f3f3;
            border-bottom: 3px solid #ffcc00;
        }

        /* Accounts Section */
        .container {
            max-width: 1000px;
            margin: 40px auto;
            background-color: #fff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        h2 {
            font-size: 20px;
            margin-bottom: 25px;
        }

        .accounts {
            display: flex;
            gap: 20px;
        }

        .card {
            flex: 1;
            background-color: #fafafa;
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 1px 5px rgba(0,0,0,0.05);
        }

        .card-title {
            font-weight: bold;
            font-size: 16px;
            color: #004b87;
        }

        .card-number {
            color: #666;
            font-size: 13px;
            margin-top: 5px;
        }

        .balance {
            font-size: 22px;
            color: #333;
            margin-top: 20px;
        }

        /* Footer Section */
        .footer {
            text-align: center;
            margin-top: 40px;
            padding: 40px 0;
            background-color: #fff;
            box-shadow: 0 -2px 5px rgba(0,0,0,0.05);
        }

        .footer h3 {
            color: #333;
        }

        .footer p {
            color: #777;
            font-size: 14px;
        }

        /* Right Sidebar (Optional) */
        .sidebar {
            position: fixed;
            right: 0;
            top: 0;
            width: 300px;
            height: 100vh;
            background-color: #111;
            color: white;
            padding: 20px;
        }

        .sidebar h4 {
            margin-top: 60px;
            color: #ffcc00;
        }

        .username {
            margin-top: 15px;
            font-size: 14px;
            color: #fff;
        }
    </style>
</head>
<body>

    <div class="header">
        <div class="header-left">
            <h1>SejongBank | SJB</h1>
        </div>
        <div class="header-right">
            <a href="#">INBOX</a>
            <a href="#">SETTINGS</a>
            <a href="#">LOGOUT</a>
        </div>
    </div>

    <div class="nav">
        <a href="#">ACCOUNTS</a>
        <a href="#">CARDS</a>
        <a href="#">FIXED DEPOSIT</a>
        <a href="#">LOAN/FINANCING</a>
        <a href="#">WEALTH</a>
    </div>

    <div class="container">
        <h2>Savings / Current Accounts</h2>
        <div class="accounts">
            <div class="card">
                <div class="card-title">MAE Wallet</div>
                <div class="card-number">000111222333</div>
                <div class="balance">RM <?php echo $mae_balance; ?></div>
            </div>

            <div class="card">
                <div class="card-title">Personal Saver Account-i</div>
                <div class="card-number">1122233344455</div>
                <div class="balance">RM <?php echo $personal_saver_balance; ?></div>
            </div>
        </div>
    </div>

    <div class="footer">
        <h3>Get more from your money!</h3>
        <p>Plan, create, and track your financial goals with the Goal Savings Plan.</p>
    </div>

</body>
</html>
