<?php ?>

<script type="text/javascript">
    function rejpromoion() {
        var promotion_pkey = $('#emp_pkey').val();
        $.ajax({
            url: livesite + 'Promo/rejsave',
            type: 'post',
            data: {
                promotion_pkey: promotion_pkey
            },
            success: function (response) {
                //process server response here
                var resp = $.parseJSON(response);
                $.notify(resp.msg, {
                    type: resp.color,
                    allow_dismiss: false
                });
                $('#largeModalForm').modal('hide');
                $('#emptable').datagrid('load');
            }
        });
    }

    $(document).ready(function () {
        /*
         * Tax Head save
         */



        $('#approve').parsley();
        var options = {
            success: function (responseText, statusText, xhr, $form) {
                var resp = $.parseJSON(responseText);
                $.notify(resp.msg, {
                    type: resp.color,
                    allow_dismiss: false
                });
                $('#largeModalForm').modal('hide');
                $('#emptable').datagrid('load');
            }
        };

        // bind to the form's submit event
        $('#approve').submit(function () {
            $('#approve').attr('action', livesite + 'Promo/approvesave');

            $('#btn-submitfami').html('<li class="fa fa-spinner fa-spin"></li> saving...').attr('disabled', 'disabled');
            $(this).ajaxSubmit(options);

            return false;
        });
        //Ends  
    });
