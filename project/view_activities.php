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

// Fetch activities
$activities_query = $conn->query("SELECT * FROM curriculum_activities WHERE center_id = $center_id");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Planned Activities</title>
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #4a148c;
            color: white;
        }
        .form-group {
            margin-top: 20px;
        }
        .form-group a {
            background-color: #4a148c;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
        }
        .form-group a:hover {
            background-color: #6a1b9a;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="../image1/k_1.png" alt="Karnataka Logo">
        <h1>Planned Curriculum Activities</h1>
        <div class="official-website">
            <a href="https://www.karnataka.gov.in" target="_blank">Karnataka.gov.in</a>
        </div>
    </div>
    <div class="container">
        <table>
            <thead>
                <tr>
                    <th>Activity Name</th>
                    <th>Date</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Description</th>
                    <th>Age Group</th>
                    <th>Participant Count</th>
                    <th>Resources Needed</th>
                    <th>Activity Type</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($activity = $activities_query->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $activity['activity_name']; ?></td>
                        <td><?php echo $activity['activity_date']; ?></td>
                        <td><?php echo $activity['start_time']; ?></td>
                        <td><?php echo $activity['end_time']; ?></td>
                        <td><?php echo $activity['activity_description']; ?></td>
                        <td><?php echo $activity['age_group']; ?></td>
                        <td><?php echo $activity['participant_count']; ?></td>
                        <td><?php echo $activity['resources_needed']; ?></td>
                        <td><?php echo $activity['activity_type']; ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <div class="form-group">
            <a href="add_activity.php">Back to Add Activity</a>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?>
