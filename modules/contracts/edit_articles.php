<?php
require_once __DIR__ . '/../../includes/header.php';

$contract_id = $_GET['contract_id'] ?? null;
if (!$contract_id) {
    echo "<div class='alert alert-danger'>Missing contract ID.</div>";
    exit;
}

// Handle add new article
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_article'])) {
    $article_title = $_POST['article_title'];
    $article_description = $_POST['article_description'];

    $stmt = $pdo->prepare("INSERT INTO contract_articles (contract_id, title, body) VALUES (?, ?, ?)");
    $stmt->execute([$contract_id, $article_title, $article_description]);
}

// Handle update articles
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_articles'])) {
    foreach ($_POST['article_ids'] as $index => $article_id) {
        $title = $_POST['titles'][$index];
        $desc = $_POST['descriptions'][$index];

        $stmt = $pdo->prepare("UPDATE contract_articles SET title = ?, body = ? WHERE article_id = ?");
        $stmt->execute([$title, $desc, $article_id]);
    }
}

// Handle delete
if (isset($_GET['delete_article'])) {
    $article_id = $_GET['delete_article'];
    $pdo->prepare("DELETE FROM contract_articles WHERE article_id = ?")->execute([$article_id]);
}

// Fetch articles
$stmt = $pdo->prepare("SELECT * FROM contract_articles WHERE contract_id = ?");
$stmt->execute([$contract_id]);
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">
    <h3>Edit Contract Articles <a href="edit_contract.php?contract_id=<?php echo $contract_id ?>" class="btn btn-info">Edit contract headers</a> <a href="edit_items.php?contract_id=<?php echo $contract_id ?>" class="btn btn-info">Edit Items</a> <a href="edit_signature.php?contract_id=<?php echo $contract_id ?>" class="btn btn-info">Edit Signature</a></h3>

    <form method="post">
        <input type="hidden" name="update_articles" value="1">
        <?php foreach ($articles as $a): ?>
            <div class="card mb-3 p-3">
                <input type="hidden" name="article_ids[]" value="<?= $a['article_id'] ?>">
                <div class="mb-2">
                    <label>Article Title</label>
                    <input type="text" name="titles[]" class="form-control" value="<?= htmlspecialchars($a['title']) ?>">
                </div>
                <div class="mb-2">
                    <label>Description</label>
                    <textarea name="descriptions[]" class="form-control" rows="2"><?= htmlspecialchars($a['body']) ?></textarea>
                </div>
                <a href="?contract_id=<?= $contract_id ?>&delete_article=<?= $a['article_id'] ?>" class="btn btn-sm btn-danger"
                   onclick="return confirm('Delete this article?')">Delete</a>
            </div>
        <?php endforeach; ?>
        <button type="submit" class="btn btn-primary">Update Articles</button>
    </form>

    <hr>

    <h4>Add New Article</h4>
    <form method="post">
        <input type="hidden" name="new_article" value="1">
        <div class="mb-2">
            <label>Article Title</label>
            <input type="text" name="article_title" class="form-control" required>
        </div>
        <div class="mb-2">
            <label>Description</label>
            <textarea name="article_description" class="form-control" rows="2" required></textarea>
        </div>
        <button type="submit" class="btn btn-success">Add Article</button>
    </form>

    <a href="contract_list.php" class="btn btn-secondary mt-4">Back to Contract List</a>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
