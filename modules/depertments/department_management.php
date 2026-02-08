<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../classes/Department.php';
require_once __DIR__ . '/../../classes/User.php';



$departmentSystem = new Department($pdo);
$userSystem = new User($pdo);

// Get all active users for manager dropdown
$managers = $userSystem->getActiveUsers();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_department'])) {
        try {
            $departmentId = $departmentSystem->createDepartment(
                $_POST['name'],
                $_POST['code'],
                $_POST['manager_id'] ?: null,
                $_POST['parent_id'] ?: null
            );

            $_SESSION['success'] = "Department created successfully!";
            redirect("/modules/admin/department_management.php");
        } catch (Exception $e) {
            $_SESSION['error'] = "Error creating department: " . $e->getMessage();
        }
    } elseif (isset($_POST['update_department'])) {
        try {
            $success = $departmentSystem->updateDepartment(
                $_POST['department_id'],
                $_POST['name'],
                $_POST['code'],
                $_POST['manager_id'] ?: null,
                $_POST['parent_id'] ?: null
            );

            if ($success) {
                $_SESSION['success'] = "Department updated successfully!";
            } else {
                $_SESSION['error'] = "Department not found or no changes made";
            }
            redirect("/modules/admin/department_management.php");
        } catch (Exception $e) {
            $_SESSION['error'] = "Error updating department: " . $e->getMessage();
        }
    } elseif (isset($_POST['toggle_status'])) {
        $success = $departmentSystem->toggleDepartmentStatus(
            $_POST['department_id'],
            $_POST['is_active'] === '1'
        );

        if ($success) {
            $_SESSION['success'] = "Department status updated successfully!";
        } else {
            $_SESSION['error'] = "Department not found or no changes made";
        }
        redirect("/modules/admin/department_management.php");
    }
}

// Get all departments
$departments = $departmentSystem->getAllDepartments(false);
$departmentTree = $departmentSystem->getDepartmentTree();

// Get department to edit (if specified)
$editDepartment = null;
if (isset($_GET['edit'])) {
    $editDepartment = $departmentSystem->getDepartment(intval($_GET['edit']));
}
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2>Department Management</h2>
        </div>
    </div>

    <div class="row">
        <div class="col-md-5">
            <div class="card">
                <div class="card-header">
                    <h4><?= $editDepartment ? 'Edit' : 'Create' ?> Department</h4>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <?php if ($editDepartment): ?>
                            <input type="hidden" name="department_id" value="<?= $editDepartment['id'] ?>">
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label">Department Name*</label>
                            <input type="text" class="form-control" name="name"
                                   value="<?= htmlspecialchars($editDepartment['name'] ?? '') ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Department Code*</label>
                            <input type="text" class="form-control" name="code"
                                   value="<?= htmlspecialchars($editDepartment['code'] ?? '') ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Manager</label>
                            <select class="form-select" name="manager_id">
                                <option value="">No Manager</option>
                                <?php foreach ($managers as $manager): ?>
                                    <option value="<?= $manager['id'] ?>"
                                        <?= isset($editDepartment['manager_id']) && $editDepartment['manager_id'] == $manager['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($manager['full_name'] . ' ' . $manager['full_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Parent Department</label>
                            <select class="form-select" name="parent_id">
                                <option value="">No Parent Department</option>
                                <?php foreach ($departments as $dept): ?>
                                    <?php if (!$editDepartment || $dept['id'] != $editDepartment['id']): ?>
                                        <option value="<?= $dept['id'] ?>"
                                            <?= isset($editDepartment['parent_id']) && $editDepartment['parent_id'] == $dept['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($dept['name']) ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="d-flex justify-content-between">
                            <?php if ($editDepartment): ?>
                                <a href="department_management.php" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                                <button type="submit" name="update_department" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update Department
                                </button>
                            <?php else: ?>
                                <button type="submit" name="create_department" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Create Department
                                </button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">All Departments</h4>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="showInactiveToggle" checked>
                            <label class="form-check-label" for="showInactiveToggle">Show Inactive</label>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="departmentsTable">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Manager</th>
                                    <th>Parent Dept</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($departments as $dept): ?>
                                    <tr class="<?= $dept['is_active'] ? '' : 'table-secondary' ?>">
                                        <td><?= htmlspecialchars($dept['code']) ?></td>
                                        <td><?= htmlspecialchars($dept['name']) ?></td>
                                        <td><?= htmlspecialchars($dept['manager_name'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($dept['parent_department_name'] ?? '-') ?></td>
                                        <td>
                                            <span class="badge bg-<?= $dept['is_active'] ? 'success' : 'secondary' ?>">
                                                <?= $dept['is_active'] ? 'Active' : 'Inactive' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="department_management.php?edit=<?= $dept['id'] ?>" class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="department_id" value="<?= $dept['id'] ?>">
                                                <input type="hidden" name="is_active" value="<?= $dept['is_active'] ? '0' : '1' ?>">
                                                <button type="submit" name="toggle_status" class="btn btn-sm btn-<?= $dept['is_active'] ? 'danger' : 'success' ?>">
                                                    <i class="fas fa-<?= $dept['is_active'] ? 'times' : 'check' ?>"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>


</div>

<style>
.department-node {
    margin-left: 20px;
    padding: 5px;
    border-left: 2px solid #ddd;
}

.department-node .node-header {
    font-weight: bold;
    cursor: pointer;
}

.department-node .node-header:hover {
    background-color: #f5f5f5;
}

.department-node .node-children {
    margin-left: 20px;
    display: none;
}

.department-node.expanded .node-children {
    display: block;
}
</style>

<script>
$(document).ready(function() {
    // Toggle inactive departments
    $('#showInactiveToggle').change(function() {
        if ($(this).is(':checked')) {
            $('#departmentsTable tbody tr').show();
        } else {
            $('#departmentsTable tbody tr.table-secondary').hide();
        }
    });

    // Initialize department tree
    $(document).on('click', '.node-header', function() {
        $(this).closest('.department-node').toggleClass('expanded');
    });
});

// Function to render department tree
function renderDepartmentTree(departments) {
    let html = '';

    departments.forEach(dept => {
        html += `
            <div class="department-node">
                <div class="node-header d-flex justify-content-between align-items-center">
                    <span>
                        ${dept.code} - ${dept.name}
                        <span class="badge bg-${dept.is_active ? 'success' : 'secondary'} ms-2">
                            ${dept.is_active ? 'Active' : 'Inactive'}
                        </span>
                    </span>
                    <div>
                        <a href="department_management.php?edit=${dept.id}" class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i>
                        </a>
                    </div>
                </div>
        `;

        if (dept.children && dept.children.length > 0) {
            html += '<div class="node-children">';
            html += renderDepartmentTree(dept.children);
            html += '</div>';
        }

        html += '</div>';
    });

    return html;
}

// Render the initial tree
document.getElementById('departmentTree').innerHTML = renderDepartmentTree(<?= json_encode($departmentTree) ?>);
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>