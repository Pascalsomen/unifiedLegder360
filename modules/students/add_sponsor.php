<?php include '../../includes/header.php'; ?>
<div class="container mt-4">
    <h4>Add Sponsor</h4>
    <form action="process_add_sponsor.php" method="POST">
        <div class="mb-2">
            <label>Full Name</label>
            <input type="text" name="full_name" class="form-control" required>
        </div>
        <div class="mb-2">
            <label>Email</label>
            <input type="email" name="email" class="form-control">
        </div>
        <div class="mb-2">
            <label>Phone</label>
            <input type="text" name="phone" class="form-control">
        </div>
        <div class="mb-2">
            <label>Address</label>
            <textarea name="address" class="form-control"></textarea>
        </div>

                <?php if(hasPermission(30)){?>

                    <button class="btn btn-success" type="submit">Add Sponsor</button>
        <?php }else{
           Echo "You do not have access to add new sponsor";
        } ?>

    </form>
</div>
<?php include '../../includes/footer.php'; ?>