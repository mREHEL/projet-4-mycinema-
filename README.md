# 🎬 My Cinema - Gestion Administrative

Un back-office complet pour gérer les films, salles et séances d'un cinéma. Architecture MVC en PHP pur (sans framework), avec API REST et interface d'administration.

## 📋 Table des matières

- [Installation](#installation)
- [Configuration](#configuration)
- [Lancement](#lancement)
- [Architecture](#architecture)
- [API Documentation](#api-documentation)
- [Fonctionnalités](#fonctionnalités)

## 🚀 Installation

### Prérequis

- PHP 8.3+
- MySQL 8.0+
- Un terminal/console
- Navigateur moderne (Chrome, Firefox, Safari, Edge)

### Étapes

1. **Cloner le dépôt**
```bash
git clone <repo_url>
cd projet-4-mycinema-
```

2. **Installer MySQL (si nécessaire - macOS)**
```bash
brew install mysql
brew services start mysql
```

3. **Configurer la base de données**
```bash
mysql -u root -p < script.sql
```
Entrez votre mot de passe MySQL quand demandé. Les tables et données seront créées automatiquement.

4. **Configurer le backend**
Éditer `backend/config/database.php` et adapter les identifiants si nécessaire:
```php
private $host = "localhost";
private $db_name = "my_cinema";
private $username = "root";  // Votre utilisateur MySQL
private $password = "";      // Votre mot de passe MySQL
```

## 🔧 Configuration

### Structure du projet

```
projet-4-mycinema-/
├── backend/
│   ├── index.php              # Point d'entrée de l'API
│   ├── config/
│   │   └── database.php        # Connexion MySQL (PDO)
│   ├── controllers/            # Logique métier
│   │   ├── MovieController.php
│   │   ├── RoomController.php
│   │   └── ScreeningController.php
│   ├── repositories/           # Accès à la base de données
│   │   ├── MovieRepository.php
│   │   ├── RoomRepository.php
│   │   └── ScreeningRepository.php
│   ├── models/                 # Entités (POPO)
│   │   ├── Movie.php
│   │   ├── Room.php
│   │   └── Screening.php
│   ├── services/               # Logique métier (services)
│   │   └── ScreeningService.php  # Vérification chevauchements
│   └── migrations/
│       └── 0001_create_schema.sql
├── frontend/
│   ├── index.html              # Interface d'administration
│   ├── styles.css              # Styles (responsive)
│   └── script.js               # Fetch API & interactivité
├── script.sql                  # Migration SQL complète
└── README.md                   # Ce fichier
```

## ▶️ Lancement

### 1. Démarrer le serveur backend

Depuis le dossier projet racine:
```bash
php -S localhost:8000 -t backend/
```

Le serveur écoute sur `http://localhost:8000`

### 2. Démarrer le serveur frontend

Depuis un nouveau terminal (dans le dossier racine):
```bash
php -S localhost:3000 -t frontend/
```

Ou ouvrez simplement `frontend/index.html` dans un navigateur (avec CORS CORS autorisant les appels à localhost:8000).

### 3. Accéder à l'interface

Ouvrez `http://localhost:3000` dans votre navigateur.

## 🏗️ Architecture

### MVC (Modèle - Vue - Contrôleur)

- **Models** (`backend/models/`): Classes POPO représentant les entités.
- **Controllers** (`backend/controllers/`): Reçoivent les requêtes, appellent les repositories/services, retournent JSON.
- **Repositories** (`backend/repositories/`): Accès exclusive à la base de données via PDO.
- **Services** (`backend/services/`): Logique métier (ex: vérification chevauchement séances).
- **Database** (`backend/config/database.php`): Connexion PDO centralisée.

### Flux requête-réponse

```
Frontend (fetch)
    ↓
backend/index.php (routing)
    ↓
Controllers (validations)
    ↓
Services & Repositories (logique + DB)
    ↓
JSON Response
    ↓
Frontend (affichage)
```

### Sécurité

✅ Requêtes préparées PDO (injection SQL impossible)
✅ Validation serveur
✅ HTTP headers CORS configurés
✅ Soft delete (colonnes `active` sur movies & rooms)
✅ Contraintes de clés étrangères

## 📡 API Documentation

### Point d'entrée

Toutes les requêtes vont à:
```
http://localhost:8000/index.php?route=<resource>
```

### Routes disponibles

#### FILMS (`route=movies`)

| Méthode | Route | Description |
|---------|-------|-------------|
| GET | `?route=movies&page=1&per_page=10` | Lister films (paginated) |
| GET | `?route=movies&id=1` | Récupérer film par ID |
| POST | `?route=movies` | Créer un film |
| PUT | `?route=movies&id=1` | Mettre à jour un film |
| DELETE | `?route=movies&id=1` | Supprimer film (soft delete) |

**Exemple POST:**
```bash
curl -X POST "http://localhost:8000/index.php?route=movies" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Avatar",
    "description": "Film de science-fiction",
    "duration": 162,
    "release_year": 2009,
    "genre": "Sci-Fi",
    "director": "James Cameron"
  }'
```

#### SALLES (`route=rooms`)

| Méthode | Route | Description |
|---------|-------|-------------|
| GET | `?route=rooms&page=1&per_page=10` | Lister salles |
| GET | `?route=rooms&id=1` | Récupérer salle par ID |
| POST | `?route=rooms` | Créer une salle |
| PUT | `?route=rooms&id=1` | Mettre à jour une salle |
| DELETE | `?route=rooms&id=1` | Supprimer salle (soft delete) |

**Exemple POST:**
```bash
curl -X POST "http://localhost:8000/index.php?route=rooms" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Salle VIP",
    "capacity": 50,
    "type": "IMAX"
  }'
```

#### SÉANCES (`route=screenings`)

| Méthode | Route | Description |
|---------|-------|-------------|
| GET | `?route=screenings` | Lister séances |
| GET | `?route=screenings&id=1` | Récupérer séance par ID |
| POST | `?route=screenings` | Créer une séance (vérif chevauchement) |
| PUT | `?route=screenings&id=1` | Mettre à jour une séance |
| DELETE | `?route=screenings&id=1` | Supprimer une séance |

**Exemple POST:**
```bash
curl -X POST "http://localhost:8000/index.php?route=screenings" \
  -H "Content-Type: application/json" \
  -d '{
    "movie_id": 1,
    "room_id": 1,
    "start_time": "2026-02-10 14:00:00"
  }'
```

**Codes HTTP retournés:**
- `200/201`: Succès
- `400`: Données invalides
- `404`: Ressource non trouvée
- `409`: Conflit (ex: chevauchement séances)
- `500`: Erreur serveur

## ✨ Fonctionnalités

### Gestion des films

✅ Afficher liste paginée (10 par page)
✅ Ajouter film (titre, durée, genre, réalisateur, année)
✅ Modifier film
✅ Supprimer film (soft delete) — bloqué si séances associées
✅ Voir détails film

### Gestion des salles

✅ Afficher liste des salles
✅ Ajouter salle (nom, capacité, type: standard/3D/IMAX/Dolby)
✅ Modifier salle
✅ Supprimer salle (soft delete)
✅ Afficher détails salle

### Gestion des séances

✅ Afficher toutes les séances
✅ Ajouter séance
  - Sélection film (automatique calcul durée)
  - Sélection salle
  - Date et heure
  - **Vérification automatique chevauchement** (ScreeningService)
✅ Modifier séance (avec re-vérification chevauchement)
✅ Supprimer séance
✅ Affichage horaires début/fin

### Contraintes métier

- Pas deux séances simultanées dans la même salle ✅
- Durée du film prise en compte (calcul automatique `end_time = start_time + duration minutes`) ✅
- Suppression film bloquée si séances liées ✅
- Soft delete (colonnes `active`) ✅

## 🛠️ Technologie

- **Backend**: PHP 8.3, PDO, MySQL 8.0
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Architecture**: MVC Pattern
- **Base de données**: Relationnelle (3NF)
- **API**: REST JSON

## 📝 Exemples d'utilisation (JavaScript)

```javascript
// Récupérer tous les films
fetch('http://localhost:8000/index.php?route=movies')
  .then(r => r.json())
  .then(movies => console.log(movies));

// Créer une film
fetch('http://localhost:8000/index.php?route=movies', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    title: 'Mon Film',
    duration: 120,
    genre: 'Action'
  })
})
.then(r => r.json())
.then(data => console.log(data));

// Mettre à jour
fetch('http://localhost:8000/index.php?route=movies&id=5', {
  method: 'PUT',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ title: 'Nouveau Titre' })
})
.then(r => r.json())
.then(data => console.log(data));

// Supprimer
fetch('http://localhost:8000/index.php?route=movies&id=5', {
  method: 'DELETE'
})
.then(r => r.json())
.then(data => console.log(data));
```

## 🐛 Dépannage

### "Table 'my_cinema.movies' doesn't exist"
→ Exécutez: `mysql -u root -p < script.sql`

### "Access denied for user 'root'@'localhost'"
→ Mettez à jour `backend/config/database.php` avec les bons identifiants MySQL

### "CORS policy: No 'Access-Control-Allow-Origin' header"
→ Assurez-vous que `backend/index.php` a les headers CORS (déjà configurés)

### Interface ne se charge pas
→ Vérifiez que `php -S localhost:3000 -t frontend/` est en cours d'exécution

## 📚 Ressources

- [PHP POO](https://www.php.net/manual/fr/language.oop5.php)
- [PDO](https://www.php.net/manual/fr/book.pdo.php)
- [SQL](https://sql.sh/)
- [MDN - Fetch API](https://developer.mozilla.org/en-US/docs/Web/API/Fetch_API)

## 📄 Licence

Projet académique - Epitech 2026

---

**Créé par**: Morgan Rehel  
**Date**: Février 2026  
**Version**: 1.0.0
