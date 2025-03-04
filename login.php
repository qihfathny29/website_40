<?php
session_start();
include 'koneksi.php';

// Cek apakah ada error dari sesi sebelumnya
$error = isset($_SESSION['error']) ? $_SESSION['error'] : '';
unset($_SESSION['error']); // Hapus pesan error setelah ditampilkan

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $selected_role = $_POST['role']; // Ambil role yang dipilih dari dropdown

    // Query untuk mencari pengguna berdasarkan email
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        // Validasi apakah role yang dipilih sesuai dengan role di database
        if ($selected_role === $user['role']) {
            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['nama'] = $user['nama'];
            $_SESSION['id_kelas'] = $user['id_kelas'];
            $_SESSION['last_activity'] = time();

            // Redirect sesuai role dengan animasi spinner
            echo '
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Redirecting...</title>
                <!-- Bootstrap CSS -->
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
                <style>
                    body {
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        height: 100vh;
                        margin: 0;
                        background-color: #ffffff; /* Putih */
                    }
                    .spinner-container {
                        text-align: center;
                    }
                    .spinner-text {
                        margin-top: 15px;
                        font-size: 18px;
                        color: #000000; /* Hitam */
                    }
                </style>
            </head>
            <body>
                <div class="spinner-container">
                    <div class="spinner-border text-danger" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <div class="spinner-text">Redirecting to dashboard...</div>
                </div>
                <script>
                    // Redirect sesuai role setelah 2 detik
                    setTimeout(function() {
                        window.location.href = "' . ($user['role'] == 'admin' ? 'dashboard_admin.php' : ($user['role'] == 'guru' ? 'dashboard_guru.php' : 'dashboard_siswa.php')) . '";
                    }, 2000); // 2 detik delay
                </script>
            </body>
            </html>';
            exit();
        } else {
            // Role tidak sesuai
            $_SESSION['error'] = "Role yang dipilih tidak sesuai.";
            header("Location: login.php");
            exit();
        }
    } else {
        // Email atau password salah
        $_SESSION['error'] = "Email atau password salah.";
        header("Location: login.php");
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
            position: relative;
            opacity: 1;
            transition: opacity 0.5s ease-in-out, transform 0.5s ease-in-out;
        }
        .login-box.hidden {
            opacity: 0;
            transform: translateY(-20px);
        }
        .login-box.visible {
            opacity: 1;
            transform: translateY(0);
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

        /* Responsive Design */
        @media (max-width: 768px) {
            .login-box {
                max-width: 350px;
                padding: 15px;
            }
            .login-box img {
                width: 80px;
                margin-bottom: 15px;
            }
            .form-control {
                font-size: 14px;
                padding: 10px;
            }
            .btn-login {
                font-size: 14px;
                padding: 10px;
            }
            .error-message {
                font-size: 12px;
            }
        }

        @media (max-width: 480px) {
            .login-box {
                max-width: 300px;
                padding: 10px;
            }
            .login-box img {
                width: 60px;
                margin-bottom: 10px;
            }
            .form-control {
                font-size: 12px;
                padding: 8px;
            }
            .btn-login {
                font-size: 12px;
                padding: 8px;
            }
            .error-message {
                font-size: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="login-box visible" id="loginBox">
        <img src="https://upload.wikimedia.org/wikipedia/id/thumb/b/b6/Logo_SMK_Negeri_40_Jakarta.png/220px-Logo_SMK_Negeri_40_Jakarta.png" alt="Logo SMK Negeri 40 Jakarta">
        <h4 id="loginTitle">Login</h4>
        <!-- Tampilkan pesan error jika ada -->
        <?php if (!empty($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="form-group">
                <select name="role" class="form-control" id="roleSelect" required>
                    <option value="" disabled selected>Pilih Role</option>
                    <option value="admin">Admin</option>
                    <option value="guru">Guru</option>
                    <option value="siswa">Siswa</option>
                </select>
            </div>
            <div class="form-group">
                <input type="email" name="email" class="form-control" placeholder="Email" required>
            </div>
            <div class="form-group">
                <input type="password" name="password" class="form-control" placeholder="Password" required>
            </div>
            <button type="submit" class="btn btn-login btn-block">Login</button>
        </form>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Fungsi untuk animasi fade-in dan fade-out
        document.getElementById('roleSelect').addEventListener('change', function () {
            const role = this.value;
            if (role) {
                // Dapatkan elemen kotak login
                const loginBox = document.getElementById('loginBox');

                // Tambahkan animasi fade-out
                loginBox.classList.remove('visible');
                loginBox.classList.add('hidden');

                // Setelah animasi fade-out selesai, ubah teks dan jalankan animasi fade-in
                setTimeout(() => {
                    // Ubah teks "Login" sesuai role
                    document.getElementById('loginTitle').innerText = `Login ${role.charAt(0).toUpperCase() + role.slice(1)}`;

                    // Hapus class hidden dan tambahkan class visible untuk fade-in
                    loginBox.classList.remove('hidden');
                    loginBox.classList.add('visible');
                }, 500); // Delay sesuai durasi animasi fade-out
            }
        });
    </script>
</body>
</html>