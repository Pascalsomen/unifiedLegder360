<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../classes/SchoolFeesSystem.php';

$schoolFees = new SchoolFeesSystem($pdo);

if (!isset($_GET['id'])) die("Sponsor ID is required.");

$sponsorId = $_GET['id'];
$sponsor = $schoolFees->getSponsorById($sponsorId); // method to fetch sponsor info
$students = $schoolFees->getStudentsBySponsor($sponsorId); // method to fetch sponsored students
?>

<div class="container mt-4">
    <h3>Students Sponsored by: <?= htmlspecialchars($sponsor['name']) ?></h3>
    <p><strong>Email:</strong> <?= htmlspecialchars($sponsor['email']) ?> |
       <strong>Phone:</strong> <?= htmlspecialchars($sponsor['phone']) ?></p>

    <?php if (!empty($students)): ?>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Full Name</th>
                    <th>Grade</th>
                    <th>School Name</th>
                    <th>Father</th>
                    <th>Mother</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($students as $i => $student): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></td>
                        <td><?= htmlspecialchars($student['grade']) ?></td>
                        <td><?= htmlspecialchars($student['school_name']) ?></td>
                        <td><?= htmlspecialchars($student['father_name']) ?></td>
                        <td><?= htmlspecialchars($student['mother_name']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-info">No students assigned to this sponsor.</div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
