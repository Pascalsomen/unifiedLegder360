<?php
require_once __DIR__ . '/../../includes/header.php';


// Get next contract number
function getNextContractNumber(PDO $pdo) {
    $stmt = $pdo->query("SELECT MAX(CAST(contract_number AS UNSIGNED)) AS last FROM contracts");
    $last = $stmt->fetchColumn();
    return str_pad(($last ?: 0) + 1, 5, "0", STR_PAD_LEFT);
}

$nextNumber = getNextContractNumber($pdo);

// Fetch suppliers
$supplierStmt = $pdo->query("SELECT id as supplier_id, name as supplier_name FROM suppliers");
$suppliers = $supplierStmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contract_number = $_POST['contract_number'];
    $supplier_id = $_POST['supplier_id'];
    $contract_title = $_POST['contract_title'];
    $contract_date = $_POST['contract_date'];
    $notes = $_POST['notes'];

    try {
        $stmt = $pdo->prepare("
            INSERT INTO contracts (contract_number, supplier_id, contract_title, contract_date, notes)
            VALUES (:contract_number, :supplier_id, :contract_title, :contract_date, :notes)
        ");

        $stmt->execute([
            ':contract_number' => $contract_number,
            ':supplier_id' => $supplier_id,
            ':contract_title' => $contract_title,
            ':contract_date' => $contract_date,
            ':notes' => $notes
        ]);

        $new_contract_id = $pdo->lastInsertId();
        $link ="add_articles.php?contract_id=".$new_contract_id;
        echo "<script>window.location='$link'</script>";
        exit;
    } catch (PDOException $e) {
        $error = "Error saving contract: " . $e->getMessage();
    }
}
?>

<div class="container-fluid mt-4">
    <h4 class="mb-4">Create New Contract</h4>

    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" class="card p-4">
        <div class="mb-3">
            <label for="contract_number" class="form-label">Contract Number</label>
            <input type="text" class="form-control" name="contract_number" value="<?= $nextNumber ?>" readonly>
        </div>

        <div class="mb-3">
            <label for="supplier_id" class="form-label">Select Supplier</label>
            <select name="supplier_id" class="form-select" required>
                <option value="">-- Select Supplier --</option>
                <?php foreach ($suppliers as $s): ?>
                    <option value="<?= $s['supplier_id'] ?>"><?= htmlspecialchars($s['supplier_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="contract_title" class="form-label">Contract Title</label>
            <input type="text" class="form-control" name="contract_title" required>
        </div>

        <div class="mb-3">
            <label for="contract_date" class="form-label">Contract Date</label>
            <input type="date" class="form-control" name="contract_date" value="<?= date('Y-m-d') ?>" required>
        </div>

        <div class="mb-3">
            <label for="notes" class="form-label">Notes (optional)</label>
            <textarea name="notes" class="form-control" rows="3"></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Save & Continue</button>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
