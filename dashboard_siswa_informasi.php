<?php
session_start();
// Cek apakah pengguna sudah login sebagai siswa
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'siswa') {
    header("Location: login.php");
    exit;
}
include 'koneksi.php';
// Query untuk mendapatkan data informasi sekolah
$sql = "SELECT * FROM informasi_sekolah ORDER BY tanggal DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informasi Sekolah</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        /* Global Styles */
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #121212; /* Latar belakang gelap */
            color: #E0E0E0; /* Warna teks terang */
        }
        .sidebar {
            height: 100vh;
            width: 250px;
            background-color: #1E1E1E; /* Abu-abu gelap */
            color: white;
            position: fixed;
            top: 0;
            left: 0;
            padding-top: 20px;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.5); /* Bayangan halus */
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            padding: 10px 20px;
            transition: all 0.3s ease;
            border-radius: 8px;
            margin-bottom: 5px;
        }
        .sidebar a:hover {
            background-color: #333; /* Abu-abu lebih terang saat hover */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3); /* Efek hover */
        }
        .sidebar i {
            margin-right: 10px;
        }
        .content {
            margin-left: 250px;
            padding: 20px;
        }
        /* Card Informasi */
        .card-informasi {
            margin-bottom: 20px;
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3); /* Bayangan kartu */
            background-color: #1E1E1E; /* Abu-abu gelap */
            color: #E0E0E0; /* Warna teks terang */
            display: flex; /* Flexbox untuk layout horizontal */
            justify-content: space-between; /* Jarak maksimal antara teks dan gambar */
            align-items: flex-start; /* Align items ke atas */
            padding: 15px;
        }
        .card-informasi .info {
            flex: 1; /* Teks mengambil sisa ruang */
        }
        .card-title {
            font-size: 1.2rem;
            font-weight: bold;
            color: #ffff !important; /* Hitam */
        }
        .text-muted {
            font-size: 0.9rem;
            color: #ffff !important; /* Hitam */
        }
        .card-text {
            font-size: 1rem;
            line-height: 1.6;
            color: #E0E0E0; /* Warna teks terang */
        }
        /* Styling untuk gambar */
        .foto img {
            max-width: 80px; /* Ukuran gambar diperkecil */
            height: auto; /* Tinggi otomatis menyesuaikan */
            float: right; /* Pindahkan gambar ke kanan */
            margin-left: 15px; /* Beri jarak antara teks dan gambar */
            border-radius: 10px; /* Sudut gambar tetap melengkung */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Bayangan halus */
        }
        /* Alert Styling */
        .alert-warning {
            background-color: #fff3cd;
            border: 1px solid #ffeeba;
            color: #856404;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="text-center mb-4">
            <img src="https://upload.wikimedia.org/wikipedia/id/thumb/b/b6/Logo_SMK_Negeri_40_Jakarta.png/220px-Logo_SMK_Negeri_40_Jakarta.png" alt="Logo Sekolah" style="width: 100px; margin-bottom: 10px;">
            <h5>Selamat Datang,<br><?php echo $_SESSION['nama']; ?></h5>
        </div>
        <a href="dashboard_siswa.php"><i class="bi bi-speedometer2"></i>Dashboard</a>
        <a href="dashboard_siswa_informasi.php"><i class="bi bi-info-circle"></i> Informasi Sekolah</a>
        <a href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
    </div>
    <!-- Content -->
    <div class="content">
        <h3><i class="bi bi-info-circle"></i> Informasi Sekolah</h3>
        <p>Berikut adalah daftar informasi terbaru dari sekolah:</p>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="card card-informasi">
                    <div class="info">
                        <h5 class="card-title"><?php echo htmlspecialchars($row['judul']); ?></h5>
                        <small class="text-muted">
                            <i class="bi bi-calendar"></i> <?php echo htmlspecialchars($row['tanggal']); ?> |
                            <i class="bi bi-tag"></i> <?php echo htmlspecialchars($row['kategori']); ?>
                        </small>
                        <p class="card-text mt-2"><?php echo nl2br(htmlspecialchars($row['deskripsi'])); ?></p>
                    </div>
                    <div class="foto">
                        <?php if (!empty($row['foto'])): ?>
                            <img src="<?php echo htmlspecialchars($row['foto']); ?>" alt="Foto Informasi">
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="alert alert-warning" role="alert">
                Tidak ada informasi sekolah saat ini.
            </div>
        <?php endif; ?>
    </div>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>