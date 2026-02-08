<?php
require_once __DIR__ . '/../../includes/header.php';

// Ensure role ID is provided
if (!isset($_GET['role_id'])) {
    echo "Role ID is required.";
    exit;
}

$roleId = (int) $_GET['role_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedPermissions = $_POST['permissions'] ?? [];

    // Clear existing role permissions
    $pdo->prepare("DELETE FROM role_permissions WHERE role_id = ?")->execute([$roleId]);

    // Insert new permissions
    $stmt = $pdo->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
    foreach ($selectedPermissions as $permId) {
        $stmt->execute([$roleId, $permId]);
    }

    echo "<div class='alert alert-success'>Permissions updated successfully.</div>";
}

// Fetch all permissions
$permissions = $pdo->query("SELECT * FROM permissions ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

// Fetch current role permissions
$stmt = $pdo->prepare("SELECT permission_id FROM role_permissions WHERE role_id = ?");
$stmt->execute([$roleId]);
$currentPermissions = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'permission_id');
?>

<div class="container mt-4">
    <h2>Assign Permissions to Role</h2>

    <form method="POST">
        <div class="form-group">
            <?php foreach ($permissions as $perm): ?>
                <div class="form-check">
                    <input
                        class="form-check-input"
                        type="checkbox"
                        name="permissions[]"
                        value="<?= $perm['id'] ?>"
                        id="perm<?= $perm['id'] ?>"
                        <?= in_array($perm['id'], $currentPermissions) ? 'checked' : '' ?>
                    >
                    <label class="form-check-label" for="perm<?= $perm['id'] ?>">
                        <?= htmlspecialchars($perm['name']) ?>
                    </label>
                </div>
            <?php endforeach; ?>
        </div>

        <br>
        <button type="submit" class="btn btn-primary">Save Permissions</button>
        <a href="roles_list.php" class="btn btn-secondary">Back</a>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
