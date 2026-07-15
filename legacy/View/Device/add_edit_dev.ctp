<div class="modal-dialog">
<!-- <?php
// echo "<pre>";
// echo "</pre>";
?> -->
    <!-- Modal content-->
    <div class="modal-content">
        <div class="modal-header" style="background: #00659f;color: white">
            <h4 class="modal-title"><?php echo isset($dev_id) ? 'Edit': 'Add';?> Device</h4>  
        </div>
        <form class="form-horizontal" method="post" id="deviceForm">
            <div class="modal-body">
                <input id="DeviceId" name="DeviceId" type="hidden"  value="<?php echo isset($dev_id) ? $dev_id : ''; ?>" >
                <!-- <div class="form-group">
                    <div class="col-md-12">
                        <label for="course" class="col-sm-4 control-label">Device ID<span class="star <?php echo isset($dev_id) ? 'hide' : '' ; ?>">*</span></label>
                        <div class="col-sm-1">:</div>
                        <div class="col-sm-7">
                            <input type="number" class="form-control" value="<?php echo (isset($dev_data['DeviceId'])) ? $dev_data['DeviceId'] : ''; ?>" name="new_DeviceId" id="new_DeviceId" <?php echo isset($dev_id) ? 'disabled': 'required=""';?> >
                            <span style="color : red" class="hide" id="id_error">Device ID Already Exist!</span>
                        </div>
                    </div>
                </div> -->
                <div class="form-group">
                    <div class="col-md-12">
                        <label for="course" class="col-sm-4 control-label">Device Name<span class="star">*</span></label>
                        <div class="col-sm-1">:</div>
                        <div class="col-sm-7">
                            <input type="text" class="form-control" value="<?php echo (isset($dev_data['DeviceFName'])) ? $dev_data['DeviceFName'] : ''; ?>" name="DeviceFName" id="DeviceFName" required="">
                        </div>
                    </div>
                </div>
                <!-- <div class="form-group">
                    <div class="col-md-12">
                        <label for="university" class="col-sm-4 control-label">Branch Code</label>
                        <div class="col-sm-1">:</div>
                        <div class="col-sm-7">
                            <input type="text" class="form-control" value="<?php echo (isset($dev_data['branch_code'])) ? $dev_data['branch_code'] : ''; ?>" name="branch_code" id="branch_code" <?php echo isset($dev_id) ? 'readonly': '';?>>
                        </div>
                    </div>
                </div> -->
                <div class="form-group">
                    <div class="col-md-12">
                        <label for="university" class="col-sm-4 control-label">Branch Name<span class="star">*</span></label>
                        <div class="col-sm-1">:</div>
                        <div class="col-sm-7">
                            <!-- <input type="hidden" value="<?php echo (isset($dev_data['branch_code'])) ? $dev_data['branch_code'] : ''; ?>" name="edit_branch_code" id="edit_branch_code"> -->
                            <select id="branch_code" name="branch_code" class="form-control js-example-basic-single" style="width: 100%" required="" >
                                <option value="">--Select--</option>
                                <?php
                                foreach($branches as $branch){
                                    $selected = (isset($dev_data['branch_code']) && $dev_data['branch_code'] == $branch['company_branches']['branch_code']) ? 'selected' : '';
                                    echo "<option value = '".$branch['company_branches']['branch_code']."' ".$selected.">".$branch['company_branches']['branch_name']."</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-12">
                        <label for="duration" class="col-sm-4 control-label">Serial Number<span class="star">*</span></label>
                        <div class="col-sm-1">:</div>
                        <div class="col-sm-7">
                            <input type="text" required="required" class="form-control" value="<?php echo (isset($dev_data['SerialNumber'])) ? $dev_data['SerialNumber'] : ''; ?>" name="SerialNumber" id="SerialNumber" >
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-12">
                        <label for="mark" class="col-sm-4 control-label">Device Location<span class="star">*</span></label>
                        <div class="col-sm-1">:</div>
                        <div class="col-sm-7">
                            <input type="text" required="required" class="form-control" value="<?php echo (isset($dev_data['DeviceLocation'])) ? $dev_data['DeviceLocation'] : ''; ?>" name="DeviceLocation" id="DeviceLocation" >
                        </div>
                    </div>
                </div>

            </div>           
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" onclick="$('#modalForm').modal('hide');">Cancel</button>
                <button type="submit" id="btn-submitempdev" class="btn btn-primary"><?php echo isset($dev_id) ? 'Update': 'Save';?></button>
            </div>

        </form>
    </div>
</div>


<script type="text/javascript">
    $(document).ready(function () {
        $("#id_error").addClass("hide");
        $("#btn-submitempdev").prop("disabled",false);

        $("#branch_code").select2();

        // $("#deviceForm #new_DeviceId").keyup(function(){
        //     let deviceId = $("#new_DeviceId").val();

        //     $.ajax({
        //         url: 'Device/checkDevice',
        //         type: 'POST',
        //         data: {
        //             deviceId: deviceId
        //         },
        //         success:function(data){
        //             if(data == "EXIST"){
        //                 $("#id_error").removeClass("hide");
        //                 $("#id_error").addClass("show");
        //                 $("#btn-submitempdev").prop("disabled",true);
        //             } else {
        //                 $("#id_error").removeClass("show");
        //                 $("#id_error").addClass("hide");
        //                 $("#btn-submitempdev").prop("disabled",false);
        //             }
        //         }
        //     });
        // });

        $('#deviceForm').parsley();
        var options = {
            success: function (responseText, statusText, xhr, $form) {
                $.notify("Employee Device Updated Successfully", {
                    type: 'success',
                    allow_dismiss: false
                });
                $('#modalForm').modal('hide');
                //$("#example1").DataTable().ajax.reload();
                // $("#deviceForm").DataTable().ajax.url(livesite + "Employee/listqualifications/" + $('#empsetuppersonal #emp_pkey').val()).load();
                $('#device').datagrid('load');
            }
        };

        // bind to the form's submit event
        $('#deviceForm').submit(function () {
            if(confirm('Are you sure want to save?')){
                $('#deviceForm').attr('action', livesite + 'Device/saveDev');
                if ($('#deviceForm #SerialNumber').val() == '' || $('#deviceForm #DeviceLocation').val() == '') {
                    alert('Please fill mandatory fields first!');
                } else {
                    $('#btn-submitempdev').html('<li class="fa fa-spinner fa-spin"></li> saving...').attr('disabled', 'disabled');
                    $(this).ajaxSubmit(options);
                }
            }
            return false;
        });
        //Ends  
    });
</script>