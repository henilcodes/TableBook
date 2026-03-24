<?php
ob_start();
?>

<style>
.dash-hero {
    border: 0;
    border-radius: 16px;
    color: #fff;
    background: linear-gradient(135deg, var(--tt-primary) 0%, var(--tt-accent) 52%, var(--tt-secondary) 100%);
    box-shadow: var(--tt-shadow-lg);
}
.dash-hero .meta { color: rgba(255,255,255,.85); }
.kpi-card {
    border: 1px solid #edf1f7;
    border-radius: 14px;
    box-shadow: 0 8px 24px rgba(16,24,40,.06);
}
.kpi-card .value { font-size: 1.65rem; font-weight: 800; line-height: 1; }
.module-card {
    border: 1px solid #e8edf5;
    border-radius: 14px;
    box-shadow: 0 10px 24px rgba(16,24,40,.05);
}
.module-card .icon-pill {
    width: 38px;
    height: 38px;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #eef6fb;
    color: #205072;
}
.dash-toolbar .btn { min-width: 108px; }
.status-pill {
    display: inline-flex;
    align-items: center;
    border-radius: 999px;
    padding: .2rem .6rem;
    font-size: .76rem;
    font-weight: 700;
    border: 1px solid transparent;
    text-transform: capitalize;
}
.status-pill.pending { background: #fff8e6; color: #8a5a00; border-color: #ffe3a3; }
.status-pill.confirmed { background: #e8f6ff; color: #0f5f8a; border-color: #b8e3fa; }
.status-pill.seated { background: #e8fff3; color: #127345; border-color: #b5efcf; }
.status-pill.completed { background: #f0f4f8; color: #2a455e; border-color: #d3dee9; }
.status-pill.cancelled { background: #ffecef; color: #9d2132; border-color: #ffc5cf; }
.status-pill.no_show { background: #f4f1ff; color: #5c3b9d; border-color: #ddd1ff; }
.today-table td {
    vertical-align: middle;
}
.today-table .meta {
    font-size: .82rem;
    color: #667085;
}
</style>

<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4">
    <div>
        <h2 class="mb-1">Admin Dashboard</h2>
        <p class="text-muted mb-0">Track reservations and manage restaurant operations in one place.</p>
    </div>
</div>

<?php if (!$restaurant): ?>
    <div class="alert alert-warning">
        No restaurant configured. Please set up a restaurant first.
    </div>
<?php else: ?>
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body">
            <form method="GET" action="<?= url('/admin') ?>" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label">Restaurant</label>
                    <select name="restaurant_id" class="form-select" onchange="this.form.submit()">
                        <?php foreach (($restaurants ?? []) as $r): ?>
                            <option value="<?= (int)$r['id'] ?>" <?= (int)$restaurant['id'] === (int)$r['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($r['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-7 text-md-end dash-toolbar">
                    <a href="<?= url('/admin/restaurants/' . $restaurant['id'] . '/hours') ?>" class="btn btn-outline-primary me-2 mb-2 mb-md-0">
                        <i class="bi bi-clock-history"></i> Hours
                    </a>
                    <a href="<?= url('/admin/restaurants/' . $restaurant['id'] . '/tables') ?>" class="btn btn-outline-primary me-2 mb-2 mb-md-0">
                        <i class="bi bi-grid-3x3-gap"></i> Tables
                    </a>
                    <a href="<?= url('/admin/restaurants/' . $restaurant['id'] . '/menu/items') ?>" class="btn btn-outline-primary me-2 mb-2 mb-md-0">
                        <i class="bi bi-menu-button-wide"></i> Menu
                    </a>
                    <a href="<?= url('/admin/reservations?restaurant_id=' . $restaurant['id'] . '&date=' . urlencode(date('Y-m-d'))) ?>" class="btn btn-primary mb-2 mb-md-0 me-2">
                        <i class="bi bi-calendar-event"></i> Reservations
                    </a>
                    <a href="<?= url('/admin/dashboard/export?restaurant_id=' . $restaurant['id']) ?>" class="btn btn-outline-success mb-2 mb-md-0">
                        <i class="bi bi-file-earmark-spreadsheet"></i> Export Stats
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card dash-hero mb-4">
        <div class="card-body p-4 p-lg-5">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div>
                    <h4 class="mb-1"><?= htmlspecialchars($restaurant['name']) ?></h4>
                    <p class="mb-2 meta">
                        <?= htmlspecialchars($restaurant['cuisine_type'] ?? 'Cuisine not set') ?> ·
                        <?= htmlspecialchars($restaurant['address'] ?? 'No address yet') ?>
                    </p>
                    <div class="small meta">
                        <i class="bi bi-telephone me-1"></i><?= htmlspecialchars($restaurant['phone'] ?? 'Phone not set') ?>
                    </div>
                </div>
                <div class="text-end">
                    <span class="badge text-bg-light border mb-2">
                        <i class="bi bi-star-fill text-warning me-1"></i>
                        <?= number_format((float)($restaurant['rating'] ?? 0), 1) ?>
                    </span>
                    <div>
                        <a href="<?= url('/admin/restaurants/' . $restaurant['id'] . '/edit') ?>" class="btn btn-sm btn-light">
                            <i class="bi bi-shop me-1"></i>Manage Restaurant
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="card kpi-card h-100">
                <div class="card-body">
                    <div class="text-muted small mb-2">Total Reservations</div>
                    <div class="value text-primary"><?= $stats['total'] ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card kpi-card h-100">
                <div class="card-body">
                    <div class="text-muted small mb-2">Today's Reservations</div>
                    <div class="value text-success"><?= $stats['today'] ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card kpi-card h-100">
                <div class="card-body">
                    <div class="text-muted small mb-2">Confirmed</div>
                    <div class="value text-info"><?= $stats['confirmed'] ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card kpi-card h-100">
                <div class="card-body">
                    <div class="text-muted small mb-2">Pending</div>
                    <div class="value text-warning"><?= $stats['pending'] ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6 col-xl-4">
            <div class="card h-100 module-card">
                <div class="card-body">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h6 class="text-muted mb-0">Tables</h6>
                            <span class="icon-pill"><i class="bi bi-grid-3x3-gap"></i></span>
                        </div>
                        <h4 class="mb-1"><?= (int)($moduleStats['tables'] ?? 0) ?></h4>
                        <small class="text-muted"><?= (int)($moduleStats['active_tables'] ?? 0) ?> active</small>
                        <div class="mt-3">
                            <a href="<?= url('/admin/restaurants/' . $restaurant['id'] . '/tables') ?>" class="btn btn-sm btn-outline-primary">Manage</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-4">
            <div class="card h-100 module-card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h6 class="text-muted mb-0">Menu Categories</h6>
                        <span class="icon-pill"><i class="bi bi-diagram-3"></i></span>
                    </div>
                    <h4 class="mb-1"><?= (int)($moduleStats['menu_categories'] ?? 0) ?></h4>
                    <div class="mt-3">
                        <a href="<?= url('/admin/restaurants/' . $restaurant['id'] . '/menu/categories') ?>" class="btn btn-sm btn-outline-primary">Manage</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-4">
            <div class="card h-100 module-card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h6 class="text-muted mb-0">Menu Items</h6>
                        <span class="icon-pill"><i class="bi bi-menu-button-wide"></i></span>
                    </div>
                    <h4 class="mb-1"><?= (int)($moduleStats['menu_items'] ?? 0) ?></h4>
                    <small class="text-muted"><?= (int)($moduleStats['available_items'] ?? 0) ?> available</small>
                    <div class="mt-3">
                        <a href="<?= url('/admin/restaurants/' . $restaurant['id'] . '/menu/items') ?>" class="btn btn-sm btn-outline-primary">Manage</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-4">
            <div class="card h-100 module-card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h6 class="text-muted mb-0">Hours</h6>
                        <span class="icon-pill"><i class="bi bi-clock-history"></i></span>
                    </div>
                    <h4 class="mb-1"><?= (int)($moduleStats['hours_days'] ?? 0) ?></h4>
                    <small class="text-muted"><?= (int)($moduleStats['hours_open_days'] ?? 0) ?> open days</small>
                    <div class="mt-3">
                        <a href="<?= url('/admin/restaurants/' . $restaurant['id'] . '/hours') ?>" class="btn btn-sm btn-outline-primary">Manage</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-4">
            <div class="card h-100 module-card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h6 class="text-muted mb-0">Service Progress</h6>
                        <span class="icon-pill"><i class="bi bi-graph-up-arrow"></i></span>
                    </div>
                    <h4 class="mb-1"><?= (int)($stats['seated'] ?? 0) ?></h4>
                    <small class="text-muted"><?= (int)($stats['completed'] ?? 0) ?> completed</small>
                    <div class="mt-3">
                        <a href="<?= url('/admin/reservations?restaurant_id=' . $restaurant['id'] . '&date=' . urlencode(date('Y-m-d'))) ?>" class="btn btn-sm btn-outline-primary">Track</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-4">
            <div class="card h-100 module-card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h6 class="text-muted mb-0">Quick Links</h6>
                        <span class="icon-pill"><i class="bi bi-lightning-charge"></i></span>
                    </div>
                    <div class="d-flex flex-column gap-2 mt-2">
                        <a class="btn btn-sm btn-outline-primary text-start" href="<?= url('/admin/restaurants/' . $restaurant['id'] . '/edit') ?>">
                            <i class="bi bi-shop-window me-1"></i> Restaurant Profile
                        </a>
                        <a class="btn btn-sm btn-outline-primary text-start" href="<?= url('/admin/reservations?restaurant_id=' . $restaurant['id'] . '&date=' . urlencode(date('Y-m-d'))) ?>">
                            <i class="bi bi-calendar-week me-1"></i> Today Schedule
                        </a>
                        <a class="btn btn-sm btn-outline-success text-start" href="<?= url('/admin/reservations/export?restaurant_id=' . $restaurant['id'] . '&date=' . urlencode(date('Y-m-d'))) ?>">
                            <i class="bi bi-download me-1"></i> Export Today CSV
                        </a>
                        <a class="btn btn-sm btn-outline-success text-start" href="<?= url('/admin/dashboard/export?restaurant_id=' . $restaurant['id']) ?>">
                            <i class="bi bi-graph-up me-1"></i> Export Full Stats
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Today's Reservations</h5>
            <a href="<?= url('/admin/reservations?restaurant_id=' . $restaurant['id'] . '&date=' . urlencode(date('Y-m-d'))) ?>" class="btn btn-sm btn-outline-primary">View All</a>
        </div>
        <div class="card-body">
            <?php if (empty($todayReservations)): ?>
                <p class="text-muted">No reservations for today.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle today-table">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Time</th>
                                <th>Guest</th>
                                <th>Table & Party</th>
                                <th>Pre-Order</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($todayReservations as $reservation): ?>
                                <tr>
                                    <td>
                                        <code><?= htmlspecialchars($reservation['reservation_code']) ?></code>
                                    </td>
                                    <td>
                                        <div class="fw-semibold"><?= date('g:i A', strtotime($reservation['reservation_time'])) ?></div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold"><?= htmlspecialchars($reservation['customer_name'] ?? 'Guest') ?></div>
                                        <div class="meta"><?= htmlspecialchars($reservation['customer_phone'] ?? '-') ?></div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">Table <?= htmlspecialchars($reservation['table_number']) ?></div>
                                        <div class="meta"><?= (int)$reservation['party_size'] ?> guests</div>
                                    </td>
                                    <td>
                                        <?php if ((int)($reservation['preorder_qty'] ?? 0) > 0): ?>
                                            <span class="badge bg-info-subtle text-info border border-info-subtle">
                                                <?= (int)$reservation['preorder_qty'] ?> items
                                            </span>
                                            <div class="meta"><?= currency($reservation['preorder_total'] ?? 0) ?></div>
                                        <?php else: ?>
                                            <span class="text-muted small">None</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="mb-2">
                                            <span class="status-pill <?= htmlspecialchars($reservation['status']) ?>" data-status-badge data-reservation-id="<?= (int)$reservation['id'] ?>">
                                                <?= htmlspecialchars(str_replace('_', ' ', $reservation['status'])) ?>
                                            </span>
                                        </div>
                                        <select class="form-select form-select-sm status-select" data-reservation-id="<?= (int)$reservation['id'] ?>" data-prev-status="<?= htmlspecialchars($reservation['status']) ?>">
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
                                            <i class="bi bi-eye me-1"></i> Details
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
<?php endif; ?>

<script>
const dashboardRestaurantId = <?= (int)($restaurant['id'] ?? 0) ?>;
const csrfToken = '<?= $csrf_token ?>';

function showAdminNotice(message, type = 'success') {
    if (window.TableTapUI && typeof window.TableTapUI.showToast === 'function') {
        window.TableTapUI.showToast(message, type === 'error' ? 'error' : 'success');
        return;
    }
    if (type === 'error') {
        alert(message);
    }
}

function setBusyForReservation(reservationId, isBusy) {
    document.querySelectorAll(`[data-reservation-id="${reservationId}"]`).forEach((el) => {
        if (el.classList.contains('status-select')) {
            el.disabled = isBusy;
        }
    });
}

function updateStatusBadge(reservationId, status) {
    const badge = document.querySelector(`[data-status-badge][data-reservation-id="${reservationId}"]`);
    if (!badge) return;
    const statusClassList = ['pending', 'confirmed', 'seated', 'completed', 'cancelled', 'no_show'];
    statusClassList.forEach((c) => badge.classList.remove(c));
    badge.classList.add(status);
    badge.textContent = String(status).replace('_', ' ');
}

function setSelectValue(reservationId, status) {
    const select = document.querySelector(`.status-select[data-reservation-id="${reservationId}"]`);
    if (!select) return;
    select.value = status;
    select.dataset.prevStatus = status;
}

async function updateReservationStatus(reservationId, status, sourceSelect = null) {
    if (!dashboardRestaurantId) return;
    setBusyForReservation(reservationId, true);
    const previous = sourceSelect ? (sourceSelect.dataset.prevStatus || sourceSelect.value) : null;

    try {
        const response = await fetch(`<?= url('/admin/reservations') ?>/${reservationId}/status?restaurant_id=${dashboardRestaurantId}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `status=${encodeURIComponent(status)}&_token=${encodeURIComponent(csrfToken)}`
        });
        const data = await response.json();

        if (!data.success) {
            if (sourceSelect && previous) {
                sourceSelect.value = previous;
            }
            showAdminNotice(data.error || 'Failed to update status.', 'error');
            return;
        }

        setSelectValue(reservationId, status);
        updateStatusBadge(reservationId, status);
        showAdminNotice('Reservation status updated.');
    } catch (error) {
        console.error(error);
        if (sourceSelect && previous) {
            sourceSelect.value = previous;
        }
        showAdminNotice('Unable to update status. Please try again.', 'error');
    } finally {
        setBusyForReservation(reservationId, false);
    }
}

document.querySelectorAll('.status-select').forEach((select) => {
    select.addEventListener('change', function() {
        const reservationId = this.dataset.reservationId;
        updateReservationStatus(reservationId, this.value, this);
    });
});
</script>

<?php
$content = ob_get_clean();
$title = 'Admin Dashboard - TableTap';
require APP_PATH . '/Views/layouts/admin.php';
?>
