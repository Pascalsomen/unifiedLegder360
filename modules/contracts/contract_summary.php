<?php
require_once __DIR__ . '/../../includes/header.php';


if (!isset($_GET['contract_id'])) {
    echo "<div class='alert alert-danger'>Contract ID is required.</div>";
    exit;
}

$contract_id = (int) $_GET['contract_id'];

// Get contract details
$stmt = $pdo->prepare("SELECT * FROM contracts WHERE contract_id = ?");
$stmt->execute([$contract_id]);
$contract = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$contract) {
    echo "<div class='alert alert-danger'>Contract not found.</div>";
    exit;
}

// Get supplier info
$supplier = null;
if ($contract['supplier_id']) {
    $sup = $pdo->prepare("SELECT * FROM suppliers WHERE id = ?");
    $sup->execute([$contract['supplier_id']]);
    $supplier = $sup->fetch(PDO::FETCH_ASSOC);
}

// Get articles
$articles = $pdo->prepare("SELECT * FROM contract_articles WHERE contract_id = ? ORDER BY article_id ASC");
$articles->execute([$contract_id]);
$articles = $articles->fetchAll(PDO::FETCH_ASSOC);

// Get items
$items = $pdo->prepare("SELECT i.*, it.item_name, c.account_name
                        FROM contract_items i
                        LEFT JOIN stock_items it ON i.item_id = it.id
                        LEFT JOIN chart_of_accounts c ON i.chart_account_id = c.id
                        WHERE i.contract_id = ?");
$items->execute([$contract_id]);
$items = $items->fetchAll(PDO::FETCH_ASSOC);

// Get signatures
$signatures = $pdo->prepare("SELECT * FROM contract_signatures WHERE contract_id = ?");
$signatures->execute([$contract_id]);
$signatures = $signatures->fetchAll(PDO::FETCH_ASSOC);

// Calculate total
$totalValue = 0;
foreach ($items as $item) {
    $totalValue += $item['unit_price'] * $item['quantity'];
}
?>

<div class="container mt-4">
    <h3 class="mb-4">Contract Summary</h3>

    <div class="card p-3 mb-4">
        <h5>Contract Information</h5>
        <p><strong>Contract No:</strong> <?= htmlspecialchars($contract['contract_number']) ?></p>
        <p><strong>Title:</strong> <?= htmlspecialchars($contract['contract_title']) ?></p>
        <p><strong>Contract Date:</strong> <?= $contract['contract_date'] ?></p>
        <p><strong>Created At:</strong> <?= $contract['created_at'] ?></p>
    </div>

    <div class="card p-3 mb-4">
        <h5>Supplier Information</h5>
        <?php if ($supplier): ?>
            <p><strong>Name:</strong> <?= htmlspecialchars($supplier['name']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($supplier['email']) ?></p>
            <p><strong>Phone:</strong> <?= htmlspecialchars($supplier['phone']) ?></p>
        <?php else: ?>
            <p>No supplier linked.</p>
        <?php endif; ?>
    </div>

    <div class="card p-3 mb-4">
        <h5>Contract Articles</h5>
        <?php if ($articles): ?>
            <ol>
                <?php foreach ($articles as $article): ?>
                    <li><strong><?= htmlspecialchars($article['title']) ?><br></strong> <?= htmlspecialchars($article['body']) ?></li>
                <?php endforeach; ?>
            </ol>
        <?php else: ?>
            <p>No articles found.</p>
        <?php endif; ?>
    </div>

    <div class="card p-3 mb-4">
        <h5>Contract Items</h5>
        <?php if ($items): ?>
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Category</th>
                        <th>Item Name</th>
                        <th>Unit Price</th>
                        <th>Quantity</th>
                        <th>Account</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['item_category']) ?></td>
                            <td><?= htmlspecialchars($item['item_name']) ?></td>
                            <td><?= number_format($item['unit_price'], 2) ?></td>
                            <td><?= $item['quantity'] ?></td>
                            <td><?= htmlspecialchars($item['account_name']) ?></td>
                            <td><?= number_format($item['unit_price'] * $item['quantity'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="table-info fw-bold">
                        <td colspan="5" class="text-end">Total Contract Value</td>
                        <td><?= number_format($totalValue, 2) ?></td>
                    </tr>
                </tbody>
            </table>
        <?php else: ?>
            <p>No items found.</p>
        <?php endif; ?>
    </div>

    <div class="card p-3 mb-4">
        <h5>Signatures</h5>
        <?php if ($signatures): ?>
            <table class="table table-bordered">
                <thead class="table-light">
                    <tr>
                        <th>Signer</th>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Date</th>
                        <th>Scanned File</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($signatures as $s): ?>
                        <tr>
                            <td><?= htmlspecialchars($s['signer_name']) ?></td>
                            <td><?= htmlspecialchars($s['signer_title']) ?></td>
                            <td><?= htmlspecialchars($s['signer_type']) ?></td>
                            <td><?= $s['signature_date'] ?></td>
                            <td>
                                <?php if ($s['scanned_file']): ?>
                                    <a href="../../uploads/contracts/<?= $s['scanned_file'] ?>" target="_blank">View File</a>
                                <?php else: ?>
                                    N/A
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No signatures found.</p>
        <?php endif; ?>
    </div>

    <a href="contract_list.php" class="btn btn-secondary">Back to Contract List</a>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
