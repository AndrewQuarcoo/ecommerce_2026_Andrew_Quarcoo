<?php
/**
 * brand.php — admin: add / edit brands (View).
 * Security: require_admin() runs before ANY output.
 */
require_once __DIR__ . '/../../core/core.php';
require_admin();
require_once __DIR__ . '/../../controllers/ProductController.php';

$controller = new ProductController();

// Edit mode when a valid ?edit_id=N is present.
$editId    = filter_input(INPUT_GET, 'edit_id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$editBrand = $editId ? $controller->getBrandById($editId) : null;

$brands = $controller->getAllBrands();

$page_title = 'Admin — Brands';
require_once __DIR__ . '/../layout/header.php';
?>
<div class="card wide">
    <h1><?php echo $editBrand ? 'Edit Brand' : 'Manage Brands'; ?></h1>
    <p class="sub">Brands group products by maker. Add one below.</p>

    <?php require __DIR__ . '/../layout/flash.php'; ?>

    <?php if ($editBrand): ?>
        <form action="<?php echo app_url('actions/update_brand_action.php'); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="brand_id" value="<?php echo (int) $editBrand['brand_id']; ?>">
            <div class="form-row">
                <label for="brand_name">Brand name</label>
                <input type="text" id="brand_name" name="brand_name" maxlength="100"
                       value="<?php echo htmlspecialchars($editBrand['brand_name']); ?>" required>
            </div>
            <button type="submit" class="btn">Update Brand</button>
            <p class="form-foot"><a href="<?php echo app_url('views/admin/brand.php'); ?>">Cancel</a></p>
        </form>
    <?php else: ?>
        <form action="<?php echo app_url('actions/add_brand_action.php'); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <div class="form-row">
                <label for="brand_name">Brand name</label>
                <input type="text" id="brand_name" name="brand_name" maxlength="100"
                       placeholder="e.g. Samsung" required>
            </div>
            <button type="submit" class="btn">Add Brand</button>
        </form>
    <?php endif; ?>
</div>

<div class="card wide" style="margin-top:20px;">
    <h2>All Brands</h2>
    <?php if ($brands): ?>
        <table class="table">
            <thead><tr><th>ID</th><th>Name</th><th style="width:90px;">Action</th></tr></thead>
            <tbody>
            <?php foreach ($brands as $b): ?>
                <tr>
                    <td><?php echo (int) $b['brand_id']; ?></td>
                    <td><?php echo htmlspecialchars($b['brand_name']); ?></td>
                    <td><a class="btn-sm"
                           href="<?php echo app_url('views/admin/brand.php?edit_id=' . (int) $b['brand_id']); ?>">Edit</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="muted">No brands yet. Add your first one above.</p>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../layout/footer.php'; ?>
