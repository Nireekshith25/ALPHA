<?php
$servername = "localhost"; // Database server (usually localhost)
$username = "root"; // Database username (default for XAMPP is root)
$password = ""; // Database password (default for XAMPP is empty)
$dbname = "anganwadi2"; // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
