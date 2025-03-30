<?php
session_start();

$servername = "localhost";
$username = "root"; // Default MySQL username
$password = ""; // Default MySQL password (empty)
$dbname = "anganwadi2"; // Updated database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST['username'];
    $pass = $_POST['password'];

    // Prepare and bind
    $stmt = $conn->prepare("SELECT * FROM anganwadi_centers WHERE username = ? AND password = ?");
    $stmt->bind_param("ss", $user, $pass);

    // Execute the statement
    $stmt->execute();

    // Store the result
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $_SESSION['username'] = $user;
        header("Location: kendra.html");
        exit();
    } else {
        echo "Invalid username or password";
    }

    // Close statement
    $stmt->close();
}

// Close connection
$conn->close();
?>
