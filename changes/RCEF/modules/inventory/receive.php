<?php
// Include necessary files and initialize the inventory system class
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/classes/InventorySystem.php';
$inventorySystem = new InventorySystem($pdo);

// Handle receiving items
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['receive_items'])) {
        // Handle item receiving
        $purchaseOrderId = $_POST['purchase_order_id'];
        $receivedItems = $_POST['received_items']; // An array of received items with quantity

        foreach ($receivedItems as $itemId => $quantity) {
            // Update stock and record stock movement for each received item
            $inventorySystem->receiveItem($purchaseOrderId, $itemId, $quantity);
        }

        // Redirect after receiving items
        header("Location: receive_items.php?success=true");
        exit;
    }
}

// Fetch pending purchase orders that need to be received
$pendingPurchaseOrders = $inventorySystem->getPendingPurchaseOrders();

?>

<div class="container mt-5">
    <h3 class="mb-4">Receive Items</h3>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">
            Items received successfully and stock updated.
        </div>
    <?php endif; ?>

    <!-- List of pending purchase orders to receive -->
    <form method="post">
        <div class="mb-3">
            <label for="purchase_order_id" class="form-label">Select Purchase Order</label>
            <select class="form-select" id="purchase_order_id" name="purchase_order_id" required>
                <option value="">Select Purchase Order</option>
                <?php foreach ($pendingPurchaseOrders as $purchaseOrder): ?>
                    <option value="<?= $purchaseOrder['id'] ?>">
                        Order #<?= $purchaseOrder['id'] ?> (<?= htmlspecialchars($purchaseOrder['supplier_name']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Receive Items Form (Dynamic based on selected Purchase Order) -->
        <div id="items_to_receive" class="mb-3">
            <!-- Items will be dynamically loaded here based on the selected purchase order -->
        </div>

        <button type="submit" name="receive_items" class="btn btn-primary">Receive Items</button>
    </form>
</div>

<script>
    // Load items dynamically based on selected Purchase Order
    document.getElementById('purchase_order_id').addEventListener('change', function() {
        const purchaseOrderId = this.value;

        if (purchaseOrderId) {
            // Fetch items for the selected purchase order
            fetch(`get_purchase_order_items.php?purchase_order_id=${purchaseOrderId}`)
                .then(response => response.json())
                .then(data => {
                    const itemsContainer = document.getElementById('items_to_receive');
                    itemsContainer.innerHTML = ''; // Clear previous items

                    // Loop through the items and create input fields
                    data.forEach(item => {
                        const itemDiv = document.createElement('div');
                        itemDiv.classList.add('mb-3');
                        itemDiv.innerHTML = `
                            <label class="form-label">${item.item_name}</label>
                            <input type="number" class="form-control" name="received_items[${item.item_id}]" value="0" min="0" required>
                        `;
                        itemsContainer.appendChild(itemDiv);
                    });
                });
        }
    });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
