<?php
session_start();
// Cek apakah pengguna sudah login sebagai admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

include 'koneksi.php';

// Query untuk menghitung total pengguna
$sql_total_pengguna = "SELECT COUNT(*) AS total_pengguna FROM users";
$result_total_pengguna = $conn->query($sql_total_pengguna);
$total_pengguna = $result_total_pengguna->fetch_assoc()['total_pengguna'];

// Query untuk menghitung absensi hari ini
$sql_absensi_hari_ini = "SELECT COUNT(*) AS absensi_hari_ini FROM absensi WHERE DATE(tanggal_absen) = CURDATE()";
$result_absensi_hari_ini = $conn->query($sql_absensi_hari_ini);
$absensi_hari_ini = $result_absensi_hari_ini->fetch_assoc()['absensi_hari_ini'];

// Query untuk menghitung jumlah kelas
$sql_jumlah_kelas = "SELECT COUNT(*) AS jumlah_kelas FROM kelas";
$result_jumlah_kelas = $conn->query($sql_jumlah_kelas);
$jumlah_kelas = $result_jumlah_kelas->fetch_assoc()['jumlah_kelas'];

// Query untuk menghitung jumlah laporan
$sql_jumlah_laporan = "SELECT COUNT(*) AS jumlah_laporan FROM laporan";
$result_jumlah_laporan = $conn->query($sql_jumlah_laporan);
$jumlah_laporan = $result_jumlah_laporan->fetch_assoc()['jumlah_laporan'];

// Query untuk menghitung jumlah siswa berdasarkan status kehadiran hari ini
$sql_status_hadir = "SELECT COUNT(*) AS hadir FROM absensi WHERE DATE(tanggal_absen) = CURDATE() AND status_kehadiran = 'Hadir'";
$sql_status_sakit = "SELECT COUNT(*) AS sakit FROM absensi WHERE DATE(tanggal_absen) = CURDATE() AND status_kehadiran = 'Sakit'";
$sql_status_izin = "SELECT COUNT(*) AS izin FROM absensi WHERE DATE(tanggal_absen) = CURDATE() AND status_kehadiran = 'Izin'";
$sql_status_alpha = "SELECT COUNT(*) AS alpha FROM absensi WHERE DATE(tanggal_absen) = CURDATE() AND status_kehadiran = 'Alpha'";

$result_hadir = $conn->query($sql_status_hadir);
$result_sakit = $conn->query($sql_status_sakit);
$result_izin = $conn->query($sql_status_izin);
$result_alpha = $conn->query($sql_status_alpha);

$jumlah_hadir = $result_hadir->fetch_assoc()['hadir'];
$jumlah_sakit = $result_sakit->fetch_assoc()['sakit'];
$jumlah_izin = $result_izin->fetch_assoc()['izin'];
$jumlah_alpha = $result_alpha->fetch_assoc()['alpha'];

