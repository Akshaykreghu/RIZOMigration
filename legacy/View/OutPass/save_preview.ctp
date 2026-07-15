<style>
    #inputTable {
        border-collapse: collapse;
        width: 100%;
    }

    #inputTable,
    #inputTable th,
    #inputTable td {
        border: 1px solid black;
    }

    #inputTable th,
    #inputTable td {
        padding: 8px;
        text-align: left;
    }

    #inputTable input {
        width: 100%;
    }

    .hidden {
        display: none;
    }
</style>
<!-- Modal content-->
<div class="modal-content">

    <div class="modal-header" style="background: #00659f; color: white; display: flex; justify-content: space-between;">
        <h4 class="modal-title" style="margin: 0;"> Preview</h4>


    </div>
    <?php
        $type = isset($arr_out_pass['type']) ? $arr_out_pass['type'] : '';
    ?>
    <div style="text-align: center;">
        <table style="width: 100%; margin-top:10px;">
            <tr>
                <td style="width: 20%; padding-right:25px;"><img style="width: auto; height: 65px;" src="https://v1.mypayrollmaster.online/<?php echo $arr_comp_contact_info['CompanyContactInfo']['logo']; ?>" alt="Company Logo" /></td>
                <td style="width: 60%;">
                    <h4 style="text-align: center;"><?php echo isset($arr_comp_contact_info['CompanyContactInfo']['business_name']) ? $arr_comp_contact_info['CompanyContactInfo']['business_name'] : ''; ?></h4>
                </td>
                <td style="width: 20%;">&nbsp;</td>
            </tr>
            <tr>
                <td style="width: 20%; padding-right:25px;">&nbsp;</td>
                <td style="width: 60%;">
                    <h4 style="text-align: center;"><span style="border-bottom: 1px dashed #000;"><?php echo strtoupper($type); ?>&nbsp;OUT PASS</span></h4>
                </td>
                <td style="width: 20%;">&nbsp;</td>
            </tr>
        </table>
    </div>

    <div class="modal-body">
        <!-- Form starts -->
        <div class="container" style="width:100%;">
            <form class="form-horizontal" method="post" action="<?php echo $this->webroot; ?>OutPass/printPass" id="previewForm">
                <!-- <input id="gate_pass_pkey" name="gate_pass_pkey" value="<?php echo isset($arr_gate_pass['OutPass']["gate_pass_pkey"]) ? $arr_gate_pass['OutPass']["gate_pass_pkey"] : ''; ?>" type="hidden" class="form-control input-md"> -->

                <div class="form-group">
                    <div class="col-md-12">
                        <label style="text-align:left;" class="col-md-4 control-label" for="classification">Out Pass No &nbsp;<span style="color:red;">*</span></label>
                        <div class="col-md-1">:</div>
                        <div class="col-md-7">
                            <input id="out_pass_number" readonly disabled name="out_pass_number" value="<?php echo isset($arr_out_pass['out_pass_number']) ? $arr_out_pass['out_pass_number'] : ''; ?>" type="text" placeholder="" class="form-control input-md" required autocomplete="off">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-md-12">
                        <label style="text-align:left;" class="col-md-4 control-label" for="req_person">Date<span style="color:red;">*</span></label>
                        <div class="col-md-1">:</div>
                        <div class="col-md-7">
                            <input id="out_pass_date1" name="out_pass_date"  onchange="checkPersonalOutPass()" disabled readonly value="<?php echo isset($arr_out_pass['out_pass_date']) ? $arr_out_pass['out_pass_date'] : ''; ?>" type="text" placeholder="" class="form-control input-md" required autocomplete="off">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-md-12">
                        <label style="text-align:left;" class="col-md-4 control-label" for="classification">Out Pass Type &nbsp;<span style="color:red;">*</span></label>
                        <div class="col-md-1">:</div>
                        <div class="col-md-7">
                            <select id="type1" disabled name="type"  onchange="checkPersonalOutPass()" class="form-control js-example-basic-single" style="width: 100%" required>
                                <option value="">--Select--</option>
                                <option value="Personal" <?php echo (isset($arr_out_pass["type"]) && $arr_out_pass["type"] == 'Personal') ? 'selected="selected"' : ''; ?>>Personal</option>
                                <option value="Official" <?php echo (isset($arr_out_pass["type"]) && $arr_out_pass["type"] == 'Official') ? 'selected="selected"' : ''; ?>>Official</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-md-12">
                        <label style="text-align:left;" class="col-md-4 control-label" for="staff_pkey">Staff Name<span style="color:red;">*</span></label>
                        <div class="col-md-1">:</div>
                        <div class="col-md-7">
                            <select id="staff_pkey1" name="staff_pkey" onchange="checkPersonalOutPass()" disabled class="form-control js-example-basic-single" style="width: 100%" required="">
                                <option value="">--Select--</option>
                                <?php
                                foreach ($arr_emp as $emp) { ?>
                                    <option value="<?php echo $emp['ei']['emp_pkey']; ?>" <?php echo (isset($arr_out_pass["staff_pkey"]) && $arr_out_pass["staff_pkey"] == $emp['ei']['emp_pkey']) ? 'selected="selected"' : ''; ?>><?php echo $emp['ei']['EmpName'] . ' - ' . $emp['ei']['employee_id']; ?></option>
                                <?php }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>


                <div class="form-group">
                    <div class="col-md-12">
                        <label style="text-align:left;" class="col-md-4 control-label" for="out_time">Out Time &nbsp;<span style="color:red;">*</span></label>
                        <div class="col-md-1">:</div>
                        <div class="col-md-7">
                            <input id="out_time1" readonly disabled name="out_time" value="<?php echo isset($arr_out_pass['out_time']) ? $arr_out_pass['out_time'] : ''; ?>" type="text" placeholder="" class="form-control input-md timepicker" required autocomplete="off">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-md-12">
                        <label style="text-align:left;" class="col-md-4 control-label" for="in_time">In Time &nbsp;<span style="color:red;">*</span></label>
                        <div class="col-md-1">:</div>
                        <div class="col-md-7">
                            <input id="in_time1" readonly disabled name="in_time" value="<?php echo isset($arr_out_pass['in_time']) ? $arr_out_pass['in_time'] : ''; ?>" type="text" placeholder="" class="form-control input-md timepicker" required autocomplete="off">
                        </div>
                    </div>
                </div>


                <div class="form-group">
                    <div class="col-md-12">
                        <label style="text-align:left;" class="col-md-4 control-label" for="remarks">Remarks &nbsp;<span style="color:red;">*</span></label>
                        <div class="col-md-1">:</div>
                        <div class="col-md-7">
                            <input id="remarks" name="remarks" disabled maxlength="50" value="<?php echo isset($arr_out_pass['remarks']) ? $arr_out_pass['remarks'] : ''; ?>" type="text" placeholder="" class="form-control input-md" required autocomplete="off">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-md-12">
                        <label style="text-align:left;" class="col-md-4 control-label" for="sanctioned_by">Sanctioned by<span style="color:red;">*</span></label>
                        <div class="col-md-1">:</div>
                        <div class="col-md-7">
                            <select id="sanctioned_by1" name="sanctioned_by" disabled class="form-control js-example-basic-single" style="width: 100%" required="">
                                <option value="">--Select--</option>
                                <?php
                                foreach ($arr_emp as $emp) { ?>
                                    <option value="<?php echo $emp['ei']['emp_pkey']; ?>" <?php echo (isset($arr_out_pass["sanctioned_by"]) && $arr_out_pass["sanctioned_by"] == $emp['ei']['emp_pkey']) ? 'selected="selected"' : ''; ?>><?php echo $emp['ei']['EmpName'] . ' - ' . $emp['ei']['employee_id']; ?></option>
                                <?php }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-md-12">
                        <label style="text-align:left;" class="col-md-4 control-label" for="req_person">Issue By<span style="color:red;">*</span></label>
                        <div class="col-md-1">:</div>
                        <div class="col-md-7">
                            <select id="issued_by1" name="issued_by" disabled class="form-control js-example-basic-single" style="width: 100%" required="">
                                <option value="">--Select--</option>
                                <?php
                                foreach ($arr_emp as $emp) { ?>
                                    <option value="<?php echo $emp['ei']['emp_pkey']; ?>" <?php echo (isset($arr_out_pass["issued_by"]) && $arr_out_pass["issued_by"] == $emp['ei']['emp_pkey']) ? 'selected="selected"' : ''; ?>><?php echo $emp['ei']['EmpName'] . ' - ' . $emp['ei']['employee_id']; ?></option>
                                <?php }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-md-12">
                        <label style="text-align:left;" class="col-md-4 control-label"></label>
                        <div class="col-md-1">&nbsp;</div>
                        <div class="col-md-7">
                            <label style="text-align:left; padding-left:54px; padding-right:0px;" class="col-md-4 control-label" for="req_person">Security &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:</label>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-md-12">
                        <label style="text-align:left;" class="col-md-4 control-label">Signature of the Authorised Person&nbsp;:</label>
                        <div class="col-md-1">&nbsp;</div>
                        <div class="col-md-7">
                            <label style="text-align:right;" class="col-md-4 control-label" for="req_person">Date & Time &nbsp;:</label>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" id="btn-submit1" onclick="btnSubmitPass();" class="btn btn-primary">Issue</button>
                    <?php
                    if ($status == 0) { ?>
                        <!-- <button type="button" id="btn-pass" onclick="btnSubmitPass(true);" class="btn btn-primary">Save</button> -->
                        <button type="button" id="btn-edit" onclick="btnEditPass();" class="btn btn-primary">Edit</button>
                    <?php }
                    ?>
                    <button type="button" class="btn btn-danger" onclick="$('#largeModalForm').modal('hide');">Close</button>
                </div>

            </form>
        </div>
        <!-- form ends-->
    </div>
