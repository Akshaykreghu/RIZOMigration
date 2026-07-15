<style>
    /* Change table font and spacing */
#promotion_list {
   font-famly:'Source Sans Pro', 'Helvetica Neue', Helvetica, Arial, sans-serif;
    font-size: 14px;
    border: 1px solid #ccc;
}

/* Style table headers */
#promotion_list thead th {
    background-color: #0c5e8e;
    color: white;
    text-align: center;
}

/* Style rows */
#promotion_list tbody td {
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
th,td{
    text-align:center;
}
.dt-buttons{
    margin-top:0px !important;
}


</style>

<table class="table no-margin" id="promotion_list">
    <thead>
        <tr>
<!--edited by sinsiya 13-06-2024-->
            <th>Sl No</th>
            <th>Employee ID</th>
            <th>Employee Name</th>
            <th>Branch </th>
             <th>Designation</th>
              <th>Department</th>
            <th>Status</th>
        </tr>
    </thead>
   
       <tbody>
<?php $i = 1;
foreach ($promotionList as $row): ?>
    <tr>
        <td><?= $i++; ?></td>
         <td><?= h($row['emp_info']['employee_id']); ?></td>
        <td><?= h($row['emp_details']['first_name'] . ' ' . $row['emp_details']['last_name']); ?></td>
     <td><?= h(!empty($row['Promotion']['emp_branch']) ? $row['Promotion']['emp_branch'] : $row['Branch']['branch_name']); ?></td>
<td><?= h(!empty($row['Promotion']['designation']) ? $row['Promotion']['designation'] : $row['emp_info']['designation']); ?></td>
<td><?= h(!empty($row['Promotion']['emp_dept']) ? $row['Promotion']['emp_dept'] : $row['emp_info']['department']); ?></td>

        <td><span class="label label-success"><?= h($row['Promotion']['promotion_status']); ?></span></td>
    </tr>
<?php endforeach; ?>
</tbody>

</table>

<script>
    
      $(document).ready(function () {
        //edited by athira on 18-08-2025
        var table = $('#promotion_list').DataTable({
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
