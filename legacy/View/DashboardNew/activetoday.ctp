<style>
        /* Change table font and spacing */
#todays_present_details {
    font-family: 'Inter', sans-serif;
    font-size: 14px;
    border: 1px solid #ccc;
}

/* Style table headers */
#todays_present_details thead th {
    background-color: #0c5e8e;
    color: white;
    text-align: center;
}

/* Style rows */
#todays_present_details tbody td {
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
    <legend>Employees Active Now</legend>
    <div class="row">
        <div class="col-md-12">
            <div class="box ">
                <div class="box-body">
                    <fieldset>
			    <table class="table table-bordered todayattandence" id="todays_present_details">
                            <thead>
                              <tr>
                                  <th >Sl.No.</th>
                                  <th style="width: 175px;">Employee Name</th>
                              </tr>
                            </thead>
                            <tbody>
                               <?php $i=0; foreach ($results as $val) {
                   $i += 1; 
                   
                  ?>
                                    
                                        <tr>
                                            <td><?php echo $i;?></td>
                                            <td><?php echo $val['present_today']['EmpName']; ?></td>
                                            
                                             <?php
                                            //$fromserver = $val['present_today']['LOGDATE'];
                                            //date_default_timezone_set('UTC');
                                            //$fs = strtotime($fromserver);
                                            //2014-07-02T05:13:45z
                                            //echo $fromserver_formated = date("d-M-Y  h:i A",strtotime($val['present_today']['LOGDATE']));  ?>
                                     
                                        </tr>
                            <?php  } ?>
                            </tbody>
                        </table>
		
                    </fieldset>
                </div>
                 <!-- /.box-body -->
            </div>
        </div>
    </div>    

</div>
<script> 
//    edited by athira on 18-08-2025 
    $(document).ready(function () {
    var table = $('#todays_present_details').DataTable({
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
                messageTop: 'My Payroll Master Employees Active Now Report.',
                title: 'My Payroll Master - Employees Active Now Report',
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
                messageTop: 'My Payroll Master Employees Active Now Report.',
                title: 'My Payroll Master - Employees Active Now Report',
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
                title: 'My Payroll Master Employees Active Now Report',
                exportOptions: {
                    columns: ':visible',
                    format: {
                        body: function (data, row, column, node) {
                            if (column === 0) return row + 1;
                            return typeof data === 'string' ? data.replace(/<[^>]*>/g, '') : data;
                        }
                    }
                },
                // Keep your existing customize function for Excel styling
                customize: function(xlsx) {
                    var sSh = xlsx.xl['styles.xml'];
                    var lastXfIndex = $('cellXfs xf', sSh).length - 1;
                    var n1 = '<numFmt formatCode="##0.0000%" numFmtId="300"/>';
                    var s1 = '<xf numFmtId="300" fontId="0" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1" xfId="0" applyNumberFormat="1"/>';
                    var s2 = '<xf numFmtId="0" fontId="2" fillId="2" borderId="0" applyFont="1" applyFill="1" applyBorder="1" xfId="0" applyAlignment="1">'+
                             '<alignment horizontal="center"/></xf>';
                    var s3 = '<xf numFmtId="4" fontId="2" fillId="0" borderId="0" applyFont="1" applyFill="1" applyBorder="1" xfId="0" applyNumberFormat="1"/>';
                    var s4 = '<xf numFmtId="0" fontId="2" fillId="2" borderId="0" applyFont="1" applyFill="1" applyBorder="1" xfId="0" applyAlignment="1">'+
                             '<alignment horizontal="center" wrapText="1"/></xf>';
                    sSh.childNodes[0].childNodes[0].innerHTML += n1;
                    sSh.childNodes[0].childNodes[5].innerHTML += s1 + s2 + s3 + s4;
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

    // 🔥 Update SL No dynamically in table on search/order/pagination
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
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>