<?php
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">Menu Categories</h2>
        <p class="text-muted mb-0">Group menu items for <?= htmlspecialchars($restaurant['name']) ?>.</p>
    </div>
    <a href="<?= url('/admin/restaurants/' . $restaurant['id'] . '/menu/categories/create') ?>" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Add Category
    </a>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($categories)): ?>
            <p class="text-muted mb-0">No categories yet. Start by creating a category like “Starters” or “Drinks”.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Order</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $category): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($category['name']) ?></strong></td>
                                <td class="tt-muted small"><?= htmlspecialchars($category['description'] ?? '') ?></td>
                                <td>
                                    <?php if (!isset($category['is_active']) || $category['is_active']): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Hidden</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= (int)$category['display_order'] ?></td>
                                <td class="text-end">
                                    <a href="<?= url('/admin/restaurants/' . $restaurant['id'] . '/menu/categories/' . $category['id'] . '/edit') ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil-square"></i> Edit
                                    </a>
                                    <form method="POST" action="<?= url('/admin/restaurants/' . $restaurant['id'] . '/menu/categories/' . $category['id'] . '/delete') ?>" class="d-inline" onsubmit="return confirm('Delete this category? Items will remain but lose this grouping.');">
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
$title = 'Menu Categories - TableTap Admin';
require APP_PATH . '/Views/layouts/admin.php';
?>
