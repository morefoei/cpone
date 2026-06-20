<?php
// Tampilkan error jika ada masalah
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include dirname(__DIR__) . '/backend/koneksi.php';
include __DIR__ . '/dokter_helpers.php';

$dokterList = getDokterList($koneksi);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Escape semua input untuk keamanan
    $nomor_rm = mysqli_real_escape_string($koneksi, $_POST['nomor_rm']);
    $nama_pasien = mysqli_real_escape_string($koneksi, $_POST['nama_pasien']);
    $tanggal_lahir = mysqli_real_escape_string($koneksi, $_POST['tanggal_lahir']);
    $jenis_kelamin = mysqli_real_escape_string($koneksi, $_POST['jenis_kelamin']);
    $tgl_masuk = mysqli_real_escape_string($koneksi, $_POST['tgl_masuk']);
    $tgl_keluar = mysqli_real_escape_string($koneksi, $_POST['tgl_keluar']);
    $lama_dirawat = mysqli_real_escape_string($koneksi, $_POST['lama_dirawat']);
    $ruang_rawat = mysqli_real_escape_string($koneksi, $_POST['ruang_rawat']);
    $dpjp_utama = mysqli_real_escape_string($koneksi, $_POST['dpjp_utama']);
    $rawat_bersama = mysqli_real_escape_string($koneksi, $_POST['rawat_bersama']);
    $dpjp_lain_1 = mysqli_real_escape_string($koneksi, $_POST['dpjp_lain_1']);
    $dpjp_lain_2 = mysqli_real_escape_string($koneksi, $_POST['dpjp_lain_2']);
    $dpjp_lain_3 = mysqli_real_escape_string($koneksi, $_POST['dpjp_lain_3']);
    $diagnosa_masuk = mysqli_real_escape_string($koneksi, $_POST['diagnosa_masuk']);
    $riwayat_penyakit = mysqli_real_escape_string($koneksi, $_POST['riwayat_penyakit']);
    $td = mysqli_real_escape_string($koneksi, $_POST['td']);
    $n = mysqli_real_escape_string($koneksi, $_POST['n']);
    $s = mysqli_real_escape_string($koneksi, $_POST['s']);
    $p = mysqli_real_escape_string($koneksi, $_POST['p']);
    $sat_o2 = mysqli_real_escape_string($koneksi, $_POST['sat_o2']);
    $laboratorium = mysqli_real_escape_string($koneksi, $_POST['laboratorium']);
    $penunjang_lain = mysqli_real_escape_string($koneksi, $_POST['penunjang_lain']);
    $diagnosa_utama = mysqli_real_escape_string($koneksi, $_POST['diagnosa_utama']);
    $icd_utama = mysqli_real_escape_string($koneksi, $_POST['icd_utama']);
    $diagnosa_sekunder_1 = mysqli_real_escape_string($koneksi, $_POST['diagnosa_sekunder_1']);
    $icd_sekunder_1 = mysqli_real_escape_string($koneksi, $_POST['icd_sekunder_1']);
    $prosedur_operasi = mysqli_real_escape_string($koneksi, $_POST['prosedur_operasi']);
    $icd_prosedur_1 = mysqli_real_escape_string($koneksi, $_POST['icd_prosedur_1']);
    $icd_prosedur_2 = mysqli_real_escape_string($koneksi, $_POST['icd_prosedur_2']);
    $pengobatan = mysqli_real_escape_string($koneksi, $_POST['pengobatan']);
    $kondisi_pulang = mysqli_real_escape_string($koneksi, $_POST['kondisi_pulang']);
    $instruksi_pulang = mysqli_real_escape_string($koneksi, $_POST['instruksi_pulang']);
    $nama_dpjp_pulang = mysqli_real_escape_string($koneksi, $_POST['nama_dpjp_pulang']);
    $dpjp_pulang_dokter_id = (int) ($_POST['dpjp_pulang_dokter_id'] ?? 0);

    if ($dpjp_pulang_dokter_id > 0) {
        $dokterPulang = getDokterById($koneksi, $dpjp_pulang_dokter_id);
        if ($dokterPulang) {
            $nama_dpjp_pulang = mysqli_real_escape_string($koneksi, $dokterPulang['nama_dokter']);
        }
    }

    // Query super panjang untuk memasukkan semua data
    $query = "INSERT INTO tabel_resume_medis (
        nomor_rm, nama_pasien, tanggal_lahir, jenis_kelamin, tgl_masuk, tgl_keluar, lama_dirawat, ruang_rawat,
        dpjp_utama, rawat_bersama, dpjp_lain_1, dpjp_lain_2, dpjp_lain_3, diagnosa_masuk, riwayat_penyakit,
        td, n, s, p, sat_o2, laboratorium, penunjang_lain, diagnosa_utama, icd_utama,
        diagnosa_sekunder_1, icd_sekunder_1, prosedur_operasi, icd_prosedur_1, icd_prosedur_2,
        pengobatan, kondisi_pulang, instruksi_pulang, nama_dpjp_pulang, dpjp_pulang_dokter_id
    ) VALUES (
        '$nomor_rm', '$nama_pasien', '$tanggal_lahir', '$jenis_kelamin', '$tgl_masuk', '$tgl_keluar', '$lama_dirawat', '$ruang_rawat',
        '$dpjp_utama', '$rawat_bersama', '$dpjp_lain_1', '$dpjp_lain_2', '$dpjp_lain_3', '$diagnosa_masuk', '$riwayat_penyakit',
        '$td', '$n', '$s', '$p', '$sat_o2', '$laboratorium', '$penunjang_lain', '$diagnosa_utama', '$icd_utama',
        '$diagnosa_sekunder_1', '$icd_sekunder_1', '$prosedur_operasi', '$icd_prosedur_1', '$icd_prosedur_2',
        '$pengobatan', '$kondisi_pulang', '$instruksi_pulang', '$nama_dpjp_pulang', " . ($dpjp_pulang_dokter_id > 0 ? $dpjp_pulang_dokter_id : "NULL") . "
    )";

    if (mysqli_query($koneksi, $query)) {
        echo "<script>
                alert('Seluruh data Resume Medis berhasil disimpan!');
                window.location.href = 'eresume.php';
              </script>";
        exit;
    } else {
        echo "<div class='alert alert-danger m-3'>Gagal menyimpan data: " . mysqli_error($koneksi) . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form E-Resume Medis Lengkap</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f0f2f5; }
        .form-container { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); max-width: 900px; margin: auto; }
        .section-title { border-bottom: 2px solid #0d6efd; padding-bottom: 5px; margin-bottom: 20px; margin-top: 30px; font-weight: bold; color: #0d6efd; }
    </style>
</head>
<body>

<div class="container mt-5 mb-5">
    <div class="form-container">
        <h2 class="text-center mb-4">Formulir E-Resume Medis</h2>
        <form action="" method="POST">
            
            <h5 class="section-title">1. Identitas Pasien & Perawatan</h5>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Nomor RM</label>
                    <input type="text" name="nomor_rm" class="form-control" required>
                </div>
                <div class="col-md-5 mb-3">
                    <label class="form-label">Nama Pasien</label>
                    <input type="text" name="nama_pasien" class="form-control" required>
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label">Tgl Lahir</label>
                    <input type="date" name="tanggal_lahir" class="form-control">
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label">J. Kelamin</label>
                    <select name="jenis_kelamin" class="form-select">
                        <option value="L">L</option>
                        <option value="P">P</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Tanggal Masuk</label>
                    <input type="date" name="tgl_masuk" class="form-control" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Tanggal Keluar</label>
                    <input type="date" name="tgl_keluar" class="form-control">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Lama Dirawat (Hari)</label>
                    <input type="number" name="lama_dirawat" class="form-control">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Ruang Rawat</label>
                    <input type="text" name="ruang_rawat" class="form-control">
                </div>
            </div>

            <h5 class="section-title">2. Dokter Penanggung Jawab</h5>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">DPJP Utama</label>
                    <input type="text" name="dpjp_utama" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Rawat Bersama?</label>
                    <select name="rawat_bersama" class="form-select">
                        <option value="Tidak">Tidak</option>
                        <option value="Ya">Ya</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">DPJP Lainnya 1 (Opsional)</label>
                    <input type="text" name="dpjp_lain_1" class="form-control">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">DPJP Lainnya 2 (Opsional)</label>
                    <input type="text" name="dpjp_lain_2" class="form-control">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">DPJP Lainnya 3 (Opsional)</label>
                    <input type="text" name="dpjp_lain_3" class="form-control">
                </div>
            </div>

            <h5 class="section-title">3. Klinis & Pemeriksaan Fisik</h5>
            <div class="mb-3">
                <label class="form-label">Diagnosa Masuk</label>
                <input type="text" name="diagnosa_masuk" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Ringkasan Riwayat Penyakit</label>
                <textarea name="riwayat_penyakit" class="form-control" rows="3"></textarea>
            </div>
            <label class="form-label fw-bold">Pemeriksaan Fisik:</label>
            <div class="row mb-3">
                <div class="col"><input type="text" name="td" class="form-control" placeholder="TD"></div>
                <div class="col"><input type="text" name="n" class="form-control" placeholder="Nadi"></div>
                <div class="col"><input type="text" name="s" class="form-control" placeholder="Suhu"></div>
                <div class="col"><input type="text" name="p" class="form-control" placeholder="Pernapasan"></div>
                <div class="col"><input type="text" name="sat_o2" class="form-control" placeholder="Sat O2"></div>
            </div>

            <h5 class="section-title">4. Penunjang & Diagnosa Akhir</h5>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Laboratorium</label>
                    <textarea name="laboratorium" class="form-control" rows="2"></textarea>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Penunjang Lain</label>
                    <textarea name="penunjang_lain" class="form-control" rows="2"></textarea>
                </div>
            </div>
            <div class="row">
                <div class="col-md-9 mb-3">
                    <label class="form-label text-danger fw-bold">Diagnosa Utama</label>
                    <input type="text" name="diagnosa_utama" class="form-control border-danger" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label text-danger fw-bold">Kode ICD</label>
                    <input type="text" name="icd_utama" class="form-control border-danger">
                </div>
            </div>
            <div class="row">
                <div class="col-md-9 mb-3">
                    <label class="form-label">Diagnosa Sekunder (1)</label>
                    <input type="text" name="diagnosa_sekunder_1" class="form-control">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Kode ICD</label>
                    <input type="text" name="icd_sekunder_1" class="form-control">
                </div>
            </div>

            <h5 class="section-title">5. Prosedur & Pengobatan</h5>
            <div class="row">
                <div class="col-md-8 mb-3">
                    <label class="form-label">Prosedur / Operasi</label>
                    <input type="text" name="prosedur_operasi" class="form-control">
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label">ICD Proc 1</label>
                    <input type="text" name="icd_prosedur_1" class="form-control">
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label">ICD Proc 2</label>
                    <input type="text" name="icd_prosedur_2" class="form-control">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Pengobatan Selama Dirawat</label>
                <textarea name="pengobatan" class="form-control" rows="3"></textarea>
            </div>

            <h5 class="section-title">6. Rencana Pulang</h5>
            <div class="row">
                <div class="col-md-4 mb-3">
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
                <div class="col-md-8 mb-3">
                    <label class="form-label">Instruksi Pulang</label>
                    <input type="text" name="instruksi_pulang" class="form-control">
                </div>
            </div>
            <div class="row mb-4">
                <div class="col-md-6 mb-3">
                    <label class="form-label">DPJP Pemulang dari Master Dokter</label>
                    <select name="dpjp_pulang_dokter_id" class="form-select">
                        <option value="">-- Pilih Dokter --</option>
                        <?php foreach ($dokterList as $dokter): ?>
                            <option value="<?= (int) $dokter['id'] ?>">
                                <?= htmlspecialchars(dokterLabel($dokter)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted">Dipakai untuk tanda tangan dan barcode di cetak resume.</small>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nama DPJP Pemulang Manual</label>
                    <input type="text" name="nama_dpjp_pulang" class="form-control">
                    <small class="text-muted">Dipakai jika dokter belum ada di master.</small>
                </div>
            </div>

            <hr>
            <div class="d-flex justify-content-between mt-4">
                <a href="eresume.php" class="btn btn-secondary px-4">Kembali</a>
                <button type="submit" class="btn btn-primary px-5 fw-bold">Simpan Data Resume Medis</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
