<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../classes/RequisitionSystem.php';



$requisitionId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($requisitionId <= 0) {
    redirect('/modules/inventory/requisitions.php');
}

$requisitionSystem = new RequisitionSystem($pdo);
$requisition = $requisitionSystem->getRequisitionDetails($requisitionId);
$items = $requisitionSystem->getRequisitionItems($requisitionId);
$approvals = $requisitionSystem->getApprovalStatus($requisitionId);

if (!$requisition) {
    $_SESSION['error'] = "Requisition not found";
    redirect('/modules/inventory/requisitions.php');
}

// Handle approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approval_action'])) {
    try {
        $approvalId = intval($_POST['approval_id']);
        $action = $_POST['approval_action'];
        $comments = $_POST['comments'] ?? '';

        $requisitionSystem->processApproval($approvalId, $_SESSION['user_id'], $action, $comments);

        $_SESSION['success'] = "Requisition " . ($action === 'approved' ? 'approved' : 'rejected') . " successfully!";
        redirect("/modules/inventory/view_requisition.php?id=$requisitionId");
    } catch (Exception $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
}

// Handle fulfillment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fulfill_item'])) {
    try {
        $itemId = intval($_POST['item_id']);
        $quantity = floatval($_POST['quantity']);
        $locationId = isset($_POST['location_id']) ? intval($_POST['location_id']) : null;
        $notes = $_POST['notes'] ?? '';

        $requisitionSystem->fulfillItem($itemId, $quantity, $_SESSION['user_id'], $locationId, $notes);

        $_SESSION['success'] = "Item fulfilled successfully!";
        redirect("/modules/inventory/view_requisition.php?id=$requisitionId");
    } catch (Exception $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
}
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2>Requisition #<?= htmlspecialchars($requisition['requisition_number']) ?></h2>
                <div>
                    <a href="requisition.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Requisitions
                    </a>
                    <?php if ($requisition['status'] === 'draft' && $requisition['requester_id'] == $_SESSION['user_id']): ?>
                        <a href="edit_requisition.php?id=<?= $requisitionId ?>" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                    <?php endif; ?>
                    <button class="btn btn-primary" onclick="window.print()">
                        <i class="fas fa-print"></i> Print
                    </button>
                </div>
            </div>
            <div class="text-muted">
                Status: <span class="badge bg-<?=
                    $requisition['status'] === 'approved' ? 'success' :
                    ($requisition['status'] === 'rejected' ? 'danger' :
                    ($requisition['status'] === 'draft' ? 'secondary' : 'info')) ?>">
                    <?= ucfirst($requisition['status']) ?>
                </span>
                | Created by <?= htmlspecialchars($requisition['requester_name']) ?> on <?= date('M j, Y H:i', strtotime($requisition['request_date'])) ?>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h4>Requisition Details</h4>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <h6>Department</h6>
                            <p><?= htmlspecialchars($requisition['department_name']) ?></p>
                        </div>
                        <div class="col-md-4">
                            <h6>Needed By</h6>
                            <p><?= date('M j, Y', strtotime($requisition['needed_by_date'])) ?></p>
                        </div>
                        <div class="col-md-4">
                            <h6>Purpose</h6>
                            <p><?= nl2br(htmlspecialchars($requisition['purpose'])) ?></p>
                        </div>
                    </div>

                    <h5>Requested Items</h5>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th class="text-end">Requested</th>
                                    <th class="text-end">Fulfilled</th>
                                    <th class="text-end">Pending</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td>
                                            <?= htmlspecialchars($item['item_code']) ?> - <?= htmlspecialchars($item['name']) ?>
                                            <?php if ($item['purpose']): ?>
                                                <br><small><?= htmlspecialchars($item['purpose']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end"><?= number_format($item['quantity'], 2) ?> <?= $item['unit_of_measure'] ?></td>
                                        <td class="text-end"><?= number_format($item['fulfilled_quantity'], 2) ?> <?= $item['unit_of_measure'] ?></td>
                                        <td class="text-end"><?= number_format($item['quantity'] - $item['fulfilled_quantity'], 2) ?> <?= $item['unit_of_measure'] ?></td>
                                        <td>
                                            <span class="badge bg-<?=
                                                $item['status'] === 'approved' ? 'success' :
                                                ($item['status'] === 'rejected' ? 'danger' :
                                                ($item['status'] === 'fulfilled' ? 'info' : 'warning')) ?>">
                                                <?= ucfirst($item['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($item['status'] === 'approved' && hasPermission('fulfill_requisition')): ?>
                                                <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#fulfillModal"
                                                    data-item-id="<?= $item['id'] ?>"
                                                    data-item-name="<?= htmlspecialchars($item['item_code'] . ' - ' . $item['name']) ?>"
                                                    data-max-quantity="<?= $item['quantity'] - $item['fulfilled_quantity'] ?>">
                                                    <i class="fas fa-check"></i> Fulfill
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h4>Approval Workflow s</h4>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <?php foreach ($approvals as $approval): ?>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">Level <?= $approval['approval_level'] ?></h6>
                                        <p class="mb-1"><?= htmlspecialchars($approval['approver_name']) ?></p>
                                    </div>
                                    <span class="badge bg-<?=
                                        $approval['status'] === 'approved' ? 'success' :
                                        ($approval['status'] === 'rejected' ? 'danger' : 'warning') ?>">
                                        <?= ucfirst($approval['status']) ?>
                                    </span>
                                </div>
                                <?php if ($approval['status'] !== 'pending'): ?>
                                    <div class="mt-2">
                                        <small class="text-muted">
                                            <?= date('M j, Y H:i', strtotime($approval['action_date'])) ?>
                                            <?php if ($approval['comments']): ?>
                                                <br><?= nl2br(htmlspecialchars($approval['comments'])) ?>
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                <?php elseif ($approval['approver_id'] == $_SESSION['user_id']): ?>
                                    <div class="mt-2">
                                        <form method="POST" class="row g-2">
                                            <input type="hidden" name="approval_id" value="<?= $approval['id'] ?>">
                                            <div class="col-12">
                                                <textarea class="form-control form-control-sm" name="comments" placeholder="Comments (optional)" rows="2"></textarea>
                                            </div>
                                            <div class="col-6">
                                                <button type="submit" name="approval_action" value="approved" class="btn btn-sm btn-success w-100">
                                                    <i class="fas fa-check"></i> Approve
                                                </button>
                                            </div>
                                            <div class="col-6">
                                                <button type="submit" name="approval_action" value="rejected" class="btn btn-sm btn-danger w-100">
                                                    <i class="fas fa-times"></i> Reject
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4>Requisition History</h4>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <span>Created</span>
                                <small class="text-muted"><?= date('M j, Y H:i', strtotime($requisition['request_date'])) ?></small>
                            </div>
                        </div>
                        <?php if ($requisition['status'] === 'approved' || $requisition['status'] === 'rejected'): ?>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between">
                                    <span><?= ucfirst($requisition['status']) ?></span>
                                    <small class="text-muted"><?= date('M j, Y H:i', strtotime($approvals[count($approvals)-1]['updated_at'])) ?></small>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Fulfillment Modal -->
<div class="modal fade" id="fulfillModal" tabindex="-1" aria-labelledby="fulfillModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="fulfillModalLabel">Fulfill Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="fulfill_item" value="1">
                <input type="hidden" id="modalItemId" name="item_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Item</label>
                        <input type="text" class="form-control" id="modalItemName" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Quantity to Fulfill</label>
                        <input type="number" class="form-control" name="quantity" step="0.01" min="0.01" id="modalQuantity" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">From Location (optional)</label>
                        <select class="form-select" name="location_id">
                            <option value="">Main Inventory</option>
                            <?php foreach ($locations as $loc): ?>
                                <option value="<?= $loc['id'] ?>"><?= htmlspecialchars($loc['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes (optional)</label>
                        <textarea class="form-control" name="notes" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Confirm Fulfillment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Fulfill modal setup
    $('#fulfillModal').on('show.bs.modal', function(event) {
        const button = $(event.relatedTarget);
        const itemId = button.data('item-id');
        const itemName = button.data('item-name');
        const maxQuantity = button.data('max-quantity');

        const modal = $(this);
        modal.find('#modalItemId').val(itemId);
        modal.find('#modalItemName').val(itemName);
        modal.find('#modalQuantity').attr('max', maxQuantity).val(maxQuantity);
    });
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>