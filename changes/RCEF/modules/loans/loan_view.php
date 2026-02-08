<?php
require_once '../../includes/header.php';
require_once '../../classes/LoanSystem.php';
$loanSystem = new LoanSystem($pdo);
$loanId = $_GET['id'];
$loan = $loanSystem->getLoanDetails($loanId);
$repayments = $loanSystem->getLoanRepayments($loanId);
?>

<div class="container mt-4">
    <h3>Loan #<?= $loan['id'] ?> - <?= ucfirst($loan['status']) ?></h3>
    <p>Amount: <?= $loan['amount'] ?> | Term: <?= $loan['term_months'] ?> months | Rate: <?= $loan['interest_rate'] ?>%</p>
    <p>Purpose: <?= htmlspecialchars($loan['purpose']) ?></p>

    <h5 class="mt-4">Repayment Schedule <button class="btn btn-success mb-1 float-end" onclick="exportToExcel('table', 'Loan Schedule')">Export to Excel</button></h5>
    <table id="table" class="table table-bordered">
        <thead>
            <tr><th>#</th><th>Due Date</th><th>Amount</th><th>Status</th><th>Receipt</th></tr>
        </thead>
        <tbody>
            <?php foreach ($repayments as $i => $r): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= $r['due_date'] ?></td>
                    <td><?= $r['amount_due'] ?></td>
                    <td><?= ucfirst($r['status']) ?></td>
                    <td>
                        <?php if ($r['receipt_file']): ?>
                            <a href="/uploads/<?= $r['receipt_file'] ?>" target="_blank">View</a>
                        <?php else: ?>N/A<?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once '../../includes/footer.php'; ?>
