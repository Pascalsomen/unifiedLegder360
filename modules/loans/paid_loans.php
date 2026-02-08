<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../classes/LoanSystem.php';

$loanSystem = new LoanSystem($pdo);

// Handle date filtering
$startDate = $_GET['start_date'] ?? null;
$endDate = $_GET['end_date'] ?? null;

$dateFilterClause = "";
$params = [];

if ($startDate && $endDate) {
    $dateFilterClause = "AND lr.due_date BETWEEN :start_date AND :end_date";
    $params[':start_date'] = $startDate;
    $params[':end_date'] = $endDate;
}

// Fetch late repayments
$sqlLate = "
    SELECT lr.*, l.id AS loan_id, l.amount AS loan_amount,
           b.first_name, b.last_name, b.phone_number, b.id_number
    FROM loan_repayments lr
    JOIN loans l ON lr.loan_id = l.id
    JOIN borrower b ON l.borrower_id = b.id
    WHERE lr.is_paid = 1 AND lr.due_date < CURDATE()
    $dateFilterClause
    ORDER BY lr.due_date ASC
";

$stmtLate = $pdo->prepare($sqlLate);
$stmtLate->execute($params);
$lateRepayments = $stmtLate->fetchAll(PDO::FETCH_ASSOC);

// Fetch paid loans
$sqlPaid = "
    SELECT lr.*, l.id AS loan_id, l.amount AS loan_amount,
           b.first_name, b.last_name, b.phone_number, b.id_number
    FROM loan_repayments lr
    JOIN loans l ON lr.loan_id = l.id
    JOIN borrower b ON l.borrower_id = b.id
    WHERE lr.is_paid = 1  AND (lr.status = 'paid' OR lr.status = 'partial')
    $dateFilterClause
    ORDER BY lr.payment_date DESC
";

$stmtPaid = $pdo->prepare($sqlPaid);
$stmtPaid->execute($params);
$paidRepayments = $stmtPaid->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">


    <form method="GET" class="form-inline mb-4">
        <div class="form-group mr-2">
            <label for="start_date">From:</label>
            <input type="date" name="start_date" id="start_date" class="form-control ml-2"
                   value="<?= htmlspecialchars($startDate ?? '') ?>">
        </div>
        <div class="form-group mr-2">
            <label for="end_date">To:</label>
            <input type="date" name="end_date" id="end_date" class="form-control ml-2"
                   value="<?= htmlspecialchars($endDate ?? '') ?>">
        </div>
        <button type="submit" class="btn btn-primary">Filter</button>
    </form>


    <h4>Paid Loans <button class="btn btn-success mb-1 float-end" onclick="exportToExcel('table', 'Borrowers')"> Export to Excel</button></h4>
    <?php if (empty($paidRepayments)): ?>
        <p>No paid repayments found for selected date range.</p>
    <?php else: ?>
        <table   id="table" class="table table-bordered">
            <thead>
                <tr>
                    <th>Borrower Name</th>
                    <th>ID Number</th>
                    <th>Phone</th>
                    <th>Loan Number</th>
                    <th>Amount Paid</th>
                    <th>Due Date</th>
                    <th>Payment Date</th>
                    <th>Status</th>
                    <th>Loan Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($paidRepayments as $repayment): ?>
                    <tr>
                        <td><?= htmlspecialchars($repayment['first_name'] . ' ' . $repayment['last_name']) ?></td>
                        <td><?= htmlspecialchars($repayment['id_number']) ?></td>
                        <td><?= htmlspecialchars($repayment['phone_number']) ?></td>
                        <td><a href="loan_view.php?id=<?= $repayment['loan_id'] ?>"><?= htmlspecialchars($repayment['loan_id']) ?></a></td>
                        <td><?= number_format($repayment['amount_paid'], 2) ?></td>
                        <td><?= date('M j, Y', strtotime($repayment['due_date'])) ?></td>
                        <td><?= $repayment['payment_date'] ? date('M j, Y', strtotime($repayment['payment_date'])) : '-' ?></td>
                        <td> <?= ucfirst($repayment['status']) ?> </td>
                        <td><?= number_format($repayment['loan_amount'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

