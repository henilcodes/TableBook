<?php
ob_start();

$isEdit = ($mode ?? '') === 'edit';
$pageTitle = $isEdit ? 'Edit Restaurant' : 'Add Restaurant';
?>

<div class="admin-page-head">
    <h2 class="mb-1"><?= $pageTitle ?></h2>
    <p class="text-muted mb-0">
        <?= $isEdit ? 'Update restaurant details.' : 'Create a new restaurant that will appear on the public site.' ?>
    </p>
</div>

<div class="card admin-form-card">
    <div class="card-body">
        <form method="POST" action="<?= $isEdit ? url('/admin/restaurants/' . $restaurant['id'] . '/update') : url('/admin/restaurants') ?>">
            <input type="hidden" name="_token" value="<?= $csrf_token ?>">

            <div class="row g-4">
                <div class="col-12">
                    <h6 class="text-uppercase text-muted mb-2">Identity</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Name *</label>
                            <input type="text" name="name" class="form-control" maxlength="100" required
                                   value="<?= htmlspecialchars($restaurant['name'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Cuisine Type</label>
                            <input type="text" name="cuisine_type" class="form-control" maxlength="50"
                                   placeholder="e.g. Italian, Sushi, Indian"
                                   value="<?= htmlspecialchars($restaurant['cuisine_type'] ?? '') ?>">
                        </div>
                    </div>
                </div>

                <?php if ($isEdit): ?>
                    <div class="col-12">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Slug</label>
                                <input type="text" name="slug" class="form-control"
                                       value="<?= htmlspecialchars($restaurant['slug'] ?? '') ?>">
                                <small class="text-muted">Used in the URL. Leave blank to regenerate from the name.</small>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="col-12">
                    <h6 class="text-uppercase text-muted mb-2">Contact & Media</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control" rows="2"><?= htmlspecialchars($restaurant['address'] ?? '') ?></textarea>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" maxlength="20"
                                   value="<?= htmlspecialchars($restaurant['phone'] ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" maxlength="100"
                                   value="<?= htmlspecialchars($restaurant['email'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Image URL</label>
                            <input type="url" name="image_url" class="form-control" placeholder="https://example.com/restaurant.jpg"
                                   value="<?= htmlspecialchars($restaurant['image_url'] ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Rating</label>
                            <input type="number" name="rating" class="form-control" min="0" max="5" step="0.1"
                                   value="<?= htmlspecialchars($restaurant['rating'] ?? '0.0') ?>">
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <h6 class="text-uppercase text-muted mb-2">Description</h6>
                    <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($restaurant['description'] ?? '') ?></textarea>
                </div>
            </div>

            <div class="mt-4 d-flex justify-content-between">
                <a href="<?= url('/admin/restaurants') ?>" class="btn btn-outline-secondary">
                    Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <?= $isEdit ? 'Save Changes' : 'Create Restaurant' ?>
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
