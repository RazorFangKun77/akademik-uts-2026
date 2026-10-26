<?php
require_once __DIR__ . '/connection.php';
mysqli_select_db($conn, "akademik_db");

// sql to create table
$sql = "CREATE TABLE nilai (
nim CHAR(10) PRIMARY KEY,
nm_lengkap VARCHAR(100) NOT NULL,
file_path VARCHAR(255) NULL,
nilai_akhir DECIMAL(5,2) NOT NULL,
nilai_huruf CHAR(1) NOT NULL,
predikat VARCHAR(100) NOT NULL,
created_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
modified_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
  echo "Table created successfully";
} else {
  echo "Error creating table: " . $conn->error;
}

$conn->close();
?>