# shoppn — E-Commerce Lab · Deployment & Live Links

The **shoppn** app (E-Commerce Lab, Vanilla HTML · CSS · JS · PHP · MySQL) is
built with a strict MVC separation and deployed to both local XAMPP and the live
CS server.

> 🔐 All credentials are kept locally, outside version control — nothing secret
> is committed to this repository, and this file deliberately contains none.

---

## 🌐 Live links

| What | URL |
|------|-----|
| **Live app (home)** | <http://169.239.251.102:442/~andrew.quarcoo/ecomlab/index.php> |
| Register | <http://169.239.251.102:442/~andrew.quarcoo/ecomlab/views/register.php> |
| Login | <http://169.239.251.102:442/~andrew.quarcoo/ecomlab/views/login.php> |
| Admin — Brands *(after you promote an admin)* | <http://169.239.251.102:442/~andrew.quarcoo/ecomlab/views/admin/brand.php> |
| Admin — Categories | <http://169.239.251.102:442/~andrew.quarcoo/ecomlab/views/admin/category.php> |
| Live phpMyAdmin | <http://169.239.251.102:442/phpmyadmin> |

## 💻 Local links (XAMPP)

| What | URL |
|------|-----|
| App (home) | <http://localhost/ecomlab/index.php> |
| Register | <http://localhost/ecomlab/views/register.php> |
| Login | <http://localhost/ecomlab/views/login.php> |
| phpMyAdmin | <http://localhost/phpmyadmin> |

## 🔗 Repository

| What | Value |
|------|-------|
| Repo | <https://github.com/AndrewQuarcoo/ecommerce_2026_Andrew_Quarcoo> |
| Tasks 1–4 branch | `feature/tasks-1-4-auth` |
| Tasks 5–8 branch | `feature/tasks-5-8-admin` |
| PR (5–8 → 1–4) | <https://github.com/AndrewQuarcoo/ecommerce_2026_Andrew_Quarcoo/pull/1> |
| App folder | `ecomlab/` |

---

## ✅ What's built & verified

| Task | Feature | Status |
|------|---------|--------|
| 1 | Database schema (`database/shoppn.sql`) | ✅ imported local + live |
| 2 | Folder structure + core (PDO base, session, helpers, layout) | ✅ |
| 3 | Customer registration (JS + server validation, bcrypt, image upload) | ✅ tested |
| 4 | Login + access control (`require_login` / `require_admin`) | ✅ tested |
| 5 | Add Brand (admin) | ✅ tested |
| 6 | Edit Brand (`?edit_id=N`) | ✅ tested |
| 7 | Add Category (admin) | ✅ tested |
| 8 | Edit Category (`?edit_id=N`) | ✅ tested |

Verified end-to-end on **both** local and live: register, duplicate-email
rejection, login (valid/invalid), logout, protected-page guard, admin access
control (customer & guest blocked), brand/category add + edit, duplicate-name
rejection, and no password-hash leakage.

---

## 🗄️ Database notes

- **Local:** database `shoppn` (its own database). Import:
  ```bash
  /Applications/XAMPP/xamppfiles/bin/mysql -u root < ecomlab/database/shoppn.sql
  ```
- **Live:** the shared host allows **no `CREATE DATABASE`**, so the 8 shoppn
  tables were imported **into the existing** database `ecommerce_2026A_andrew_quarcoo`
  (they coexist with the old `tasks` table). The live import file
  `database/shoppn_live.sql` is the same schema **without** the
  `CREATE DATABASE` / `USE` lines.
- `core/db_cred.php` differs per environment (local = `root`/blank/`shoppn`;
  live = the CS creds → `ecommerce_2026A_andrew_quarcoo`). The live `db_cred.php`
  is **not** in git.

### Make yourself an admin

Every signup is a customer (`user_role = 2`). Promote one to admin, then log out
and back in — the **Brands** / **Categories** nav links appear:

```sql
UPDATE customer SET user_role = 1 WHERE customer_email = 'you@example.com';
```

---

## 🚀 How it was deployed (and how to redeploy)

1. **Stage** the app, swapping in the live `core/db_cred.php` and generating
   `database/shoppn_live.sql` (schema minus `CREATE DATABASE`/`USE`).
2. **Upload** to the server over SSH/SCP (SSH port `422`) into
   `~/public_html/ecomlab/`.
3. **Import** the schema into the live DB:
   ```bash
   mysql -u andrew.quarcoo -p ecommerce_2026A_andrew_quarcoo < ~/public_html/ecomlab/database/shoppn_live.sql
   ```
4. **Permissions:** `images/products`, `images/customers` → `777`;
   `error/error.log` → `666` (this account can't `chgrp www-data`).
5. **Smoke-test** the live URL above.

> The app is portable across environments because `app_url()` derives its base
> path from the request (`SCRIPT_NAME` / `SCRIPT_FILENAME`), so links resolve
> correctly under both XAMPP htdocs (`/ecomlab`) and the live server's
> `mod_userdir` (`/~andrew.quarcoo/ecomlab`).

---

## 🔒 Security summary

- **SQL injection** — every query with user input uses **prepared statements**.
- **Passwords** — stored with **bcrypt** (`password_hash` / `password_verify`);
  hashes are never sent to any view.
- **XSS** — all output escaped with `htmlspecialchars`; a **Content-Security-Policy**
  (`script-src 'self'`) plus `X-Content-Type-Options`, `X-Frame-Options: DENY` and
  `Referrer-Policy` are sent on every page.
- **CSRF** — every state-changing POST form carries a per-session token, verified
  server-side with a timing-safe comparison (`hash_equals`).
- **Session security** — cookie is `HttpOnly` + `SameSite=Lax`; the session id is
  **regenerated on login** (defeats session fixation).
- **Input validation** — client (`js/validate.js` regex) **and** server; ids are
  validated as positive integers, contact as a digit pattern, country against a
  server-side whitelist.
- **Open redirect** — the post-login "return to" target is followed only when it is
  a safe same-site path; `redirect()` strips CR/LF to block header injection.
- **File uploads** — validated by **MIME type + size**; the saved extension is
  derived from the detected MIME (never the user's filename), so an executable
  name can't be smuggled in.
- **Authorization (row-level)** — the account page reads only the logged-in user's
  own row (keyed by the session), and every admin page/action calls
  `require_admin()` **before any output**.
