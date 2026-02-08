<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../classes/Inventory.php';
require_once __DIR__ . '/../../classes/InventoryMovement.php';

if (!hasPermission('view_inventory')) {
    redirect('/index.php');
}

$inventorySystem = new Inventory($pdo);
$movementSystem = new InventoryMovement($pdo);

$itemId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($itemId <= 0) {
    redirect('/modules/inventory/inventory_dashboard.php');
}

$item = $inventorySystem->getItem($itemId);
if (!$item) {
    $_SESSION['error'] = "Item not found";
    redirect('/modules/inventory/inventory_dashboard.php');
}

// Get item movement history
$movements = $movementSystem->getItemMovements($itemId, null, null, null, 50);

// Get item quantities by location
$locationQuantities = $inventorySystem->getItemQuantitiesByLocation($itemId);
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2>
                    <?= htmlspecialchars($item['item_code']) ?> - <?= htmlspecialchars($item['item_name']) ?>
                    <span class="badge bg-<?= $item['is_active'] ? 'success' : 'secondary' ?>">
                        <?= $item['is_active'] ? 'Active' : 'Inactive' ?>
                    </span>
                </h2>
                <div>
                    <?php if (hasPermission('manage_inventory')): ?>
                        <a href="edit_item.php?id=<?= $itemId ?>" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                    <?php endif; ?>
                    <a href="inventory_dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Inventory
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Item Details -->
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h4>Item Details</h4>
                </div>
                <div class="card-body">
                    <?php if ($item['image_path']): ?>
                        <div class="text-center mb-3">
                            <img src="<?= htmlspecialchars($item['image_path']) ?>" alt="Item Image" class="img-fluid" style="max-height: 200px;">
                        </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <h6>Description</h6>
                        <p><?= nl2br(htmlspecialchars($item['description'] ?? 'No description available')) ?></p>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6>Category</h6>
                            <p><?= htmlspecialchars($item['category_name'] ?? '-') ?></p>
                        </div>
                        <div class="col-md-6">
                            <h6>Supplier</h6>
                            <p><?= htmlspecialchars($item['supplier_name'] ?? '-') ?></p>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6>Unit of Measure</h6>
                            <p><?= htmlspecialchars($item['unit_of_measure']) ?></p>
                        </div>
                        <div class="col-md-6">
                            <h6>Reorder Level</h6>
                            <p><?= number_format($item['reorder_level']) ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stock Levels -->
            <div class="card">
                <div class="card-header">
                    <h4>Stock Information</h4>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6>Current Stock</h6>
                        <h3 class="<?=
                            $item['quantity_on_hand'] <= 0 ? 'text-danger' :
                            ($item['quantity_on_hand'] <= $item['reorder_level'] ? 'text-warning' : 'text-success') ?>">
                            <?= number_format($item['quantity_on_hand']) ?> <?= $item['unit_of_measure'] ?>
                        </h3>
                    </div>

                    <?php if (!empty($locationQuantities)): ?>
                        <div class="mb-3">
                            <h6>Stock by Location</h6>
                            <ul class="list-group">
                                <?php foreach ($locationQuantities as $location): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <?= htmlspecialchars($location['location_name']) ?>
                                        <span class="badge bg-primary rounded-pill">
                                            <?= number_format($location['quantity']) ?>
                                        </span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-md-6">
                            <h6>Cost Price</h6>
                            <p>$<?= number_format($item['cost_price'], 2) ?></p>
                        </div>
                        <div class="col-md-6">
                            <h6>Selling Price</h6>
                            <p>$<?= number_format($item['selling_price'], 2) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Movement History -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Movement History</h4>
                        <div>
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#newMovementModal">
                                <i class="fas fa-plus"></i> New Movement
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Reference</th>
                                    <th>Quantity</th>
                                    <th>Location</th>
                                    <th>User</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($movements as $movement): ?>
                                    <tr>
                                        <td><?= date('m/d/Y H:i', strtotime($movement['date_time'])) ?></td>
                                        <td>
                                            <span class="badge bg-<?=
                                                $movement['movement_type'] === 'purchase' ? 'success' :
                                                ($movement['movement_type'] === 'sale' ? 'danger' : 'info') ?>">
                                                <?= ucfirst($movement['movement_type']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($movement['reference_id']): ?>
                                                <a href="#" class="view-reference"
                                                   data-type="<?= $movement['reference_type'] ?>"
                                                   data-id="<?= $movement['reference_id'] ?>">
                                                    <?= strtoupper($movement['reference_type']) ?> #<?= $movement['reference_id'] ?>
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                        <td class="<?= $movement['quantity'] > 0 ? 'text-success' : 'text-danger' ?>">
                                            <?= ($movement['quantity'] > 0 ? '+' : '') . number_format($movement['quantity']) ?>
                                        </td>
                                        <td><?= htmlspecialchars($movement['location_name'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($movement['user_name']) ?></td>
                                        <td><?= htmlspecialchars($movement['notes']) ?></td>
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

<!-- New Movement Modal -->
<div class="modal fade" id="newMovementModal" tabindex="-1" aria-labelledby="newMovementModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newMovementModalLabel">Record New Movement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="process_movement.php">
                <input type="hidden" name="item_id" value="<?= $itemId ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Movement Type*</label>
                        <select class="form-select" name="movement_type" required>
                            <option value="">Select Type</option>
                            <option value="purchase">Purchase</option>
                            <option value="sale">Sale</option>
                            <option value="adjustment">Adjustment</option>
                            <option value="transfer">Transfer</option>
                            <option value="return">Return</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Quantity*</label>
                        <input type="number" class="form-control" name="quantity" step="0.01" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Location</label>
                        <select class="form-select" name="location_id">
                            <option value="">Main Warehouse</option>
                            <?php foreach ($locationQuantities as $location): ?>
                                <option value="<?= $location['location_id'] ?>">
                                    <?= htmlspecialchars($location['location_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Reference (optional)</label>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <select class="form-select" name="reference_type">
                                    <option value="">Select Reference Type</option>
                                    <option value="purchase_order">Purchase Order</option>
                                    <option value="sale">Sale</option>
                                    <option value="adjustment">Adjustment</option>
                                    <option value="transfer">Transfer</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <input type="number" class="form-control" name="reference_id" placeholder="Reference ID">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" name="notes" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Record Movement</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // View reference details
    $(document).on('click', '.view-reference', function(e) {
        e.preventDefault();
        const type = $(this).data('type');
        const id = $(this).data('id');

        // Determine the appropriate view page based on reference type
        let url = '';
        switch(type) {
            case 'purchase_order':
                url = '/modules/purchasing/view_order.php?id=' + id;
                break;
            case 'sale':
                url = '/modules/sales/view_sale.php?id=' + id;
                break;
            case 'adjustment':
                url = '/modules/inventory/view_adjustment.php?id=' + id;
                break;
            case 'transfer':
                url = '/modules/inventory/view_transfer.php?id=' + id;
                break;
            default:
                alert('View details not available for this type');
                return;
        }

        window.open(url, '_blank');
    });
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>