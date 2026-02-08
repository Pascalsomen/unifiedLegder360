<?php
require_once __DIR__ . '/../../includes/header.php';


if (!hasRole('accountant')) {
    redirect($base);
}

// Set default date range (current fiscal year)
$startDate = $_GET['start_date'] ?? date('Y-01-01');
$endDate = $_GET['end_date'] ?? date('Y-12-31');
$currency = $_GET['currency'] ?? 'RWF';

// Set page title and breadcrumbs
$pageTitle = 'Financial Statements';
$breadcrumbs = [
    'Accounting' => '/modules/accounting',
    'Financial Statements' => ''
];
?>

<div class="container-fluid">
    <div class="card mb-4">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h4><i class="fas fa-file-invoice-dollar"></i> Financial Statements</h4>
                <div>
                    <button class="btn btn-success" onclick="window.print()">
                        <i class="fas fa-print"></i> Print
                    </button>
                    <a href="financial_statements.php?export=excel&start_date=<?= $startDate ?>&end_date=<?= $endDate ?>"
                       class="btn btn-primary">
                        <i class="fas fa-file-excel"></i> Export
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3 mb-4">
                <div class="col-md-3">
                    <label for="start_date" class="form-label">From Date</label>
                    <input type="date" class="form-control" id="start_date" name="start_date"
                           value="<?= $startDate ?>" required>
                </div>
                <div class="col-md-3">
                    <label for="end_date" class="form-label">To Date</label>
                    <input type="date" class="form-control" id="end_date" name="end_date"
                           value="<?= $endDate ?>" required>
                </div>
                <div class="col-md-3">
                    <label for="currency" class="form-label">Currency</label>
                    <select class="form-select" id="currency" name="currency">
                        <option value="RWF" <?= $currency == 'RWF' ? 'selected' : '' ?>>RWF</option>

                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                </div>
            </form>

            <!-- Tab Navigation -->
            <ul class="nav nav-tabs" id="financialTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="income-tab" data-bs-toggle="tab"
                            data-bs-target="#income-statement" type="button" role="tab">
                        Income Statement
                    </button>
                </li>


                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="balance-tab" data-bs-toggle="tab"
                            data-bs-target="#balance-sheet" type="button" role="tab">
                        Balance Sheet
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="cashflow-tab" data-bs-toggle="tab"
                            data-bs-target="#cashflow-statement" type="button" role="tab">
                    Cashflow Statements
                    </button>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content p-3 border border-top-0 rounded-bottom" id="financialTabsContent">
                <!-- Income Statement Tab -->
                <div class="tab-pane fade show active" id="income-statement" role="tabpanel">
                    <?php include 'income_statement.php'; ?>
                     <?php //include 'balance_sheet.php'; ?>
                </div>

                <!-- Balance Sheet Tab -->
                <div class="tab-pane fade" id="balance-sheet" role="tabpanel">
                    <?php include 'balance_sheet.php'; ?>
                </div>

                <!-- Cash Flow Statement Tab -->
                <div class="tab-pane fade" id="cashflow-statement" role="tabpanel">
                    <?php include 'cash_flow_statement.php'; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
<script>
    // Disable all DataTables error popups
    $.fn.dataTable.ext.errMode = 'none';

    // Now initialize your table
    $(document).ready(function () {
        $('#DataTables_Table_0').DataTable({
            // your options here
        });
    });
</script>
<script>


document.addEventListener('DOMContentLoaded', function () {
    // Restore last active tab from localStorage
    const savedTab = localStorage.getItem('activeTab');
    if (savedTab) {
        const triggerEl = document.querySelector(`button[data-bs-target="${savedTab}"]`);
        if (triggerEl) {
            bootstrap.Tab.getOrCreateInstance(triggerEl).show();
        }
    }

    // Listen for tab change and store active tab in localStorage
    const tabButtons = document.querySelectorAll('button[data-bs-toggle="tab"]');
    tabButtons.forEach(button => {
        button.addEventListener('shown.bs.tab', function (event) {
            const target = event.target.getAttribute('data-bs-target');
            localStorage.setItem('activeTab', target);
        });
    });
});
</script>

