<?php
session_start();
// Cek apakah pengguna sudah login sebagai siswa
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'siswa') {
    header("Location: login.php");
    exit;
}
include 'koneksi.php';

// Redirect to logout if session is inactive for 10 minutes
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 600)) {
    header("Location: logout.php");
    exit();
}
$_SESSION['last_activity'] = time(); // Update last activity time

// Ambil ID absensi dari parameter URL
$id_absensi = isset($_GET['id']) ? $_GET['id'] : null;
if (!$id_absensi) {
    $_SESSION['error'] = "ID absensi tidak valid.";
    header("Location: dashboard_siswa.php");
    exit;
}

// Query untuk menghapus absensi berdasarkan ID
$sql_hapus = "DELETE FROM absensi WHERE id = '$id_absensi'";
if ($conn->query($sql_hapus) === TRUE) {
    $_SESSION['success'] = "Absensi berhasil dihapus.";
} else {
    $_SESSION['error'] = "Gagal menghapus absensi: " . $conn->error;
}

header("Location: dashboard_siswa.php");
exit;
?>