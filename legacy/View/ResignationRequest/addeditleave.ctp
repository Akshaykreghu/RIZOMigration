<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal">&times;</button>
    <h4 class="modal-title"><?php echo isset($arr_requests['0']['EmployeeDetails']['first_name']) ? $arr_requests['0']['EmployeeDetails']['first_name'] . ' ' . $arr_requests['0']['EmployeeDetails']['last_name'] : 'NOT MENTIONED'; ?></h4>
</div> 



<section class="invoice">
    <!-- title row -->
    <div class="row">
        <div class="col-xs-12">
            <h2 class="page-header">
                <i class="fa "></i> <b><?php echo $arr_requests['0']['EmployeeDetails']['first_name'] . ' ' . $arr_requests['0']['EmployeeDetails']['last_name']; ?></b>.
                <small class="pull-right">Date: <?php echo date('Y-m-D'); ?></small>
            </h2>
        </div>
        <!-- /.col -->
    </div>
    <!-- info row -->
    <div class="row invoice-info">
        <div class="col-sm-4 invoice-col">
            From
            <address>
                <strong><?php echo $arr_requests['0']['EmployeeDetails']['first_name'] . ' ' . $arr_requests['0']['EmployeeDetails']['last_name']; ?></strong><br>
                <?php echo $arr_requests['0']['EmployeeDetails']['address']; ?><br>
                <?php echo $arr_requests['0']['EmployeeDetails']['state']; ?><br>
                Phone: <?php echo $arr_requests['0']['EmployeeDetails']['mobile_no']; ?><br>
                Email: <?php echo $arr_requests['0']['EmployeeDetails']['email']; ?>
            </address>
        </div>
        <!-- /.col -->
        <div class="col-sm-4 invoice-col">
            To
            <address>
                <strong><?php echo $arr_to['0']['EmployeeDetails']['first_name'] . ' ' . $arr_to['0']['EmployeeDetails']['last_name']; ?></strong><br>
                <?php echo $arr_to['0']['Designation']['desig_name']; ?><br>
                <?php echo $arr_to['0']['Branches']['branch_name']; ?><br>
                <?php echo $arr_to['0']['Department']['dept_name']; ?><br>
                <?php echo $company['CompanyContactInfo']['business_name']; ?>
            </address>
        </div>
        <!-- /.col -->
    </div>
    <!-- /.row -->

    <!-- Table row -->
    <div class="row">
        <?php
        $mobile = isset($arr_requests['0']['EmployeeDetails']['mobile_no']) ? $arr_requests['0']['EmployeeDetails']['mobile_no'] : '(MObile Number not given)';
        ?>
        <div class="mailbox-read-message">
            <p>Dear <?php echo $arr_to['0']['EmployeeDetails']['first_name']; ?>,</p>

            <p>Please accept this letter as notice of my resignation from my position as <?php echo $arr_requests['0']['Designation']['desig_name']; ?>. My last day of employment will be <?php echo $arr_requests['0']['ResignationRequests']['Last_workingday']; ?>.</p>

            <p>I have taken this decision as <?php echo $arr_requests['0']['ResignationRequests']['Reason_Desc']; ?>.</p>

            <p>I would like to help with the transition of my duties so that systems continue to function smoothly
                after my departure. I will make certain that all reporting and records are updated before my last day of work.</p>
            <p><?php echo $arr_to['0']['EmployeeDetails']['first_name']; ?>, thank you again for the opportunity to work for <?php echo $company['CompanyContactInfo']['business_name']; ?> Company. I wish you and your staff all the best and
                I look forward
                to staying in touch with you. You can email me anytime at <?php echo isset($arr_requests['0']['EmployeeDetails']['email']) ? $arr_requests['0']['EmployeeDetails']['email'] : '(No email sets)'; ?> or call me at <?php echo isset($arr_requests['0']['ResignationRequests']['contact_no']) ? $arr_requests['0']['ResignationRequests']['contact_no'] : $mobile; ?>.</p>
            <p>Sincerely,<br><?php echo $arr_requests['0']['EmployeeDetails']['first_name']; ?></p>
        </div>
        <!-- /.col -->
    </div>
    <!-- /.row -->

    <div class="row">
        <!-- accepted payments column -->

        <!-- /.col -->
    </div>
    <!-- /.row -->

    <!-- this row will not appear when printing -->
    <div class="row no-print">
        <form method="post" action="<?php echo $this->webroot; ?>ResignationRequest/Empagreed" id="Agree">

        </form>
    </div>
</section>

