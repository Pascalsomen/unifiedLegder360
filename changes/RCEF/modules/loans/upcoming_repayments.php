<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../classes/LoanSystem.php';

// Fetch upcoming repayments data (loans with repayments due within the next 30 days)
$loanSystem = new LoanSystem($pdo);

// Fetch upcoming repayments (loans with repayments due in the next 30 days)
$stmt = $pdo->prepare(" SELECT
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
    WHERE lr.is_paid = 0
      AND lr.due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
    ORDER BY lr.due_date ASC");
$stmt->execute();
$upcomingRepayments = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="container mt-4">
    <h2>Upcoming Repayments</h2>

    <?php if (empty($upcomingRepayments)): ?>
        <p>No upcoming repayments within the next 30 days.</p>
    <?php else: ?>
        <table id='table' class="table table-bordered">
           <thead>
        <tr>

          <th>Loan Number</th>
           <th>Borrower Name</th>
            <th>ID Number</th>
            <th>Phone</th>

            <th>Repayment Amount</th>
            <th>Due Date</th>
            <th>Loan Amount</th>
            <th>Payment Status</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($upcomingRepayments as $repayment): ?>
            <tr>
                <td><a href="loan_view.php?id=<?= $repayment['loan_id'] ?>"><?= htmlspecialchars($repayment['loan_id']) ?></a></td>
                <td><a href="loan_view.php?id=<?= $repayment['loan_id'] ?>"><?= htmlspecialchars($repayment['first_name'] . ' ' . $repayment['last_name']) ?></a></td>
                <td><?= htmlspecialchars($repayment['id_number']) ?></td>
                <td><?= htmlspecialchars($repayment['phone_number']) ?></td>
                <td><?= number_format($repayment['amount_due'], 2) ?></td>
                <td><?= date('M j, Y', strtotime($repayment['due_date'])) ?></td>
                <td><?= number_format($repayment['loan_amount'], 2) ?></td>
                <td>Upcoming</td>
            </tr>
        <?php endforeach; ?>
    </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
