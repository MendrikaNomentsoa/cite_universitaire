<?php
// controllers/UtilisateurController.php

require_once __DIR__ . '/../models/UtilisateurModel.php';
require_once __DIR__ . '/../utils/Response.php';

class UtilisateurController {
    private UtilisateurModel $model;

    public function __construct() {
        $this->model = new UtilisateurModel();
    }

    // GET /utilisateurs
    public function index(): void {
        Response::success($this->model->findAll());
    }

    // GET /utilisateurs/{id}
    public function show(int $id): void {
        $u = $this->model->findById($id);
        if (!$u) Response::error('Utilisateur introuvable.', 404);
        Response::success($u);
    }

    // POST /utilisateurs
    public function store(): void {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        foreach (['nom', 'email', 'password'] as $champ) {
            if (empty($data[$champ])) Response::error("Champ obligatoire : $champ");
        }
        $id = $this->model->create($data);
        Response::success(['numUtilisateur' => $id], 'Utilisateur créé.');
    }

    // PUT /utilisateurs/{id}
    public function update(int $id): void {
        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        if (empty($data)) Response::error('Aucune donnée fournie.');
        $this->model->update($id, $data);
        Response::success(null, 'Utilisateur modifié.');
    }

    // DELETE /utilisateurs/{id}
    public function delete(int $id): void {
        $nb = $this->model->delete($id);
        if ($nb === 0) Response::error('Utilisateur introuvable.', 404);
        Response::success(null, 'Utilisateur supprimé.');
    }

    // POST /auth/login
    public function login(): void {
        $data     = json_decode(file_get_contents('php://input'), true) ?? [];
        $email    = trim($data['email']    ?? '');
        $password = trim($data['password'] ?? '');
        if (!$email || !$password) Response::error('Email et mot de passe obligatoires.');
        $user = $this->model->authenticate($email, $password);
        if (!$user) Response::error('Identifiants incorrects.', 401);
        unset($user['password']);
        Response::success($user, 'Connexion réussie.');
    }
}