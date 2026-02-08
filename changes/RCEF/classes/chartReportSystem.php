<?php
class chartReportSystem {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getIncomeVsExpenses($year = null) {
        $year = $year ?? date('Y');
        $sql = "
            SELECT
                MONTH(t.transaction_date) AS month,
                SUM(CASE WHEN a.account_type = 'revenue' THEN tl.credit - tl.debit ELSE 0 END) AS income,
                SUM(CASE WHEN a.account_type = 'expense' THEN tl.debit - tl.credit ELSE 0 END) AS expenses
            FROM transaction_lines tl
            JOIN transactions t ON tl.transaction_id = t.id
            JOIN chart_of_accounts a ON tl.account_id = a.id
            WHERE YEAR(t.transaction_date) = :year
            GROUP BY MONTH(t.transaction_date)
            ORDER BY MONTH(t.transaction_date)
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['year' => $year]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getExpenseBreakdown($year = null) {
        $year = $year ?? date('Y');
        $sql = "
            SELECT
                a.account_name AS category,
                SUM(tl.debit - tl.credit) AS amount
            FROM transaction_lines tl
            JOIN transactions t ON tl.transaction_id = t.id
            JOIN chart_of_accounts a ON tl.account_id = a.id
            WHERE a.account_type = 'expense' AND YEAR(t.transaction_date) = :year
            GROUP BY a.account_name
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['year' => $year]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCashFlow($year = null) {
        $year = $year ?? date('Y');
        $sql = "
            SELECT
                MONTH(t.transaction_date) AS month,
                SUM(CASE WHEN a.account_type = 'Asset' AND tl.debit > 0 THEN tl.debit ELSE 0 END) AS cash_in,
                SUM(CASE WHEN a.account_type = 'Asset' AND tl.credit > 0 THEN tl.credit ELSE 0 END) AS cash_out
            FROM transaction_lines tl
            JOIN transactions t ON tl.transaction_id = t.id
            JOIN chart_of_accounts a ON tl.account_id = a.id
            WHERE YEAR(t.transaction_date) = :year
            GROUP BY MONTH(t.transaction_date)
            ORDER BY MONTH(t.transaction_date)
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['year' => $year]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProfitTrend($year = null) {
        $year = $year ?? date('Y');
        $sql = "
            SELECT
                MONTH(t.transaction_date) AS month,
                SUM(CASE WHEN a.account_type = 'revenue' THEN tl.credit - tl.debit ELSE 0 END) -
                SUM(CASE WHEN a.account_type = 'expense' THEN tl.debit - tl.credit ELSE 0 END) AS profit
            FROM transaction_lines tl
            JOIN transactions t ON tl.transaction_id = t.id
            JOIN chart_of_accounts a ON tl.account_id = a.id
            WHERE YEAR(t.transaction_date) = :year
            GROUP BY MONTH(t.transaction_date)
            ORDER BY MONTH(t.transaction_date)
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['year' => $year]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
