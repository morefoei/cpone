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
// PROSES HAPUS REGISTRASI
// ==========================================
$isAdmin = ($_SESSION['role'] ?? 'admin') === 'admin';
if (isset($_GET['action']) && $_GET['action'] === 'delete_reg' && isset($_GET['id']) && $isAdmin) {
    $delId = (int)$_GET['id'];
    
    // Cari semua E-Resume yang menempel pada Registrasi ini
    $resQuery = mysqli_query($koneksi, "SELECT id FROM tabel_resume_medis WHERE registrasi_id = $delId");
    if ($resQuery) {
        while($rowRes = mysqli_fetch_assoc($resQuery)) {
            $resId = (int)$rowRes['id'];
            // Hapus semua ICD terkait E-Resume tersebut
            mysqli_query($koneksi, "DELETE FROM tabel_resume_icd WHERE resume_id = $resId");
        }
    }
    
    // Hapus E-Resume terkait
    mysqli_query($koneksi, "DELETE FROM tabel_resume_medis WHERE registrasi_id = $delId");
    
    // Hapus Registrasi
    if (mysqli_query($koneksi, "DELETE FROM tabel_registrasi WHERE id = $delId")) {
        echo "<script>alert('Data registrasi berhasil dihapus secara permanen.'); window.location.href='/form/eresume';</script>";
        exit;
    } else {
        $error = mysqli_error($koneksi);
        echo "<script>alert('Gagal menghapus: $error'); window.location.href='/form/eresume';</script>";
        exit;
    }
}

if (isset($_GET['action']) && $_GET['action'] === 'delete_res' && isset($_GET['id']) && $isAdmin) {
    $delId = (int)$_GET['id'];
    mysqli_query($koneksi, "DELETE FROM tabel_resume_icd WHERE resume_id = $delId");
    if (mysqli_query($koneksi, "DELETE FROM tabel_resume_medis WHERE id = $delId")) {
        echo "<script>alert('Data E-Resume berhasil dihapus.'); window.location.href='/form/eresume';</script>";
        exit;
    }
}

// ==========================================
// 2. AMBIL DATA DARI TABEL
// ==========================================
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

$registrasiSql = "SELECT r.*, tr.id AS resume_id
                  FROM tabel_registrasi r
                  LEFT JOIN (
                      SELECT registrasi_id, MAX(id) AS id
                      FROM tabel_resume_medis
                      WHERE registrasi_id IS NOT NULL
                      GROUP BY registrasi_id
                  ) tr ON tr.registrasi_id = r.id
                  ORDER BY r.id DESC LIMIT $limit OFFSET $offset";
$registrasiResult = mysqli_query($koneksi, $registrasiSql);
$registrasiResult = mysqli_query($koneksi, $registrasiSql);

// Syarat E-Resume dianggap sudah lengkap:
$condLengkap = "(diagnosa_utama IS NOT NULL AND trim(diagnosa_utama) != '') AND (tgl_keluar IS NOT NULL AND trim(tgl_keluar) != '') AND (dpjp_utama_dokter_id IS NOT NULL AND dpjp_utama_dokter_id > 0) AND (kondisi_pulang IS NOT NULL AND trim(kondisi_pulang) != '')";

// Ambil E-Resume Draft (Belum Lengkap)
$sqlDraft = "SELECT * FROM tabel_resume_medis WHERE NOT ($condLengkap) ORDER BY id DESC LIMIT $limit OFFSET $offset";
$resultDraft = mysqli_query($koneksi, $sqlDraft);

// Ambil E-Resume Selesai (Sudah Lengkap)
$sqlSelesai = "SELECT * FROM tabel_resume_medis WHERE $condLengkap ORDER BY id DESC LIMIT $limit OFFSET $offset";
$resultSelesai = mysqli_query($koneksi, $sqlSelesai);

// Hitung total untuk pagination
$totalRegResult = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM tabel_registrasi");
$totalReg = mysqli_fetch_assoc($totalRegResult)['total'];

$totalDraftResult = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM tabel_resume_medis WHERE NOT ($condLengkap)");
$totalDraft = mysqli_fetch_assoc($totalDraftResult)['total'];

$totalSelesaiResult = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM tabel_resume_medis WHERE $condLengkap");
$totalSelesai = mysqli_fetch_assoc($totalSelesaiResult)['total'];

$totalRows = max($totalReg, $totalDraft, $totalSelesai);
$totalPages = ceil($totalRows / $limit);

