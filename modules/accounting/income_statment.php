<?php include '../../includes/header.php';

// ==================================================
// INITIAL CONFIGURATION & DATE SETUP
// ==================================================


ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

$from = $_GET['from'] ?? date('2025-01-01');
$to = $_GET['to'] ?? date('Y-m-t');

// ==================================================
// DATA COLLECTION FUNCTIONS
// ==================================================

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

function sumByIds($list, $ids) {
    $total = 0;
    foreach ($list as $a) {
        if (in_array($a['id'], $ids)) {
            $total += $a['balance'];
        }
    }
    return $total;
}

// ==================================================
// DATA PROCESSING
// ==================================================

// Prepare date condition
$dateCondition = '';
$params = [];
if ($from && $to) {
    $dateCondition = ' AND j.entry_date BETWEEN :from AND :to';
    $params['from'] = $from;
    $params['to'] = $to;
}

// Get account balances
$sql = "
    SELECT l.account_id,
           SUM(l.debit) AS total_debit,
           SUM(l.credit) AS total_credit
    FROM journal_entry_lines l
    INNER JOIN journal_entries j ON j.id = l.journal_entry_id
    WHERE l.isdeleted = 0 $dateCondition
    GROUP BY l.account_id
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$balances_raw = $stmt->fetchAll(PDO::FETCH_ASSOC);

$balances = [];
foreach ($balances_raw as $b) {
    $balances[$b['account_id']] = [
        'debit' => (float) $b['total_debit'],
        'credit' => (float) $b['total_credit'],
    ];
}

// Get all accounts
$accounts_stmt = $pdo->query('SELECT * FROM chart_of_accounts ORDER BY account_code');
$accounts = $accounts_stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate balances for each account
foreach ($accounts as &$a) {
    $bal = $balances[$a['id']] ?? ['debit' => 0, 'credit' => 0];
    if ($a['account_type'] == 'asset' || $a['account_type'] == 'expense') {
        $a['balance'] = $bal['debit'] - $bal['credit'];
    } else {
        $a['balance'] = $bal['credit'] - $bal['debit'];
    }
}
unset($a);

// ==================================================
// INCOME STATEMENT CALCULATIONS
// ==================================================

$revenueTotal = sumByIds($accounts, getAccountIdsByParent($accounts, 110));
$cogsTotal = sumByIds($accounts, getAccountIdsByParent($accounts, 50));
$cogsTotal += sumByIds($accounts, getAccountIdsByParent($accounts, 51));
$otherIncome = sumByIds($accounts, getAccountIdsByParent($accounts, 44));
$adminExpenses = sumByIds($accounts, getAccountIdsByParent($accounts, 59));
$otherExpenses = sumByIds($accounts, getAccountIdsByParent($accounts, 53));
$financeCost = sumByIds($accounts, getAccountIdsByParent($accounts, 66));

$grossProfit = $revenueTotal - $cogsTotal;
$operatingProfit = $grossProfit + $otherIncome - $adminExpenses - $otherExpenses;
$profitBeforeTax = $operatingProfit - $financeCost;

// Tax calculations
$taxRate = 0.28;
$taxExpense = $profitBeforeTax * $taxRate;
$profitAfterTax = $profitBeforeTax - $taxExpense;
?>

