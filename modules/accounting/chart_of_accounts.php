<?php
require_once __DIR__ . '/../../includes/header.php';

if (!hasRole('accountant')) {
    redirect($base);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_account'])) {
        $accountCode = $_POST['account_code'];
        $accountName = $_POST['account_name'];
        $accountType = $_POST['account_type'];
        $parentAccount = $_POST['parent_account'] ?? null;

        $stmt = $pdo->prepare("INSERT INTO chart_of_accounts
                              (account_code, account_name, account_type, parent_id)
                              VALUES (?, ?, ?, ?)");
        $stmt->execute([$accountCode, $accountName, $accountType, $parentAccount]);

        $_SESSION['toast'] = "Account added successfully!";
        redirect($base_url.'/accounting/chart_of_accounts.php');
    }

    // Handle delete
    if (isset($_POST['delete_account'])) {
        $id = intval($_POST['id']);

        // Check if account has children
        $check = $pdo->prepare("SELECT COUNT(*) FROM chart_of_accounts WHERE parent_id = ?");
        $check->execute([$id]);
        $hasChildren = $check->fetchColumn();

        if ($hasChildren > 0) {
            $_SESSION['error'] = "Cannot delete account that has children. Delete child accounts first.";
        } else {
            $stmt = $pdo->prepare("DELETE FROM chart_of_accounts WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['toast'] = "Account deleted successfully!";
        }
        redirect($base_url.'/accounting/chart_of_accounts.php');
    }
}

// Fetch all accounts
$accounts = $pdo->query("SELECT * FROM chart_of_accounts WHERE status = TRUE ORDER BY account_code")->fetchAll();

// Build hierarchical structure for navigation
function buildNavigationTree($accounts, $parent_id = null) {
    $tree = [];
    foreach ($accounts as $account) {
        if ($account['parent_id'] == $parent_id) {
            // Check if this account has children
            $has_children = false;
            foreach ($accounts as $child) {
                if ($child['parent_id'] == $account['id']) {
                    $has_children = true;
                    break;
                }
            }

            // Only include in navigation if it has children OR is top-level
            if ($has_children || $parent_id === null) {
                $account['has_children'] = $has_children;
                if ($has_children) {
                    $account['children'] = buildNavigationTree($accounts, $account['id']);
                }
                $tree[] = $account;
            }
        }
    }
    return $tree;
}

$navigation_tree = buildNavigationTree($accounts);

// GET SELECTED DATA
$selected_id = isset($_GET['selected_id']) ? intval($_GET['selected_id']) : null;
$selected_node = null;
$items_to_display = [];
$ancestor_ids = [];

if ($selected_id) {
    // Find selected node
    foreach ($accounts as $account) {
        if ($account['id'] == $selected_id) {
            $selected_node = $account;
            break;
        }
    }

    if ($selected_node) {
        // Get direct children of the selected node
        foreach ($accounts as $account) {
            if ($account['parent_id'] == $selected_id) {
                $items_to_display[] = $account;
            }
        }

        // Trace ancestors
        $current = $selected_node;
        while ($current) {
            $ancestor_ids[] = $current['id'];
            $parent_id = $current['parent_id'];
            $current = null;
            if ($parent_id) {
                foreach ($accounts as $account) {
                    if ($account['id'] == $parent_id) {
                        $current = $account;
                        break;
                    }
                }
            }
        }
    }
}

// If no selection, get first parent from navigation
if (!$selected_id && !empty($navigation_tree)) {
    $selected_id = $navigation_tree[0]['id'];
    $selected_node = $navigation_tree[0];

    // Get direct children of the selected node
    foreach ($accounts as $account) {
        if ($account['parent_id'] == $selected_id) {
            $items_to_display[] = $account;
        }
    }

    $ancestor_ids = [$selected_id];
}

// RENDER NAVIGATION TREE FUNCTION
function render_nav_tree($nodes, $selected_id, $ancestor_ids) {
    echo '<ul class="nav-group">';
    foreach ($nodes as $node) {
        $is_parent = $node['has_children'];
        $is_selected = ($node['id'] == $selected_id);
        $is_open = in_array($node['id'], $ancestor_ids);

        echo '<li class="nav-node' . ($is_open ? ' is-open' : '') . '">';
        echo '<a href="?selected_id=' . $node['id'] . '" class="nav-link ' . ($is_selected ? 'active' : '') . '">';
        echo '<span>' . htmlspecialchars($node['account_name']) . '</span>';

        if ($is_parent) {
            echo '<i class="fas fa-chevron-right expand-icon"></i>';
        }
        echo '</a>';

        if ($is_parent && isset($node['children'])) {
            render_nav_tree($node['children'], $selected_id, $ancestor_ids);
        }
        echo '</li>';
    }
    echo '</ul>';
}
?>

<div class="container-fluid mt-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Chart of Accounts</h2>
            <p class="text-muted mb-0">Manage your accounting chart of accounts hierarchically</p>
        </div>
        <div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAccountModal">
                <i class="fas fa-plus me-2"></i>Add New Account
            </button>
            <button class="btn btn-success ms-2" onclick="exportToExcel('completeAccountTable', 'Chart_of_Accounts')">
                <i class="fas fa-file-excel me-2"></i>Export Excel
            </button>
        </div>
    </div>

    <!-- Success/Error Messages -->
    <?php if (isset($_SESSION['toast'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['toast'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['toast']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $_SESSION['error'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Main Content Area -->
    <div class="row">
        <!-- Left Sidebar - Navigation Tree -->
        <div class="col-lg-3 col-xl-3 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Account Groups</h6>
                    <span class="badge bg-primary"><?= count($accounts) ?></span>
                </div>
                <div class="card-body p-0" style="max-height: 600px; overflow-y: auto;">
                    <?php if (!empty($navigation_tree)): ?>
                        <?php render_nav_tree($navigation_tree, $selected_id, $ancestor_ids); ?>
                    <?php else: ?>
                        <div class="text-center p-4">
                            <i class="fas fa-folder-open text-muted fa-2x mb-3"></i>
                            <p class="text-muted">No accounts found</p>
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addAccountModal">
                                Add First Account
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Main Content - Selected Account Details -->
        <div class="col-lg-9 col-xl-9">
            <?php if ($selected_node): ?>
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0">
                                <?= htmlspecialchars($selected_node['account_name']) ?>
                                <small class="text-muted ms-2">
                                    <i class="fas fa-hashtag me-1"></i><?= $selected_node['account_code'] ?>
                                </small>
                            </h5>
                            <small class="text-muted">Parent Account - Sub Accounts</small>
                        </div>
                        <div>
                            <a href="edit_account.php?id=<?= $selected_node['id'] ?>" class="btn btn-sm btn-warning me-2">
                                <i class="fas fa-edit me-1"></i>Edit Parent
                            </a>
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addAccountModal" onclick="setParentAccount(<?= $selected_node['id'] ?>)">
                                <i class="fas fa-plus me-1"></i>Add Sub Account
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th width="15%">Code</th>
                                        <th width="40%">Account Name</th>
                                        <th width="20%">Type</th>
                                        <th width="25%">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($items_to_display)): ?>
                                        <tr>
                                            <td colspan="4" class="text-center py-4">
                                                <i class="fas fa-folder-open text-muted fa-2x mb-3"></i>
                                                <p class="text-muted mb-0">No sub-accounts found for this parent account.</p>
                                                <button class="btn btn-sm btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#addAccountModal" onclick="setParentAccount(<?= $selected_node['id'] ?>)">
                                                    Add First Sub Account
                                                </button>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($items_to_display as $item): ?>
                                            <tr>
                                                <td>
                                                    <span class="badge bg-dark"><?= htmlspecialchars($item['account_code']) ?></span>
                                                </td>
                                                <td>
                                                    <strong><?= htmlspecialchars($item['account_name']) ?></strong>
                                                    <?php if ($item['parent_id']): ?>
                                                        <br><small class="text-muted">Child of: <?= $selected_node['account_code'] ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    $type_badge_class = '';
                                                    switch($item['account_type']) {
                                                        case 'asset': $type_badge_class = 'bg-primary'; break;
                                                        case 'liability': $type_badge_class = 'bg-danger'; break;
                                                        case 'equity': $type_badge_class = 'bg-success'; break;
                                                        case 'revenue': $type_badge_class = 'bg-info'; break;
                                                        case 'expense': $type_badge_class = 'bg-warning text-dark'; break;
                                                        case 'stock': $type_badge_class = 'bg-secondary'; break;
                                                        case 'finances': $type_badge_class = 'bg-purple'; break;
                                                        case 'payables': $type_badge_class = 'bg-orange'; break;
                                                        case 'receivables': $type_badge_class = 'bg-teal'; break;
                                                        default: $type_badge_class = 'bg-secondary';
                                                    }
                                                    ?>
                                                    <span class="badge <?= $type_badge_class ?>"><?= ucfirst($item['account_type']) ?></span>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <a href="edit_account.php?id=<?= $item['id'] ?>" class="btn btn-outline-warning">
                                                            <i class="fas fa-edit me-1"></i>Edit
                                                        </a>
                                                        <button type="button" class="btn btn-outline-danger" onclick="confirmDelete(<?= $item['id'] ?>, '<?= htmlspecialchars(addslashes($item['account_name'])) ?>')">
                                                            <i class="fas fa-trash me-1"></i>Delete
                                                        </button>
                                                        <a href="?selected_id=<?= $item['id'] ?>" class="btn btn-outline-primary">
                                                            <i class="fas fa-folder me-1"></i>View Children
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-hand-pointer fa-3x text-muted mb-4"></i>
                        <h4 class="text-muted mb-3">Select an Account Group</h4>
                        <p class="text-muted mb-4">Choose an account group from the left sidebar to view and manage its sub-accounts.</p>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAccountModal">
                            <i class="fas fa-plus me-2"></i>Create Your First Account
                        </button>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add Account Modal -->
<div class="modal fade" id="addAccountModal" tabindex="-1" aria-labelledby="addAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" id="addAccountForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="addAccountModalLabel">Add New Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="account_code" class="form-label">Account Code *</label>
                            <input type="text" class="form-control" id="account_code" name="account_code" required placeholder="e.g., 1001">
                            <div class="form-text">Unique code for the account</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="account_name" class="form-label">Account Name *</label>
                            <input type="text" class="form-control" id="account_name" name="account_name" required placeholder="e.g., Cash Account">
                            <div class="form-text">Descriptive name for the account</div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="account_type" class="form-label">Account Type *</label>
                            <select class="form-select" id="account_type" name="account_type" required>
                                <option value="">Select Type</option>
                                <option value="asset">Asset</option>
                                <option value="liability">Liability</option>
                                <option value="equity">Equity</option>
                                <option value="revenue">Revenue</option>
                                <option value="expense">Expense</option>
                                <option value="stock">Stock</option>
                                <option value="finances">Finances</option>
                                <option value="payables">Payables</option>
                                <option value="receivables">Receivables</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="parent_account" class="form-label">Parent Account</label>
                            <select class="form-select" id="parent_account" name="parent_account">
                                <option value="">None (Top Level)</option>
                                <?php foreach ($accounts as $account): ?>
                                    <option value="<?= $account['id'] ?>">
                                        <?= $account['account_code'] ?> - <?= $account['account_name'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">Leave empty for top-level accounts</div>
                        </div>
                    </div>

                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Note:</strong> Account codes should follow a hierarchical numbering system. Child accounts typically extend parent account codes.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_account" class="btn btn-primary">Save Account</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="deleteForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                        <h5>Are you sure?</h5>
                        <p>You are about to delete the account: <strong id="deleteAccountName"></strong></p>
                        <p class="text-danger"><small>This action cannot be undone.</small></p>
                    </div>
                    <input type="hidden" name="id" id="deleteAccountId">
                    <input type="hidden" name="delete_account" value="1">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete Account</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Navigation tree styles */
.nav-group {
    list-style: none;
    padding: 0;
    margin: 0;
}

.nav-node {
    margin-bottom: 1px;
}

.nav-node .nav-link {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 15px;
    border-radius: 6px;
    text-decoration: none;
    color: #333;
    font-weight: 500;
    transition: all 0.2s;
    border: 1px solid transparent;
    background: #f8f9fa;
    margin-bottom: 2px;
}

.nav-node .nav-link:hover {
    background: #e9ecef;
    border-color: #dee2e6;
    transform: translateX(2px);
}

.nav-node .nav-link.active {
    background: #e7f1ff;
    color: #0d6efd;
    border-color: #b6d4fe;
    box-shadow: 0 2px 4px rgba(13, 110, 253, 0.1);
}

.nav-node .nav-link .expand-icon {
    font-size: 11px;
    transition: transform 0.2s;
}

.nav-node .nav-group {
    padding-left: 20px;
    margin-top: 2px;
}

/* Dropdown Styles */
.nav-node .nav-group {
    display: none;
}

.nav-node.is-open > .nav-group {
    display: block;
}

.nav-node.is-open > .nav-link .expand-icon {
    transform: rotate(90deg);
}

/* Custom badge colors */
.bg-purple { background-color: #6f42c1 !important; }
.bg-orange { background-color: #fd7e14 !important; }
.bg-teal { background-color: #20c997 !important; }

/* Smooth transitions */
.table-hover tbody tr {
    transition: background-color 0.15s ease;
}

/* Responsive adjustments */
@media (max-width: 992px) {
    .accounts-container {
        flex-direction: column;
    }

    #accounts-nav {
        width: 100% !important;
        border-right: none !important;
        border-bottom: 1px solid #dee2e6;
        padding-bottom: 1rem;
        margin-bottom: 1rem;
    }

    .btn-group {
        flex-wrap: wrap;
    }
}

/* Card enhancements */
.card {
    border: 1px solid #e0e0e0;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.card-header {
    background: #f8f9fa;
    border-bottom: 1px solid #e0e0e0;
}
</style>

<script>
// Navigation tree functionality
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('#accounts-nav .nav-link').forEach(link => {
        link.addEventListener('click', (event) => {
            const parentNode = event.currentTarget.closest('.nav-node');
            if (!parentNode) return;

            const hasChildren = parentNode.querySelector('.nav-group');

            // If the clicked link is already active and it's a dropdown,
            // prevent navigation and toggle its open/closed state.
            if (event.currentTarget.classList.contains('active') && hasChildren) {
                event.preventDefault();
                parentNode.classList.toggle('is-open');
            }
        });
    });
});

// Export to Excel function
function exportToExcel(tableID, filename = '') {
    var downloadLink;
    var dataType = 'application/vnd.ms-excel';
    var tableSelect = document.getElementById(tableID);
    var tableHTML = tableSelect.outerHTML.replace(/ /g, '%20');

    // Specify file name
    filename = filename ? filename + '.xls' : 'excel_data.xls';

    // Create download link element
    downloadLink = document.createElement("a");

    document.body.appendChild(downloadLink);

    if (navigator.msSaveOrOpenBlob) {
        var blob = new Blob(['\ufeff', tableHTML], {
            type: dataType
        });
        navigator.msSaveOrOpenBlob(blob, filename);
    } else {
        // Create a link to the file
        downloadLink.href = 'data:' + dataType + ', ' + tableHTML;

        // Set the file name
        downloadLink.download = filename;

        //triggering the function
        downloadLink.click();
    }
}

// Set parent account in modal
function setParentAccount(parentId) {
    document.getElementById('parent_account').value = parentId;
    const modal = new bootstrap.Modal(document.getElementById('addAccountModal'));
    modal.show();
}

// Confirm delete
function confirmDelete(accountId, accountName) {
    document.getElementById('deleteAccountId').value = accountId;
    document.getElementById('deleteAccountName').textContent = accountName;
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
}

// Form validation for account code
document.getElementById('addAccountForm')?.addEventListener('submit', function(e) {
    const accountCode = document.getElementById('account_code').value;
    if (!/^[0-9]{4,}$/.test(accountCode)) {
        e.preventDefault();
        alert('Account code should be numeric and at least 4 digits long.');
        return false;
    }
    return true;
});

// Auto-focus on modal show
const addAccountModal = document.getElementById('addAccountModal');
if (addAccountModal) {
    addAccountModal.addEventListener('shown.bs.modal', function () {
        document.getElementById('account_code').focus();
    });
}

// Initialize tooltips
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>