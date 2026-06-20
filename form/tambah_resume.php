<?php
// Tampilkan error jika ada masalah
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include dirname(__DIR__) . '/backend/koneksi.php';
include __DIR__ . '/dokter_helpers.php';
include __DIR__ . '/registrasi_helpers.php';

$dokterList = getDokterList($koneksi);
$registrasiList = getRegistrasiList($koneksi);
$editId = (int) ($_GET['id'] ?? 0);
$editData = null;

if ($editId > 0) {
    $editResult = mysqli_query($koneksi, "SELECT * FROM tabel_resume_medis WHERE id = $editId LIMIT 1");
    $editData = $editResult ? mysqli_fetch_assoc($editResult) : null;
    if (!$editData) {
        die("Data e-resume tidak ditemukan.");
    }
}

$selectedRegistrasiId = (int) ($_GET['registrasi_id'] ?? ($editData['registrasi_id'] ?? 0));
$dokterPreviewList = [];
$registrasiPreviewList = [];

function formValue($data, $key, $default = '') {
    if (!is_array($data)) {
        return htmlspecialchars($default, ENT_QUOTES, 'UTF-8');
    }

    return htmlspecialchars($data[$key] ?? $default, ENT_QUOTES, 'UTF-8');
}

function formSelected($data, $key, $value, $default = '') {
    $current = is_array($data) ? ($data[$key] ?? $default) : $default;
    return (string) $current === (string) $value ? 'selected' : '';
}

foreach ($dokterList as $dokter) {
    $dokterPreviewList[(int) $dokter['id']] = [
        'nama_dokter' => $dokter['nama_dokter'] ?? '',
        'label' => dokterLabel($dokter),
        'tanda_tangan' => $dokter['tanda_tangan'] ?? '',
        'barcode' => qrCodeImg(dokterQrData($dokter), 75),
    ];
}

