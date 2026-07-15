<form class="form-horizontal" method="post" action="<?php echo $this->webroot; ?>LeaveEncashmentRequest/save" id="myleaverequestform">
    <div class="modal-header">
       <!--  <button type="button" class="close" data-dismiss="modal">&times;</button> -->
        <h4 class="modal-title">Apply Leave Encashment</h4>
    </div>
    <div class="modal-body">
        <fieldset>
            <!-- Form Name -->
            <input id="model" name="emp_pkey" type="hidden"  value="<?php echo $emp_pkey; ?>" >
            <input id="name" name="name" type="hidden"  value="<?php echo $emp_name; ?>" >
            <input id="name" name="branch" type="hidden"  value="<?php echo $branch_code; ?>" >
            <input id="action" name="action" type="hidden"  value="<?php echo $action; ?>" >
            <div class="form-group">
                <?php if($leaves){ ?>
                <div class="col-md-6">
                <table class="table table-bordered" >
                    <thead>
                    <th>Leave Type</th>
		    <th>Encash Limit</th>
<!--                    <th>Yearly Balance</th>-->
		    <th>Approved Days</th>
                    <th>Eligible Days</th>
		    <th>Applied Days</th>
                    <th>Apply Days</th>
                    </thead>
                <?php foreach($leaves as $leavepolicies) { 
				$limit = isset($leavepolicies['leavepolicy']['leave_encash_limit'])?$leavepolicies['leavepolicy']['leave_encash_limit']:0; 
				$monthlylimit = isset($leavepolicies[0]['monthlybalance'])?$leavepolicies[0]['monthlybalance']:0;
                                $yearlybal = isset($leavepolicies[0]['yearlybalance'])?$leavepolicies[0]['yearlybalance']:0;
				$yearlylimit = isset($leavepolicies[0]['yearlylimit'])?$leavepolicies[0]['yearlylimit']:0;
				$encashyearly = isset($leavepolicies[0]['encashyearly'])?$leavepolicies[0]['encashyearly']:0;
				$leave_bal = $limit - $encashyearly ;
                         
				if($leavepolicies['leavepolicy']['ALLOW_NEGETIVE'] == 'Y'){
				$bal = min($limit,$yearlybal);
			    }else{
				$bal = $monthlylimit;
				//$eligible = min(($leavepolicies[0]['monthlybalance'] - $monthlylimit),$leave_bal);
			    } 
                         
                                 $eligible = min($bal,$leave_bal);
                            ?>
                    <tr>
                        <td><?php echo $leavepolicies['salary_head_items']['item']; ?></td>
			<td><?php echo $leavepolicies['leavepolicy']['leave_encash_limit']; ?><input value='<?php echo $leavepolicies['leavepolicy']['leave_encash_limit']; ?>' type="hidden" name="encashment[<?php echo $leavepolicies['leavepolicy']['salary_head_item_fkey'] ; ?>][limit]"></td>
<!--                        <td><?php echo $leavepolicies[0]['yearlybalance']; ?></td>-->
			<td><?php echo $encashyearly; ?></td>
                        <td><?php echo $eligible; ?><input value='<?php echo $eligible; ?>' type="hidden" name="encashment[<?php echo $leavepolicies['leavepolicy']['salary_head_item_fkey'] ; ?>][available]"></td>
			<td><?php echo $yearlylimit; ?></td>
                        <td><input onkeyup="checkavail(this,<?php echo $eligible; ?>);" max="<?php echo $eligible; ?>" type="text" class="form-control" name="encashment[<?php echo $leavepolicies['leavepolicy']['salary_head_item_fkey'] ; ?>][requested]" placeholder="Enter Days to apply" value="0" ></td>
                    </tr>
                    
                <?php } ?>
                </table>
                </div>
               
                <div class="col-md-6">
                    <div class="col-md-12 form-group">
                        <label style="text-align:left;" class="col-md-4 control-label" for="Authorized">Approve By&nbsp;<span style="color:red;">*</span></label>
                        <div class="col-md-8">
                            <input type="text" id="Authorized" class="form-control input-md strict-field" value="" name="Authorized" placeholder="Search Your Employee Name"  required="" />
                            <input type="hidden" id="ISAutherizedby" name="ISAutherizedby" value="" />
                        </div>
                    </div>
                    <div class="col-md-12 form-group">
                    <label style="text-align:left;" class="col-md-4 control-label" for="Reason">Reason&nbsp;<span style="color:red;">*</span></label>
                    <div class="col-md-8">
                        <textarea id="Reason" name="Reason" type="text" placeholder="Reason" class="form-control input-md strict-field" required="" ></textarea>
                    </div>
                    
                    </div>
                    
                </div>
                <div class="col-md-12">
                        <button id="btn-submit" class="btn btn-primary pull-right">Apply Requests</button>
                    </div>
                 <?php } else { ?>
                <div class="col-md-12">
                    <h3>You dont have any eligible leave encahsment to apply</h3>
                </div>
                 <?php } ?>
            </div>
        </fieldset>
    </div>
</form>
            
            

            

            

            
    </div>


