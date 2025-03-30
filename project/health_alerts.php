<?php 
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: index.html");
    exit();
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'anganwadi2');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch health alerts
$alerts_query = $conn->query("SELECT title, description, alert_date FROM health_alerts ORDER BY alert_date DESC");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Health Alerts</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 80%;
            margin: 20px auto;
            padding: 20px;
            background-color: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            padding: 20px;
            background-color: #4a148c;
            color: white;
            position: relative;
        }
        .header img {
            height: 50px;
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
        }
        h1 {
            text-align: center;
            color: #4a148c;
        }
        .alert {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 10px;
            background-color: #f9f9f9;
        }
        .alert h2 {
            margin-top: 0;
            color: #b71c1c;
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        .back-link a {
            color: #4a148c;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="../image1/k_1.png" alt="Karnataka Logo">
            <h1>Health Alerts</h1>
        </div>
        <div class="alerts">
            <?php
            if ($alerts_query->num_rows > 0) {
                while ($alert = $alerts_query->fetch_assoc()) {
                    echo "<div class='alert'>";
                    echo "<h2>" . htmlspecialchars($alert['title']) . "</h2>";
                    echo "<p>" . htmlspecialchars($alert['description']) . "</p>";
                    echo "<p><strong>Date:</strong> " . htmlspecialchars($alert['alert_date']) . "</p>";
                    echo "</div>";
                }
            } else {
                echo "<p>No health alerts found.</p>";
            }
            ?>
        </div>
        <div class="back-link">
            <a href="index.html">Back to Home</a>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?>
