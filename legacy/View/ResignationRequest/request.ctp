<form class="form-horizontal" method="post" action="<?php echo $this->webroot; ?>ResignationRequest/Saverequests" id="myleaverequestform">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">My Resignation</h4>
    </div>
    <div class="modal-body">
        <fieldset>
            <!-- Form Name -->
            <div class="form-group">
                <div class="col-md-6">
                    <label style="text-align:left;" class="col-md-4 control-label" for="Reason">Reason for Resignation</label>
                    <div class="col-md-8">
                        <select id="Reason" name="Reason" class="form-control" required="">
                            <?php 
                            if($arr_requests['0']['ResignationRequests']['Reason'])
                            {
                            ?>
                            <option selected="selected" value="<?php echo $arr_requests['0']['ResignationRequests']['Reason']; ?>"><?php echo $arr_requests['0']['ResignationRequests']['Reason'] ; ?></option>
                            <?php
                            }
                            ?>
                            <option value="Personal">Personal</option>
                            <option value="Relocation">Relocation</option>
                            <option value="Better Opportunity">Better Opportunity</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <label style="text-align:left;" class="col-md-4 control-label" for="Reason_Desc">Reason Description</label>
                    <div class="col-md-8">
                        <textarea id="Reason_Desc" required="" value="" name="Reason_Desc" type="text" placeholder="<?php echo isset($arr_requests['0']['ResignationRequests']['Reason_Desc'])? $arr_requests['0']['ResignationRequests']['Reason_Desc'] : 'Reason Descriptions....' ; ?>" class="form-control input-md" ><?php echo isset($arr_requests['0']['ResignationRequests']['Reason_Desc'])? $arr_requests['0']['ResignationRequests']['Reason_Desc'] : '' ; ?></textarea>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="col-md-6">
                    <label style="text-align:left;" class="col-md-4 control-label" for="Last_workingday">Last Working Date</label>
                    <div class="col-md-8" style="padding-left: 0px;padding-right: 0px;">
                        <div class="col-md-12">
                            <input class="form-control input-md" id="Last_workingday" name="Last_workingday" value="<?php echo isset($arr_requests['0']['ResignationRequests']['Last_workingday'])? $arr_requests['0']['ResignationRequests']['Last_workingday'] : '' ; ?>" type="text" required="" >
                        </div>
                        
                    </div>
                </div>
<!--                <div class="col-md-6">
                    <label style="text-align:left;" class="col-md-4 control-label" for="TODATE">To Date</label>
                    <div class="col-md-8" style="padding-left: 0px;padding-right: 0px;">
                        <div class="col-md-12">
                            <input class="form-control input-md" id="TODATE" name="TODATE" value="" type="text" >
                        </div>
                        
                    </div>
                </div>-->
            </div>

            <div class="form-group">
                <!--edited by megha on 04/10/2019 resignation request unwanted field removal-->
<!--                <div class="col-md-6">
                    <label style="text-align:left;" class="col-md-4 control-label" for="authorised_to">Authorize By</label>
                    <div class="col-md-8">
                        <input required="" type="text" id="Authorized" class="form-control" value="<?php echo isset($arr_requests['0']['EmployeeDetails']['first_name'])? $arr_requests['0']['EmployeeDetails']['first_name'] : '' ; ?>" name="authorised_to" placeholder="Search Your Employee Name" style="width: 250px; "/>
                        <input type="hidden" value="<?php echo isset($arr_requests['0']['ResignationRequests']['authorised_to'])? $arr_requests['0']['ResignationRequests']['authorised_to'] : '' ; ?>" id="authorised_to" name="authorised_to"/>
                    </div>
                </div>-->
             <div class="col-md-6">
                    <label style="text-align:left;" class="col-md-4 control-label" for="contact_no">Contact Number</label>
                    <div class="col-md-8">
                        <input required="" id="contact_no" name="contact_no" value="<?php echo isset($arr_requests['0']['ResignationRequests']['contact_no'])? $arr_requests['0']['ResignationRequests']['contact_no'] : '' ; ?>" type="text" placeholder="Contact Number" class="form-control input-md" >
                    </div>
                </div>
            </div>
