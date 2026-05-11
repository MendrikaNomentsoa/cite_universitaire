<?php
// models/ChambreModel.php

require_once __DIR__ . '/Model.php';

class ChambreModel extends Model {

    public function findAll(): array {
        return $this->fetchAll("SELECT * FROM chambre ORDER BY numchambre");
    }

    public function findById(int $id): array|false {
        return $this->fetchOne(
            "SELECT * FROM chambre WHERE numchambre = :id",
            [':id' => $id]
        );
    }

    public function findByLogement(int $numLogement): array {
        return $this->fetchAll(
            "SELECT * FROM chambre WHERE numlogement = :nl ORDER BY numchambre",
            [':nl' => $numLogement]
        );
    }

    public function create(array $data): string {
        return $this->insert(
            "INSERT INTO chambre (placedisponiblechambre, placetotalchambre, etatchambre, numlogement)
             VALUES (:pd, :pt, :etat, :nl)
             RETURNING numchambre",
            [
                ':pd'   => $data['placeDisponibleChambre'] ?? $data['placeTotalChambre'],
                ':pt'   => $data['placeTotalChambre'],
                ':etat' => $data['etatChambre']            ?? 'actif',
                ':nl'   => $data['numLogement'],
            ]
        );
    }

    public function update(int $id, array $data): int {
        $fields = [];
        $params = [':id' => $id];

        if (isset($data['placeDisponibleChambre'])) { $fields[] = 'placedisponiblechambre = :pd';  $params[':pd']   = $data['placeDisponibleChambre']; }
        if (isset($data['placeTotalChambre']))      { $fields[] = 'placetotalchambre = :pt';        $params[':pt']   = $data['placeTotalChambre']; }
        if (isset($data['etatChambre']))            { $fields[] = 'etatchambre = :etat';            $params[':etat'] = $data['etatChambre']; }
        if (isset($data['numLogement']))            { $fields[] = 'numlogement = :nl';              $params[':nl']   = $data['numLogement']; }

        if (empty($fields)) return 0;

        return $this->execute(
            "UPDATE chambre SET " . implode(', ', $fields) . " WHERE numchambre = :id",
            $params
        );
    }

    public function delete(int $id): int {
        return $this->execute(
            "DELETE FROM chambre WHERE numchambre = :id",
            [':id' => $id]
        );
    }

    public function deleteChambreLogement($id)
    {
        return $this->execute(
            "UPDATE chambre SET etatChambre='hs' WHERE numlogement = :id",[":id"=>$id]
        );
    }
}