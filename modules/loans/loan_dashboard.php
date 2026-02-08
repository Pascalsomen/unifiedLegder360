<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../classes/LoanSystem.php';

$loanSystem = new LoanSystem($pdo);

// Total loans
$totalLoansStmt = $pdo->query("SELECT COUNT(*) as total_loans FROM loans");
$totalLoans = $totalLoansStmt->fetch(PDO::FETCH_ASSOC)['total_loans'];

// Total amount loaned
$totalLoanAmountStmt = $pdo->query("SELECT SUM(amount) as total_amount FROM loans");
$totalLoanAmount = $totalLoanAmountStmt->fetch(PDO::FETCH_ASSOC)['total_amount'];

// Total repayments made
$totalRepaymentsStmt = $pdo->query("SELECT SUM(amount_paid) as total_repayments FROM loan_repayments WHERE is_paid = 1");
$totalRepayments = $totalRepaymentsStmt->fetch(PDO::FETCH_ASSOC)['total_repayments'];

// Total outstanding balance
$outstandingBalance = $totalLoanAmount - $totalRepayments;

// Loan statuses (approved, pending, rejected)
$approvedLoansStmt = $pdo->query("SELECT COUNT(*) as approved FROM loans WHERE status = 'approved'");
$approvedLoans = $approvedLoansStmt->fetch(PDO::FETCH_ASSOC)['approved'];

$pendingLoansStmt = $pdo->query("SELECT COUNT(*) as pending FROM loans WHERE status = 'pending'");
$pendingLoans = $pendingLoansStmt->fetch(PDO::FETCH_ASSOC)['pending'];

$rejectedLoansStmt = $pdo->query("SELECT COUNT(*) as rejected FROM loans WHERE status = 'rejected'");
$rejectedLoans = $rejectedLoansStmt->fetch(PDO::FETCH_ASSOC)['rejected'];
?>

<div class="container mt-4">
    <h2>Loan Summary Dashboard</h2>

    <div class="row">
        <div class="col-md-3">
            <div class="card text-white bg-primary mb-3">
                <div class="card-body">
                    <h5 class="card-title">Total Loans</h5>
                    <p class="card-text"><?= $totalLoans ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success mb-3">
                <div class="card-body">
                    <h5 class="card-title">Approved Loans</h5>
                    <p class="card-text"><?= $approvedLoans ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning mb-3">
                <div class="card-body">
                    <h5 class="card-title">Pending Loans</h5>
                    <p class="card-text"><?= $pendingLoans ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-danger mb-3">
                <div class="card-body">
                    <h5 class="card-title">Rejected Loans</h5>
                    <p class="card-text"><?= $rejectedLoans ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card bg-light mb-3">
                <div class="card-body">
                    <h5 class="card-title">Total Loan Amount</h5>
                    <p class="card-text"><?= number_format($totalLoanAmount, 2) ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card bg-light mb-3">
                <div class="card-body">
                    <h5 class="card-title">Total Repayments</h5>
                    <p class="card-text"><?= number_format($totalRepayments, 2) ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card bg-light mb-3">
                <div class="card-body">
                    <h5 class="card-title">Outstanding Balance</h5>
                    <p class="card-text"><?= number_format($outstandingBalance, 2) ?></p>
                </div>
            </div>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
