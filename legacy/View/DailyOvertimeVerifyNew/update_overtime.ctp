<style>
    .form-horizontal .control-label {
        text-align: left;
    }
</style>

<div class="modal-content">

    <!-- Header -->
    <div class="modal-header" style="background:#00659f;color:white;">
        <h4 class="modal-title">Set Overtime Duration</h4>
    </div>

    <!-- Body -->
    <div class="modal-body">
        <form class="form-horizontal">

            <div class="form-group col-md-12">
                <label class="col-md-4 control-label">
                    Set Duration (minutes)
                </label>
                <div class="col-md-1">:</div>
                <div class="col-md-7">
                    <input id="set_duration_value"
                        class="easyui-numberbox"
                        value="<?php echo $set_ot; ?>"
                        data-options="min:0,precision:0" type="text"
                        style="width:100%;" />
                </div>
            </div>

            <div class="form-group col-md-12">
                <label class="col-md-4 control-label">
                    Remarks
                </label>
                <div class="col-md-1">:</div>
                <div class="col-md-7">
                    <input type="text"
                        id="set_duration_remark"
                        value="<?php echo $remarks; ?>"
                        placeholder="Remarks (optional)"
                        maxlength="255"
                        style="width:100%;"
                        maxlength="30" />
                </div>
            </div>

        </form>
    </div>

    <!-- Footer -->
    <div style="text-align:right;padding:15px; margin-right:45px;">
        <button type="button"
            class="btn btn-danger"
            onclick="closeModalForm();">
            Close
        </button>

        <button
            id="saveSetDurationBtn"
            type="button"
            class="btn btn-primary"
            onclick="saveSetDuration()">
            Save
        </button>
    </div>

</div>


<script>
    var initialSetDuration = <?php echo ($set_ot !== '' ? (int)$set_ot : 'null'); ?>;
    var initialSetRemark = <?php echo isset($set_remark)
                                ? json_encode(trim($set_remark))
                                : "''"; ?>;

    // Edited by Akshay on 22-5-2029
    $('#set_duration_value').on('input', function() {
        // Allow only digits
        this.value = this.value.replace(/[^0-9]/g, '');

    });
    // End

    $('#set_duration_value').on('input change', toggleSaveButton);
    $('#set_duration_remark').on('input', toggleSaveButton);

    function toggleSaveButton() {
        var currentValue = $('#set_duration_value').val();
        var currentRemark = $('#set_duration_remark').val().trim();

        // normalize
        currentValue = currentValue === '' ? null : parseInt(currentValue, 10);

        var changed =
            currentValue !== initialSetDuration ||
            currentRemark !== initialSetRemark;

        //  $('#saveSetDurationBtn').prop('disabled', !changed);
    }

    // Run once on load
    $(document).ready(function() {
        toggleSaveButton();
    });

    function saveSetDuration() {

        let value = $('#set_duration_value').val();
        let remark = $('#set_duration_remark').val().trim();

        if (value !== '' && (!Number.isInteger(Number(value)) || Number(value) < 0)) {
            $.notify('Overtime must be a positive integer', {
                type: 'danger',
                z_index: 9999
            });
            return;
        }

        if (value > 1440) {
            $.notify('Overtime duration cannot be greater than 1440 mins', {
                type: 'danger',
                z_index: 9999
            });
            return;
        }



        // if ((value === '' || value === null) && (remark === '' || remark === null)) {
        //     alert('Please enter duration or value');
        //     return;
        // }

        let rows = $('#dailyovertimediv').datagrid('getSelections');

        if (!rows.length) {
            alert('Please select at least one record');
            return;
        }

        // Payload with arrays
        let payload = {
            value: value,
            remark: remark,
            att_date: [],
            emp_pkey: [],
            // emp_detail_timeattandance_pkey: []
            branch: $('#filterby_branch').val(),
            month: $('#filterby_month').val()
        };

        rows.forEach(row => {
            payload.att_date.push(row.att_date);
            payload.emp_pkey.push(row.emp_pkey);
            // payload.emp_detail_timeattandance_pkey.push(row.emp_detail_timeattandance_pkey);
        });

        $.ajax({
            url: livesite + 'DailyOvertimeVerifyNew/updateSetDuration',
            type: 'POST',
            data: JSON.stringify(payload),
            contentType: 'application/json',
            dataType: 'json',
            success: function(res) {

                // If message exists → show danger notify
                if (res.message && res.message !== '') {
                    $.notify(res.message, {
                        type: 'danger',
                        allow_dismiss: false,
                        z_index: 9999
                    });
                    $('#modalForm').modal('hide');

                    if (!res.success) {
                        return; // stop further actions if needed
                    }

                }

                if (res.success) {
                    $.notify("Overtime updated successully.", {
                        type: 'success',
                        allow_dismiss: false,
                        z_index: 9999
                    });
                    $('#dailyovertimediv').datagrid('reload');
                }
                $('#modalForm').modal('hide');
            },
            error: function() {
                $.notify('Error updating duration', {
                    type: 'success',
                    allow_dismiss: false,
                    z_index: 9999
                });
            }
        });

    }

    function closeModalForm() {
        $('#modalForm').modal('hide');
    }
</script>