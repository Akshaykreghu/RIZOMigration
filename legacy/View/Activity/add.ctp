<style>
    .form-horizontal .control-label {
        text-align: left;
        /*padding-left: 76px;*/
    }

    input.form-control, textarea.form-control {
        border-radius: 4px !important;
        /* border: 1px solid #aaa; */
    }

    .parsley-errors-list {
        list-style: none;
        color: #f44336;
    }

    .showTransfers, .subTasks {
        display: none;
    }

    .showTransfers.show {
        display: block;
    }

    .durationList {
        margin: 0;
        padding: 5px;
        text-align: right;
        font-size: 20px;
        font-weight: bold;
        margin-top: -18px;
        color: #666;
    }


</style>
<div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content customized-contents">
        <div class="modal-header" style="background: #00659f;color: white">
            <h4 class="modal-title"><?= isset($editData['0']['activity_track']['summary']) ? 'Task - ' . $editData['0']['activity_track']['summary']: 'Add New Task'; ?>  <div style="float: right; display: flex; flex-direction: column; ">
            <span style="font-size: 14px; "><?= isset($created_by) ? 'Created By : ' . $created_by: ''; ?></span>
            <span style="font-size: 12px;"><?= isset($editData['0']['activity_track']['creation_date']) ? $editData['0']['activity_track']['creation_date']: ''; ?></span>
            </div></h4>  
        </div>
        <div class="modal-body">
            <!-- Form starts -->

            <?php if($this->Session->read('emp_fkey')) {
                $isAdmin = false;
            } else {
                $isAdmin = true;
            } ?>

            <?php 
            $hasThePrivilege = false;

            if($isAdmin) {
                $hasThePrivilege = true;
            } else {
                if(isset($editData['0'])) {
                    // editing
                    if($this->Session->read('emp_fkey') === $editData['0']['activity_track']['created_by']) {
                        $hasThePrivilege = true;
                    }
                } else {
                    // create
                    $hasThePrivilege = true;
                }

            }

            if(isset($editData['0']) && $hasThePrivilege == false && $editData['0']['activity_track']['status_fkey'] == 5) {
                $disabledStatus = true;
            } else {
                $disabledStatus = false;
            }
            
            ?>

            <form class="form-horizontal" method="post" action="<?php echo $this->webroot; ?>Activity/save" id="deptForm">
                <div class="modal-body" style="margin: 0 20px; ">
                    <!-- Text input-->

                    <div class="form-group">
                        <label class="control-label" for="user_id">Task Name</label>  
                        <div style="margin-top: 10px; ">
                            <input <?= $disabledStatus ? 'disabled="disabled"' : ''; ?> id="summary" <?= ($isAdmin == false && isset($editData['0']) ) ? 'readonly="true"' : ''; ?> name="summary" value="<?= isset($editData['0']['activity_track']['summary']) ? $editData['0']['activity_track']['summary']: ''; ?>" type="text" placeholder="Enter Task Name" class="form-control input-md" required="true">
                        </div>
                    </div>

                    <input type="hidden" value="<?= isset($editData['0']['activity_track']['activity_track_pkey']) ? $editData['0']['activity_track']['activity_track_pkey']: ''; ?>" name="activity_track_pkey" id="activity_track_pkey" />

                    <?php if(1 == 2) { ?>
                        <input type="hidden" value="<?= isset($editData['0']['activity_track']['emp_fkey']) ? $editData['0']['activity_track']['emp_fkey']: $this->Session->read('emp_fkey'); ?>" name="emp_fkey" id="emp_pkey" />
                        <div class="form-group">
                            <label class="control-label" for="user_id">Project</label>  
                            <div style="margin-top: 10px; ">
                                <select style="width: 100% ; " id="filterby_branch" name="project_fkey" class="form-control" required="required" >
                                    <option value="">Select</option>
                                    <?php foreach ($projects as $key => $value) { ?>                              
                                        <option <?php echo (isset($editData['0']['activity_track']['project_fkey']) && $editData['0']['activity_track']['project_fkey'] == $value['activity_projects']['activity_projects_pkey']) ? 'selected="selected"' : ''; ?> value="<?php echo $value['activity_projects']['activity_projects_pkey']; ?>"><?php echo $value['activity_projects']['activity_projects_head']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>

                    <?php } else { ?>
                        <div class="form-group">
                            <div class="col-md-6" style="padding-left: 0;" >
                                <label class="control-label" for="user_id">Assigned to</label>  
                            </div>
                            <div class="col-md-6" style="padding-right: 0;" >
                                <label class="control-label" for="user_id">Project</label>  
                            </div>

                            <div class="col-md-6" style="padding-left: 0;" >
                                <div style="margin-top: 10px; ">
                                    <select <?= $disabledStatus ? 'disabled="disabled"' : ''; ?> style="width: 100% ; " id="emp_fkey" name="emp_fkey" required="required" class="form-control" >
                                        <option value="">Select</option>
                                        <?php foreach ($arr_users as $key => $value) { ?>                              
                                            <option <?php echo (isset($editData['0']['activity_track']['emp_fkey']) && $editData['0']['activity_track']['emp_fkey'] == $value['EmployeeDetails']['emp_pkey']) ? 'selected="selected"' : ''; ?> value="<?php echo $value['EmployeeDetails']['emp_pkey']; ?>"><?php echo $value['EmployeeDetails']['first_name']; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6" style="padding-right: 0;" >
                                <div style="margin-top: 10px; ">
                                    <select <?= $disabledStatus ? 'disabled="disabled"' : ''; ?> style="width: 100% ; " id="filterby_branch" required="required" name="project_fkey" class="form-control" >
                                        <option value="">Select</option>
                                        <?php foreach ($projects as $key => $value) { ?>                              
                                            <option <?php echo (isset($editData['0']['activity_track']['project_fkey']) && $editData['0']['activity_track']['project_fkey'] == $value['activity_projects']['activity_projects_pkey']) ? 'selected="selected"' : ''; ?> value="<?php echo $value['activity_projects']['activity_projects_pkey']; ?>"><?php echo $value['activity_projects']['activity_projects_head']; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                    
                    <input type="hidden" value="<?= isset($editData['0']['activity_track']['activity_track_pkey']) ? $editData['0']['activity_track']['activity_track_pkey']: ''; ?>" name="activity_track_pkey" id="emp_pkey" />

                    <div class="form-group">
                        <div class="">

                            <div class="col-md-6" style="padding-left: 0;" >
                                <label class="control-label" for="user_id">Activity Type</label>  
                            </div>
                            <div class="col-md-6" style="padding-right: 0;" >
                                <label class="control-label" for="user_id">Activity Status</label>  
                            </div>
                            
                            <div class="col-md-6" style="padding-left: 0; margin-top: 10px; " >
                                <select <?= $disabledStatus ? 'disabled="disabled"' : ''; ?>  style="width: 100% ; padding-left: 0; " id="activity_type_filter" name="activity_type" class="form-control" >
                                    <option value="">Select</option>
                                    <option <?php echo (isset($editData['0']['activity_track']['activity_type']) && $editData['0']['activity_track']['activity_type'] == "daily") ? 'selected="selected"' : ''; ?> value="daily">Daily</option>
                                    <option <?php echo (isset($editData['0']['activity_track']['activity_type']) && $editData['0']['activity_track']['activity_type'] == "monthly") ? 'selected="selected"' : ''; ?>value="monthly">Monthly</option>
                                </select>
                            </div>

                            <div class="col-md-6" style="padding-right: 0; margin-top: 10px; " >
                                <select <?= $disabledStatus ? 'disabled="disabled"' : ''; ?>  style="width: 100% ; " id="status_fkey" required="required" name="status_fkey" class="form-control" >
                                    <?php foreach ($activity_status as $key => $value) { ?>     
                                        <?php if($value['activity_status']['activity_status_pkey'] == 5) { ?>
                                            <?php if($hasThePrivilege === true) { ?>
                                                <option <?php echo (isset($editData['0']['activity_track']['status_fkey']) && $editData['0']['activity_track']['status_fkey'] == $value['activity_status']['activity_status_pkey']) ? 'selected="selected"' : ''; ?> value="<?php echo $value['activity_status']['activity_status_pkey']; ?>"><?php echo $value['activity_status']['activity_status']; ?></option>
                                            <?php } ?>
                                        <?php } else { ?>
                                            <option <?php echo (isset($editData['0']['activity_track']['status_fkey']) && $editData['0']['activity_track']['status_fkey'] == $value['activity_status']['activity_status_pkey']) ? 'selected="selected"' : ''; ?> value="<?php echo $value['activity_status']['activity_status_pkey']; ?>"><?php echo $value['activity_status']['activity_status']; ?></option>
                                        <?php } ?>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="">
                            
                            <div class="col-md-6" style="padding-left: 0;" >
                                <label class="col-md-12 control-label" style="padding: 0;" for="user_id">Due Date</label>
                            </div>
                            <div class="col-md-6" style="padding-right: 0;" >
                                <label class="control-label" style="padding: 0;" for="user_id">Estimated Time</label>
                            </div>

                            <div class="col-md-6" style="padding-left: 0; margin-top: 10px; " >
                                <input <?= $disabledStatus ? 'disabled="disabled"' : ''; ?>  id="activity_date" name="activity_date" autocomplete="off" value="<?= isset($editData['0']['activity_track']['activity_date']) ? $editData['0']['activity_track']['activity_date']: ''; ?>" type="text" placeholder="Due Date" class="form-control input-md" required="true">
                            </div>
                            <div class="col-md-6" style="padding-right: 0; margin-top: 10px; " >
                                <input <?= $disabledStatus ? 'disabled="disabled"' : ''; ?>  id="estimated_time" name="estimated_time" value="<?= isset($editData['0']['activity_track']['estimated_time']) ? $editData['0']['activity_track']['estimated_time']: ''; ?>" type="text" placeholder="Estimated Time in Hours" class="form-control input-md" required="true">
                            </div>
                            
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="">
                            <label class="col-md-4 control-label" for="user_id"  style="padding-left: 0; padding-right: 0; padding-bottom: 8px; " >Description</label>
                            <div class="col-md-12" style="padding-left: 0; padding-right: 0; " >
                                <textarea <?= $disabledStatus ? 'disabled="disabled"' : ''; ?>  class="form-control" id="description" required="required" name="description"><?= isset($editData['0']['activity_track']['description']) ? $editData['0']['activity_track']['description']: ''; ?></textarea>
                            </div>
                        </div>
                    </div>

                    <?php if(isset($editData['0'])) { ?>
                    <div class="form-group">
                        <div class="">
                            <label class="col-md-12 control-label" for="user_id"  style="padding-left: 0; padding-right: 0; padding-bottom: 8px; " >Final Comments</label>
                            <div class="col-md-12"  style="padding-left: 0; padding-right: 0; " >
                                <textarea <?= $disabledStatus ? 'disabled="disabled"' : ''; ?> class="form-control" rows="4" style="padding-top: 10px; " placeholder="Enter Comments Here" id="final_comments" name="final_comments"><?= isset($editData['0']['activity_track']['final_comments']) ? $editData['0']['activity_track']['final_comments']: ''; ?></textarea>
                            </div>
                        </div>
                    </div>
                    <?php } ?>

                    <?php if(isset($editData['0'])) { ?>
                    <div class="form-group">
                        <!-- <a href="javascript:void(0);" style="color: #3f51b5; font-weight: bold; text-decoration: underline; " onclick="toggleShowTransfers(); ">Transfer To</a> -->
                        <div style="margin-top: 10px; " class="showTransfers">
                            <label class="col-md-12 control-label" for="user_id"  style="padding-left: 0; padding-right: 0; " >Select the Employee</label>
                            <select <?= $disabledStatus ? 'disabled="disabled"' : ''; ?> style="width: 100% ; " id="transfer_to" name="transfer_to" class="form-control" >
                                <option value="">Select</option>
                                <?php foreach ($arr_users as $key => $value) { ?>                              
                                    <option <?php echo (isset($editData['0']['activity_track']['transfer_to']) && $editData['0']['activity_track']['transfer_to'] == $value['EmployeeDetails']['emp_pkey']) ? 'selected="selected"' : ''; ?> value="<?php echo $value['EmployeeDetails']['emp_pkey']; ?>"><?php echo $value['EmployeeDetails']['first_name']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                    <?php } ?>

                    <!-- Log Time -->
                    <div class="form-group">
                        <a href="javascript:void(0);" class="btn btn-primary btn-sm" style="font-weight: bold; color: #ffffff; font-weight: bold; background: #00629b; " onclick="toggleShowSubtasks(); ">Log Time</a>
                        <div style="margin-top: 10px; margin-top: 10px; height: 191px; padding: 12px; border-left: 2px solid #07629b; " class="subTasks">
                            
                            <div class="col-md-12" style="padding-left: 0; padding-right: 0; " >
                                <label class="control-label" for="user_id">Work Done</label>  
                                <div style="margin-top: 10px; ">
                                    <input <?= $disabledStatus ? 'disabled="disabled"' : ''; ?> id="summary2" name="summary2" type="text" placeholder="Enter a brief of the work done" class="form-control input-md">
                                </div>
                            </div>

                            <div class="col-md-5" style="padding: 0;" >
                                <label class="col-md-12 control-label" style="padding: 0;" for="user_id">Start Time</label>
                            </div>
                            <div class="col-md-5" style="padding-right: 0;" >
                                <label class="col-md-12 control-label" style="padding: 0;" for="user_id">End Time </label>
                            </div>
                            <div class="col-md-2" style="padding-right: 0;" >
                                <label class="control-label" for="user_id">Duration</label>
                            </div>

                            <div class="col-md-5" style="padding: 0;" >
                                <input <?= $disabledStatus ? 'disabled="disabled"' : ''; ?> id="start_time" onchange="calculateTime(); " name="start_time" type="datetime-local" placeholder="Start Time" class="form-control input-md">
                                <!-- value must be done like this  value="date('Y-m-d\TH:i', strtotime($editData['0']['activity_track']['start_time']))"  -->
                                <!-- max="date('Y-m-d\TH:i'); ?>"  -->
                            </div>
                            <div class="col-md-5" style="padding-right: 0;" >
                                <input <?= $disabledStatus ? 'disabled="disabled"' : ''; ?>  id="end_time" onchange="calculateTime(); " name="end_time" type="datetime-local" placeholder="End Time" class="form-control input-md">
                            </div>
                            <div class="col-md-2" style="padding-right: 0;" >
                                <input <?= $disabledStatus ? 'disabled="disabled"' : ''; ?>  id="duration" name="duration" type="text" placeholder="Duration" class="form-control input-md">
                            </div>

                            <div class="col-md-12 DisplayWarning" style="padding: 6; background: red; display: none; margin-top: 10px; padding: 6px; color: red; " >
                                <label class="col-md-12 control-label" style="padding: 0; font-weight: 600; color: #fff; "></label>
                            </div>

                        </div>

                        <div style="background: #f4f5f7; padding: 10px; margin-top: 20px; ">
                            <?php if(empty($subtasks)) { ?>
                                <div style="text-align: center; padding: 20px; ">
                                    No work has been logged for this issue yet. Logging work lets you track and report on the time spent on Activities.

                                </div>
                            <?php } else { ?>
                                <?php $totalHours = 0; ?>
                                <?php foreach ($subtasks as $key => $value) { ?>
                                    <div class="row" style="background: #ffffff; margin: 10px; ">
                                        <div class="col-md-12" style="  padding: 10px; padding-bottom: 0; ">
                                            <b style=" margin: 0; padding: 5px; "><?= $value['emp_details']['first_name']; ?> - </b>
                                            <b style=" margin: 0; padding: 5px; "><?= $value['activity_track']['summary']; ?></b>
                                        </div>
                                        <div class="col-md-10" style="  padding: 10px; ">
                                            <p style=" margin: 0;  padding: 5px; "><?= $value['activity_track']['start_time']; ?> - <?= $value['activity_track']['end_time']; ?></p>
                                        </div>
                                        <div class="col-md-2" style="  padding: 10px; ">
                                            <p class="durationList" ><?= $value['activity_track']['duration']; ?> hrs</p>
                                        </div>
                                        <a href="javascript:void(0); " onclick="deleteTask('<?= $value['activity_track']['activity_track_pkey']; ?>', this)" style="color: red; float: right; margin: 10px; ">Delete</a>
                                    </div>
                                <?php
                                $totalHours = $totalHours + $value['activity_track']['duration'];
                                } ?>
                                <div style="  text-align: right; margin: 10px; font-size: 14px; font-weight: bold; ">
                                    <span>Estimated Total: </span>
                                    <span><?= isset($editData['0']['activity_track']['estimated_time']) ? $editData['0']['activity_track']['estimated_time']: ''; ?></span>
                                </div>
                                <div style="  text-align: right; margin: 10px; font-size: 16px; font-weight: bold; ">
                                    <span>Total Log Hours: </span>
                                    <span><?= $totalHours; ?></span>
                                </div>
                                <div style="  text-align: right; margin: 5px; font-size: 14px; color: orange; font-weight: bold; ">
                                    <span>Remained: </span>
                                    <span><?= $editData['0']['activity_track']['estimated_time'] - $totalHours; ?></span>
                                </div>
                            <?php } ?>
                        </div>

                    </div>
                    <!-- Log Time -->

                    <div class="form-group">
                        <label class="control-label" for="user_id">Notified To</label>  
                        <div style="margin-top: 10px; ">
                            <select style="width: 100% ; " id="notified_to" name="notified_to" class="form-control" >
                                <option value="">Select</option>
                                <?php foreach ($arr_users as $key => $value) { ?>                              
                                    <option <?php echo (isset($editData['0']['activity_track']['notified_to']) && $editData['0']['activity_track']['notified_to'] == $value['EmployeeDetails']['emp_pkey']) ? 'selected="selected"' : ''; ?> value="<?php echo $value['EmployeeDetails']['emp_pkey']; ?>"><?php echo $value['EmployeeDetails']['first_name']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-md-12">
                            <div class="alert alert-danger showMessage" style="padding: 8px; margin: 10px; display: none; " role="alert">
                        
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" style="font-weight: bold; min-width: 88px; " class="btn btn-danger" data-dismiss="modal">Close</button>
                    <button type="submit" style="font-weight: bold; min-width: 88px; "class="btn btn-submit-access btn-primary">Save</button>
                </div>
            </form>
            <!-- form ends-->
        </div>

    </div>

</div>
<script type="text/javascript">
    $(document).ready(function () {

        $('#activity_date').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true
        });

        var $ckfield = CKEDITOR.replace( 'description', {
            toolbar: [
                { name: 'document', items: [ 'Source', '-', 'NewPage', 'Preview', '-', 'Templates' ] },	// Defines toolbar group with name (used to create voice label) and items in 3 subgroups.
                [ 'Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo' ],			// Defines toolbar group without name.
                '/',																					// Line break - next group will be placed in new line.
                { name: 'basicstyles', items: [ 'Bold', 'Italic', 'NumberedList', 'BulletedList' ] }
            ]
        } );

        $ckfield.on('change', function() {
            $ckfield.updateElement();         
        });

        $("#filterby_branch").select2();
        $("#activity_type_filter").select2();
        $("#status_fkey").select2();
        $("#transfer_to").select2();
        $("#notified_to").select2();
        $("#emp_fkey").select2();

        $("#dept_code").hide();
        $('#deptForm').parsley();
        var options = {
            success: function (responseText, statusText, xhr, $form) {
                
                var response = $.parseJSON(responseText);
                console.log("response", response);
                $('.btn-submit-access').html('Save');
                if(response.success == 1) {
                    $('#uaccess').datagrid('reload');
                    closeActivityModal('dpttable');
                    $(".showMessage").html('').hide();
                    
                    if(response.isSubTask) {
                        
                        if (confirm("Do you want to open the subtask just created?")) {
                            showActivityModalForm(livesite + 'Activity/add?id=' + response.fkey);
                        }
                        
                    } else {
                        alert("Activity Saved Successfully!")
                    }

                } else {
                    $(".showMessage").html(response.msg).show();
                }
            }
        };
        // bind to the form's submit event 
        $('#deptForm').submit(function () {

            if($("#status_fkey").val() == 5) {
                if($("#final_comments").val() == '') {
                    alert("Please submit a final comment before closing the task! ");
                    return false;
                }
            }

            if ($('#start_time').val() > $('#end_time').val()) {
                alert("Start Time and End Time are not valid! ");
            }
            else
            {
                $('.btn-submit-access').html('<li class="fa fa-spinner fa-spin"></li> Saving Changes ... ');
                $(this).ajaxSubmit(options);
//                    $('#uaccess').datagrid('reload');
            }
            // inside event callbacks 'this' is the DOM element so we first 
            // wrap it in a jQuery object and then invoke ajaxSubmit 


            // !!! Important !!! 
            // always return false to prevent standard browser submit and page navigation 
            return false;
        });
    });

    function toggleShowTransfers() {
        $('.showTransfers').toggleClass('show');
    }

    function toggleShowSubtasks() {
        $('.subTasks').toggleClass('show');
        if($('.subTasks').hasClass("show")) {
            $("#summary2").attr("required", true);
            $("#start_time").attr("required", true);
            $("#end_time").attr("required", true);
            $("#duration").attr("required", true);
        } else {
            $("#start_time").attr("required", true);
            $("#end_time").attr("required", true);
            $("#duration").attr("required", true);
            $("#summary2").attr("required", false);
        }
    }

    function calculateTime() {
        var start_time = $("#start_time").val();
        var end_time = $("#end_time").val();

        if(start_time != '' && end_time != '') {
            dt1 = new Date(start_time);
            dt2 = new Date(end_time);
            dt3 = new Date(); // current time

            if(dt1 > dt3 || dt2 > dt3) {
                $("#start_time").val('');
                $("#end_time").val('');
                showLabelWarning('Cannot Enter Future Dates, without finishing it.');
                return false;
            } else {
                hideLabelWarning();
            }

            console.log(diff_hours(dt1, dt2));
            $("#duration").val(diff_hours(dt1, dt2));

            
            console.log("current time differences", diff_hours(dt3, dt2));
            var difference_time = diff_hours(dt3, dt2);
            console.log("diff time in end time", difference_time);
            console.log("current time", dt3);
            if(parseFloat(difference_time) > 1) {
                showLabelWarning('Note: Entered log time seems to be late than 1 hour time period. ');
            } else {
                hideLabelWarning();
            }

        }
    }

    function showLabelWarning(_msg) {
        $(".DisplayWarning").find('label').html(_msg);
        $(".DisplayWarning").show();
    }

    function hideLabelWarning() {
        $(".DisplayWarning").hide();
    }

    function deleteTask(_id, _s) {
        if (confirm("Do you want to delete the selected Logs?")) {

        $.ajax({
            url: livesite + "Activity/deleteLogTime",
            data: {
                id: _id
            },
            success: function (response) {
                $(_s).parent().fadeOut();
            }
        });

        }
    }
    
    function diff_hours(dt2, dt1) 
    {

        var diff =(dt2.getTime() - dt1.getTime()) / 1000;
        diff /= (60 * 60);
        return Math.abs(diff.toFixed(2));
    
    }


</script>