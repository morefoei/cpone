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
                [cite_start]<p class="text-muted mb-0">Daftar rekam medis pasien sesuai formulir[cite: 9].</p>
            </div>
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
                        <th>No. [cite_start]RM [cite: 1]</th>
                        [cite_start]<th>Nama Pasien [cite: 4]</th>
                        [cite_start]<th>Tgl Masuk [cite: 12]</th>
                        [cite_start]<th>Tgl Keluar [cite: 13]</th>
                        [cite_start]<th>Diagnosa Utama [cite: 31]</th>
                        [cite_start]<th>DPJP Utama [cite: 15]</th>
                        [cite_start]<th>Kondisi Pulang [cite: 43]</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="badge bg-secondary">RM-001234</span></td>
                        <td>Budi Santoso</td>
                        <td>01-Oct-2023</td>
                        <td>05-Oct-2023</td>
                        <td>Dengue Haemorrhagic Fever</td>
                        <td>dr. Andi, Sp.PD</td>
                        <td>Diijinkan Pulang</td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary">Lihat Detail</button>
                        </td>
                    </tr>
                    <tr>
                        <td><span class="badge bg-secondary">RM-001235</span></td>
                        <td>Siti Aminah</td>
                        <td>02-Oct-2023</td>
                        <td>04-Oct-2023</td>
                        <td>Gastritis</td>
                        <td>dr. Budi, Sp.PD</td>
                        <td>Diijinkan Pulang</td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary">Lihat Detail</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <small class="text-muted">*Hanya menampilkan kolom utama. Ekspor ke Excel untuk melihat seluruh detail data pemeriksaan fisik, penunjang, dan terapi.</small>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>