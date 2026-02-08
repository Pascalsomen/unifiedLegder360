<?php
require_once __DIR__ . '/../../includes/header.php';

if (!isset($_GET['contract_id'])) {
    echo "<div class='alert alert-danger'>Contract ID missing.</div>";
    exit;
}

$contract_id = (int) $_GET['contract_id'];

$contract = $pdo->prepare("SELECT contract_number, contract_title FROM contracts WHERE contract_id = ?");
$contract->execute([$contract_id]);
$contract = $contract->fetch(PDO::FETCH_ASSOC);

if (!$contract) {
    echo "<div class='alert alert-danger'>Contract not found.</div>";
    exit;
}

// Handle form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $signer_name = $_POST['signer_name'];
    $signer_title = $_POST['signer_title'];
    $signature_date = $_POST['signature_date'];
    $signer_type = $_POST['signer_type'];
    $scanned_file = null;

    // Handle file upload
    if (!empty($_FILES['scanned_file']['name'])) {
        $uploadDir = __DIR__ . '/../../uploads/contracts/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $filename = time() . '_' . basename($_FILES['scanned_file']['name']);
        $targetFile = $uploadDir . $filename;

        if (move_uploaded_file($_FILES['scanned_file']['tmp_name'], $targetFile)) {
            $scanned_file = $filename;
        }
    }

    if ($signer_name && $signer_title && $signature_date && $signer_type) {
        $stmt = $pdo->prepare("
            INSERT INTO contract_signatures (contract_id, signer_name, signer_title, signature_date, signer_type, scanned_file)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$contract_id, $signer_name, $signer_title, $signature_date, $signer_type, $scanned_file]);
        $success = "Signature added successfully.";
    } else {
        $error = "Please fill all required fields.";
    }
}

// Fetch all signatures
$sigs = $pdo->prepare("SELECT * FROM contract_signatures WHERE contract_id = ? ORDER BY id ASC");
$sigs->execute([$contract_id]);
$signatures = $sigs->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid mt-4">
    <h4 class="mb-3">Add Contract Signatures</h4>
    <p><strong>Contract #<?= htmlspecialchars($contract['contract_number']) ?>:</strong> <?= htmlspecialchars($contract['contract_title']) ?></p>

    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php elseif (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="card p-4 mb-4">
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Signer Name</label>
                <input type="text" name="signer_name" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Position/Title</label>
                <input type="text" name="signer_title" class="form-control" required>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">Signature Date</label>
                <input type="date" name="signature_date" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Signer Type</label>
                <select name="signer_type" class="form-select" required>
                    <option value="">-- Select --</option>
                    <option value="Company">Company</option>
                    <option value="Supplier">Supplier</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Upload Scanned File (optional)</label>
                <input type="file" name="scanned_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
            </div>
        </div>

        <button type="submit" class="btn btn-success">Add Signature</button>
        <a href="contract_summary.php?contract_id=<?= $contract_id ?>" class="btn btn-primary float-end">Finish & View Contract</a>
    </form>

    <?php if ($signatures): ?>
        <h5>Signatures Added</h5>
        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>Type</th>
                    <th>Name</th>
                    <th>Title</th>
                    <th>Date</th>
                    <th>Scanned File</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($signatures as $s): ?>
                    <tr>
                        <td><?= htmlspecialchars($s['signer_type']) ?></td>
                        <td><?= htmlspecialchars($s['signer_name']) ?></td>
                        <td><?= htmlspecialchars($s['signer_title']) ?></td>
                        <td><?= htmlspecialchars($s['signature_date']) ?></td>
                        <td>
                            <?php if ($s['scanned_file']): ?>
                                <a href="../../uploads/contracts/<?= $s['scanned_file'] ?>" target="_blank">View</a>
                            <?php else: ?>
                                N/A
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
