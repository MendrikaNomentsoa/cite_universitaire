<?php
require_once __DIR__. '/Model.php';

class RealiserModel extends Model
{
    public function create(array $data): string {
        return $this->insert(
            "INSERT INTO REALISER(numEtudiant,numDemande) VALUES (:etudiant,:demande)  RETURNING numEtudiant",
            [':etudiant'=>$data["numEtudiant"],':demande'=>$data["numDemande"]]
        );
    }
}