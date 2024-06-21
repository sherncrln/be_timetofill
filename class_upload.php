<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: *");


include 'connection_upload.php';
$objDb = new Connection;
$conn = $objDb->connect();

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case "POST":

        $mode = $_POST['mode'];
        $file = $_FILES['file'];

        if ($file['error'] === 0) {
            $fileTmpPath = $file['tmp_name'];
            $fileName = $file['name'];
            $fileSize = $file['size'];
            $fileType = $file['type'];

            $allowedFileTypes = array('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            if (in_array($fileType, $allowedFileTypes)) {
                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                $newFileName = uniqid() . '.' . $fileExtension;
                $uploadFileDir = './uploads/';
                $destPath = $uploadFileDir . $newFileName;

                if (move_uploaded_file($fileTmpPath, $destPath)) {
                    // Process the Excel file
                    $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
                    $spreadsheet = $reader->load($destPath);
                    $data = $spreadsheet->getActiveSheet()->toArray();

                    // Insert or update data in the database
                    if ($mode === 'add') {
                        foreach ($data as $row) {
                            $sql = "INSERT INTO class (category, class, semester, valid_from, valid_to, variable_1, variable_2, variable_3, variable_4, variable_5, variable_6) VALUES (:category, :class, :semester, :valid_from, :valid_to, :variable_1, :variable_2, :variable_3, :variable_4, :variable_5, :variable_6)";
                            $stmt = $conn->prepare($sql);
                            $stmt->bindParam(':category', $row[0]);
                            $stmt->bindParam(':class', $row[1]);
                            $stmt->bindParam(':semester', $row[2], PDO::PARAM_INT);
                            $stmt->bindParam(':valid_from', $row[3]);
                            $stmt->bindParam(':valid_to', $row[4]);
                            $stmt->bindParam(':variable_1', $row[5]);
                            $stmt->bindParam(':variable_2', $row[6]);
                            $stmt->bindParam(':variable_3', $row[7]);
                            $stmt->bindParam(':variable_4', $row[8]);
                            $stmt->bindParam(':variable_5', $row[9]);
                            $stmt->bindParam(':variable_6', $row[10]);
                            $stmt->execute();
                        }
                    } elseif ($mode === 'edit') {
                        // Update data
                        foreach ($data as $row) {
                            $sql = "UPDATE class SET category = :category, class = :class, semester = :semester, valid_from = :valid_from, valid_to = :valid_to, variable_1 = :variable_1, variable_2 = :variable_2, variable_3 = :variable_3, variable_4 = :variable_4, variable_5 = :variable_5, variable_6 = :variable_6 WHERE class_id = :id";
                            $stmt = $conn->prepare($sql);
                            $stmt->bindParam(':id', $row[0], PDO::PARAM_INT);
                            $stmt->bindParam(':category', $row[1]);
                            $stmt->bindParam(':class', $row[2]);
                            $stmt->bindParam(':semester', $row[3], PDO::PARAM_INT);
                            $stmt->bindParam(':valid_from', $row[4]);
                            $stmt->bindParam(':valid_to', $row[5]);
                            $stmt->bindParam(':variable_1', $row[6]);
                            $stmt->bindParam(':variable_2', $row[7]);
                            $stmt->bindParam(':variable_3', $row[8]);
                            $stmt->bindParam(':variable_4', $row[9]);
                            $stmt->bindParam(':variable_5', $row[10]);
                            $stmt->bindParam(':variable_6', $row[11]);
                            $stmt->execute();
                        }
                    }
                        echo json_encode(['status' => 1, 'message' => 'File uploaded successfully.']);
                    } else {
                        echo json_encode(['status' => 0, 'message' => 'Failed to upload file.']);
                    }

                } else {
                    echo json_encode(['status' => 0, 'message' => 'Invalid file type.']);
                }
            } else {
                echo json_encode(['status' => 0, 'message' => 'Failed to upload file.']);
        } else {
            echo json_encode(['status' => 0, 'message' => 'Invalid file type.']);
        }
    cbreak;
}