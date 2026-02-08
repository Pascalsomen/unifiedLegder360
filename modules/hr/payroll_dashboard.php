<?php
require_once '../../includes/header.php';
require_once '../../classes/HRSystem.php';

$payroll = new HRSystem($pdo);
$salariesLast6Months = $payroll->getSalariesForLast6Months();
// Fetch available months for filtering
$months = $payroll->getDistinctMonths();

// If a month is selected, fetch stats for that month
$selectedMonth = isset($_GET['month']) ? $_GET['month'] : date('Y-m'); // default to current month
$stats = $payroll->getPayrollStats($selectedMonth);
?>

<div class="container mt-4">
    <h3>Payroll Dashboard</h3>

    <!-- Filter by Month -->
    <form method="GET" action="">
        <div class="row">
            <div class="col-md-4">
                <select class="form-select" name="month" onchange="this.form.submit()">
                    <option value="">Select Month</option>
                    <?php foreach ($months as $month) : ?>
                        <option value="<?= $month ?>" <?= $month == $selectedMonth ? 'selected' : '' ?>>
                            <?= date('F Y', strtotime($month . '-01')) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </form>

    <div class="row g-4 mt-3">
        <!-- Stats Cards -->
        <div class="col-md-4">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5>Total Gross Salaries</h5>
                    <h3><?= number_format($stats['total_gross'], 2) ?> RWF</h3>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5>Total Net Salaries Paid</h5>
                    <h3><?= number_format($stats['total_net'], 2) ?> RWF</h3>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card text-white bg-danger">
                <div class="card-body">
                    <h5>Total Employee Deductions</h5>
                    <h3><?= number_format($stats['total_deductions'], 2) ?> RWF</h3>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card text-white bg-dark">
                <div class="card-body">
                    <h5>Total Employer Contributions</h5>
                    <h3><?= number_format($stats['total_employer_contribution'], 2) ?> RWF</h3>
                </div>
            </div>
        </div>

    </div>

    <!-- Bar chart for Total Paid Salaries -->
    <div class="row mt-4">
        <div class="col-md-12">
            <canvas id="salaryChart"></canvas>
        </div>
    </div>
</div>


<?php require_once '../../includes/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const salaryData = <?php echo json_encode($salariesLast6Months); ?>;

    const labels = salaryData.map(item => item.month);
    const data = salaryData.map(item => item.total_salary);

    const ctx = document.getElementById('salaryChart').getContext('2d');
    const salaryChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Total Paid Salaries (Last 6 Months)',
                data: data,
                backgroundColor: '#4e73df',
                borderColor: '#4e73df',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) { return value.toFixed(0); }
                    }
                }
            }
        });
</script>
