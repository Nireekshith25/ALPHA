<?php 
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: index.html");
    exit();
}

$servername = "localhost";
$username = "root"; // Change this to your database username
$password = ""; // Change this to your database password
$dbname = "anganwadi2"; // Updated database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user = $_SESSION['username'];
$sql = "SELECT center_id FROM anganwadi_centers WHERE username = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("s", $user);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$center_id = $row['center_id'];

// Fetch scheme progress records
$sql_scheme_progress = "SELECT s.student_name, sp.scheme_name, sp.progress_date, sp.approval_date, sp.scheme_status, sp.progress_details 
                        FROM scheme_progress sp 
                        JOIN students s ON sp.student_id = s.student_id 
                        WHERE s.center_id = ? 
                        ORDER BY sp.progress_date DESC";
$stmt_scheme_progress = $conn->prepare($sql_scheme_progress);
if ($stmt_scheme_progress === false) {
    die("Prepare failed: " . $conn->error);
}
$stmt_scheme_progress->bind_param("i", $center_id);
$stmt_scheme_progress->execute();
$result_scheme_progress = $stmt_scheme_progress->get_result();

$scheme_progress_counts = [];
while($row = $result_scheme_progress->fetch_assoc()) {
    $scheme_progress_counts[$row['scheme_name']] = ($scheme_progress_counts[$row['scheme_name']] ?? 0) + 1;
    echo "<tr>
            <td>{$row['student_name']}</td>
            <td>{$row['scheme_name']}</td>
            <td>{$row['progress_date']}</td>
            <td>{$row['approval_date']}</td>
            <td>{$row['scheme_status']}</td>
            <td>{$row['progress_details']}</td>
          </tr>";
}

$conn->close();
?>
