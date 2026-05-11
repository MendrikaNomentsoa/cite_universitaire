<?php
// controllers/LogementController.php

require_once __DIR__ . '/../models/LogementModel.php';
require_once __DIR__ . '/../utils/Response.php';

class LogementController {
    private LogementModel $model;

    public function __construct() {
        $this->model = new LogementModel();
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