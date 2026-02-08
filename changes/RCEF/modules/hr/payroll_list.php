<?php
require_once __DIR__ . '/../../includes/header.php';
require_once '../../classes/HRSystem.php';
$payroll = new HRSystem($pdo);
//$payrolls = $payroll->getAllPayrolls(); // we’ll define this method below



$month = $_GET['month'] ?? date('Y-m');

if ($month) {
    $payrolls = $payroll->getPayrollsByMonth($month);
} else {
    $payrolls = $payroll->getAllPayrolls();
}

?>
<div class="container mt-4">
<h3>Monthly Payroll Report</h3>
<button class="btn btn-success mb-1 float-end" onclick="exportToExcel('table', 'Payment Slip')">Export to Excel</button>


<?php if (isset($_GET['success'])): ?>
    <div style="color: green;">Payroll successfully generated!</div>
<?php endif; ?>


<form method="GET">
    <label>Filter by Month:</label>
    <input type="month" name="month" value="<?= $_GET['month'] ?? '' ?>">
    <button type="submit">Filter</button>
</form>
<br><br>
<div class="table-responsive">
<table border="1" id="table" cellpadding="5" cellspacing="0" width="100%">
    <thead>
        <tr>
            <th>Employee</th>
            <th>Month</th>
            <th>Gross Salary</th>
            <th>Transport</th>
            <th>Employee Pension</th>
            <th>Employee PAYE</th>
            <th>Employee Maternity</th>
            <th>Employee CBHI Mutuelle</th>
            <th>Total Deductions</th>
            <th>Net Salary</th>
            <th>Employer Pension</th>
            <th>Employer Maternity</th>

            <th>Total Employer Contribution</th>

        </tr>
    </thead>
    <tbody>
        <?php foreach ($payrolls as $p): ?>
        <tr>
            <td><?= htmlspecialchars($p['full_name']) ?></td>
            <td><?= htmlspecialchars($p['month']) ?></td>
            <td><?= number_format($p['gross_salary'], 2) ?></td>
            <td><?= number_format($p['transport'], 2) ?></td>
            <td><?= number_format($p['emp_pension'], 2) ?></td>
            <td><?= number_format($p['emp_rama'], 2) ?></td>
            <td><?= number_format($p['emp_maternity'], 2) ?></td>
            <td><?= number_format($p['emp_cbhi'], 2) ?></td>
            <td><?= number_format($p['total_deductions'], 2) ?></td>
            <td><strong><?= number_format($p['net_salary'], 2) ?></strong></td>
            <td><?= number_format($p['employer_pension'] + $p['employer_occupational'], 2) ?></td>
            <td><?= number_format($p['employer_maternity'], 2) ?></td>
            <td><?= number_format($p['total_employer_contribution'] - $p['employer_rama'], 2) ?></td>

        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
        </div> </div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
