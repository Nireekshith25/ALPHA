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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_POST['student_id'];
    $scheme = $_POST['scheme'];
    $progress_details = $_POST['progress_details'];
    $report_file = $_FILES['report_file'];

    // Validate file size (e.g., max 50MB)
    $max_file_size = 50 * 1024 * 1024; // 50MB
    if ($report_file['size'] > $max_file_size) {
        $_SESSION['flash_message'] = "Error: File size exceeds 50MB.";
        $_SESSION['flash_message_type'] = "error";
    } else {
        // Handle file upload
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($report_file["name"]);
        if (move_uploaded_file($report_file["tmp_name"], $target_file)) {
            // Save report details to the database
            $stmt = $conn->prepare("INSERT INTO progress_reports (student_id, scheme, progress_details, report_file_path) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isss", $student_id, $scheme, $progress_details, $target_file);
            if ($stmt->execute()) {
                $_SESSION['flash_message'] = "Report submitted successfully.";
                $_SESSION['flash_message_type'] = "success";
            } else {
                $_SESSION['flash_message'] = "Error submitting report: " . $stmt->error;
                $_SESSION['flash_message_type'] = "error";
            }
            $stmt->close();
        } else {
            $_SESSION['flash_message'] = "Error uploading file.";
            $_SESSION['flash_message_type'] = "error";
        }
    }

    header("Location: submit_reports.php");
    exit();
}

$conn->close();
?>
