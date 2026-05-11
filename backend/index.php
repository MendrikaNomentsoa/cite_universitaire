<?php
// index.php — Routeur principal

header('Access-Control-Allow-Origin: http://localhost:3000');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

require_once __DIR__ . '/utils/Response.php';
require_once __DIR__ . '/config/database.php';

// Autoload
spl_autoload_register(function (string $class): void {
    foreach ([__DIR__ . '/controllers/', __DIR__ . '/models/', __DIR__ . '/utils/'] as $dir) {
        $file = $dir . $class . '.php';
        if (file_exists($file)) { require_once $file; return; }
    }
});

// Lecture de l'URL
$path     = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$base     = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$path     = '/' . ltrim(substr($path, strlen($base)), '/');
$method   = $_SERVER['REQUEST_METHOD'];
$segments = array_values(array_filter(explode('/', $path)));

$resource = $segments[0] ?? '';
$id       = isset($segments[1]) && is_numeric($segments[1]) ? (int)$segments[1] : null;
$sub      = $segments[2] ?? null; // ex: /logements/1/chambres

try {
    switch ($resource) {

        // ── AUTH ──────────────────────────────────────────────
        case 'auth':
            $ctrl = new UtilisateurController();
            if ($sub === 'login' || $segments[1] === 'login') $ctrl->login();
            else Response::error('Route inconnue.', 404);
            break;

        // ── ÉCOLES ───────────────────────────────────────────
        case 'ecoles':
            $ctrl = new EcoleController();
            match (true) {
                $method === 'GET'    && $id === null => $ctrl->index(),
                $method === 'GET'    && $id !== null => $ctrl->show($id),
                $method === 'POST'                  => $ctrl->store(),
                $method === 'PUT'    && $id !== null => $ctrl->update($id),
                $method === 'DELETE' && $id !== null => $ctrl->delete($id),
                default => Response::error('Route inconnue.', 404),
            };
            break;

        // ── UTILISATEURS ─────────────────────────────────────
        case 'utilisateurs':
            $ctrl = new UtilisateurController();
            match (true) {
                $method === 'GET'    && $id === null => $ctrl->index(),
                $method === 'GET'    && $id !== null => $ctrl->show($id),
                $method === 'POST'                  => $ctrl->store(),
                $method === 'PUT'    && $id !== null => $ctrl->update($id),
                $method === 'DELETE' && $id !== null => $ctrl->delete($id),
                default => Response::error('Route inconnue.', 404),
            };
            break;

        // ── ÉTUDIANTS ────────────────────────────────────────
        case 'etudiants':
            $ctrl = new EtudiantController();
            match (true) {
                $method === 'GET'    && $id === null && !isset($segments[1])=> $ctrl->index(),
                $method === 'GET'    && $id !== null => $ctrl->show($id),
                $method=== 'GET'    && $id === null && isset($segments[1]) =>$ctrl->search(),
                $method === 'POST'                  => $ctrl->store(),
                $method === 'PUT'    && $id !== null => $ctrl->update($id),
                $method === 'DELETE' && $id !== null => $ctrl->delete($id),
                $method === 'PATCH'                  => $ctrl->permuter(),
                default => Response::error('Route inconnue.', 404),
            };
            break;

        // ── LOGEMENTS ────────────────────────────────────────
        case 'logements':
            $ctrl        = new LogementController();
            $chambreCtrl = new ChambreController();
            match (true) {
                // GET /logements/1/chambres
                $method === 'GET' && $id !== null && $sub === 'chambres' => $chambreCtrl->findByLogement($id),
                $method === 'GET'    && $id === null => $ctrl->index(),
                $method === 'GET'    && $id !== null => $ctrl->show($id),
                $method === 'POST'   && $id === null => $ctrl->store(),
                $method === 'PUT'    && $id !== null => $ctrl->update($id),
                $method === 'DELETE' && $id !== null => $ctrl->delete($id),
                default => Response::error('Route inconnue.', 404),
            };
            break;

        // ── CHAMBRES ─────────────────────────────────────────
        case 'chambres':
            $ctrl = new ChambreController();
            match (true) {
                $method === 'GET'    && $id === null => $ctrl->index(),
                $method === 'GET'    && $id !== null => $ctrl->show($id),
                $method === 'POST'                  => $ctrl->store(),
                $method === 'PUT'    && $id !== null => $ctrl->update($id),
                $method === 'DELETE' && $id !== null => $ctrl->delete($id),
                default => Response::error('Route inconnue.', 404),
            };
            break;

        // ── DEMANDES ─────────────────────────────────────────
        case 'demandes':
            $ctrl = new DemandeController();
            match (true) {
                $method === 'GET'    && $id === null => $ctrl->index(),
                $method === 'GET'    && $id !== null => $ctrl->show($id),
                $method === 'POST'                  => $ctrl->store(),
                $method === 'PUT'    && $id !== null => $ctrl->update($id),
                $method === 'DELETE' && $id !== null => $ctrl->delete($id),
                default => Response::error('Route inconnue.', 404),
            };
            break;

        default:
            Response::error('Ressource inconnue : ' . htmlspecialchars($resource), 404);
    }

} catch (PDOException $e) {
    error_log($e->getMessage());
    Response::error('Erreur base de données: '.$e->getMessage(), 500);
} catch (Exception $e) {
    error_log($e->getMessage());
    Response::error('Erreur serveur : ' . $e->getMessage(), 500);
}