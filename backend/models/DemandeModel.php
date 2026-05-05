<?php
// models/DemandeModel.php

require_once __DIR__ . '/Model.php';

class DemandeModel extends Model {

    public function findAll(): array {
        return $this->fetchAll(
            "SELECT d.*, e.nometudiant, e.prenoms
             FROM demande d
             JOIN realiser r ON d.numdemande = r.numdemande
             JOIN etudiant e ON r.numetudiant = e.numetudiant
             ORDER BY d.datedemande DESC"
        );
    }

    public function findById(int $id): array|false {
        return $this->fetchOne(
            "SELECT d.*, e.nometudiant, e.prenoms, e.numetudiant
             FROM demande d
             JOIN realiser r ON d.numdemande = r.numdemande
             JOIN etudiant e ON r.numetudiant = e.numetudiant
             WHERE d.numdemande = :id",
            [':id' => $id]
        );
    }

    public function create(array $data): string {
        return $this->insert(
            "INSERT INTO demande (datedemande, etatdemande)
             VALUES (:date, :etat)
             RETURNING numdemande",
            [
                ':date' => $data['dateDemande'] ?? date('Y-m-d'),
                ':etat' => $data['etatDemande'] ?? 'attente',
            ]
        );
    }

    public function update(int $id, array $data): int {
        $fields = [];
        $params = [':id' => $id];

        if (isset($data['dateDemande'])) { $fields[] = 'datedemande = :date'; $params[':date'] = $data['dateDemande']; }
        if (isset($data['etatDemande'])) { $fields[] = 'etatdemande = :etat'; $params[':etat'] = $data['etatDemande']; }

        if (empty($fields)) return 0;

        return $this->execute(
            "UPDATE demande SET " . implode(', ', $fields) . " WHERE numdemande = :id",
            $params
        );
    }

    public function delete(int $id): int {
        return $this->execute(
            "DELETE FROM demande WHERE numdemande = :id",
            [':id' => $id]
        );
    }

    public function listerDemandeAvantJour($jour)
    {
        return $this->fetchAll("SELECT d.*, e.nometudiant, e.prenoms
             FROM demande d
             JOIN realiser r ON d.numdemande = r.numdemande
             JOIN etudiant e ON r.numetudiant = e.numetudiant
             WHERE dateDemande<=:jour
             ORDER BY d.datedemande DESC",[":jour"=>$jour]);
    }
}