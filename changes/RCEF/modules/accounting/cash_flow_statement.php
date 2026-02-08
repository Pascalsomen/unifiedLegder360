<?php
require_once '../../includes/header.php';
require_once '../../classes/AccountingSystem.php';

$accounting = new AccountingSystem($pdo);

$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate = $_GET['end_date'] ?? date('Y-m-t');

$cashFlow = $accounting->getCashFlowStatement($startDate, $endDate);
?>

<h2>Cash Flow Statement</h2>
<form method="get" class="mb-3">
    <label>From: <input type="date" name="start_date" value="<?= $startDate ?>"></label>
    <label>To: <input type="date" name="end_date" value="<?= $endDate ?>"></label>
    <button class="btn btn-primary">Filter</button>
</form>

<table class="table table-bordered">
    <thead class="thead-dark">
        <tr><th>Category</th><th>Amount</th></tr>
    </thead>
    <tbody>
        <tr><th colspan="2">Operating Activities</th></tr>
        <?php foreach ($cashFlow['operating'] as $row): ?>
            <tr>
                <td><?= $row['account_name'] ?></td>
                <td><?= number_format($row['amount'], 2) ?></td>
            </tr>
        <?php endforeach; ?>

        <tr><th colspan="2">Investing Activities</th></tr>
        <?php foreach ($cashFlow['investing'] as $row): ?>
            <tr>
                <td><?= $row['account_name'] ?></td>
                <td><?= number_format($row['amount'], 2) ?></td>
            </tr>
        <?php endforeach; ?>

        <tr><th colspan="2">Financing Activities</th></tr>
        <?php foreach ($cashFlow['financing'] as $row): ?>
            <tr>
                <td><?= $row['account_name'] ?></td>
                <td><?= number_format($row['amount'], 2) ?></td>
            </tr>
        <?php endforeach; ?>

        <tr class="table-info">
            <th>Net Cash Flow</th>
            <th><?= number_format($cashFlow['net_cash'], 2) ?> FGFF</th>
        </tr>
    </tbody>
</table>

<?php require_once '../../includes/footer.php'; ?>
