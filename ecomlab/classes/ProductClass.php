<?php
/**
 * ProductClass.php — the Model for brands, categories and products.
 *
 * MVC note: SQL and data logic only. No HTML, no echo, no $_POST/$_SESSION.
 * Every query that touches user input uses a prepared statement.
 *
 * Tasks 5–8 cover brands and categories; product methods arrive with Task 9.
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
}
