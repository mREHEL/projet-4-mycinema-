<?php
require_once __DIR__ . '/../repositories/ScreeningRepository.php';
class ScreeningService {
    private $pdo;
    private $repository;
    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->repository = new ScreeningRepository($pdo);
    }
    /** Vérifier qu'une plage horaire est disponible (pas de conflit) Pour création */
    public function isSlotAvailable($roomId, $startTime, $endTime) {
        return !$this->repository->existsOverlap($roomId, $startTime, $endTime);
    }
    /** Vérifier qu'une plage horaire est disponible en excluant une séance (pour update)*/
    public function isSlotAvailableForUpdate($roomId, $startTime, $endTime, $excludeScreeningId) {
        return !$this->repository->existsOverlap($roomId, $startTime, $endTime, $excludeScreeningId);
    }
}
?>