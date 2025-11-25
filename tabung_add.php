<?php
session_start();
include 'db_connect.php';

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $target = $_POST['target'];
    $deadline = $_POST['deadline'];

    $sql = "INSERT INTO piggybank (user_id, piggybank_name, target_amount, deadline)
            VALUES ($user_id, '$name', '$target', '$deadline')";

    mysqli_query($conn, $sql);

    header("Location: tabung.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Create Tabung</title>
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
        background: #004b87; color: white; border: none;
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

    <h2 style="color:#004b87; text-align:center; margin-bottom:25px;">Create New Tabung</h2>

    <form method="POST">
        <label>Tabung Name</label>
        <input type="text" name="name" required>

        <label>Target Amount (RM)</label>
        <input type="number" step="0.01" name="target" required>

        <label>Deadline</label>
        <input type="date" name="deadline">

        <button type="submit" style="
            width:100%;
            margin-top:25px;
            background:#004b87;
            padding:14px;
            border:none;
            color:white;
            font-weight:bold;
            border-radius:8px;
            cursor:pointer;
            font-size:15px;
            box-shadow:0 2px 5px rgba(0,0,0,0.2);
        ">Create</button>
    </form>

</div>

</body>

</html>
