<?php
require_once '../../config.php';
require_once '../../classes/SchoolFeesSystem.php';

$school = new SchoolFeesSystem($pdo);
$students = $school->getAllStudents();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Fee Balances</title>
    <link rel="stylesheet" href="../../assets/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <h4 class="mb-3">Student Fee Balances</h4>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Student</th>
                <th>Total Fees</th>
                <th>Total Paid</th>
                <th>Balance</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($students as $s):
            $totalFees = $school->getTotalFees($s['id']);
            $totalPaid = $school->getTotalPaid($s['id']);
            $balance = $totalFees - $totalPaid;
        ?>
            <tr>
                <td><?= $s['full_name'] ?></td>
                <td><?= number_format($totalFees, 2) ?></td>
                <td><?= number_format($totalPaid, 2) ?></td>
                <td><?= number_format($balance, 2) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
