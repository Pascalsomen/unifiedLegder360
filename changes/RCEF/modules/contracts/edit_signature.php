<?php
require_once __DIR__ . '/../../includes/header.php';

$contract_id = $_GET['contract_id'] ?? null;
if (!$contract_id) {
    echo "<div class='alert alert-danger'>Missing contract ID.</div>";
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update existing signatures
    if (!empty($_POST['existing_id'])) {
        foreach ($_POST['existing_id'] as $index => $id) {
            $signer_name = $_POST['existing_signer_name'][$index];
            $signer_title = $_POST['existing_signer_title'][$index];
            $signer_type = $_POST['existing_signer_type'][$index];
            $signature_date = $_POST['existing_signature_date'][$index];

            // Fetch existing scanned file path
            $stmt = $pdo->prepare("SELECT scanned_file FROM contract_signatures WHERE id = ?");
            $stmt->execute([$id]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            $scanned_file = $existing['scanned_file'];

            if (isset($_FILES['existing_scanned_file']['name'][$index]) && $_FILES['existing_scanned_file']['error'][$index] === UPLOAD_ERR_OK) {
                $upload_dir = __DIR__ . '/../../uploads/signatures/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

                $ext = pathinfo($_FILES['existing_scanned_file']['name'][$index], PATHINFO_EXTENSION);
                $new_name = 'signature_' . time() . "_$index." . $ext;
                $destination = $upload_dir . $new_name;

                if (move_uploaded_file($_FILES['existing_scanned_file']['tmp_name'][$index], $destination)) {
                    $scanned_file = 'uploads/signatures/' . $new_name;
                }
            }

            $stmt = $pdo->prepare("UPDATE contract_signatures SET signer_name = ?, signer_title = ?, signer_type = ?, signature_date = ?, scanned_file = ? WHERE id = ?");
            $stmt->execute([$signer_name, $signer_title, $signer_type, $signature_date, $scanned_file, $id]);
        }
    }

    // Insert new signatures
    if (!empty($_POST['signer_name'])) {
        for ($i = 0; $i < count($_POST['signer_name']); $i++) {
            $signer_name = $_POST['signer_name'][$i];
            $signer_title = $_POST['signer_title'][$i];
            $signer_type = $_POST['signer_type'][$i];
            $signature_date = $_POST['signature_date'][$i];

            $scanned_file = null;
            if (isset($_FILES['scanned_file']['name'][$i]) && $_FILES['scanned_file']['error'][$i] === UPLOAD_ERR_OK) {
                $upload_dir = __DIR__ . '/../../uploads/signatures/';
                if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

                $ext = pathinfo($_FILES['scanned_file']['name'][$i], PATHINFO_EXTENSION);
                $new_name = 'signature_' . time() . "_new_$i." . $ext;
                $destination = $upload_dir . $new_name;

                if (move_uploaded_file($_FILES['scanned_file']['tmp_name'][$i], $destination)) {
                    $scanned_file = 'uploads/signatures/' . $new_name;
                }
            }

            $stmt = $pdo->prepare("INSERT INTO contract_signatures (contract_id, signer_name, signer_title, signature_date, signer_type, scanned_file, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$contract_id, $signer_name, $signer_title, $signature_date, $signer_type, $scanned_file]);
        }
    }

    echo "<div class='alert alert-success'>Signatures saved successfully.</div>";
}

// Fetch existing signatures
$stmt = $pdo->prepare("SELECT * FROM contract_signatures WHERE contract_id = ?");
$stmt->execute([$contract_id]);
$signatures = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">
    <h3>Manage Contract Signatures</h3>
    <form method="post" enctype="multipart/form-data">
        <!-- Existing Signatures -->
        <?php foreach ($signatures as $index => $s): ?>
            <div class="signature-block border rounded p-3 mb-3 bg-light">
                <input type="hidden" name="existing_id[]" value="<?= $s['id'] ?>">
                <h5>Existing Signer #<?= $index + 1 ?></h5>
                <div class="mb-2">
                    <label>Name</label>
                    <input type="text" name="existing_signer_name[]" class="form-control" value="<?= htmlspecialchars($s['signer_name']) ?>" required>
                </div>
                <div class="mb-2">
                    <label>Title</label>
                    <input type="text" name="existing_signer_title[]" class="form-control" value="<?= htmlspecialchars($s['signer_title']) ?>" required>
                </div>
                <div class="mb-2">
                    <label>Type</label>
                    <select name="existing_signer_type[]" class="form-control" required>
                        <option value="">Select Type</option>
                        <option value="Company" <?= $s['signer_type'] === 'Company' ? 'selected' : '' ?>>Company</option>
                        <option value="Supplier" <?= $s['signer_type'] === 'Supplier' ? 'selected' : '' ?>>Supplier</option>
                    </select>
                </div>
                <div class="mb-2">
                    <label>Date Signed</label>
                    <input type="date" name="existing_signature_date[]" class="form-control" value="<?= $s['signature_date'] ?>" required>
                </div>
                <div class="mb-2">
                    <label>Scanned Signature</label>
                    <input type="file" name="existing_scanned_file[]" class="form-control">
                    <?php if ($s['scanned_file']): ?>
                        <p class="mt-1">Current: <a href="/<?= $s['scanned_file'] ?>" target="_blank">View</a></p>
                    <?php else: ?>
                        <p class="mt-1 text-muted">No file</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- New Signature Block -->
        <div id="signature-wrapper">
            <div class="signature-block border rounded p-3 mb-3">
                <h5>New Signer</h5>
                <div class="mb-2">
                    <label>Name</label>
                    <input type="text" name="signer_name[]" class="form-control">
                </div>
                <div class="mb-2">
                    <label>Title</label>
                    <input type="text" name="signer_title[]" class="form-control">
                </div>
                <div class="mb-2">
                    <label>Type</label>
                    <select name="signer_type[]" class="form-control">
                        <option value="">Select Type</option>
                        <option value="Company">Company</option>
                        <option value="Supplier">Supplier</option>
                    </select>
                </div>
                <div class="mb-2">
                    <label>Date Signed</label>
                    <input type="date" name="signature_date[]" class="form-control" value="<?= date('Y-m-d') ?>">
                </div>
                <div class="mb-2">
                    <label>Scanned Signature</label>
                    <input type="file" name="scanned_file[]" class="form-control">
                </div>
            </div>
        </div>

        <button type="button" class="btn btn-secondary mb-3" onclick="addSignatureBlock()">Add Another New Signer</button>
        <br>
        <button type="submit" class="btn btn-primary">Save Signatures</button>
    </form>

    <a href="contract_list.php" class="btn btn-secondary mt-4">Back to Contract List</a>
</div>

<script>
function addSignatureBlock() {
    const wrapper = document.getElementById('signature-wrapper');
    const clone = wrapper.querySelector('.signature-block').cloneNode(true);
    clone.querySelectorAll('input, select').forEach(el => el.value = '');
    wrapper.appendChild(clone);
}
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
