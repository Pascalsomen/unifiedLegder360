<?php
require_once __DIR__ . '/../../includes/header.php';

if (!isset($_GET['id'])) die("Student ID required.");
$studentId = $_GET['id'];

// Fetch documents
$stmt = $pdo->prepare("SELECT * FROM student_documents WHERE student_id = ? ORDER BY uploaded_at DESC");
$stmt->execute([$studentId]);
$documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">
     <br> <br>
    <h3>Uploaded Documents for this Student </h3>
    <a class="btn btn-primary" href="upload_documents.php?id=<?php echo $studentId?>">Upload New Document</a>
    <br><br>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Document Name</th>
                <th>File Type</th>
                <th>Uploaded At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($documents) > 0): ?>
                <?php foreach ($documents as $index => $doc): ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td><?= htmlspecialchars($doc['document_name']) ?></td>
                        <td><?= htmlspecialchars($doc['filetype']) ?></td>
                        <td><?= $doc['uploaded_at'] ?></td>
                        <td>
                            <a href="../uploads/student_documents/<?= urlencode($doc['filepath']) ?>" class="btn btn-sm btn-primary" download>Download</a>
                            <a href="delete_documents.php?id=<?= $doc['id'] ?>&student_id=<?= $studentId ?>"
                               class="btn btn-sm btn-danger"
                               onclick="return confirm('Are you sure you want to delete this document?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="5" class="text-center">No documents found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
