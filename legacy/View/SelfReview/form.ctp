<style>
    /* .modal-lg {
        max-width: 90% !important;
    } */

    .form-horizontal .control-label {
        text-align: left;
    }
</style>


<?php
$readonly = ($edit == 0) ? 'readonly' : '';
$disabled = ($edit == 0) ? 'disabled' : '';
?>

<div class="modal-dialog  modal-lg">
    <div class="modal-content">
        <div class="modal-header" style="background: #00659f; color: white">
            <h4 class="modal-title">Self Appraisal</h4>
        </div>
        <div class="modal-body">
            <form class="form-horizontal" method="post" action="<?php echo $this->webroot; ?>SelfReview/saveSelfReview" id="deptForm">

                <!-- Duty Description -->
                <div class="form-group row">
                    <label class="col-md-12 control-label" for="duty_desc">Brief description of duties<span class="star">*</span>:</label>
                    <div class="col-md-12">
                        <textarea name="duty_desc" id="duty_desc" class="form-control" rows="4" placeholder="Enter duties description" <?php echo $readonly; ?>><?php echo !empty($duty_desc) ? trim(preg_replace('/\s+/', ' ', $duty_desc)) : ''; ?></textarea>
                    </div>
                </div>


                <!-- Work Done Description -->
                <div class="form-group row">
                    <label class="col-md-12 control-label" for="work_done_desc">
                        Brief resume of the work done by you bringing out any special achievements during the year/period under review.
                        In the event of shortfall in achievement furnish reasons. (The resume to be furnished within the space provided limited to 100 words and is required to be signed)<span class="star">*</span>:
                    </label>
                    <div class="col-md-12">
                        <textarea name="work_done_desc" id="work_done_desc" class="form-control" rows="5" placeholder="Enter work done" <?php echo $readonly; ?>><?php echo !empty($work_done_desc) ? trim(preg_replace('/\s+/', ' ', $work_done_desc)) : ''; ?></textarea>
                    </div>
                </div>


                <!-- Reporting and Reviewing Officers Dropdowns in the Same Row -->
                <div class="form-group row">
                    <label class="col-md-2 control-label" for="reporting_officer">
                        Reporting Officer<span class="star">*</span>:
                    </label>
                    <div class="col-md-4">
                        <select name="reporting_officer" id="reporting_officer" class="form-control" required disabled>
                            <?php foreach ($arr_employees as $index => $employee) {
                                $selected = ($employee['ei']['emp_pkey'] == $reporting_officer || (empty($reporting_officer) && $index === 0)) ? 'selected' : '';
                            ?>
                                <option value="<?php echo $employee['ei']['emp_pkey']; ?>" <?php echo $selected; ?>>
                                    <?php echo ($employee['ei']['EmpName'] . ' - ' . $employee['ei']['employee_id']); ?>
                                </option>
                            <?php } ?>
                        </select>

                    </div>

                    <label class="col-md-2 control-label" for="reviewing_officer">
                        Reviewing Officer<span class="star">*</span>:
                    </label>
                    <div class="col-md-4">
                        <select name="reviewing_officer" id="reviewing_officer" class="form-control" required disabled>
                            <?php foreach ($arr_leave as $index => $employee) {
                                $selected = ($employee['ei']['emp_pkey'] == $reviewing_officer || (empty($reviewing_officer) && $index === 0)) ? 'selected' : '';
                            ?>
                                <option value="<?php echo $employee['ei']['emp_pkey']; ?>" <?php echo $selected; ?>>
                                    <?php echo ($employee['ei']['EmpName'] . ' - ' . $employee['ei']['employee_id']); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                </div>


                <!-- Hidden Inputs -->
                <?php if (isset($self_review_details_pkey)) { ?>
                    <input type="hidden" name="self_review_details_pkey" value="<?php echo $self_review_details_pkey; ?>">
                <?php } ?>

                <input type="hidden" name="status" id="status" value="Draft">

                <!-- Footer Buttons -->
                <div class="modal-footer">
                    <?php if ($edit != 0) { ?>
                        <button type="button" class="btn btn-primary" id="saveBtn">Save</button>
                        <button type="button" class="btn btn-primary" id="submitBtn">Submit</button>
                    <?php } ?>
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#largeModalForm').modal({
            backdrop: 'static', // Prevents closing the modal by clicking outside
            keyboard: false // Disables closing the modal with the keyboard (ESC key)
        });

        $('.modal').on('click', function(e) {
            // Prevent closing modal if the click is not on a button and is outside .modal-content
            if (!$(e.target).closest('.modal-content').length) {
                e.stopPropagation(); // Prevent closing the modal if clicked outside .modal-content
            }
        });

        let clickedButtonId = null;

        $('.modal').on('shown.bs.modal', function() {
            $('#duty_desc').val($.trim($('#duty_desc').val()));
            $('#work_done_desc').val($.trim($('#work_done_desc').val()));
        });

        // Trim leading and trailing spaces from text areas
        $('textarea.form-control').each(function() {
            var currentValue = $(this).val();
            $(this).val(currentValue.trim()); // Trim the value on load
        });

        function cleanTextAreaContent() {
            $('#duty_desc, #work_done_desc').each(function() {
                let cleanedValue = $(this).val().trim().replace(/\s+/g, ' ');
                $(this).val(cleanedValue);
            });
        }

        function enforceWordLimit() {
            const wordLimit = 100;
            let text = $('#work_done_desc').val();
            let words = text.trim().split(/\s+/);
            if (words.length > wordLimit) {
                words = words.slice(0, wordLimit);
                $('#work_done_desc').val(words.join(' '));
            }
        }

        $('#work_done_desc').on('keyup input', enforceWordLimit);
        enforceWordLimit();

        $('#saveBtn').click(function() {
            clickedButtonId = 'saveBtn';
            $('#status').val('Draft');
            $('#deptForm').submit();
        });

        $('#submitBtn').click(function() {
            clickedButtonId = 'submitBtn';
            $('#status').val('Applied');
            $('#deptForm').submit();
        });

        $('#deptForm').on('submit', function(e) {
            e.preventDefault();
            cleanTextAreaContent();

            let isValid = true;

            if (clickedButtonId === 'submitBtn') {
                let dutyDesc = $('#duty_desc').val().trim();
                let workDoneDesc = $('#work_done_desc').val().trim();
                let reportingOfficer = $('#reporting_officer').val();
                let reviewingOfficer = $('#reviewing_officer').val();

                if (dutyDesc === '') {
                    $.notify("Please fill in the Brief Description of Duties.", {
                        type: "danger",
                        z_index: 9999,
                    });
                    $('#duty_desc').focus();
                    isValid = false;
                } else if (workDoneDesc === '') {
                    $.notify("Please fill in the Work Done description.", {
                        type: "danger",
                        z_index: 9999,
                    });
                    $('#work_done_desc').focus();
                    isValid = false;
                } else if (!reportingOfficer) {
                    $.notify("Please select a Reporting Officer.", {
                        type: "danger",
                        z_index: 9999,
                    });
                    $('#reporting_officer').focus();
                    isValid = false;
                } else if (!reviewingOfficer) {
                    $.notify("Please select a Reviewing Officer.", {
                        type: "danger",
                        z_index: 9999,
                    });
                    $('#reviewing_officer').focus();
                    isValid = false;
                }
            } else {
                let dutyDesc = $('#duty_desc').val().trim();
                let workDoneDesc = $('#work_done_desc').val().trim();
                if (dutyDesc === '' && workDoneDesc === '') {
                    $.notify("Please fill in at least one of the text areas.", {
                        type: "danger",
                        z_index: 9999,
                    });
                    $('#duty_desc').focus();
                    isValid = false;
                }
            }

            if (!isValid) return;

            $.ajax({
                type: 'POST',
                url: $(this).attr('action'),
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        $.notify(response.message || 'Saved successfully.', {
                            type: "success",
                            z_index: 9999,
                        });

                        $('#myleaverequeststable').datagrid('reload');
                        $('#deptForm')[0].reset();
                        $('.modal').modal('hide');
                    } else {
                        $.notify(response.message || 'Failed to save. Please try again.', {
                            type: "danger",
                            z_index: 9999,
                        });
                    }
                },
                error: function() {
                    $.notify('An error occurred while submitting the form.', {
                        type: "danger",
                        z_index: 9999,
                    });
                }
            });
        });
    });
</script>