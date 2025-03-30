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

    // Check if attendance for this student on this date already exists
    $check_sql = "SELECT * FROM attendance WHERE student_id = ? AND date = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("is", $student_id, $date);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $message = "Attendance for this student on this date already exists.";
    } else {
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

    $check_stmt->close();
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

$today = date("Y-m-d");

$sql_attendance = "SELECT s.student_name, a.date, a.status 
                   FROM attendance a 
                   JOIN students s ON a.student_id = s.student_id 
                   WHERE s.center_id = ? AND a.date = ?";
$stmt_attendance = $conn->prepare($sql_attendance);
$stmt_attendance->bind_param("is", $center_id, $today);
$stmt_attendance->execute();
$result_attendance = $stmt_attendance->get_result();

// Fetch statistics
$sql_stats = "SELECT 
                SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) AS total_present, 
                SUM(CASE WHEN status = 'Absent' THEN 1 ELSE 0 END) AS total_absent 
              FROM attendance a 
              JOIN students s ON a.student_id = s.student_id 
              WHERE s.center_id = ? AND a.date = ?";
$stmt_stats = $conn->prepare($sql_stats);
$stmt_stats->bind_param("is", $center_id, $today);
$stmt_stats->execute();
$result_stats = $stmt_stats->get_result();
$stats = $result_stats->fetch_assoc();

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
        .statistics {
            margin-top: 20px;
            display: flex;
            justify-content: space-around;
        }
        .stat-box {
            padding: 20px;
            background-color: #e0e0e0;
            border-radius: 5px;
            text-align: center;
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
                    <option value="">Select Student</option>
                    <?php while($student = $result_students->fetch_assoc()): ?>
                        <option value="<?php echo $student['student_id']; ?>"><?php echo $student['student_name']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="attendance_date">Date</label>
                <input type="date" id="attendance_date" name="attendance_date" value="<?php echo $today; ?>" required readonly>
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
            <h2 class="section-title">Attendance Records for Today</h2>
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
        <div class="statistics">
            <div class="stat-box">
                <h3>Total Present</h3>
                <p><?php echo $stats['total_present']; ?></p>
            </div>
            <div class="stat-box">
                <h3>Total Absent</h3>
                <p><?php echo $stats['total_absent']; ?></p>
            </div>
        </div>
        <div class="back-link">
            <a href="attendance_records.php">Go back to Update Attendance</a>
        </div>
    </div>
</body>
<script>
    document.addEventListener("DOMContentLoaded", () => {
        if ('webkitSpeechRecognition' in window) {
            const recognition = new webkitSpeechRecognition();
            recognition.lang = 'en-US';
            recognition.continuous = false; 
            recognition.interimResults = false; 

            recognition.onresult = (event) => {
                const command = event.results[0][0].transcript.toLowerCase();
                console.log("Voice Command:", command);

                if (command.startsWith("name")) {
                    const studentName = command.replace("name", "").trim();
                    const options = document.getElementById("student_name").options;
                    let found = false;
                    for (let i = 0; i < options.length; i++) {
                        if (options[i].text.toLowerCase() === studentName.toLowerCase()) {
                            document.getElementById("student_name").value = options[i].value;
                            found = true;
                            break;
                        }
                    }
                    if (!found) {
                        alert("Student name not found. Please try again.");
                    }
                }

                else if (command.startsWith("date")) {
                    const dateInput = command.replace("date", "").trim();
                    const dateRegex = /^\d{2}-\d{2}-\d{4}$/; 
                    if (dateRegex.test(dateInput)) {
                        const [day, month, year] = dateInput.split("-");
                        const formattedDate = ${year}-${month}-${day}; 
                        document.getElementById("attendance_date").value = formattedDate;
                    } else {
                        alert("Please provide a valid date in DD-MM-YYYY format.");
                    }
                }

                else if (command.startsWith("status")) {
                    const statusInput = command.replace("status", "").trim().toLowerCase();
                    if (statusInput === "present" || statusInput === "absent") {
                        document.getElementById("attendance_status").value = statusInput.charAt(0).toUpperCase() + statusInput.slice(1);
                    } else {
                        alert("Please provide a valid status: 'Present' or 'Absent'.");
                    }
                }

                else if (command === "submit") {
                    document.getElementById("updateForm").submit();
                } else {
                    alert("Command not recognized. Please try again. Example commands: 'Name Alice', 'Date 10-12-2024', 'Status Present', or 'Submit'.");
                }
            };

            recognition.onerror = (event) => {
                console.error("Speech recognition error: " + event.error);
                alert("There was an issue with speech recognition. Please check your microphone and try again.");
            };

            document.body.addEventListener("keydown", (event) => {
                if (event.key === "v") {
                    recognition.start();
                    alert("Speak your command. Example: 'Name Alice', 'Date 10-12-2024', 'Status Present', or 'Submit'.");
                }
            });

            document.getElementById("startVoiceCommand").addEventListener("click", () => {
                recognition.start();
                alert("Speak your command: 'Name Alice', 'Date 10-12-2024', 'Status Present', or 'Submit'.");
            });

        } else {
            alert("Your browser does not support speech recognition. Please use Google Chrome.");
        }
    });
</script>
</html>