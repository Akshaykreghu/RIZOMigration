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
            <h4 class="modal-title"> Employee EMI Upload</h4>  
        </div>
        <div class="modal-body">
            <!-- Form starts -->

            <form class="form-horizontal" id="form-user-master" action="<?php echo $this->webroot; ?>EmployeeLoan/employeeemiloansave" method="POST">

                <div class="modal-body">
                    <div class="form-group">
                        <div class="col-md-10">
                            <label for="in_date" class="col-sm-4 control-label">Choose Employee<span class="star">*</span></label>
                            <div class="col-md-7">
                                <select id="emp_fkey1" class="form-control" style="width: 100%; " name="emp_pkey"  onchange="findextingsalary();" required="required" >
                                    <option value="">[--Select--]</option>
                                    <?php
//                                    foreach ($arr_employees as $value) {
//                                        $selected = ($data['emp_fkey'] == $value['EmployeeDetails']['emp_pkey']) ? 'selected="selected"' : '';
//
//                                        echo '<option value="' . $value['EmployeeDetails']['emp_pkey'] . '" ' . $selected . '>' . $value['EmployeeDetails']['first_name'] . ' ' . $value['EmployeeDetails']['last_name'] . ' - ' . $value['EmployeeDetails']['emp_id'] . '</option>';
//                                    }
                                    foreach ($arr_employees as $key => $value) {
                                    ?>
                                        <option value="<?php echo $value['emp_details']['emp_pkey']; ?>"><?php echo $value['emp_details']['first_name'].' '.$value['emp_details']['last_name'].' - '.$value['emp_proff']['emp_company_id']; ?></option>    
                                    <?php
                                    }
                                    ?>
                                </select>                            </div>
                        </div>
                    </div>


                    <div class="form-group">
                        <div class="col-md-10">
                            <label for="out_time" class="col-sm-4 control-label">Select Loan <span class="star">*</span></label>
                            <div class="col-md-7">
                                <select id="emi_amt" class="form-control" name="loan_pkey"  required="required" >
                                    <option value="">[--Select--]</option>
                                    <!-- added by amal -->
                                     <?php foreach ($arr_loan as $key => $val) { ?>

                                      <option value="<?php echo $val['loan']['amount']; ?>"><?php echo $val['loan']['loan_amount']; ?></option>  
                                   <?php } ?>
                                      <!-- added by amal -->
                                </select>
                            </div> 
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-10">
                            <label for="out_date" class="col-sm-4 control-label">Amount Paid</label>
                            <div class="col-md-7">
                                <input type="text" class="form-control" class="form-control" value="" name="loan_emi" id="loan_emi">
                            </div> 
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-10">
                            <label for="out_date" class="col-sm-4 control-label">Remark</label>
                            <div class="col-md-7">
                                <input type="text" class="form-control" class="form-control" value="<?php echo $data['remarks'] ?>" name="remarks" id="remarks">
                            </div> 
                        </div>
                    </div>


                    
                        <div class="modal-footer">
                            <input type="hidden" required="required" class="form-control" id="month_year" value=""name="month_year"      >
                            <input type="hidden" required="required" class="form-control" value="<?php echo $data['emp_loan_pkey'] ?>"name="emp_loan_pkey"      >
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

    //already  
    function findextingsalary() {
        var month = $('#ctc_upload_type').val();
        var emp_fkey = $('#emp_fkey1').val();
        var url = 'EmployeeLoan/getEmi';
        $('.form-control').attr("disabled",true);
//value passiing ajax   
        $.ajax({
            url: url,
            type: 'post',
            data: {
                month_year: month,
                empid: emp_fkey,
            },
            success: function (resp) {
                var json_obj = $.parseJSON(resp);
                if (json_obj.success == 1) {
                    
                    $('#emi_amt').html(json_obj.data);
//                    $('#loan_emi').val(json_obj.sum);
                    $('.form-control').attr("disabled",false)
                } else {
                    $('.form-control').attr("disabled",false);
                }
            }
        });
    }

//    function loancal() {
//        var amount = $('#loan_amount').val();
//        var month = $('#tenure').val();
//        var result = amount / month;
//        $("#emi_amount").val(result.toFixed(2));
//
//    };
    $(document).ready(function () {

    $('#emp_fkey1').select2();
    $('#month_year').val($('#ctc_upload_type').val());


        // bind to the form's submit event 
        $('#attendanceuploadtable').submit(function () {
            $('#btn-submit').html('<li class="fa fa-spinner fa-spin"></li>Saving').prop("disabled", true);
            $(this).ajaxSubmit(options);
            return false;
        });

    });
</script>