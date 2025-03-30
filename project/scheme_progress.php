<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scheme Progress</title>
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
        #schemeProgressChart {
            max-width: 600px;
            margin: 0 auto;
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
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="header">
        <img src="../image1/k_1.png" alt="Karnataka Logo">
        <h1>Scheme Progress</h1>
        <div class="official-website">
            <a href="https://www.karnataka.gov.in" target="_blank">Karnataka.gov.in</a>
        </div>
    </div>
    <div class="container">
        <div class="section">
            <h2 class="section-title">Scheme Progress for Students</h2>
            <table>
                <tr>
                    <th>Student Name</th>
                    <th>Scheme Name</th>
                    <th>Progress Date</th>
                    <th>Approval Date</th>
                    <th>Scheme Status</th>
                    <th>Progress Details</th>
                </tr>
                <?php include 'fetch_scheme_progress.php'; ?>
            </table>
        </div>
        <div class="section">
            <h2 class="section-title">Scheme Progress Distribution</h2>
            <canvas id="schemeProgressChart"></canvas>
        </div>
        <div class="back-link">
            <a href="dashboard.php">Go Back to Dashboard</a>
        </div>
    </div>
    <script>
        const schemeProgressData = <?php echo json_encode($scheme_progress_counts); ?>;
        const labels = Object.keys(schemeProgressData);
        const data = Object.values(schemeProgressData);

        const ctx = document.getElementById('schemeProgressChart').getContext('2d');
        const schemeProgressChart = new Chart(ctx, {
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
                        text: 'Scheme Progress Distribution'
                    }
                }
            },
        });
    </script>
</body>
</html>
