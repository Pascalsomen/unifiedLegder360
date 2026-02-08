<?php
require_once __DIR__ . '/../../includes/header.php';


if (!isset($_GET['contract_id'])) {
    echo "<div class='alert alert-danger'>Contract ID missing.</div>";
    exit;
}

$contract_id = (int) $_GET['contract_id'];

// Fetch contract
$contract = $pdo->prepare("SELECT contract_number, contract_title FROM contracts WHERE contract_id = ?");
$contract->execute([$contract_id]);
$contract = $contract->fetch(PDO::FETCH_ASSOC);
if (!$contract) {
    echo "<div class='alert alert-danger'>Contract not found.</div>";
    exit;
}

// Fetch items and chart of accounts
$items = $pdo->query("SELECT id as item_id, item_name FROM stock_items ORDER BY item_name")->fetchAll(PDO::FETCH_ASSOC);
$accounts = $pdo->query("SELECT id as account_id, account_name FROM chart_of_accounts ORDER BY account_name")->fetchAll(PDO::FETCH_ASSOC);

// Handle form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category = $_POST['category'];
    $item_id = $_POST['item_id'];
    $unit_price = $_POST['unit_price'];
    $quantity = $_POST['quantity'];
    $chart_account_id = $_POST['chart_account_id'];

    if ($category && $item_id && $unit_price && $quantity && $chart_account_id) {
        $stmt = $pdo->prepare("
            INSERT INTO contract_items (contract_id, item_id, item_category, unit_price, quantity, chart_account_id)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$contract_id, $item_id, $category, $unit_price, $quantity, $chart_account_id]);
        $success = "Item added successfully.";
    } else {
        $error = "Please fill all required fields.";
    }
}

// Fetch added items
$itemsStmt = $pdo->prepare("
    SELECT ci.*, i.item_name, a.account_name
    FROM contract_items ci
    JOIN stock_items i ON ci.item_id = i.id
    JOIN chart_of_accounts a ON ci.chart_account_id = a.id
    WHERE ci.contract_id = ?
    ORDER BY ci.item_id
");
$itemsStmt->execute([$contract_id]);
$contractItems = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid mt-4">
    <h4 class="mb-3">Add Contract Items</h4>
    <p><strong>Contract #<?= htmlspecialchars($contract['contract_number']) ?>:</strong> <?= htmlspecialchars($contract['contract_title']) ?></p>

    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php elseif (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" class="card p-4 mb-4">
        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">Category</label>
                <select name="category" class="form-select" required>
                    <option value="">-- Select Category --</option>
                    <option value="Fixed">Fixed</option>
                    <option value="Inventory">Inventory</option>
                    <option value="Service">Service</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Item</label>
                <select name="item_id" class="form-select" required>
                    <option value="">-- Select Item --</option>
                    <?php foreach ($items as $item): ?>
                        <option value="<?= $item['item_id'] ?>"><?= htmlspecialchars($item['item_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Chart of Account</label>
                <select name="chart_account_id" class="form-select" required>
                    <option value="">-- Select Account --</option>
                    <?php foreach ($accounts as $acc): ?>
                        <option value="<?= $acc['account_id'] ?>"><?= htmlspecialchars($acc['account_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">Unit Price</label>
                <input type="number" name="unit_price" step="0.01" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Quantity</label>
                <input type="number" name="quantity" class="form-control" required>
            </div>
        </div>

        <button type="submit" class="btn btn-success">Add Item</button>
        <a href="add_signature.php?contract_id=<?= $contract_id ?>" class="btn btn-primary float-end">Next: Add Signatures</a>
    </form>

    <?php if ($contractItems): ?>
        <h5 class="mb-3">Items Added</h5>
        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>Category</th>
                    <th>Item</th>
                    <th>Unit Price</th>
                    <th>Quantity</th>
                    <th>Chart of Account</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($contractItems as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['item_category']) ?></td>
                        <td><?= htmlspecialchars($item['item_name']) ?></td>
                        <td><?= number_format($item['unit_price'], 2) ?></td>
                        <td><?= $item['quantity'] ?></td>
                        <td><?= htmlspecialchars($item['account_name']) ?></td>
                        <td><?= number_format($item['unit_price'] * $item['quantity'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
