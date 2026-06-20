<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include dirname(__DIR__) . '/backend/koneksi.php';
include __DIR__ . '/registrasi_helpers.php';

ensureRegistrasiSchema($koneksi);

$message = '';
$error = '';

// Generate Nomor RM Otomatis (Format Terminal Digit Filing: XX-XX-XX)
$queryRm = mysqli_query($koneksi, "SELECT nomor_rm FROM tabel_registrasi");
$maxRmInt = 0;
if ($queryRm) {
    while ($rowRm = mysqli_fetch_assoc($queryRm)) {
        // Hapus tanda strip dan ubah ke integer
        $rmInt = (int) str_replace('-', '', $rowRm['nomor_rm']);
        if ($rmInt > $maxRmInt) {
            $maxRmInt = $rmInt;
        }
    }
}
$nextId = $maxRmInt + 1;
$strId = str_pad($nextId, 6, "0", STR_PAD_LEFT);
$autoRm = substr($strId, 0, 2) . '-' . substr($strId, 2, 2) . '-' . substr($strId, 4, 2);

$editId = 0;
$editData = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $editId = (int)$_GET['id'];
    $res = mysqli_query($koneksi, "SELECT * FROM tabel_registrasi WHERE id = $editId");
    if ($res && mysqli_num_rows($res) > 0) {
        $editData = mysqli_fetch_assoc($res);
        $autoRm = $editData['nomor_rm']; // Gunakan nomor RM yang sudah ada jika edit
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_pasien = mysqli_real_escape_string($koneksi, trim($_POST['nama_pasien'] ?? ''));
    $tanggal_lahir = mysqli_real_escape_string($koneksi, $_POST['tanggal_lahir'] ?? '');
    $jenis_kelamin = mysqli_real_escape_string($koneksi, $_POST['jenis_kelamin'] ?? '');
    $nomor_rm = mysqli_real_escape_string($koneksi, trim($_POST['nomor_rm'] ?? ''));
    $tgl_masuk = mysqli_real_escape_string($koneksi, $_POST['tgl_masuk'] ?? '');
    $penyakit = mysqli_real_escape_string($koneksi, trim($_POST['penyakit'] ?? ''));

    if ($nama_pasien === '' || $nomor_rm === '' || $tgl_masuk === '' || $penyakit === '') {
        $error = 'Nama pasien, nomor RM, tanggal masuk, dan penyakit wajib diisi.';
    } else {
        $post_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        
        if ($post_id > 0) {
            $tgl_lhr_sql = $tanggal_lahir !== '' ? "'$tanggal_lahir'" : "NULL";
            $query = "UPDATE tabel_registrasi SET 
                        nomor_rm = '$nomor_rm', 
                        nama_pasien = '$nama_pasien', 
                        tanggal_lahir = $tgl_lhr_sql, 
                        jenis_kelamin = '$jenis_kelamin', 
                        tgl_masuk = '$tgl_masuk', 
                        penyakit = '$penyakit' 
                      WHERE id = $post_id";
            
            // Cascade update ke tabel_resume_medis agar tersinkronisasi
            $queryCascade = "UPDATE tabel_resume_medis SET
                                nama_pasien = '$nama_pasien',
                                tanggal_lahir = $tgl_lhr_sql,
                                jenis_kelamin = '$jenis_kelamin',
                                tgl_masuk = '$tgl_masuk',
                                diagnosa_masuk = '$penyakit'
                             WHERE registrasi_id = $post_id";
            mysqli_query($koneksi, $queryCascade);
            
            $msg = 'Data registrasi berhasil diperbarui!';
        } else {
            $query = "INSERT INTO tabel_registrasi (
                nomor_rm, nama_pasien, tanggal_lahir, jenis_kelamin, tgl_masuk, penyakit
            ) VALUES (
                '$nomor_rm', '$nama_pasien', " . ($tanggal_lahir !== '' ? "'$tanggal_lahir'" : "NULL") . ", '$jenis_kelamin', '$tgl_masuk', '$penyakit'
            )";
            $msg = 'Data registrasi berhasil disimpan!';
        }

        if (mysqli_query($koneksi, $query)) {
            echo "<script>
                    alert('$msg');
                    window.location.href = '/form/eresume';
                  </script>";
            exit;
        }

        $error = 'Gagal menyimpan registrasi: ' . mysqli_error($koneksi);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Pasien</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f0f2f5; }
        .form-container { background: white; padding: 32px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); max-width: 760px; margin: auto; }
    </style>
</head>
<body>

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="form-container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="mb-0"><?= $editData ? 'Edit Data Pasien' : 'Registrasi Pasien Baru' ?></h3>
                    <?php if ($editData): ?>
                        <a href="/form/eresume" class="btn btn-sm btn-outline-secondary">Batal Edit</a>
                    <?php else: ?>
                        <a href="/form/eresume" class="btn btn-sm btn-outline-secondary">Kembali</a>
                    <?php endif; ?>
                </div>

                <?php if ($error !== ''): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST">
                    <?php if ($editData): ?>
                        <input type="hidden" name="id" value="<?= $editData['id'] ?>">
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label class="form-label">Nomor Rekam Medis (Auto TDF)</label>
                        <input type="text" name="nomor_rm" class="form-control bg-light fw-bold" value="<?= htmlspecialchars($autoRm) ?>" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap Pasien <span class="text-danger">*</span></label>
                        <input type="text" name="nama_pasien" class="form-control" value="<?= htmlspecialchars($editData['nama_pasien'] ?? '') ?>" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Lahir</label>
                            <input type="date" name="tanggal_lahir" class="form-control" value="<?= htmlspecialchars($editData['tanggal_lahir'] ?? '') ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jenis Kelamin</label>
                            <select name="jenis_kelamin" class="form-select">
                                <option value="">Pilih...</option>
                                <option value="L" <?= ($editData['jenis_kelamin'] ?? '') === 'L' ? 'selected' : '' ?>>Laki-laki</option>
                                <option value="P" <?= ($editData['jenis_kelamin'] ?? '') === 'P' ? 'selected' : '' ?>>Perempuan</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tanggal Masuk (Admisi) <span class="text-danger">*</span></label>
                        <input type="date" name="tgl_masuk" class="form-control" value="<?= htmlspecialchars($editData['tgl_masuk'] ?? '') ?>" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Penyakit / Keluhan <span class="text-danger">*</span></label>
                        <textarea name="penyakit" class="form-control" rows="3" required><?= htmlspecialchars($editData['penyakit'] ?? '') ?></textarea>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn <?= $editData ? 'btn-success' : 'btn-primary' ?> btn-lg fw-bold"><?= $editData ? 'Update Data Registrasi' : 'Simpan Registrasi' ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
