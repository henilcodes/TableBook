<?php
ob_start();
?>

<h2 class="mb-4">Welcome back, <?= htmlspecialchars($customer['name']) ?>!</h2>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="text-primary"><?= count($upcomingReservations) ?></h3>
                <p class="text-muted mb-0">Upcoming Reservations</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="text-success"><?= count($recentHistory) ?></h3>
                <p class="text-muted mb-0">Past Reservations</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-center">
            <div class="card-body">
                <a href="<?= url('/restaurants') ?>" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Make New Reservation
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Upcoming Reservations</h5>
            </div>
            <div class="card-body">
                <?php if (empty($upcomingReservations)): ?>
                    <p class="text-muted">You have no upcoming reservations.</p>
                    <a href="<?= url('/restaurants') ?>" class="btn btn-primary">Book a Table</a>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Date & Time</th>
                                    <th>Restaurant</th>
                                    <th>Table</th>
                                    <th>Party Size</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($upcomingReservations as $reservation): ?>
                                    <tr>
                                        <td>
                                            <?= date('M j, Y', strtotime($reservation['reservation_date'])) ?><br>
                                            <small class="text-muted"><?= date('g:i A', strtotime($reservation['reservation_time'])) ?></small>
                                        </td>
                                        <td><?= htmlspecialchars($reservation['restaurant_name']) ?></td>
                                        <td><?= htmlspecialchars($reservation['table_number']) ?></td>
                                        <td><?= $reservation['party_size'] ?></td>
                                        <td>
                                            <span class="badge bg-<?= $reservation['status'] === 'confirmed' ? 'success' : 'warning' ?>">
                                                <?= ucfirst($reservation['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="<?= url('/reservation/' . htmlspecialchars($reservation['reservation_code'])) ?>" class="btn btn-sm btn-outline-primary">View</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <a href="<?= url('/account/reservations') ?>" class="btn btn-outline-primary">View All</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">My Profile</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= url('/account/profile/update') ?>">
                    <input type="hidden" name="_token" value="<?= $csrf_token ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($customer['name']) ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" value="<?= htmlspecialchars($customer['email']) ?>" disabled>
                        <small class="text-muted">Email cannot be changed</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Phone</label>
                        <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($customer['phone'] ?? '') ?>">
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100">Update Profile</button>
                </form>
                
                <hr>
                
                <h6>Change Password</h6>
                <form method="POST" action="<?= url('/account/password/update') ?>">
                    <input type="hidden" name="_token" value="<?= $csrf_token ?>">
                    
                    <div class="mb-2">
                        <input type="password" name="current_password" class="form-control form-control-sm" placeholder="Current Password" required>
                    </div>
                    <div class="mb-2">
                        <input type="password" name="new_password" class="form-control form-control-sm" placeholder="New Password" required>
                    </div>
                    <div class="mb-2">
                        <input type="password" name="confirm_password" class="form-control form-control-sm" placeholder="Confirm Password" required>
                    </div>
                    <button type="submit" class="btn btn-sm btn-outline-primary w-100">Change Password</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = 'My Account - TableTap';
require APP_PATH . '/Views/layouts/customer.php';
?>

