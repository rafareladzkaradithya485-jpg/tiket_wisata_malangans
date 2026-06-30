-- Database schema for Wisata_Malang
-- Jika menggunakan Railway MySQL, jalankan hanya CREATE TABLE saja setelah database dibuat.

CREATE TABLE IF NOT EXISTS `users` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `nama` VARCHAR(200) NOT NULL,
    `username` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('user','admin') NOT NULL DEFAULT 'user',
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tiket` (
    `id_tiket` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `wisata` VARCHAR(255) NOT NULL,
    `jumlah` INT UNSIGNED NOT NULL DEFAULT 1,
    `total_harga` INT UNSIGNED NOT NULL DEFAULT 0,
    `tgl_beli` DATETIME NOT NULL,
    `status` VARCHAR(50) NOT NULL DEFAULT 'pending',
    `kode_barcode` VARCHAR(100) NOT NULL,
    `payment_method` VARCHAR(50) DEFAULT NULL,
    `payment_id` VARCHAR(100) DEFAULT NULL,
    INDEX `idx_tiket_user_id` (`user_id`),
    CONSTRAINT `fk_tiket_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel untuk pembayaran
CREATE TABLE IF NOT EXISTS `payments` (
    `id_payment` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `id_tiket` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `jumlah` INT UNSIGNED NOT NULL,
    `payment_method` VARCHAR(50) NOT NULL,
    `payment_status` ENUM('pending','success','failed','expired') NOT NULL DEFAULT 'pending',
    `transaction_id` VARCHAR(100) UNIQUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_payment_tiket` (`id_tiket`),
    INDEX `idx_payment_user` (`user_id`),
    CONSTRAINT `fk_payment_tiket` FOREIGN KEY (`id_tiket`) REFERENCES `tiket`(`id_tiket`) ON DELETE CASCADE,
    CONSTRAINT `fk_payment_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel untuk refund
CREATE TABLE IF NOT EXISTS `refunds` (
    `id_refund` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `id_payment` INT UNSIGNED NOT NULL,
    `id_tiket` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `jumlah_refund` INT UNSIGNED NOT NULL,
    `alasan` TEXT,
    `status` ENUM('pending','approved','rejected','completed') NOT NULL DEFAULT 'pending',
    `request_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `approved_date` TIMESTAMP NULL,
    `completed_date` TIMESTAMP NULL,
    INDEX `idx_refund_tiket` (`id_tiket`),
    INDEX `idx_refund_user` (`user_id`),
    CONSTRAINT `fk_refund_payment` FOREIGN KEY (`id_payment`) REFERENCES `payments`(`id_payment`) ON DELETE CASCADE,
    CONSTRAINT `fk_refund_tiket` FOREIGN KEY (`id_tiket`) REFERENCES `tiket`(`id_tiket`) ON DELETE CASCADE,
    CONSTRAINT `fk_refund_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel untuk analytics & prediksi
CREATE TABLE IF NOT EXISTS `analytics` (
    `id_analytics` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `tanggal` DATE NOT NULL,
    `total_tiket_terjual` INT UNSIGNED DEFAULT 0,
    `total_revenue` BIGINT UNSIGNED DEFAULT 0,
    `wisata_terpopuler` VARCHAR(255),
    `visitor_count` INT UNSIGNED DEFAULT 0,
    `predicted_revenue_next_day` BIGINT UNSIGNED DEFAULT 0,
    `trend` VARCHAR(50),
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_analytics_date` (`tanggal`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Optional: create akun admin menggunakan hash password.
-- Contoh: php -r "echo password_hash('admin123', PASSWORD_DEFAULT) . PHP_EOL;"
-- INSERT INTO users (nama, username, password, role) VALUES ('Admin', 'admin', '<HASHED_PASSWORD>', 'admin');
