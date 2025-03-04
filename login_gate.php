<?php
session_start();
include 'koneksi.php';

// Cek apakah ada error dari sesi sebelumnya
$error = isset($_SESSION['error']) ? $_SESSION['error'] : '';
unset($_SESSION['error']); // Hapus pesan error setelah ditampilkan

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Query untuk mencari pengguna berdasarkan email
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['nama'] = $user['nama'];
        $_SESSION['id_kelas'] = $user['id_kelas'];
        $_SESSION['last_activity'] = time();

        // Redirect sesuai role
        if ($user['role'] == 'admin') {
            header("Location: dashboard_admin.php");
        } elseif ($user['role'] == 'guru') {
            header("Location: dashboard_guru.php");
        } elseif ($user['role'] == 'siswa') {
            header("Location: dashboard_siswa.php");
        }
        exit();
    } else {
        $_SESSION['error'] = "Email atau password salah.";
        header("Location: login_gate.php");
        exit();
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Manajemen Sekolah</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-image: url('https://smknegeri40-jkt.sch.id/wp-content/uploads/2023/07/WhatsApp-Image-2023-07-06-at-2.59.15-PM-3-1024x576.jpeg');
            background-size: cover;
            background-position: center;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-box {
            background-color: #ffffff; /* Putih */
            padding: 20px;
            border-radius: 10px;
            width: 100%;
            max-width: 400px;
            text-align: center;
            color: #000000; /* Hitam */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            margin: auto; /* Agar kotak login benar-benar di tengah */
        }
        .login-box img {
            width: 100px;
            margin-bottom: 20px;
        }
        .form-control {
            margin-bottom: 15px;
        }
        .btn-login {
            background-color: #dc3545; /* Merah */
            border: none;
            color: white;
        }
        .btn-login:hover {
            background-color: #b02a37; /* Merah lebih gelap */
        }
        .error-message {
            color: red;
            font-size: 14px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <img src="https://upload.wikimedia.org/wikipedia/id/thumb/b/b6/Logo_SMK_Negeri_40_Jakarta.png/220px-Logo_SMK_Negeri_40_Jakarta.png" alt="Logo SMK Negeri 40 Jakarta">
        <h4>Login</h4>
        <!-- Tampilkan pesan error jika ada -->
        <?php if (!empty($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="form-group">
                <input type="email" name="email" class="form-control" id="email" placeholder="Email" required>
            </div>
            <div class="form-group">
                <input type="password" name="password" class="form-control" id="password" placeholder="Password" required>
            </div>
            <button type="submit" class="btn btn-login btn-block">Login</button>
        </form>
    </div>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>