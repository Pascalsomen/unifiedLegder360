<?php
require_once '../../classes/SchoolFeesSystem.php';
require_once '../../config.php';

$school = new SchoolFeesSystem($pdo);

$data = [
    'student_id' => $_POST['student_id'],
    'sponsor_id' => $_POST['sponsor_id'],
    'term_id' => $_POST['term_id'],
    'amount_paid' => $_POST['amount_paid'],
    'payment_date' => $_POST['payment_date'],
    'payment_method' => $_POST['payment_method'],
    'reference' => $_POST['reference']
];

$school->addPayment($data);
header("Location: ../school_fees/payments_list.php?msg=Payment recorded");
exit;
