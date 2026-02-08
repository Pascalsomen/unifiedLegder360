<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../classes/LoanSystem.php';

// Fetch late repayments data
$loanSystem = new LoanSystem($pdo);

// Fetch overdue repayments (loans with repayments due but not paid)
$stmt = $pdo->prepare("
    SELECT
        lr.*,
        l.id AS loan_id,
        l.amount AS loan_amount,
        b.first_name,
        b.last_name,
        b.phone_number,
        b.id_number
    FROM loan_repayments lr
    JOIN loans l ON lr.loan_id = l.id
    JOIN borrower b ON l.borrower_id = b.id
    WHERE lr.is_paid = 0 AND lr.due_date < CURDATE()
    ORDER BY lr.due_date ASC
");

$stmt->execute();
$lateRepayments = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="container mt-4">
    <h2>Late Repayments</h2>

    <?php if (empty($lateRepayments)): ?>
        <p>No late repayments found.</p>
    <?php else: ?>
       <table class="table table-bordered">
    <thead>
        <tr>
            <th>Borrower Name</th>
            <th>ID Number</th>
            <th>Phone</th>
            <th>Loan Number</th>
            <th>Repayment Amount</th>
            <th>Due Date</th>
            <th>Loan Amount</th>
            <th>Payment Status</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($lateRepayments as $repayment): ?>
            <tr>
                <td><?= htmlspecialchars($repayment['first_name'] . ' ' . $repayment['last_name']) ?></td>
                <td><?= htmlspecialchars($repayment['id_number']) ?></td>
                <td><?= htmlspecialchars($repayment['phone_number']) ?></td>
                <td><a href="loan_view.php?id=<?= $repayment['loan_id'] ?>"><?= htmlspecialchars($repayment['loan_id']) ?></a></td>
                <td><?= number_format($repayment['amount_due'], 2) ?></td>
                <td><?= date('M j, Y', strtotime($repayment['due_date'])) ?></td>
                <td><?= number_format($repayment['loan_amount'], 2) ?></td>
                <td><span class="badge badge-danger">Late</span></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
