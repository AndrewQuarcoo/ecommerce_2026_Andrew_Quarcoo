<?php
/**
 * brand.php — admin: add / edit brands (View). Tasks 5 & 6.
 *
 * MVC note: a View asks the Controller for data and renders it. It never
 * runs SQL and never performs the INSERT/UPDATE — the forms POST to
 * actions/add_brand_action.php and actions/update_brand_action.php.
 *
 * Security: require_admin() runs before ANY output, so a customer or a
 * guest is redirected away rather than shown a half-rendered admin page.
 */

require_once __DIR__ . '/../../core/core.php';
require_admin();
require_once __DIR__ . '/../../controllers/ProductController.php';

$controller = new ProductController();

// Task 6 — edit mode whenever a valid ?edit_id=N names a real brand.
$editId    = filter_input(INPUT_GET, 'edit_id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$editBrand = $editId ? $controller->getBrandById($editId) : false;

// Task 5 — the full list shown below the form.
$brands = $controller->getAllBrands();

$page_title = 'Admin — Brands';
require_once __DIR__ . '/../layout/header.php';
?>
<div class="card wide">
    <h1><?php echo $editBrand ? 'Edit Brand' : 'Manage Brands'; ?></h1>
    <p class="sub">Brands group products by maker.</p>

    <?php require __DIR__ . '/../layout/flash.php'; ?>

    <?php if ($editBrand): ?>
        <!-- Edit form (Task 6): same page, different action + hidden id. -->
        <form action="<?php echo app_url('actions/update_brand_action.php'); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="brand_id" value="<?php echo (int) $editBrand['brand_id']; ?>">
            <div class="form-row">
                <label for="brand_name">Brand name</label>
                <input type="text" id="brand_name" name="brand_name" maxlength="100" required
                       value="<?php echo htmlspecialchars($editBrand['brand_name']); ?>">
            </div>
            <button type="submit" class="btn">Update Brand</button>
            <p class="form-foot">
                <a href="<?php echo app_url('views/admin/brand.php'); ?>">Cancel</a>
            </p>
        </form>
    <?php else: ?>
        <!-- Add form (Task 5). -->
        <form action="<?php echo app_url('actions/add_brand_action.php'); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <div class="form-row">
                <label for="brand_name">Brand name</label>
                <input type="text" id="brand_name" name="brand_name" maxlength="100" required
                       placeholder="e.g. Samsung">
            </div>
            <button type="submit" class="btn">Add Brand</button>
        </form>
    <?php endif; ?>
</div>

<div class="card wide">
    <h2>All Brands</h2>
    <?php if ($brands): ?>
        <table class="table">
            <thead>
                <tr><th>ID</th><th>Name</th><th>Action</th></tr>
            </thead>
            <tbody>
                <?php foreach ($brands as $b): ?>
                    <tr>
                        <td><?php echo (int) $b['brand_id']; ?></td>
                        <td><?php echo htmlspecialchars($b['brand_name']); ?></td>
                        <td>
                            <a class="btn-sm"
                               href="<?php echo app_url('views/admin/brand.php?edit_id=' . (int) $b['brand_id']); ?>">Edit</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="muted">No brands yet. Add your first one above.</p>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../layout/footer.php'; ?>
