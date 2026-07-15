<style>
    .form-horizontal .control-label {
        text-align: left;
        /*padding-left: 76px;*/
    }
</style>
<div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
        <div class="modal-header" style="background: #00659f;color: white">
            <h4 class="modal-title">Edit User Access</h4>  
        </div>
        <div class="modal-body">
            <!-- Form starts -->

            <form class="form-horizontal" method="post" action="<?php echo $this->webroot; ?>UserCredentials/save" id="deptForm">
                <div class="modal-body">
                    <!-- Text input-->
                    <input id="id" name="id" type="hidden"  value="<?php echo $data_db['UserCredentials']['user_pkey']; ?>" >
                    <input id="fnam" name="fnam" type="hidden"  value="<?php echo $data_db['UserCredentials']['first_name']; ?>" >
                    <input id="lnam" name="lnam" type="hidden"  value="<?php echo $data_db['UserCredentials']['last_name']; ?>" >
                    <div class="form-group">
                        <div class="col-md-12">
                            <label class="col-md-4 control-label" for="user_id">User Name</label>
                            <div class="col-md-1">:</div>
                            <div class="col-md-7">
                                <input readonly="readonly" id="dept_name" name="user_id"  value="<?php echo $data_db['UserCredentials']["user_id"]; ?>" type="text" placeholder="Department Name" class="form-control input-md" required="">

                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-12">
                            <label class="col-md-4 control-label" for="user_id">User email</label>  
                            <div class="col-md-1">:</div>
                            <div class="col-md-7">
                                <input id="dept_name" name="email"  value="<?php echo $data_db['UserCredentials']["email"]; ?>" type="text" placeholder="Email" class="form-control input-md" required="">

                            </div>
                        </div>
                    </div>

                    <!-- Text input-->
                    <div class="form-group">
                        <div class="col-md-12">
                            <label class="col-md-4 control-label" for="Active">Password Reset </label>   
                            <div class="col-md-1">:</div>
                            <div class="col-md-7">
                                <input type="checkbox" value="y" id="Active" name="status"/> <input id="dept_code" name="pasword"  value="" autocomplete="off" type="text"  title="Must contain at least one number and one uppercase and lowercase letter, and at least 8 or more characters" placeholder="Enter New Password" class="form-control input-md" >

                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-12">
                            <label  class="col-md-4 control-label" for="locked">Locked</label>
                            <div class="col-md-1">:</div>
                            <div class="col-md-7">
                                <select id="locked" class="form-control"  name="locked" required="required" >

                                    <option value="0"  <?php echo (isset($data_db['UserCredentials']['locked']) && (($data_db['UserCredentials']['locked']) == '0' || ($data_db['UserCredentials']['locked']) == '0') ) ? 'selected="selected"' : ''; ?> >NO</option>
                                    <option value="1"  <?php echo (isset($data_db['UserCredentials']['locked']) && (($data_db['UserCredentials']['locked']) == '1' || ($data_db['UserCredentials']['locked']) == '1') ) ? 'selected="selected"' : ''; ?>>YES</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-12">
                            <label  class="col-md-4 control-label" for="access_allowed">Access Allowed</label>
                            <div class="col-md-1">:</div>
                            <div class="col-md-7">
                                <select id="access_allowed" class="form-control"  name="access_allowed" required="required" >
                                    <option value="N" <?php echo (isset($data_db['UserCredentials']['access_allowed']) && (($data_db['UserCredentials']['access_allowed']) == 'n' || ($data_db['UserCredentials']['access_allowed']) == 'N') ) ? 'selected="selected"' : ''; ?>>NO</option>
                                    <option value="Y" <?php echo (isset($data_db['UserCredentials']['access_allowed']) && (($data_db['UserCredentials']['access_allowed']) == 'y' || ($data_db['UserCredentials']['access_allowed']) == 'Y') ) ? 'selected="selected"' : ''; ?> >YES</option>

                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-12">
                            <label  class="col-md-4 control-label" for="Mobile_Allow">Mobile Access Allowed</label>
                            <div class="col-md-1">:</div>
                            <div class="col-md-7">
                                <select id="Mobile_Allow" class="form-control"  name="Mobile_Allow" required="required" >
                                    <?php isset($data_db['Mob']['locked']) ? $data_db['Mob']['locked'] : 'N'; ?>
                                    <option value="Y" <?php echo (isset($data_db['Mob']['locked']) && (($data_db['Mob']['locked']) == 'n' || ($data_db['Mob']['locked']) == 'N') ) ? 'selected="selected"' : ''; ?> >YES</option>
                                    <option value="N" <?php echo (isset($data_db['Mob']['locked']) && (($data_db['Mob']['locked']) == 'y' || ($data_db['Mob']['locked']) == 'Y') ) ? 'selected="selected"' : ''; ?>>NO</option>

                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-12">
                            <label  class="col-md-4 control-label" for="punch_type">Punch Type</label>
                            <div class="col-md-1">:</div>
                            <div class="col-md-7">
                                <select id="punch_type" class="form-control"  name="punch_type" required="required" >
                                    <option value="M" <?php echo (isset($data_db['Mob']['punchtype']) && (($data_db['Mob']['punchtype']) == 'M' || ($data_db['Mob']['punchtype']) == 'm') ) ? 'selected="selected"' : ''; ?>>Mobile from anywhere</option>
                                    <option value="W" <?php echo (isset($data_db['Mob']['punchtype']) && (($data_db['Mob']['punchtype']) == 'W' || ($data_db['Mob']['punchtype']) == 'w') ) ? 'selected="selected"' : ''; ?>>Machine</option>
                                    <option value="O" <?php echo (isset($data_db['Mob']['punchtype']) && (($data_db['Mob']['punchtype']) == 'O' || ($data_db['Mob']['punchtype']) == 'o') ) ? 'selected="selected"' : ''; ?>>Mobile from office only</option>
                                    <option value="S" <?php echo (isset($data_db['Mob']['punchtype']) && (($data_db['Mob']['punchtype']) == 'S' || ($data_db['Mob']['punchtype']) == 's') ) ? 'selected="selected"' : ''; ?>>Web</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <!-- Tracking access field -- start added by megha-->
