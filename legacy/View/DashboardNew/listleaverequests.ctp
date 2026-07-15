<style>
        /* Change table font and spacing */
#empleaverequests {
    font-famly:'Source Sans Pro', 'Helvetica Neue', Helvetica, Arial, sans-serif;
    font-size: 14px;
    border: 1px solid #ccc;
}

/* Style table headers */
#empleaverequests thead th {
    background-color: #0c5e8e;
    color: white;
    text-align: center;
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
th,td{
    text-align:center;
}
.dt-buttons{
    margin-top:0px !important;
}
</style>
<table class="table no-margin" id="empleaverequests">
    <thead>
        <tr>
<!--edited by sinsiya 13-06-2024-->
            <th>Sl No</th>
             <th>Employee ID</th>
            <th>Employee Name</th>
            <th>Branch </th>
            <th>Leave type</th>
            <th>Applied date</th>
            <th>From date</th>
            <th>To date</th>
            <th>Leave status</th>
        </tr>
    </thead>
    <tbody>
        <?php $i=1; foreach ($results as $key => $leaverequest) { ?>
            <tr>
                <td style="padding-left: 20px !important;"><?php echo $i;?></td>
                <td><?php echo $leaverequest["emp_info"]["employee_id"]; ?></td>
                 <td><?php echo $leaverequest[0]["emp_name"]; ?></td>
                <td><?php echo $leaverequest["Branch"]["branch_name"]; ?></td>
                <td><?php echo $leaverequest["SalaryHeadItems"]["leave_type"]; ?> </td>
  <!-- edited by ASHIN ON 28-06-24 -->
                <td><?php echo date("d-m-Y", strtotime($leaverequest["LeaveRequests"]["applied_date"])); ?></td>
                <td><?php echo date("d-m-Y", strtotime($leaverequest["LeaveRequests"]["FROMDATE"])); ?></td>
                <td><?php echo date("d-m-Y", strtotime($leaverequest["LeaveRequests"]["TODATE"])); ?></td>
                <td><span class="label label-success"><?php echo strtoupper($leaverequest["LeaveRequests"]["LEAVESTATUS"]); ?></span></td>

            </tr>
        <?php $i++;} ?>
    </tbody>
</table>

<script>
    $(document).ready(function () {
        //edited by athira on 18-08-2025
      var table=  $('#empleaverequests').DataTable({
            "paging": true,
            "lengthChange": false,
            "searching": true,
             dom: "<'row'<'col-sm-6'B><'col-sm-6'f>>" + 
                 "<'row'<'col-sm-12'tr>>" + 
                 "<'row'<'col-sm-5'i><'col-sm-7'p>>",
           buttons: [
    {
        extend: 'print',
        messageTop: 'My Payroll Master Leave Requests Report.',
        title: 'My Payroll Master - Leave Requests',
        exportOptions: {
            columns: ':visible',
            format: {
                body: function (data, row, column, node) {
                    // For SL No column
                    if (column === 0) return row + 1;
                    
                    // For other columns, strip HTML
                    return typeof data === 'string' ? data.replace(/<[^>]*>/g, '') : data;
                }
            }
        }
    },
    {
        extend: 'pdf',
        messageTop: 'My Payroll Master Leave Requests Report.',
        title: 'My Payroll Master - Leave Requests',
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
        messageTop: 'My Payroll Master Leave Requests Report.',
        title: 'My Payroll Master - Leave Requests',
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

    columnDefs: [{
        targets: 0,
        searchable: false,
        orderable: false,
    }]
            
        });

          table.on('order.dt search.dt draw.dt', function () {
        table.column(0, { search: 'applied', order: 'applied' }).nodes().each(function (cell, i) {
            cell.innerHTML = i + 1;
        });
    }).draw();

        //end
        $('.buttons-print').ready(function () {
            $('.buttons-print').html('<li class="fa fa-print"></li>').addClass('btn-primary').addClass('btn');
            
        });
        $('.buttons-pdf').html('<li class="fa fa-file-pdf-o"></li>').addClass('btn-danger').addClass('btn');
        ;
        $('.buttons-excel').html('<li class="fa fa-file-excel-o"></li>').addClass('btn-success').addClass('btn');
    });
</script>
