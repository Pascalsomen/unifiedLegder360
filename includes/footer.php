        </div> <!-- Close container-fluid from header -->

        <!-- Footer -->
        <footer class="footer mt-auto py-3 bg-light">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-6">
                        <span class="text-muted">
                            &copy; <?= date('Y') ?> Unified Legder 360 v2.1.2 | Powered  BY <a target="_blank" href="https://panatechrwanda.com/">Panatech Ltd</a>
                        </span>
                    </div>
                    <div class="col-md-6 text-end">
                        <span class="text-muted">
                            <?= $currentUser['full_name'] ?? 'Guest' ?> |
                            <?= date('Y-m-d H:i:s') ?> |
                            <span id="systemLoad"></span>
                        </span>
                    </div>
                </div>
            </div>
        </footer>

        <!-- JavaScript Libraries -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


        <!-- Custom JS -->
<script src="/assets/js/main.js"></script>
<script>
function exportToExcel(tableID, filename = '') {
    let table = document.getElementById(tableID);
    let workbook = XLSX.utils.table_to_book(table, {sheet: "Sheet1"});
    return XLSX.writeFile(workbook, filename ? `${filename}.xlsx` : 'report.xlsx');
}
</script>
<script>

$(document).ready(function() {
    $('table').each(function() {
    if (!$.fn.DataTable.isDataTable(this)) {
        $(this).DataTable({

            pageLength: 10,

            responsive: true,
        });
    }
});
});
</script>
        <!-- Page-specific JS -->
        <?php if (isset($customJS)): ?>
            <script>
                <?= $customJS ?>
            </script>
        <?php endif; ?>

        <!-- System Load Indicator -->
        <script>
            function updateSystemLoad() {
                $.get('/api/system/load.php', function(data) {
                    $('#systemLoad').html('Load: ' + data.load + '%');
                }, 'json');
            }

            // Update every 30 seconds
            updateSystemLoad();
            setInterval(updateSystemLoad, 30000);

            // Initialize plugins
            $(document).ready(function() {
                // Initialize Select2
                $('select').select2({
                    width: '100%',
                    theme: 'bootstrap'
                });

                // // Initialize DataTables
                // $('.datatable').DataTable({
                //     responsive: true,
                //     dom: '<"top"lf>rt<"bottom"ip>',
                //     language: {
                //         search: "_INPUT_",
                //         searchPlaceholder: "Search...",
                //     }
                // });

                // Update clock every second
                function updateClock() {
                    $('#current-time').text(new Date().toLocaleTimeString());
                }
                setInterval(updateClock, 1000);
                updateClock();
            });
        </script>
        <?php if (!empty($_SESSION['toast'])): ?>
<script>
  window.addEventListener('DOMContentLoaded', () => {
    const toastEl = document.getElementById('liveToast');
    const toast = new bootstrap.Toast(toastEl, { delay: 3000 });
    toast.show();
  });
</script>
<?php unset($_SESSION['toast']); endif; ?>

<script>
  window.addEventListener('load', function () {
    setTimeout(() => {
      const loader = document.getElementById('loaderOverlay');
      loader.classList.add('fade-out');
      setTimeout(() => loader.style.display = 'none', 500); // Wait for fade to finish
    }, 1000); // 1 second delay
  });
</script>
<script>
  window.addEventListener('load', function () {
    setTimeout(() => {
      const loader = document.getElementById('loaderOverlay');
      loader.classList.add('fade-out');
      setTimeout(() => loader.style.display = 'none', 500);
    }, 1000); // Show for 1 second
  });
</script>

    </body>
</html>