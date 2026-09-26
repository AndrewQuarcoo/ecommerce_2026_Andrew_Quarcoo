<?php
/**
 * ProductClass.php — the Model for brands, categories and products.
 *
 * MVC note: SQL only. Every method uses prepared statements; none echo HTML.
 * Tasks 5–8 cover brands and categories; product methods arrive with Task 9+.
 */

require_once __DIR__ . '/../core/db_class.php';

class Product extends Database
{
    // ── Brands (Task 5 & 6) ──────────────────────────────────

    /** Insert a brand. @return bool */
    public function addBrand($name)
    {
        return $this->execute(
            'INSERT INTO brands (brand_name) VALUES (?)',
            [$name]
        );
    }

    /** All brands, A→Z. @return array */
    public function getAllBrands()
    {
        return $this->fetchAll('SELECT * FROM brands ORDER BY brand_name ASC');
    }

    /** One brand by id. @return array|false */
    public function getBrandById($id)
    {
        return $this->fetchOne('SELECT * FROM brands WHERE brand_id = ?', [$id]);
    }

    /** Rename a brand. @return bool */
    public function updateBrand($id, $name)
    {
        return $this->execute(
            'UPDATE brands SET brand_name = ? WHERE brand_id = ?',
            [$name, $id]
        );
    }

    // ── Categories (Task 7 & 8) ──────────────────────────────

    /** Insert a category. @return bool */
    public function addCategory($name)
    {
        return $this->execute(
            'INSERT INTO categories (cat_name) VALUES (?)',
            [$name]
        );
    }

    /** All categories, A→Z. @return array */
    public function getAllCategories()
    {
        return $this->fetchAll('SELECT * FROM categories ORDER BY cat_name ASC');
    }

    /** One category by id. @return array|false */
    public function getCategoryById($id)
    {
        return $this->fetchOne('SELECT * FROM categories WHERE cat_id = ?', [$id]);
    }

    /** Rename a category. @return bool */
    public function updateCategory($id, $name)
    {
        return $this->execute(
            'UPDATE categories SET cat_name = ? WHERE cat_id = ?',
            [$name, $id]
        );
    }
}
