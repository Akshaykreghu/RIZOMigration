<section>
    <div class="row">
    <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"> Allocate Employee's To Site </h4>  
            </div>
            <div class="modal-body">
                    <div class="form-group">
                        <input type="hidden" id="store_pkey" name="store_pkey" value="<?php echo isset($store_pkey)?$store_pkey:''; ?>" >

                        <label for="attendance_type" class="col-sm-4 control-label">Employee Name<span class="star">*</span></label>                         

                        <div class="col-md-8">
                            <select id="emp_fkeys" class="form-control js-example-basic-single pull-left" style="width: 60%; " name="emp_fkeys" onchange="filterAttendanceupload(this);" >
                                <?php
                                foreach ($arr_employees as $value) {
                                    echo '<option value="' . $value['EmployeeDetails']['emp_pkey'] . '" >' . $value['EmployeeDetails']['first_name'] . ' ' . $value['EmployeeDetails']['last_name'] . '</option>';
                                }
                                ?>
                            </select>
                            <button style="margin-left: 12px; " onclick="allocate_emps(); " class="btn btn-primary">Add</button>
                        </div>
                        </div>


                    <div class="row">
                        <div class="col-md-12">
                            <h4>Allocated Employees </h4>
                            <table class="table table-responsive table-bordered">
                                <tbody id="allocating">
                                    <tr>
                                        <th>Employee Name</th>
                                        <th>Action</th>
                                    </tr>
                                    <?php foreach($arr_employees_allocates as $val){ ?>
                                    <tr>
                                        <td><?php echo $val['emp_details']['first_name'].' '.$val['emp_details']['last_name']; ?></td>
                                        <td><button class="btn btn-danger"><li class="fa fa-remove" onclick="removeemps(this,<?php echo $val['emp_details']['emp_pkey']; ?>)"></li></button></td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    </div> 
            </div>
        </div>
    </div>
</section>
<script>
    $('#emp_fkeys').select2();
    
    function allocate_emps(){
        var store_code = $('#store_pkey').val();
        var emps = $('#emp_fkeys').val();
        $.ajax({
            url: 'store/save_allocate/',
            type: 'POST',
            data: {
                store_code: store_code,
                emps:emps
            },
            success: function (resp)
            {
                var response = $.parseJSON(resp);
                if(response.success == 0){
                    $.notify($.parseJSON(resp).msg, {
                        type: 'danger',
                        allow_dismiss: false
                    });
                }else{
                    $.notify($.parseJSON(resp).msg, {
                        type: 'success',
                        allow_dismiss: false
                    });
                    $('#allocating').append('<tr><td>'+ $('#emp_fkeys  option:selected').html() + '</td><td><button class="btn btn-danger"><li class="fa fa-remove" onclick="removeemps(this,' + emps + ')"></li></button></td></tr>');
                }
            }
        });
    }
    
    function removeemps(s,emps){
        var store_code = $('#store_pkey').val();
        var emps = emps;
        $.ajax({
            url: 'store/remove_allocate/',
            type: 'POST',
            data: {
                store_code: store_code,
                emps:emps
            },
            success: function (resp)
            {
                var response = $.parseJSON(resp);
                if(response.success == 0){
                    $.notify($.parseJSON(resp).msg, {
                        type: 'danger',
                        allow_dismiss: false
                    });
                }else{
                    $.notify($.parseJSON(resp).msg, {
                        type: 'success',
                        allow_dismiss: false
                    });
                    $(s).parent().parent().parent().hide();
                }
            }
        });
    }
</script>