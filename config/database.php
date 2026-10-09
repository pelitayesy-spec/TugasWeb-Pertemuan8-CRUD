<?php
declare(strict_types=1);

/**
 * Koneksi PDO dengan Singleton pattern.
 * Hanya ada SATU objek Database selama satu request, jadi koneksi
 * ke MySQL tidak dibuat berulang-ulang.
 *
 * Sesuaikan konstanta di bawah dengan pengaturan MySQL di komputermu.
 */
define('DB_HOST', 'localhost');
define('DB_NAME', 'inventaris_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

final class Database
{
    private static ?Database $instance = null;
    private PDO $pdo;

    // constructor private: tidak bisa di-new dari luar class
    private function __construct()
    {
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET);

        try {
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,   // error jadi exception
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,                    // prepared statement asli
            ]);
        } catch (PDOException $e) {
            // Detail error hanya ke log server, bukan ke layar pengguna
            error_log('Koneksi DB gagal: ' . $e->getMessage());
            throw new RuntimeException('Koneksi database gagal. Periksa pengaturan di config/database.php dan pastikan schema.sql sudah di-import.');
        }
    }

    // cegah duplikasi objek
    private function __clone() {}

    public function __wakeup()
    {
        throw new LogicException('Singleton tidak boleh di-unserialize.');
    }

    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->pdo;
    }
}
