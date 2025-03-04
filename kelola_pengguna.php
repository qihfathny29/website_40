<?php
session_start();
// Cek apakah pengguna sudah login sebagai admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

include 'koneksi.php';

// Query untuk menampilkan semua pengguna
$sql = "SELECT users.id, users.nama, users.email, users.role, kelas.tingkat, kelas.jurusan 
        FROM users 
        LEFT JOIN kelas ON users.id_kelas = kelas.id";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pengguna</title>
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
            <h5>Selamat Datang,<br>Admin</h5>
        </div>
        <a href="dashboard_admin.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
        <a href="kelola_pengguna.php"><i class="bi bi-people"></i> Kelola Pengguna</a>
        <a href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
    </div>
    <!-- Content -->
    <div class="content">
        <h3><i class="bi bi-people"></i> Kelola Pengguna</h3>
        <div class="card">
            <div class="card-body">
                <form action="" method="GET" class="mb-3">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Cari pengguna..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                        <button class="btn btn-danger" type="submit">Cari</button>
                    </div>
                </form>
                <table class="table table-bordered table-dark">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Kelas</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $kelas = $row['tingkat'] && $row['jurusan'] ? $row['tingkat'] . ' ' . $row['jurusan'] : '-';
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['nama']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['role']) . "</td>";
                                echo "<td>" . htmlspecialchars($kelas) . "</td>";
                                echo "<td>
                                        <a href='edit_pengguna.php?id=" . $row['id'] . "' class='btn btn-sm btn-warning'>Edit</a>
                                        <a href='hapus_jadwal.php?id=" . $row['id'] . "' class='btn btn-sm btn-danger' onclick='return confirm(\"Yakin ingin menghapus?\")'>Hapus</a>
                                      </td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5' class='text-center'>Tidak ada data pengguna.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
        <!-- Absensi Siswa Hari Ini --
    </div>
    Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>