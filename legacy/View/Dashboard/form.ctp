
<div class="modal-body" style="overflow-y: auto;">
    <legend>Report</legend>
    <div class="row">
        <div class="col-md-12">
            <div class="box ">
                  
                <div class="box-body">
                 
                    <br>
                    <fieldset>
			

			    <table class="table table-bordered" id="punchLateInOut">
                            <thead>
                              <tr>
                                  <th>Employee Name</th>
                                  <th>Date</th>
                                  <th>Location</th>
                                 <?php echo isset($results[0]['emp_early_in']['SharpInTime'])? " <th>Sharp In Time </th>" : ''; ?>
                                 <?php echo isset($results[0]['emp_early_in']['InTime'])? " <th>In Time</th> " : ''; ?>
                                  <?php echo isset($results[0]['emp_early_in']['EarlyInTime'])? "<th>Early In Time</th> " : ''; ?>
                                
                                  <?php echo isset($results[0]['emp_early_in']['OffDutyTime'])? " <th>Off Duty Time</th>" : ''; ?>
                                 <?php echo isset($results[0]['emp_early_in']['OutTime'])? " <th>Out Time</th> " : ''; ?>
                                  <?php echo isset($results[0]['emp_early_in']['EarlyOutTime'])? "<th>Early Out Time</th> " : ''; ?>
                           
                                  <?php echo isset($results[0]['emp_early_in']['LateInLimit'])? " <th>Late In Limit</th>" : ''; ?>
                                 <?php echo isset($results[0]['emp_early_in']['LateTime'])? " <th>Late In By</th> " : ''; ?>
                              
                              
                                  <?php echo isset($results[0]['emp_early_in']['LateOutTime'])? " <th>Late Out By</th>" : ''; ?>
                              
                              
                              
                              </tr>
                            </thead>
                            <tbody>
                                <?php $i=0; foreach ($results as $val) {
                   $i += 1; 
                   
                  ?>
                                    
                                        <tr> 
                                            <td><?php echo $val['emp_early_in']['EmpName']; ?></td>
                                            <td><?php echo $val['emp_early_in']['LogDate']; ?></td>
                                            <td> <?php echo $val['emp_early_in']['Location']; ?></td>
                                            <?php echo isset($val['emp_early_in']['SharpInTime'])?"<td>" .$val['emp_early_in']['SharpInTime'] ."</td>" : ''; ?>
                                            <?php echo isset( $val['emp_early_in']['InTime'])?"<td>" . $val['emp_early_in']['InTime'] ."</td>" : ''; ?>
                                            <?php echo isset($val['emp_early_in']['EarlyInTime'])?"<td>" .date('H:i',strtotime($val['emp_early_in']['EarlyInTime'])) ."</td>" : ''; ?>
                                           
                                            <?php echo isset($val['emp_early_in']['OffDutyTime'])?"<td>" .$val['emp_early_in']['OffDutyTime'] ."</td>" : ''; ?>
                                            <?php echo isset( $val['emp_early_in']['OutTime'])?"<td>" . $val['emp_early_in']['OutTime'] ."</td>" : ''; ?>
                                            <?php echo isset($val['emp_early_in']['EarlyOutTime'])?"<td>" .date('H:i',strtotime($val['emp_early_in']['EarlyOutTime'])) ."</td>" : ''; ?>
                                             
                                           <?php echo isset( $val['emp_early_in']['LateInLimit'])?"<td>" . $val['emp_early_in']['LateInLimit'] ."</td>" : ''; ?>
                                           
                                           <?php echo isset( $val['emp_early_in']['LateTime'])?"<td>" . date('H:i',strtotime($val['emp_early_in']['LateTime'])) ."</td>" : ''; ?>
                                           
                                            <?php echo isset($val['emp_early_in']['LateOutTime'])?"<td>" .date('H:i',strtotime($val['emp_early_in']['LateOutTime'])) ."</td>" : ''; ?>
                                           
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

</div>
<script> 
    $(document).ready(function () {
        $('#punchLateInOut').DataTable({
            "paging": true,
            "lengthChange": false,
            "searching": true,
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
            ]
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
