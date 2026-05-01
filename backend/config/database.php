<?php
// config/database.php

class Database {
    private static ?PDO $instance = null;

    private static string $host     = 'localhost';
    private static string $port     = '5432';
    private static string $dbname   = 'cite_universitaire';
    private static string $user     = 'postgres';
    private static string $password = 'your_password';

    private function __construct() {}

    public static function getInstance(): PDO {
        if (self::$instance === null) {
            $dsn = "pgsql:host=" . self::$host
                 . ";port=" . self::$port
                 . ";dbname=" . self::$dbname;
            try {
                self::$instance = new PDO($dsn, self::$user, self::$password, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                http_response_code(500);
                die(json_encode(['error' => 'Connexion BD échouée : ' . $e->getMessage()]));
            }
        }
        return self::$instance;
    }
}