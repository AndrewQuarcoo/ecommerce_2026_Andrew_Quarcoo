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

    // ── Products (Tasks 9 & 10) ─────────────────────

    public function getProductById($id)
    {
        return $this->product->getProductById($id);
    }

    public function getAllProducts()
    {
        return $this->product->getAllProducts();
    }

    public function getFeaturedProducts($limit = 6)
    {
        return $this->product->getFeaturedProducts($limit);
    }

    public function getProductsByCategory($catId)
    {
        return $this->product->getProductsByCategory($catId);
    }

    public function getProductsByBrand($brandId)
    {
        return $this->product->getProductsByBrand($brandId);
    }

    public function searchProducts($query)
    {
        return $this->product->searchProducts($query);
    }

    /**
     * Task 9 — create a product. The Action has already moved the uploaded
     * file and passes only its filename; the Controller checks that the
     * foreign keys actually exist, because the DB would otherwise reject the
     * INSERT with a raw constraint error the user cannot act on.
     */
    public function addProduct($cat, $brand, $title, $price, $desc, $image, $keywords)
    {
        if (!$this->product->getCategoryById($cat)) {
            return ['success' => false, 'error' => 'Please choose a valid category.'];
        }
        if (!$this->product->getBrandById($brand)) {
            return ['success' => false, 'error' => 'Please choose a valid brand.'];
        }
        $ok = $this->product->addProduct($cat, $brand, $title, $price, $desc, $image, $keywords);
        return $ok ? ['success' => true] : ['success' => false, 'error' => 'Could not add product.'];
    }

    /** Task 9 — update a product, with the same foreign-key checks. */
    public function updateProduct($id, $cat, $brand, $title, $price, $desc, $image, $keywords)
    {
        if (!$this->product->getProductById($id)) {
            return ['success' => false, 'error' => 'Product not found.'];
        }
        if (!$this->product->getCategoryById($cat)) {
            return ['success' => false, 'error' => 'Please choose a valid category.'];
        }
        if (!$this->product->getBrandById($brand)) {
            return ['success' => false, 'error' => 'Please choose a valid brand.'];
        }
        $ok = $this->product->updateProduct($id, $cat, $brand, $title, $price, $desc, $image, $keywords);
        return $ok ? ['success' => true] : ['success' => false, 'error' => 'Could not update product.'];
    }
}
