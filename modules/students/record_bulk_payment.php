<?php
require_once  '../../classes/AccountingSystem.php';
require_once  '../../classes/LoanSystem.php';
require_once __DIR__ . '/../../includes/header.php'; $termId = $_POST['term_id'];

$studentIds = $_POST['students'];
$total = $_POST['total'];
$stmt = $pdo->prepare("INSERT INTO student_payments (student_id, term_id, payment_date) VALUES (?, ?, NOW())");

foreach ($studentIds as $studentId) {
    $stmt->execute([$studentId, $termId]);
}

$sys= new LoanSystem($pdo);
$tm = new AccountingSystem($pdo);
$credit =$sys->getAccountDetails('310102R');
$credit =$credit['id'];
$debit =$sys->getAccountDetails('270102R');
$debit=$debit['id'];
$ref ='RCEF-'.date('Ymdhis');
$header = [
    'transaction_date' => date('Y-m-d'),
    'reference' => $ref,
    'description' => 'School fees payment',
    'created_by' => $_SESSION['user_id']
];

$lines = [];

                $lines[] = [
                    'account_id' => $debit,
                    'debit' => $total,
                    'credit' => 0
                ];

                $lines[] = [
                    'account_id' => $credit,
                    'debit' => 0,
                    'credit' => $total
                ];


                if($total > 0){

                    $transactionId = $tm->createJournalEntry($header, $lines);
                    $tm->postJournalEntry($transactionId, $_SESSION['user_id']);

                    $_SESSION['voucher_total'] = $total;
                    $_SESSION['voucher_date'] = date('Y-m-d');
                    $_SESSION['voucher_no'] = $ref;
                    $_SESSION['voucher_payee'] = 'SACCO KIMIHURURA Frw 4755 Rwanda Children Educational Foundation';
                    $_SESSION['voucher_desc']  =  'School fees payment';

                }





echo "<div class='alert alert-success'>Payments recorded successfully,  <a href='$base./page.php?ref=$ref' target='_blank' class='btn btn-sm btn-success ms-2'>Preview And Print Voucher</a></div>";

require_once __DIR__ . '/../../includes/footer.php';