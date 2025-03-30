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

$month = isset($_GET['month']) ? $_GET['month'] : date('m');
$year = isset($_GET['year']) ? $_GET['year'] : date('Y');

if (isset($_GET['all_months'])) {
    $start_date = "1970-01-01";
    $end_date = date("Y-m-d");
} else {
    $start_date = "$year-$month-01";
    $end_date = date("Y-m-t", strtotime($start_date));
}

// Fetch attendance records for the selected month or all months
$sql_attendance = "SELECT s.student_name, a.date, a.status 
                   FROM attendance a 
                   JOIN students s ON a.student_id = s.student_id 
                   WHERE s.center_id = ? AND a.date BETWEEN ? AND ? 
                   ORDER BY a.date DESC";
$stmt_attendance = $conn->prepare($sql_attendance);
if ($stmt_attendance === false) {
    die("Prepare failed: " . $conn->error);
}
$stmt_attendance->bind_param("iss", $center_id, $start_date, $end_date);
$stmt_attendance->execute();
$result_attendance = $stmt_attendance->get_result();

$attendance_data = [];
while ($row = $result_attendance->fetch_assoc()) {
    $attendance_data[] = $row;
}

$attendance_summary = [];
foreach ($attendance_data as $record) {
    $date = $record['date'];
    $status = $record['status'];
    if (!isset($attendance_summary[$date])) {
        $attendance_summary[$date] = ['Present' => 0, 'Absent' => 0];
    }
    $attendance_summary[$date][$status]++;
}

$conn->close();

// Generate month and year options
$months = [];
for ($i = 1; $i <= 12; $i++) {
    $months[] = date('F', mktime(0, 0, 0, $i, 10));
}

$years = range(date('Y'), date('Y') - 5);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Reports</title>
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
        .chart-container {
            width: 100%;
            max-width: 600px;
            margin: 20px auto;
            text-align: center;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="header">
        <img src="../image1/k_1.png" alt="Karnataka Logo">
        <h1>Attendance Reports</h1>
        <div class="official-website">
            <a href="https://www.karnataka.gov.in" target="_blank">Karnataka.gov.in</a>
        </div>
    </div>
    <div class="container">
        <form method="GET" action="">
            <label for="month">Select Month:</label>
            <select id="month" name="month">
                <?php foreach ($months as $index => $name): ?>
                    <option value="<?php echo $index + 1; ?>" <?php echo ($index + 1 == $month) ? 'selected' : ''; ?>><?php echo $name; ?></option>
                <?php endforeach; ?>
            </select>
            <label for="year">Select Year:</label>
            <select id="year" name="year">
                <?php foreach ($years as $yr): ?>
                    <option value="<?php echo $yr; ?>" <?php echo ($yr == $year) ? 'selected' : ''; ?>><?php echo $yr; ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit">View</button>
            <button type="submit" name="all_months" value="1">View All Months</button>
        </form>
        <div class="section">
            <h2 class="section-title">Attendance Records</h2>
            <table>
                <tr>
                    <th>Student Name</th>
                    <th>Date</th>
                    <th>Status</th>
                </tr>
                <?php foreach ($attendance_data as $record): ?>
                    <tr>
                        <td><?php echo $record['student_name']; ?></td>
                        <td><?php echo $record['date']; ?></td>
                        <td><?php echo $record['status']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
        <div class="chart-container">
            <h2 class="section-title">Attendance Summary (Selected Period)</h2>
            <canvas id="attendanceChart"></canvas>
        </div>
    </div>
    <script>
        const attendanceSummary = <?php echo json_encode($attendance_summary); ?>;
        const labels = Object.keys(attendanceSummary);
        const presentData = labels.map(date => attendanceSummary[date]['Present']);
        const absentData = labels.map(date => attendanceSummary[date]['Absent']);

        const ctx = document.getElementById('attendanceChart').getContext('2d');
        const attendanceChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Present',
                        data: presentData,
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Absent',
                        data: absentData,
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Attendance Summary (Selected Period)'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            },
        });
    </script>
</body>
</html>
