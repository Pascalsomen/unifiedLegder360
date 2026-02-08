<?php
class PurchaseOrder {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Create a new purchase order
     */
    public function createPurchaseOrder(
        int $supplierId,
        int $createdBy,
        string $orderDate,
        ?string $expectedDeliveryDate,
        ?string $notes,
        ?string $terms,
        array $items
    ): int {
        // Start transaction
        $this->pdo->beginTransaction();

        try {
            // Generate PO number (format: PO-YYYYMMDD-XXXX)
            $poNumber = 'PO-' . date('Ymd') . '-' . str_pad(
                $this->getNextPONumberSequence(),
                4,
                '0',
                STR_PAD_LEFT
            );

            // Insert purchase order header
            $stmt = $this->pdo->prepare("
                INSERT INTO purchase_orders (
                    po_number,
                    supplier_id,
                    created_by,
                    order_date,
                    expected_delivery_date,
                    notes,
                    terms,
                    status_id,
                    created_at
                ) VALUES (
                    :po_number,
                    :supplier_id,
                    :created_by,
                    :order_date,
                    :expected_delivery_date,
                    :notes,
                    :terms,
                    1, /* Draft status */
                    NOW()
                )
            ");

            $stmt->execute([
                ':po_number' => $poNumber,
                ':supplier_id' => $supplierId,
                ':created_by' => $createdBy,
                ':order_date' => $orderDate,
                ':expected_delivery_date' => $expectedDeliveryDate,
                ':notes' => $notes,
                ':terms' => $terms
            ]);

            $poId = $this->pdo->lastInsertId();

            // Insert purchase order items
            $totalAmount = 0;

            foreach ($items as $item) {
                $itemTotal = $item['quantity'] * $item['price'];
                $totalAmount += $itemTotal;

                $stmt = $this->pdo->prepare("
                    INSERT INTO purchase_order_items (
                        po_id,
                        item_id,
                        description,
                        quantity,
                        unit_price,
                        tax_rate_id,
                        line_total
                    ) VALUES (
                        :po_id,
                        :item_id,
                        :description,
                        :quantity,
                        :unit_price,
                        :tax_rate_id,
                        :line_total
                    )
                ");

                $stmt->execute([
                    ':po_id' => $poId,
                    ':item_id' => $item['item_id'],
                    ':description' => $item['description'],
                    ':quantity' => $item['quantity'],
                    ':unit_price' => $item['price'],
                    ':tax_rate_id' => $item['tax_rate_id'] ?: null,
                    ':line_total' => $itemTotal
                ]);
            }

            // Update total amount in the header
            $stmt = $this->pdo->prepare("
                UPDATE purchase_orders
                SET total_amount = :total_amount
                WHERE id = :id
            ");

            $stmt->execute([
                ':total_amount' => $totalAmount,
                ':id' => $poId
            ]);

            // Commit transaction
            $this->pdo->commit();

            return $poId;

        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Get all purchase orders with optional filtering
     */
    public function getAll(string $filter = 'all', ?int $userId = null): array {
        $sql = "
            SELECT
                po.*,
                s.name AS supplier_name,
                u.full_name AS created_by_name,
                pos.name AS status_name,
                pos.id AS status_id
            FROM purchase_orders po
            JOIN suppliers s ON po.supplier_id = s.id
            JOIN users u ON po.created_by = u.id
            JOIN purchase_order_status pos ON po.status_id = pos.id
        ";

        $params = [];

        // Apply filters
        switch ($filter) {
            case 'draft':
                $sql .= " WHERE po.status_id = 1";
                break;
            case 'pending_approval':
                $sql .= " WHERE po.status_id = 2";
                break;
            case 'approved':
                $sql .= " WHERE po.status_id = 3";
                break;
            case 'ordered':
                $sql .= " WHERE po.status_id = 4";
                break;
            case 'pending_receipt':
                $sql .= " WHERE po.status_id = 5";
                break;
            case 'my_orders':
                $sql .= " WHERE po.created_by = :user_id";
                $params[':user_id'] = $userId;
                break;
            case 'all':
            default:
                // No additional filter
                break;
        }

        $sql .= " ORDER BY po.order_date DESC, po.id DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /**
     * Get purchase order details by ID
     */
    public function getById(int $id): ?array {
        $stmt = $this->pdo->prepare("
            SELECT
                po.*,
                s.name AS supplier_name,
                s.contact_person,
                s.email AS supplier_email,
                s.phone AS supplier_phone,
                s.address AS supplier_address,
                u.full_name AS created_by_name

            FROM purchase_orders po
            JOIN suppliers s ON po.supplier_id = s.id
            JOIN users u ON po.created_by = u.id
            WHERE po.id = :id
        ");

        $stmt->execute([':id' => $id]);
        $po = $stmt->fetch();

        if (!$po) {
            return null;
        }

        // Get items
        $stmt = $this->pdo->prepare("
            SELECT
                poi.*,
                i.item_code,
                i.description AS item_description,
                tr.name AS tax_rate_name,
            FROM purchase_order_items poi
            LEFT JOIN inventory_items i ON poi.item_id = i.id
            WHERE poi.purchase_order_id = :po_id
        ");

        $stmt->execute([':po_id' => $id]);
        $po['items'] = $stmt->fetchAll();

        return $po;
    }

    /**
     * Get the next PO number sequence
     */
    private function getNextPONumberSequence(): int {
        $stmt = $this->pdo->query("
            SELECT COUNT(*) + 1 AS next_num
            FROM purchase_orders
            WHERE DATE(created_at) = CURDATE()
        ");

        $result = $stmt->fetch();
        return (int)$result['next_num'];
    }

    /**
     * Update purchase order status
     */
    // public function updateStatus(int $poId, int $statusId, ?int $updatedBy = null): bool {
    //     $stmt = $this->pdo->prepare("
    //         UPDATE purchase_orders
    //         SET
    //             status_id = :status_id,
    //             updated_by = :updated_by,
    //             updated_at = NOW()
    //         WHERE id = :id
    //     ");

    //     return $stmt->execute([
    //         ':id' => $poId,
    //         ':status_id' => $statusId,
    //         ':updated_by' => $updatedBy
    //     ]);
    // }

    /**
     * Get purchase order status history
     */
    // public function getStatusHistory(int $poId): array {
    //     $stmt = $this->pdo->prepare("
    //         SELECT
    //             posh.*,
    //             u.name AS changed_by_name,
    //             pos.name AS status_name
    //         FROM purchase_order_status_history posh
    //         JOIN users u ON posh.changed_by = u.id
    //         JOIN purchase_order_status pos ON posh.status_id = pos.id
    //         WHERE posh.po_id = :po_id
    //         ORDER BY posh.changed_at DESC
    //     ");

    //     $stmt->execute([':po_id' => $poId]);
    //     return $stmt->fetchAll();
    // }



/**
 * Update purchase order status and record history
 */
public function updateStatus(int $poId, int $statusId, ?int $changedBy = null, ?string $notes = null): bool
{
    $this->pdo->beginTransaction();

    try {
        // Update the main PO status
        $stmt = $this->pdo->prepare("
            UPDATE purchase_orders
            SET
                status_id = :status_id,
                updated_by = :updated_by,
                updated_at = NOW()
            WHERE id = :id
        ");

        $stmt->execute([
            ':id' => $poId,
            ':status_id' => $statusId,
            ':updated_by' => $changedBy
        ]);

        // Record the status change in history
        $this->recordStatusChange($poId, $statusId, $changedBy, $notes);

        $this->pdo->commit();
        return true;

    } catch (Exception $e) {
        $this->pdo->rollBack();
        throw $e;
    }
}

/**
 * Record a status change in history
 */
private function recordStatusChange(int $poId, int $statusId, ?int $changedBy, ?string $notes): void
{
    $stmt = $this->pdo->prepare("
        INSERT INTO purchase_order_status_history (
            po_id,
            status_id,
            changed_by,
            notes
        ) VALUES (
            :po_id,
            :status_id,
            :changed_by,
            :notes
        )
    ");

    $stmt->execute([
        ':po_id' => $poId,
        ':status_id' => $statusId,
        ':changed_by' => $changedBy,
        ':notes' => $notes
    ]);
}

/**
 * Get status history for a purchase order
 */
public function getStatusHistory(int $poId): array
{
    $stmt = $this->pdo->prepare("
        SELECT
            posh.*,
            u.full_name AS changed_by_name,
            pos.name AS status_name

        FROM purchase_order_status_history posh
        JOIN users u ON posh.changed_by = u.id
        JOIN purchase_order_status pos ON posh.status_id = pos.id
        WHERE posh.po_id = :po_id
        ORDER BY posh.changed_at DESC
    ");

    $stmt->execute([':po_id' => $poId]);
    return $stmt->fetchAll();
}

/**
 * Get the current status of a purchase order
 */
public function getCurrentStatus(int $poId): ?array
{
    $stmt = $this->pdo->prepare("
        SELECT
            pos.*,
            u.name AS changed_by_name,
            posh.changed_at
        FROM purchase_order_status_history posh
        JOIN purchase_order_status pos ON posh.status_id = pos.id
        JOIN users u ON posh.changed_by = u.id
        WHERE posh.po_id = :po_id
        ORDER BY posh.changed_at DESC
        LIMIT 1
    ");

    $stmt->execute([':po_id' => $poId]);
    return $stmt->fetch() ?: null;
}

public function receivePurchaseOrder(
    int $poId,
    int $receivedBy,
    array $receivedItems,
    ?string $notes = null,
    ?DateTime $receiptDate = null
): int {
    $startedTransaction = false;

    if (!$this->pdo->inTransaction()) {
        $this->pdo->beginTransaction();
        $startedTransaction = true;
    }

    try {
        // Create receipt header
        $stmt = $this->pdo->prepare("
            INSERT INTO purchase_order_receipts (
                po_id,
                received_by,
                receipt_date,
                notes
            ) VALUES (
                :po_id,
                :received_by,
                :receipt_date,
                :notes
            )
        ");

        $stmt->execute([
            ':po_id' => $poId,
            ':received_by' => $receivedBy,
            ':receipt_date' => $receiptDate ? $receiptDate->format('Y-m-d H:i:s') : date('Y-m-d H:i:s'),
            ':notes' => $notes
        ]);

        $receiptId = (int) $this->pdo->lastInsertId();

        // Record received items
        foreach ($receivedItems as $item) {
            $this->recordReceivedItem(
                $receiptId,
                $item['po_item_id'],
                $item['quantity_received'],
                $item['condition_notes'] ?? null,
                $item['location_id'] ?? null,
                $item['batch_number'] ?? null,
                $item['expiry_date'] ?? null
            );

            // Update inventory if item is stockable
            if ($item['update_inventory'] ?? false) {
                $this->updateInventory(
                    $item['item_id'],
                    $item['quantity_received'],
                    $item['location_id'] ?? null,
                    $item['batch_number'] ?? null,
                    $item['expiry_date'] ?? null,
                    $receiptId // Pass explicitly
                );
            }
        }

        // Update PO status if all items are received
        $this->checkIfComplete($poId);

        if ($startedTransaction) {
            $this->pdo->commit();
        }

        return $receiptId;
    } catch (Exception $e) {
        if ($startedTransaction) {
            $this->pdo->rollBack();
        }
        throw $e;
    }
}


    private function recordReceivedItem(
        int $receiptId,
        int $poItemId,
        float $quantityReceived,
        ?string $conditionNotes,
        ?int $locationId,
        ?string $batchNumber,
        ?string $expiryDate
    ): void {
        $stmt = $this->pdo->prepare("
            INSERT INTO purchase_order_received_items (
                receipt_id,
                po_item_id,
                quantity_received,
                condition_notes,
                location_id,
                batch_number,
                expiry_date
            ) VALUES (
                :receipt_id,
                :po_item_id,
                :quantity_received,
                :condition_notes,
                :location_id,
                :batch_number,
                :expiry_date
            )
        ");

        $stmt->execute([
            ':receipt_id' => $receiptId,
            ':po_item_id' => $poItemId,
            ':quantity_received' => $quantityReceived,
            ':condition_notes' => $conditionNotes,
            ':location_id' => $locationId,
            ':batch_number' => $batchNumber,
            ':expiry_date' => $expiryDate ?: null
        ]);
    }






private function updateInventory(
    int $itemId,
    float $quantity,
    ?int $locationId,
    ?string $batchNumber,
    ?string $expiryDate,
    int $referenceId // <-- added this
): void {
    $stmt = $this->pdo->prepare("
        INSERT INTO inventory_movements (
            item_id,
            location_id,
            quantity,
            movement_type,
            reference_id,
            batch_number,
            expiry_date
        ) VALUES (
            :item_id,
            :location_id,
            :quantity,
            'purchase_receipt',
            :reference_id,
            :batch_number,
            :expiry_date
        )
    ");

    $stmt->execute([
        ':item_id' => $itemId,
        ':location_id' => $locationId,
        ':quantity' => $quantity,
        ':reference_id' => $referenceId,
        ':batch_number' => $batchNumber,
        ':expiry_date' => $expiryDate ?: null
    ]);
}

    private function checkIfComplete(int $poId): void
    {
        // Check if all items have been fully received
        $stmt = $this->pdo->prepare("
            SELECT
                poi.id,
                poi.quantity AS ordered_quantity,
                IFNULL(SUM(pori.quantity_received), 0) AS received_quantity
            FROM purchase_order_items poi
            LEFT JOIN purchase_order_received_items pori ON poi.id = pori.po_item_id
            WHERE poi.po_id = :po_id
            GROUP BY poi.id
            HAVING ordered_quantity > received_quantity
            LIMIT 1
        ");

        $stmt->execute([':po_id' => $poId]);

        // If no rows returned, all items are fully received
        if ($stmt->rowCount() === 0) {
            $this->updateStatus($poId, 6, $_SESSION['user_id'], 'All items received'); // Status 6 = Completed
        } else {
            $this->updateStatus($poId, 5, $_SESSION['user_id'], 'Partial receipt recorded'); // Status 5 = Partially Received
        }
    }

    /**
     * Get receipt history for a purchase order
     */
    public function getReceiptHistory(int $poId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                por.*,
                u.name AS received_by_name,
                COUNT(pori.id) AS item_count,
                SUM(pori.quantity_received * poi.unit_price) AS total_value
            FROM purchase_order_receipts por
            JOIN users u ON por.received_by = u.id
            LEFT JOIN purchase_order_received_items pori ON por.id = pori.receipt_id
            LEFT JOIN purchase_order_items poi ON pori.po_item_id = poi.id
            WHERE por.po_id = :po_id
            GROUP BY por.id
            ORDER BY por.receipt_date DESC
        ");

        $stmt->execute([':po_id' => $poId]);
        return $stmt->fetchAll();
    }

    /**
     * Get details for a specific receipt
     */
    public function getReceiptDetails(int $receiptId): array
    {
        // Get receipt header
        $stmt = $this->pdo->prepare("
            SELECT
                por.*,
                u.name AS received_by_name,
                po.po_number
            FROM purchase_order_receipts por
            JOIN users u ON por.received_by = u.id
            JOIN purchase_orders po ON por.po_id = po.id
            WHERE por.id = :receipt_id
        ");

        $stmt->execute([':receipt_id' => $receiptId]);
        $receipt = $stmt->fetch();

        if (!$receipt) {
            throw new Exception("Receipt not found");
        }

        // Get received items
        $stmt = $this->pdo->prepare("
            SELECT
                pori.*,
                poi.item_id,
                poi.description,
                poi.quantity AS ordered_quantity,
                poi.unit_price,
                i.item_code,
                l.name AS location_name
            FROM purchase_order_received_items pori
            JOIN purchase_order_items poi ON pori.po_item_id = poi.id
            LEFT JOIN items i ON poi.item_id = i.id
            LEFT JOIN inventory_locations l ON pori.location_id = l.id
            WHERE pori.receipt_id = :receipt_id
        ");

        $stmt->execute([':receipt_id' => $receiptId]);
        $receipt['items'] = $stmt->fetchAll();

        return $receipt;
    }


/**
 * Get valid status transitions for a purchase order
 */
public function getValidTransitions(int $currentStatusId): array
{
    // Define allowed status transitions
    $transitions = [
        1 => [2, 7], // Draft -> Pending Approval or Cancelled
        2 => [3, 7], // Pending Approval -> Approved or Cancelled
        3 => [4, 7], // Approved -> Ordered or Cancelled
        4 => [5],    // Ordered -> Pending Receipt
        5 => [6, 7], // Pending Receipt -> Completed or Cancelled
        6 => [],     // Completed - no further changes
        7 => []      // Cancelled - no further changes
    ];

    return $transitions[$currentStatusId] ?? [];
}

/**
 * Get pending approvals for a user
 */
public function getPendingApprovals(int $userId): array
{
    $stmt = $this->pdo->prepare("
        SELECT
            poa.*,
            po.po_number,
            s.name AS status_name,
            u.name AS requested_by_name
        FROM purchase_order_approvals poa
        JOIN purchase_orders po ON poa.po_id = po.id
        JOIN purchase_order_status s ON poa.status_id = s.id
        JOIN users u ON poa.requested_by = u.id
        WHERE poa.approver_id = :user_id
        AND poa.approved_at IS NULL
        ORDER BY poa.requested_at DESC
    ");

    $stmt->execute([':user_id' => $userId]);
    return $stmt->fetchAll();
}



}

