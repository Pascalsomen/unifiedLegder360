<?php

require_once __DIR__ . '/../../includes/header.php';  // Include header

// ==================================================
// INITIAL CONFIGURATION & DATE SETUP
// ==================================================



// Default date range: current week (Monday to Sunday)
if (isset($_GET['from']) && $_GET['from'] !== '' &&
    isset($_GET['to'])   && $_GET['to']   !== '') {
    $from = $_GET['from'];
    $to   = $_GET['to'];
} else {
    $from = date('Y-m-d', strtotime('monday this week'));
    $to   = date('Y-m-d', strtotime('sunday this week'));
}

$selectedNote = $_GET['note'] ?? null;

// ==================================================
// CONFIGURATION & DATA
// ==================================================

// Note Configuration
$notesConfig = [
    '1' => ['title' => 'Property, Plant and Equipment', 'code' => '1200'],
    '2' => ['title' => 'Investments', 'code' => '120001'],
    '3' => ['title' => 'Cash and Cash Equivalent', 'code' => '1110'],
    '4' => ['title' => 'Accounts Receivable', 'code' => '1120'],
    '5' => ['title' => 'Inventory / Stock', 'code' => '1130'],
    '6' => ['title' => 'Prepayments', 'code' => '1202'],
    '7' => ['title' => 'Business Advance', 'code' => '1201'],
    '8' => ['title' => 'Withholding Taxes', 'code' => '110002'],
    '9' => ['title' => 'Payables / Suppliers', 'code' => '2110'],
    '10' => ['title' => 'Taxes Payable', 'code' => '210007'],
    '11' => ['title' => 'Other Payables', 'code' => '210006'],
    '12' => ['title' => 'Accrued Expenses', 'code' => '2120'],
    '13' => ['title' => 'Long Term Borrowing', 'code' => '2200'],
    '14' => ['title' => 'Sales', 'code' => '4403'],
    '15' => ['title' => 'Revenues', 'code' => '4000'],
    '16' => ['title' => 'Cost of Goods Sold', 'code' => '5100'],
    '17' => ['title' => 'Other Income', 'code' => '4400'],
    '18' => ['title' => 'Operating Expenses', 'code' => '5200'],
    '19' => ['title' => 'Administrative Expenses', 'code' => '5300'],
    '20' => ['title' => 'Finance Cost', 'code' => '5500'],
];

// Title accounts for showing all notes
$titleAccounts = [
    '4403', '4400', '5100', '5300', '5200', '5500',
    '1200', '1130', '1120', '1110', '2200', '2110', '2130', '3000','210006','2120','210007','1202','120001'
];

// Asset Types for Depreciation Rates
$asset_types = [
    'Land' => 0,
    'Building' => 5,
    'Motor Vehicle' => 25,
    'Plant & Machinery' => 5,
    'Computers' => 50,
    'Furniture' => 25,
    'Other Assets' => 25,
    'Gym and Kitchen Equipments' => 25,
    'Electronic equipment' => 25,
    'Work in Progress' => 25,
    'HardWare' => 25,
    'Other Equipment' => 25,
    'Intangible Assets' => 10,
];

// ==================================================
// HELPER FUNCTIONS
// ==================================================

