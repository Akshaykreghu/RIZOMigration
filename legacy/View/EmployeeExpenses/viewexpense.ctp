<form class="form-vertical" id="expenseapproveform">
    <div class="modal-header" style="background:#004d77;color:white;">

        <h4 class="modal-title"><?php echo $arr_expense['0']['emp_expense']['expense_status']; ?></h4>
    </div>

    <div class="modal-body">
        <input id="expensepkey" name="expensepkey" type="hidden" value="<?php echo $arr_expense['0']['emp_expense']['emp_expenses_pkey']; ?>">

        <div class="form-group">
            <div class="col-md-6">
                <div style="font-size: 25px; margin-top: -8px; " class="col-md-12"><?php echo $arr_expense['0']['employee_info']['EmpName']; ?> - <span style="font-size: 20px;"><?php echo $arr_expense['0']['employee_info']['employee_id']; ?></span></div>
                <div style="padding-left: 15px;"> <?php echo $arr_expense['0']['employee_info']['designation']; ?> -<span style="font-size: 15px;"><?php echo $arr_expense['0']['employee_info']['department']; ?></span></div>
            </div>
            <div class="col-md-6">
                <div style="text-align:left;" class="col-md-5 control-label">Branch</div>
                <label style="text-align:left;" class="col-md-1">:</label>
                <span style="padding-left: 15px;"><?php echo $arr_expense['0']['employee_info']['branch']; ?></span>
            </div>
            <div class="col-md-6">
                <div style="text-align:left;" class="col-md-5 control-label">Applied Date</div>
                <label style="text-align:left;" class="col-md-1">:</label>
                <span style="padding-left: 15px;"><?php echo date('Y-m-d', strtotime($arr_expense['0']['emp_expense']['created_date'])); ?></span>
            </div>
        </div>
        <hr style="padding-right: 1px; padding-top: 1px; margin-bottom: -3px;">
        <div class="form-group">
            <div class="row">
                <div class="col-md-12">
                    <br>
                    <div class="col-md-6">

                        <label style="text-align:left;" class="col-md-5 control-label">Expense Amount ( <i class="fa fa-inr" aria-hidden="true"></i> )</label>
                        <label style="text-align:left;" class="col-md-1">:</label>
                        <span style="text-align:left;" class="col-md-6"><?php echo $arr_expense['0']['emp_expense']['expenses_amount']; ?></span>

                    </div>
                    <div class="col-md-6">

                        <label style="text-align:left;" class="col-md-5 control-label">Expense Type</label>
                        <label style="text-align:left;" class="col-md-1">:</label>
                        <span style="text-align:left;" class="col-md-6"><?php echo $arr_expense['0']['emp_expense']['expense_type']; ?></span>

                    </div>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <div class="col-md-12" style="margin-top: -30px;">
                    <br>
                    <div class="col-md-6">

                        <label style="text-align:left;" class="col-md-5 control-label">Expense Date</label>
                        <label style="text-align:left;" class="col-md-1">:</label>
                        <span style="text-align:left;" class="col-md-6"><?php echo $arr_expense['0']['emp_expense']['expense_date']; ?></span>
                    </div>
                    <div class="col-md-6">
                        <label style="text-align:left;" class="col-md-5 control-label">Authorized By</label>
                        <label style="text-align:left;" class="col-md-1">:</label>
                        <span style="text-align:left;" class="col-md-6"><?php echo isset($auth_person[0]["employee_info"]["EmpName"]) ? $auth_person[0]["employee_info"]["EmpName"] : 'Admin'; ?></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <div class="col-md-12" style="margin-top: -30px;">
                    <br>
                    <div class="col-md-6">

                        <label style="text-align:left;" class="col-md-5 control-label">Vendor</label>
                        <label style="text-align:left;" class="col-md-1">:</label>
                        <span style="text-align:left;" class="col-md-6"><?php echo $arr_expense['0']['emp_expense']['vendor']; ?></span>
                    </div>
                    <div class="col-md-6">

                        <label style="text-align:left;" class="col-md-5 control-label">Approved By</label>
                        <label style="text-align:left;" class="col-md-1">:</label>
                        <span style="text-align:left;" class="col-md-6"><?php echo isset($apr_person[0]["employee_info"]["EmpName"]) ? $apr_person[0]["employee_info"]["EmpName"] : $apr_by_admin; ?></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <div class="col-md-12" style="margin-top: -30px;">
                    <br>
                    <div class="col-md-6">

                        <label style="text-align:left;" class="col-md-5 control-label">Purpose</label>
                        <label style="text-align:left;" class="col-md-1">:</label>
                        <span style="text-align:left;" class="col-md-6"><?php echo $arr_expense['0']['emp_expense']['purpose']; ?></span>
                    </div>
                    <div class="col-md-6">

                        <label style="text-align:left;" class="col-md-5 control-label">Remarks</label>
                        <label style="text-align:left;" class="col-md-1">:</label>
                        <span style="text-align:left;" class="col-md-6"><?php echo $arr_expense['0']['emp_expense']['remarks']; ?></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <div class="col-md-12" style="margin-top: -30px;">
                    <br>
                    <div class="col-md-6">
                            <label style="text-align:left;" class="col-md-5 control-label" for="AUTHREMARKS">Remarks By Approved Person</label>
                            <label style="text-align:left;" class="col-md-1">:</label>


                            <div class="col-md-5">
                                <textarea id="AUTHREMARKS" rows="2" cols="20" name="AUTHREMARKS" type="text" placeholder="Remarks" style=" width: 195px;" readonly="" wrap="hard" class="form-control input-md"><?php echo $arr_expense['0']['emp_expense']['remarks_auth']; ?></textarea>
                            </div>
                        </div>
                    <div class="col-md-6">
                        <label style="text-align:left;" class="col-md-5 control-label" for="REMARKS">Remarks By Approved Person</label>
                        <label style="text-align:left;" class="col-md-1">:</label>


                        <div class="col-md-5">
                            <textarea id="REMARKS" rows="2" cols="20" name="REMARKS" type="text" placeholder="Remarks" style=" width: 195px;" readonly="" wrap="hard" class="form-control input-md"><?php echo $arr_expense['0']['emp_expense']['remarks_approved']; ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group">
            <div class="row">
                <div class="col-md-12" style="margin-top: -30px;margin-bottom: -18px;    text-align: right;">
                    <br>


                    <button type="button" id="btn-grandemployeeleave" class="btn btn-danger" data-dismiss="modal"><?php echo "Close"; ?></button>
                    <button type="button" id="btn-grandemployeeleave" class="btn btn-info" onclick="showimage()"><?php echo "Show Image"; ?></button>


                </div>
            </div>
        </div>
    </div>
</form>
<script>
    function showimage() {
        showSmallModalForm(livesite + 'EmployeeExpenses/showimage/' + <?php echo $arr_expense['0']['emp_expense']['emp_expenses_pkey']; ?>);
    }
</script>