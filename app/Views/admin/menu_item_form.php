<?php
ob_start();

$isEdit = ($mode ?? '') === 'edit';
$pageTitle = $isEdit ? 'Edit Menu Item' : 'Add Menu Item';
?>

<div class="admin-page-head">
    <h2 class="mb-1"><?= $pageTitle ?></h2>
    <p class="text-muted mb-0">
        <?= $isEdit ? 'Update item details, price, and availability.' : 'Create a new dish for this restaurant.' ?>
    </p>
</div>

<div class="card admin-form-card">
    <div class="card-body">
        <form method="POST" action="<?= $isEdit ? url('/admin/restaurants/' . $restaurant['id'] . '/menu/items/' . $item['id'] . '/update') : url('/admin/restaurants/' . $restaurant['id'] . '/menu/items') ?>">
            <input type="hidden" name="_token" value="<?= $csrf_token ?>">

            <div class="row g-4">
                <div class="col-12">
                    <h6 class="text-uppercase text-muted mb-2">Primary Details</h6>
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label class="form-label">Name *</label>
                            <input type="text" name="name" class="form-control" maxlength="100" required
                                   value="<?= htmlspecialchars($item['name'] ?? '') ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">SKU</label>
                            <input type="text" name="sku" class="form-control" maxlength="50" placeholder="PST-101"
                                   value="<?= htmlspecialchars($item['sku'] ?? '') ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Price *</label>
                            <input type="number" name="price" class="form-control" min="0.01" step="0.01" required
                                   value="<?= htmlspecialchars($item['price'] ?? '0.00') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Category</label>
                            <select name="category_id" class="form-select">
                                <option value="0">No Category</option>
                                <?php
                                $currentCategory = $item['category_id'] ?? 0;
                                foreach ($categories as $category):
                                ?>
                                    <option value="<?= $category['id'] ?>" <?= (int)$currentCategory === (int)$category['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($category['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <h6 class="text-uppercase text-muted mb-2">Operational Fields</h6>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Prep Time (min)</label>
                            <input type="number" name="prep_time_minutes" class="form-control" min="1"
                                   value="<?= htmlspecialchars($item['prep_time_minutes'] ?? '15') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Calories</label>
                            <input type="number" name="calories" class="form-control" min="0" placeholder="Optional"
                                   value="<?= htmlspecialchars($item['calories'] ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Spice Level</label>
                            <?php $spice = $item['spice_level'] ?? 'none'; ?>
                            <select name="spice_level" class="form-select">
                                <option value="none" <?= $spice === 'none' ? 'selected' : '' ?>>None</option>
                                <option value="mild" <?= $spice === 'mild' ? 'selected' : '' ?>>Mild</option>
                                <option value="medium" <?= $spice === 'medium' ? 'selected' : '' ?>>Medium</option>
                                <option value="hot" <?= $spice === 'hot' ? 'selected' : '' ?>>Hot</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Display Order</label>
                            <input type="number" name="display_order" class="form-control" min="0"
                                   value="<?= htmlspecialchars($item['display_order'] ?? '0') ?>">
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <h6 class="text-uppercase text-muted mb-2">Description & Media</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Image URL</label>
                            <input type="url" name="image_url" class="form-control" placeholder="https://example.com/image.jpg"
                                   value="<?= htmlspecialchars($item['image_url'] ?? '') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($item['description'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <h6 class="text-uppercase text-muted mb-2">Dietary & Visibility</h6>
                    <div class="row g-3">
                        <div class="col-md-8">
                            <div class="d-flex flex-wrap gap-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_vegetarian" id="isVegetarian"
                                           <?= !empty($item['is_vegetarian']) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="isVegetarian">Vegetarian</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_vegan" id="isVegan"
                                           <?= !empty($item['is_vegan']) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="isVegan">Vegan</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_gluten_free" id="isGlutenFree"
                                           <?= !empty($item['is_gluten_free']) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="isGlutenFree">Gluten Free</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_available" id="isAvailable"
                                       <?= !isset($item['is_available']) || $item['is_available'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="isAvailable">
                                    Item is available and visible
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4 d-flex justify-content-between">
                <a href="<?= url('/admin/restaurants/' . $restaurant['id'] . '/menu/items') ?>" class="btn btn-outline-secondary">
                    Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <?= $isEdit ? 'Save Changes' : 'Create Item' ?>
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
