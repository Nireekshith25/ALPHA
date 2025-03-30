<?php
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

$user = $_SESSION['username'];
$sql = "SELECT center_id, center_name FROM anganwadi_centers WHERE username = '$user'";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$center_id = $row['center_id'];

$sql_students = "SELECT * FROM students WHERE center_id = $center_id";
$result_students = $conn->query($sql_students);

$sql_attendance = "SELECT s.student_name, a.date, a.status FROM attendance a JOIN students s ON a.student_id = s.student_id WHERE s.center_id = $center_id";
$result_attendance = $conn->query($sql_attendance);

$sql_health_updates = "SELECT s.student_name, h.update_details, h.update_date FROM health_updates h JOIN students s ON h.student_id = s.student_id WHERE s.center_id = $center_id";
$result_health_updates = $conn->query($sql_health_updates);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ANGANWADI - Voice Command & Chatbox</title>

    <style>
    /* General body styling for page alignment */
    body {
        font-family: Arial, sans-serif;
        display: flex;
        flex-direction: column;
        min-height: 100vh;
        margin: 0;
        background-color: #f4f4f4;
    }

    .container {
        max-width: 800px; /* Adjusted width for page layout */
        width: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        margin: auto;
        padding: 20px;
    }

    /* Chatbox styling */
    .chatbox {
        width: 100%; /* Adjusted width for full-width responsiveness */
        max-width: 600px; /* Optional max width for larger screens */
        border: 1px solid #ddd;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        background-color: white;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        margin: 20px 0; /* Space from other page elements */
    }

    .chatbox-header {
        padding: 15px;
        background-color: #6200ea;
        color: white;
        text-align: center;
        font-size: 20px;
    }

    .chatbox-body {
        flex: 1;
        padding: 20px;
        overflow-y: auto;
        max-height: 400px; /* Optional max height for scrollable content */
    }

    /* Message bubbles styling */
    .message {
        margin-bottom: 10px;
        padding: 10px;
        border-radius: 5px;
    }

    .user-message {
        background-color: #d1c4e9;
        align-self: flex-end;
        text-align: right;
        max-width: 70%; /* Adjusted for better fit in chatbox */
    }

    .bot-message {
        background-color: #e1bee7;
        align-self: flex-start;
        text-align: left;
        max-width: 70%; 
    }

    .chatbox-footer {
        display: flex;
        align-items: center;
        padding: 10px;
        border-top: 1px solid #ddd;
    }

    .search-input {
        flex: 1;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 16px;
        margin-right: 8px;
    }

    .voice-btn {
        font-size: 20px;
        color: #6200ea;
        cursor: pointer;
        padding: 8px;
        border: none;
        border-radius: 5px;
        background-color: #f0f0f0;
    }

    .voice-btn:focus {
        outline: none;
    }
</style>



</head>
<body>

<div class="container">
    <div class="chatbox">
        <div class="chatbox-header">Chatbox</div>
        <div class="chatbox-body" id="chatboxBody"></div>
        <div class="chatbox-footer">
            <input type="text" id="searchInput" class="search-input" placeholder="Type a message..." />
            <button id="voiceBtn" class="voice-btn">🎤</button>
        </div>
    </div>
</div>

