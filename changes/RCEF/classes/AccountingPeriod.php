<?php
class AccountingPeriod {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Create a new accounting period
     */
    public function createPeriod(string $name, DateTime $startDate, DateTime $endDate, int $userId): int {
        try {
            $stmt = $this->pdo->prepare("CALL create_accounting_period(?, ?, ?, ?)");
            $stmt->execute([
                $name,
                $startDate->format('Y-m-d'),
                $endDate->format('Y-m-d'),
                $userId
            ]);

            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            throw new Exception("Failed to create accounting period: " . $e->getMessage());
        }
    }

    /**
     * Close an accounting period
     */
    public function closePeriod(int $periodId, int $userId, string $description = ''): void {
        try {
            $stmt = $this->pdo->prepare("CALL close_accounting_period(?, ?, ?)");
            $stmt->execute([$periodId, $userId, $description]);
        } catch (PDOException $e) {
            throw new Exception("Failed to close accounting period: " . $e->getMessage());
        }
    }

    /**
     * Reopen a closed accounting period
     */
    public function reopenPeriod(int $periodId, int $userId): void {
        try {
            $stmt = $this->pdo->prepare("UPDATE accounting_periods SET is_closed = 0, closed_by = NULL, closed_at = NULL WHERE id = ?");
            $stmt->execute([$periodId]);
        } catch (PDOException $e) {
            throw new Exception("Failed to reopen accounting period: " . $e->getMessage());
        }
    }

    /**
     * Reactivate a previous accounting period
     */
public function reactivatePeriod(int $periodId, int $userId): void {
    try {
        $stmt = $this->pdo->prepare("CALL reactivate_accounting_period(?, ?)");
        $stmt->execute([$periodId, $userId]);
    } catch (PDOException $e) {
        throw new Exception("Failed to reactivate accounting period: " . $e->getMessage());
    }
}

    /**
     * Get all accounting periods
     */
    public function getAllPeriods(): array {
        $stmt = $this->pdo->query("
            SELECT p.*,
                   u.full_name AS created_by_name,
                   c.full_name AS closed_by_name
            FROM accounting_periods p
            LEFT JOIN users u ON p.created_by = u.id
            LEFT JOIN users c ON p.closed_by = c.id
            ORDER BY p.start_date DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get current active period
     */
    public function getCurrentPeriod(): ?array {
        $stmt = $this->pdo->query("
            SELECT * FROM accounting_periods
            WHERE is_active = TRUE
            LIMIT 1
        ");
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Get period by ID
     */
    public function getPeriodById(int $periodId): ?array {
        $stmt = $this->pdo->prepare("
            SELECT p.*,
                   u.full_name AS created_by_name,
                   c.full_name AS closed_by_name
            FROM accounting_periods p
            LEFT JOIN users u ON p.created_by = u.id
            LEFT JOIN users c ON p.closed_by = c.id
            WHERE p.id = ?
        ");
        $stmt->execute([$periodId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Check if a date falls within any existing period
     */
    public function isDateInAnyPeriod(DateTime $date): bool {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM accounting_periods
            WHERE ? BETWEEN start_date AND end_date
        ");
        $stmt->execute([$date->format('Y-m-d')]);
        return $stmt->fetchColumn() > 0;
    }
}
