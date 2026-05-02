<?php
// models/UtilisateurModel.php

require_once __DIR__ . '/Model.php';

class UtilisateurModel extends Model {

    public function findAll(): array {
        return $this->fetchAll(
            "SELECT numutilisateur, nom, tell, email, role FROM utilisateur ORDER BY nom"
        );
    }

    public function findById(int $id): array|false {
        return $this->fetchOne(
            "SELECT numutilisateur, nom, tell, email, role FROM utilisateur WHERE numutilisateur = :id",
            [':id' => $id]
        );
    }

    public function create(array $data): string {
        return $this->insert(
            "INSERT INTO utilisateur (nom, tell, email, password, role)
             VALUES (:nom, :tell, :email, :password, :role)
             RETURNING numutilisateur",
            [
                ':nom'      => $data['nom'],
                ':tell'     => $data['tell']     ?? null,
                ':email'    => $data['email'],
                ':password' => password_hash($data['password'], PASSWORD_BCRYPT),
                ':role'     => $data['role']     ?? 'agent',
            ]
        );
    }

    public function update(int $id, array $data): int {
        $fields = [];
        $params = [':id' => $id];

        if (isset($data['nom']))      { $fields[] = 'nom = :nom';         $params[':nom']      = $data['nom']; }
        if (isset($data['tell']))     { $fields[] = 'tell = :tell';       $params[':tell']     = $data['tell']; }
        if (isset($data['email']))    { $fields[] = 'email = :email';     $params[':email']    = $data['email']; }
        if (isset($data['role']))     { $fields[] = 'role = :role';       $params[':role']     = $data['role']; }
        if (isset($data['password'])) {
            $fields[] = 'password = :password';
            $params[':password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }

        if (empty($fields)) return 0;

        return $this->execute(
            "UPDATE utilisateur SET " . implode(', ', $fields) . " WHERE numutilisateur = :id",
            $params
        );
    }

    public function delete(int $id): int {
        return $this->execute(
            "DELETE FROM utilisateur WHERE numutilisateur = :id",
            [':id' => $id]
        );
    }

    public function authenticate(string $email, string $password): array|false {
        $user = $this->fetchOne(
            "SELECT * FROM utilisateur WHERE email = :email",
            [':email' => $email]
        );
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }
}