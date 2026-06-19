<?php
// Koneksi database MySQL

$db_host = 'sql204.infinityfree.com';
$db_user = 'if0_42222933';
$db_pass = 'sq8TmcIbM6FQ2D';
$db_name = 'if0_42222933_cp';

$koneksi = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

if (!$koneksi) {
    die('Koneksi database gagal: ' . mysqli_connect_error());
}

mysqli_set_charset($koneksi, 'utf8mb4');
