<?php
$startDate = $_GET['start_date'] ?? date('Y-01-01');
$endDate = $_GET['end_date'] ?? date('Y-m-d');

function getCashFlow($pdo, $accountTypes, $startDate, $endDate, $direction = 'in') {
    $placeholders = str_repeat('?,', count($accountTypes) - 1) . '?';
    $sql = "
        SELECT coa.account_name,
               SUM(CASE WHEN tl.debit > 0 THEN tl.debit ELSE 0 END) AS total_debit,
               SUM(CASE WHEN tl.credit > 0 THEN tl.credit ELSE 0 END) AS total_credit
        FROM chart_of_accounts coa
        JOIN transaction_lines tl ON coa.id = tl.account_id
        JOIN transactions t ON tl.transaction_id = t.id
        WHERE coa.account_type IN ($placeholders)
          AND t.transaction_date BETWEEN ? AND ?
        GROUP BY coa.account_name
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array_merge($accountTypes, [$startDate, $endDate]));
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $total = 0;
    foreach ($results as &$r) {
        // Adjust cash flow direction for each section (in or out)
        $r['net'] = ($direction === 'in')
            ? $r['total_debit'] - $r['total_credit']
            : $r['total_credit'] - $r['total_debit'];
        $total += $r['net'];
    }
    return [$results, $total];
}

// Operating Activities: revenue, expense, receivable, payable
[$operatingCashFlow, $operatingTotal] = getCashFlow(
    $pdo,
    ['revenue', 'expense', 'receivables', 'payables'],
    $startDate,
    $endDate
);

// Investing Activities: fixed assets
[$investingCashFlow, $investingTotal] = getCashFlow(
    $pdo,
    ['asset'],
    $startDate,
    $endDate,
    'out' // Cash outflow for investing (e.g., buying fixed assets)
);

// Financing Activities: equity, liability, loans (payable or receivable)
[$financingCashFlow, $financingTotal] = getCashFlow(
    $pdo,
    ['equity', 'liability'],
    $startDate,
    $endDate
);

$netCashFlow = $operatingTotal - $investingTotal + $financingTotal;
?>

<div class="container mt-5">
    <h3 class="mb-4">Cash Flow Statement</h3>

    <?php function renderSection($title, $rows, $total) { ?>
        <h5><?= $title ?></h5>
        <table class="table table-bordered">
            <thead class="table-light">
                <tr><th>Account</th><th class="text-end">Cash Flow</th></tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['account_name']) ?></td>
                        <td class="text-end"><?= number_format($row['net'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr class="table-secondary">
                    <td><strong>Total <?= $title ?></strong></td>
                    <td class="text-end"><strong><?= number_format($total, 2) ?></strong></td>
                </tr>
            </tbody>
        </table>
    <?php } ?>

    <?php
        renderSection('Operating Activities', $operatingCashFlow, $operatingTotal);
        renderSection('Investing Activities', $investingCashFlow, $investingTotal);
        renderSection('Financing Activities', $financingCashFlow, $financingTotal);
    ?>

    <h5 class="mt-4">Net Cash Flow: <strong><?= number_format($netCashFlow, 2) ?></strong> </h5>
</div>