<div class="modal-body">
    <form class="form-horizontal" method="post" action="<?php echo $this->webroot; ?>ResignationRequest/grandrequest" id="employeeleaverequestform">
        <!-- Form Name -->
        <input name="Resignation_pkey" value="<?php echo isset($arr_requests['0']['ResignationRequests']['Resignation_pkey']) ? $arr_requests['0']['ResignationRequests']['Resignation_pkey'] : ''; ?>" type="hidden" class="form-control input-md" >
        <input name="applied_emp" value="<?php echo isset($arr_requests['0']['ResignationRequests']['emp_fkey']) ? $arr_requests['0']['ResignationRequests']['emp_fkey'] : ''; ?>" type="hidden" class="form-control input-md" >
        <!-- Added on 21 Feb 2016 -->

        <div class="form-group">
            <div class="col-md-12">
                <label style="text-align:left;" class="col-md-4 control-label" for="REMARKS">Comments To employee</label><span class="star">*</span>
                <div class="col-md-8">
                    <textarea required="required" id="REMARKS" name="comments_to_emp" type="text" placeholder="Remarks" class="form-control input-md" ><?php echo isset($arr_requests['0']['ResignationAccept']['comments_to_emp']) ? $arr_requests['0']['ResignationAccept']['comments_to_emp'] : ''; ?></textarea>
                </div>
            </div>
        </div>
        <hr> 	
        <?php
        if ($cur_emp_key != $arr_requests['0']['ResignationAccept']['forwarded'] && $arr_requests['0']['ResignationAccept']['isApproved'] != 1) {
            ?>
            <fieldset>
                <h4 class="modal-title"><b>Forward to HR</b></h4>
                <hr>
                <!-- Form Name -->
                <div class="form-group">
                    <div class="col-md-6">
                        <label style="text-align:left;" class="col-md-4 control-label" for="Reason_Desc">Reason for Resignation</label>
                        <div class="col-md-8">
                            <input id="manager_reason" name="manager_reason" value="<?php echo isset($arr_requests['0']['ResignationAccept']['manager_reason']) ? $arr_requests['0']['ResignationAccept']['manager_reason'] : ''; ?>" type="text" placeholder="Reason" class="form-control input-md" >
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label style="text-align:left;" class="col-md-4 control-label" for="Reason_Desc">Reason Desc</label>
                        <div class="col-md-8">
                            <textarea id="comments_to_hr" name="comments_to_hr" value="" type="text" placeholder="Reason Descriptions" class="form-control input-md" ><?php echo isset($arr_requests['0']['ResignationAccept']['comments_to_hr']) ? $arr_requests['0']['ResignationAccept']['comments_to_hr'] : ''; ?></textarea>
                        </div>
                    </div>
                </div>


                <div class="form-group">
                    <div class="col-md-6">
                        <label style="text-align:left;" class="col-md-4 control-label" for="Reason_Desc">Last allowed working Date</label><span class="star">*</span>
                        <div class="col-md-8">
                            <input required="required" id="last_allowed_date" name="last_allowed_date" value="<?php echo isset($arr_requests['0']['ResignationAccept']['last_allowed_date']) ? $arr_requests['0']['ResignationAccept']['last_allowed_date'] : ''; ?>" type="text" placeholder="Enter Date" class="form-control input-md" >
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label style="text-align:left;" class="col-md-4 control-label" for="Reason_Desc">Forwarded to</label><span class="star">*</span>
                        <div class="col-md-8">
                            <input id="to" name="to" style="width:240px;" value="<?php echo isset($arr_emphr['0']['EmployeeDetails']['first_name']) ? $arr_emphr['0']['EmployeeDetails']['first_name'] . ' ' . $arr_emphr['0']['EmployeeDetails']['last_name'] : ''; ?>" type="text" placeholder="Search Employee Name" class="form-control input-md" >
                            <input required="required" id="forwarded" name="forwarded" value="<?php echo isset($arr_requests['0']['ResignationAccept']['forwarded']) ? $arr_requests['0']['ResignationAccept']['forwarded'] : ''; ?>" type="hidden" class="form-control input-md" >
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-6">
                        <label style="text-align:left;" class="col-md-4 control-label" for="Reason_Desc">Duties Hand Over to</label><span class="star">*</span>
                        <div class="col-md-8">
                            <input required="required" id="to1" name="to1" style="width:240px;" value="<?php echo isset($arr_emp['0']['EmployeeDetails']['first_name']) ? $arr_emp['0']['EmployeeDetails']['first_name'] . ' ' . $arr_emp['0']['EmployeeDetails']['last_name'] : ''; ?>" type="text" placeholder="Search Employee Name" class="form-control input-md" >
                            <input id="handover_to" name="handover_to" value="<?php echo isset($arr_requests['0']['ResignationAccept']['handover_to']) ? $arr_requests['0']['ResignationAccept']['handover_to'] : ''; ?>" type="hidden" class="form-control input-md" >
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label style="text-align:left;" class="col-md-4 control-label" for="Reason_Desc">Authorize/Reject</label><span class="star">*</span>
                        <div class="col-md-8">
                            <select required="required" class="form-control" name="isauthorized">
                                <option <?php if (isset($arr_requests['0']['ResignationAccept']['isauthorized']) == '1') { ?>selected="selected"<?php }; ?> value="1">Authorize</option>
                                <option <?php if (isset($arr_requests['0']['ResignationAccept']['isauthorized']) == '2') { ?>selected="selected"<?php }; ?> value="2">Reject</option>
                            </select>
                        </div>
                    </div>
                </div>

            </fieldset>
            <div class="modal-footer">
                <button type="submit" class="btn btn-info" id="btn-rejectemployeeleave">Submit</button>
                <button type="button" onclick="$('#largeModalForm').modal('hide');" class="btn btn-info" id="btn-rejectemployeeleave">Take Action Later</button>



            </div>
            <?php
        }
        ?>
    </form>





    <?php
    $pkey = isset($arr_requests['0']['ResignationAccept']['forwarded']) ? $arr_requests['0']['ResignationAccept']['forwarded'] : '0';
    if ($cur_emp_key == $pkey || $arr_requests['0']['ResignationAccept']['isApproved'] == 1) {
        ?>

        <div >    <fieldset>
                <form class="form-horizontal" method="post" action="<?php echo $this->webroot; ?>ResignationRequest/grandrequest" id="hrsubmittes">
                    <input name="Resignation_pkey" value="<?php echo isset($arr_requests['0']['ResignationRequests']['Resignation_pkey']) ? $arr_requests['0']['ResignationRequests']['Resignation_pkey'] : ''; ?>" type="hidden" class="form-control input-md" >
                    <input name="applied_emp" value="<?php echo isset($arr_requests['0']['ResignationRequests']['emp_fkey']) ? $arr_requests['0']['ResignationRequests']['emp_fkey'] : ''; ?>" type="hidden" class="form-control input-md" >
                    <strong>FROM <?php echo isset($arr_to['0']['EmployeeDetails']['first_name']) ? $arr_to['0']['EmployeeDetails']['first_name'] . ' ' . $arr_to['0']['EmployeeDetails']['last_name'] : 'NOT MENTIONED'; ?></strong><br>
                    <hr>
                    <div class="form-group">
                        <div class="col-md-12">
                            <label style="text-align:left;" class="col-md-4 control-label" for="leave_days">Manager Given Reason</label>
                            <label style="text-align:left;padding-top: 7px;" class="col-md-8"><?php echo isset($arr_requests['0']['ResignationAccept']['manager_reason']) ? $arr_requests['0']['ResignationAccept']['manager_reason'] : ''; ?></label>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-md-12">
                            <label style="text-align:left;" class="col-md-4 control-label" for="leave_days">Description</label>
                            <label style="text-align:left;padding-top: 7px;" class="col-md-8"><?php echo isset($arr_requests['0']['ResignationAccept']['comments_to_hr']) ? $arr_requests['0']['ResignationAccept']['comments_to_hr'] : ''; ?></label>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-12">
                            <label style="text-align:left;" class="col-md-4 control-label" for="leave_days">Last Allowed Working Date</label>
                            <label style="text-align:left;padding-top: 7px;" class="col-md-8"><?php echo isset($arr_requests['0']['ResignationAccept']['last_allowed_date']) ? $arr_requests['0']['ResignationAccept']['last_allowed_date'] : ''; ?></label>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-12">
                            <label style="text-align:left;" class="col-md-4 control-label" for="leave_days">Duties Hand Over To</label>
                            <label style="text-align:left;padding-top: 7px;" class="col-md-8"><?php echo isset($arr_emp['0']['EmployeeDetails']['first_name']) ? $arr_emp['0']['EmployeeDetails']['first_name'] . ' ' . $arr_emp['0']['EmployeeDetails']['last_name'] : ''; ?></label>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-12">
                            <label style="text-align:left;" class="col-md-4 control-label" for="leave_days">Authorise/Reject</label>
                            <label style="text-align:left;padding-top: 7px;" class="col-md-8"><?php if (isset($arr_requests['0']['ResignationAccept']['isauthorized']) == '1') {
        echo "Authorized";
    } else {
        echo "UnAuthorized";
    }; ?></label>
                        </div>
                    </div>

                    <hr>
    <?php
    if ($cur_emp_key == $pkey) {
        ?>

                        <div class="form-group">
                            <div class="col-md-12">
                                leave balance
                            </div>
                            <div class="col-md-12">
                                <input type="checkbox" name="chek_formalities" id="2"> I have completed all the formalities as per our organization's policy to complete this resignation. 
                            </div>
                            <div class="col-md-12">
                                <input type="checkbox" name="chek_assets" id="3"> I have retrieved all assets allocated to this employee. 
                            </div>
                            <div class="col-md-12">
                                <input type="checkbox" name="chek_leave" id="4"> I have calculated the leave encashment and arrive at the full and final settlement amount.
                            </div>
                        </div>
                    <?php if($arr_requests['0']['ResignationAccept']['isApproved'] != 1){ ?>
                        <div class="form-group">
                            <div class="col-md-6">
                                <label style="text-align:left;" class="col-md-4 control-label" for="Reason_Desc">Comment to Manager</label>
                                <div class="col-md-8">
                                    <textarea id="comments_to_hr" name="hr_comment" type="text" placeholder="Comments" class="form-control input-md" ></textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label style="text-align:left;" class="col-md-4 control-label" for="Reason_Desc">Approve/Reject</label><span class="star">*</span>
                                <div class="col-md-8">
                                    <select required="required" class="form-control" name="isApproved">
                                        <option value="1">Approve</option>
                                        <option value="2">Reject</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
        <?php
    }
    ?>
                    <?php if($arr_requests['0']['ResignationAccept']['isApproved'] != 1){ ?>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-info" id="btn-rejectemployeeleave">Submit</button>
                        <button type="button" class="btn btn-info" onclick="$('#largeModalForm').modal('hide');" id="btn-rejectemployeeleave">Take Action Later</button>

                    </div>
                    <?php } ?>
                </form></fieldset> 
        </div>
    <?php
}
?>


</div>
<script>
    var empleaverequestoptions = {};
    jQuery(document).ready(function () {

        $('#invoice').load(livesite + 'ResignationRequest/letteredit');
        $('#employeeleaverequestform').parsley();
        $('#hrsubmittes').parsley();
        var empleaverequestoptions = {
            //  target:        '#output2',   // target element(s) to be updated with server response 
            //  beforeSubmit:  showRequest,  // pre-submit callback 
            success: function (resp) {

                $('#largeModalForm').modal('hide');
                $.notify(" Resignation Request Forwarded To HR Successfully ", {
                    type: 'success',
                    allow_dismiss: false
                });
                reloadTable('myleaverequeststable');

            }, // post-submit callback
            error: function () {
                $('#largeModalForm').modal('hide');
                alert('Sorry for the inconvenience, please contact support');
            }

        };
        var empleaverequestoptions1 = {
            //  target:        '#output2',   // target element(s) to be updated with server response 
            //  beforeSubmit:  showRequest,  // pre-submit callback 
            success: function (resp) {

                $('#largeModalForm').modal('hide');
                $.notify(" Resignation Request Approved Successfully ", {
                    type: 'success',
                    allow_dismiss: false
                });

            }, // post-submit callback
            error: function () {
                $('#largeModalForm').modal('hide');
                alert('Sorry for the inconvenience, please contact support');
            }

        };
        // bind to the form's submit event 
        $('#employeeleaverequestform').submit(function () {
            $(this).ajaxSubmit(empleaverequestoptions);
            return false;


        });

        $('#hrsubmittes').submit(function () {
            var b = document.getElementById("2");
            var c = document.getElementById("3");
            var d = document.getElementById("4");
            if (b.checked && c.checked && d.checked)
            {
                $(this).ajaxSubmit(empleaverequestoptions1);

            } else
            {
                alert('Check all to submit');
            }
            return false;
        });
        $('#last_allowed_date').datepicker({
            format: 'yyyy-mm-dd'
        })
        $("#last_allowed_date").inputmask("yyyy-mm-dd");


        var usersoptions = {
            url: function (phrase) {
                return livesite + 'LeaveRequest/getusers?username=' + phrase;
            },
            getValue: "full_name",
            list: {
                onClickEvent: function () {
                    console.log('onClickEvent');
                    var selectedItem = $("#to").getSelectedItemData();
                    var site_fkey = selectedItem.emp_pkey;
                    //  alert(site_fkey); 


                    $("#forwarded").val(site_fkey);

                },
                onKeyEnterEvent: function () {
                    console.log('onKeyEnterEvent');
                },
                onMouseOverEvent: function () {
                    console.log('onMouseOverEvent');
                }
            }
        };
        var usersoptions1 = {
            url: function (phrase) {
                return livesite + 'LeaveRequest/getusers?username=' + phrase;
            },
            getValue: "full_name",
            list: {
                onClickEvent: function () {
                    console.log('onClickEvent');
                    var selectedItem = $("#to1").getSelectedItemData();
                    var site_fkey = selectedItem.emp_pkey;
                    //  alert(site_fkey); 


                    $("#handover_to").val(site_fkey);

                },
                onKeyEnterEvent: function () {
                    console.log('onKeyEnterEvent');
                },
                onMouseOverEvent: function () {
                    console.log('onMouseOverEvent');
                }
            }
        };
        $('#to').easyAutocomplete(usersoptions);
        $('#to1').easyAutocomplete(usersoptions1);

    });
</script>