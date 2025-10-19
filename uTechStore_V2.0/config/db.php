<?php
$servername = "sql210.infinityfree.com";
$username = "if0_40202747"; // আপনার ডাটাবেস ইউজারনেম দিন
$password = "Udoy2025"; // আপনার ডাটাবেস পাসওয়ার্ড দিন
$dbname = "if0_40202747_utechstore";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>