<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");

// Tambahkan pengecekan untuk request method OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

include 'connection.php';
$objDb = new Connection;
$conn = $objDb->connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari request body
    $data = json_decode(file_get_contents('php://input'), true);

    // Pastikan $data adalah array
    if (is_array($data)) {
        foreach ($data as $item) {
            // Periksa setiap kunci yang dibutuhkan di dalam item
            $form_id = isset($item['form_id']) ? $item['form_id'] : 0;
            $dosen_id = isset($item['dosen_id']) ? $item['dosen_id'] : '';

            // Logika untuk memproses setiap item
            if (!is_null($form_id)) {
                // Cek apakah entri sudah ada dan statusnya "Published"
                $sqlCheck = "SELECT COUNT(*) FROM edom_result WHERE form_id = :form_id AND dosen_id = :dosen_id AND status_edom = 'Published';";
                $stmtCheck = $conn->prepare($sqlCheck);
                $stmtCheck->bindValue(':form_id', $form_id, PDO::PARAM_INT);
                $stmtCheck->bindValue(':dosen_id', $dosen_id, PDO::PARAM_STR);
                $stmtCheck->execute();
                $count = $stmtCheck->fetchColumn();

                if ($count > 0) {
                    // Jika ada, lakukan UPDATE
                    $sql = "UPDATE edom_result SET 
                            status_edom = 'On Process' 
                            WHERE form_id = :form_id AND dosen_id = :dosen_id AND status_edom = 'Published'";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindValue(':form_id', $form_id, PDO::PARAM_INT);
                    $stmt->bindValue(':dosen_id', $dosen_id, PDO::PARAM_STR);

                    if ($stmt->execute()) {
                        echo json_encode(["message" => "Status updated to 'Unpublished' for form_id: $form_id, dosen_id: $dosen_id"]);
                    } else {
                        http_response_code(500);
                        echo json_encode(["error" => "Failed to update status to 'Unpublished' for form_id: $form_id"]);
                    }
                } else {
                    http_response_code(404);
                    echo json_encode(["error" => "No entry found or not in 'Published' status for form_id: $form_id, dosen_id: $dosen_id"]);
                }
            } else {
                echo json_encode(["error" => "Invalid input for item"]);
            }
        }
    } else {
        echo json_encode(["error" => "Invalid input"]);
    }

    $conn = null;
} else {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
}
?>
