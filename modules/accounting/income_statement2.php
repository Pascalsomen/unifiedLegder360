<?php
require_once '../../includes/header.php';
require_once '../../classes/AccountingSystem.php';

$accounting = new AccountingSystem($pdo);

// You can pass a specific start and end date, or use current month
$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate = $_GET['end_date'] ?? date('Y-m-t');

$data = $accounting->getIncomeStatement($startDate, $endDate);
?>

<h2>Income Statement</h2>
<form method="get" class="mb-3">
    <label>From: <input type="date" name="start_date" value="<?= $startDate ?>"></label>
    <label>To: <input type="date" name="end_date" value="<?= $endDate ?>"></label>
    <button class="btn btn-primary">Filter</button>
</form>

<table class="table table-bordered">
    <thead class="thead-dark">
        <tr><th>Account</th><th>Amount</th></tr>
    </thead>
    <tbody>
        <tr><th colspan="2">Revenue</th></tr>
        <?php foreach ($data['revenue'] as $rev): ?>
            <tr>
                <td><?= $rev['account_name'] ?></td>
                <td><?= number_format($rev['balance'], 2) ?></td>
            </tr>
        <?php endforeach; ?>

        <tr><th colspan="2">Expenses</th></tr>
        <?php foreach ($data['expenses'] as $exp): ?>
            <tr>
                <td><?= $exp['account_name'] ?></td>
                <td><?= number_format($exp['balance'], 2) ?></td>
            </tr>
        <?php endforeach; ?>

        <tr class="table-info">
            <th>Net Income</th>
            <th><?= number_format($data['net_income'], 2) ?></th>
        </tr>
    </tbody>
</table>

<?php require_once '../../includes/footer.php'; ?>
