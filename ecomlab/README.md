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
  classes/    CustomerClass.php · ProductClass.php
  controllers/CustomerController.php · ProductController.php
  actions/    register_action.php · login_action.php
              add_brand_action.php · update_brand_action.php
              add_category_action.php · update_category_action.php
  views/      home.php · register.php · login.php
              layout/ (header, footer, sidebar, flash)
              account/ (my_account.php)
              admin/ (brand.php, category.php)
  css/        style.css
  js/         validate.js
  images/     products/ · customers/
  error/      error.log
  database/   shoppn.sql
  index.php · logout.php
```

Folders for later tasks (the product, cart and checkout views) are
intentionally absent — this branch stops at Task 8.

## Tasks 1–8 (this branch)

- **Task 1 — Database:** `database/shoppn.sql` (improved: utf8mb4, `customer_address`,
  `DECIMAL` money, `created_at`, unique constraints). Import and set `core/db_cred.php`.

  > **Import this file, not an older copy of the handout schema.** The register
  > form has an Address field (required by Task 3), so `customer` needs the
  > `customer_address` column. Importing the original handout SQL instead gives
  > `Unknown column 'customer_address'` the first time anyone registers.
- **Task 2 — Scaffold + core:** PDO base class, session/helpers, shared layout, home page, logout.
- **Task 3 — Registration:** form → JS validation → action → controller → model → DB, with
  server-side sanitisation, `password_hash()`, optional image upload, duplicate-email check.
- **Task 4 — Login + access control:** `password_verify()`, session management, and
  `is_logged_in()` / `is_admin()` / `require_login()` / `require_admin()` guards.
- **Task 5 — Add brand:** `views/admin/brand.php` form → `actions/add_brand_action.php`
  → `ProductController::addBrand()` → `Product::addBrand()`. Duplicate names refused.
- **Task 6 — Edit brand:** the same `brand.php` switches to edit mode on `?edit_id=N`,
  pre-filled via `getBrandById()`, posting to `actions/update_brand_action.php`.
- **Task 7 — Add category:** the Task 5 flow repeated for `categories`
  (`views/admin/category.php`).
- **Task 8 — Edit category:** the Task 6 `?edit_id=N` pattern repeated for categories.

Both admin pages call `require_admin()` before any output, and the **Brands** /
**Categories** nav links in `views/layout/header.php` render only when
`is_admin()` is true. The storefront sidebar lists whatever an admin has added,
reading it through the same `ProductController`.

## Local setup (XAMPP)

1. Start **Apache** and **MySQL** from the XAMPP app.
2. Import the schema:
   ```bash
   /Applications/XAMPP/xamppfiles/bin/mysql -u root < database/shoppn.sql
   ```
3. Create your credentials file (it is git-ignored, so a fresh clone has none):
   ```bash
   cp core/db_cred.example.php core/db_cred.php
   ```
   then confirm it points at your local DB (`root` / blank / `shoppn`).
4. Browse to <http://localhost/ecomlab/>.

## Make an admin

Every signup is a customer (`user_role = 2`). Promote one to admin:
```sql
UPDATE customer SET user_role = 1 WHERE customer_email = 'you@example.com';
```
Log out and back in — the **Brands** and **Categories** links appear in the nav.

> The role is read from the session at login, so an existing session keeps the
> old role until you log out and back in.

## Security notes

- Every query with user input uses **prepared statements**.
- Passwords are stored with **bcrypt** (`password_hash` / `password_verify`).
- All forms validate on **both** the client (`js/validate.js`) and the server.
- **Password strength** is enforced in both places: at least 8 characters with an
  uppercase letter, a lowercase letter, a number and a special character, so a
  weak password like `12345678` is refused.
- Every state-changing POST carries a per-session **CSRF token**, checked with
  `hash_equals()`.
- Record ids from the URL or a form (`edit_id`, `brand_id`, `cat_id`) are validated
  as **positive integers** before they reach the Model.
- Uploaded images are validated by **MIME type and size** server-side.
- Admin pages call `require_admin()` **before any output**.
