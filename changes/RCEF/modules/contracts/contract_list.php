<?php
require_once __DIR__ . '/../../includes/header.php';



// Fetch contracts with supplier names
$sql = "SELECT c.*, s.name
        FROM contracts c
        LEFT JOIN suppliers s ON c.supplier_id = s.id
        ORDER BY c.contract_id DESC";

$stmt = $pdo->query($sql);
$contracts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">
    <h3 class="mb-4">All Contracts<a  class="btn btn-info float-end" href="add_contract.php">Create New Contract</a></h3>

    <table class="table table-bordered table-hover">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Contract Number</th>
                <th>Title</th>
                <th>Supplier</th>
                <th>Contract Date</th>

                <th>Total Value</th>
                 <th>Paid Amount</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($contracts): ?>
                <?php foreach ($contracts as $index => $c): ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td><?= htmlspecialchars($c['contract_number']) ?></td>
                        <td><?= htmlspecialchars($c['contract_title']) ?></td>
                        <td><?= htmlspecialchars($c['name']) ?></td>
                        <td><?= $c['contract_date'] ?></td>

                        <td>
                            <?php
                                $stmtVal = $pdo->prepare("SELECT SUM(unit_price * quantity) AS total FROM contract_items WHERE contract_id = ?");
                                $stmtVal->execute([$c['contract_id']]);
                                $val = $stmtVal->fetch(PDO::FETCH_ASSOC);
                                echo number_format($val['total'] ?? 0, 2);
                            ?>
                        </td>
                        <td>
    <?php
        // Step 1: Get all PO IDs for the contract
        $stmtPO = $pdo->prepare("SELECT id FROM purchase_orders WHERE contract_id = ?");
        $stmtPO->execute([$c['contract_id']]);
        $poIds = $stmtPO->fetchAll(PDO::FETCH_COLUMN);

        $paidAmount = 0;

       if (!empty($poIds)) {
    $placeholders = implode(',', array_fill(0, count($poIds), '?'));

    $stmtTxn = $pdo->prepare("
        SELECT tl.debit
        FROM transactions t
        INNER JOIN transaction_lines tl ON tl.transaction_id = t.id
        WHERE t.purchase_order IN ($placeholders)
    ");
    $stmtTxn->execute($poIds);

    $lines = $stmtTxn->fetchAll(PDO::FETCH_ASSOC);
    foreach ($lines as $line) {
        $paidAmount += floatval($line['debit']);
    }
}

        echo number_format($paidAmount, 2);
    ?>
</td>
                        <td>
                            <a href="contract_preview.php?contract_id=<?= $c['contract_id'] ?>" class="btn btn-sm btn-info">View</a>
                            <a href="edit_contract.php?contract_id=<?= $c['contract_id'] ?>" class="btn btn-sm btn-warning">Edit</a>

                            <form method="POST" action="delete.php" onsubmit="return confirm('Are you sure you want to delete this contract and all its related data?');">
    <input type="hidden" name="contract_id" value="<?= $c['contract_id'] ?>">
    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
</form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="8">No contracts found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
