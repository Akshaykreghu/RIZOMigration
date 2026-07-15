<script>
    $.validate({
        form: '#form-user-master'
    });
    var options = {
        success: function (resp) {
            $('#modalForm').modal('hide');
            $('#att_table').datagrid('reload');
            $('#attdatacsv').val('');
            $("#filterby_branch").select2("val", "");
            $("#emp_fkey").select2("val", "");

            $.notify($.parseJSON(resp).msg, {
                type: 'success',
                allow_dismiss: false
            });
        }  // post-submit callback
    };


    $('#form-user-master').on('submit', function (event) {
        event.preventDefault();
        if (confirm(" Do You Want  To Save The Form")) {
            $('#form-user-master').ajaxSubmit(options)
        }
    });


</script>
<div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title"> Equipments Master</h4>  
        </div>
        <div class="modal-body">
            <!-- Form starts -->

            <form class="form-horizontal" id="form-user-master" action="<?php echo $this->webroot; ?>FieldSurvey/saveEquip" method="POST">

                <div class="modal-body">


                    <div class="form-group">
                        <div class="col-md-10">
                            <label for="out_date" class="col-sm-4 control-label">Equipment Name</label>
                            <div class="col-md-7">
                                <input type="hidden" class="form-control" class="form-control" value="<?php echo isset($arr_equipment_data['0']['efsr_equipments_master'])?$arr_equipment_data['0']['efsr_equipments_master']['efsr_equipments_master_pkey']:''; ?>" name="efsr_equipments_master_pkey" id="equipments_name">
                                <input type="text" class="form-control" class="form-control" value="<?php echo isset($arr_equipment_data['0']['efsr_equipments_master'])?$arr_equipment_data['0']['efsr_equipments_master']['equipments_name']:''; ?>" name="equipments_name" id="equipments_name">
                            </div> 
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-10">
                            <label for="out_date" class="col-sm-4 control-label">Equipment type</label>
                            <div class="col-md-7">
                                <select id="equipments_type" style="width: 100%; " name="equipments_type" class="emp_fkey form-control js-example-basic-single" onchange="filterAttendanceupload(this);" >
                                         <?php foreach ($arr_types as $value) {
                                        ?>
                                    <option <?php if(isset($arr_equipment_data['0']['efsr_equipments_master']['equipments_type']) && $arr_equipment_data['0']['efsr_equipments_master']['equipments_type'] == $value['equipment_type']['equipment_type_pkey']){ echo 'selected="selected"'; } ?> value="<?php echo $value['equipment_type']['equipment_type_pkey']; ?>"><?php echo $value['equipment_type']['equipment_name'] ;?></option>
                                        <?php
                                         }       ?>
                                </select>
                            </div> 
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-10">
                            <label for="out_date" class="col-sm-4 control-label">Site Name</label>
                            <div class="col-md-7">
                                <select id="site_fkey" style="width: 100%; " name="site_fkey" class="emp_fkey form-control js-example-basic-single" >
                                         <?php foreach ($arr_site_list as $value) {
                                        ?>
                                    <option <?php if(isset($arr_equipment_data['0']['efsr_equipments_master']['equipments_type']) && $arr_equipment_data['0']['efsr_equipments_master']['site_fkey'] == $value['efsr_site']['efsr_site_pkey']){ echo 'selected="selected"'; } ?> value="<?php echo $value['efsr_site']['efsr_site_pkey']; ?>"><?php echo $value['efsr_site']['site_name'] ;?></option>
                                        <?php
                                         }       ?>
                                </select>
                            </div> 
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-10">
                            <label for="out_date" class="col-sm-4 control-label">Manufacturer</label>
                            <div class="col-md-7">
                                <input type="text" class="form-control" class="form-control" value="<?php echo isset($arr_equipment_data['0']['efsr_equipments_master'])?$arr_equipment_data['0']['efsr_equipments_master']['manufacturer']:''; ?>" name="manufacturer" id="manufacturer">
                            </div> 
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-10">
                            <label for="out_date" class="col-sm-4 control-label">Model No</label>
                            <div class="col-md-7">
                                <input type="text" class="form-control" class="form-control" value="<?php echo isset($arr_equipment_data['0']['efsr_equipments_master'])?$arr_equipment_data['0']['efsr_equipments_master']['model_no']:''; ?>" name="model_no" id="model_no">
                            </div> 
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-10">
                            <label for="out_date" class="col-sm-4 control-label">Equipments Specifications</label>
                            <div class="col-md-7">
                                <input type="text" class="form-control" class="form-control" value="<?php echo isset($arr_equipment_data['0']['efsr_equipments_master'])?$arr_equipment_data['0']['efsr_equipments_master']['equipments_spec']:''; ?>" name="equipments_spec" id="equipments_spec">
                            </div> 
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-10">
                            <label for="out_date" class="col-sm-4 control-label">Serial Number</label>
                            <div class="col-md-7">
                                <input type="text" class="form-control" class="form-control" value="<?php echo isset($arr_equipment_data['0']['efsr_equipments_master'])?$arr_equipment_data['0']['efsr_equipments_master']['equipments_sn']:''; ?> " name="equipments_sn" id="equipments_sn">
                            </div> 
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-10">
                            <label for="out_date" class="col-sm-4 control-label">Year make</label>
                            <div class="col-md-7">
                                <input type="text" class="form-control" class="form-control" value="<?php echo isset($arr_equipment_data['0']['year_makes'])?$arr_equipment_data['0']['efsr_equipments_master']['year_makes']:''; ?> " name="year_makes" id="equipments_sn">
                            </div> 
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-10">
                            <label for="out_date" class="col-sm-4 control-label">Supplied By</label>
                            <div class="col-md-7">
                                <input type="text" class="form-control" class="form-control" value="<?php echo isset($arr_equipment_data['0']['efsr_equipments_master'])?$arr_equipment_data['0']['efsr_equipments_master']['supplied_by']:''; ?>" name="supplied_by" id="supplied_by">
                            </div> 
                        </div>
                    </div>


                    
                        <div class="modal-footer">
                            <!--<input type="hidden" required="required" class="form-control" id="site_fkey" value="" name="site_fkey"      >-->
                            <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                            <button type="submit"  name="btn-submit" id="btn-submit" class="btn btn-success">Save</button>

                        </div>


                </div>



            </form>
            <!-- Tax Head Detail Form -->





            <!-- form ends-->
        </div>

    </div>

</div>
<script type="text/javascript">
//number valiadtion
//    function numbevalidation() {
////        var loan = $('#loan_amount').val(); .
////        var rate = $('#intrest_rate').val();
////        var ten = $('#tenure').val();
//            
//        
//    }

    

//    function loancal() {
//        var amount = $('#loan_amount').val();
//        var month = $('#tenure').val();
//        var result = amount / month;
//        $("#emi_amount").val(result.toFixed(2));
//
//    };
    $(document).ready(function () {

    $(".emp_fkey").select2();
    
//    $('#site_fkey').val($('#filterby_branch').val());


        // bind to the form's submit event 
        $('#attendanceuploadtable').submit(function () {
            $('#btn-submit').html('<li class="fa fa-spinner fa-spin"></li>Saving').prop("disabled", true);
            $(this).ajaxSubmit(options);
            return false;
        });

    });
</script>