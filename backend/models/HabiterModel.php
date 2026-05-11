<?php
// models/EcoleModel.php

require_once __DIR__ . '/Model.php';

class HabiterModel extends Model {

    public function findAll(): array {
        return $this->fetchAll("SELECT * FROM HABITER");
    }

    public function findById(int $id): array|false {
        return $this->fetchOne(
            "SELECT * FROM habiter WHERE numetudiant = :id",
            [':id' => $id]
        );
    }

    public function findRenouvellementTerminer()
    {
        return $this->fetchAll("SELECT numEtudiant FROM HABITER WHERE debutRenouvellement + INTERVAL '1 year' < :d",[":d"=>date('Y-m-d')]);
    }
    public function create(array $data): string {
        return $this->insert(
            "INSERT INTO HABITER(numEtudiant,numLogement,numChambre,debutInscription,debutRenouvellement) VALUES (:num,:numLogement,:numChambre,:debutInscription,:debutInscription) RETURNING numEtudiant",
            [':num' => $data['numEtudiant'],':numLogement'=>$data['numLogement'],'numChambre'=>$data['numChambre'],'debutInscription'=>$data['debutInscription']]
        );
    }

    public function update(int $id, array $data): int {
        $fields = [];
        $params = [':id' => $id];

        if (isset($data['numEtudiant'])) { $fields[] = 'numEtudiant = :num';   $params[':num']   = $data['numEtudiant']; }
        if (isset($data['numLogement']))      { $fields[] = 'numLogement = :logement';         $params[':logement']   = $data['numLogement']; }
        if (isset($data['numChambre']))    { $fields[] = 'numChambre = :chambre';     $params[':chambre'] = $data['numChambre']; }
        if (isset($data['debutInscription'])) { $fields[] = 'debutInscription = :inscription';   $params[':inscription']   = $data['debutInscription']; }
        if (isset($data['debutRenouvellement'])) { $fields[] = 'debutRenouvellement = :renouvellement';   $params[':renouvellement']   = $data['debutRenouvellement']; }


        if (empty($fields)) return 0;

        return $this->execute(
            "UPDATE habiter SET " . implode(', ', $fields) . " WHERE numEtudiant = :id",
            $params
        );
    }

    public function delete(int $id): int {
        return $this->execute(
            "DELETE FROM HABITER WHERE numetudiant = :id",
            [':id' => $id]
        );
    }
}