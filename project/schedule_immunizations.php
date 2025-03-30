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
    $immunization_name = $_POST['immunization_name'];
    $immunization_date = $_POST['immunization_date'];
    $notes = $_POST['notes'];

    $sql = "INSERT INTO immunizations (student_id, immunization_name, immunization_date, notes) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("isss", $student_id, $immunization_name, $immunization_date, $notes);

    if ($stmt->execute()) {
        $message = "Immunization scheduled successfully.";
    } else {
        $message = "Error scheduling immunization: " . $stmt->error;
    }

    $stmt->close();
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
$_SESSION['center_id'] = $center_id;

// Fetch students
$sql_students = "SELECT student_id, student_name FROM students WHERE center_id = ?";
$stmt_students = $conn->prepare($sql_students);
if ($stmt_students === false) {
    die("Prepare failed: " . $conn->error);
}
$stmt_students->bind_param("i", $center_id);
$stmt_students->execute();
$result_students = $stmt_students->get_result();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Immunizations</title>
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
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 10px;
            box-sizing: border-box;
        }
        .form-group select[multiple] {
            height: 100px;
            overflow-y: scroll;
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
</head>
<body>
    <div class="header">
        <img src="../image1/k_1.png" alt="Karnataka Logo">
        <h1>Schedule Immunizations</h1>
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
                <label for="student_id">Student Name</label>
                <select id="student_id" name="student_id" required>
                    <?php while($student = $result_students->fetch_assoc()): ?>
                        <option value="<?php echo $student['student_id']; ?>"><?php echo $student['student_name']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="immunization_name">Immunization Name</label>
                <select id="immunization_name" name="immunization_name" required>
                    <option value="BCG">BCG</option>
                    <option value="Hepatitis B">Hepatitis B</option>
                    <option value="Polio">Polio</option>
                    <option value="DPT">DPT</option>
                    <option value="MMR">MMR</option>
                    <option value="Hib">Hib</option>
                </select>
            </div>
            <div class="form-group">
                <label for="immunization_date">Immunization Date</label>
                <input type="date" id="immunization_date" name="immunization_date" required>
            </div>
            <div class="form-group">
                <label for="notes">Additional Notes</label>
                <textarea id="notes" name="notes" rows="4" required></textarea>
            </div>
            <div class="form-group">
                <button type="submit">Schedule Immunization</button>
            </div>
        </form>
        <div class="back-link">
            <a href="view_immunizations.php">View Scheduled Immunizations</a>
        </div>
    </div>
</body>
</html>
