<?php
ob_start();

$isEdit = ($mode ?? '') === 'edit';
$pageTitle = $isEdit ? 'Edit Table' : 'Add Table';
?>

<div class="admin-page-head">
    <h2 class="mb-1"><?= $pageTitle ?></h2>
    <p class="text-muted mb-0">
        <?= $isEdit ? 'Update table details and availability.' : 'Create a new table for this restaurant.' ?>
    </p>
</div>

<div class="card admin-form-card">
    <div class="card-body">
        <form method="POST" action="<?= $isEdit ? url('/admin/restaurants/' . $restaurant['id'] . '/tables/' . $table['id'] . '/update') : url('/admin/restaurants/' . $restaurant['id'] . '/tables') ?>">
            <input type="hidden" name="_token" value="<?= $csrf_token ?>">

            <div class="row g-4">
                <div class="col-12">
                    <h6 class="text-uppercase text-muted mb-2">Core Details</h6>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Table Number *</label>
                            <input type="text" name="table_number" class="form-control" maxlength="20" required
                                   value="<?= htmlspecialchars($table['table_number'] ?? '') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Capacity *</label>
                            <input type="number" name="capacity" class="form-control" min="1" required
                                   value="<?= htmlspecialchars($table['capacity'] ?? '2') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Min Party Size *</label>
                            <input type="number" name="min_party_size" class="form-control" min="1" required
                                   value="<?= htmlspecialchars($table['min_party_size'] ?? '1') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Max Party Size</label>
                            <input type="number" name="max_party_size" class="form-control" min="1" placeholder="Optional"
                                   value="<?= htmlspecialchars($table['max_party_size'] ?? '') ?>">
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <h6 class="text-uppercase text-muted mb-2">Placement</h6>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Seating Preference</label>
                            <select name="seating_preference" class="form-select">
                                <?php
                                $currentPref = $table['seating_preference'] ?? 'indoor';
                                $options = ['indoor' => 'Indoor', 'outdoor' => 'Outdoor', 'bar' => 'Bar', 'window' => 'Window'];
                                foreach ($options as $value => $label):
                                ?>
                                    <option value="<?= $value ?>" <?= $currentPref === $value ? 'selected' : '' ?>><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Section</label>
                            <input type="text" name="section_name" class="form-control"
                                   list="sectionOptions"
                                   placeholder="e.g. Main Dining, Patio"
                                   value="<?= htmlspecialchars($table['section_name'] ?? '') ?>">
                            <datalist id="sectionOptions">
                                <?php foreach ($sections as $section): ?>
                                    <option value="<?= htmlspecialchars($section['name']) ?>"></option>
                                <?php endforeach; ?>
                            </datalist>
                            <small class="text-muted">Choose an existing section or type a new name.</small>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Sort Order</label>
                            <input type="number" name="sort_order" class="form-control" min="0"
                                   value="<?= htmlspecialchars($table['sort_order'] ?? '0') ?>">
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <h6 class="text-uppercase text-muted mb-2">Notes & Status</h6>
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Internal Notes</label>
                            <textarea name="notes" class="form-control" rows="2" maxlength="255" placeholder="e.g. Near entrance, suitable for high chairs"><?= htmlspecialchars($table['notes'] ?? '') ?></textarea>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" id="isActive"
                                       <?= !isset($table['is_active']) || $table['is_active'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="isActive">
                                    Table is active and bookable
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4 d-flex justify-content-between">
                <a href="<?= url('/admin/restaurants/' . $restaurant['id'] . '/tables') ?>" class="btn btn-outline-secondary">
                    Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <?= $isEdit ? 'Save Changes' : 'Create Table' ?>
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
