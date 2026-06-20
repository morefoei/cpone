<?php
require_once __DIR__ . '/backend/koneksi.php';

$isAdmin = ($_SESSION['role'] ?? 'admin') === 'admin';
if (!$isAdmin) {
    die("Akses ditolak. Halaman ini hanya untuk Administrator.");
}

$message = '';
$error = '';

// Proses Hapus User
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $delId = (int)$_GET['id'];
    if ($delId !== (int)$_SESSION['user_id']) { // Jangan hapus diri sendiri
        if (mysqli_query($koneksi, "DELETE FROM tabel_users WHERE id = $delId")) {
            $message = 'User berhasil dihapus.';
        } else {
            $error = 'Gagal menghapus user.';
        }
    } else {
        $error = 'Anda tidak bisa menghapus akun Anda sendiri.';
    }
}

// Ambil Data Edit Jika Ada
$editId = 0;
$editData = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $editId = (int)$_GET['id'];
    try {
        $res = mysqli_query($koneksi, "SELECT * FROM tabel_users WHERE id = $editId");
        if ($res && mysqli_num_rows($res) > 0) {
            $editData = mysqli_fetch_assoc($res);
        }
    } catch (Exception $e) {
        // Abaikan sementara jika gagal
    }
}

// Proses Tambah / Update User
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $username = mysqli_real_escape_string($koneksi, trim($_POST['username']));
    $nama_lengkap = mysqli_real_escape_string($koneksi, trim($_POST['nama_lengkap']));
    $password = $_POST['password'];
    $role = $_POST['role'] === 'admin' ? 'admin' : 'rekam medis';

    // Cek apakah username sudah ada
    $cek = mysqli_query($koneksi, "SELECT id FROM tabel_users WHERE username = '$username' AND id != $post_id");
    if (mysqli_num_rows($cek) > 0) {
        $error = "Username '$username' sudah terdaftar. Silakan gunakan yang lain.";
    } else {
        $sqlPass = "";
        if (!empty($password)) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $sqlPass = ", password = '$hash'";
        }
        
        if ($post_id > 0) {
            // Jika password kosong, jangan update password
            $query = "UPDATE tabel_users SET username='$username', nama_lengkap='$nama_lengkap', role='$role' $sqlPass WHERE id = $post_id";
            $successMsg = "User '$username' berhasil diupdate!";
        } else {
            // Jika tambah baru, password wajib diisi. (HTML attribute 'required' mencegah password kosong, tapi bisa lolos)
            if (empty($password)) {
                $error = "Password wajib diisi untuk pengguna baru.";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $query = "INSERT INTO tabel_users (username, password, nama_lengkap, role) VALUES ('$username', '$hash', '$nama_lengkap', '$role')";
                $successMsg = "User '$username' berhasil ditambahkan!";
            }
        }
        
        if (empty($error)) {
            if (mysqli_query($koneksi, $query)) {
                $message = $successMsg;
                $editData = null; // Clear edit form after success
            } else {
                $error = "Gagal menyimpan user: " . mysqli_error($koneksi);
            }
        }
    }
}

$userList = false;
$dbError = '';
try {
    $userList = mysqli_query($koneksi, "SELECT id, username, nama_lengkap, role FROM tabel_users ORDER BY id DESC");
} catch (Exception $e) {
    $dbError = "Terjadi kesalahan Database: Kolom 'role' belum ada. Mohon jalankan perintah SQL ALTER TABLE seperti yang diinstruksikan.";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola User - E-Resume</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .panel { background: #fff; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); padding: 24px; }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container mt-5 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1">Kelola Akun Pengguna</h3>
            <p class="text-muted mb-0">Hanya Administrator yang dapat melihat dan menambah pengguna baru.</p>
        </div>
        <a href="/" class="btn btn-secondary">Kembali ke Beranda</a>
    </div>

    <?php if ($message !== ''): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($dbError !== ''): ?>
        <div class="alert alert-danger shadow-sm border-danger">
            <h5 class="fw-bold">Peringatan: Database Belum Diupdate!</h5>
            <?= htmlspecialchars($dbError) ?><br><br>
            Silakan buka phpMyAdmin dan jalankan perintah SQL ini:<br>
            <code>ALTER TABLE `tabel_users` ADD COLUMN `role` VARCHAR(20) NOT NULL DEFAULT 'admin';</code>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="panel">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="mb-0"><?= $editData ? 'Edit User' : 'Tambah User Baru' ?></h5>
                    <?php if ($editData): ?>
                        <a href="/users" class="btn btn-sm btn-outline-secondary">Batal Edit</a>
                    <?php endif; ?>
                </div>
                <form method="POST">
                    <?php if ($editData): ?>
                        <input type="hidden" name="id" value="<?= $editData['id'] ?>">
                    <?php endif; ?>
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" class="form-control" value="<?= htmlspecialchars($editData['nama_lengkap'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($editData['username'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password <?= $editData ? '<small class="text-muted">(Kosongkan jika tidak diubah)</small>' : '' ?></label>
                        <input type="password" name="password" class="form-control" <?= $editData ? '' : 'required' ?>>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Role Akses</label>
                        <select name="role" class="form-select" required>
                            <option value="rekam medis" <?= ($editData['role'] ?? '') === 'rekam medis' ? 'selected' : '' ?>>Rekam Medis (Hanya Lihat Dokter)</option>
                            <option value="admin" <?= ($editData['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin (Akses Penuh)</option>
                        </select>
                    </div>
                    <button type="submit" class="btn <?= $editData ? 'btn-success' : 'btn-primary' ?> w-100 fw-bold"><?= $editData ? 'Simpan Perubahan' : 'Simpan User Baru' ?></button>
                </form>
            </div>
        </div>

        <div class="col-md-8">
            <div class="panel">
                <h5 class="mb-4">Daftar Pengguna</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Nama Lengkap</th>
                                <th>Username</th>
                                <th>Role</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($userList && mysqli_num_rows($userList) > 0): ?>
                                <?php while ($u = mysqli_fetch_assoc($userList)): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($u['nama_lengkap']) ?></td>
                                        <td><?= htmlspecialchars($u['username']) ?></td>
                                        <td>
                                            <?php if (($u['role'] ?? 'admin') === 'admin'): ?>
                                                <span class="badge bg-danger">Admin</span>
                                            <?php else: ?>
                                                <span class="badge bg-info text-dark">Rekam Medis</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="/users?action=edit&id=<?= $u['id'] ?>" class="btn btn-sm btn-outline-primary me-1">Edit</a>
                                            <?php if ($u['id'] !== $_SESSION['user_id']): ?>
                                                <a href="/users?action=delete&id=<?= $u['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Yakin ingin menghapus user ini?');">Hapus</a>
                                            <?php else: ?>
                                                <span class="badge bg-secondary ms-1">Anda</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center">Belum ada data user.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
