<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include dirname(__DIR__) . '/backend/koneksi.php';
include __DIR__ . '/dokter_helpers.php';

ensureDokterSchema($koneksi);

$message = $_GET['msg'] ?? '';
$error = '';
$isAdmin = ($_SESSION['role'] ?? 'admin') === 'admin';

if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id']) && $isAdmin) {
    $delId = (int)$_GET['id'];
    if (mysqli_query($koneksi, "DELETE FROM tabel_dokter WHERE id = $delId")) {
        header("Location: dokter.php?msg=" . urlencode('Data dokter berhasil dihapus.'));
        exit;
    } else {
        $error = "Gagal menghapus dokter.";
    }
}

$editId = 0;
$editData = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id']) && $isAdmin) {
    $editId = (int)$_GET['id'];
    $res = mysqli_query($koneksi, "SELECT * FROM tabel_dokter WHERE id = $editId");
    if ($res && mysqli_num_rows($res) > 0) {
        $editData = mysqli_fetch_assoc($res);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isAdmin) {
    $post_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $nama_dokter = mysqli_real_escape_string($koneksi, trim($_POST['nama_dokter'] ?? ''));
    $nomor_dokter = mysqli_real_escape_string($koneksi, trim($_POST['nomor_dokter'] ?? ''));
    $jenis_dokter = ($_POST['jenis_dokter'] ?? 'Umum') === 'Spesialis' ? 'Spesialis' : 'Umum';
    $spesialis = mysqli_real_escape_string($koneksi, trim($_POST['spesialis'] ?? ''));
    $tanda_tangan = '';

    if ($nama_dokter === '' || $nomor_dokter === '') {
        $error = 'Nama dokter dan nomor dokter wajib diisi.';
    } elseif (!empty($_FILES['tanda_tangan']['name'])) {
        $allowed = ['png', 'jpg', 'jpeg'];
        $ext = strtolower(pathinfo($_FILES['tanda_tangan']['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed, true)) {
            $error = 'Tanda tangan harus berupa file PNG atau JPG.';
        } elseif ($_FILES['tanda_tangan']['error'] !== UPLOAD_ERR_OK) {
            $error = 'Upload tanda tangan gagal.';
        } else {
            // === BAGIAN PERBAIKAN: MEMBUAT FOLDER OTOMATIS ===
            // Pastikan kita membuat jalur/path yang benar dari root server
            $targetDir = dirname(__DIR__) . '/assets/img/signatures/';
            
            // Cek apakah foldernya sudah ada. Jika belum, buatkan.
            if (!file_exists($targetDir)) {
                // mkdir dengan parameter 'true' agar membuat folder secara beruntun (assets -> img -> signatures)
                mkdir($targetDir, 0755, true); 
            }
            
            $filename = 'ttd_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            // Jalur relatif yang disimpan ke database (untuk dipanggil di tag <img>)
            $relativePath = 'assets/img/signatures/' . $filename;
            // Jalur absolut tempat file dipindahkan di dalam server
            $targetPath = $targetDir . $filename;

            // Proses pemindahan (upload) file
            if (move_uploaded_file($_FILES['tanda_tangan']['tmp_name'], $targetPath)) {
                $tanda_tangan = mysqli_real_escape_string($koneksi, $relativePath);
            } else {
                $error = 'Gagal menyimpan file tanda tangan ke dalam folder server.';
            }
        }
    }

    if ($error === '') {
        if ($post_id > 0) {
            $sqlTtd = $tanda_tangan ? ", tanda_tangan = '$tanda_tangan'" : "";
            $query = "UPDATE tabel_dokter SET nama_dokter = '$nama_dokter', nomor_dokter = '$nomor_dokter', jenis_dokter = '$jenis_dokter', spesialis = '$spesialis' $sqlTtd WHERE id = $post_id";
        } else {
            $query = "INSERT INTO tabel_dokter (nama_dokter, nomor_dokter, jenis_dokter, spesialis, tanda_tangan)
                      VALUES ('$nama_dokter', '$nomor_dokter', '$jenis_dokter', '$spesialis', '$tanda_tangan')";
        }

        if (mysqli_query($koneksi, $query)) {
            $msg = $post_id > 0 ? 'Data dokter berhasil diupdate.' : 'Data dokter berhasil disimpan.';
            header("Location: dokter.php?msg=" . urlencode($msg));
            exit;
        } else {
            $error = 'Gagal menyimpan data dokter: ' . mysqli_error($koneksi);
        }
    }
}

$dokterList = getDokterList($koneksi);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master Dokter</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .panel { background: #fff; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); padding: 24px; }
        .signature-preview { max-width: 120px; max-height: 56px; object-fit: contain; }
    </style>
</head>
<body>

<div class="container mt-5 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1">Master Dokter</h3>
            <p class="text-muted mb-0">Daftar dokter untuk DPJP dan tanda tangan barcode.</p>
        </div>
        <a href="/form/eresume" class="btn btn-secondary">Kembali</a>
    </div>

    <?php if ($message !== ''): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($isAdmin): ?>
    <div class="panel mb-4">
        <h5 class="mb-3"><?= $editData ? 'Edit Dokter' : 'Tambah Dokter' ?></h5>
        <?php if ($editData): ?>
            <a href="/form/dokter" class="btn btn-sm btn-outline-secondary mb-3">Batal Edit</a>
        <?php endif; ?>
        <form method="POST" enctype="multipart/form-data" action="dokter.php">
            <?php if ($editData): ?>
                <input type="hidden" name="id" value="<?= $editData['id'] ?>">
            <?php endif; ?>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Nama Dokter</label>
                    <input type="text" name="nama_dokter" class="form-control" value="<?= htmlspecialchars($editData['nama_dokter'] ?? '') ?>" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Nomor Dokter</label>
                    <input type="text" name="nomor_dokter" class="form-control" value="<?= htmlspecialchars($editData['nomor_dokter'] ?? '') ?>" required>
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label">Jenis Dokter</label>
                    <select name="jenis_dokter" class="form-select">
                        <option value="Umum" <?= ($editData['jenis_dokter'] ?? '') === 'Umum' ? 'selected' : '' ?>>Umum</option>
                        <option value="Spesialis" <?= ($editData['jenis_dokter'] ?? '') === 'Spesialis' ? 'selected' : '' ?>>Spesialis</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Spesialis</label>
                    <input type="text" name="spesialis" class="form-control" value="<?= htmlspecialchars($editData['spesialis'] ?? '') ?>" placeholder="Contoh: Penyakit Dalam">
                </div>
            </div>
            <div class="row align-items-end">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Gambar Tanda Tangan (PNG/JPG)</label>
                    <input type="file" name="tanda_tangan" class="form-control" accept=".png,.jpg,.jpeg,image/png,image/jpeg">
                </div>
                <div class="col-md-6 mb-3 text-md-end">
                    <button type="submit" class="btn <?= $editData ? 'btn-success' : 'btn-primary' ?> px-4"><?= $editData ? 'Update Dokter' : 'Simpan Dokter' ?></button>
                </div>
            </div>
        </form>
    </div>
    <?php else: ?>
        <div class="alert alert-info">Anda login sebagai Rekam Medis. Anda hanya dapat melihat daftar dokter.</div>
    <?php endif; ?>

    <div class="panel">
        <h5 class="mb-3">List Dokter</h5>
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Nama Dokter</th>
                        <th>Nomor Dokter</th>
                        <th>Jenis</th>
                        <th>Tanda Tangan</th>
                        <?php if ($isAdmin): ?><th>Aksi</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($dokterList) > 0): ?>
                        <?php foreach ($dokterList as $dokter): ?>
                            <tr>
                                <td><?= htmlspecialchars($dokter['nama_dokter'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($dokter['nomor_dokter'] ?? '-') ?></td>
                                <td>
                                    <?= htmlspecialchars($dokter['jenis_dokter'] ?? '-') ?>
                                    <?php if (!empty($dokter['spesialis'])): ?>
                                        <span class="text-muted">- <?= htmlspecialchars($dokter['spesialis']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($dokter['tanda_tangan'])): ?>
                                        <img src="/<?= htmlspecialchars($dokter['tanda_tangan']) ?>" alt="Tanda tangan" class="signature-preview">
                                    <?php else: ?>
                                        <span class="text-muted">Belum ada</span>
                                    <?php endif; ?>
                                </td>
                                <?php if ($isAdmin): ?>
                                <td>
                                    <a href="/form/dokter?action=edit&id=<?= $dokter['id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                    <a href="/form/dokter?action=delete&id=<?= $dokter['id'] ?>" class="btn btn-sm btn-outline-danger ms-1" onclick="return confirm('Apakah Anda yakin ingin menghapus dokter ini?');">Hapus</a>
                                </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-3">Belum ada data dokter.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>