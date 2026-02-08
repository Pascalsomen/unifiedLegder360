<?php
require_once '../../includes/header.php';
require_once '../../classes/LoanSystem.php';
$loanSystem = new LoanSystem($pdo);

$loanId = $_GET['id'];
$loan = $loanSystem->getLoanDetails($loanId);
$borrower = $loanSystem->getBorrowerDetails($loan['borrower_id']);
$borrower = $borrower['first_name']." ".$borrower['last_name'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $loanSystem->approveLoan($loanId, $_SESSION['user_id']);
    $_SESSION['toast'] = "Loan approved and schedule generated.";
    redirect("$base_url/loans/loan_view.php?id=$loanId");
    $ref = 'RCEF-'.date('Ymdhis');
    $_SESSION['voucher_total'] = $loan['amount'];
    $_SESSION['voucher_date'] = date('Y-m-d');
    $_SESSION['voucher_no'] = $ref;
    $_SESSION['voucher_payee'] = $borrower;
    $_SESSION['voucher_desc']  =  'Loan Approval ';

    echo "<script> window.open('$base./page.php?ref=$ref', '_blank'); </script>";


}






?>

<form method="post" class="container mt-4">
    <h4>Approve Loan #<?= $loan['id'] ?></h4>
    <p>Amount: <?= $loan['amount'] ?> | Term: <?= $loan['term_months'] ?> months</p>
    <?php if(hasPermission(36)){?>
        <button class="btn btn-success" type="submit">Approve and Generate Schedule</button>
<?php }else{
 Echo "You do not have access to approve and generate repayment schedule";
} ?>


</form>
<?php require_once '../../includes/footer.php'; ?>
