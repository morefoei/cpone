<?php
// ==========================================
// NYALAKAN PENAMPIL ERROR
// ==========================================
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Panggil koneksi database
include dirname(__DIR__) . '/backend/koneksi.php';

// Cek apakah tombol submit sudah ditekan
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form dan bersihkan untuk mencegah SQL Injection
    $nomor_rm       = mysqli_real_escape_string($koneksi, $_POST['nomor_rm']);
    $nama_pasien    = mysqli_real_escape_string($koneksi, $_POST['nama_pasien']);
    $tgl_masuk      = mysqli_real_escape_string($koneksi, $_POST['tgl_masuk']);
    $tgl_keluar     = mysqli_real_escape_string($koneksi, $_POST['tgl_keluar']);
    $diagnosa_utama = mysqli_real_escape_string($koneksi, $_POST['diagnosa_utama']);
    $dpjp_utama     = mysqli_real_escape_string($koneksi, $_POST['dpjp_utama']);
    $kondisi_pulang = mysqli_real_escape_string($koneksi, $_POST['kondisi_pulang']);

    // ==========================================
    // PERHATIAN: GANTI NAMA TABEL DI BAWAH INI!
    // ==========================================
    // Ganti kata 'tabel_resume_medis' dengan nama tabel asli Anda
    $query = "INSERT INTO tabel_resume_medis (nomor_rm, nama_pasien, tgl_masuk, tgl_keluar, diagnosa_utama, dpjp_utama, kondisi_pulang) 
              VALUES ('$nomor_rm', '$nama_pasien', '$tgl_masuk', '$tgl_keluar', '$diagnosa_utama', '$dpjp_utama', '$kondisi_pulang')";

    // Eksekusi query dan cek hasilnya
    if (mysqli_query($koneksi, $query)) {
        // Jika berhasil, munculkan alert dan kembali ke halaman eresume.php
        echo "<script>
                alert('Data berhasil ditambahkan!');
                window.location.href = 'eresume.php';
              </script>";
        exit;
    } else {
        echo "<div class='alert alert-danger m-3'>Gagal menambahkan data: " . mysqli_error($koneksi) . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Data E-Resume Medis</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .form-container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); max-width: 800px; margin: auto; }
    </style>
</head>
<body>

<div class="container mt-5 mb-5">
    <div class="form-container">
        <h3 class="mb-4">Form Tambah E-Resume Medis</h3>
        
        <form action="" method="POST">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nomor RM</label>
                    <input type="text" name="nomor_rm" class="form-control" required placeholder="Contoh: RM-12345">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nama Pasien</label>
                    <input type="text" name="nama_pasien" class="form-control" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tanggal Masuk</label>
                    <input type="date" name="tgl_masuk" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tanggal Keluar</label>
                    <input type="date" name="tgl_keluar" class="form-control">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Diagnosa Utama</label>
                <input type="text" name="diagnosa_utama" class="form-control" required>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">DPJP Utama</label>
                    <input type="text" name="dpjp_utama" class="form-control" required>
                </div>
                <div class="col-md-6 mb-4">
                    <label class="form-label">Kondisi Pulang</label>
                    <select name="kondisi_pulang" class="form-select" required>
                        <option value="">-- Pilih Kondisi --</option>
                        <option value="Diijinkan Pulang">Diijinkan Pulang</option>
                        <option value="Dirujuk">Dirujuk</option>
                        <option value="Atas Permintaan Sendiri">Atas Permintaan Sendiri</option>
                        <option value="Meninggal">Meninggal</option>
                        <option value="Melarikan Diri">Melarikan Diri</option>
                    </select>
                </div>
            </div>

            <div class="d-flex justify-content-between">
                <a href="eresume.php" class="btn btn-secondary">Kembali</a>
                <button type="submit" class="btn btn-primary">Simpan Data</button>
            </div>
        </form>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>