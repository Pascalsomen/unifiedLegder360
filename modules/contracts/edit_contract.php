<?php
require_once __DIR__ . '/../../includes/header.php';

if (!isset($_GET['contract_id'])) {
    echo "<div class='alert alert-danger'>Missing contract ID.</div>";
    exit;
}

$contract_id = (int) $_GET['contract_id'];

// Fetch contract details
$stmt = $pdo->prepare("SELECT * FROM contracts WHERE contract_id = ?");
$stmt->execute([$contract_id]);
$contract = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$contract) {
    echo "<div class='alert alert-danger'>Contract not found.</div>";
    exit;
}

// Fetch suppliers
$suppliers = $pdo->query("SELECT id as supplier_id, name as supplier_name FROM suppliers")->fetchAll(PDO::FETCH_ASSOC);

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contract_title = $_POST['contract_title'];
    $supplier_id = $_POST['supplier_id'];
    $start_date = $_POST['start_date'];


    $update = $pdo->prepare("UPDATE contracts SET contract_title = ?, supplier_id = ?, contract_date = ? WHERE contract_id = ?");
    $update->execute([$contract_title, $supplier_id, $start_date, $contract_id]);

    echo "<script>alert('Contract updated successfully.'); window.location.href='contract_list.php';</script>";
    exit;
}
?>

<div class="container">
    <h3>Edit Contract  <a href="edit_articles.php?contract_id=<?php echo $contract_id ?>" class="btn btn-info">Edit Articles</a> <a href="edit_items.php?contract_id=<?php echo $contract_id ?>" class="btn btn-info">Edit Items</a> <a href="edit_signature.php?contract_id=<?php echo $contract_id ?>" class="btn btn-info">Edit Signature</a></h3>
    <form method="post" class="card p-4">
        <div class="mb-3">
            <label>Contract Number</label>
            <input type="text" name="contract_number" class="form-control" value="<?= htmlspecialchars($contract['contract_number']) ?>" disabled>
        </div>
        <div class="mb-3">
            <label>Contract Title</label>
            <input type="text" name="contract_title" class="form-control" required value="<?= htmlspecialchars($contract['contract_title']) ?>">
        </div>
        <div class="mb-3">
            <label>Supplier</label>
            <select name="supplier_id" class="form-control" required>
                <option value="">-- Select Supplier --</option>
                <?php foreach ($suppliers as $sup): ?>
                    <option value="<?= $sup['supplier_id'] ?>" <?= $contract['supplier_id'] == $sup['supplier_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($sup['supplier_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label>Contract Date</label>
            <input type="date" name="start_date" class="form-control" required value="<?= $contract['contract_date'] ?>">
        </div>


        <button type="submit" class="btn btn-primary">Update Contract</button>
        <a href="contract_list.php" class="btn btn-secondary">Cancel</a>



    </form>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
