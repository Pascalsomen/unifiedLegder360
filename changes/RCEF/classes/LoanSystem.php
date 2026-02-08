<?php require_once 'AccountingSystem.php';
class LoanSystem {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function createLoan(array $loanData, int $userId): int {
        $stmt = $this->pdo->prepare("
            INSERT INTO loans (borrower_id, amount, interest_rate, term_months, purpose, created_by)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $loanData['borrower_id'],
            $loanData['amount'],
            $loanData['interest_rate'],
            $loanData['term_months'],
            $loanData['purpose'],
            $userId
        ]);
        return $this->pdo->lastInsertId();
    }

    public function approveLoan(int $loanId, int $approverId): bool {
        $stmt = $this->pdo->prepare("UPDATE loans SET status = 'approved', start_date = CURDATE() WHERE id = ?");
        if ($stmt->execute([$loanId])) {



            $loan = $this->getLoanDetails($loanId);
            $amount = $loan['amount'];
            $borrower = $this->getBorrowerDetails($loan['borrower_id']);
            $account =$this->getAccountDetails($borrower['id_number']);
            $id_number = $account['id'];
            $description = 'Loan transaction Approval';
            $ref ='RCEF-'.date('Ymdhis');
            $trx =$this->getAccountDetails('310502R');
            $trx =$trx['id'];

            $header = [
                'transaction_date' => date('Y-m-d'),
                'reference' => $ref,
                'description' => $description,
                'created_by' => $_SESSION['user_id'],
                'trx_type' => 'service',
            ];

            $lines = [];

                $lines[] = [
                    'account_id' => $id_number,
                    'debit' => $amount,
                    'credit' => 0
                ];

                $lines[] = [
                    'account_id' => $trx,
                    'debit' => 0,
                    'credit' => $amount
                ];


            $tm = new AccountingSystem($this->pdo);
            $transactionId = $tm->createJournalEntry($header, $lines);
            $tm->postJournalEntry($transactionId, $_SESSION['user_id']);



            $this->generateRepaymentSchedule($loanId);
            return true;
        }
        return false;
    }

    public function generateRepaymentSchedule(int $loanId): array {
        $loan = $this->getLoanDetails($loanId);
        $monthlyInterest = ($loan['interest_rate'] / 100) / 12;
        $term = $loan['term_months'];
        $principal = $loan['amount'];
        $payment = ($principal * $monthlyInterest) / (1 - pow(1 + $monthlyInterest, -$term));
        $schedule = [];

        for ($i = 1; $i <= $term; $i++) {
            $dueDate = date('Y-m-d', strtotime("+$i month", strtotime($loan['start_date'])));
            $stmt = $this->pdo->prepare("
                INSERT INTO loan_repayments (loan_id, due_date, amount_due)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$loanId, $dueDate, round($payment, 2)]);
            $schedule[] = [
                'due_date' => $dueDate,
                'amount_due' => round($payment, 2)
            ];
        }
        return $schedule;
    }

        public function recordPayment(int $repaymentId, float $amount, string $method, string $ref, string $receiptPath = null): bool {
        $stmt = $this->pdo->prepare("
            UPDATE loan_repayments
            SET amount_paid = ?, payment_date = NOW(), payment_method = ?, reference_number = ?, receipt_file = ?, status = ?,is_paid = ?
            WHERE id = ?
        ");


        $repayment = $this->getRepayment($repaymentId);

        $loanId = $repayment['loan_id'];
        $loandetails = $this->getLoanDetails($loanId);



        $id = $loandetails['borrower_id'];
        $borrower = $this->getBorrowerDetails($id);
        $account =$this->getAccountDetails($borrower['id_number']);
        $id = $account['id'];


        $newStatus = $amount >= $repayment['amount_due'] ? 'paid' : 'partial';
        $is_paid = $amount >= $repayment['amount_due'] ? 1 : 0;

        $description = 'Loan repayment transaction';

            $ref ='RCEF-'.date('Ymdhis');
            $trx =$this->getAccountDetails('310502R');
            $trx =$trx['id'];

            $header = [
                'transaction_date' => date('Y-m-d'),
                'reference' => $ref,
                'description' => $description,
                'created_by' => $_SESSION['user_id']
            ];

            $lines = [];

                $lines[] = [
                    'account_id' => $id,
                    'debit' => 0,
                    'credit' => $amount
                ];

                $lines[] = [
                    'account_id' => $trx,
                    'debit' =>  $amount,
                    'credit' =>0
                ];


            $tm = new AccountingSystem($this->pdo);
            $transactionId = $tm->createJournalEntry($header, $lines);
            $tm->postJournalEntry($transactionId, $_SESSION['user_id']);

        return $stmt->execute([$amount, $method, $ref, $receiptPath, $newStatus,$is_paid, $repaymentId]);
    }

    public function uploadDocument(int $loanId, string $type, string $filePath, int $uploaderId, string $notes = ''): bool {
        $stmt = $this->pdo->prepare("
            INSERT INTO loan_documents (loan_id, document_type, file_path, uploaded_by, notes)
            VALUES (?, ?, ?, ?, ?)
        ");
        return $stmt->execute([$loanId, $type, $filePath, $uploaderId, $notes]);
    }

    public function getLoanDetails(int $loanId): array {
        $stmt = $this->pdo->prepare("SELECT * FROM loans WHERE id = ?");
        $stmt->execute([$loanId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getBorrowerDetails(int $loanId): array {
        $stmt = $this->pdo->prepare("SELECT * FROM  borrower WHERE id = ?");
        $stmt->execute([$loanId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAccountDetails(string  $loanId): array {
        $stmt = $this->pdo->prepare("SELECT * FROM chart_of_accounts WHERE account_code = ?");
        $stmt->execute([$loanId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getRepayment($repaymentId): array {
        $stmt = $this->pdo->prepare("SELECT * FROM loan_repayments WHERE id = ?");
        $stmt->execute([$repaymentId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getLoanRepayments(int $loanId): array {
        $stmt = $this->pdo->prepare("SELECT * FROM loan_repayments WHERE loan_id = ?");
        $stmt->execute([$loanId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
