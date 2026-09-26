<?php
/**
 * ProductController.php — middleman between actions/views and the Product model.
 *
 * MVC note: instantiates the model, returns arrays/booleans. No SQL, no HTML.
 * Read methods (getAllBrands/getAllCategories) return plain arrays so Views
 * (e.g. the sidebar) can loop them directly. Write methods return a structured
 * ['success'=>bool, 'error'=>?] so actions can flash a message.
 */

require_once __DIR__ . '/../classes/ProductClass.php';

class ProductController
{
    private $product;

    public function __construct()
    {
        $this->product = new Product();
    }

    // ── Brands ───────────────────────────────────────────────

    public function getAllBrands()
    {
        return $this->product->getAllBrands();
    }

    public function getBrandById($id)
    {
        return $this->product->getBrandById($id);
    }

    public function addBrand($name)
    {
        foreach ($this->product->getAllBrands() as $b) {
            if (strcasecmp($b['brand_name'], $name) === 0) {
                return ['success' => false, 'error' => 'That brand already exists.'];
            }
        }
        $ok = $this->product->addBrand($name);
        return $ok ? ['success' => true] : ['success' => false, 'error' => 'Could not add brand.'];
    }

    public function updateBrand($id, $name)
    {
        if (!$this->product->getBrandById($id)) {
            return ['success' => false, 'error' => 'Brand not found.'];
        }
        foreach ($this->product->getAllBrands() as $b) {
            if ((int) $b['brand_id'] !== (int) $id && strcasecmp($b['brand_name'], $name) === 0) {
                return ['success' => false, 'error' => 'Another brand already uses that name.'];
            }
        }
        $ok = $this->product->updateBrand($id, $name);
        return $ok ? ['success' => true] : ['success' => false, 'error' => 'Could not update brand.'];
    }

    // ── Categories ───────────────────────────────────────────

    public function getAllCategories()
    {
        return $this->product->getAllCategories();
    }

    public function getCategoryById($id)
    {
        return $this->product->getCategoryById($id);
    }

    public function addCategory($name)
    {
        foreach ($this->product->getAllCategories() as $c) {
            if (strcasecmp($c['cat_name'], $name) === 0) {
                return ['success' => false, 'error' => 'That category already exists.'];
            }
        }
        $ok = $this->product->addCategory($name);
        return $ok ? ['success' => true] : ['success' => false, 'error' => 'Could not add category.'];
    }

    public function updateCategory($id, $name)
    {
        if (!$this->product->getCategoryById($id)) {
            return ['success' => false, 'error' => 'Category not found.'];
        }
        foreach ($this->product->getAllCategories() as $c) {
            if ((int) $c['cat_id'] !== (int) $id && strcasecmp($c['cat_name'], $name) === 0) {
                return ['success' => false, 'error' => 'Another category already uses that name.'];
            }
        }
        $ok = $this->product->updateCategory($id, $name);
        return $ok ? ['success' => true] : ['success' => false, 'error' => 'Could not update category.'];
    }
}
