<?php
require_once __DIR__ . '/../models/Movie.php';
class MovieRepository {
    private $pdo;
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    /**
     * Récupérer les films avec pagination (uniquement actifs)
     */
    public function getPaginated($limit = 10, $offset = 0) {
        $sql = "SELECT * FROM movies WHERE active = 1 ORDER BY id DESC LIMIT :limit OFFSET :offset";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_CLASS, 'Movie');
    }
    /**
     * Récupérer tous les films
     */
    public function findAll() {
        $sql = "SELECT * FROM movies WHERE active = 1 ORDER BY id DESC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_CLASS, 'Movie');
    }
    /**
     * Récupérer un film par ID
     */
    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM movies WHERE id = :id AND active = 1");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_CLASS, 'Movie');
        return $stmt->fetch();
    }
    /**
     * Créer un nouveau film
     */
    public function create($title, $duration, $description = null, $release_year = null, $genre = null, $director = null) {
        $sql = "INSERT INTO movies (title, duration, description, release_year, genre, director, active, created_at) 
                VALUES (:title, :duration, :description, :release_year, :genre, :director, 1, NOW())";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':duration', $duration, PDO::PARAM_INT);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':release_year', $release_year, PDO::PARAM_INT);
        $stmt->bindParam(':genre', $genre);
        $stmt->bindParam(':director', $director);
        return $stmt->execute();
    }
    /**
     * Mettre à jour un film
     */
    public function update($id, $data) {
        $allowed = ['title', 'description', 'duration', 'release_year', 'genre', 'director'];
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

        $sql = "UPDATE movies SET " . implode(', ', $updateFields) . ", updated_at = NOW() WHERE id = :id AND active = 1";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }
    /**
     * Soft delete un film
     */
    public function softDelete($id) {
        $sql = "UPDATE movies SET active = 0, updated_at = NOW() WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
    /**
     * Vérifier si un film a des séances
     */
    public function hasScreenings($movieId) {
        $sql = "SELECT COUNT(*) FROM screenings WHERE movie_id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id', $movieId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }
    public function hasShowtimes($movieId) {
        return $this->hasScreenings($movieId);
    }
}
?>