<!--edited by megha on 04/10/2019 resignation request unwanted field removal-->
<!--            <div class="form-group">
                <div class="col-md-6">
                    <label style="text-align:left;" class="col-md-4 control-label" for="Reason">Comments To Manager</label>
                    <div class="col-md-8">
                        <textarea id="Comments_to_manager" required="" value="" name="Comments_to_manager" type="text" placeholder="<?php echo isset($arr_requests['0']['ResignationRequests']['Comments_to_manager'])? $arr_requests['0']['ResignationRequests']['Comments_to_manager'] : 'Comments....' ; ?>" class="form-control input-md" ><?php echo isset($arr_requests['0']['ResignationRequests']['Comments_to_manager'])? $arr_requests['0']['ResignationRequests']['Comments_to_manager'] : '' ; ?></textarea>
                    </div>
                </div>
                
            </div>-->
            
                
            </div>
    </div>

<?php // if (!in_array($arr_leave_details['LEAVESTATUS'], array('Authorized', 'Approved'))) { ?>
        <div class="modal-footer">
<!--            <button type="button" id="btn-cancel" class="btn btn-default" onclick="cancelLeave()" style="display: <?php if ($leaveentryId == 0) {
        echo "none";
    } ?>">
                Cancel Leave
            </button>-->
            <button type="submit" id="btn-submit" class="btn btn-primary">
<!--                Generate Letter--> Submit
            </button>
    <button type="button" class="btn bg-maroon" data-dismiss="modal">Close</button>
    
        </div>
<?php // } ?>
</form>


<script>






    var leaverequestoptions = {};
    jQuery(document).ready(function () {

        $('#Leavebalance').hide();
        $('#balance').hide();
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


                    $("#authorised_to").val(site_fkey);

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
            var usrid = $(this).val();
            var user_pkey = $('#salary_head_item_fkey').val();
            $.ajax({
                url: livesite + 'LeaveRequest/GetLeaveBalance/' + user_pkey,
                type: 'POST',
                data: {
                    userid: usrid
                },
                success: function (resp)
                {
                    $('#balance').show();
                    $('#Leavebalance').show();
                    //   alert(resp);
                    $('#Leavebalance').val(resp);

                }
            });
        })

        $('#FROMDATE').datepicker({
            format: 'yyyy-mm-dd'
        })
        $("#FROMDATE").inputmask("yyyy-mm-dd");
        //added by megha start date on 15/02/2020
        $('#Last_workingday').datepicker({
            format: 'yyyy-mm-dd',
            startDate:'0d'
        })
        $("#Last_workingday").inputmask("yyyy-mm-dd");

        $('#myleaverequestform').parsley();
        var leaverequestoptions = {
            //  target:        '#output2',   // target element(s) to be updated with server response 
            //  beforeSubmit:  showRequest,  // pre-submit callback 
            success: function (resp) {
                    //edited by megha on 11/12/2019 resignation request to admin
                   // var url = livesite+'ResignationRequest/letter';
//                    var container = $("#modalDetailForm1 #modaldetails-content1")
//                    container.load(url, function() {
//                    $("#modalDetailForm1").modal('show');
//                    });
                    $('#modalDetailForm').modal('hide');
                      showmain();
                    $.notify("Resignation Request Saved And Forwarded to the Admin",{
                            type: 'success',
                            allow_dismiss: false
                        });
                        $("#myleaverequestform").show();
                    //end resignation request to admin
                    
                    //$('#modalDetailForm').modal('hide');
//                    $.notify("Resignation Request Saved And Forward to the Manager Please Wait For Responses",{
//                            type: 'success',
//                            allow_dismiss: false
//                        });
                    //showSmallModalForm(livesite + 'LeaveRequest/showleavedays/' + leaveentryid);
                
                //Ends
            }, // post-submit callback
            error: function () {
                $('#modalDetailForm').modal('hide');
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
        $('#myleaverequestform').submit(function () {
            
            if ($('#myleaverequestform #LEAVEENTRYID').val() > 0) {
                var r = confirm("Do you want to resubmit leave ?");
                if (r == true) {
                    // inside event callbacks 'this' is the DOM element so we first 
                    // wrap it in a jQuery object and then invoke ajaxSubmit 
                    $(this).ajaxSubmit(leaverequestoptions);
                } else {
                    return false;
                }
            } else {
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
            $('#myleaverequestform').ajaxSubmit(leaverequestoptions);
        }
    }
</script>
<!--commented by megha -->
<!--<div id="modalDetailForm1" class="modal fade bs-example-modal-lg"  tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
    <div class="modal-dialog modal-lg" style="width: 820px;">
        <div class="modal-content" id="modaldetails-content1">

        </div>
    </div>
    </div>-->