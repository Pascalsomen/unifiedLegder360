<?php

require_once __DIR__ . '/../../includes/header.php';

// ==================================================
// INITIAL CONFIGURATION & DATE SETUP
// ==================================================


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Date range filter with defaults
$start = $_GET['start'] ?? date('Y-m-01');
$end   = $_GET['end']   ?? date('Y-m-d');
$mess  = $_GET['msg']   ?? '';

// Normalize datetimes for queries
$start_dt = $start . ' 00:00:00';
$end_dt   = $end   . ' 23:59:59';

// System start date
$system_start = '2024-01-01';
$system_start_dt = $system_start . ' 00:00:00';

// Previous period calculation
$previous_end = date('Y-m-d', strtotime($start . ' -1 day'));
$previous_end_dt = $previous_end . ' 23:59:59';

// ==================================================
// DATA COLLECTION FUNCTIONS
// ==================================================

function getBalancesForRange($pdo, $startDateTime, $endDateTime) {
    $balances_stmt = $pdo->prepare('
        SELECT l.account_id,
               SUM(l.debit) AS total_debit,
               SUM(l.credit) AS total_credit
        FROM journal_entry_lines l
        JOIN journal_entries j ON j.id = l.journal_entry_id
        WHERE j.entry_date BETWEEN ? AND ?

        GROUP BY l.account_id
    ');
    $balances_stmt->execute([$startDateTime, $endDateTime]);
    $balances_raw = $balances_stmt->fetchAll(PDO::FETCH_ASSOC);

    $balances = [];
    foreach ($balances_raw as $b) {
        $balances[$b['account_id']] = [
            'debit' => (float) $b['total_debit'],
            'credit' => (float) $b['total_credit']
        ];
    }
    return $balances;
}

function getDepreciationForRange($pdo, $startDate, $endDate) {
    $stmt = $pdo->prepare('
        SELECT l.account_id, COALESCE(SUM(l.debit - l.credit),0) AS total
        FROM journal_entry_lines l
        INNER JOIN journal_entries j ON j.id = l.journal_entry_id
        WHERE j.entry_date >= ?
          AND j.entry_date <= ?
          AND l.account_id IN (19, 102)

        GROUP BY l.account_id
    ');

    $stmt->execute([
        $startDate . ' 00:00:00',
        $endDate   . ' 23:59:59'
    ]);

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $accumulatedDep = [
        19  => 0.0,
        102 => 0.0
    ];

    foreach ($rows as $row) {
        if ($row['account_id'] == 19) {
            $accumulatedDep[19] = abs((float)$row['total']);
        } else {
            $accumulatedDep[102] = (float)$row['total'];
        }
    }

    return $accumulatedDep;
}

function getTotalDepreciationRange($pdo, $startDate, $endDate) {
    $stmt = $pdo->prepare('
        SELECT COALESCE(SUM(l.debit - l.credit),0) AS total
        FROM journal_entry_lines l
        INNER JOIN journal_entries j ON j.id = l.journal_entry_id
        WHERE j.entry_date >= ?
          AND j.entry_date <= ?
          AND l.account_id = 102

    ');

    $stmt->execute([
        $startDate . ' 00:00:00',
        $endDate   . ' 23:59:59'
    ]);

    return (float)$stmt->fetchColumn();
}

function sumByIds($list, $ids, $period = 'current') {
    $total = 0;
    $field = $period . '_balance';
    foreach ($list as $a) {
        if (in_array($a['id'], $ids)) {
            $total += $a[$field];
        }
    }
    return $total;
}

function sumByParent($list, $parentId, $period = 'current') {
    $total = 0;
    $field = $period . '_balance';
    foreach ($list as $a) {
        if ($a['parent_id'] == $parentId) {
            $total += $a[$field];
        }
    }
    return $total;
}

function getAccountIdsByParent($accounts, $parentId, $includeSelf = true) {
    $ids = $includeSelf ? [$parentId] : [];
    foreach ($accounts as $acc) {
        if ($acc['parent_id'] == $parentId) {
            $ids[] = $acc['id'];
            $ids = array_merge($ids, getAccountIdsByParent($accounts, $acc['id'], false));
        }
    }
    return $ids;
}

function formatChange($change) {
    return number_format(abs($change), 0);
}

// ==================================================
// DATA PROCESSING
// ==================================================

// Fetch all accounts
$accounts_stmt = $pdo->query('SELECT * FROM chart_of_accounts ORDER BY account_code');
$accounts = $accounts_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get balances for current and previous periods
$current_balances = getBalancesForRange($pdo, $system_start_dt, $end_dt);
$previous_balances = getBalancesForRange($pdo, $system_start_dt, $previous_end_dt);

// Get depreciation data
$current_accumulatedDep = getDepreciationForRange($pdo, $system_start, $end);
$total_depreciation_current = getTotalDepreciationRange($pdo, $system_start, $end);
$previous_accumulatedDep = getDepreciationForRange($pdo, $system_start, $previous_end);
$total_depreciation_previous = getTotalDepreciationRange($pdo, $system_start, $previous_end);

// Compute account balances
foreach ($accounts as &$a) {
    $current_bal = $current_balances[$a['id']] ?? ['debit' => 0, 'credit' => 0];
    $previous_bal = $previous_balances[$a['id']] ?? ['debit' => 0, 'credit' => 0];

    switch ($a['account_type']) {
        case 'asset':
            $current_assetBal = $current_bal['debit'] - $current_bal['credit'];
            $previous_assetBal = $previous_bal['debit'] - $previous_bal['credit'];
            $current_dep = $current_accumulatedDep[$a['id']] ?? 0;
            $previous_dep = $previous_accumulatedDep[$a['id']] ?? 0;

            if ($a['parent_id'] == 14) {
                $a['current_balance'] = max(0, $current_assetBal - $current_dep);
                $a['previous_balance'] = max(0, $previous_assetBal - $previous_dep);
            } else {
                $a['current_balance'] = $current_assetBal;
                $a['previous_balance'] = $previous_assetBal;
            }
            break;

        case 'expense':
            $a['current_balance'] = $current_bal['debit'] - $current_bal['credit'];
            $a['previous_balance'] = $previous_bal['debit'] - $previous_bal['credit'];
            break;

        default:
            $a['current_balance'] = $current_bal['credit'] - $current_bal['debit'];
            $a['previous_balance'] = $previous_bal['credit'] - $previous_bal['debit'];
    }
}
unset($a);

// Group accounts by type
$assetsOnly = array_filter($accounts, fn($a) => $a['account_type'] === 'asset');
$liabilities = array_filter($accounts, fn($a) => $a['account_type'] === 'liability');
$equity = array_filter($accounts, fn($a) => $a['account_type'] === 'equity');

// ==================================================
// FINANCIAL CALCULATIONS
// ==================================================

// Profit & Loss Calculations
$revenueTotal_current = sumByIds($accounts, getAccountIdsByParent($accounts, 110), 'current');
$revenueTotal_previous = sumByIds($accounts, getAccountIdsByParent($accounts, 110), 'previous');

$cogsTotal_current = sumByIds($accounts, getAccountIdsByParent($accounts, 49), 'current');
$cogsTotal_previous = sumByIds($accounts, getAccountIdsByParent($accounts, 49), 'previous');

$otherIncome_current = sumByIds($accounts, getAccountIdsByParent($accounts, 44), 'current');
$otherIncome_previous = sumByIds($accounts, getAccountIdsByParent($accounts, 44), 'previous');

$adminExpenses_current = sumByIds($accounts, getAccountIdsByParent($accounts, 59), 'current');
$adminExpenses_previous = sumByIds($accounts, getAccountIdsByParent($accounts, 59), 'previous');

$otherExpenses_current = sumByIds($accounts, getAccountIdsByParent($accounts, 53), 'current');
$otherExpenses_previous = sumByIds($accounts, getAccountIdsByParent($accounts, 53), 'previous');

$financeCost_current = sumByIds($accounts, getAccountIdsByParent($accounts, 66), 'current');
$financeCost_previous = sumByIds($accounts, getAccountIdsByParent($accounts, 66), 'previous');

$grossProfit_current = $revenueTotal_current - $cogsTotal_current;
$grossProfit_previous = $revenueTotal_previous - $cogsTotal_previous;

$operatingProfit_current = $grossProfit_current + $otherIncome_current - $adminExpenses_current - $otherExpenses_current;
$operatingProfit_previous = $grossProfit_previous + $otherIncome_previous - $adminExpenses_previous - $otherExpenses_previous;

$net_profit_current = $operatingProfit_current - $financeCost_current;
$net_profit_previous = $operatingProfit_previous - $financeCost_previous;

// Tax Calculations
$taxRate = 0.28;
$taxExpense_current = $net_profit_current * $taxRate;
$taxExpense_previous = $net_profit_previous * $taxRate;

// Asset Calculations
$ppeTotal_current = sumByIds($assetsOnly, getAccountIdsByParent($accounts, 14), 'current') - $total_depreciation_current;
$ppeTotal_previous = sumByIds($assetsOnly, getAccountIdsByParent($accounts, 14), 'previous') - $total_depreciation_previous;

$investment_current = sumByParent($assetsOnly, 610, 'current');
$investment_previous = sumByParent($assetsOnly, 610, 'previous');

$cashTotal_current = sumByIds($assetsOnly, getAccountIdsByParent($accounts, 3), 'current');
$cashTotal_previous = sumByIds($assetsOnly, getAccountIdsByParent($accounts, 3), 'previous');

$arTotal_current = sumByParent($assetsOnly, 7, 'current');
$arTotal_previous = sumByParent($assetsOnly, 7, 'previous');

// Inventory calculation
$stockTotal_current = 0;
$stockTotal_previous = 0;
$stockIds = getAccountIdsByParent($accounts, 9);
foreach ($stockIds as $sid) {
    $current_bal = $current_balances[$sid] ?? ['debit' => 0, 'credit' => 0];
    $previous_bal = $previous_balances[$sid] ?? ['debit' => 0, 'credit' => 0];
    $stockTotal_current += ($current_bal['debit'] - $current_bal['credit']);
    $stockTotal_previous += ($previous_bal['debit'] - $previous_bal['credit']);
}

$advanceTotal_current = sumByIds($assetsOnly, getAccountIdsByParent($accounts, 94), 'current');
$advanceTotal_previous = sumByIds($assetsOnly, getAccountIdsByParent($accounts, 94), 'previous');

$advancepayment_current = sumByParent($assetsOnly, 98, 'current');
$advancepayment_previous = sumByParent($assetsOnly, 98, 'previous');

$withholdings3_current = sumByParent($assetsOnly, 611, 'current');
$withholdings3_previous = sumByParent($assetsOnly, 611, 'previous');

// Liability Calculations
$taxes_current = sumByParent($liabilities, 609, 'current');
$taxes_previous = sumByParent($liabilities, 609, 'previous');

$accrued_current = sumByParent($liabilities, 24, 'current');
$accrued_previous = sumByParent($liabilities, 24, 'previous');

$long_current = sumByIds($liabilities, getAccountIdsByParent($accounts, 26), 'current');
$long_previous = sumByIds($liabilities, getAccountIdsByParent($accounts, 26), 'previous');

$payables_current = sumByIds($liabilities, getAccountIdsByParent($accounts, 22), 'current');
$payables_previous = sumByIds($liabilities, getAccountIdsByParent($accounts, 22), 'previous');

$otherpayable_current = sumByIds($liabilities, getAccountIdsByParent($accounts, 578), 'current');
$otherpayable_previous = sumByIds($liabilities, getAccountIdsByParent($accounts, 578), 'previous');

$cit_current = sumByIds($liabilities, getAccountIdsByParent($accounts, 966), 'current');
$cit_previous = sumByIds($liabilities, getAccountIdsByParent($accounts, 966), 'previous');

// Equity Calculations
$shareCapital_current = sumByIds($equity, getAccountIdsByParent($accounts, 30), 'current');
$shareCapital_previous = sumByIds($equity, getAccountIdsByParent($accounts, 30), 'previous');

$retained_current = sumByIds($equity, [32], 'current');
$retained_previous = sumByIds($equity, [32], 'previous');
$previous_proo_current = sumByIds($equity, [947], 'current');
$previous_proo_previous = sumByIds($equity, [947], 'previous');

$openning_current = sumByIds($equity, [78], 'current');
$openning_previous = sumByIds($equity, [78], 'previous');

$Expropriation_current = sumByIds($equity, [269], 'current');
$Expropriation_previous = sumByIds($equity, [269], 'previous');

$Revaluation_surplus_current = sumByIds($equity, [268], 'current');
$Revaluation_surplus_previous = sumByIds($equity, [268], 'previous');

// ==================================================
// TOTALS CALCULATION
// ==================================================

// Assets Totals
$total_fixed_assets_current = $ppeTotal_current + $investment_current;
$total_fixed_assets_previous = $ppeTotal_previous + $investment_previous;

$total_current_assets_current = $cashTotal_current + $arTotal_current + $stockTotal_current + $advanceTotal_current + $advancepayment_current + $withholdings3_current;
$total_current_assets_previous = $cashTotal_previous + $arTotal_previous + $stockTotal_previous + $advanceTotal_previous + $advancepayment_previous + $withholdings3_previous;

$total_assets_current = $total_fixed_assets_current + $total_current_assets_current;
$total_assets_previous = $total_fixed_assets_previous + $total_current_assets_previous;

// Liabilities Totals
$total_current_liabilities_current = $payables_current + $taxes_current + $accrued_current + $otherpayable_current;
$total_current_liabilities_previous = $payables_previous + $taxes_previous + $accrued_previous + $otherpayable_previous;

$total_liabilities_current = $total_current_liabilities_current + $long_current + $taxExpense_current - $cit_current;
$total_liabilities_previous = $total_current_liabilities_previous + $long_previous + $taxExpense_previous - $cit_previous;

// Equity Totals
$total_equity_current = $shareCapital_current + $Expropriation_current + $Revaluation_surplus_current + $retained_current + $openning_current + $previous_proo_current + ($net_profit_current - $taxExpense_current);
$total_equity_previous = $shareCapital_previous + $Expropriation_previous + $Revaluation_surplus_previous + $retained_previous + $openning_previous + $previous_proo_previous + ($net_profit_previous - $taxExpense_previous);

// Final Totals
$total_liabilities_equity_current = $total_liabilities_current + $total_equity_current;
$total_liabilities_equity_previous = $total_liabilities_previous + $total_equity_previous;

// Balance Check
$balance_difference_current = $total_assets_current - $total_liabilities_equity_current;
$balance_difference_previous = $total_assets_previous - $total_liabilities_equity_previous;

// Display Labels
$current_label = date('M j, Y', strtotime($end));
$prev_label = 'Closing at ' . date('M j, Y', strtotime($previous_end));

// ==================================================
// CIT ENTRY HANDLING
// ==================================================

if (isset($_POST['save_cit'])) {
    $entry_date = $_POST['entry_date'];
    $description = $_POST['description'];
    $reference = $_POST['reference'];
    $_cit_amount = $_POST['amount'] ?? [];
    $line_descriptions = 'cit added';
    $current_user_id = $_SESSION['user_id'] ?? null;

    $stmt = $pdo->prepare('INSERT INTO journal_entries (entry_date, description, reference, created_by, created_at) VALUES (:date, :desc, :ref, :created_by, NOW())');
    $stmt->execute([':date' => $entry_date, ':desc' => $description, ':ref' => $reference, ':created_by' => $current_user_id]);
    $journal_id = $pdo->lastInsertId();

    $stmt2 = $pdo->prepare('INSERT INTO journal_entry_lines (journal_entry_id, account_id,line_description, debit, credit, currency, exchange_rate) VALUES (:jid, :acc, :line_description, :debit, :credit, :currency, :exchange_rate)');
    $stmt2->execute([
        ':jid' => $journal_id,
        ':acc' => 966,
        ':line_description' => $line_descriptions,
        ':debit' => 0,
        ':credit' => $_cit_amount,
        ':currency' => 'Rwf',
        ':exchange_rate' => 1,
    ]);

    echo "<script>window.location.href = 'index?resto=balance_fixed&start=$start&end=$end&msg=1';</script>";
    exit();
}
?>

<div class="container mt-5">
    <!-- Success Alert -->
    <?php if ($mess == 1): ?>
    <div id="citAlert" class="cit-alert">
        <div class="alert-text">
            <span class="alert-badge">Done</span>
            Income Tax Payable entry has been successfully added.
        </div>
        <button type="button" class="alert-close" onclick="closeAlert()">×</button>
    </div>
    <?php endif; ?>

    <!-- Date Filter -->
    <div class="no-print mb-4">
        <form method="get" class="row g-2">
            <div class="col-auto">
                <label class="form-label">Start Date:</label>
                <input type="hidden" name="resto" value="balance_fixed">
                <input type="date" name="start" value="<?= htmlspecialchars($start) ?>" class="form-control">
            </div>
            <div class="col-auto">
                <label class="form-label">End Date:</label>
                <input type="date" name="end" value="<?= htmlspecialchars($end) ?>" class="form-control">
            </div>
            <div class="col-auto align-self-end">
                <button class="btn btn-primary">Apply Filter</button>
            </div>
        </form>
    </div>

    <!-- Balance Check -->
    <div class="balance-check <?= abs($balance_difference_current) < 100 ? 'balanced' : 'unbalanced' ?> mb-4">
        <?php if (abs($balance_difference_current) < 100): ?>
            ✓ Current Period Balance Sheet is Balanced
        <?php else: ?>
            ⚠ Current Period Balance Sheet Difference: <?= number_format($balance_difference_current, 0) ?>
        <?php endif; ?>
    </div>

    <!-- Action Buttons -->
    <div class="no-print mb-4">
        <button onclick="printInvoice()" class="btn btn-secondary">Print</button>
        <button type="button" class="btn btn-success" style="background-color: #8b2626ff !important; border-color: #8b2626ff !important;"
            onclick="saveBalanceSheetFixedAsExcel('balanceSheetFixedTable', 'balance_sheet_fixed_<?= htmlspecialchars($start) ?>_to_<?= htmlspecialchars($end) ?>.xls')">
            Export to Excel
        </button>
    </div>

    <!-- Balance Sheet Content -->
    <div id="content" class="card">
        <div class="card-header">
            <h4 class="mb-0">Balance Sheet - Vertical Format</h4>
            <p class="text-muted mb-0">Period Comparison:
                <span class="period-label"><?= htmlspecialchars($prev_label) ?></span> vs
                <span class="period-label">Current (<?= htmlspecialchars($current_label) ?>)</span>
            </p>
        </div>
        <div class="card-body">
            <!-- Balance Sheet Table -->
            <div class="table-responsive">
                <table id="balanceSheetFixedTable" class="table table-bordered balance-sheet-table">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 45%">Account</th>
                            <th style="width: 10%">Note</th>
                            <th class="text-end" style="width: 15%">Previous<br><small><?= htmlspecialchars($prev_label) ?></small></th>
                            <th class="text-end" style="width: 15%">Current<br><small><?= htmlspecialchars($current_label) ?></small></th>
                            <th class="text-end" style="width: 15%">Change</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- ASSETS SECTION -->
                        <tr class="account-group"><td colspan="5"><strong>ASSETS</strong></td></tr>

                        <!-- Fixed Assets -->
                        <tr class="account-subgroup"><td colspan="5"><strong>Fixed Assets / Non-Current Assets</strong></td></tr>

                        <tr>
                            <td class="account-name">Property, Plant and Equipment</td>
                            <td><a href="notes.php?note=1" class="note-link">1</a></td>
                            <td class="text-end"><?= number_format($ppeTotal_previous, 0) ?></td>
                            <td class="text-end"><?= number_format($ppeTotal_current, 0) ?></td>
                            <td class="text-end"><?= formatChange($ppeTotal_current - $ppeTotal_previous) ?></td>
                        </tr>

                        <tr>
                            <td class="account-name">Investments</td>
                            <td><a href="notes.php?note=2" class="note-link">2</a></td>
                            <td class="text-end"><?= number_format($investment_previous, 0) ?></td>
                            <td class="text-end"><?= number_format($investment_current, 0) ?></td>
                            <td class="text-end"><?= formatChange($investment_current - $investment_previous) ?></td>
                        </tr>

                        <tr class="account-total">
                            <td><strong>TOTAL FIXED ASSETS</strong></td>
                            <td></td>
                            <td class="text-end"><strong><?= number_format($total_fixed_assets_previous, 0) ?></strong></td>
                            <td class="text-end"><strong><?= number_format($total_fixed_assets_current, 0) ?></strong></td>
                            <td class="text-end"><strong><?= formatChange($total_fixed_assets_current - $total_fixed_assets_previous) ?></strong></td>
                        </tr>

                        <!-- Current Assets -->
                        <tr class="account-subgroup"><td colspan="5"><strong>Current Assets</strong></td></tr>

                        <tr>
                            <td class="account-name">Cash and Cash Equivalent</td>
                            <td><a href="notes.php?note=3" class="note-link">3</a></td>
                            <td class="text-end"><?= number_format($cashTotal_previous, 0) ?></td>
                            <td class="text-end"><?= number_format($cashTotal_current, 0) ?></td>
                            <td class="text-end"><?= formatChange($cashTotal_current - $cashTotal_previous) ?></td>
                        </tr>

                        <tr>
                            <td class="account-name">Accounts Receivable</td>
                            <td><a href="notes.php?note=4" class="note-link">4</a></td>
                            <td class="text-end"><?= number_format($arTotal_previous, 0) ?></td>
                            <td class="text-end"><?= number_format($arTotal_current, 0) ?></td>
                            <td class="text-end"><?= formatChange($arTotal_current - $arTotal_previous) ?></td>
                        </tr>

                        <tr>
                            <td class="account-name">Inventory / Stock</td>
                            <td><a href="notes.php?note=5" class="note-link">5</a></td>
                            <td class="text-end"><?= number_format($stockTotal_previous, 0) ?></td>
                            <td class="text-end"><?= number_format($stockTotal_current, 0) ?></td>
                            <td class="text-end"><?= formatChange($stockTotal_current - $stockTotal_previous) ?></td>
                        </tr>

                        <tr>
                            <td class="account-name">Prepayments</td>
                            <td><a href="notes.php?note=6" class="note-link">6</a></td>
                            <td class="text-end"><?= number_format($advancepayment_previous, 0) ?></td>
                            <td class="text-end"><?= number_format($advancepayment_current, 0) ?></td>
                            <td class="text-end"><?= formatChange($advancepayment_current - $advancepayment_previous) ?></td>
                        </tr>

                        <tr>
                            <td class="account-name">Business Advance</td>
                            <td><a href="notes.php?note=7" class="note-link">7</a></td>
                            <td class="text-end"><?= number_format($advanceTotal_previous, 0) ?></td>
                            <td class="text-end"><?= number_format($advanceTotal_current, 0) ?></td>
                            <td class="text-end"><?= formatChange($advanceTotal_current - $advanceTotal_previous) ?></td>
                        </tr>

                        <tr>
                            <td class="account-name">Withholding Taxes</td>
                            <td><a href="notes.php?note=8" class="note-link">8</a></td>
                            <td class="text-end"><?= number_format($withholdings3_previous, 0) ?></td>
                            <td class="text-end"><?= number_format($withholdings3_current, 0) ?></td>
                            <td class="text-end"><?= formatChange($withholdings3_current - $withholdings3_previous) ?></td>
                        </tr>

                        <tr class="account-total">
                            <td><strong>TOTAL CURRENT ASSETS</strong></td>
                            <td></td>
                            <td class="text-end"><strong><?= number_format($total_current_assets_previous, 0) ?></strong></td>
                            <td class="text-end"><strong><?= number_format($total_current_assets_current, 0) ?></strong></td>
                            <td class="text-end"><strong><?= formatChange($total_current_assets_current - $total_current_assets_previous) ?></strong></td>
                        </tr>

                        <!-- Total Assets -->
                        <tr class="final-total">
                            <td><strong>TOTAL ASSETS</strong></td>
                            <td></td>
                            <td class="text-end"><strong><?= number_format($total_assets_previous, 0) ?></strong></td>
                            <td class="text-end"><strong><?= number_format($total_assets_current, 0) ?></strong></td>
                            <td class="text-end"><strong><?= formatChange($total_assets_current - $total_assets_previous) ?></strong></td>
                        </tr>

                        <!-- LIABILITIES & EQUITY SECTION -->
                        <tr class="account-group"><td colspan="5"><strong>EQUITY AND LIABILITIES</strong></td></tr>

                        <!-- Current Liabilities -->
                        <tr class="account-subgroup"><td colspan="5"><strong>Current Liabilities</strong></td></tr>

                        <tr>
                            <td class="account-name">Payables / Suppliers</td>
                            <td><a href="notes.php?note=9" class="note-link">9</a></td>
                            <td class="text-end"><?= number_format($payables_previous, 0) ?></td>
                            <td class="text-end"><?= number_format($payables_current, 0) ?></td>
                            <td class="text-end"><?= formatChange($payables_current - $payables_previous) ?></td>
                        </tr>

                        <tr>
                            <td class="account-name">Taxes Payable</td>
                            <td><a href="notes.php?note=10" class="note-link">10</a></td>
                            <td class="text-end"><?= number_format($taxes_previous, 0) ?></td>
                            <td class="text-end"><?= number_format($taxes_current, 0) ?></td>
                            <td class="text-end"><?= formatChange($taxes_current - $taxes_previous) ?></td>
                        </tr>

                        <tr>
                            <td class="account-name">
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="openCITModal()">CIT Payable</button>
                            </td>
                            <td></td>
                            <td class="text-end"><?= number_format($taxExpense_previous - $cit_previous, 0) ?></td>
                            <td class="text-end"><?= number_format($taxExpense_current - $cit_current, 0) ?></td>
                            <td class="text-end"><?= formatChange(($taxExpense_current - $cit_current) - ($taxExpense_previous - $cit_previous)) ?></td>
                        </tr>

                        <tr>
                            <td class="account-name">Other Payables</td>
                            <td><a href="notes.php?note=11" class="note-link">11</a></td>
                            <td class="text-end"><?= number_format($otherpayable_previous, 0) ?></td>
                            <td class="text-end"><?= number_format($otherpayable_current, 0) ?></td>
                            <td class="text-end"><?= formatChange($otherpayable_current - $otherpayable_previous) ?></td>
                        </tr>

                        <tr>
                            <td class="account-name">Accrued Expenses</td>
                            <td><a href="notes.php?note=12" class="note-link">12</a></td>
                            <td class="text-end"><?= number_format($accrued_previous, 0) ?></td>
                            <td class="text-end"><?= number_format($accrued_current, 0) ?></td>
                            <td class="text-end"><?= formatChange($accrued_current - $accrued_previous) ?></td>
                        </tr>

                        <!-- Non-Current Liabilities -->
                        <tr class="account-subgroup"><td colspan="5"><strong>Non-Current Liabilities</strong></td></tr>

                        <tr>
                            <td class="account-name">Long Term Borrowing</td>
                            <td><a href="notes.php?note=13" class="note-link">13</a></td>
                            <td class="text-end"><?= number_format($long_previous, 0) ?></td>
                            <td class="text-end"><?= number_format($long_current, 0) ?></td>
                            <td class="text-end"><?= formatChange($long_current - $long_previous) ?></td>
                        </tr>

                        <!-- Total Liabilities -->
                        <tr class="account-total">
                            <td><strong>TOTAL LIABILITIES</strong></td>
                            <td></td>
                            <td class="text-end"><strong><?= number_format($total_liabilities_previous, 0) ?></strong></td>
                            <td class="text-end"><strong><?= number_format($total_liabilities_current, 0) ?></strong></td>
                            <td class="text-end"><strong><?= formatChange($total_liabilities_current - $total_liabilities_previous) ?></strong></td>
                        </tr>

                        <!-- Equity -->
                        <tr class="account-subgroup"><td colspan="5"><strong>Equity</strong></td></tr>

                        <tr>
                            <td class="account-name">Share Capital</td>
                            <td></td>
                            <td class="text-end"><?= number_format($shareCapital_previous, 0) ?></td>
                            <td class="text-end"><?= number_format($shareCapital_current, 0) ?></td>
                            <td class="text-end"><?= formatChange($shareCapital_current - $shareCapital_previous) ?></td>
                        </tr>

                        <tr>
                            <td class="account-name">Retained Earnings</td>
                            <td></td>
                            <td class="text-end"><?= number_format($retained_previous, 0) ?></td>
                            <td class="text-end"><?= number_format($retained_current, 0) ?></td>
                            <td class="text-end"><?= formatChange($retained_current - $retained_previous) ?></td>
                        </tr>

                        <tr>
                            <td class="account-name">Expropriation</td>
                            <td></td>
                            <td class="text-end"><?= number_format($Expropriation_previous, 0) ?></td>
                            <td class="text-end"><?= number_format($Expropriation_current, 0) ?></td>
                            <td class="text-end"><?= formatChange($Expropriation_current - $Expropriation_previous) ?></td>
                        </tr>

                        <tr>
                            <td class="account-name">Revaluation Surplus</td>
                            <td></td>
                            <td class="text-end"><?= number_format($Revaluation_surplus_previous, 0) ?></td>
                            <td class="text-end"><?= number_format($Revaluation_surplus_current, 0) ?></td>
                            <td class="text-end"><?= formatChange($Revaluation_surplus_current - $Revaluation_surplus_previous) ?></td>
                        </tr>

                        <tr>
                            <td class="account-name">Opening Equity Balance</td>
                            <td></td>
                            <td class="text-end"><?= number_format($openning_previous, 0) ?></td>
                            <td class="text-end"><?= number_format($openning_current, 0) ?></td>
                            <td class="text-end"><?= formatChange($openning_current - $openning_previous) ?></td>
                        </tr>

                        <tr>
                            <td class="account-name">Previous Profit</td>
                            <td></td>
                            <td class="text-end"><?= number_format($p_previous = $previous_proo_current + $net_profit_previous - $taxExpense_previous, 0) ?></td>
                            <td class="text-end"><?= number_format($previous_proo_current + $net_profit_previous - $taxExpense_previous, 0) ?></td>
                            <td class="text-end"><?= formatChange($p_previous - $p_previous) ?></td>
                        </tr>

                        <tr>
                            <td class="account-name">Net Profit</td>
                            <td></td>
                            <td class="text-end"><?= number_format($net_profit_previous - $taxExpense_previous, 0) ?></td>
                            <td class="text-end"><?= number_format($net_profit_current - $taxExpense_current, 0) ?></td>
                            <td class="text-end"><?= formatChange(($net_profit_current - $taxExpense_current) - ($net_profit_previous - $taxExpense_previous)) ?></td>
                        </tr>

                        <!-- Total Equity -->
                        <tr class="account-total">
                            <td><strong>TOTAL EQUITY</strong></td>
                            <td></td>
                            <td class="text-end"><strong><?= number_format($total_equity_previous, 0) ?></strong></td>
                            <td class="text-end"><strong><?= number_format($total_equity_current, 0) ?></strong></td>
                            <td class="text-end"><strong><?= formatChange($total_equity_current - $total_equity_previous) ?></strong></td>
                        </tr>

                        <!-- Total Liabilities & Equity -->
                        <tr class="final-total">
                            <td><strong>TOTAL EQUITY & LIABILITIES</strong></td>
                            <td></td>
                            <td class="text-end"><strong><?= number_format($total_liabilities_equity_previous, 0) ?></strong></td>
                            <td class="text-end"><strong><?= number_format($total_liabilities_equity_current, 0) ?></strong></td>
                            <td class="text-end"><strong><?= formatChange($total_liabilities_equity_current - $total_liabilities_equity_previous) ?></strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- CIT Modal -->
<div id="citModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Record CIT Payable</h3>
            <span class="close-btn" onclick="closeCITModal()">&times;</span>
        </div>
        <form method="POST" action="">
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Date</label>
                    <input type="date" name="entry_date" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <input type="text" name="description" class="form-control" value="" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Reference</label>
                    <input type="text" name="reference" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Amount (Frw)</label>
                    <input type="number" name="amount" class="form-control" required>
                </div>
                <input type="hidden" name="currency" value="RWF">
                <input type="hidden" name="exchange_rate" value="1">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeCITModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" name="save_cit">Save Journal Entry</button>
            </div>
        </form>
    </div>
</div>

<!-- Add necessary CSS -->
<style>
    table tr:hover { background-color: #fad4d4 !important; }
    .balance-sheet-table th, .balance-sheet-table td { padding: 8px 12px; border: 1px solid #dee2e6; }
    .account-group { font-weight: 600; background-color: #e9ecef; }
    .account-subgroup { font-weight: 500; background-color: #f8f9fa; }
    .account-name { padding-left: 20px; }
    .account-total { font-weight: 600; border-top: 2px solid #333; background-color: #f8f9fa; }
    .final-total { font-weight: 700; font-size: 1.1em; background-color: #d1ecf1; }
    .note-link { text-decoration: none; color: #0d6efd; font-weight: normal; font-size: 0.8em; margin-left: 10px; }
    .note-link:hover { text-decoration: underline; }
    .balance-check { font-weight: bold; padding: 10px; margin: 10px 0; border-radius: 5px; }
    .balanced { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .unbalanced { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    .period-label { font-size: 0.9em; color: #666; font-weight: normal; }
    .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; }
    .modal-box { background: #fff; width: 420px; margin: 5% auto; border-radius: 6px; padding: 20px; }
    .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
    .close-btn { cursor: pointer; font-size: 24px; }
    .cit-alert { display: flex; justify-content: space-between; align-items: center; background: #d1e7dd; border-left: 5px solid #198754; padding: 12px 16px; margin-bottom: 15px; border-radius: 6px; }
    .alert-text { color: #0f5132; }
    .alert-badge { background: #198754; color: #fff; padding: 3px 8px; border-radius: 4px; margin-right: 8px; font-size: 12px; }
    .alert-close { background: none; border: none; font-size: 20px; cursor: pointer; color: #0f5132; }
    @media print {
        .no-print { display: none !important; }
        .balance-check { display: none !important; }
    }
</style>

<script>
    // Alert Functions
    function closeAlert() {
        const alertBox = document.getElementById('citAlert');
        if (alertBox) {
            alertBox.remove();
        }
    }

    // Modal Functions
    function openCITModal() {
        document.getElementById('citModal').style.display = 'block';
    }

    function closeCITModal() {
        document.getElementById('citModal').style.display = 'none';
    }

    // Close modal when clicking outside
    window.onclick = function(e) {
        const modal = document.getElementById('citModal');
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    }

    // Print Function
    function printInvoice() {
        window.print();
    }

    // Set note links to open in new tab
    document.addEventListener('DOMContentLoaded', function() {
        var noteLinks = document.querySelectorAll('.note-link');
        noteLinks.forEach(function(link) {
            link.target = '_blank';
        });
    });

    // Excel Export Function
    function saveBalanceSheetFixedAsExcel(tableId, filename) {
        var table = document.getElementById(tableId);
        if (!table) return;

        var exportTable = document.createElement('table');
        exportTable.style.width = '100%';

        // Add logo header
        var logoRow = exportTable.insertRow();
        var logoCell = logoRow.insertCell();
        logoCell.colSpan = 5;
        logoCell.innerHTML = '<div style="text-align: center; padding: 20px;"><h3>Balance Sheet - Vertical Format</h3><p>Period: <?= htmlspecialchars($start) ?> to <?= htmlspecialchars($end) ?></p></div>';

        // Copy table content with styles
        var originalRows = table.querySelectorAll('tr');
        for (var i = 0; i < originalRows.length; i++) {
            var newRow = exportTable.insertRow();
            var cells = originalRows[i].querySelectorAll('th, td');
            for (var j = 0; j < cells.length; j++) {
                var newCell = newRow.insertCell();
                newCell.innerHTML = cells[j].innerText || cells[j].textContent;

                // Apply styles based on row classes
                if (i === 0) {
                    newCell.style.backgroundColor = '#8b2626';
                    newCell.style.color = '#ffffff';
                    newCell.style.fontWeight = 'bold';
                }
                if (originalRows[i].classList.contains('account-group')) {
                    newCell.style.fontWeight = 'bold';
                    newCell.style.backgroundColor = '#e9ecef';
                }
                if (originalRows[i].classList.contains('account-subgroup')) {
                    newCell.style.fontWeight = '500';
                    newCell.style.backgroundColor = '#f8f9fa';
                }
                if (originalRows[i].classList.contains('account-total')) {
                    newCell.style.fontWeight = 'bold';
                    newCell.style.backgroundColor = '#f8f9fa';
                    newCell.style.borderTop = '2px solid #333';
                }
                if (originalRows[i].classList.contains('final-total')) {
                    newCell.style.fontWeight = 'bold';
                    newCell.style.backgroundColor = '#d1ecf1';
                }
            }
        }

        // Create and trigger download
        var html = exportTable.outerHTML;
        var blob = new Blob([html], { type: 'application/vnd.ms-excel' });
        var url = URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url;
        a.download = filename;
        a.click();
        URL.revokeObjectURL(url);
    }
</script>

<?php require_once __DIR__ . '/../../includes/footer.php';?>