<?php
include dirname(__DIR__) . '/backend/koneksi.php';

if (!isset($_GET['id'])) {
    die("ID tidak ditemukan");
}

$id = mysqli_real_escape_string($koneksi, $_GET['id']);
$query = "SELECT * FROM tabel_resume_medis WHERE id = '$id'";
$result = mysqli_query($koneksi, $query);
$data = mysqli_fetch_assoc($result);

// Nama file excel
$nama_file = "Resume_Medis_" . str_replace(' ', '_', $data['nama_pasien']) . ".xls";

// Memaksa browser mengunduh halaman ini sebagai file Excel
header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=$nama_file");
?>

<style>
    .form-table { border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; font-size: 12px; }
    .form-table td, .form-table th { border: 1px solid black; padding: 5px; vertical-align: top; }
    .judul { text-align: center; font-size: 16px; font-weight: bold; background-color: #f2f2f2; }
    .label { font-weight: bold; width: 20%; }
    .isi { width: 30%; }
    .no-border { border: none !important; }
</style>

<table class="form-table">
    <tr>
        <th colspan="4" class="judul" style="padding: 15px;">RESUME MEDIS</th>
    </tr>

    <tr>
        <td class="label">Nama Pasien</td>
        <td class="isi">: <?= $data['nama_pasien'] ?></td>
        <td class="label">Nomor RM</td>
        <td class="isi">: <?= $data['nomor_rm'] ?></td>
    </tr>
    <tr>
        <td class="label">Tanggal Lahir</td>
        <td class="isi">: <?= $data['tanggal_lahir'] ?></td>
        <td class="label">Jenis Kelamin</td>
        <td class="isi">: <?= $data['jenis_kelamin'] ?></td>
    </tr>

    <tr>
        <td class="label">Tanggal Masuk</td>
        <td class="isi">: <?= $data['tgl_masuk'] ?></td>
        <td class="label">Tanggal Keluar</td>
        <td class="isi">: <?= $data['tgl_keluar'] ?></td>
    </tr>
    <tr>
        <td class="label">Ruang Rawat</td>
        <td class="isi">: <?= $data['ruang_rawat'] ?></td>
        <td class="label">Lama Dirawat</td>
        <td class="isi">: <?= $data['lama_dirawat'] ?> Hari</td>
    </tr>
    <tr>
        <td class="label">DPJP Utama</td>
        <td colspan="3">: <?= $data['dpjp_utama'] ?></td>
    </tr>

    <tr>
        <td class="label">Diagnosa Masuk</td>
        <td colspan="3">: <?= $data['diagnosa_masuk'] ?></td>
    </tr>
    <tr>
        <td class="label">Ringkasan Riwayat Penyakit</td>
        <td colspan="3">: <?= $data['riwayat_penyakit'] ?></td>
    </tr>
    <tr>
        <td class="label">Pemeriksaan Fisik</td>
        <td colspan="3">
            TD: <?= $data['td'] ?> &nbsp;|&nbsp; 
            N: <?= $data['n'] ?> &nbsp;|&nbsp; 
            S: <?= $data['s'] ?> &nbsp;|&nbsp; 
            P: <?= $data['p'] ?> &nbsp;|&nbsp; 
            SatO2: <?= $data['sat_o2'] ?>
        </td>
    </tr>

    <tr>
        <td class="label">Laboratorium</td>
        <td colspan="3">: <?= $data['laboratorium'] ?></td>
    </tr>
    <tr>
        <td class="label">Penunjang Lain</td>
        <td colspan="3">: <?= $data['penunjang_lain'] ?></td>
    </tr>
    <tr>
        <td class="label">Diagnosa Utama</td>
        <td colspan="2">: <?= $data['diagnosa_utama'] ?></td>
        <td class="label">Kode ICD: <?= $data['icd_utama'] ?></td>
    </tr>
    <tr>
        <td class="label">Diagnosa Sekunder</td>
        <td colspan="2">: <?= $data['diagnosa_sekunder_1'] ?></td>
        <td class="label">Kode ICD: <?= $data['icd_sekunder_1'] ?></td>
    </tr>

    <tr>
        <td class="label">Prosedur / Operasi</td>
        <td colspan="2">: <?= $data['prosedur_operasi'] ?></td>
        <td class="label">Kode ICD: <?= $data['icd_prosedur_1'] ?></td>
    </tr>
    <tr>
        <td class="label">Pengobatan Selama Dirawat</td>
        <td colspan="3">: <?= $data['pengobatan'] ?></td>
    </tr>

    <tr>
        <td class="label">Kondisi Pulang</td>
        <td colspan="3">: <?= $data['kondisi_pulang'] ?></td>
    </tr>
    <tr>
        <td class="label">Instruksi Pulang</td>
        <td colspan="3">: <?= $data['instruksi_pulang'] ?></td>
    </tr>
    
    <tr>
        <td colspan="3" style="border: none;"></td>
        <td style="border: none; text-align: center; padding-top: 40px;">
            ( <?= $data['nama_dpjp_pulang'] ?> )<br>
            <b>Nama DPJP</b>
        </td>
    </tr>
</table>