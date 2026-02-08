<?php
require_once __DIR__ . '/../../includes/header.php';

if (!hasPermission('pos')) {
    redirect('/index.php');
}

// Fetch required data
$items = $pdo->query("SELECT id, item_code, name, selling_price, current_quantity
                      FROM inventory_items WHERE is_active = TRUE")->fetchAll();
$customers = $pdo->query("SELECT id, name FROM customers ORDER BY name")->fetchAll();
$paymentTypes = $pdo->query("SELECT * FROM payment_types")->fetchAll();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between">
                    <h5>POS Register</h5>
                    <div>
                        <span id="current-time"></span> |
                        <span id="current-user"><?= $_SESSION['user_name'] ?></span>
                    </div>
                </div>
                <div class="card-body">
                    <form id="posForm" method="POST" action="process_sale.php">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label>Customer</label>
                                <select class="form-control select2" id="customerSelect" name="customer_id">
                                    <option value="">Walk-in Customer</option>
                                    <?php foreach ($customers as $customer): ?>
                                        <option value="<?= $customer['id'] ?>"><?= $customer['name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label>Payment Method</label>
                                <select class="form-control" id="paymentMethod" name="payment_method" required>
                                    <?php foreach ($paymentTypes as $type): ?>
                                        <option value="<?= $type['id'] ?>"
                                            <?= $type['is_cash'] ? 'selected' : '' ?>>
                                            <?= $type['name'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="input-group">
                                    <input type="text" class="form-control" id="itemSearch"
                                           placeholder="Scan barcode or search items...">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="button" id="scanBarcode">
                                            <i class="fas fa-barcode"></i> Scan
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table" id="saleItems">
                                <thead>
                                    <tr>
                                        <th width="40%">Item</th>
                                        <th width="15%">Price</th>
                                        <th width="15%">Qty</th>
                                        <th width="20%">Total</th>
                                        <th width="10%"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Items will be added here -->
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="text-right"><strong>Subtotal</strong></td>
                                        <td id="subtotal">0.00</td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="text-right"><strong>Tax (16%)</strong></td>
                                        <td id="tax">0.00</td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="text-right"><strong>Discount</strong></td>
                                        <td>
                                            <input type="number" class="form-control form-control-sm"
                                                   id="discountInput" name="discount" value="0" min="0" step="0.01">
                                        </td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="text-right"><strong>Total</strong></td>
                                        <td id="total">0.00</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <input type="hidden" name="items" id="itemsInput">
                        <input type="hidden" name="user_id" value="<?= $_SESSION['user_id'] ?>">

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <button type="button" class="btn btn-secondary btn-lg" id="holdSale">
                                    <i class="fas fa-pause"></i> Hold Sale
                                </button>
                                <button type="button" class="btn btn-danger btn-lg" id="cancelSale">
                                    <i class="fas fa-times"></i> Cancel
                                </button>
                            </div>
                            <div class="col-md-6 text-right">
                                <button type="submit" class="btn btn-success btn-lg" id="completeSale">
                                    <i class="fas fa-check"></i> Complete Sale
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5>Quick Actions</h5>
                </div>
                <div class="card-body">
                    <button class="btn btn-primary btn-block mb-2" id="newCustomer">
                        <i class="fas fa-user-plus"></i> New Customer
                    </button>
                    <button class="btn btn-warning btn-block mb-2" id="openDrawer">
                        <i class="fas fa-cash-register"></i> Open Cash Drawer
                    </button>
                    <button class="btn btn-secondary btn-block mb-2" id="dailyReport">
                        <i class="fas fa-file-alt"></i> Daily Report
                    </button>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header bg-success text-white">
                    <h5>Product Quick Select</h5>
                </div>
                <div class="card-body p-2">
                    <div class="row">
                        <?php foreach ($items as $item): ?>
                        <div class="col-4 mb-2">
                            <button class="btn btn-outline-primary btn-block item-btn"
                                    data-id="<?= $item['id'] ?>"
                                    data-name="<?= htmlspecialchars($item['name']) ?>"
                                    data-price="<?= $item['selling_price'] ?>"
                                    data-stock="<?= $item['current_quantity'] ?>">
                                <small><?= $item['item_code'] ?></small><br>
                                <strong><?= $item['name'] ?></strong><br>
                                <?= number_format($item['selling_price'], 2) ?>
                            </button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- New Customer Modal -->
<div class="modal fade" id="customerModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">New Customer</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="customerForm">
                    <div class="form-group">
                        <label>Name*</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="text" class="form-control" name="phone">
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" class="form-control" name="email">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveCustomer">Save</button>
            </div>
        </div>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js" integrity="sha512-v2CJ7UaYy4JwqLDIrZUI/4hqeoQieOmAZNXBeQyjo21dadnwR+8ZaIJVT8EE2iyI61OV8e6M8PP2/4hpQINQ/g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
$(document).ready(function() {
    const saleItems = [];
    let taxRate = 0.16; // 16% tax

    // Initialize select2
    $('.select2').select2();

    // Update current time
    function updateClock() {
        const now = new Date();
        $('#current-time').text(now.toLocaleTimeString());
    }
    setInterval(updateClock, 1000);
    updateClock();

    // Add item to sale
    function addItemToSale(id, name, price, quantity = 1) {
        const existingItem = saleItems.find(item => item.id === id);

        if (existingItem) {
            existingItem.quantity += quantity;
        } else {
            saleItems.push({
                id: id,
                name: name,
                price: price,
                quantity: quantity
            });
        }

        updateSaleTable();
    }

    // Item search functionality
    $('#itemSearch').on('keyup', function(e) {
        if (e.key === 'Enter') {
            const searchTerm = $(this).val().trim().toLowerCase();
            if (searchTerm) {
                const foundItem = items.find(item =>
                    item.item_code.toLowerCase() === searchTerm ||
                    item.barcode === searchTerm ||
                    item.name.toLowerCase().includes(searchTerm)
                );

                if (foundItem) {
                    addItemToSale(foundItem.id, foundItem.name, foundItem.selling_price);
                    $(this).val('');
                } else {
                    alert('Item not found!');
                }
            }
        }
    });

    // Barcode scanning simulation
    $('#scanBarcode').click(function() {
        alert('In a real implementation, this would interface with a barcode scanner');
    });

    // New customer modal
    $('#newCustomer').click(function() {
        $('#customerModal').modal('show');
    });

    // Save new customer
    $('#saveCustomer').click(function() {
        const formData = $('#customerForm').serialize();

        $.post('/api/customers.php?action=create', formData, function(response) {
            if (response.success) {
                // Add new customer to select dropdown
                const newOption = new Option(response.data.name, response.data.id, true, true);
                $('#customerSelect').append(newOption).trigger('change');
                $('#customerModal').modal('hide');
                $('#customerForm')[0].reset();
            } else {
                alert('Error: ' + response.message);
            }
        }, 'json');
    });

    // Complete sale handling
    $('#posForm').on('submit', function(e) {
        e.preventDefault();

        if (saleItems.length === 0) {
            alert('Please add items to the sale');
            return;
        }

        // Prepare items data
        const itemsData = saleItems.map(item => ({
            id: item.id,
            quantity: item.quantity,
            price: item.price
        }));

        $('#itemsInput').val(JSON.stringify(itemsData));

        // Submit form
        this.submit();
    });

    // Other POS functions would be implemented similarly...
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>