<?php
require_once '../../includes/header.php';
require_once '../../classes/HRSystem.php';

$hrSystem = new HRSystem($pdo);
$allRoles = $hrSystem->getAllRoles();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $employeeData = [
            'full_name' => $_POST['full_name'],
            'email' => $_POST['email'],
            'position' => $_POST['position'],
            'phone' => $_POST['phone'],
            'salary' => $_POST['salary'],
            'roles' => $_POST['roles'] ?? []
        ];


        $employeeId = $hrSystem->addEmployee($employeeData, $_SESSION['user_id']);
        $hrSystem->assignRoles($employeeId, $_POST['roles']);
        $_SESSION['success'] = "Employee added successfully!";

    } catch (Exception $e) {
       echo $_SESSION['error'] = "Error: " . $e->getMessage();
    }
}
?>

<div class="container mt-4">
    <h2>Add New Employee</h2>
    <form method="post">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label>Full Name *</label>
                <input type="text" name="full_name" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
                <label>Email *</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div  hidden class="col-md-6 mb-3">
                <label>Position</label>
                <input type="text" name="position" class="form-control">
            </div>
            <div class="col-md-6 mb-3">
                <label>Phone</label>
                <input type="text" name="phone" class="form-control">
            </div>
            <div class="col-md-6 mb-3">
                <label>Basic Salary *</label>
                <input type="number" step="0.01" name="salary" class="form-control" required>
            </div>
        </div>





<div class="col-md-6 mb-3">
    <label>Assign Roles *</label>
    <select name="roles[]" class="form-select" multiple required>
        <?php foreach ($allRoles as $role): ?>
            <option value="<?= $role['id'] ?>"><?= htmlspecialchars($role['role_name']) ?></option>
        <?php endforeach; ?>
    </select>
    <small class="text-muted">Hold Ctrl (Windows) or Cmd (Mac) to select multiple.</small>
</div>

<?php if(hasPermission(20)){?>

    <button type="submit" class="btn btn-primary">Add Employee</button>
<?php }else{
Echo "You do not have access to add new employee";
} ?>

    </form>
</div>

<?php require_once '../../includes/footer.php'; ?>
