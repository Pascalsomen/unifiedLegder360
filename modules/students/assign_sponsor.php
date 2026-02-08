<?php include '../../includes/header.php'; ?>
<div class="container mt-4">
    <h4>Assign Sponsor to Student</h4>
    <form action="process_assign_sponsor.php" method="POST">
        <div class="row mb-2">
            <div class="col-md-6">
                <label>Student</label>
                <select name="student_id" class="form-control">
                    <?php foreach ($schoolFees->getStudents() as $student): ?>
                        <option value="<?= $student['id'] ?>"><?= $student['full_name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label>Sponsor</label>
                <select name="sponsor_id" class="form-control">
                    <?php foreach ($schoolFees->getSponsors() as $sponsor): ?>
                        <option value="<?= $sponsor['id'] ?>"><?= $sponsor['full_name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <button class="btn btn-primary" type="submit">Assign</button>
    </form>
</div>
<?php include '../../includes/footer.php'; ?>