<?php
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/classes/InventorySystem.php';

$inventorySystem = new InventorySystem($pdo);

// Ensure purchase_order_id is provided and is valid
if (isset($_GET['purchase_order_id']) && is_numeric($_GET['purchase_order_id'])) {
    $purchaseOrderId = $_GET['purchase_order_id'];

    // Fetch items in the selected purchase order
    $items = $inventorySystem->getPurchaseOrderItems($purchaseOrderId);

    // Return items in JSON format
    echo json_encode($items);
} else {
    // Return empty array if purchase_order_id is not provided or invalid
    echo json_encode([]);
}
