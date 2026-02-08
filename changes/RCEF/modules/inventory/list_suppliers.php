<?php
require_once __DIR__ . '/../../includes/header.php';
require_once '../../classes/SchoolFeesSystem.php';
$school = new SchoolFeesSystem($pdo);

// Fetch all suppliers
$stmt = $pdo->query("SELECT * FROM suppliers ORDER BY id DESC");
$suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">
    <h3>Supplier List</h3>

    <a href="add_supplier.php" class="btn btn-success mb-3">+ Add Supplier</a>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>#</th>
                <th>Acc NUmber</th>
                <th>Supplier Name</th>
                <th>Contact Person</th>
                <th>Alt. Contact</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Tax ID</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($suppliers) > 0): ?>
                <?php foreach ($suppliers as $index => $supplier): ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td><?= htmlspecialchars($supplier['account_number']) ?></td>
                        <td><?= htmlspecialchars($supplier['name']) ?></td>
                        <td><?= htmlspecialchars($supplier['contact_person']) ?></td>
                        <td><?= htmlspecialchars($supplier['supplier_contact_person']) ?></td>
                        <td><?= htmlspecialchars($supplier['email']) ?></td>
                        <td><?= htmlspecialchars($supplier['phone']) ?></td>
                        <td><?= htmlspecialchars($supplier['tax_id']) ?></td>
                        <td>
                            <?= $supplier['is_active'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>' ?>
                        </td>
                        <td>
                        <?php if(hasPermission(18)){ ?>
                            <a href="edit_supplier.php?id=<?= $supplier['id'] ?>" class="btn btn-sm btn-primary">Edit</a>

 <?php }?>


                        </td>
                    </tr>
                <?php endforeach ?>
            <?php else: ?>
                <tr>
                    <td colspan="9" class="text-center">No suppliers found.</td>
                </tr>
            <?php endif ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
