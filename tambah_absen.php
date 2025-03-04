<?php
session_start();
include 'koneksi.php'; // Pastikan file koneksi.php sudah ada dan berfungsi

// Cek apakah pengguna sudah login sebagai siswa
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'siswa') {
    header("Location: login.php");
    exit;
}

// Proses tambah absen jika form disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nisn = $_POST['nisn'];
    $nama = $_POST['nama'];
    $jurusan = $_POST['jurusan'];
    $kelas = $_POST['kelas'];
    $status_kehadiran = $_POST['status_kehadiran'];
    $tanggal_absen = $_POST['tanggal_absen'];

    // Upload foto (opsional)
    $foto = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true); // Buat folder uploads jika belum ada
        }
        $target_file = $target_dir . basename($_FILES["foto"]["name"]);
        move_uploaded_file($_FILES["foto"]["tmp_name"], $target_file);
        $foto = $target_file; // Simpan path foto ke database
    }

    // Query insert data absensi
    $sql = "INSERT INTO absensi (nisn, nama, jurusan, kelas, status_kehadiran, tanggal_absen, foto) 
            VALUES ('$nisn', '$nama', '$jurusan', '$kelas', '$status_kehadiran', '$tanggal_absen', '$foto')";
    
    if ($conn->query($sql) === TRUE) {
        $_SESSION['success'] = "Absensi berhasil ditambahkan.";
        header("Location: dashboard_siswa.php"); // Redirect ke halaman dashboard siswa
        exit;
    } else {
        $_SESSION['error'] = "Gagal menambahkan absensi: " . $conn->error;
        header("Location: tambah_absen.php"); // Redirect kembali ke halaman tambah absen
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Absen</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h3>Tambah Absen</h3>
        <?php
        // Tampilkan pesan error jika ada
        if (isset($_SESSION['error'])) {
            echo "<div class='alert alert-danger'>" . $_SESSION['error'] . "</div>";
            unset($_SESSION['error']); // Hapus pesan error setelah ditampilkan
        }
        ?>
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="nisn" class="form-label">NISN</label>
                <input type="text" class="form-control" id="nisn" name="nisn" required>
            </div>
            <div class="mb-3">
                <label for="nama" class="form-label">Nama</label>
                <input type="text" class="form-control" id="nama" name="nama" required>
            </div>
            <div class="mb-3">
                <label for="jurusan" class="form-label">Jurusan</label>
                <select class="form-select" id="jurusan" name="jurusan" required>
                    <option value="RPL">RPL</option>
                    <option value="DKV">DKV</option>
                    <option value="BR">BR</option>
                    <option value="MP">MP</option>
                    <option value="AK">AK</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="kelas" class="form-label">Kelas</label>
                <input type="text" class="form-control" id="kelas" name="kelas" required>
            </div>
            <div class="mb-3">
                <label for="status_kehadiran" class="form-label">Status Kehadiran</label>
                <select class="form-select" id="status_kehadiran" name="status_kehadiran" required>
                    <option value="Hadir">Hadir</option>
                    <option value="Sakit">Sakit</option>
                    <option value="Izin">Izin</option>
                    <option value="Alpha">Alpha</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="tanggal_absen" class="form-label">Tanggal Absen</label>
                <input type="date" class="form-control" id="tanggal_absen" name="tanggal_absen" required>
            </div>
            <div class="mb-3">
                <label for="foto" class="form-label">Foto (Opsional)</label>
                <input type="file" class="form-control" id="foto" name="foto">
            </div>
            <button type="submit" class="btn btn-primary">Simpan</button>
        </form>
    </div>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>