// Proses tambah informasi jika form disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_informasi'])) {
    $judul = $_POST['judul'];
    $deskripsi = $_POST['deskripsi'];
    $kategori = $_POST['kategori'];
    $tanggal = $_POST['tanggal'];

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

    // Query insert data informasi sekolah
    $sql = "INSERT INTO informasi_sekolah (judul, deskripsi, kategori, tanggal, foto) 
            VALUES ('$judul', '$deskripsi', '$kategori', '$tanggal', '$foto')";
    
    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Informasi berhasil ditambahkan!');</script>";
    } else {
        echo "<script>alert('Gagal menambahkan informasi: " . $conn->error . "');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Chart.js dan Plugin Data Labels -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
    <style>
    /* Global Styles */

    body {
        margin: 0;
        font-family: 'Arial', sans-serif;
        background-color: #121212; /* Latar belakang gelap */
        color: #E0E0E0; /* Warna teks terang */
    }

    /* Sidebar */
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
        padding: 12px 20px;
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

    /* Content */
    .content {
        margin-left: 250px;
        padding: 20px;
    }

    /* Cards */
    .card {
        background-color: #1E1E1E; /* Abu-abu gelap */
        color: #E0E0E0; /* Warna teks terang */
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3); /* Bayangan kartu */
        padding: 15px;
        margin-bottom: 20px;
    }

    .card-title {
        font-size: 1.2rem;
        font-weight: bold;
        color: #0d6efd; /* Biru */
    }

    .text-muted {
        color: #888; /* Abu-abu muda */
    }

    /* Buttons */
    .btn-primary {
        background-color: #0d6efd; /* Biru */
        border: none;
    }

    .btn-primary:hover {
        background-color: #0b5ed7; /* Biru lebih gelap */
    }

    .btn-danger {
        background-color: #dc3545; /* Merah */
        border: none;
    }

    .btn-danger:hover {
        background-color: #b02a37; /* Merah lebih gelap */
    }

    .btn-warning {
        background-color: #ffc107; /* Kuning */
        border: none;
    }

    .btn-warning:hover {
        background-color: #e0a800; /* Kuning lebih gelap */
    }

    /* Alerts */
    .alert {
        background-color: #1E1E1E; /* Abu-abu gelap */
        color: #E0E0E0; /* Warna teks terang */
        border: 1px solid #333; /* Border abu-abu gelap */
        border-radius: 8px;
        padding: 15px;
        margin-top: 20px;
    }

    .alert-success {
        background-color: #198754; /* Hijau */
        color: white;
    }

    .alert-danger {
        background-color: #dc3545; /* Merah */
        color: white;
    }
    
