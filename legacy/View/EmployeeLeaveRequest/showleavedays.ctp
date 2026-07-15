<div class="modal-body">
    <legend><?php echo $head; ?></legend>
    <div class="row">
        <div class="col-md-12">
            <div class="col-md-4" style="padding-right: 0px;padding-left: 0px;">
            <label style="text-align:left;" >Leave Type: <?php echo $leave_type; ?></label>
            <!--<label style="text-align:left;" class="col-md-6 control-label"></label>-->
        </div>
            <div class="col-md-3 " style="padding-right: 0px;padding-left: 0px;">
            <label style="text-align:left;" >Leave Balance: <?php echo $leavebalance; ?></label>
            <!--<label style="text-align:left;" class="col-md-6 control-label"><?php echo $leavebalance; ?></label>-->
        </div>
        <div class="col-md-2"style="padding-right: 0px;padding-left: 0px;">
            <label><!--input type="checkbox" value="1" <?php if ($allow_negative == 'Y') {
                    echo 'checked="checked"';
                } ?> readonly="readonly"-->Negative : <?php echo ($allow_negative == 'Y')?'Yes':'No'; ?></label>
        </div>
        <div class="col-md-3" style="padding-right: 0px;padding-left: 0px;">
            <label><!--input type="checkbox" value="1" <?php if ($is_sandwitch == 'Y') {
    echo 'checked="checked"';
} ?> readonly="readonly"-->Sandwhich : <?php echo ($is_sandwitch == 'Y')?'Yes':'No'; ?></label>
        </div>
        </div>      
    </div>
    <hr style="margin-top: 12px; margin-bottom: -1px;">
    <div class="row">
        <div class="col-md-12" style="padding-top: 10px;">
            <table class="table ">
                <thead>
                    <tr class="danger">
                        <th>Leave Days</th>
                        <th>Leave Status</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($resp_leavetransactions as $leavetransaction) { 
                    $leaveentryId = $leavetransaction['LEAVEENTRYID'];?>
                        <tr class="success">
                            <td><?php echo date('d-m-Y', strtotime($leavetransaction['leave_date'])); ?></td>
                            <td><?php echo $leavetransaction['Leavestatus']; ?></td>
                            <td><?php echo $leavetransaction['Remarks']; ?></td>
                        </tr>
<?php } ?>
                </tbody>
            </table>
        </div>
    </div>
    <form class="form-horizontal" method="post" action="<?php echo $this->webroot; ?>EmployeeLeaveRequest/saveDocument" id="myleaverequestform1" enctype="multipart/form-data">
    <div class="modal-header" >
 
    </div>
        <legend>Add Document</legend>
    <input id="myLeaveAction" name="myLeaveAction" type="hidden"  value="Save" >
    <input id="LEAVEENTRYID" name="LEAVEENTRYID" type="hidden"  value="<?php echo $leaveentryId; ?>" >
      <div class="modal-body">
            <!-- Form Name -->
            <input id="model" name="model" type="hidden"  value="LeaveRequests" >
             <div class="form-group">
                <div class="col-md-12">
                    <label style="text-align:left;" class="col-md-4 control-label" for="File">New Document Name&nbsp;</label>
                    <div class="col-md-8">
                        <input id="filename" name="filename" value="" type="text" placeholder="Uploaded File Name" class="form-control input-md strict-field" >
                    </div>
                </div>
             </div>
            <div class="form-group">
                <div class="col-md-12" >
                    <label style="text-align:left;" class="col-md-4 control-label" for="file">Upload Document</label>
                    <div class="col-md-8">
                        <input id="image" name="image" value="" type="file" placeholder="Upload file" class="form-control input-md" >
                    </div>
                </div>
            </div>
      </div>
    <div class="modal-footer">
        <button type="submit" id="btn-submit" class="btn btn-primary strict-field" >
                    Save
        </button>
    </div>
</form>
</div>
<!--<div class="modal-footer">
    <button type="submit" id="btn-submit" class="btn btn-primary" data-dismiss="modal">
        Ok
    </button>
</div>-->
<script>
    $(document).ready(function(){
    $('#myleaverequestform1').parsley();

        var    leaverequestoptions = {
                success: function (resp) {
                    var success = $.parseJSON(resp).success;
                    var message = $.parseJSON(resp).message;
                    //if (success == false) {
                  closeSmallModalForm();
                     //$('#SmallModalForm').modal('hide');
                       // alert(message);
                         $.notify(message,{
                            type: 'success',
                            allow_dismiss: true

                    });
                        // return;
                   // }
                 
                   
                }, // post-submit callback
                error: function () {
                   $('#SmallModalForm').modal('hide');
                    alert('Sorry for the inconvenience, please contact support');
                }

            };
     // bind to the form's submit event 
            $('#myleaverequestform1').submit(function (event) {
                event.preventDefault();
               
                    $('#btn-submit').html('<li class="fa fa-spinner fa-spin"></li> saving...').prop('disabled', 'disabled');
                    $(this).ajaxSubmit(leaverequestoptions);
                
                // !!! Important !!! 
                // always return false to prevent standard browser submit and page navigation 
                return false;
            });
        });
            </script>