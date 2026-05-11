<?php
// controllers/EtudiantController.php

require_once __DIR__ . '/../models/EtudiantModel.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../models/HabiterModel.php';
require_once __DIR__ . '/../models/LogementModel.php';
require_once __DIR__ . '/../models/ChambreModel.php';
class EtudiantController {
    private EtudiantModel $model;
    private HabiterModel $relationHabiter;
    private ChambreModel $chambre;
    private LogementModel $logement;
    public function __construct() {
        $this->model = new EtudiantModel();
        $this->relationHabiter=new HabiterModel();
        $this->chambre=new ChambreModel();
        $this->logement=new LogementModel();
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
    // GET /etudiants/search
    public function search()
    {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        $listeEtudiant=$this->model->search($data["id"]);
        if(!$listeEtudiant) Response::error("Etudiant introuvable");
        Response::success($listeEtudiant,"etudiant trouvé ".$data["id"]);
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
    //pour les renouvellements d'une demande,on doit ajouter type=renouveller
    public function update(int $id): void {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        if (empty($data)) Response::error('Aucune donnée fournie.');
        if(isset($data["type"]))
        {
            $etudiant=$this->model->findById($id);
            if($etudiant["niveau"]=="M2")
            {
                $this->delete($id);
                Response::error("Les etudiants en M2 ne peuvent plus renouveller une demande");
            }else if($etudiant["estexclus"])
            {
                Response::error("L'etudiant est exclus");
            }else{
                $this->model->update($id,$data);
                $this->relationHabiter->update($id,["debutRenouvellement"=>date("Y-m-d")]);
                Response::success(null,"Etudiant renouvelé avec succès");
            }
        }else{
        $this->model->update($id, $data);
        Response::success(null, 'Étudiant modifié.');
        }
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
        Response::success(null,"Etudiant permuté avec succès");
    }
    //PATCH /etudiants/{id}
    //permet de reinclure un etudiant exclus
    public function reintegrer($id)
    {
        $this->model->update($id,["estExclus"=>"FALSE"]);
        Response::success(null,"Etudiant reintegré avec succès");
    }
    //PATCH /etudiants/deplacer
    public function deplacer()
    {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        $ancienLogement=$this->relationHabiter->findById($data["numEtudiant"]);
        $placeAncienLogement=++$this->logement->findById($ancienLogement["numlogement"])["placedisponible"];
        $placeAncienChambre=++$this->chambre->findById($ancienLogement["numchambre"])["placedisponiblechambre"];
        
        $placeNouveauLogement=--$this->logement->findById($data["numLogement"])["placedisponible"];
        $placeNouveauChambre=--$this->chambre->findById($data["numChambre"])["placedisponiblechambre"];
        //Response::success([$placeNouveauChambre,$placeNouveauLogement]);
        //modifier le nombre de place pour les chambres et logement (ancien et nouveau)
        $this->logement->update($ancienLogement["numlogement"],["placeDisponible"=>$placeAncienLogement]);
        $this->chambre->update($ancienLogement["numchambre"],["placeDisponibleChambre"=>$placeAncienChambre]);
        $this->logement->update($data["numLogement"],["placeDisponible"=>$placeNouveauLogement]);
        $this->chambre->update($data["numChambre"],["placeDisponibleChambre"=>$placeNouveauChambre]);
        $this->relationHabiter->update($data["numEtudiant"],["numLogement"=>$data["numLogement"],"numChambre"=>$data["numChambre"]]);
        Response::success(null,"Etudiant deplacé avec succès");
    }
//  DELETE /etudiants
    public function exclureNonRenouveller()
    {
        $id=$this->relationHabiter->findRenouvellementTerminer();
        for($i=0;$i<count($id)/2;$i++)
        {
            $this->relationHabiter->delete($id[$i]);
            $this->model->update($id[$i],['estExclus'=>true]);
        }
        Response::success(null,"Les etudiants n'ayant pas renouvellé sa demande sont exclus");
    }
}