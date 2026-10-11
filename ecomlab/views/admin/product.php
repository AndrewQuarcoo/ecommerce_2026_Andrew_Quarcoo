<?php
/**
 * product.php — admin: add / edit products (View). Task 9.
 *
 * The same ?edit_id=N switch as brand.php and category.php, with three extra
 * wrinkles: two foreign-key dropdowns, a file upload (so the form needs
 * enctype="multipart/form-data"), and a preview of the current image.
 *
 * MVC note: the View asks the Controller for categories, brands and the row
 * being edited. It runs no SQL and performs no upload — the forms POST to
 * actions/add_product_action.php and actions/update_product_action.php.
 *
 * Security: require_admin() runs before ANY output.
 */

require_once __DIR__ . '/../../core/core.php';
require_admin();
require_once __DIR__ . '/../../controllers/ProductController.php';

$controller = new ProductController();

$editId   = filter_input(INPUT_GET, 'edit_id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$editProd = $editId ? $controller->getProductById($editId) : false;

$categories = $controller->getAllCategories();
$brands     = $controller->getAllBrands();
$products   = $controller->getAllProducts();

// Values to show in the form: the row being edited, or whatever the user
// typed on a submission the server rejected, or empty.
$old = $_SESSION['old'] ?? [];
unset($_SESSION['old']);
$val = function ($key, $column = null) use ($editProd, $old) {
    if (isset($old[$key])) {
        return $old[$key];
    }
    return $editProd ? ($editProd[$column ?? $key] ?? '') : '';
};

$page_title = 'Admin — Products';
require_once __DIR__ . '/../layout/header.php';
?>
<div class="card wide">
    <h1><?php echo $editProd ? 'Edit Product' : 'Add Product'; ?></h1>
    <p class="sub">Products need a category and a brand, so add those first.</p>

    <?php require __DIR__ . '/../layout/flash.php'; ?>

    <?php if (!$categories || !$brands): ?>
        <p class="muted">
            You need at least one
            <a href="<?php echo app_url('views/admin/category.php'); ?>">category</a> and one
            <a href="<?php echo app_url('views/admin/brand.php'); ?>">brand</a>
            before you can add a product.
        </p>
    <?php else: ?>
    <form id="product-form" method="POST" enctype="multipart/form-data"
          action="<?php echo $editProd
                        ? app_url('actions/update_product_action.php')
                        : app_url('actions/add_product_action.php'); ?>">
        <?php echo csrf_field(); ?>
        <?php if ($editProd): ?>
            <input type="hidden" name="product_id" value="<?php echo (int) $editProd['product_id']; ?>">
            <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($editProd['product_image']); ?>">
        <?php endif; ?>

        <div class="form-row">
            <label for="product_title">Title</label>
            <input type="text" id="product_title" name="product_title" maxlength="200" required
                   value="<?php echo htmlspecialchars($val('product_title')); ?>">
            <div class="field-error" id="error-product-title"></div>
        </div>

        <div class="form-grid">
            <div class="form-row">
                <label for="product_cat">Category</label>
                <select id="product_cat" name="product_cat" required>
                    <option value="">Select…</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?php echo (int) $c['cat_id']; ?>"
                            <?php echo (int) $val('product_cat') === (int) $c['cat_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($c['cat_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="field-error" id="error-product-cat"></div>
            </div>

            <div class="form-row">
                <label for="product_brand">Brand</label>
                <select id="product_brand" name="product_brand" required>
                    <option value="">Select…</option>
                    <?php foreach ($brands as $b): ?>
                        <option value="<?php echo (int) $b['brand_id']; ?>"
                            <?php echo (int) $val('product_brand') === (int) $b['brand_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($b['brand_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="field-error" id="error-product-brand"></div>
            </div>
        </div>

        <div class="form-row">
            <label for="product_price">Price (GH₵)</label>
            <input type="text" inputmode="decimal" id="product_price" name="product_price" required
                   value="<?php echo htmlspecialchars($val('product_price')); ?>" placeholder="e.g. 249.99">
            <div class="field-error" id="error-product-price"></div>
        </div>

        <div class="form-row">
            <label for="product_desc">Description</label>
            <textarea id="product_desc" name="product_desc" rows="4" maxlength="500"><?php
                echo htmlspecialchars($val('product_desc')); ?></textarea>
            <div class="field-error" id="error-product-desc"></div>
        </div>

        <div class="form-row">
            <label for="product_keywords">Keywords</label>
            <input type="text" id="product_keywords" name="product_keywords" maxlength="100"
                   value="<?php echo htmlspecialchars($val('product_keywords')); ?>"
                   placeholder="comma, separated, search terms">
            <div class="field-error" id="error-product-keywords"></div>
        </div>

        <div class="form-row">
            <label for="product_image">Image<?php echo $editProd ? ' (leave empty to keep the current one)' : ''; ?></label>
            <input type="file" id="product_image" name="product_image"
                   accept="image/jpeg,image/png,image/gif,image/webp"
                   <?php echo $editProd ? '' : 'required'; ?>>
            <p class="hint">JPG, PNG, GIF or WEBP, 2 MB maximum.</p>
            <div class="field-error" id="error-product-image"></div>
            <?php if ($editProd && !empty($editProd['product_image'])): ?>
                <p class="current-image">
                    Current image:<br>
                    <img src="<?php echo product_image_url($editProd['product_image']); ?>"
                         alt="Current product image" width="140">
                </p>
            <?php endif; ?>
        </div>

        <button type="submit" class="btn" id="product-submit">
            <?php echo $editProd ? 'Update Product' : 'Add Product'; ?>
        </button>
        <?php if ($editProd): ?>
            <p class="form-foot"><a href="<?php echo app_url('views/admin/product.php'); ?>">Cancel</a></p>
        <?php endif; ?>
    </form>
    <?php endif; ?>
</div>

<div class="card wide">
    <h2>All Products</h2>
    <?php if ($products): ?>
        <table class="table">
            <thead>
                <tr><th>ID</th><th>Image</th><th>Title</th><th>Category</th><th>Brand</th><th>Price</th><th>Action</th></tr>
            </thead>
            <tbody>
                <?php foreach ($products as $p): ?>
                    <tr>
                        <td><?php echo (int) $p['product_id']; ?></td>
                        <td><img src="<?php echo product_image_url($p['product_image']); ?>"
                                 alt="" width="48" height="48" class="thumb-sm"></td>
                        <td><?php echo htmlspecialchars($p['product_title']); ?></td>
                        <td><?php echo htmlspecialchars($p['cat_name']); ?></td>
                        <td><?php echo htmlspecialchars($p['brand_name']); ?></td>
                        <td><?php echo money($p['product_price']); ?></td>
                        <td><a class="btn-sm"
                               href="<?php echo app_url('views/admin/product.php?edit_id=' . (int) $p['product_id']); ?>">Edit</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="muted">No products yet. Add your first one above.</p>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../layout/footer.php'; ?>
