<?php
require_once __DIR__ . '/../..//includes/header.php';
require_once __DIR__ . '/../..//classes/InventorySystem.php';
$stockMovements = $inventorySystem->listStockMovements();
?>

<div class="container mt-5">
    <h3 class="mb-4">Stock Movement History</h3>

    <!-- Stock Movements Table -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Item</th>
                <th>Movement Type</th>
                <th>Quantity</th>
                <th>Date</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($stockMovements as $movement): ?>
                <tr>
                    <td><?= htmlspecialchars($movement['item_name']) ?></td>
                    <td><?= htmlspecialchars($movement['movement_type']) ?></td>
                    <td><?= htmlspecialchars($movement['quantity']) ?></td>
                    <td><?= htmlspecialchars($movement['movement_date']) ?></td>
                    <td><?= htmlspecialchars($movement['remarks']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php
require_once __DIR__ . '/../..//includes/footer.php';?>
