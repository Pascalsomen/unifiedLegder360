<?php
require_once __DIR__ . '/../../includes/db.php'; // PDO connection
require_once __DIR__ . '/../../classes/LoanSystem.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'] ?? null;

    if (!$userId) {
        die("Unauthorized access.");
    }

    $amount = $_POST['amount'] ?? 0;
    $interestRate = $_POST['interest_rate'] ?? 0;
    $termMonths = $_POST['term_months'] ?? 0;
    $purpose = trim($_POST['purpose'] ?? '');

    if (!$amount || !$interestRate || !$termMonths || !$purpose) {
        $_SESSION['error'] = "All fields are required.";
        header("Location: create.php");
        exit;
    }

    $loanData = [
        'amount' => floatval($amount),
        'interest_rate' => floatval($interestRate),
        'term_months' => intval($termMonths),
        'purpose' => $purpose,
    ];

    try {
        $loanSystem = new LoanSystem($pdo);
        $loanId = $loanSystem->createLoan($loanData, $userId);

        $_SESSION['success'] = "Loan application submitted successfully. Loan ID: $loanId";
        header("Location: index.php");
        exit;
    } catch (Exception $e) {
        error_log("Loan creation failed: " . $e->getMessage());
        $_SESSION['error'] = "An error occurred while processing your loan.";
        header("Location: create.php");
        exit;
    }
} else {
    header("Location: create.php");
    exit;
}
