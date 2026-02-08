<?php
include '../../includes/header.php';
require_once '../../classes/SchoolFeesSystem.php';

$school = new SchoolFeesSystem($pdo);
$sponsors = $school->getAllSponsors();

if (!isset($_GET['id'])) {
    echo "No student selected.";
    exit;
}

$student_id = $_GET['id'];
$student = $school->getStudentById($student_id); // You need this method in your class

if (!$student) {
    echo "Student not found.";
    exit;
}
?>

<div class="container mt-4">
    <h4>Edit Student</h4>
    <form action="process_edit_student.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="student_id" value="<?= $student['id'] ?>">

        <div class="row">
            <div class="col-md-6">
                <label>First Name</label>
                <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($student['first_name']) ?>" required>

                <label>Last Name</label>
                <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($student['last_name']) ?>" required>

                <label>Contact Phone</label>
                <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($student['phone']) ?>" required>

                <label>Date of Birth</label>
                <input type="date" name="dob" class="form-control" value="<?= $student['dob'] ?>" required>

                <label>Gender</label>
                <select name="gender" class="form-control" required>
                    <option value="">-- Select Gender --</option>
                    <option value="Male" <?= $student['gender'] == 'Male' ? 'selected' : '' ?>>Male</option>
                    <option value="Female" <?= $student['gender'] == 'Female' ? 'selected' : '' ?>>Female</option>
                </select>

                <label>School Name</label>
                <input type="text" name="school_id" class="form-control" value="<?= htmlspecialchars($student['school_name']) ?>" required>

                <label>Fee Amount</label>
                <input type="text" name="fees_payment" class="form-control" value="<?= $student['fees_payment'] ?>" required>

                <label>School Bank Name</label>
                <input type="text" name="bank_name" class="form-control" value="<?= htmlspecialchars($student['bank_name']) ?>" required>

                <label>School Bank Account</label>
                <input type="text" name="bank_account" class="form-control" value="<?= htmlspecialchars($student['bank_account']) ?>" required>
            </div>

            <div class="col-md-6">
                <label>Father Name</label>
                <input type="text" name="father_name" class="form-control" value="<?= htmlspecialchars($student['father_name']) ?>" required>

                <label>Mother Name</label>
                <input type="text" name="mother_name" class="form-control" value="<?= htmlspecialchars($student['mother_name']) ?>" required>

                <label>Guardian Name</label>
                <input type="text" name="guardian_name" class="form-control" value="<?= htmlspecialchars($student['guardian_name']) ?>">

                <label>Student Grade</label>
                <input type="text" name="grade" class="form-control" value="<?= htmlspecialchars($student['grade']) ?>">

                <label hidden>Upload Documents (optional)</label>
                <input hidden type="file" name="documents[]" class="form-control" multiple>

                <label>Sponsor</label>
                <select name="sponsor_id" class="form-control">
    <?php if (empty($student['sponsor_id'])): ?>
        <option value="">-- No Sponsor --</option>
    <?php endif; ?>

    <?php foreach ($sponsors as $sponsor): ?>
        <option value="<?= $sponsor['id'] ?>" <?= ($student['sponsor_id'] ?? '') == $sponsor['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($sponsor['name']) ?>
        </option>
    <?php endforeach; ?>
</select>

                <label>Address</label>
                <textarea name="address" class="form-control"><?= htmlspecialchars($student['address']) ?></textarea>

                <div class="form-check mt-3">
    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" <?= isset($student['is_active']) && $student['is_active'] ? 'checked' : '' ?>>
    <label class="form-check-label" for="is_active">
        Student is Active
    </label>
</div>
            </div>
        </div>

        <?php if (hasPermission(28)) { ?>
            <button type="submit" class="btn btn-primary mt-3">Update Student</button>
        <?php } else {
            echo "You do not have access to edit student data.";
        } ?>
    </form>
</div>

<?php include '../../includes/footer.php'; ?>
