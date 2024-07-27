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
                $skippedClasses = [];
                $processedCount = 0;

                foreach ($sheetData as $key => $row) {
                    if ($key == 1) continue; // Lewati baris header

                    $class = $row['A'];
                    $category = $row['B'];
                    $semester = $row['C'];
                    $valid_from = $row['D'];
                    $valid_to = $row['E'];

                    // Cek apakah category adalah "Mahasiswa"
                    if ($category == "Mahasiswa") {
                        $variable_1 = $row['F'];
                        $variable_2 = $row['G'];
                        $variable_3 = $row['H'];
                        $variable_4 = $row['I'];
                        $variable_5 = $row['J'];
                        $variable_6 = $row['K'];

                        // Cek apakah variable 1 sampai 6 adalah 'username' yang terdaftar pada tabel user dengan category 'Dosen'
                        $variables = [$variable_1, $variable_2, $variable_3, $variable_4, $variable_5, $variable_6];
                        $validVariables = true;

                        foreach ($variables as $variable) {
                            $stmtCheckUser = $conn->prepare("SELECT COUNT(*) FROM user WHERE username = ? AND category = 'Dosen'");
                            $stmtCheckUser->execute([$variable]);
                            $userExists = $stmtCheckUser->fetchColumn();

                            if ($userExists == 0) {
                                $validVariables = false;
                                break;
                            }
                        }

                        if (!$validVariables) {
                            $skippedClasses[] = $class;
                            continue;
                        }
                    } else {
                        $variable_1 = null;
                        $variable_2 = null;
                        $variable_3 = null;
                        $variable_4 = null;
                        $variable_5 = null;
                        $variable_6 = null;
                    }

                    // Cek apakah class sudah ada
                    $stmtCheck = $conn->prepare("SELECT COUNT(*) FROM class WHERE class = ?");
                    $stmtCheck->execute([$class]);
                    $classExists = $stmtCheck->fetchColumn();

                    if ($mode == 'add' && $classExists > 0) {
                        // Jika mode 'add' dan class sudah ada, tambahkan ke daftar yang dilewati
                        $skippedClasses[] = $class;
                        continue;
                    }

                    if ($mode == 'add') {
                        $stmt = $conn->prepare("INSERT INTO class (class, category, semester, valid_from, valid_to, variable_1, variable_2, variable_3, variable_4, variable_5, variable_6) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$class, $category, $semester, $valid_from, $valid_to, $variable_1, $variable_2, $variable_3, $variable_4, $variable_5, $variable_6]);
                    } else if ($mode == 'edit') {
                        $stmt = $conn->prepare("UPDATE class SET category=?, semester=?, valid_from=?, valid_to=?, variable_1=?, variable_2=?, variable_3=?, variable_4=?, variable_5=?, variable_6=? WHERE class=?");
                        $stmt->execute([$category, $semester, $valid_from, $valid_to, $variable_1, $variable_2, $variable_3, $variable_4, $variable_5, $variable_6, $class]);
                    }

                    $processedCount++;
                }

                $conn->commit(); // Commit transaksi

                if ($processedCount > 0) {
                    $response['status'] = 1;
                    $response['message'] = 'File uploaded and processed successfully';
                    if (count($skippedClasses) > 0) {
                        $response['message'] .= '. Some classes were skipped because they already exist or variable validation failed: ' . implode(', ', $skippedClasses);
                    }
                } else {
                    $response['message'] = 'No classes were processed. All provided classes already exist or variable validation failed.';
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
