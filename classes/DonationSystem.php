<?php
class DonationSystem {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Record a new donation
     */
    public function recordDonation(array $data): bool {
        $sql = "INSERT INTO donations (donor_id, amount, currency, donation_date, payment_method, purpose, project_id, is_acknowledged, receipt_number, receipt_path, created_by)
                VALUES (:donor_id, :amount, :currency, :donation_date, :payment_method, :purpose, :project_id, :is_acknowledged, :receipt_number, :receipt_path, :created_by)";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'donor_id' => $data['donor_id'],
            'amount' => $data['amount'],
            'currency' => $data['currency'],
            'donation_date' => $data['donation_date'],
            'payment_method' => $data['payment_method'],
            'purpose' => $data['purpose'],
            'project_id' => $data['project_id'] ?? null,
            'is_acknowledged' => $data['is_acknowledged'] ?? 0,
            'receipt_number' => $data['receipt_number'],
            'receipt_path' => $data['receipt_path'] ?? null,
            'created_by' => $data['created_by']
        ]);
    }

    /**
     * Get all donations
     */
    public function getAllDonations(): array {
        $sql = "SELECT d.*, donors.name AS donor_name, p.name AS project_name
                FROM donations d
                LEFT JOIN donors ON d.donor_id = donors.id
                LEFT JOIN donation_projects p ON d.project_id = p.id
                ORDER BY d.donation_date DESC";

        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get filtered donation report
     */
    public function getFilteredDonations(array $filters): array {
        $conditions = [];
        $params = [];

        if (!empty($filters['donor_id'])) {
            $conditions[] = "d.donor_id = :donor_id";
            $params['donor_id'] = $filters['donor_id'];
        }
        if (!empty($filters['project_id'])) {
            $conditions[] = "d.project_id = :project_id";
            $params['project_id'] = $filters['project_id'];
        }
        if (!empty($filters['from_date'])) {
            $conditions[] = "d.donation_date >= :from_date";
            $params['from_date'] = $filters['from_date'];
        }
        if (!empty($filters['to_date'])) {
            $conditions[] = "d.donation_date <= :to_date";
            $params['to_date'] = $filters['to_date'];
        }

        $sql = "SELECT d.*, donors.name AS donor_name, p.name AS project_name
                FROM donations d
                LEFT JOIN donors ON d.donor_id = donors.id
                LEFT JOIN donation_projects p ON d.project_id = p.id";

        if ($conditions) {
            $sql .= " WHERE " . implode(' AND ', $conditions);
        }

        $sql .= " ORDER BY d.donation_date DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Upload donation receipt
     */
    public function uploadReceipt(int $donationId, string $filePath): bool {
        $sql = "UPDATE donations SET receipt_path = :filePath WHERE id = :donationId";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'filePath' => $filePath,
            'donationId' => $donationId
        ]);
    }

    /**
     * Get a single donation by ID
     */
    public function getDonation(int $donationId): ?array {
        $sql = "SELECT d.*, donors.name AS donor_name, p.name AS project_name
                FROM donations d
                LEFT JOIN donors ON d.donor_id = donors.id
                LEFT JOIN projects p ON d.project_id = p.id
                WHERE d.id = :donationId";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['donationId' => $donationId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}
?>