foreach ($registrasiList as $registrasi) {
    $registrasiPreviewList[(int) $registrasi['id']] = [
        'nomor_rm' => $registrasi['nomor_rm'] ?? '',
        'nama_pasien' => $registrasi['nama_pasien'] ?? '',
        'tanggal_lahir' => $registrasi['tanggal_lahir'] ?? '',
        'jenis_kelamin' => $registrasi['jenis_kelamin'] ?? '',
        'tgl_masuk' => $registrasi['tgl_masuk'] ?? '',
        'penyakit' => $registrasi['penyakit'] ?? '',
    ];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Escape semua input untuk keamanan
    $edit_id = (int) ($_POST['id'] ?? 0);
    $registrasi_id = (int) ($_POST['registrasi_id'] ?? 0);
    $nomor_rm = mysqli_real_escape_string($koneksi, $_POST['nomor_rm']);
    $nama_pasien = mysqli_real_escape_string($koneksi, $_POST['nama_pasien']);
    $tanggal_lahir = mysqli_real_escape_string($koneksi, $_POST['tanggal_lahir']);
    $jenis_kelamin = mysqli_real_escape_string($koneksi, $_POST['jenis_kelamin']);
    $tgl_masuk = mysqli_real_escape_string($koneksi, $_POST['tgl_masuk']);
    $tgl_keluar = mysqli_real_escape_string($koneksi, $_POST['tgl_keluar']);
    $lama_dirawat = mysqli_real_escape_string($koneksi, $_POST['lama_dirawat']);
    $ruang_rawat = mysqli_real_escape_string($koneksi, $_POST['ruang_rawat']);
    $dpjp_utama = mysqli_real_escape_string($koneksi, $_POST['dpjp_utama']);
    $dpjp_utama_dokter_id = (int) ($_POST['dpjp_utama_dokter_id'] ?? 0);
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

    if ($registrasi_id > 0) {
        $registrasi = getRegistrasiById($koneksi, $registrasi_id);
        if ($registrasi) {
            $nomor_rm = mysqli_real_escape_string($koneksi, $registrasi['nomor_rm']);
            $nama_pasien = mysqli_real_escape_string($koneksi, $registrasi['nama_pasien']);
            $tanggal_lahir = mysqli_real_escape_string($koneksi, $registrasi['tanggal_lahir'] ?? '');
            $jenis_kelamin = mysqli_real_escape_string($koneksi, $registrasi['jenis_kelamin'] ?? '');
            $tgl_masuk = mysqli_real_escape_string($koneksi, $registrasi['tgl_masuk']);
            $diagnosa_masuk = mysqli_real_escape_string($koneksi, $registrasi['penyakit']);
        } else {
            $registrasi_id = 0;
        }
    }

    if ($dpjp_utama_dokter_id > 0) {
        $dokterUtama = getDokterById($koneksi, $dpjp_utama_dokter_id);
        if ($dokterUtama) {
            $dpjp_utama = mysqli_real_escape_string($koneksi, $dokterUtama['nama_dokter']);
        } else {
            $dpjp_utama_dokter_id = 0;
        }
    }

    if ($dpjp_pulang_dokter_id > 0) {
        $dokterPulang = getDokterById($koneksi, $dpjp_pulang_dokter_id);
        if ($dokterPulang) {
            $nama_dpjp_pulang = mysqli_real_escape_string($koneksi, $dokterPulang['nama_dokter']);
        }
    }

    $registrasiSqlValue = $registrasi_id > 0 ? $registrasi_id : "NULL";
    $dpjpUtamaSqlValue = $dpjp_utama_dokter_id > 0 ? $dpjp_utama_dokter_id : "NULL";
    $dpjpPulangSqlValue = $dpjp_pulang_dokter_id > 0 ? $dpjp_pulang_dokter_id : "NULL";

    if ($edit_id > 0) {
        $query = "UPDATE tabel_resume_medis SET
            registrasi_id = $registrasiSqlValue,
            nomor_rm = '$nomor_rm',
            nama_pasien = '$nama_pasien',
            tanggal_lahir = '$tanggal_lahir',
            jenis_kelamin = '$jenis_kelamin',
            tgl_masuk = '$tgl_masuk',
            tgl_keluar = '$tgl_keluar',
            lama_dirawat = '$lama_dirawat',
            ruang_rawat = '$ruang_rawat',
            dpjp_utama = '$dpjp_utama',
            dpjp_utama_dokter_id = $dpjpUtamaSqlValue,
            rawat_bersama = '$rawat_bersama',
            dpjp_lain_1 = '$dpjp_lain_1',
            dpjp_lain_2 = '$dpjp_lain_2',
            dpjp_lain_3 = '$dpjp_lain_3',
            diagnosa_masuk = '$diagnosa_masuk',
            riwayat_penyakit = '$riwayat_penyakit',
            td = '$td',
            n = '$n',
            s = '$s',
            p = '$p',
            sat_o2 = '$sat_o2',
            laboratorium = '$laboratorium',
            penunjang_lain = '$penunjang_lain',
            diagnosa_utama = '$diagnosa_utama',
            icd_utama = '$icd_utama',
            diagnosa_sekunder_1 = '$diagnosa_sekunder_1',
            icd_sekunder_1 = '$icd_sekunder_1',
            prosedur_operasi = '$prosedur_operasi',
            icd_prosedur_1 = '$icd_prosedur_1',
            icd_prosedur_2 = '$icd_prosedur_2',
            pengobatan = '$pengobatan',
            kondisi_pulang = '$kondisi_pulang',
            instruksi_pulang = '$instruksi_pulang',
            nama_dpjp_pulang = '$nama_dpjp_pulang',
            dpjp_pulang_dokter_id = $dpjpPulangSqlValue
            WHERE id = $edit_id";
    } else {
        $query = "INSERT INTO tabel_resume_medis (
            registrasi_id, nomor_rm, nama_pasien, tanggal_lahir, jenis_kelamin, tgl_masuk, tgl_keluar, lama_dirawat, ruang_rawat,
            dpjp_utama, dpjp_utama_dokter_id, rawat_bersama, dpjp_lain_1, dpjp_lain_2, dpjp_lain_3, diagnosa_masuk, riwayat_penyakit,
            td, n, s, p, sat_o2, laboratorium, penunjang_lain, diagnosa_utama, icd_utama,
            diagnosa_sekunder_1, icd_sekunder_1, prosedur_operasi, icd_prosedur_1, icd_prosedur_2,
            pengobatan, kondisi_pulang, instruksi_pulang, nama_dpjp_pulang, dpjp_pulang_dokter_id
        ) VALUES (
            $registrasiSqlValue, '$nomor_rm', '$nama_pasien', '$tanggal_lahir', '$jenis_kelamin', '$tgl_masuk', '$tgl_keluar', '$lama_dirawat', '$ruang_rawat',
            '$dpjp_utama', $dpjpUtamaSqlValue, '$rawat_bersama', '$dpjp_lain_1', '$dpjp_lain_2', '$dpjp_lain_3', '$diagnosa_masuk', '$riwayat_penyakit',
            '$td', '$n', '$s', '$p', '$sat_o2', '$laboratorium', '$penunjang_lain', '$diagnosa_utama', '$icd_utama',
            '$diagnosa_sekunder_1', '$icd_sekunder_1', '$prosedur_operasi', '$icd_prosedur_1', '$icd_prosedur_2',
            '$pengobatan', '$kondisi_pulang', '$instruksi_pulang', '$nama_dpjp_pulang', $dpjpPulangSqlValue
        )";
    }

    if (mysqli_query($koneksi, $query)) {
        $successMessage = $edit_id > 0 ? 'Data Resume Medis berhasil diperbarui!' : 'Seluruh data Resume Medis berhasil disimpan!';
        echo "<script>
                alert('$successMessage');
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
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <style>
        body { background-color: #f0f2f5; }
        .form-container { background: white; padding: 40px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); max-width: 900px; margin: auto; }
        .section-title { border-bottom: 2px solid #0d6efd; padding-bottom: 5px; margin-bottom: 20px; margin-top: 30px; font-weight: bold; color: #0d6efd; }
        .doctor-preview { border: 1px solid #dee2e6; border-radius: 8px; padding: 12px; min-height: 128px; background: #f8f9fa; }
        .doctor-preview img { width: 170px; height: 62px; object-fit: contain; display: block; margin-bottom: 10px; background: #fff; border: 1px solid #e9ecef; }
        .doctor-preview .barcode { width: 75px !important; height: 75px !important; margin: 0 auto 10px; display: block; background: #fff; border: none; }
    </style>
</head>
<body>

<div class="container mt-5 mb-5">
    <div class="form-container">
        <h2 class="text-center mb-4"><?= $editData ? 'Edit E-Resume Medis' : 'Formulir E-Resume Medis' ?></h2>
        <form action="" method="POST">
            <input type="hidden" name="id" value="<?= (int) ($editData['id'] ?? 0) ?>">
            
            <h5 class="section-title">1. Identitas Pasien & Perawatan</h5>
            <div class="mb-3">
                <label class="form-label">Pilih Pasien dari Registrasi</label>
                <select name="registrasi_id" id="registrasiSelect" class="form-select" <?= $editData ? '' : 'required' ?>>
                    <option value="">-- Pilih Data Registrasi --</option>
                    <?php foreach ($registrasiList as $registrasi): ?>
                        <?php
                        $labelRegistrasi = trim(($registrasi['nomor_rm'] ?? '') . ' | ' . ($registrasi['nama_pasien'] ?? '') . ' | ' . ($registrasi['penyakit'] ?? ''), ' |');
                        ?>
                        <option value="<?= (int) $registrasi['id'] ?>" <?= (int) $registrasi['id'] === $selectedRegistrasiId ? 'selected' : '' ?>>
                            <?= htmlspecialchars($labelRegistrasi) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="text-muted">Identitas pasien dan diagnosa masuk akan mengikuti data registrasi.</small>
            </div>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Nomor RM</label>
                    <input type="text" name="nomor_rm" id="nomorRmInput" class="form-control" value="<?= formValue($editData, 'nomor_rm') ?>" required readonly>
                </div>
                <div class="col-md-5 mb-3">
                    <label class="form-label">Nama Pasien</label>
                    <input type="text" name="nama_pasien" id="namaPasienInput" class="form-control" value="<?= formValue($editData, 'nama_pasien') ?>" required readonly>
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label">Tgl Lahir</label>
                    <input type="date" name="tanggal_lahir" id="tanggalLahirInput" class="form-control" value="<?= formValue($editData, 'tanggal_lahir') ?>" readonly>
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label">J. Kelamin</label>
                    <select name="jenis_kelamin" id="jenisKelaminSelect" class="form-select">
                        <option value="L" <?= formSelected($editData, 'jenis_kelamin', 'L', 'L') ?>>L</option>
                        <option value="P" <?= formSelected($editData, 'jenis_kelamin', 'P') ?>>P</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Tanggal Masuk</label>
                    <input type="date" name="tgl_masuk" id="tglMasukInput" class="form-control" value="<?= formValue($editData, 'tgl_masuk') ?>" required readonly>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Tanggal Keluar</label>
                    <input type="date" name="tgl_keluar" class="form-control" value="<?= formValue($editData, 'tgl_keluar') ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Lama Dirawat (Hari)</label>
                    <input type="number" name="lama_dirawat" class="form-control" value="<?= formValue($editData, 'lama_dirawat') ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Ruang Rawat</label>
                    <input type="text" name="ruang_rawat" class="form-control" value="<?= formValue($editData, 'ruang_rawat') ?>">
                </div>
            </div>

            <h5 class="section-title">2. Dokter Penanggung Jawab</h5>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">DPJP Utama</label>
                    <input type="text" id="dpjpUtamaSearch" class="form-control mb-2" placeholder="Cari nama / nomor / spesialis dokter">
                    <select name="dpjp_utama_dokter_id" id="dpjpUtamaSelect" class="form-select" required>
                        <option value="">-- Pilih Dokter --</option>
                        <?php foreach ($dokterList as $dokter): ?>
                            <option value="<?= (int) $dokter['id'] ?>" data-search="<?= htmlspecialchars(strtolower(dokterLabel($dokter)), ENT_QUOTES, 'UTF-8') ?>" <?= (int) ($editData['dpjp_utama_dokter_id'] ?? 0) === (int) $dokter['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars(dokterLabel($dokter)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="hidden" name="dpjp_utama" id="dpjpUtamaName">
                    <small class="text-muted">Pilihan ini dipakai untuk tanda tangan dan barcode di cetak resume.</small>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Rawat Bersama?</label>
                    <select name="rawat_bersama" class="form-select">
                        <option value="Tidak" <?= formSelected($editData, 'rawat_bersama', 'Tidak', 'Tidak') ?>>Tidak</option>
                        <option value="Ya" <?= formSelected($editData, 'rawat_bersama', 'Ya') ?>>Ya</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Preview Tanda Tangan & Barcode DPJP Utama</label>
                    <div class="doctor-preview" id="dpjpUtamaPreview">
                        <span class="text-muted">Pilih DPJP Utama dari master dokter.</span>
                    </div>
                </div>
                <div class="col-md-6 mb-3"></div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">DPJP Lainnya 1 (Opsional)</label>
                    <input type="text" name="dpjp_lain_1" class="form-control" value="<?= formValue($editData, 'dpjp_lain_1') ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">DPJP Lainnya 2 (Opsional)</label>
                    <input type="text" name="dpjp_lain_2" class="form-control" value="<?= formValue($editData, 'dpjp_lain_2') ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">DPJP Lainnya 3 (Opsional)</label>
                    <input type="text" name="dpjp_lain_3" class="form-control" value="<?= formValue($editData, 'dpjp_lain_3') ?>">
                </div>
            </div>

            <h5 class="section-title">3. Klinis & Pemeriksaan Fisik</h5>
            <div class="mb-3">
                <label class="form-label">Diagnosa Masuk</label>
                <input type="text" name="diagnosa_masuk" id="diagnosaMasukInput" class="form-control" value="<?= formValue($editData, 'diagnosa_masuk') ?>" required readonly>
            </div>
            <div class="mb-3">
                <label class="form-label">Ringkasan Riwayat Penyakit</label>
                <textarea name="riwayat_penyakit" class="form-control" rows="3"><?= formValue($editData, 'riwayat_penyakit') ?></textarea>
            </div>
            <label class="form-label fw-bold">Pemeriksaan Fisik:</label>
            <div class="row mb-3">
                <div class="col"><input type="text" name="td" class="form-control" placeholder="TD" value="<?= formValue($editData, 'td') ?>"></div>
                <div class="col"><input type="text" name="n" class="form-control" placeholder="Nadi" value="<?= formValue($editData, 'n') ?>"></div>
                <div class="col"><input type="text" name="s" class="form-control" placeholder="Suhu" value="<?= formValue($editData, 's') ?>"></div>
                <div class="col"><input type="text" name="p" class="form-control" placeholder="Pernapasan" value="<?= formValue($editData, 'p') ?>"></div>
                <div class="col"><input type="text" name="sat_o2" class="form-control" placeholder="Sat O2" value="<?= formValue($editData, 'sat_o2') ?>"></div>
            </div>

            <h5 class="section-title">4. Penunjang & Diagnosa Akhir</h5>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Laboratorium</label>
                    <textarea name="laboratorium" class="form-control" rows="2"><?= formValue($editData, 'laboratorium') ?></textarea>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Penunjang Lain</label>
                    <textarea name="penunjang_lain" class="form-control" rows="2"><?= formValue($editData, 'penunjang_lain') ?></textarea>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label class="form-label text-danger fw-bold">Diagnosa Utama & Kode ICD</label>
                    <div class="d-flex gap-2 mb-2">
                        <div class="flex-grow-1">
                            <select id="icdSearch" class="form-select"></select>
                        </div>
                        <button type="button" class="btn btn-danger fw-bold" id="btnAddDiagnosa">Add</button>
                    </div>
                    <ul class="list-group mb-2" id="diagnosaList">
                        <!-- List of diseases will be added here -->
                    </ul>
                    <input type="hidden" name="diagnosa_utama" id="diagnosaUtamaHidden" value="<?= formValue($editData, 'diagnosa_utama') ?>" required>
                    <input type="hidden" name="icd_utama" id="icdUtamaHidden" value="<?= formValue($editData, 'icd_utama') ?>">
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label class="form-label">Diagnosa Sekunder & Kode ICD</label>
                    <div class="d-flex gap-2 mb-2">
                        <div class="flex-grow-1">
                            <select id="icdSekunderSearch" class="form-select"></select>
                        </div>
                        <button type="button" class="btn btn-primary fw-bold" id="btnAddSekunder">Add</button>
                    </div>
                    <ul class="list-group mb-2" id="sekunderList">
                        <!-- List of diseases will be added here -->
                    </ul>
                    <input type="hidden" name="diagnosa_sekunder_1" id="diagnosaSekunderHidden" value="<?= formValue($editData, 'diagnosa_sekunder_1') ?>">
                    <input type="hidden" name="icd_sekunder_1" id="icdSekerunderHidden" value="<?= formValue($editData, 'icd_sekunder_1') ?>">
                </div>
            </div>

            <h5 class="section-title">5. Prosedur & Pengobatan</h5>
            <div class="row">
                <div class="col-md-12 mb-3">
                    <label class="form-label">Prosedur / Operasi & Kode ICD (ICD-9-CM / ICD-10-PCS)</label>
                    <div class="d-flex gap-2 mb-2">
                        <div class="flex-grow-1">
                            <select id="icdProsedurSearch" class="form-select"></select>
                        </div>
                        <button type="button" class="btn btn-success fw-bold" id="btnAddProsedur">Add</button>
                    </div>
                    <ul class="list-group mb-2" id="prosedurList">
                        <!-- List of procedures will be added here -->
                    </ul>
                    <input type="hidden" name="prosedur_operasi" id="prosedurHidden" value="<?= formValue($editData, 'prosedur_operasi') ?>">
                    <input type="hidden" name="icd_prosedur_1" id="icdProsedurHidden" value="<?= formValue($editData, 'icd_prosedur_1') ?>">
                    <input type="hidden" name="icd_prosedur_2" value="<?= formValue($editData, 'icd_prosedur_2') ?>">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Pengobatan Selama Dirawat</label>
                <textarea name="pengobatan" class="form-control" rows="3"><?= formValue($editData, 'pengobatan') ?></textarea>
            </div>

            <h5 class="section-title">6. Rencana Pulang</h5>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Kondisi Pulang</label>
                    <select name="kondisi_pulang" class="form-select" required>
                        <option value="">-- Pilih Kondisi --</option>
                        <option value="Diijinkan Pulang" <?= formSelected($editData, 'kondisi_pulang', 'Diijinkan Pulang') ?>>Diijinkan Pulang</option>
                        <option value="Dirujuk" <?= formSelected($editData, 'kondisi_pulang', 'Dirujuk') ?>>Dirujuk</option>
                        <option value="Atas Permintaan Sendiri" <?= formSelected($editData, 'kondisi_pulang', 'Atas Permintaan Sendiri') ?>>Atas Permintaan Sendiri</option>
                        <option value="Meninggal" <?= formSelected($editData, 'kondisi_pulang', 'Meninggal') ?>>Meninggal</option>
                        <option value="Melarikan Diri" <?= formSelected($editData, 'kondisi_pulang', 'Melarikan Diri') ?>>Melarikan Diri</option>
                    </select>
                </div>
                <div class="col-md-8 mb-3">
                    <label class="form-label">Instruksi Pulang</label>
                    <input type="text" name="instruksi_pulang" class="form-control" value="<?= formValue($editData, 'instruksi_pulang') ?>">
                </div>
            </div>
            <div class="row mb-4">
                <div class="col-md-6 mb-3">
                    <label class="form-label">DPJP Pemulang dari Master Dokter</label>
                    <select name="dpjp_pulang_dokter_id" class="form-select">
                        <option value="">-- Pilih Dokter --</option>
                        <?php foreach ($dokterList as $dokter): ?>
                            <option value="<?= (int) $dokter['id'] ?>" <?= (int) ($editData['dpjp_pulang_dokter_id'] ?? 0) === (int) $dokter['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars(dokterLabel($dokter)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted">Dipakai untuk tanda tangan dan barcode di cetak resume.</small>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nama DPJP Pemulang Manual</label>
                    <input type="text" name="nama_dpjp_pulang" class="form-control" value="<?= formValue($editData, 'nama_dpjp_pulang') ?>">
                    <small class="text-muted">Dipakai jika dokter belum ada di master.</small>
                </div>
            </div>

            <hr>
            <div class="d-flex justify-content-between mt-4">
                <a href="eresume.php" class="btn btn-secondary px-4">Kembali</a>
                <button type="submit" class="btn btn-primary px-5 fw-bold"><?= $editData ? 'Update Data Resume Medis' : 'Simpan Data Resume Medis' ?></button>
            </div>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    const registrasiData = <?= json_encode($registrasiPreviewList, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    const resumePasienData = <?= json_encode([
        'nomor_rm' => $editData['nomor_rm'] ?? '',
        'nama_pasien' => $editData['nama_pasien'] ?? '',
        'tanggal_lahir' => $editData['tanggal_lahir'] ?? '',
        'jenis_kelamin' => $editData['jenis_kelamin'] ?? '',
        'tgl_masuk' => $editData['tgl_masuk'] ?? '',
        'penyakit' => $editData['diagnosa_masuk'] ?? '',
    ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    const dokterData = <?= json_encode($dokterPreviewList, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    const registrasiSelect = document.getElementById('registrasiSelect');
    const nomorRmInput = document.getElementById('nomorRmInput');
    const namaPasienInput = document.getElementById('namaPasienInput');
    const tanggalLahirInput = document.getElementById('tanggalLahirInput');
    const jenisKelaminSelect = document.getElementById('jenisKelaminSelect');
    const tglMasukInput = document.getElementById('tglMasukInput');
    const diagnosaMasukInput = document.getElementById('diagnosaMasukInput');
    const dpjpUtamaSearch = document.getElementById('dpjpUtamaSearch');
    const dpjpUtamaSelect = document.getElementById('dpjpUtamaSelect');
    const dpjpUtamaName = document.getElementById('dpjpUtamaName');
    const dpjpUtamaPreview = document.getElementById('dpjpUtamaPreview');

    function escapeHtml(value) {
        return String(value).replace(/[&<>"']/g, function (char) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            }[char];
        });
    }

    function updateRegistrasiFields() {
        const selectedId = registrasiSelect.value;
        const registrasi = registrasiData[selectedId] || resumePasienData;

        nomorRmInput.value = registrasi ? registrasi.nomor_rm : '';
        namaPasienInput.value = registrasi ? registrasi.nama_pasien : '';
        tanggalLahirInput.value = registrasi ? registrasi.tanggal_lahir : '';
        jenisKelaminSelect.value = registrasi ? registrasi.jenis_kelamin : 'L';
        tglMasukInput.value = registrasi ? registrasi.tgl_masuk : '';
        diagnosaMasukInput.value = registrasi ? registrasi.penyakit : '';
    }

    function updateDpjpUtamaPreview() {
        const selectedId = dpjpUtamaSelect.value;
        const dokter = dokterData[selectedId];

        if (!dokter) {
            dpjpUtamaName.value = '';
            dpjpUtamaPreview.innerHTML = '<span class="text-muted">Pilih DPJP Utama dari master dokter.</span>';
            return;
        }

        dpjpUtamaName.value = dokter.nama_dokter;
        const namaDokter = escapeHtml(dokter.nama_dokter);
        const labelDokter = escapeHtml(dokter.label);
        const tandaTangan = encodeURI(dokter.tanda_tangan);
        const signatureHtml = dokter.tanda_tangan
            ? '<img src="../' + tandaTangan + '" alt="Tanda tangan ' + namaDokter + '">'
            : '<div class="text-muted mb-2">Tanda tangan belum ada di master dokter.</div>';

        dpjpUtamaPreview.innerHTML = signatureHtml + '<div>' + dokter.barcode + '</div><small class="text-muted">' + labelDokter + '</small>';
    }

    dpjpUtamaSearch.addEventListener('input', function () {
        const keyword = this.value.trim().toLowerCase();

        Array.from(dpjpUtamaSelect.options).forEach(function (option) {
            if (option.value === '') {
                option.hidden = false;
                return;
            }

            option.hidden = keyword !== '' && !option.dataset.search.includes(keyword);
        });
    });

    dpjpUtamaSelect.addEventListener('change', updateDpjpUtamaPreview);
    registrasiSelect.addEventListener('change', updateRegistrasiFields);
    updateRegistrasiFields();
    updateDpjpUtamaPreview();

    $(document).ready(function() {
        $('#icdSearch').select2({
            theme: 'bootstrap-5',
            placeholder: 'Cari Kode ICD / Penyakit (ketik min. 3 huruf)...',
            minimumInputLength: 3,
            ajax: {
                url: 'https://clinicaltables.nlm.nih.gov/api/icd10cm/v3/search',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        terms: params.term,
                        sf: 'code,name',
                        df: 'code,name'
                    };
                },
                processResults: function (data) {
                    return {
                        results: data[3].map(function(item) {
                            return {
                                id: item[0],
                                text: item[0] + ' - ' + item[1],
                                code: item[0],
                                name: item[1]
                            };
                        })
                    };
                },
                cache: true
            }
        });

        $('#icdSekunderSearch').select2({
            theme: 'bootstrap-5',
            placeholder: 'Cari Kode ICD / Penyakit (ketik min. 3 huruf)...',
            minimumInputLength: 3,
            ajax: {
                url: 'https://clinicaltables.nlm.nih.gov/api/icd10cm/v3/search',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        terms: params.term,
                        sf: 'code,name',
                        df: 'code,name'
                    };
                },
                processResults: function (data) {
                    return {
                        results: data[3].map(function(item) {
                            return {
                                id: item[0],
                                text: item[0] + ' - ' + item[1],
                                code: item[0],
                                name: item[1]
                            };
                        })
                    };
                },
                cache: true
            }
        });

        $('#icdProsedurSearch').select2({
            theme: 'bootstrap-5',
            placeholder: 'Cari Kode Prosedur / Operasi (ketik min. 3 huruf)...',
            minimumInputLength: 3,
            ajax: {
                url: 'https://clinicaltables.nlm.nih.gov/api/icd10pcs/v3/search',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        terms: params.term,
                        sf: 'code,name',
                        df: 'code,name'
                    };
                },
                processResults: function (data) {
                    return {
                        results: data[3].map(function(item) {
                            return {
                                id: item[0],
                                text: item[0] + ' - ' + item[1],
                                code: item[0],
                                name: item[1]
                            };
                        })
                    };
                },
                cache: true
            }
        });

        let diagnosaData = [];
        let sekunderData = [];
        let prosedurData = [];

        // Helper to load existing data
        function loadExistingData(diagStr, icdStr, targetArr, renderFunc) {
            if (diagStr) {
                const diagArray = diagStr.split(' ; ');
                const icdArray = icdStr ? icdStr.split(' ; ') : [];
                for (let i = 0; i < diagArray.length; i++) {
                    if (diagArray[i].trim() !== '') {
                        targetArr.push({
                            code: icdArray[i] ? icdArray[i].trim() : '',
                            name: diagArray[i].trim()
                        });
                    }
                }
                renderFunc();
            }
        }

        // Parsing existing data
        loadExistingData($('#diagnosaUtamaHidden').val(), $('#icdUtamaHidden').val(), diagnosaData, renderDiagnosaList);
        loadExistingData($('#diagnosaSekunderHidden').val(), $('#icdSekerunderHidden').val(), sekunderData, renderSekunderList);
        loadExistingData($('#prosedurHidden').val(), $('#icdProsedurHidden').val(), prosedurData, renderProsedurList);
        
        $('#btnAddDiagnosa').click(function() {
            const selectedData = $('#icdSearch').select2('data');
            if (selectedData && selectedData.length > 0) {
                const item = selectedData[0];
                diagnosaData.push({ code: item.code || '', name: item.name || item.text });
                $('#icdSearch').val(null).trigger('change');
                renderDiagnosaList();
            } else { alert('Silakan cari dan pilih Diagnosa terlebih dahulu.'); }
        });

        $('#btnAddSekunder').click(function() {
            const selectedData = $('#icdSekunderSearch').select2('data');
            if (selectedData && selectedData.length > 0) {
                const item = selectedData[0];
                sekunderData.push({ code: item.code || '', name: item.name || item.text });
                $('#icdSekunderSearch').val(null).trigger('change');
                renderSekunderList();
            } else { alert('Silakan cari dan pilih Diagnosa Sekunder terlebih dahulu.'); }
        });

        $('#btnAddProsedur').click(function() {
            const selectedData = $('#icdProsedurSearch').select2('data');
            if (selectedData && selectedData.length > 0) {
                const item = selectedData[0];
                prosedurData.push({ code: item.code || '', name: item.name || item.text });
                $('#icdProsedurSearch').val(null).trigger('change');
                renderProsedurList();
            } else { alert('Silakan cari dan pilih Prosedur terlebih dahulu.'); }
        });

        window.removeDiagnosa = function(index) { diagnosaData.splice(index, 1); renderDiagnosaList(); };
        window.removeSekunder = function(index) { sekunderData.splice(index, 1); renderSekunderList(); };
        window.removeProsedur = function(index) { prosedurData.splice(index, 1); renderProsedurList(); };

        function renderList(dataArr, listElementId, hiddenDiagId, hiddenIcdId, removeFuncName) {
            $('#' + listElementId).empty();
            let diagStrings = [];
            let icdStrings = [];

            dataArr.forEach(function(item, index) {
                const displayCode = item.code ? escapeHtml(item.code) + ' - ' : '';
                $('#' + listElementId).append(
                    '<li class="list-group-item d-flex justify-content-between align-items-center">' +
                    '<span><strong>' + escapeHtml(item.code) + '</strong> ' + (item.code ? '-' : '') + ' ' + escapeHtml(item.name) + '</span>' +
                    '<button type="button" class="btn btn-sm btn-outline-danger" onclick="' + removeFuncName + '(' + index + ')">Hapus</button>' +
                    '</li>'
                );
                diagStrings.push(item.name);
                icdStrings.push(item.code);
            });

            $('#' + hiddenDiagId).val(diagStrings.join(' ; '));
            $('#' + hiddenIcdId).val(icdStrings.join(' ; '));
        }

        function renderDiagnosaList() { renderList(diagnosaData, 'diagnosaList', 'diagnosaUtamaHidden', 'icdUtamaHidden', 'removeDiagnosa'); }
        function renderSekunderList() { renderList(sekunderData, 'sekunderList', 'diagnosaSekunderHidden', 'icdSekerunderHidden', 'removeSekunder'); }
        function renderProsedurList() { renderList(prosedurData, 'prosedurList', 'prosedurHidden', 'icdProsedurHidden', 'removeProsedur'); }
    });
</script>
</body>
</html>
