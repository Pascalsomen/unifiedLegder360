<?php
class DocumentManager {
    private $pdo;
    private $uploadPath;

    public function __construct(PDO $pdo, string $uploadPath) {
        $this->pdo = $pdo;
        $this->uploadPath = $uploadPath;

        if (!file_exists($this->uploadPath)) {
            mkdir($this->uploadPath, 0755, true);
        }
    }

    /**
     * Upload a document for a loan or rental
     */
    public function uploadDocument(
        string $entityType,
        int $entityId,
        string $documentType,
        array $file,
        int $uploaderId,
        string $notes = ''
    ): bool {
        // Validate entity type
        if (!in_array($entityType, ['loan', 'rental'])) {
            throw new InvalidArgumentException("Invalid entity type");
        }

        // Validate file
        $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
        if (!in_array($file['type'], $allowedTypes)) {
            throw new RuntimeException("Invalid file type. Only PDF, JPG, PNG allowed");
        }

        if ($file['size'] > 5 * 1024 * 1024) { // 5MB
            throw new RuntimeException("File size exceeds 5MB limit");
        }

        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $extension;
        $destination = $this->uploadPath . "/{$entityType}s/{$entityId}/{$filename}";

        // Create directory if needed
        $dir = dirname($destination);
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            throw new RuntimeException("Failed to move uploaded file");
        }

        // Record in database
        $table = $entityType . '_documents';
        $stmt = $this->pdo->prepare("
            INSERT INTO {$table} (
                {$entityType}_id, document_type, file_path,
                upload_date, notes, uploaded_by
            ) VALUES (?, ?, ?, CURDATE(), ?, ?)
        ");

        return $stmt->execute([
            $entityId,
            $documentType,
            $destination,
            $notes,
            $uploaderId
        ]);
    }

    /**
     * Get documents for an entity
     */
    public function getDocuments(string $entityType, int $entityId): array {
        $table = $entityType . '_documents';
        $stmt = $this->pdo->prepare("
            SELECT d.*, u.username as uploaded_by_name
            FROM {$table} d
            JOIN users u ON d.uploaded_by = u.id
            WHERE d.{$entityType}_id = ?
            ORDER BY d.upload_date DESC
        ");
        $stmt->execute([$entityId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Delete a document
     */
    public function deleteDocument(string $entityType, int $documentId): bool {
        // First get file path
        $table = $entityType . '_documents';
        $stmt = $this->pdo->prepare("
            SELECT file_path FROM {$table} WHERE id = ?
        ");
        $stmt->execute([$documentId]);
        $document = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$document) {
            return false;
        }

        // Delete file
        if (file_exists($document['file_path'])) {
            unlink($document['file_path']);
        }

        // Delete database record
        $stmt = $this->pdo->prepare("
            DELETE FROM {$table} WHERE id = ?
        ");
        return $stmt->execute([$documentId]);
    }
}