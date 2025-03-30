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
if ($center_query) {
    $center_id = $center_query->fetch_assoc()['center_id'];
} else {
    die("Error fetching center ID: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Progress Reports</title>
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
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            box-sizing: border-box;
        }
        .form-group input[type="file"] {
            padding: 3px;
        }
        .form-group .file-size-info {
            color: red;
            font-size: 0.9em;
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
    </style>
</head>
<body>
    <div class="header">
        <img src="../image1/k_1.png" alt="Karnataka Logo">
        <h1>Submit Progress Reports</h1>
        <div class="official-website">
            <a href="https://www.karnataka.gov.in" target="_blank">Karnataka.gov.in</a>
        </div>
    </div>
    <div class="container">
        <?php
        if (isset($_SESSION['flash_message'])) {
            $flash_message = $_SESSION['flash_message'];
            $flash_message_type = $_SESSION['flash_message_type'];
            echo "<div class='flash-message $flash_message_type'>$flash_message</div>";
            unset($_SESSION['flash_message']);
            unset($_SESSION['flash_message_type']);
        }
        ?>
        <form action="process_report.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="student">Select Student</label>
                <select id="student" name="student_id" required>
                    <option value="">--Select Student--</option>
                    <?php
                    $result = $conn->query("SELECT student_id, student_name FROM students WHERE center_id = '$center_id'");
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<option value='" . $row['student_id'] . "'>" . $row['student_name'] . "</option>";
                        }
                    } else {
                        echo "<option value=''>No students found</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="scheme">Select Scheme</label>
                <select id="scheme" name="scheme" required>
                    <option value="">--Select Scheme--</option>
                    <option value="snp">Supplementary Nutrition Program (SNP)</option>
                    <option value="immunization">Immunization</option>
                    <option value="health_checkups">Health Check-ups</option>
                    <option value="referral_services">Referral Services</option>
                    <option value="pse">Pre-school Education (PSE)</option>
                    <option value="nhe">Nutrition and Health Education (NHE)</option>
                </select>
            </div>
            <div class="form-group">
                <label for="progress_details">Progress Details</label>
                <textarea id="progress_details" name="progress_details" rows="5" required></textarea>
            </div>
            <div class="form-group">
                <label for="report_file">Upload Report File</label>
                <input type="file" id="report_file" name="report_file" accept=".pdf,.doc,.docx" required>
                <div class="file-size-info">Maximum file size: 50MB</div>
            </div>
            <div class="form-group">
                <button type="submit">Submit Report</button>
            </div>
        </form>
    </div>
</body>
</html>

<?php
$conn->close();
?>
