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

// Fetch Anganwadi center ID associated with the logged-in user
$username = $_SESSION['username'];
$center_query = $conn->query("SELECT center_id FROM anganwadi_centers WHERE username = '$username'");
$center = $center_query->fetch_assoc();
if (!$center) {
    die("No Anganwadi center found for the logged-in user.");
}
$center_id = $center['center_id'];

$success_message = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $activity_name = $_POST['activity_name'];
    $activity_date = $_POST['activity_date'];
    $start_time = $_POST['start_time'] . " " . $_POST['start_time_ampm'];
    $end_time = $_POST['end_time'] . " " . $_POST['end_time_ampm'];
    $description = $_POST['description'];
    $age_group = $_POST['age_group'];
    $participant_count = $_POST['participant_count'];
    $resources_needed = $_POST['resources_needed'];
    $activity_type = $_POST['activity_type'];

    $stmt = $conn->prepare("INSERT INTO curriculum_activities (center_id, activity_name, activity_date, start_time, end_time, activity_description, age_group, participant_count, resources_needed, activity_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssssiss", $center_id, $activity_name, $activity_date, $start_time, $end_time, $description, $age_group, $participant_count, $resources_needed, $activity_type);
    
    if ($stmt->execute()) {
        $success_message = "Activity added successfully.";
    } else {
        $success_message = "Error adding activity: " . $stmt->error;
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Curriculum Activity</title>
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
            position: relative;
        }
        .header img {
            height: 50px;
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
        }
        .header .official-website {
            position: center;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
        }
        .header .official-website a {
            color: white;
            text-decoration: none;
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
        .form-group select,
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
        .success-message {
            padding: 10px;
            background-color: #dff0d8;
            color: #3c763d;
            margin-bottom: 20px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="../image1/k_1.png" alt="Karnataka Logo">
        <h1>Add Curriculum Activity</h1>
        <div class="official-website">
            <a href="https://www.karnataka.gov.in" target="_blank">Karnataka.gov.in</a>
        </div>
    </div>
    <div class="container">
        <?php if (!empty($success_message)): ?>
            <div class="success-message"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <form action="add_activity.php" method="POST">
            <div class="form-group">
                <label for="activity_name">Activity Name</label>
                <input type="text" id="activity_name" name="activity_name" required>
            </div>
            <div class="form-group">
                <label for="activity_date">Activity Date</label>
                <input type="date" id="activity_date" name="activity_date" required>
            </div>
            <div class="form-group">
                <label for="start_time">Start Time</label>
                <input type="time" id="start_time" name="start_time" required>
                <select id="start_time_ampm" name="start_time_ampm" required>
                    <option value="AM">AM</option>
                    <option value="PM">PM</option>
                </select>
            </div>
            <div class="form-group">
                <label for="end_time">End Time</label>
                <input type="time" id="end_time" name="end_time" required>
                <select id="end_time_ampm" name="end_time_ampm" required>
                    <option value="AM">AM</option>
                    <option value="PM">PM</option>
                </select>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="5"></textarea>
            </div>
            <div class="form-group">
                <label for="age_group">Age Group</label>
                <select id="age_group" name="age_group" required>
                    <option value="">--Select Age Group--</option>
                    <option value="0-3">0-3 years</option>
                    <option value="3-6">3-6 years</option>
                </select>
            </div>
            <div class="form-group">
                <label for="participant_count">Participant Count</label>
                <input type="number" id="participant_count" name="participant_count" value="0" required>
            </div>
            <div class="form-group">
                <label for="resources_needed">Resources Needed</label>
                <textarea id="resources_needed" name="resources_needed" rows="3" required></textarea>
            </div>
            <div class="form-group">
                <label for="activity_type">Activity Type</label>
                <select id="activity_type" name="activity_type" required>
                    <option value="">--Select Activity Type--</option>
                    <option value="Educational">Educational</option>
                    <option value="Health">Health</option>
                    <option value="Nutritional">Nutritional</option>
                    <option value="Recreational">Recreational</option>
                    <option value="Social">Social</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div class="form-group">
                <button type="submit">Add Activity</button>
            </div>
        </form>
        <div class="form-group">
            <a href="view_activities.php">View Planned Activities</a>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?>
