<?php

function ensureDokterSchema($koneksi) {
    mysqli_query($koneksi, "CREATE TABLE IF NOT EXISTS tabel_dokter (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nama_dokter VARCHAR(150) NOT NULL,
        nomor_dokter VARCHAR(80) NOT NULL,
        jenis_dokter ENUM('Umum', 'Spesialis') NOT NULL DEFAULT 'Umum',
        spesialis VARCHAR(120) DEFAULT NULL,
        tanda_tangan VARCHAR(255) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $columnCheck = mysqli_query($koneksi, "SHOW COLUMNS FROM tabel_resume_medis LIKE 'dpjp_pulang_dokter_id'");
    if ($columnCheck && mysqli_num_rows($columnCheck) == 0) {
        mysqli_query($koneksi, "ALTER TABLE tabel_resume_medis ADD COLUMN dpjp_pulang_dokter_id INT NULL AFTER nama_dpjp_pulang");
    }
}

function getDokterList($koneksi) {
    ensureDokterSchema($koneksi);

    $dokter = [];
    $result = mysqli_query($koneksi, "SELECT * FROM tabel_dokter ORDER BY nama_dokter ASC");
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $dokter[] = $row;
        }
    }

    return $dokter;
}

function getDokterById($koneksi, $id) {
    ensureDokterSchema($koneksi);

    $id = (int) $id;
    if ($id <= 0) {
        return null;
    }

    $result = mysqli_query($koneksi, "SELECT * FROM tabel_dokter WHERE id = $id LIMIT 1");
    return $result ? mysqli_fetch_assoc($result) : null;
}

function dokterLabel($dokter) {
    if (!$dokter) {
        return '';
    }

    $jenis = $dokter['jenis_dokter'] ?? '';
    $spesialis = trim($dokter['spesialis'] ?? '');
    $jenisLengkap = $jenis === 'Spesialis' && $spesialis !== '' ? "$jenis - $spesialis" : $jenis;

    return trim(($dokter['nama_dokter'] ?? '') . ' | ' . ($dokter['nomor_dokter'] ?? '') . ' | ' . $jenisLengkap, ' |');
}

function code128BSvg($text, $height = 82, $module = 1.25) {
    $patterns = [
        '212222','222122','222221','121223','121322','131222','122213','122312','132212','221213',
        '221312','231212','112232','122132','122231','113222','123122','123221','223211','221132',
        '221231','213212','223112','312131','311222','321122','321221','312212','322112','322211',
        '212123','212321','232121','111323','131123','131321','112313','132113','132311','211313',
        '231113','231311','112133','112331','132131','113123','113321','133121','313121','211331',
        '231131','213113','213311','213131','311123','311321','331121','312113','312311','332111',
        '314111','221411','431111','111224','111422','121124','121421','141122','141221','112214',
        '112412','122114','122411','142112','142211','241211','221114','413111','241112','134111',
        '111242','121142','121241','114212','124112','124211','411212','421112','421211','212141',
        '214121','412121','111143','111341','131141','114113','114311','411113','411311','113141',
        '114131','311141','411131','211412','211214','211232','2331112'
    ];

    $codes = [104];
    $checksum = 104;
    $length = strlen($text);

    for ($i = 0; $i < $length; $i++) {
        $ord = ord($text[$i]);
        $value = max(0, min(95, $ord - 32));
        $codes[] = $value;
        $checksum += $value * ($i + 1);
    }

    $codes[] = $checksum % 103;
    $codes[] = 106;

    $x = 0;
    $bars = '';
    foreach ($codes as $code) {
        $pattern = $patterns[$code];
        $barsCount = strlen($pattern);
        for ($i = 0; $i < $barsCount; $i++) {
            $width = (int) $pattern[$i] * $module;
            if ($i % 2 == 0) {
                $bars .= '<rect x="' . $x . '" y="0" width="' . $width . '" height="' . $height . '" fill="#000"/>';
            }
            $x += $width;
        }
    }

    return '<svg class="barcode" xmlns="http://www.w3.org/2000/svg" width="' . $x . '" height="' . $height . '" viewBox="0 0 ' . $x . ' ' . $height . '" role="img" aria-label="Barcode dokter">' . $bars . '</svg>';
}
