<?php
require_once __DIR__ . '/../../includes/header.php';

if (!isset($_GET['id'], $_GET['student_id'])) die("Invalid parameters.");
$docId = $_GET['id'];
$studentId = $_GET['student_id'];

// Get file info
$stmt = $pdo->prepare("SELECT document_name FROM student_documents WHERE id = ?");
$stmt->execute([$docId]);
$doc = $stmt->fetch();

if ($doc) {
    $filePath = __DIR__ . '/uploads/' . $doc['document_name'];
    if (file_exists($filePath)) {
        unlink($filePath); // delete file
    }

    // Remove from DB
    $pdo->prepare("DELETE FROM student_documents WHERE id = ?")->execute([$docId]);
}

echo "<script> window.location='list_documents.php?id=$studentId' </script>";
exit;
