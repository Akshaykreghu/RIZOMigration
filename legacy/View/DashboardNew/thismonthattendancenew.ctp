<!-- edited by athira on 18-08-2025 -->
<div class="attendance-table-wrapper" style="font-family: 'Inter' !important;" >
<div style="display:flex;justify-content:space-between;align-items:center;margin:30px 0 24px 0;">
<div>
    <legend style="border:none;margin:0px;">This Month Attendance </legend>
</div>
<div>
        <!-- Add Buttons Here -->
        <button type="button" class="btn btn-danger"
        onclick="openAttendanceReport('Latein','Month')">
  Late In
</button>

<button type="button" class="btn btn-success"
        onclick="openAttendanceReport('Earlyin','Month')">
  Early In
</button>

<button type="button" class="btn btn-success"
        onclick="openAttendanceReport('Lateout','Month')">
  Late Out
</button>

<button type="button" class="btn btn-danger"
        onclick="openAttendanceReport('Earlyout','Month')">
  Early Out
</button>

<div class="modal fade" id="attendanceReportModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-body" id="attendanceReportContent">
        <!-- form.ctp will load here -->
      </div>
    </div>
  </div>
</div>
   

</div>
</div>
    <div class="table-responsive" style="margin-top: -12px;">
        <table class="table no-margin" id="thismonthattandence">
            <thead>
                <tr>
                    <th style=" background-color: #0c5e8e;color: white;text-align: center;">Sl No</th>
                    <th style=" background-color: #0c5e8e;color: white;text-align: center;">Name</th>
                    <th style=" background-color: #0c5e8e;color: white;text-align: center;">Employee ID</th>
                    <th style=" background-color: #0c5e8e;color: white;text-align: center;">Branch</th>
                    <th style=" background-color: #0c5e8e;color: white;text-align: center;">Date</th>
                    <th style=" background-color: #0c5e8e;color: white;text-align: center;">Time</th>
                    <th style=" background-color: #0c5e8e;color: white;text-align: center;">Check-In/Out</th>
                    <th style=" background-color: #0c5e8e;color: white;text-align: center;">Location</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>

<script>
function openAttendanceReport(type, month) {
    $('#attendanceReportModal .modal-title').text(type + " Report - " + month);

    // Base URL from CakePHP (no manual double slashes)
    var baseUrl = "<?php echo $this->Html->url(['controller' => 'DashboardNew', 'action' => 'form']); ?>";

    // Build the final URL cleanly
    var url = baseUrl.replace(/\/$/, '') + "/" + type + "/" + month;

    $('#attendanceReportContent').load(url, function () {
        if ($.fn.DataTable.isDataTable('#punchLateInOut')) {
            $('#punchLateInOut').DataTable().destroy();
        }

      var table = $('#punchLateInOut').DataTable({
    paging: true,
    lengthChange: false,
    searching: true,
    dom: 'Bfrtip',
    buttons: [
        { 
            extend: 'print', 
            title: 'My Payroll Master - Attendance Late In Out Report',
            customize: function (win) {
                // Fix SL No in Print
                $(win.document.body).find('table tbody tr').each(function (index) {
                    $(this).find('td:first').html(index + 1);
                });
            }
        },
        { 
            extend: 'pdf',   
            title: 'My Payroll Master - Attendance Late In Out Report',
            customize: function (doc) {
                // Fix SL No in PDF
                doc.content[1].table.body.forEach(function(row, i) {
                    if (i > 0) { // skip header
                        row[0].text = i; 
                    }
                });
            }
        },
        { 
            extend: 'excel', 
            title: 'My Payroll Master - Attendance Late In Out Report',
            exportOptions: {
        columns: ':visible',
        format: {
            body: function (data, row, column, node) {
                if (column === 0) {
                    // First column = SL No
                    return row + 1;
                }
                return data;
            }
        }
    }
        }
    ],
    columnDefs: [
        {
            targets: 0, // SL No column
            orderable: false,
            searchable: false
        }
    ],
    initComplete: function () {
        $('.buttons-print')
            .html('<i class="fa fa-print"></i>')
            .addClass('btn btn-primary');
        $('.buttons-pdf')
            .html('<i class="fa fa-file-pdf-o"></i>')
            .addClass('btn btn-danger');
        $('.buttons-excel')
            .html('<i class="fa fa-file-excel-o"></i>')
            .addClass('btn btn-success');
    }
});

// 🔥 Update SL No dynamically in table on search/order/page change
table.on('order.dt search.dt draw.dt', function () {
    table.column(0, { search: 'applied', order: 'applied', page: 'current' })
        .nodes()
        .each(function (cell, i) {
            cell.innerHTML = i + 1;
        });
}).draw();


    });

    $('#attendanceReportModal').modal('show');
}

</script>

<!-- end -->