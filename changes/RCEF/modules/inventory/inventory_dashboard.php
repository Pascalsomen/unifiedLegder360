<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../classes/Inventory.php';
require_once __DIR__ . '/../../classes/InventoryCategory.php';

if (!hasPermission('inventory')) {
    redirect('/index.php');
}

$inventorySystem = new Inventory($pdo);
$categorySystem = new InventoryCategory($pdo);

// Get summary data
$totalItems = $inventorySystem->getTotalItemCount();
$lowStockItems = $inventorySystem->getLowStockItems();
$inventoryValue = $inventorySystem->getTotalInventoryValue();
$recentMovements = $inventorySystem->getRecentMovements(10);
$categories = $inventorySystem->getCategories();
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2>Inventory Dashboard</h2>
                <?php if (hasPermission('manage_inventory')): ?>
                    <div>
                        <a href="items.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add New Item
                        </a>
                        <a href="create_adjustment.php" class="btn btn-warning">
                            <i class="fas fa-adjust"></i> Create Adjustment
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Items</h5>
                    <h2 class="card-text"><?= number_format($totalItems) ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h5 class="card-title">Low Stock Items</h5>
                    <h2 class="card-text"><?= number_format(count($lowStockItems)) ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Value</h5>
                    <h2 class="card-text">RWF <?= number_format($inventoryValue, 2) ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Categories</h5>
                    <h2 class="card-text"><?= number_format(count($categories)) ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Low Stock Items -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4>Low Stock Items</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($lowStockItems)): ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Current Stock</th>
                                        <th>Reorder Level</th>
                                        <th>Category</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($lowStockItems as $item): ?>
                                        <tr>
                                            <td>
                                                <a href="view_item.php?id=<?= $item['id'] ?>">
                                                    <?= htmlspecialchars($item['item_code']) ?> - <?= htmlspecialchars($item['name']) ?>
                                                </a>
                                            </td>
                                            <td class="<?= $item['current_quantity'] <= 0 ? 'text-danger' : 'text-warning' ?>">
                                                <?= number_format($item['current_quantity']) ?> <?= $item['unit_of_measure'] ?>
                                            </td>
                                            <td><?= number_format($item['reorder_level']) ?></td>
                                            <td><?= htmlspecialchars($item['category_name'] ?? '-') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-success">
                            No low stock items at this time.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Movements -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4>Recent Inventory Movements</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Item</th>
                                    <th>Type</th>
                                    <th>Qty</th>
                                    <th>User</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentMovements as $movement): ?>
                                    <tr>
                                        <td><?= date('m/d/Y', strtotime($movement['date_time'])) ?></td>
                                        <td><?= htmlspecialchars($movement['item_code']) ?></td>
                                        <td>
                                            <span class="badge bg-<?=
                                                $movement['movement_type'] === 'purchase' ? 'success' :
                                                ($movement['movement_type'] === 'sale' ? 'danger' : 'info') ?>">
                                                <?= ucfirst($movement['movement_type']) ?>
                                            </span>
                                        </td>
                                        <td class="<?= $movement['quantity'] > 0 ? 'text-success' : 'text-danger' ?>">
                                            <?= ($movement['quantity'] > 0 ? '+' : '') . number_format($movement['quantity']) ?>
                                        </td>
                                        <td><?= htmlspecialchars($movement['user_name']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>