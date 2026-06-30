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
    INDEX `idx_tiket_user_id` (`user_id`),
    CONSTRAINT `fk_tiket_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Optional: create akun admin menggunakan hash password.
-- Contoh: php -r "echo password_hash('admin123', PASSWORD_DEFAULT) . PHP_EOL;"
-- INSERT INTO users (nama, username, password, role) VALUES ('Admin', 'admin', '<HASHED_PASSWORD>', 'admin');
