<?php
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">Restaurants</h2>
        <p class="text-muted mb-0">Manage the restaurants shown on the public site.</p>
    </div>
    <a href="<?= url('/admin/restaurants/create') ?>" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Add Restaurant
    </a>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($restaurants)): ?>
            <p class="text-muted mb-0">No restaurants yet. Click “Add Restaurant” to create one.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Cuisine</th>
                            <th>Rating</th>
                            <th>Created</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($restaurants as $restaurant): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($restaurant['name']) ?></strong><br>
                                    <span class="tt-muted small"><?= htmlspecialchars($restaurant['address'] ?? '') ?></span>
                                </td>
                                <td><?= htmlspecialchars($restaurant['cuisine_type'] ?? '-') ?></td>
                                <td><?= number_format((float)($restaurant['rating'] ?? 0), 1) ?></td>
                                <td><?= date('M j, Y', strtotime($restaurant['created_at'])) ?></td>
                                <td class="text-end">
                                    <a href="<?= url('/restaurants/' . htmlspecialchars($restaurant['slug'])) ?>" class="btn btn-sm btn-outline-secondary" target="_blank">
                                        <i class="bi bi-box-arrow-up-right"></i>
                                    </a>
                                    <a href="<?= url('/admin/restaurants/' . $restaurant['id'] . '/tables') ?>" class="btn btn-sm btn-outline-dark">
                                        <i class="bi bi-grid-3x3-gap"></i> Tables
                                    </a>
                                    <a href="<?= url('/admin/restaurants/' . $restaurant['id'] . '/hours') ?>" class="btn btn-sm btn-outline-dark">
                                        <i class="bi bi-clock-history"></i> Hours
                                    </a>
                                    <a href="<?= url('/admin/restaurants/' . $restaurant['id'] . '/menu/items') ?>" class="btn btn-sm btn-outline-dark">
                                        <i class="bi bi-menu-button-wide"></i> Menu
                                    </a>
                                    <a href="<?= url('/admin/restaurants/' . $restaurant['id'] . '/edit') ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil-square"></i> Edit
                                    </a>
                                    <form method="POST" action="<?= url('/admin/restaurants/' . $restaurant['id'] . '/delete') ?>" class="d-inline" onsubmit="return confirm('Delete this restaurant? This will also remove its tables, hours, and menu.');">
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
$title = 'Restaurants - TableTap Admin';
require APP_PATH . '/Views/layouts/admin.php';
?>
