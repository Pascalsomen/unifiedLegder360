<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../classes/InventorySystem.php';
$inventorySystem = new InventorySystem($pdo);

// Default date range: current month
$fromDate = $_GET['from_date'] ?? date('Y-m-01');
$toDate = $_GET['to_date'] ?? date('Y-m-t');

// Fetch stock report
$stockReport = $inventorySystem->getStockReport($fromDate, $toDate);

?>

<div class="container mt-5">
    <h3 class="mb-4">Stock Report</h3>

    <!-- Filter Form -->
    <form class="row g-3 mb-4" method="get">
        <div class="col-auto">
            <label for="from_date" class="form-label">From</label>
            <input type="date" class="form-control" id="from_date" name="from_date" value="<?= htmlspecialchars($fromDate) ?>">
        </div>
        <div class="col-auto">
            <label for="to_date" class="form-label">To</label>
            <input type="date" class="form-control" id="to_date" name="to_date" value="<?= htmlspecialchars($toDate) ?>">
        </div>
        <div class="col-auto align-self-end">
            <button type="submit" class="btn btn-primary">Filter</button>
        </div>
    </form>

    <!-- Stock Report Table -->
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Item</th>
                <th>Opening Stock</th>
                <th>Stock In</th>
                <th>Stock Out</th>
                <th>Closing Stock</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($stockReport as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['item_name']) ?></td>
                    <td><?= $row['opening_stock'] ?></td>
                    <td><?= $row['stock_in'] ?></td>
                    <td><?= $row['stock_out'] ?></td>
                    <td><?= $row['closing_stock'] ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
