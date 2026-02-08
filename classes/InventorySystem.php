<?php
class InventorySystem
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // 1. Add new stock item
    public function addItem($itemData)
    {
        $stmt = $this->pdo->prepare("INSERT INTO stock_items (item_name, description,itemtype, unit) VALUES (?, ?, ?,?)");
        $stmt->execute([
            $itemData['item_name'],
            $itemData['description'],
              $itemData['itemtype'],
            $itemData['unit']
        ]);
    }

    // 2. Update existing stock item
    public function updateItem($itemId, $itemData)
    {
        $stmt = $this->pdo->prepare("UPDATE stock_items SET item_name = ?, description = ?,itemtype= ?, unit = ? WHERE id = ?");
        $stmt->execute([
            $itemData['item_name'],
            $itemData['description'],
            $itemData['itemtype'],
            $itemData['unit'],
            $itemId
        ]);
    }

    // 3. Get a single stock item
    public function getItem($itemId)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM stock_items WHERE id = ?");
        $stmt->execute([$itemId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function deleteItem($itemId)
{
    $stmt = $this->pdo->prepare("DELETE FROM stock_items WHERE id = ?");
    $stmt->execute([$itemId]);
}

    // 4. List all stock items
    public function listItems()
    {
        $stmt = $this->pdo->query("SELECT * FROM stock_items ORDER BY item_name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

  public function createRequisition($requisitionData, $items) {
        try {
            $this->pdo->beginTransaction();

            // Insert into requisitions table
            $stmt = $this->pdo->prepare("INSERT INTO requisitions (purpose, status, created_at) VALUES (?, ?, NOW())");
            $stmt->execute([
                $requisitionData['purpose'],
                $requisitionData['status'],
            ]);

            $requisitionId = $this->pdo->lastInsertId();

            // Insert into requisition_items
            foreach ($items['item_id'] as $index => $itemId) {
                $quantity = $items['quantity'][$index];
                $this->pdo->prepare("INSERT INTO requisition_items (requisition_id, item_id, quantity) VALUES (?, ?, ?)")
                    ->execute([$requisitionId, $itemId, $quantity]);
            }

            $this->pdo->commit();
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    // Update an existing requisition
    public function updateRequisition($requisitionId, $requisitionData, $items) {
        try {
            $this->pdo->beginTransaction();

            // Update requisitions table
            $stmt = $this->pdo->prepare("UPDATE requisitions SET purpose = ?, status = ? WHERE id = ?");
            $stmt->execute([
                $requisitionData['purpose'],
                $requisitionData['status'],
                $requisitionId,
            ]);

            // Delete old requisition items
            $this->pdo->prepare("DELETE FROM requisition_items WHERE requisition_id = ?")
                ->execute([$requisitionId]);

            // Insert new requisition items
            foreach ($items['item_id'] as $index => $itemId) {
                $quantity = $items['quantity'][$index];
                $this->pdo->prepare("INSERT INTO requisition_items (requisition_id, item_id, quantity) VALUES (?, ?, ?)")
                    ->execute([$requisitionId, $itemId, $quantity]);
            }

            $this->pdo->commit();
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }





    // Get all requisitions with their items
    public function listRequisitions() {
        $stmt = $this->pdo->query("SELECT * FROM requisitions ORDER BY created_at DESC");
        $requisitions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($requisitions as &$requisition) {
            $requisition['items'] = $this->getRequisitionItems($requisition['id']);
        }

        return $requisitions;
    }

    // Get a single requisition
    public function getRequisition($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM requisitions WHERE id = ?");
        $stmt->execute([$id]);
        $requisition = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($requisition) {
            $requisition['items'] = $this->getRequisitionItems($id);
        }

        return $requisition;
    }

    // Get items of a specific requisition
    private function getRequisitionItems($requisitionId) {
        $stmt = $this->pdo->prepare("
            SELECT ri.*, i.item_name
            FROM requisition_items ri
            JOIN stock_items i ON ri.item_id = i.id
            WHERE ri.requisition_id = ?
        ");
        $stmt->execute([$requisitionId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

public function getRequisitionById($id)
{
    $stmt = $this->pdo->prepare("SELECT * FROM requisitions WHERE id = ?");
    $stmt->execute([$id]);
    $requisition = $stmt->fetch(PDO::FETCH_ASSOC);

    $itemsStmt = $this->pdo->prepare("
        SELECT ri.*, si.item_name
        FROM requisition_items ri
        JOIN stock_items si ON ri.item_id = si.id
        WHERE ri.requisition_id = ?
    ");
    $itemsStmt->execute([$id]);
    $requisition['items'] = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);

    return $requisition;
}
public function deleteRequisition($id)
{
    $this->pdo->prepare("DELETE FROM requisition_items WHERE requisition_id = ?")->execute([$id]);
    $this->pdo->prepare("DELETE FROM requisitions WHERE id = ? AND status = 'draft'")->execute([$id]);
}

public function submitRequisition($id)
{
    $stmt = $this->pdo->prepare("UPDATE requisitions SET status = 'submitted' WHERE id = ? AND status = 'draft'");
    $stmt->execute([$id]);
}



public function updateRequisitions($id, $headerData, $items)
{
    $this->pdo->beginTransaction();
    try {
        $stmt = $this->pdo->prepare("
            UPDATE requisitions SET purpose = ? WHERE id = ? AND status = 'draft'
        ");
        $stmt->execute([
            $headerData['purpose'],
            $id
        ]);

        $this->pdo->prepare("DELETE FROM requisition_items WHERE requisition_id = ?")
            ->execute([$id]);

        $itemStmt = $this->pdo->prepare("
            INSERT INTO requisition_items (requisition_id, item_id, quantity)
            VALUES (?, ?, ?)
        ");
        foreach ($items as $item) {
            $itemStmt->execute([
                $id,
                $item['item_id'],
                $item['quantity']
            ]);
        }

        $this->pdo->commit();
    } catch (Exception $e) {
        $this->pdo->rollBack();
        throw $e;
    }
}

public function getStockReport($fromDate, $toDate)
{
    $query = "
        SELECT
            stock_items.id,
            stock_items.item_name,
            IFNULL(opening.opening_stock, 0) AS opening_stock,
            IFNULL(movement_in.stock_in, 0) AS stock_in,
            IFNULL(movement_out.stock_out, 0) AS stock_out,
            (IFNULL(opening.opening_stock, 0) + IFNULL(movement_in.stock_in, 0) - IFNULL(movement_out.stock_out, 0)) AS closing_stock
        FROM stock_items
        LEFT JOIN (
            SELECT
                item_id,
                SUM(quantity_in) - SUM(quantity_out) AS opening_stock
            FROM stock_movements
            WHERE date < :from_date
            GROUP BY item_id
        ) AS opening ON opening.item_id = stock_items.id
        LEFT JOIN (
            SELECT
                item_id,
                SUM(quantity_in) AS stock_in
            FROM stock_movements
            WHERE date BETWEEN :from_date AND :to_date
            GROUP BY item_id
        ) AS movement_in ON movement_in.item_id = stock_items.id
        LEFT JOIN (
            SELECT
                item_id,
                SUM(quantity_out) AS stock_out
            FROM stock_movements
            WHERE date BETWEEN :from_date AND :to_date
            GROUP BY item_id
        ) AS movement_out ON movement_out.item_id = stock_items.id
        WHERE stock_items.itemtype='inventory' ORDER BY stock_items.item_name
    ";

    $stmt = $this->pdo->prepare($query);
    $stmt->execute([
        ':from_date' => $fromDate,
        ':to_date' => $toDate,
    ]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}







    // 7. Approve Requisition and Stock Out
    public function approveRequisition($requisitionId)
    {
        $this->pdo->beginTransaction();
        try {
            // Get all items
            $stmt = $this->pdo->prepare("SELECT * FROM requisition_items WHERE requisition_id = ?");
            $stmt->execute([$requisitionId]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Insert into stock_movements
            $movementStmt = $this->pdo->prepare("INSERT INTO stock_movements (item_id, date, quantity_out, reference_type, reference_id, remarks) VALUES (?, NOW(), ?, 'requisition', ?, 'Requisition Approved')");
            foreach ($items as $item) {
                $movementStmt->execute([
                    $item['item_id'],
                    $item['quantity'],
                    $requisitionId
                ]);
            }

            // Update status
            $updateStmt = $this->pdo->prepare("UPDATE requisitions SET status = 'approved' WHERE id = ?");
            $updateStmt->execute([$requisitionId]);

            $this->pdo->commit();
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    // 8. Create Purchase Order
    public function createPurchaseOrder($poData, $items)
    {
        $this->pdo->beginTransaction();
        try {
            // Insert the purchase order
            $stmt = $this->pdo->prepare("INSERT INTO purchase_orders (supplier_id, order_date, created_by, purpose, status,contract_id) VALUES (?, ?, ?, ?, 'draft',?)");
            $stmt->execute([
                $poData['supplier_id'],
                $poData['order_date'],
                $poData['created_by'],
                $poData['purpose'],
                $poData['contract_id']
            ]);
            $poId = $this->pdo->lastInsertId();

            // Insert each item in the purchase order
            $itemStmt = $this->pdo->prepare("INSERT INTO purchase_order_items (purchase_order_id, item_id, quantity,price) VALUES (?, ?, ?,?)");
            foreach ($items as $item) {
                $itemStmt->execute([
                    $poId,
                    $item['item_id'],
                    $item['quantity'],
                    $item['price']
                ]);
            }

            $this->pdo->commit();
            return $poId;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }


    // 9. Submit Purchase Order
    public function submitPurchaseOrder($poId)
    {
        $stmt = $this->pdo->prepare("UPDATE purchase_orders SET status = 'submitted' WHERE id = ?");
        $stmt->execute([$poId]);
    }

    // 10. Approve Purchase Order
    public function approvePurchaseOrder($poId)
    {
        $stmt = $this->pdo->prepare("UPDATE purchase_orders SET status = 'approved' WHERE id = ?");
        $stmt->execute([$poId]);
    }

    // 11. Receive Purchase Order (Stock In)
    public function receivePurchaseOrder($poId)
    {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM purchase_order_items WHERE purchase_order_id = ?");
            $stmt->execute([$poId]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $movementStmt = $this->pdo->prepare("INSERT INTO stock_movements (item_id, date, quantity_in, reference_type, reference_id, remarks) VALUES (?, NOW(), ?, 'purchase_order', ?, 'PO Received')");
            foreach ($items as $item) {
                $movementStmt->execute([
                    $item['item_id'],
                    $item['quantity'],
                    $poId
                ]);
            }

            $updateStmt = $this->pdo->prepare("UPDATE purchase_orders SET status = 'received' WHERE id = ?");
            $updateStmt->execute([$poId]);

            $this->pdo->commit();
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    // 12. Stock Movement Summary (opening, in, out, closing)
    public function getStockMovementSummary($startDate, $endDate)
    {
        $stmt = $this->pdo->prepare("
            SELECT
                si.id AS item_id,
                si.item_name,
                COALESCE(SUM(CASE WHEN sm.date < ? THEN sm.quantity_in - sm.quantity_out ELSE 0 END),0) AS opening,
                COALESCE(SUM(CASE WHEN sm.date BETWEEN ? AND ? THEN sm.quantity_in ELSE 0 END),0) AS stock_in,
                COALESCE(SUM(CASE WHEN sm.date BETWEEN ? AND ? THEN sm.quantity_out ELSE 0 END),0) AS stock_out
            FROM stock_items si
            LEFT JOIN stock_movements sm ON sm.item_id = si.id
            GROUP BY si.id, si.item_name
            ORDER BY si.item_name
        ");
        $stmt->execute([$startDate, $startDate, $endDate, $startDate, $endDate]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function receiveItem($purchaseOrderId, $itemId, $quantity) {
        // 1. Update stock for the item
        $stmt = $this->pdo->prepare("UPDATE stock_items SET stock_quantity = stock_quantity + ? WHERE id = ?");
        $stmt->execute([$quantity, $itemId]);

        // 2. Record the stock movement
        $stmt = $this->pdo->prepare("INSERT INTO stock_movements (item_id, movement_type, quantity, movement_date, remarks)
                                      VALUES (?, 'in', ?, NOW(), 'Received from purchase order #?')");
        $stmt->execute([$itemId, $quantity, $purchaseOrderId]);
    }


    // Fetch pending purchase orders (those that have not been fully received)
    public function listSuppliers() {
        $stmt = $this->pdo->query("SELECT * FROM suppliers");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listPurchaseOrders() {
        $stmt = $this->pdo->query("SELECT * FROM purchase_orders inner join suppliers on purchase_orders.supplier_id = suppliers.id");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPendingPurchaseOrders() {
        $stmt = $this->pdo->query("SELECT * FROM purchase_orders WHERE status = 'approved'");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPurchaseOrders(): array
{
    $stmt = $this->pdo->prepare("SELECT * FROM purchase_orders ORDER BY order_date DESC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
public function getSupplierById(int $supplierId): array
{
    $stmt = $this->pdo->prepare("SELECT * FROM suppliers WHERE id = ?");
    $stmt->execute([$supplierId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}


    // Get items for a specific purchase order


    public function getPurchaseOrderByIds(int $purchaseOrderId): ?array
{
    $stmt = $this->pdo->prepare("SELECT * FROM purchase_orders WHERE id = ?");
    $stmt->execute([$purchaseOrderId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    return $order ?: null;
}

public function updatePurchaseOrder(int $poId, array $poData, array $items): void
{
    $this->pdo->beginTransaction();
    try {
        // Update purchase order header
        $stmt = $this->pdo->prepare("UPDATE purchase_orders SET supplier_id = ?, order_date = ?, purpose = ? WHERE id = ?");
        $stmt->execute([
            $poData['supplier_id'],
            $poData['order_date'],
            $poData['purpose'],
            $poId
        ]);

        // First, delete all old items
        $this->pdo->prepare("DELETE FROM purchase_order_items WHERE purchase_order_id = ?")->execute([$poId]);

        // Insert new items
        $itemStmt = $this->pdo->prepare("INSERT INTO purchase_order_items (purchase_order_id, item_id, quantity) VALUES (?, ?, ?)");
        foreach ($items as $item) {
            $itemStmt->execute([
                $poId,
                $item['item_id'],
                $item['quantity']
            ]);
        }

        $this->pdo->commit();
    } catch (Exception $e) {
        $this->pdo->rollBack();
        throw $e;
    }
}

public function deletePurchaseOrder(int $purchaseOrderId): bool
{
    try {
        $this->pdo->beginTransaction();

        // First delete purchase order items
        $stmtItems = $this->pdo->prepare("DELETE FROM purchase_order_items WHERE purchase_order_id = ?");
        $stmtItems->execute([$purchaseOrderId]);

        // Then delete purchase order itself
        $stmtOrder = $this->pdo->prepare("DELETE FROM purchase_orders WHERE id = ? AND status = 'draft'");
        $stmtOrder->execute([$purchaseOrderId]);

        if ($stmtOrder->rowCount() === 0) {
            // Means order wasn't found or not in 'draft' status
            $this->pdo->rollBack();
            return false;
        }

        $this->pdo->commit();
        return true;
    } catch (Exception $e) {
        $this->pdo->rollBack();
        throw $e;
    }
}


public function listStockItems(): array
{
    $stmt = $this->pdo->query("
        SELECT id, item_name, description, unit
        FROM stock_items
        ORDER BY item_name ASC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


// Get a single Purchase Order
public function getPurchaseOrderById($poId)
{
    $stmt = $this->pdo->prepare("
        SELECT po.*, s.name AS supplier_name,
        s.account_number AS account_number FROM purchase_orders po
        JOIN suppliers s ON s.id = po.supplier_id
        WHERE po.id = ?
    ");
    $stmt->execute([$poId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Get Purchase Order Items
public function getPurchaseOrderItems($poId)
{
    $stmt = $this->pdo->prepare("
        SELECT poi.*, si.item_name,si.itemtype
        FROM purchase_order_items poi
        JOIN stock_items si ON si.id = poi.item_id
        WHERE poi.purchase_order_id = ?
    ");
    $stmt->execute([$poId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Record a stock movement (for received items)
public function recordStockMovement($data)
{
    $stmt = $this->pdo->prepare("
        INSERT INTO stock_movements (item_id, date, quantity_in, quantity_out, reference_type, reference_id, remarks, created_at)
        VALUES (:item_id, :date, :quantity_in, :quantity_out, :reference_type, :reference_id, :remarks, NOW())
    ");
    $stmt->execute($data);
}

// Mark PO as received (and save delivery note)
public function markPurchaseOrderAsReceived($poId, $deliveryNote = null)
{
    $stmt = $this->pdo->prepare("
        UPDATE purchase_orders
        SET status = 'received', delivery_note = :delivery_note
        WHERE id = :po_id
    ");
    $stmt->execute([
        ':delivery_note' => $deliveryNote,
        ':po_id' => $poId
    ]);
}



    // Get items for a specific purchase order
public function getPurchaseOrderItemss(int $purchaseOrderId): array
{
    $stmt = $this->pdo->prepare("SELECT * FROM purchase_order_items WHERE purchase_order_id = ?");
    $stmt->execute([$purchaseOrderId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get stock item by ID
public function getStockItemById(int $itemId): array
{
    $stmt = $this->pdo->prepare("SELECT * FROM stock_items WHERE id = ?");
    $stmt->execute([$itemId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}







}
?>
