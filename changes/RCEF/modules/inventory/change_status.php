<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../classes/PurchaseOrder.php';


// Check permissions
if (!hasPermission('purchase_view')) {
    redirect('/index.php');
}

// Get PO ID from URL
$poId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$poId) {
    redirect('purchase_orders.php');
}

$poSystem = new PurchaseOrder($pdo);
$purchaseOrder = $poSystem->getById($poId);

// Check if PO exists
if (!$purchaseOrder) {
    $_SESSION['flash_message'] = 'Purchase order not found';
    redirect('purchase_orders.php');
}

// Check if user has permission to view this PO
if (!hasPermission('purchase_view_all') && $purchaseOrder['created_by'] != $_SESSION['user_id']) {
    $_SESSION['flash_message'] = 'You do not have permission to view this purchase order';
    redirect('purchase_orders.php');
}

// Get status history
$statusHistory = $poSystem->getStatusHistory($poId);
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
                <h2>Purchase Order #<?= htmlspecialchars($purchaseOrder['po_number']) ?></h2>

asasdsad

            <form method="post" action="change_po_status.php">
                <input type="hidden" name="po_id" value="<?= $poId ?>">
                <input type="hidden" name="current_status" value="<?= $purchaseOrder['status_id'] ?>">

                <div class="modal-header">
                    <h5 class="modal-title" id="statusChangeModalLabel">Change PO Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label for="new_status" class="form-label">New Status</label>
                        <select class="form-select" id="new_status" name="new_status" required>
                            <option value="">Select Status</option>
                            <?php foreach ($statuses as $status): ?>
                                <?php if ($status['id'] != $purchaseOrder['status_id']): ?>
                                    <option value="<?= $status['id'] ?>"
                                        data-requires-approval="<?= $status['requires_approval'] ?? 0 ?>">
                                        <?= htmlspecialchars($status['name']) ?>
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3" id="approvalFields" style="display: none;">
                        <label for="approver_id" class="form-label">Approver</label>
                        <select class="form-select" id="approver_id" name="approver_id">
                            <option value="">Select Approver</option>
                            <?php
                            $approvers = $pdo->query("
                                SELECT u.id, u.name
                                FROM users u
                                JOIN user_permissions up ON u.id = up.user_id
                                WHERE up.permission = 'purchase_approve'
                            ")->fetchAll();
                            foreach ($approvers as $approver): ?>
                                <option value="<?= $approver['id'] ?>">
                                    <?= htmlspecialchars($approver['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="status_notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="status_notes" name="notes" rows="3"></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Status</button>
                </div>
            </form>

<script>
// Show/hide approver field based on status selection
document.getElementById('new_status').addEventListener('change', function() {
    const requiresApproval = this.options[this.selectedIndex].dataset.requiresApproval === '1';
    document.getElementById('approvalFields').style.display = requiresApproval ? 'block' : 'none';
    if (requiresApproval) {
        document.getElementById('approver_id').setAttribute('required', 'required');
    } else {
        document.getElementById('approver_id').removeAttribute('required');
    }
});
</script>








                <div class="btn-group">
                    <a href="purchase_orders.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back to List
                    </a>

                    <?php if(
    $purchaseOrder['status_id'] == 1 &&
    (hasPermission('purchase_edit') ||
     ($purchaseOrder['created_by'] == $_SESSION['user_id'] && hasPermission('purchase_edit_own')))
): ?>
                        <a href="edit_po.php?id=<?= $poId ?>" class="btn btn-outline-primary">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                    <?php endif; ?>

                    <button class="btn btn-outline-secondary" onclick="window.print()">
                        <i class="fas fa-print"></i> Print
                    </button>
                </div>
            </div>

            <nav aria-label="breadcrumb" class="mt-3">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/index.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="purchase_orders.php">Purchase Orders</a></li>
                    <li class="breadcrumb-item active" aria-current="page">PO #<?= htmlspecialchars($purchaseOrder['po_number']) ?></li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Order Details</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Supplier</label>
                                <p><?= htmlspecialchars($purchaseOrder['supplier_name']) ?></p>
<!--
                                <?php if ($purchaseOrder['supplier_contact_person']): ?>
                                    <label class="form-label fw-bold">Contact Person</label>
                                    <p><?= htmlspecialchars($purchaseOrder['supplier_contact_person']) ?></p>
                                <?php endif; ?> -->

                                <?php if ($purchaseOrder['supplier_phone']): ?>
                                    <label class="form-label fw-bold">Phone</label>
                                    <p><?= htmlspecialchars($purchaseOrder['supplier_phone']) ?></p>
                                <?php endif; ?>

                                <?php if ($purchaseOrder['supplier_email']): ?>
                                    <label class="form-label fw-bold">Email</label>
                                    <p><?= htmlspecialchars($purchaseOrder['supplier_email']) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">PO Number</label>
                                <p><?= htmlspecialchars($purchaseOrder['po_number']) ?></p>

                                <label class="form-label fw-bold">Order Date</label>
                                <p><?= date('m/d/Y', strtotime($purchaseOrder['order_date'])) ?></p>

                                <?php if ($purchaseOrder['expected_delivery_date']): ?>
                                    <label class="form-label fw-bold">Expected Delivery</label>
                                    <p><?= date('m/d/Y', strtotime($purchaseOrder['expected_delivery_date'])) ?></p>
                                <?php endif; ?>

                                <label class="form-label fw-bold">Status</label>
                                <p>
                                    <span class="badge
                                        <?= $purchaseOrder['status_id'] == 1 ? 'bg-secondary' : '' ?>
                                        <?= $purchaseOrder['status_id'] == 2 ? 'bg-info' : '' ?>
                                        <?= $purchaseOrder['status_id'] == 3 ? 'bg-success' : '' ?>
                                        <?= $purchaseOrder['status_id'] == 4 ? 'bg-primary' : '' ?>
                                        <?= $purchaseOrder['status_id'] == 5 ? 'bg-warning' : '' ?>
                                        <?= $purchaseOrder['status_id'] == 6 ? 'bg-success' : '' ?>
                                        <?= $purchaseOrder['status_id'] == 7 ? 'bg-danger' : '' ?>">
                                        <?= htmlspecialchars($purchaseOrder['status_name']) ?>
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <?php if ($purchaseOrder['notes']): ?>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Notes</label>
                            <p><?= nl2br(htmlspecialchars($purchaseOrder['notes'])) ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if ($purchaseOrder['terms']): ?>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Terms & Conditions</label>
                            <p><?= nl2br(htmlspecialchars($purchaseOrder['terms'])) ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Order Items</h5>
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
                                    <th class="text-end">Unit Price</th>
                                    <th class="text-end">Tax</th>
                                    <th class="text-end">Line Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($purchaseOrder['items'] as $index => $item): ?>
                                    <tr>
                                        <td><?= $index + 1 ?></td>
                                        <td><?= htmlspecialchars($item['item_code'] ?? 'N/A') ?></td>
                                        <td>
                                            <?= htmlspecialchars($item['description']) ?>
                                            <?php if (!empty($item['item_description'])): ?>
                                                <br><small class="text-muted"><?= htmlspecialchars($item['item_description']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end"><?= number_format($item['quantity'], 2) ?></td>
                                        <td class="text-end"><?= number_format($item['unit_price'], 2) ?></td>
                                        <td class="text-end">
                                            <?php if ($item['tax_rate_name']): ?>
                                                <?= htmlspecialchars($item['tax_rate_name']) ?> (<?= number_format($item['tax_rate'], 2) ?>%)
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end"><?= number_format($item['line_total'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="6" class="text-end fw-bold">Subtotal:</td>
                                    <td class="text-end fw-bold"><?= number_format($purchaseOrder['total_amount'], 2) ?></td>
                                </tr>
                                <?php
                                // Calculate tax total if applicable
                                $taxTotal = 0;
                                foreach ($purchaseOrder['items'] as $item) {
                                    if ($item['tax_rate'] > 0) {
                                        $taxTotal += $item['line_total'] * ($item['tax_rate'] / 100);
                                    }
                                }

                                if ($taxTotal > 0): ?>
                                    <tr>
                                        <td colspan="6" class="text-end fw-bold">Tax:</td>
                                        <td class="text-end fw-bold"><?= number_format($taxTotal, 2) ?></td>
                                    </tr>
                                    <tr>
                                        <td colspan="6" class="text-end fw-bold">Grand Total:</td>
                                        <td class="text-end fw-bold"><?= number_format($purchaseOrder['total_amount'] + $taxTotal, 2) ?></td>
                                    </tr>
                                <?php endif; ?>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Order Summary</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Created By</label>
                        <p><?= htmlspecialchars($purchaseOrder['created_by_name']) ?></p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Created At</label>
                        <p><?= date('m/d/Y H:i', strtotime($purchaseOrder['created_at'])) ?></p>
                    </div>

                    <?php if ($purchaseOrder['updated_at']): ?>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Last Updated</label>
                            <p><?= date('m/d/Y H:i', strtotime($purchaseOrder['updated_at'])) ?></p>
                        </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Total Items</label>
                        <p><?= count($purchaseOrder['items']) ?></p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Order Total</label>
                        <p class="h5">$<?= number_format($purchaseOrder['total_amount'] + $taxTotal, 2) ?></p>
                    </div>
                </div>
            </div>

            <?php if (!empty($statusHistory)): ?>
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Status History</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            <?php foreach ($statusHistory as $history): ?>
                                <li class="list-group-item">
                                    <div class="d-flex justify-content-between">
                                        <span class="fw-bold"><?= htmlspecialchars($history['status_name']) ?></span>
                                        <small class="text-muted"><?= date('m/d/Y H:i', strtotime($history['changed_at'])) ?></small>
                                    </div>
                                    <div>
                                        <small>Changed by: <?= htmlspecialchars($history['changed_by_name']) ?></small>
                                    </div>
                                    <?php if ($history['notes']): ?>
                                        <div class="mt-1">
                                            <small class="text-muted"><?= htmlspecialchars($history['notes']) ?></small>
                                        </div>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Print Styles -->
<style media="print">
    @page {
        size: A4;
        margin: 1cm;
    }

    body {
        font-size: 12pt;
        background: #fff;
        color: #000;
    }

    .container-fluid {
        width: 100%;
        padding: 0;
    }

    .card {
        border: none;
        box-shadow: none;
    }

    .card-header {
        background: #fff !important;
        color: #000 !important;
        border-bottom: 2px solid #000;
    }

    .btn, .breadcrumb, .alert {
        display: none !important;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    table, th, td {
        border: 1px solid #ddd;
    }

    th, td {
        padding: 8px;
        text-align: left;
    }

    .text-end {
        text-align: right;
    }
</style>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>