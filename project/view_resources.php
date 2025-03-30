<?php 
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

// Fetch resources
$result = $conn->query("SELECT * FROM resources");

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Allocated Resources</title>
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
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
        .back-button {
            margin-bottom: 20px;
        }
        .back-button a {
            background-color: #4a148c;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Allocated Resources</h1>
    </div>
    <div class="container">
        <div class="back-button">
            <a href="allocate_resources.html">Back to Allocate Resources</a>
        </div>
        <?php if ($result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Resource Type</th>
                        <th>File Path</th>
                        <th>Upload Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['resource_type']); ?></td>
                            <td><?php echo htmlspecialchars(basename($row['file_path'])); ?></td>
                            <td><?php echo htmlspecialchars($row['upload_date']); ?></td>
                            <td><a href="view_file.php?file=<?php echo urlencode($row['file_path']); ?>" target="_blank">Open</a></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No resources found.</p>
        <?php endif; ?>
    </div>
</body>
</html>
