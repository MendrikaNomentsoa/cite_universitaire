<?php
// ============================================================
// models/Model.php
// Classe de base pour tous les modèles
// ============================================================

require_once __DIR__ . '/../config/database.php';
 
abstract class Model {
    protected PDO $db;
 
    public function __construct() {
        $this->db = Database::getInstance();
    }
 
    protected function fetchAll(string $sql, array $params = []): array {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
 
    protected function fetchOne(string $sql, array $params = []): array|false {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }
 
    protected function execute(string $sql, array $params = []): int {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }
 
    protected function insert(string $sql, array $params = []): string {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }
}
 
