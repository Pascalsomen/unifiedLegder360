<?php
require_once __DIR__ . '/../../includes/header.php';

$stmt = $pdo->query("SELECT * FROM backup_logs ORDER BY created_at DESC");
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">
    <h4>Backup Logs</h4>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Filename</th>
                <th>Sent to</th>
                <th>Status</th>
                <th>Created At</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($logs as $i => $log): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><a href="../../backups/<?= $log['filename'] ?>" target="_blank"><?= $log['filename'] ?></a></td>
                    <td><?= htmlspecialchars($log['sent_to']) ?></td>
                    <td><?= $log['status'] ?></td>
                    <td><?= $log['created_at'] ?></td>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
