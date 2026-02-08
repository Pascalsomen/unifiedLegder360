<?php
require_once __DIR__ . '/../../includes/header.php';

$contract_id = $_GET['contract_id'] ?? null;
if (!$contract_id) {
    echo "<div class='alert alert-danger'>Missing contract ID.</div>";
    exit;
}

// Fetch data
$items = $pdo->query("SELECT id as item_id, item_name FROM stock_items")->fetchAll(PDO::FETCH_ASSOC);
$accounts = $pdo->query("SELECT id as account_id, account_name FROM chart_of_accounts")->fetchAll(PDO::FETCH_ASSOC);

// Add item
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_item'])) {
    $item_type = $_POST['item_type'];
    $item_id = $_POST['item_id'];
    $quantity = $_POST['quantity'];
    $unit_price = $_POST['unit_price'];
    $account_id = $_POST['account_id'];

    $stmt = $pdo->prepare("INSERT INTO contract_items (contract_id, item_category, item_id, quantity, unit_price, chart_account_id) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$contract_id, $item_type, $item_id, $quantity, $unit_price, $account_id]);
}

// Update items
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_items'])) {
    foreach ($_POST['item_ids'] as $index => $item_id_row) {
        $type = $_POST['types'][$index];
        $item_id = $_POST['item_ids_field'][$index];
        $qty = $_POST['quantities'][$index];
        $price = $_POST['prices'][$index];
        $acct = $_POST['accounts'][$index];

        $stmt = $pdo->prepare("UPDATE contract_items SET item_category = ?, item_id = ?, quantity = ?, unit_price = ?, chart_account_id = ? WHERE item_id = ?");
        $stmt->execute([$type, $item_id, $qty, $price, $acct, $item_id_row]);
    }
}

// Delete item
if (isset($_GET['delete_item'])) {
    $id = $_GET['delete_item'];
    $pdo->prepare("DELETE FROM contract_items WHERE item_id = ?")->execute([$id]);
}

// Load current items
$stmt = $pdo->prepare("SELECT ci.*, i.item_name, a.account_name
                       FROM contract_items ci
                       LEFT JOIN stock_items i ON ci.item_id = i.id
                       LEFT JOIN chart_of_accounts a ON ci.chart_account_id = a.id
                       WHERE ci.contract_id = ?");
$stmt->execute([$contract_id]);
$contract_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">
    <h3>Edit Contract Items <a href="edit_contract.php?contract_id=<?php echo $contract_id ?>" class="btn btn-info">Edit contract headers</a> <a href="edit_articles.php?contract_id=<?php echo $contract_id ?>" class="btn btn-info">Edit Articles</a> <a href="edit_signature.php?contract_id=<?php echo $contract_id ?>" class="btn btn-info">Edit Signature</a></h3>

    <form method="post">
        <input type="hidden" name="update_items" value="1">
        <?php foreach ($contract_items as $item): ?>
            <div class="card p-3 mb-3">
                <input type="hidden" name="item_ids[]" value="<?= $item['item_id'] ?>">
                <div class="row">
                    <div class="col-md-2">
                        <label>Type</label>
                        <select name="types[]" class="form-control" required>
                            <option <?= $item['item_category'] == 'Fixed' ? 'selected' : '' ?>>Fixed</option>
                            <option <?= $item['item_category'] == 'Inventory' ? 'selected' : '' ?>>Inventory</option>
                            <option <?= $item['item_category'] == 'Service' ? 'selected' : '' ?>>Service</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Item</label>
                        <select name="item_ids_field[]" class="form-control" required>
                            <?php foreach ($items as $it): ?>
                                <option value="<?= $it['item_id'] ?>" <?= $item['item_id'] == $it['item_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($it['item_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <label>Qty</label>
                        <input type="number" name="quantities[]" class="form-control" value="<?= $item['quantity'] ?>" required>
                    </div>
                    <div class="col-md-2">
                        <label>Unit Price</label>
                        <input type="number" step="0.01" name="prices[]" class="form-control" value="<?= $item['unit_price'] ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label>Account</label>
                        <select name="accounts[]" class="form-control" required>
                            <?php foreach ($accounts as $acct): ?>
                                <option value="<?= $acct['account_id'] ?>" <?= $item['chart_account_id'] == $acct['account_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($acct['account_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <label>Action</label><br>
                        <a href="?contract_id=<?= $contract_id ?>&delete_item=<?= $item['item_id'] ?>" class="btn btn-danger btn-sm"
                           onclick="return confirm('Delete item?')">X</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        <button type="submit" class="btn btn-primary">Update Items</button>
    </form>

    <hr>

    <h4>Add New Item</h4>
    <form method="post">
        <input type="hidden" name="new_item" value="1">
        <div class="row">
            <div class="col-md-2">
                <label>Type</label>
                <select name="item_type" class="form-control" required>
                    <option value="Fixed">Fixed</option>
                    <option value="Inventory">Inventory</option>
                    <option value="Service">Service</option>
                </select>
            </div>
            <div class="col-md-3">
                <label>Item</label>
                <select name="item_id" class="form-control" required>
                    <?php foreach ($items as $it): ?>
                        <option value="<?= $it['item_id'] ?>"><?= htmlspecialchars($it['item_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-1">
                <label>Qty</label>
                <input type="number" name="quantity" class="form-control" required>
            </div>
            <div class="col-md-2">
                <label>Unit Price</label>
                <input type="number" step="0.01" name="unit_price" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label>Account</label>
                <select name="account_id" class="form-control" required>
                    <?php foreach ($accounts as $acct): ?>
                        <option value="<?= $acct['account_id'] ?>"><?= htmlspecialchars($acct['account_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" class="btn btn-success">Add</button>
            </div>
        </div>
    </form>

    <a href="contract_list.php" class="btn btn-secondary mt-4">Back to Contract List</a>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
