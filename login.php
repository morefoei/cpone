<?php
require_once __DIR__ . '/backend/koneksi.php';

if (isset($_SESSION['user_id'])) {
    header("Location: /");
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = $_POST['password'];

    $query = "SELECT * FROM tabel_users WHERE username = '$username'";
    $result = mysqli_query($koneksi, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            header("Location: /");
            exit;
        } else {
            $error = 'Password salah!';
        }
    } else {
        $error = 'Username tidak ditemukan!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login E-Resume</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f7f6; }
        .login-card { border-radius: 15px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.08); }
        .login-header { background: linear-gradient(135deg, #0d6efd, #0b5ed7); color: white; border-radius: 15px 15px 0 0; padding: 25px; text-align: center; }
        .form-control { padding: 12px 15px; border-radius: 8px; }
        .btn-login { padding: 12px; border-radius: 8px; font-weight: bold; font-size: 1.1rem; }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center" style="height: 100vh;">
    <div class="card login-card" style="width: 100%; max-width: 420px;">
        <div class="login-header">
            <h3 class="fw-bold mb-1">E-Resume Medis</h3>
            <p class="mb-0 text-white-50">Silakan login untuk melanjutkan</p>
        </div>
        <div class="card-body p-4">
            <?php if ($error): ?>
                <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-semibold text-secondary">Username</label>
                    <input type="text" name="username" class="form-control bg-light border-0" required autofocus placeholder="Masukkan username">
                </div>
                <div class="mb-4">
                    <label class="form-label fw-semibold text-secondary">Password</label>
                    <input type="password" name="password" class="form-control bg-light border-0" required placeholder="Masukkan password">
                </div>
                <button type="submit" class="btn btn-primary w-100 btn-login mt-2">Masuk</button>
            </form>
        </div>
    </div>
</body>
</html>
