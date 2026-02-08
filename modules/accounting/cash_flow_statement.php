<?php

$startDate = $_GET['start_date'] ?? date('Y-01-01');
$endDate = $_GET['end_date'] ?? date('Y-m-d');

// Helper: Get accounts by type
function getAccountsByType($pdo, $type) {
    $stmt = $pdo->prepare("SELECT id FROM chart_of_accounts WHERE account_type = :type AND is_active = 1");
    $stmt->execute(['type' => $type]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Helper: Sum cash movement
function getCashFlowByAccounts($pdo, $accountIds, $start, $end) {
    if (empty($accountIds)) return 0;
    $idList = implode(',', array_map('intval', $accountIds));
    $stmt = $pdo->prepare("
        SELECT SUM(tl.debit - tl.credit) AS net
        FROM transaction_lines tl
        INNER JOIN transactions t ON t.id = tl.transaction_id
        WHERE tl.account_id IN ($idList)
        AND t.transaction_date BETWEEN :start AND :end
    ");
    $stmt->execute(['start' => $start, 'end' => $end]);
    return (float) ($stmt->fetchColumn() ?? 0);
}

// Get finance account movements
$financeAccounts = getAccountsByType($pdo, 'finances');
$openingCash = getCashFlowByAccounts($pdo, $financeAccounts, '2000-01-01', date('Y-m-d', strtotime("$startDate -1 day")));
$closingCash = getCashFlowByAccounts($pdo, $financeAccounts, '2000-01-01', $endDate);

// Categories (simplified logic based on account type)
$revenueAccounts = getAccountsByType($pdo, 'revenue');
$expenseAccounts = getAccountsByType($pdo, 'expense');
$liabilityAccounts = getAccountsByType($pdo, 'liability');
$equityAccounts = getAccountsByType($pdo, 'equity');
$fixedAssetAccounts = []; // fixed_assets is handled from table, not CoA

$operatingIn = getCashFlowByAccounts($pdo, $revenueAccounts, $startDate, $endDate);
$operatingOut = getCashFlowByAccounts($pdo, $expenseAccounts, $startDate, $endDate);
$netOperating = $operatingIn - $operatingOut;

$netInvesting = 0;
$assets = $pdo->query("SELECT cost FROM fixed_assets WHERE purchase_date BETWEEN '$startDate' AND '$endDate'")->fetchAll(PDO::FETCH_COLUMN);
foreach ($assets as $cost) {
    $netInvesting -= $cost; // purchase is cash out
}

$netFinancing = getCashFlowByAccounts($pdo, array_merge($liabilityAccounts, $equityAccounts), $startDate, $endDate);

$netCash = $netOperating + $netInvesting + $netFinancing;
?>

<div class="container mt-4">
    <h2>Cash Flow Statement</h2>
    <p><strong>Period:</strong> <?= $startDate ?> to <?= $endDate ?></p>

    <h4>Cash Flows from Operating Activities</h4>
    <ul>
        <li>Cash Inflows (Revenue): <strong><?= number_format($operatingIn, 2) ?></strong></li>
        <li>Cash Outflows (Expenses): <strong><?= number_format($operatingOut, 2) ?></strong></li>
        <li><strong>Net Cash from Operating:</strong> <?= number_format($netOperating, 2) ?></li>
    </ul>

    <h4>Cash Flows from Investing Activities</h4>
    <ul>
        <li>Fixed Asset Purchases: <strong><?= number_format($netInvesting, 2) ?></strong></li>
        <li><strong>Net Investing Cash:</strong> <?= number_format($netInvesting, 2) ?></li>
    </ul>

    <h4>Cash Flows from Financing Activities</h4>
    <ul>
        <li>Net Financing Cash (Loans, Equity): <strong><?= number_format($netFinancing, 2) ?></strong></li>
    </ul>

    <h4>Cash Summary</h4>
    <ul>
        <li>Opening Cash: <strong><?= number_format($openingCash, 2) ?></strong></li>
        <li>Net Change: <strong><?= number_format($netCash, 2) ?></strong></li>
        <li><strong>Closing Cash:</strong> <?= number_format($closingCash, 2) ?></li>
    </ul>
</div>
