<?php
class RequisitionSystem {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Create a new requisition
     */
    public function createRequisition(array $header, array $items, int $userId): int {
        $this->validateRequisition($header, $items);

        $stmt = $this->pdo->prepare("
        SELECT COUNT(*) FROM departments
        WHERE id = ? AND is_active = TRUE
    ");
    $stmt->execute([$header['department_id']]);

    if ($stmt->fetchColumn() === 0) {
        throw new Exception("Invalid or inactive department");
    }

        try {
            $this->pdo->beginTransaction();

            // Generate requisition number
            $requisitionNumber = 'REQ-' . date('Ymd') . '-' . str_pad(
                $this->getNextRequisitionNumber(), 4, '0', STR_PAD_LEFT
            );

            // Insert requisition header
            $stmt = $this->pdo->prepare("
                INSERT INTO internal_requisitions (
                    requisition_number, requester_id, department_id,
                    needed_by_date, purpose, status
                ) VALUES (?, ?, ?, ?, ?, 'draft')
            ");
            $stmt->execute([
                $requisitionNumber,
                $userId,
                $header['department_id'],
                $header['needed_by_date'],
                $header['purpose']
            ]);
            $requisitionId = $this->pdo->lastInsertId();

            // Insert requisition items
            $itemStmt = $this->pdo->prepare("
                INSERT INTO internal_requisition_items (
                    requisition_id, item_id, quantity, unit_of_measure, purpose
                ) VALUES (?, ?, ?, ?, ?)
            ");

            foreach ($items as $item) {
                $itemStmt->execute([
                    $requisitionId,
                    $item['item_id'],
                    $item['quantity'],
                    $item['unit_of_measure'],
                    $item['purpose'] ?? ''
                ]);
            }

            $this->pdo->commit();
            return $requisitionId;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Submit a requisition for approval
     */
    public function submitForApproval(int $requisitionId, int $userId): void {
        try {
            $this->pdo->beginTransaction();

            // Update requisition status
            $stmt = $this->pdo->prepare("
                UPDATE internal_requisitions
                SET status = 'submitted'
                WHERE id = ? AND requester_id = ? AND status = 'draft'
            ");
            $stmt->execute([$requisitionId, $userId]);

            if ($stmt->rowCount() === 0) {
                throw new Exception("Requisition cannot be submitted");
            }

            // Create approval workflow
            $this->createApprovalWorkflow($requisitionId);

            $this->pdo->commit();

            // Send notifications (to be implemented)
            $this->sendApprovalNotifications($requisitionId);
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Create approval workflow for a requisition
     */
    private function createApprovalWorkflow(int $requisitionId): void {
        // Get requisition details
        $requisition = $this->getRequisitionDetails($requisitionId);

        // Get applicable approval workflow
        $approvers = $this->getApproversForRequisition($requisition);

        // Insert approval records
        $stmt = $this->pdo->prepare("
            INSERT INTO requisition_approvals (
                requisition_id, approver_id, approval_level
            ) VALUES (?, ?, ?)
        ");

        foreach ($approvers as $level => $approverId) {
            $stmt->execute([$requisitionId, $approverId, $level + 1]);
        }
    }

    /**
     * Get approvers for a requisition based on workflow rules
     */




private function getApproversForRequisition(array $requisition): array {
    // Get department hierarchy
    $stmt = $this->pdo->prepare("
        WITH RECURSIVE dept_hierarchy AS (
            SELECT id, parent_id, 1 AS level
            FROM departments WHERE id = ?

            UNION ALL

            SELECT d.id, d.parent_id, dh.level + 1
            FROM departments d
            JOIN dept_hierarchy dh ON d.id = dh.parent_id
        )
        SELECT level FROM dept_hierarchy ORDER BY level DESC LIMIT 1
    ");
    $stmt->execute([$requisition['department_id']]);
    $hierarchyDepth = $stmt->fetchColumn() ?: 1;

    // Get approvers considering department hierarchy
    $approvers = [];
    for ($level = 1; $level <= $hierarchyDepth; $level++) {
        $stmt = $this->pdo->prepare("
            SELECT a.approver_id
            FROM approval_workflows a
            WHERE a.department_id IN (
                WITH RECURSIVE dept_hierarchy AS (
                    SELECT id, parent_id
                    FROM departments WHERE id = ?

                    UNION ALL

                    SELECT d.id, d.parent_id
                    FROM departments d
                    JOIN dept_hierarchy dh ON d.id = dh.parent_id
                )
                SELECT id FROM dept_hierarchy WHERE id = ? OR parent_id IS NOT NULL
            )
            AND a.approval_level = ?
            LIMIT 1
        ");
        $stmt->execute([$requisition['department_id'], $requisition['department_id'], $level]);
        $approverId = $stmt->fetchColumn();

        if ($approverId) {
            $approvers[$level] = $approverId;
        }
    }

    return $approvers;
}





    // private function getApproversForRequisition(array $requisition): array {
    //     // Check department-specific approvers first
    //     $stmt = $this->pdo->prepare("
    //         SELECT approval_level, approver_id
    //         FROM approval_workflows
    //         WHERE department_id = ?
    //         ORDER BY approval_level
    //     ");
    //     $stmt->execute([$requisition['department_id']]);
    //     $departmentApprovers = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    //     if (!empty($departmentApprovers)) {
    //         return $departmentApprovers;
    //     }

    //     // Fall back to default approvers
    //     $stmt = $this->pdo->query("
    //         SELECT approval_level, approver_id
    //         FROM approval_workflows
    //         WHERE department_id IS NULL
    //         ORDER BY approval_level
    //     ");
    //     return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    // }

    /**
     * Process approval/rejection of a requisition
     */
    public function processApproval(int $approvalId, int $approverId, string $action, string $comments = ''): void {
        try {
            $this->pdo->beginTransaction();

            // Update approval record
            $stmt = $this->pdo->prepare("
                UPDATE requisition_approvals
                SET status = ?, comments = ?, action_date = NOW()
                WHERE id = ? AND approver_id = ? AND status = 'pending'
            ");
            $stmt->execute([$action, $comments, $approvalId, $approverId]);

            if ($stmt->rowCount() === 0) {
                throw new Exception("Approval action cannot be processed");
            }

            // Get requisition ID
            $stmt = $this->pdo->prepare("SELECT requisition_id FROM requisition_approvals WHERE id = ?");
            $stmt->execute([$approvalId]);
            $requisitionId = $stmt->fetchColumn();

            if ($action === 'rejected') {
                // Reject the entire requisition
                $this->pdo->prepare("
                    UPDATE internal_requisitions
                    SET status = 'rejected'
                    WHERE id = ?
                ")->execute([$requisitionId]);

                // Reject all items
                $this->pdo->prepare("
                    UPDATE internal_requisition_items
                    SET status = 'rejected'
                    WHERE requisition_id = ?
                ")->execute([$requisitionId]);
            } else {
                // Check if all approvals are complete
                $stmt = $this->pdo->prepare("
                    SELECT COUNT(*)
                    FROM requisition_approvals
                    WHERE requisition_id = ? AND status = 'pending'
                ");
                $stmt->execute([$requisitionId]);
                $pendingApprovals = $stmt->fetchColumn();

                if ($pendingApprovals === 0) {
                    // Approve the requisition
                    $this->pdo->prepare("
                        UPDATE internal_requisitions
                        SET status = 'approved'
                        WHERE id = ?
                    ")->execute([$requisitionId]);

                    // Approve all items
                    $this->pdo->prepare("
                        UPDATE internal_requisition_items
                        SET status = 'approved'
                    ")->execute([$requisitionId]);
                }
            }

            $this->pdo->commit();

            // Send notifications (to be implemented)
            $this->sendApprovalNotifications($requisitionId);
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Fulfill a requisition item
     */
    public function fulfillItem(int $itemId, float $quantity, int $userId, ?int $fromLocationId = null, string $notes = ''): void {
        try {
            $this->pdo->beginTransaction();

            // Get current fulfillment status
            $stmt = $this->pdo->prepare("
                SELECT quantity, fulfilled_quantity, requisition_id, item_id
                FROM internal_requisition_items
                WHERE id = ? AND status = 'approved'
            ");
            $stmt->execute([$itemId]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$item) {
                throw new Exception("Item cannot be fulfilled");
            }

            $newFulfilled = $item['fulfilled_quantity'] + $quantity;
            $remaining = $item['quantity'] - $item['fulfilled_quantity'];

            if ($quantity > $remaining) {
                throw new Exception("Cannot fulfill more than remaining quantity");
            }

            // Record fulfillment
            $this->pdo->prepare("
                INSERT INTO requisition_fulfillments (
                    requisition_item_id, fulfilled_by, quantity, from_location_id, notes
                ) VALUES (?, ?, ?, ?, ?)
            ")->execute([$itemId, $userId, $quantity, $fromLocationId, $notes]);

            // Update item status
            $newStatus = ($newFulfilled >= $item['quantity']) ? 'fulfilled' : 'partially_fulfilled';
            $this->pdo->prepare("
                UPDATE internal_requisition_items
                SET fulfilled_quantity = ?, status = ?
                WHERE id = ?
            ")->execute([$newFulfilled, $newStatus, $itemId]);

            // Update inventory
            $this->adjustInventory($item['item_id'], -$quantity, $fromLocationId, "Requisition fulfillment for item #$itemId");

            // Check if all items are fulfilled
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*)
                FROM internal_requisition_items
                WHERE requisition_id = ? AND status NOT IN ('fulfilled', 'rejected')
            ");
            $stmt->execute([$item['requisition_id']]);
            $pendingItems = $stmt->fetchColumn();

            if ($pendingItems === 0) {
                // Mark requisition as fulfilled
                $this->pdo->prepare("
                    UPDATE internal_requisitions
                    SET status = 'fulfilled'
                    WHERE id = ?
                ")->execute([$item['requisition_id']]);
            } else {
                // Mark requisition as partially fulfilled
                $this->pdo->prepare("
                    UPDATE internal_requisitions
                    SET status = 'partially_fulfilled'
                    WHERE id = ? AND status = 'approved'
                ")->execute([$item['requisition_id']]);
            }

            $this->pdo->commit();
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Get requisition details
     */
    public function getRequisitionDetails(int $requisitionId): array {
        $stmt = $this->pdo->prepare("
            SELECT r.*, d.name AS department_name,
                   CONCAT(u.full_name, ' ', u.full_name) AS requester_name
            FROM internal_requisitions r
            JOIN departments d ON r.department_id = d.id
            JOIN users u ON r.requester_id = u.id
            WHERE r.id = ?
        ");
        $stmt->execute([$requisitionId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Get requisition items
     */
    public function getRequisitionItems(int $requisitionId): array {
        $stmt = $this->pdo->prepare("
            SELECT i.*, it.item_code, it.name, it.current_quantity AS current_stock
            FROM internal_requisition_items i
            JOIN inventory_items it ON i.item_id = it.id
            WHERE i.requisition_id = ?
        ");
        $stmt->execute([$requisitionId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }




    /**
     * Get approval status for a requisition
     */
    public function getApprovalStatus(int $requisitionId): array {
        $stmt = $this->pdo->prepare("
            SELECT a.*,
                   CONCAT(u.full_name, ' ', u.full_name) AS approver_name,
                   u.email AS approver_email
            FROM requisition_approvals a
            JOIN users u ON a.approver_id = u.id
            WHERE a.requisition_id = ?
            ORDER BY a.approval_level
        ");
        $stmt->execute([$requisitionId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get pending approvals for a user
     */
    public function getPendingApprovals(int $userId): array {
        $stmt = $this->pdo->prepare("
            SELECT r.id, r.requisition_number, r.request_date,
                   CONCAT(u.full_name, ' ', u.full_name) AS requester_name,
                   d.name AS department_name
            FROM requisition_approvals a
            JOIN internal_requisitions r ON a.requisition_id = r.id
            JOIN users u ON r.requester_id = u.id
            JOIN departments d ON r.department_id = d.id
            WHERE a.approver_id = ? AND a.status = 'pending'
            ORDER BY r.needed_by_date, a.approval_level
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get requisitions for a user
     */
    public function getUserRequisitions(int $userId, string $status = null): array {
        $sql = "
            SELECT r.*, d.name AS department_name,
                   (SELECT COUNT(*) FROM internal_requisition_items WHERE requisition_id = r.id) AS item_count
            FROM internal_requisitions r
            JOIN departments d ON r.department_id = d.id
            WHERE r.requester_id = ?
        ";

        $params = [$userId];

        if ($status) {
            $sql .= " AND r.status = ?";
            $params[] = $status;
        }

        $sql .= " ORDER BY r.request_date DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }



/**
 * Update an existing requisition
 *
 * @param int $requisitionId ID of the requisition to update
 * @param array $header Requisition header data (department_id, needed_by_date, purpose)
 * @param array $items Array of requisition items (item_id, quantity, unit_of_measure, purpose)
 * @param int $userId ID of the user updating the requisition
 * @return bool True on success
 * @throws Exception If validation fails or database error occurs
 */
public function updateRequisition(int $requisitionId, array $header, array $items, int $userId): bool
{
    // Validate input data
    $this->validateRequisition($header, $items);

    try {
        $this->pdo->beginTransaction();

        // Verify requisition exists and is editable
        $stmt = $this->pdo->prepare("
            SELECT id FROM internal_requisitions
            WHERE id = ? AND requester_id = ? AND status = 'draft'
        ");
        $stmt->execute([$requisitionId, $userId]);

        if (!$stmt->fetch()) {
            throw new Exception("Requisition not found or not editable");
        }

        // Verify department is active
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) FROM departments
            WHERE id = ? AND is_active = TRUE
        ");
        $stmt->execute([$header['department_id']]);

        if ($stmt->fetchColumn() === 0) {
            throw new Exception("Invalid or inactive department");
        }

        // Update requisition header
        $stmt = $this->pdo->prepare("
            UPDATE internal_requisitions SET
                department_id = ?,
                needed_by_date = ?,
                purpose = ?,
                updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([
            $header['department_id'],
            $header['needed_by_date'],
            $header['purpose'],
            $requisitionId
        ]);

        // Get current items to compare
        $currentItems = $this->getRequisitionItems($requisitionId);
        $currentItemIds = array_column($currentItems, 'item_id');

        // Prepare statements for item operations
        $insertStmt = $this->pdo->prepare("
            INSERT INTO internal_requisition_items (
                requisition_id, item_id, quantity, unit_of_measure, purpose
            ) VALUES (?, ?, ?, ?, ?)
        ");

        $updateStmt = $this->pdo->prepare("
            UPDATE internal_requisition_items SET
                quantity = ?,
                unit_of_measure = ?,
                purpose = ?
            WHERE requisition_id = ? AND item_id = ?
        ");

        $deleteStmt = $this->pdo->prepare("
            DELETE FROM internal_requisition_items
            WHERE requisition_id = ? AND item_id = ?
        ");

        $processedItemIds = [];

        // Process each item in the update
        foreach ($items as $item) {
            $itemId = (int)$item['item_id'];

            // Verify inventory item exists and is active
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) FROM inventory_items
                WHERE id = ? AND is_active = TRUE
            ");
            $stmt->execute([$itemId]);

            if ($stmt->fetchColumn() === 0) {
                throw new Exception("Invalid or inactive inventory item");
            }

            if (in_array($itemId, $currentItemIds)) {
                // Update existing item
                $updateStmt->execute([
                    $item['quantity'],
                    $item['unit_of_measure'],
                    $item['purpose'] ?? '',
                    $requisitionId,
                    $itemId
                ]);
            } else {
                // Insert new item
                $insertStmt->execute([
                    $requisitionId,
                    $itemId,
                    $item['quantity'],
                    $item['unit_of_measure'],
                    $item['purpose'] ?? ''
                ]);
            }

            $processedItemIds[] = $itemId;
        }

        // Delete items that were removed
        foreach ($currentItemIds as $currentItemId) {
            if (!in_array($currentItemId, $processedItemIds)) {
                $deleteStmt->execute([$requisitionId, $currentItemId]);
            }
        }

        $this->pdo->commit();
        return true;
    } catch (Exception $e) {
        $this->pdo->rollBack();
        throw $e;
    }
}




    /**
     * Validate requisition data
     */
    private function validateRequisition(array $header, array $items): void {
        if (empty($header['department_id'])) {
            throw new Exception("Department is required");
        }

        if (empty($header['needed_by_date'])) {
            throw new Exception("Needed by date is required");
        }

        if (empty($header['purpose'])) {
            throw new Exception("Purpose is required");
        }

        if (count($items) === 0) {
            throw new Exception("At least one item is required");
        }

        foreach ($items as $item) {
            if (empty($item['item_id'])) {
                throw new Exception("Item is required");
            }

            if (empty($item['quantity']) || $item['quantity'] <= 0) {
                throw new Exception("Quantity must be greater than zero");
            }
        }
    }

    /**
     * Get next requisition number sequence
     */
    private function getNextRequisitionNumber(): int {
        $stmt = $this->pdo->query("
            SELECT COUNT(*)
            FROM internal_requisitions
            WHERE request_date >= CURDATE()
        ");
        return (int)$stmt->fetchColumn() + 1;
    }

    /**
     * Adjust inventory after fulfillment
     */
    private function adjustInventory(int $itemId, float $quantity, ?int $locationId, string $notes): void {
        // This would integrate with your existing inventory system
        // Implementation depends on your inventory structure
    }

    /**
     * Send notifications for approval workflow
     */
    private function sendApprovalNotifications(int $requisitionId): void {
        // Implementation depends on your notification system
        // Could be email, in-app notifications, etc.
    }
}