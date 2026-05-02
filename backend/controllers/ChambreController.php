<?php
// controllers/ChambreController.php

require_once __DIR__ . '/../models/ChambreModel.php';
require_once __DIR__ . '/../utils/Response.php';

class ChambreController {
    private ChambreModel $model;

    public function __construct() {
        $this->model = new ChambreModel();
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
        $nb = $this->model->delete($id);
        if ($nb === 0) Response::error('Chambre introuvable.', 404);
        Response::success(null, 'Chambre supprimée.');
    }
}