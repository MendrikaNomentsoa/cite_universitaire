<?php
// controllers/EtudiantController.php

require_once __DIR__ . '/../models/EtudiantModel.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../models/HabiterModel.php';
class EtudiantController {
    private EtudiantModel $model;
    private HabiterModel $relationHabiter;
    public function __construct() {
        $this->model = new EtudiantModel();
        $this->relationHabiter=new HabiterModel();
    }

    // GET /etudiants
    //si il y a des parametre avec le get,on liste les etudiants exclus
    public function index(): void {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        if(empty($data))
        {
            Response::success($this->model->findAll());
        }else{
            Response::success($this->model->findAllExclus());
        }

    }

    // GET /etudiants/{id}
    public function show(int $id): void {
        $e = $this->model->findById($id);
        if (!$e) Response::error('Étudiant introuvable.', 404);
        Response::success($e);
    }

    // POST /etudiants
    public function store(): void {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        foreach (['nomEtudiant', 'prenoms', 'anneeNaissance', 'niveau', 'sexe', 'numEcole'] as $champ) {
            if (empty($data[$champ])) Response::error("Champ obligatoire : $champ");
        }
        $id = $this->model->create($data);
        Response::success(['numEtudiant' => $id], 'Étudiant créé.');
    }

    // PUT /etudiants/{id}
    public function update(int $id): void {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        if (empty($data)) Response::error('Aucune donnée fournie.');
        $this->model->update($id, $data);
        Response::success(null, 'Étudiant modifié.');
    }

//on ne supprime pas un étudiant mais on change seulement l'etat de la colonne estExlus en vrai
    // DELETE /etudiants/{id}
    public function delete(int $id): void {
        //suppression de la relation entre etudiant et habiter
        $this->relationHabiter->delete($id);
        $this->model->update($id,['estExclus'=>true]);
        Response::success(null, 'Étudiant exclus avec succès.');
    }

    // PATCH /etudiants
    public function permuter()
    {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        $idPremierEtudiant=$data["idPremier"];
        $idDeuxiemeEtudiant=$data["idDeuxieme"];
        $informationPremier=$this->relationHabiter->findById($idPremierEtudiant);
        $informationDeuxieme=$this->relationHabiter->findById($idDeuxiemeEtudiant);
        $this->relationHabiter->update($idPremierEtudiant,['numLogement'=>$informationDeuxieme["numlogement"],"numChambre"=>$informationDeuxieme['numchambre']]);
        $this->relationHabiter->update($idDeuxiemeEtudiant,['numLogement'=>$informationPremier["numlogement"],"numChambre"=>$informationPremier['numchambre']]);
    }
}