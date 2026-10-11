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
| Tasks 1–4 | `feature/tasks-1-4-auth` |
| Tasks 5–8 | `feature/tasks-5-8-brands-categories` |
| Tasks 9–10 | `feature/tasks-9-10-products` |
| Tasks 11–12 | `feature/tasks-11-12-cart` |
| Task 13 (**deployed**) | `feature/task-13-payment` |
| Craigslist restyle | `feature/craigslist-ui` |
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
| 9 | Product add/edit with image upload | ✅ tested live |
| 10 | Product grid, filters, detail page, search | ✅ tested live |
| 11 | Add to cart + header summary | ✅ tested live |
| 12 | Remove items, update quantities | ✅ tested live |
| 13 | Paystack payment, order, invoice | ⚠️ see *Paystack keys* below |

Verified end-to-end against the live server, 62 checks for tasks 9–10 and
71 for tasks 11–13: brand/category/product add and edit, A→Z ordering,
duplicate and empty-name rejection, tampered/unknown/negative ids, CSRF
rejection, HTML escaping, image uploads (including a PHP file renamed
`.png`, which is refused), category/brand filters, product detail, search
(including a bare `%`, which must not match everything), cart add/remove/
update with invalid quantities refused, the guest-cart-carries-over-login
behaviour, and forged payment references — none of which create an order.

`require_admin()` blocks guests and customers on both the admin pages and
every admin action endpoint.

### Paystack keys

`views/payment.php` reports that keys are not set until
`core/paystack_cred.php` exists with real values. That file is **git-ignored**,
like `db_cred.php`: a Paystack secret key can charge and refund money and
this repository is public. To switch the live payment flow on:

```bash
cd ~/public_html/shoppn/core
cp paystack_cred.example.php paystack_cred.php   # already done by the deploy
# then edit paystack_cred.php and paste your pk_test_ / sk_test_ keys
```

No code change is needed. Test card: 4084 0840 8408 4081, any future
expiry, CVV 408.

Everything *around* the payment is already verified live: the page renders
the cart, the public key reaches the browser while the secret key never
does, the CSP exception is granted only to that page, and a forged
`?ref=` is refused and leaves the cart untouched.

### Staging copy

`~/public_html/shoppn-dev/` runs the `feature/craigslist-ui` branch against
the same database — useful for previewing the restyle without touching the
graded site:
<http://169.239.251.102:442/~andrew.quarcoo/shoppn-dev/index.php>

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
   `core/db_cred.php` and `core/paystack_cred.php` must stay **readable by
   Apache** (`644`). Setting one to `700` makes every PHP page return **500**,
   because the web server can no longer `require` it. `core/.htaccess` is what
   keeps them off the web, not the file mode.
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
- **Uploads cannot execute** — `images/.htaccess` turns the PHP interpreter
  off, unhooks the script handlers and denies the request outright. A live
  audit showed a `.php` file in `images/products/` *did* run; the upload code
  already made that unreachable (the extension comes from the MIME finfo reads
  out of the file's bytes, never from the user's filename), but that left one
  function as the only thing between an upload form and remote code execution
  in a `777` directory.
- **Server config** — `Options -Indexes` stops directory listings, and
  `core/`, `error/`, `database/`, `classes/` and `controllers/` each deny HTTP
  access. Apache authorisation does not affect filesystem includes, so the app
  is unaffected. `actions/` stays reachable (forms POST into it), so the
  include-only `actions/product_input.php` refuses to run if requested
  directly.
