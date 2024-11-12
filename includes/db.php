<?php
$servername = "localhost";
$username = "root"; // Ganti jika menggunakan username lain
$password = "";     // Ganti jika menggunakan password
$dbname = "db_hutang";

// Membuat Koneksi
$conn = new mysqli($servername, $username, $password, $dbname);

// Memeriksa Koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>
