<?php
// 1. Tampilkan Error
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);    

// ==========================================
// 1. PANGGIL FILE KONEKSI DATABASE
// ==========================================
include dirname(__DIR__) . '/backend/koneksi.php';
include __DIR__ . '/registrasi_helpers.php';

ensureRegistrasiSchema($koneksi);

// ==========================================
// 2. AMBIL DATA DARI TABEL
// ==========================================
$registrasiSql = "SELECT r.*, tr.id AS resume_id
                  FROM tabel_registrasi r
                  LEFT JOIN (
                      SELECT registrasi_id, MAX(id) AS id
                      FROM tabel_resume_medis
                      WHERE registrasi_id IS NOT NULL
                      GROUP BY registrasi_id
                  ) tr ON tr.registrasi_id = r.id
                  ORDER BY r.id DESC";
$registrasiResult = mysqli_query($koneksi, $registrasiSql);
$sql = "SELECT * FROM tabel_resume_medis WHERE diagnosa_utama IS NOT NULL AND diagnosa_utama <> '' ORDER BY id DESC";
$result = mysqli_query($koneksi, $sql);

if (!$registrasiResult || !$result) {
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
        .section-heading { font-size: 1.1rem; font-weight: 700; margin: 24px 0 12px; }
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
            
            <div>
                <a href="registrasi.php" class="btn btn-outline-primary shadow-sm me-2">
                    Registrasi
                </a>
                <a href="dokter.php" class="btn btn-outline-secondary shadow-sm me-2">
                    Master Dokter
                </a>
                <a href="tambah_resume.php" class="btn btn-primary shadow-sm me-2">
                    Buat E-Resume
                </a>
                <a href="export-excel.php" class="btn btn-success shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-excel me-1" viewBox="0 0 16 16">
                      <path d="M5.884 6.68a.5.5 0 1 0-.768.64L7.349 10l-2.233 2.68a.5.5 0 0 0 .768.64L8 10.781l2.116 2.539a.5.5 0 0 0 .768-.641L8.651 10l2.233-2.68a.5.5 0 0 0-.768-.64L8 9.219l-2.116-2.54z"/>
                      <path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2zM9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5v2z"/>
                    </svg>
                    Export ke Excel
                </a>
            </div>
        </div>

        <div class="section-heading">List Registrasi</div>
        <div class="table-responsive mb-4">
            <table class="table table-hover table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>No. RM</th>
                        <th>Nama Pasien</th>
                        <th>Tgl Lahir</th>
                        <th>Jenis Kelamin</th>
                        <th>Tgl Masuk</th>
                        <th>Penyakit</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($registrasiResult) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($registrasiResult)): ?>
                            <?php
                            $tglLahir = !empty($row['tanggal_lahir']) ? date('d-M-Y', strtotime($row['tanggal_lahir'])) : '-';
                            $tglMasuk = !empty($row['tgl_masuk']) ? date('d-M-Y', strtotime($row['tgl_masuk'])) : '-';
                            ?>
                            <tr>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($row['nomor_rm'] ?? '-') ?></span></td>
                                <td><?= htmlspecialchars($row['nama_pasien'] ?? '-') ?></td>
                                <td><?= $tglLahir ?></td>
                                <td><?= htmlspecialchars($row['jenis_kelamin'] ?? '-') ?></td>
                                <td><?= $tglMasuk ?></td>
                                <td><?= htmlspecialchars($row['penyakit'] ?? '-') ?></td>
                                <td>
                                    <?php if (!empty($row['resume_id'])): ?>
                                        <span class="badge bg-success">Sudah E-Resume</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">Belum E-Resume</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($row['resume_id'])): ?>
                                        <a href="detail_resume.php?id=<?= (int) $row['resume_id'] ?>" class="btn btn-sm btn-outline-primary">Lihat E-Resume</a>
                                    <?php else: ?>
                                        <a href="tambah_resume.php?registrasi_id=<?= (int) $row['id'] ?>" class="btn btn-sm btn-primary">Buat E-Resume</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-3">Belum ada data registrasi.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="section-heading">List E-Resume</div>
        <div class="table-responsive">
            <table class="table table-hover table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>No. RM</th>
                        <th>Nama Pasien</th>
                        <th>Tgl Masuk</th>
                        <th>Tgl Keluar</th>
                        <th>Diagnosa / Penyakit</th>
                        <th>DPJP Utama</th>
                        <th>Kondisi Pulang</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (mysqli_num_rows($result) > 0) {
                        while($row = mysqli_fetch_assoc($result)) {
                            $tglMasuk = !empty($row['tgl_masuk']) ? date('d-M-Y', strtotime($row['tgl_masuk'])) : '-'; 
                            $tglKeluar = !empty($row['tgl_keluar']) ? date('d-M-Y', strtotime($row['tgl_keluar'])) : '-';
                            $diagnosa = !empty($row['diagnosa_utama']) ? $row['diagnosa_utama'] : ($row['diagnosa_masuk'] ?? '-');
                            ?>
                            <tr>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($row['nomor_rm'] ?? '-') ?></span></td>
                                <td><?= htmlspecialchars($row['nama_pasien'] ?? '-') ?></td>
                                <td><?= $tglMasuk ?></td>
                                <td><?= $tglKeluar ?></td>
                                <td><?= htmlspecialchars($diagnosa) ?></td>
                                <td><?= htmlspecialchars($row['dpjp_utama'] ?? '-') ?></td>
                                <td><?= htmlspecialchars($row['kondisi_pulang'] ?? '-') ?></td>
                                <td>
                                    <a href="detail_resume.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-primary">Lihat Detail</a>
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
