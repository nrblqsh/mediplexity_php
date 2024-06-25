<?php
header('Content-Type: application/json; charset=utf-8');

$db = mysqli_connect('localhost', 'root', '', 'mediplexity');

if (!$db) {
    echo "Database connection failed";
}

$phone = $_POST['phone'];
$password = $_POST['password'];

$sql_patient = "SELECT * FROM patient WHERE phone = '$phone' AND password = '$password'";
$result_patient = mysqli_query($db, $sql_patient);
$count = mysqli_num_rows($result_patient);

$sql_specialist = "SELECT * FROM specialist WHERE phone = '$phone' AND password = '$password'";
$result_specialist = mysqli_query($db, $sql_specialist);
$count_specialist = mysqli_num_rows($result_specialist);

if ($count == 1) {
    // Fetch patient details including patientName
    $row = mysqli_fetch_assoc($result_patient);
    $patientName = $row['patientName'];
    $patientID = $row['patientID'];

    // Create an associative array to send as JSON response for patient
    $response = array(
        "status" => "success patients",
        "patientName" => $patientName,
        "patientID" => $patientID
    );
    echo json_encode($response);
} else if ($count_specialist == 1) {
    // Fetch specialist details including specialistName
    $row = mysqli_fetch_assoc($result_specialist);
    $specialistName = $row['specialistName'];
    $specialistID = $row['specialistID'];

    // Update the logStatus to 'online' when a specialist logs in
    $update_status_sql = "UPDATE specialist SET logStatus = 'ONLINE' WHERE specialistID = $specialistID";
    mysqli_query($db, $update_status_sql);

    // Create an associative array to send as JSON response for specialist
    $response = array(
        "status" => "success specialist",
        "specialistName" => $specialistName,
        "specialistID" => $specialistID,
	"logStatus" => "ONLINE"
    );
    echo json_encode($response);
} else {
    echo json_encode("error");
}
?>