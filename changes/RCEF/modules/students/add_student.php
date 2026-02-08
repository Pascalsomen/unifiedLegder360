<?php include '../../includes/header.php';
require_once '../../classes/SchoolFeesSystem.php';
$school = new SchoolFeesSystem($pdo);
$sponsors = $school->getAllSponsors();
?>


<div class="container mt-4">
    <h4>Add Student</h4>
    <form action="process_add_student.php" method="POST" enctype="multipart/form-data">
        <div class="row">
            <div class="col-md-6">
                <label>First Name</label>
                <input type="text" name="first_name" class="form-control" required>

                <label>Last Name</label>
                <input type="text" name="last_name" class="form-control" required>

                <label>Contact Phone</label>
                <input type="tel" name="phone" class="form-control" required>

                <label>Date of Birth</label>
                <input type="date" name="dob" class="form-control" required>
                <label>Gender</label>
                <select name="gender" class="form-control" required>
                    <option value="">-- Select Gender --</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                </select>
                <label>School Name</label>
                <input type="text" name="school_id" class="form-control" required>

                <label>Fee Amount </label>
                <input type="text" name="fees_payment" class="form-control" required>
                <label>School bank name</label>
                <input type="text" name="bank_name" class="form-control" required>
                <label>School Bank Account number</label>
                <input type="text" name="bank_account" class="form-control" required>
            </div>
            <div class="col-md-6">
            <label>Father Name</label>
            <input type="text" name="father_name" class="form-control" >
            <label>Mother Name</label>
            <input type="text" name="mother_name" class="form-control" >

            <label>Guardian Name</label>
            <input type="text" name="guardian_name" class="form-control" >

            <label>Student Grade</label>
            <input type="text" name="grade" class="form-control" >

                <label hidden>Upload Documents (optional)</label>
                <input  hidden type="file" name="documents[]" class="form-control" multiple>
                <label>Sponsor</label>
                <select name="sponsor_id" class="form-control">
                    <option value="">-- No Sponsor --</option>
                    <?php foreach ($sponsors as $sponsor): ?>
                        <option value="<?= $sponsor['id'] ?>"><?= $sponsor['name'] ?></option>
                    <?php endforeach; ?>
                </select>
                <label>Address</label>
                <textarea name="address" class="form-control"></textarea>
            </div>
        </div>
        <?php if(hasPermission(28)){?>

            <button type="submit" class="btn btn-success mt-3">Save Student</button>
<?php }else{
   Echo "You do not have access to add new student";
} ?>

    </form>
</div>
<?php include '../../includes/footer.php'; ?>
