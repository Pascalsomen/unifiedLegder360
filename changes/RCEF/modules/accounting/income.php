<?php
require_once '../../includes/header.php';
require_once '../../classes/AccountingSystem.php';
$system = new AccountingSystem($pdo);

$startDate = $_GET['start_date'] ?? date('Y-01-01');
$endDate = $_GET['end_date'] ?? date('Y-m-d');

$data = $system->getIncomeStatementByDateRange($startDate, $endDate);

$incomeTotal = 0;
$expenseTotal = 0;
?>
<div class="container mt-4">
<h2>Income Statement - Fiscal Year <?= htmlspecialchars($startDate)  ?> <?= htmlspecialchars($endDate)  ?> </h2>

<form method="get" class="mb-4">
    <label for="start_date">Start Date:</label>
    <input type="date" name="start_date" value="<?= $_GET['start_date'] ?? date('Y-01-01') ?>" required>

    <label for="end_date">End Date:</label>
    <input type="date" name="end_date" value="<?= $_GET['end_date'] ?? date('Y-m-d') ?>" required>

    <button type="submit" class="btn btn-primary">Filter</button>
</form>

<table class="table table-bordered mt-4">
    <thead>
        <tr>
            <th>Account Name</th>
            <th>Type</th>
            <th>Debit</th>
            <th>Credit</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($data as $row): ?>
        <tr>
            <td><?= htmlspecialchars($row['account_name']) ?></td>
            <td><?= ucfirst($row['account_type']) ?></td>
            <td><?= number_format($row['total_debit'], 2) ?></td>
            <td><?= number_format($row['total_credit'], 2) ?></td>
        </tr>
        <?php
        if ($row['account_type'] === 'income') {
            $incomeTotal += $row['total_credit'] - $row['total_debit'];
        } elseif ($row['account_type'] === 'expense') {
            $expenseTotal += $row['total_debit'] - $row['total_credit'];
        }
        ?>
    <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr>
            <th colspan="2">Net Income</th>
            <th colspan="2"><?= number_format($incomeTotal - $expenseTotal, 2) ?></th>
        </tr>
    </tfoot>
</table>
    </div>
<?php require_once '../../includes/footer.php'; ?>