</style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="text-center mb-4">
            <img src="https://upload.wikimedia.org/wikipedia/id/thumb/b/b6/Logo_SMK_Negeri_40_Jakarta.png/220px-Logo_SMK_Negeri_40_Jakarta.png" alt="Logo Sekolah" style="width: 100px; margin-bottom: 10px;">
            <h5>Selamat Datang,<br>Admin</h5>
        </div>
        <a href="dashboard_admin.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
        <a href="kelola_pengguna.php"><i class="bi bi-people"></i> Kelola Pengguna</a>
        <a href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
    </div>
    <!-- Content -->
    <div class="content">
        <div class="welcome">Dashboard Admin</div>
        <p>Selamat datang, <?php echo $_SESSION['nama']; ?>!</p>
        <!-- Cards -->
        <div class="row">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <i class="bi bi-person-fill card-icon"></i>
                        <h5>Total Pengguna</h5>
                        <p><?php echo $total_pengguna; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <i class="bi bi-calendar-check card-icon"></i>
                        <h5>Absensi Hari Ini</h5>
                        <p><?php echo $absensi_hari_ini; ?></p>
                        <a href="admin_persetujuan.php" class="btn btn-primary">Lihat Persetujuan</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <i class="bi bi-book card-icon"></i>
                        <h5>Jumlah Kelas</h5>
                        <p><?php echo $jumlah_kelas; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <i class="bi bi-file-text card-icon"></i>
                        <h5>Laporan</h5>
                        <p><?php echo $jumlah_laporan; ?></p>
                    </div>
                </div>
            </div>
        </div>
        <!-- Diagram Lingkaran -->
        <div class="mt-5">
            <h5>Diagram Persentase Kehadiran Hari Ini</h5>
            <div style="width: 400px; margin: auto;">
                <canvas id="kehadiranChart"></canvas>
            </div>
        </div>
        <!-- Script untuk Diagram Lingkaran -->
        <script>
            // Data untuk diagram lingkaran
            const dataKehadiran = {
                labels: ['Hadir', 'Sakit', 'Izin', 'Alpha'],
                datasets: [{
                    data: [<?php echo $jumlah_hadir; ?>, <?php echo $jumlah_sakit; ?>, <?php echo $jumlah_izin; ?>, <?php echo $jumlah_alpha; ?>],
                    backgroundColor: ['#28a745', '#ffc107', '#17a2b8', '#dc3545'], // Warna hijau, kuning, biru, merah
                    hoverBackgroundColor: ['#218838', '#e0a800', '#138496', '#c82333']
                }]
            };
            // Konfigurasi diagram lingkaran
            const configKehadiran = {
                type: 'pie',
                data: dataKehadiran,
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(tooltipItem) {
                                    return tooltipItem.label + ': ' + tooltipItem.raw + ' siswa';
                                }
                            }
                        },
                        datalabels: {
                            formatter: (value, ctx) => {
                                const total = ctx.dataset.data.reduce((acc, curr) => acc + curr, 0);
                                const percentage = ((value / total) * 100).toFixed(2) + '%';
                                return percentage;
                            },
                            color: '#fff', // Warna teks putih
                            font: {
                                size: 20,
                                weight: 'bold'
                            }
                        }
                    }
                },
                plugins: [ChartDataLabels] // Aktifkan plugin ChartDataLabels
            };
            // Render diagram lingkaran
            const kehadiranChart = new Chart(
                document.getElementById('kehadiranChart'),
                configKehadiran
            );
        </script>

        <!-- Form Tambah Informasi Sekolah -->
        <div class="mt-5">
            <h5><i class="bi bi-plus-circle"></i> Tambah Informasi Sekolah</h5>
            <div class="card">
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="judul" class="form-label">Judul</label>
                            <input type="text" class="form-control" id="judul" name="judul" required>
                        </div>
                        <div class="mb-3">
                            <label for="deskripsi" class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="kategori" class="form-label">Kategori</label>
                            <select class="form-select" id="kategori" name="kategori" required>
                                <option value="Lomba">Lomba</option>
                                <option value="Ulangan">Ulangan</option>
                                <option value="Upacara">Upacara</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="tanggal" class="form-label">Tanggal</label>
                            <input type="date" class="form-control" id="tanggal" name="tanggal" required>
                        </div>
                        <div class="mb-3">
                            <label for="foto" class="form-label">Foto (Opsional)</label>
                            <input type="file" class="form-control" id="foto" name="foto">
                        </div>
                        <button type="submit" name="tambah_informasi" class="btn btn-primary"><i class="bi bi-save"></i> Simpan</button>
                        <button type="reset" class="btn btn-secondary"><i class="bi bi-x-circle"></i> Batal</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Daftar Informasi Sekolah -->
        <div class="mt-5">
            <h5><i class="bi bi-info-circle"></i> Daftar Informasi Sekolah</h5>
            <?php
            // Query untuk mendapatkan data informasi sekolah
            $sql = "SELECT * FROM informasi_sekolah ORDER BY tanggal DESC";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<div class='card card-informasi'>";
                    echo "<div class='info'>";
                    echo "<h5 class='card-title'>" . htmlspecialchars($row['judul']) . "</h5>";
                    echo "<small class='text-muted'>";
                    echo "<p style='color: white;'>"; // 
                    echo "<i class='bi bi-calendar'></i> " . htmlspecialchars($row['tanggal']) . " | ";
                    echo "<i class='bi bi-tag'></i> " . htmlspecialchars($row['kategori']);
                    echo "</small>";
                    echo "<p class='card-text mt-2'>" . nl2br(htmlspecialchars($row['deskripsi'])) . "</p>";
                    echo "</div>";
                    echo "<div class='foto'>";
                    if (!empty($row['foto'])) {
                        echo "<img src='" . htmlspecialchars($row['foto']) . "' alt='Foto Informasi' style='max-width: 150px; height: auto; border: 2px solid #ddd; border-radius: 8px;'>";
                    }
                    echo "</div>";
                    echo "<div class='mt-3'>";
                    echo "<a href='edit_informasi.php?id=" . $row['id'] . "' class='btn btn-warning btn-sm'><i class='bi bi-pencil'></i> Edit</a> ";
                    echo "<a href='hapus_informasi.php?id=" . $row['id'] . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Apakah Anda yakin ingin menghapus informasi ini?\");'><i class='bi bi-trash'></i> Hapus</a>";
                    echo "</div>";
                    echo "</div>";
                }
            } else {
                echo "<div class='alert alert-warning' role='alert'>Tidak ada informasi sekolah saat ini.</div>";
            }
            ?>
        </div>
    </div>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>