# My Cinema — backend

Quick notes to get the backend running locally and to apply the DB schema.

Run the PHP server (from project root):

```bash
php -S localhost:8000 -t backend/
```

Database setup
1. Edit `backend/config/database.php` and set your MySQL credentials (host, user, password).
2. Run the migration SQL file to create the database and tables:

```bash
# run from project root
mysql -u <user> -p < backend/migrations/0001_create_schema.sql
```

Example (typical local environments):

```bash
mysql -u root -p < backend/migrations/0001_create_schema.sql
```

If your MySQL user requires a password, you will be prompted. After the migration you can test the API:

```bash
curl "http://localhost:8000/index.php?route=movies"
```

Notes and next steps
- The migration creates `movies`, `rooms`, and `screenings`. `movies` and `rooms` include an `active` flag for soft deletes.
- `screenings` stores `start_time` and `end_time` (used to check overlaps). Business rules (no overlapping screenings in same room) should be enforced in a `ScreeningService` when creating/updating screenings.
- If you want, I can now implement: paginated movies list, create/update/delete for movies, or the `ScreeningService` overlap check.
