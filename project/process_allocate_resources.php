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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['upload_resource'])) {
    $resource_type = $_POST['resource_type'];
    $file = $_FILES['resource_file'];

    // Check file size
    if ($file['size'] > 600 * 1024 * 1024) {
        $message = "File size exceeds 600MB.";
        $status = "error";
    } else {
        $target_dir = __DIR__ . "/uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $target_file = $target_dir . basename($file["name"]);

        if (move_uploaded_file($file["tmp_name"], $target_file)) {
            $stmt = $conn->prepare("INSERT INTO resources (resource_type, file_path) VALUES (?, ?)");
            $stmt->bind_param("ss", $resource_type, $target_file);
            if ($stmt->execute()) {
                $message = "File uploaded successfully: " . basename($file["name"]);
                $status = "success";
            } else {
                $message = "Error uploading file.";
                $status = "error";
            }
            $stmt->close();
        } else {
            $message = "Error moving uploaded file.";
            $status = "error";
        }
    }

    header("Location: allocate_resources.html?message=" . urlencode($message) . "&status=" . $status);
    exit();
}

$conn->close();
?>
