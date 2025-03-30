<?php
session_start();

// Check if the user is logged in by verifying the 'username' session variable
if (!isset($_SESSION['username'])) {
    header("Location: index.html");
    exit();
}

// Retrieve the username from the session
$username = $_SESSION['username'];

// Database connection
$conn = new mysqli('localhost', 'root', '', 'anganwadi2');
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Fetch students belonging to the logged-in user (based on 'username')
$student_query = "SELECT student_id, student_name FROM students WHERE center_id = (SELECT center_id FROM anganwadi_centers WHERE username = ?)";
$stmt = $conn->prepare($student_query);
$stmt->bind_param("s", $username); // Use the username to filter students by center_id
$stmt->execute();
$student_result = $stmt->get_result();
$students = [];
while ($row = $student_result->fetch_assoc()) {
    $students[] = $row;
}

// If the form is submitted, process the download
if (isset($_GET['student_id']) && isset($_GET['student_name'])) {
    $student_id = intval($_GET['student_id']);
    $student_name = $_GET['student_name'];

    require('fpdf.php'); // Include the FPDF library

    // Fetch student details
    $student_details_query = "SELECT * FROM students WHERE student_id = ? AND student_name = ?";
    $stmt = $conn->prepare($student_details_query);
    $stmt->bind_param("is", $student_id, $student_name);
    $stmt->execute();
    $student_details_result = $stmt->get_result();
    $student_data = $student_details_result->fetch_assoc();

    // Fetch health records
    $health_query = "SELECT * FROM health_records WHERE student_id = ?";
    $stmt = $conn->prepare($health_query);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $health_result = $stmt->get_result();

    if (!$student_data) {
        die("No student found with the provided details.");
    }

    // Create PDF object
    $pdf = new FPDF();
    $pdf->AddPage();

    // Include the font (provide full or relative path to the font files)
    // Make sure the 'times.php' and 'times.ttf' files are in the correct location
    $pdf->AddFont('Times', 'B', 'C:/xampp/htdocs/ANGANWADI/project/times.php');

    // Set font to Times-Bold for the title
    $pdf->SetFont('Times', 'B', 16);

    // Title
    $pdf->Cell(0, 10, "Student Full Details", 0, 1, 'C');
    $pdf->Ln(10);

    // Student Details
    $pdf->SetFont('Times', '', 12);
    $pdf->Cell(50, 10, "Student ID: ", 0, 0);
    $pdf->Cell(0, 10, $student_data['student_id'], 0, 1);
    $pdf->Cell(50, 10, "Name: ", 0, 0);
    $pdf->Cell(0, 10, $student_data['student_name'], 0, 1);

    // Health Records
    $pdf->Ln(10);
    $pdf->SetFont('Times', 'B', 14);
    $pdf->Cell(0, 10, "Health Records", 0, 1);
    $pdf->Ln(5);

    // Use regular font size for details
    $pdf->SetFont('Times', '', 12);

    // Loop through health records and display them
    while ($health_record = $health_result->fetch_assoc()) {
        $pdf->Cell(50, 10, "Record ID: ", 0, 0);
        $pdf->Cell(0, 10, $health_record['record_id'], 0, 1);
        $pdf->Cell(50, 10, "Details: ", 0, 0);
        $pdf->MultiCell(0, 10, $health_record['details']);
        $pdf->Ln(5);
    }

    // Output the PDF and force download with the given file name
    $pdf->Output('D', "Student_{$student_id}_Details.pdf");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Download Student Details</title>
    <style>
        body {
            font-family: Times, sans-serif;
            margin: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #f4f4f4;
        }

        #container {
            width: 100%;
            max-width: 600px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            background-color: white;
            text-align: center;
        }

        h1 {
            margin-bottom: 20px;
            color: #007BFF;
        }

        select, input[type="text"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        input[type="submit"] {
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            padding: 10px 20px;
            margin-top: 20px;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }

        .download-button {
            display: inline-block;
            background-color: #007BFF;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        .download-button:hover {
            background-color: #0056b3;
        }

        #gov-logo {
            width: 120px;
            height: auto;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

    <div id="container">
        <img src="../image1/k_1.png" alt="Government Logo" id="gov-logo">
        <h1>Download Student Full Details</h1>
        <form action="" method="GET">
            <label for="student_id">Select Student ID:</label>
            <select id="student_id" name="student_id" required>
                <option value="">-- Select --</option>
                <?php foreach ($students as $student): ?>
                    <option value="<?= htmlspecialchars($student['student_id']) ?>" data-name="<?= htmlspecialchars($student['student_name']) ?>">
                        <?= htmlspecialchars($student['student_id']) ?>  <!-- Only show the ID -->
                    </option>
                <?php endforeach; ?>
            </select>
            <br><br>
            <label for="student_name">Selected Student Name:</label>
            <input type="text" id="student_name" name="student_name" required readonly>
            <br><br>
            <button type="submit" class="download-button">Download Full Details</button>
        </form>
    </div>

    <script>
        // Auto-fill student name based on selected student ID
        const studentSelect = document.getElementById('student_id');
        const studentNameInput = document.getElementById('student_name');
        studentSelect.addEventListener('change', () => {
            const selectedOption = studentSelect.options[studentSelect.selectedIndex];
            studentNameInput.value = selectedOption.getAttribute('data-name');
        });
    </script>

</body>
</html>
