<?php
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">Menu Items</h2>
        <p class="text-muted mb-0">Manage dishes and prices for <?= htmlspecialchars($restaurant['name']) ?>.</p>
    </div>
    <a href="<?= url('/admin/restaurants/' . $restaurant['id'] . '/menu/items/create') ?>" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Add Item
    </a>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($items)): ?>
            <p class="text-muted mb-0">No menu items yet. Create a category and then add your first dish.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>SKU</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Prep</th>
                            <th>Dietary</th>
                            <th>Order</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($item['name']) ?></strong><br>
                                    <span class="tt-muted small"><?= htmlspecialchars($item['description'] ?? '') ?></span>
                                </td>
                                <td><code><?= htmlspecialchars($item['sku'] ?? '-') ?></code></td>
                                <td><?= htmlspecialchars($item['category_name'] ?? '-') ?></td>
                                <td><?= currency($item['price']) ?></td>
                                <td><?= (int)($item['prep_time_minutes'] ?? 0) ?>m</td>
                                <td>
                                    <?php if (!empty($item['is_vegetarian'])): ?><span class="badge bg-success-subtle text-success border border-success-subtle">Veg</span><?php endif; ?>
                                    <?php if (!empty($item['is_vegan'])): ?><span class="badge bg-primary-subtle text-primary border border-primary-subtle">Vegan</span><?php endif; ?>
                                    <?php if (!empty($item['is_gluten_free'])): ?><span class="badge bg-info-subtle text-info border border-info-subtle">GF</span><?php endif; ?>
                                </td>
                                <td><?= (int)$item['display_order'] ?></td>
                                <td>
                                    <?php if ($item['is_available']): ?>
                                        <span class="badge bg-success">Available</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Hidden</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <a href="<?= url('/admin/restaurants/' . $restaurant['id'] . '/menu/items/' . $item['id'] . '/edit') ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil-square"></i> Edit
                                    </a>
                                    <form method="POST" action="<?= url('/admin/restaurants/' . $restaurant['id'] . '/menu/items/' . $item['id'] . '/delete') ?>" class="d-inline" onsubmit="return confirm('Delete this menu item?');">
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
$title = 'Menu Items - TableTap Admin';
require APP_PATH . '/Views/layouts/admin.php';
?>
