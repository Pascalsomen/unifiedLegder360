<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../classes/LoanSystem.php';
require_once __DIR__ . '/../../classes/DocumentManager.php';

if (!hasPermission('apply_loans')) {
    redirect('/index.php');
}

$loanSystem = new LoanSystem($pdo);
$documentManager = new DocumentManager($pdo, __DIR__ . '/../../uploads');
$borrowers = $pdo->query("SELECT b.id, COALESCE(b.company_name, u.username) as name
                          FROM borrowers b JOIN users u ON b.user_id = u.id")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Create loan
        $loanId = $loanSystem->createLoan([
            'borrower_id' => (int)$_POST['borrower_id'],
            'loan_type' => $_POST['loan_type'],
            'principal_amount' => (float)$_POST['principal_amount'],
            'interest_rate' => (float)$_POST['interest_rate'],
            'term_months' => (int)$_POST['term_months'],
            'purpose' => $_POST['purpose']
        ], $_SESSION['user_id']);

        // Handle document uploads
        if (!empty($_FILES['documents'])) {
            foreach ($_FILES['documents']['tmp_name'] as $index => $tmpName) {
                if ($_FILES['documents']['error'][$index] === UPLOAD_ERR_OK) {
                    $file = [
                        'name' => $_FILES['documents']['name'][$index],
                        'type' => $_FILES['documents']['type'][$index],
                        'tmp_name' => $tmpName,
                        'size' => $_FILES['documents']['size'][$index]
                    ];

                    $documentManager->uploadDocument(
                        'loan',
                        $loanId,
                        $_POST['document_types'][$index] ?? 'other',
                        $file,
                        $_SESSION['user_id'],
                        "Uploaded with application"
                    );
                }
            }
        }

        $_SESSION['success'] = "Loan application submitted successfully";
        redirect("/loans/view.php?id=$loanId");
    } catch (Exception $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
}

include 'loan_application.php';
include __DIR__ . '/../../includes/footer.php';