</form>
<script>
function checkavail(s,avail){
    var EnteredNumber = $(s).val();
	var action = $('#action').val();
    if(EnteredNumber > avail){
        alert("Leave count exceeding the eligible limit.");
        $(s).val(0);
    }else if(action == 'Processed' || action == 'Approved'){
                $.notify("Can't approve leave encashment.Payroll Processed for this month. ", {
                            type: 'danger',
                            allow_dismiss: false
                        });
                 $(s).val(0);
    }
}





    var leaverequestoptions = {};
    jQuery(document).ready(function () {

        <?php if (isset($leaveentryId) && $leaveentryId != 0) { ?>
                getLeaveBalance();
        <?php } else{ ?>
            $('#Leavebalance').hide();
            $('#balance').hide();
        <?php } ?>
        var usersoptions = {
            url: function (phrase) {
                return livesite + 'LeaveRequest/getusers?username=' + phrase;
            },
            getValue: "full_name",
            list: {
                onClickEvent: function () {
                    console.log('onClickEvent');
                    var selectedItem = $("#Authorized").getSelectedItemData();
                    var site_fkey = selectedItem.emp_pkey;
                    //  alert(site_fkey); 


                    $("#ISAutherizedby").val(site_fkey);

                },
                onKeyEnterEvent: function () {
                    console.log('onKeyEnterEvent');
                },
                onMouseOverEvent: function () {
                    console.log('onMouseOverEvent');
                }
            }
        };

        $('#Authorized').easyAutocomplete(usersoptions);


        var usersoptionsappr = {
            url: function (phrase) {
                return livesite + 'LeaveRequest/getusers?username=' + phrase;
            },
            getValue: "full_name",
            list: {
                onClickEvent: function () {
                    var selectedItem = $("#APPROVED").getSelectedItemData();
                    var site_fkey = selectedItem.emp_pkey;
                    //  alert(site_fkey); 


                    $("#APPROVEDBY").val(site_fkey);

                }
            }
        };

        $('#APPROVED').easyAutocomplete(usersoptionsappr);

        $('#salary_head_item_fkey').on('change', function () {
            getLeaveBalance();
        })

        $('#FROMDATE').datepicker({
            format: 'yyyy-mm-dd',
			autoclose: true
        }).on('changeDate', function(e) {
			// `e` here contains the extra attributes
			var selected = $("#FROMDATE").val();
			var sdt = new Date(selected);
			var selectenddate = $("#TODATE").val();
			var edt = new Date(selectenddate);
			if (edt < sdt)
			{
				alert("From date should be less than To date");
				$("#FROMDATE").val('');
			}			
		});
        $("#FROMDATE").inputmask("yyyy-mm-dd");
		
        $('#TODATE').datepicker({
            format: 'yyyy-mm-dd',
			autoclose: true
        }).on('changeDate', function(e) {
			// `e` here contains the extra attributes
			var selected = $("#TODATE").val();
			var edt = new Date(selected);
			var selectsdate = $("#FROMDATE").val();
			var sdt = new Date(selectsdate);
			if (edt < sdt)
			{
				alert("To date should be greater than From date");
				$("#TODATE").val('');
                                return false;
			}
                        getLeaveBalance();
		});
        $("#TODATE").inputmask("yyyy-mm-dd");

        $('#myleaverequestform').parsley();
        leaverequestoptions = {
            //  target:        '#output2',   // target element(s) to be updated with server response 
            //  beforeSubmit:  showRequest,  // pre-submit callback 
            success: function (resp) {
                $('#btn-submit').css('transition','1s').addClass('btn-success').html('<li class="">Leave Encashment request submitted</li>');
                $('#leave_load').load(livesite+'LeaveEncashmentRequest/loadleave');
				$('#app_loader').load(livesite+'LeaveEncashmentRequest/form/');
                //Ends
            }, // post-submit callback
            error: function () {
                $('#largeModalForm').modal('hide');
                alert('Sorry for the inconvenience, please contact support');
            }

            // other available options: 
            //url:       url         // override for form's 'action' attribute 
            //type:      type        // 'get' or 'post', override for form's 'method' attribute 
            //dataType:  null        // 'xml', 'script', or 'json' (expected server response type) 
            //clearForm: true        // clear all form fields after successful submit 
            //resetForm: true        // reset the form after successful submit 

            // $.ajax options can be used here too, for example: 
            //timeout:   3000 
        };

        // bind to the form's submit event 
        $('#myleaverequestform').submit(function (event) {
            event.preventDefault();
            var yearly_balance = $('#hid_yearly_balance').val();
            var monthly_balance = $('#hid_monthly_balance').val();
            if(yearly_balance == 0 && monthly_balance == 0 && $('#myleaverequestform #LEAVEENTRYID').val() <= 0){
                alert("You don't have sufficient leave balance!");
                return false;
            }
			
            var start = $('#FROMDATE').datepicker('getDate');
            var end = $('#TODATE').datepicker('getDate');
            var days = (end - start) / 1000 / 60 / 60 / 24;
            $('#leave_days').val(days + 1);
            if ($('#myleaverequestform #LEAVEENTRYID').val() > 0) {
                var r = confirm("Do you want to resubmit leave ?");
                if (r == true) {
                    $('#btn-submit').html('<li class="fa fa-spinner fa-spin"></li> saving...').prop('disabled','disabled');
                    // inside event callbacks 'this' is the DOM element so we first 
                    // wrap it in a jQuery object and then invoke ajaxSubmit 
                    $(this).ajaxSubmit(leaverequestoptions);
                } else {
                    return false;
                }
            } else {
                $('#btn-submit').html('<li class="fa fa-spinner fa-spin"></li> saving...').prop('disabled','disabled');
                $(this).ajaxSubmit(leaverequestoptions);
            }
            // !!! Important !!! 
            // always return false to prevent standard browser submit and page navigation 
            return false;
        });
    });
    function cancelLeave() {
        if (confirm("Really you want to cancel leave?")) {
            $('#myLeaveAction').val('Cancelled');
            $('#btn-cancel').html('<li class="fa fa-spinner fa-spin"></li> Cancelling Leave...').attr('disabled','disabled');
            $('#myleaverequestform').ajaxSubmit(leaverequestoptions);
        }
    }
    
    
</script>