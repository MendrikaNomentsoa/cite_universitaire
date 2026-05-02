<?php
// models/LogementModel.php

require_once __DIR__ . '/Model.php';

class LogementModel extends Model {

    public function findAll(): array {
        return $this->fetchAll("SELECT * FROM logement ORDER BY numlogement");
    }

    public function findById(int $id): array|false {
        return $this->fetchOne(
            "SELECT * FROM logement WHERE numlogement = :id",
            [':id' => $id]
        );
    }

    public function create(array $data): string {
        return $this->insert(
            "INSERT INTO logement (placedisponible, placetotal, etatlogement)
             VALUES (:pd, :pt, :etat)
             RETURNING numlogement",
            [
                ':pd'   => $data['placeDisponible'] ?? 0,
                ':pt'   => $data['placeTotal']      ?? 0,
                ':etat' => $data['etatLogement']    ?? 'actif',
            ]
        );
    }

    public function update(int $id, array $data): int {
        $fields = [];
        $params = [':id' => $id];

        if (isset($data['placeDisponible'])) { $fields[] = 'placedisponible = :pd';   $params[':pd']   = $data['placeDisponible']; }
        if (isset($data['placeTotal']))      { $fields[] = 'placetotal = :pt';         $params[':pt']   = $data['placeTotal']; }
        if (isset($data['etatLogement']))    { $fields[] = 'etatlogement = :etat';     $params[':etat'] = $data['etatLogement']; }

        if (empty($fields)) return 0;

        return $this->execute(
            "UPDATE logement SET " . implode(', ', $fields) . " WHERE numlogement = :id",
            $params
        );
    }

    public function delete(int $id): int {
        return $this->execute(
            "DELETE FROM logement WHERE numlogement = :id",
            [':id' => $id]
        );
    }
}