<?php
function getConnection() {
    $conn = new mysqli("localhost", "root", "", "db_sport");
    if ($conn->connect_error) {
        die(json_encode(["error" => "DB connection failed"]));
    }
    return $conn;
}