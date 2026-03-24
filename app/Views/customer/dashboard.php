<?php
ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-1">Welcome back, <?= htmlspecialchars($customer['name']) ?>!</h2>
        <p class="text-muted">Manage your reservations and profile from here.</p>
    </div>
    <div class="d-none d-md-block">
        <a href="<?= url('/restaurants') ?>" class="btn btn-primary rounded-pill px-4">
            <i class="bi bi-plus-lg me-2"></i> Book a Table
        </a>
    </div>
</div>

<div class="row g-4 mb-5">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 text-center p-3 h-100">
            <div class="card-body">
                <div class="bg-primary bg-opacity-10 p-3 rounded-circle d-inline-block mb-3">
                    <i class="bi bi-calendar-check text-primary fs-3"></i>
                </div>
                <h3 class="fw-bold mb-1"><?= count($upcomingReservations) ?></h3>
                <p class="text-muted mb-0 small text-uppercase fw-bold letter-spacing-1">Upcoming</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 text-center p-3 h-100">
            <div class="card-body">
                <div class="bg-success bg-opacity-10 p-3 rounded-circle d-inline-block mb-3">
                    <i class="bi bi-clock-history text-success fs-3"></i>
                </div>
                <h3 class="fw-bold mb-1"><?= count($recentHistory) ?></h3>
                <p class="text-muted mb-0 small text-uppercase fw-bold letter-spacing-1">Past Visits</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-4 text-center p-3 h-100">
            <div class="card-body d-flex flex-column justify-content-center">
                <p class="text-muted mb-3">Quick Actions</p>
                <div class="d-grid gap-2">
                    <a href="<?= url('/account/reservations') ?>" class="btn btn-outline-primary btn-sm rounded-pill">Manage Bookings</a>
                    <a href="<?= url('/account/history') ?>" class="btn btn-outline-secondary btn-sm rounded-pill">View History</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Upcoming Reservations -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-transparent border-0 px-4 pt-4 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0">Upcoming Reservations</h5>
                <a href="<?= url('/account/reservations') ?>" class="btn btn-link btn-sm text-decoration-none p-0">View All</a>
            </div>
            <div class="card-body p-4">
                <?php if (empty($upcomingReservations)): ?>
                    <div class="text-center py-5">
                        <img src="<?= url('/public/assets/img/restaurant-placeholder.svg') ?>" alt="No reservations" class="mb-3 opacity-25" style="width: 80px;">
                        <p class="text-muted">You have no upcoming reservations.</p>
                        <a href="<?= url('/restaurants') ?>" class="btn btn-primary rounded-pill px-4">Book Now</a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="border-0 rounded-start-pill">Date & Time</th>
                                    <th class="border-0">Restaurant</th>
                                    <th class="border-0">Table</th>
                                    <th class="border-0">Status</th>
                                    <th class="border-0 rounded-end-pill"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($upcomingReservations as $reservation): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold"><?= date('M j, Y', strtotime($reservation['reservation_date'])) ?></div>
                                            <div class="small text-muted"><?= date('g:i A', strtotime($reservation['reservation_time'])) ?></div>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark"><?= htmlspecialchars($reservation['restaurant_name']) ?></div>
                                            <div class="small text-muted"><?= $reservation['party_size'] ?> Guests</div>
                                        </td>
                                        <td><span class="badge bg-light text-dark fw-normal border"><?= htmlspecialchars($reservation['table_number'] ?? 'TBD') ?></span></td>
                                        <td>
                                            <?php
                                            $statusClass = match($reservation['status']) {
                                                'confirmed' => 'success',
                                                'pending' => 'warning',
                                                'cancelled' => 'danger',
                                                default => 'secondary'
                                            };
                                            ?>
                                            <span class="badge rounded-pill bg-<?= $statusClass ?> bg-opacity-10 text-<?= $statusClass ?> px-3 py-2">
                                                <?= ucfirst($reservation['status']) ?>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <a href="<?= url('/reservation/' . htmlspecialchars($reservation['reservation_code'])) ?>" class="btn btn-light btn-sm rounded-pill px-3 border shadow-sm">
                                                Details
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
    </div>
    
    <!-- Profile & Security -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-transparent border-0 px-4 pt-4">
                <h5 class="fw-bold mb-0">My Profile</h5>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="<?= url('/account/profile/update') ?>">
                    <input type="hidden" name="_token" value="<?= $csrf_token ?>">
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-uppercase text-muted">Full Name</label>
                        <input type="text" name="name" class="form-control rounded-3" value="<?= htmlspecialchars($customer['name']) ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-uppercase text-muted">Email Address</label>
                        <input type="email" class="form-control rounded-3 bg-light" value="<?= htmlspecialchars($customer['email']) ?>" disabled>
                        <div class="form-text small">Email cannot be changed</div>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-uppercase text-muted">Phone Number</label>
                        <input type="tel" name="phone" class="form-control rounded-3" value="<?= htmlspecialchars($customer['phone'] ?? '') ?>">
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 rounded-pill py-2">Update Profile</button>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="card-header bg-transparent border-0 px-4 pt-4">
                <h5 class="fw-bold mb-0">Security</h5>
            </div>
            <div class="card-body p-4">
                <button class="btn btn-outline-secondary btn-sm w-100 rounded-pill mb-3" type="button" data-bs-toggle="collapse" data-bs-target="#passwordCollapse">
                    Change Password <i class="bi bi-chevron-down ms-2"></i>
                </button>
                
                <div class="collapse" id="passwordCollapse">
                    <form method="POST" action="<?= url('/account/password/update') ?>">
                        <input type="hidden" name="_token" value="<?= $csrf_token ?>">
                        
                        <div class="mb-2">
                            <input type="password" name="current_password" class="form-control form-control-sm rounded-3" placeholder="Current Password" required>
                        </div>
                        <div class="mb-2">
                            <input type="password" name="new_password" class="form-control form-control-sm rounded-3" placeholder="New Password" required>
                        </div>
                        <div class="mb-3">
                            <input type="password" name="confirm_password" class="form-control form-control-sm rounded-3" placeholder="Confirm Password" required>
                        </div>
                        <button type="submit" class="btn btn-dark btn-sm w-100 rounded-pill py-2">Update Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = 'My Account - TableTap';
require APP_PATH . '/Views/layouts/customer.php';
?>
