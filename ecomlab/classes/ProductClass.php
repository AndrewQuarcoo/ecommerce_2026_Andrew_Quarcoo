<?php
/**
 * ProductClass.php — the Model for brands, categories and products.
 *
 * MVC note: SQL and data logic only. No HTML, no echo, no $_POST/$_SESSION.
 * Every query that touches user input uses a prepared statement.
 *
 * Tasks 5–8 cover brands and categories; Tasks 9–10 add products.
 */

require_once __DIR__ . '/../core/db_class.php';

class Product extends Database
{
    // ── Brands (Tasks 5 & 6) ─────────────────────────────────

    /**
     * Insert a brand (Task 5).
     * @return bool true on success
     */
    public function addBrand($name)
    {
        return $this->execute(
            'INSERT INTO brands (brand_name) VALUES (?)',
            [$name]
        );
    }

    /**
     * Every brand, A→Z (Task 5). Also feeds the storefront sidebar.
     * @return array list of rows (empty array if none)
     */
    public function getAllBrands()
    {
        return $this->fetchAll('SELECT * FROM brands ORDER BY brand_name ASC');
    }

    /**
     * One brand by id (Task 6) — used to pre-fill the edit form.
     * @return array|false the row, or false if no such brand
     */
    public function getBrandById($id)
    {
        return $this->fetchOne('SELECT * FROM brands WHERE brand_id = ?', [$id]);
    }

    /**
     * Rename a brand (Task 6).
     * @return bool true on success
     */
    public function updateBrand($id, $name)
    {
        return $this->execute(
            'UPDATE brands SET brand_name = ? WHERE brand_id = ?',
            [$name, $id]
        );
    }

    /**
     * Is this brand name already taken? $exceptId lets the edit form keep
     * its own name. The column collation (utf8mb4_unicode_ci) makes `=`
     * case-insensitive, so "Samsung" and "samsung" collide — which matches
     * the UNIQUE KEY on brand_name.
     * @return bool
     */
    public function brandNameExists($name, $exceptId = 0)
    {
        $row = $this->fetchOne(
            'SELECT brand_id FROM brands WHERE brand_name = ? AND brand_id <> ?',
            [$name, (int) $exceptId]
        );
        return $row !== false;
    }

    // ── Categories (Tasks 7 & 8) ─────────────────────────────

    /**
     * Insert a category (Task 7).
     * @return bool true on success
     */
    public function addCategory($name)
    {
        return $this->execute(
            'INSERT INTO categories (cat_name) VALUES (?)',
            [$name]
        );
    }

    /**
     * Every category, A→Z (Task 7). Also feeds the storefront sidebar.
     * @return array list of rows (empty array if none)
     */
    public function getAllCategories()
    {
        return $this->fetchAll('SELECT * FROM categories ORDER BY cat_name ASC');
    }

    /**
     * One category by id (Task 8) — used to pre-fill the edit form.
     * @return array|false the row, or false if no such category
     */
    public function getCategoryById($id)
    {
        return $this->fetchOne('SELECT * FROM categories WHERE cat_id = ?', [$id]);
    }

    /**
     * Rename a category (Task 8).
     * @return bool true on success
     */
    public function updateCategory($id, $name)
    {
        return $this->execute(
            'UPDATE categories SET cat_name = ? WHERE cat_id = ?',
            [$name, $id]
        );
    }

    /**
     * Is this category name already taken? See brandNameExists().
     * @return bool
     */
    public function categoryNameExists($name, $exceptId = 0)
    {
        $row = $this->fetchOne(
            'SELECT cat_id FROM categories WHERE cat_name = ? AND cat_id <> ?',
            [$name, (int) $exceptId]
        );
        return $row !== false;
    }

    // ── Products: admin writes (Task 9) ───────────────

    /**
     * Insert a product (Task 9). $image is just the stored filename — the
     * Action moves the uploaded file; the Model only records its name.
     * @return bool true on success
     */
    public function addProduct($cat, $brand, $title, $price, $desc, $image, $keywords)
    {
        return $this->execute(
            'INSERT INTO products
                (product_cat, product_brand, product_title, product_price,
                 product_desc, product_image, product_keywords)
             VALUES (?, ?, ?, ?, ?, ?, ?)',
            [$cat, $brand, $title, $price, $desc, $image, $keywords]
        );
    }

