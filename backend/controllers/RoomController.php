<?php
require_once __DIR__ . '/../repositories/RoomRepository.php';
class RoomController {
    private $repository;
    public function __construct($dbConnection) {
        $this->repository = new RoomRepository($dbConnection);
    }
    public function getAllRooms() {
        // support pagination via ?page=1
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = isset($_GET['per_page']) ? max(1, (int)$_GET['per_page']) : 10;
        $offset = ($page - 1) * $perPage;

        $rooms = $this->repository->getPaginated($perPage, $offset);
        header('Content-Type: application/json');
        echo json_encode($rooms);
    }
    public function getRoomById() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(["message" => "ID manquant ou invalide."]);
            return;
        }
        $room = $this->repository->findById($id);
        header('Content-Type: application/json');
        if ($room) {
            echo json_encode($room);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "Salle non trouvée."]);
        }
    }
    public function createRoom() {
        $data = json_decode(file_get_contents("php://input"));
        if (!empty($data->name) && !empty($data->capacity)) {
            if ($this->repository->create($data->name, $data->capacity, $data->type ?? null)) {
                header('Content-Type: application/json');
                echo json_encode(["message" => "Salle créée."]);
            } else {
                header('Content-Type: application/json');
                echo json_encode(["message" => "Impossible de créer la salle."]);
            }
        } else {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(["message" => "Données incomplètes."]);
        }
    }
    public function updateRoom() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        $data = json_decode(file_get_contents('php://input'), true);

        if ($id <= 0 || empty($data) || !is_array($data)) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(["message" => "ID manquant ou données invalides."]);
            return;
        }

        $ok = $this->repository->update($id, $data);
        header('Content-Type: application/json');
        if ($ok) {
            echo json_encode(["message" => "Salle mise à jour."]);
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Impossible de mettre à jour la salle ou rien à changer."]);
        }
    }

    public function deleteRoom() {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        header('Content-Type: application/json');
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(["message" => "ID manquant ou invalide."]);
            return;
        }
        if ($this->repository->hasScreenings($id)) {
            http_response_code(409);
            echo json_encode(["message" => "Impossible de supprimer : séances liées à cette salle."]);
            return;
        }
        if ($this->repository->softDelete($id)) {
            echo json_encode(["message" => "Salle supprimée (soft delete)."]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Erreur lors de la suppression."]);
        }
    }
}
?>