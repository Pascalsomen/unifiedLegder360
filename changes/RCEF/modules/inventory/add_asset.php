<?php require_once __DIR__ . '/../../includes/header.php'; ?>

<div class="container mt-5">
    <h3 class="mb-4">Add Fixed Asset</h3>

    <form action="process_add_asset.php" method="post">
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="asset_name" class="form-label">Asset Name</label>
                <input type="text" name="asset_name" id="asset_name" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label for="category" class="form-label">Category</label>
                <input type="text" name="category" id="category" class="form-control" required>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="purchase_date" class="form-label">Purchase Date</label>
                <input type="date" name="purchase_date" id="purchase_date" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label for="cost" class="form-label">Cost</label>
                <input type="number" name="cost" id="cost" step="0.01" class="form-control" required>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="useful_life" class="form-label">Useful Life (Years)</label>
                <input type="number" name="useful_life" id="useful_life" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label for="salvage_value" class="form-label">Salvage Value</label>
                <input type="number" name="salvage_value" id="salvage_value" step="0.01" class="form-control" value="0">
            </div>
        </div>

        <div class="mb-3">
            <label for="depreciation_method" class="form-label">Depreciation Method</label>
            <select name="depreciation_method" id="depreciation_method" class="form-select" required>
                <option value="straight_line">Straight Line</option>
                <option value="reducing_balance">Reducing Balance</option>
            </select>
        </div>

        <div class="text-end">
            <button type="submit" class="btn btn-primary">Save Asset</button>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
