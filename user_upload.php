<?php
require 'vendor/autoload.php'; // Composer autoload
include 'connection.php'; // File koneksi database

use PhpOffice\PhpSpreadsheet\IOFactory;

$response = ['status' => 0, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_FILES['file']) && isset($_POST['mode'])) {
        $file = $_FILES['file']['tmp_name'];
        $mode = $_POST['mode'];
        $spreadsheet = IOFactory::load($file);
        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

        $connection = new Connection();
        $conn = $connection->connect(); // Membuka koneksi ke database

        if ($conn) {
            // Mulai transaksi
            $conn->beginTransaction();
            try {
                $skippedUsers = [];
                $processedCount = 0;

                foreach ($sheetData as $key => $row) {
                    if ($key == 1) continue; // Lewati baris header

                    $username = $row['A'];
                    $name = $row['B'];
                    $category = $row['C'];
                    $class = $row['D'];
                    $status_user = $row['E'];
                    $created_at = date('Y-m-d H:i:s', strtotime('+7 hour'));

                    // Cek apakah class ada
                    $stmtCheckClass = $conn->prepare("SELECT COUNT(*) FROM class WHERE class = ?");
                    $stmtCheckClass->execute([$class]);
                    $classExists = $stmtCheckClass->fetchColumn();

                    if ($classExists == 0) {
                        // Jika class tidak ada, tambahkan ke daftar yang dilewati
                        $skippedUsers[] = $username;
                        continue;
                    }

                    // Cek apakah user sudah ada
                    $stmtCheck = $conn->prepare("SELECT COUNT(*) FROM user WHERE username = ?");
                    $stmtCheck->execute([$username]);
                    $userExists = $stmtCheck->fetchColumn();

                    if ($mode == 'add' && $userExists > 0) {
                        // Jika mode 'add' dan user sudah ada, tambahkan ke daftar yang dilewati
                        $skippedUsers[] = $username;
                        continue;
                    }

                    if ($mode == 'add') {
                        $stmt = $conn->prepare("INSERT INTO user (username, name, category, class, status_user, created_at) VALUES (?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$username, $name, $category, $class, $status_user, $created_at]);
                    } else if ($mode == 'edit') {
                        $stmt = $conn->prepare("UPDATE user SET name=?, category=?, class=?, status_user=?, created_at=? WHERE username=?");
                        $stmt->execute([$name, $category, $class, $status_user, $created_at, $username]);
                    }

                    $processedCount++;
                }

                $conn->commit(); // Commit transaksi

                if ($processedCount > 0) {
                    $response['status'] = 1;
                    $response['message'] = 'File uploaded and processed successfully';
                    if (count($skippedUsers) > 0) {
                        $response['message'] .= '. Some users were skipped because they already exist or class does not exist: ' . implode(', ', $skippedUsers);
                    }
                } else {
                    $response['message'] = 'No users were processed. All provided users already exist or class does not exist.';
                }
            } catch (Exception $e) {
                $conn->rollBack(); // Rollback transaksi jika ada error
                $response['message'] = 'Failed to upload and process file: ' . $e->getMessage();
            }

            $connection->closecon($conn); // Menutup koneksi ke database
        } else {
            $response['message'] = 'Failed to connect to the database';
        }
    } else {
        $response['message'] = 'Invalid request';
    }
} else {
    $response['message'] = 'Invalid request method';
}

echo json_encode($response);
?>
