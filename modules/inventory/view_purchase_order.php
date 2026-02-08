<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../classes/InventorySystem.php';

$inventorySystem = new InventorySystem($pdo);

if (isset($_GET['id'])) {
    $orderId = $_GET['id'];
    $purchaseOrder = $inventorySystem->getPurchaseOrderById($orderId);
    if (!$purchaseOrder) {
        echo "Purchase order not found.";
        exit;
    }
} else {
    echo "No purchase order ID provided.";
    exit;
}

?>

<div class="container mt-5">
    <h3 class="mb-4">Purchase Order Details</h3>

    <p><strong>Order Date:</strong> <?= htmlspecialchars($purchaseOrder['order_date']) ?></p>
    <p><strong>Supplier:</strong>
        <?php
        $supplier = $inventorySystem->getSupplierById($purchaseOrder['supplier_id']);
        echo htmlspecialchars($supplier['name']);
        ?>
    </p>
    <p><strong>Purpose:</strong> <?= htmlspecialchars($purchaseOrder['purpose']) ?></p>
    <p><strong>Status:</strong> <?= htmlspecialchars(ucfirst($purchaseOrder['status'])) ?></p>

    <h4>Items:</h4>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Item Name</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>Total Price</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $items = $inventorySystem->getPurchaseOrderItems($purchaseOrder['id']);
            foreach ($items as $item){
                $stockItem = $inventorySystem->getStockItemById($item['item_id']);
                echo "<tr>
                    <td>" . htmlspecialchars($stockItem['item_name']) . "</td>
                    <td>" . number_format($item['quantity'],2) . "</td>
                    <td>" . number_format($item['price'],2) . "</td>
                      <td>" . number_format($item['quantity'] * $item['price'],2) . "</td>
                </tr>";
            }
            ?>
        </tbody>
    </table>

    <a href="purchase_orders.php" class="btn btn-secondary">Back to List</a>




</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
