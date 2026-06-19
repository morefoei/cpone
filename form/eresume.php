<?php
// ==========================================
// 1. PANGGIL FILE KONEKSI DATABASE
// ==========================================
// Pastikan path-nya benar (sesuaikan jika file eresume.php ada di dalam folder yang sama/berbeda)
require 'backend/koneksi.php';

// ==========================================
// 2. AMBIL DATA DARI TABEL
// ==========================================
// GANTI 'tabel_resume_medis' dengan nama tabel Anda yang sebenarnya di database if0_42222933_cp
$sql = "SELECT * FROM tabel_resume_medis ORDER BY id DESC"; 

// Menggunakan variabel $koneksi dari file backend/koneksi.php
$result = mysqli_query($koneksi, $sql); 

// Cek jika query gagal
if (!$result) {
    die("Query error: " . mysqli_error($koneksi));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem E-Resume Medis</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .table-container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
    </style>
</head>
<body>

<div class="container mt-5">
    <div class="table-container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="mb-1">Data E-Resume Medis</h3>
                <p class="text-muted mb-0">Daftar rekam medis pasien sesuai formulir.</p>
            </div>
            <a href="export-excel.php" class="btn btn-success shadow-sm">
                Export ke Excel
            </a>
            <a href="export-excel.php" class="btn btn-success shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-excel me-1" viewBox="0 0 16 16">
                  <path d="M5.884 6.68a.5.5 0 1 0-.768.64L7.349 10l-2.233 2.68a.5.5 0 0 0 .768.64L8 10.781l2.116 2.539a.5.5 0 0 0 .768-.641L8.651 10l2.233-2.68a.5.5 0 0 0-.768-.64L8 9.219l-2.116-2.54z"/>
                  <path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2zM9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5v2z"/>
                </svg>
                Export ke Excel
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-hover table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>No. RM</th>
                        <th>Nama Pasien</th>
                        <th>Tgl Masuk</th>
                        <th>Tgl Keluar</th>
                        <th>Diagnosa Utama</th>
                        <th>DPJP Utama</th>
                        <th>Kondisi Pulang</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // ==========================================
                    // 3. MENAMPILKAN DATA
                    // ==========================================
                    if (mysqli_num_rows($result) > 0) {
                        
                        while($row = mysqli_fetch_assoc($result)) {
                            // Cek jika tgl_masuk dan tgl_keluar tidak kosong sebelum diformat
                            $tglMasuk = !empty($row['tgl_masuk']) ? date('d-M-Y', strtotime($row['tgl_masuk'])) : '-'; 
                            $tglKeluar = !empty($row['tgl_keluar']) ? date('d-M-Y', strtotime($row['tgl_keluar'])) : '-';

                            ?>
                            <tr>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($row['nomor_rm'] ?? '-') ?></span></td>
                                <td><?= htmlspecialchars($row['nama_pasien'] ?? '-') ?></td>
                                <td><?= $tglMasuk ?></td>
                                <td><?= $tglKeluar ?></td>
                                <td><?= htmlspecialchars($row['diagnosa_utama'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($row['dpjp_utama'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($row['kondisi_pulang'] ?? '-') ?></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary">Lihat Detail</button>
                                </td>
                            </tr>
                            <?php
                        }

                    } else {
                        ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-3">Belum ada data Resume Medis.</td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <small class="text-muted">*Hanya menampilkan kolom utama. Ekspor ke Excel untuk melihat seluruh detail data pemeriksaan fisik, penunjang, dan terapi.</small>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>