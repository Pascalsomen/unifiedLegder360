<?php
require_once '../../config.php';
require_once '../../classes/SchoolFeesSystem.php';

$school = new SchoolFeesSystem($pdo);
$sponsors = $school->getAllSponsors();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Sponsor Contributions</title>
    <link rel="stylesheet" href="../../assets/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <h4 class="mb-3">Sponsor Contributions</h4>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Sponsor</th>
                <th>Total Contributed</th>
                <th>Number of Students</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($sponsors as $s):
            $total = $school->getSponsorTotalPaid($s['id']);
            $students = $school->getStudentsBySponsor($s['id']);
        ?>
            <tr>
                <td><?= $s['name'] ?></td>
                <td><?= number_format($total, 2) ?></td>
                <td><?= count($students) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
