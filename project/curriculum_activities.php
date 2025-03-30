<?php include 'newspeech.html';
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
    $activity_name = $_POST['activity_name'];
    $activity_date = date("Y-m-d");
    $activity_description = $_POST['activity_description'];
    $student_ids = $_POST['student_ids'];
    $center_id = $_SESSION['center_id'];

    // Check if the activity has been planned in the last 2 months
    $two_months_ago = date("Y-m-d", strtotime("-2 months"));
    $sql_check = "SELECT * FROM curriculum_activities WHERE center_id = ? AND activity_name = ? AND activity_date >= ?";
    $stmt_check = $conn->prepare($sql_check);
    if ($stmt_check === false) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt_check->bind_param("iss", $center_id, $activity_name, $two_months_ago);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        $message = "This activity has already been planned in the last 2 months.";
    } else {
        // Insert activity
        $sql = "INSERT INTO curriculum_activities (center_id, activity_name, activity_date, activity_description) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("isss", $center_id, $activity_name, $activity_date, $activity_description);

        if ($stmt->execute()) {
            $activity_id = $stmt->insert_id;
            // Insert participants
            foreach ($student_ids as $student_id) {
                $sql_participation = "INSERT INTO activity_participation (activity_id, student_id, participation_date) VALUES (?, ?, ?)";
                $stmt_participation = $conn->prepare($sql_participation);
                if ($stmt_participation === false) {
                    die("Prepare failed: " . $conn->error);
                }
                $stmt_participation->bind_param("iis", $activity_id, $student_id, $activity_date);
                $stmt_participation->execute();
                $stmt_participation->close();
            }
            $message = "Activity planned successfully.";
        } else {
            $message = "Error planning activity: " . $stmt->error;
        }

        $stmt->close();
    }

    $stmt_check->close();
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

$today = date("Y-m-d");
$week_start = date("Y-m-d", strtotime('-7 days'));

// Fetch students
$sql_students = "SELECT student_id, student_name FROM students WHERE center_id = ?";
$stmt_students = $conn->prepare($sql_students);
if ($stmt_students === false) {
    die("Prepare failed: " . $conn->error);
}
$stmt_students->bind_param("i", $center_id);
$stmt_students->execute();
$result_students = $stmt_students->get_result();

// Fetch activities and participation count for the past week
$sql_activities = "SELECT a.activity_name, a.activity_date, COUNT(p.student_id) as participant_count 
                   FROM curriculum_activities a 
                   LEFT JOIN activity_participation p ON a.activity_id = p.activity_id 
                   WHERE a.center_id = ? AND a.activity_date BETWEEN ? AND ? 
                   GROUP BY a.activity_id";
$stmt_activities = $conn->prepare($sql_activities);
if ($stmt_activities === false) {
    die("Prepare failed: " . $conn->error);
}
$stmt_activities->bind_param("iss", $center_id, $week_start, $today);
$stmt_activities->execute();
$result_activities = $stmt_activities->get_result();

$activities = [];
while($row = $result_activities->fetch_assoc()) {
    $activities[] = $row;
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plan Curriculum Activities</title>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
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
            height: 200px;
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
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="header">
        <img src="../image1/k_1.png" alt="Karnataka Logo">
        <h1>Plan Curriculum Activities</h1>
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
                <label for="activity_name">Activity Name</label>
                <input type="text" id="activity_name" name="activity_name" required>
            </div>
            <div class="form-group">
                <label for="activity_description">Description</label>
                <textarea id="activity_description" name="activity_description" rows="4" required></textarea>
            </div>
            <div class="form-group">
                <label for="student_ids">Select Students</label>
                <select id="student_ids" name="student_ids[]" multiple="multiple" required>
                    <?php while($student = $result_students->fetch_assoc()): ?>
                        <option value="<?php echo $student['student_id']; ?>"><?php echo $student['student_name']; ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <button type="submit">Plan Activity</button>
            </div>
        </form>
        <div class="section">
            <h2 class="section-title">Activity Participation for the Last Week</h2>
            <table>
                <tr>
                    <th>Activity Name</th>
                    <th>Activity Date</th>
                    <th>Participants</th>
                </tr>
                <?php foreach ($activities as $activity): ?>
                    <tr>
                        <td><?php echo $activity['activity_name']; ?></td>
                        <td><?php echo $activity['activity_date']; ?></td>
                        <td><?php echo $activity['participant_count']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
        <div class="section">
            <h2 class="section-title">Participation Bar Graph</h2>
            <canvas id="participationChart"></canvas>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            $('#student_ids').select2({
                placeholder: 'Select students',
                allowClear: true,
                width: 'resolve'
            });
        });

        const activities = <?php echo json_encode($activities); ?>;
        const activityNames = activities.map(activity => activity.activity_name);
        const participantCounts = activities.map(activity => activity.participant_count);

        const ctx = document.getElementById('participationChart').getContext('2d');
        const participationChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: activityNames,
                datasets: [{
                    label: 'Number of Participants',
                    data: participantCounts,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>