    /**
     * Update a product (Task 9). The caller decides what $image holds: the
     * newly uploaded filename, or the existing one when no new file was sent.
     * @return bool true on success
     */
    public function updateProduct($id, $cat, $brand, $title, $price, $desc, $image, $keywords)
    {
        return $this->execute(
            'UPDATE products SET
                product_cat = ?, product_brand = ?, product_title = ?,
                product_price = ?, product_desc = ?, product_image = ?,
                product_keywords = ?
             WHERE product_id = ?',
            [$cat, $brand, $title, $price, $desc, $image, $keywords, $id]
        );
    }

    /**
     * One product by id, joined so the caller also gets the readable
     * category and brand names (Task 9 edit form, Task 10 detail page).
     * @return array|false the row, or false if no such product
     */
    public function getProductById($id)
    {
        return $this->fetchOne(
            'SELECT p.*, c.cat_name, b.brand_name
               FROM products p
               JOIN categories c ON c.cat_id   = p.product_cat
               JOIN brands     b ON b.brand_id = p.product_brand
              WHERE p.product_id = ?',
            [$id]
        );
    }

    // ── Products: storefront reads (Task 10) ─────────────

    /**
     * The SELECT behind every storefront listing. Kept in one place so the
     * home grid, the category/brand filters and search all return rows of
     * exactly the same shape, which lets one card partial render them all.
     */
    private function listSelect()
    {
        return 'SELECT p.*, c.cat_name, b.brand_name
                  FROM products p
                  JOIN categories c ON c.cat_id   = p.product_cat
                  JOIN brands     b ON b.brand_id = p.product_brand';
    }

    /**
     * Every product, newest first (Task 9 admin table). Distinct from
     * getFeaturedProducts(), which is deliberately random and capped — an
     * admin list must be stable and complete, or rows appear to move about
     * between page loads.
     * @return array list of rows (empty array if none)
     */
    public function getAllProducts()
    {
        return $this->fetchAll($this->listSelect() . ' ORDER BY p.product_id DESC');
    }

    /**
     * A random handful for the home page (Task 10).
     *
     * $limit is interpolated rather than bound: with real (non-emulated)
     * prepared statements MySQL will not accept a placeholder in LIMIT, and
     * a bound value arrives as the string \'6\' which is a syntax error. It is
     * cast to int and clamped first, so nothing but a small number can land
     * in the SQL — and it never comes from user input anyway.
     * @return array list of rows (empty array if none)
     */
    public function getFeaturedProducts($limit = 6)
    {
        $limit = max(1, min(60, (int) $limit));
        return $this->fetchAll($this->listSelect() . ' ORDER BY RAND() LIMIT ' . $limit);
    }

    /**
     * Every product in one category (Task 10, ?cat=N).
     * @return array list of rows (empty array if none)
     */
    public function getProductsByCategory($catId)
    {
        return $this->fetchAll(
            $this->listSelect() . ' WHERE p.product_cat = ? ORDER BY p.product_title ASC',
            [$catId]
        );
    }

    /**
     * Every product of one brand (Task 10, ?brand=N).
     * @return array list of rows (empty array if none)
     */
    public function getProductsByBrand($brandId)
    {
        return $this->fetchAll(
            $this->listSelect() . ' WHERE p.product_brand = ? ORDER BY p.product_title ASC',
            [$brandId]
        );
    }

    /**
     * Search titles and keywords (Task 10).
     *
     * The % wildcards are concatenated onto the *value*, not into the SQL, so
     * the search term is still a bound parameter and cannot alter the query.
     * % and _ typed by the user are escaped first, otherwise a query of "%"
     * would match every row.
     * @return array list of rows (empty array if none)
     */
    public function searchProducts($query)
    {
        $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $query) . '%';
        return $this->fetchAll(
            $this->listSelect() . ' WHERE p.product_title LIKE ? OR p.product_keywords LIKE ?
                                    ORDER BY p.product_title ASC',
            [$like, $like]
        );
    }
}
