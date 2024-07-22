<?php
class Connection {
    private $server = 'localhost';
    private $dbname = 'timetofill';
    private $user = 'root';
    private $pass = '';

    public function connect() {
        try {
            // Buat koneksi ke database MySQL menggunakan PDO
            $conn = new PDO('mysql:host=' . $this->server . ';dbname=' . $this->dbname, $this->user, $this->pass);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $conn;
        } catch (\Exception $e) {
            // Tangani error koneksi
            error_log("Database Error: " . $e->getMessage());
            return null; // Mengembalikan null jika koneksi gagal
        }
    }
}
?>