if (!$registrasiResult || !$resultDraft || !$resultSelesai) {
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
                <a href="/" class="btn btn-outline-dark shadow-sm me-2">
                    Kembali ke Index
                </a>
                <a href="/form/registrasi" class="btn btn-outline-primary shadow-sm me-2">
                    Registrasi
                </a>
                <a href="/form/dokter" class="btn btn-outline-secondary shadow-sm me-2">
                    Master Dokter
                </a>
                <a href="/form/tambah_resume" class="btn btn-primary shadow-sm me-2">
                    Buat E-Resume
                </a>
                <a href="/form/export-excel" class="btn btn-success shadow-sm">
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
                        <th>NRM</th>
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
                                        <a href="/form/detail_resume?id=<?= (int) $row['resume_id'] ?>" class="btn btn-sm btn-outline-primary mb-1">Lihat E-Resume</a>
                                    <?php else: ?>
                                        <a href="/form/tambah_resume?registrasi_id=<?= (int) $row['id'] ?>" class="btn btn-sm btn-primary mb-1">Buat E-Resume</a>
                                    <?php endif; ?>
                                    
                                    <a href="/form/registrasi?action=edit&id=<?= (int) $row['id'] ?>" class="btn btn-sm btn-outline-warning mb-1">Edit</a>

                                    <?php if ($isAdmin): ?>
                                        <a href="/form/eresume?action=delete_reg&id=<?= (int) $row['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus Registrasi? Data E-Resume terkait juga akan ikut terhapus!');">Hapus</a>
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

        <div class="section-heading text-warning">List E-Resume (Draft / Belum Lengkap)</div>
        <div class="table-responsive mb-4">
            <table class="table table-hover table-bordered align-middle">
                <thead class="table-warning">
                    <tr>
                        <th>NRM</th>
                        <th>Nama Pasien</th>
                        <th>Tgl Masuk</th>
                        <th>Tgl Keluar</th>
                        <th>Belum Lengkap</th>
                        <th>Kondisi Pulang</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (mysqli_num_rows($resultDraft) > 0) {
                        while($row = mysqli_fetch_assoc($resultDraft)) {
                            $tglMasuk = !empty($row['tgl_masuk']) ? date('d-M-Y', strtotime($row['tgl_masuk'])) : '-'; 
                            $tglKeluar = !empty($row['tgl_keluar']) ? date('d-M-Y', strtotime($row['tgl_keluar'])) : '-';
                            
                            $missing = [];
                            if (empty($row['tgl_keluar'])) $missing[] = 'Tgl Keluar';
                            if (empty($row['dpjp_utama_dokter_id']) || $row['dpjp_utama_dokter_id'] == 0) $missing[] = 'DPJP Utama';
                            if (empty(trim($row['diagnosa_utama'] ?? ''))) $missing[] = 'Diagnosa Utama';
                            if (empty(trim($row['kondisi_pulang'] ?? ''))) $missing[] = 'Kondisi Pulang';
                            
                            $missingText = implode(', ', $missing);
                            ?>
                            <tr>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($row['nomor_rm'] ?? '-') ?></span></td>
                                <td><?= htmlspecialchars($row['nama_pasien'] ?? '-') ?></td>
                                <td><?= $tglMasuk ?></td>
                                <td><?= $tglKeluar ?></td>
                                <td>
                                    <?php if (!empty($missing)): ?>
                                        <span class="text-danger fst-italic"><small>Kurang: <?= $missingText ?></small></span>
                                    <?php else: ?>
                                        <span class="text-success"><small>Siap disimpan</small></span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($row['kondisi_pulang'] ?? '-') ?></td>
                                <td>
                                    <a href="/form/tambah_resume?id=<?= (int) $row['id'] ?>" class="btn btn-sm btn-primary">Lengkapi Data</a>
                                    <?php if ($isAdmin): ?>
                                        <a href="/form/eresume?action=delete_res&id=<?= (int) $row['id'] ?>" class="btn btn-sm btn-outline-danger ms-1" onclick="return confirm('Yakin hapus E-Resume ini?');">Hapus</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-3">Tidak ada draft E-Resume yang belum lengkap.</td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <div class="section-heading text-success">List E-Resume (Sudah Lengkap)</div>
        <div class="table-responsive">
            <table class="table table-hover table-bordered align-middle">
                <thead class="table-success">
                    <tr>
                        <th>NRM</th>
                        <th>Nama Pasien</th>
                        <th>Tgl Masuk</th>
                        <th>Tgl Keluar</th>
                        <th>Diagnosa Penyakit (Utama)</th>
                        <th>DPJP Utama</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (mysqli_num_rows($resultSelesai) > 0) {
                        while($row = mysqli_fetch_assoc($resultSelesai)) {
                            $tglMasuk = !empty($row['tgl_masuk']) ? date('d-M-Y', strtotime($row['tgl_masuk'])) : '-'; 
                            $tglKeluar = !empty($row['tgl_keluar']) ? date('d-M-Y', strtotime($row['tgl_keluar'])) : '-';
                            ?>
                            <tr>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($row['nomor_rm'] ?? '-') ?></span></td>
                                <td><?= htmlspecialchars($row['nama_pasien'] ?? '-') ?></td>
                                <td><?= $tglMasuk ?></td>
                                <td><?= $tglKeluar ?></td>
                                <td><?= htmlspecialchars($row['diagnosa_utama']) ?></td>
                                <td><?= htmlspecialchars($row['dpjp_utama'] ?? '-') ?></td>
                                <td>
                                    <a href="/form/detail_resume?id=<?= (int) $row['id'] ?>" class="btn btn-sm btn-outline-primary">Lihat Resume</a>
                                    <a href="/form/tambah_resume?id=<?= (int) $row['id'] ?>" class="btn btn-sm btn-outline-warning ms-1">Edit Data</a>
                                    <?php if ($isAdmin): ?>
                                        <a href="/form/eresume?action=delete_res&id=<?= (int) $row['id'] ?>" class="btn btn-sm btn-outline-danger ms-1" onclick="return confirm('Yakin hapus E-Resume ini?');">Hapus</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-3">Belum ada E-Resume yang sudah lengkap.</td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>
        </div>
        <small class="text-muted d-block mb-3">*Hanya menampilkan kolom utama. Ekspor ke Excel untuk melihat seluruh detail data pemeriksaan fisik, penunjang, dan terapi.</small>

        <?php if ($totalPages > 1): ?>
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page - 1 ?>">Previous</a>
                    </li>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page + 1 ?>">Next</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
