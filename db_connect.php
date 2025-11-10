<?php
$host = "localhost";
$user = "root"; // default XAMPP
$pass = ""; // default empty
$dbname = "sejongbank_db";

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>
