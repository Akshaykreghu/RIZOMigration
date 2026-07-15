<style>
    #location {
    font-famly:'Source Sans Pro', 'Helvetica Neue', Helvetica, Arial, sans-serif;
    font-size: 14px;
    border: 1px solid #ccc;
}

#location_wrapper{
    margin-top: 30px !important;
}
/* Style table headers */
#location thead th {
    background-color: #337ab7;
    color: white;
    text-align: center;
}

/* Style rows */
#location tbody td {
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
<div class="row">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <fieldset>
            <div class="table-responsive">
                <table class="table no-margin" id="location" >
                    <thead>
                        <tr>
                            <th>Sl No</th>
                            <th>Employee Name</th>
                            <th>Employee ID</th>
                            <th>Branch</th>
                            <th>Action</th>
                            <th>Date & Time</th>
                            <th>Location</th>
                            
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 0; ?>
                        <!-- edited by athira on 08-09-2025 -->
                        <?php  foreach($arr_location as $val){
                         ?>
                            <tr>
                                <td><?php echo $i; ?></td>
                                 <td><?php echo isset($val[1]) ? $val[1] : ' ' ?></td>
                                 <td><?php echo isset($val[2]) ? $val[1] : ' ' ?></td>
                                 <td><?php echo isset($val[3]) ? $val[3] : ' ' ?></td>
                                 <td><?php echo isset($val[4]) ? $val[4] : ' ' ?></td>
                                 <td><?php echo isset($val[5]) ? $val[5] : ' ' ?></td>
                                <td><?php echo isset($val[6]) ? $val[6] : '' ; ?></td>
                             </tr>
                            <?php $i++;} ?>
                        <!-- end -->
                    </tbody>
                </table>
            </div>
        </fieldset>              
    </div>
</div>
        
<script> 
$(document).ready(function () {
    var table = $('#location').DataTable({
        "paging": true,
        "lengthChange": false,
        "searching": true,
        dom: "<'row'<'col-sm-6'B><'col-sm-6'f>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row'<'col-sm-5'i><'col-sm-7'p>>",
        buttons: [
            {
                extend: 'print',
                messageTop: 'My Payroll Master Customer Visits Report.',
                title: 'My Payroll Master - Customer Visits Report',
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
                messageTop: 'My Payroll Master Customer Visits Report.',
                title: 'My Payroll Master - Customer Visits Report',
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
                messageTop: 'My Payroll Master Customer Visits Report.',
                title: 'My Payroll Master - Customer Visits Report',
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
                targets: 0,
                orderable: false,
                searchable: false
            }
        ]
    });

    // 🔥 Update SL No in datagrid after search, order, or page change
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
</script>

