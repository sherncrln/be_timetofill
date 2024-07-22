<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

include 'connection.php';
$objDb = new Connection;
$conn = $objDb->connect();

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case "POST":
        $res = json_decode(file_get_contents('php://input'), true);
        if (isset($res)) {
            $id = $res[0];
            $timestamp = date('Y-m-d H:i:s', strtotime('+7 hour'));
            $answer = json_encode($res[3]);
            
            $sql = "INSERT INTO `response_detail` (`response_id`, `timestamp`, `class`, `username`, `answer`) VALUES (:response_id, :timestamp,  :class, :username, :answer)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':response_id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':class', $res[1]);
            $stmt->bindParam(':username', $res[2]);
            $stmt->bindParam(':answer', $answer);
            $stmt->bindParam(':timestamp', $timestamp);

            if ($stmt->execute()) {
                $response = ['status' => 1, 'message' => 'Data process successfully.'];
            } else {
                $response = ['status' => 0, 'message' => 'Failed to process data.'];
            }
            echo json_encode($response);
        } else {
                $response = ["error" => "Data is not valid"];
                echo json_encode($response);
        }
        break;
}
?>
