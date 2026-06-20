<?php
require_once __DIR__ . '/backend/koneksi.php';

// Ambil 10 besar penyakit dari tabel_resume_medis berdasarkan diagnosa_utama
$query = "SELECT diagnosa_utama AS penyakit, COUNT(id) AS jumlah 
          FROM tabel_resume_medis 
          WHERE diagnosa_utama IS NOT NULL AND trim(diagnosa_utama) != '' 
          GROUP BY diagnosa_utama 
          ORDER BY jumlah DESC 
          LIMIT 10";

$result = mysqli_query($koneksi, $query);

$labels = [];
$data_jumlah = [];
$top10 = [];

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        // Potong nama penyakit jika terlalu panjang untuk grafik
        $penyakit = htmlspecialchars($row['penyakit']);
        $label_short = (strlen($penyakit) > 30) ? substr($penyakit, 0, 30) . '...' : $penyakit;
        
        $labels[] = $label_short;
        $data_jumlah[] = (int)$row['jumlah'];
        $top10[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Laporan 10 Besar Penyakit</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Chart.js untuk Grafik Batang -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- SheetJS untuk Export Excel -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <!-- HTML2PDF untuk Export PDF -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        body { background-color: #f4f6f9; }
        .dashboard-container { max-width: 1000px; margin: 40px auto; padding: 20px; }
        .card { border: none; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); margin-bottom: 30px; }
        .card-header { background-color: #fff; border-bottom: 1px solid #edf2f9; padding: 20px 25px; border-radius: 12px 12px 0 0 !important; }
        .card-title { color: #2c3e50; font-weight: 700; margin: 0; }
        .table th { background-color: #f8f9fa; color: #495057; font-weight: 600; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.5px; }
        .table td { vertical-align: middle; }
        .badge-rank { font-size: 0.9rem; padding: 8px 12px; border-radius: 8px; font-weight: 600; }
        .rank-1 { background-color: #ffd700; color: #856404; }
        .rank-2 { background-color: #e0e0e0; color: #383d41; }
        .rank-3 { background-color: #cd7f32; color: #5c3a21; }
        .rank-other { background-color: #f8f9fa; color: #6c757d; border: 1px solid #dee2e6; }
        .chart-container { position: relative; height: 400px; width: 100%; padding: 20px; }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container dashboard-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold" style="color: #2c3e50;">📊 Dashboard Laporan</h2>
            <p class="text-muted">Statistik dan daftar 10 besar diagnosa penyakit terbanyak.</p>
        </div>
        <div class="d-print-none">
            <button onclick="exportAllExcel()" class="btn btn-outline-success shadow-sm fw-semibold me-2">📥 Export All Excel</button>
            <button onclick="exportAllPDF()" class="btn btn-outline-danger shadow-sm fw-semibold me-2">📄 Export All PDF</button>
            <button onclick="window.print()" class="btn btn-primary shadow-sm fw-semibold">🖨️ Cetak</button>
        </div>
    </div>

    <!-- Tabel 10 Besar Penyakit -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title">🏆 Top 10 Diagnosa Penyakit (Berdasarkan Diagnosa Utama)</h5>
            <div class="d-print-none">
                <button onclick="exportTableExcel()" class="btn btn-sm btn-success">Excel</button>
                <button onclick="exportTablePDF()" class="btn btn-sm btn-danger ms-1">PDF</button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="tablePenyakit">
                    <thead>
                        <tr>
                            <th class="text-center" width="80">Peringkat</th>
                            <th>Diagnosa Penyakit (ICD-10)</th>
                            <th class="text-center" width="150">Jumlah Kasus</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($top10) > 0): ?>
                            <?php foreach ($top10 as $index => $row): ?>
                                <?php 
                                    $rank = $index + 1;
                                    $badgeClass = 'rank-other';
                                    if ($rank === 1) $badgeClass = 'rank-1';
                                    elseif ($rank === 2) $badgeClass = 'rank-2';
                                    elseif ($rank === 3) $badgeClass = 'rank-3';
                                ?>
                                <tr>
                                    <td class="text-center">
                                        <span class="badge-rank <?= $badgeClass ?>">#<?= $rank ?></span>
                                    </td>
                                    <td class="fw-medium text-dark"><?= htmlspecialchars($row['penyakit']) ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-primary rounded-pill px-3 py-2 fs-6"><?= $row['jumlah'] ?> Pasien</span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="text-center py-4 text-muted">Belum ada data diagnosa penyakit di database.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Grafik Batang -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title">📉 Grafik Batang 10 Besar Penyakit</h5>
            <div class="d-print-none">
                <button onclick="exportChartPNG()" class="btn btn-sm btn-primary">Export PNG</button>
            </div>
        </div>
        <div class="card-body">
            <div class="chart-container">
                <canvas id="barChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const labels = <?= json_encode($labels) ?>;
    const dataJumlah = <?= json_encode($data_jumlah) ?>;

    if(labels.length > 0) {
        const ctx = document.getElementById('barChart').getContext('2d');
        
        // Buat gradien warna untuk grafik
        const gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(54, 162, 235, 0.8)');
        gradient.addColorStop(1, 'rgba(54, 162, 235, 0.2)');

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Jumlah Kasus',
                    data: dataJumlah,
                    backgroundColor: gradient,
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 2,
                    borderRadius: 6,
                    hoverBackgroundColor: 'rgba(255, 99, 132, 0.8)',
                    hoverBorderColor: 'rgba(255, 99, 132, 1)'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: { size: 14 },
                        bodyFont: { size: 14 }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0,
                            font: { size: 13 }
                        },
                        grid: {
                            color: '#f0f0f0'
                        }
                    },
                    x: {
                        ticks: {
                            font: { size: 12 }
                        },
                        grid: {
                            display: false
                        }
                    }
                },
                animation: {
                    duration: 1500,
                    easing: 'easeOutQuart'
                }
            }
        });
    } else {
        document.getElementById('barChart').parentElement.innerHTML = '<p class="text-center text-muted mt-5">Grafik tidak tersedia karena belum ada data penyakit.</p>';
    }
});

