


<style>
td,th
{
    text-align: left;
}

    /* Change table font and spacing */
#todays_absent_details {
   font-famly:'Source Sans Pro', 'Helvetica Neue', Helvetica, Arial, sans-serif;
    font-size: 14px;
    border: 1px solid #ccc;
}

/* Style table headers */
#todays_absent_details thead th {
    background-color: #337ab7;
    color: white;
    text-align: center;
}

/* Style rows */
#todays_absent_details tbody td {
    padding: 8px 10px;
}

/* Search box */
.dataTables_filter input {
    height: 35px;
    border-radius: 4px;
    border: 1px solid #ccc;
    padding: 6px 10px;
}

/* Export buttons */
.dt-buttons .btn {
    margin-right: 5px;
    font-weight: bold;
}

.dt-buttons{
    margin-top:0 !important;
}

</style>

<div class="modal-body" style="overflow-y: auto;">
    <form id="form-showreport" class="form-horizontal" method="post" action="" >
    <legend>Absent Employees</legend>
    <div class="row">
        <div class="col-md-12">
            <div class="box ">
                <div class="box-body">
                    <fieldset>
			    <table class="table table-bordered" id="todays_absent_details" >
                            <thead>
                              <tr>
                                  <th> Sl. No.</th>
                                 <th style="width: 175px;">Employee Name</th>
                              </tr>
                            </thead>
                            <tbody>
                                <?php $i=0; foreach ($results1 as $val) {
                                   $i++;  ?>
                                        <tr> 
                                         <td><?php echo $i;?></td>
                                            <td><?php echo $val['emp_details']['first_name'].' '.$val['emp_details']['last_name']. ' - ' . $val['emp_proff']['emp_company_id']; ?></td>
                                          </tr>
                            <?php  } ?>
                            </tbody>
                        </table>
                    </fieldset>
                    <br>
                </div>
                 <!-- /.box-body -->
            </div>
        </div>
    </div>
</form>
    <div class="row">
        <div class="form-group">
            <div class="col-md-12" align="right">
                <a href="#" class="btn btn-default" onclick="downloadReport();" ><i class="icon-file"></i>Download As PDF</a>
              <!--  <a href="#" class="btn btn-default" onclick="downloadReport('shiftpolicy','excel');"><i class="icon-file"></i>Download As Excel</a> -->
            </div>
        </div>
    </div>
</div>
<script>
function downloadReport(type,mode){
    var mode= 1;
    if(mode != ''){
        $('#form-showreport').attr('action',livesite+'DashboardNew/absenttoday/'+mode);
        $('#form-showreport').submit();
    }
    else{
        return false;
    }
}
</script>
<script> 
//   edited by athira on 18-08-2025 
    $(document).ready(function () {
    var table = $('#todays_absent_details').DataTable({
        paging: true,
        lengthChange: false,
        searching: true,
        ordering: true,
        info: true,
        autoWidth: false,
        dom: "<'row'<'col-sm-6'B><'col-sm-6'f>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row'<'col-sm-5'i><'col-sm-7'p>>",
        buttons: [
            {
                extend: 'print',
                messageTop: 'My Payroll Master Employees Absent Today Report.',
                title: 'My Payroll Master - Absent Today Report',
                exportOptions: {
                    columns: ':visible',
                    format: {
                        body: function (data, row, column, node) {
                            if (column === 0) return row + 1; // SL No
                            return typeof data === 'string' ? data.replace(/<[^>]*>/g, '') : data;
                        }
                    }
                }
            },
            {
                extend: 'pdf',
                messageTop: 'My Payroll Master Employees Absent Today Report.',
                title: 'My Payroll Master - Absent Today Report',
                exportOptions: {
                    columns: ':visible',
                    format: {
                        body: function (data, row, column, node) {
                            if (column === 0) return row + 1;
                            return typeof data === 'string' ? data.replace(/<[^>]*>/g, '') : data;
                        }
                    }
                }
            },
            {
                extend: 'excel',
                messageTop: 'My Payroll Master Employees Absent Today Report.',
                title: 'My Payroll Master - Absent Today Report',
                exportOptions: {
                    columns: ':visible',
                    format: {
                        body: function (data, row, column, node) {
                            if (column === 0) return row + 1;
                            return typeof data === 'string' ? data.replace(/<[^>]*>/g, '') : data;
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
        ]
    });

    // 🔥 Update SL No dynamically on search/order/pagination
    table.on('order.dt search.dt draw.dt', function () {
        table.column(0, { search: 'applied', order: 'applied', page: 'current' })
            .nodes()
            .each(function (cell, i) {
                cell.innerHTML = i + 1;
            });
    }).draw();

    // Style buttons with icons
    $('.buttons-print').html('<li class="fa fa-print"></li>').addClass('btn btn-primary');
    $('.buttons-pdf').html('<li class="fa fa-file-pdf-o"></li>').addClass('btn btn-danger');
    $('.buttons-excel').html('<li class="fa fa-file-excel-o"></li>').addClass('btn btn-success');
});
//end
</script>

<!-- DataTables JS -->
<!-- <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script> -->

<!-- DataTables CSS -->
<!-- <link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.dataTables.min.css">   -->

<!-- edited by athira on 23-06-2025 -->
<!-- <script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script> -->

<!-- Dependencies for PDF Export -->
<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script> -->