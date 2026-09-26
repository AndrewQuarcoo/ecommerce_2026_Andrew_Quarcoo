<?php
/**
 * category.php — admin: add / edit categories (View).
 * Security: require_admin() runs before ANY output.
 */
require_once __DIR__ . '/../../core/core.php';
require_admin();
require_once __DIR__ . '/../../controllers/ProductController.php';

$controller = new ProductController();

$editId  = filter_input(INPUT_GET, 'edit_id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$editCat = $editId ? $controller->getCategoryById($editId) : null;

$categories = $controller->getAllCategories();

$page_title = 'Admin — Categories';
require_once __DIR__ . '/../layout/header.php';
?>
<div class="card wide">
    <h1><?php echo $editCat ? 'Edit Category' : 'Manage Categories'; ?></h1>
    <p class="sub">Categories group products by type. Add one below.</p>

    <?php require __DIR__ . '/../layout/flash.php'; ?>

    <?php if ($editCat): ?>
        <form action="<?php echo app_url('actions/update_category_action.php'); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="cat_id" value="<?php echo (int) $editCat['cat_id']; ?>">
            <div class="form-row">
                <label for="cat_name">Category name</label>
                <input type="text" id="cat_name" name="cat_name" maxlength="100"
                       value="<?php echo htmlspecialchars($editCat['cat_name']); ?>" required>
            </div>
            <button type="submit" class="btn">Update Category</button>
            <p class="form-foot"><a href="<?php echo app_url('views/admin/category.php'); ?>">Cancel</a></p>
        </form>
    <?php else: ?>
        <form action="<?php echo app_url('actions/add_category_action.php'); ?>" method="POST">
            <?php echo csrf_field(); ?>
            <div class="form-row">
                <label for="cat_name">Category name</label>
                <input type="text" id="cat_name" name="cat_name" maxlength="100"
                       placeholder="e.g. Electronics" required>
            </div>
            <button type="submit" class="btn">Add Category</button>
        </form>
    <?php endif; ?>
</div>

<div class="card wide" style="margin-top:20px;">
    <h2>All Categories</h2>
    <?php if ($categories): ?>
        <table class="table">
            <thead><tr><th>ID</th><th>Name</th><th style="width:90px;">Action</th></tr></thead>
            <tbody>
            <?php foreach ($categories as $c): ?>
                <tr>
                    <td><?php echo (int) $c['cat_id']; ?></td>
                    <td><?php echo htmlspecialchars($c['cat_name']); ?></td>
                    <td><a class="btn-sm"
                           href="<?php echo app_url('views/admin/category.php?edit_id=' . (int) $c['cat_id']); ?>">Edit</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="muted">No categories yet. Add your first one above.</p>
    <?php endif; ?>
</div>
<?php require_once __DIR__ . '/../layout/footer.php'; ?>
