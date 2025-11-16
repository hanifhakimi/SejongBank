<?php
session_start();
include 'db_connect.php';

$email = $_POST['email'];
$password = $_POST['password'];

// Hash the entered password
$hashed = hash("sha256", $password);

// Check login table
$query = "SELECT * FROM logins WHERE email='$email' AND password_hash='$hashed'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 1) {
    $data = mysqli_fetch_assoc($result);

    $_SESSION['user_id'] = $data['user_id'];
    $_SESSION['email'] = $email;

    header("Location: home.php");
    exit;
} else {
    echo "<script>alert('Incorrect email or password!'); window.location='login.php';</script>";
}
?>
