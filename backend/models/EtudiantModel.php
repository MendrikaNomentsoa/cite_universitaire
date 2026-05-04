<?php
// models/EtudiantModel.php

require_once __DIR__ . '/Model.php';

class EtudiantModel extends Model {

    public function findAll(): array {
        return $this->fetchAll(
            "SELECT e.*, ec.nomecole
             FROM etudiant e
             JOIN ecole ec ON ec.numecole = e.numecole
             ORDER BY e.nometudiant, e.prenoms"
        );
    }


    public function findById(int $id): array|false {
        return $this->fetchOne(
            "SELECT e.*, ec.nomecole
             FROM etudiant e
             JOIN ecole ec ON ec.numecole = e.numecole
             WHERE e.numetudiant = :id",
            [':id' => $id]
        );
    }
    
    public function create(array $data): string {
        return $this->insert(
            "INSERT INTO etudiant
                (nometudiant, prenoms, anneenaissance, numcin, villeoriginaire,
                 estexclus, niveau, situation, sexe, email, numecole)
             VALUES
                (:nom, :prenoms, :annee, :cin, :ville,
                 FALSE, :niveau, :situation, :sexe, :email, :numecole)
             RETURNING numetudiant",
            [
                ':nom'       => $data['nomEtudiant'],
                ':prenoms'   => $data['prenoms'],
                ':annee'     => $data['anneeNaissance'],
                ':cin'       => $data['numCin']          ?? null,
                ':ville'     => $data['villeOriginaire'] ?? null,
                ':niveau'    => $data['niveau'],
                ':situation' => $data['situation']       ?? null,
                ':sexe'      => $data['sexe'],
                ':email'     => $data['email']           ?? null,
                ':numecole'  => $data['numEcole'],
            ]
        );
    }

    public function update(int $id, array $data): int {
        $fields = [];
        $params = [':id' => $id];

        $map = [
            'nomEtudiant'    => 'nometudiant',
            'prenoms'        => 'prenoms',
            'anneeNaissance' => 'anneenaissance',
            'numCin'         => 'numcin',
            'villeOriginaire'=> 'villeoriginaire',
            'niveau'         => 'niveau',
            'situation'      => 'situation',
            'sexe'           => 'sexe',
            'email'          => 'email',
            'numEcole'       => 'numecole',
            'estExclus'      => 'estexclus',
        ];

        foreach ($map as $input => $col) {
            if (array_key_exists($input, $data)) {
                $fields[] = "$col = :$input";
                $params[":$input"] = $data[$input];
            }
        }

        if (empty($fields)) return 0;

        return $this->execute(
            "UPDATE etudiant SET " . implode(', ', $fields) . " WHERE numetudiant = :id",
            $params
        );
    }

    public function delete(int $id): int {
        return $this->execute(
            "DELETE FROM etudiant WHERE numetudiant = :id",
            [':id' => $id]
        );
    }

    public function findAllExclus()
    {
        return $this->fetchAll(
            "SELECT e.*, ec.nomecole
             FROM etudiant e
             JOIN ecole ec ON ec.numecole = e.numecole
             WHERE e.estExclus=TRUE
             ORDER BY e.nometudiant, e.prenoms"
        ); 
    }

    public function search($id)
    {
        return $this->fetchAll(
        "SELECT e.*, ec.nomecole
        FROM etudiant e
        JOIN ecole ec ON ec.numecole = e.numecole
        WHERE e.numetudiant LIKE :id OR e.numCin LIKE :id
        ",[':id'=>$id]);
    }
}