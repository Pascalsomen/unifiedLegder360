<?php
function getAccountSum($pdo, $type, $startDate, $endDate) {
    $sql = "SELECT coa.id
            FROM chart_of_accounts coa
            WHERE coa.account_type = :type AND coa.is_active = 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['type' => $type]);
    $accountIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($accountIds)) return 0;
    $idsStr = implode(',', array_map('intval', $accountIds));

    $balSql = "SELECT
                    SUM(debit) as total_debit,
                    SUM(credit) as total_credit
               FROM transaction_lines tl
               INNER JOIN transactions t ON t.id = tl.transaction_id
               WHERE tl.account_id IN ($idsStr)
                 AND t.transaction_date BETWEEN :start AND :end";
    $balStmt = $pdo->prepare($balSql);
    $balStmt->execute(['start' => $startDate, 'end' => $endDate]);
    $row = $balStmt->fetch(PDO::FETCH_ASSOC);

    return in_array($type, ['asset', 'receivables', 'finances','expense'])
       ? ($row['total_debit'] ?? 0) - ($row['total_credit'] ?? 0)
       : ($row['total_credit'] ?? 0) - ($row['total_debit'] ?? 0);
}

// Net Income: Revenue - Expenses
$revenue = getAccountSum($pdo, 'revenue', $startDate, $endDate);
$expense = getAccountSum($pdo, 'expense', $startDate, $endDate);
$netIncome = $revenue - $expense;

// Payables, Receivables, Liabilities
$finances = getAccountSum($pdo, 'finances', $startDate, $endDate);
$payables = getAccountSum($pdo, 'payables', $startDate, $endDate);
$receivables = getAccountSum($pdo, 'receivables', $startDate, $endDate);
$liabilities = getAccountSum($pdo, 'liability', $startDate, $endDate);
$equity = getAccountSum($pdo, 'equity', $startDate, $endDate);
// Fixed Assets: only include assets purchased on or before balance sheet date
$assets = $pdo->prepare("SELECT asset_name, cost, purchase_date, useful_life, salvage_value FROM fixed_assets WHERE purchase_date <= :endDate");
$assets->execute(['endDate' => $endDate]);
$assets = $assets->fetchAll(PDO::FETCH_ASSOC);

$fixedTotal = 0;
$fixedList = [];

foreach ($assets as $a) {
    // Calculate age in years capped at useful life
    $age = max(0, floor((strtotime($endDate) - strtotime($a['purchase_date'])) / (365*24*60*60)));
    $age = min($age, max(1, $a['useful_life'])); // Do not depreciate beyond useful life

    $depPerYear = ($a['cost'] - $a['salvage_value']) / max(1, $a['useful_life']);
    $dep = min($a['cost'] - $a['salvage_value'], $depPerYear * $age);
    $netValue = $a['cost'] - $dep;

    $fixedTotal += $netValue;
    $fixedList[] = [
        'name' => $a['asset_name'],
        'cost' => $a['cost'],
        'dep' => $dep,
        'net' => $netValue
    ];
}

// Inventory FIFO with date filtering

$inventoryTotal = 0;
$itemStmt = $pdo->query("SELECT id FROM stock_items WHERE itemtype = 'inventory'");
$itemIds = $itemStmt->fetchAll(PDO::FETCH_COLUMN);

