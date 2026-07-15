<div class="modal-content">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
        <h4 class="modal-title">Leave Balance Upload</h4>
    </div>
    <div class="modal-body">

        <form id="leaveuploadtable" action="<?php echo $this->webroot; ?>Empleaveupload/leavebalancesave" method="POST">

            <div class="form-group" style="padding: 12px;">
                <label class="col-sm-4" for="employee">Employee Name</label>
                <div class="col-md-8">
                    <select id="emp_fkey1" class="form-control" name="emp_fkey1" style="width: 100%;">
                        <option value="">[--Select--]</option>
                        <?php
                        foreach ($arr_employees as $value) {
                            $selected = ($data['emp_fkey'] == $value['EmployeeDetails']['emp_pkey']) ? 'selected="selected"' : '';

                            echo '<option value="' . $value['EmployeeDetails']['emp_pkey'] . '" ' . $selected . '>' . $value['EmployeeDetails']['first_name'] . ' '  . $value['EmployeeDetails']['last_name'] . ' -- '.$value['emp_proff']['emp_company_id'] . '</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="form-group" style="padding: 12px;">
                <input type="hidden" id="emp_leave_upload_pkey" name="emp_leave_upload_pkey" value="<?php echo $data['emp_leave_upload_pkey']; ?>">
                <label for="leave_type" class="col-sm-4 control-label">Leave Type<span class="star">*</span></label>
                <div class="col-sm-8">
                        <!-- edited by athira on 22-09-2025 -->
                    <div id="leave_type_container">
                <select id="leave_type" name="leave_type" class="form-control" style="width: 100%;" readonly>
                    <option value="">[--Select--]</option>
                    <?php if (!empty($arr_leavetypes)) {
                        foreach ($arr_leavetypes as $key => $value) {
                            $selected = ($value['SalaryHeadItems']['salary_head_item_pkey'] === ($data['leave_type'] )) ? 'selected="selected"' : '';
                    ?>
                        <option <?php echo $selected; ?> value="<?php echo $value['SalaryHeadItems']['salary_head_item_pkey']; ?>">
                            <?php echo $value['SalaryHeadItems']['item']; ?>
                        </option>
                    <?php } } ?>
                </select>
                </div>
                  <!-- end -->
            </div>
            </div>
            <div class="form-group" id="bal_leave" style="padding: 12px;">
                <label for="current_leave_balance" class="col-sm-4 control-label">Current Leave Balance</label>
                <div class="col-sm-8">
                    <input class="form-control" id="current_leave_balance" name="current_leave_balance" value="" type="text" readonly>
                </div>
            </div>
            <div class="form-group" style="padding: 12px;">
                <label for="leave_balance" class="col-sm-4 control-label">Balance Leave </label>
                <div class="col-sm-8">
                    <input class="form-control" id="leave_balance" name="leave_balance" value="" type="text">
                </div>
            </div>

            <div class="pull-right" style="padding: 12px;">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
    <div class="modal-footer">

    </div>
</div>
<script type="text/javascript">

    //edited by athira on 11-09-2025
document.getElementById("leave_balance").addEventListener("input", function () {
    let val = this.value;

    // ❌ Prevent negatives
    if (/^-/.test(val)) {
        alert("Negative leave balance not allowed");
        this.value = "";
        return;
    }

    // ✅ Clean input — allow only digits and a single dot
    val = val
        .replace(/[^0-9.]/g, "")      // remove non-numeric/dot chars
        .replace(/(\..*?)\..*/g, "$1"); // keep only first dot

    // ✅ Limit to one digit after the decimal
    if (val.includes(".")) {
        const parts = val.split(".");
        val = parts[0] + "." + parts[1].substring(0, 1);
    }

    // ✅ Prevent leading "." (convert ".5" → "0.5")
    if (val.startsWith(".")) {
        val = "0" + val;
    }

    // ❌ Prevent multiple leading zeros (but allow single 0)
    if (/^0{2,}$/.test(val)) {
        alert("Invalid number — cannot be all zeros");
        val = "0";
    }

    this.value = val;
});


//end

    $(document).ready(function() {
        $('#emp_fkey1').select2();
        //edited by athira on 22-09-2025
        $('#leave_type').prop('disabled', true); // disables the select
        $('#leave_type').select2(); // re-initialize Select2 to apply disabled
        //end

        $('#leaveuploadtable').parsley();
        var options = {
            success: function(responseText, statusText, xhr, $form) {
                alert("Leave Balance Uploaded Successfully");
                closeModal('leave_table');
            }
        };

        $('#leaveuploadtable').submit(function(e) {
            e.preventDefault();
            // let emp_fkey1 = $("#emp_fkey1").val();
            // let leave_type = $("#leave_type").val();
            // let current_leave_balance = $("#current_leave_balance").val();
            // let leave_balance = $("#leave_balance").val();
            $(this).ajaxSubmit(options);
        });
        // $('#emp_fkey1').change(function() {});
//edited by athira on 22-09-2025
        $('#emp_fkey1').change(function() {
            $('#leave_type').prop('disabled', false);
    var empKey = $(this).val();
    if(empKey) {
        $.ajax({
            url: livesite + 'Empleaveupload/getLeaves',
            type: 'POST',
            data: { emp_pkey: empKey },
            success: function(response) {
                // assuming controller returns JSON
                var leaves = JSON.parse(response);
                var options = '<option value="">[--Select--]</option>';
                leaves.forEach(function(l) {
                    options += '<option value="'+l.SalaryHeadItems.salary_head_item_pkey+'">'+l.SalaryHeadItems.item+'</option>';
                });
                $('#leave_type_container select').html(options);
            }
        });
    } else {
        $('#leave_type_container select').html('<option value="">[--Select Employee First--]</option>');
    }
});
//end


        $('#leave_type').change(function() {

            var employee = $('#emp_fkey1').val();
            var type = $('#leave_type').val();
            $.ajax({
                url: livesite + 'Empleaveupload/getleave',
                data: {
                    employee: employee,
                    type: type
                },
                success: function(response) {
                    console.log(response);
                    $("#current_leave_balance").val(response)
                }
            });
            // empleaverequeststable.search( this.value ).draw();
        });

    });
</script>