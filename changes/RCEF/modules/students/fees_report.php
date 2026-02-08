<?php
require_once '../../config.php';
require_once '../../classes/SchoolFeesSystem.php';

$school = new SchoolFeesSystem($pdo);

// Fetch filters
$students = $school->getAllStudents();
$sponsors = $school->getAllSponsors();
$terms = $school->getAllTerms();

$filters = [
    'term_id' => $_GET['term_id'] ?? '',
    'student_id' => $_GET['student_id'] ?? '',
    'sponsor_id' => $_GET['sponsor_id'] ?? '',
    'from_date' => $_GET['from_date'] ?? '',
    'to_date' => $_GET['to_date'] ?? '',
];

$payments = $school->getFilteredPayments($filters);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Fees Report</title>
    <link rel="stylesheet" href="../../assets/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <h4 class="mb-3">School Fees Report</h4>
    <form method="get" class="row g-2">
        <div class="col-md-3">
            <label>Term</label>
            <select name="term_id" class="form-control">
                <option value="">All</option>
                <?php foreach ($terms as $term): ?>
                    <option value="<?= $term['id'] ?>" <?= $filters['term_id'] == $term['id'] ? 'selected' : '' ?>>
                        <?= $term['term_name'] ?> <?= $term['year'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label>Student</label>
            <select name="student_id" class="form-control">
                <option value="">All</option>
                <?php foreach ($students as $s): ?>
                    <option value="<?= $s['id'] ?>" <?= $filters['student_id'] == $s['id'] ? 'selected' : '' ?>>
                        <?= $s['full_name'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label>Sponsor</label>
            <select name="sponsor_id" class="form-control">
                <option value="">All</option>
                <?php foreach ($sponsors as $s): ?>
                    <option value="<?= $s['id'] ?>" <?= $filters['sponsor_id'] == $s['id'] ? 'selected' : '' ?>>
                        <?= $s['name'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label>Date From</label>
            <input type="date" name="from_date" class="form-control" value="<?= $filters['from_date'] ?>">
        </div>
        <div class="col-md-3">
            <label>Date To</label>
            <input type="date" name="to_date" class="form-control" value="<?= $filters['to_date'] ?>">
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <button class="btn btn-primary">Filter</button>
            <a href="#" onclick="window.print()" class="btn btn-secondary ms-2">Print</a>
        </div>
    </form>

    <hr>

    <table class="table table-bordered table-striped mt-3">
        <thead>
            <tr>
                <th>Student</th>
                <th>Sponsor</th>
                <th>Amount</th>
                <th>Term</th>
                <th>Date</th>
                <th>Method</th>
                <th>Reference</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($payments as $p): ?>
            <tr>
                <td><?= $p['student_name'] ?></td>
                <td><?= $p['sponsor_name'] ?></td>
                <td><?= number_format($p['amount_paid'], 2) ?></td>
                <td><?= $p['term_name'] ?> <?= $p['year'] ?></td>
                <td><?= $p['payment_date'] ?></td>
                <td><?= $p['payment_method'] ?></td>
                <td><?= $p['reference'] ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
