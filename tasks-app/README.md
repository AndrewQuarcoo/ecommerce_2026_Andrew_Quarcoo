# Tasks App — PHP + MySQL CRUD

A small task manager built for E-Commerce Lab 01. It lets you **C**reate, **R**ead,
**U**pdate, and **D**elete tasks stored in a MySQL database. Runs locally on XAMPP
and deploys to the CS shared server via FileZilla.

## Files

| File | Role | CRUD |
|------|------|------|
| `db.php` | Opens the MySQL connection (`$conn`) used by every page | — |
| `setup.php` | Creates the `tasks_app` database + `tasks` table (run once) | — |
| `index.php` | Lists all tasks, newest first | **R**ead |
| `create.php` | Form + insert for a new task | **C**reate |
| `edit.php` | Form + update for an existing task | **U**pdate |
| `delete.php` | Removes a task by id | **D**elete |
| `db.live.php.example` | Template for the live-server database credentials | — |
| `setup.live.php.example` | Live-server setup (no `CREATE DATABASE`) | — |

## The `tasks` table

```sql
CREATE TABLE tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    status ENUM('pending', 'in_progress', 'done') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

## Run locally (XAMPP)

1. Start **Apache** and **MySQL** in XAMPP (both green / "Running").
2. Copy this folder into XAMPP's web root:
   ```bash
   cp -r ~/Desktop/tasks-app /Applications/XAMPP/xamppfiles/htdocs/
   chmod -R 755 /Applications/XAMPP/xamppfiles/htdocs/tasks-app
   ```
3. Run setup once: <http://localhost/tasks-app/setup.php> → "Setup complete!"
4. Use the app: <http://localhost/tasks-app/index.php>
5. Verify data in phpMyAdmin: <http://localhost/phpmyadmin> → `tasks_app` → `tasks`.

## Deploy to the live server

1. Edit `db.php` with your real server credentials (see `db.live.php.example`).
2. Replace `setup.php` with the live version (see `setup.live.php.example` — no
   `CREATE DATABASE`, since shared hosting won't allow it).
3. Upload the folder into `public_html/tasks-app` via FileZilla (FTP, port 421).
4. Run setup once, then test the live app and check the live phpMyAdmin.

## Security notes

- **Prepared statements** (`prepare` + `bind_param`) on every write query → no SQL injection.
- **`htmlspecialchars()`** on every value echoed into HTML → no stored XSS.
- **`intval()`** on every `id` from the URL → ids are always clean integers.
- Real credentials live only in the deployed `db.php`, never committed to Git.
