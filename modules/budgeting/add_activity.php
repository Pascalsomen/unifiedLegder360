<?php
require_once __DIR__ . '/../../includes/header.php';
require_once '../../classes/BudgetingSystem.php';
$project = new BudgetingSystem($pdo);

$projectId = $_GET['project_id'] ?? null;
if (!$projectId) {
    echo "<div class='alert alert-danger'>Project ID is missing.</div>";
    require_once __DIR__ . '/../../includes/footer.php';
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $activityData = [
        'name' => $_POST['name'],
        'budgeted_amount' => $_POST['budgeted_amount'],
        'revised_amount' => $_POST['revised_amount'],
        'actual_expense' => $_POST['actual_expense']
    ];

    $project->addActivity($projectId, $activityData);

    echo "<script>window.location='view_project.php?id=$projectId'</script>";
    exit;
}
?>

<div class="container mt-5">
    <h3>Add Activity to Project</h3>
    <form method="POST" action="">
        <div class="mb-3">
            <label for="name" class="form-label">Activity Name</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>

        <div class="mb-3">
            <label for="budgeted_amount" class="form-label">Budgeted Amount</label>
            <input type="number" step="0.01" class="form-control" id="budgeted_amount" name="budgeted_amount" required>
        </div>


            <div class="mb-3">
            <label for="revised_amount" class="form-label">Revised Amount</label>
            <input type="number" step="0.01" class="form-control" id="revised_amount" name="revised_amount" required>
        </div>

        <div class="mb-3">
            <label for="actual_expense" class="form-label">Actual Expense</label>
            <input type="number" step="0.01" class="form-control" id="actual_expense" name="actual_expense" required>
        </div>

        <?php if(hasPermission(25)){?>

            <button type="submit" class="btn btn-success">Add Activity</button>
<?php }else{
Echo "You do not have access to create project activities";
} ?>

        <a href="view_project.php?id=<?= $projectId ?>" class="btn btn-secondary">Back to Project</a>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
