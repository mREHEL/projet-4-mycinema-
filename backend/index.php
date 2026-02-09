<?php
// HEADERS CORS & JSON
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
// Répondre aux requêtes OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}
// ===== INCLUDES =====
require_once './config/database.php';
// Models
require_once './models/Movie.php';
require_once './models/Room.php';
require_once './models/Screening.php';
// Repositories
require_once './repositories/MovieRepository.php';
require_once './repositories/RoomRepository.php';
require_once './repositories/ScreeningRepository.php';
// Services
require_once './services/ScreeningService.php';
// Controllers
require_once './controllers/MovieController.php';
require_once './controllers/RoomController.php';
require_once './controllers/ScreeningController.php';
// ===== ROUTING =====
$database = new Database();
$db = $database->getConnection();
$route = $_GET['route'] ?? 'movies'; // Par défaut: movies
$method = $_SERVER['REQUEST_METHOD'];
// Instancier le contrôleur approprié
$controller = null;
switch ($route) {
    case 'movies':
        $controller = new MovieController($db);
        if ($method === 'GET') {
            if (isset($_GET['id'])) $controller->getMovieById();
            else $controller->getAllMovies();
        } elseif ($method === 'POST') {
            $controller->createMovie();
        } elseif ($method === 'PUT') {
            $controller->updateMovie();
        } elseif ($method === 'DELETE') {
            $controller->deleteMovie();
        }
        break;
    case 'rooms':
        $controller = new RoomController($db);
        if ($method === 'GET') {
            if (isset($_GET['id'])) $controller->getRoomById();
            else $controller->getAllRooms();
        } elseif ($method === 'POST') {
            $controller->createRoom();
        } elseif ($method === 'PUT') {
            $controller->updateRoom();
        } elseif ($method === 'DELETE') {
            $controller->deleteRoom();
        }
        break;
    case 'screenings':
        $controller = new ScreeningController($db);
        if ($method === 'GET') {
            if (isset($_GET['id'])) $controller->getScreeningById();
            else $controller->getAllScreenings();
        } elseif ($method === 'POST') {
            $controller->createScreening();
        } elseif ($method === 'PUT') {
            $controller->updateScreening();
        } elseif ($method === 'DELETE') {
            $controller->deleteScreening();
        }
        break;
    default:
        http_response_code(404);
        echo json_encode(["message" => "Route non trouvée. Utilisez ?route=movies, rooms, ou screenings"]);
        break;
}
?>