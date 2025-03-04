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

// Query untuk mendapatkan data absensi berdasarkan ID
$sql_absensi = "SELECT * FROM absensi WHERE id = '$id_absensi'";
$result_absensi = $conn->query($sql_absensi);
if ($result_absensi->num_rows == 0) {
    $_SESSION['error'] = "Absensi tidak ditemukan.";
    header("Location: dashboard_siswa.php");
    exit;
}
$absensi = $result_absensi->fetch_assoc();

// Proses update jika form disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = $_POST['nama']; // Ambil nilai nama dari form
    $jurusan = $_POST['jurusan'];
    $kelas = $_POST['kelas'];
    $status_kehadiran = $_POST['status_kehadiran'];
    $tanggal_absen = $_POST['tanggal_absen'];

    // Upload foto (opsional)
    $foto = $absensi['foto']; // Default foto lama
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true); // Buat folder uploads jika belum ada
        }
        $target_file = $target_dir . basename($_FILES["foto"]["name"]);
        move_uploaded_file($_FILES["foto"]["tmp_name"], $target_file);
        $foto = $target_file; // Simpan path foto baru
    }

    // Query update data absensi
    $sql_update = "UPDATE absensi 
                   SET nama = '$nama', jurusan = '$jurusan', kelas = '$kelas', status_kehadiran = '$status_kehadiran', tanggal_absen = '$tanggal_absen', foto = '$foto' 
                   WHERE id = '$id_absensi'";

    if ($conn->query($sql_update) === TRUE) {
        $_SESSION['success'] = "Absensi berhasil diperbarui.";
        header("Location: dashboard_siswa.php");
        exit;
    } else {
        $_SESSION['error'] = "Gagal memperbarui absensi: " . $conn->error;
        header("Location: edit_absen.php?id=$id_absensi");
        exit;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Absensi</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h3><i class="bi bi-pencil-square"></i> Edit Absensi</h3>
        <?php
        // Tampilkan pesan error jika ada
        if (isset($_SESSION['error'])) {
            echo "<div class='alert alert-danger'>" . $_SESSION['error'] . "</div>";
            unset($_SESSION['error']);
        }
        ?>
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="nama" class="form-label">Nama</label>
                <input type="text" class="form-control" id="nama" name="nama" value="<?php echo htmlspecialchars($absensi['nama']); ?>">
            </div>
            <div class="mb-3">
                <label for="jurusan" class="form-label">Jurusan</label>
                <select class="form-select" id="jurusan" name="jurusan" required>
                    <option value="RPL" <?php echo ($absensi['jurusan'] == 'RPL') ? 'selected' : ''; ?>>RPL</option>
                    <option value="DKV" <?php echo ($absensi['jurusan'] == 'DKV') ? 'selected' : ''; ?>>DKV</option>
                    <option value="BR" <?php echo ($absensi['jurusan'] == 'BR') ? 'selected' : ''; ?>>BR</option>
                    <option value="MP" <?php echo ($absensi['jurusan'] == 'MP') ? 'selected' : ''; ?>>MP</option>
                    <option value="AK" <?php echo ($absensi['jurusan'] == 'AK') ? 'selected' : ''; ?>>AK</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="kelas" class="form-label">Kelas</label>
                <input type="text" class="form-control" id="kelas" name="kelas" value="<?php echo htmlspecialchars($absensi['kelas']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="status_kehadiran" class="form-label">Status Kehadiran</label>
                <select class="form-select" id="status_kehadiran" name="status_kehadiran" required>
                    <option value="Hadir" <?php echo ($absensi['status_kehadiran'] == 'Hadir') ? 'selected' : ''; ?>>Hadir</option>
                    <option value="Sakit" <?php echo ($absensi['status_kehadiran'] == 'Sakit') ? 'selected' : ''; ?>>Sakit</option>
                    <option value="Izin" <?php echo ($absensi['status_kehadiran'] == 'Izin') ? 'selected' : ''; ?>>Izin</option>
                    <option value="Alpha" <?php echo ($absensi['status_kehadiran'] == 'Alpha') ? 'selected' : ''; ?>>Alpha</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="tanggal_absen" class="form-label">Tanggal Absen</label>
                <input type="date" class="form-control" id="tanggal_absen" name="tanggal_absen" value="<?php echo htmlspecialchars($absensi['tanggal_absen']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="foto" class="form-label">Foto (Opsional)</label>
                <input type="file" class="form-control" id="foto" name="foto">
                <?php if (!empty($absensi['foto'])): ?>
                    <img src="<?php echo htmlspecialchars($absensi['foto']); ?>" alt="Foto Absen" style="width: 100px; margin-top: 10px;">
                <?php endif; ?>
            </div>
            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Simpan</button>
            <a href="dashboard_siswa.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
        </form>
    </div>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>