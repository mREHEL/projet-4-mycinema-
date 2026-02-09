<?php
require_once __DIR__ . '/../models/Room.php';
class RoomRepository {
    private $connection;
    public function __construct($connection) {
        $this->connection = $connection;
    }
    /*** Récupérer les salles avec pagination (uniquement actives)*/
    public function getPaginated($limit = 10, $offset = 0) {
        $sql = "SELECT * FROM rooms WHERE active = 1 ORDER BY id DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_CLASS, 'Room');
    }
    /*** Récupérer toutes les salles*/
    public function findAll() {
        $sql = "SELECT * FROM rooms WHERE active = 1 ORDER BY id DESC";
        $stmt = $this->connection->query($sql);
        return $stmt->fetchAll(PDO::FETCH_CLASS, 'Room');
    }
    /*** Récupérer une salle par ID*/
    public function findById($id) {
        $stmt = $this->connection->prepare("SELECT * FROM rooms WHERE id = :id AND active = 1");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_CLASS, 'Room');
        return $stmt->fetch();
    }
    /*** Créer une nouvelle salle */
    public function create($name, $capacity, $type = null) {
        $sql = "INSERT INTO rooms (name, capacity, type, active, created_at) VALUES (:name, :capacity, :type, 1, NOW())";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':capacity', $capacity, PDO::PARAM_INT);
        $stmt->bindParam(':type', $type);
        return $stmt->execute();
    }
    /*** Mettre à jour une salle */
    public function update($id, $data) {
        $allowed = ['name', 'capacity', 'type'];
        $updateFields = [];
        $params = ['id' => $id];
        foreach ($data as $key => $value) {
            if (in_array($key, $allowed)) {
                $updateFields[] = "$key = :$key";
                $params[$key] = $value;
            }
        }
        if (empty($updateFields)) {
            return false;
        }
        $sql = "UPDATE rooms SET " . implode(', ', $updateFields) . ", updated_at = NOW() WHERE id = :id AND active = 1";
        $stmt = $this->connection->prepare($sql);
        return $stmt->execute($params);
    }
    /*** Soft delete une salle*/
    public function softDelete($id) {
        $sql = "UPDATE rooms SET active = 0, updated_at = NOW() WHERE id = :id";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
    /*** Vérifier si une salle a des séances */
    public function hasScreenings($roomId) {
        $sql = "SELECT COUNT(*) FROM screenings WHERE room_id = :id";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(':id', $roomId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }
}
?>