<?php
require_once '../../includes/header.php';
require_once '../../classes/HRSystem.php';

$hr = new HRSystem($pdo);
$employees = $hr->getAllEmployees();
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2><i class="bi bi-people-fill"></i> Employees</h2>
        <a href="add_employee.php" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Add Employee</a>
    </div>

    <table class="table table-striped table-bordered">
        <thead class="table-light">
            <tr>
                <th>Name</th>
                <th>Email</th>

                <th>Role(s)</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($employees as $emp): ?>

                <?php
$rolesArray = $hr->getEmployeeRoles($emp['id']); // returns array of arrays
$roles = array_column($rolesArray, 'role_name'); // extract just the 'role' values
?>
                <tr>
                    <td><?= htmlspecialchars($emp['full_name']) ?></td>
                    <td><?= htmlspecialchars($emp['email']) ?></td>
                    <td><?= implode(', ', $roles) ?></td>
                    <td>
                        <span class="badge bg-<?= $emp['is_active'] ? 'success' : 'secondary' ?>">
                            <?= $emp['is_active'] ? 'Active' : 'Inactive' ?>
                        </span>
                    </td>
                    <td>
                        <a href="view_employee.php?id=<?= $emp['id'] ?>" class="btn btn-sm btn-primary">View</a>

                        <?php if(hasPermission(22)){?>
<br><br>
                            <a href="edit_employee.php?id=<?= $emp['id'] ?>" class="btn btn-sm btn-info">Edit</a>
<?php }else{
//Echo "You do not have access to generate payroll";
} ?>


                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once '../../includes/footer.php'; ?>
