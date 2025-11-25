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

            //insert transaction
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
            font-family: Arial;
            background: #f9f9f9;
        }

        .container {
            width: 500px;
            margin: 50px auto;
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #004b87;
            text-align: center;
        }

        /* 🔥 Ensures ALL form elements have the SAME width */
        select,
        input {
            width: 100%;
            padding: 12px;
            margin-top: 10px;
            border-radius: 6px;
            border: 1px solid #ddd;
            box-sizing: border-box;
            font-size: 14px;
        }

        button {
            width: 100%;
            padding: 12px;
            background: #ff4d4d;
            color: white;
            margin-top: 20px;
            border-radius: 6px;
            font-weight: bold;
            border: none;
        }

        .back {
            text-align: center;
            margin-top: 20px;
        }

        .back a {
            text-decoration: none;
            color: #004b87;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <div class="container">
        <h2>Bill Payment</h2>

        <?php echo $message; ?>

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
            <input type="number" name="amount" step="0.01" required>

            <button type="submit">Pay Bill</button>
        </form>

        <div class="back">
            <a href="home.php">← Back to Home</a>
        </div>
    </div>

</body>

</html>