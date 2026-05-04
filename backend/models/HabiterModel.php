<?php
// models/EcoleModel.php

require_once __DIR__ . '/Model.php';

class EcoleModel extends Model {

    // public function findAll(): array {
    //     return $this->fetchAll("SELECT * FROM ecole ORDER BY nomecole");
    // }

    // public function findById(int $id): array|false {
    //     return $this->fetchOne(
    //         "SELECT * FROM ecole WHERE numecole = :id",
    //         [':id' => $id]
    //     );
    // }

    // public function create(array $data): string {
    //     return $this->insert(
    //         "INSERT INTO ecole (nomecole) VALUES (:nom) RETURNING numecole",
    //         [':nom' => $data['nomEcole']]
    //     );
    // }

    // public function update(int $id, array $data): int {
    //     return $this->execute(
    //         "UPDATE ecole SET nomecole = :nom WHERE numecole = :id",
    //         [':nom' => $data['nomEcole'], ':id' => $id]
    //     );
    // }

    public function delete(int $id): int {
        return $this->execute(
            "DELETE FROM HABITER WHERE numetudiant = :id",
            [':id' => $id]
        );
    }
}