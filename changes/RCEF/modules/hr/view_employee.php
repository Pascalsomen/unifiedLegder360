<?php
require_once '../../includes/header.php';
require_once '../../classes/HRSystem.php';

if (isset($_GET['id'])) {
    $employeeId = $_GET['id'];
    $hr = new HRSystem($pdo);
    $employee = $hr->getEmployeeById($employeeId);

    if (!$employee) {
        echo "<div class='alert alert-danger'>Employee not found.</div>";
        exit;
    }
} else {
    echo "<div class='alert alert-danger'>No employee ID provided.</div>";
    exit;
}
?>

<div class="container mt-4">
    <h2>Employee Details</h2>
    <div class="card">
        <?php
$rolesArray = $hr->getEmployeeRoles($employee['id']); // returns array of arrays
$roles = array_column($rolesArray, 'role_name'); // extract just the 'role' values
        ?>
        <div class="card-body">
            <h5>Name: <?= htmlspecialchars($employee['full_name']) ?></h5>
            <p><strong>Email:</strong> <?= htmlspecialchars($employee['email']) ?></p>
            <p><strong>Phone:</strong> <?= htmlspecialchars($employee['phone']) ?></p>
            <p><strong>Role(s):</strong> <?= implode(', ', $roles) ?> </p>
            <p><strong>Status:</strong>
                <span class="badge bg-<?= $employee['is_active'] ? 'success' : 'secondary' ?>">
                    <?= $employee['is_active'] ? 'Active' : 'Inactive' ?>
                </span>
            </p>
            <p><strong>Date Created:</strong> <?= date('Y-m-d', strtotime($employee['created_at'])) ?></p>

        </div>
    </div>

    <hr>

    <?php if(hasPermission(22)){?>

        <a href="edit_employee.php?id=<?= $employee['id'] ?>" class="btn btn-warning">Edit Employee</a>
<?php }else{
///Echo "You do not have access to update employee";
} ?>


    <a href="employees_list.php" class="btn btn-secondary">Back to Employees List</a>
</div>

<?php require_once '../../includes/footer.php'; ?>
