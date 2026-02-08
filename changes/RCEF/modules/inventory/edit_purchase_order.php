<?php
require_once '../../includes/header.php';
require_once '../../classes/InventorySystem.php';

$inventory = new InventorySystem($pdo);

$poId = (int)($_GET['id'] ?? 0);
$purchaseOrder = $inventory->getPurchaseOrderById($poId);
$items = $inventory->getPurchaseOrderItems($poId);
$suppliers = $inventory->listSuppliers();
$stockItems = $inventory->listStockItems();

if (!$purchaseOrder) {
    echo "Purchase Order not found!";
    exit;
}

if (isset($_POST['update_purchase_order'])) {
    $poData = [
        'supplier_id' => $_POST['supplier_id'],
        'order_date' => $_POST['order_date'],
        'purpose' => $_POST['purpose']
    ];

    $newItems = [];
    foreach ($_POST['item_id'] as $index => $itemId) {
        $newItems[] = [
            'item_id' => $itemId,
            'quantity' => $_POST['quantity'][$index]
        ];
    }

    $id = $_REQUEST['id'];
    $inventory->updatePurchaseOrder($poId, $poData, $newItems);
    echo "<script>window.location='view_purchase_order.php?id=$id'</script>";
    exit;
}
?>

<div class="container mt-5">
    <h3>Edit Purchase Order</h3>
    <form method="post">
        <div class="mb-3">
            <label>Supplier</label>
            <select name="supplier_id" class="form-select" required>
                <option value="">Select Supplier</option>
                <?php foreach ($suppliers as $supplier): ?>
                    <option value="<?= $supplier['id'] ?>" <?= $supplier['id'] == $purchaseOrder['supplier_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($supplier['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label>Order Date</label>
            <input type="date" name="order_date" class="form-control" value="<?= htmlspecialchars($purchaseOrder['order_date']) ?>" required>
        </div>

        <div class="mb-3">
            <label>Purpose / Remarks</label>
            <textarea name="purpose" class="form-control" required><?= htmlspecialchars($purchaseOrder['purpose']) ?></textarea>
        </div>

        <h5>Items</h5>
        <div id="items-section">
            <?php foreach ($items as $index => $item): ?>
                <div class="row g-2 mb-2">
                    <div class="col-md-6">
                        <select name="item_id[]" class="form-select" required>
                            <option value="">Select Item</option>
                            <?php foreach ($stockItems as $stockItem): ?>
                                <option value="<?= $stockItem['id'] ?>" <?= $stockItem['id'] == $item['item_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($stockItem['item_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="number" name="quantity[]" class="form-control" value="<?= $item['quantity'] ?>" required>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger remove-item">X</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <button type="button" class="btn btn-secondary my-2" id="addItem">Add Item</button>

        <div class="mt-3">
            <button type="submit" name="update_purchase_order" class="btn btn-primary">Update Purchase Order</button>
        </div>
    </form>
</div>

<script>
document.getElementById('addItem').addEventListener('click', function () {
    const section = document.getElementById('items-section');
    const newRow = document.createElement('div');
    newRow.className = 'row g-2 mb-2';
    newRow.innerHTML = `
        <div class="col-md-6">
            <select name="item_id[]" class="form-select" required>
                <option value="">Select Item</option>
                <?php foreach ($stockItems as $item): ?>
                    <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['item_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <input type="number" name="quantity[]" class="form-control" required>
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-danger remove-item">X</button>
        </div>
    `;
    section.appendChild(newRow);
});

document.addEventListener('click', function (e) {
    if (e.target.classList.contains('remove-item')) {
        e.target.closest('.row').remove();
    }
});
</script>

<?php require_once '../../includes/footer.php'; ?>
