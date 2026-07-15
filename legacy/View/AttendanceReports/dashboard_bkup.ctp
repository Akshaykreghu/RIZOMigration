<style>
    div.inflow {

    }
    div.positioner {position: absolute; right: 0;} /*may not be needed: see below*/
    div.fixed {
        overflow: auto;

    }
    /*tr td.fix{
        position:fixed;
    }*/
    
    
</style>
    <div class="modal-body">
        <legend style="text-align: center; ">Employee attendance summary report - <?php echo $report_month; ?></legend>
        <div class="row">
            <div class="col-md-12">
                <div class="box ">
                    

                    <div class="box-body">
                                    <fieldset>
                                        
                                        
                                        <div class="row">
                                            <div class="col-md-12">

                                            </div>
                                        </div>


                                    </fieldset>
                        
                                    <br>
                                    <fieldset>

                                        <div class="inflow">
                                            <div class="fixed">
                                                <div class="table-responsive">
                                                    <table class="table no-margin" id="todayattandence">
                                                        <thead>
                                                            <tr>
                                                                <th>Name</th>
                                                                <th>Branch</th>
                                                                <th>Date</th>
                                                                <th>Time</th>
                                                                <th>Check-In/Out</th>
                                                                <th>Location</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php $data = $arr_leavepolicydetails_for_template['data']; ?>
                                                            <?php foreach ($data as $val){ ?>
                                                            <tr>
                                                                <td><?php echo $val[0]; ?></td>
                                                                <td><?php echo $val[1]; ?></td>
                                                                <td><?php echo $val[2]; ?></td>
                                                                <td><?php echo $val[3]; ?></td>
                                                                <td><?php echo $val[4]; ?></td>
                                                                <td><?php echo $val[5]; ?></td>
                                                            </tr>
                                                            
                                                            <?php } ?>
                                                            
                                                        </tbody>
                                                    </table>
                                                </div> 
                                            </div>
                                        </div>
                                    </fieldset>
                                    <br>

     <!-- /.box-body -->
                </div>
            </div>
        </div>   
    </div>
    <?php //echo '<style>'.file_get_contents("css/pdfbootstrap.css").'</style>';  ?>
    <script>
        $(document).ready(function () {
             
        $('#todayattandence').DataTable({
            "paging": true,
            "lengthChange": false,
            "searching": true,
            "ordering": true,
            dom: 'Bfrtip',
            buttons: [
                {
                     extend: 'print',
                     messageTop: 'My payroll Master Employee Check-in/out logs - <?php echo $report_month; ?>',
                     messageBottom: null,
                     title: 'My Payroll Master - Employee Check-in/out logs'
                },
                {
                     extend: 'pdf',
                     messageTop: 'My payroll Master Employee Check-in/out logs - <?php echo $report_month; ?>',
                     messageBottom: null,
                     title: 'My Payroll Master - Employee Check-in/out logs'
                },
                {
                     extend: 'excel',
                     messageTop: 'My payroll Master Employee Check-in/out logs - <?php echo $report_month; ?>',
                     messageBottom: null,
                     title: 'My payroll Master - Employee Check-in/out logs'
                }
            ],
            "info": true,
            "autoWidth": false,
            "initComplete": function () {
                //actions
            },
//             "createdRow": function ( row, data, index ) {
//                if ( data[5].replace(/[\$,]/g, '') * 1 > 150000 ) {
//                    $('td', row).eq(5).addClass('highlight');
//                }
//            },
            "scrollCollapse": true,
        });
        
        
        $('.buttons-print').ready(function(){
            $('.buttons-print').html('<li class="fa fa-print"></li>').addClass('btn-primary').addClass('btn');;
        });
        $('.buttons-pdf').html('<li class="fa fa-file-pdf-o"></li>').addClass('btn-danger').addClass('btn');;
        $('.buttons-excel').html('<li class="fa fa-file-excel-o"></li>').addClass('btn-success').addClass('btn');
    });
    </script>