// Export Functions
function exportTableExcel() {
    let table = document.getElementById('tablePenyakit');
    let wb = XLSX.utils.table_to_book(table, {sheet: "Top 10 Penyakit"});
    XLSX.writeFile(wb, "Top_10_Diagnosa_Penyakit.xlsx");
}

function exportTablePDF() {
    let element = document.getElementById('tablePenyakit').closest('.card');
    
    let opt = {
        margin:       10,
        filename:     'Top_10_Diagnosa_Penyakit.pdf',
        image:        { type: 'jpeg', quality: 0.98 },
        html2canvas:  { scale: 2 },
        jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
    };
    
    // Hide buttons inside the card before exporting
    let buttons = element.querySelectorAll('.d-print-none');
    buttons.forEach(b => b.style.display = 'none');
    
    html2pdf().set(opt).from(element).save().then(() => {
        buttons.forEach(b => b.style.display = ''); // restore
    });
}

function exportChartPNG() {
    let canvas = document.getElementById('barChart');
    let imgURL = canvas.toDataURL("image/png");
    let link = document.createElement('a');
    link.download = 'Grafik_10_Besar_Penyakit.png';
    link.href = imgURL;
    link.click();
}

function exportAllExcel() {
    exportTableExcel();
    setTimeout(() => {
        alert("Catatan: Hanya data tabel yang di-export ke Excel. Untuk melihat tabel dan grafik secara bersamaan, silakan gunakan 'Export All PDF'.");
    }, 500);
}

function exportAllPDF() {
    let element = document.querySelector('.dashboard-container');
    
    let opt = {
        margin:       10,
        filename:     'Dashboard_Laporan_Penyakit.pdf',
        image:        { type: 'jpeg', quality: 0.98 },
        html2canvas:  { scale: 2 },
        jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
    };
    
    // Hide all print-none buttons
    let buttons = element.querySelectorAll('.d-print-none');
    buttons.forEach(b => b.style.display = 'none');
    
    html2pdf().set(opt).from(element).save().then(() => {
        // Restore buttons
        buttons.forEach(b => b.style.display = '');
    });
}
</script>

</body>
</html>
