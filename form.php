<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: *");


include 'connection.php';
$objDb = new Connection;
$conn = $objDb->connect();

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case "GET":
        $sql = "SELECT * FROM form";
        // $path = explode('/', $_SERVER['REQUEST_URI']);
         
        if(isset($_GET['form_id']) && is_numeric($_GET['form_id'])) {
            $sql .= " WHERE form_id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id', $_GET['form_id'], PDO::PARAM_INT);
            $stmt->execute();
            $form = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $form = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        echo json_encode($form);
        break;
}