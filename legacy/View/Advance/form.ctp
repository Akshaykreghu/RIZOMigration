<script>
    $.validate({
        form: '#employee_advance'
    });
    var options = {
        success: function (resp) {
            $('#modalForm').modal('hide');
            $('#att_table').datagrid('reload');
            $.notify($.parseJSON(resp).msg, {
                type: 'success',
                allow_dismiss: false
            });
        }  // post-submit callback
    };


    $('#employee_advance').on('submit', function (event) {
        event.preventDefault();
        $advance_date = $("#advance_date").val();
        if($advance_date == ''){
            alert("Please choose the date.");
        }else{
        $("#btn-submit").prop("value", "Saving");
        $("#btn-submit").prop("disabled", true);
        setTimeout(
                function () {
                    if (confirm(" Do You Want  To Save The Form")) {
                        $('#employee_advance').ajaxSubmit(options)
                    } else {
                        $("#btn-submit").prop("value", "Save");
                        $("#btn-submit").prop("disabled", false);
                    }
                }, 1000
                ); }
    });


</script>
<div class="modal-dialog">
    <!-- Modal content-->
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title"> Employee Advances</h4>  
        </div>
        <?php 
        $pkey = isset($data["advance_pkey"])?$data["advance_pkey"]:'';
        $acc_pkey = isset($data['account_fkey'])?$data['account_fkey'] : ''; 
        $emp_pkey = isset($data['emp_fkey'])?$data['emp_fkey'] : '';?>
        <div class="modal-body">
            <!-- Form starts -->
            <form class="form-horizontal" id="employee_advance" action="<?php echo $this->webroot; ?>Advance/advancesave" method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <div class="col-md-12">
                            <label for="vehicle_master_fkey" class="col-sm-4 control-label">Employee<span class="star">*</span></label>
                            <div class="col-md-7">
                                <select id="emp_fkey" class="form-control" name="emp_fkey" required="required" style="width: 285px;">
                                    <option value="">[--Select--]</option>
                                    <?php 
                                    foreach ($arr_employees as $value) {
                                        $selected = ($emp_pkey == $value['EmployeeDetails']['emp_pkey']) ? 'selected="selected"' : '';
                                        echo '<option value="' . $value['EmployeeDetails']['emp_pkey'] . '" ' . $selected . '>' . $value['0']['name'] . ' - ' . $value['emp_proff']['emp_company_id'] . '</option>';
                                    }
                                    ?>
                                </select> 
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-12">
                            <label for="advance_date" class="col-sm-4 control-label">Date<span class="star">*</span></label>
                            <div class="col-md-7">
                                <input type="text"  class="form-control" value="<?php echo isset($data['advance_date'])?$data['advance_date']:''; ?>" name="advance_date" id="advance_date" readonly="readonly" style="background:white;" placeholder="Select Date" required="required">
                            </div> 
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-12">
                            <label for="account_fkey" class="col-sm-4 control-label">From Account<span class="star">*</span></label>
                            <div class="col-md-7">
                                <select id="account_fkey" name="account_fkey" class="form-control" style="width: 285px" required="required">
                                    <option value="">[--Select--]</option>
                                     <option value="1" <?php if($acc_pkey == '1'){ echo 'selected="selected"';} ?> >Bank Accounts</option>
                                     <option value="2" <?php if($acc_pkey == '2'){ echo 'selected="selected"';} ?> >Cash</option>
                                     <option value="3" <?php if($acc_pkey == '3'){ echo 'selected="selected"';} ?> >Credit Card</option>
                                     <option value="4" <?php if($acc_pkey == '4'){ echo 'selected="selected"';} ?> >Debit Card</option>
                                     <option value="5" <?php if($acc_pkey == '5'){ echo 'selected="selected"';} ?> >Mobile Payments</option>
                                     <option value="6" <?php if($acc_pkey == '6'){ echo 'selected="selected"';} ?> >ANOOP PC</option>
                                     <option value="7" <?php if($acc_pkey == '7'){ echo 'selected="selected"';} ?> >RAFEEQ PC</option>
                                     <option value="8" <?php if($acc_pkey == '8'){ echo 'selected="selected"';} ?> >MUSFEER PC</option>
                                     <option value="9" <?php if($acc_pkey == '9'){ echo 'selected="selected"';} ?> >MUNEEB PC</option>
                                     <option value="10" <?php if($acc_pkey == '10'){ echo 'selected="selected"';} ?> >PRABEESH PC</option>
                                     <option value="11" <?php if($acc_pkey == '11'){ echo 'selected="selected"';} ?> >ALLES HAPPAY</option>
                                     <option value="12" <?php if($acc_pkey == '12'){ echo 'selected="selected"';} ?> >JAC HAPPAY</option>
                                     <option value="13" <?php if($acc_pkey == '13'){ echo 'selected="selected"';} ?> >ZB CARD</option>
                                     <option value="14" <?php if($acc_pkey == '14'){ echo 'selected="selected"';} ?> >Other Payments</option>
                                </select>
                            </div> 
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-12">
                            <label for="total_cost" class="col-sm-4 control-label">Amount<span class="star">*</span></label>
                            <div class="col-md-7">
                                <input type="number" required="required" class="form-control" value="<?php echo isset($data['amount'])?$data['amount']:''; ?>" name="amount" id="amount">
                            </div> 
                        </div>
                    </div>
                  <div class="form-group">
                            <div class="col-md-12">
                                                <label for="in_date" class="col-sm-4 control-label">Remarks<span class="star">*</span></label>
                                                <div class="col-md-7">
                                                    <input type="text" required="required" class="form-control" value="<?php echo isset($data['remarks'])?$data['remarks']:''; ?>" name="remarks" id="remarks" placeholder="Enter Remarks">
                                                </div> 
                                            </div>
                                        </div>
                    <div class="modal-footer">
                        <input type="hidden" required="required" class="form-control" value="<?php echo isset($data['advance_pkey'])?$data['advance_pkey']:''; ?>" name="advance_pkey">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                        <button type="submit" name="btn-submit" id="btn-submit" class="btn btn-success">Save</button>
                    </div>
                </div>
            </form>
            <!-- form ends-->
        </div>
    </div>
</div>
<script type="text/javascript">
    $("#emp_fkey").select2();
    $('#account_fkey').select2();
  
    function nofityMsg(msg) {
        $.notify(msg, {
            z_index: 1051,
            type: "danger"
        });
    }
    $(document).ready(function () {
    $('#advance_date').datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true
    });
    });
</script>