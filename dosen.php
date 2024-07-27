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
        $sql = "SELECT * FROM user WHERE category = 'Dosen' AND status_user = 'Active';";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $dosen = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($dosen);
        break;
}