</script>
<div class="modal-body">
    <!-- Form Name -->
    <legend>Promotion  - <?php echo isset($arr_empdetails['0']['EmployeeDetails']['first_name']) ? $arr_empdetails['0']['EmployeeDetails']['first_name'] : ""; ?></legend>
    <form class="form-horizontal" method="post" id="approve">
        <div class="modal-body">
            <input id="emp_pkey" name="promotion_pkey" type="hidden"  value="<?php echo $promo_pkey; ?>" >
            <div class="col-xs-12 col-md-12">
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">History Table</h3>

                        <div class="box-tools">
                            <div class="input-group input-group-sm" style="width: 150px;">
                                <input type="text" name="table_search" class="form-control pull-right" placeholder="Search">

                            </div>
                        </div>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body table-responsive no-padding">
                        <table class="table table-hover">
                            <tbody>
                                <tr>
                                    <th>User ID</th>
                                    <th>Type</th>
                                    <th>From</th>
                                    <th>To</th>
                                    <th>Date</th>
                                    <th>Approval By</th>
                                    <th>Status</th>
                                    <th>Reason</th>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <!-- /.box-body -->
                </div>
            </div>

            <div class="col-xs-12 col-md-12">
                <!-- /.box -->
                <div class="col-xs-12 col-md-12">
                    <center><h2><strong>Change Configuration </strong></h2><hr></center>

                    <?php if (isset($arr_promo['0']['Promotion']['emp_type']) && $arr_promo['0']['Promotion']['emp_type'] != '') { ?>

                        <div class="form-group">
                            <div class="col-md-6">
                                <label style="text-align:left;" class="col-md-4 control-label" for="designation">Employee Type &nbsp;</label>
                                <label style="text-align:right;" class="col-md-8 control-label" for="designation"><?php echo $arr_emp_structures['0']['employee_structure_vview']['employee_type']; ?> &nbsp;</label>
                            </div>
                            <div class="col-md-6">
                                <label style="text-align:right;" class="col-md-4 control-label" for="designation">to &nbsp;</label>
                                <label style="text-align:right;" class="col-md-8 control-label" for="designation"><?php echo $arr_promo['0']['Promotion']['emp_type']; ?> &nbsp;</label>
                            </div>
                        </div>

                        <hr>

                    <?php } ?>

                    <?php if (isset($arr_promo['0']['Promotion']['designation']) && $arr_promo['0']['Promotion']['designation'] != '') { ?>

                        <div class="form-group">
                            <div class="col-md-6">
                                <label style="text-align:left;" class="col-md-4 control-label" for="designation">Designation &nbsp;</label>
                                <label style="text-align:right;" class="col-md-8 control-label" for="designation"><?php echo $arr_emp_structures['0']['employee_structure_vview']['designation']; ?> &nbsp;</label>
                            </div>
                            <div class="col-md-6">
                                <label style="text-align:right;" class="col-md-4 control-label" for="designation">to &nbsp;</label>
                                <label style="text-align:right;" class="col-md-8 control-label" for="designation"><?php echo $arr_promo['0']['Designation']['desig_name']; ?> &nbsp;</label>
                            </div>
                        </div>

                        <hr>

                    <?php } ?>

                    <?php if (isset($arr_promo['0']['Promotion']['emp_branch']) && $arr_promo['0']['Promotion']['emp_branch'] != '') { ?>

                        <div class="form-group">
                            <div class="col-md-6">
                                <label style="text-align:left;" class="col-md-4 control-label" for="designation">Branch &nbsp;</label>
                                <label style="text-align:right;" class="col-md-8 control-label" for="designation"><?php echo $arr_emp_structures['0']['employee_structure_vview']['branch']; ?> &nbsp;</label>
                            </div>
                            <div class="col-md-6">
                                <label style="text-align:right;" class="col-md-4 control-label" for="designation">to &nbsp;</label>
                                <label style="text-align:right;" class="col-md-8 control-label" for="designation"><?php echo $arr_promo['0']['Branches']['branch_name']; ?> &nbsp;</label>
                            </div>
                        </div>

                        <hr>

                    <?php } ?>

                    <?php if (isset($arr_promo['0']['Promotion']['emp_dept']) && $arr_promo['0']['Promotion']['emp_dept'] != '') { ?>

                        <div class="form-group">
                            <div class="col-md-6">
                                <label style="text-align:left;" class="col-md-4 control-label" for="designation">Department &nbsp;</label>
                                <label style="text-align:right;" class="col-md-8 control-label" for="designation"><?php echo $arr_emp_structures['0']['employee_structure_vview']['department']; ?> &nbsp;</label>
                            </div>
                            <div class="col-md-6">
                                <label style="text-align:right;" class="col-md-4 control-label" for="designation">to &nbsp;</label>
                                <label style="text-align:right;" class="col-md-8 control-label" for="designation"><?php echo $arr_promo['0']['Department']['dept_name']; ?> &nbsp;</label>
                            </div>
                        </div>

                        <hr>

                    <?php } ?>

                    <?php if (isset($arr_promo['0']['Promotion']['shift']) && $arr_promo['0']['Promotion']['shift'] != '') { ?>

                        <div class="form-group">
                            <div class="col-md-6">
                                <label style="text-align:left;" class="col-md-4 control-label" for="designation">Shift Policy &nbsp;</label>
                                <label style="text-align:right;" class="col-md-8 control-label" for="designation"><?php echo $arr_emp_structures['0']['employee_structure_vview']['SHIFT_POLICYS']; ?> &nbsp;</label>
                            </div>
                            <div class="col-md-6">
                                <label style="text-align:right;" class="col-md-4 control-label" for="designation">to &nbsp;</label>
                                <label style="text-align:right;" class="col-md-8 control-label" for="designation"><?php echo $arr_promo['0']['ShiftPolicy']['day_time_desc']; ?> &nbsp;</label>
                            </div>
                        </div>

                        <hr>

                    <?php } ?>

                    <?php if (isset($arr_promo['0']['Promotion']['annual_gross']) && $arr_promo['0']['Promotion']['annual_gross'] != '') { ?>

                        <div class="form-group">
                            <div class="col-md-6">
                                <label style="text-align:left;" class="col-md-4 control-label" for="designation">Annual Gross &nbsp;</label>
                                <label style="text-align:right;" class="col-md-8 control-label" for="designation"><?php echo isset($arr_gross['0']["emp_ctc_transaction"]["emp_anual_ctc"]) ? $arr_gross['0']["emp_ctc_transaction"]["emp_anual_ctc"] : NULL; ?> &nbsp;</label>
                            </div>
                            <div class="col-md-6">
                                <label style="text-align:right;" class="col-md-4 control-label" for="designation">to &nbsp;</label>
                                <label style="text-align:right;" class="col-md-8 control-label" for="designation"><?php echo $arr_promo['0']['Promotion']['annual_gross']; ?> &nbsp;</label>
                            </div>
                        </div>                    

                        <hr>

                    <?php } ?>

                    <?php if (isset($arr_promo['0']['Promotion']['leave']) && $arr_promo['0']['Promotion']['leave'] != '') { ?>

                        <div class="form-group">
                            <div class="col-md-6">
                                <label style="text-align:left;" class="col-md-4 control-label" for="designation">Leave Policy &nbsp;</label>
                                <label style="text-align:right;" class="col-md-8 control-label" for="designation"><?php echo $arr_emp_structures['0']['employee_structure_vview']['LEAVEPOLICY']; ?> &nbsp;</label>
                            </div>
                            <div class="col-md-6">
                                <label style="text-align:right;" class="col-md-4 control-label" for="designation">to &nbsp;</label>
                                <label style="text-align:right;" class="col-md-8 control-label" for="designation"><?php echo $arr_promo['0']['LeavePolicy']['LEAVEPOLICY_GROUP_NAME']; ?> &nbsp;</label>
                            </div>
                        </div>

                        <hr>

                    <?php } ?>

                    <?php if (isset($arr_promo['0']['Promotion']['salary']) && $arr_promo['0']['Promotion']['salary'] != '') { ?>

                        <div class="form-group">
                            <div class="col-md-6">
                                <label style="text-align:left;" class="col-md-4 control-label" for="designation">Salary Policy &nbsp;</label>
                                <label style="text-align:right;" class="col-md-8 control-label" for="designation"><?php echo $arr_emp_structures['0']['employee_structure_vview']['Salary_structure']; ?> &nbsp;</label>
                            </div>
                            <div class="col-md-6">
                                <label style="text-align:right;" class="col-md-4 control-label" for="designation">to &nbsp;</label>
                                <label style="text-align:right;" class="col-md-8 control-label" for="designation"><?php echo $arr_promo['0']['SalaryStructure']['structure_name']; ?> &nbsp;</label>
                            </div>
                        </div>

                        <hr>

                    <?php } ?>

                    <?php if (isset($arr_promo['0']['Promotion']['hierarch']) && $arr_promo['0']['Promotion']['hierarch'] != '') { ?>

                        <div class="form-group">
                            <div class="col-md-6">
                                <label style="text-align:left;" class="col-md-4 control-label" for="designation">Superior &nbsp;</label>
                                <label style="text-align:right;" class="col-md-8 control-label" for="designation"><?php echo $arr_emp_structures['0']['employee_structure_vview']['hierarchy']; ?> &nbsp;</label>
                            </div>
                            <div class="col-md-6">
                                <label style="text-align:right;" class="col-md-4 control-label" for="designation">to &nbsp;</label>
                                <label style="text-align:right;" class="col-md-8 control-label" for="designation"><?php echo $arr_promo['0']['0']['name']; ?> &nbsp;</label>
                            </div>
                        </div>


                    <?php } ?>

                    <hr>

                    <div class="form-group pull-right">

                        <button type="button" class="btn btn-default " onclick="$('#largeModalForm').modal('hide');">Cancel</button>
                        <button type="submit" id="btn-submitfami" class="btn btn-primary">Approve Promotion</button>
                        <button type="button" id="btn-reject" onclick="rejpromoion();" class="btn btn-danger">Reject Promotion</button>

                    </div>

                </div>
            </div>
        </div>
        <hr>
        <div class="modal-footer">
        </div>

    </form>
</div>
