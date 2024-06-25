<?php
header('Content-Type: application/json; charset=utf-8');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mediplexity";

// Create Connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check Connection
if ($conn->connect_error) {
    die("Connection Failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    try {
        // Get the specialistID from the GET parameters
        $specialistID = isset($_GET['specialistID']) ? $_GET['specialistID'] : null;

        if ($specialistID !== null) {
            $stmt = $conn->prepare("SELECT consultation.*, patient.patientID, patient.icNumber, patient.patientName, patient.phone, patient.gender, patient.birthDate
                                    FROM consultation
                                    INNER JOIN patient ON consultation.patientID = patient.patientID
                                    WHERE consultation.specialistID = ? AND consultation.consultationStatus = 'Accepted' OR 'Done'
                                    GROUP BY consultation.patientID");
            $stmt->bind_param("i", $specialistID);
            $stmt->execute();
            $result = $stmt->get_result();
            $response = $result->fetch_all(MYSQLI_ASSOC);
            http_response_code(200);
            echo json_encode($response);
        } else {
            // Return an error if specialistID is not provided
            http_response_code(400);
            echo json_encode(array('error' => 'Specialist ID is required.'));
        }
    } catch (Exception $ee) {
        http_response_code(500);
        $response = array('error' => 'Error occurred ' . $ee->getMessage());
        echo json_encode($response);
    }
}
?>
