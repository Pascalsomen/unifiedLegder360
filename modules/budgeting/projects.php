<?php
require_once __DIR__ . '/../../includes/header.php';
require_once '../../classes/BudgetingSystem.php';
$project = new BudgetingSystem($pdo);
$projects = $project->getAllProjectSummaries();


?>

<div class="container mt-5">
    <h3>Projects Dashboard</h3>
    <a href="create_project.php" class="btn btn-success mb-3">➕ New Project</a>

    <table class="table table-bordered table-striped">
    <thead class="table-light">
        <tr>
            <th>#</th>
            <th>Project Name</th>
            <th>Budgeted</th>
            <th>Revised</th>
            <th>Used %</th>
            <th>Created</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($projects as $i => $project): ?>
        <tr>
            <td><?= $i + 1 ?></td>
            <td><?= htmlspecialchars($project['name']) ?></td>
            <td>RWF<?= number_format($project['budgeted_amount'], 2) ?></td>
            <td><?= $project['revised_budget'] > 0 ? 'RWF' . number_format($project['revised_budget'], 2) : '—' ?></td>
            <td><?= $project['percent_used'] ?>%</td>
            <td><?= date('Y-m-d', strtotime($project['created_at'])) ?></td>
            <td>
                <a href="view_project.php?id=<?= $project['id'] ?>" class="btn btn-sm btn-info">View</a>
            </td>
        </tr>
    <?php endforeach ?>
    </tbody>
</table>

</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
