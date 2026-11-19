<?php

require_once __DIR__ . '/const.php';

class Database
{
    private static ?PDO $sharedConn = null;

    public function __construct()
    {
        if (self::$sharedConn === null) {
            $host = $_ENV['DB_HOST'] ?? $_SERVER['DB_HOST'] ?? getenv('DB_HOST') ?: 'localhost';
            $port = $_ENV['DB_PORT'] ?? $_SERVER['DB_PORT'] ?? getenv('DB_PORT') ?: '3306';
            $dbname = $_ENV['DB_NAME'] ?? $_SERVER['DB_NAME'] ?? getenv('DB_NAME') ?: 'db_eicg';
            $user = $_ENV['DB_USER'] ?? $_SERVER['DB_USER'] ?? getenv('DB_USER') ?: 'root';
            $pass = $_ENV['DB_PASSWORD'] ?? $_ENV['DB_PASS'] ?? $_SERVER['DB_PASSWORD'] ?? $_SERVER['DB_PASS'] ?? getenv('DB_PASSWORD') ?? getenv('DB_PASS') ?? '';

            try {
                self::$sharedConn = new PDO("mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4", $user, $pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                ]);
            } catch (PDOException $e) {
                die("Erreur de connexion à la base de données : " . $e->getMessage());
            }
        }
    }

    public function getCon(): PDO
    {
        return self::$sharedConn;
    }
}
