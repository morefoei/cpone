<?php

function ensureRegistrasiSchema($koneksi) {
    mysqli_query($koneksi, "CREATE TABLE IF NOT EXISTS tabel_registrasi (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nomor_rm VARCHAR(80) NOT NULL,
        nama_pasien VARCHAR(150) NOT NULL,
        tanggal_lahir DATE NULL,
        jenis_kelamin VARCHAR(10) DEFAULT NULL,
        tgl_masuk DATE NOT NULL,
        penyakit VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $columnCheck = mysqli_query($koneksi, "SHOW COLUMNS FROM tabel_resume_medis LIKE 'registrasi_id'");
    if ($columnCheck && mysqli_num_rows($columnCheck) == 0) {
        mysqli_query($koneksi, "ALTER TABLE tabel_resume_medis ADD COLUMN registrasi_id INT NULL AFTER id");
    }

    mysqli_query($koneksi, "INSERT INTO tabel_registrasi (nomor_rm, nama_pasien, tanggal_lahir, jenis_kelamin, tgl_masuk, penyakit)
        SELECT trm.nomor_rm, trm.nama_pasien, NULLIF(trm.tanggal_lahir, ''), trm.jenis_kelamin, trm.tgl_masuk, trm.diagnosa_masuk
        FROM tabel_resume_medis trm
        WHERE COALESCE(trm.diagnosa_utama, '') = ''
          AND COALESCE(trm.diagnosa_masuk, '') <> ''
          AND COALESCE(trm.nomor_rm, '') <> ''
          AND COALESCE(trm.nama_pasien, '') <> ''
          AND COALESCE(trm.tgl_masuk, '') <> ''
          AND NOT EXISTS (
              SELECT 1
              FROM tabel_registrasi r
              WHERE r.nomor_rm = trm.nomor_rm
                AND r.nama_pasien = trm.nama_pasien
                AND r.tgl_masuk = trm.tgl_masuk
          )");
}

function getRegistrasiList($koneksi) {
    ensureRegistrasiSchema($koneksi);

    $registrasi = [];
    $result = mysqli_query($koneksi, "SELECT * FROM tabel_registrasi ORDER BY id DESC");
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $registrasi[] = $row;
        }
    }

    return $registrasi;
}

function getRegistrasiById($koneksi, $id) {
    ensureRegistrasiSchema($koneksi);

    $id = (int) $id;
    if ($id <= 0) {
        return null;
    }

    $result = mysqli_query($koneksi, "SELECT * FROM tabel_registrasi WHERE id = $id LIMIT 1");
    return $result ? mysqli_fetch_assoc($result) : null;
}
