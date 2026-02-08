<?php
require_once __DIR__ . '/../../includes/header.php';
require_once '../../classes/BudgetingSystem.php';
$project = new BudgetingSystem($pdo);

$projectId = $_GET['id'] ?? null;
if (!$projectId) {
    echo "<div class='alert alert-danger'>No project selected.</div>";
    require_once __DIR__ . '/../../includes/footer.php';
    exit;
}

$project = $project->getProjectDetails($projectId);
?>

<div class="container mt-5">
    <h3>📊 Project Report: <?= htmlspecialchars($project['name']) ?></h3>
    <p><?= nl2br(htmlspecialchars($project['description'])) ?></p>

    <div class="row g-3 mt-3 mb-4">
        <div class="col-md-3"><strong>Budgeted Amount:</strong><br> RWF<?= number_format($project['budgeted_amount'], 2) ?></div>
        <div class="col-md-3"><strong>Revised Budget:</strong><br> <?= $project['revised_budget'] ? '$' . number_format($project['revised_budget'], 2) : '—' ?></div>
        <div class="col-md-3"><strong>Actual Expense:</strong><br> RWF<?= number_format($project['actual_expense_total'], 2) ?></div>
        <div class="col-md-3"><strong>Solde (Balance):</strong><br> RWF<?= number_format($project['balance'], 2) ?></div>
        <div class="col-md-3"><strong>% Used:</strong><br> <?= $project['percent_used'] ?>%</div>
    </div>

    <h5>Activities Summary</h5>
    <table class="table table-bordered">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Activity</th>
                <th>Budgeted</th>
                <th>Actual Expense</th>
                <th>Difference</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($project['activities'] as $i => $a):
            $diff = $a['budgeted_amount'] - $a['actual_expense'];
        ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><?= htmlspecialchars($a['name']) ?></td>
                <td>$<?= number_format($a['budgeted_amount'], 2) ?></td>
                <td>$<?= number_format($a['actual_expense'], 2) ?></td>
                <td class="<?= $diff < 0 ? 'text-danger' : 'text-success' ?>">
                    <?= $diff >= 0 ? '+' : '-' ?>$<?= number_format(abs($diff), 2) ?>
                </td>
            </tr>
        <?php endforeach ?>
        </tbody>
    </table>

    <a href="projects.php" class="btn btn-secondary mt-3">⬅ Back to Dashboard</a>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
