<?php
require_once __DIR__ . '/../../includes/header.php';
require_once '../../classes/SchoolFeesSystem.php';
$school = new SchoolFeesSystem($pdo);

// Get student ID from query string
$studentId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$studentId) {
    die("Invalid student ID.");
}

// Fetch student
$student = $school->getStudentById($studentId);
if (!$student) {
    die("Student not found.");
}

// Fetch documents
$documents = $school->getStudentDocuments($studentId);
?>

<div class="container mt-4">

<?php if (!empty($student['profile_picture'])): ?>
                <img src="../../uploads/student_documents/<?= htmlspecialchars($student['profile_picture']) ?>" alt="Profile" width="200" height="200">
            <?php else: ?>
                <img src="../../uploads/student_documents/default.avif" alt="No Picture" width="200" height="200">
            <?php endif; ?>

  <br>  Student Info:
<h1><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></h1>

<table class="table table-considered">

<tr><th>Grade</th><td><?= htmlspecialchars($student['grade']) ?></td></tr>
    <tr><th>Gender</th><td><?= htmlspecialchars($student['gender']) ?></td></tr>
    <tr><th>Date of Birth</th><td><?= htmlspecialchars($student['dob']) ?></td></tr>
    <tr><th>Contact Phone</th><td><?= htmlspecialchars($student['phone']) ?></td></tr>
    <tr><th>Address</th><td><?= htmlspecialchars($student['address']) ?></td></tr>
    <tr><th>School</th><td><?= htmlspecialchars($student['school_name']) ?></td></tr>


    <tr><th>Father Name</th><td><?= htmlspecialchars($student['father_name']) ?></td></tr>
    <tr><th>Mother Name</th><td><?= htmlspecialchars($student['mother_name']) ?></td></tr>
    <tr><th>Guardian Name</th><td><?= htmlspecialchars($student['guardian_name']) ?></td></tr>
    <tr><th>Bank Name</th><td><?= htmlspecialchars($student['bank_name']) ?></td></tr>

    <tr><th>Bank Account</th><td><?= htmlspecialchars($student['bank_account']) ?></td></tr>
    <tr><th>Term fees</th><td><?= htmlspecialchars($student['fees_payment']) ?></td></tr>

    <tr><th>Sponsor</th><td><?= htmlspecialchars($student['sponsor_name'] ?? 'N/A') ?></td></tr>
</table>

  <a href="list_documents.php?id=<?php echo $_REQUEST['id']?>">View Documents</a>



</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>