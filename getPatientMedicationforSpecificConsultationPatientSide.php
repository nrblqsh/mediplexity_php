<?php
header('Content-Type: application/json; charset=utf-8');

$hostname = "localhost";
$database = "mediplexity";
$username = "root";
$password = "";

$db = new PDO("mysql:host=$hostname;dbname=$database", $username, $password);
//initial response code
//response code will be changed if the request goes into any of the process
http_response_code(404);
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
        // Retrieve patientID and specialistID from the URL
        $consultationID = isset($_GET['consultationID']) ? $_GET['consultationID'] : null;
        // Check if both parameters are present
        if ($consultationID !== null) {
            $stmt = $db->prepare("SELECT medicine.MedGeneral, medicine.MedForm, medicine.dosage, medication.medInstruction, consultation.consultationDateTime
             FROM
              ((medication INNER JOIN consultation ON medication.consultationID = consultation.consultationID)
               INNER JOIN medicine ON medication.MedID = medicine.MedID)
                WHERE consultation.consultationID=:consultationID ");
            $stmt->bindParam(':consultationID', $consultationID, PDO::PARAM_INT);
            $stmt->execute();

            $response = $stmt->fetchAll(PDO::FETCH_ASSOC);
            http_response_code(200);
        } else {
            http_response_code(400); // Bad Request
            $response->error = "Both patientID and specialistID are required.";
        }
    } catch (Exception $ee) {
        http_response_code(500);
        $response->error = "Error occurred " . $ee->getMessage();
    }
}
echo json_encode($response);
exit();
?>