<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../classes/AccountingPeriod.php';
require_once __DIR__ . '/../../classes/AccountingSystem.php';



$periodId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($periodId <= 0) {
    redirect('/modules/accounting/accounting_periods.php');
}

$accountingPeriod = new AccountingPeriod($pdo);
$accountingSystem = new AccountingSystem($pdo);
$period = $accountingPeriod->getPeriodById($periodId);

if (!$period) {
    $_SESSION['error'] = "Accounting period not found";
    redirect('/modules/accounting/accounting_periods.php');
}

// Get trial balance for this period
$trialBalance = $accountingSystem->getTrialBalance($periodId);
$balanceStatus = $accountingSystem->verifyBalances($periodId);

// Get closing summary if period is closed
$closingSummary = null;
if ($period['is_closed']) {
    $stmt = $pdo->prepare("
        SELECT s.*, CONCAT(u.first_name, ' ', u.last_name) AS created_by_name
        FROM period_closing_summaries s
        JOIN users u ON s.created_by = u.id
        WHERE s.period_id = ?
    ");
    $stmt->execute([$periodId]);
    $closingSummary = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2>Accounting Period Report: <?= htmlspecialchars($period['name']) ?></h2>
                <a href="accounting_periods.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Periods
                </a>
            </div>
            <p class="mb-0">
                <?= date('F j, Y', strtotime($period['start_date'])) ?> - <?= date('F j, Y', strtotime($period['end_date'])) ?>
                | Status:
                <?php if ($period['is_closed']): ?>
                    <span class="badge bg-secondary">Closed</span>
                <?php elseif ($period['is_active']): ?>
                    <span class="badge bg-success">Active</span>
                <?php else: ?>
                    <span class="badge bg-warning">Past (Not Closed)</span>
                <?php endif; ?>
            </p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-header">
                    <h4>Period Summary</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <h6>Date Range</h6>
                            <p><?= date('F j, Y', strtotime($period['start_date'])) ?> - <?= date('F j, Y', strtotime($period['end_date'])) ?></p>
                        </div>
                        <div class="col-md-4">
                            <h6>Created By</h6>
                            <p><?= htmlspecialchars($period['created_by_name']) ?><br>
                            <small><?= date('M j, Y H:i', strtotime($period['created_at'])) ?></small></p>
                        </div>
                        <div class="col-md-4">
                            <h6>Closed By</h6>
                            <?php if ($period['closed_by_name']): ?>
                                <p><?= htmlspecialchars($period['closed_by_name']) ?><br>
                                <small><?= date('M j, Y H:i', strtotime($period['closed_at'])) ?></small></p>
                            <?php else: ?>
                                <p>-</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ($closingSummary): ?>
                        <div class="mt-4">
                            <h5>Closing Summary</h5>
                            <div class="row">
                                <div class="col-md-4">
                                    <h6>Total Debits</h6>
                                    <p><?= number_format($closingSummary['total_debits'], 2) ?></p>
                                </div>
                                <div class="col-md-4">
                                    <h6>Total Credits</h6>
                                    <p><?= number_format($closingSummary['total_credits'], 2) ?></p>
                                </div>
                                <div class="col-md-4">
                                    <h6>Retained Earnings</h6>
                                    <p><?= number_format($closingSummary['retained_earnings'], 2) ?></p>
                                </div>
                            </div>
                            <div class="mt-3">
                                <h6>Closing Notes</h6>
                                <p><?= nl2br(htmlspecialchars($closingSummary['closing_entries_description'])) ?></p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Trial Balance</h4>
                        <div>
                            <?php if ($balanceStatus): ?>
                                <span class="badge bg-<?= $balanceStatus['status'] === 'Balanced' ? 'success' : 'danger' ?>">
                                    <?= $balanceStatus['status'] ?> |
                                    Debits: <?= number_format($balanceStatus['total_debits'], 2) ?> |
                                    Credits: <?= number_format($balanceStatus['total_credits'], 2) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Account Code</th>
                                    <th>Account Name</th>
                                    <th>Account Type</th>
                                    <th class="text-end">Debit</th>
                                    <th class="text-end">Credit</th>
                                    <th class="text-end">Balance</th>
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
                                        <td class="text-end <?= $account['balance'] >= 0 ? 'text-success' : 'text-danger' ?>">
                                            <?= number_format(abs($account['balance']), 2) ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="table-active">
                                <tr>
                                    <th colspan="3" class="text-end">Totals</th>
                                    <th class="text-end"><?= number_format(array_sum(array_column($trialBalance, 'debit_amount')), 2) ?></th>
                                    <th class="text-end"><?= number_format(array_sum(array_column($trialBalance, 'credit_amount')), 2) ?></th>
                                    <th class="text-end">-</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>