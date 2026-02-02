<?php
require_once __DIR__ . '/../models/Screening.php';
class ScreeningRepository {
    private $pdo;

    public function __construct($pdo) { 
        $this->pdo = $pdo; 
    }
    /**
     * Récupérer les séances avec pagination
     */
    public function getAll($offset = 0, $limit = 10) {
        $sql = "SELECT * FROM screenings ORDER BY start_time DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_CLASS, 'Screening');
    }

    /**
     * Récupérer une séance par ID
     */
    public function findById($id) {
        $sql = "SELECT * FROM screenings WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_CLASS, 'Screening');
        return $stmt->fetch();
    }

    /**
     * Créer une nouvelle séance
     */
    public function create($movieId, $roomId, $startTime, $endTime) {
        $sql = "INSERT INTO screenings (movie_id, room_id, start_time, end_time, created_at) 
                VALUES (:movie_id, :room_id, :start_time, :end_time, NOW())";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':movie_id', $movieId, PDO::PARAM_INT);
        $stmt->bindParam(':room_id', $roomId, PDO::PARAM_INT);
        $stmt->bindParam(':start_time', $startTime);
        $stmt->bindParam(':end_time', $endTime);
        return $stmt->execute();
    }

    /**
     * Mettre à jour une séance
     */
    public function update($id, $data) {
        $allowed = ['movie_id', 'room_id', 'start_time', 'end_time'];
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

        $sql = "UPDATE screenings SET " . implode(', ', $updateFields) . " WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Supprimer une séance (hard delete)
     */
    public function delete($id) {
        $sql = "DELETE FROM screenings WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Vérifier s'il existe un conflit horaire dans une salle
     * Retourne true s'il y a un conflit, false sinon
     */
    public function existsOverlap($roomId, $startTime, $endTime, $excludeScreeningId = null) {
        $sql = "SELECT COUNT(*) FROM screenings 
                WHERE room_id = :room_id 
                AND NOT (end_time <= :start_time OR start_time >= :end_time)";
        
        if ($excludeScreeningId) {
            $sql .= " AND id != :exclude_id";
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':room_id', $roomId, PDO::PARAM_INT);
        $stmt->bindParam(':start_time', $startTime);
        $stmt->bindParam(':end_time', $endTime);
        
        if ($excludeScreeningId) {
            $stmt->bindParam(':exclude_id', $excludeScreeningId, PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetchColumn() > 0; // true si conflit
    }
}

?>