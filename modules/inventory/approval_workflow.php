<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../classes/Department.php';
require_once __DIR__ . '/../../classes/Inventory.php';
require_once __DIR__ . '/../../classes/User.php';



$departmentSystem = new Department($pdo);
$inventorySystem = new Inventory($pdo);
$userSystem = new User($pdo);

// Get data for dropdowns
$departments = $departmentSystem->getAllDepartments();
$categories = $inventorySystem->getCategories();
$approvers = $userSystem->getActiveUsers();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        // Clear existing rules
        $pdo->query("DELETE FROM approval_workflows");

        // Insert new rules
        $stmt = $pdo->prepare("
            INSERT INTO approval_workflows (
                department_id, item_category_id, amount_threshold, approval_level, approver_id
            ) VALUES (?, ?, ?, ?, ?)
        ");

        foreach ($_POST['rules'] as $rule) {
            if (!empty($rule['approver_id'])) {
                $stmt->execute([
                    $rule['department_id'] ?: null,
                    $rule['category_id'] ?: null,
                    $rule['amount_threshold'] ?: null,
                    $rule['approval_level'],
                    $rule['approver_id']
                ]);
            }
        }

        $pdo->commit();
        $_SESSION['success'] = "Approval workflow updated successfully!";
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
}

// Get current workflow rules
$currentRules = $pdo->query("
    SELECT w.*,
           d.name AS department_name,
           c.name AS category_name,
           CONCAT(u.full_name, ' ', u.full_name) AS approver_name
    FROM approval_workflows w
    LEFT JOIN departments d ON w.department_id = d.id
    LEFT JOIN inventory_categories c ON w.item_category_id = c.id
    LEFT JOIN users u ON w.approver_id = u.id
    ORDER BY w.approval_level
")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2>Approval Workflow Configuration</h2>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h4>Current Workflow Rules</h4>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="table-responsive">
                    <table class="table" id="workflowRules">
                        <thead>
                            <tr>
                                <th>Level</th>
                                <th>Department</th>
                                <th>Item Category</th>
                                <th>Amount Threshold</th>
                                <th>Approver</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php for ($i = 1; $i <= 3; $i++): ?>
                                <?php
                                $rule = array_filter($currentRules, fn($r) => $r['approval_level'] == $i);
                                $rule = reset($rule) ?: [];
                                ?>
                                <tr>
                                    <td><?= $i ?></td>
                                    <td>
                                        <select class="form-select" name="rules[<?= $i ?>][id]">
                                            <option value="">Any Department</option>
                                            <?php foreach ($departments as $dept): ?>
                                                <option value="<?= $dept['id'] ?>" <?= $rule['id'] == $dept['id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($dept['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-select" name="rules[<?= $i ?>][category_id]">
                                            <option value="">Any Category</option>
                                            <?php foreach ($categories as $cat): ?>
                                                <option value="<?= $cat['id'] ?>" <?= $rule['item_category_id'] == $cat['id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($cat['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" class="form-control" name="rules[<?= $i ?>][amount_threshold]"
                                               value="<?= $rule['amount_threshold'] ?? '' ?>" placeholder="Minimum amount">
                                    </td>
                                    <td>
                                        <select class="form-select" name="rules[<?= $i ?>][approver_id]" required>
                                            <option value="">Select Approver</option>
                                            <?php foreach ($approvers as $approver): ?>
                                                <option value="<?= $approver['id'] ?>" <?= $rule['approver_id'] == $approver['id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($approver['full_name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td>
                                        <?php if ($i == 1): ?>
                                            <button type="button" class="btn btn-sm btn-success add-level" data-level="<?= $i + 1 ?>">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-sm btn-danger remove-level">
                                                <i class="fas fa-minus"></i>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Workflow
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Add new approval level
    $(document).on('click', '.add-level', function() {
        const level = parseInt($(this).data('level'));
        const newRow = `
            <tr>
                <td>${level}</td>
                <td>
                    <select class="form-select" name="rules[${level}][department_id]">
                        <option value="">Any Department</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?= $dept['id'] ?>"><?= htmlspecialchars($dept['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td>
                    <select class="form-select" name="rules[${level}][category_id]">
                        <option value="">Any Category</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td>
                    <input type="number" class="form-control" name="rules[${level}][amount_threshold]" placeholder="Minimum amount">
                </td>
                <td>
                    <select class="form-select" name="rules[${level}][approver_id]" required>
                        <option value="">Select Approver</option>
                        <?php foreach ($approvers as $approver): ?>
                            <option value="<?= $approver['id'] ?>"><?= htmlspecialchars($approver['full_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-danger remove-level">
                        <i class="fas fa-minus"></i>
                    </button>
                </td>
            </tr>
        `;

        $('#workflowRules tbody').append(newRow);
        $(this).data('level', level + 1);
    });

    // Remove approval level
    $(document).on('click', '.remove-level', function() {
        $(this).closest('tr').remove();
    });
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>