# My Cinema — Backend (simple)

Petit guide pour lancer l'API backend en local.

1) Configurer la base de données
- Ouvrez `backend/config/database.php` et mettez vos paramètres MySQL (host, user, password, dbname).

2) Importer le schéma
Exécutez depuis la racine du projet :

```bash
mysql -u root -p < backend/migrations/0001_create_schema.sql
```

3) Lancer le serveur PHP (dev)

```bash
php -S localhost:8000 -t backend/
```

4) Tester une route

```bash
curl "http://localhost:8000/index.php?route=movies"
```

Infos rapides
- Point d'entrée de l'API : `backend/index.php` (paramètre `?route=` : `movies`, `rooms`, `screenings`).
- Les tables créées : `movies`, `rooms`, `screenings`.
- `movies` et `rooms` utilisent une colonne `active` pour les suppressions soft.
- `screenings` a `start_time` et `end_time` : le backend vérifie les conflits d'horaire (pas de séances qui se chevauchent dans la même salle).

Si vous voulez, je peux :
- Exécuter des tests rapides (GET/POST/PUT/DELETE) sur l'API.
- Simplifier ou documenter d'autres parties du projet.
