-- Database setup untuk SIM Penjaminan Mutu
-- Jalankan script ini untuk membuat database

CREATE DATABASE IF NOT EXISTS sim_pm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Grant privileges (opsional, sesuaikan dengan user MySQL Anda)
-- GRANT ALL PRIVILEGES ON sim_pm.* TO 'root'@'localhost';
-- FLUSH PRIVILEGES;

USE sim_pm;

-- Database siap digunakan
-- Selanjutnya jalankan: php artisan migrate --seed
