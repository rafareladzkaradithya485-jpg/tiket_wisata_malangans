<?php
if (!defined('WISATA_MALANG_CONFIG_LOADED')) {
    define('WISATA_MALANG_CONFIG_LOADED', true);

    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    // Disable mysqli exception throwing so we can handle connection failures gracefully
    if (function_exists('mysqli_report')) {
        mysqli_report(MYSQLI_REPORT_OFF);
    }

    // Prefer explicit TCP host; using 127.0.0.1 avoids socket lookup failures
    // First, check if a DATABASE_URL (or RAILWAY_DATABASE_URL) is provided (format: mysql://user:pass@host:port/db)
    $databaseUrl = getenv('DATABASE_URL') ?: getenv('RAILWAY_DATABASE_URL') ?: getenv('DB_URL');
    if ($databaseUrl) {
        $parts = @parse_url($databaseUrl);
        if ($parts !== false && isset($parts['host'])) {
            $host = $parts['host'];
            $user = isset($parts['user']) ? $parts['user'] : (getenv('DB_USER') ?: getenv('MYSQL_USER') ?: 'root');
            $pass = isset($parts['pass']) ? $parts['pass'] : (getenv('DB_PASS') ?: getenv('MYSQL_PASSWORD') ?: '');
            $db   = isset($parts['path']) ? ltrim($parts['path'], '/') : (getenv('DB_NAME') ?: getenv('MYSQL_DATABASE') ?: 'db_wisata');
            $port = isset($parts['port']) ? (int)$parts['port'] : (int)(getenv('DB_PORT') ?: getenv('MYSQL_PORT') ?: 3306);
        }
    } else {
        $host = getenv('DB_HOST') ?: getenv('MYSQL_HOST') ?: getenv('MYSQLHOST') ?: "127.0.0.1";
        $user = getenv('DB_USER') ?: getenv('MYSQL_USER') ?: getenv('MYSQL_USERNAME') ?: "root";
        $pass = getenv('DB_PASS') ?: getenv('MYSQL_PASSWORD') ?: getenv('MYSQL_PASS') ?: "";
        $db   = getenv('DB_NAME') ?: getenv('MYSQL_DATABASE') ?: getenv('MYSQL_DB') ?: "db_wisata";
        $port = (int) (getenv('DB_PORT') ?: getenv('MYSQL_PORT') ?: 3306);
    }

    // Normalize obvious localhost values
    if (is_string($host)) {
        $host = trim($host);
    }

    // Build candidate hosts to try (prefer explicit TCP 127.0.0.1)
    $candidates = [];
    if ($host !== '') {
        $candidates[] = $host;
    }
    if (!in_array('127.0.0.1', $candidates, true)) {
        $candidates[] = '127.0.0.1';
    }

    $conn = null;
    $lastError = '';
    foreach ($candidates as $candidateHost) {
        // If candidate is the literal 'localhost', prefer 127.0.0.1 to force TCP
        $tryHost = ($candidateHost === 'localhost') ? '127.0.0.1' : $candidateHost;
        $tryHost = trim($tryHost);
        // Ensure port is integer
        $tryPort = (int)$port;
        $conn = @mysqli_connect($tryHost, $user, $pass, $db, $tryPort);
        if ($conn) {
            // Found working connection
            break;
        }
        $lastError = mysqli_connect_error();
    }

    if (!$conn) {
        // Provide useful debug info for deployment logs without leaking sensitive creds
        $displayHost = isset($tryHost) ? $tryHost : $host;
        error_log("DB connection failed. Tried host={$displayHost} port={$tryPort} db={$db} user={$user}. Error: {$lastError}");
        die("Koneksi ke database gagal: " . $lastError);
    }

    mysqli_set_charset($conn, 'utf8mb4');

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!function_exists('bersih_data')) {
        function bersih_data($data) {
            global $conn;
            $data = trim($data);
            $data = stripslashes($data);
            $data = htmlspecialchars($data);
            return mysqli_real_escape_string($conn, $data);
        }
    }

    if (!function_exists('rupiah')) {
        function rupiah($angka) {
            return "Rp " . number_format($angka, 0, ',', '.');
        }
    }

if (!function_exists('table_exists')) {
    function table_exists($conn, $table_name) {
        $safe_name = mysqli_real_escape_string($conn, $table_name);
        $result = mysqli_query($conn, "SHOW TABLES LIKE '$safe_name'");
        return $result && mysqli_num_rows($result) > 0;
    }
}

