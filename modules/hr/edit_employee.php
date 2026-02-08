<?php
require_once __DIR__ . '/../../includes/header.php';

// Get employee ID
if (!isset($_GET['id'])) die("Employee ID required.");
$employeeId = $_GET['id'];

// Fetch employee details
$employee = $pdo->prepare("SELECT * FROM employees WHERE id = ?");
$employee->execute([$employeeId]);
$employee = $employee->fetch(PDO::FETCH_ASSOC);

// Roles
$roles = $pdo->query("SELECT * FROM roles")->fetchAll(PDO::FETCH_ASSOC);

// Employee roles
$empRoleStmt = $pdo->prepare("SELECT role_id FROM employee_roles WHERE employee_id = ?");
$empRoleStmt->execute([$employeeId]);
$employeeRoleIds = array_column($empRoleStmt->fetchAll(PDO::FETCH_ASSOC), 'role_id');

// Employee permissions
$empPermStmt = $pdo->prepare("SELECT permission_id FROM employee_permissions WHERE employee_id = ?");
$empPermStmt->execute([$employeeId]);
$employeePermissionIds = array_column($empPermStmt->fetchAll(PDO::FETCH_ASSOC), 'permission_id');

// Permissions grouped by role
$rolePermissions = [];
foreach ($roles as $role) {
    $stmt = $pdo->prepare("SELECT permissions.id, permissions.name FROM role_permissions
                            JOIN permissions ON role_permissions.permission_id = permissions.id
                            WHERE role_permissions.role_id = ?");
    $stmt->execute([$role['id']]);
    $rolePermissions[$role['id']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update info
    $pdo->prepare("UPDATE employees SET full_name = ?, email = ?, phone = ?, salary = ? WHERE id = ?")
        ->execute([$_POST['full_name'], $_POST['email'], $_POST['phone'], $_POST['salary'], $employeeId]);

    // Update roles
    $pdo->prepare("DELETE FROM employee_roles WHERE employee_id = ?")->execute([$employeeId]);
    if (!empty($_POST['roles'])) {
        foreach ($_POST['roles'] as $roleId) {
            $pdo->prepare("INSERT INTO employee_roles (employee_id, role_id) VALUES (?, ?)")->execute([$employeeId, $roleId]);
        }
    }

    // Update employee permissions
    $pdo->prepare("DELETE FROM employee_permissions WHERE employee_id = ?")->execute([$employeeId]);
    if (!empty($_POST['permissions'])) {
        foreach ($_POST['permissions'] as $permId) {
            $pdo->prepare("INSERT INTO employee_permissions (employee_id, permission_id) VALUES (?, ?)")->execute([$employeeId, $permId]);
        }
    }
    echo "<script>window.location='employees_list.php'</script>";
    exit;
}
?>

<div class="container mt-4">
    <h2>Edit Employee</h2>
    <form method="POST">
        <div class="mb-3">
            <label>Full Name</label>
            <input class="form-control" name="full_name" value="<?= htmlspecialchars($employee['full_name']) ?>" required>
        </div>
        <div class="mb-3">
            <label>Email</label>
            <input class="form-control" name="email" value="<?= htmlspecialchars($employee['email']) ?>" required>
        </div>
        <div class="mb-3">
            <label>Phone</label>
            <input class="form-control" name="phone" value="<?= htmlspecialchars($employee['phone']) ?>" required>
        </div>
        <div class="mb-3">
            <label>Salary</label>
            <input class="form-control" name="salary" value="<?= htmlspecialchars($employee['salary']) ?>" required>
        </div>

        <div class="mb-3">
            <label>Assign Roles</label>
            <?php foreach ($roles as $role): ?>
                <div class="form-check">
                    <input class="form-check-input role-check" type="checkbox" name="roles[]" value="<?= $role['id'] ?>"
                        id="role_<?= $role['id'] ?>" <?= in_array($role['id'], $employeeRoleIds) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="role_<?= $role['id'] ?>">
                        <?= htmlspecialchars($role['role_name']) ?>
                    </label>
                </div>

                <div class="ms-3 mb-2 permissions-group" id="permissions_<?= $role['id'] ?>" style="display: none;">
                    <?php foreach ($rolePermissions[$role['id']] as $perm): ?>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="permissions[]" value="<?= $perm['id'] ?>"
                                id="perm_<?= $perm['id'] ?>" <?= in_array($perm['id'], $employeePermissionIds) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="perm_<?= $perm['id'] ?>"><?= $perm['name'] ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>


        <?php if(hasPermission(22)){?>

            <button class="btn btn-success">Update</button>
<?php }else{
Echo "You do not have access to update employee";
} ?>

    </form>
</div>

<script>
document.querySelectorAll('.role-check').forEach(input => {
    const roleId = input.value;
    const permDiv = document.getElementById('permissions_' + roleId);

    // Initially show if role was selected
    if (input.checked) {
        permDiv.style.display = 'block';
    }

    input.addEventListener('change', function () {
        permDiv.style.display = this.checked ? 'block' : 'none';
    });
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
