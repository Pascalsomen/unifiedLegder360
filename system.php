<?php require_once __DIR__ . '/includes/header.php';


if (!hasRole('admin')) {
    redirect($base);
}
// Fetch existing settings
$stmt = $pdo->query("SELECT * FROM system_settings LIMIT 1");
$settings = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<div class="container mt-5">
    <h3>System Settings</h3>
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">Settings saved successfully.</div>
    <?php endif; ?>

    <form action="save_settings.php" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>System Name (Short)</label>
            <input type="text" name="system_name_short" class="form-control" required value="<?= htmlspecialchars($settings['system_name_short'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label>System Name (Full)</label>
            <input type="text" name="system_name_full" class="form-control" required value="<?= htmlspecialchars($settings['system_name_full'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label>System Logo</label>
            <input type="file" name="logo" class="form-control">
            <?php if (!empty($settings['logo'])): ?>
                <img src="<?= htmlspecialchars($settings['logo']) ?>" alt="Current Logo" style="height: 80px; margin-top:10px;">
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label>Contact Email</label>
            <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($settings['email'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label>Phone Number</label>
            <input type="text" name="phone_number" class="form-control" value="<?= htmlspecialchars($settings['phone_number'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label>Address</label>
            <textarea name="address" class="form-control"><?= htmlspecialchars($settings['address'] ?? '') ?></textarea>
        </div>

        <h5 class="mt-4">Backup Settings</h5>

        <div class="form-group">
            <label>Email to Send Backup</label>
            <input type="email" name="backup_email" class="form-control" value="<?= htmlspecialchars($settings['backup_email'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label>SMTP Host</label>
            <input type="text" name="smtp_host" class="form-control" value="<?= htmlspecialchars($settings['smtp_host'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label>SMTP Username</label>
            <input type="text" name="smtp_username" class="form-control" value="<?= htmlspecialchars($settings['smtp_username'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label>SMTP Password</label>
            <input type="password" name="smtp_password" class="form-control" value="<?= htmlspecialchars($settings['smtp_password'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label>Time to Send Backup</label>
            <input type="time" name="backup_time" class="form-control" value="<?= htmlspecialchars($settings['backup_time'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label>Backup Frequency</label>
            <select name="backup_frequency" class="form-control">
                <option value="">-- Select Frequency --</option>
                <option value="daily" <?= ($settings['backup_frequency'] ?? '') === 'daily' ? 'selected' : '' ?>>Daily</option>
                <option value="weekly" <?= ($settings['backup_frequency'] ?? '') === 'weekly' ? 'selected' : '' ?>>Weekly</option>
                <option value="monthly" <?= ($settings['backup_frequency'] ?? '') === 'monthly' ? 'selected' : '' ?>>Monthly</option>
            </select>
        </div>

        <div class="form-group">
            <label>Timezone</label>
            <input type="text" name="timezone" class="form-control" placeholder="e.g. Africa/Kigali" value="<?= htmlspecialchars($settings['timezone'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label>Date Format</label>
            <input type="text" name="date_format" class="form-control" placeholder="e.g. Y-m-d or d/m/Y" value="<?= htmlspecialchars($settings['date_format'] ?? '') ?>">
        </div>

        <button type="submit" class="btn btn-primary mt-3">Save Settings</button>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
