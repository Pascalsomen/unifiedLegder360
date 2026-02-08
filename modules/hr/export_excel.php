<?php header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=payroll_export.xls");
require_once __DIR__ . '/../../includes/header.php';
require_once '../../classes/HRSystem.php';
$payroll = new HRSystem($pdo);

$month = $_GET['month'] ?? null;
$data = $month ? $payroll->getPayrollsByMonth($month) : $payroll->getAllPayrolls();



echo "<table border='1'>";
echo "<tr><th>Employee</th><th>Month</th><th>Gross Salary</th><th>Transport</th><th>Net Salary</th></tr>";
foreach ($data as $p) {
    echo "<tr>
        <td>{$p['full_name']}</td>
        <td>{$p['month']}</td>
        <td>{$p['gross_salary']}</td>
        <td>{$p['transport']}</td>
        <td>{$p['net_salary']}</td>
    </tr>";
}
echo "</table>";
