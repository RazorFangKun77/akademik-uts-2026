<?php
require_once __DIR__ . '/connection.php';

// Create database
$sql = "CREATE DATABASE dbNilai";
if (mysqli_query($conn, $sql)) {
  echo "Database created successfully";
} else {
  echo "Error creating database: " . mysqli_error($conn);
}

// Close connection
mysqli_close($conn);
?>