<!--edited by sinsiya on 16-10-2024-->
                    <!--<div class="form-group">
                        <div class="col-md-12">
                            <label  class="col-md-4 control-label" for="Tracking_Allow">Tracking Access</label>
                            <div class="col-md-1">:</div>
                            <div class="col-md-7">         
                                <select id="Tracking_Allow" class="form-control"  name="Tracking_Allow" required="required" >
                                    <?php //isset($data_db['Mob']['is_track']) ? $data_db['Mob']['is_track'] : 'N'; ?>
                                    <option value="N" <?php //echo (isset($data_db['Mob']['is_track']) && (($data_db['Mob']['is_track']) == 'n' || ($data_db['Mob']['is_track']) == 'N') ) ? 'selected="selected"' : ''; ?>>DISABLE</option>
                                    <option value="Y" <?php //echo (isset($data_db['Mob']['is_track']) && (($data_db['Mob']['is_track']) == 'y' || ($data_db['Mob']['is_track']) == 'Y') ) ? 'selected="selected"' : ''; ?>>ENABLE</option>

                                </select>
                            </div>
                        </div>
                    </div>--> 

                    <div class="form-group">
                        <div class="col-md-12">
                            <label class="col-md-4 control-label" for="profile_id">Profile ID (Optional)</label>
                            <div class="col-md-1">:</div>
                            <div class="col-md-7">
                                <input  id="profile_id" name="attr2"  value="<?php echo $data_db['UserCredentials']["attr2"]; ?>" type="text" placeholder="Profile ID" class="form-control input-md" >
                            </div>
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
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary btn-sucees">Save</button>
                </div>
            </form>
            <!-- form ends-->
        </div>

    </div>

</div>
<script type="text/javascript">
    $(document).ready(function () {

        $("#dept_code").hide();
        $('#deptForm').parsley();
        var options = {
            success: function (responseText, statusText, xhr, $form) {
                
                var response = $.parseJSON(responseText);
                console.log("response", response);
                $('.btn-sucees').html('Save');
                if(response.thirdPartyApi.status == 200) {
                    $('#uaccess').datagrid('reload');
                    closeModal('dpttable');
                    $(".showMessage").html('').hide();
                } else {
                    //edited by megha on 13-06-2025
                   // $(".showMessage").html(response.thirdPartyApi.detail).show();
                   $(".showMessage").html(response.thirdPartyApi.fieldErrors[0].message).show();
                }
            }
        };
        $('input:checkbox[id="Active"]').change(
                function () {
                    if ($(this).is(':checked')) {
                        $(this).val('Y');
                        $status = 'n';
//           	$("#site_id").attr("disabled", "disabled"); 
//    		$("#site_name").attr("disabled", "disabled");

                        $("#dept_code").show();
                    }
                    else
                    {
                        $("#dept_code").hide();
                        $("#dept_code").val('');
                        $(this).val('N');
                    }

                });
        // bind to the form's submit event 
        $('#deptForm').submit(function () {
            if ($('#Active').is(':checked') && $('#dept_code').val() == '') {
                alert("Password Can not be empty");
            }
            else
            {
                $('.btn-sucees').html('<li class="fa fa-spinner fa-spin"></li> Saving Changes ... ');
                $(this).ajaxSubmit(options);
                $('#uaccess').datagrid('reload');
                    //closeModal('dpttable');
                    //$(".showMessage").html('').hide();
            }
            // inside event callbacks 'this' is the DOM element so we first 
            // wrap it in a jQuery object and then invoke ajaxSubmit 


            // !!! Important !!! 
            // always return false to prevent standard browser submit and page navigation 
            return false;
        });
    });
</script>