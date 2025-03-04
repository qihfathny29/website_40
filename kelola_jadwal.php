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

// Proses tambah jadwal jika form disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_mapel = $_POST['id_mapel'];
    $id_kelas = $_POST['id_kelas'];
    $id_guru = $_POST['id_guru'];
    $hari = $_POST['hari'];
    $jam = $_POST['jam'];
    $sql = "INSERT INTO jadwal (id_mapel, id_kelas, id_guru, hari, jam) 
            VALUES ('$id_mapel', '$id_kelas', '$id_guru', '$hari', '$jam')";
    
    if ($conn->query($sql) === TRUE) {
        $_SESSION['success'] = "Jadwal berhasil ditambahkan.";
        header("Location: kelola_jadwal.php");
        exit;
    } else {
        $_SESSION['error'] = "Gagal menambahkan jadwal: " . $conn->error;
        header("Location: kelola_jadwal.php");
        exit;
    }
}

// Query untuk mendapatkan daftar jadwal
$sql_jadwal = "SELECT jadwal.id, mapel.nama_mapel, kelas.tingkat, kelas.jurusan, jadwal.hari, jadwal.jam, users.nama AS nama_guru
               FROM jadwal 
               JOIN mapel ON jadwal.id_mapel = mapel.id 
               JOIN kelas ON jadwal.id_kelas = kelas.id
               LEFT JOIN users ON jadwal.id_guru = users.id"; 
$result_jadwal = $conn->query($sql_jadwal);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Jadwal</title>
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
            <h5>Selamat Datang,<br><?php echo $_SESSION['nama']; ?></h5>
        </div>
        <a href="dashboard_guru.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
        <a href="kelola_jadwal.php"><i class="bi bi-calendar-plus"></i> Kelola Jadwal</a>
        <a href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
    </div>
    <!-- Content -->
    <div class="content">
        <h3><i class="bi bi-calendar-plus"></i> Kelola Jadwal Pelajaran</h3>
        <div class="card">
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label for="id_mapel" class="form-label">Nama Mata Pelajaran</label>
                        <select class="form-select" id="id_mapel" name="id_mapel" required>
                            <option value="1">Pemrograman Perangkat Lunak</option>
                            <option value="2">Manajemen Perkantoran</option>
                            <option value="3">Desain Komunikasi Visual</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="id_kelas" class="form-label">Kelas</label>
                        <select class="form-select" id="id_kelas" name="id_kelas" required>
                            <option value="1">10 RPL</option>
                            <option value="2">11 DKV</option>
                            <option value="3">12 BR</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="id_guru" class="form-label">Guru</label>
                        <select class="form-select" id="id_guru" name="id_guru" required>
                            <option value="1">Pak Ardan</option>
                            <option value="2">Ibu Aini</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="hari" class="form-label">Hari</label>
                        <select class="form-select" id="hari" name="hari" required>
                            <option value="Senin">Senin</option>
                            <option value="Selasa">Selasa</option>
                            <option value="Rabu">Rabu</option>
                            <option value="Kamis">Kamis</option>
                            <option value="Jumat">Jumat</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="jam" class="form-label">Jam</label>
                        <input type="time" class="form-control" id="jam" name="jam" required>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Simpan</button>
                    <button type="reset" class="btn btn-secondary"><i class="bi bi-x-circle"></i> Batal</button>
                </form>
            </div>
        </div>
        <h5><i class="bi bi-list-task"></i> Daftar Jadwal</h5>
        <div class="card">
            <div class="card-body">
                <table class="table table-bordered table-dark">
                    <thead>
                        <tr>
                            <th>Nama Mata Pelajaran</th>
                            <th>Kelas</th>
                            <th>Hari</th>
                            <th>Jam</th>
                            <th>Guru</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result_jadwal->num_rows > 0) {
                            while ($row = $result_jadwal->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['nama_mapel']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['tingkat'] . ' ' . $row['jurusan']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['hari']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['jam']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['nama_guru']) . "</td>";
                                echo "<td>
                                        <a href='edit_jadwal.php?id=" . $row['id'] . "' class='btn btn-warning btn-sm'><i class='bi bi-pencil'></i> Edit</a>
                                        <a href='hapus_jadwal.php?id=" . $row['id'] . "' class='btn btn-danger btn-sm'><i class='bi bi-trash'></i> Hapus</a>
                                      </td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6' class='text-center'>Tidak ada data jadwal.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>