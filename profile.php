<?php
require_once __DIR__ . '/backend/koneksi.php';

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_lengkap = mysqli_real_escape_string($koneksi, trim($_POST['nama_lengkap']));
    $password_baru = $_POST['password_baru'];
    $password_konfirmasi = $_POST['password_konfirmasi'];

    $update_query = "UPDATE tabel_users SET nama_lengkap = '$nama_lengkap'";
    $boleh_update = true;

    if (!empty($password_baru)) {
        if ($password_baru === $password_konfirmasi) {
            $hash = password_hash($password_baru, PASSWORD_DEFAULT);
            $update_query .= ", password = '$hash'";
        } else {
            $error = 'Konfirmasi password baru tidak cocok!';
            $boleh_update = false;
        }
    }

    $update_query .= " WHERE id = $user_id";

    if ($boleh_update) {
        if (mysqli_query($koneksi, $update_query)) {
            $_SESSION['nama_lengkap'] = $nama_lengkap;
            $message = 'Profil berhasil diupdate!';
        } else {
            $error = 'Gagal mengupdate profil: ' . mysqli_error($koneksi);
        }
    }
}

$user_query = mysqli_query($koneksi, "SELECT * FROM tabel_users WHERE id = $user_id");
$user = mysqli_fetch_assoc($user_query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f7f6; }
    </style>
</head>
<body>
    <?php include __DIR__ . '/navbar.php'; ?>
    <div class="container mt-5">
        <div class="card shadow-sm mx-auto" style="max-width: 500px; border-radius: 12px;">
            <div class="card-header bg-white pt-4 pb-2 border-0 text-center">
                <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow-sm" style="width: 60px; height: 60px; font-size: 28px;">
                    👤
                </div>
                <h4 class="fw-bold mb-0">Edit Profile</h4>
            </div>
            <div class="card-body p-4 pt-2">
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <?php if ($message): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label text-muted fw-semibold" style="font-size: 0.9rem;">Username</label>
                        <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($user['username']) ?>" readonly>
                        <small class="text-muted">Username tidak dapat diubah.</small>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold" style="font-size: 0.9rem;">Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" class="form-control" value="<?= htmlspecialchars($user['nama_lengkap']) ?>" required>
                    </div>
                    
                    <hr class="my-4">
                    
                    <h6 class="fw-bold text-primary mb-3">Keamanan (Ganti Password)</h6>
                    <p class="text-muted small mb-3">Kosongkan kolom di bawah ini jika Anda tidak ingin mengganti password.</p>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold" style="font-size: 0.9rem;">Password Baru</label>
                        <input type="password" name="password_baru" class="form-control bg-light border-0" placeholder="Masukkan password baru">
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold" style="font-size: 0.9rem;">Konfirmasi Password Baru</label>
                        <input type="password" name="password_konfirmasi" class="form-control bg-light border-0" placeholder="Ketik ulang password baru">
                    </div>
                    
                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="/" class="btn btn-outline-secondary px-4 fw-semibold rounded-pill">Batal</a>
                        <button type="submit" class="btn btn-primary px-4 fw-semibold rounded-pill shadow-sm">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
