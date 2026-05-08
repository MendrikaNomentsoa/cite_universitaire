<?php
require_once __DIR__. '/Model.php';

class EtudierModel extends Model
{
    public function create(array $data): string {
        return $this->insert(
            "INSERT INTO ETUDIER(numEtudiant,numEcole) VALUES (:etudiant,:ecole) RETURNING numEtudiant",
            [':etudiant'=>$data[""],':ecole'=>$data[""]]
        );
    }
}