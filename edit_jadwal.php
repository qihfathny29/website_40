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

// Query untuk mendapatkan data jadwal berdasarkan ID
$sql_jadwal = "SELECT * FROM jadwal WHERE id = '$id_jadwal'";
$result_jadwal = $conn->query($sql_jadwal);

if ($result_jadwal->num_rows == 0) {
    $_SESSION['error'] = "Jadwal tidak ditemukan.";
    header("Location: kelola_jadwal.php");
    exit;
}

$jadwal = $result_jadwal->fetch_assoc();

// Proses update jika form disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_mapel = $_POST['id_mapel'];
    $id_kelas = $_POST['id_kelas'];
    $id_guru = $_POST['id_guru'];
    $hari = $_POST['hari'];
    $jam = $_POST['jam'];

    $sql_update = "UPDATE jadwal 
                   SET id_mapel = '$id_mapel', id_kelas = '$id_kelas', id_guru = '$id_guru', hari = '$hari', jam = '$jam' 
                   WHERE id = '$id_jadwal'";
    
    if ($conn->query($sql_update) === TRUE) {
        $_SESSION['success'] = "Jadwal berhasil diperbarui.";
        header("Location: kelola_jadwal.php");
        exit;
    } else {
        $_SESSION['error'] = "Gagal memperbarui jadwal: " . $conn->error;
        header("Location: edit_jadwal.php?id=$id_jadwal");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Jadwal</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h3><i class="bi bi-pencil-square"></i> Edit Jadwal</h3>

        <?php
        // Tampilkan pesan error jika ada
        if (isset($_SESSION['error'])) {
            echo "<div class='alert alert-danger'>" . $_SESSION['error'] . "</div>";
            unset($_SESSION['error']);
        }
        ?>

        <form method="POST">
            <div class="mb-3">
                <label for="id_mapel" class="form-label">Nama Mata Pelajaran</label>
                <select class="form-select" id="id_mapel" name="id_mapel" required>
                    <option value="1" <?php echo ($jadwal['id_mapel'] == 1) ? 'selected' : ''; ?>>Pemrograman Perangkat Lunak</option>
                    <option value="2" <?php echo ($jadwal['id_mapel'] == 2) ? 'selected' : ''; ?>>Manajemen Perkantoran</option>
                    <option value="3" <?php echo ($jadwal['id_mapel'] == 3) ? 'selected' : ''; ?>>Desain Komunikasi Visual</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="id_kelas" class="form-label">Kelas</label>
                <select class="form-select" id="id_kelas" name="id_kelas" required>
                    <option value="1" <?php echo ($jadwal['id_kelas'] == 1) ? 'selected' : ''; ?>>10 RPL</option>
                    <option value="2" <?php echo ($jadwal['id_kelas'] == 2) ? 'selected' : ''; ?>>11 DKV</option>
                    <option value="3" <?php echo ($jadwal['id_kelas'] == 3) ? 'selected' : ''; ?>>12 BR</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="id_guru" class="form-label">Guru</label>
                <select class="form-select" id="id_guru" name="id_guru" required>
                    <option value="1" <?php echo ($jadwal['id_guru'] == 1) ? 'selected' : ''; ?>>Pak Ardan</option>
                    <option value="2" <?php echo ($jadwal['id_guru'] == 2) ? 'selected' : ''; ?>>Ibu Aini</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="hari" class="form-label">Hari</label>
                <select class="form-select" id="hari" name="hari" required>
                    <option value="Senin" <?php echo ($jadwal['hari'] == 'Senin') ? 'selected' : ''; ?>>Senin</option>
                    <option value="Selasa" <?php echo ($jadwal['hari'] == 'Selasa') ? 'selected' : ''; ?>>Selasa</option>
                    <option value="Rabu" <?php echo ($jadwal['hari'] == 'Rabu') ? 'selected' : ''; ?>>Rabu</option>
                    <option value="Kamis" <?php echo ($jadwal['hari'] == 'Kamis') ? 'selected' : ''; ?>>Kamis</option>
                    <option value="Jumat" <?php echo ($jadwal['hari'] == 'Jumat') ? 'selected' : ''; ?>>Jumat</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="jam" class="form-label">Jam</label>
                <input type="time" class="form-control" id="jam" name="jam" value="<?php echo htmlspecialchars($jadwal['jam']); ?>" required>
            </div>
            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Simpan</button>
            <a href="kelola_jadwal.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
        </form>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>