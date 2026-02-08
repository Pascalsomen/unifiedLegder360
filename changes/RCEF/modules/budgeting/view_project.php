<?php
require_once __DIR__ . '/../../includes/header.php';
require_once '../../classes/BudgetingSystem.php';
$project = new BudgetingSystem($pdo);
error_reporting(0);
$projectId = $_GET['id'] ?? null;
if (!$projectId) {
    echo "<div class='alert alert-danger'>Project ID is missing.</div>";
    require_once __DIR__ . '/../../includes/footer.php';
    exit;
}

$project = $project->getProjectDetails($projectId);
?>

<div class="container mt-5">
    <h3>Project: <?= htmlspecialchars($project['name']) ?></h3>
    <p><?= nl2br(htmlspecialchars($project['description'])) ?></p>

    <div class="row mb-4">
        <div class="col-md-4"><strong>Budgeted Amount:</strong> RWF<?= number_format($project['budgeted_amount'], 2) ?></div>
        <div class="col-md-4"><strong>Revised Budget:</strong> <?= $project['revised_budget'] ? 'RWF' . number_format($project['revised_budget'], 2) : '—' ?></div>
        <div class="col-md-4"><strong>Actual Expense:</strong> RWF<?= number_format($project['actual_expense_total'], 2) ?></div>
        <div class="col-md-4"><strong>Solde (Balance):</strong> RWF<?= number_format($project['balance'], 2) ?></div>
        <div class="col-md-4"><strong>% Used:</strong> <?= $project['percent_used'] ?>%</div>
    </div>

    <a href="add_activity.php?project_id=<?= $project['id'] ?>" class="btn btn-success mb-3">➕ Add Activity</a>
    <a href="projects.php" class="btn btn-secondary mb-3">⬅ Back to Projects</a>

    <h5>Project Activities</h5>
    <table class="table table-bordered table-striped">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Activity Name</th>
                <th>Budgeted</th>
                <th>Revised</th>
                <th>Actual Expense</th>
                 <th>Revised Usage Percentange</th>
                 <th>Budgeted Usage Percentange</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($project['activities'] as $i => $activity): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><?= htmlspecialchars($activity['name']) ?></td>
                <td>RWF <?= number_format($activity['budgeted_amount'], 2) ?></td>
                  <td>RWF <?= $activity['revised_amount'] ? number_format($activity['revised_amount'],2) : number_format(0,2)  ?></td>
                <td>RWF <?= number_format($activity['actual_expense'], 2) ?></td>









<!-- Add these new columns for percentages -->
<td>
    <?php
    // Revised Budget Usage Percentage
    $revisedPercentage = 0;
    if ($activity['revised_amount'] > 0) {
        $revisedPercentage = ($activity['actual_expense'] / $activity['revised_amount']) * 100;
    }
    echo number_format($revisedPercentage, 2) . '%';

    // Add color coding based on percentage
    $revisedColor = '';
    if ($revisedPercentage > 100) {
        $revisedColor = 'text-danger'; // Over budget (red)
    } elseif ($revisedPercentage > 80) {
        $revisedColor = 'text-warning'; // Approaching limit (yellow)
    }
    ?>
    <span class="<?= $revisedColor ?>"><?= $revisedPercentage > 0 ? '▲' : '' ?></span>
</td>

<td>
    <?php
    // Original Budget Usage Percentage (if you have original budget)
    $originalPercentage = 0;
    if ($activity['budgeted_amount'] > 0) {
        $originalPercentage = ($activity['actual_expense'] / $activity['budgeted_amount']) * 100;
    }
    echo number_format($originalPercentage, 2) . '%';

    // Color coding for original budget
    $originalColor = '';
    if ($originalPercentage > 100) {
        $originalColor = 'text-danger';
    } elseif ($originalPercentage > 80) {
        $originalColor = 'text-warning';
    }
    ?>
    <span class="<?= $originalColor ?>"><?= $originalPercentage > 0 ? '▲' : '' ?></span>
</td>


                <td>


                <?php if(hasPermission(26)){?>

<a href="edit_activity.php?id=<?= $activity['id'] ?>&project_id=<?= $projectId ?>" class="btn btn-sm btn-primary">Edit</a>
<?php }else{
//Echo "You do not have access to edit project activities";
} ?>

<?php if(hasPermission(27)){?>
<a hidden href="delete_activity.php?id=<?= $activity['id'] ?>&project_id=<?= $projectId ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this activity?');">Delete</a>
<?php }else{
//Echo "You do not have access to edit project activities";
} ?>



                </td>
            </tr>
        <?php endforeach ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
