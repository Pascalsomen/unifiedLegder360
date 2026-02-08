<?php
require_once __DIR__ . '/../../includes/header.php';

$startDate = $_GET['start_date'] ?? date('Y-01-01');
$endDate = $_GET['end_date'] ?? date('Y-m-d');

$query = "
    SELECT
        COALESCE(parent.account_name, child.account_name) AS main_account_name,
        child.account_name AS sub_account_name,
        child.account_type,
        SUM(CASE WHEN tl.debit > 0 THEN tl.debit ELSE 0 END) AS total_debit,
        SUM(CASE WHEN tl.credit > 0 THEN tl.credit ELSE 0 END) AS total_credit
    FROM chart_of_accounts child
    LEFT JOIN chart_of_accounts parent ON child.parent_account = parent.id
    LEFT JOIN transaction_lines tl ON child.id = tl.account_id
    LEFT JOIN transactions t ON tl.transaction_id = t.id
    WHERE t.transaction_date BETWEEN ? AND ?
    AND child.account_type IN ('revenue', 'expense')
    GROUP BY parent.account_name, child.account_name, child.account_type
    ORDER BY child.account_type, parent.account_name, child.account_name
";
$stmt = $pdo->prepare($query);
$stmt->execute([$startDate, $endDate]);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

$grouped = [];
foreach ($data as $row) {
    $main = $row['main_account_name'];
    $type = $row['account_type'];
    $grouped[$type][$main][] = $row;
}
?>
<div class="container mt-5">
    <h3 class="mb-4">Income Statement</h3>

    <form class="mb-4" method="get">
        <div class="row g-2">
            <div class="col-md-3">
                <label>Start Date</label>
                <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($startDate) ?>">
            </div>
            <div class="col-md-3">
                <label>End Date</label>
                <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($endDate) ?>">
            </div>
            <div class="col-md-2 align-self-end">
                <button class="btn btn-primary">Filter</button>
            </div>
        </div>
    </form>

    <table class="table table-bordered">
        <thead class="table-light">
            <tr>
                <th>Main Account</th>
                <th>Sub Account</th>
                <th class="text-end">Debit</th>
                <th class="text-end">Credit</th>
            </tr>
        </thead>
        <tbody>
<?php
$totalRevenue = 0;
$totalExpense = 0;

foreach (['revenue', 'expense'] as $type) {
    echo "<tr class='table-secondary'><td colspan='4'><strong>" . ucfirst($type) . "</strong></td></tr>";
    if (!isset($grouped[$type])) continue;

    foreach ($grouped[$type] as $main => $accounts) {
        $mainDebit = 0;
        $mainCredit = 0;

        // Sum totals for all sub-accounts under this main account
        foreach ($accounts as $acc) {
            $mainDebit += $acc['total_debit'];
            $mainCredit += $acc['total_credit'];
        }

        if ($type === 'revenue') {
            $totalRevenue += $mainCredit;
        } else {
            $totalExpense += $mainDebit;
        }

        // Only show the total row for the main account
        echo "<tr class='table-light'>
            <td colspan='1'><strong>$main</strong></td>
            <td><em>(Total of Sub-accounts)</em></td>
            <td class='text-end'><strong>" . number_format($mainDebit, 2) . "</strong></td>
            <td class='text-end'><strong>" . number_format($mainCredit, 2) . "</strong></td>
        </tr>";
    }
}
?>
</tbody>

        <tfoot class="table-dark text-white">
            <tr>
                <th colspan="2">Net Income</th>
                <th colspan="2" class="text-end"><?= number_format($totalRevenue - $totalExpense, 2) ?></th>
            </tr>
        </tfoot>
    </table>
    </div>
    <?php require_once __DIR__ . '/../../includes/footer.php'; ?>
