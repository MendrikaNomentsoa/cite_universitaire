<?php
// controllers/EcoleController.php

require_once __DIR__ . '/../models/EcoleModel.php';
require_once __DIR__ . '/../utils/Response.php';

class EcoleController {
    private EcoleModel $model;

    public function __construct() {
        $this->model = new EcoleModel();
    }

    // GET /ecoles
    public function index(): void {
        Response::success($this->model->findAll());
    }

    // GET /ecoles/{id}
    public function show(int $id): void {
        $ecole = $this->model->findById($id);
        if (!$ecole) Response::error('École introuvable.', 404);
        Response::success($ecole);
    }

    // POST /ecoles
    public function store(): void {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        if (empty($data['nomEcole'])) Response::error('nomEcole est obligatoire.');
        $id = $this->model->create($data);
        Response::success(['numEcole' => $id], 'École créée.');
    }

    // PUT /ecoles/{id}
    public function update(int $id): void {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        if (empty($data['nomEcole'])) Response::error('nomEcole est obligatoire.');
        $nb = $this->model->update($id, $data);
        if ($nb === 0) Response::error('École introuvable.', 404);
        Response::success(null, 'École modifiée.');
    }

    // DELETE /ecoles/{id}
    public function delete(int $id): void {
        $nb = $this->model->delete($id);
        if ($nb === 0) Response::error('École introuvable.', 404);
        Response::success(null, 'École supprimée.');
    }
}