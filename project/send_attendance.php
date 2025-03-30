<?php
// Get the posted data from the request
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['records'])) {
    $attendanceRecords = $data['records'];

    // Here you can handle the sending of the data (e.g., email or file export)
    // Example: Sending email with the attendance records

    $subject = "Monthly Attendance Records";
    $message = "Here are the monthly attendance records:\n\n";

    foreach ($attendanceRecords as $record) {
        $message .= "Student Name: {$record['studentName']}, Status: {$record['status']}, Time: {$record['time']}\n";
    }

    $headers = "From: your-email@example.com";

    if (mail("nireekshithramesh3@gmail.com", $subject, $message, $headers)) {
        echo json_encode(["status" => "success", "message" => "Attendance sent successfully."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to send attendance."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "No records received."]);
}
?>
