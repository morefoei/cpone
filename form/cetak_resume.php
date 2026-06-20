<?php
include dirname(__DIR__) . '/backend/koneksi.php';
include __DIR__ . '/dokter_helpers.php';

if (!isset($_GET['id'])) {
    die("ID tidak ditemukan");
}

$id = mysqli_real_escape_string($koneksi, $_GET['id']);
$query = "SELECT * FROM tabel_resume_medis WHERE id = '$id'";
$result = mysqli_query($koneksi, $query);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    die("Data tidak ditemukan");
}

$dokterUtama = getDokterById($koneksi, $data['dpjp_utama_dokter_id'] ?? 0);
$dokterPulang = getDokterById($koneksi, $data['dpjp_pulang_dokter_id'] ?? 0);
$dokterTtd = $dokterUtama ?: $dokterPulang;
$namaDokterTtd = $dokterTtd['nama_dokter'] ?? ($data['dpjp_utama'] ?: ($data['nama_dpjp_pulang'] ?: 'Nama DPJP'));
$barcodeDokter = $dokterTtd
    ? dokterLabel($dokterTtd)
    : ($data['dpjp_utama'] ?: ($data['nama_dpjp_pulang'] ?: 'Nama DPJP'));

// Fungsi kecil untuk mengecek checkbox
function isChecked($db_value, $target_value) {
    return (strcasecmp(trim($db_value ?? ''), $target_value) == 0) ? 'checked' : '';
}

