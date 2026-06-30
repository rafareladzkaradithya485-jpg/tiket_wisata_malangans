<?php
function bersih_data($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return mysqli_real_escape_string($conn, $data);
}
function rupiah($angka){
    return "Rp " . number_format($angka, 0, ',', '.');
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = getenv('DB_HOST') ?: getenv('MYSQL_HOST') ?: getenv('MYSQLHOST') ?: "localhost";
$user = getenv('DB_USER') ?: getenv('MYSQL_USER') ?: getenv('MYSQL_USERNAME') ?: "root";
$pass = getenv('DB_PASS') ?: getenv('MYSQL_PASSWORD') ?: getenv('MYSQL_PASS') ?: "";
$db   = getenv('DB_NAME') ?: getenv('MYSQL_DATABASE') ?: getenv('MYSQL_DB') ?: "db_wisata";
$port = getenv('DB_PORT') ?: getenv('MYSQL_PORT') ?: 3306;

$conn = mysqli_connect($host, $user, $pass, $db, $port);

if (!$conn) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!function_exists('rupiah')) {
    function rupiah($angka) {
        return "Rp " . number_format($angka, 0, ',', '.');
    }
}

function input_bersih($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return mysqli_real_escape_string($conn, $data);
}

date_default_timezone_set('Asia/Jakarta');


?>