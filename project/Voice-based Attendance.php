<?php
// Fetch valid students for the logged-in center
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: index.html");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "anganwadi2";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user = $_SESSION['username'];
$sql = "SELECT center_id FROM anganwadi_centers WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$center_id = $row['center_id'];

// Fetch students for this center
$sql_students = "SELECT student_name FROM students WHERE center_id = ?";
$stmt_students = $conn->prepare($sql_students);
$stmt_students->bind_param("i", $center_id);
$stmt_students->execute();
$result_students = $stmt_students->get_result();

$validStudents = [];
while ($row = $result_students->fetch_assoc()) {
    $validStudents[] = $row['student_name'];
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anganwadi Voice-Based Attendance</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #4CAF50;
            color: white;
            text-align: center;
            padding: 1rem;
        }

        .attendance-btn, .manual-entry-btn, .view-attendance-btn {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            margin: 10px;
            border-radius: 5px;
        }

        .attendance-btn:hover, .manual-entry-btn:hover, .view-attendance-btn:hover {
            background-color: #45a049;
        }

        .attendance-status, .error-status {
            padding: 10px;
            margin: 20px;
            font-size: 18px;
            text-align: center;
            color: white;
            border-radius: 5px;
        }

        .attendance-status {
            background-color: #4CAF50;
        }

        .error-status {
            background-color: #f44336;
        }

        .attendance-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .attendance-table th, .attendance-table td {
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
        }

        .attendance-table th {
            background-color: #4CAF50;
            color: white;
        }

        .attendance-table td {
            background-color: #f9f9f9;
        }

        ul {
            list-style-type: none;
        }

        li {
            padding: 5px 0;
        }
    </style>
</head>
<body>

<header>
    <img src="../image1/k_1.png" alt="Government of India Symbol" style="width: 100px; height: auto;">
    <h1>Voice-Based Attendance for Anganwadi Centers</h1>
</header>

    <button class="attendance-btn" onclick="startVoiceRecognition()">Start Voice Recognition</button>
    <button class="manual-entry-btn" onclick="markAbsentStudents()">Mark Absent Students</button>
    <button class="view-attendance-btn" onclick="viewAttendance()">View Monthly Attendance</button>

    <div id="statusMessage" class="attendance-status" style="display:none;"></div>
    <div id="errorMessage" class="error-status" style="display:none;"></div>

    <h2>Attendance Records</h2>
    <table class="attendance-table">
        <thead>
            <tr>
                <th>Student Name</th>
                <th>Attendance Status</th>
                <th>Date & Time</th>
            </tr>
        </thead>
        <tbody id="attendanceRecords">
            <!-- Attendance records will be populated here -->
        </tbody>
    </table>

    <h2>Present Students</h2>
    <ul id="presentList"></ul>

    <h2>Absent Students</h2>
    <ul id="absentList"></ul>

    <script>
        const validStudents = <?php echo json_encode($validStudents); ?>;
        const markedPresent = new Set();
        const presentList = document.getElementById('presentList');
        const absentList = document.getElementById('absentList');
        const attendanceRecords = document.getElementById('attendanceRecords');

        function startVoiceRecognition() {
            const statusMessage = document.getElementById('statusMessage');
            const errorMessage = document.getElementById('errorMessage');

            statusMessage.style.display = 'none';
            errorMessage.style.display = 'none';

            const recognition = new (window.SpeechRecognition || window.webkitSpeechRecognition)();
            recognition.lang = 'en-US';

            recognition.start();

            recognition.onstart = function() {
                statusMessage.style.display = 'block';
                statusMessage.textContent = "Listening for your voice...";
            };

            recognition.onresult = function(event) {
                const recognizedName = event.results[0][0].transcript.trim();
                if (validStudents.includes(recognizedName)) {
                    if (!markedPresent.has(recognizedName)) {
                        markAttendance(recognizedName, "Present");
                    } else {
                        errorMessage.style.display = 'block';
                        errorMessage.textContent = `${recognizedName} has already been marked present.`;
                    }
                } else {
                    errorMessage.style.display = 'block';
                    errorMessage.textContent = `${recognizedName} is not a valid student.`;
                }
            };

            recognition.onerror = function() {
                errorMessage.style.display = 'block';
                errorMessage.textContent = "Error recognizing speech. Please try again.";
            };

            recognition.onend = function() {
                statusMessage.style.display = 'none';
            };
        }

        function markAttendance(studentName, status) {
            const currentDateTime = new Date().toLocaleString();
            const newRow = document.createElement('tr');
            newRow.innerHTML = ` 
                <td>${studentName}</td>
                <td>${status}</td>
                <td>${currentDateTime}</td>
            `;
            attendanceRecords.appendChild(newRow);

            if (status === "Present") {
                markedPresent.add(studentName);
                const presentItem = document.createElement('li');
                presentItem.textContent = `${studentName} (Marked at: ${currentDateTime})`;
                presentList.appendChild(presentItem);
            }

            const statusMessage = document.getElementById('statusMessage');
            statusMessage.style.display = 'block';
            statusMessage.textContent = `${studentName} marked as ${status}!`;
        }

        function markAbsentStudents() {
            const absentStudents = validStudents.filter(student => !markedPresent.has(student));
            absentStudents.forEach(student => {
                const absentItem = document.createElement('li');
                absentItem.textContent = student;
                absentList.appendChild(absentItem);
                markAttendance(student, "Absent");
            });
        }

        function viewAttendance() {
            alert("Feature to view monthly attendance will be implemented.");
        }
    </script>

</body>
</html>
