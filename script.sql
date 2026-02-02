-- MY CINEMA - Base de données complète
CREATE DATABASE IF NOT EXISTS my_cinema CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE my_cinema;
-- TABLE MOVIES (Films)
CREATE TABLE IF NOT EXISTS movies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description LONGTEXT,
    duration INT,
    release_year INT,
    genre VARCHAR(100),
    director VARCHAR(255),
    active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_active (active),
    INDEX idx_title (title)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- TABLE ROOMS (Salles)
CREATE TABLE IF NOT EXISTS rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    capacity INT DEFAULT 0,
    type VARCHAR(50) DEFAULT 'standard',
    active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_active (active),
    INDEX idx_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- TABLE SCREENINGS (Séances)
CREATE TABLE IF NOT EXISTS screenings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    movie_id INT NOT NULL,
    room_id INT NOT NULL,
    start_time DATETIME NOT NULL,
    end_time DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE RESTRICT,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE RESTRICT,
    INDEX idx_room_start (room_id, start_time),
    INDEX idx_movie (movie_id),
    INDEX idx_start_time (start_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- DONNÉES D'EXEMPLE
INSERT INTO movies (title, description, duration, release_year, genre, director) VALUES
('Inception', 'Un vol d\'idées lors de rêves partagés', 148, 2010, 'Sci-Fi', 'Christopher Nolan'),
('The Matrix', 'La réalité est une simulation contrôlée par des machines', 136, 1999, 'Sci-Fi', 'Wachowskis'),
('Pulp Fiction', 'Plusieurs histoires criminelles s\'entrelacent à Los Angeles', 154, 1994, 'Crime', 'Quentin Tarantino'),
('Interstellar', 'Une mission spatiale pour sauver l\'humanité', 169, 2014, 'Sci-Fi', 'Christopher Nolan'),
('The Dark Knight', 'Batman affronte son plus grand ennemi: le Joker', 152, 2008, 'Action', 'Christopher Nolan');
INSERT INTO rooms (name, capacity, type) VALUES
('Salle 1', 120, 'standard'),
('Salle 2', 85, '3D'),
('Salle 3', 150, 'standard'),
('Salle 4', 60, 'IMAX'),
('Salle 5', 95, '3D');
INSERT INTO screenings (movie_id, room_id, start_time, end_time) VALUES
(1, 1, '2026-02-05 14:00:00', '2026-02-05 16:28:00'),
(2, 2, '2026-02-05 15:30:00', '2026-02-05 17:46:00'),
(3, 3, '2026-02-05 18:00:00', '2026-02-05 20:34:00'),
(4, 4, '2026-02-05 19:00:00', '2026-02-05 21:49:00'),
(5, 5, '2026-02-06 14:00:00', '2026-02-06 16:32:00'),
(1, 2, '2026-02-06 17:00:00', '2026-02-06 19:28:00'),
(3, 1, '2026-02-06 20:00:00', '2026-02-06 22:34:00');
