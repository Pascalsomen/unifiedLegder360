<?php
require_once __DIR__ . '/../../includes/header.php';

if (!hasPermission('accountant')) {
    redirect('/index.php');
}

// Get bank accounts
$bankAccounts = $pdo->query("SELECT * FROM bank_accounts WHERE is_active = TRUE")->fetchAll();

// Handle reconciliation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bankAccountId = $_POST['bank_account_id'];
    $startDate = $_POST['start_date'];
    $endDate = $_POST['end_date'];
    $matches = $_POST['matches'] ?? [];

    try {
        $pdo->beginTransaction();

        // Create reconciliation log
        $stmt = $pdo->prepare("INSERT INTO reconciliation_logs
                              (bank_account_id, reconciled_by, start_date, end_date)
                              VALUES (?, ?, ?, ?)");
        $stmt->execute([$bankAccountId, $_SESSION['user_id'], $startDate, $endDate]);

        // Update matched transactions
        foreach ($matches as $bankTxnId => $systemTxnId) {
            $stmt = $pdo->prepare("UPDATE bank_transactions
                                  SET status = 'reconciled', system_transaction_id = ?
                                  WHERE id = ?");
            $stmt->execute([$systemTxnId, $bankTxnId]);

            // Mark system transaction as reconciled
            $stmt = $pdo->prepare("UPDATE transactions
                                  SET is_reconciled = TRUE
                                  WHERE id = ?");
            $stmt->execute([$systemTxnId]);
        }

        // Update bank account balance
        $stmt = $pdo->prepare("UPDATE bank_accounts
                              SET current_balance = (
                                  SELECT balance FROM bank_transactions
                                  WHERE bank_account_id = ?
                                  ORDER BY transaction_date DESC, id DESC
                                  LIMIT 1
                              )
                              WHERE id = ?");
        $stmt->execute([$bankAccountId, $bankAccountId]);

        $pdo->commit();

        $_SESSION['success'] = "Bank reconciliation completed successfully!";
        redirect('/modules/bank-reconciliation/reconcile.php');
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Reconciliation failed: " . $e->getMessage();
    }
}

// Get data for display
$bankAccountId = $_GET['bank_account_id'] ?? $bankAccounts[0]['id'] ?? null;
$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate = $_GET['end_date'] ?? date('Y-m-t');

$bankTransactions = [];
$systemTransactions = [];
$unreconciledBalance = 0;
$discrepancy = 0;

if ($bankAccountId) {
    // Get bank transactions
    $stmt = $pdo->prepare("SELECT * FROM bank_transactions
                          WHERE bank_account_id = ?
                          AND transaction_date BETWEEN ? AND ?
                          ORDER BY transaction_date, id");
    $stmt->execute([$bankAccountId, $startDate, $endDate]);
    $bankTransactions = $stmt->fetchAll();

    // Get system transactions
    $stmt = $pdo->prepare("SELECT t.*,
                          GROUP_CONCAT(a.account_code SEPARATOR ', ') as account_codes
                          FROM transactions t
                          JOIN transaction_lines tl ON t.id = tl.transaction_id
                          JOIN chart_of_accounts a ON tl.account_id = a.id
                          WHERE t.transaction_date BETWEEN ? AND ?
                          AND t.is_reconciled = FALSE
                          AND a.account_code LIKE ?
                          GROUP BY t.id
                          ORDER BY t.transaction_date, t.id");
    $stmt->execute([$startDate, $endDate, '1%']); // Assuming bank accounts start with 1
    $systemTransactions = $stmt->fetchAll();

    // Calculate unreconciled balance
    $stmt = $pdo->prepare("SELECT SUM(amount) FROM bank_transactions
                          WHERE bank_account_id = ?
                          AND status = 'unreconciled'
                          AND transaction_date <= ?");
    $stmt->execute([$bankAccountId, $endDate]);
    $unreconciledBalance = $stmt->fetchColumn() ?? 0;

    // Calculate system balance
    $stmt = $pdo->prepare("SELECT SUM(CASE WHEN tl.debit > 0 THEN tl.debit ELSE -tl.credit END)
                          FROM transactions t
                          JOIN transaction_lines tl ON t.id = tl.transaction_id
                          JOIN chart_of_accounts a ON tl.account_id = a.id
                          WHERE a.account_code LIKE ?
                          AND t.is_reconciled = FALSE
                          AND t.transaction_date <= ?");
    $stmt->execute(['1%', $endDate]); // Assuming bank accounts start with 1
    $systemBalance = $stmt->fetchColumn() ?? 0;

    $discrepancy = $unreconciledBalance - $systemBalance;
}
?>

<div class="container-fluid">
    <h2>Bank Reconciliation</h2>

    <div class="card mb-3">
        <div class="card-header">
            Reconciliation Parameters
        </div>
        <div class="card-body">
            <form method="GET" id="reconciliationForm">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Bank Account*</label>
                            <select class="form-control" name="bank_account_id" required>
                                <?php foreach ($bankAccounts as $account): ?>
                                    <option value="<?= $account['id'] ?>"
                                        <?= $account['id'] == $bankAccountId ? 'selected' : '' ?>>
                                        <?= $account['bank_name'] ?> - <?= $account['account_number'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Start Date*</label>
                            <input type="date" class="form-control" name="start_date"
                                   value="<?= $startDate ?>" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>End Date*</label>
                            <input type="date" class="form-control" name="end_date"
                                   value="<?= $endDate ?>" required>
                        </div>
                    </div>
                    <div class="col-md-2 pt-4">
                        <button type="submit" class="btn btn-primary">Load Transactions</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php if ($bankAccountId): ?>
    <div class="card mb-3">
        <div class="card-header bg-info text-white">
            Reconciliation Summary
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <h5>Bank Statement</h5>
                            <h3><?= number_format($unreconciledBalance, 2) ?></h3>
                            <small>Unreconciled Balance</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <h5>System Records</h5>
                            <h3><?= number_format($systemBalance, 2) ?></h3>
                            <small>Outstanding Transactions</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <h5>Difference</h5>
                            <h3 class="<?= $discrepancy != 0 ? 'text-danger' : 'text-success' ?>">
                                <?= number_format($discrepancy, 2) ?>
                            </h3>
                            <small><?= $discrepancy != 0 ? 'Needs investigation' : 'Balanced' ?></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form method="POST" id="reconciliationMatchForm">
        <input type="hidden" name="bank_account_id" value="<?= $bankAccountId ?>">
        <input type="hidden" name="start_date" value="<?= $startDate ?>">
        <input type="hidden" name="end_date" value="<?= $endDate ?>">

        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-warning">
                        Bank Statement Transactions
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Description</th>
                                        <th>Amount</th>
                                        <th>Match</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($bankTransactions as $txn): ?>
                                    <tr class="<?= $txn['status'] !== 'unreconciled' ? 'table-success' : '' ?>">
                                        <td><?= $txn['transaction_date'] ?></td>
                                        <td><?= $txn['description'] ?></td>
                                        <td><?= number_format($txn['amount'], 2) ?></td>
                                        <td>
                                            <?php if ($txn['status'] === 'unreconciled'): ?>
                                                <select class="form-control form-control-sm" name="matches[<?= $txn['id'] ?>]">
                                                    <option value="">-- Select --</option>
                                                    <?php foreach ($systemTransactions as $sysTxn): ?>
                                                        <?php if (abs($sysTxn['amount'] - $txn['amount']) < 0.01): ?>
                                                            <option value="<?= $sysTxn['id'] ?>">
                                                                <?= $sysTxn['transaction_date'] ?> -
                                                                <?= $sysTxn['description'] ?> -
                                                                <?= number_format($sysTxn['amount'], 2) ?>
                                                            </option>
                                                        <?php endif; ?>
                                                    <?php endforeach; ?>
                                                </select>
                                            <?php else: ?>
                                                <span class="badge badge-success">Reconciled</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        System Transactions
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Description</th>
                                        <th>Accounts</th>
                                        <th>Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($systemTransactions as $txn): ?>
                                    <tr>
                                        <td><?= $txn['transaction_date'] ?></td>
                                        <td><?= $txn['description'] ?></td>
                                        <td><?= $txn['account_codes'] ?></td>
                                        <td><?= number_format($txn['amount'], 2) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-md-12 text-right">
                <button type="submit" class="btn btn-success btn-lg">
                    <i class="fas fa-check-circle"></i> Complete Reconciliation
                </button>
            </div>
        </div>
    </form>
    <?php endif; ?>
</div>

<script>
$(document).ready(function() {
    // Auto-match transactions with same amount and date
    $('select[name^="matches"]').each(function() {
        const bankTxnRow = $(this).closest('tr');
        const bankDate = bankTxnRow.find('td:eq(0)').text();
        const bankAmount = parseFloat(bankTxnRow.find('td:eq(2)').text().replace(/,/g, ''));

        $(this).find('option').each(function() {
            const optionText = $(this).text();
            const parts = optionText.split(' - ');
            const sysDate = parts[0];
            const sysAmount = parseFloat(parts[2].replace(/,/g, ''));

            if (bankDate === sysDate && Math.abs(bankAmount - sysAmount) < 0.01) {
                $(this).prop('selected', true);
                return false; // Break the loop
            }
        });
    });

    // Highlight matched rows
    $('select[name^="matches"]').change(function() {
        if ($(this).val()) {
            $(this).closest('tr').addClass('table-info');
        } else {
            $(this).closest('tr').removeClass('table-info');
        }
    });
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>