<?php
require_once __DIR__ . '/../../config/database.php';


$contractId = $_GET['contract_id'] ?? null;

if (!$contractId) {
    echo json_encode(['error' => 'Missing contract_id']);
    exit;
}

// Fetch suppliers related to this contract
$stmtSuppliers = $pdo->prepare("
    SELECT s.id, s.name
    FROM suppliers s
    INNER JOIN contracts cs ON cs.supplier_id = s.id
    WHERE cs.contract_id = ?
");
$stmtSuppliers->execute([$contractId]);
$suppliers = $stmtSuppliers->fetchAll(PDO::FETCH_ASSOC);

// Fetch items related to this contract
$stmtItems = $pdo->prepare("
    SELECT ci.id as contract_item_id, si.item_name, si.description, si.unit, ci.unit_price, ci.quantity
    FROM contract_items ci
    INNER JOIN stock_items si ON si.id = ci.item_id
    WHERE ci.contract_id = ?
");
$stmtItems->execute([$contractId]);
$items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'suppliers' => $suppliers,
    'items' => $items
]);
