<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../classes/InventorySystem.php';





$inventorySystem = new InventorySystem($pdo);


if (isset($_POST['approve_po_id'])) {
    $poId = (int) $_POST['approve_po_id'];
    if ($inventorySystem->approvePurchaseOrder($poId)) {
        $_SESSION['toast'] = "Purchase Order approved successfully.";
        echo "<script>window.location='purchase_orders.php'</script>";
    } else {
        $errorMessage = "Failed to approve Purchase Order.";
    }
}

if (isset($_POST['submit_po_id'])) {
    $poId = (int) $_POST['submit_po_id'];
    if ($inventorySystem->submitPurchaseOrder($poId)) {
        $_SESSION['toast'] = "Purchase Order submitted successfully.";
        echo "<script>window.location='purchase_orders.php'</script>";

    } else {
        $errorMessage = "Failed to submit Purchase Order.";
    }
}



$purchaseOrders = $inventorySystem->getPurchaseOrders();
$stockItems = $inventorySystem->listStockItems();
$suppliers= $inventorySystem->listSuppliers();



try {
    $stmt = $pdo->query("SELECT contract_id, contract_number, contract_title FROM contracts");
    $contracts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Handle error (log it, show user-friendly message, etc.)
    error_log("Database error: " . $e->getMessage());

    echo $e->getMessage();
    $contracts = []; // Empty array as fallback
}

if (isset($_GET['delete_po'])) {
    $poId = (int)$_GET['delete_po'];
    if ($inventorySystem->deletePurchaseOrder($poId)) {
        $_SESSION['toast'] = "Purchase order deleted successfully.";
    } else {
        $_SESSION['error'] = "Failed to delete purchase order. Only draft orders can be deleted.";
    }
    echo "<script>window.location='purchase_orders.php'</script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_purchase_order'])) {
        // Get form data
        $poData = [
            'supplier_id' => $_POST['supplier_id'],
            'order_date' => date('Y-m-d'),
            'contract_id' => $_POST['contract_id'],
            'created_by' => $_SESSION['user_id'],
            'purpose' => $_POST['remarks']
        ];

        // Collect the items and their quantities
        $items = [];
        foreach ($_POST['item_id'] as $index => $itemId) {
            $items[] = [
                'item_id' => $itemId,
                'quantity' => $_POST['quantity'][$index],
                'price' => $_POST['price'][$index]
            ];
        }

        // Create the purchase order
        $poId = $inventorySystem->createPurchaseOrder($poData, $items);

        $_SESSION['toast'] = "Purchase order created successfully.";
        echo "<script>window.location='view_purchase_order.php?id=$poId'</script>";
        exit;
    }
}



?>


<div class="container mt-5">
    <h3 class="mb-4">Purchase Orders</h3>
    <div class="text-end mb-3">
    <?php if(hasPermission(7)){
                            ?>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPurchaseOrderModal">
        Add Purchase Order
    </button>

    <?php }else{
        echo "You do not have access to create Purchase order";
    }?>
</div>


    <table class="table table-bordered">
        <thead class="table-light">
            <tr>
                <th>Order code</th>
                <th>Order Date</th>
                <th>Supplier</th>
                <th>Purpose</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($purchaseOrders as $order): ?>
                <tr>
                <td>RCF-PO-<?= htmlspecialchars($order['id']) ?></td>
                    <td><?= htmlspecialchars($order['order_date']) ?></td>
                    <td>
                        <?php
                        // Fetch the supplier name by supplier_id
                        $supplier = $inventorySystem->getSupplierById($order['supplier_id']);
                        echo htmlspecialchars($supplier['name']);
                        ?>
                    </td>
                    <td><?= htmlspecialchars($order['purpose']) ?></td>
                    <td><?= htmlspecialchars(ucfirst($order['status'])) ?></td>
                    <td>
                        <a href="view_purchase_order.php?id=<?= $order['id'] ?>" class="btn btn-info btn-sm">View</a>
                        <?php if ($order['status'] == 'draft'): ?>

                            <?php if(hasPermission(8)){
                            ?><a href="edit_purchase_order.php?id=<?= $order['id'] ?>" class="btn btn-warning btn-sm">Edit</a><?php } ?>


<?php if(hasPermission(9)){ ?>

    <a href="purchase_orders.php?delete_po=<?= $order['id'] ?>"
   class="btn btn-danger btn-sm"
   onclick="return confirm('Are you sure you want to delete this draft purchase order?')">
   Delete
</a> <?php } ?>



                        <?php endif; ?>

                        <?php if($order['status'] === 'draft'): ?>
            <form method="post" style="display:inline;">
                <input type="hidden" name="submit_po_id" value="<?= $order['id'] ?>">
                <button type="submit" class="btn btn-success btn-sm">Submit for Approval</button>
            </form>
        <?php endif; ?>

        <?php if($order['status'] === 'submitted'): ?>

            <?php if(hasPermission(10)){ ?>
            <form method="post" style="display:inline;">
                <input type="hidden" name="approve_po_id" value="<?= $order['id'] ?>">
                <button type="submit" class="btn btn-primary btn-sm">Approve</button>
            </form>
            <?php } ?>
        <?php endif; ?>
        <?php if($order['status'] === 'approved'): ?>
        <a href="print_purchase_order.php?id=<?= $order['id'] ?>" target="_blank" class="btn btn-success btn-sm">
 Preview and Print        </a>

 <?php if(hasPermission(12)){ ?>
 <a href="receive_purchase.php?id=<?= $order['id'] ?>" target="_blank" class="btn btn-primary btn-sm">
 Receive      </a>

 <?php }?>
    <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<div class="modal fade" id="addPurchaseOrderModal" tabindex="-1" aria-labelledby="addPurchaseOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPurchaseOrderModalLabel">Add New Purchase Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="post">


                <div class="mb-3">
                        <label for="supplier_id" class="form-label">Contract</label>
