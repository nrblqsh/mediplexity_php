<?php
header('Content-Type: application/json; charset=utf-8');

$hostname = "localhost";
$database = "mediplexity";
$username = "root";
$password = "";

$db = new PDO("mysql:host=$hostname;dbname=$database", $username, $password);
http_response_code(404); // Set initial response code

$response = new stdClass();
$jsonbody = json_decode(file_get_contents('php://input'));

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $stmt = $db->prepare("INSERT INTO medication (`medicationID`, `consultationID`, `MedID`)
                            VALUES (:medicationID, :consultationID, :MedID)");
        $stmt->execute(array(
            ':medicationID' => $jsonbody->medicationID,
            ':consultationID' => $jsonbody->consultationID,
            ':MedID' => $jsonbody->MedID
        ));
        http_response_code(200);
    } catch (Exception $ee) {
        http_response_code(500);
        $response->error = "Error occurred " . $ee->getMessage();
    }
} else if ($_SERVER["REQUEST_METHOD"] == "GET") {
    try {
        $patientID = isset($_GET['patientID']) ? $_GET['patientID'] : null;

        if ($patientID !== null) {
            $stmt = $db->prepare("SELECT consultation.consultationID, consultation.consultationDateTime, specialist.specialistName,
             consultation.consultationSymptom, consultation.consultationTreatment, consultation.feesConsultation, consultation.feesConsultationStatus
              FROM (consultation INNER JOIN specialist ON consultation.specialistID = specialist.specialistID) 
              WHERE consultation.patientID = :patientID AND consultation.consultationStatus = 'Done'");
            $stmt->bindParam(':patientID', $patientID, PDO::PARAM_INT);

            // Check if execution is successful before fetching results
            if ($stmt->execute()) {
                $historyConsultations = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $response->data = $historyConsultations;
                $response->success = true;
                http_response_code(200); // Set success response code
            } else {
                $response->success = false;
                $response->error = "Error retrieving history consultations: " . $stmt->errorInfo()[2];
            }
        } else {
            $response->error = "PatientID is missing in the request.";
            $response->success = false;
        }
    } catch (Exception $ee) {
        http_response_code(500);
        $response->error = "Error occurred " . $ee->getMessage();
    }
}

echo json_encode($response);
exit();
?>
