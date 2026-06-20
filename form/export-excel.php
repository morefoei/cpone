<?php
// Tampilkan semua error untuk proses debugging jika diperlukan (bisa dimatikan di production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include dirname(__DIR__) . '/backend/koneksi.php';

// Pastikan skema tabel resume sudah sesuai
$query = "SELECT * FROM tabel_resume_medis ORDER BY id DESC";
$result = mysqli_query($koneksi, $query);

if (!$result) {
    die("Query error: " . mysqli_error($koneksi));
}

// Set Header agar file didownload sebagai Excel (.xls)
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=Data_E_Resume_Medis.xls");
header("Pragma: no-cache");
header("Expires: 0");

// Buat struktur HTML Table agar rapi di Excel
echo "<table border='1'>";
echo "<tr>
        <th style='background-color:#4CAF50;color:white;'>Nomor RM</th>
        <th style='background-color:#4CAF50;color:white;'>Nama Pasien</th>
        <th style='background-color:#4CAF50;color:white;'>Tanggal Lahir</th>
        <th style='background-color:#4CAF50;color:white;'>Jenis Kelamin</th>
        <th style='background-color:#4CAF50;color:white;'>Tanggal Masuk</th>
        <th style='background-color:#4CAF50;color:white;'>Tanggal Keluar</th>
        <th style='background-color:#4CAF50;color:white;'>Lama Dirawat</th>
        <th style='background-color:#4CAF50;color:white;'>Ruang Rawat</th>
        <th style='background-color:#4CAF50;color:white;'>DPJP Utama</th>
        <th style='background-color:#4CAF50;color:white;'>Rawat Bersama</th>
        <th style='background-color:#4CAF50;color:white;'>Diagnosa Masuk</th>
        <th style='background-color:#4CAF50;color:white;'>Diagnosa Utama</th>
        <th style='background-color:#4CAF50;color:white;'>ICD Utama</th>
        <th style='background-color:#4CAF50;color:white;'>Kondisi Pulang</th>
        <th style='background-color:#4CAF50;color:white;'>Instruksi Pulang</th>
      </tr>";

while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['nomor_rm'] ?? '-') . "</td>";
    echo "<td>" . htmlspecialchars($row['nama_pasien'] ?? '-') . "</td>";
    echo "<td>" . htmlspecialchars($row['tanggal_lahir'] ?? '-') . "</td>";
    echo "<td>" . htmlspecialchars($row['jenis_kelamin'] ?? '-') . "</td>";
    echo "<td>" . htmlspecialchars($row['tgl_masuk'] ?? '-') . "</td>";
    echo "<td>" . htmlspecialchars($row['tgl_keluar'] ?? '-') . "</td>";
    echo "<td>" . htmlspecialchars($row['lama_dirawat'] ?? '-') . "</td>";
    echo "<td>" . htmlspecialchars($row['ruang_rawat'] ?? '-') . "</td>";
    echo "<td>" . htmlspecialchars($row['dpjp_utama'] ?? '-') . "</td>";
    echo "<td>" . htmlspecialchars($row['rawat_bersama'] ?? '-') . "</td>";
    echo "<td>" . htmlspecialchars($row['diagnosa_masuk'] ?? '-') . "</td>";
    echo "<td>" . htmlspecialchars($row['diagnosa_utama'] ?? '-') . "</td>";
    echo "<td>" . htmlspecialchars($row['icd_utama'] ?? '-') . "</td>";
    echo "<td>" . htmlspecialchars($row['kondisi_pulang'] ?? '-') . "</td>";
    echo "<td>" . htmlspecialchars($row['instruksi_pulang'] ?? '-') . "</td>";
    echo "</tr>";
}

echo "</table>";
exit;
?>