<?php
$host = "localhost"; // Host database
$username = "root"; // Username database
$password = ""; // Password database (kosong jika XAMPP)
$dbname = "manajemen_sekolah40"; // Nama database

// Membuat koneksi
$conn = new mysqli($host, $username, $password, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>