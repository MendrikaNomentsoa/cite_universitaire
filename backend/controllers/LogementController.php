<?php
// controllers/LogementController.php

require_once __DIR__ . '/../models/LogementModel.php';
require_once __DIR__ . '/../utils/Response.php';
require_once __DIR__ . '/../models/ChambreModel.php';
class LogementController {
    private LogementModel $model;
    private ChambreModel $chambre;
    public function __construct() {
        $this->model = new LogementModel();
        $this->chambre=new ChambreModel();
    }

    // GET /logements
    //si type existe,alors, on trie les logements selon les capacités
    public function index(): void {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        if(empty($data))
        {
            Response::success($this->model->findAll());
        }else if($data["type"]==="libre")
        {
            Response::success($this->model->findAllLibre());
        }else{
            Response::success($this->model->findAllOccupe());
        }
    }

    // GET /logements/{id}
    public function show(int $id): void {
        $l = $this->model->findById($id);
        if (!$l) Response::error('Logement introuvable.', 404);
        Response::success($l);
    }

    // POST /logements
    public function store(): void {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        $id = $this->model->create($data);
        $placeTotale=0;
        if(isset($data["listeChambre"]))
        {
            for($i=0;$i<count($data["listeChambre"]);$i++)
            {
                $placeTotale+=$data["listeChambre"][$i];
                $this->chambre->create(["placeTotalChambre"=>$data["listeChambre"][$i],"numLogement"=>$id]);
            }
            $this->model->update($id,["placeDisponible"=>$placeTotale,"placeTotal"=>$placeTotale]);
        }
        Response::success(['numLogement' => $id], 'Logement créé.');
    }

    // PUT /logements/{id}
    public function update(int $id): void {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        if (empty($data)) Response::error('Aucune donnée fournie.');
        $this->model->update($id, $data);
        Response::success(null, 'Logement modifié.');
    }

    // DELETE /logements/{id}
    public function delete(int $id): void {
        $nb = $this->model->delete($id);
        if ($nb === 0) Response::error('Logement introuvable.', 404);
        Response::success(null, 'Logement supprimé.');
    }
}