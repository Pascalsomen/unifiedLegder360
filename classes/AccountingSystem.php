<?php //error_reporting(0);
class AccountingSystem {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Create a new journal entry with validation
     *
     *
     *
     */

     public function getIncomeVsExpenses($from = null, $to = null) {
        $sql = "SELECT DATE_FORMAT(date, '%b %Y') as month,
                       SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END) as income,
                       SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END) as expenses
                FROM transactions
                WHERE 1";

        $params = [];

        if ($from && $to) {
            $sql .= " AND date BETWEEN :from AND :to";
            $params[':from'] = $from;
            $params[':to'] = $to;
        }

        $sql .= " GROUP BY DATE_FORMAT(date, '%Y-%m') ORDER BY date";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }


    public function updateJournalEntry($id, $header, $lines) {
    $this->pdo->beginTransaction();

    // Update transaction header
    $stmt = $this->pdo->prepare("UPDATE transactions SET transaction_date=?, reference=?, description=?, trx_type=? WHERE id=?");
    $stmt->execute([
        $header['transaction_date'],
        $header['reference'],
        $header['description'],
        $header['trx_type'],
        $id
    ]);

    // Delete old lines
    $this->pdo->prepare("DELETE FROM transaction_lines WHERE transaction_id = ?")->execute([$id]);

    // Insert new lines
    $lineStmt = $this->pdo->prepare("INSERT INTO transaction_lines (transaction_id, account_id, debit, credit) VALUES (?, ?, ?, ?)");
    foreach ($lines as $line) {
        $lineStmt->execute([$id, $line['account_id'], $line['debit'], $line['credit']]);
    }

    $this->pdo->commit();

}


        public function createJournalEntry(array $header, array $lines): int {
        $this->validateJournalEntry($lines);

        try {
            $this->pdo->beginTransaction();

  if($header['purchase_order']){

     // Insert transaction header
            $stmt = $this->pdo->prepare("
                INSERT INTO transactions (
                    transaction_date, reference, description,
                    transaction_type, created_by,trx_type,purchase_order
                ) VALUES (?, ?, ?, 'journal_entry', ?,?,?)
            ");


            $stmt->execute([
                $header['transaction_date'],
                $header['reference'],
                $header['description'],
                $header['created_by'],
                $header['trx_type'],
                 $header['purchase_order'],
            ]);

            }else{
                 // Insert transaction header
            $stmt = $this->pdo->prepare("
                INSERT INTO transactions (
                    transaction_date, reference, description,
                    transaction_type, created_by,trx_type
                ) VALUES (?, ?, ?, 'journal_entry', ?,?)
            ");


            $stmt->execute([
                $header['transaction_date'],
                $header['reference'],
                $header['description'],
                $header['created_by'],
                $header['trx_type']
            ]);

            }





            $transactionId = $this->pdo->lastInsertId();

            // Insert transaction lines
            $lineStmt = $this->pdo->prepare("
                INSERT INTO transaction_lines (
                    transaction_id, account_id, debit, credit
                ) VALUES (?, ?, ?, ?)
            ");

            foreach ($lines as $line) {
                $lineStmt->execute([
                    $transactionId,
                    $line['account_id'],
                    $line['debit'],
                    $line['credit']
                ]);
            }

            $this->pdo->commit();
            return $transactionId;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Validate that debits equal credits
     */
    private function validateJournalEntry(array $lines): void {
        $debitTotal = 0;
        $creditTotal = 0;

        foreach ($lines as $line) {
            $debitTotal += (float)$line['debit'];
            $creditTotal += (float)$line['credit'];
        }

        if (round($debitTotal, 2) !== round($creditTotal, 2)) {
            throw new Exception(
                "Journal entry unbalanced. Debits: $debitTotal, Credits: $creditTotal"
            );
        }
    }

    /**
     * Post a journal entry to the general ledger
     */
    public function postJournalEntry(int $transactionId, int $userId): void {
        $stmt = $this->pdo->prepare("CALL post_journal_entry(?, ?)");
        $stmt->execute([$transactionId, $userId]);
    }

    /**
     * Get trial balance for a period
     */
    public function getTrialBalance(int $periodId): array {
        $stmt = $this->pdo->prepare("
            SELECT
                a.id AS account_id,
                a.account_code,
                a.account_name,
                a.account_type,
                b.debit_amount,
                b.credit_amount,
                CASE
                    WHEN a.account_type IN ('asset', 'expense') THEN
                        b.debit_amount - b.credit_amount
                    ELSE
                        b.credit_amount - b.debit_amount
                END AS balance
            FROM account_balances b
            JOIN chart_of_accounts a ON b.account_id = a.id
            WHERE b.period_id = ?
            ORDER BY a.account_code
        ");
        $stmt->execute([$periodId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Verify that debits equal credits for a period
     */
    public function verifyBalances(int $periodId): array {
        $stmt = $this->pdo->prepare("CALL verify_account_balances(?)");
        $stmt->execute([$periodId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }



    public function getBalanceSheet($date): array {
        $stmt = $this->pdo->prepare("
            SELECT
                a.account_name, a.account_type,
                SUM(CASE WHEN l.debit > 0 THEN l.debit ELSE 0 END) -
                SUM(CASE WHEN l.credit > 0 THEN l.credit ELSE 0 END) AS balance
            FROM chart_of_accounts a
            LEFT JOIN transaction_lines l ON l.account_id = a.id
            LEFT JOIN transactions t ON l.transaction_id = t.id
            WHERE t.transaction_date <= ?
            GROUP BY a.id
            ORDER BY a.account_type, a.account_name
        ");
        $stmt->execute([$date]);

        $data = ['assets' => [], 'liabilities' => [], 'equity' => []];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            if ($row['balance'] != 0) {
                $type = strtolower($row['account_type']);
                $data[$type][] = $row;
            }
        }

        return $data;
    }

    public function getIncomeStatement($startDate, $endDate): array {
        $stmt = $this->pdo->prepare("
            SELECT
                a.account_name, a.account_type,
                SUM(CASE WHEN l.debit > 0 THEN l.debit ELSE 0 END) -
                SUM(CASE WHEN l.credit > 0 THEN l.credit ELSE 0 END) AS balance
            FROM chart_of_accounts a
            LEFT JOIN transaction_lines l ON l.account_id = a.id
            LEFT JOIN transactions t ON l.transaction_id = t.id
            WHERE t.transaction_date BETWEEN ? AND ?
            AND a.account_type IN ('revenue', 'expense')
            GROUP BY a.id
            ORDER BY a.account_type, a.account_name
        ");
        $stmt->execute([$startDate, $endDate]);

        $data = ['revenue' => [], 'expenses' => [], 'net_income' => 0];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            if ($row['account_type'] === 'revenue') {
                $data['revenue'][] = $row;
                $data['net_income'] += $row['balance'];
            } elseif ($row['account_type'] === 'expense') {
                $data['expenses'][] = $row;
                $data['net_income'] -= $row['balance'];
            }
        }

        return $data;
    }

    public function getIncomeStatementByDateRange(string $startDate, string $endDate): array {
    $stmt = $this->pdo->prepare("
     SELECT
        a.account_type,
        a.account_name,
        SUM(CASE WHEN tl.debit > 0 THEN tl.debit ELSE 0 END) AS total_debit,
        SUM(CASE WHEN tl.credit > 0 THEN tl.credit ELSE 0 END) AS total_credit
    FROM chart_of_accounts a
    JOIN transaction_lines tl ON a.id = tl.account_id
    JOIN transactions t ON t.id = tl.transaction_id
    WHERE t.transaction_date BETWEEN ? AND ?
    AND a.account_type IN ('revenue', 'expense')
    GROUP BY a.account_type, a.account_name
    ORDER BY a.account_type, a.account_name
    ");
    $stmt->execute([$startDate, $endDate]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


    public function getIncomeStatementData($year): array {
        $stmt = $this->pdo->prepare("
           SELECT
    a.account_type,
    a.account_name,
    a.id AS account_id,
    SUM(CASE WHEN tl.debit > 0 THEN tl.debit ELSE 0 END) AS total_debit,
    SUM(CASE WHEN tl.credit > 0 THEN tl.credit ELSE 0 END) AS total_credit
FROM chart_of_accounts a
LEFT JOIN transaction_lines tl ON a.id = tl.account_id
LEFT JOIN transactions t ON tl.transaction_id = t.id
WHERE t.transaction_date BETWEEN ? AND ?
AND a.account_type IN ('revenue', 'expense')
GROUP BY a.account_type, a.account_name, a.id
ORDER BY a.account_type, a.account_name

        ");
        $stmt->execute([$year]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function getCashFlowStatement(string $startDate, string $endDate): array {
        $categories = ['operating', 'investing', 'financing'];
        $activityMap = [
            'operating' => ['revenue', 'expense'],
            'investing' => ['asset'],
            'financing' => ['liability', 'equity']
        ];

        $data = ['operating' => [], 'investing' => [], 'financing' => [], 'net_cash' => 0];

        foreach ($categories as $category) {
            $types = $activityMap[$category];

            $stmt = $this->pdo->prepare("
                SELECT
                    a.account_name,
                    SUM(l.debit - l.credit) AS amount
                FROM chart_of_accounts a
                JOIN transaction_lines l ON a.id = l.account_id
                JOIN transactions t ON t.id = l.transaction_id
                WHERE t.transaction_date BETWEEN ? AND ?
                  AND a.account_type IN (" . implode(',', array_fill(0, count($types), '?')) . ")
                GROUP BY a.id
            ");
            $params = array_merge([$startDate, $endDate], $types);
            $stmt->execute($params);

            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $data[$category] = $results;

            foreach ($results as $row) {
                $data['net_cash'] += $row['amount'];
            }
        }

        return $data;
    }



}