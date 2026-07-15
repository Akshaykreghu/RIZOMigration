<div class="modal-body">
<!--    added by megha Details not found condition on 15/02/2020-->
     <?php if(empty($arr_requests)){ ?>
       <h3>Resignation Details Not Found</h3> 
    <?php }else {?>
    <legend>Resignation Details of &nbsp;<?php echo $arr_requests['0']['EmployeeDetails']['first_name'].' '.$arr_requests['0']['EmployeeDetails']['last_name'].' - '.$arr_requests['0']['EmployeeProffessional']['emp_company_id'] ; ?></legend>
    <section class="invoice">
      <!-- title row -->
      <div class="row">
       
            
      <div class="col-md-12">
        <div class="box box-default collapsed-box box-solid">
            <div class="box-header with-border">
                <h3 class="box-title">Personal Details</h3>

                <!-- /.box-tools -->
            </div>
            <!-- /.box-header -->
            <div class="box-body show-first" id="contain"  style="display:block;">
                  <div class="col-sm-3 col-xs-6">
                    <div class="description-block border-right">
                        <h5 class="description-header"><span class="description-text" title="Branch">Branch</span></h5>
                        <?php echo $arr_requests['0']['Branches']['branch_name']; ?>
                        
                    </div>
                </div>
                <div class="col-sm-3 col-xs-6">
                    <div class="description-block border-right">
                        <!--<span class="description-percentage text-yellow"><i class="fa fa-caret-left"></i> 0%</span>-->
                        <h5 class="description-header"><span class="description-text" title="Designation">Designation</span></h5>
                        <?php echo $arr_requests['0']['Designation']['desig_name'] ; ?>
                        
                    </div>
                    <!-- /.description-block -->
                </div>
                
                <div class="col-sm-3 col-xs-6">
                    <div class="description-block border-right">
                        <h5 class="description-header"><span class="description-text" title="Department">Department</span></h5>
                        <?php echo $arr_requests['0']['Department']['dept_name']; ?>
                        
                    </div>
                </div>
              
                <div class="col-sm-3 col-xs-6">
                    <div class="description-block border-right">
                        <!--<span class="description-percentage text-yellow"><i class="fa fa-caret-left"></i> 0%</span>-->
                        <h5 class="description-header"><span class="description-text" title="Email">Email</span></h5>
                        <?php echo $arr_requests['0']['EmployeeDetails']['email']; ?>
                        
                    </div>
                    <!-- /.description-block -->
                </div>
            </div>
            <!-- /.box-body -->
        </div>
        <!-- /.box -->
    </div>
       <div class="col-md-12">
        <div class="box box-default collapsed-box box-solid">
            <div class="box-header with-border">
                <h3 class="box-title">Resignation Details</h3>

                <!-- /.box-tools -->
            </div>
            <!-- /.box-header -->
            <div class="box-body show-first" id="contain"  style="display:block;">
                
                <div class="col-sm-3 col-xs-6">
                    <div class="description-block border-right">
                        <!--<span class="description-percentage text-yellow"><i class="fa fa-caret-left"></i> 0%</span>-->
                        <h5 class="description-header"><span class="description-text" title="RESIGNATION SUBMITTED DATE">Resignation Submitted Date</span></h5>
                        <?php echo date('Y-m-d', strtotime($arr_requests['0']['ResignationRequests']['applied_date'])); ?>
                        
                    </div>
                    <!-- /.description-block -->
                </div>
                
                <div class="col-sm-3 col-xs-6">
                    <div class="description-block border-right">
                        <h5 class="description-header"><span class="description-text" title="Reason">Reason</span></h5>
                        <?php echo $arr_requests['0']['ResignationRequests']['Reason']; ?>
                        
                    </div>
                </div>
                <div class="col-sm-3 col-xs-6">
                    <div class="description-block border-right">
                        <h5 class="description-header"><span class="description-text" title="LAST WORKING DATE">Last Working Date</span></h5>
                        <?php echo $arr_requests['0']['ResignationRequests']['Last_workingday']; ?>
                        
                    </div>
                </div>
                <div class="col-sm-3 col-xs-6">
                    <div class="description-block border-right">
                        <!--<span class="description-percentage text-yellow"><i class="fa fa-caret-left"></i> 0%</span>-->
                        <h5 class="description-header"><span class="description-text" title="Contact Number">Contact Number</span></h5>
                        <?php echo $arr_requests['0']['ResignationRequests']['contact_no']; ?>
                        
                    </div>
                    <!-- /.description-block -->
                </div>
            </div>
            <!-- /.box-body -->
        </div>
        <!-- /.box -->
    </div>
      </div>
    </section>
      <?php } ?>
</div>

<script>

    var leaverequestoptions = {};
    jQuery(document).ready(function () {


        $('#Agree').parsley();
        var leaverequestoptions1 = {
            //  target:        '#output2',   // target element(s) to be updated with server response 
            //  beforeSubmit:  showRequest,  // pre-submit callback 
            success: function (resp) {

                    $('#modalDetailForm1').modal('hide');
                    $('#modalDetailForm').modal('hide');
                    $.notify("Resignation Request Saved And Forward to the Manager Please Wait For Responses",{
                            type: 'success',
                            allow_dismiss: false
                        });
                    //showSmallModalForm(livesite + 'LeaveRequest/showleavedays/' + leaveentryid);
                
                //Ends
            }, // post-submit callback
            error: function () {
                $('#modalDetailForm').modal('hide');
                alert('Sorry for the inconvenience, please contact support');
            }

        };

        // bind to the form's submit event 
        $('#Agree').submit(function() {
     
            $(this).ajaxSubmit(leaverequestoptions1);
       
        return false;
    });
   
          
    });
    
</script>