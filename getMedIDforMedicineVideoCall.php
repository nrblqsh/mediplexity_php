<?php
header('Content-Type: application/json; charset=utf-8');

$hostname = "localhost";
$database = "mediplexity";
$username = "root";
$password = "";

try {
    $db = new PDO("mysql:host=$hostname;dbname=$database", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    http_response_code(404); // Set initial response code

    $response = new stdClass();
    $jsonbody = json_decode(file_get_contents('php://input'));

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $MedGeneral = $jsonbody->MedGeneral;
        $MedForm = $jsonbody->MedForm;
        $dosage = $jsonbody->dosage;

        try {
            // Check if the medication already exists
            $stmt = $db->prepare("SELECT MedID FROM medicine WHERE MedGeneral = :MedGeneral AND MedForm = :MedForm AND dosage = :dosage");
            $stmt->execute(array(
                ':MedGeneral' => $MedGeneral,
                ':MedForm' => $MedForm,
                ':dosage' => $dosage
            ));
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                // Medication exists, get the MedID
                $medID = $result['MedID'];
                $response->status = 'success';
                $response->medID = $medID;
                http_response_code(200);
            } else {
                // Medication does not exist, insert it
                $stmt = $db->prepare("INSERT INTO medicine (MedGeneral, MedForm, dosage) VALUES (:MedGeneral, :MedForm, :dosage)");
                $stmt->execute(array(
                    ':MedGeneral' => $MedGeneral,
                    ':MedForm' => $MedForm,
                    ':dosage' => $dosage
                ));
                // Get the MedID of the newly inserted record
                $medID = $db->lastInsertId();
                $response->status = 'success';
                $response->medID = $medID;
                http_response_code(201); // Resource created
            }
        } catch (Exception $e) {
            http_response_code(500);
            $response->status = 'error';
            $response->error = "Error occurred: " . $e->getMessage();
        }
    } else {
        http_response_code(405); // Method not allowed
        $response->status = 'error';
        $response->error = "Invalid request method.";
    }

    echo json_encode($response);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(array('status' => 'error', 'error' => "Database connection failed: " . $e->getMessage()));
}
