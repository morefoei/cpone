<?php
// Wajib memanggil autoload dari Composer
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// 1. Buat object Spreadsheet baru
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Data Resume Medis');

// 2. Set Header Kolom (Baris 1) sesuai format Resume Medis
// Format ini disusun berdasarkan isian form dari data pasien, tanda vital, diagnosa, hingga kondisi pulang.
$headers = [
    'Nomor RM', 'Nama Pasien', 'Tanggal Lahir', 'Jenis Kelamin', 
    'Tanggal Masuk', 'Tanggal Keluar', 'Lama Dirawat (Hari)', 'Ruang Rawat', 
    'DPJP Utama', 'Rawat Bersama', 'DPJP Lainnya 1', 'DPJP Lainnya 2', 'DPJP Lainnya 3', 
    'Diagnosa Masuk', 'Ringkasan Riwayat Penyakit', 'TD', 'N', 'S', 'P', 'Sat 02', 
    'Laboratorium', 'Penunjang Lain', 'Diagnosa Utama', 'Kode ICD Utama', 
    'Diagnosa Sekunder 1', 'Kode ICD Sek 1', 'Prosedur/Operasi', 'ICD Prosedur 1', 
    'Pengobatan Selama Dirawat', 'Kondisi Pulang', 'Instruksi Pulang', 'Nama DPJP (Pulang)'
];

// Masukkan data header ke baris pertama (mulai dari A1)
$sheet->fromArray($headers, NULL, 'A1');

// Buat tulisan header menjadi Bold agar rapi
$sheet->getStyle('A1:AF1')->getFont()->setBold(true);

// 3. Data Dummy (Nantinya bagian ini Anda ganti dengan perulangan / while loop dari query Database Anda)
$data = [
    [
        'RM-001234', 'Budi Santoso', '1985-08-15', 'L',
        '2023-10-01', '2023-10-05', 4, 'Mawar Kelas 1',
        'dr. Andi, Sp.PD', 'Ya', 'dr. Siti, Sp.JP', '-', '-',
        'Demam hari ke-3', 'Pasien datang dengan keluhan demam, mual dan muntah', '120/80', '88', '38.5', '20', '98%',
        'Hb: 13, Trombosit: 100k', 'Rontgen Thorax Normal', 'Dengue Haemorrhagic Fever', 'A91',
        'Dyspepsia', 'K30', 'Pemasangan Infus', '38.99',
        'Paracetamol 3x500mg, IVFD RL', 'Diijinkan Pulang', 'Banyak minum air, kontrol 3 hari lagi', 'dr. Andi, Sp.PD'
    ],
    // Baris data pasien kedua
    [
        'RM-001235', 'Siti Aminah', '1990-12-01', 'P',
        '2023-10-02', '2023-10-04', 2, 'Melati Kelas 2',
        'dr. Budi, Sp.PD', 'Tidak', '-', '-', '-',
        'Nyeri ulu hati', 'Pasien mengeluh nyeri perut sejak 2 hari yang lalu', '110/70', '80', '36.5', '18', '99%',
        'Normal', 'USG Abdomen Normal', 'Gastritis', 'K29.7',
        '-', '-', '-', '-',
        'Omeprazole 1x20mg', 'Diijinkan Pulang', 'Hindari makanan pedas', 'dr. Budi, Sp.PD'
    ]
];

// Masukkan data pasien mulai dari baris kedua (A2)
$sheet->fromArray($data, NULL, 'A2');

// (Opsional) Mengatur lebar kolom agar otomatis menyesuaikan panjang teks
foreach(range('A','Z') as $columnID) {
    $sheet->getColumnDimension($columnID)->setAutoSize(true);
}
$sheet->getColumnDimension('AA')->setAutoSize(true);
$sheet->getColumnDimension('AB')->setAutoSize(true);
$sheet->getColumnDimension('AC')->setAutoSize(true);
$sheet->getColumnDimension('AD')->setAutoSize(true);
$sheet->getColumnDimension('AE')->setAutoSize(true);
$sheet->getColumnDimension('AF')->setAutoSize(true);

// 4. Proses output agar file langsung ter-download di browser
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Laporan_EResume_Medis.xlsx"');
header('Cache-Control: max-age=0'); // Mencegah caching di browser

// Tulis dan kirim ke output stream
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');

// Pastikan script berhenti di sini agar tidak ada kode HTML tambahan yang ikut tertulis ke dalam file Excel
exit;
?>