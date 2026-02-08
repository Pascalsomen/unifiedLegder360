<?php
// Date Range
$startDate = $_GET['start_date'] ?? date('Y-01-01');
$endDate = $_GET['end_date'] ?? date('Y-m-d');

// Function to fetch income or expense accounts
function getIncomeAccounts($pdo, $type, $startDate, $endDate) {
    $sql = "SELECT coa.id, coa.account_name
            FROM chart_of_accounts coa
            WHERE coa.account_type = :type AND coa.is_active = 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['type' => $type]);
    $accounts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $results = [];

    foreach ($accounts as $acc) {
        $balStmt = $pdo->prepare("SELECT
            SUM(tl.debit) AS debit,
            SUM(tl.credit) AS credit
            FROM transaction_lines tl
            INNER JOIN transactions t ON t.id = tl.transaction_id
            WHERE tl.account_id = :acc_id
              AND t.transaction_date BETWEEN :start AND :end
              ");
        $balStmt->execute([
            'acc_id' => $acc['id'],
            'start' => $startDate,
            'end' => $endDate
        ]);
        $row = $balStmt->fetch(PDO::FETCH_ASSOC);
        $amount = ($type === 'revenue')
            ? ($row['credit'] ?? 0) - ($row['debit'] ?? 0)
            : ($row['debit'] ?? 0) - ($row['credit'] ?? 0);

        if ($amount != 0) {
            $results[] = [
                'name' => $acc['account_name'],
                'amount' => $amount
            ];
        }
    }

    return $results;
}

// Fetch accounts
$revenues = getIncomeAccounts($pdo, 'revenue', $startDate, $endDate);
$expenses = getIncomeAccounts($pdo, 'expense', $startDate, $endDate);

// Totals
$totalRevenue = array_sum(array_column($revenues, 'amount'));
$totalExpense = array_sum(array_column($expenses, 'amount'));
$netIncome = $totalRevenue - $totalExpense;
?>

<!-- HTML Output -->
<div class="container mt-4">
    <h2>Income Statement</h2>
    <p><strong>Period:</strong> <?= htmlspecialchars($startDate) ?> to <?= htmlspecialchars($endDate) ?></p>

    <div class="row">
        <div class="col-md-6">
            <h4>Revenue</h4>
            <ul class="list-group mb-3">
                <?php foreach ($revenues as $rev): ?>
                    <li class="list-group-item d-flex justify-content-between">
                        <span><?= htmlspecialchars($rev['name']) ?></span>
                        <strong><?= number_format($rev['amount'], 2) ?></strong>
                    </li>
                <?php endforeach; ?>
                <li class="list-group-item d-flex justify-content-between bg-light">
                    <strong>Total Revenue</strong>
                    <strong><?= number_format($totalRevenue, 2) ?></strong>
                </li>
            </ul>
        </div>

        <div class="col-md-6">
            <h4>Expenses</h4>
            <ul class="list-group mb-3">
                <?php foreach ($expenses as $exp): ?>
                    <li class="list-group-item d-flex justify-content-between">
                        <span><?= htmlspecialchars($exp['name']) ?></span>
                        <strong><?= number_format($exp['amount'], 2) ?></strong>
                    </li>
                <?php endforeach; ?>
                <li class="list-group-item d-flex justify-content-between bg-light">
                    <strong>Total Expenses</strong>
                    <strong><?= number_format($totalExpense, 2) ?></strong>
                </li>
            </ul>
        </div>
    </div>

    <div class="alert alert-info mt-3">
        <strong>Net Income:</strong> <?= number_format($netIncome, 2) ?>
    </div>
</div>