<div class="container mt-5">
    <!-- Filters Section -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Income Statement Filter</h5>
        </div>
        <div class="card-body">
            <form method="get" action="index" class="row g-3">
                <input type="hidden" name="resto" value="income">
                <div class="col-md-4">
                    <label class="form-label">Date from:</label>
                    <input type="date" name="from" value="<?= htmlspecialchars($from) ?>" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Date to:</label>
                    <input type="date" name="to" value="<?= htmlspecialchars($to) ?>" class="form-control">
                </div>
                <div class="col-md-4 d-flex align-items-end gap-2">
                    <button class="btn btn-primary">Filter</button>
                    <a href="index?resto=income" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="mb-4">
        <button type="button" class="btn" style="background-color: #8b2626ff; border-color: #8b2626ff; color: white;"
            onclick="saveAsExcel('incomeTable', 'income_statement_<?= htmlspecialchars($from) ?>_to_<?= htmlspecialchars($to) ?>.xls')">
            Export to Excel
        </button>
        <button class="btn btn-secondary" onclick="printInvoice()">Print</button>
        <a href="index?resto=incomedetailed" class="btn btn-link">See detailed Income statement</a>
    </div>

    <!-- Income Statement Content -->
    <div class="card shadow">
        <div class="card-header text-center">
            <h4 class="mb-0">STATEMENT OF PROFIT OR LOSS</h4>
            <small class="text-muted">Period: <?= htmlspecialchars($from) ?> to <?= htmlspecialchars($to) ?></small>
        </div>

        <div class="card-body">
            <div id="content">
                <table class="table table-bordered table-sm" id="incomeTable">
                    <thead style="background-color: #7a2020 !important; color: #ffffff !important;">
                        <tr>
                            <th>Item</th>
                            <th>
                                <a href="index?resto=notes&from=<?= urlencode($from) ?>&to=<?= urlencode($to) ?>" style="color: #ffffff !important;">
                                    Note
                                </a>
                            </th>
                            <th class="text-end">Amount (Frw)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Revenue -->
                        <tr>
                            <td>Revenue</td>
                            <td>
                                <a href="index?resto=notes&note=15&from=<?= urlencode($from) ?>&to=<?= urlencode($to) ?>">
                                    1
                                </a>
                            </td>
                            <td class="text-end"><?= number_format($revenueTotal, 2) ?></td>
                        </tr>

                        <!-- Cost of Sales -->
                        <tr>
                            <td>Cost Of Sales</td>
                            <td>
                                <a href="index?resto=notes&note=16&from=<?= urlencode($from) ?>&to=<?= urlencode($to) ?>">
                                    2
                                </a>
                            </td>
                            <td class="text-end"><?= number_format($cogsTotal, 2) ?></td>
                        </tr>

                        <!-- Gross Profit -->
                        <tr class="fw-bold table-success">
                            <td>Gross Profit</td>
                            <td></td>
                            <td class="text-end"><?= number_format($grossProfit, 2) ?></td>
                        </tr>

                        <!-- Other Income -->
                        <tr>
                            <td>Other Income</td>
                            <td>
                                <a href="index?resto=notes&note=17&from=<?= urlencode($from) ?>&to=<?= urlencode($to) ?>">
                                    3
                                </a>
                            </td>
                            <td class="text-end"><?= number_format($otherIncome, 2) ?></td>
                        </tr>

                        <!-- Administrative Expenses -->
                        <tr>
                            <td>Administrative Expenses</td>
                            <td>
                                <a href="index?resto=notes&note=19&from=<?= urlencode($from) ?>&to=<?= urlencode($to) ?>">
                                    4
                                </a>
                            </td>
                            <td class="text-end">(<?= number_format($adminExpenses, 2) ?>)</td>
                        </tr>

                        <!-- Operating Expenses -->
                        <tr>
                            <td>Operating Expenses</td>
                            <td>
                                <a href="index?resto=notes&note=18&from=<?= urlencode($from) ?>&to=<?= urlencode($to) ?>">
                                    5
                                </a>
                            </td>
                            <td class="text-end">(<?= number_format($otherExpenses, 2) ?>)</td>
                        </tr>

                        <!-- Operating Profit -->
                        <tr class="fw-bold table-success">
                            <td>Operating Profit / (Loss)</td>
                            <td></td>
                            <td class="text-end"><?= number_format($operatingProfit, 2) ?></td>
                        </tr>

                        <!-- Finance Cost -->
                        <tr>
                            <td>Finance Cost</td>
                            <td>
                                <a href="index?resto=notes&note=20&from=<?= urlencode($from) ?>&to=<?= urlencode($to) ?>">
                                    6
                                </a>
                            </td>
                            <td class="text-end">(<?= number_format($financeCost, 2) ?>)</td>
                        </tr>

                        <!-- Profit Before Tax -->
                        <tr class="fw-bold table-primary">
                            <td>Profit Before Tax</td>
                            <td></td>
                            <td class="text-end"><?= number_format($profitBeforeTax, 2) ?></td>
                        </tr>

                        <!-- Tax Expense -->
                        <tr class="fw-bold table-primary">
                            <td>Tax Expense (28%)</td>
                            <td></td>
                            <td class="text-end"><?= number_format($taxExpense, 2) ?></td>
                        </tr>

                        <!-- Profit After Tax -->
                        <tr class="fw-bold table-primary">
                            <td>Profit After Tax</td>
                            <td></td>
                            <td class="text-end"><?= number_format($profitAfterTax, 2) ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add necessary CSS -->
