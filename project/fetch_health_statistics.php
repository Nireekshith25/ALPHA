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

// Fetch growth metrics
$growth_metrics = $conn->query("SELECT student_id, measurement_date, weight, height, growth_percentile FROM growth_metrics")->fetch_all(MYSQLI_ASSOC);

// Fetch immunization records
$immunization_records = $conn->query("SELECT student_id, vaccine_name, vaccination_date FROM immunization_records")->fetch_all(MYSQLI_ASSOC);

// Fetch health checkups
$health_checkups = $conn->query("SELECT student_id, checkup_date, findings, referrals FROM health_checkups")->fetch_all(MYSQLI_ASSOC);

// Fetch nutrition status
$nutrition_status = $conn->query("SELECT student_id, assessment_date, dietary_intake, supplements_provided, anemia_prevalence FROM nutrition_status")->fetch_all(MYSQLI_ASSOC);

// Fetch disease incidence
$disease_incidence = $conn->query("SELECT student_id, disease_name, occurrence_date, preventive_measures FROM disease_incidence")->fetch_all(MYSQLI_ASSOC);

// Fetch maternal health
$maternal_health = $conn->query("SELECT mother_id, health_status, antenatal_visits, postnatal_visits, nutrition_education FROM maternal_health")->fetch_all(MYSQLI_ASSOC);

// Fetch sanitation and hygiene
$sanitation_hygiene = $conn->query("SELECT center_id, clean_drinking_water, sanitation_facilities, hygiene_education FROM sanitation_hygiene")->fetch_all(MYSQLI_ASSOC);

$conn->close();

$response = [
    'growth_metrics' => $growth_metrics,
    'immunization_records' => $immunization_records,
    'health_checkups' => $health_checkups,
    'nutrition_status' => $nutrition_status,
    'disease_incidence' => $disease_incidence,
    'maternal_health' => $maternal_health,
    'sanitation_hygiene' => $sanitation_hygiene
];

echo json_encode($response);
?>
