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
        if (isset($jsonbody->consultationID) && isset($jsonbody->medications)) {
            $consultationID = $jsonbody->consultationID;
            $medications = $jsonbody->medications;

            try {
                foreach ($medications as $medication) {
                    $MedID = $medication->MedID;
                    $medInstruction = $medication->medInstruction;

                    // Insert medication details
                    $stmt = $db->prepare("INSERT INTO medication(consultationID, MedID, medInstruction) VALUES (:consultationID, :MedID, :medInstruction)");
                    $stmt->execute(array(
                        ':consultationID' => $consultationID,
                        ':MedID' => $MedID,
                        ':medInstruction' => $medInstruction
                    ));
                }

                http_response_code(200); // OK
                $response->message = "Medications inserted successfully.";
            } catch (Exception $e) {
                http_response_code(500);
                $response->error = "Error occurred: " . $e->getMessage();
            }
        } else {
            http_response_code(400); // Bad request
            $response->error = "Invalid input.";
        }
    } else {
        http_response_code(405); // Method not allowed
        $response->error = "Invalid request method.";
    }

    echo json_encode($response);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(array('error' => "Database connection failed: " . $e->getMessage()));
}