if (!function_exists('column_exists')) {
    function column_exists($conn, $table_name, $column_name) {
        $safe_table = mysqli_real_escape_string($conn, $table_name);
        $safe_column = mysqli_real_escape_string($conn, $column_name);
        $result = mysqli_query($conn, "SHOW COLUMNS FROM `$safe_table` LIKE '$safe_column'");
        return $result && mysqli_num_rows($result) > 0;
    }
}

if (!function_exists('ensure_column_exists')) {
    function ensure_column_exists($conn, $table_name, $column_name, $definition) {
        if (!column_exists($conn, $table_name, $column_name)) {
            mysqli_query($conn, "ALTER TABLE `$table_name` ADD COLUMN `$column_name` $definition");
        }
    }
}

if (!function_exists('wisata_image_path')) {
    function wisata_image_path($nama_wisata) {
        $slug = preg_replace('/[^a-z0-9]+/i', '_', trim(strtolower($nama_wisata)));
        $slug = trim($slug, '_');
        return 'images/' . $slug . '.png';
    }
}

if (!function_exists('resolve_wisata_image_url')) {
    function resolve_wisata_image_url($gambar_url) {
        $gambar_url = trim((string)$gambar_url);

        if ($gambar_url === '') {
            return 'images/placeholder.png';
        }

        if (preg_match('#^https?://#i', $gambar_url)) {
            return $gambar_url;
        }

        $local_file = __DIR__ . DIRECTORY_SEPARATOR . ltrim($gambar_url, '/\\');
        if (file_exists($local_file)) {
            return $gambar_url;
        }

        return 'images/placeholder.png';
    }
}

