# shoppn — E-Commerce Lab (Vanilla HTML · CSS · JS · PHP · MySQL)

A server-rendered e-commerce app built on a strict MVC separation, following
the E-Commerce Lab handout.

## MVC layout

| Layer | Folder | Responsibility |
|-------|--------|----------------|
| **Model** | `classes/` | All SQL. Extends `core/db_class.php` (PDO, prepared statements). No HTML. |
| **Controller** | `controllers/` | Instantiates models, returns arrays/booleans. No SQL, no HTML. |
| **Action** | `actions/` | Reads `$_POST`, validates, calls a controller, sets session, redirects. No HTML. |
| **View** | `views/` | HTML + minimal PHP for display. No SQL. |
| **Core** | `core/` | DB connection, session, shared helpers (`is_logged_in`, `redirect`, …). |

```
ecomlab/
  core/       db_class.php · db_cred.php · core.php
  classes/    CustomerClass.php
  controllers/CustomerController.php
  actions/    register_action.php · login_action.php
  views/      home.php · register.php · login.php
              layout/ (header, footer, sidebar, flash)
              account/ (my_account.php)
              admin/  (brands & categories — tasks 5–8 branch)
  css/        style.css
  js/         validate.js
  images/     products/ · customers/
  error/      error.log
  database/   shoppn.sql
  index.php · logout.php
```

## Tasks 1–4 (this branch)

- **Task 1 — Database:** `database/shoppn.sql` (improved: utf8mb4, `customer_address`,
  `DECIMAL` money, `created_at`, unique constraints). Import and set `core/db_cred.php`.
- **Task 2 — Scaffold + core:** PDO base class, session/helpers, shared layout, home page, logout.
- **Task 3 — Registration:** form → JS validation → action → controller → model → DB, with
  server-side sanitisation, `password_hash()`, optional image upload, duplicate-email check.
- **Task 4 — Login + access control:** `password_verify()`, session management, and
  `is_logged_in()` / `is_admin()` / `require_login()` / `require_admin()` guards.

## Tasks 5–8 (admin brands & categories)

- **Task 5 — Add Brand:** `ProductClass::addBrand()/getAllBrands()`,
  `ProductController`, `actions/add_brand_action.php` (require_admin, POST-only),
  `views/admin/brand.php` (form + table with Edit buttons).
- **Task 6 — Edit Brand:** `getBrandById()/updateBrand()`,
  `actions/update_brand_action.php`, `brand.php` handles `?edit_id=N` prefill.
- **Task 7 — Add Category:** mirrors Task 5 — `addCategory()/getAllCategories()`,
  `add_category_action.php`, `views/admin/category.php`.
- **Task 8 — Edit Category:** `getCategoryById()/updateCategory()`,
  `update_category_action.php`, same `?edit_id=N` pattern.

The header shows **Brands** / **Categories** nav links only when `is_admin()`.
The sidebar now lists live categories and brands via `ProductController`.

## Local setup (XAMPP)

1. Start **Apache** and **MySQL** from the XAMPP app.
2. Import the schema:
   ```bash
   /Applications/XAMPP/xamppfiles/bin/mysql -u root < database/shoppn.sql
   ```
3. Confirm `core/db_cred.php` points at your local DB (`root` / blank / `shoppn`).
4. Browse to <http://localhost/ecomlab/>.

## Make an admin

Every signup is a customer (`user_role = 2`). Promote one to admin:
```sql
UPDATE customer SET user_role = 1 WHERE customer_email = 'you@example.com';
```
Log out and back in — the Admin links appear (tasks 5–8 branch).

## Security notes

- Every query with user input uses **prepared statements**.
- Passwords are stored with **bcrypt** (`password_hash` / `password_verify`).
- All forms validate on **both** the client (`js/validate.js`) and the server.
- Uploaded images are validated by **MIME type and size** server-side.
- Admin pages call `require_admin()` **before any output**.
