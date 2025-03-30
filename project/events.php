<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
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

// Include the simple_html_dom library
include('simple_html_dom.php');

// URL to scrape
$events_url = "https://dwcd.karnataka.gov.in/info-2/INTEGRATED+CHILD+DEVELOPMENT+SERVICES+SCHEME/en";
$html = file_get_html_curl($events_url);

if (is_array($html) && isset($html['error'])) {
    echo "Failed to retrieve the events page. Error: " . $html['error'];
    exit();
}

$events = [];

// Adjust the selector as per the actual HTML structure of the target page
foreach ($html->find('.event-class') as $event) {
    $event_name = $event->find('.event-name-class', 0)->innertext;
    $event_date = $event->find('.event-date-class', 0)->innertext;
    $event_description = $event->find('.event-description-class', 0)->innertext;

    $events[] = [
        'name' => $event_name,
        'date' => $event_date,
        'description' => $event_description
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upcoming Events</title>
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
            position: absolute;
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
        .event {
            margin-bottom: 20px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .event h3 {
            margin-top: 0;
        }
        .event-date {
            font-weight: bold;
            color: #4a148c;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="../image1/k_1.png" alt="Karnataka Logo">
        <h1>Upcoming Events</h1>
        <div class="official-website">
            <a href="https://www.karnataka.gov.in" target="_blank">Karnataka.gov.in</a>
        </div>
    </div>
    <div class="container">
        <?php if (empty($events)): ?>
            <p>No upcoming events found.</p>
        <?php else: ?>
            <?php foreach ($events as $event): ?>
                <div class="event">
                    <h3><?php echo $event['name']; ?></h3>
                    <p class="event-date"><?php echo $event['date']; ?></p>
                    <p><?php echo $event['description']; ?></p>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
$conn->close();
?>