<script>
        // Initialize Web Speech API components
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        const synth = window.speechSynthesis;
        let recognition;
    
        if (SpeechRecognition) {
            recognition = new SpeechRecognition();
            recognition.lang = 'en-US';
            recognition.continuous = false;
    
            // Function to speak out text
            const speak = (message) => {
                const utterance = new SpeechSynthesisUtterance(message);
                synth.speak(utterance);
            };
    
            // Function to add messages to the chatbox
            const addMessage = (text, isUser = false) => {
                const messageDiv = document.createElement('div');
                messageDiv.classList.add('message', isUser ? 'user-message' : 'bot-message');
                messageDiv.textContent = text;
                document.getElementById('chatboxBody').appendChild(messageDiv);
                document.getElementById('chatboxBody').scrollTop = document.getElementById('chatboxBody').scrollHeight;
            };
    
            // Voice command for project-specific commands
            document.getElementById('voiceBtn').addEventListener('click', () => {
                recognition.start();
            });
    
            recognition.onstart = () => {
                addMessage("Listening...", false);
            };
    
            recognition.onresult = (event) => {
                const transcript = event.results[0][0].transcript.toLowerCase();
                handleCommand(transcript, true);
            };
    
            // Handle recognition errors
            recognition.onerror = (event) => {
                addMessage("Error: " + event.error);
            };
    
            recognition.onend = () => {
                addMessage("Listening stopped.", false);
            };
    
            // Function to process voice or text commands
            const handleCommand = (command, isVoice = false) => {
                addMessage(command, true);
    
                if (command.includes("open google")) {
                    speak("Opening Google.");
                    window.location.href = "https://www.google.com";  // Opens Google in the same tab
                }else if (command.includes("open events")) {
                    speak("Opening Events");
                    window.location.href = "http://localhost/ANGANWADI/project/events.php";
                }else if (command.includes("open updates")) {
                    speak("Opening Updates");
                    window.location.href = "http://localhost/ANGANWADI/project/required_updates.htm";
                }else if (command.includes("open schemes")) {
                    speak("Opening Schemes");
                    window.location.href = "http://localhost/ANGANWADI/project/icds_schemes.html";
                }
                else if (command.includes("open reports")) {
                    speak("Opening Reports");
                    window.location.href = "http://localhost/ANGANWADI/project/submit_reports.php";
                }else if (command.includes("open attendance records")) {
                    speak("Opening Attendance Records");
                    window.location.href = "http://localhost/ANGANWADI/project/attendance_records.php";
                }
                else if (command.includes("open allocate resources")) {
                    speak("Opening Allocate Resources");
                    window.location.href = "http://localhost/ANGANWADI/project/allocate_resources.php";
                }
                else if (command.includes("open activity")) {
                    speak("Opening Add Activity");
                    window.location.href = "http://localhost/ANGANWADI/project/add_activity.php";
                }
                else if (command.includes("open resources")) {
                    speak("Opening Resources");
                    window.location.href = "http://localhost/ANGANWADI/project/resources.html";
                }else if (command.includes("open health records")) {
                    speak("Opening Health Records");
                    window.location.href = "http://localhost/ANGANWADI/project/update_child_health_records.php";
                }else if (command.includes("open immunization")) {
                    speak("Opening Immunization");
                    window.location.href = "http://localhost/ANGANWADI/project/schedule_immunizations.php";
                }else if (command.includes("open nutrition")) {
                    speak("Opening Nutrition");
                    window.location.href = "http://localhost/ANGANWADI/project/nutrition_status.php";
                }else if (command.includes("open biometric")) {
                    speak("Opening Biometric");
                    window.location.href = "http://localhost/ANGANWADI/project/biometric_scans.html";
                }else if (command.includes("open id")) {
                    speak("Opening ID");
                    window.location.href = "http://localhost/ANGANWADI/project/verify_ids.html";
                }else if (command.includes("open attendance")) {
                    speak("Opening Attendance Reports");
                    window.location.href = "http://localhost/ANGANWADI/project/attendance_reports.php";
                }else if (command.includes("open health data")) {
                    speak("Opening Health Data");
                    window.location.href = "http://localhost/ANGANWADI/project/add_health_data.htmlp";
                }else if (command.includes("open progress")) {
                    speak("Opening Scheme Progress");
                    window.location.href = "http://localhost/ANGANWADI/project/scheme_progress.php";
                }else if (command.includes("open updates")) {
                    speak("Opening Required Updates");
                    window.location.href = "http://localhost/ANGANWADI/project/required_updates.html";
                }else if (command.includes("open health alert")) {
                    speak("Opening Health Alert");
                    window.location.href = "http://localhost/ANGANWADI/project/schedule_health_alert.php";
                }else if (command.includes("open profile")) {
                    speak("Opening Updated Profile");
                    window.location.href = "http://localhost/ANGANWADI/project/update_profile.html";
                }else if (command.includes("help and support")) {
                    speak("Opening Help and Support");
                    window.location.href = "http://localhost/ANGANWADI/project/help_support.html";
                }



                 else {
                    const response = "I'm not sure how to respond to that.";
                    addMessage(response);
                    speak(response);
                }
            };
    
            // Handling text input in the search bar
            document.getElementById('searchInput').addEventListener('keypress', (event) => {
                if (event.key === 'Enter') {
                    const message = event.target.value.toLowerCase();
                    handleCommand(message);
                    event.target.value = ''; // Clear input
                }
            });
    
        } else {
            alert("Speech Recognition is not supported in this browser.");
        }
    </script>
