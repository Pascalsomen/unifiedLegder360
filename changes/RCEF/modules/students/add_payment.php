<?php include '../../includes/header.php'; ?>
<div class="container mt-4">
    <h4>Add School Fee Payment</h4>
    <form action="process_add_payment.php" method="POST" enctype="multipart/form-data">
        <label>Student</label>
        <select name="student_id" class="form-control" required>
            <?php foreach ($students as $student): ?>
                <option value="<?= $student['id'] ?>"><?= $student['full_name'] ?></option>
            <?php endforeach; ?>
        </select>
        <label>Amount</label>
        <input type="number" step="0.01" name="amount" class="form-control" required>
        <label>Term</label>
        <select name="term" class="form-control" required>
            <option value="1">Term 1</option>
            <option value="2">Term 2</option>
            <option value="3">Term 3</option>
        </select>
        <label>Year</label>
        <input type="number" name="year" value="<?= date('Y') ?>" class="form-control" required>
        <label>Payment Date</label>
        <input type="date" name="payment_date" class="form-control" required>
        <label>Upload Receipt</label>
        <input type="file" name="receipt" class="form-control">
        <button type="submit" class="btn btn-success mt-3">Save Payment</button>
    </form>
</div>
<?php include '../../includes/footer.php'; ?>
