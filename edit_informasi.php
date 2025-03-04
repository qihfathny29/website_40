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

// Query untuk mendapatkan data informasi berdasarkan ID
$sql = "SELECT * FROM informasi_sekolah WHERE id = '$id'";
$result = $conn->query($sql);
$row = $result->fetch_assoc();

// Proses update jika form disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $judul = $_POST['judul'];
    $deskripsi = $_POST['deskripsi'];
    $kategori = $_POST['kategori'];
    $tanggal = $_POST['tanggal'];

    // Upload foto (opsional)
    $foto = $row['foto']; // Default foto lama
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true); // Buat folder uploads jika belum ada
        }
        $target_file = $target_dir . basename($_FILES["foto"]["name"]);
        move_uploaded_file($_FILES["foto"]["tmp_name"], $target_file);
        $foto = $target_file; // Simpan path foto baru
    }

    // Query update data informasi sekolah
    $sql_update = "UPDATE informasi_sekolah 
                   SET judul='$judul', deskripsi='$deskripsi', kategori='$kategori', tanggal='$tanggal', foto='$foto'
                   WHERE id='$id'";
    
    if ($conn->query($sql_update) === TRUE) {
        echo "<script>alert('Informasi berhasil diperbarui!');</script>";
        echo "<script>window.location.href='dashboard_admin.php';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui informasi: " . $conn->error . "');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Informasi Sekolah</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h3><i class="bi bi-pencil"></i> Edit Informasi Sekolah</h3>
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="judul" class="form-label">Judul</label>
                <input type="text" class="form-control" id="judul" name="judul" value="<?php echo htmlspecialchars($row['judul']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="deskripsi" class="form-label">Deskripsi</label>
                <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3" required><?php echo htmlspecialchars($row['deskripsi']); ?></textarea>
            </div>
            <div class="mb-3">
                <label for="kategori" class="form-label">Kategori</label>
                <select class="form-select" id="kategori" name="kategori" required>
                    <option value="Lomba" <?php echo ($row['kategori'] == 'Lomba') ? 'selected' : ''; ?>>Lomba</option>
                    <option value="Ulangan" <?php echo ($row['kategori'] == 'Ulangan') ? 'selected' : ''; ?>>Ulangan</option>
                    <option value="Upacara" <?php echo ($row['kategori'] == 'Upacara') ? 'selected' : ''; ?>>Upacara</option>
                    <option value="Lainnya" <?php echo ($row['kategori'] == 'Lainnya') ? 'selected' : ''; ?>>Lainnya</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="tanggal" class="form-label">Tanggal</label>
                <input type="date" class="form-control" id="tanggal" name="tanggal" value="<?php echo htmlspecialchars($row['tanggal']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="foto" class="form-label">Foto (Opsional)</label>
                <input type="file" class="form-control" id="foto" name="foto">
                <?php if (!empty($row['foto'])): ?>
                    <img src="<?php echo htmlspecialchars($row['foto']); ?>" alt="Foto Saat Ini" style="max-width: 150px; margin-top: 10px;">
                <?php endif; ?>
            </div>
            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Simpan Perubahan</button>
            <a href="dashboard_admin.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
        </form>
    </div>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>