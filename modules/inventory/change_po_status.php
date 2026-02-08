<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../classes/PurchaseOrder.php';

// Check permissions
if (!hasPermission('purchase_change_status')) {
    $_SESSION['flash_message'] = 'You do not have permission to change PO status';
    //redirect('/index.php');
}

$poId = filter_input(INPUT_POST, 'po_id', FILTER_VALIDATE_INT);
$currentStatus = filter_input(INPUT_POST, 'current_status', FILTER_VALIDATE_INT);
$newStatus = filter_input(INPUT_POST, 'new_status', FILTER_VALIDATE_INT);
$notes = filter_input(INPUT_POST, 'notes', FILTER_SANITIZE_STRING);
$approverId = filter_input(INPUT_POST, 'approver_id', FILTER_VALIDATE_INT);

if (!$poId || !$newStatus || $newStatus === $currentStatus) {
    $_SESSION['flash_message'] = 'Invalid status change request';
    redirect('list_po.php');
}

$poSystem = new PurchaseOrder($pdo);

// Check if status transition is allowed
$allowedTransitions = [
    1 => [2, 7], // Draft -> Pending Approval or Cancelled
    2 => [3, 7], // Pending Approval -> Approved or Cancelled
    3 => [4, 7], // Approved -> Ordered or Cancelled
    4 => [5],    // Ordered -> Pending Receipt
    5 => [6, 7], // Pending Receipt -> Completed or Cancelled
    6 => [],     // Completed - no further changes
    7 => []      // Cancelled - no further changes
];

if (!in_array($newStatus, $allowedTransitions[$currentStatus] ?? [])) {
    $_SESSION['flash_message'] = 'This status transition is not allowed';
    redirect("view_po.php?id=$poId");
}

// Check if approval is required for the new status
$statusRequiresApproval = $pdo->query("
    SELECT requires_approval
    FROM purchase_order_status
    WHERE id = $newStatus
")->fetchColumn();

// if ($statusRequiresApproval && !$approverId) {
//     $_SESSION['flash_message'] = 'Approver is required for this status';
//     redirect("view_po.php?id=$poId");
// }

try {
    // Update status
    $success = $poSystem->updateStatus(
        $poId,
        $newStatus,
        $_SESSION['user_id'],
        $notes
    );

    if ($success) {
        // If status requires approval, create approval record
        if ($statusRequiresApproval && $approverId) {
            $stmt = $pdo->prepare("
                INSERT INTO purchase_order_approvals (
                    po_id,
                    requested_by,
                    approver_id,
                    status_id,
                    requested_at
                ) VALUES (
                    :po_id,
                    :requested_by,
                    :approver_id,
                    :status_id,
                    NOW()
                )
            ");

            $stmt->execute([
                ':po_id' => $poId,
                ':requested_by' => $_SESSION['user_id'],
                ':approver_id' => $approverId,
                ':status_id' => $newStatus
            ]);

            // Send notification to approver
            $poNumber = $pdo->query("SELECT po_number FROM purchase_orders WHERE id = $poId")->fetchColumn();
            $message = "You have a new PO #$poNumber to approve. Status change requested to " .
                      $pdo->query("SELECT name FROM purchase_order_status WHERE id = $newStatus")->fetchColumn();

            $pdo->prepare("
                INSERT INTO notifications (
                    user_id,
                    message,
                    link,
                    created_at
                ) VALUES (
                    :user_id,
                    :message,
                    :link,
                    NOW()
                )
            ")->execute([
                ':user_id' => $approverId,
                ':message' => $message,
                ':link' => "view_po.php?id=$poId"
            ]);
        }

        $_SESSION['flash_message'] = 'PO status updated successfully';
    } else {
        $_SESSION['flash_message'] = 'Failed to update PO status';
    }
} catch (Exception $e) {
    $_SESSION['flash_message'] = 'Error: ' . $e->getMessage();
}

redirect("view_po.php?id=$poId");