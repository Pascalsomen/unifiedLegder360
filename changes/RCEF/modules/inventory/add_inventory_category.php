<?php
require_once __DIR__ . '/../../includes/header.php';
require_once '../../classes/SchoolFeesSystem.php';
$school = new SchoolFeesSystem($pdo);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);

    if (!empty($name)) {
        $stmt = $pdo->prepare("INSERT INTO inventory_categories (name) VALUES (:name)");
        $stmt->execute(['name' => $name]);
        echo "<div class='alert alert-success'>Category added successfully.</div>";
    } else {
        echo "<div class='alert alert-danger'>Category name is required.</div>";
    }
}
?>

<div class="container mt-4">
    <h3>Add Inventory Category</h3>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Category Name</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <button class="btn btn-primary" type="submit">Save</button>
        <a href="list_inventory_categories.php" class="btn btn-secondary">Back to List</a>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
