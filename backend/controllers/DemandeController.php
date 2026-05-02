<?php
// controllers/DemandeController.php

require_once __DIR__ . '/../models/DemandeModel.php';
require_once __DIR__ . '/../utils/Response.php';

class DemandeController {
    private DemandeModel $model;

    public function __construct() {
        $this->model = new DemandeModel();
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
        Response::success(['numDemande' => $id], 'Demande créée.');
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