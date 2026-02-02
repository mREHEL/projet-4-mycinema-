<?php
require_once __DIR__ . '/../repositories/ScreeningRepository.php';
require_once __DIR__ . '/../repositories/MovieRepository.php';
require_once __DIR__ . '/../services/ScreeningService.php';
class ScreeningController {
    private $repository;
    private $service;
    private $db;
    public function __construct($dbConnection) {
        $this->db = $dbConnection;
        $this->repository = new ScreeningRepository($dbConnection);
        $this->service = new ScreeningService($dbConnection);
    }
    public function getAllScreenings() {
        // support pagination via ?page=1
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = isset($_GET['per_page']) ? max(1, (int)$_GET['per_page']) : 10;
        $offset = ($page - 1) * $perPage;
        $screenings = $this->repository->getAll($offset, $perPage);
        header('Content-Type: application/json');
        echo json_encode($screenings);
    }
    public function getScreeningById() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(["message" => "ID manquant ou invalide."]);
            return;
        }

        $screening = $this->repository->findById($id);
        header('Content-Type: application/json');
        if ($screening) {
            echo json_encode($screening);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "Séance non trouvée."]);
        }
    }
    public function createScreening() {
        $data = json_decode(file_get_contents("php://input"));

        // Validation minimale : accepter start_time et calculer end_time depuis la durée du film
        if (empty($data->movie_id) || empty($data->room_id) || empty($data->start_time)) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(["message" => "Données manquantes (movie_id, room_id, start_time requis)."]);
            return;
        }
        $movieId = $data->movie_id;
        $roomId = $data->room_id;
        $startTime = $data->start_time;

        // Récupérer la durée du film pour calculer end_time
        $movieRepo = new MovieRepository($this->db);
        $movie = $movieRepo->findById($movieId);
        if (!$movie) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(["message" => "Film introuvable."]);
            return;
        }

        $duration = (int)($movie->duration ?? 0);
        $dt = new DateTime($startTime);
        $dt->add(new DateInterval('PT' . max(0, $duration) . 'M'));
        $endTime = $dt->format('Y-m-d H:i:s');

        // Vérification conflit via le Service
        if (!$this->service->isSlotAvailable($roomId, $startTime, $endTime)) {
            http_response_code(409);
            header('Content-Type: application/json');
            echo json_encode(["message" => "Conflit : Salle déjà occupée à cet horaire !"]);
            return;
        }

        // Création via le Repository
        if ($this->repository->create($movieId, $roomId, $startTime, $endTime)) {
            header('Content-Type: application/json');
            echo json_encode(["message" => "Séance créée."]);
        } else {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(["message" => "Erreur lors de la création de la séance."]);
        }
    }

    public function updateScreening() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $data = json_decode(file_get_contents('php://input'), true);

        if ($id <= 0 || empty($data) || !is_array($data)) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(["message" => "ID manquant ou données invalides."]);
            return;
        }

        // Si on change les horaires (ou movie), vérifier absence de conflit (en excluant la séance actuelle)
        $existing = $this->repository->findById($id);
        if (!$existing) {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(["message" => "Séance non trouvée."]);
            return;
        }

        // Si start_time est fourni mais pas end_time, calculer end_time depuis la durée (movie_id prioritaire dans payload sinon existant)
        if (isset($data['start_time']) && !isset($data['end_time'])) {
            $movieIdForDuration = $data['movie_id'] ?? $existing->movie_id;
            $movieRepo = new MovieRepository($this->db);
            $movie = $movieRepo->findById($movieIdForDuration);
            $duration = (int)($movie->duration ?? 0);
            $dt = new DateTime($data['start_time']);
            $dt->add(new DateInterval('PT' . max(0, $duration) . 'M'));
            $data['end_time'] = $dt->format('Y-m-d H:i:s');
        }

        if (isset($data['start_time']) && isset($data['end_time'])) {
            $roomIdForCheck = $data['room_id'] ?? $existing->room_id;
            if (!$this->service->isSlotAvailableForUpdate($roomIdForCheck, $data['start_time'], $data['end_time'], $id)) {
                http_response_code(409);
                header('Content-Type: application/json');
                echo json_encode(["message" => "Conflit : Salle déjà occupée à cet horaire !"]);
                return;
            }
        }

        $ok = $this->repository->update($id, $data);
        header('Content-Type: application/json');
        if ($ok) {
            echo json_encode(["message" => "Séance mise à jour."]);
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Impossible de mettre à jour la séance ou rien à changer."]);
        }
    }

    public function deleteScreening() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        header('Content-Type: application/json');
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(["message" => "ID manquant ou invalide."]);
            return;
        }
        if ($this->repository->delete($id)) {
            echo json_encode(["message" => "Séance supprimée."]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Erreur lors de la suppression."]);
        }
    }
}
?>