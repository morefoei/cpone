<?php
include dirname(__DIR__) . '/backend/koneksi.php';

if (!isset($_GET['id'])) {
    die("ID tidak ditemukan");
}

$id = mysqli_real_escape_string($koneksi, $_GET['id']);
$query = "SELECT * FROM tabel_resume_medis WHERE id = '$id'";
$result = mysqli_query($koneksi, $query);
$data = mysqli_fetch_assoc($result);

// Nama file excel yang akan diunduh (otomatis sesuai nama pasien)
$nama_file = "Resume_Medis_" . str_replace(' ', '_', $data['nama_pasien']) . ".xls";

// Memaksa browser mengunduh halaman ini sebagai file Excel
header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=$nama_file");
?>

<table border="1">
    <tr><th colspan="2" style="background-color: #4CAF50; color: white;">FORM E-RESUME MEDIS</th></tr>
    <tr><th>Nomor RM</th><td><?= $data['nomor_rm'] ?></td></tr>
    <tr><th>Nama Pasien</th><td><?= $data['nama_pasien'] ?></td></tr>
    <tr><th>Tanggal Lahir</th><td><?= $data['tanggal_lahir'] ?></td></tr>
    <tr><th>Jenis Kelamin</th><td><?= $data['jenis_kelamin'] ?></td></tr>
    
    <tr><th colspan="2" style="background-color: #dddddd;">Perawatan</th></tr>
    <tr><th>Tanggal Masuk</th><td><?= $data['tgl_masuk'] ?></td></tr>
    <tr><th>Tanggal Keluar</th><td><?= $data['tgl_keluar'] ?></td></tr>
    <tr><th>Lama Dirawat (Hari)</th><td><?= $data['lama_dirawat'] ?></td></tr>
    <tr><th>Ruang Rawat</th><td><?= $data['ruang_rawat'] ?></td></tr>
    <tr><th>DPJP Utama</th><td><?= $data['dpjp_utama'] ?></td></tr>
    
    <tr><th colspan="2" style="background-color: #dddddd;">Klinis & Diagnosa</th></tr>
    <tr><th>Diagnosa Masuk</th><td><?= $data['diagnosa_masuk'] ?></td></tr>
    <tr><th>Riwayat Penyakit</th><td><?= $data['riwayat_penyakit'] ?></td></tr>
    <tr><th>Tanda Vital</th><td>TD: <?= $data['td'] ?>, N: <?= $data['n'] ?>, S: <?= $data['s'] ?>, P: <?= $data['p'] ?>, SatO2: <?= $data['sat_o2'] ?></td></tr>
    <tr><th>Laboratorium</th><td><?= $data['laboratorium'] ?></td></tr>
    <tr><th>Diagnosa Utama</th><td><?= $data['diagnosa_utama'] ?> (ICD: <?= $data['icd_utama'] ?>)</td></tr>
    
    <tr><th colspan="2" style="background-color: #dddddd;">Tindakan & Pulang</th></tr>
    <tr><th>Prosedur / Operasi</th><td><?= $data['prosedur_operasi'] ?></td></tr>
    <tr><th>Pengobatan</th><td><?= $data['pengobatan'] ?></td></tr>
    <tr><th>Kondisi Pulang</th><td><?= $data['kondisi_pulang'] ?></td></tr>
    <tr><th>Instruksi Pulang</th><td><?= $data['instruksi_pulang'] ?></td></tr>
    <tr><th>DPJP Pulang</th><td><?= $data['nama_dpjp_pulang'] ?></td></tr>
</table>