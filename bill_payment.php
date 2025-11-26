<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
include 'db_connect.php';


$query = "SELECT account_id, account_type, balance FROM accounts WHERE user_id = $user_id";
$accounts = mysqli_query($conn, $query);

$message = "";


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $account_id = $_POST['account_id'];
    $bill_type = $_POST['bill_type'];
    $amount = floatval($_POST['amount']);

    if ($amount <= 0) {
        $message = "<p style='color:red;'>Amount must be greater than 0.</p>";
    } else {

        //check account balance
        $bal_query = "SELECT balance FROM accounts WHERE account_id = $account_id AND user_id = $user_id";
        $bal_result = mysqli_query($conn, $bal_query);
        $bal_row = mysqli_fetch_assoc($bal_result);

        if ($bal_row['balance'] < $amount) {
            $message = "<p style='color:red;'>Insufficient balance.</p>";
        } else {

            //deduct balance
            $update = "UPDATE accounts 
                       SET balance = balance - $amount 
                       WHERE account_id = $account_id AND user_id = $user_id";
            mysqli_query($conn, $update);

            //insert into transaction
            $desc = "Bill Payment - $bill_type";
            $insert = "INSERT INTO transactions (user_id, account_id, type, amount, description)
                       VALUES ($user_id, $account_id, 'Bill Payment', $amount, '$desc')";
            mysqli_query($conn, $insert);

            $message = "<p style='color:green;'>Bill payment successful!</p>";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bill Payment | SJB</title>

    <style>
        body {
            font-family: 'Segoe UI', Arial;
            margin: 0;
            background: linear-gradient(135deg, #ff4d4d 0%, #ffffff 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .container {
            width: 650px;
            background: rgba(255, 255, 255, 0.92);
            padding: 35px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            backdrop-filter: blur(10px);
            animation: fadeIn 0.8s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        h2 {
            color: #004b87;
            text-align: center;
            margin-bottom: 25px;
            font-size: 28px;
            font-weight: bold;
        }

        /* Bigger, modern form fields */
        select, input {
            width: 100%;
            padding: 15px;
            margin-top: 12px;
            border-radius: 10px;
            border: 1px solid #ccc;
            background: #fafafa;
            font-size: 15px;
            transition: 0.3s;
        }

        select:focus, input:focus {
            border-color: #004b87;
            outline: none;
            background: #fff;
            box-shadow: 0 0 8px rgba(0,75,135,0.3);
        }

        label {
            font-weight: bold;
            margin-top: 18px;
            display: block;
            color: #333;
        }

        /* Larger modern button */
        button {
            width: 100%;
            padding: 15px;
            margin-top: 30px;
            background: #ff4d4d;
            color: white;
            border-radius: 10px;
            border: none;
            font-size: 17px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.25s;
        }

        button:hover {
            background: #c20000;
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(0,0,0,0.2);
        }

        /* Back button */
        .back a {
            display: inline-block;
            margin-top: 25px;
            padding: 10px 20px;
            background: #004b87;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
        }

        .back a:hover {
            background: #00345d;
        }

        /* Message box styling */
        .msg-success {
            color: #0a7a26;
            padding: 10px;
            background: #dfffe6;
            border-left: 5px solid #0a7a26;
            margin-bottom: 15px;
            border-radius: 6px;
        }

        .msg-error {
            color: #b00000;
            padding: 10px;
            background: #ffe0e0;
            border-left: 5px solid #b00000;
            margin-bottom: 15px;
            border-radius: 6px;
        }
    </style>
</head>

<body>

    <div class="container">

        <h2>Bill Payment</h2>

        <!-- Dynamic message box -->
        <?php 
        if (!empty($message)) {
            if (strpos($message, 'successful') !== false) {
                echo "<div class='msg-success'>$message</div>";
            } else {
                echo "<div class='msg-error'>$message</div>";
            }
        }
        ?>

        <form action="" method="POST">

            <label>Select Account</label>
            <select name="account_id" required>
                <option value="">-- Choose Account --</option>
                <?php while ($acc = mysqli_fetch_assoc($accounts)) { ?>
                    <option value="<?php echo $acc['account_id']; ?>">
                        <?php echo $acc['account_type'] . " (RM " . number_format($acc['balance'], 2) . ")"; ?>
                    </option>
                <?php } ?>
            </select>

            <label>Select Bill Type</label>
            <select name="bill_type" required>
                <option value="">-- Choose Bill --</option>
                <option value="Electricity">Electricity</option>
                <option value="Water">Water</option>
                <option value="Internet">Internet</option>
                <option value="Mobile Postpaid">Mobile Postpaid</option>
                <option value="Astro TV">Astro TV</option>
                <option value="Car Loan">Car Loan</option>
            </select>

            <label>Amount (RM)</label>
            <input type="number" name="amount" step="0.01" placeholder="Enter payment amount" required>

            <button type="submit">Pay Bill</button>
        </form>

        <div class="back">
            <a href="home.php">← Back to Home</a>
        </div>

    </div>

</body>
</html>
