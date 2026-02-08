<?php
require_once '../../includes/header.php';
require_once '../../classes/AccountingSystem.php';

$accounting = new AccountingSystem($pdo);
$data = $accounting->getBalanceSheet(date('Y-m-d')); // today or select a specific date
?>

<h2>Balance Sheet</h2>
<table class="table table-bordered">
    <thead class="thead-dark">
        <tr><th>Category</th><th>Account</th><th>Amount</th></tr>
    </thead>
    <tbody>
        <tr><th colspan="3">Assets</th></tr>
        <?php foreach ($data['assets'] as $item): ?>
            <tr>
                <td><?= $item['account_type'] ?></td>
                <td><?= $item['account_name'] ?></td>
                <td><?= number_format($item['balance'], 2) ?></td>
            </tr>
        <?php endforeach; ?>

        <tr><th colspan="3">Liabilities</th></tr>
        <?php foreach ($data['liabilities'] as $item): ?>
            <tr>
                <td><?= $item['account_type'] ?></td>
                <td><?= $item['account_name'] ?></td>
                <td><?= number_format($item['balance'], 2) ?></td>
            </tr>
        <?php endforeach; ?>

        <tr><th colspan="3">Equity</th></tr>
        <?php foreach ($data['equity'] as $item): ?>
            <tr>
                <td><?= $item['account_type'] ?></td>
                <td><?= $item['account_name'] ?></td>
                <td><?= number_format($item['balance'], 2) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require_once '../../includes/footer.php'; ?>