foreach ($itemIds as $itemId) {
    // Calculate stock as of balance sheet date
    $stmt = $pdo->prepare("SELECT
        COALESCE(SUM(quantity_in), 0) - COALESCE(SUM(quantity_out), 0) AS stock
        FROM stock_movements
        WHERE item_id = :id
          AND date <= :endDate");
    $stmt->execute(['id' => $itemId, 'endDate' => $endDate]);
    $stockQty = $stmt->fetchColumn();

    if ($stockQty <= 0) continue;

    // Fetch purchase order items only on or before balance sheet date (FIFO)
    $stmt = $pdo->prepare("SELECT poi.quantity, poi.price
        FROM purchase_order_items poi
        INNER JOIN purchase_orders po ON poi.purchase_order_id = po.id
        WHERE poi.item_id = :id
          AND po.order_date <= :endDate
        ORDER BY po.order_date ASC");
    $stmt->execute(['id' => $itemId, 'endDate' => $endDate]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $remaining = $stockQty;
    foreach ($rows as $row) {
        $qty = min($row['quantity'], $remaining);
        $inventoryTotal += $qty * $row['price'];
        $remaining -= $qty;
        if ($remaining <= 0) break;
    }
}


$financeList = [];
$financeAccounts = $pdo->prepare("SELECT id, account_name FROM chart_of_accounts WHERE account_type = 'finances' AND is_active = 1");
$financeAccounts->execute();
foreach ($financeAccounts->fetchAll(PDO::FETCH_ASSOC) as $acc) {
    $balStmt = $pdo->prepare("SELECT
        SUM(tl.debit) AS debit, SUM(tl.credit) AS credit
        FROM transaction_lines tl
        INNER JOIN transactions t ON t.id = tl.transaction_id
        WHERE tl.account_id = :acc_id AND t.transaction_date BETWEEN :start AND :end");
    $balStmt->execute([
        'acc_id' => $acc['id'],
        'start' => $startDate,
        'end' => $endDate
    ]);
    $row = $balStmt->fetch(PDO::FETCH_ASSOC);
    $bal = ($row['debit'] ?? 0) - ($row['credit'] ?? 0);
    if ($bal != 0) {
        $financeList[] = [
            'name' => $acc['account_name'],
            'balance' => $bal
        ];
    }
}

// Total Assets and Liabilities
$totalAssets = $fixedTotal + $inventoryTotal + $finances + $receivables;
$totalLiabilities = $payables + $liabilities + $equity + $netIncome;
?>

<div class="container mt-4">
    <h2>Balance Sheet</h2>
    <p><strong>Period:</strong> <?= $startDate ?> to <?= $endDate ?></p>
    <div class="row">
        <div class="col-md-6">
    <h4>Assets</h4>
    <h5 class="mt-3">Fixed Assets</h5>
    <ul class="list-group mb-3">
        <?php foreach ($fixedList as $fa): ?>
            <li class="list-group-item">
                <div><strong><?= htmlspecialchars($fa['name']) ?></strong></div>
                <small>Cost: <?= number_format($fa['cost'], 2) ?> | Dep: <?= number_format($fa['dep'], 2) ?> | Net: <?= number_format($fa['net'], 2) ?></small>
            </li>
        <?php endforeach; ?>
        <li class="list-group-item d-flex justify-content-between bg-light">
            <span><strong>Total Fixed Assets</strong></span>
            <strong><?= number_format($fixedTotal, 2) ?></strong>
        </li>
    </ul>

    <h5>Current Assets</h5>
    <ul class="list-group mb-3">
        <li class="list-group-item d-flex justify-content-between">
            <span>Inventory</span>
            <strong><?= number_format($inventoryTotal, 2) ?></strong>
        </li>
        <?php foreach ($financeList as $f): ?>
            <li class="list-group-item d-flex justify-content-between">
                <span><?= htmlspecialchars($f['name']) ?></span>
                <strong><?= number_format($f['balance'], 2) ?></strong>
            </li>
        <?php endforeach; ?>
        <li class="list-group-item d-flex justify-content-between">
            <span>Receivables</span>
            <strong><?= number_format($receivables, 2) ?></strong>
        </li>
        <li class="list-group-item d-flex justify-content-between bg-light">
            <span><strong>Total Current Assets</strong></span>
            <strong><?= number_format($inventoryTotal + $finances + $receivables, 2) ?></strong>
        </li>
    </ul>

    <li class="list-group-item d-flex justify-content-between bg-success text-white">
        <span><strong>Total Assets</strong></span>
        <strong><?= number_format($totalAssets, 2) ?></strong>
    </li>
</div>

<div class="col-md-6">
    <h4>Liabilities & Equity</h4>
    <ul class="list-group mb-3">
        <li class="list-group-item d-flex justify-content-between">
            <span>Payables</span>
            <strong><?= number_format($payables, 2) ?></strong>
        </li>
        <li class="list-group-item d-flex justify-content-between">
            <span>Liabilities</span>
            <strong><?= number_format($liabilities, 2) ?></strong>
        </li>
        <li class="list-group-item d-flex justify-content-between">
            <span>Equity</span>
            <strong><?= number_format($equity, 2) ?></strong>
        </li>
        <li class="list-group-item d-flex justify-content-between bg-light">
            <span><strong>Net Income</strong></span>
            <strong><?=  number_format($netIncome, 2) ?></strong>
        </li>
        <li class="list-group-item d-flex justify-content-between bg-success text-white">
            <span><strong>Total Liabilities + Equity</strong></span>
            <strong><?= number_format($totalLiabilities, 2) ?></strong>
        </li>
    </ul>
</div>

    </div>

<br><br>
<div  class="alert <?= ($totalAssets == $totalLiabilities) ? 'alert-success' : 'alert-danger' ?>">
    <strong>Balance Check:</strong>
    <?= ($totalAssets == $totalLiabilities)
        ? 'Balanced ✅'
        : 'Not Balanced ❌ — Difference: ' . number_format($totalAssets - $totalLiabilities, 2) ?>
</div>
</div>
