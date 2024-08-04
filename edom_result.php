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
        // $path = explode('/', $_SERVER['REQUEST_URI']);
         
        if(isset($_GET['dosen_id']) && is_numeric($_GET['dosen_id'])) {
            $sql = "SELECT * FROM edom_result WHERE dosen_id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id', $_GET['dosen_id'], PDO::PARAM_INT);
            $stmt->execute();
            $edom = $stmt->fetch(PDO::FETCH_ASSOC);
            $edom = $_GET['dosen_id'];
        }else{
        }
        echo json_encode($edom);
        break;
}