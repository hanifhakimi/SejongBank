<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Replace your $user_id = 1 with:
$user_id = $_SESSION['user_id'];
?>


<?php
include 'db_connect.php';

/*-------------------------------------
   1. Fetch user full name
-------------------------------------*/
$query_user = "SELECT full_name FROM users WHERE user_id = $user_id";
$result_user = mysqli_query($conn, $query_user);
$user_data = mysqli_fetch_assoc($result_user);

$user_name = $user_data ? $user_data['full_name'] : "User";

/*-------------------------------------
   2. Fetch account balances
-------------------------------------*/
$query_accounts = "SELECT account_type, balance, account_number 
                   FROM accounts 
                   WHERE user_id = $user_id";
$result_accounts = mysqli_query($conn, $query_accounts);

$sejong_wallet_balance = "0.00";
$sejong_wallet_number = "N/A";

$personal_saver_balance = "0.00";
$personal_saver_number = "N/A";


while ($row = mysqli_fetch_assoc($result_accounts)) {
    if ($row['account_type'] === "Sejong Wallet") {
        $sejong_wallet_balance = $row['balance'];
        $sejong_wallet_number = $row['account_number'];
    }
    if ($row['account_type'] === "Personal Saver Account-i") {
        $personal_saver_balance = $row['balance'];
        $personal_saver_number = $row['account_number'];
    }
}

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
            background: linear-gradient(135deg, #ff4d4d 0%, #ffffff 100%);
            color: #333;
            min-height: 100vh;
        }

        .header {
            background-color: #fff;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 50px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
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

        .container {
            max-width: 1000px;
            margin: 40px auto;
            background-color: #fff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
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
            box-shadow: 0 1px 5px rgba(0, 0, 0, 0.05);
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

        .footer {
            text-align: center;
            margin-top: 40px;
            padding: 40px 0;
            background-color: #fff;
            box-shadow: 0 -2px 5px rgba(0, 0, 0, 0.05);
        }

        .footer h3 {
            color: #333;
        }

        .footer p {
            color: #777;
            font-size: 14px;
        }
    </style>
</head>

<body>

    <div class="header">
        <div class="header-left">
            <h1>SejongBank | Welcome, <?php echo $user_name; ?></h1>
        </div>
        <div class="header-right">
            <a href="#">INBOX</a>
            <a href="#">SETTINGS</a>
            <a href="logout.php">LOGOUT</a>
        </div>
    </div>

    <div class="nav">
        <a href="#">ACCOUNTS</a>
        <a href="#">CARDS</a>
        <a href="deposit.php">DEPOSIT</a>
        <a href="withdraw.php">WITHDRAW</a>
        <a href="#">WEALTH</a>
    </div>


    <div class="container">
        <h2>Savings / Current Accounts</h2>
        <div class="accounts">
            <div class="card">
                <div class="card-title">Sejong Wallet</div>
                <div class="card-number"><?php echo $sejong_wallet_number; ?></div>
                <div class="balance">RM <?php echo number_format($sejong_wallet_balance, 2); ?></div>
            </div>

            <div class="card">
                <div class="card-title">Personal Saver Account-i</div>
                <div class="card-number"><?php echo $personal_saver_number; ?></div>
                <div class="balance">RM <?php echo number_format($personal_saver_balance, 2); ?></div>
            </div>
        </div>
    </div>

    <div class="container" style="margin-top: 20px;">
        <h2>Quick Actions</h2>

        <div style="display: flex; gap: 20px; justify-content: space-between;">

            <!-- Tabung -->
            <a href="tabung.php" style="
                flex: 1;
                text-align: center;
                background: #004b87;
                color: white;
                padding: 20px;
                border-radius: 10px;
                text-decoration: none;
                font-weight: bold;
                box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            ">
                Tabung (Piggy Bank)
            </a>

            <!-- Bill Payment -->
            <a href="bill_payment.php" style="
                flex: 1;
                text-align: center;
                background: #ff6b6b;
                color: white;
                padding: 20px;
                border-radius: 10px;
                text-decoration: none;
                font-weight: bold;
                box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            ">
                Bill Payment
            </a>

            <!-- Currency Converter -->
            <a href="currency_converter.php" style="
                flex: 1;
                text-align: center;
                background: #ffaa00;
                color: white;
                padding: 20px;
                border-radius: 10px;
                text-decoration: none;
                font-weight: bold;
                box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            ">
                Currency Converter
            </a>

            <!-- Transaction History -->
            <a href="transaction_history.php" style="
                flex: 1;
                text-align: center;
                background: #2ecc71;
                color: white;
                padding: 20px;
                border-radius: 10px;
                text-decoration: none;
                font-weight: bold;
                box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            ">
                Transaction History
            </a>

        </div>
    </div>


    <div class="footer">
        <h3>Get more from your money!</h3>
        <p>Plan, create, and track your financial goals with the Goal Savings Plan.</p>
    </div>

</body>

</html>