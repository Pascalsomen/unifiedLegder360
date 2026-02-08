<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../classes/PurchaseOrder.php';

// Check permissions
if (!hasPermission('purchase_view')) {
    redirect('/index.php');
}

$receiptId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$receiptId) {
    redirect('purchase_orders.php');
}

$poSystem = new PurchaseOrder($pdo);
$receipt = $poSystem->getReceiptDetails($receiptId);

// Check if receipt exists
if (!$receipt) {
    $_SESSION['flash_message'] = 'Receipt not found';
    redirect('purchase_orders.php');
}
?>

<div class="container-fluid">
    <!-- Flash Message -->
    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
            <?= $_SESSION['flash_message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['flash_message']); ?>
    <?php endif; ?>

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2>Receipt #<?= $receiptId ?> for PO #<?= htmlspecialchars($receipt['po_number']) ?></h2>

                <div class="btn-group">
                    <a href="view_po.php?id=<?= $receipt['po_id'] ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back to PO
                    </a>
                    <button class="btn btn-outline-secondary" onclick="window.print()">
                        <i class="fas fa-print"></i> Print
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Receipt Details</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Received By</label>
                        <p><?= htmlspecialchars($receipt['received_by_name']) ?></p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Receipt Date</label>
                        <p><?= date('m/d/Y H:i', strtotime($receipt['receipt_date'])) ?></p>
                    </div>

                    <?php if ($receipt['notes']): ?>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Notes</label>
                            <p><?= nl2br(htmlspecialchars($receipt['notes'])) ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Receipt Summary</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Items Received</label>
                        <p><?= count($receipt['items']) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Received Items</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Item Code</th>
                            <th>Description</th>
                            <th class="text-end">Quantity</th>
                            <th>Location</th>
                            <th>Batch/Serial</th>
                            <th>Expiry</th>
                            <th>Condition</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($receipt['items'] as $index => $item): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= htmlspecialchars($item['item_code'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($item['description']) ?></td>
                                <td class="text-end"><?= number_format($item['quantity_received'], 2) ?></td>
                                <td><?= htmlspecialchars($item['location_name'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($item['batch_number'] ?? 'N/A') ?></td>
                                <td><?= $item['expiry_date'] ? date('m/d/Y', strtotime($item['expiry_date'])) : 'N/A' ?></td>
                                <td><?= htmlspecialchars($item['condition_notes'] ?? 'Good') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>