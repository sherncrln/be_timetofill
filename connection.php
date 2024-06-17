<?php
// connection.php

class Connection {
    private $server = 'localhost';
    private $dbname = 'timetofill';
    private $user = 'root';
    private $pass = '';

    public function connect() {
        try {
            $conn = new PDO('mysql:host=' . $this->server . ';dbname=' . $this->dbname, $this->user, $this->pass);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $conn;
        } catch (\Exception $e) {
            error_log("Database Error: " . $e->getMessage());
        }
    }
}