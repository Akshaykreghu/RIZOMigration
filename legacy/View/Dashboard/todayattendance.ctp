
<div class="modal-body" style="overflow-y: auto;">
    <legend>Today's Attendance</legend>
    <div class="row">
        <div class="col-md-12">
            <div class="box ">
                  
                <div class="box-body">
                 
                    <br>
                    <fieldset>
			

			   <div class="table-responsive"style="margin-top: -25px;">
                                        <table class="table no-margin" id="todayattandence">
                                            <!--</span>-->
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
                    <br>
                  
                </div>
                 <!-- /.box-body -->
            </div>
        </div>
    </div>    

</div>
<script> 
    $(document).ready(function () {
         $('#todayattandence').DataTable({
            "paging": true,
            "lengthChange": false,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "ajax": livesite + "Dashboard/listtodayattendance",
        });
    });
</script>
