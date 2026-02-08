<?php
require_once __DIR__ . '/../../includes/header.php';
require_once '../../classes/SchoolFeesSystem.php';
$school = new SchoolFeesSystem($pdo);

if (!isset($_GET['id'])) {
    echo "<div class='alert alert-danger'>Missing category ID.</div>";
    require_once __DIR__ . '/../../includes/footer.php';
    exit;
}

$id = $_GET['id'];

// Fetch existing category
$stmt = $pdo->prepare("SELECT * FROM inventory_categories WHERE id = :id");
$stmt->execute(['id' => $id]);
$category = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$category) {
    echo "<div class='alert alert-danger'>Category not found.</div>";
    require_once __DIR__ . '/../../includes/footer.php';
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);

    if (!empty($name)) {
        $stmt = $pdo->prepare("UPDATE inventory_categories SET name = :name WHERE id = :id");
        $stmt->execute(['name' => $name, 'id' => $id]);
        echo "<div class='alert alert-success'>Category updated successfully.</div>";
        // Refresh category
        $stmt = $pdo->prepare("SELECT * FROM inventory_categories WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $category = $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        echo "<div class='alert alert-danger'>Category name is required.</div>";
    }
}
?>

<div class="container mt-4">
    <h3>Edit Inventory Category</h3>

    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Category Name</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($category['name']) ?>" required>
        </div>
        <button class="btn btn-primary" type="submit">Update</button>
        <a href="list_inventory_categories.php" class="btn btn-secondary">Back to List</a>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
