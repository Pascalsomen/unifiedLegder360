<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../classes/AccountingSystem.php';

if (!hasPermission('accountant')) {
    redirect('/index.php');
}

$accountingSystem = new AccountingSystem($pdo);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $header = [
            'transaction_date' => $_POST['transaction_date'],
            'reference' => $_POST['reference'],
            'description' => $_POST['description'],
            'created_by' => $_SESSION['user_id']
        ];

        $lines = [];
        foreach ($_POST['lines'] as $line) {
            if (empty($line['account_id']) || (empty($line['debit']) && empty($line['credit']))) {
                continue;
            }

            $lines[] = [
                'account_id' => $line['account_id'],
                'debit' => $line['debit'] ?? 0,
                'credit' => $line['credit'] ?? 0
            ];
        }

        // Create and post the journal entry
        $transactionId = $accountingSystem->createJournalEntry($header, $lines);
        $accountingSystem->postJournalEntry($transactionId, $_SESSION['user_id']);

        $_SESSION['success'] = "Journal entry posted successfully!";
        redirect('/modules/accounting/journal_entries.php');
    } catch (Exception $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
}

// Rest of your existing form code...
// [Keep all your existing HTML and JavaScript for the form interface]