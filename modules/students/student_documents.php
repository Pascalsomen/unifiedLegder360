<?php include '../../includes/header.php'; ?>
<div class="container mt-4">
    <h4>Upload Student Document</h4>
    <form action="process_upload_document.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="student_id" value="<?= $_GET['id'] ?>">
        <div class="row mb-3">
            <div class="col-md-6">
                <label>Document Type</label>
                <input type="text" name="document_type" class="form-control">
            </div>
            <div class="col-md-6">
                <label>File</label>
                <input type="file" name="file" class="form-control">
            </div>
        </div>
        <button class="btn btn-primary">Upload</button>
    </form>
</div>
<?php include '../../includes/footer.php'; ?>