function ensure_required_tables($conn) {
    $queries = [];

    $queries[] = "CREATE TABLE IF NOT EXISTS users (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        nama VARCHAR(200) NOT NULL,
        username VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('user','admin') NOT NULL DEFAULT 'user',
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    $queries[] = "CREATE TABLE IF NOT EXISTS wisata (
        id_wisata INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        nama_wisata VARCHAR(255) NOT NULL UNIQUE,
        deskripsi LONGTEXT,
        lokasi VARCHAR(255) NOT NULL,
        harga_tiket INT UNSIGNED NOT NULL,
        jam_buka TIME,
        jam_tutup TIME,
        kategori VARCHAR(100),
        rating DECIMAL(3,2) DEFAULT 0.00,
        total_review INT UNSIGNED DEFAULT 0,
        gambar_url VARCHAR(500),
        lat DECIMAL(10, 8),
        lon DECIMAL(11, 8),
        no_hp_contact VARCHAR(20),
        email_contact VARCHAR(100),
        status_aktif BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    $queries[] = "CREATE TABLE IF NOT EXISTS tiket (
        id_tiket INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT UNSIGNED NOT NULL,
        wisata VARCHAR(255) NOT NULL,
        jumlah INT UNSIGNED NOT NULL DEFAULT 1,
        total_harga INT UNSIGNED NOT NULL DEFAULT 0,
        tgl_beli DATETIME NOT NULL,
        status VARCHAR(50) NOT NULL DEFAULT 'pending',
        kode_barcode VARCHAR(100) NOT NULL,
        payment_method VARCHAR(50) DEFAULT NULL,
        payment_id VARCHAR(100) DEFAULT NULL,
        INDEX idx_tiket_user_id (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    $queries[] = "CREATE TABLE IF NOT EXISTS payments (
        id_payment INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        id_tiket INT UNSIGNED NOT NULL,
        user_id INT UNSIGNED NOT NULL,
        jumlah INT UNSIGNED NOT NULL,
        payment_method VARCHAR(50) NOT NULL,
        payment_status ENUM('pending','success','failed','expired') NOT NULL DEFAULT 'pending',
        transaction_id VARCHAR(100) UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    $queries[] = "CREATE TABLE IF NOT EXISTS reviews (
        id_review INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        id_wisata INT UNSIGNED NOT NULL,
        user_id INT UNSIGNED NOT NULL,
        rating INT UNSIGNED,
        ulasan TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    $queries[] = "CREATE TABLE IF NOT EXISTS fasilitas_wisata (
        id_fasilitas INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        id_wisata INT UNSIGNED NOT NULL,
        nama_fasilitas VARCHAR(100) NOT NULL,
        keterangan VARCHAR(255),
        tersedia BOOLEAN DEFAULT TRUE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    foreach ($queries as $sql) {
        mysqli_query($conn, $sql);
    }

    ensure_column_exists($conn, 'tiket', 'payment_method', 'VARCHAR(50) DEFAULT NULL');
    ensure_column_exists($conn, 'tiket', 'payment_id', 'VARCHAR(100) DEFAULT NULL');
    ensure_column_exists($conn, 'tiket', 'kode_barcode', 'VARCHAR(100) NOT NULL');
    ensure_column_exists($conn, 'tiket', 'tgl_beli', 'DATETIME NOT NULL');
    ensure_column_exists($conn, 'tiket', 'status', 'VARCHAR(50) NOT NULL DEFAULT "pending"');

    ensure_column_exists($conn, 'payments', 'payment_status', 'ENUM("pending","success","failed","expired") NOT NULL DEFAULT "pending"');
    ensure_column_exists($conn, 'payments', 'transaction_id', 'VARCHAR(100) UNIQUE');
    ensure_column_exists($conn, 'payments', 'payment_method', 'VARCHAR(50) NOT NULL');
    ensure_column_exists($conn, 'payments', 'user_id', 'INT UNSIGNED NOT NULL');
}

function ensure_seed_data($conn) {
    $result = mysqli_query($conn, "SELECT COUNT(*) as count FROM wisata");
    $row = mysqli_fetch_assoc($result);
    if (!$row || (int)$row['count'] === 0) {
        mysqli_query($conn, "INSERT INTO wisata (nama_wisata, deskripsi, lokasi, harga_tiket, jam_buka, jam_tutup, kategori, rating, gambar_url, lat, lon, no_hp_contact, email_contact, status_aktif) VALUES
            ('Jatim Park 1', 'Jatim Park 1 adalah taman rekreasi keluarga terbesar di Jawa Timur dengan wahana edukatif dan permainan seru.', 'Jl. Oro-Oro Dowo, Kota Batu, Jawa Timur', 150000, '09:00:00', '17:00:00', 'Taman Hiburan', 4.50, 'images/jatim_park_1.png', -7.8945, 112.3050, '0341-597711', 'info@jatimpark.com', TRUE),
            ('Gunung Bromo', 'Gunung Bromo adalah destinasi wisata alam terkenal dengan pemandangan matahari terbit dan kawah yang menakjubkan.', 'Probolinggo, Jawa Timur', 120000, '04:00:00', '17:00:00', 'Alam', 4.80, 'images/gunung_bromo.png', -7.9424, 112.9526, '0325-123456', 'info@bromotour.com', TRUE),
            ('Museum Angkut', 'Museum Angkut menampilkan koleksi transportasi klasik dan modern dari seluruh dunia dengan pameran interaktif.', 'Jl. Terusan Sultan Agung No.2, Kota Batu', 100000, '12:00:00', '20:00:00', 'Edukasi', 4.40, 'images/museum_angkut.png', -7.8835, 112.5070, '0341-512345', 'info@museumangkut.com', TRUE)");

        $wisata1 = mysqli_insert_id($conn);
        mysqli_query($conn, "INSERT INTO fasilitas_wisata (id_wisata, nama_fasilitas, keterangan, tersedia) VALUES
            ($wisata1, 'Wahana Air', 'Berbagai wahana air untuk keluarga', TRUE),
            ($wisata1, 'Taman Bermain', 'Area bermain anak-anak', TRUE)");
    }
}

function input_bersih($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return mysqli_real_escape_string($conn, $data);
}

if (!function_exists('ensure_wisata_image_urls')) {
    function ensure_wisata_image_urls($conn) {
        if (!table_exists($conn, 'wisata')) {
            return;
        }

        $result = mysqli_query($conn, "SELECT id_wisata, nama_wisata, gambar_url FROM wisata");
        while ($row = mysqli_fetch_assoc($result)) {
            $current = trim($row['gambar_url'] ?? '');
            if ($current === '' || strpos($current, 'via.placeholder.com') !== false) {
                $path = wisata_image_path($row['nama_wisata']);
                $safe_path = mysqli_real_escape_string($conn, $path);
                mysqli_query($conn, "UPDATE wisata SET gambar_url = '$safe_path' WHERE id_wisata = " . (int)$row['id_wisata']);
            }
        }
    }
}

    date_default_timezone_set('Asia/Jakarta');

    ensure_required_tables($conn);
    ensure_seed_data($conn);
    ensure_wisata_image_urls($conn);
}

?>