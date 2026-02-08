<?php
require_once __DIR__ . '/../../includes/header.php';
require_once '../../classes/BudgetingSystem.php';
$project = new BudgetingSystem($pdo);

// Get all projects with aggregate data
$projects = $project->getAllProjectsWithStats();
?>

<div class="container mt-5">
    <h3>📊 All Projects Budget Report</h3>

    <table class="table table-bordered table-striped">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Project Name</th>
                <th>Budgeted</th>
                <th>Revised</th>
                <th>Actual</th>
                <th>Solde</th>
                <th>% Used</th>
                <th>Report</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($projects as $i => $project): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><?= htmlspecialchars($project['name']) ?></td>
                <td>RWF<?= number_format($project['budgeted_amount'], 2) ?></td>
                <td><?= $project['revised_budget'] ? '$' . number_format($project['revised_budget'], 2) : '—' ?></td>
                <td>RWF<?= number_format($project['actual_expense_total'], 2) ?></td>
                <td>RWF<?= number_format($project['balance'], 2) ?></td>
                <td><?= $project['percent_used'] ?>%</td>
                <td>
                    <a href="project_report.php?id=<?= $project['id'] ?>" class="btn btn-sm btn-info">📄 View</a>
                </td>
            </tr>
        <?php endforeach ?>
        </tbody>
    </table>

    <a href="projects.php" class="btn btn-secondary mt-3">⬅ Back to Dashboard</a>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
