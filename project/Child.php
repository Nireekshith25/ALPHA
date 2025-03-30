<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: index.html");
    exit();
}

// Include the database connection
include('db_connection.php');

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect and sanitize input data
    $student_name = $_POST['student_name'];
    $age = $_POST['age'];
    $dob = $_POST['dob'];
    $father_name = $_POST['father_name'];
    $mother_name = $_POST['mother_name'];
    $aadhaar_number = $_POST['aadhaar_number'];
    $disabilities = $_POST['disabilities'] ?? 'No';
    $description = $_POST['description'];
    $extra_details = $_POST['extra_details'] ?? 'No';

    // Handle file upload
    $photo_path = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES['photo']['name']);
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_file)) {
            $photo_path = $target_file;
        } else {
            echo "Failed to upload the photo.";
            exit;
        }
    }

    // Prepare the SQL query to insert the data
    $sql = "INSERT INTO students (center_id, student_name, age, dob, father_name, mother_name, aadhaar_number, disabilities, description, extra_details, photo_path) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    // Prepare and bind the parameters
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        die('MySQL prepare error: ' . $conn->error);
    }

    // Bind the parameters
    $stmt->bind_param("isissssssss", $_SESSION['center_id'], $student_name, $age, $dob, $father_name, $mother_name, $aadhaar_number, $disabilities, $description, $extra_details, $photo_path);

    // Execute the query
    if ($stmt->execute()) {
        echo "Child details added successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close the statement
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Child</title>
    <style>
        /* Add your CSS styling here */
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
        .header img {
            height: 50px;
            vertical-align: middle;
            margin-right: 20px;
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
        .form-group button,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            box-sizing: border-box;
        }
        .form-group button {
            background-color: #4a148c;
            color: white;
            border: none;
            cursor: pointer;
        }
        .form-group button:hover {
            background-color: #6a1b9a;
        }
        #extra-details-section, #disabilities-section {
            display: none;
        }
    </style>
</head>
<body>

    <div class="header">
        <img src="../image1/k_1.png" alt="Government Logo">  <!-- Add your logo here -->
        <h1>Add New Child</h1>
    </div>

    <div class="container">
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="form-group">
                <label for="student_name">Child's Name:</label>
                <input type="text" id="student_name" name="student_name" required>
            </div>

            <div class="form-group">
                <label for="age">Age:</label>
                <input type="number" id="age" name="age" min="1" max="20" required>
            </div>

            <div class="form-group">
                <label for="dob">Date of Birth:</label>
                <input type="date" id="dob" name="dob" required>
            </div>

            <div class="form-group">
                <label for="father_name">Father's Name:</label>
                <input type="text" id="father_name" name="father_name" required>
            </div>

            <div class="form-group">
                <label for="mother_name">Mother's Name:</label>
                <input type="text" id="mother_name" name="mother_name" required>
            </div>

            <div class="form-group">
                <label for="aadhaar_number">Aadhaar Number:</label>
                <input type="text" id="aadhaar_number" name="aadhaar_number" pattern="\d{12}" required>
                <small>(Enter a 12-digit Aadhaar number)</small>
            </div>

            <div class="form-group">
                <label for="photo">Child's Photo:</label>
                <input type="file" id="photo" name="photo" accept="image/*">
                <br><br>
                <!-- Display uploaded photo -->
                <?php if (isset($photo_path)) { echo "<img src='$photo_path' width='100'>"; } ?>
            </div>

            <div class="form-group">
                <label for="disabilities">Does the child have disabilities?</label><br>
                <input type="radio" id="disabilities_yes" name="disabilities_option" value="Yes" onchange="toggleDisabilities(true)">
                <label for="disabilities_yes">Yes</label><br>
                <input type="radio" id="disabilities_no" name="disabilities_option" value="No" checked onchange="toggleDisabilities(false)">
                <label for="disabilities_no">No</label>
            </div>

            <div id="disabilities-section">
                <div class="form-group">
                    <label for="disabilities">Disabilities Description:</label>
                    <textarea id="disabilities" name="disabilities"></textarea>
                </div>
            </div>

            <div class="form-group">
                <label for="extra_details">Do you want to provide extra details?</label><br>
                <input type="radio" id="extra_details_yes" name="extra_details_option" value="Yes" onchange="toggleExtraDetails(true)">
                <label for="extra_details_yes">Yes</label><br>
                <input type="radio" id="extra_details_no" name="extra_details_option" value="No" checked onchange="toggleExtraDetails(false)">
                <label for="extra_details_no">No</label>
            </div>

            <div id="extra-details-section">
                <div class="form-group">
                    <label for="extra_details">Extra Details:</label>
                    <textarea id="extra_details" name="extra_details"></textarea>
                </div>
            </div>

            <div class="form-group">
                <button type="submit">Add Child</button>
            </div>
        </form>
    </div>

    <script>
        function toggleDisabilities(show) {
            document.getElementById('disabilities-section').style.display = show ? 'block' : 'none';
        }

        function toggleExtraDetails(show) {
            document.getElementById('extra-details-section').style.display = show ? 'block' : 'none';
        }
    </script>

</body>
</html>
