<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
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

// Fetch ongoing disease information
$api_url = "https://example.com/api/ongoing_diseases"; // Replace with actual API URL
$disease_data = @file_get_contents($api_url);

if ($disease_data === FALSE) {
    $diseases = [];
} else {
    $diseases = json_decode($disease_data, true);
}

// Process and display the disease information
if (isset($diseases) && is_array($diseases)) {
    foreach ($diseases as $disease) {
        if ($disease['age_group'] === 'children') { // Assuming the API provides age group information
            $title = $disease['name'];
            $description = $disease['description'];
            $alert_date = date('Y-m-d');

            $stmt = $conn->prepare("INSERT INTO health_alerts (title, description, alert_date) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $title, $description, $alert_date);
            $stmt->execute();
            $stmt->close();
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $alert_date = $_POST['alert_date'];

    $stmt = $conn->prepare("INSERT INTO health_alerts (title, description, alert_date) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $title, $description, $alert_date);
    if ($stmt->execute()) {
        $message = "Health alert scheduled successfully.";
    } else {
        $message = "Error scheduling health alert.";
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Health Alert</title>
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
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            box-sizing: border-box;
        }
        .form-group button {
            background-color: #4a148c;
            color: white;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
        }
        .form-group button:hover {
            background-color: #6a1b9a;
        }
        .message {
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .message.success {
            background-color: #dff0d8;
            color: #3c763d;
        }
        .message.error {
            background-color: #f2dede;
            color: #a94442;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Schedule Health Alert</h1>
    </div>
    <div class="container">
        <?php if (isset($message)): ?>
            <div class="message <?php echo strpos($message, 'success') !== false ? 'success' : 'error'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        <form action="schedule_health_alert.php" method="POST">
            <div class="form-group">
                <label for="title">Alert Title</label>
                <input type="text" id="title" name="title" required>
            </div>
            <div class="form-group">
                <label for="description">Alert Description</label>
                <textarea id="description" name="description" rows="5" required></textarea>
            </div>
            <div class="form-group">
                <label for="alert_date">Alert Date</label>
                <input type="date" id="alert_date" name="alert_date" required>
            </div>
            <div class="form-group">
                <button type="submit">Schedule Alert</button>
            </div>
        </form>
        <a href="view_health_alerts.php">View Health Alerts</a>
    </div>
</body>
</html>