</div>

<script>
        function checkPersonalOutPass() {
        $("#btn-submit1").prop("disabled", true);
        $("#btn-pass").prop("disabled", true);

        var type = $('#type1').val();
        if (type == 'Personal') {
            var date = $('#out_pass_date1').val();
            var pkey = $('#staff_pkey1').val();

            // Perform AJAX request
            if (date != '' && pkey != '') {
                $.ajax({
                    type: 'GET',
                    url: "<?php echo $this->webroot; ?>OutPass/checkPersonalPass/" + date + "/" + pkey,
                    success: function(response) {
                        // Handle the success response here
                        console.log('Status', response);
                        if (response.status == 'failure') {
                            $("#btn-submit1").prop("disabled", true);
                            $("#btn-pass").prop("disabled", true);
                            $.notify(response.message, {
                                type: 'danger',
                                placement: {
                                    from: "bottom",
                                    align: "left"
                                },
                                allow_dismiss: true,
                                z_index: 9999
                            });
                        } else if (response.status == 'success') {
                            $("#btn-submit1").prop("disabled", false);
                            $("#btn-pass").prop("disabled", false);
                        }
                    },
                    error: function(error) {
                        // Handle the error here
                        console.error(error);
                    }
                });
            }

        } else {
            $("#btn-submit1").prop("disabled", false);
            $("#btn-pass").prop("disabled", false);
        }
    }


    function btnEditPass() {
        // Find all input fields and selects in the form and toggle the disabled attribute
        $("#previewForm input, #previewForm select, #previewForm textarea").prop("disabled", false);

    }

    function btnSubmitPass(pass = false) {
        // Flag to check if all fields have values

        var pass = pass;
        // console.log('allFieldsFilled', emptyField);
        // Display a message based on whether all fields are filled
        if (true) {

            var isFormEdited = true; // Default value

            // Check if the form has been edited (you might adjust this condition based on your specific logic)
            if ($("#previewForm input:disabled").length > 0) {
                isFormEdited = false;
            }
            // Add any additional actions you want to perform when all fields are filled
            if (pass == false) {
                var confirmation = confirm("Are you sure you want to issue?");
            } else {
                var confirmation = confirm("Are you sure you want to save?");
            }

            if (confirmation) {
                // Collect form data
                var pkey = <?php echo json_encode($pkey); ?>;
                var formData = $("#previewForm").serialize();

                // Perform AJAX request
                $.ajax({
                    url: "<?php echo $this->webroot; ?>OutPass/printPass/" + pkey + "/" + isFormEdited + "/" + pass,
                    type: "POST",
                    data: formData,
                    success: function(response) {
                        // Handle the response from the server

                        if (pass == false) {

                            $.notify('Pass issued successfully!', {
                                type: 'success',
                                allow_dismiss: false,
                                className: 'notify-container',
                                z_index: 9999
                            });
                            $.notify('Downloading out pass...', {
                                type: 'success',
                                allow_dismiss: false,
                                className: 'notify-container',
                                z_index: 9999
                            });

                            printOutPass(pkey, pass);
                        } else {
                            $.notify('Pass saved successfully!', {
                                type: 'success',
                                allow_dismiss: false,
                                className: 'notify-container',
                                z_index: 9999
                            });
                        }
                        $("#largeModalForm").modal("hide");
                        $('#documents_manager').datagrid('reload');

                    },
                    error: function(xhr, status, error) {
                        // Handle errors
                        console.error(xhr.responseText);
                        $.notify('Error submitting the form. Please try again.', {
                            type: 'danger',
                            allow_dismiss: false,
                            className: 'notify-container',
                            z_index: 9999
                        });
                        $('#documents_manager').datagrid('reload');
                        $("#largeModalForm").modal("hide");
                    }

                });
            } else {
                // $("#largeModalForm").modal("hide");
            }
            // $('#documents_manager').datagrid('reload');
        } else {
            alert("Please fill the required fields.");
        }
        // $("#largeModalForm").modal("hide");

        $('#documents_manager').datagrid('reload');
    }


    function printOutPass(pkey = 0, pass = false) {
        // Create form element
        var form = document.createElement('form');
        form.style.display = 'none'; // Hide the form

        // Construct the URL
        var url = livesite + 'OutPass/printOutPass/';
        if (pkey) {
            url += pkey;
        }

        // Set form attributes
        form.method = 'GET';
        form.action = url;

        // Append the form to the body
        document.body.appendChild(form);

        // Prevent the default form submission (which causes a page reload)
        form.addEventListener('submit', function(event) {
            event.preventDefault();
        });

        // Submit the form
        form.submit();

        // Remove the form from the body
        document.body.removeChild(form);
        $("#largeModalForm").modal("hide");

    }


    $(document).ready(function() {
        console.log('Applying select2 to #type');
        $('#type1').select2().trigger('select2:open');
        $('#staff_pkey1').select2();
        $('#issued_by1').select2();
        $('#sanctioned_by1').select2();
        $('#in_time1').timepicker({
            format: 'hh:mm:ss A',
            showMeridian: true,
            showSeconds: true,
        });

        $('#out_time1').timepicker({
            format: 'hh:mm:ss A',
            showMeridian: true,
            explicitMode: false,
            showSeconds: true,
        });

        $("#out_pass_date1").datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true
        });

        var edit = <?php echo json_encode($edit); ?>;

        if (edit == 'true' || edit == true) {
            console.log('Edit', edit);
            $("#previewForm input, #previewForm select, #previewForm textarea").prop("disabled", false);

            // $('#addItemBtn').removeClass('hidden');
        }
    })
</script>