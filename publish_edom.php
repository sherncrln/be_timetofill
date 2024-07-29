<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: *");

include 'connection.php';
$objDb = new Connection;
$conn = $objDb->connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari request body
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['form_id']) || !isset($data['status_edom']) || !isset($data['last_published'])) {
        http_response_code(400);
        echo json_encode(["error" => "Invalid input"]);
        exit;
    }

    $form_id = $data['form_id'];
    $dosen_id = $data['dosen_id'];
    $status_edom = $data['status_edom'];
    $last_published = $data['last_published'];

    // Nilai default untuk kolom lain jika tidak disediakan
    $total = isset($data['total']) ? $data['total'] : 0;
    $class = isset($data['class']) ? $data['class'] : 0;
    $result = isset($data['result']) ? $data['result'] : 0;
    $rank = isset($data['rank']) ? $data['rank'] : '';
    $predikat = isset($data['predikat']) ? $data['predikat'] : '';

    // Cek apakah entri sudah ada
    $sqlCheck = "SELECT COUNT(*) FROM edom_result WHERE form_id = ?";
    $stmtCheck = $conn->prepare($sqlCheck);
    $stmtCheck->bind_param("i", $form_id);
    $stmtCheck->execute();
    $stmtCheck->bind_result($count);
    $stmtCheck->fetch();
    $stmtCheck->close();

    if ($count > 0) {
        // Jika ada, lakukan UPDATE
        $sql = "UPDATE edom_result SET 
                dosen_id = ?
                status_edom = ?, 
                last_published = ?, 
                total = ?, 
                class = ?, 
                result = ?, 
                rank = ?, 
                predikat = ? 
                WHERE form_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssiiiiiiissi", 
            $dosen_id,
            $status_edom, 
            $last_published, 
            $total, 
            $class, 
            $result, 
            $rank, 
            $predikat, 
            $form_id);
    } else {
        // Jika tidak ada, lakukan INSERT
        $sql = "INSERT INTO edom_result 
                (form_id, dosen_id, status_edom, last_published, total, class, result, rank, predikat) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssiiisss", 
            $form_id, 
            $dosen_id,
            $status_edom, 
            $last_published, 
            $total, 
            $class, 
            $result, 
            $rank, 
            $predikat);
    }

    if ($stmt->execute()) {
        echo json_encode(["message" => "Publish status updated successfully"]);
    } else {
        http_response_code(500);
        echo json_encode(["error" => "Failed to update publish status"]);
    }

    $stmt->close();
    $conn->close();
} else {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
}
?>
