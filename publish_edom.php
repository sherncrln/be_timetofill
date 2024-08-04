<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

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
            $status_edom = isset($item['status_edom']) ? $item['status_edom'] : '';
            $last_published = isset($item['last_published']) ? $item['last_published'] : '';
            
            // Logika untuk memproses setiap item
            if (!is_null($form_id) && !is_null($status_edom) && !is_null($last_published)) {
                // Nilai default untuk kolom lain jika tidak disediakan
                $total = isset($item['total']) ? $item['total'] : '';
                $class = isset($item['class']) ? $item['class'] : '';
                $result = isset($item['result']) ? $item['result'] : '';
                $rank = isset($item['rank']) ? $item['rank'] : '';
                $predikat = isset($item['predikat']) ? $item['predikat'] : '';

                // Cek apakah entri sudah ada
                $sqlCheck = "SELECT COUNT(*) FROM edom_result WHERE form_id = :form_id AND dosen_id = :dosen_id ;";
                $stmtCheck = $conn->prepare($sqlCheck);
                $stmtCheck->bindValue(':form_id', $form_id, PDO::PARAM_INT);
                $stmtCheck->bindValue(':dosen_id', $dosen_id, PDO::PARAM_INT);
                $stmtCheck->execute();
                $count = $stmtCheck->fetchColumn();

                if ($count > 0) {
                    // Jika ada, lakukan UPDATE
                    $sql = "UPDATE edom_result SET 
                            status_edom = :status_edom, 
                            last_published = :last_published, 
                            total = :total, 
                            class = :class, 
                            result = :result, 
                            rank = :rank, 
                            predikat = :predikat 
                            WHERE form_id = :form_id AND dosen_id = :dosen_id ";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindValue(':dosen_id', $dosen_id, PDO::PARAM_STR);
                    $stmt->bindValue(':status_edom', $status_edom, PDO::PARAM_STR);
                    $stmt->bindValue(':last_published', $last_published, PDO::PARAM_STR);
                    $stmt->bindValue(':total', $total, PDO::PARAM_STR);
                    $stmt->bindValue(':class', $class, PDO::PARAM_INT);
                    $stmt->bindValue(':result', $result, PDO::PARAM_STR);
                    $stmt->bindValue(':rank', $rank, PDO::PARAM_INT);
                    $stmt->bindValue(':predikat', $predikat, PDO::PARAM_STR);
                    $stmt->bindValue(':form_id', $form_id, PDO::PARAM_INT);
                } else {
                    // Jika tidak ada, lakukan INSERT
                    $sql = "INSERT INTO edom_result 
                            (form_id, dosen_id, status_edom, last_published, total, class, result, rank, predikat) 
                            VALUES (:form_id, :dosen_id, :status_edom, :last_published, :total, :class, :result, :rank, :predikat)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindValue(':form_id', $form_id, PDO::PARAM_INT);
                    $stmt->bindValue(':dosen_id', $dosen_id, PDO::PARAM_STR);
                    $stmt->bindValue(':status_edom', $status_edom, PDO::PARAM_STR);
                    $stmt->bindValue(':last_published', $last_published, PDO::PARAM_STR);
                    $stmt->bindValue(':total', $total, PDO::PARAM_STR);
                    $stmt->bindValue(':class', $class, PDO::PARAM_INT);
                    $stmt->bindValue(':result', $result, PDO::PARAM_STR);
                    $stmt->bindValue(':rank', $rank, PDO::PARAM_INT);
                    $stmt->bindValue(':predikat', $predikat, PDO::PARAM_STR);
                }

                if ($stmt->execute()) {
                    echo json_encode(["message" => "Publish status updated successfully for form_id: $form_id, $dosen_id"]);
                } else {
                    http_response_code(500);
                    echo json_encode(["error" => "Failed to update publish status for form_id: $form_id"]);
                }
            } else {
                echo json_encode(["error" => "Invalid input for item"]);
            }
        }
    } else {
        echo json_encode(["error" => "Invalid input"]);
    }

    $conn = null;
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Ambil form_id dari query parameter
    
    if (isset($_GET['form_id'])) {
        $form_id = isset($_GET['form_id']) ? $_GET['form_id'] : 0;
        $sql = "SELECT status_edom, last_published AS publish_date FROM edom_result WHERE form_id = :form_id ";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':form_id', $form_id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            echo json_encode($result);
        } else {
            http_response_code(404);
            echo json_encode(["error" => "No data found for form_idtes: $form_id"]);
        }
    } elseif(isset($_GET['dosen_id'])){
        $dosen_id = isset($_GET['dosen_id']) ? $_GET['dosen_id']: 0;
        $sql = "SELECT e.dosen_id, e.status_edom, e.last_published, e.total, e.class, e.result, e.rank, e.predikat, f.name_form FROM edom_result e JOIN form f ON e.form_id = f.form_id WHERE e.dosen_id = :dosen_id AND e.status_edom = 'Published';";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':dosen_id', $dosen_id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($result) {
            echo json_encode($result);
        } else {
            echo json_encode(["error" => "No data found for dosen_id: $dosen_id"]);
        }
    } else {
        echo json_encode(["error" => "Invalid Edom Data"]);
    }

    $conn = null;
} else {
    echo json_encode(["error" => "Method not allowed"]);
}
?>
