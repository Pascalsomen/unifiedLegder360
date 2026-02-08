<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../classes/AccountingSystem.php';


if (!hasRole('accountant')) {
    redirect($base);
}

$accountingSystem = new AccountingSystem($pdo);

// Get current period
$currentPeriod = $pdo->query("
    SELECT * FROM accounting_periods
    WHERE start_date <= CURDATE() AND end_date >= CURDATE()
    LIMIT 1
")->fetch();

// Get trial balance data
$trialBalance = [];
$balanceStatus = [];
if ($currentPeriod) {
    $trialBalance = $accountingSystem->getTrialBalance($currentPeriod['id']);
    $balanceStatus = $accountingSystem->verifyBalances($currentPeriod['id']);
}

// Get all periods for dropdown
$periods = $pdo->query("SELECT * FROM accounting_periods ORDER BY start_date DESC")->fetchAll();
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2>Trial Balance</h2>
            <?php if ($balanceStatus): ?>
                <div class="alert alert-<?= $balanceStatus['status'] === 'Balanced' ? 'success' : 'danger' ?>">
                    <strong><?= $balanceStatus['status'] ?></strong> |
                    Total Debits: <?= number_format($balanceStatus['total_debits'], 2) ?> |
                    Total Credits: <?= number_format($balanceStatus['total_credits'], 2) ?>
                    <?php if ($balanceStatus['difference'] != 0): ?>
                        | Difference: <?= number_format($balanceStatus['difference'], 2) ?>
                    <?php endif; ?>
                </div>
            <?php
        endif; ?>
        </div>


        <?php
$totalDebitBalance = 0;
$totalCreditBalance = 0;
foreach ($trialBalance as $account) {
    if ($account['balance'] > 0) {
        $totalDebitBalance += $account['balance'];
    } elseif ($account['balance'] < 0) {
        $totalCreditBalance += abs($account['balance']);
    }
}
?>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h4>Filters</h4>
        </div>
        <div class="card-body">
            <form method="get" class="row">
                <div class="col-md-4">
                    <label class="form-label">Accounting Period</label>
                    <select name="period_id" class="form-select" onchange="this.form.submit()">
                        <?php foreach ($periods as $period): ?>
                            <option value="<?= $period['id'] ?>"
                                <?= $currentPeriod && $currentPeriod['id'] == $period['id'] ? 'selected' : '' ?>
                                <?= $period['is_closed'] ? 'style="font-weight:bold;"' : '' ?>>
                                <?= $period['name'] ?> (<?= date('m/d/Y', strtotime($period['start_date'])) ?> - <?= date('m/d/Y', strtotime($period['end_date'])) ?>)
                                <?= $period['is_closed'] ? ' [CLOSED]' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Trial Balance</h4>
                <div>

                    <button class="btn btn-success mb-1 float-end" onclick="exportToExcel('table', 'Trial Balance')">Export to Excel</button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <?php if ($currentPeriod): ?>
                <div class="table-responsive">
                    <table id="table" class="table table-striped">
                        <thead>
                            <tr>
                                <th>Account Code</th>
                                <th>Account Name</th>
                                <th>Account Type</th>
                                <th class="text-end">Debit</th>
                                <th class="text-end">Credit</th>
                                <!-- <th class="text-end">Balance</th> -->
                                 <th class="text-end">Balance Debit</th>
<th class="text-end">Balance Credit</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($trialBalance as $account): ?>
                                <tr>
                                    <td><?= $account['account_code'] ?></td>
                                    <td><?= $account['account_name'] ?></td>
                                    <td><?= ucfirst($account['account_type']) ?></td>
                                    <td class="text-end"><?= number_format($account['debit_amount'], 2) ?></td>
                                    <td class="text-end"><?= number_format($account['credit_amount'], 2) ?></td>

                                  <td class="text-end <?= $account['balance'] > 0 ? 'text-success' : '' ?>">
    <?= $account['balance'] > 0 ? number_format($account['balance'], 2) : '' ?>
</td>
<td class="text-end <?= $account['balance'] < 0 ? 'text-danger' : '' ?>">
    <?= $account['balance'] < 0 ? number_format(abs($account['balance']), 2) : '' ?>
</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-active">
                            <tr>
                                <th colspan="3" class="text-end">Totals</th>
                              <th class="text-end"><?= number_format(array_sum(array_column($trialBalance, 'debit_amount')), 2) ?></th>
        <th class="text-end"><?= number_format(array_sum(array_column($trialBalance, 'credit_amount')), 2) ?></th>
        <th class="text-end"><?= number_format($totalDebitBalance, 2) ?></th>
        <th class="text-end"><?= number_format($totalCreditBalance, 2) ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-warning">
                    No active accounting period found.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function exportToExcel() {
    // Implement Excel export functionality
    alert("Export to Excel would be implemented here");
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>