<select class="form-select" id="contract_id" name="contract_id" required onchange="fetchContractData(this.value)">
    <option value="">Select Contract</option>
    <?php foreach ($contracts as $cont): ?>
        <option value="<?= $cont['contract_id'] ?>">
            <?= htmlspecialchars($cont['contract_number']) ?> - <?= htmlspecialchars($cont['contract_title']) ?>
        </option>
    <?php endforeach; ?>
</select>


                    </div>
                    <div class="mb-3">
                        <label for="supplier_id" class="form-label">Supplier</label>
                     <select class="form-select" id="supplier_id" name="supplier_id" required>

    <!-- Suppliers will be loaded here via JavaScript -->
</select>
                    </div>



                    <div id="items-container">
                        <!-- Dynamically added items will go here -->
                        <div class="item-entry mb-3">
                            <label for="item_id" class="form-label">Item</label>
                            <select class="form-select" name="item_id[]" required>
                                <option value="">Select Item</option>
                                <?php foreach ($stockItems as $item): ?>
                                    <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['item_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <label for="quantity" class="form-label">Quantity</label>
                            <input type="number" class="form-control" name="quantity[]" min="1" required>

                            <label for="quantity" class="form-label">Unit Price</label>
                            <input type="number" class="form-control" name="price[]" min="1" required>

                        </div>
                    </div>

                    <button type="button" class="btn btn-secondary" id="addItemButton">Add Another Item</button>

                    <div class="mb-3 mt-3">
                        <label for="remarks" class="form-label">Remarks / Purpose</label>
                        <textarea class="form-control" id="remarks" name="remarks" required></textarea>
                    </div>

                    <button type="submit" name="create_purchase_order" class="btn btn-primary">Save</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const stockItems = <?= json_encode($stockItems) ?>;
</script>
<script>
function fetchSuppliers(contractId) {
    if (!contractId) {
        document.getElementById('supplier_id').innerHTML = '';
        return;
    }

    fetch(`get_suppliers.php?contract_id=${contractId}`)
        .then(response => response.json())
        .then(data => {
            const supplierSelect = document.getElementById('supplier_id');


            data.forEach(supplier => {
                const option = document.createElement('option');
                option.value = supplier.id;
                option.textContent = supplier.name;
                supplierSelect.appendChild(option);
            });
        })
        .catch(error => console.error('Error:', error));
}
</script>
<script>
function fetchContractData(contractId) {
    if (!contractId) return;

    fetch('get_suppliers.php?contract_id=' + contractId)
        .then(response => response.json())
        .then(data => {
            // Populate suppliers
            const supplierSelect = document.getElementById('supplier_id');
     
            data.suppliers.forEach(supplier => {
                const opt = document.createElement('option');
                opt.value = supplier.id;
                opt.textContent = supplier.name;
                supplierSelect.appendChild(opt);
            });

             const container = document.getElementById('items-container');
            container.innerHTML = ''; // Clear previous items

            data.items.forEach(item => {
                const entry = document.createElement('div');
                entry.className = 'item-entry mb-3';

                entry.innerHTML = `
                    <label class="form-label">Item</label>
                    <select class="form-select" name="item_id[]" required>
                        <option value="">Select Item</option>
                        ${stockItems.map(opt =>
                            `<option value="${opt.id}" ${opt.item_name === item.item_name ? 'selected' : ''}>
                                ${opt.item_name}
                            </option>`
                        ).join('')}
                    </select>

                    <label class="form-label">Quantity</label>
                    <input type="number" class="form-control" name="quantity[]" min="1" required value="${item.quantity}">

                    <label class="form-label">Unit Price</label>
                    <input type="number" class="form-control" name="price[]" min="0" required value="${item.unit_price}">
                `;

                container.appendChild(entry);
            });
        })
        .catch(err => console.error('Error fetching contract data:', err));
}
</script>

<script>

document.addEventListener('DOMContentLoaded', function() {
    const addItemButton = document.getElementById('addItemButton');
    const itemsContainer = document.getElementById('items-container');

    // Add new item entry
    addItemButton.addEventListener('click', function() {
        const itemEntry = document.createElement('div');
        itemEntry.classList.add('item-entry', 'mb-3');
        itemEntry.innerHTML = `
            <label class="form-label">Item</label>
            <select class="form-select" name="item_id[]" required>
                <option value="">Select Item</option>
                <?php foreach ($stockItems as $item): ?>
                    <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['item_name']) ?></option>
                <?php endforeach; ?>
            </select>
            <label class="form-label">Quantity</label>
            <input type="number" class="form-control" name="quantity[]" min="1" required>

            <label class="form-label">Unit Price</label>
            <input type="number" class="form-control" name="price[]" min="1" required>


            <button type="button" class="btn btn-danger remove-item-btn mt-2">Remove</button>
        `;
        itemsContainer.appendChild(itemEntry);
    });

    // Remove item entry
    itemsContainer.addEventListener('click', function(event) {
        if (event.target.classList.contains('remove-item-btn')) {
            event.target.parentElement.remove();
        }
    });
});



</script>


<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

<script>
       $(document).ready(function() {
                // Initialize Select2
              $('select').select2('destroy');
             // alert('he');

       });
    </script>