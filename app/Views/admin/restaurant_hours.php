<?php
ob_start();
?>

<div class="d-flex justify-content-between align-items-center admin-page-head">
    <div>
        <h2 class="mb-1">Restaurant Hours</h2>
        <p class="text-muted mb-0">Manage weekly opening hours for <?= htmlspecialchars($restaurant['name']) ?>.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('/admin/restaurants/' . (int)$restaurant['id'] . '/hours/export') ?>" class="btn btn-outline-success">
            <i class="bi bi-file-earmark-spreadsheet"></i> Export CSV
        </a>
        <a href="<?= url('/admin/restaurants/' . (int)$restaurant['id'] . '/edit') ?>" class="btn btn-outline-primary">
            <i class="bi bi-shop"></i> Restaurant Profile
        </a>
    </div>
</div>

<div class="card admin-form-card">
    <div class="card-body">
        <form method="POST" action="<?= url('/admin/restaurants/' . (int)$restaurant['id'] . '/hours') ?>">
            <input type="hidden" name="_token" value="<?= $csrf_token ?>">

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th style="min-width: 140px;">Day</th>
                            <th style="min-width: 160px;">Open Time</th>
                            <th style="min-width: 160px;">Close Time</th>
                            <th style="min-width: 120px;">Closed</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($hours as $hour): ?>
                            <?php $day = (int)$hour['day_of_week']; ?>
                            <tr>
                                <td class="fw-semibold"><?= htmlspecialchars($hour['day_name']) ?></td>
                                <td>
                                    <input
                                        type="time"
                                        name="open_time[<?= $day ?>]"
                                        class="form-control"
                                        value="<?= htmlspecialchars($hour['open_time']) ?>"
                                        <?= !empty($hour['is_closed']) ? 'disabled' : '' ?>
                                    >
                                </td>
                                <td>
                                    <input
                                        type="time"
                                        name="close_time[<?= $day ?>]"
                                        class="form-control"
                                        value="<?= htmlspecialchars($hour['close_time']) ?>"
                                        <?= !empty($hour['is_closed']) ? 'disabled' : '' ?>
                                    >
                                </td>
                                <td>
                                    <div class="form-check form-switch">
                                        <input
                                            type="checkbox"
                                            class="form-check-input js-closed-toggle"
                                            id="is-closed-<?= $day ?>"
                                            name="is_closed[<?= $day ?>]"
                                            data-day="<?= $day ?>"
                                            <?= !empty($hour['is_closed']) ? 'checked' : '' ?>
                                        >
                                        <label for="is-closed-<?= $day ?>" class="form-check-label">Closed</label>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="d-flex gap-2 mt-3">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Save Hours
                </button>
                <a href="<?= url('/admin/restaurants') ?>" class="btn btn-outline-secondary">Back</a>
            </div>
        </form>
    </div>
</div>

<script>
document.querySelectorAll('.js-closed-toggle').forEach((toggle) => {
    toggle.addEventListener('change', () => {
        const row = toggle.closest('tr');
        if (!row) return;
        row.querySelectorAll('input[type="time"]').forEach((timeInput) => {
            timeInput.disabled = toggle.checked;
        });
    });
});
</script>

<?php
$content = ob_get_clean();
$title = 'Restaurant Hours - TableTap Admin';
require APP_PATH . '/Views/layouts/admin.php';
?>
