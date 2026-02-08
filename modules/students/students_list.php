<?php include '../../includes/header.php';
require_once '../../classes/SchoolFeesSystem.php';
$school = new SchoolFeesSystem($pdo);
$students = $school->listStudents();
?>

<div class="container mt-4">
    <h4>Students List



        <?php if(hasPermission(28)){?>
            <a href="add_student.php" class="btn btn-primary mb-3">Add New Student</a>
<?php }else{
 //Echo "You do not have access to add repayment";
} ?><button class="btn btn-success mb-1 float-end" onclick="exportToExcel('table', 'Donors')">Export to Excel</button> </h4>

    <table  id="table" class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Picture</th>
                <th>Student Name</th>
                <th>School</th>
                <th>Fees</th>
                <th>Bank Name</th>
                <th>Bank Account</th>
                <th>Actions</th>

            </tr>
        </thead>
        <tbody>
            <!-- Loop through students -->
            <?php foreach ($students as $i => $student): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td>
            <?php if (!empty($student['profile_picture'])): ?>
                <img src="../../uploads/student_documents/<?= htmlspecialchars($student['profile_picture']) ?>" alt="Profile" width="50" height="50" style="border-radius: 50%;">
            <?php else: ?>
                <img src="../../uploads/student_documents/default.avif" alt="No Picture" width="50" height="50">
            <?php endif; ?>
        </td>
                <td> <?= htmlspecialchars($student['last_name']) ?> <?= htmlspecialchars($student['first_name']) ?> </td>

                <td><?= $student['school_name'] ?></td>
                <td><?= $student['fees_payment'] ?></td>
                <td><?= $student['bank_name'] ?></td>
                <td><?= $student['bank_account'] ?></td>

                <td>
                    <a href="view_student.php?id=<?= $student['id'] ?>" class="btn btn-sm btn-info">View</a>
                    <br>
                    <a href="student_marks.php?id=<?= $student['id'] ?>" class="btn btn-sm btn-primary">School Reports</a>

                    <?php if(hasPermission(29)){?>
            <a href="edit_student.php?id=<?= $student['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
<?php }else{
 //Echo "You do not have access to add repayment";
} ?>

                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php include '../../includes/footer.php'; ?>
