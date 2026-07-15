<form class="form-horizontal" method="post" action="<?php echo $this->webroot; ?>LeaveEncashmentRequest/grandLeave" id="employeeleaverequestform">
    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal">&times;</button>
      <h4 class="modal-title"><?php echo "Approve Leave Encashment Request"; ?></h4>
    </div>   
    <div class="modal-body">
    <!-- Form Name -->
        <input id="model" name="model" type="hidden"  value="LeaveRequests" >
		<input id="action" name="action" type="hidden"  value="<?php echo $action; ?>" >
		<input id="eligible" name="eligible" type="hidden"  value="<?php echo $eligible; ?>" >
		<input id="request" name="request" type="hidden"  value="<?php echo $arr_empleaverequests['0']['LeaveEncashmentMaster']['requested_days']; ?>" >
        <input id="curuserid" name="leaveentryid" type="hidden"  value="<?php echo $arr_empleaverequests['0']['LeaveEncashmentMaster']['leave_encashment_master_pkey']; ?>" >
        <div class="form-group">
            <div class="col-md-12">
                <label style="text-align:left;" class="col-md-4 control-label" for="emp_name">Employee Name</label>
                <label style="text-align:left;padding-top: 7px;" class="col-md-8"><?php echo $arr_empleaverequests['0']['LeaveEncashmentMaster']['emp_name']; ?></label>
            </div>
        </div>
        <div class="form-group">
            <div class="col-md-12">
                <label style="text-align:left;" class="col-md-4 control-label" for="leave_type">Leave Type</label>
                <label style="text-align:left;padding-top: 7px;" class="col-md-8"><?php echo $arr_empleaverequests['0']['SalaryHeadItems']['item']; ?></label>
            </div>
        </div>
        <div class="form-group">
            <div class="col-md-12">
                <label style="text-align:left;" class="col-md-4 control-label" for="LEAVESTATUS">Leave Status</label>
                <label style="text-align:left;padding-top: 7px;" class="col-md-8"><?php echo $arr_empleaverequests['0']['LeaveEncashmentMaster']['is_approved']; ?></label>
            </div>
        </div>
        <div class="form-group">
            <div class="col-md-12">
                <label style="text-align:left;" class="col-md-4 control-label" for="leave_days">Number of days Requested</label>
                <label style="text-align:left;padding-top: 7px;" class="col-md-3"><?php echo $arr_empleaverequests['0']['LeaveEncashmentMaster']['requested_days']; ?></label>
            </div>
        </div>
        <div class="form-group">
            <div class="col-md-12">
			<label style="text-align:left;" class="col-md-4 control-label" for="leave_days">Number of days to Approve</label>
                <input type="text" class="col-md-3" name="approved_days" value="<?php echo $arr_empleaverequests['0']['LeaveEncashmentMaster']['requested_days']; ?>">
            </div>
        </div>
        <div class="form-group">
            <div class="col-md-12">
                <label style="text-align:left;" class="col-md-4 control-label" for="Reason">Reason</label>
                <label style="text-align:left;padding-top: 7px;" class="col-md-8"><?php echo $arr_empleaverequests['0']['LeaveEncashmentMaster']['remarks']; ?></label>
            </div>
        </div>
    </div>
    <?php
    if($arr_empleaverequests['0']['LeaveEncashmentMaster']['is_approved'] == 'N'){ 
    ?>
    <div class="modal-footer">
         <button type="button" onclick="grandLeave(this)" class="btn btn-info">Approve</button>
    </div>
    <?php
    }
    ?>
</form>
<script>
var empleaverequestoptions = {};
jQuery(document).ready(function() {
    $('#employeeleaverequestform').parsley();
    empleaverequestoptions = { 
      //  target:        '#output2',   // target element(s) to be updated with server response 
      //  beforeSubmit:  showRequest,  // pre-submit callback 
       success:       function(resp){
	   console.log(resp);
            var success = $.parseJSON(resp).success;
            var message = $.parseJSON(resp).message;
            alert(message);
			if(success){
				$('#largeModalForm').modal('hide');
				reloadTable('empleaverequeststable');
			}
       },  // post-submit callback
       error: function() {
          	$('#largeModalForm').modal('hide');
			alert('Sorry for the inconvenience, please contact support');
      } 
   };     
 });
function grandLeave(obj){
	var action = $('#action').val();
	var eligible = $('#eligible').val();
	var request = $('#request').val();
	if(action == 'Processed' || action == 'Approved'){
        alert("Can't approve leave encashment.Payroll Processed for this month.");
	}else if(eligible < request){
        alert("Can't approve leave encashment. Encashable leave balance exceeding the eligible limit. Eligible leave balance : "
		+eligible);
	}
	else{
    var actionType = $.trim($(obj).html());
    $(obj).html('<li class="fa fa-spinner fa-spin"></li>Please Wait').prop("disabled","disabled");
	//$('.btn').prop("disabled", "disabled");
    $('#actionType').val(actionType);
    $('#employeeleaverequestform').ajaxSubmit(empleaverequestoptions);
	}
}
</script>
