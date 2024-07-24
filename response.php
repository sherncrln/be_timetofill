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
            
            $sql = "INSERT INTO `response` (`form_id`, `timestamp`, `class`, `username`, `answer`) VALUES (:form_id, :timestamp,  :class, :username, :answer);";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':form_id', $id, PDO::PARAM_INT);
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

    case "GET":
        if(isset($_GET['form_id']) && is_numeric($_GET['form_id'])) {
            $sql = "SELECT f.form_id, f.name_form, f.status_form, f.show_username, f.respondent, fd.question, fd.qtype FROM form f JOIN form_detail fd ON f.form_id = fd.form_id WHERE f.form_id = :id ;";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id', $_GET['form_id'], PDO::PARAM_INT);
            $stmt->execute();
            $head = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($stmt->execute()) {
                $sql_detail = "SELECT r.response_id, r.timestamp, r.class, u.name, r.answer, f.form_id FROM response r JOIN form_detail f ON r.form_id = f.form_id JOIN user u ON r.username = u.username WHERE f.form_id = :id ;";
                $stmt_detail = $conn->prepare($sql_detail);
                $stmt_detail->bindParam(':id', $_GET['form_id'], PDO::PARAM_INT);
                $stmt_detail->execute();
                $response = $stmt_detail->fetchAll(PDO::FETCH_ASSOC);
                $result = ['head' => $head, 'response' => $response];
            } else {
                $result = ['head' => 0, 'response' => 'Failed to process data.'];
            }
        }
        echo json_encode($result);
        break;
}
?>