function getAccountByCode($pdo, $code) {
    $stmt = $pdo->prepare('SELECT * FROM chart_of_accounts WHERE account_code = :code LIMIT 1');
    $stmt->execute([':code' => $code]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getChildren($pdo, $parentId) {
    $stmt = $pdo->prepare('SELECT * FROM chart_of_accounts WHERE parent_id = :pid ORDER BY account_code');
    $stmt->execute([':pid' => $parentId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAccountBalance($pdo, $accountId, $from, $to) {
    $stmt = $pdo->prepare('
        SELECT COALESCE(SUM(debit - credit), 0) AS balance
        FROM journal_entry_lines jel
        JOIN journal_entries je ON jel.journal_entry_id = je.id
        WHERE jel.account_id = :acc
          AND DATE(je.entry_date) <= :to
    ');
    $stmt->execute([':acc' => $accountId, ':to' => $to]);
    return (float) $stmt->fetchColumn();
}

function getReceivableDetails($pdo, $from, $to) {
    $stmt = $pdo->prepare('
        SELECT
            DATE(v.salesDt) as sale_date,
            v.custnm as customer_name,
            (v.totAmt - v.taxAmtB) as net_amount
        FROM tbl_vsdc_sales v
        INNER JOIN journal_entries je ON DATE(v.salesDt) = DATE(je.entry_date) AND je.reference_type = "sales_date"
        WHERE v.has_refund = "0"
          AND v.salesTyCd = "N"
          AND v.rcptTyCd = "S"
          AND v.pmtTyCd NOT IN ("06", "05", "03")
          AND DATE(v.salesDt) BETWEEN :from AND :to
          AND DATE(je.entry_date) BETWEEN :from AND :to
        GROUP BY v.transaction_id
        ORDER BY sale_date, customer_name
    ');
    $stmt->execute([':from' => $from, ':to' => $to]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getNovemberMonthNumber() {
    return 11;
}

function getAssetTypeTotals($pdo, $toDate, $asset_types) {
    $toYear = date('Y', strtotime($toDate));
    $toMonth = date('m', strtotime($toDate));
    $novemberMonth = getNovemberMonthNumber();

    $stmt = $pdo->prepare('
        SELECT
            fa.asset_type,
            COUNT(fa.id) as asset_count,
            SUM(fa.purchase_cost) as total_purchase_cost,
            SUM(
                (
                    SELECT COALESCE(SUM(ad2.depreciation_amount), 0)
                    FROM asset_depreciation ad2
                    WHERE ad2.asset_id = fa.id
                    AND (
                        ad2.year < :to_year
                        OR (ad2.year = :to_year AND ad2.month <= :to_month)
                    )
                    AND ad2.month >= :november_month
                )
            ) as total_accumulated_depreciation,
            SUM(fa.purchase_cost) - SUM(
                (
                    SELECT COALESCE(SUM(ad2.depreciation_amount), 0)
                    FROM asset_depreciation ad2
                    WHERE ad2.asset_id = fa.id
                    AND (
                        ad2.year < :to_year
                        OR (ad2.year = :to_year AND ad2.month <= :to_month)
                    )
                    AND ad2.month >= :november_month
                )
            ) as total_net_book_value
        FROM fixed_assets fa
        WHERE fa.purchase_cost > 0 and asset_type !="Buildings"
        GROUP BY fa.asset_type
        ORDER BY fa.asset_type
    ');
    $stmt->execute([
        ':to_year' => $toYear,
        ':to_month' => $toMonth,
        ':november_month' => $novemberMonth
    ]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTotalAccumulatedDepreciation($pdo, $toDate) {
    $toYear = date('Y', strtotime($toDate));
    $toMonth = date('m', strtotime($toDate));
    $novemberMonth = getNovemberMonthNumber();

    $stmt = $pdo->prepare('
        SELECT
            SUM(
                (
                    SELECT COALESCE(SUM(ad2.depreciation_amount), 0)
                    FROM asset_depreciation ad2
                    WHERE ad2.asset_id = fa.id
                    AND (
                        ad2.year < :to_year
                        OR (ad2.year = :to_year AND ad2.month <= :to_month)
                    )
                    AND ad2.month >= :november_month
                )
            ) as total_depreciation,
            SUM(fa.purchase_cost) as total_purchase_cost,
            COUNT(fa.id) as total_assets
        FROM fixed_assets fa
        WHERE fa.purchase_cost > 0 and asset_type !="Buildings"
    ');
    $stmt->execute([
        ':to_year' => $toYear,
        ':to_month' => $toMonth,
        ':november_month' => $novemberMonth
    ]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function renderChildren($pdo, $account, $from, $to, $level = 1) {
    $totalBalance = 0;
    $children = getChildren($pdo, $account['id']);

    if ($children) {
        foreach ($children as $child) {
            $totalBalance += renderChildren($pdo, $child, $from, $to, $level + 1);
        }

        if (abs($totalBalance) > 0.0001) {
            $displayBalance = ($account['account_code'] == '1120') ? $totalBalance : $totalBalance;
            echo "<tr>
                    <td style='padding-left:".($level * 20)."px'>
                        <strong>{$account['account_code']} - {$account['account_name']}</strong>
                    </td>
                    <td class='text-end'><strong>" . number_format($displayBalance, 2) . "</strong></td>
                  </tr>";
        }
    } else {
        $balance = getAccountBalance($pdo, $account['id'], $from, $to);

        if (abs($balance) > 0.0001) {
            $displayBalance = (strpos($account['account_code'], '1120') === 0) ? $balance : $balance;

            if ($account['account_code'] == '11004') {
                $details = getReceivableDetails($pdo, $from, $to);
                echo "<tr>
                        <td style='padding-left:".($level * 20)."px'>
                            {$account['account_code']} - {$account['account_name']}
                            <button class='btn btn-sm btn-outline-primary ms-2' type='button' onclick=\"toggleDetails('details-{$account['id']}')\">
                                Toggle Details
                            </button>
                        </td>
                        <td class='text-end'>" . number_format($displayBalance, 2) . "</td>
                      </tr>";
                echo "<tr id='details-{$account['id']}' style='display: none;'>
                        <td colspan='2' class='p-0'>
                            <div class='p-2'>
                                <table class='table table-sm table-bordered mb-0'>
                                    <thead class='table-secondary'>
                                        <tr><th>Date</th><th>Customer</th><th class='text-end'>Amount</th></tr>
                                    </thead>
                                    <tbody>";
                $detailsTotal = 0;
                foreach ($details as $detail) {
                    $detailsTotal += $detail['net_amount'];
                    echo "<tr>
                            <td>".htmlspecialchars($detail['sale_date'])."</td>
                            <td>".htmlspecialchars($detail['customer_name'])."</td>
                            <td class='text-end'>".number_format($detail['net_amount'], 2)."</td>
                          </tr>";
                }
                echo "          </tbody>
                                    <tfoot class='table-light fw-bold'>
                                        <tr>
                                            <td colspan='2' class='text-end'>Total of Detailed Credit Sales</td>
                                            <td class='text-end'>".number_format($detailsTotal, 2)."</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </td>
                      </tr>";
            } else {
                echo "<tr>
                        <td style='padding-left:".($level * 20)."px'>{$account['account_code']} - {$account['account_name']}</td>
                        <td class='text-end'>" . number_format($displayBalance, 2) . "</td>
                      </tr>";
            }
        }
        $totalBalance = $balance;
    }

    return $totalBalance;
}

function renderNoteSection($pdo, $titleAcc, $from, $to, $asset_types) {
    $children = getChildren($pdo, $titleAcc['id']);
    if (!$children) {
        return null;
    }

    $sectionBalance = 0;
    $isNote1 = ($titleAcc['account_code'] == '1200');

    ob_start();
    foreach ($children as $child) {
        $sectionBalance += renderChildren($pdo, $child, $from, $to, 1);
    }
    $rows = ob_get_clean();

    if (abs($sectionBalance) > 0.0001 || $isNote1) {
        if ($isNote1) {
            $depreciationTotals = getTotalAccumulatedDepreciation($pdo, $to);
            $assetTypeTotals = getAssetTypeTotals($pdo, $to, $asset_types);

            $totalDepreciation = (float) ($depreciationTotals['total_depreciation'] ?? 0);
            $totalAssetsCost = (float) ($depreciationTotals['total_purchase_cost'] ?? 0);
            $totalAssets = (int) ($depreciationTotals['total_assets'] ?? 0);
            $totalNetBookValue = $totalAssetsCost - $totalDepreciation;

            $adjustedBalance = $sectionBalance - $totalDepreciation;
            $displayBalance = $adjustedBalance;

            echo "<h5 class='mt-4 mb-2 text-uppercase'><b>{$titleAcc['account_code']} - {$titleAcc['account_name']}</b></h5>
                    <table class='table table-bordered'>
                      <thead class='table-light'>
                        <tr>
                            <th>Account</th>
                            <th class='text-end'>Balance</th>
                        </tr>
                      </thead>
                      <tbody>$rows</tbody>";

            if ($totalDepreciation > 0) {
                echo "<tbody>
                        <tr>
                            <td style='padding-left:20px'><i>Less: Accumulated Depreciation (from November only)</i></td>
                            <td class='text-end text-danger'>(" . number_format($totalDepreciation, 2) . ")</td>
                        </tr>
                      </tbody>";
            }

            echo "<tfoot class='fw-bold table-light'>
                    <tr>
                        <td>Total Net Book Value</td>
                        <td class='text-end text-success'>" . number_format($displayBalance, 2) . "</td>
                    </tr>
                  </tfoot>
                </table>";

            if ($totalAssets > 0) {
                echo "<div class='mt-3' >
                        <h6>Fixed Assets Summary by Type (as of " . htmlspecialchars($to) . ")</h6>
                        <table class='table table-sm table-bordered'>
                            <thead class='table-secondary'>
                                <tr>
                                    <th>Asset Type</th>
                                    <th class='text-end'>No. of Assets</th>
                                    <th class='text-end'>Purchase Cost</th>
                                    <th class='text-end'>Accumulated Depreciation<br><small>(From November only)</small></th>
                                    <th class='text-end'>Net Book Value</th>
                                </tr>
                            </thead>
                            <tbody>";

                $typeSummaryTotalCost = 0;
                $typeSummaryTotalDep = 0;
                $typeSummaryTotalNBV = 0;

                foreach ($assetTypeTotals as $typeTotal) {
                    if ($typeTotal['total_purchase_cost'] > 0) {
                        $typeCost = (float) $typeTotal['total_purchase_cost'];
                        $typeDep = (float) $typeTotal['total_accumulated_depreciation'];
                        $typeNBV = (float) $typeTotal['total_net_book_value'];
                        $assetCount = (int) $typeTotal['asset_count'];

                        $typeSummaryTotalCost += $typeCost;
                        $typeSummaryTotalDep += $typeDep;
                        $typeSummaryTotalNBV += $typeNBV;

                        $depRate = $asset_types[$typeTotal['asset_type']] ?? 0;

                        echo "<tr>
                                <td>{$typeTotal['asset_type']} ({$depRate}% depreciation)</td>
                                <td class='text-end'>" . number_format($assetCount) . "</td>
                                <td class='text-end'>" . number_format($typeCost, 2) . "</td>
                                <td class='text-end text-danger'>" . number_format($typeDep, 2) . "</td>
                                <td class='text-end text-success'>" . number_format($typeNBV, 2) . "</td>
                              </tr>";
                    }
                }

                echo "  </tbody>
                            <tfoot class='fw-bold table-light'>
                                <tr>
                                    <td class='text-end'>Totals:</td>
                                    <td class='text-end'>" . number_format($totalAssets) . "</td>
                                    <td class='text-end'>" . number_format($typeSummaryTotalCost, 2) . "</td>
                                    <td class='text-end text-danger'>" . number_format($typeSummaryTotalDep, 2) . "</td>
                                    <td class='text-end text-success'>" . number_format($typeSummaryTotalNBV, 2) . "</td>
                                </tr>
                            </tfoot>
                        </table>
                      </div>";
            }

            return $adjustedBalance;
        } else {
            $displayBalance = ($titleAcc['account_code'] == '1120') ? $sectionBalance : $sectionBalance;
            echo "<h5 class='mt-4 mb-2 text-uppercase'><b>{$titleAcc['account_code']} - {$titleAcc['account_name']}</b></h5>
                    <table class='table table-bordered'>
                      <thead class='table-light'>
                        <tr>
                            <th>Account</th>
                            <th class='text-end'>Balance</th>
                        </tr>
                      </thead>
                      <tbody>$rows</tbody>
                      <tfoot class='fw-bold table-light'>
                        <tr>
                            <td>Total for {$titleAcc['account_name']}</td>
                            <td class='text-end'>" . number_format($displayBalance) . "</td>
                        </tr>
                      </tfoot>
                    </table>";

            return $sectionBalance;
        }
    }

    return null;
}

function renderSingleNote($pdo, $noteCode, $noteTitle, $from, $to, $asset_types) {
    $titleAcc = getAccountByCode($pdo, $noteCode);
    if (!$titleAcc) {
        echo "<div class='alert alert-warning'>Account not found for code: $noteCode</div>";
        return;
    }

    $result = renderNoteSection($pdo, $titleAcc, $from, $to, $asset_types);
    if (!$result && $noteCode != '1200') {
        echo "<div class='alert alert-info'>No transactions found for: $noteTitle</div>";
    }
}
?>

<div class="container mt-5">
    <!-- Date Filter and Actions -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Finance Notes Filter</h5>
        </div>
        <div class="card-body">
            <form method="get" class="row g-3 align-items-end">
                <input type="hidden" name="resto" value="notes">
                <?php if ($selectedNote): ?>
                <input type="hidden" name="note" value="<?= htmlspecialchars($selectedNote) ?>">
                <?php endif; ?>

                <div class="col-md-4">
                    <label class="form-label">From Date</label>
                    <input type="date" name="from" value="<?= htmlspecialchars($from) ?>" class="form-control">
                </div>

                <div class="col-md-4">
                    <label class="form-label">To Date</label>
                    <input type="date" name="to" value="<?= htmlspecialchars($to) ?>" class="form-control">
                </div>

                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Apply Date Range</button>
                    <button type="button" class="btn btn-secondary" onclick="window.history.back()">
                        ← Back
                    </button>
                </div>
            </form>

            <div class="d-flex gap-2 mt-3">
                <button onclick="printInvoice()" class="btn btn-secondary">Print</button>
                <button type="button" class="btn" style="background-color: #8b2626ff; border-color: #8b2626ff; color: white;"
                    onclick="saveAllNotesAsExcel('content', 'notes_<?= htmlspecialchars($from) ?>_to_<?= htmlspecialchars($to) ?>.xls')">
                    Export to Excel
                </button>

            </div>
        </div>
    </div>

    <!-- Main Content Card -->
    <div class="card shadow">
        <div class="card-header">
            <h4 class="mb-0 text-center">
                <?php if ($selectedNote && isset($notesConfig[$selectedNote])): ?>
                Finance Note: <?= $notesConfig[$selectedNote]['title'] ?>
                <small class="text-muted">(Note <?= $selectedNote ?>)</small>
                <?php else: ?>
                Finance Notes
                <small class="text-muted">(<?= htmlspecialchars($from) ?> → <?= htmlspecialchars($to) ?>)</small>
                <?php endif; ?>
            </h4>
        </div>

        <div class="card-body">
            <!-- Search Input -->
            <div class="mb-4">
                <input type="text" id="searchInput" class="form-control" placeholder="Search accounts...">
            </div>

            <!-- Notes Content -->
            <div id="content">
                <?php
                $grandTotalBalance = 0;

                if ($selectedNote && isset($notesConfig[$selectedNote])) {
                    $noteInfo = $notesConfig[$selectedNote];
                    renderSingleNote($pdo, $noteInfo['code'], $noteInfo['title'], $from, $to, $asset_types);
                } else {
                    foreach ($titleAccounts as $code) {
                        $titleAcc = getAccountByCode($pdo, $code);
                        if (!$titleAcc) continue;

                        $result = renderNoteSection($pdo, $titleAcc, $from, $to, $asset_types);
                        if ($result) {
                            $grandTotalBalance += $result;
                        }
                    }

                    if (abs($grandTotalBalance) > 0.0001) {
                        $displayGrandTotal = abs($grandTotalBalance);
                        echo "<div class='mt-4 p-3 bg-light rounded'>
                                <h5 class='text-center'><strong>Grand Total</strong></h5>
                                <table class='table table-bordered'>
                                    <tbody>
                                        <tr>
                                            <td class='text-end h5'>" . number_format($displayGrandTotal, 2) . "</td>
                                        </tr>
                                    </tbody>
                                </table>
                              </div>";
                    }
                }
                ?>
            </div>
        </div>
    </div>
</div>

<!-- Add necessary CSS -->
<style>
    .table tbody tr:hover,
    .table tfoot tr:hover,
    .table-striped>tbody>tr:nth-of-type(odd):hover,
    .table-striped>tbody>tr:nth-of-type(even):hover {
        background-color: rgba(255, 87, 34, 0.2) !important;
    }

    .table-bordered {
        border: 1px solid #dee2e6;
    }

    .table-light {
        background-color: #f8f9fa;
    }

    .text-end {
        text-align: right;
    }

    .fw-bold {
        font-weight: bold;
    }

    .text-danger {
        color: #dc3545;
    }

    .text-success {
        color: #198754;
    }

    .text-muted {
        color: #6c757d;
    }

    .text-uppercase {
        text-transform: uppercase;
    }

    .table-secondary {
        background-color: #6c757d;
        color: white;
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

    // Toggle Details Function
    function toggleDetails(id) {
        var element = document.getElementById(id);
        if (element) {
            element.style.display = (element.style.display === 'none') ? 'table-row' : 'none';
        }
    }

    // Print Function
    function printInvoice() {
        var searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.style.display = 'none';
        }

        var printContents = document.getElementById('content').innerHTML;
        var originalContents = document.body.innerHTML;

        document.body.innerHTML = `
            <div style="padding: 20px;">
                <div style="text-align: center; margin-bottom: 20px;">
                    <h2>Finance Notes Report</h2>
                    <p>Period: <?= htmlspecialchars($from) ?> to <?= htmlspecialchars($to) ?></p>
                    <p><small>Depreciation calculated from November only</small></p>
                </div>
                ${printContents}
            </div>
        `;

        window.print();
        document.body.innerHTML = originalContents;

        if (searchInput) {
            searchInput.style.display = '';
        }
    }

    // Excel Export Function
    function saveAllNotesAsExcel(contentId, filename) {
        var excelContent = `
        <html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
        <head>
            <meta charset="utf-8">
            <style>
                table { border-collapse: collapse; width: 100%; }
                th, td { border: 1px solid #000; padding: 8px; text-align: left; }
                .header { text-align: center; border: none; padding: 20px; }
                .company-name { font-size: 18px; font-weight: bold; }
                .company-info { font-size: 12px; }
                .report-title { font-size: 16px; font-weight: bold; margin: 10px 0; }
                .table-header { background-color: #8b2626; color: white; font-weight: bold; }
                .text-end { text-align: right; }
                .fw-bold { font-weight: bold; }
                .text-danger { color: #dc3545; }
                .text-success { color: #198754; }
            </style>
        </head>
        <body>
            <table>
                <tr>
                    <td colspan="2" class="header">
                        <div class="company-name">Finance Notes Report</div>
                        <div class="company-info">
                            Period: <?= htmlspecialchars($from) ?> to <?= htmlspecialchars($to) ?>
                        </div>
                        <div class="report-title">Financial Account Details</div>
                        <div><small>Depreciation calculated from November only</small></div>
                    </td>
                </tr>
            </table>
            <br>
        `;

        var content = document.getElementById(contentId);
        var sections = content.querySelectorAll('h5');

        sections.forEach(function(h5) {
            var table = h5.nextElementSibling;
            if (table && table.tagName === 'TABLE') {
                excelContent += `<h3>${h5.textContent}</h3>`;
                excelContent += '<table>';
                excelContent += '<tr><th style="background-color: #8b2626; color: white; font-weight: bold;">Account</th><th style="background-color: #8b2626; color: white; font-weight: bold; text-align: right;">Balance</th></tr>';

                var tbody = table.querySelector('tbody');
                if (tbody) {
                    var rows = tbody.querySelectorAll('tr');
                    rows.forEach(function(row) {
                        if (row.style.display !== 'none') {
                            excelContent += '<tr>';
                            var cells = row.querySelectorAll('td');
                            cells.forEach(function(cell) {
                                var cellClass = cell.classList.contains('text-end') ? 'text-end' : '';
                                var cellColor = '';
                                if (cell.classList.contains('text-danger')) cellColor = 'color: #dc3545;';
                                if (cell.classList.contains('text-success')) cellColor = 'color: #198754;';
                                excelContent += `<td class="${cellClass}" style="${cellColor}">${cell.innerText || cell.textContent}</td>`;
                            });
                            excelContent += '</tr>';
                        }
                    });
                }

                var tfoot = table.querySelector('tfoot');
                if (tfoot) {
                    var footerRows = tfoot.querySelectorAll('tr');
                    footerRows.forEach(function(row) {
                        excelContent += '<tr>';
                        var cells = row.querySelectorAll('td');
                        cells.forEach(function(cell) {
                            var cellClass = cell.classList.contains('text-end') ? 'text-end fw-bold' : 'fw-bold';
                            var cellColor = '';
                            if (cell.classList.contains('text-danger')) cellColor = 'color: #dc3545;';
                            if (cell.classList.contains('text-success')) cellColor = 'color: #198754;';
                            excelContent += `<td class="${cellClass}" style="${cellColor}">${cell.innerText || cell.textContent}</td>`;
                        });
                        excelContent += '</tr>';
                    });
                }

                excelContent += '</table><br>';

                // Check if there's an asset summary table after this
                var nextElement = table.nextElementSibling;
                if (nextElement && nextElement.tagName === 'DIV' && nextElement.querySelector('h6')) {
                    var assetTable = nextElement.querySelector('table');
                    if (assetTable) {
                        excelContent += `<h4>${nextElement.querySelector('h6').textContent}</h4>`;
                        excelContent += assetTable.outerHTML + '<br>';
                    }
                }
            }
        });

        excelContent += `
            <br><br>
            <div style="text-align: center; font-size: 10px;">
                Generated on: ${new Date().toLocaleString()}<br>
                Depreciation calculated from November only
            </div>
        </body>
        </html>`;

        var blob = new Blob([excelContent], { type: 'application/vnd.ms-excel' });
        var url = URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url;
        a.download = filename;
        a.click();
        URL.revokeObjectURL(url);
    }

    // Search functionality
    document.addEventListener('DOMContentLoaded', function() {
        var searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                var searchTerm = this.value.toLowerCase();
                var sections = document.querySelectorAll('#content h5');
                var grandTotal = document.querySelector('#content .mt-4.p-3.bg-light.rounded');
                var hasVisibleSections = false;

                sections.forEach(function(h5) {
                    var table = h5.nextElementSibling;
                    var titleText = h5.textContent.toLowerCase();
                    var hasVisibleRows = false;

                    if (table && table.tagName === 'TABLE') {
                        var tbody = table.querySelector('tbody');
                        if (tbody) {
                            var rows = tbody.querySelectorAll('tr');
                            rows.forEach(function(row) {
                                var accountCell = row.querySelector('td:first-child');
                                if (accountCell) {
                                    var accountText = accountCell.textContent.toLowerCase();
                                    var matches = accountText.includes(searchTerm);
                                    row.style.display = matches ? '' : 'none';
                                    if (matches) {
                                        hasVisibleRows = true;
                                    }
                                    var nextRow = row.nextElementSibling;
                                    if (nextRow && nextRow.id && nextRow.id.startsWith('details-')) {
                                        nextRow.style.display = matches ? '' : 'none';
                                    }
                                }
                            });
                        }
                    }

                    var titleMatches = titleText.includes(searchTerm);
                    if (titleMatches || hasVisibleRows) {
                        h5.style.display = '';
                        if (table) table.style.display = '';
                        hasVisibleSections = true;
                    } else {
                        h5.style.display = 'none';
                        if (table) table.style.display = 'none';
                    }
                });

                if (grandTotal) {
                    grandTotal.style.display = hasVisibleSections ? '' : 'none';
                }
            });
        }
    });
</script>

<?php

require_once __DIR__ . '/../../includes/footer.php';  // Include header?>
