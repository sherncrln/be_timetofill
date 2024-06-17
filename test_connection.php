<?php
// Set headers to allow cross-origin requests and handle the methods and content types
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type");

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$response = [];

// Establish a connection to the MySQL database
$conn = new mysqli("localhost", "root", "", "timetofill"); // Change "root" and "your_password" accordingly

// Check if the connection was successful
if ($conn->connect_error) {
    $response["result"] = "Connection failed: " . $conn->connect_error;
    echo json_encode($response);
    exit; // Terminate the script
} else {
    // Output a JSON response indicating a successful connection
    $response["result"] = "Connected successfully";
    echo json_encode($response);
    $conn->close();
    exit; // Terminate the script
}
?>