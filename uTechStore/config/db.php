<?php
$servername = "localhost";
$username = "root"; // আপনার ডাটাবেস ইউজারনেম দিন
$password = ""; // আপনার ডাটাবেস পাসওয়ার্ড দিন
$dbname = "app_store_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>