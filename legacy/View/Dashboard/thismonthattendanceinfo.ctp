<div class="modal-dialog modal-lg" style="overflow-y: auto;position: absolute;float: left;left: 50%;top: 50%;transform: translate(-50%, -4%);" >
    <div class = "modal-content">
        <div class = "modal-header"> 
            <legend>This Month Attendance
                <!-- <button style="background: rgb(12, 94, 142);float: right; margin-left: 5px; " type="button" onclick="Modal('Latein','Month')"  class="btn btn-linkedin btn-xs">Late In</button>
                <button style="background: rgb(12, 94, 142);margin-left: 5px;float: right; " type="button" onclick="Modal('Earlyin','Month')" class="btn btn-linkedin btn-xs">Early In</button>
                <button style="background: rgb(12, 94, 142);margin-left: 5px;float: right; " type="button" onclick="Modal('Lateout','Month')" class="btn btn-linkedin btn-xs">Late Out</button>
                <button style="background: rgb(12, 94, 142); margin-left: 5px;float: right;" type="button" onclick="Modal('Earlyout','Month')" class="btn btn-linkedin btn-xs">Early Out</button> -->
            </legend>
        </div>
        <div class = "modal-body">
            <div class="row">
                <div class="col-md-12 col-sm-12 col-xs-12">
                    <fieldset>
                        <div class="table-responsive" style="margin-top: -25px;">
                            <table class="table no-margin" id="thismonthattandence" style="margin-top: -23px;">
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
                            </table>
                        </div>
                    </fieldset>              
                </div>
            </div>
         </div>
     </div>
</div>
<script> 
 $(document).ready(function () {
  $('#thismonthattandence').DataTable({
            "paging": true,
            "lengthChange": false,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            dom: 'Bfrtip',
            buttons: [
                {
                     extend: 'print',
                     messageTop: 'My Payroll Master Employees Attendance Report.',
                     messageBottom: null,
                     title: 'My Payroll Master - Attendance Report'
                },
                {
                     extend: 'pdf',
                     messageTop: 'My Payroll Master Employees Attendance Report.',
                     messageBottom: null,
                     title: 'My Payroll Master - Attendance Report'
                },
                {
                     extend: 'excel',
                     messageTop: 'My Payroll Master Employees Attendance Report.',
                     messageBottom: null,
                     title: 'My Payroll Master - Attendance Report'
                }
            ],
            
            "ajax": livesite + "dashboard/listthismonthattendance",
            
            
        });
        $('.buttons-print').ready(function () {
        $('.buttons-print').html('<li class="fa fa-print"></li>').addClass('btn-primary').addClass('btn');
        ;
        });
        $('.buttons-pdf').html('<li class="fa fa-file-pdf-o"></li>').addClass('btn-danger').addClass('btn');
        ;
        $('.buttons-excel').html('<li class="fa fa-file-excel-o"></li>').addClass('btn-success').addClass('btn');
   });     
</script>
