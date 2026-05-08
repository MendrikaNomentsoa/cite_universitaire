<?php
// controllers/DemandeController.php

require_once __DIR__ . '/../models/DemandeModel.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__. '/../models/EtudiantModel.php';
class DemandeController {
    private DemandeModel $model;
    private EtudiantModel $etudiant;
    public function __construct() {
        $this->model = new DemandeModel();
        $this->etudiant=new EtudiantModel();
    }

    // GET /demandes
    public function index(): void {
        Response::success($this->model->findAll());
    }

    // GET /demandes/{id}
    public function show(int $id): void {
        $d = $this->model->findById($id);
        if (!$d) Response::error('Demande introuvable.', 404);
        Response::success($d);
    }

    // POST /demandes
    public function store(): void {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        $id = $this->model->create($data);
    //ajout de l'information de l'etudiant
        foreach (['nomEtudiant', 'prenoms', 'anneeNaissance', 'niveau', 'sexe', 'numEcole'] as $champ) {
            if (empty($data[$champ])) Response::error("Champ obligatoire : $champ");
        }
        $numEtudiant=$this->etudiant->create($data);
    //ajout de la relation entre l'etudiant et l'ecole
        
        Response::success(['numDemande' => $id,'numEtudiant'=>$numEtudiant], 'Demande créée.');
    }

    // PUT /demandes/{id}
    public function update(int $id): void {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        if (empty($data)) Response::error('Aucune donnée fournie.');
        $this->model->update($id, $data);
        Response::success(null, 'Demande modifiée.');
    }

    // DELETE /demandes/{id}
    public function delete(int $id): void {
        $nb = $this->model->delete($id);
        if ($nb === 0) Response::error('Demande introuvable.', 404);
        Response::success(null, 'Demande supprimée.');
    }
}