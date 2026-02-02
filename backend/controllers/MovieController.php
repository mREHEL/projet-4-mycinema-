<?php
require_once __DIR__ . '/../repositories/MovieRepository.php';

class MovieController {
    private $repository;

    public function __construct($dbConnection) {
        $this->repository = new MovieRepository($dbConnection);
    }

    public function getAllMovies() {
        // support pagination via ?page=1
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = isset($_GET['per_page']) ? max(1, (int)$_GET['per_page']) : 10;
        $offset = ($page - 1) * $perPage;

        $movies = $this->repository->getPaginated($perPage, $offset);
        header('Content-Type: application/json');
        
        // 3. On affiche le résultat encodé
        echo json_encode($movies);
    }

    public function getMovieById() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(["message" => "ID manquant ou invalide."]);
            return;
        }

        $movie = $this->repository->findById($id);
        header('Content-Type: application/json');
        if ($movie) {
            echo json_encode($movie);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "Film non trouvé."]);
        }
    }

    public function createMovie() {
        // 1. On récupère le JSON envoyé par le Javascript
        $data = json_decode(file_get_contents("php://input"));

        // 2. Vérification simple (est-ce qu'on a bien les données ?)
        if (!empty($data->title) && !empty($data->duration)) {
            
            // 3. On appelle le repository pour insérer
            if ($this->repository->create($data->title, $data->duration, $data->description ?? null, $data->release_year ?? null, $data->genre ?? null, $data->director ?? null)) {
                echo json_encode(["message" => "Le film a été créé."]);
            } else {
                echo json_encode(["message" => "Impossible de créer le film."]);
            }
            
        } else {
            echo json_encode(["message" => "Données incomplètes."]);
        }
    }

    public function updateMovie() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $data = json_decode(file_get_contents('php://input'), true);

        if ($id <= 0 || empty($data) || !is_array($data)) {
            http_response_code(400);
            echo json_encode(["message" => "ID manquant ou données invalides."]);
            return;
        }

        $ok = $this->repository->update($id, $data);
        header('Content-Type: application/json');
        if ($ok) {
            echo json_encode(["message" => "Film mis à jour."]);
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Impossible de mettre à jour le film ou rien à changer."]);
        }
    }

    public function deleteMovie() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        header('Content-Type: application/json');
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(["message" => "ID manquant ou invalide."]);
            return;
        }

        // empêcher suppression si la séance existe
        if ($this->repository->hasScreenings($id)) {
            http_response_code(409);
            echo json_encode(["message" => "Impossible de supprimer : séances liées au film." ]);
            return;
        }

        if ($this->repository->softDelete($id)) {
            echo json_encode(["message" => "Film supprimé (soft delete)."]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Erreur lors de la suppression."]);
        }
    }
}

?>