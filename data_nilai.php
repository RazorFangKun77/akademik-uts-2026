<?php
require_once __DIR__ . '/connection.php';
mysqli_select_db($conn, "akademik_db");

$nim = "$_POST[nim]";
$nm_lengkap = "$_POST[nm_lengkap]";
$file_path = trim($_POST['file_path'] ?? '');
$nilai_akhir = "$_POST[nilai_akhir]";
$nilai_huruf = "$_POST[nilai_huruf]";
$predikat = "$_POST[predikat]";

if ($file_path === '' || !filter_var($file_path, FILTER_VALIDATE_URL)) {
    $file_path = 'https://ui-avatars.com/api/?name=' . rawurlencode($nm_lengkap) . '&background=random&color=fff&size=128';
}

$sql = "INSERT INTO nilai (nim, nm_lengkap, file_path, nilai_akhir, nilai_huruf, predikat, created_date, modified_date)
VALUES ('$nim', '$nm_lengkap', '$file_path', '$nilai_akhir', '$nilai_huruf', '$predikat', NOW(), NOW())";
if (mysqli_query($conn, $sql)) {
    echo "<script>
            alert('New record created successfully');
            window.location.href = 'nilaiui.php';
          </script>";
} else {
    echo "Error: " . $sql . "<br>" . mysqli_error($conn);
}

mysqli_close($conn);


header('Location: readnilai.php');
?>