function h($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function multiline($value) {
    return nl2br(h($value));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Resume Medis - <?= htmlspecialchars($data['nama_pasien']) ?></title>
    <style>
        @page { size: letter; margin: 0; }

        body { font-family: "Times New Roman", Times, serif; font-size: 19px; line-height: 1.18; color: #000; background: #525659; margin: 0; padding: 20px; }
        * { box-sizing: border-box; }

        .page { background: white; width: 8.5in; min-height: 11in; margin: 0 auto 20px; padding: 0.58in 0.52in 0.45in; box-shadow: 0 0 10px rgba(0,0,0,0.5); position: relative; page-break-after: always; }
        .page:last-of-type { page-break-after: auto; }
        .doc-code { text-align: right; font-family: Arial, sans-serif; font-size: 15px; margin: 0 0 0.45in; padding-right: 0.45in; }

        .form-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .form-table td { border: 1px solid #000; padding: 3px 7px; vertical-align: top; }
        .col-label { width: 36%; }
        .col-main { width: 30%; }
        .col-side { width: 34%; }
        .logo-cell { height: 125px; vertical-align: middle !important; text-align: left; }
        .header-logo { width: 210px; max-width: 92%; height: auto; display: block; margin: 0 auto; }
        .patient-row td { height: 37px; }
        .birth-row td { height: 61px; }
        .title-cell { font-size: 26px; font-weight: bold; text-align: center; vertical-align: middle !important; }
        .label { font-weight: bold; }
        .care-cell { height: 195px; }
        .care-grid { display: grid; grid-template-columns: 160px 1fr 190px 120px; column-gap: 18px; row-gap: 5px; }
        .care-grid .colon { display: inline-block; width: 16px; text-align: center; }
        .dpjp-list { margin-left: 166px; line-height: 1.45; }
        .tall-riwayat { height: 104px; }
        .row-fisik { height: 60px; }
        .tall-lab { height: 134px; }
        .tall-penunjang { height: 105px; }
        .diag-row { height: 40px; }
        .diag-secondary { height: 112px; }
        .procedure-row { height: 62px; }
        .medicine-row { height: 150px; }
        .condition-row { height: 62px; }
        .instruction-row { height: 40px; }
        .vitals { display: flex; gap: 34px; white-space: nowrap; }
        .checkbox-custom { display: inline-block; width: 15px; height: 15px; border: 1px solid #000; margin: 0 3px 0 0; position: relative; top: 2px; }
        .checkbox-custom.checked::after { content: '✓'; position: absolute; top: -7px; left: 1px; font-size: 21px; line-height: 1; }
        .checkbox-line { white-space: nowrap; }
        .signature-area { display: grid; grid-template-columns: 1fr 240px; margin-top: 78px; align-items: start; }
        .date-line { padding-top: 34px; letter-spacing: 1px; }
        .signature-box { text-align: center; font-family: Arial, sans-serif; font-size: 19px; }
        .signature-image { width: 170px; height: 62px; object-fit: contain; display: block; margin: 8px auto 6px; }
        .signature-placeholder { width: 170px; height: 62px; margin: 8px auto 6px; border-bottom: 1px solid #000; }
        .barcode-wrap { width: 100%; height: auto; margin: 0 auto 10px; display: flex; align-items: center; justify-content: center; }
        .barcode { width: 75px !important; height: 75px !important; }
        .doctor-meta { font-size: 12px; margin-top: 3px; }

        @media print {
            body { background: white; padding: 0; }
            .page { margin: 0; box-shadow: none; }
            .no-print { display: none !important; }
        }

        /* Tombol Aksi Melayang */
        .btn-action { position: fixed; right: 20px; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold; border: none; cursor: pointer; box-shadow: 0 4px 6px rgba(0,0,0,0.3); z-index: 1000; display: inline-block; }
        .btn-print { top: 20px; background: #0d6efd; }
        .btn-print:hover { background: #0b5c30; }
        .btn-back { top: 70px; background: #6c757d; }
        .btn-back:hover { background: #5c636a; }
    </style>
</head>
<body>

<button class="btn-action btn-print no-print" onclick="window.print()">🖨️ Cetak / Simpan PDF</button>
<a href="detail_resume.php?id=<?= urlencode($id) ?>" class="btn-action btn-back no-print">← Kembali</a>

<div class="page">
    <div class="doc-code">RI 02/2020/I</div>

    <table class="form-table">
        <colgroup>
            <col class="col-label">
            <col class="col-main">
            <col class="col-side">
        </colgroup>
        <tr class="patient-row">
            <td rowspan="3" class="logo-cell">
                <img src="../assets/img/logo-ueu-unggul.png" alt="Universitas Esa Unggul" class="header-logo">
            </td>
            <td>Nomor RM</td>
            <td><?= h($data['nomor_rm']) ?></td>
        </tr>
        <tr class="patient-row">
            <td>Nama Pasien</td>
            <td><?= h($data['nama_pasien']) ?></td>
        </tr>
        <tr class="birth-row">
            <td>Tanggal Lahir</td>
            <td><?= h($data['tanggal_lahir']) ?></td>
        </tr>
        <tr class="patient-row">
            <td class="title-cell">RESUME MEDIS</td>
            <td>Jenis Kelamin</td>
            <td>
                <span class="checkbox-custom <?= isChecked($data['jenis_kelamin'], 'L') ?>"></span>L
                <span class="checkbox-custom <?= isChecked($data['jenis_kelamin'], 'P') ?>"></span>P
            </td>
        </tr>
        <tr>
            <td colspan="3" class="care-cell">
                <div class="care-grid">
                    <div>Tanggal Masuk <span class="colon">:</span></div>
                    <div><?= h($data['tgl_masuk']) ?></div>
                    <div>Tanggal Keluar: <?= h($data['tgl_keluar']) ?></div>
                    <div>Lama Dirawat: <?= h($data['lama_dirawat']) ?> Hari</div>
                    <div>Ruang Rawat <span class="colon">:</span></div>
                    <div><?= h($data['ruang_rawat']) ?></div>
                    <div></div>
                    <div></div>
                    <div>DPJP Utama <span class="colon">:</span></div>
                    <div><?= h($data['dpjp_utama']) ?></div>
                    <div></div>
                    <div></div>
                    <div>Rawat Bersama <span class="colon">:</span></div>
                    <div class="checkbox-line">
                        <span class="checkbox-custom <?= isChecked($data['rawat_bersama'], 'Ya') ?>"></span>Ya
                        <span class="checkbox-custom <?= isChecked($data['rawat_bersama'], 'Tidak') ?>"></span> Tidak
                    </div>
                    <div></div>
                    <div></div>
                </div>
                <div class="dpjp-list">
                    1. <?= h($data['dpjp_lain_1']) ?><br>
                    2. <?= h($data['dpjp_lain_2']) ?><br>
                    3. <?= h($data['dpjp_lain_3']) ?>
                </div>
                <div>Diagnosa Masuk : <?= h($data['diagnosa_masuk']) ?></div>
            </td>
        </tr>
        <tr class="tall-riwayat">
            <td class="label">Ringkasan Riwayat Penyakit</td>
            <td colspan="2"><?= multiline($data['riwayat_penyakit']) ?></td>
        </tr>
        <tr class="row-fisik">
            <td class="label">Pemeriksaan Fisik</td>
            <td colspan="2">
                <div class="vitals">
                    <span>TD: <?= h($data['td']) ?></span>
                    <span>N: <?= h($data['n']) ?></span>
                    <span>S: <?= h($data['s']) ?></span>
                    <span>P: <?= h($data['p']) ?></span>
                    <span>Sat O<sub>2</sub>: <?= h($data['sat_o2']) ?></span>
                </div>
            </td>
        </tr>
        <tr class="tall-lab">
            <td class="label">Laboratorium</td>
            <td colspan="2"><?= multiline($data['laboratorium']) ?></td>
        </tr>
        <tr class="tall-penunjang">
            <td class="label">Penunjang Lain</td>
            <td colspan="2"><?= multiline($data['penunjang_lain']) ?></td>
        </tr>
        <tr class="diag-row">
            <td class="label">Diagnosa Utama</td>
            <td><?= h($data['diagnosa_utama']) ?></td>
            <td>Kode ICD: <?= h($data['icd_utama']) ?></td>
        </tr>
        <tr class="diag-secondary">
            <td class="label">Diagnosa Sekunder</td>
            <td><?= h($data['diagnosa_sekunder_1']) ?></td>
            <td>
                Kode ICD: <?= h($data['icd_sekunder_1']) ?><br>
                Kode ICD:<br>
                Kode ICD:<br>
                Kode ICD:
            </td>
        </tr>
    </table>
</div>

<div class="page">
    <div class="doc-code">RI 02/2020/I</div>

    <table class="form-table">
        <colgroup>
            <col class="col-label">
            <col class="col-main">
            <col class="col-side">
        </colgroup>
        <tr class="procedure-row">
            <td class="label">Prosedur/Operasi</td>
            <td><?= h($data['prosedur_operasi']) ?></td>
            <td>
                Kode ICD: <?= h($data['icd_prosedur_1']) ?><br>
                Kode ICD: <?= h($data['icd_prosedur_2']) ?>
            </td>
        </tr>
        <tr class="medicine-row">
            <td class="label">Pengobatan Selama Dirawat</td>
            <td colspan="2"><?= multiline($data['pengobatan']) ?></td>
        </tr>
        <tr class="condition-row">
            <td class="label">Kondisi Pulang</td>
            <td colspan="2" class="checkbox-line">
                <span class="checkbox-custom <?= isChecked($data['kondisi_pulang'], 'Diijinkan Pulang') ?>"></span>Diijinkan Pulang
                <span class="checkbox-custom <?= isChecked($data['kondisi_pulang'], 'Dirujuk') ?>"></span>Dirujuk
                <span class="checkbox-custom <?= isChecked($data['kondisi_pulang'], 'Atas Permintaan Sendiri') ?>"></span>Atas Permintaan Sendiri<br>
                <span class="checkbox-custom <?= isChecked($data['kondisi_pulang'], 'Meninggal') ?>"></span>Meninggal
                <span class="checkbox-custom <?= isChecked($data['kondisi_pulang'], 'Melarikan Diri') ?>"></span> Melarikan Diri
            </td>
        </tr>
        <tr class="instruction-row">
            <td class="label">Instruksi Pulang</td>
            <td colspan="2"><?= multiline($data['instruksi_pulang']) ?></td>
        </tr>
    </table>

    <div class="signature-area">
        <div class="date-line">..........................,..........................</div>
        <div class="signature-box">
            <div>Tanda Tangan</div>
            <?php if ($dokterTtd && !empty($dokterTtd['tanda_tangan'])): ?>
                <img src="../<?= h($dokterTtd['tanda_tangan']) ?>" alt="Tanda tangan <?= h($namaDokterTtd) ?>" class="signature-image">
            <?php else: ?>
                <div class="signature-placeholder"></div>
            <?php endif; ?>
            <div class="barcode-wrap"><?= qrCodeImg($barcodeDokter, 75) ?></div>
            <div>(<?= h($namaDokterTtd) ?>)</div>
            <?php if ($dokterTtd): ?>
                <div class="doctor-meta"><?= h($dokterTtd['nomor_dokter']) ?> | <?= h($dokterTtd['jenis_dokter']) ?><?= !empty($dokterTtd['spesialis']) ? ' - ' . h($dokterTtd['spesialis']) : '' ?></div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    // Hilangkan komentar pada baris di bawah ini jika ingin dialog print otomatis muncul
    // window.onload = function() { window.print(); }
</script>

</body>
</html>
