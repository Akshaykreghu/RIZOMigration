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

    .delete-column {
        display: none;
        /* Initially hide the column */
    }
</style>
<!-- Modal content-->
<div class="modal-content">

    <div class="modal-header" style="background: #00659f; color: white; display: flex; justify-content: space-between;">
        <h4 class="modal-title" style="margin: 0;"> Preview</h4>


    </div>
    <?php
    $type = '';
    if (isset($arr_gate_pass['GatePass']['type']) && $arr_gate_pass['GatePass']['type'] == 'Material Gate Pass - Free') {
        $type = 'Material Gate Pass - Free';
    } elseif (isset($arr_gate_pass['GatePass']['type']) && $arr_gate_pass['GatePass']['type'] == 'Material Gate Pass - Return') {
        $type = 'Material Gate Pass - Return';
    } elseif (isset($arr_gate_pass['GatePass']['type']) && $arr_gate_pass['GatePass']['type'] == 'Material Gate Pass - Sale') {
        $type = 'Material Gate Pass - Sale';
    }
    ?>
    <!-- <img style=" margin-left: 40px;margin-top: 40px; width: 70px; height: auto; " src="https://v1.mypayrollmaster.online/<?php echo $arr_comp_contact_info['CompanyContactInfo']['logo']; ?>" alt="Company Logo" />
    <h4 style="text-align: center;"><?php echo isset($arr_comp_contact_info['CompanyContactInfo']['business_name']) ? $arr_comp_contact_info['CompanyContactInfo']['business_name'] : ''; ?></h4> -->

    <div style="text-align: center; margin-top:10px;">
        <table style="width: 100%;">
            <tr>
                <td style="width: 20%;">&nbsp;</td>
                <td style="width: 60%;">
                    <h4 style="text-align: center;font-weight:bold;"><?php echo isset($arr_comp_contact_info['CompanyContactInfo']['business_name']) ? $arr_comp_contact_info['CompanyContactInfo']['business_name'] : ''; ?></h4>
                </td>
                <td style="width: 20%; text-align:left; padding-left:40px;"><img style="width: auto; height: 70px;" src="https://v1.mypayrollmaster.online/<?php echo $arr_comp_contact_info['CompanyContactInfo']['logo']; ?>" alt="Company Logo" /></td>
            </tr>
            <tr>
                <td style="width:20%">&nbsp;</td>
                <td style="width: 60%; font-weight:bold;"><span style="border-bottom: 1px dashed #000;"><?php echo strtoupper($type); ?></span></td>
                <td style="width: 20%;">&nbsp;</td>
            </tr>
            <tr>
                <td style="width:20%;">&nbsp;</td>
                <td style="width: 60%; padding-top: -10px;">&nbsp;</td>
                <td style="width: 20%; ">&nbsp;</td>
            </tr>
            <tr>
                <td style="width:20%;">&nbsp;</td>
                <td style="width: 60%;">&nbsp;</td>
                <td style="width: 20%; ">&nbsp;</td>
            </tr>
        </table>
    </div>

    <div class="modal-body">
        <!-- Form starts -->
        <div class="container" style="width:100%;">
            <form class="form-horizontal" method="post" action="<?php echo $this->webroot; ?>GatePass/printPass" id="previewForm">
                <!-- <input id="gate_pass_pkey" name="gate_pass_pkey" value="<?php echo isset($arr_gate_pass['GatePass']["gate_pass_pkey"]) ? $arr_gate_pass['GatePass']["gate_pass_pkey"] : ''; ?>" type="hidden" class="form-control input-md"> -->

                <div class="form-group">

                    <div class="col-md-6">
                        <label style="text-align:left;margin-right: -6px;width: 140px" class="col-md-4 control-label" for="EmpName">Gate Pass No &nbsp;</label>
                        <div class="col-md-1">:</div>
                        <div class="col-md-7">
                            <input id="gate_pass_no" readonly name="gate_pass_no" disabled value="<?php echo isset($arr_gate_pass['GatePass']["gate_pass_no"]) ? $arr_gate_pass['GatePass']["gate_pass_no"] : ''; ?>" type="text" placeholder="" class="form-control input-md" required autocomplete="off">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label style="text-align:left;margin-right: -6px;width: 140px" class="col-md-4 control-label" for="req_person">Requested Person &nbsp;</label>
                        <div class="col-md-1">:</div>
                        <div class="col-md-7">
                            <select id="req_person" disabled name="req_person" class="form-control js-example-basic-single" style="width: 100%" required="">
                                <option value="">--Select--</option>
                                <?php
                                foreach ($arr_emp as $emp) { ?>
                                    <option value="<?php echo $emp['ei']['emp_pkey']; ?>" <?php echo (isset($arr_gate_pass['GatePass']["req_person"]) && $arr_gate_pass['GatePass']["req_person"] == $emp['ei']['emp_pkey']) ? 'selected="selected"' : ''; ?>><?php echo $emp['ei']['EmpName'] . ' - ' . $emp['ei']['employee_id']; ?></option>
                                <?php }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!--   <div class="form-group">
                    <div class="col-md-6">
                        <label style="text-align:left;" class="col-md-4 control-label" for="issued_to">Issued To&nbsp;<span style="color:red;">*</span></label>
                        <div class="col-md-1">:</div>
                        <div class="col-md-7">
                            <select id="issued_to" disabled name="issued_to" class="form-control js-example-basic-single" style="width: 100%" required="">
                                <option value="">--Select--</option>
                                <?php
                                foreach ($arr_emp as $emp) { ?>
                                    <option value="<?php echo $emp['ei']['emp_pkey']; ?>" <?php echo (isset($arr_gate_pass['GatePass']["issued_to"]) && $arr_gate_pass['GatePass']["issued_to"] == $emp['ei']['emp_pkey']) ? 'selected="selected"' : ''; ?>><?php echo $emp['ei']['EmpName'] . ' - ' . $emp['ei']['employee_id']; ?></option>
                                <?php }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label style="text-align:left;" class="col-md-4 control-label" for=""> &nbsp;</label>
                        <div class="col-md-1">&nbsp;</div>
                        <div class="col-md-7">

                        </div>
                    </div>
                </div>
 -->
                <div class="form-group">
                    <div class="col-md-6">
                        <label style="text-align:left;" class="col-md-4 control-label" for="issued_to">Issues To&nbsp;<span style="color:red;">*</span></label>
                        <div class="col-md-1">:</div>
                        <div class="col-md-7">
                            <textarea id="issued_to" disabled name="issued_to" required class="form-control input-md" style="height: auto;" maxlength="50"><?php echo isset($arr_gate_pass['GatePass']["issued_to"]) ? $arr_gate_pass['GatePass']["issued_to"] : ''; ?></textarea>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label style="text-align:left;" class="col-md-4 control-label" for=""> &nbsp;</label>
                        <div class="col-md-1">&nbsp;</div>
                        <div class="col-md-7">

                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-6">
                        <label style="text-align:left;" class="col-md-4 control-label" for="purpose">Purpose&nbsp;<span style="color:red;">*</span></label>
                        <div class="col-md-1">:</div>
                        <div class="col-md-7">
                            <textarea id="purpose" required disabled name="purpose" class="form-control input-md" style="height: auto;" maxlength="50"><?php echo isset($arr_gate_pass['GatePass']["purpose"]) ? $arr_gate_pass['GatePass']["purpose"] : ''; ?></textarea>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label style="text-align:left;" class="col-md-4 control-label" for=""> &nbsp;</label>
                        <div class="col-md-1">&nbsp;</div>
                        <div class="col-md-7">

                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-md-6">
                        <label style="text-align:left;" class="col-md-4 control-label" for="remarks">Remarks&nbsp;<span style="color:red;">*</span></label>
                        <div class="col-md-1">:</div>
                        <div class="col-md-7">
                            <textarea id="remarks" disabled name="remarks" required class="form-control input-md" style="height: auto;" maxlength="50"><?php echo isset($arr_gate_pass['GatePass']["remarks"]) ? $arr_gate_pass['GatePass']["remarks"] : ''; ?></textarea>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label style="text-align:left;" class="col-md-4 control-label" for=""> &nbsp;</label>
                        <div class="col-md-1">&nbsp;</div>
                        <div class="col-md-7">

                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button id="addItemBtn" onclick="addItem()" type="button" class="btn btn-primary hidden">Add Item</button>
                </div>

                <?php
                // debug($arr_item);
                if (count($arr_item) > 0) {
                ?>
                    <div id='tableDiv' class="col-md-12" style="display: block; margin-bottom: 20px;">

                        <table id="inputTable">
                            <thead>
                                <tr>
                                    <th>Sl No.</th>
                                    <th>Item</th>
                                    <th>Quantity</th>
                                    <th style="width: 100px;">UOM</th>
                                    <th>Rate</th>
                                    <th>Amount</th>
                                    <?php
                                    if (isset($arr_gate_pass['GatePass']["type"]) && $arr_gate_pass['GatePass']["type"] == 'Material Gate Pass - Return') { ?>
                                        <th>Expected Returnable Date</th>
                                    <?php }
                                    ?>

                                </tr>
                            </thead>
                            <tbody>
                                <!-- Add your rows with input boxes here -->
                                <?php
                                $total_amount = 0;
                                // $debug($arr_item);
                                foreach ($arr_item as $key => $item) {
                                    $total_amount += isset($item['gi']['amount']) ? $item['gi']['amount'] : 0;
                                ?>
                                    <input type="hidden" name="gate_pass_items_pkey[]" value="<?php echo isset($item['gi']['gate_pass_items_pkey']) ? $item['gi']['gate_pass_items_pkey'] : 0; ?>">
                                    <tr>

                                        <td><?php echo $key + 1; ?></td>
                                        <td><input disabled name="item_name[]" maxlength="100" type="text" value="<?php echo $item['gi']['item_name']; ?>" placeholder=""></td>
                                        <td><input class="qty" disabled name="qty[]" type="number" oninput="updateAmount(this)" value="<?php echo $item['gi']['qty']; ?>" placeholder=""></td>
                                        <td>
                                            <select name="units[]" class="form-control select3" style="width: 100px;" disabled>
                                                <option value="">Select--</option>
                                                <?php
                                                $unitOptions = ['Meter', 'KG', 'Litre', 'Load', 'Sqft', 'Sheet', 'Coil', 'Set', 'Pair', 'Roll', 'Nos', 'Pkt', 'Pcs', 'Feet', 'Tone', 'Box', 'Bags', 'Ton']; // Your array of units
                                                foreach ($unitOptions as $option) {
                                                    $selected = ($item['gi']['units'] == $option) ? 'selected="selected"' : '';
                                                    echo "<option value='$option' $selected>$option</option>";
                                                }
                                                ?>
                                            </select>
                                        </td>
                                        <td><input disabled class="rate" name="rate[]" type="number" oninput="updateAmount(this)" value="<?php echo $item['gi']['rate']; ?>" placeholder=""></td>
                                        <td><input disabled class="amt" name="amount[]" type="text" readonly value="<?php echo $item['gi']['amount']; ?>" placeholder=""></td>

                                        <?php
                                        if (isset($arr_gate_pass['GatePass']["type"]) && $arr_gate_pass['GatePass']["type"] == 'Material Gate Pass - Return') { ?>
                                            <td><input class="return" disabled readonly name="return[]" type="text" value="<?php echo $item['gi']['return']; ?>" placeholder="" autocomplete="off"></td>
                                        <?php }
                                        ?>
                                        <td class="delete-column"><button type="button" class="btn btn-danger btn-sm" onclick="deleteRow(this)">Delete</button></td>
                                    </tr>
                                <?php
                                }
                                ?>
                                <tr>
                                    <th colspan="5" style="text-align:right;">Total</th>
                                    <th id="total"><?php echo $total_amount; ?></th>
                                </tr>
                                <!-- Add more rows as needed -->
                            </tbody>
                        </table>
                    </div>
                <?php
                }
                ?>


                <div class="form-group">
                    <div class="col-md-6">
                        <label style="text-align:left;margin-right: -6px;width: 140px" class="col-md-4 control-label" for="issued_by">Issued By &nbsp;</label>
                        <div class="col-md-1">:</div>
                        <div class="col-md-7">
                            <select id="issued_by" disabled name="issued_by" class="form-control js-example-basic-single" style="width: 100%" required="">
                                <option value="">--Select--</option>
                                <?php
                                foreach ($arr_emp as $emp) { ?>
                                    <option value="<?php echo $emp['ei']['emp_pkey']; ?>" <?php echo (isset($arr_gate_pass['GatePass']["issued_by"]) && $arr_gate_pass['GatePass']["issued_by"] == $emp['ei']['emp_pkey']) ? 'selected="selected"' : ''; ?>><?php echo $emp['ei']['EmpName'] . ' - ' . $emp['ei']['employee_id']; ?></option>
                                <?php }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label style="text-align:left;margin-right: -6px;width: 140px" class="col-md-4 control-label" for="sanctioned_by">Sanctioned By &nbsp;</label>
                        <div class="col-md-1">:</div>
                        <div class="col-md-7">
                            <select id="sanctioned_by" disabled name="sanctioned_by" class="form-control js-example-basic-single" style="width: 100%" required="">
                                <option value="">--Select--</option>
                                <?php
                                foreach ($arr_emp as $emp) { ?>
                                    <option value="<?php echo $emp['ei']['emp_pkey']; ?>" <?php echo (isset($arr_gate_pass['GatePass']["sanctioned_by"]) && $arr_gate_pass['GatePass']["sanctioned_by"] == $emp['ei']['emp_pkey']) ? 'selected="selected"' : ''; ?>><?php echo $emp['ei']['EmpName'] . ' - ' . $emp['ei']['employee_id']; ?></option>
                                <?php }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>


                <div class="form-group">
                    <div class="col-md-6">
                        <label style="text-align:left;" class="col-md-4 control-label" for="issued_date">Date&nbsp;<span style="color:red;">*</span></label>
                        <div class="col-md-1">:</div>
                        <div class="col-md-7">
                            <input disabled readonly id="issued_date" name="issued_date" value="<?php echo isset($arr_gate_pass['GatePass']["issued_date"]) ? $arr_gate_pass['GatePass']["issued_date"] : ''; ?>" type="text" required class="form-control input-md">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label style="text-align:left;" class="col-md-4 control-label" for=""> &nbsp;</label>
                        <div class="col-md-1">&nbsp;</div>
                        <div class="col-md-7">

                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-md-6">
                        <label style="text-align:left;" class="col-md-4 control-label" for=""> &nbsp;</label>
                        <div class="col-md-1">&nbsp;</div>
                        <div class="col-md-7">

                        </div>
                    </div>
                    <div class="col-md-6">
                        <label style="text-align:left;" class="col-md-4 control-label" for="">Name of Security&nbsp;<span style="color:red;">*</span></label>
                        <div class="col-md-1">:</div>
                        <div class="col-md-7">

                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-md-6">
                        <label style="text-align:left;margin-right: -6px;width: 140px" class="col-md-4 control-label" for="issued_by">Signature of the Authorized Person &nbsp;</label>
                        <div class="col-md-1">:</div>
                        <div class="col-md-7">

                        </div>
                    </div>

                    <div class="col-md-6">
                        <label style="text-align:left;margin-right: -6px;width: 140px" class="col-md-4 control-label" for="sanctioned_by">Date & Time &nbsp;</label>
                        <div class="col-md-1">:</div>
                        <div class="col-md-7">

                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" id="btn-submit" onclick="btnSubmit();" class="btn btn-primary">Issue</button>
                    <?php
                    if ($status == 0) { ?>
                        <!-- <button type="button" id="btn-pass" onclick="btnSubmit(true);" class="btn btn-primary">Save</button> -->
                        <button type="button" id="btn-edit" onclick="btnEdit();" class="btn btn-primary">Edit</button>
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
    // Function to show the delete column
    function showDeleteColumn() {
        $('.delete-column').show();
    }


    function btnEdit() {
        // Find all input fields and selects in the form and toggle the disabled attribute
        $("#previewForm input, #previewForm select, #previewForm textarea").prop("disabled", false);

        // Remove the "hidden" class and show the delete column
        showDeleteColumn();

        $('#addItemBtn').removeClass('hidden');

    }

    function btnSubmit(pass = false) {
        // Flag to check if all fields have values
        var allFieldsFilled = true;
        // Iterate through each input field within the form
        $("#previewForm input[type='text'], #previewForm select").each(function() {
            // Check if the field has a value
            $('input, select').each(function() {
                // Check if the field is not disabled
                if (!$(this).is(':disabled')) {
                    // Check if the field is empty
                    if ($(this).val() === "") {
                        emptyField = $(this).attr('id') || $(this).attr('name');
                        if (emptyField !== 'searchqupo') {
                            allFieldsFilled = false;
                            console.log('emptyField', emptyField);
                            return false; // Stop the iteration if any non-disabled field is empty
                        }
                    }
                }
            });
        });

        var pass = pass;
        // console.log('allFieldsFilled', emptyField);
        // Display a message based on whether all fields are filled
        var rowCount = $('#inputTable tbody tr:not(:last-child)').length;
        console.log('RowCount', rowCount);
        if (rowCount > 0) {

            if (allFieldsFilled) {
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
                        url: "<?php echo $this->webroot; ?>GatePass/printPass/" + pkey + "/" + isFormEdited + "/" + pass,
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
                                $.notify('Downloading gate pass...', {
                                    type: 'success',
                                    allow_dismiss: false,
                                    className: 'notify-container',
                                    z_index: 9999
                                });

                                printGatePass(pkey, pass);
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
                            $("#largeModalForm").modal("hide");
                            $('#documents_manager').datagrid('reload');
                        }

                    });
                } else {
                    // $("#largeModalForm").modal("hide");
                }
                $('#documents_manager').datagrid('reload');
            } else {
                $.notify("Please fill the required fields.", {
                    type: 'danger',
                    allow_dismiss: false,
                    className: 'notify-container',
                    z_index: 9999
                });
            }
        } else {
            $.notify("No item to submit.", {
                type: 'danger',
                allow_dismiss: false,
                className: 'notify-container',
                z_index: 9999
            });
        }
        // $("#largeModalForm").modal("hide");


    }

    // To print
    function printGatePass(pkey = 0, pass = false) {
        // Create form element
        var form = document.createElement('form');
        form.style.display = 'none'; // Hide the form

        // Construct the URL
        var url = livesite + 'GatePass/printGatePass/';
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


    // Function to update row counts in the first column
    function updateRowCount() {
        $('#inputTable tbody tr:not(:last-child)').each(function(index) {
            $(this).find('td:first').text(index + 1);
        });
    }

    function updateAmount(input) {
        // Get the row containing the input field
        var row = input.closest('tr');
        console.log('Input', row);

        // Find the related input fields and amount element within the same row
        var qtyInput = row.querySelector('.qty');
        var rateInput = row.querySelector('.rate');
        var amountInput = row.querySelector('.amt');

        // Get the values of qty and rate
        var qty = parseFloat(qtyInput.value) || 0;
        var rate = parseFloat(rateInput.value) || 0;
        console.log('qty', qty);
        console.log('rate', rate);
        // Calculate the product of qty and rate
        var amount = qty * rate;
        console.log('Amount', amount);
        // Update the amount field in the same row
        amountInput.value = amount.toFixed(2);

        var totalAmount = 0;
        $('.amt').each(function() {
            totalAmount += parseFloat($(this).val()) || 0;
        });
        console.log();
        // Update the total cell
        $('#total').text(totalAmount.toFixed(2));
    }

    // Function to handle row deletion
    function deleteRow(btn) {
        var row = $(btn).closest('tr');
        var amountInput = row.find('.amt');

        // Get the amount of the deleted row
        var deletedAmount = parseFloat(amountInput.val()) || 0;
        row.remove();
        updateRowCount();
        toggleTableVisibility();
        // Update the total amount by subtracting the deleted amount
        updateTotalAmount(-deletedAmount);
    }

    function updateTotalAmount(change) {
        var totalAmountElement = $('#total');
        var totalAmount = parseFloat(totalAmountElement.text()) || 0;

        // Update the total amount by adding the change
        totalAmount += change;

        // Update the total cell
        totalAmountElement.text(totalAmount.toFixed(2));
    }

    // Function to toggle table visibility based on the number of rows
    function toggleTableVisibility() {
        // var rowCount = $('#inputTable tbody tr').length;
        var rowCount = $('#inputTable tbody tr:not(:last-child)').length;
        console.log('RowCount', rowCount);
        if (rowCount === 0) {
            $('#inputTable').hide();
            $('#tableDiv').hide();
        } else {
            $('#inputTable').show();
            $('#tableDiv').show();
        }
    }

    // Add item button click event
    function addItem() {
        $('#tableDiv').toggle(true);

        // Clone the first row and append it to the tbody
        var rowCount = $('#inputTable tbody tr').length;
        var unitOptions = ['Meter', 'KG', 'Litre', 'Load', 'Sqft', 'Sheet', 'Coil', 'Set', 'Pair', 'Roll', 'Nos', 'Pkt', 'Pcs', 'Feet', 'Tone', 'Box', 'Bags', 'Ton'];
        var selectOptionsArray = unitOptions.map(function(option) {
            return '<option value="' + option + '">' + option + '</option>';
        }).join('');

        var returnDate = <?php echo json_encode($arr_gate_pass["GatePass"]["type"]); ?>;
        console.log('returnDate', returnDate);
        var addSeventhColumn = <?php echo json_encode(isset($arr_gate_pass['GatePass']["type"]) && $arr_gate_pass['GatePass']["type"] == 'Material Gate Pass - Return'); ?>;
        var newRow = $('<tr><td>' + rowCount + '</td>' +
            '<td><input name="item_name[]" maxlength="100" type="text" value="" placeholder=""></td>' +
            '<td><input name="qty[]" class="qty" oninput="updateAmount(this)" type="number" value="" placeholder=""></td>' +
            '<td><select name="units[]" class="form-control select3" style="width:100px;"><option value="">Select--</option>' + selectOptionsArray + '</select></td>' +
            '<td><input name="rate[]" class="rate" oninput="updateAmount(this)" type="number" value="" placeholder=""></td>' +
            '<td><input name="amount[]" class="amt" readonly type="text" value="" placeholder=""></td>' +
            '</tr>');

        if (returnDate == "Material Gate Pass - Return") {
            var returnInput = $('<td><input class="return" name="return[]" readonly type="text" value="" placeholder="" autocomplete="off"></td>');
            newRow.append(returnInput);

            // Initialize date picker
            returnInput.find('.return').datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true
            });
        }

        // Create a delete button for the new row
        var deleteButton = $('<button type="button" class="btn btn-danger btn-sm" onclick="deleteRow(this)">Delete</button>');
        // Add the delete button to the last column of the new row
        newRow.append('<td></td>').find('td:last').append(deleteButton);

        newRow.find('td:first').text(rowCount);

        // Clear input values in other columns
        newRow.find('td:not(:first) input').val('');

        console.log('newRow', newRow);

        // Find the "Total" row and insert the new row before it
        $('#inputTable tbody tr:last').before(newRow);
        $('.select3').select2();


        // Initialize the date picker for the dynamically added input
        newRow.find('.return_date').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true
            // You can customize the date format as needed
        });

        if ($('#type').val() === 'Material Gate Pass - Return') {
            $('.return_date').prop('disabled', false);
        } else {
            $('.return_date').prop('disabled', true);
        }

        // Show the table if it was hidden
        $('#inputTable').show();
        $('#tableDiv').show();

        // toggleTableDivVisibility();
    }



    $(document).ready(function() {
        var edit = <?php echo json_encode($edit); ?>;
        console.log('Edit', typeof edit);
        if (edit == 'true') {
            $("#previewForm input, #previewForm select, #previewForm textarea").prop("disabled", false);
            $('#addItemBtn').removeClass('hidden');
            showDeleteColumn();
        }

        $('.select3').select2();
        $('#type').select2();
        $('#req_person').select2();
        //$('#issued_to').select2();
        $('#issued_by').select2();
        $('#sanctioned_by').select2();

        $("#issued_date").datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true
        });

        $(".return").datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true
        });

    });
</script>