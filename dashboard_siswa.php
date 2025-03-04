<?php
session_start();
include 'koneksi.php';

// Cek apakah siswa sudah login
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'siswa') {
    header("Location: login.php");
    exit();
}

// Proses tambah absensi jika form disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nisn = $_SESSION['user_id'];
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
    } elseif (isset($_POST['foto_base64'])) {
        // Jika foto diambil dari kamera
        $foto_data = $_POST['foto_base64'];
        $foto_data = str_replace('data:image/png;base64,', '', $foto_data);
        $foto_data = base64_decode($foto_data);

        $target_dir = "uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true); // Buat folder uploads jika belum ada
        }

        $target_file = $target_dir . uniqid() . '.png';
        file_put_contents($target_file, $foto_data);
        $foto = $target_file; // Simpan path foto ke database
    }

    // Query insert data absensi
    $sql = "INSERT INTO absensi (nisn, nama, jurusan, kelas, status_kehadiran, tanggal_absen, foto) 
            VALUES ('$nisn', '$nama', '$jurusan', '$kelas', '$status_kehadiran', '$tanggal_absen', '$foto')";
    
    if ($conn->query($sql) === TRUE) {
        $_SESSION['success'] = "Absensi berhasil ditambahkan.";
        header("Location: dashboard_siswa.php");
        exit();
    } else {
        $_SESSION['error'] = "Gagal menambahkan absensi: " . $conn->error;
        header("Location: dashboard_siswa.php");
        exit();
    }
}

// Query untuk mendapatkan absensi siswa berdasarkan NISN
$nisn_siswa = $_SESSION['user_id'];
$sql_absensi = "SELECT * FROM absensi WHERE nisn = '$nisn_siswa'";
$result_absensi = $conn->query($sql_absensi);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Absensi - Siswa</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
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
        /* Tambahkan gaya untuk tombol kamera */
        .camera-container {
            display: none;
            margin-top: 10px;
        }
        video {
            width: 100%;
            max-width: 300px;
            border: 2px solid #ccc;
            border-radius: 10px;
        }
        canvas {
            display: none;
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
        <a href="dashboard_siswa.php"><i class="bi bi-speedometer2"></i>Dashboard</a>
        <a href="dashboard_siswa_informasi.php"><i class="bi bi-info-circle"></i> Informasi Sekolah</a>
        <a href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
    </div>

    <!-- Content -->
    <div class="content">
        <h3><i class="bi bi-calendar-check"></i>  Kelola Absensi</h3>
        <h5>Tambah Absensi</h5>
        <form method="POST" action="" enctype="multipart/form-data" id="absensiForm">
            <div class="mb-3">
                <label for="nama" class="form-label">Nama</label>
                <input type="text" class="form-control" id="nama" name="nama" >
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
                <select class="form-select" id="kelas" name="kelas" required>
                    <option value="10">10</option>
                    <option value="11">11</option>
                    <option value="12">12</option>
                </select>
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
            <div class="mb-3">
                <button type="button" class="btn btn-secondary" id="toggleCamera">Buka Kamera</button>
            </div>
            <div class="camera-container" id="cameraContainer">
            <video id="video" autoplay></video>
            <button type="button" class="btn btn-success mt-2" id="capture">Ambil Foto</button>
            <canvas id="canvas"></canvas>
        </div>
        <button type="submit" class="btn btn-primary">Simpan</button>
    </form>

    <!-- Daftar Absensi -->
    <h5 class="mt-4">Daftar Absensi</h5>
    <table class="table table-bordered table-dark">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama</th>
                <th>Jurusan</th>
                <th>Kelas</th>
                <th>Tanggal Absen</th>
                <th>Status Kehadiran</th>
                <th>Foto</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result_absensi->num_rows > 0): ?>
                <?php $id = 1; while ($row = $result_absensi->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $id++; ?></td>
                        <td><?php echo htmlspecialchars($row['nama']); ?></td>
                        <td><?php echo htmlspecialchars($row['jurusan']); ?></td>
                        <td><?php echo htmlspecialchars($row['kelas']); ?></td>
                        <td><?php echo htmlspecialchars($row['tanggal_absen']); ?></td>
                        <td><?php echo htmlspecialchars($row['status_kehadiran']); ?></td>
                        <td>
                            <?php if (!empty($row['foto'])): ?>
                                <img src="<?php echo htmlspecialchars($row['foto']); ?>" alt="Foto Absen" style="max-width: 100px;">
                            <?php else: ?>
                                Tidak Ada Foto
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="edit_absen.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                            <a href="hapus_absen.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger">Hapus</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" class="text-center">Tidak ada data absensi.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Script untuk membuka kamera
    const toggleCameraButton = document.getElementById('toggleCamera');
    const cameraContainer = document.getElementById('cameraContainer');
    const video = document.getElementById('video');
    const canvas = document.getElementById('canvas');
    const captureButton = document.getElementById('capture');
    const absensiForm = document.getElementById('absensiForm');

    let mediaStream;

    toggleCameraButton.addEventListener('click', async () => {
        if (!mediaStream) {
            try {
                mediaStream = await navigator.mediaDevices.getUserMedia({ video: true });
                video.srcObject = mediaStream;
                cameraContainer.style.display = 'block';
                toggleCameraButton.textContent = 'Tutup Kamera';
            } catch (error) {
                alert('Kamera tidak dapat diakses. Pastikan izin kamera telah diberikan.');
            }
        } else {
            mediaStream.getTracks().forEach(track => track.stop());
            mediaStream = null;
            video.srcObject = null;
            cameraContainer.style.display = 'none';
            toggleCameraButton.textContent = 'Buka Kamera';
        }
    });

    captureButton.addEventListener('click', () => {
        const context = canvas.getContext('2d');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        context.drawImage(video, 0, 0, canvas.width, canvas.height);

        // Convert canvas to base64 image
        const fotoBase64 = canvas.toDataURL('image/png');
        const inputFotoBase64 = document.createElement('input');
        inputFotoBase64.type = 'hidden';
        inputFotoBase64.name = 'foto_base64';
        inputFotoBase64.value = fotoBase64;

        // Append input to form
        absensiForm.appendChild(inputFotoBase64);

        // Disable file input
        document.getElementById('foto').disabled = true;

        alert('Foto berhasil diambil!');
    });
</script>
</body>
</html>