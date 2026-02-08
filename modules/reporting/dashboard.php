<?php
require_once __DIR__ . '/../../includes/header.php';

// Check permissions
$allowedModules = ['accounting', 'inventory', 'pos', 'donations', 'loans', 'payroll', 'school-fees'];
$userRole = $_SESSION['user_role'];
?>

<div class="container-fluid">
    <h2>Reporting Dashboard</h2>

    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5>Quick Reports</h5>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <?php if (hasPermission('accountant') || $userRole === 'admin'): ?>
                            <a href="accounting.php?report=trial_balance" class="list-group-item list-group-item-action">
                                <i class="fas fa-balance-scale"></i> Trial Balance
                            </a>
                            <a href="accounting.php?report=profit_loss" class="list-group-item list-group-item-action">
                                <i class="fas fa-chart-line"></i> Profit & Loss
                            </a>
                            <a href="accounting.php?report=cash_flow" class="list-group-item list-group-item-action">
                                <i class="fas fa-money-bill-wave"></i> Cash Flow
                            </a>
                        <?php endif; ?>

                        <?php if (hasPermission('inventory') || $userRole === 'admin'): ?>
                            <a href="inventory.php?report=stock_levels" class="list-group-item list-group-item-action">
                                <i class="fas fa-boxes"></i> Stock Levels
                            </a>
                            <a href="inventory.php?report=movement" class="list-group-item list-group-item-action">
                                <i class="fas fa-exchange-alt"></i> Inventory Movement
                            </a>
                        <?php endif; ?>

                        <?php if (hasPermission('pos') || $userRole === 'admin'): ?>
                            <a href="pos.php?report=daily_sales" class="list-group-item list-group-item-action">
                                <i class="fas fa-shopping-cart"></i> Daily Sales
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header bg-info text-white">
                    <h5>Saved Reports</h5>
                </div>
                <div class="card-body">
                    <div class="list-group" id="savedReportsList">
                        <!-- Saved reports will be loaded via AJAX -->
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs" id="reportTabs">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="tab" href="#customReport">Custom Report</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#scheduledReports">Scheduled</a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="customReport">
                            <form id="customReportForm">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Module</label>
                                            <select class="form-control" name="module" id="moduleSelect">
                                                <option value="">-- Select Module --</option>
                                                <?php foreach ($allowedModules as $module): ?>
                                                    <?php if (hasPermission($module) || $userRole === 'admin'): ?>
                                                        <option value="<?= $module ?>"><?= ucfirst(str_replace('-', ' ', $module)) ?></option>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Report Type</label>
                                            <select class="form-control" name="report_type" id="reportTypeSelect" disabled>
                                                <option value="">-- Select Module First --</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div id="reportParameters">
                                    <!-- Parameters will be loaded dynamically -->
                                </div>

                                <div class="row mt-3">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Output Format</label>
                                            <select class="form-control" name="output_format">
                                                <option value="html">Web View</option>
                                                <option value="pdf">PDF</option>
                                                <option value="excel">Excel</option>
                                                <option value="csv">CSV</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6 text-right pt-4">
                                        <button type="button" class="btn btn-secondary" id="saveReportBtn">
                                            <i class="fas fa-save"></i> Save Report
                                        </button>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-chart-bar"></i> Generate Report
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="tab-pane fade" id="scheduledReports">
                            <div class="table-responsive">
                                <table class="table table-striped" id="scheduledReportsTable">
                                    <thead>
                                        <tr>
                                            <th>Report Name</th>
                                            <th>Frequency</th>
                                            <th>Last Sent</th>
                                            <th>Next Send</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Scheduled reports will be loaded via AJAX -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Load saved reports
    function loadSavedReports() {
        $.get('/api/reporting.php?action=get_saved_reports', function(data) {
            let html = '';
            data.reports.forEach(report => {
                html += `
                    <a href="#" class="list-group-item list-group-item-action report-item"
                       data-id="${report.id}" data-module="${report.module}"
                       data-type="${report.report_type}" data-params='${JSON.stringify(report.parameters)}'>
                        <strong>${report.name}</strong><br>
                        <small>${report.module} - ${report.report_type}</small>
                    </a>
                `;
            });
            $('#savedReportsList').html(html || '<div class="text-muted">No saved reports</div>');
        }, 'json');
    }

    // Load scheduled reports
    function loadScheduledReports() {
        $('#scheduledReportsTable').DataTable({
            ajax: '/api/reporting.php?action=get_scheduled_reports',
            columns: [
                { data: 'report_name' },
                { data: 'frequency' },
                { data: 'last_sent' },
                { data: 'next_send' },
                {
                    data: 'is_active',
                    render: function(data) {
                        return data ? '<span class="badge badge-success">Active</span>' :
                                     '<span class="badge badge-secondary">Inactive</span>';
                    }
                },
                {
                    data: 'id',
                    render: function(data) {
                        return `
                            <button class="btn btn-sm btn-info edit-schedule" data-id="${data}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger delete-schedule" data-id="${data}">
                                <i class="fas fa-trash"></i>
                            </button>
                        `;
                    },
                    orderable: false
                }
            ]
        });
    }

    // Load report types when module is selected
    $('#moduleSelect').change(function() {
        const module = $(this).val();
        if (module) {
            $.get(`/api/reporting.php?action=get_report_types&module=${module}`, function(data) {
                let options = '<option value="">-- Select Report Type --</option>';
                data.types.forEach(type => {
                    options += `<option value="${type}">${type.replace(/_/g, ' ')}</option>`;
                });
                $('#reportTypeSelect').html(options).prop('disabled', false);
            }, 'json');
        } else {
            $('#reportTypeSelect').html('<option value="">-- Select Module First --</option>')
                                 .prop('disabled', true);
            $('#reportParameters').html('');
        }
    });

    // Load parameters when report type is selected
    $('#reportTypeSelect').change(function() {
        const module = $('#moduleSelect').val();
        const reportType = $(this).val();

        if (module && reportType) {
            $.get(`/api/reporting.php?action=get_report_parameters&module=${module}&type=${reportType}`,
                function(data) {
                    let html = '<h5>Report Parameters</h5><div class="row">';

                    data.parameters.forEach(param => {
                        html += `
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>${param.label}</label>
                                    ${renderParameterInput(param)}
                                </div>
                            </div>
                        `;
                    });

                    html += '</div>';
                    $('#reportParameters').html(html);

                    // Initialize date pickers if any
                    $('.datepicker').datepicker({
                        format: 'yyyy-mm-dd',
                        autoclose: true
                    });
                }, 'json');
        }
    });

    // Helper function to render parameter inputs
    function renderParameterInput(param) {
        switch (param.type) {
            case 'date':
                return `<input type="text" class="form-control datepicker" name="params[${param.name}]">`;
            case 'select':
                let options = param.options.map(opt =>
                    `<option value="${opt.value}">${opt.label}</option>`
                ).join('');
                return `<select class="form-control" name="params[${param.name}]">${options}</select>`;
            case 'checkbox':
                return `<input type="checkbox" class="form-check-input" name="params[${param.name}]">`;
            default:
                return `<input type="${param.type}" class="form-control" name="params[${param.name}]">`;
        }
    }

    // Save report button
    $('#saveReportBtn').click(function() {
        const module = $('#moduleSelect').val();
        const reportType = $('#reportTypeSelect').val();

        if (!module || !reportType) {
            alert('Please select module and report type first');
            return;
        }

        // Prompt for report name
        const reportName = prompt('Enter a name for this report:');
        if (reportName) {
            const formData = $('#customReportForm').serialize();

            $.post('/api/reporting.php?action=save_report',
                formData + '&report_name=' + encodeURIComponent(reportName),
                function(response) {
                    if (response.success) {
                        alert('Report saved successfully!');
                        loadSavedReports();
                    } else {
                        alert('Error: ' + response.message);
                    }
                }, 'json');
        }
    });

    // Load saved report when clicked
    $(document).on('click', '.report-item', function(e) {
        e.preventDefault();

        const module = $(this).data('module');
        const reportType = $(this).data('type');
        const params = $(this).data('params');

        // Set module and report type
        $('#moduleSelect').val(module).trigger('change');

        // Wait for report types to load
        setTimeout(() => {
            $('#reportTypeSelect').val(reportType).trigger('change');

            // Wait for parameters to load
            setTimeout(() => {
                // Set parameter values
                for (const [name, value] of Object.entries(params)) {
                    $(`[name="params[${name}]"]`).val(value);

                    // Handle datepicker fields
                    if ($(`[name="params[${name}]"]`).hasClass('datepicker')) {
                        $(`[name="params[${name}]"]`).datepicker('update', value);
                    }
                }
            }, 500);
        }, 500);
    });

    // Initial load
    loadSavedReports();
    loadScheduledReports();
});
</script>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>