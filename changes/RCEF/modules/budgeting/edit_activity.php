<?php
require_once __DIR__ . '/../../includes/header.php';
require_once '../../classes/BudgetingSystem.php';
$project = new BudgetingSystem($pdo);

// Get project and activity IDs
$projectId = $_GET['project_id'] ?? null;
$activityId = $_GET['id'] ?? null;

if (!$projectId || !$activityId) {
    echo "<div class='alert alert-danger'>Project ID or Activity ID is missing.</div>";
    require_once __DIR__ . '/../../includes/footer.php';
    exit;
}

// Fetch existing activity data
$activity = $project->getActivity($activityId);
if (!$activity) {
    echo "<div class='alert alert-danger'>Activity not found.</div>";
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

    if ($project->updateActivity($activityId, $activityData)) {
        echo "<script>window.location='view_project.php?id=$projectId'</script>";
        exit;
    } else {
        echo "<div class='alert alert-danger'>Failed to update activity.</div>";
    }
}
?>

<div class="container mt-5">
    <h3>Edit Activity</h3>
    <form method="POST" action="">
        <div class="mb-3">
            <label for="name" class="form-label">Activity Name</label>
            <input type="text" class="form-control" id="name" name="name"
                   value="<?= htmlspecialchars($activity['name']) ?>" required>
        </div>

        <div class="mb-3">
            <label for="budgeted_amount" class="form-label">Budgeted Amount</label>
            <input type="number" step="0.01" class="form-control" id="budgeted_amount"
                   name="budgeted_amount" value="<?= htmlspecialchars($activity['budgeted_amount']) ?>" required>
        </div>

        <div class="mb-3">
            <label for="revised_amount" class="form-label">Revised Amount</label>
            <input type="number" step="0.01" class="form-control" id="revised_amount"
                   name="revised_amount" value="<?= htmlspecialchars($activity['revised_amount']) ?>" required>
        </div>

        <div class="mb-3">
            <label for="actual_expense" class="form-label">Actual Expense</label>
            <input type="number" step="0.01" class="form-control" id="actual_expense"
                   name="actual_expense" value="<?= htmlspecialchars($activity['actual_expense']) ?>" required>
        </div>

        <?php if(hasPermission(26)) { ?>
            <button type="submit" class="btn btn-primary">Update Activity</button>
        <?php } else {
            echo "You do not have permission to edit project activities";
        } ?>

        <a href="view_project.php?id=<?= $projectId ?>" class="btn btn-secondary">Back to Project</a>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>