<style>
    .form-horizontal .control-label {
        text-align: left;
    }
</style>
<!-- Bulk approve by *** ARUL P DAS *** on 11/10/2022 -->
<div class="modal-content">

    <div class="modal-header" style="background: #00659f;color: white">
        <h4 class="modal-title">Attendance</h4>
    </div>
    <div class="modal-body">

        <?php
        if ($regularasation_data) :
        ?>
            <!-- Attendance Approval Tab. Only if pending attendance exst. -->
            <form class="form-horizontal" id="regularisation_approve" method="post" action="<?php echo $this->webroot; ?>Regularisation/bulkupdate">
                <?php
                $attendance_dates = [];
                foreach ($regularasation_data as $data) {
                    $date = date('d-m-Y', strtotime($data['employee_regularaization']['att_date']));
                    $c1 = $data['employee_regularaization']['C1'];
                    $LOGTIME = $data['employee_regularaization']['LOGTIME'];
                    array_push($attendance_dates,  $date . ' - (' . $c1 . ' : ' . $LOGTIME . ')');
                ?>
                    <input type="hidden" name="id[]" value="<?php echo $data['employee_regularaization']['id']; ?>" />
                <?php
                }
                ?>

                <div class="form-group col-md-12">
                    <label class="col-md-4 control-label">Attendance Dates</label>
                    <div class="col-md-1">:</div>
                    <div class="col-md-7">
                        <b>
                            <?php
                            echo implode("<br>", $attendance_dates);
                            ?>
                        </b>
                    </div>
                </div>
                <div class="form-group col-md-12">
                    <label class="col-md-4 control-label">Remarks</label>
                    <div class="col-md-1">:</div>
                    <div class="col-md-7">
                        <textarea id="remarks" name="remarks" class="form-control"></textarea>
                    </div>
                </div>
                <!-- <div class="form-group col-md-12">
                    <label class="col-md-4 control-label">Approve / Reject <span class="star">*</span></label>
                    <div class="col-md-1">:</div>
                    <div class="col-md-7">
                        <select name="approved" id="approved" class="form-control">
                            <option value="A"></option>
                            <option value="R"></option>
                        </select>
                    </div>
                </div> -->
                <div style="text-align: right;padding:15px;">
                    <button type="button" class="btn btn-primary" data-dismiss="modal" onclick="clearForm();">Close</button>

                    <input type="hidden" name="approved" id="approved" value="" />
                    <button type="submit" class="btn btn-success" value="A" onclick="setAction('A')">Approve</button>
                    <button type="submit" class="btn btn-danger" value="R" onclick="setAction('R')">Reject</button>
                </div>
            </form>
        <?php
        else :
        ?>
            <div class="form-group col-md-12">
                <h3>
                    There is no Regularisation Pending on the selected dates.
                </h3>
            </div>
            <div style="text-align: right;padding:15px;">
                <button type="button" class="btn btn-danger" data-dismiss="modal" onclick="clearForm();">Close</button>
            </div>
        <?php
        endif;
        ?>
    </div>

</div>



</div> <!-- Closing of Modal Content -->
<script type="text/javascript">
    $(document).ready(function() {

        $('#regularisation_approve').parsley();
        var reg_options = {
            success: function(responseText, statusText, xhr, $form) {
                var response = JSON.parse(responseText);
                if (response.success == false) {
                    alert(response.msg);
                    clearForm();
                    // closeSmallModalForm();
                    $('#modalForm').modal('hide');
                    refresheditpunchgrid();
                } else {
                    clearForm();
                    $('#modalForm').modal('hide');
                    refresheditpunchgrid();
                    if (response.approve == 'A') {
                        $.notify("Attendance Approved Successfully", {
                            type: 'success',
                            allow_dismiss: false
                        });
                    } else {
                        $.notify("Attendance Rejected Successfully", {
                            type: 'success',
                            allow_dismiss: false
                        });
                    }
                }
            }
        };

        // bind to the form's submit event 
        $('#regularisation_approve').submit(function() {
            console.log('second action');
            $(this).ajaxSubmit(reg_options);
            return false;
        });
    });

    function clearForm() {
        $('#empid').val("")
        $('#regularisation_approve').form('clear');
    }

    function setAction(action) {
        $("#approved").val(action);
    }
</script>