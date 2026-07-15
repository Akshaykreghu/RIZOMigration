<div class="modal-dialog">
<!-- <?php
// echo "<pre>";
// print_r($emp_dev_data);
// echo $emp_dev_seq;
// echo "</pre>";
?> -->
    <!-- Modal content-->
    <div class="modal-content">
        <div class="modal-header" style="background: #00659f;color: white">
            <h4 class="modal-title">Edit Employee Device</h4>  
        </div>
        <form class="form-horizontal" method="post" id="empDevice">
            <div class="modal-body">
                <input id="emp_device_comp_branch_seq" name="emp_device_comp_branch_seq" type="hidden"  value="<?php echo $emp_dev_seq; ?>" >
                <div class="form-group">
                    <div class="col-md-12">
                        <label for="course" class="col-sm-4 control-label">Employee ID</label>
                        <div class="col-sm-1">:</div>
                        <div class="col-sm-7">
                            <input type="text" class="form-control" value="<?php echo $emp_dev_data['emp_username']; ?>" name="emp_username" id="emp_username" readonly>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-12">
                        <label for="university" class="col-sm-4 control-label">Employee Name</label>
                        <div class="col-sm-1">:</div>
                        <div class="col-sm-7">
                            <input type="text" class="form-control" value="<?php echo $emp_dev_data['emp_name']; ?>" name="emp_name" id="emp_name" readonly>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-12">
                        <label for="duration" class="col-sm-4 control-label">Device ID<span class="star">*</span></label>
                        <div class="col-sm-1">:</div>
                        <div class="col-sm-7">
                            <!-- <input type="number" required="required" class="form-control" value="<?php echo $emp_dev_data['deviceid']; ?>" name="deviceid" id="deviceid" > -->
                            <select id="deviceid" name="deviceid" class="form-control js-example-basic-single" style="width: 100%" required="" onchange="selectSerial();">
                                <option value="">--Select--</option>
                                <?php
                                foreach($devices as $device){
                                    $selected = (isset($emp_dev_data['deviceid']) && $emp_dev_data['deviceid'] == $device['devices']['DeviceId']) ? 'selected' : '';
                                    echo "<option value = '".$device['devices']['DeviceId']."' ".$selected.">".$device['devices']['DeviceFName']."</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-12">
                        <label for="mark" class="col-sm-4 control-label">Serial Number</label>
                        <div class="col-sm-1">:</div>
                        <div class="col-sm-7">
                            <select id="SerialNumber" name="SerialNumber" class="form-control js-example-basic-single" style="width: 100%" disabled>
                                <option value=""></option>
                                <?php
                                foreach($devices as $device){
                                    $selected = (isset($emp_dev_data['deviceid']) && $emp_dev_data['deviceid'] == $device['devices']['DeviceId']) ? 'selected' : '';
                                    echo "<option value = '".$device['devices']['DeviceId']."' ".$selected.">".$device['devices']['SerialNumber']."</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-12">
                        <label for="mark" class="col-sm-4 control-label">Employee Device ID<span class="star">*</span></label>
                        <div class="col-sm-1">:</div>
                        <div class="col-sm-7">
                            <input type="text"  required="required" class="form-control" value="<?php echo $emp_dev_data['emp_device_id']; ?>" name="emp_device_id" id="emp_device_id">
                        </div>
                    </div>
                </div>

            </div>           
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" onclick="$('#modalForm').modal('hide');">Cancel</button>
                <button type="submit" id="btn-submitempdev" class="btn btn-primary">Save</button>
            </div>

        </form>
    </div>
</div>


<script type="text/javascript">
    $(document).ready(function () {

        $("#deviceid").select2();

        $('#empDevice').parsley();
        var options = {
            success: function (responseText, statusText, xhr, $form) {
                $.notify("Employee Device Updated Successfully", {
                    type: 'success',
                    allow_dismiss: false
                });
                $('#modalForm').modal('hide');
                //$("#example1").DataTable().ajax.reload();
                // $("#empdevice").DataTable().ajax.url(livesite + "Employee/listqualifications/" + $('#empsetuppersonal #emp_pkey').val()).load();
                $('#empdevice').datagrid('load');
            }
        };

        // bind to the form's submit event
        $('#empDevice').submit(function () {
            if(confirm('Are you sure want to save?')){
                $('#empDevice').attr('action', livesite + 'DeviceEmployeeInfo/saveEmpDev');
                if ($('#empDevice #deviceid').val() == '' || $('#empDevice #emp_device_id').val() == '') {
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

    function selectSerial(){
        $("#SerialNumber").val($("#deviceid").val());
    }
</script>