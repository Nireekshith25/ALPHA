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

// Fetch form data
$activity_name = $_POST['activity_name'];
$activity_date = $_POST['activity_date'];
$start_time = $_POST['start_time'] . " " . $_POST['start_time_ampm'];
$end_time = $_POST['end_time'] . " " . $_POST['end_time_ampm'];
$description = $_POST['description'];
$age_group = $_POST['age_group'];
$participant_count = $_POST['participant_count'];
$resources_needed = $_POST['resources_needed'];
$activity_type = $_POST['activity_type'];

// Insert data into database
$stmt = $conn->prepare("INSERT INTO curriculum_activities (center_id, activity_name, activity_date, start_time, end_time, activity_description, age_group, participant_count, resources_needed, activity_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("issssssiss", $center_id, $activity_name, $activity_date, $start_time, $end_time, $description, $age_group, $participant_count, $resources_needed, $activity_type);

if ($stmt->execute()) {
    header("Location: add_activity.php?success=1");
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
