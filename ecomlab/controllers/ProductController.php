<?php
/**
 * ProductController.php — middleman between actions/views and the Product model.
 *
 * MVC note: instantiates the Model and returns plain data. No SQL, no HTML,
 * no $_POST/$_SESSION reads — the Action does that and passes values in.
 *
 * Read methods return arrays/rows so a View (the sidebar, the admin tables)
 * can loop them directly. Write methods return a structured
 * ['success' => bool, 'error' => string] so an Action can flash a message.
 */

require_once __DIR__ . '/../classes/ProductClass.php';

class ProductController
{
    private $product;

    public function __construct()
    {
        $this->product = new Product();
    }

    // ── Brands (Tasks 5 & 6) ─────────────────────────────────

    public function getAllBrands()
    {
        return $this->product->getAllBrands();
    }

    public function getBrandById($id)
    {
        return $this->product->getBrandById($id);
    }

    /** Task 5 — create a brand, refusing a duplicate name. */
    public function addBrand($name)
    {
        if ($this->product->brandNameExists($name)) {
            return ['success' => false, 'error' => 'That brand already exists.'];
        }
        return $this->product->addBrand($name)
            ? ['success' => true]
            : ['success' => false, 'error' => 'Could not add brand.'];
    }

    /** Task 6 — rename a brand, refusing an unknown id or a duplicate name. */
    public function updateBrand($id, $name)
    {
        if (!$this->product->getBrandById($id)) {
            return ['success' => false, 'error' => 'Brand not found.'];
        }
        if ($this->product->brandNameExists($name, $id)) {
            return ['success' => false, 'error' => 'Another brand already uses that name.'];
        }
        return $this->product->updateBrand($id, $name)
            ? ['success' => true]
            : ['success' => false, 'error' => 'Could not update brand.'];
    }

    // ── Categories (Tasks 7 & 8) ─────────────────────────────

    public function getAllCategories()
    {
        return $this->product->getAllCategories();
    }

    public function getCategoryById($id)
    {
        return $this->product->getCategoryById($id);
    }

    /** Task 7 — create a category, refusing a duplicate name. */
    public function addCategory($name)
    {
        if ($this->product->categoryNameExists($name)) {
            return ['success' => false, 'error' => 'That category already exists.'];
        }
        return $this->product->addCategory($name)
            ? ['success' => true]
            : ['success' => false, 'error' => 'Could not add category.'];
    }

    /** Task 8 — rename a category, refusing an unknown id or a duplicate name. */
    public function updateCategory($id, $name)
    {
        if (!$this->product->getCategoryById($id)) {
            return ['success' => false, 'error' => 'Category not found.'];
        }
        if ($this->product->categoryNameExists($name, $id)) {
            return ['success' => false, 'error' => 'Another category already uses that name.'];
        }
        return $this->product->updateCategory($id, $name)
            ? ['success' => true]
            : ['success' => false, 'error' => 'Could not update category.'];
    }
}
