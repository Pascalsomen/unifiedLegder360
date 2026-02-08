<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../classes/InventorySystem.php';
$inventorySystem = new InventorySystem($pdo);

// Fetch list of available items from the stock_items table
$items = $inventorySystem->listStockItems(); // Adjust the method name accordingly

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_purchase_order'])) {
        // Get form data
        $poData = [
            'supplier_id' => $_POST['supplier_id'],
            'order_date' => $_POST['order_date'],
            'created_by' => $_POST['created_by'],
            'purpose' => $_POST['purpose']
        ];

        // Collect the items and their quantities
        $items = [];
        foreach ($_POST['item_id'] as $index => $itemId) {
            $items[] = [
                'item_id' => $itemId,
                'quantity' => $_POST['quantity'][$index]
            ];
        }

        // Create the purchase order
        $poId = $inventorySystem->createPurchaseOrder($poData, $items);

        // Redirect to view the created purchase order
        header("Location: purchase_order_view.php?id=$poId");
        exit;
    }
}
?>

<div class="container mt-5">
    <h3 class="mb-4">Create Purchase Order</h3>

    <form method="post">
        <div class="mb-3">
            <label for="supplier_id" class="form-label">Supplier</label>
            <select class="form-select" id="supplier_id" name="supplier_id" required>
                <option value="">Select Supplier</option>
                <?php foreach ($suppliers as $supplier): ?>
                    <option value="<?= $supplier['id'] ?>"><?= htmlspecialchars($supplier['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="order_date" class="form-label">Order Date</label>
            <input type="date" class="form-control" id="order_date" name="order_date" value="<?= date('Y-m-d') ?>" required>
        </div>

        <div class="mb-3">
            <label for="purpose" class="form-label">Purpose</label>
            <textarea class="form-control" id="purpose" name="purpose" rows="3" required></textarea>
        </div>

        <div id="items">
            <!-- Dynamically load items here -->
            <div class="mb-3">
                <label class="form-label">Item</label>
                <select class="form-select" name="item_id[]" required>
                    <option value="">Select Item</option>
                    <?php foreach ($items as $item): ?>
                        <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <label class="form-label">Quantity</label>
                <input type="number" class="form-control" name="quantity[]" value="1" min="1" required>
            </div>
        </div>

        <button type="button" class="btn btn-secondary" id="add_item">Add Another Item</button>
        <button type="submit" name="create_purchase_order" class="btn btn-primary mt-3">Create Purchase Order</button>
    </form>
</div>



<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
<script>
    // Function to add a new item entry dynamically
    document.getElementById('addItemButton').addEventListener('click', function() {
        const itemsContainer = document.getElementById('items-container');

        const newItemDiv = document.createElement('div');
        newItemDiv.classList.add('item-entry', 'mb-3');

        newItemDiv.innerHTML = `
            <label for="item_id" class="form-label">Item</label>
            <select class="form-select" name="item_id[]" required>
                <option value="">Select Item</option>
                <?php foreach ($stockItems as $item): ?>
                    <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['item_name']) ?></option>
                <?php endforeach; ?>
            </select>
            <label for="quantity" class="form-label">Quantity</label>
            <input type="number" class="form-control" name="quantity[]" min="1" required>
        `;

        itemsContainer.appendChild(newItemDiv);
    });
</script>