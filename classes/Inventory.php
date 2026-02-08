<?php
class Inventory {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    // ==============================================
    // ITEM MANAGEMENT METHODS
    // ==============================================

    /**
     * Get an inventory item by ID
     */
    public function getItem(int $itemId): ?array {
        $stmt = $this->pdo->prepare("
            SELECT i.*,
                   c.name AS category_name,
                   s.name AS supplier_name,
                   CONCAT(u.first_name, ' ', u.last_name) AS created_by_name
            FROM inventory_items i
            LEFT JOIN inventory_categories c ON i.category_id = c.id
            LEFT JOIN suppliers s ON i.supplier_id = s.id
            LEFT JOIN users u ON i.created_by = u.id
            WHERE i.id = ?
        ");
        $stmt->execute([$itemId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Get all active inventory items
     */
    public function getActiveItems(bool $includeLowStock = false): array {
        $sql = "
            SELECT i.*, c.name AS category_name
            FROM inventory_items i
            LEFT JOIN inventory_categories c ON i.category_id = c.id
            WHERE i.is_active = TRUE
        ";

        if ($includeLowStock) {
            $sql .= " AND i.quantity_on_hand <= i.reorder_level";
        }

        $sql .= " ORDER BY i.name";

        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Create a new inventory item
     */
    public function createItem(array $itemData, int $userId): int {
        $this->validateItemData($itemData);

        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare("
                INSERT INTO inventory_items (
                    item_code, name, description, category_id, supplier_id,
                    unit_of_measure, cost_price, selling_price, reorder_level,
                    quantity_on_hand, is_active, created_by, image_path
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $itemData['item_code'],
                $itemData['name'],
                $itemData['description'] ?? null,
                $itemData['category_id'] ?? null,
                $itemData['supplier_id'] ?? null,
                $itemData['unit_of_measure'],
                $itemData['cost_price'] ?? 0,
                $itemData['selling_price'] ?? 0,
                $itemData['reorder_level'] ?? 0,
                $itemData['quantity_on_hand'] ?? 0,
                $itemData['is_active'] ?? true,
                $userId,
                $itemData['image_path'] ?? null
            ]);

            $itemId = $this->pdo->lastInsertId();

            // Record initial inventory movement if quantity is provided
            if (isset($itemData['quantity_on_hand']) && $itemData['quantity_on_hand'] > 0) {
                $this->recordMovement(
                    $itemId,
                    'initial_balance',
                    $itemData['quantity_on_hand'],
                    null,
                    null,
                    'Initial inventory balance',
                    $userId
                );
            }

            $this->pdo->commit();
            return $itemId;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new Exception("Failed to create inventory item: " . $e->getMessage());
        }
    }

    /**
     * Update an inventory item
     */
    public function updateItem(int $itemId, array $itemData): bool {
        $this->validateItemData($itemData, $itemId);

        try {
            $stmt = $this->pdo->prepare("
                UPDATE inventory_items
                SET item_code = ?,
                    name = ?,
                    description = ?,
                    category_id = ?,
                    supplier_id = ?,
                    unit_of_measure = ?,
                    cost_price = ?,
                    selling_price = ?,
                    reorder_level = ?,
                    is_active = ?,
                    image_path = ?
                WHERE id = ?
            ");

            $stmt->execute([
                $itemData['item_code'],
                $itemData['name'],
                $itemData['description'] ?? null,
                $itemData['category_id'] ?? null,
                $itemData['supplier_id'] ?? null,
                $itemData['unit_of_measure'],
                $itemData['cost_price'] ?? 0,
                $itemData['selling_price'] ?? 0,
                $itemData['reorder_level'] ?? 0,
                $itemData['is_active'] ?? true,
                $itemData['image_path'] ?? null,
                $itemId
            ]);

            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            throw new Exception("Failed to update inventory item: " . $e->getMessage());
        }
    }

    /**
     * Validate item data before create/update
     */
    private function validateItemData(array $itemData, ?int $excludeItemId = null): void {
        if (empty($itemData['item_code'])) {
            throw new Exception("Item code is required");
        }

        if (empty($itemData['name'])) {
            throw new Exception("Item name is required");
        }

        if (empty($itemData['unit_of_measure'])) {
            throw new Exception("Unit of measure is required");
        }

        // Check for duplicate item code
        $sql = "SELECT COUNT(*) FROM inventory_items WHERE item_code = ?";
        $params = [$itemData['item_code']];

        if ($excludeItemId !== null) {
            $sql .= " AND id != ?";
            $params[] = $excludeItemId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        if ($stmt->fetchColumn() > 0) {
            throw new Exception("Item code already exists");
        }
    }

    // ==============================================
    // STOCK MOVEMENT METHODS
    // ==============================================

    /**
     * Record inventory movement
     */
    public function recordMovement(
        int $itemId,
        string $movementType,
        float $quantity,
        ?int $referenceId,
        ?string $referenceType,
        ?string $notes,
        int $userId,
        ?int $locationId = null
    ): int {
        if (!in_array($movementType, ['purchase', 'sale', 'adjustment', 'transfer', 'return', 'initial_balance'])) {
            throw new Exception("Invalid movement type");
        }

        try {
            $this->pdo->beginTransaction();

            // Get current cost price for the item
            $currentCost = $this->pdo->query("
                SELECT cost_price FROM inventory_items WHERE id = $itemId
            ")->fetchColumn();

            // Insert movement record
            $stmt = $this->pdo->prepare("
                INSERT INTO inventory_movements (
                    item_id, movement_type, quantity, reference_id, reference_type,
                    date_time, cost_price, notes, created_by, location_id
                ) VALUES (?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?)
            ");

            $stmt->execute([
                $itemId,
                $movementType,
                $quantity,
                $referenceId,
                $referenceType,
                $currentCost,
                $notes,
                $userId,
                $locationId
            ]);

            $movementId = $this->pdo->lastInsertId();

            // Update item quantity
            $this->adjustItemQuantity($itemId, $quantity);

            $this->pdo->commit();
            return $movementId;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new Exception("Failed to record inventory movement: " . $e->getMessage());
        }
    }

    /**
     * Adjust item quantity
     */
    private function adjustItemQuantity(int $itemId, float $quantityChange): void {
        $stmt = $this->pdo->prepare("
            UPDATE inventory_items
            SET quantity_on_hand = quantity_on_hand + ?
            WHERE id = ?
        ");
        $stmt->execute([$quantityChange, $itemId]);

        if ($stmt->rowCount() === 0) {
            throw new Exception("Item not found");
        }
    }

    /**
     * Get item movement history
     */
    public function getItemMovements(int $itemId, ?string $movementType = null, ?DateTime $startDate = null, ?DateTime $endDate = null): array {
        $sql = "
            SELECT m.*,
                   CONCAT(u.first_name, ' ', u.last_name) AS user_name,
                   l.name AS location_name
            FROM inventory_movements m
            LEFT JOIN users u ON m.created_by = u.id
            LEFT JOIN inventory_locations l ON m.location_id = l.id
            WHERE m.item_id = ?
        ";

        $params = [$itemId];

        if ($movementType) {
            $sql .= " AND m.movement_type = ?";
            $params[] = $movementType;
        }

        if ($startDate) {
            $sql .= " AND m.date_time >= ?";
            $params[] = $startDate->format('Y-m-d 00:00:00');
        }

        if ($endDate) {
            $sql .= " AND m.date_time <= ?";
            $params[] = $endDate->format('Y-m-d 23:59:59');
        }

        $sql .= " ORDER BY m.date_time DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ==============================================
    // STOCK ADJUSTMENT METHODS
    // ==============================================

    /**
     * Create a stock adjustment
     */
    public function createAdjustment(
        array $adjustmentHeader,
        array $adjustmentItems,
        int $userId
    ): int {
        try {
            $this->pdo->beginTransaction();

            // Insert adjustment header
            $stmt = $this->pdo->prepare("
                INSERT INTO inventory_adjustments (
                    adjustment_date, reference, reason, created_by
                ) VALUES (?, ?, ?, ?)
            ");

            $stmt->execute([
                $adjustmentHeader['adjustment_date'],
                $adjustmentHeader['reference'],
                $adjustmentHeader['reason'],
                $userId
            ]);

            $adjustmentId = $this->pdo->lastInsertId();

            // Process adjustment items
            foreach ($adjustmentItems as $item) {
                $this->processAdjustmentItem($adjustmentId, $item, $userId);
            }

            $this->pdo->commit();
            return $adjustmentId;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw new Exception("Failed to create inventory adjustment: " . $e->getMessage());
        }
    }

    /**
     * Process an individual adjustment item
     */
    private function processAdjustmentItem(int $adjustmentId, array $item, int $userId): void {
        // Get current quantity
        $currentQty = $this->pdo->query("
            SELECT quantity_on_hand FROM inventory_items WHERE id = {$item['item_id']}
        ")->fetchColumn();

        $newQty = $currentQty + $item['quantity_change'];

        // Insert adjustment item
        $stmt = $this->pdo->prepare("
            INSERT INTO inventory_adjustment_items (
                adjustment_id, item_id, quantity_change, new_quantity,
                cost_price, notes
            ) VALUES (?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $adjustmentId,
            $item['item_id'],
            $item['quantity_change'],
            $newQty,
            $item['cost_price'] ?? null,
            $item['notes'] ?? null
        ]);

        // Record inventory movement
        $movementType = $item['quantity_change'] > 0 ? 'adjustment' : 'adjustment';

        $this->recordMovement(
            $item['item_id'],
            $movementType,
            $item['quantity_change'],
            $adjustmentId,
            'adjustment',
            $item['notes'] ?? null,
            $userId,
            $item['location_id'] ?? null
        );
    }

    // ==============================================
    // REPORTING METHODS
    // ==============================================

    /**
     * Get current inventory levels
     */
    public function getInventoryLevels(?int $categoryId = null, ?int $supplierId = null, bool $includeInactive = false): array {
        $sql = "
            SELECT i.*,
                   c.name AS category_name,
                   s.name AS supplier_name,
                   CASE
                       WHEN i.quantity_on_hand <= 0 THEN 'out_of_stock'
                       WHEN i.quantity_on_hand <= i.reorder_level THEN 'low_stock'
                       ELSE 'in_stock'
                   END AS stock_status
            FROM inventory_items i
            LEFT JOIN inventory_categories c ON i.category_id = c.id
            LEFT JOIN suppliers s ON i.supplier_id = s.id
            WHERE 1=1
        ";

        $params = [];

        if (!$includeInactive) {
            $sql .= " AND i.is_active = TRUE";
        }

        if ($categoryId) {
            $sql .= " AND i.category_id = ?";
            $params[] = $categoryId;
        }

        if ($supplierId) {
            $sql .= " AND i.supplier_id = ?";
            $params[] = $supplierId;
        }

        $sql .= " ORDER BY i.name";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get inventory valuation report
     */
    public function getInventoryValuation(): array {
        $sql = "
            SELECT
                c.name AS category_name,
                COUNT(i.id) AS item_count,
                SUM(i.quantity_on_hand) AS total_quantity,
                SUM(i.quantity_on_hand * i.cost_price) AS total_value
            FROM inventory_items i
            LEFT JOIN inventory_categories c ON i.category_id = c.id
            WHERE i.is_active = TRUE
            GROUP BY c.name
            ORDER BY total_value DESC
        ";

        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get low stock items
     */
    public function getLowStockItems(): array {
        $sql = "
            SELECT i.*, c.name AS category_name
            FROM inventory_items i
            LEFT JOIN inventory_categories c ON i.category_id = c.id
            WHERE i.is_active = TRUE
            AND i.current_quantity <= i.reorder_level
            ORDER BY i.current_quantity ASC
        ";

        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // ==============================================
    // CATEGORY MANAGEMENT METHODS
    // ==============================================

    /**
     * Get all inventory categories
     */
    public function getCategories(): array {
        return $this->pdo->query("
            SELECT * FROM inventory_categories
            ORDER BY name
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get category by ID
     */
    public function getCategory(int $categoryId): ?array {
        $stmt = $this->pdo->prepare("
            SELECT * FROM inventory_categories
            WHERE id = ?
        ");
        $stmt->execute([$categoryId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    // ==============================================
    // LOCATION MANAGEMENT METHODS
    // ==============================================

    /**
     * Get all inventory locations
     */
    public function getLocations(): array {
        return $this->pdo->query("
            SELECT * FROM inventory_locations
            ORDER BY name
        ")->fetchAll(PDO::FETCH_ASSOC);
    }



 public function getTotalItemCount() {
        $sql = "SELECT COUNT(*) as total_count
                FROM inventory_items
                WHERE is_active = 1";
        $stmt = $this->pdo->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)$result['total_count'];
    }

    /**
     * Get items that are below their minimum stock level
     * @param int $threshold Optional threshold to override item's minimum quantity
     * @return array Array of low stock items with details
     */
    // public function getLowStockItems($threshold = null) {
    //     $sql = "SELECT i.id, i.item_code, i.item_name, i.quantity_on_hand,
    //                    i.minimum_quantity, l.location_name
    //             FROM inventory_items i
    //             LEFT JOIN locations l ON i.primary_location_id = l.id
    //             WHERE i.is_active = 1 AND i.quantity_on_hand <= ";

    //     $sql .= $threshold !== null ? ":threshold" : "i.minimum_quantity";
    //     $sql .= " ORDER BY i.quantity_on_hand ASC";

    //     $stmt = $this->pdo->prepare($sql);
    //     if ($threshold !== null) {
    //         $stmt->bindValue(':threshold', $threshold, PDO::PARAM_INT);
    //     }
    //     $stmt->execute();

    //     return $stmt->fetchAll(PDO::FETCH_ASSOC);
    // }

    /**
     * Calculate total value of all inventory items
     * @return float Total inventory value
     */
    public function getTotalInventoryValue() {
        $sql = "SELECT SUM(current_quantity * cost_price) as total_value
                FROM inventory_items";
        $stmt = $this->pdo->query($sql);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return (float)$result['total_value'] ?? 0.0;
    }

    /**
     * Get recent inventory movements/transactions
     * @param int $limit Number of recent movements to return
     * @return array Array of recent inventory movements
     */
    public function getRecentMovements($limit = 10) {
        $sql = "SELECT m.id, m.movement_type, m.quantity, m.date_time,
                       i.item_code, i.name,
                       u.full_name as user_name,
                       l.name,
                       m.reference_id, m.notes
                FROM inventory_movements m
                JOIN inventory_items i ON m.item_id = i.id
                LEFT JOIN users u ON m.created_by = u.id
                LEFT JOIN inventory_locations l ON m.location_id = l.id
                WHERE i.is_active = 1
                ORDER BY m.date_time DESC
                LIMIT :limit";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }



    /**
     * Get item quantities by location
     */
    public function getItemQuantitiesByLocation(int $itemId): array {
        $stmt = $this->pdo->prepare("
            SELECT l.id AS location_id, l.name AS location_name,
                   COALESCE(SUM(CASE WHEN m.movement_type IN ('purchase', 'adjustment', 'transfer_in') THEN m.quantity
                                     WHEN m.movement_type IN ('sale', 'transfer_out') THEN -m.quantity
                                     ELSE 0 END), 0) AS quantity
            FROM inventory_locations l
            LEFT JOIN inventory_movements m ON l.id = m.location_id AND m.item_id = ?
            GROUP BY l.id, l.name
            HAVING quantity > 0
        ");
        $stmt->execute([$itemId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}







