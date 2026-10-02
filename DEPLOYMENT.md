# shoppn — E-Commerce Lab · Deployment & Live Links

The **shoppn** app (Vanilla HTML · CSS · JS · PHP · MySQL) is built on a strict
MVC separation and runs on both local XAMPP and the live CS server.

> 🔐 No credentials are committed. `core/db_cred.php` is git-ignored and differs
> per environment; `core/db_cred.example.php` shows the shape.

---

## 🌐 Live links

| What | URL |
|------|-----|
| **Live app (home)** | <http://169.239.251.102:442/~andrew.quarcoo/shoppn/index.php> |
| Register | <http://169.239.251.102:442/~andrew.quarcoo/shoppn/views/register.php> |
| Login | <http://169.239.251.102:442/~andrew.quarcoo/shoppn/views/login.php> |
| Admin — Brands *(admin login required)* | <http://169.239.251.102:442/~andrew.quarcoo/shoppn/views/admin/brand.php> |
| Admin — Categories *(admin login required)* | <http://169.239.251.102:442/~andrew.quarcoo/shoppn/views/admin/category.php> |
| Live phpMyAdmin | <http://169.239.251.102:442/phpmyadmin> |

The app folder on the server is `~/public_html/shoppn/`, served through Apache
`mod_userdir` on port **442**.

## 💻 Local links (XAMPP)

| What | URL |
|------|-----|
| App (home) | <http://localhost/ecomlab/index.php> |
| Register | <http://localhost/ecomlab/views/register.php> |
| phpMyAdmin | <http://localhost/phpmyadmin> |

`app_url()` derives the base path from the request (`SCRIPT_NAME` /
`SCRIPT_FILENAME`), so the same code resolves links correctly under XAMPP
htdocs (`/ecomlab`) and under `mod_userdir` (`/~andrew.quarcoo/shoppn`).

## 🔗 Repository

| What | Value |
|------|-------|
| Repo | <https://github.com/AndrewQuarcoo/ecommerce_2026_Andrew_Quarcoo> |
| Tasks 1–4 branch | `feature/tasks-1-4-auth` |
| Tasks 5–8 branch (**deployed**) | `feature/tasks-5-8-brands-categories` |
| App folder | `ecomlab/` |

---

## ✅ What's built & verified live

| Task | Feature | Status |
|------|---------|--------|
| 1 | Database schema (`database/shoppn.sql`) | ✅ imported local + live |
| 2 | Folder structure + core (PDO base, session, helpers, layout) | ✅ |
| 3 | Customer registration (JS + server validation, bcrypt, image upload) | ✅ |
| 4 | Login + access control (`require_login` / `require_admin`) | ✅ |
| 5 | Add Brand (admin) | ✅ tested live |
| 6 | Edit Brand (`?edit_id=N`) | ✅ tested live |
| 7 | Add Category (admin) | ✅ tested live |
| 8 | Edit Category (`?edit_id=N`) | ✅ tested live |

Verified end-to-end against the live server: brand/category add and edit,
A→Z ordering, duplicate-name rejection (case-insensitive), empty-name
rejection, tampered and unknown ids rejected, CSRF rejection, HTML escaping
on output, admin nav links hidden from non-admins, and `require_admin()`
blocking guests and customers on both the pages and the action endpoints.

### Getting an admin account

Every signup is a customer (`user_role = 2`). A demo admin and a demo customer
exist on the live server so the Tasks 5–8 pages can be reviewed without
promoting an account by hand — their logins are in `SUBMISSION.txt`, which is
git-ignored on purpose, because **this repository is public** and those are
working credentials for a live site.

To promote any account yourself:

```sql
UPDATE customer SET user_role = 1 WHERE customer_email = 'you@example.com';
```

The role is read into the session at login, so log out and back in for the
**Brands** / **Categories** nav links to appear.

---

## 🗄️ Database notes

- **Local:** its own database, `shoppn`. Import:
  ```bash
  /Applications/XAMPP/xamppfiles/bin/mysql -u root < ecomlab/database/shoppn.sql
  ```
- **Live:** the shared host allows no `CREATE DATABASE`, so the shoppn tables
  live inside the existing database `ecommerce_2026A_andrew_quarcoo` (alongside
  the older `tasks` table from Lab 01). The live import file is the same schema
  without the `CREATE DATABASE` / `USE` lines.

---

## 🚀 How to redeploy

1. Build a clean archive of tracked files only — this deliberately excludes
   `core/db_cred.php`, so the live credentials on the server are never
   overwritten by local ones:
   ```bash
   git archive --format=tar <branch> ecomlab/ > shoppn.tar
   ```
2. Copy it up (SSH/SCP on port **422**) and extract over the app folder:
   ```bash
   tar -xf /tmp/shoppn.tar --strip-components=1 -C ~/public_html/shoppn
   ```
3. Clear macOS AppleDouble junk if the files went up any other way:
   `find ~/public_html/shoppn -name '._*' -delete`
4. **Permissions** — `images/products`, `images/customers` → `777`;
   `error/error.log` → `666`.
   `core/db_cred.php` must stay **readable by Apache** (`644`). Setting it to
   `700` makes every PHP page return **500**, because the web server can no
   longer `require` it. `core/.htaccess` is what keeps it off the web, not the
   file mode.
5. Smoke-test the live home page, then log in as admin and add a brand.

---

## 🔒 Security summary

- **SQL injection** — every query with user input uses **prepared statements**.
- **Passwords** — **bcrypt** (`password_hash` / `password_verify`); hashes never
  reach a view. Registration requires 8–72 characters with an uppercase letter,
  a lowercase letter, a number and a special character, enforced on the server
  as well as in `js/validate.js`.
- **XSS** — all output escaped with `htmlspecialchars`, plus a
  **Content-Security-Policy** (`script-src 'self'`), `X-Content-Type-Options`,
  `X-Frame-Options: DENY` and `Referrer-Policy`.
- **CSRF** — every state-changing POST carries a per-session token, verified
  with `hash_equals()`.
- **Sessions** — cookie is `HttpOnly` + `SameSite=Lax`; the session id is
  regenerated on login (defeats session fixation).
- **Input validation** — client and server. Ids (`edit_id`, `brand_id`,
  `cat_id`) are validated as positive integers; names are length-capped to the
  `VARCHAR(100)` the schema declares.
- **Open redirect** — the post-login "return to" target is followed only when
  it is a safe same-site path; `redirect()` strips CR/LF.
- **File uploads** — validated by MIME type and size, and the saved extension
  is derived from the detected MIME, never from the user's filename.
- **Authorization** — every admin page and admin action calls `require_admin()`
  **before any output**, so a non-admin never sees partial admin HTML.
- **Server config** — `Options -Indexes` stops directory listings, and
  `core/.htaccess` denies HTTP access to the bootstrap/credentials folder.
