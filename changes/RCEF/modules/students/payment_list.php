<?php include '../../includes/header.php'; ?>
<div class="container mt-4">
    <h4>All School Fee Payments</h4>
    <a href="add_payment.php" class="btn btn-primary mb-3">Add Payment</a>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Student</th>
                <th>Amount</th>
                <th>Term</th>
                <th>Year</th>
                <th>Payment Date</th>
                <th>Receipt</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($payments as $i => $pay): ?>
            <tr>
                <td><?= $i + 1 ?></td>
                <td><?= $pay['student_name'] ?></td>
                <td><?= number_format($pay['amount'], 2) ?></td>
                <td><?= $pay['term'] ?></td>
                <td><?= $pay['year'] ?></td>
                <td><?= $pay['payment_date'] ?></td>
                <td>
                    <?php if ($pay['receipt']): ?>
                        <a href="<?= $pay['receipt'] ?>" target="_blank">View</a>
                    <?php else: ?>
                        No Receipt
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php include '../../includes/footer.php'; ?>
