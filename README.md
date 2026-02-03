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

# My Cinema — README (minimal)

Instructions rapides pour lancer le projet localement.

1) Configurer la DB
- Éditez `backend/config/database.php` avec vos identifiants MySQL.

2) Importer le schéma

```bash
mysql -u root -p < backend/migrations/0001_create_schema.sql
```

3) Lancer le backend

```bash
php -S localhost:8000 -t backend/
```

4) Ouvrir le frontend
- Ouvrez `frontend/index.html` dans le navigateur (ou servez le dossier `frontend/`).

API (point d'entrée)

```
http://localhost:8000/index.php?route=movies|rooms|screenings
```