<style>
    /* Make links inside thead white */
    .table thead th a,
    .table thead td a,
    thead th a,
    thead td a,
    #incomeTable thead th a,
    #incomeTable thead td a {
        color: #ffffff !important;
    }

    /* Print Styles */
    @media print {
        .table thead th a,
        .table thead td a,
        thead th a,
        thead td a,
        #incomeTable thead th a,
        #incomeTable thead td a {
            color: #ffffff !important;
        }

        /* Prevent browser from adding href text */
        a[href]:after {
            content: "" !important;
        }

        /* Make link just look like normal text */
        a {
            color: black !important;
            text-decoration: none !important;
        }

        /* Print only the content */
        body * {
            visibility: hidden;
        }

        #content,
        #content * {
            visibility: visible;
        }

        #content {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
        }

        /* Force browsers to print background colors */
        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color-adjust: exact !important;
        }

        /* Override table-light and force chocolate color for thead in print */
        .table thead th,
        .table thead td,
        thead th,
        thead td,
        #incomeTable thead th,
        #incomeTable thead td {
            background: #7a2020 !important;
            background-color: #7a2020 !important;
            color: #ffffff !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color-adjust: exact !important;
        }

        /* Add background colors for table rows in print */
        #incomeTable .table-success,
        .table-success {
            background-color: #d1e7dd !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color-adjust: exact !important;
        }

        #incomeTable .table-primary,
        .table-primary {
            background-color: #cff4fc !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color-adjust: exact !important;
        }

        /* Specific row targeting for better compatibility */
        #incomeTable tbody tr.fw-bold.table-success td {
            background-color: #d1e7dd !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        #incomeTable tbody tr.fw-bold.table-primary td {
            background-color: #cff4fc !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* Make totals bold in print */
        .fw-bold {
            font-weight: bold !important;
        }

        @page {
            size: A4 portrait;
            margin: 6mm;
        }

        /* Hide buttons and filters when printing */
        .btn, .card-header, .mb-4:first-child {
            display: none !important;
        }
    }

    /* Table styling */
    .table-bordered {
        border: 1px solid #dee2e6;
    }

    .table-success {
        background-color: #d1e7dd;
    }

    .table-primary {
        background-color: #cff4fc;
    }

    .text-end {
        text-align: right;
    }

    .fw-bold {
        font-weight: bold;
    }

    .text-muted {
        color: #6c757d;
    }
</style>

<script>
    // Print Function
    function printInvoice() {
        window.print();
    }

    // Excel Export Function
    function saveAsExcel(tableId, filename) {
        var table = document.getElementById(tableId);
        if (!table) return;

        var exportTable = document.createElement('table');
        exportTable.style.width = '100%';

        // Add logo header row
        var logoRow = exportTable.insertRow();
        var logoCell = logoRow.insertCell();
        logoCell.colSpan = 3;
        logoCell.innerHTML = `
            <div style="text-align: center; padding: 20px;">
                <img src="https://nsportsclub.rw/public/back/images/logo.jpeg" width="70" height="70"><br>
                <h3>NYARUTARAMA SPORTS TRUST CLUB LTD</h3>
                <p>
                    KG 13 Avenue 22, Kigali, Nyarutarama, Rwanda<br>
                    TIN/VAT Number: 103149499<br>
                    Email: info.nsportclub@gmail.com<br>
                    Phone: (250) 0788566526 | P.O.BOX 6487
                </p>
                <h4>STATEMENT OF PROFIT OR LOSS</h4>
                <p>Period: <?= htmlspecialchars($from) ?> to <?= htmlspecialchars($to) ?></p>
            </div>`;

        // Add the original table content
        var originalRows = table.querySelectorAll('tr');
        for (var i = 0; i < originalRows.length; i++) {
            var newRow = exportTable.insertRow();
            var cells = originalRows[i].querySelectorAll('th, td');
            for (var j = 0; j < cells.length; j++) {
                var newCell = newRow.insertCell();
                newCell.innerHTML = cells[j].innerText || cells[j].textContent;

                // Copy styles for important rows
                if (originalRows[i].classList.contains('fw-bold')) {
                    newCell.style.fontWeight = 'bold';
                }
                if (originalRows[i].classList.contains('table-success')) {
                    newCell.style.backgroundColor = '#d1e7dd';
                }
                if (originalRows[i].classList.contains('table-primary')) {
                    newCell.style.backgroundColor = '#cff4fc';
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

<?php
include '../../includes/footer.php';?>