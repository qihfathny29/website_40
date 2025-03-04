<?php
session_start();
// Cek apakah pengguna sudah login sebagai guru
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'guru') {
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

// Ambil ID jadwal dari parameter URL
$id_jadwal = isset($_GET['id']) ? $_GET['id'] : null;

if (!$id_jadwal) {
    $_SESSION['error'] = "ID jadwal tidak valid.";
    header("Location: kelola_jadwal.php");
    exit;
}

// Query untuk menghapus jadwal berdasarkan ID
$sql_hapus = "DELETE FROM jadwal WHERE id = '$id_jadwal'";

if ($conn->query($sql_hapus) === TRUE) {
    $_SESSION['success'] = "Jadwal berhasil dihapus.";
} else {
    $_SESSION['error'] = "Gagal menghapus jadwal: " . $conn->error;
}

header("Location: kelola_jadwal.php");
exit;
?>