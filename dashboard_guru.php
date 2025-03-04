<?php
session_start();
include 'koneksi.php';

// Cek apakah guru sudah login
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'guru') {
    header("Location: login.php");
    exit();
}

// Query untuk mendapatkan daftar siswa
$sql_siswa = "SELECT users.id AS user_id, users.nama, kelas.tingkat, kelas.jurusan 
              FROM users 
              LEFT JOIN kelas ON users.id_kelas = kelas.id 
              WHERE users.role = 'siswa'";
$result_siswa = $conn->query($sql_siswa);

// Query untuk mendapatkan data jadwal pelajaran
$sql_jadwal = "SELECT mapel.nama_mapel, kelas.tingkat, kelas.jurusan, jadwal.hari, jadwal.jam, users.nama AS nama_guru
               FROM jadwal 
               JOIN mapel ON jadwal.id_mapel = mapel.id 
               JOIN kelas ON jadwal.id_kelas = kelas.id
               LEFT JOIN users ON jadwal.id_guru = users.id"; 
$result_jadwal = $conn->query($sql_jadwal);

// Query untuk mendapatkan data absensi siswa hari ini
$today = date("Y-m-d"); // Ambil tanggal hari ini
$sql_absensi = "SELECT users.nama AS nama_siswa, kelas.tingkat, kelas.jurusan, absensi.status_kehadiran, absensi.tanggal_absen 
                FROM users 
                LEFT JOIN absensi ON users.nisn = absensi.nisn AND absensi.tanggal_absen = '$today' 
                LEFT JOIN kelas ON users.id_kelas = kelas.id 
                WHERE users.role = 'siswa'";
$result_absensi = $conn->query($sql_absensi);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Guru</title>
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
        .card {
            background-color: #1E1E1E; /* Abu-abu gelap */
            color: #E0E0E0; /* Warna teks terang */
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3); /* Bayangan kartu */
            margin-bottom: 20px;
        }
        .table-dark {
            background-color: #1E1E1E; /* Abu-abu gelap */
            color: #E0E0E0; /* Warna teks terang */
        }
        .table-dark th, .table-dark td {
            border-color: #333; /* Border abu-abu gelap */
        }
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
            <h5>Selamat Datang,<br><?php echo htmlspecialchars($_SESSION['nama']); ?></h5>
        </div>
        <a href="dashboard_guru.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
        <a href="kelola_jadwal.php"><i class="bi bi-calendar-event"></i> Kelola Jadwal</a>
        <a href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
    </div>

    <!-- Content -->
    <div class="content">
        <h3><i class="bi bi-search"></i> Cari Siswa</h3>
        <div class="mb-3">
            <input type="text" id="searchInput" class="form-control" placeholder="Cari siswa berdasarkan nama, kelas, atau NISN..." onkeyup="searchStudents()">
        </div>

        <!-- Daftar Siswa -->
        <h5><i class="bi bi-people"></i> Daftar Siswa</h5>
        <table class="table table-bordered table-dark">
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Kelas</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody id="studentList">
                <?php if ($result_siswa->num_rows > 0): ?>
                    <?php while ($row = $result_siswa->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['nama']); ?></td>
                            <td><?php echo htmlspecialchars($row['tingkat'] . ' ' . $row['jurusan']); ?></td>
                            <td>
                                <a href="edit_siswa.php?id=<?php echo $row['user_id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                <a href="detail_absensi.php?id=<?php echo $row['user_id']; ?>" class="btn btn-sm btn-info">Lihat Absensi</a>
                                <a href="hapus_siswa.php?id=<?php echo $row['user_id']; ?>" class="btn btn-sm btn-danger">Hapus</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" class="text-center">Tidak ada data siswa.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Jadwal Pelajaran -->
        <h5><i class="bi bi-calendar-check"></i> Jadwal Pelajaran</h5>
        <table class="table table-bordered table-dark">
            <thead>
                <tr>
                    <th>Nama Mata Pelajaran</th>
                    <th>Kelas</th>
                    <th>Hari</th>
                    <th>Jam</th>
                    <th>Guru</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result_jadwal->num_rows > 0): ?>
                    <?php while ($row = $result_jadwal->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['nama_mapel']); ?></td>
                            <td><?php echo htmlspecialchars($row['tingkat'] . ' ' . $row['jurusan']); ?></td>
                            <td><?php echo htmlspecialchars($row['hari']); ?></td>
                            <td><?php echo htmlspecialchars($row['jam']); ?></td>
                            <td><?php echo htmlspecialchars($row['nama_guru']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center">Tidak ada data jadwal pelajaran.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Absensi Hari Ini -->
        <h5><i class="bi bi-calendar-date"></i> Absensi Siswa Hari Ini</h5>
        <table class="table table-bordered table-dark">
            <thead>
                <tr>
                    <th>Nama Siswa</th>
                    <th>Kelas</th>
                    <th>Status Kehadiran</th>
                    <th>Tanggal Absen</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result_absensi->num_rows > 0): ?>
                    <?php while ($row = $result_absensi->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['nama_siswa']); ?></td>
                            <td><?php echo htmlspecialchars($row['tingkat'] . ' ' . $row['jurusan']); ?></td>
                            <td><?php echo htmlspecialchars($row['status_kehadiran'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($row['tanggal_absen'] ?? '-'); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center">Tidak ada data absensi hari ini.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // AJAX untuk pencarian siswa
        function searchStudents() {
            const keyword = document.getElementById('searchInput').value;
            const xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    document.getElementById('studentList').innerHTML = this.responseText;
                }
            };
            xhr.open("GET", "search_students.php?keyword=" + keyword, true);
            xhr.send();
        }
    </script>
</body>
</html>