<?php
session_start();
// Cek apakah pengguna sudah login sebagai admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

include 'koneksi.php';

// Ambil ID informasi dari parameter URL
$id = $_GET['id'];

// Query untuk menghapus data informasi sekolah
$sql = "DELETE FROM informasi_sekolah WHERE id = '$id'";
if ($conn->query($sql) === TRUE) {
    echo "<script>alert('Informasi berhasil dihapus!');</script>";
} else {
    echo "<script>alert('Gagal menghapus informasi: " . $conn->error . "');</script>";
}

echo "<script>window.location.href='dashboard_admin.php';</script>";
?>