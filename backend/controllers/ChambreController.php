<?php
// controllers/ChambreController.php

require_once __DIR__ . '/../models/ChambreModel.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../models/LogementModel.php';
class ChambreController {
    private ChambreModel $model;
    private LogementModel $logement;
    public function __construct() {
        $this->model = new ChambreModel();
        $this->logement=new LogementModel();
    }

    // GET /chambres
    public function index(): void {
        Response::success($this->model->findAll());
    }

    // GET /chambres/{id}
    public function show(int $id): void {
        $c = $this->model->findById($id);
        if (!$c) Response::error('Chambre introuvable.', 404);
        Response::success($c);
    }

    // GET /logements/{id}/chambres  — appelé depuis le routeur
    public function findByLogement(int $numLogement): void {
        Response::success($this->model->findByLogement($numLogement));
    }

    // POST /chambres
    public function store(): void {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        foreach (['placeTotalChambre', 'numLogement'] as $champ) {
            if (empty($data[$champ])) Response::error("Champ obligatoire : $champ");
        }
        $id = $this->model->create($data);
        if(isset($data["placeTotalChambre"]))
        {
            $infoLogement=$this->logement->findById($data["numLogement"]);
            $placeTotalLogement=$infoLogement["placetotal"];
            $placeDisponible=$infoLogement["placedisponible"];
            $this->logement->update($data["numLogement"],["placeTotal"=>$placeTotalLogement+$data["placeTotalChambre"],"placeDisponible"=>$placeDisponible+$data["placeTotalChambre"]]);
        }
        Response::success(['numChambre' => $id], 'Chambre créée.');
    }

    // PUT /chambres/{id}
    public function update(int $id): void {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        if (empty($data)) Response::error('Aucune donnée fournie.');
        $this->model->update($id, $data);
        Response::success(null, 'Chambre modifiée.');
    }

    // DELETE /chambres/{id}
    public function delete(int $id): void {
        $infoChambre=$this->model->findById($id);
        if($infoChambre["placedisponiblechambre"]==$infoChambre["placetotalchambre"])
        {
            $placeDispoLogement=$this->logement->findById($infoChambre["numlogement"])["placedisponible"];
            $placeTotalLogement=$this->logement->findById($infoChambre["numlogement"])["placetotal"];
            $this->logement->update($infoChambre["numlogement"],["placeDisponible"=>$placeDispoLogement-$infoChambre["placedisponiblechambre"],"placeTotal"=>$placeTotalLogement-$infoChambre["placetotalchambre"]]);
            $this->model->update($id,["etatChambre"=>"inactif"]);
        }else
        {
            Response::error("Il y a encore des etudiants dans cette chambre");
        }
//        $nb = $this->model->delete($id);
  //      if ($nb === 0) Response::error('Chambre introuvable.', 404);
        Response::success(null, 'Chambre supprimée.');
    }
}