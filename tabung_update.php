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
<body style="
    margin:0;
    font-family:Arial;
    background: linear-gradient(135deg, #ff4d4d 0%, #ffffff 100%);
    min-height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
">

<div style="
    width:600px;
    background:#fff;
    padding:30px;
    border-radius:10px;
    box-shadow:0 2px 10px rgba(0,0,0,0.1);
">

    <h2 style="color:#ffaa00; text-align:center; margin-bottom:25px;">Add Money to Tabung</h2>

    <form method="POST">
        <label>Amount (KRW)</label>
        <input type="number" step="0.01" name="amount" required
               style="width:100%; padding:12px; border-radius:8px; border:1px solid #ddd; margin-top:10px;">

        <button type="submit" style="
            width:100%;
            margin-top:25px;
            background:#ffaa00;
            padding:14px;
            border:none;
            color:white;
            font-weight:bold;
            border-radius:8px;
            cursor:pointer;
            font-size:15px;
            box-shadow:0 2px 5px rgba(0,0,0,0.2);
        ">Add Money</button>
    </form>

</div>

</body>


</html>
