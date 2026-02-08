<?php
require_once __DIR__ . '/../../includes/header.php';

if (!hasPermission('inventory_view')) {
    redirect('/index.php');
}

$itemId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($itemId <= 0) {
    $_SESSION['error'] = "Invalid item ID";
    redirect('/modules/inventory/items.php');
}

// Get item details
$stmt = $pdo->prepare("SELECT * FROM inventory_items WHERE id = ?");
$stmt->execute([$itemId]);
$item = $stmt->fetch();

if (!$item) {
    $_SESSION['error'] = "Item not found";
    redirect('/modules/inventory/items.php');
}

// Get movement history for this item
$stmt = $pdo->prepare("
    SELECT m.*, u.username as user_name
    FROM inventory_movements m
    LEFT JOIN users u ON m.created_by = u.id
    WHERE m.item_id = ?
    ORDER BY m.date_time DESC
");
$stmt->execute([$itemId]);
$movements = $stmt->fetchAll();

// Calculate current quantity (sum of all movements)
$stmt = $pdo->prepare("SELECT SUM(quantity) as total FROM inventory_movements WHERE item_id = ?");
$stmt->execute([$itemId]);
$totalQuantity = $stmt->fetch()['total'];
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0">Movement History: <?= htmlspecialchars($item['item_name']) ?></h2>
                <a href="items.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Items
                </a>
            </div>
            <div class="text-muted">
                <?= htmlspecialchars($item['item_code']) ?> |
                Current Quantity: <strong><?= number_format($totalQuantity, 2) ?> <?= $item['unit_of_measure'] ?></strong>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Movement Details</h4>
                <div class="text-muted">
                    <?= count($movements) ?> records found
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Date/Time</th>
                            <th>Type</th>
                            <th class="text-end">Quantity</th>
                            <th class="text-end">Cost</th>
                            <th class="text-end">Price</th>
                            <th>Reference</th>
                            <th>User</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($movements)): ?>
                            <tr>
                                <td colspan="8" class="text-center">No movement history found for this item</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($movements as $movement): ?>
                                <tr>
                                    <td><?= date('M j, Y H:i', strtotime($movement['date_time'])) ?></td>
                                    <td>
                                        <span class="badge
                                            <?= $movement['movement_type'] == 'purchase' || $movement['movement_type'] == 'transfer_in' ? 'bg-success' : '' ?>
                                            <?= $movement['movement_type'] == 'sale' || $movement['movement_type'] == 'transfer_out' ? 'bg-danger' : '' ?>
                                            <?= $movement['movement_type'] == 'adjustment' ? 'bg-warning' : '' ?>
                                            <?= $movement['movement_type'] == 'return' ? 'bg-info' : '' ?>">
                                            <?= ucfirst($movement['movement_type']) ?>
                                        </span>
                                    </td>
                                    <td class="text-end <?= $movement['quantity'] > 0 ? 'text-success' : 'text-danger' ?>">
                                        <?= number_format($movement['quantity'], 2) ?> <?= $item['unit_of_measure'] ?>
                                    </td>
                                    <td class="text-end"><?= $movement['cost_price'] ? number_format($movement['cost_price'], 2) : '' ?></td>
                                    <td class="text-end"><?= $movement['selling_price'] ? number_format($movement['selling_price'], 2) : '' ?></td>
                                    <td>
                                        <?php if ($movement['reference_id']): ?>
                                            <a href="#" class="view-reference"
                                               data-type="<?= $movement['reference_type'] ?>"
                                               data-id="<?= $movement['reference_id'] ?>">
                                                <?= strtoupper($movement['reference_type']) ?> #<?= $movement['reference_id'] ?>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($movement['user_name']) ?></td>
                                    <td><?= htmlspecialchars($movement['notes']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
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
                url = '/modules/inventory/view_purchase_order.php?id=' + id;
                break;
            case 'sale':
                url = '/modules/sales/view_sale.php?id=' + id;
                break;
            case 'adjustment':
                url = '/modules/inventory/view_adjustment.php?id=' + id;
                break;
            // Add other cases as needed
            default:
                alert('View details not available for this type');
                return;
        }

        window.open(url, '_blank');
    });
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>