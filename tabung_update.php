<?php
session_start();
include 'db_connect.php';

$piggybank_id = $_GET['id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $amount = $_POST['amount'];

    $update = "UPDATE piggybank 
               SET current_amount = current_amount + $amount
               WHERE piggybank_id = $piggybank_id";

    mysqli_query($conn, $update);

    header("Location: tabung.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Add Money</title>
<style>
    body { font-family: Arial; background: #f3f3f3; }
    .container {
        width: 400px; margin: 100px auto; background: #fff;
        padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px #ccc;
    }
    input, button {
        width: 100%; padding: 10px; margin-top: 10px;
        border-radius: 5px; border: 1px solid #ccc;
    }
    button {
        background: #ffaa00; color: white; border: none;
    }
</style>
</head>
<body>

<div class="container">
    <h2>Add Money to Tabung</h2>

    <form method="POST">
        <label>Amount (KRW)</label>
        <input type="number" step="0.01" name="amount" required>

        <button type="submit">Add</button>
    </form>
</div>

</body>
</html>
