<?php
// controllers/DemandeController.php

require_once __DIR__ . '/../models/DemandeModel.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__. '/../models/EtudiantModel.php';
require_once __DIR__. '/../models/EtudierModel.php';
require_once __DIR__. '/../models/HabiterModel.php';
require_once __DIR__. '/../models/ChambreModel.php';
require_once __DIR__. '/../models/LogementModel.php';
//require_once __DIR__. '/../models/RealiserModel.php';
class DemandeController {
    private DemandeModel $model;
    private EtudiantModel $etudiant;
    private EtudierModel $relationEtudier;
    private HabiterModel $relationHabiter;

    private ChambreModel $chambre;
    private LogementModel $logement;
    //private RealiserModel $relationRealiser;
    public function __construct() {
        $this->model = new DemandeModel();
        $this->etudiant=new EtudiantModel();
        $this->relationEtudier=new EtudierModel();
        $this->relationHabiter=new HabiterModel();
        $this->logement=new LogementModel();
        $this->chambre=new ChambreModel();
        //$this->relationRealiser=new RealiserModel();
    }

    // GET /demandes
    
    public function index(): void {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        if(isset($data["nbJour"]))
        {
            
            $date=new DateTime();
            $date->sub(new DateInterval('P'.$data["nbJour"]."D"));
            
            Response::success($this->model->listerDemandeAvantJour($date->format("Y-m-d")));
        }else{
            Response::success($this->model->findAll());
        }
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
    //ajout de l'information de l'etudiant
        foreach (['nomEtudiant', 'prenoms', 'anneeNaissance', 'niveau', 'sexe', 'numEcole'] as $champ) {
            if (empty($data[$champ])) Response::error("Champ obligatoire : $champ");
        }
        $etudiant=$this->etudiant->search($data['numCin']);
        if(!$etudiant)
        {
            $numEtudiant=$this->etudiant->create($data);
        }else if($etudiant[0]["estexclus"]){
            Response::error("L'etudiant est déjà exclus");
        }else{
        $numEtudiant=$etudiant[0]["numetudiant"];
        }
    //ajout de la relation entre l'etudiant et l'ecole
        if(!$this->relationEtudier->findById(['numEcole'=>$data['numEcole'],'numEtudiant'=>$numEtudiant]))
        {
            $this->relationEtudier->create(["numEtudiant"=>$numEtudiant,"numEcole"=>$data["numEcole"]]);
        }
        $id = $this->model->create(array_merge($data,['numEtudiant'=>$numEtudiant]));
        //$this->relationRealiser->create(["numEtudiant"=>$numEtudiant,"numDemande"=>$id]);
        Response::success(['numDemande' => $id,'numEtudiant'=>$numEtudiant], 'Demande créée.');
    }

    // PUT /demandes/{id}
    //cette methode est utilisé pour valider les demandes des etudiants
    //data contient les numero de chambre et de logement choisi
    public function update(int $id): void {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        if (empty($data)) Response::error('Aucune donnée fournie.');
        //cette fonctionnalité est pour la validation des demandes
            $informationDemande=$this->model->findById($id);
            $this->relationHabiter->create(array_merge($data,["numEtudiant"=>$informationDemande['numetudiant'],"debutInscription"=>date('Y-m-d')]));
            //modification des places disponibles
            $placeDisponibleLogement=--$this->logement->findById($data['numLogement'])["placedisponible"];
            $placeDisponibleChambre=--$this->chambre->findById($data["numChambre"])["placedisponiblechambre"];
            $this->logement->update($data['numLogement'],['placeDisponible'=>$placeDisponibleLogement]);
            $this->chambre->update($data['numChambre'],['placeDisponibleChambre'=>$placeDisponibleChambre]);
            $this->model->update($id,['etatDemande'=>'valide']);
        //fin de cette fonctionnalité
//        $this->model->update($id, $data);
        Response::success(null, 'Demande modifiée.');
    }

    // DELETE /demandes/{id}
    public function delete(int $id): void {
        $nb = $this->model->delete($id);
        if ($nb === 0) Response::error('Demande introuvable.', 404);
        Response::success(null, 'Demande supprimée.');
    }
}