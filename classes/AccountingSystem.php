<?php
class AccountingSystem {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Create a new journal entry with validation
     */
    public function createJournalEntry(array $header, array $lines): int {
        $this->validateJournalEntry($lines);

        try {
            $this->pdo->beginTransaction();

            // Insert journal entry header
            $stmt = $this->pdo->prepare("
                INSERT INTO journal_entries (
                    entry_date, reference, description,
                    created_by, reference_type, reference_id, reconciled
                ) VALUES (?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $header['entry_date'],
                $header['reference'] ?? null,
                $header['description'] ?? null,
                $header['created_by'],
                $header['reference_type'] ?? null,
                $header['reference_id'] ?? null,
                $header['reconciled'] ?? false
            ]);

            $journalEntryId = $this->pdo->lastInsertId();

            // Insert journal entry lines
            $lineStmt = $this->pdo->prepare("
                INSERT INTO journal_entry_lines (
                    journal_entry_id, account_id, line_description, debit, credit
                ) VALUES (?, ?, ?, ?, ?)
            ");

            foreach ($lines as $line) {
                $lineStmt->execute([
                    $journalEntryId,
                    $line['account_id'],
                    $line['line_description'] ?? null,
                    $line['debit'],
                    $line['credit']
                ]);
            }

            $this->pdo->commit();
            return $journalEntryId;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Update an existing journal entry
     */
    public function updateJournalEntry($id, $header, $lines) {
        $this->pdo->beginTransaction();

        // Update journal entry header
        $stmt = $this->pdo->prepare("
            UPDATE journal_entries
            SET entry_date = ?, reference = ?, description = ?,
                reference_type = ?, reference_id = ?, reconciled = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $header['entry_date'],
            $header['reference'] ?? null,
            $header['description'] ?? null,
            $header['reference_type'] ?? null,
            $header['reference_id'] ?? null,
            $header['reconciled'] ?? false,
            $id
        ]);

        // Delete old lines
        $this->pdo->prepare("DELETE FROM journal_entry_lines WHERE journal_entry_id = ?")->execute([$id]);

        // Insert new lines
        $lineStmt = $this->pdo->prepare("
            INSERT INTO journal_entry_lines
            (journal_entry_id, account_id, line_description, debit, credit)
            VALUES (?, ?, ?, ?, ?)
        ");

        foreach ($lines as $line) {
            $lineStmt->execute([
                $id,
                $line['account_id'],
                $line['line_description'] ?? null,
                $line['debit'],
                $line['credit']
            ]);
        }

        $this->pdo->commit();
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
     * Get income vs expenses report
     */
    public function getIncomeVsExpenses($from = null, $to = null) {
        $sql = "
            SELECT
                DATE_FORMAT(je.entry_date, '%b %Y') as month,
                SUM(CASE WHEN ca.account_type = 'revenue' THEN jel.credit - jel.debit ELSE 0 END) as income,
                SUM(CASE WHEN ca.account_type = 'expense' THEN jel.debit - jel.credit ELSE 0 END) as expenses
            FROM journal_entries je
            JOIN journal_entry_lines jel ON je.id = jel.journal_entry_id
            JOIN chart_of_accounts ca ON jel.account_id = ca.id
            WHERE ca.account_type IN ('revenue', 'expense')
        ";

        $params = [];

        if ($from && $to) {
            $sql .= " AND je.entry_date BETWEEN :from AND :to";
            $params[':from'] = $from;
            $params[':to'] = $to;
        }

        $sql .= " GROUP BY DATE_FORMAT(je.entry_date, '%Y-%m') ORDER BY je.entry_date";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Post a journal entry to the general ledger
     */
    public function postJournalEntry(int $journalEntryId, int $userId): void {
        $stmt = $this->pdo->prepare("CALL post_journal_entry(?, ?)");
        $stmt->execute([$journalEntryId, $userId]);
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

    /**
     * Get balance sheet for a specific date
     */
    public function getBalanceSheet($date): array {
        $stmt = $this->pdo->prepare("
            SELECT
                a.account_name,
                a.account_type,
                SUM(CASE WHEN jel.debit > 0 THEN jel.debit ELSE 0 END) -
                SUM(CASE WHEN jel.credit > 0 THEN jel.credit ELSE 0 END) AS balance
            FROM chart_of_accounts a
            LEFT JOIN journal_entry_lines jel ON jel.account_id = a.id
            LEFT JOIN journal_entries je ON jel.journal_entry_id = je.id
            WHERE je.entry_date <= ?
            GROUP BY a.id
            ORDER BY a.account_type, a.account_name
        ");
        $stmt->execute([$date]);

        $data = ['assets' => [], 'liabilities' => [], 'equity' => []];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            if ($row['balance'] != 0) {
                $type = strtolower($row['account_type']);
                if (in_array($type, ['asset', 'liability', 'equity'])) {
                    $data[$type . 's'][] = $row;
                }
            }
        }

        return $data;
    }

    /**
     * Get income statement for a date range
     */
    public function getIncomeStatement($startDate, $endDate): array {
        $stmt = $this->pdo->prepare("
            SELECT
                a.account_name,
                a.account_type,
                SUM(CASE WHEN jel.debit > 0 THEN jel.debit ELSE 0 END) -
                SUM(CASE WHEN jel.credit > 0 THEN jel.credit ELSE 0 END) AS balance
            FROM chart_of_accounts a
            LEFT JOIN journal_entry_lines jel ON jel.account_id = a.id
            LEFT JOIN journal_entries je ON jel.journal_entry_id = je.id
            WHERE je.entry_date BETWEEN ? AND ?
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

    /**
     * Get income statement by date range (detailed)
     */
    public function getIncomeStatementByDateRange(string $startDate, string $endDate): array {
        $stmt = $this->pdo->prepare("
            SELECT
                a.account_type,
                a.account_name,
                SUM(CASE WHEN jel.debit > 0 THEN jel.debit ELSE 0 END) AS total_debit,
                SUM(CASE WHEN jel.credit > 0 THEN jel.credit ELSE 0 END) AS total_credit
            FROM chart_of_accounts a
            JOIN journal_entry_lines jel ON a.id = jel.account_id
            JOIN journal_entries je ON je.id = jel.journal_entry_id
            WHERE je.entry_date BETWEEN ? AND ?
            AND a.account_type IN ('revenue', 'expense')
            GROUP BY a.account_type, a.account_name
            ORDER BY a.account_type, a.account_name
        ");
        $stmt->execute([$startDate, $endDate]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get income statement data for a specific year
     */
    public function getIncomeStatementData($year): array {
        $stmt = $this->pdo->prepare("
            SELECT
                a.account_type,
                a.account_name,
                a.id AS account_id,
                SUM(CASE WHEN jel.debit > 0 THEN jel.debit ELSE 0 END) AS total_debit,
                SUM(CASE WHEN jel.credit > 0 THEN jel.credit ELSE 0 END) AS total_credit
            FROM chart_of_accounts a
            LEFT JOIN journal_entry_lines jel ON a.id = jel.account_id
            LEFT JOIN journal_entries je ON jel.journal_entry_id = je.id
            WHERE je.entry_date BETWEEN ? AND ?
            AND a.account_type IN ('revenue', 'expense')
            GROUP BY a.account_type, a.account_name, a.id
            ORDER BY a.account_type, a.account_name
        ");
        $stmt->execute([$year]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get cash flow statement for a date range
     */
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
                    SUM(jel.debit - jel.credit) AS amount
                FROM chart_of_accounts a
                JOIN journal_entry_lines jel ON a.id = jel.account_id
                JOIN journal_entries je ON je.id = jel.journal_entry_id
                WHERE je.entry_date BETWEEN ? AND ?
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

    /**
     * Get a journal entry by ID
     */
    public function getJournalEntry(int $id): array {
        $stmt = $this->pdo->prepare("
            SELECT je.*,
                   GROUP_CONCAT(CONCAT_WS('|', jel.account_id, jel.line_description, jel.debit, jel.credit) SEPARATOR ';') as lines
            FROM journal_entries je
            LEFT JOIN journal_entry_lines jel ON je.id = jel.journal_entry_id
            WHERE je.id = ?
            GROUP BY je.id
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Mark journal entry as reconciled
     */
    public function reconcileJournalEntry(int $id, bool $reconciled = true): bool {
        $stmt = $this->pdo->prepare("UPDATE journal_entries SET reconciled = ? WHERE id = ?");
        return $stmt->execute([$reconciled, $id]);
    }
}