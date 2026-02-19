<?php
ob_start();
?>

<h2 class="mb-4">Upcoming Reservations</h2>

<?php if (empty($reservations)): ?>
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-calendar-x" style="font-size: 3rem; color: #ccc;"></i>
            <p class="text-muted mt-3">You have no upcoming reservations.</p>
            <a href="<?= url('/restaurants') ?>" class="btn btn-primary">Book a Table</a>
        </div>
    </div>
<?php else: ?>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Reservation Code</th>
                            <th>Date & Time</th>
                            <th>Restaurant</th>
                            <th>Table</th>
                            <th>Party Size</th>
                            <th>Status</th>
                            <th>Pre-Order</th>
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
                                <td><?= htmlspecialchars($reservation['restaurant_name']) ?></td>
                                <td><?= htmlspecialchars($reservation['table_number']) ?></td>
                                <td><?= $reservation['party_size'] ?></td>
                                <td>
                                    <span class="badge bg-<?= 
                                        $reservation['status'] === 'confirmed' ? 'success' : 
                                        ($reservation['status'] === 'pending' ? 'warning' : 'secondary')
                                    ?>">
                                        <?= ucfirst($reservation['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($reservation['cart_items'])): ?>
                                        <span class="badge bg-info"><?= count($reservation['cart_items']) ?> items</span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?= url('/reservation/' . htmlspecialchars($reservation['reservation_code'])) ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye"></i> View
                                    </a>
                                    <?php
                                    // Check if cancellation is allowed
                                    $reservationDateTime = strtotime($reservation['reservation_date'] . ' ' . $reservation['reservation_time']);
                                    $cutoffTime = $reservationDateTime - (2 * 3600); // 2 hours before
                                    if (time() < $cutoffTime && in_array($reservation['status'], ['pending', 'confirmed'])):
                                    ?>
                                        <form method="POST" action="<?= url('/account/reservation/' . $reservation['id'] . '/cancel') ?>" class="d-inline" onsubmit="return confirm('Are you sure you want to cancel this reservation?');">
                                            <input type="hidden" name="_token" value="<?= $csrf_token ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-x-circle"></i> Cancel
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php
$content = ob_get_clean();
$title = 'My Reservations - TableTap';
require APP_PATH . '/Views/layouts/customer.php';
?>

