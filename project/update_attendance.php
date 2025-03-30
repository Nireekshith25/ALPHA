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

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = $_POST['student_id'];
    $date = $_POST['attendance_date'];
    $status = $_POST['attendance_status'];

    $sql = "INSERT INTO attendance (student_id, date, status) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $student_id, $date, $status);

    if ($stmt->execute()) {
        $message = "Attendance updated successfully.";
    } else {
        $message = "Error updating attendance: " . $conn->error;
    }

    $stmt->close();
}

$user = $_SESSION['username'];
$sql = "SELECT center_id FROM anganwadi_centers WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$center_id = $row['center_id'];

$sql_students = "SELECT student_id, student_name FROM students WHERE center_id = ?";
$stmt_students = $conn->prepare($sql_students);
$stmt_students->bind_param("i", $center_id);
$stmt_students->execute();
$result_students = $stmt_students->get_result();

$sql_attendance = "SELECT s.student_name, a.date, a.status 
                   FROM attendance a 
                   JOIN students s ON a.student_id = s.student_id 
                   WHERE s.center_id = ?";
$stmt_attendance = $conn->prepare($sql_attendance);
$stmt_attendance->bind_param("i", $center_id);
$stmt_attendance->execute();
$result_attendance = $stmt_attendance->get_result();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Attendance Records</title>
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
        .form-group select {
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
        .flash-message {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .flash-message.success {
            background-color: #dff0d8;
            color: #3c763d;
        }
        .flash-message.error {
            background-color: #f2dede;
            color: #a94442;
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        .back-link a {
            color: #4a148c;
            text-decoration: none;
        }
        .back-link a:hover {
            text-decoration: underline;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
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
    </style>
</head>
<body>
    <div class="header">
        <img src="../image1/k_1.png" alt="Karnataka Logo">
        <h1>Update Attendance Records</h1>
        <div class="official-website">
            <a href="https://www.karnataka.gov.in" target="_blank">Karnataka.gov.in</a>
        </div>
    </div>
    <div class="container">
        <?php if ($message != ""): ?>
            <div class="flash-message <?php echo strpos($message, 'successfully') !== false ? 'success' : 'error'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        <form action="" method="POST">
            <div class="form-group">
                <label for="student_name">Student Name</label>
                <select id="student_name" name="student_id" required>
                    <?php while($student = $result_students->fetch_assoc()): ?>
                        <option value="<?php echo $student['student_id']; ?>"><?php echo $student['student_name']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="attendance_date">Date</label>
                <input type="date" id="attendance_date" name="attendance_date" required>
            </div>
            <div class="form-group">
                <label for="attendance_status">Status</label>
                <select id="attendance_status" name="attendance_status" required>
                    <option value="Present">Present</option>
                    <option value="Absent">Absent</option>
                </select>
            </div>
            <div class="form-group">
                <button type="submit">Update Attendance</button>
            </div>
        </form>
        <div class="section">
            <h2 class="section-title">Attendance Records</h2>
            <table>
                <tr>
                    <th>Student Name</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
                <?php while($attendance = $result_attendance->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $attendance['student_name']; ?></td>
                        <td><?php echo $attendance['date']; ?></td>
                        <td><?php echo $attendance['status']; ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>
        <div class="back-link">
            <a href="attendance_records.php">Go back to Update Attendance</a>
        </div>
    </div>
</body>
</html>
