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

// Fetch immunizations
$sql_immunizations = "SELECT s.student_name, i.immunization_name, i.immunization_date, i.notes 
                      FROM immunizations i 
                      JOIN students s ON i.student_id = s.student_id 
                      WHERE s.center_id = ? 
                      ORDER BY i.immunization_date DESC";
$stmt_immunizations = $conn->prepare($sql_immunizations);
if ($stmt_immunizations === false) {
    die("Prepare failed: " . $conn->error);
}
$stmt_immunizations->bind_param("i", $center_id);
$stmt_immunizations->execute();
$result_immunizations = $stmt_immunizations->get_result();

$immunization_counts = [];
while ($row = $result_immunizations->fetch_assoc()) {
    if (isset($immunization_counts[$row['immunization_name']])) {
        $immunization_counts[$row['immunization_name']]++;
    } else {
        $immunization_counts[$row['immunization_name']] = 1;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Scheduled Immunizations</title>
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
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        .back-link a {
            background-color: #4a148c;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
        }
        .back-link a:hover {
            background-color: #6a1b9a;
        }
        .chart-container {
            width: 100%;
            max-width: 600px;
            margin: 20px auto;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="header">
        <img src="../image1/k_1.png" alt="Karnataka Logo">
        <h1>View Scheduled Immunizations</h1>
        <div class="official-website">
            <a href="https://www.karnataka.gov.in" target="_blank">Karnataka.gov.in</a>
        </div>
    </div>
    <div class="container">
        <div class="section">
            <h2 class="section-title">Scheduled Immunizations</h2>
            <table>
                <tr>
                    <th>Student Name</th>
                    <th>Immunization Name</th>
                    <th>Immunization Date</th>
                    <th>Notes</th>
                </tr>
                <?php
                $result_immunizations->data_seek(0);
                while($immunization = $result_immunizations->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $immunization['student_name']; ?></td>
                        <td><?php echo $immunization['immunization_name']; ?></td>
                        <td><?php echo $immunization['immunization_date']; ?></td>
                        <td><?php echo $immunization['notes']; ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>
        <div class="chart-container">
            <h2 class="section-title">Immunization Distribution</h2>
            <canvas id="immunizationChart"></canvas>
        </div>
        <div class="back-link">
            <a href="schedule_immunizations.php">Back to Schedule Immunizations</a>
        </div>
    </div>
    <script>
        const immunizationCounts = <?php echo json_encode($immunization_counts); ?>;
        const labels = Object.keys(immunizationCounts);
        const data = Object.values(immunizationCounts);

        const ctx = document.getElementById('immunizationChart').getContext('2d');
        const immunizationChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Number of Immunizations',
                    data: data,
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Immunization Distribution'
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
