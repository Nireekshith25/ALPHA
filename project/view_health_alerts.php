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
$alerts_query = $conn->query("SELECT * FROM health_alerts ORDER BY alert_date DESC");
$alerts = $alerts_query->fetch_all(MYSQLI_ASSOC);

$conn->close();
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
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .header {
            background-color: #4a148c;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .container {
            width: 80%;
            margin: 20px auto;
            padding: 20px;
            background-color: white;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .alert {
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f2dede;
        }
        .alert-title {
            font-weight: bold;
            color: red;
        }
        .alert-date {
            color: #888;
        }
        .alert-description {
            color: #a94442;
        }
        .days-left {
            font-weight: bold;
            color: red;
        }
        .back-link {
            display: block;
            margin-top: 20px;
            color: #4a148c;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Health Alerts</h1>
    </div>
    <div class="container">
        <?php if (empty($alerts)): ?>
            <p>No health alerts found.</p>
        <?php else: ?>
            <?php foreach ($alerts as $alert): ?>
                <div class="alert">
                    <div class="alert-title"><?php echo htmlspecialchars($alert['title']); ?></div>
                    <div class="alert-date"><?php echo htmlspecialchars($alert['alert_date']); ?></div>
                    <div class="alert-description"><?php echo nl2br(htmlspecialchars($alert['description'])); ?></div>
                    <?php
                    $current_date = new DateTime();
                    $alert_date = new DateTime($alert['alert_date']);
                    $interval = $current_date->diff($alert_date);
                    $days_left = $interval->format('%a days left');
                    ?>
                    <div class="days-left"><?php echo $days_left; ?></div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        <a class="back-link" href="schedule_health_alert.php">Back to Schedule Health Alert</a>
    </div>
</body>
</html>
