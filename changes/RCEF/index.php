<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/classes/chartReportSystem.php';

$report = new chartReportSystem($pdo);

$from = $_GET['from'] ?? null;
$to = $_GET['to'] ?? null;

$incomeVsExpenses = $report->getIncomeVsExpenses($from, $to);
$expenseBreakdown = $report->getExpenseBreakdown($from, $to);
$cashFlow = $report->getCashFlow($from, $to);
$profitTrend = $report->getProfitTrend($from, $to);
?>

<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3>📊 Financial Graphical Reports</h3>
        <div class="d-flex gap-2">
        <form method="GET" class="d-flex gap-2 align-items-center">
    <input type="date" name="from" value="<?= $_GET['from'] ?? '' ?>" class="form-control" required>
    <input type="date" name="to" value="<?= $_GET['to'] ?? '' ?>" class="form-control" required>
    <button type="submit" class="btn btn-primary">Filter</button>
</form>

        </div>
    </div>

    <!-- Income vs Expenses -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <h5 class="card-title">Income vs Expenses (Monthly)</h5>
            <div style="height: 300px;">
                <canvas id="incomeExpenseChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Expense Breakdown and Profit Trend -->
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Expense Breakdown</h5>
                    <div style="height: 300px;">
                        <canvas id="expenseBreakdownChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card mb-4 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Profit Trend</h5>
                    <div style="height: 300px;">
                        <canvas id="profitTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cash Flow -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <h5 class="card-title">Cash Flow</h5>
            <div style="height: 300px;">
                <canvas id="cashFlowChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Income vs Expenses Chart
    const incomeExpenseChart = new Chart(document.getElementById('incomeExpenseChart'), {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_column($incomeVsExpenses, 'month')) ?>,
            datasets: [
                {
                    label: 'Income',
                    backgroundColor: '#4BC0C0',
                    data: <?= json_encode(array_column($incomeVsExpenses, 'income')) ?>
                },
                {
                    label: 'Expenses',
                    backgroundColor: '#FF6384',
                    data: <?= json_encode(array_column($incomeVsExpenses, 'expenses')) ?>
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Expense Breakdown Pie Chart
    const expenseBreakdownChart = new Chart(document.getElementById('expenseBreakdownChart'), {
        type: 'pie',
        data: {
            labels: <?= json_encode(array_column($expenseBreakdown, 'category')) ?>,
            datasets: [{
                data: <?= json_encode(array_column($expenseBreakdown, 'amount')) ?>,
                backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#9CCC65', '#AB47BC', '#FFA726']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Profit Trend Line Chart
    const profitTrendChart = new Chart(document.getElementById('profitTrendChart'), {
        type: 'line',
        data: {
            labels: <?= json_encode(array_column($profitTrend, 'month')) ?>,
            datasets: [{
                label: 'Profit',
                data: <?= json_encode(array_column($profitTrend, 'profit')) ?>,
                borderColor: '#4CAF50',
                backgroundColor: 'rgba(76, 175, 80, 0.2)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    // Cash Flow Chart
    const cashFlowChart = new Chart(document.getElementById('cashFlowChart'), {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_column($cashFlow, 'month')) ?>,
            datasets: [
                {
                    label: 'Cash In',
                    backgroundColor: '#42A5F5',
                    data: <?= json_encode(array_column($cashFlow, 'cash_in')) ?>
                },
                {
                    label: 'Cash Out',
                    backgroundColor: '#EF5350',
                    data: <?= json_encode(array_column($cashFlow, 'cash_out')) ?>
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
