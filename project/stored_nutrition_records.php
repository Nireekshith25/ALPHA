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

// Fetch all nutrition records
$sql_nutrition_records = "SELECT s.student_name, n.date, n.height, n.weight, n.bmi, n.nutrition_status, n.immunization_status, n.health_status, n.tests_conducted, n.notes 
                          FROM nutrition_records n 
                          JOIN students s ON n.student_id = s.student_id 
                          WHERE s.center_id = ? 
                          ORDER BY n.date DESC";
$stmt_nutrition_records = $conn->prepare($sql_nutrition_records);
if ($stmt_nutrition_records === false) {
    die("Prepare failed: " . $conn->error);
}
$stmt_nutrition_records->bind_param("i", $center_id);
$stmt_nutrition_records->execute();
$result_nutrition_records = $stmt_nutrition_records->get_result();

$nutrition_statuses = [];
while($row = $result_nutrition_records->fetch_assoc()) {
    $nutrition_statuses[] = $row['nutrition_status'];
}

$conn->close();

// Count occurrences of each nutrition status
$nutrition_status_counts = array_count_values($nutrition_statuses);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stored Nutrition Records</title>
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
        .section {
            margin-bottom: 20px;
        }
        .section-title {
            font-size: 1.5em;
            margin-bottom: 10px;
        }
        #nutritionStatusChart {
            max-width: 600px;
            margin: 0 auto;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="header">
        <img src="../image1/k_1.png" alt="Karnataka Logo">
        <h1>Stored Nutrition Records</h1>
        <div class="official-website">
            <a href="https://www.karnataka.gov.in" target="_blank">Karnataka.gov.in</a>
        </div>
    </div>
    <div class="container">
        <div class="section">
            <h2 class="section-title">All Nutrition Records</h2>
            <table>
                <tr>
                    <th>Student Name</th>
                    <th>Date</th>
                    <th>Height (cm)</th>
                    <th>Weight (kg)</th>
                    <th>BMI</th>
                    <th>Nutrition Status</th>
                    <th>Immunization Status</th>
                    <th>Health Status</th>
                    <th>Tests Conducted</th>
                    <th>Notes</th>
                </tr>
                <?php
                $result_nutrition_records->data_seek(0);
                while($record = $result_nutrition_records->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $record['student_name']; ?></td>
                        <td><?php echo $record['date']; ?></td>
                        <td><?php echo $record['height']; ?></td>
                        <td><?php echo $record['weight']; ?></td>
                        <td><?php echo $record['bmi']; ?></td>
                        <td><?php echo $record['nutrition_status']; ?></td>
                        <td><?php echo $record['immunization_status']; ?></td>
                        <td><?php echo $record['health_status']; ?></td>
                        <td><?php echo $record['tests_conducted']; ?></td>
                        <td><?php echo $record['notes']; ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>
        <div class="section">
            <h2 class="section-title">Nutrition Status Distribution</h2>
            <canvas id="nutritionStatusChart"></canvas>
        </div>
    </div>
    <script>
        const nutritionStatusCounts = <?php echo json_encode($nutrition_status_counts); ?>;
        const labels = Object.keys(nutritionStatusCounts);
        const data = Object.values(nutritionStatusCounts);

        const ctx = document.getElementById('nutritionStatusChart').getContext('2d');
        const nutritionStatusChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.2)',
                        'rgba(54, 162, 235, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(153, 102, 255, 0.2)',
                        'rgba(255, 159, 64, 0.2)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(153, 102, 255, 1)',
                        'rgba(255, 159, 64, 1)'
                    ],
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
                        text: 'Nutrition Status Distribution'
                    }
                }
            },
        });
    </script>
</body>
</html>
