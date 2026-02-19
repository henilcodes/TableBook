<?php
ob_start();
?>

<div class="admin-page-head">
    <h2 class="mb-1">Reservations</h2>
    <p class="text-muted mb-0">Filter and manage reservations by restaurant, date, and status.</p>
</div>

<div class="card mb-3 admin-filter-card admin-form-card">
    <div class="card-body">
        <form method="GET" action="<?= url('/admin/reservations') ?>" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Restaurant</label>
                <select name="restaurant_id" class="form-select" onchange="this.form.submit()">
                    <?php foreach ($restaurants as $r): ?>
                        <option value="<?= (int)$r['id'] ?>" <?= (int)$restaurant['id'] === (int)$r['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($r['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Select Date</label>
                <input type="date" name="date" class="form-control" value="<?= htmlspecialchars($selectedDate) ?>" onchange="this.form.submit()">
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <?php
                    $statusOptions = [
                        'all' => 'All',
                        'pending' => 'Pending',
                        'confirmed' => 'Confirmed',
                        'seated' => 'Seated',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                        'no_show' => 'No Show'
                    ];
                    foreach ($statusOptions as $value => $label):
                    ?>
                        <option value="<?= $value ?>" <?= ($selectedStatus ?? 'all') === $value ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" value="<?= htmlspecialchars($searchTerm ?? '') ?>" placeholder="Code, customer, phone, table">
            </div>
            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-funnel"></i> Apply Filters
                </button>
                <a href="<?= url('/admin/reservations?restaurant_id=' . (int)$restaurant['id'] . '&date=' . urlencode(date('Y-m-d'))) ?>" class="btn btn-outline-secondary">
                    Reset
                </a>
                <a href="<?= url('/admin/export/reservations?restaurant_id=' . (int)$restaurant['id'] . '&date=' . urlencode($selectedDate) . '&status=' . urlencode($selectedStatus ?? 'all') . '&search=' . urlencode($searchTerm ?? '')) ?>" class="btn btn-success ms-auto">
                    <i class="bi bi-download"></i> Export CSV
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($reservations)): ?>
            <p class="text-muted">No reservations found for this date.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Date & Time</th>
                            <th>Table</th>
                            <th>Party Size</th>
                            <th>Customer</th>
                            <th>Phone</th>
                            <th>Order</th>
                            <th>Source</th>
                            <th>Notes</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reservations as $reservation): ?>
                            <tr>
                                <td><code><?= htmlspecialchars($reservation['reservation_code']) ?></code></td>
                                <td>
                                    <?= date('M j, Y', strtotime($reservation['reservation_date'])) ?><br>
                                    <small class="text-muted"><?= date('g:i A', strtotime($reservation['reservation_time'])) ?></small>
                                </td>
                                <td><?= htmlspecialchars($reservation['table_number']) ?></td>
                                <td><?= $reservation['party_size'] ?></td>
                                <td><?= htmlspecialchars($reservation['customer_name'] ?? 'Guest') ?></td>
                                <td><?= htmlspecialchars($reservation['customer_phone'] ?? '-') ?></td>
                                <td>
                                    <?php if ((int)($reservation['preorder_qty'] ?? 0) > 0): ?>
                                        <span class="badge bg-info-subtle text-info border border-info-subtle">
                                            <?= (int)$reservation['preorder_qty'] ?> items
                                        </span>
                                        <div class="small text-muted"><?= currency($reservation['preorder_total'] ?? 0) ?></div>
                                    <?php else: ?>
                                        <span class="text-muted">No pre-order</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border text-uppercase">
                                        <?= htmlspecialchars(str_replace('_', ' ', (string)($reservation['reservation_source'] ?? 'web'))) ?>
                                    </span>
                                </td>
                                <td class="small tt-muted" style="max-width: 200px;">
                                    <?= !empty($reservation['notes']) ? htmlspecialchars($reservation['notes']) : '-' ?>
                                </td>
                                <td>
                                    <select class="form-select form-select-sm status-select" data-reservation-id="<?= $reservation['id'] ?>">
                                        <option value="pending" <?= $reservation['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="confirmed" <?= $reservation['status'] === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                        <option value="seated" <?= $reservation['status'] === 'seated' ? 'selected' : '' ?>>Seated</option>
                                        <option value="completed" <?= $reservation['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                                        <option value="cancelled" <?= $reservation['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                        <option value="no_show" <?= $reservation['status'] === 'no_show' ? 'selected' : '' ?>>No Show</option>
                                    </select>
                                </td>
                                <td>
                                    <a href="<?= url('/reservation/' . htmlspecialchars($reservation['reservation_code'])) ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.querySelectorAll('.status-select').forEach(select => {
    select.addEventListener('change', function() {
        const reservationId = this.dataset.reservationId;
        const status = this.value;
        
        fetch(`<?= url('/admin/reservations') ?>/${reservationId}/status?restaurant_id=<?= (int)$restaurant['id'] ?>`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `status=${status}&_token=<?= $csrf_token ?>`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Status updated successfully');
            } else {
                alert(data.error || 'Failed to update status');
                location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred');
            location.reload();
        });
    });
});
</script>

<?php
$content = ob_get_clean();
$title = 'Reservations - TableTap Admin';
require APP_PATH . '/Views/layouts/admin.php';
?>
