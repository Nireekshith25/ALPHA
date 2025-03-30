<?php
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: index.html");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "anganwadi2";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the logged-in user's username
$username = $_SESSION['username'];

// Query to get center ID and other details for the logged-in user
$sql = "SELECT * FROM anganwadi_centers WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

// Fetch center details
$center = $result->fetch_assoc();
$center_id = $center['center_id'];
$center_name = $center['center_name']; // Assuming the center name is also stored

// Get the worker details for the center
$sql_workers = "SELECT * FROM anganwadi_workers WHERE center_id = ?";
$stmt_workers = $conn->prepare($sql_workers);
$stmt_workers->bind_param("i", $center_id);
$stmt_workers->execute();
$workers_result = $stmt_workers->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anganwadi Workers</title>
    <style>
        /* General styles */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
        }

        header {
            background-color: #003366;
            color: white;
            padding: 20px 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
        }

        .gov-symbol {
            width: 50px;
            height: 50px;
        }

        .center-info h2 {
            margin: 0;
        }

        .profile {
            display: flex;
            align-items: center;
        }

        .profile-img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
        }

        .logout-btn {
            background-color: #d32f2f;
            color: white;
            padding: 10px;
            text-decoration: none;
            border-radius: 5px;
            margin-left: 10px;
        }

        .logout-btn:hover {
            background-color: #c2185b;
        }

        /* Workers table styles */
        table {
            width: 100%;
            margin: 20px 0;
            border-collapse: collapse;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #f1f1f1;
        }
    </style>
</head>
<body>

    <!-- Header Section -->
    <header>
        <div class="header-container">
            <img src="../image1/k_1.png" alt="Gov Symbol" class="gov-symbol">
            <div class="center-info">
                <h2>Center: <?php echo $center_name; ?> (ID: <?php echo $center_id; ?>)</h2>
            </div>
            <div class="profile">
                <?php if (isset($center['profile_picture']) && !empty($center['profile_picture'])) : ?>
                    <img src="data:image/jpeg;base64,<?php echo base64_encode($center['profile_picture']); ?>" alt="Profile" class="profile-img">
                <?php else: ?>
                    <img src="default-profile.png" alt="Profile" class="profile-img">
                <?php endif; ?>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </div>
    </header>

    <!-- Workers Table Section -->
    <main>
        <h3>Workers List for Center ID: <?php echo $center_id; ?></h3>
        <table>
            <tr>
                <th>Worker ID</th>
                <th>Name</th>
                <th>Role</th>
                <th>Center Name</th>
                <th>Date of Joining</th>
                <th>Date of Birth</th>
                <th>Gender</th>
                <th>Address</th>
                <th>Contact Number</th>
                <th>Email ID</th>
                <th>Emergency Contact</th>
                <th>Qualifications</th>
                <th>Aadhar Number</th>
                <th>Bank Account</th>
                <th>IFSC Code</th>
                <th>Status</th>
                <th>Remarks</th>
                <th>Profile Picture</th>
            </tr>
            <?php while ($worker = $workers_result->fetch_assoc()) : ?>
                <tr>
                    <td><?php echo $worker['worker_id']; ?></td>
                    <td><?php echo $worker['worker_name']; ?></td>
                    <td><?php echo $worker['worker_role']; ?></td>
                    <td><?php echo $worker['center_name']; ?></td>
                    <td><?php echo $worker['date_of_joining']; ?></td>
                    <td><?php echo $worker['date_of_birth']; ?></td>
                    <td><?php echo $worker['gender']; ?></td>
                    <td><?php echo $worker['address']; ?></td>
                    <td><?php echo $worker['contact_number']; ?></td>
                    <td><?php echo $worker['email_id']; ?></td>
                    <td><?php echo $worker['emergency_contact']; ?></td>
                    <td><?php echo $worker['qualifications']; ?></td>
                    <td><?php echo $worker['aadhar_number']; ?></td>
                    <td><?php echo $worker['bank_account_number']; ?></td>
                    <td><?php echo $worker['ifsc_code']; ?></td>
                    <td><?php echo $worker['employment_status']; ?></td>
                    <td><?php echo $worker['remarks']; ?></td>
                    <td>
                        <?php if (isset($worker['profile_picture']) && !empty($worker['profile_picture'])) : ?>
                            <img src="data:image/jpeg;base64,<?php echo base64_encode($worker['profile_picture']); ?>" alt="Worker Profile" class="profile-img">
                        <?php else: ?>
                            <img src="default-profile.png" alt="Worker Profile" class="profile-img">
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </main>

</body>
</html>

<?php
$stmt->close();
$stmt_workers->close();
$conn->close();
?>
