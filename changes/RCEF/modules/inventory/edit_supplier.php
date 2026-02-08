<?php
require_once __DIR__ . '/../../includes/header.php';
require_once '../../classes/SchoolFeesSystem.php';
$school = new SchoolFeesSystem($pdo);

// Get supplier ID from URL
if (!isset($_GET['id'])) {
    echo "<div class='alert alert-danger'>Supplier ID is missing.</div>";
    require_once __DIR__ . '/../../includes/footer.php';
    exit;
}

$supplierId = $_GET['id'];

// Fetch supplier info
$stmt = $pdo->prepare("SELECT * FROM suppliers WHERE id = ?");
$stmt->execute([$supplierId]);
$supplier = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$supplier) {
    echo "<div class='alert alert-danger'>Supplier not found.</div>";
    require_once __DIR__ . '/../../includes/footer.php';
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name' => $_POST['name'],
        'contact_person' => $_POST['contact_person'],
        'email' => $_POST['email'],
        'phone' => $_POST['phone'],
        'address' => $_POST['address'],
        'tax_id' => $_POST['tax_id'],
        'supplier_contact_person' => $_POST['supplier_contact_person'],
        'is_active' => isset($_POST['is_active']) ? 1 : 0,
        'id' => $supplierId
    ];

    $update = $pdo->prepare("
        UPDATE suppliers SET
            name = :name,
            contact_person = :contact_person,
            email = :email,
            phone = :phone,
            address = :address,
            tax_id = :tax_id,
            supplier_contact_person = :supplier_contact_person,
            is_active = :is_active
        WHERE id = :id
    ");
    $update->execute($data);

    echo "<script>window.location='list_suppliers.php'</script>";

    exit;
}
?>

<div class="container mt-4">
    <h3>Edit Supplier</h3>
    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Supplier Name</label>
            <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($supplier['name']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Primary Contact Person</label>
            <input type="text" class="form-control" name="contact_person" value="<?= htmlspecialchars($supplier['contact_person']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Alternative Contact Person</label>
            <input type="text" class="form-control" name="supplier_contact_person" value="<?= htmlspecialchars($supplier['supplier_contact_person']) ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Email Address</label>
            <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($supplier['email']) ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Phone Number</label>
            <input type="text" class="form-control" name="phone" value="<?= htmlspecialchars($supplier['phone']) ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Address</label>
            <textarea class="form-control" name="address"><?= htmlspecialchars($supplier['address']) ?></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Tax Identification Number (TIN)</label>
            <input type="text" class="form-control" name="tax_id" value="<?= htmlspecialchars($supplier['tax_id']) ?>">
        </div>

        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" <?= $supplier['is_active'] ? 'checked' : '' ?>>
            <label class="form-check-label" for="is_active">Active</label>
        </div>

        <button type="submit" class="btn btn-primary">Update Supplier</button>
        <a href="list_suppliers.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
