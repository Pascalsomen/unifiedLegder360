<?php
require_once __DIR__ . '/../../includes/header.php';
require_once '../../classes/BudgetingSystem.php';
$project = new BudgetingSystem($pdo);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name' => $_POST['name'],
        'description' => $_POST['description'],
        'budgeted_amount' => $_POST['budgeted_amount'] ?? null,
        'revised_budget' => $_POST['revised_budget'] ?? null
    ];

    $projectId = $project->createProject($data);
    echo "<script>window.location='view_project.php?id=$projectId'</script>";
    exit;
}
?>

<div class="container mt-5">
    <h3>Create New Project Budget</h3>
    <form method="POST" action="">
        <div class="mb-3">
            <label for="name" class="form-label">Project Name</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Project Description</label>
            <textarea class="form-control" id="description" name="description" rows="4"></textarea>
        </div>




        <?php if(hasPermission(24)){?>

            <button type="submit" class="btn btn-primary">Create Project</button>
<?php }else{
Echo "You do not have access to create project budgeting";
} ?>

    </form>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
