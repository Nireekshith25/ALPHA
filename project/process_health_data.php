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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Handle Growth Metrics
    if (isset($_POST['submit_growth_metrics'])) {
        $student_id = $_POST['student_id'];
        $measurement_date = $_POST['measurement_date'];
        $weight = $_POST['weight'];
        $height = $_POST['height'];
        $growth_percentile = $_POST['growth_percentile'];

        $stmt = $conn->prepare("INSERT INTO growth_metrics (student_id, measurement_date, weight, height, growth_percentile) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $student_id, $measurement_date, $weight, $height, $growth_percentile);
        if ($stmt->execute()) {
            echo "Growth metrics added successfully.";
        } else {
            echo "Error adding growth metrics.";
        }
        $stmt->close();
    }

    // Handle Immunization Records
    if (isset($_POST['submit_immunization_records'])) {
        $student_id = $_POST['student_id'];
        $vaccine_name = $_POST['vaccine_name'];
        $vaccination_date = $_POST['vaccination_date'];

        $stmt = $conn->prepare("INSERT INTO immunization_records (student_id, vaccine_name, vaccination_date) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $student_id, $vaccine_name, $vaccination_date);
        if ($stmt->execute()) {
            echo "Immunization record added successfully.";
        } else {
            echo "Error adding immunization record.";
        }
        $stmt->close();
    }

    // Handle Health Checkups
    if (isset($_POST['submit_health_checkups'])) {
        $student_id = $_POST['student_id'];
        $checkup_date = $_POST['checkup_date'];
        $findings = $_POST['findings'];
        $referrals = $_POST['referrals'];

        $stmt = $conn->prepare("INSERT INTO health_checkups (student_id, checkup_date, findings, referrals) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $student_id, $checkup_date, $findings, $referrals);
        if ($stmt->execute()) {
            echo "Health checkup added successfully.";
        } else {
            echo "Error adding health checkup.";
        }
        $stmt->close();
    }

    // Handle Nutritional Status
    if (isset($_POST['submit_nutrition_status'])) {
        $student_id = $_POST['student_id'];
        $assessment_date = $_POST['assessment_date'];
        $dietary_intake = $_POST['dietary_intake'];
        $supplements_provided = $_POST['supplements_provided'];
        $anemia_prevalence = $_POST['anemia_prevalence'];

        $stmt = $conn->prepare("INSERT INTO nutrition_status (student_id, assessment_date, dietary_intake, supplements_provided, anemia_prevalence) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $student_id, $assessment_date, $dietary_intake, $supplements_provided, $anemia_prevalence);
        if ($stmt->execute()) {
            echo "Nutritional status added successfully.";
        } else {
            echo "Error adding nutritional status.";
        }
        $stmt->close();
    }

    // Handle Disease Incidence
    if (isset($_POST['submit_disease_incidence'])) {
        $student_id = $_POST['student_id'];
        $disease_name = $_POST['disease_name'];
        $occurrence_date = $_POST['occurrence_date'];
        $preventive_measures = $_POST['preventive_measures'];

        $stmt = $conn->prepare("INSERT INTO disease_incidence (student_id, disease_name, occurrence_date, preventive_measures) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $student_id, $disease_name, $occurrence_date, $preventive_measures);
        if ($stmt->execute()) {
            echo "Disease incidence added successfully.";
        } else {
            echo "Error adding disease incidence.";
        }
        $stmt->close();
    }

    // Handle Maternal Health
    if (isset($_POST['submit_maternal_health'])) {
        $mother_id = $_POST['mother_id'];
        $health_status = $_POST['health_status'];
        $antenatal_visits = $_POST['antenatal_visits'];
        $postnatal_visits = $_POST['postnatal_visits'];
        $nutrition_education = $_POST['nutrition_education'];

        $stmt = $conn->prepare("INSERT INTO maternal_health (mother_id, health_status, antenatal_visits, postnatal_visits, nutrition_education) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $mother_id, $health_status, $antenatal_visits, $postnatal_visits, $nutrition_education);
        if ($stmt->execute()) {
            echo "Maternal health added successfully.";
        } else {
            echo "Error adding maternal health.";
        }
        $stmt->close();
    }

    // Handle Sanitation and Hygiene
    if (isset($_POST['submit_sanitation_hygiene'])) {
        $center_id = $_POST['center_id'];
        $clean_drinking_water = $_POST['clean_drinking_water'];
        $sanitation_facilities = $_POST['sanitation_facilities'];
        $hygiene_education = $_POST['hygiene_education'];

        $stmt = $conn->prepare("INSERT INTO sanitation_hygiene (center_id, clean_drinking_water, sanitation_facilities, hygiene_education) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $center_id, $clean_drinking_water, $sanitation_facilities, $hygiene_education);
        if ($stmt->execute()) {
            echo "Sanitation and hygiene added successfully.";
        } else {
            echo "Error adding sanitation and hygiene.";
        }
        $stmt->close();
    }

    // Handle Health Alerts
    if (isset($_POST['submit_health_alert'])) {
        $title = $_POST['title'];
        $description = $_POST['description'];
        $alert_date = $_POST['alert_date'];

        $stmt = $conn->prepare("INSERT INTO health_alerts (title, description, alert_date) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $title, $description, $alert_date);
        if ($stmt->execute()) {
            echo "Health alert added successfully.";
        } else {
            echo "Error adding health alert.";
        }
        $stmt->close();
    }
}

$conn->close();
?>
