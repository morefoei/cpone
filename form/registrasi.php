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
$queryRm = mysqli_query($koneksi, "SELECT id FROM tabel_registrasi ORDER BY id DESC LIMIT 1");
$lastId = 0;
if ($queryRm && mysqli_num_rows($queryRm) > 0) {
    $rowRm = mysqli_fetch_assoc($queryRm);
    $lastId = (int)$rowRm['id'];
}
$nextId = $lastId + 1;
// Format menjadi 6 digit angka
$strId = str_pad($nextId, 6, "0", STR_PAD_LEFT);
// Gabungkan menjadi format XX-XX-XX
$autoRm = substr($strId, 0, 2) . '-' . substr($strId, 2, 2) . '-' . substr($strId, 4, 2);

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
        $query = "INSERT INTO tabel_registrasi (
            nomor_rm, nama_pasien, tanggal_lahir, jenis_kelamin, tgl_masuk, penyakit
        ) VALUES (
            '$nomor_rm', '$nama_pasien', " . ($tanggal_lahir !== '' ? "'$tanggal_lahir'" : "NULL") . ", '$jenis_kelamin', '$tgl_masuk', '$penyakit'
        )";

        if (mysqli_query($koneksi, $query)) {
            echo "<script>
                    alert('Data registrasi berhasil disimpan!');
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
    <div class="form-container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="mb-1">Registrasi Pasien</h3>
                <p class="text-muted mb-0">Input data awal pasien sebelum dibuatkan e-resume.</p>
            </div>
            <a href="/form/eresume" class="btn btn-secondary">Kembali</a>
        </div>

        <?php if ($error !== ''): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($message !== ''): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="row">
                <div class="col-md-8 mb-3">
                    <label class="form-label">Nama Pasien</label>
                    <input type="text" name="nama_pasien" class="form-control" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Nomor Rekam Medis</label>
                    <input type="text" name="nomor_rm" class="form-control bg-light" value="<?= htmlspecialchars($autoRm) ?>" readonly required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" class="form-control">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Jenis Kelamin</label>
                    <select name="jenis_kelamin" class="form-select">
                        <option value="L">Laki-laki</option>
                        <option value="P">Perempuan</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Tanggal Masuk</label>
                    <input type="date" name="tgl_masuk" class="form-control" required>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">Penyakit</label>
                <input type="text" name="penyakit" class="form-control" required>
                <small class="text-muted">Data ini akan masuk ke Diagnosa Masuk pada e-resume.</small>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="/form/eresume" class="btn btn-outline-secondary px-4">Batal</a>
                <button type="submit" class="btn btn-primary px-4">Simpan Registrasi</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
