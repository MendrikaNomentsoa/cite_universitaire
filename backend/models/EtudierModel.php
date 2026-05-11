<?php
require_once __DIR__. '/Model.php';

class EtudierModel extends Model
{
    public function create(array $data): string {
        return $this->insert(
            "INSERT INTO ETUDIER(numEtudiant,numEcole) VALUES (:etudiant,:ecole) RETURNING numEtudiant",
            [':etudiant'=>$data["numEtudiant"],':ecole'=>$data["numEcole"]]
        );
    }

    public function findById($data): array|false {
        return $this->fetchOne(
            "SELECT * FROM ETUDIER WHERE numecole = :id AND numEtudiant=:etudiant",
            [':id' => $data['numEcole'],':etudiant'=>$data['numEtudiant']]
        );
    }
}