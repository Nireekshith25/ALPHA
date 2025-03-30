<?php
session_start();

// Check if the user is logged in, if not, redirect to the login page
if (!isset($_SESSION['username'])) {
    header("Location: index.html"); // Redirect to login page
    exit();
}

// Database connection details
$servername = "localhost";  // Database server name
$username = "root";         // MySQL username
$password = "";             // MySQL password
$dbname = "anganwadi2";     // Database name

// Create the connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check if the connection was successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the center_id from the session (assuming center_id is stored in session)
$center_id = $_SESSION['center_id'];

// Check if the center_id is available in the session
if (empty($center_id)) {
    echo "Center ID not found for this user. Please log in again.";
    exit();
}

// Query to fetch worker details based on the center_id
$sql = "SELECT * FROM anganwadi_workers WHERE center_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $center_id);  // Bind center_id as a parameter
$stmt->execute();
$result = $stmt->get_result();  // Execute the query and get the result

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Worker Details</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7fc;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        h2 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 30px;
        }
        .worker-details-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            overflow-x: auto;
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
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #3498db;
            color: white;
        }
        td {
            background-color: #f9f9f9;
        }
        tr:nth-child(even) td {
            background-color: #f2f2f2;
        }
        tr:hover td {
            background-color: #e6f7ff;
        }
        .logout-container {
            text-align: center;
            margin-top: 30px;
        }
        .logout-btn {
            background-color: #e74c3c;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
        }
        .logout-btn:hover {
            background-color: #c0392b;
        }
        img.profile-picture {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 50%;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="worker-details-container">
            <h2>Worker Details for Center ID: <?php echo htmlspecialchars($center_id); ?></h2>
            
            <!-- Table to display worker details -->
            <table>
                <thead>
                    <tr>
                        <th>Worker ID</th>
                        <th>Name</th>
                        <th>Role</th>
                        <th>Date of Joining</th>
                        <th>Date of Birth</th>
                        <th>Gender</th>
                        <th>Contact Number</th>
                        <th>Email</th>
                        <th>Emergency Contact</th>
                        <th>Qualifications</th>
                        <th>Aadhar Number</th>
                        <th>Bank Account</th>
                        <th>Status</th>
                        <th>Remarks</th>
                        <th>Profile</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Check if any workers are found for the given center_id
                    if ($result->num_rows > 0) {
                        // Loop through the result set and display each worker's details
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>
                                    <td>{$row['worker_id']}</td>
                                    <td>{$row['worker_name']}</td>
                                    <td>{$row['worker_role']}</td>
                                    <td>{$row['date_of_joining']}</td>
                                    <td>{$row['date_of_birth']}</td>
                                    <td>{$row['gender']}</td>
                                    <td>{$row['contact_number']}</td>
                                    <td>{$row['email_id']}</td>
                                    <td>{$row['emergency_contact']}</td>
                                    <td>{$row['qualifications']}</td>
                                    <td>{$row['aadhar_number']}</td>
                                    <td>{$row['bank_account_number']}</td>
                                    <td>{$row['employment_status']}</td>
                                    <td>{$row['remarks']}</td>";
                            // Check if profile_picture is available
                            if ($row['profile_picture']) {
                                echo "<td><img src='data:image/jpeg;base64," . base64_encode($row['profile_picture']) . "' alt='Profile Picture' class='profile-picture'></td>";
                            } else {
                                echo "<td>No Profile Picture</td>";
                            }
                            echo "</tr>";
                        }
                    } else {
                        // No workers found for this center
                        echo "<tr><td colspan='15'>No workers found for your center.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>

            <!-- Optional: Add a button to log out -->
            <div class="logout-container">
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </div>
    </div>
</body>
</html>

<?php
// Close the database connection
$stmt->close();
$conn->close();
?>
