<?php
require_once __DIR__ . '/../../includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $frequency = $_POST['frequency'];
    $time = $_POST['time'];

    $stmt = $pdo->prepare("DELETE FROM backup_settings"); // Only one row needed
    $stmt->execute();

    $stmt = $pdo->prepare("INSERT INTO backup_settings (email, frequency, time) VALUES (?, ?, ?)");
    $stmt->execute([$email, $frequency, $time]);

    echo "<div class='alert alert-success'>Backup settings saved successfully.</div>";
}

// Fetch current settings
$stmt = $pdo->query("SELECT * FROM backup_settings LIMIT 1");
$settings = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">
    <h4>Backup Settings</h4>
    <form method="POST">
        <div class="mb-3">
            <label>Email to receive backups</label>
            <input type="email" name="email" class="form-control" required value="<?= $settings['email'] ?? '' ?>">
        </div>
        <div class="mb-3">
            <label>Frequency</label>
            <select name="frequency" class="form-control" required>
                <option value="daily" <?= ($settings['frequency'] ?? '') === 'daily' ? 'selected' : '' ?>>Daily</option>
                <option value="weekly" <?= ($settings['frequency'] ?? '') === 'weekly' ? 'selected' : '' ?>>Weekly</option>
            </select>
        </div>
        <div class="mb-3">
            <label>Time (24h format)</label>
            <input type="time" name="time" class="form-control" required value="<?= $settings['time'] ?? '' ?>">
        </div>
        <button class="btn btn-primary">Save Settings</button>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
