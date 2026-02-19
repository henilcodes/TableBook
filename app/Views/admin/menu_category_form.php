<?php
ob_start();

$isEdit = ($mode ?? '') === 'edit';
$pageTitle = $isEdit ? 'Edit Menu Category' : 'Add Menu Category';
?>

<div class="admin-page-head">
    <h2 class="mb-1"><?= $pageTitle ?></h2>
    <p class="text-muted mb-0">
        <?= $isEdit ? 'Update category details.' : 'Create a new category to group menu items.' ?>
    </p>
</div>

<div class="card admin-form-card">
    <div class="card-body">
        <form method="POST" action="<?= $isEdit ? url('/admin/restaurants/' . $restaurant['id'] . '/menu/categories/' . $category['id'] . '/update') : url('/admin/restaurants/' . $restaurant['id'] . '/menu/categories') ?>">
            <input type="hidden" name="_token" value="<?= $csrf_token ?>">

            <div class="row g-4">
                <div class="col-12">
                    <h6 class="text-uppercase text-muted mb-2">Category Details</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Name *</label>
                            <input type="text" name="name" class="form-control" maxlength="50" required
                                   value="<?= htmlspecialchars($category['name'] ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Display Order</label>
                            <input type="number" name="display_order" class="form-control" min="0"
                                   value="<?= htmlspecialchars($category['display_order'] ?? '0') ?>">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" id="isCategoryActive"
                                       <?= !isset($category['is_active']) || $category['is_active'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="isCategoryActive">Active</label>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Image URL</label>
                            <input type="url" name="image_url" class="form-control" placeholder="https://example.com/category.jpg"
                                   value="<?= htmlspecialchars($category['image_url'] ?? '') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($category['description'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4 d-flex justify-content-between">
                <a href="<?= url('/admin/restaurants/' . $restaurant['id'] . '/menu/categories') ?>" class="btn btn-outline-secondary">
                    Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <?= $isEdit ? 'Save Changes' : 'Create Category' ?>
                </button>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = $pageTitle . ' - TableTap Admin';
require APP_PATH . '/Views/layouts/admin.php';
?>
