<?php
require_once __DIR__ . '/../../includes/header.php';

if (!isset($_GET['id'])) die("Student ID required.");
$studentId = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $documentName = $_POST['document_name'];
    $file = $_FILES['document_file'];

    if ($file['error'] === 0) {
        $fileType = $file['type'];
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $uploadDir = __DIR__ . '/../uploads/student_documents/';
        $newFileName = uniqid() . '.' . $ext;

        if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);

        if (move_uploaded_file($file['tmp_name'], $uploadDir . $newFileName)) {
            $stmt = $pdo->prepare("INSERT INTO student_documents (student_id, document_name, filetype, uploaded_at,	filepath) VALUES (?, ?, ?, NOW(),?)");
            $stmt->execute([$studentId, $documentName, $fileType,$newFileName]);

            echo "<div class='alert alert-success'>Document uploaded successfully.</div>";
        } else {
            echo "<div class='alert alert-danger'>Failed to move uploaded file.</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>File upload error.</div>";
    }
}
?>

<div class="container mt-4">
    <h3>Upload Document for Student #<?= htmlspecialchars($studentId) ?></h3>
    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label>Document Name</label>
            <input type="text" name="document_name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Choose File</label>
            <input type="file" name="document_file" class="form-control" required>
        </div>
        <button class="btn btn-primary">Upload</button>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
