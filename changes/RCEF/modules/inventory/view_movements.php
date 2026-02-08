<?php
require_once __DIR__ . '/../../includes/header.php';

if (!hasPermission('inventory')) {
    redirect('/index.php');
}

// Filter parameters
$itemId = isset($_GET['item_id']) ? intval($_GET['item_id']) : null;
$type = isset($_GET['type']) ? $_GET['type'] : null;
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');

// Build query
$query = "SELECT * FROM inventory_movement_report WHERE date_time BETWEEN ? AND ?";
$params = [$startDate . ' 00:00:00', $endDate . ' 23:59:59'];

if ($itemId) {
    $query .= " AND id = ?";
    $params[] = $itemId;
}

if ($type && in_array($type, ['purchase', 'sale', 'adjustment', 'transfer_in', 'transfer_out', 'return'])) {
    $query .= " AND movement_type = ?";
    $params[] = $type;
}

$query .= " ORDER BY date_time DESC";

// Get movements
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$movements = $stmt->fetchAll();

// Get items for dropdown
$items = $pdo->query("SELECT id, item_code, name FROM inventory_items ORDER BY name")->fetchAll();
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2>Inventory Movements</h2>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h4>Filters</h4>
        </div>
        <div class="card-body">
            <form method="get" class="row">
                <div class="col-md-3">
                    <label class="form-label">Item</label>
                    <select name="item_id" class="form-select">
                        <option value="">All Items</option>
                        <?php foreach ($items as $item): ?>
                            <option value="<?= $item['id'] ?>" <?= $itemId == $item['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($item['item_code']) ?> - <?= htmlspecialchars($item['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Movement Type</label>
                    <select name="type" class="form-select">
                        <option value="">All Types</option>
                        <option value="purchase" <?= $type == 'purchase' ? 'selected' : '' ?>>Purchases</option>
                        <option value="sale" <?= $type == 'sale' ? 'selected' : '' ?>>Sales</option>
                        <option value="adjustment" <?= $type == 'adjustment' ? 'selected' : '' ?>>Adjustments</option>
                        <option value="transfer_in" <?= $type == 'transfer_in' ? 'selected' : '' ?>>Transfers In</option>
                        <option value="transfer_out" <?= $type == 'transfer_out' ? 'selected' : '' ?>>Transfers Out</option>
                        <option value="return" <?= $type == 'return' ? 'selected' : '' ?>>Returns</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" class="form-control" value="<?= $startDate ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">End Date</label>
                    <input type="date" name="end_date" class="form-control" value="<?= $endDate ?>">
                </div>
                <div class="col-md-12 mt-3">
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                    <a href="view_movements.php" class="btn btn-secondary">Reset</a>
                    <button class="btn btn-success mb-1 float-end" onclick="exportToExcel('table', 'Stock Movements')">Export to Excel</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Movement History</h4>
                <div class="text-muted">
                    <?= count($movements) ?> records found
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="table" class="table table-striped" id="movementsTable">
                    <thead>
                        <tr>
                            <th>Date/Time</th>
                            <th>Item</th>
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
                                <td colspan="9" class="text-center">No movements found for the selected filters</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($movements as $movement): ?>
                                <tr>
                                    <td><?= date('M j, Y H:i', strtotime($movement['date_time'])) ?></td>
                                    <td>
                                        <?= htmlspecialchars($movement['item_code']) ?>
                                        <?php if ($movement['item_name']): ?>
                                            <br><small><?= htmlspecialchars($movement['item_name']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge
                                            <?= $movement['movement_type'] == 'purchase' || $movement['movement_type'] == 'transfer_in' ? 'bg-success' : '' ?>
                                            <?= $movement['movement_type'] == 'sale' || $movement['movement_type'] == 'transfer_out' ? 'bg-danger' : '' ?>
                                            <?= $movement['movement_type'] == 'adjustment' ? 'bg-warning' : '' ?>
                                            <?= $movement['movement_type'] == 'return' ? 'bg-info' : '' ?>">
                                            <?= $movement['movement_type_name'] ?>
                                        </span>
                                    </td>
                                    <td class="text-end <?= $movement['quantity'] > 0 ? 'text-success' : 'text-danger' ?>">
                                        <?= number_format($movement['quantity'], 2) ?> <?= $movement['unit_of_measure'] ?>
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
function exportToExcel() {
    // You would implement this function to export the data to Excel
    // This could be done with a library like SheetJS or by sending to a server-side export script
    alert("Export to Excel functionality would be implemented here");
}

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