</body>
</html>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Department of Women and Child Development</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            margin: 0;
        }
        .header {
            background-color: #4a148c;
            color: white;
            padding: 10px 20px;
            text-align: center;
            position: relative;
        }
        .header img {
            height: 50px;
            vertical-align: middle;
            width: 6%;
            border-radius: 50%;
        }
        .header-title {
            display: inline-block;
            margin-left: 10px;
            vertical-align: middle;
        }
        .logout {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: white;
            text-decoration: none;
            font-size: 16px;
            display: flex;
            align-items: center;
        }
        .logout img {
            height: 20px;
            margin-right: 5px;
        }
        .navbar {
            display: flex;
            justify-content: center;
            background-color: #333;
        }
        .navbar a, .dropbtn {
            display: block;
            color: #f2f2f2;
            text-align: center;
            padding: 14px 16px;
            text-decoration: none;
        }
        .navbar a:hover, .dropdown:hover .dropbtn {
            background-color: #ddd;
            color: black;
        }
        .navbar .dropdown {
            overflow: hidden;
        }
        .navbar .dropdown .dropbtn {
            font-size: 16px;
            border: none;
            outline: none;
            color: white;
            padding: 14px 16px;
            background-color: inherit;
            font-family: inherit;
            margin: 0;
        }
        .navbar .dropdown-content {
            display: none;
            position: absolute;
            background-color: #f9f9f9;
            min-width: 160px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1;
        }
        .navbar .dropdown-content a {
            float: none;
            color: black;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            text-align: left;
        }
        .navbar .dropdown-content a:hover {
            background-color: #ddd;
        }
        .navbar .dropdown:hover .dropdown-content {
            display: block;
        }
        .main-content {
            text-align: center;
            padding: 20px;
            flex: 1;
        }
        .main-content img {
            width: 100%;
            max-width: 450px;
        }
        .info-bar {
            background-color: #ff7043;
            color: white;
            padding: 5px;
            text-align: center;
            font-size: 14px;
        }
        .info-bar a {
            color: white;
            text-decoration: underline;
        }
        .footer {
            background-color: #333;
            color: white;
            padding: 10px 0;
            text-align: center;
            width: 100%;
            position: fixed;
            bottom: 0;
            left: 0;
        }
        .footer a {
            color: white;
            text-decoration: none;
            margin: 0 10px;
        }
        .footer a:hover {
            text-decoration: underline;
        }
        .gallery-container {
            width: 80%;
            margin: 20px auto;
            overflow: hidden;
            position: relative;
        }
        .gallery {
            display: flex;
            animation: scroll 40s linear infinite;
        }
        .gallery img {
            width: 600px;
            margin: 10px;
        }
        @keyframes scroll {
            0% { transform: translateX(100%); }
            100% { transform: translateX(-100%); }
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        .section-title {
            font-size: 24px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="../image1/Karnataka-Government.png" alt="Logo">
        <div class="header-title">
            <h1>Department of Child Development</h1>
            <h2>GOVERNMENT of KARNATAKA</h2>
        </div>
        <a href="logout.php" class="logout">
            <i class="fa-solid fa-right-from-bracket"></i>Log Out
        </a>
    </div>

    <div class="navbar">
        <div class="dropdown">
            <button class="dropbtn">ICDS Schemes 
                <i class="fa fa-caret-down"></i>
            </button>
            <div class="dropdown-content">
                <a href="icds_schemes.html">View Schemes</a>
                <a href="submit_reports.php">Submit Progress Reports</a>
                <a href="resources.html">Access Resources</a>
            </div>
        </div> 

        <div class="dropdown">
            <button class="dropbtn">Pre-school Management 
                <i class="fa fa-caret-down"></i>
            </button>
            <div class="dropdown-content">
                <a href="attendance_records.php">Update Attendance Records</a>
                <a href="add_activity.php">Plan Curriculum Activities</a>
                <a href="allocate_resources.php">Allocate Resources</a>
                <a href="Child.php">Add New Child</a>
            </div>
        </div>

        <div class="dropdown">
            <button class="dropbtn">Health Updates 
                <i class="fa fa-caret-down"></i>
            </button>
            <div class="dropdown-content">
                <a href="update_child_health_records.php">Update Child Health Records</a>
                <a href="schedule_immunizations.php">Schedule Immunizations</a>
                <a href="nutrition_status.php">Monitor Nutrition Status</a>
            </div>
        </div>

        <div class="dropdown">
            <button class="dropbtn">Authentication 
                <i class="fa fa-caret-down"></i>
            </button>
            <div class="dropdown-content">
                <a href="Voice-based Attendance.php">Voice-based Attendance</a>
                <a href="verify_ids.php">Verify IDs</a>
            </div>
        </div>

        <div class="dropdown">
            <button class="dropbtn">Reports and Analytics 
                <i class="fa fa-caret-down"></i>
            </button>
            <div class="dropdown-content">
                <a href="attendance_reports.php">Attendance Reports</a>
                <a href="add_health_data.html">Health Statistics</a>
                <a href="scheme_progress.php">Scheme Progress</a>
                <a href="download.php">Child Report</a>
            </div>
        </div>

        <div class="dropdown">
            <button class="dropbtn">Notifications 
                <i class="fa fa-caret-down"></i>
            </button>
            <div class="dropdown-content">
                <a href="events.php">Upcoming Events</a>
                <a href="required_updates.html">Required Updates</a>
                <a href="schedule_health_alert.php">Health Alerts</a>
            </div>
        </div>

        <div class="dropdown">
            <button class="dropbtn">User Settings and Help 
                <i class="fa fa-caret-down"></i>
            </button>
            <div class="dropdown-content">
                <a href="update_profile.html">Update Profile</a>
                <a href="help_support.html">Help and Support</a>
            </div>
        </div>
    </div>

    <div class="main-content">
        <div class="gallery-container">
            <div class="gallery">
                <img src="../image1/anganwadi 5.jpg" alt="Gallery Image 1">
                <img src="../image1/anganwadi 6.jpg" alt="Gallery Image 2">
                <img src="../image1/anganwadi 9.jpg" alt="Gallery Image 3">
                <img src="../image1/anganwadi 10.jpeg" alt="Gallery Image 4">
                <img src="../image1/anganwadi 11.jpg" alt="Gallery Image 5">
                <img src="../image1/Anganwadi_1.jpg" alt="Gallery Image 6">
                <img src="../image1/Anganwadi_2.jpeg" alt="Gallery Image 7">
                <img src="../image1/anganwadi_4.jpg" alt="Gallery Image 8">
            </div>
        </div>

       
    <div class="footer">
        © Department of  Child Development
    </div>
</body>
</html>
