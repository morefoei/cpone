<?php
include dirname(__DIR__) . '/backend/koneksi.php';

if (!isset($_GET['id'])) {
    die("ID tidak ditemukan");
}

$id = mysqli_real_escape_string($koneksi, $_GET['id']);
$query = "SELECT * FROM tabel_resume_medis WHERE id = '$id'";
$result = mysqli_query($koneksi, $query);
$data = mysqli_fetch_assoc($result);

// Fungsi kecil untuk mengecek checkbox
function isChecked($db_value, $target_value) {
    return (strcasecmp(trim($db_value ?? ''), $target_value) == 0) ? 'checked' : '';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Resume Medis - <?= htmlspecialchars($data['nama_pasien']) ?></title>
    <style>
        /* Pengaturan kertas A4 dan font */
        body { font-family: Arial, sans-serif; font-size: 13px; line-height: 1.4; color: #000; background: #525659; margin: 0; padding: 20px; }
        * { box-sizing: border-box; }
        
        /* Area kertas (Template) */
        .page { background: white; width: 21cm; min-height: 29.7cm; margin: 0 auto; padding: 1.5cm; box-shadow: 0 0 10px rgba(0,0,0,0.5); position: relative; }
        
        /* Desain Tabel dan Kotak seperti PDF asli */
        .header-table { width: 100%; margin-bottom: 10px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .header-table td { vertical-align: top; }
        .title-doc { font-size: 18px; font-weight: bold; text-align: center; text-decoration: underline; margin-bottom: 5px; }
        .kode-doc { font-size: 11px; text-align: right; font-weight: bold; }
        
        .box-section { border: 1px solid #000; margin-bottom: 10px; padding: 8px; }
        .box-title { font-weight: bold; margin-bottom: 5px; }
        
        .row-grid { display: flex; flex-wrap: wrap; }
        .col-half { width: 50%; padding-right: 10px; }
        .col-full { width: 100%; }
        
        table.identitas { width: 100%; font-size: 13px; }
        table.identitas td { padding: 3px 0; vertical-align: top; }
        .label { width: 130px; font-weight: bold; }
        .titik-dua { width: 15px; }
        
        .icd-box { float: right; border: 1px solid #000; padding: 2px 10px; font-weight: normal; font-size: 12px; }
        
        .checkbox-custom { display: inline-block; width: 12px; height: 12px; border: 1px solid #000; margin-right: 5px; position: relative; top: 2px; }
        .checkbox-custom.checked::after { content: '✔'; position: absolute; top: -3px; left: 1px; font-size: 12px; }

        /* Pengaturan Khusus saat tekan Ctrl+P (Print) */
        @media print {
            body { background: white; padding: 0; }
            .page { width: 100%; min-height: auto; margin: 0; padding: 0; box-shadow: none; }
            /* Sembunyikan elemen yang tidak perlu dicetak */
            .no-print { display: none !important; }
        }

        /* Tombol Cetak Melayang */
        .btn-print { position: fixed; top: 20px; right: 20px; background: #0d6efd; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold; border: none; cursor: pointer; box-shadow: 0 4px 6px rgba(0,0,0,0.3); z-index: 1000;}
        .btn-print:hover { background: #0b5c30; }
    </style>
</head>
<body>

<button class="btn-print no-print" onclick="window.print()">🖨️ Cetak / Simpan PDF</button>

<div class="page">
    
    <table class="header-table">
        <tr>
            <td width="30%">
                <b>Universitas Esa Unggul</b> </td>
            <td width="40%">
                <div class="title-doc">RESUME MEDIS</div> </td>
            <td width="30%" class="kode-doc">
                RI 02/2020/1 </td>
        </tr>
    </table>

    <div class="box-section row-grid">
        <div class="col-half">
            <table class="identitas">
                <tr>
                    <td class="label">Nomor RM</td> <td class="titik-dua">:</td>
                    <td><?= htmlspecialchars($data['nomor_rm'] ?? '') ?></td>
                </tr>
                <tr>
                    <td class="label">Nama Pasien</td> <td class="titik-dua">:</td>
                    <td><?= htmlspecialchars($data['nama_pasien'] ?? '') ?></td>
                </tr>
            </table>
        </div>
        <div class="col-half">
            <table class="identitas">
                <tr>
                    <td class="label">Tanggal Lahir</td> <td class="titik-dua">:</td>
                    <td><?= htmlspecialchars($data['tanggal_lahir'] ?? '') ?></td>
                </tr>
                <tr>
                    <td class="label">Jenis Kelamin</td> <td class="titik-dua">:</td>
                    <td>
                        <span class="checkbox-custom <?= isChecked($data['jenis_kelamin'], 'L') ?>"></span> L &nbsp;&nbsp; <span class="checkbox-custom <?= isChecked($data['jenis_kelamin'], 'P') ?>"></span> P </td>
                </tr>
            </table>
        </div>
    </div>

    <div class="box-section row-grid">
        <div class="col-half">
            <table class="identitas">
                <tr><td class="label">Tanggal Masuk</td><td class="titik-dua">:</td><td><?= htmlspecialchars($data['tgl_masuk'] ?? '') ?></td></tr> <tr><td class="label">Lama Dirawat</td><td class="titik-dua">:</td><td><?= htmlspecialchars($data['lama_dirawat'] ?? '') ?> Hari</td></tr> <tr><td class="label">DPJP Utama</td><td class="titik-dua">:</td><td><?= htmlspecialchars($data['dpjp_utama'] ?? '') ?></td></tr> </table>
        </div>
        <div class="col-half">
            <table class="identitas">
                <tr><td class="label">Tanggal Keluar</td><td class="titik-dua">:</td><td><?= htmlspecialchars($data['tgl_keluar'] ?? '') ?></td></tr> <tr><td class="label">Ruang Rawat</td><td class="titik-dua">:</td><td><?= htmlspecialchars($data['ruang_rawat'] ?? '') ?></td></tr> <tr>
                    <td class="label">Rawat Bersama</td> <td class="titik-dua">:</td>
                    <td>
                        <span class="checkbox-custom <?= isChecked($data['rawat_bersama'], 'Ya') ?>"></span> Ya &nbsp;&nbsp;
                        <span class="checkbox-custom <?= isChecked($data['rawat_bersama'], 'Tidak') ?>"></span> Tidak
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div class="box-section">
        <table class="identitas">
            <tr><td class="label" style="width: 180px;">Diagnosa Masuk</td><td class="titik-dua">:</td><td><?= htmlspecialchars($data['diagnosa_masuk'] ?? '') ?></td></tr> <tr><td class="label" style="width: 180px;">Ringkasan Riwayat Penyakit</td><td class="titik-dua">:</td><td><?= nl2br(htmlspecialchars($data['riwayat_penyakit'] ?? '')) ?></td></tr> <tr>
                <td class="label" style="width: 180px;">Pemeriksaan Fisik</td> <td class="titik-dua">:</td>
                <td>
                    TD: <?= htmlspecialchars($data['td'] ?? '___') ?> &nbsp; N: <?= htmlspecialchars($data['n'] ?? '___') ?> &nbsp; S: <?= htmlspecialchars($data['s'] ?? '___') ?> &nbsp; P: <?= htmlspecialchars($data['p'] ?? '___') ?> &nbsp; Sat O2: <?= htmlspecialchars($data['sat_o2'] ?? '___') ?> </td>
            </tr>
            <tr><td class="label" style="width: 180px;">Laboratorium</td><td class="titik-dua">:</td><td><?= nl2br(htmlspecialchars($data['laboratorium'] ?? '')) ?></td></tr> <tr><td class="label" style="width: 180px;">Penunjang Lain</td><td class="titik-dua">:</td><td><?= nl2br(htmlspecialchars($data['penunjang_lain'] ?? '')) ?></td></tr> </table>
    </div>

    <div class="box-section">
        <div style="margin-bottom: 5px;">
            <span class="label">Diagnosa Utama :</span><br> <?= htmlspecialchars($data['diagnosa_utama'] ?? '') ?>
            <div class="icd-box">Kode ICD: <b><?= htmlspecialchars($data['icd_utama'] ?? '') ?></b></div> </div>
        <hr style="border-top: 1px dashed #ccc;">
        <div style="margin-bottom: 5px;">
            <span class="label">Diagnosa Sekunder :</span><br> <?= htmlspecialchars($data['diagnosa_sekunder_1'] ?? '') ?>
            <div class="icd-box">Kode ICD: <b><?= htmlspecialchars($data['icd_sekunder_1'] ?? '') ?></b></div> </div>
    </div>

    <div class="box-section">
        <div style="margin-bottom: 5px;">
            <span class="label">Prosedur/Operasi :</span><br> <?= htmlspecialchars($data['prosedur_operasi'] ?? '') ?>
            <div class="icd-box">Kode ICD: <b><?= htmlspecialchars($data['icd_prosedur_1'] ?? '') ?></b></div> </div>
    </div>

    <div class="box-section">
        <div class="label">Pengobatan Selama Dirawat :</div> <div style="padding: 5px 0;">
            <?= nl2br(htmlspecialchars($data['pengobatan'] ?? '')) ?>
        </div>
    </div>

    <div class="box-section">
        <div class="label">Kondisi Pulang :</div> <div style="margin: 8px 0;" class="row-grid">
            <div style="width: 33%;">
                <span class="checkbox-custom <?= isChecked($data['kondisi_pulang'], 'Diijinkan Pulang') ?>"></span> Diijinkan Pulang<br> <span class="checkbox-custom <?= isChecked($data['kondisi_pulang'], 'Dirujuk') ?>"></span> Dirujuk </div>
            <div style="width: 33%;">
                <span class="checkbox-custom <?= isChecked($data['kondisi_pulang'], 'Atas Permintaan Sendiri') ?>"></span> Atas Permintaan Sendiri<br> <span class="checkbox-custom <?= isChecked($data['kondisi_pulang'], 'Meninggal') ?>"></span> Meninggal </div>
            <div style="width: 33%;">
                <span class="checkbox-custom <?= isChecked($data['kondisi_pulang'], 'Melarikan Diri') ?>"></span> Melarikan Diri </div>
        </div>
        <hr style="border-top: 1px dashed #ccc;">
        <table class="identitas">
            <tr><td class="label" style="width: 150px;">Instruksi Pulang</td><td class="titik-dua">:</td><td><?= nl2br(htmlspecialchars($data['instruksi_pulang'] ?? '')) ?></td></tr> </table>
    </div>

    <div style="text-align: right; margin-top: 40px; padding-right: 30px;">
        <br><br><br>
        ( <u><b><?= htmlspecialchars($data['nama_dpjp_pulang'] ?? '..................................') ?></b></u> )<br> Nama DPJP </div>

</div>

<script>
    // Hilangkan komentar pada baris di bawah ini jika ingin dialog print otomatis muncul
    // window.onload = function() { window.print(); }
</script>

</body>
</html>