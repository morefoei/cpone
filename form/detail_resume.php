<?php
include dirname(__DIR__) . '/backend/koneksi.php';

// Cek apakah ada ID yang dikirim
if (!isset($_GET['id'])) {
    die("Data pasien tidak ditemukan!");
}

$id = mysqli_real_escape_string($koneksi, $_GET['id']);
$query = "SELECT * FROM tabel_resume_medis WHERE id = '$id'";
$result = mysqli_query($koneksi, $query);

if (mysqli_num_rows($result) == 0) {
    die("Data tidak ditemukan di database!");
}

$data = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail E-Resume Medis</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .detail-container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); max-width: 900px; margin: auto; }
        .section-title { font-weight: bold; border-bottom: 2px solid #0d6efd; padding-bottom: 5px; margin-top: 25px; margin-bottom: 15px; color: #0d6efd; }
        th { width: 30%; background-color: #f8f9fa !important; }
    </style>
</head>
<body>

<div class="container mt-5 mb-5">
    <div class="detail-container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="mb-0">Detail Resume Medis</h3>
            <div>
                <a href="eresume.php" class="btn btn-secondary me-2">Kembali</a>
                <a href="cetak_resume.php?id=<?= $data['id'] ?>" target="_blank" class="btn btn-danger">
                    🖨️ Cetak Form (PDF)
                </a>
            </div>
        </div>

        <table class="table table-bordered">
            <tr><th colspan="2" class="section-title">1. Identitas Pasien</th></tr>
            <tr><th>Nomor RM</th><td><?= htmlspecialchars($data['nomor_rm'] ?? '-') ?></td></tr>
            <tr><th>Nama Pasien</th><td><?= htmlspecialchars($data['nama_pasien'] ?? '-') ?></td></tr>
            <tr><th>Tanggal Lahir</th><td><?= htmlspecialchars($data['tanggal_lahir'] ?? '-') ?></td></tr>
            <tr><th>Jenis Kelamin</th><td><?= htmlspecialchars($data['jenis_kelamin'] ?? '-') ?></td></tr>
            
            <tr><th colspan="2" class="section-title">2. Perawatan & DPJP</th></tr>
            <tr><th>Tanggal Masuk</th><td><?= htmlspecialchars($data['tgl_masuk'] ?? '-') ?></td></tr>
            <tr><th>Tanggal Keluar</th><td><?= htmlspecialchars($data['tgl_keluar'] ?? '-') ?></td></tr>
            <tr><th>Lama Dirawat</th><td><?= htmlspecialchars($data['lama_dirawat'] ?? '-') ?> Hari</td></tr>
            <tr><th>Ruang Rawat</th><td><?= htmlspecialchars($data['ruang_rawat'] ?? '-') ?></td></tr>
            <tr><th>DPJP Utama</th><td><?= htmlspecialchars($data['dpjp_utama'] ?? '-') ?></td></tr>
            <tr><th>Rawat Bersama</th><td><?= htmlspecialchars($data['rawat_bersama'] ?? '-') ?></td></tr>

            <tr><th colspan="2" class="section-title">3. Klinis & Pemeriksaan Fisik</th></tr>
            <tr><th>Diagnosa Masuk</th><td><?= htmlspecialchars($data['diagnosa_masuk'] ?? '-') ?></td></tr>
            <tr><th>Riwayat Penyakit</th><td><?= nl2br(htmlspecialchars($data['riwayat_penyakit'] ?? '-')) ?></td></tr>
            <tr><th>Tanda Vital</th>
                <td>
                    TD: <?= htmlspecialchars($data['td'] ?? '-') ?> | N: <?= htmlspecialchars($data['n'] ?? '-') ?> | 
                    S: <?= htmlspecialchars($data['s'] ?? '-') ?> | P: <?= htmlspecialchars($data['p'] ?? '-') ?> | 
                    Sat O2: <?= htmlspecialchars($data['sat_o2'] ?? '-') ?>
                </td>
            </tr>

            <tr><th colspan="2" class="section-title">4. Penunjang & Diagnosa Akhir</th></tr>
            <tr><th>Laboratorium</th><td><?= nl2br(htmlspecialchars($data['laboratorium'] ?? '-')) ?></td></tr>
            <tr><th>Penunjang Lain</th><td><?= nl2br(htmlspecialchars($data['penunjang_lain'] ?? '-')) ?></td></tr>
            <tr><th>Diagnosa Utama</th><td><?= htmlspecialchars($data['diagnosa_utama'] ?? '-') ?> (ICD: <?= htmlspecialchars($data['icd_utama'] ?? '-') ?>)</td></tr>
            <tr><th>Diagnosa Sekunder</th><td><?= htmlspecialchars($data['diagnosa_sekunder_1'] ?? '-') ?> (ICD: <?= htmlspecialchars($data['icd_sekunder_1'] ?? '-') ?>)</td></tr>

            <tr><th colspan="2" class="section-title">5. Prosedur & Pengobatan</th></tr>
            <tr><th>Prosedur/Operasi</th><td><?= htmlspecialchars($data['prosedur_operasi'] ?? '-') ?> (ICD: <?= htmlspecialchars($data['icd_prosedur_1'] ?? '-') ?>)</td></tr>
            <tr><th>Pengobatan (Terapi)</th><td><?= nl2br(htmlspecialchars($data['pengobatan'] ?? '-')) ?></td></tr>

            <tr><th colspan="2" class="section-title">6. Rencana Pulang</th></tr>
            <tr><th>Kondisi Pulang</th><td><?= htmlspecialchars($data['kondisi_pulang'] ?? '-') ?></td></tr>
            <tr><th>Instruksi Pulang</th><td><?= htmlspecialchars($data['instruksi_pulang'] ?? '-') ?></td></tr>
            <tr><th>Nama DPJP Pulang</th><td><?= htmlspecialchars($data['nama_dpjp_pulang'] ?? '-') ?></td></tr>
        </table>
    </div>
</div>

</body>
</html>