<?php 
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: index.html");
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'anganwadi2');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$username = $_SESSION['username'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $center_name = $_POST['center_name'];
    $profile_photo = $_FILES['profile_photo'];
    $message = "";
    $status = "";

    if ($profile_photo['name']) {
        $target_dir = __DIR__ . "/uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $target_file = $target_dir . basename($profile_photo["name"]);

        if (move_uploaded_file($profile_photo["tmp_name"], $target_file)) {
            $stmt = $conn->prepare("UPDATE anganwadi_centers SET center_name = ?, profile_photo = ? WHERE username = ?");
            $stmt->bind_param("sss", $center_name, basename($profile_photo["name"]), $username);
        } else {
            $message = "Error moving uploaded file.";
            $status = "error";
        }
    } else {
        $stmt = $conn->prepare("UPDATE anganwadi_centers SET center_name = ? WHERE username = ?");
        $stmt->bind_param("ss", $center_name, $username);
    }

    if ($stmt->execute()) {
        $message = "Profile updated successfully.";
        $status = "success";
    } else {
        $message = "Error updating profile.";
        $status = "error";
    }
    $stmt->close();
    header("Location: update_profile.html?message=" . urlencode($message) . "&status=" . $status);
    exit();
}

$conn->close();
?>
