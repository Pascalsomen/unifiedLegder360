<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Supporting Documents</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
            <i class="bi bi-upload"></i> Upload Document
        </button>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Documents for <?= $entityType ?> #<?= $entityNumber ?></h5>
        </div>
        <div class="card-body">
            <?php if (empty($documents)): ?>
                <div class="alert alert-info">No documents uploaded yet</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Document Type</th>
                                <th>Upload Date</th>
                                <th>Uploaded By</th>
                                <th>Notes</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($documents as $doc): ?>
                            <tr>
                                <td><?= htmlspecialchars($doc['document_type']) ?></td>
                                <td><?= date('M j, Y', strtotime($doc['upload_date'])) ?></td>
                                <td><?= htmlspecialchars($doc['uploaded_by_name']) ?></td>
                                <td><?= htmlspecialchars($doc['notes']) ?></td>
                                <td>
                                    <a href="<?= htmlspecialchars($doc['file_path']) ?>"
                                       class="btn btn-sm btn-primary" download>
                                        <i class="bi bi-download"></i> Download
                                    </a>
                                    <button class="btn btn-sm btn-danger delete-document"
                                            data-document-id="<?= $doc['id'] ?>">
                                        <i class="bi bi-trash"></i> Delete
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Upload Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="uploadForm" method="post" enctype="multipart/form-data">
                <input type="hidden" name="entity_id" value="<?= $entityId ?>">
                <input type="hidden" name="entity_type" value="<?= $entityType ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Upload Document</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Document Type</label>
                        <select name="document_type" class="form-select" required>
                            <option value="">Select Type</option>
                            <option value="agreement">Agreement</option>
                            <option value="id_proof">ID Proof</option>
                            <option value="financial_statement">Financial Statement</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Document File</label>
                        <input type="file" name="document_file" class="form-control" required>
                        <small class="text-muted">Max size: 5MB (PDF, JPG, PNG)</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>