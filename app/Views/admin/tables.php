<?php
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">Tables</h2>
        <p class="text-muted mb-0">Manage seating layout for <?= htmlspecialchars($restaurant['name']) ?>.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('/admin/restaurants/' . $restaurant['id'] . '/tables/export') ?>" class="btn btn-outline-success">
            <i class="bi bi-file-earmark-spreadsheet"></i> Export CSV
        </a>
        <a href="<?= url('/admin/restaurants/' . $restaurant['id'] . '/tables/create') ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add Table
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($tables)): ?>
            <p class="text-muted mb-0">No tables yet. Click “Add Table” to create your seating layout.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Table</th>
                            <th>Section</th>
                            <th>Capacity</th>
                            <th>Party Range</th>
                            <th>Preference</th>
                            <th>Order</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tables as $table): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($table['table_number']) ?></strong></td>
                                <td><?= htmlspecialchars($table['section_name'] ?? '-') ?></td>
                                <td><?= (int)$table['capacity'] ?></td>
                                <td>
                                    <?= (int)($table['min_party_size'] ?? 1) ?> -
                                    <?= (int)($table['max_party_size'] ?? $table['capacity']) ?>
                                </td>
                                <td><?= htmlspecialchars(ucfirst($table['seating_preference'])) ?></td>
                                <td><?= (int)($table['sort_order'] ?? 0) ?></td>
                                <td>
                                    <?php if ($table['is_active']): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <a href="<?= url('/admin/restaurants/' . $restaurant['id'] . '/tables/' . $table['id'] . '/edit') ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil-square"></i> Edit
                                    </a>
                                    <form method="POST" action="<?= url('/admin/restaurants/' . $restaurant['id'] . '/tables/' . $table['id'] . '/delete') ?>" class="d-inline" onsubmit="return confirm('Delete this table? Existing reservations for this table will also be removed.');">
                                        <input type="hidden" name="_token" value="<?= $csrf_token ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = 'Tables - TableTap Admin';
require APP_PATH . '/Views/layouts/admin.php';
?>
