<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../classes/InventorySystem.php';
require_once __DIR__ . '/../../classes/AccountingSystem.php';
require_once __DIR__ . '/../../classes/LoanSystem.php';

$inventorySystem = new InventorySystem($pdo);
$accountingSystem = new AccountingSystem($pdo);
$LoanSystem = new LoanSystem($pdo);

$accounts = $pdo->query("SELECT id, account_code, account_name
                        FROM chart_of_accounts
                        WHERE is_active = TRUE
                        ORDER BY account_code")->fetchAll();

$poId = (int) ($_GET['id'] ?? 0);
$purchaseOrder = $inventorySystem->getPurchaseOrderById($poId);
$poItems = $inventorySystem->getPurchaseOrderItems($poId);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $receivedItems = $_POST['received'] ?? [];
    $deliveryNote = null;
    $total = $_POST['total'];
    $account = $_POST['account'];
    $supplier = $_POST['supplier'];

    if (isset($_FILES['delivery_note']) && $_FILES['delivery_note']['error'] == 0) {
        $uploadDir = __DIR__ . '/../../uploads/delivery_notes/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $filename = time() . '_' . basename($_FILES['delivery_note']['name']);
        $filepath = $uploadDir . $filename;
        if (move_uploaded_file($_FILES['delivery_note']['tmp_name'], $filepath)) {
            $deliveryNote = 'uploads/delivery_notes/' . $filename;
        }
    }

    foreach ($receivedItems as $itemId => $qty) {
        $qty = (float)$qty;
        if ($qty <= 0) continue;

        $itemType = $_POST['item_type'][$itemId];

        if ($itemType === 'inventory') {
            $inventorySystem->recordStockMovement([
                'item_id' => $itemId,
                'date' => date('Y-m-d'),
                'quantity_in' => $qty,
                'quantity_out' => 0,
                'reference_type' => 'PurchaseOrder',
                'reference_id' => $poId,
                'remarks' => 'PO Receiving',
            ]);
        }

        if ($itemType === 'fixed') {
            $usefulLife = (int)$_POST['fixed_useful_life'][$itemId];
            $method = $_POST['fixed_method'][$itemId];
            $salvage = (float)$_POST['fixed_salvage'][$itemId];
            $category = $_POST['fixed_category'][$itemId];

            foreach ($poItems as $poItem) {
                if ($poItem['item_id'] == $itemId) {
                    $assetName = $poItem['item_name'];
                    $cost = $poItem['price'] * $qty;
                    break;
                }
            }

            $stmt = $pdo->prepare("INSERT INTO fixed_assets (asset_name, category, purchase_date, cost, useful_life, depreciation_method, salvage_value, created_at)
                                   VALUES (:name, :category, :purchase_date, :cost, :life, :method, :salvage, NOW())");
            $stmt->execute([
                'name' => $assetName,
                'category' => $category,
                'purchase_date' => date('Y-m-d'),
                'cost' => $cost,
                'life' => $usefulLife,
                'method' => $method,
                'salvage' => $salvage
            ]);
        }
    }

    $user = $LoanSystem->getAccountDetails($supplier);
    $id = $user['id'];

    $header = [
        'transaction_date' => date('Y-m-d'),
        'reference' => date('Ymdhis'),
        'description' => 'Receiving purchase order',
        'created_by' => $_SESSION['user_id'],
        'purchase_order' => $poId,
        'trx_type' => 'goods'
    ];

    $lines = [
        [ 'account_id' => $account, 'debit' => $total, 'credit' => 0 ],
        [ 'account_id' => $id, 'debit' => 0, 'credit' => $total ]
    ];

    $transactionId = $accountingSystem->createJournalEntry($header, $lines);
    $accountingSystem->postJournalEntry($transactionId, $_SESSION['user_id']);

    $inventorySystem->markPurchaseOrderAsReceived($poId, $deliveryNote);

    $_SESSION['toast'] = "Purchase order received successfully!";
    echo "<script>window.location='purchase_orders.php'</script>";
    exit;
}
?>

<div class="container mt-5">
    <h3 class="mb-4">Receive Purchase Order</h3>
    <div class="card p-4">
        <h5>Supplier: <?= htmlspecialchars($purchaseOrder['supplier_name']) ?></h5>
        <p>Purpose: <?= htmlspecialchars($purchaseOrder['purpose']) ?></p>
        <form method="post" enctype="multipart/form-data">
            <table class="table table-bordered mt-3">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Ordered Qty</th>
                        <th>Receive Qty</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $total = 0; ?>
                    <?php foreach ($poItems as $item): ?>
                        <?php $total += $item['quantity'] * $item['price']; ?>
                        <tr>
                            <td>
                                <?= htmlspecialchars($item['item_name']) ?>
                                <input type="hidden" name="item_type[<?= $item['item_id'] ?>]" value="<?= htmlspecialchars($item['itemtype']) ?>">
                            </td>
                            <td><?= $item['quantity'] ?></td>
                            <td>
                                <input type="number" name="received[<?= $item['item_id'] ?>]" class="form-control" min="0" max="<?= $item['quantity'] ?>" value="<?= $item['quantity'] ?>" required>
                            </td>
                        </tr>

                        <?php if ($item['itemtype'] === 'fixed'): ?>
                        <tr>
                            <td colspan="3">
                                <div class="border p-3 bg-light">
                                    <strong>Fixed Asset Info for <?= htmlspecialchars($item['item_name']) ?>:</strong>
                                    <div class="row mt-2">
                                        <div class="col-md-6">
                                            <label>Useful Life (years)</label>
                                            <input type="number" class="form-control" name="fixed_useful_life[<?= $item['item_id'] ?>]" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label>Depreciation Method</label>
                                            <select class="form-control" name="fixed_method[<?= $item['item_id'] ?>]" required>
                                                <option value="straight_line">Straight Line</option>
                                                <option value="reducing_balance">Reducing Balance</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mt-2">
                                            <label>Salvage Value</label>
                                            <input type="number" class="form-control" name="fixed_salvage[<?= $item['item_id'] ?>]" required>
                                        </div>
                                        <div class="col-md-6 mt-2">
                                            <label>Category</label>
                                            <input type="text" class="form-control" name="fixed_category[<?= $item['item_id'] ?>]" required>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="mb-3">
                <label for="delivery_note" class="form-label">Upload Delivery Note (optional)</label>
                <input type="file" name="delivery_note" id="delivery_note" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
            </div>

            <input type="hidden" name="total" value="<?= $total ?>">
            <input type="hidden" name="supplier" value="<?= $purchaseOrder['account_number'] ?>">

            <p>Select Account to debit</p>
            <select class="form-control" name="account" required>
                <option value="">Select Account</option>
                <?php foreach ($accounts as $account): ?>
                    <option value="<?= $account['id'] ?>">
                        <?= $account['account_code'] ?> - <?= $account['account_name'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <br>
            <button type="submit" class="btn btn-primary">Confirm and Receive</button>
            <a href="purchase_orders.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
