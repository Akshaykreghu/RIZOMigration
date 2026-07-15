<style>
  .salary_revision_autofill {
    background-color: #80808047;
  }

  .foggy-text {
    color: #999;
    opacity: 0.6;
    filter: blur(0.3px);
  }

  #salary_structure_container {
    width: 100%;
  }

  #incrementeuploadtable {
    display: flex;
    flex-direction: column;
    align-items: center;
    width: 100%;
  }

  #incrementeuploadtable table {
    margin: 0 auto;
  }

  #incrementeuploadtable .footer {
    width: 95%;
    text-align: center;
  }

  .increment-table {
    border-collapse: collapse;
    width: 95%;
  }

  .increment-table td,
  .increment-table th {
    border: 1px solid #aaa;
    padding: 6px;
    text-align: center;
  }

  .increment-table .header {
    background-color: #dceeff;
  }

  .increment-table .highlight {
    background-color: yellow;
  }

  .increment-table .highlight-red {
    background-color: #f99;
  }

  .increment-table .green {
    background-color: #00ff99;
  }

  .increment-table .gray {
    background-color: #ddd;
  }

  .increment-table .footer {
    background-color: #666;
    color: white;
    text-align: left;
    padding: 8px;
  }

  .increment-table input {
    width: 100%;
    box-sizing: border-box;
  }

  .increment-table td {
    text-align: left;
    vertical-align: top;
  }

  .modal-header {
    font-size: 24px;
    font-weight: bold;
    margin-bottom: 20px;
    color: #333;
  }

  .form-row {
    margin-bottom: 15px;
  }

  .form-row label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
    font-size: 14px;
    color: #555;
  }

  .form-row input,
  .form-row select {
    width: 100%;
    padding: 5px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
    color: #333;
  }

  .form-row input[readonly] {
    background-color: #f9f9f9;
  }

  .form-actions {
    text-align: center;
    margin-top: 20px;
  }

  .form-actions .btn {
    padding: 10px 20px;
    border-radius: 5px;
    font-size: 14px;
    cursor: pointer;
    margin: 0 10px;
  }

  .btn-close {
    background-color: #ccc;
    border: none;
    color: #fff;
  }

  .btn-save {
    background-color: #007bff;
    border: none;
    color: #fff;
  }

  .btn:hover {
    opacity: 0.8;
  }

  .swal2-confirm-btn-lg,
  .swal2-cancel-btn-lg {
    font-size: 16px !important;
    padding: 10px 25px !important;
  }

  /* Edited by Akshay on 27-9-2025 */
  /* Style only the select2 generated for #emp_fkey2 */
  /* Allow multiple rows of selected items */
  #emp_fkey2+.select2-container .select2-selection--multiple {
    min-height: 20px;
    /* taller than default */
    height: auto !important;
    /* expand as needed */
    max-height: 100px;
    /* stop growing after this */
    overflow-y: auto !important;
    /* scroll after limit */
    overflow-x: hidden !important;
    /* prevent horizontal scrollbar */
    padding: 4px;
    /* breathing space */
  }

  /* Each choice on its own row */
  #emp_fkey2+.select2-container .select2-selection__choice {
    display: block !important;
    width: auto !important;
    margin: 2px 0 !important;
  }

  /* End */

  /* Edited by Akshay on 18-11-2025 */
  .narrow-input {
    width: 80px !important;
    min-width: 80px;
  }

  /* End */
  .increment-table th:nth-child(3),
  .increment-table td:nth-child(3) {
    width: 120px;
    /* Current */
  }

  .increment-table th:nth-child(5),
  .increment-table td:nth-child(5) {
    width: 120px;
    /* New – match Current */
  }

  .increment-table th:nth-child(2),
  .increment-table td:nth-child(2) {
    width: auto;
    /* Expand Salary Components */
  }

  .gap-col {
    background: transparent !important;
    border: none !important;
    visibility: hidden;
    /* hides content but keeps spacing */
  }
</style>
<!-- CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<!-- JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- Modal Content -->
<div class="modal-dialog modal-lg">
  <div class="modal-header" style="background: #00659f; color: white">
    <!-- Edited by Akshay on 10-10-2025 -->
    <h4 class="modal-title">Salary Update Form</h4>
    <!-- End -->
  </div>

  <div class="form-group" style="padding: 0px 24px;">
    <label><strong>Employee Selection:</strong></label><br>
    <label class="radio-inline">
      <input type="radio" name="is_multiple" id="single_option" value="N" checked> Single
    </label>
    &nbsp;&nbsp;
    <label class="radio-inline">
      <input type="radio" name="is_multiple" id="multiple_option" value="Y"> Multiple
    </label>

    <div id="single_form_section">
      <form class="form-horizontal" id="incrementeuploadtable" action="<?php echo $this->webroot; ?>SalaryIncrement/saveIncrement" method="POST">
        <br>
        <input type="hidden" name="is_multiple" value="N">
        <table class="increment-table">
          <tr>
            <td class="header">Employee Name</td>
            <td colspan="2" style="display:flex; align-items:center; gap:5px;">
              <!-- Edited by Akshay on 11-10-2025 -->
              <select id="Employee_fkey" class="form-control" name="emp_fkey" required style="width:100%;"
                <?php if (!empty($emp_pkey) && $emp_pkey != 0) echo 'disabled'; ?>>
                <option value="">Select</option>
                <?php
                foreach ($arr_employees as $value) {
                  $selected = ($value['emp_details']['emp_pkey'] == $emp_pkey) ? 'selected' : '';
                  echo '<option value="' . $value['emp_details']['emp_pkey'] . '" ' . $selected . '>'
                    . $value['emp_details']['first_name'] . ' '
                    . $value['emp_details']['last_name'] . ' - '
                    . $value['emp_proff']['emp_company_id'] . '</option>';
                }
                ?>
              </select>
              <?php if (!empty($emp_pkey) && $emp_pkey != 0) { ?>
                <input type="hidden" name="emp_fkey" value="<?php echo $emp_pkey; ?>">
              <?php } ?>
              <!-- Button to "enter" the selected employee -->
              <button type="button" class="btn btn-success" id="enterEmployeeBtn">
                Enter
              </button>
              <!-- End -->
            </td>
            <!-- Edited by Akshay on 11-10-2025 -->
            <td colspan="2" class="header">
              <!-- End -->
              <span id="designation_text"><small class="foggy-text">Designation</small></span>
              <input type="hidden" id="designation" name="designation">
            </td>
            <td class="header">
              <span id="branch_text"><small class="foggy-text">Branch</small></span>
              <input type="hidden" id="branch" name="branch">
            </td>
            <td class="header">
              <span id="department_text"><small class="foggy-text">Department</small></span>
              <input type="hidden" id="department" name="department">
            </td>
          </tr>
          <tr>
            <td class="header">Previous Salary (M)</td>
            <td colspan="2"><span id="prev_sal_text"></span><input type="hidden" id="prev_sal"></td>
            <td class="header" colspan="2">Joining Date</td>
            <td>
              <span id="joining_date_text"></span>
              <input type="hidden" id="joining_date_input" name="joining_date">
            </td>
          </tr>
          <tr>
            <td class="header">Previous Hike %</td>
            <td colspan="2">
              <span id="prev_hike_text"></span>
              <input type="hidden" id="prev_hike" name="previous_hike">
            </td>
            <td class="header" colspan="2">Tenure</td>
            <td>
              <span id="tenure_text"></span>
              <input type="hidden" id="tenure">
            </td>
          </tr>
        </table>

        <br>

        <table class="increment-table" id="salary_structure_table" style="display: none;">
          <tr>
            <td class="header" colspan="1" rowspan="2">
              Salary Structure:
            </td>
            <td class="header" rowspan="2">
              <select id="salary_structure" name="salary_structure" class="form-control js-example-basic-single" style="width: 100%" required="">
                <?php
                foreach ($arr_salary as $values) {
                ?>
                  <option data-foo="<?php echo $values['salary_structure']['structure_eg_amt']; ?>" <?php echo (isset($arr_professionalinfo['structure_id']) && $arr_professionalinfo['structure_id'] == $values['salary_structure']['structure_id']) ? 'selected="selected"' : ''; ?> value="<?php echo $values['salary_structure']['structure_id']; ?>"><?php echo $values['salary_structure']['structure_name'] . ' - ' . $values['salary_structure']['structure_eg_amt']; ?></option>
                <?php
                }
                ?>
              </select>
            </td>
            <td class="header" colspan="1" rowspan="2">
              <button class="btn btn-primary" id="apply_structure">Apply</button>
            </td>
          </tr>
        </table>

        <br>

        <table class="increment-table">

          <tr>
            <td class="header" colspan="2" rowspan="2">
              I will update:
              <select id="select-update" onchange="changeItem()">
                <option value="">--Select--</option>
                <option value="1">Gross Salary</option>
                <option value="2">Components</option>
              </select>
            </td>
            <td class="header" rowspan="2">
              Increment Type:
              <select id="select-item" onchange="changeItem()">
                <option value="">--Select--</option>
                <option value="new_amount">New</option>
                <option value="amount">Increment</option>
                <option value="percentage">Increment %</option>
              </select>
            </td>
          </tr>

        </table>
        <br>
        <div id="no-data" style="display: none;">
          <div class="text-danger" style="text-align:center;">Salary Structure not available.</div>
        </div>

        <div id="salary_structure_container" style="display: none;">
          <table class="increment-table" id="salary-structure">
            <thead>
              <tr class="gray">
                <th>&nbsp;</th>
                <th>Salary Components</th>
                <th>Current</th>

                <th class="gap-col" style="width:20px;"></th> <!-- Blank column -->

                <th>New</th>
                <th style="width:80px;">Increment</th>
                <th style="width:80px;">Increment %</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>

        </div>

        <!-- Edited by Akshay on 8-10-2025 -->
        <input type="hidden" id="total_direct_value" name="total_direct_value" value="">
        <input type="hidden" id="total_direct_value_permanent" name="total_direct_value_permanent" value="">
        <!-- End -->
        <br>

        <div id="footer-input" class="footer" style="text-align: left; display: none; padding: 20px;">
          <input type="hidden" name="arrear_salary" value="N">

          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; max-width: 800px; margin: auto;">

            <!-- With Effect From -->
            <div>
              <label for="increment_start_date_effective" style="font-size: 13px;">
                With Effect From: <span style="color: red;">*</span>
              </label>
              <input type="text" autocomplete="off" id="increment_start_date_effective" name="start_date_effective" required placeholder="Select Date" class="form-control">
            </div>

            <!-- Next Increment Date -->
            <div>
              <label for="increment_next_increment_date" style="font-size: 13px;">
                Next Increment Date: <span style="color: red;">*</span>
              </label>
              <input type="text" autocomplete="off" id="increment_next_increment_date" name="next_increment_date" required placeholder="Select Date" class="form-control">
            </div>

            <!-- Payout Month -->
            <div>
              <label for="form_pay_out_month" style="font-size: 13px;">
                Payout Month: <span style="color: red;">*</span>
              </label>
              <input type="text" autocomplete="off" id="form_pay_out_month" name="pay_out_month" required placeholder="Select Month" class="form-control">
            </div>

            <!-- New Column (e.g., Remarks) -->
            <div>
              <label for="remarks2" style="font-size: 13px;">
                Remarks: <span style="color: red;">*</span>
              </label>
              <input type="text" id="remarks2" name="remarks2" placeholder="Enter Remarks" class="form-control" autocomplete="off" required>
            </div>

          </div>
        </div>


        <!-- Modal Footer -->
        <div class=" modal-footer" style="text-align: right; width:95%;">
          <button type="submit" id="btn-submit" class="btn btn-primary" style="display: none;">Save</button>
          <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
    <div id="multiple_form_section" style="display: none;">
      <form id="itemIncrementForm" action="<?php echo $this->webroot; ?>SalaryIncrement/saveItemIncrement" method="POST">
        <input type="hidden" name="is_multiple" value="Y">
        <!-- Row 1: Choose Item + Salary Component -->
        <div class="form-row">
          <div class="form-group col-md-5">
            <label for="itemSelect">Choose Item: <span style="color:red">*</span></label>
            <select id="itemSelect" name="component_item" class="form-control" required>
              <option value="">-- Select --</option>
              <?php foreach ($arr_salary_head_items as $item) {
                $key = $item['salary_head_items']['salary_head_item_pkey'];
                $value = trim($item['salary_head_items']['item']);
                echo "<option value=\"$key\">$value</option>";
              } ?>
            </select>
          </div>

          <div class="form-group col-md-2">
            <label for="percentageHike">Percentage: <span style="color:red">*</span></label>
            <input type="number" id="percentageHike" name="hike" class="form-control" placeholder="Enter percentage" step="0.1" required>
          </div>

          <div class="col-md-5">
            <label for="component">Salary Component: <span style="color:red">*</span></label>
            <select id="component" name="component_item_2" class="form-control" required>
              <option value="">-- Select --</option>
              <?php foreach ($arr_salary_head_items as $item) {
                $key = $item['salary_head_items']['salary_head_item_pkey'];
                $value = trim($item['salary_head_items']['item']);
                echo "<option value=\"$key\">$value</option>";
              } ?>
            </select>
          </div>
        </div>

        <!-- Row 2: Formulae -->
        <div class="form-row">
          <div class="col-md-12">
            <p id="calculationDisplay" style="color: red; font-weight: bold; display: none;"></p>
          </div>
        </div>

        <!-- Row 3: Date of Effect + Next Increment -->
        <div class="form-row">
          <div class="col-md-6" style="margin-top: 8px;">
            <label for="effectFrom">With Effect From: <span style="color:red">*</span></label>
            <input type="date" class="form-control" id="effectFrom" name="with_effect_from" required>
          </div>
          <div class=" col-md-6" style="margin-top: 8px;">
            <label for="nextIncrementDate">Next Increment Date:</label>
            <input type="date" class="form-control" id="nextIncrementDate" name="next_increment_date">
          </div>
        </div>

        <!-- Row 4: Payout month -->
        <div class="form-row">
          <div class=" col-md-6" style="margin-top: 8px;">
            <label for="payOutMonth">Payout Month:</label>
            <input type="text" class="form-control" id="payOutMonth" name="payout_month" autocomplete="off" required placeholder="Select Month">
          </div>
        </div>

        <!-- Row 5: Type Selection -->
        <div class="form-row">
          <div class="col-md-6" style="margin-top: 8px;">
            <label for="type">Type: <span class="star" style="color:red">*</span></label>
            <select id="type" name="type" class="form-control js-example-basic-single" onchange="handleTypeChange(this);" required>
              <option value="">--Select--</option>
              <option value="Employee">Employee</option>
              <option value="Employee Type">Employee Type</option>
              <option value="Branch">Branch</option>
              <option value="Department">Department</option>
              <option value="Designation">Designation</option>
              <option value="Grade">Grade</option>
              <!-- Edited by Akshay on 27-9-2025 -->
              <option value="Category">Category</option>
              <!-- End -->
            </select>
          </div>
        </div>


        <!-- Row 7: Employee Filter -->
        <div class="form-row">
          <div class="col-md-6 d-flex align-items-center" id="type_value_row" style="margin-top: 8px;display:none;">
            <label for="type_value" id="type_value_label" class="mr-2 mb-0" style="min-width: 100px;">&nbsp;</label>
            <select id="type_value" name="type_value" class="form-control js-example-basic-single" style="width: 100%;">
              <option value="">--Select--</option>
            </select>
          </div>
        </div>

        <!-- Row 6: Type Value (Shown Conditionally) -->
        <div class="form-row" style="display: none;" id="emp_fkey2_row">
          <div class="col-md-6" style="margin-top: 8px;">
            <label for="emp_fkey2" id="emp_fkey2_label">Employee list: </label>
            <div style="display: flex; align-items: flex-start; gap: 8px; margin-top: 4px;">
              <select id="emp_fkey2" class="form-control" name="emp_fkey2[]" multiple="multiple"
                style="width: 250px; min-height: 100px;"></select>
              <div style="display: flex; flex-direction: row; gap: 4px; align-items: center;">
                <button type="button" id="selectAllEmp" class="btn btn-sm btn-primary">Select All</button>
                <button type="button" id="deselectAllEmp" class="btn btn-sm btn-secondary">Deselect All</button>
              </div>
            </div>
          </div>
        </div>




        <div class="form-row">
          <div class="col-md-6" style="margin-top:8px">
            <label for="remarks">Remarks: <span style="color: red;">*</span></label>
            <input type="text" id="remarks" name="remarks" class="form-control" required>
          </div>
        </div>

        <!-- Row 9: Action Buttons -->
        <div class="py-3" style="padding:20px 0;text-align:end;">
          <button type="button" class="btn btn-danger" data-dismiss="modal" style="margin-top: 50px;">Cancel</button>
          <button type="submit" class="btn btn-primary" style="margin-top: 50px;">Save</button>
        </div>
      </form>
    </div>
  </div>

</div>


<script>
  $.fn.modal.Constructor.prototype.enforceFocus = function() {};
  $(document).ready(function() {
    $('#single_option').on('change', function() {
      if ($(this).is(':checked')) {
        $('#single_form_section').show();
        $('#multiple_form_section').hide();
      }
    });

    $('#multiple_option').on('change', function() {
      if ($(this).is(':checked')) {
        $('#single_form_section').hide();
        $('#multiple_form_section').show();
      }
    });

    // Formula display
    function updateDisplay() {
      var itemText = $('#itemSelect option:selected').text().trim();
      var componentText = $('#component option:selected').text().trim();
      var percentage = $('#percentageHike').val();

      // Check if all fields are filled
      if (itemText !== "-- Select --" && componentText !== "-- Select --" && percentage !== "") {
        $('#calculationDisplay').html(`${itemText} = ${percentage}% × ${componentText}`);
        $('#calculationDisplay').show();
      } else {
        $('#calculationDisplay').hide();
      }
    }

    $('#itemSelect, #component, #percentageHike').on('change input', updateDisplay);

    // Edited by Akshay on 27-9-2025
    $('#emp_fkey2').val(null).trigger('change');
    $('#emp_fkey2').select2({
      placeholder: "--Select--",
      closeOnSelect: false,
      allowClear: true,
      // templateSelection: function(data, container) {
      //   // return nothing so the input stays empty
      //   return '';
      // }
    });

    // Select All
    $('#selectAllEmp').on('click', function() {
      $('#emp_fkey2 option').each(function() {
        $(this).siblings('[value="' + this.value + '"]').remove();
      });
      let allOptions = [...new Set($('#emp_fkey2 option').map(function() {
        return $(this).val();
      }).get())];
      console.log('allOptions', allOptions);

      $('#emp_fkey2').val(allOptions).trigger('change');
    });



    // Deselect All
    $('#deselectAllEmp').on('click', function() {
      $('#emp_fkey2').val(null).trigger('change');
    });
    // End
  });


  function changeItem() {
    const update = $('#select-update').val();
    const item = $('#select-item').val();

    // Always reset all fields to readonly first
    $('#gross-new-value, #gross-increment-amt, #gross-increment-pct, .increment-new-amt, .increment-amt, .increment-pct, .indirect-increment-amt, .indirect-increment-pct').attr('readonly', true);

    if (update === "1") {
      if (item === 'amount') {
        $('#gross-increment-amt').removeAttr('readonly');
      } else if (item === 'percentage') {
        $('#gross-increment-pct').removeAttr('readonly');
      }
      // Edited by Akshay on 18-11-2025
      else if (item === 'new_amount') {
        $('#gross-new-value').removeAttr('readonly');
      }
      // End
      else {
        // If item not valid, make sure all are readonly
        $('#gross-increment-amt, #gross-increment-pct').attr('readonly', true);
        $('.increment-amt, .increment-pct').attr('readonly', true);
        console.warn("Invalid item type selected for update 1", item);
      }

    } else if (update === "2") {
      if (item === 'amount') {
        $('.increment-amt').removeAttr('readonly');
      } else if (item === 'percentage') {
        $('.increment-pct').removeAttr('readonly');
      }
      // Edited by Akshay on 18-11-2025
      else if (item === 'new_amount') {
        $('.increment-new-amt').removeAttr('readonly');
      }
      // End
      else {
        // If item not valid, make sure all are readonly
        $('#gross-increment-amt, #gross-increment-pct').attr('readonly', true);
        $('.increment-amt, .increment-pct, .indirect-increment-amt, .indirect-increment-pct').attr('readonly', true);
        console.warn("Invalid item type selected for update 2", item);
      }

    } else {
      // If update not 1 or 2, make sure all are readonly
      $('#gross-increment-amt, #gross-increment-pct, .increment-amt, .increment-pct, .indirect-increment-amt, .indirect-increment-pct').attr('readonly', true);
      console.warn("Invalid update type selected");
    }
  }

  function calculateGrossTotals() {
    var totalAmt = 0;
    var totalPct = 0;
    var totalNewVal = 0;
    var count = 0;
    // Edited by Akshay on 9-10-2025
    const salaryHeadKeys = <?php echo json_encode($arr_salary_head_item_keys); ?>;
    var totalMonthlyGross = 0;
    // End
    // Direct components
    $('.increment-amt').each(function() {
      const val = parseFloat($(this).val()) || 0;
      totalAmt += val;
    });

    $('.increment-pct').each(function() {
      const val = parseFloat($(this).val()) || 0;
      totalPct += val;
      count++;
    });

    $('input[id^="new_value_"]').each(function() {
      // Edited by Akshay on 9-10-2025
      const id = $(this).attr('id');
      const key = parseInt(id.replace('new_value_', ''), 10);
      if (salaryHeadKeys.includes(key)) {
        totalMonthlyGross += val;
      }
      // End
      const val = parseFloat($(this).val()) || 0;
      totalNewVal += val;
    });

    var avgPct = count > 0 ? (totalPct / count).toFixed(2) : '0.00';

    // Update input fields
    $('#gross-increment-amt').val(totalAmt.toFixed(2));

    $('#gross-increment-pct').val(avgPct);
    $('#total_direct_value').val(totalMonthlyGross); // Edited by Akshay on 9-10-2025
    $('#gross-new-value').val(totalNewVal.toFixed(2));
    $('#yearly-gross-new-value').text(Math.round(totalNewVal * 12));
    // Indirect components
    $('.indirect-increment-amt').each(function() {
      const val = parseFloat($(this).val()) || 0;
      totalAmt += val;
    });

    $('.indirect-increment-pct').each(function() {
      const val = parseFloat($(this).val()) || 0;
      totalPct += val;
      count++;
    });

    $('input[id^="indirect_new_value_"]').each(function() {
      const val = parseFloat($(this).val()) || 0;
      totalNewVal += val;
    });

    avgPct = count > 0 ? (totalPct / count).toFixed(2) : '0.00';


    // Update CTC text cells
    $('#ctc_new_value').text(totalNewVal.toFixed(2));
    $(".emp_monthly_ctc").val(Math.round(totalNewVal));
    console.log('totalNewVal', totalNewVal);
    $(".emp_anual_ctc").val(Math.round(totalNewVal) * 12);
    $('#yearly_ctc_new_value').text(Math.round(totalNewVal) * 12);
    $('#ctc_increment_amt').text(totalAmt.toFixed(2));
    $('#ctc_increment_pct').text(avgPct);
  }

  // On change of gross
  function recalcSalaryStructure() {
    const empFkey = $('#Employee_fkey').val();
    // const gross = $('#gross-new-value').val();
    const gross = $('#total_direct_value').val(); // Edited by Akshay on 9-10-2025
    const ctcNewVal = Math.round(parseFloat($('#ctc_new_value').text()));
    const structureId = $('#salary_structure').val();

    $.ajax({
      url: livesite + 'SalaryIncrement/calcSalaryStructure/',
      method: 'POST',
      data: {
        emp_fkey: empFkey,
        monthly_gross: gross,
        monthly_ctc: ctcNewVal,
        structure_id: structureId
      },
      dataType: 'json',
      success: function(breakup) {
        var totalNewValue = 0;
        var totalCurrent = 0;
        var totalDiffAmt = 0;

        breakup.forEach(function(row) {
          const key = row.salary_breakup_temp.salary_head_item_fkey;
          const newAmount = parseFloat(row.salary_breakup_temp.amount);
          const formattedAmount = Math.round(newAmount * 100) / 100;

          // ===== DIRECT =====
          const $newInput = $(`input[name="new_value_${key}"]`);
          const $currentInput = $(`input[name="current_${key}"]`);
          const $diffAmtInput = $(`input[name="new_value_amt${key}"]`);
          const $diffPctInput = $(`input[name="new_value_pct${key}"]`);

          if ($newInput.length && $currentInput.length) {
            const currentVal = parseFloat($currentInput.val()) || 0;
            const diff = formattedAmount - currentVal;
            const pct = currentVal !== 0 ? ((diff / currentVal) * 100) : 0;

            $newInput.val(formattedAmount);
            $diffAmtInput.val(diff.toFixed(2));
            $diffPctInput.val(pct.toFixed(2));

            totalNewValue += formattedAmount;
            totalCurrent += currentVal;
            totalDiffAmt += diff;
          }

          // ===== INDIRECT =====
          const $indirectNewInput = $(`input[name="indirect_new_value_${key}"]`);
          const $indirectCurrentInput = $(`input[name="indirect_current_${key}"]`);
          const $indirectAmtInput = $(`input[name="indirect_new_value_amt${key}"]`);
          const $indirectPctInput = $(`input[name="indirect_new_value_pct${key}"]`);

          if ($indirectNewInput.length && $indirectCurrentInput.length) {
            const indirectCurrent = parseFloat($indirectCurrentInput.val()) || 0;
            const indirectDiff = formattedAmount - indirectCurrent;
            const indirectPct = indirectCurrent !== 0 ? ((indirectDiff / indirectCurrent) * 100) : 0;

            $indirectNewInput.val(formattedAmount);
            $indirectAmtInput.val(indirectDiff.toFixed(2));
            $indirectPctInput.val(indirectPct.toFixed(2));

            totalNewValue += formattedAmount;
            totalCurrent += indirectCurrent;
            totalDiffAmt += indirectDiff;
          }

          // ===== CONTRIBUTION =====
          const $contribNewInput = $(`input[name="contrib_new_value_${key}"]`);
          const $contribCurrentInput = $(`input[name="contrib_current_${key}"]`);
          const $contribAmtInput = $(`input[name="contrib_new_value_amt${key}"]`);
          const $contribPctInput = $(`input[name="contrib_new_value_pct${key}"]`);

          if ($contribNewInput.length && $contribCurrentInput.length) {
            const contribCurrent = parseFloat($contribCurrentInput.val()) || 0;
            const contribDiff = formattedAmount - contribCurrent;
            const contribPct = contribCurrent !== 0 ? ((contribDiff / contribCurrent) * 100) : 0;

            $contribNewInput.val(formattedAmount);
            $contribAmtInput.val(contribDiff.toFixed(2));
            $contribPctInput.val(contribPct.toFixed(2));

            // 🚫 Do not include contribution values in CTC totals
          }
        });

        // ===== Calculate total increment % =====
        const totalIncrementPct = totalCurrent !== 0 ? ((totalDiffAmt / totalCurrent) * 100).toFixed(2) : '0.00';

        // ===== Update CTC Row =====
        $('#ctc_new_value').text(totalNewValue.toFixed(2));
        $(".emp_monthly_ctc").val(Math.round(totalNewValue));
        $(".emp_anual_ctc").val(Math.round(totalNewValue) * 12);
        $('#yearly_ctc_new_value').text(Math.round(totalNewValue) * 12);
        $('#ctc_increment_amt').text(totalDiffAmt.toFixed(2));
        $('#ctc_increment_pct').text(totalIncrementPct);
      },
      error: function(xhr, status, err) {
        console.error('Salary calc error:', err);
      }
    });
  }


  // On change of item
  function sendIncrementData(index, element) {
    const currentVal = parseFloat($(`#current_${index}`).text()) || 0;
    const newVal = parseFloat($(`#new_value_${index}`).val()) || 0;
    // const grossVal = parseFloat($('#gross-new-value').val()) || 0;
    const grossVal = $('#total_direct_value').val() || 0; // Edited by Akshay on 9-10-2025
    const structureId = $('#salary_structure').val();
    const empFkey = $('#Employee_fkey').val();
    const nameAttr = $(element).attr('name') || '';
    const match = nameAttr.match(/^new_value_amt(\d+)$/);
    const salaryHeadId = match ? match[1] : '';

    // Edited by Akshay on 11-12-2025
    let allNewValues = {};

    $("input[name^='new_value_']").each(function() {
      const name = $(this).attr('name'); // example → new_value_6
      const match = name.match(/^new_value_(\d+)$/);

      if (match) {
        const key = match[1]; // example → 6
        const value = parseFloat($(this).val()) || 0;
        allNewValues[key] = value;
      }
    });
    // End

    $.ajax({
      url: livesite + 'SalaryIncrement/onIncrementChange', // Edited by Akshay on 11-12-2025
      url: livesite + 'SalaryIncrement/onIncrementChangeNew',
      type: 'POST',
      dataType: 'json',
      data: {
        salary_head_item_pkey: salaryHeadId,
        current_value: currentVal,
        new_value: newVal,
        gross_amount: grossVal,
        structure_id: structureId,
        emp_pkey: empFkey,
        new_values: allNewValues // Edited by Akshay on 11-12-2025
      },

      success: function(response) {
        var totalNewValue = 0;
        var totalCurrent = 0;
        var totalDiffAmt = 0;

        response.forEach(function(row) {
          const key = row.salary_breakup_temp.salary_head_item_fkey;
          // const newAmount = parseFloat(row.salary_breakup_temp.amount);
          const newAmount = parseFloat(row.salary_breakup_temp.calculated_value); // Edited by Akshay on 11-12-2025
          const formattedAmount = Math.round(newAmount * 100) / 100;
          console.log('formattedAmount', formattedAmount);

          // ===== DIRECT =====
          const $newInput = $(`input[name="new_value_${key}"]`);
          const $currentInput = $(`input[name="current_${key}"]`);
          const $diffAmtInput = $(`input[name="new_value_amt${key}"]`);
          const $diffPctInput = $(`input[name="new_value_pct${key}"]`);

          if ($newInput.length && $currentInput.length) {
            const currentVal = parseFloat($currentInput.val()) || 0;
            const diff = formattedAmount - currentVal;
            const pct = currentVal !== 0 ? ((diff / currentVal) * 100) : 0;

            $newInput.val(formattedAmount);
            $diffAmtInput.val(diff.toFixed(2));
            $diffPctInput.val(pct.toFixed(2));

            totalNewValue += formattedAmount;
            totalCurrent += currentVal;
            totalDiffAmt += diff;
          }

          // ===== INDIRECT =====
          const $indirectNewInput = $(`input[name="indirect_new_value_${key}"]`);
          const $indirectCurrentInput = $(`input[name="indirect_current_${key}"]`);
          const $indirectAmtInput = $(`input[name="indirect_new_value_amt${key}"]`);
          const $indirectPctInput = $(`input[name="indirect_new_value_pct${key}"]`);

          if ($indirectNewInput.length && $indirectCurrentInput.length) {
            const indirectCurrent = parseFloat($indirectCurrentInput.val()) || 0;
            const indirectDiff = formattedAmount - indirectCurrent;
            const indirectPct = indirectCurrent !== 0 ? ((indirectDiff / indirectCurrent) * 100) : 0;

            $indirectNewInput.val(formattedAmount);
            $indirectAmtInput.val(indirectDiff.toFixed(2));
            $indirectPctInput.val(indirectPct.toFixed(2));

            totalNewValue += formattedAmount;
            totalCurrent += indirectCurrent;
            totalDiffAmt += indirectDiff;
          }

          // ===== CONTRIBUTION =====
          const $contribNewInput = $(`input[name="contrib_new_value_${key}"]`);
          const $contribCurrentInput = $(`input[name="contrib_current_${key}"]`);
          const $contribAmtInput = $(`input[name="contrib_new_value_amt${key}"]`);
          const $contribPctInput = $(`input[name="contrib_new_value_pct${key}"]`);

          if ($contribNewInput.length && $contribCurrentInput.length) {
            const contribCurrent = parseFloat($contribCurrentInput.val()) || 0;
            const contribDiff = formattedAmount - contribCurrent;
            const contribPct = contribCurrent !== 0 ? ((contribDiff / contribCurrent) * 100) : 0;

            $contribNewInput.val(formattedAmount);
            $contribAmtInput.val(contribDiff.toFixed(2));
            $contribPctInput.val(contribPct.toFixed(2));

            // ❌ Do NOT add to totals
          }
        });

        // ===== Update CTC & Totals (exclude contributions) =====
        const totalIncrementPct = totalCurrent !== 0 ? ((totalDiffAmt / totalCurrent) * 100).toFixed(2) : '0.00';

        $('#ctc_new_value').text(totalNewValue.toFixed(2));
        $(".emp_monthly_ctc").val(Math.round(totalNewValue));
        $(".emp_anual_ctc").val(Math.round(totalNewValue) * 12);
        $('#yearly_ctc_new_value').text(Math.round(totalNewValue) * 12);
        $('#ctc_increment_amt').text(totalDiffAmt.toFixed(2));
        $('#ctc_increment_pct').text(totalIncrementPct);
      },

      error: function() {
        console.error('Error sending increment data.');
      }
    });
  }




  function handleTypeChange(select) {
    const selectedType = select.value;
    const typeValueSelect = $('#type_value');
    const typeValueLabel = $('#type_value_label');
    const empSelect = $('#emp_fkey2');
    const empLabel = $('#emp_fkey2_label');

    // Reset both dropdowns
    typeValueSelect.empty().append('<option value="">--Select--</option>');
    // empSelect.empty().append('<option value="">--Select--</option>');
    empSelect.empty(); // Edited by Akshay on 27-9-2025

    // Reset and hide rows initially
    $('#type_value_row').hide();
    $('#emp_fkey2_row').hide();
    typeValueLabel.html('&nbsp;');

    if (selectedType === '') return;

    // Update type_value label
    if (selectedType === 'Employee Type') {
      typeValueLabel.text('Employee Type:');
    } else if (selectedType === 'Joining date') {
      typeValueLabel.text('Joining Date:');
    } else {
      typeValueLabel.text(selectedType + ':');
    }

    if (selectedType === 'Employee') {
      console.log('selectedType', selectedType);

      // Directly load all employees
      $('#emp_fkey2_row').show();

      $.ajax({
        url: livesite + 'SalaryIncrement/getTypeValues',
        type: 'POST',
        data: {
          type: selectedType
        },
        dataType: 'json',
        success: function(response) {
          if (response.success && response.data && Object.keys(response.data).length > 0) {
            // empSelect.append('<option value="All">All</option>');

            // Convert to array and sort by value (text)
            const sortedData = Object.entries(response.data).sort((a, b) => {
              return a[1].localeCompare(b[1]); // Compare the text values
            });

            // Append sorted options
            $.each(sortedData, function(index, [key, value]) {
              empSelect.append($('<option>', {
                value: key,
                text: value
              }));
            });
          }

          // empSelect.select2();
          // Edited by Akshay on 27-9-2025
          empSelect.select2({
            placeholder: "--Select--",
            closeOnSelect: false,
            allowClear: true,
            // templateSelection: function(data, container) {
            //   // return nothing so the input stays empty
            //    return '';
            // }
          });
          // End
        },
        error: function() {
          console.error('Error fetching employee list');
        }
      });

    } else {
      // Show and populate type_value dropdown
      $('#type_value_row').show();

      $.ajax({
        url: livesite + 'SalaryIncrement/getTypeValues',
        type: 'POST',
        data: {
          type: selectedType
        },
        dataType: 'json',
        success: function(response) {
          typeValueSelect.empty();
          if (response.success && response.data) {
            typeValueSelect.append('<option value="">--Select--</option>');
            $.each(response.data, function(key, value) {
              typeValueSelect.append($('<option>', {
                value: key,
                text: value
              }));
            });
          }
          typeValueSelect.select2();
        },
        error: function() {
          console.error('Error fetching type values');
        }
      });
    }
  }


  //Edited by Akshay on 25-6-2025
  $(document).on('change', '#type_value', function() {
    const selectedType = $('#type').val();
    const selectedValue = $(this).val();
    const empSelect = $('#emp_fkey2');
    const empLabel = $('#emp_fkey2_label');

    // empSelect.empty().append('<option value="">--Select--</option>');
    empSelect.empty(); // Edited by Akshay on 27-9-2025

    if (!selectedValue || selectedValue === 'All') {
      $('#emp_fkey2_row').hide();
      return;
    }

    $.ajax({
      url: livesite + 'SalaryIncrement/getEmployeesByTypeValue',
      type: 'POST',
      data: {
        type: selectedType,
        value: selectedValue
      },
      dataType: 'json',
      success: function(response) {
        if (response.success && response.data.length > 0) {
          // empSelect.empty().append('<option value="All">All</option>');
          empSelect.empty(); // clears all previous options
          $.each(response.data, function(i, emp) {
            empSelect.append($('<option>', {
              value: emp.emp_pkey,
              text: emp.name
            }));
          });
          $('#emp_fkey2_row').show();
          // empSelect.select2();
          // Edited by Akshay on 27-9-2025
          empSelect.select2({
            placeholder: "--Select--",
            closeOnSelect: false,
            allowClear: true,
            // templateSelection: function(data, container) {
            //   // return nothing so the input stays empty
            //    return '';
            // }
          });
          // End
        } else {
          $('#emp_fkey2_row').hide();
        }
      },
      error: function() {
        console.error('Error fetching employees by type value');
      }
    });
  });


  // End


  $(document).ready(function() {
    $(document).on('change', '.increment-amt, .increment-pct, input[id^="new_value_"], .indirect-increment-amt, .indirect-increment-pct, input[id^="indirect_new_value_"]', calculateGrossTotals);

    $('#increment_start_date_effective, #increment_next_increment_date').datepicker({
      format: 'dd-mm-yyyy', // Set the format to YYYY-MM-DD
      changeMonth: true,
      changeYear: true,
      showButtonPanel: true
    });

    $('#form_pay_out_month').datepicker({
      format: 'MM-yyyy', // Month name - Year
      startView: "months",
      minViewMode: "months",
      autoclose: true
    });

    $('#payOutMonth').datepicker({
      format: 'MM-yyyy', // Month name - Year
      startView: "months",
      minViewMode: "months",
      autoclose: true
    });

    // Define livesite variable
    var livesite = "<?php echo $this->webroot; ?>";

    // Initialize Select2
    $('#Employee_fkey').select2();
    $('#salary_structure').select2({
      minimumResultsForSearch: 0
    });
    // On employee select
    $('#enterEmployeeBtn').on('click', function() { // Edited by Akshay on 11-10-2025
      // Reset select
      $('#select-update').val('');
      $('#select-item').val('');


      var empPkey = $('#Employee_fkey').val(); // Edited by Akshay on 11-10-2025

      if (empPkey !== "") {
        fetchSalaryStructure(empPkey);
        console.log('#Employee_fkey', $('#Employee_fkey').prop('disabled'));
        $('#Employee_fkey').select2('destroy').select2({
          dropdownParent: $('#incrementeuploadtable')
        });
      } else {
        $('#salary_structure_table').slideUp();
        $('#no-data').slideUp(); // hide it
        $('#salary_structure_container').slideUp(); // hide it
        $('#footer-input').slideUp(); // hide it
        $('#btn-submit').slideUp(); // hide it

        $('#designation').val('');
        $('#branch').val('');
        $('#department').val('');
        $('#joining_date_input').val('');
        $('#joining_date_text').text('');
        $('#prev_sal').val('');
        $('#prev_sal_text').text('');
        $('#designation_text').html('<small class="foggy-text">Designation</small>');
        $('#branch_text').html('<small class="foggy-text">Branch</small>');
        $('#department_text').html('<small class="foggy-text">Department</small>');
      }
    });

    // Change structure
    $('#apply_structure').click(function(e) {
      e.preventDefault();

      var structureId = $('#salary_structure').val();
      var empPkey = $('#Employee_fkey').val();
      if (!structureId) {
        alert("Please select a salary structure.");
        return;
      }


      fetchTempSalaryStructure(empPkey);
    });

    // With effect from
    let debounceTimer;

    $('#increment_start_date_effective').on('change', function() {
      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(function() {
        var startDate = $('#increment_start_date_effective').val();
        var empPkey = $('#Employee_fkey').val();

        console.log('Call'); // Now it will show only once

        if (empPkey && startDate) {
          $.ajax({
            url: livesite + 'SalaryIncrement/onEffectiveDateChange',
            type: 'POST',
            data: {
              empPkey: empPkey,
              start_date_effective: startDate
            },
            dataType: 'json',
            success: function(response) {
              if (response.status === 'success') {
                $('input[name="arrear_salary"]').val(response.is_processed ? 'Y' : 'N');
                if (response.is_processed) alert(response.message);
              } else {
                console.warn('Error:', response.message || 'Something went wrong.');
              }
            },
            error: function() {
              console.error('AJAX request failed.');
            }
          });
        }
      }, 300); // Adjust delay if needed
    });



    var currentStructure = [];

    // Render salary structure
    function renderSalaryStructure(structure, indirectStructure, empContribution) {
      let rowNumber = 1; // Edited by Akshay on 18-11-2025

      currentStructure = structure; // Save it for future logic

      const $container = $('#salary_structure_container');
      const $tbody = $('#salary-structure tbody');

      $tbody.empty();

      var totalIndex = 0;
      var totalCurrent = 0;
      var totalNewVal = 0;
      var totalIncrementAmt = 0;
      var grossTotal = 0;

      structure.forEach((item, index) => {
        const current = parseFloat(item.value).toFixed(2);
        const newVal = parseFloat(item.new_value || item.value).toFixed(2);
        const incrementAmt = (newVal - current).toFixed(2);
        const incrementPct = current > 0 ? (((newVal - current) / current) * 100).toFixed(2) : '';

        totalCurrent += parseFloat(current);
        totalNewVal += parseFloat(newVal);
        totalIncrementAmt += parseFloat(incrementAmt);

        const row = `
                      <tr>
                        <td class="row-no">${rowNumber}</td>
                        <td>${item.desc}<input type="hidden" name="desc_${item.key}" value="${item.desc}"></td>

                        <td id="current_${index}">${current}
                          <input type="hidden" name="current_${item.key}" value="${current}">
                        </td>

                        <td class="gap-col"></td> <!-- Blank column -->

                        <td>
                          <input type="number" id="new_value_${index}" 
                                name="new_value_${item.key}" 
                                class="form-control increment-new-amt" 
                                value="${newVal}" data-index="${index}" readonly>
                        </td>

                        <td>
                          <input type="number" name="new_value_amt${item.key}" 
                                class="form-control increment-amt narrow-input" 
                                data-index="${index}" value="${incrementAmt}" readonly>
                        </td>

                        <td>
                          <input type="number" name="new_value_pct${item.key}" 
                                class="form-control increment-pct narrow-input" 
                                data-index="${index}" value="${incrementPct}" readonly>
                        </td>
                      </tr>
                    `;
        rowNumber++; // Edited by Akshay on 18-11-2025
        totalIndex = index + 1;
        $tbody.append(row);
      });

      // Append Gross Salary row
      const grossIncrementPct = totalCurrent > 0 ?
        (((totalNewVal - totalCurrent) / totalCurrent) * 100).toFixed(2) :
        '';

      grossTotal = totalCurrent;

      const grossRow = `
                  <tr style="font-weight: bold;">
                    <td class="row-no">${rowNumber}</td>
                    <td>Gross Salary</td>
                    <td id="gross-current">${totalCurrent.toFixed(2)}</td>

                    <td class="gap-col"></td> <!-- Blank column -->

                    <td><input id="gross-new-value" name ="gross_new_value" type="number" class="form-control" value="${totalNewVal.toFixed(2)}" readonly></td>
                    <td><input id="gross-increment-amt" type="number" class="form-control narrow-input" value="${totalIncrementAmt.toFixed(2)}" readonly></td>
                    <td><input id="gross-increment-pct" type="number" class="form-control narrow-input" value="${((totalIncrementAmt / totalCurrent) * 100).toFixed(2)}" readonly></td>
                  </tr>
                `;


      $tbody.append(grossRow);

      rowNumber++; // Edited by Akshay on 18-11-2025

      // Indirect
      totalIndex = totalIndex + 1;
      indirectStructure.forEach((item, index) => {
        const indirectCurrent = parseFloat(item.value).toFixed(2);
        const indirectNewVal = parseFloat(item.new_value || item.value).toFixed(2);
        const indirectIncrementAmt = (indirectNewVal - indirectCurrent).toFixed(2);
        const indirectIncrementPct = indirectCurrent > 0 ? (((indirectNewVal - indirectCurrent) / indirectCurrent) * 100).toFixed(2) : '';

        totalCurrent += parseFloat(indirectCurrent);
        totalNewVal = parseFloat(totalNewVal) + parseFloat(indirectNewVal);
        totalIncrementAmt = parseFloat(totalIncrementAmt) + parseFloat(indirectIncrementAmt);

        const indirectRow = `
      <tr>
        <td class="row-no">${rowNumber}</td>
        <td>${item.desc}<input type="hidden" name="desc_${item.key}" value="${item.desc}"></td>
        <td id="indirect_current_${index}">${indirectCurrent}<input type="hidden" name="indirect_current_${item.key}" value="${indirectCurrent}"></td>
        <td class="gap-col"></td> <!-- Blank column -->
        <td><input type="number" id="indirect_new_value_${index}" name="indirect_new_value_${item.key}" class="form-control" value="${indirectNewVal}" data-index="${index}" min="0" readonly></td>
        <td><input type="number" name="indirect_new_value_amt${item.key}" class="form-control indirect-increment-amt" data-index="${index}" value="${indirectIncrementAmt}" min="0" readonly></td>
        <td><input type="number" name="indirect_new_value_pct${item.key}" class="form-control indirect-increment-pct" data-index="${index}" value="${indirectIncrementPct}" min="0" readonly></td>
      </tr>
    `;
        $tbody.append(indirectRow);
        rowNumber++; // Edited by Akshay on 18-11-2025
      });

      // CTC row
      const ctcRow = `
                        <tr style="font-weight: bold;">
                          <td class="row-no">${rowNumber}</td>
                          <td>CTC</td>
                          <td>${totalCurrent.toFixed(2)}
                            <input type="hidden" name="emp_cur_monthly_ctc" value="${totalCurrent.toFixed(2)}">
                          </td>
                          <td class="gap-col" ></td> 
                          <td id="ctc_new_value">${totalNewVal.toFixed(2)}
                            <input type="hidden" name="emp_monthly_ctc" class="emp_monthly_ctc" value="">
                          </td>
                          <td id="ctc_increment_amt">${totalIncrementAmt.toFixed(2)}</td>
                          <td id="ctc_increment_pct">${((totalIncrementAmt / totalCurrent) * 100).toFixed(2)}</td>
                        </tr>
                      `;
      rowNumber++; // increment AFTER row is added

      const yearlyGrossRow = `
                              <tr style="font-weight: bold;">
                                <td class="row-no">${rowNumber}</td>
                                <td>Yearly Gross Salary</td>
                                <td>${(grossTotal * 12).toFixed(2)}</td>
                                <td class="gap-col"></td>
                                <td id="yearly-gross-new-value">
                                  <input type="hidden" name="emp_anual_ctc" class="emp_anual_ctc" value="">
                                </td>
                                <td></td>
                                <td></td>
                              </tr>
                            `;
      rowNumber++;

      const yearlyCtcRow = `
                            <tr style="font-weight: bold;">
                              <td class="row-no">${rowNumber}</td>
                              <td>Yearly CTC</td>
                              <td>${(totalCurrent * 12).toFixed(2)}</td>
                              <td class="gap-col"></td>
                              <td id="yearly_ctc_new_value"></td>
                              <td></td>
                              <td></td>
                            </tr>
                          `;
      rowNumber++;

      $tbody.append(ctcRow + yearlyGrossRow + yearlyCtcRow);

      // Employee cobtribution
      // Append hidden inputs for empContribution directly to tbody
      if (Array.isArray(empContribution) && empContribution.length > 0) {
        empContribution.forEach((item, index) => {
          const contributionCurrent = parseFloat(item.value || 0).toFixed(2);
          const contributionNewVal = parseFloat(item.new_value || item.value || 0).toFixed(2);
          const contributionIncrementAmt = (contributionNewVal - contributionCurrent).toFixed(2);
          const contributionIncrementPct = contributionCurrent > 0 ?
            (((contributionNewVal - contributionCurrent) / contributionCurrent) * 100).toFixed(2) :
            '';

          const rowIndex = totalIndex + index + 3; // Adjust index as needed

          const contribRow = `
                                <tr>
                                  <td class="row-no">${rowNumber}</td>
                                  <td>${item.desc}
                                    <input type="hidden" name="desc_${item.key}" value="${item.desc}">
                                  </td>
                                  <td id="contrib_current_${index}">${contributionCurrent}
                                    <input type="hidden" name="contrib_current_${item.key}" value="${contributionCurrent}">
                                  </td>
                                  <td class="gap-col"></td> <!-- Blank column -->
                                  <td>
                                    <input type="number" id="contrib_new_value_${index}" name="contrib_new_value_${item.key}" class="form-control" value="${contributionNewVal}" data-index="${index}" min="0" readonly>
                                  </td>
                                  <td>
                                    <input type="number" name="contrib_new_value_amt${item.key}" class="form-control contrib-increment-amt" data-index="${index}" value="${contributionIncrementAmt}" min="0" readonly>
                                  </td>
                                  <td>
                                    <input type="number" name="contrib_new_value_pct${item.key}" class="form-control contrib-increment-pct" data-index="${index}" value="${contributionIncrementPct}" min="0" readonly>
                                  </td>
                                </tr>
                              `;

          $tbody.append(contribRow);
          rowNumber++; // Edited by Akshay on 18-11-2025
        });
      }

      // Ended

      $container.show(); // Now display the full table
    }


    $('#new_vda_pc').on('input', function() {
      updateVDAFields();
    });

    function updateVDAFields() {
      const vdaPercent = parseFloat($('#new_vda_pc').val());

      const vdaItem = currentStructure.find(item => item.desc.toLowerCase().includes('vda'));
      const grossItem = currentStructure.find(item => item.desc.toLowerCase().includes('gross'));

      if (!isNaN(vdaPercent) && vdaItem && grossItem) {
        const originalVDA = parseFloat(vdaItem.value);
        const newVDA = (originalVDA * vdaPercent / 100).toFixed(2);
        const newGross = (parseFloat(grossItem.value) + (newVDA - originalVDA)).toFixed(2);

        const html = `
      <div class="form-group salary_revision_autofill">
        <div class="col-md-12">
          <label class="col-md-4 control-label">New VDA (₹)<span class="star">*</span></label>
          <div class="col-md-1">:</div>
          <div class="col-md-7">
            <label class="col-md-4 control-label">${newVDA}</label>
          </div>
        </div>
      </div>
      <div class="form-group salary_revision_autofill">
        <div class="col-md-12">
          <label class="col-md-4 control-label">New Gross Salary (₹)<span class="star">*</span></label>
          <div class="col-md-1">:</div>
          <div class="col-md-7">
            <label class="col-md-4 control-label">${newGross}</label>
          </div>
        </div>
      </div>
    `;

        // Remove existing VDA-related fields and append new ones
        $('.salary_revision_autofill').remove();
        $('#salary_structure_container').append(html);
      } else {
        $('.salary_revision_autofill').remove(); // Clean if inputs are invalid
      }
    }

    // On change of employee
    function fetchSalaryStructure(empPkey) {
      $.ajax({
        url: livesite + 'SalaryIncrement/getSalaryStructure/' + empPkey,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
          $('#select-item option[value="percentage"]').prop('disabled', response.ctc == ''); // Edited by Akshay on 14-11-2025
          if (response.success && ((Array.isArray(response.structure) && response.structure.length > 0) || (Array.isArray(response.structure_indirect) && response.structure_indirect.length > 0))) {
            renderSalaryStructure(response.structure, response.structure_indirect, response.emp_contribution);

            $('#salary_structure').val(response.structure_id).trigger('change');
            $('#salary_structure_table').slideDown();

            $('#no-data').slideUp();
            $('#footer-input').slideDown();
            $('#btn-submit').slideDown();
            $('#salary_structure_container').show();
            $('#total_direct_value').val(response.monthly_gross);
            $('#total_direct_value_permanent').val(response.monthly_gross);
          } else {
            $('#no-data').slideDown();
            $('#salary_structure_container').hide();
            $('#footer-input').slideUp();
            $('#btn-submit').slideUp();
            // $('#salary_structure_table').slideUp();
            $('#salary_structure_table').slideDown(); // Edited by Akshay on 7-10-2025
          }

          if (response.success && typeof response.emp === 'object' && response.emp !== null) {
            var designation = response.emp.designation;
            var branch = response.emp.branch;
            var department = response.emp.department;
            var joiningDate = response.emp.joining_date;
            var formattedDate = '';

            if (joiningDate) {
              var parts = joiningDate.split('-');
              formattedDate = parts[2] + '-' + parts[1] + '-' + parts[0];
            }

            var totalCurrentCTC = parseFloat($('input[name="emp_cur_monthly_ctc"]').val());
            var prevSal = parseFloat(response.ctc);
            if (!isNaN(prevSal) && prevSal !== 0) {
              var hikePerc = ((totalCurrentCTC - prevSal) / prevSal) * 100;
              var formattedHike = hikePerc.toFixed(2) + '%';
              $('#prev_hike_text').text(formattedHike);
              $('#prev_hike').val(hikePerc.toFixed(2));

              // Edited by Akshay on 8-10-2025
              $('#increment_start_date_effective')
                .prop('readonly', false) // remove readonly
                .css('pointer-events', 'auto'); // allow typing/click

              // 2️⃣ Enable 'form_pay_out_month' and make it required again
              $('#form_pay_out_month').prop({
                  disabled: false, // enable input
                  required: true // make mandatory
                })
                .css('background-color', ''); // reset background color
              // End
            } else {
              $('#prev_hike_text').text('');
              $('#prev_hike').val('');
              prevSal = '';
              // Edited by Akshay on 8-10-2025
              $('#increment_start_date_effective')
                // .val(new Date().toISOString().slice(0, 10)) // set today
                .val(joiningDate)
                .attr('readonly', true) // make readonly
                .css('pointer-events', 'none'); // prevent typing/click

              $('#form_pay_out_month')
                .prop('disabled', true) // disable input
                .prop('required', false) // remove required validation
                .val('') // clear value if needed
                .css('background-color', '#e9ecef'); // optional: show visually disabled

              // End
            }

            $('#designation').val(designation);
            $('#branch').val(branch);
            $('#department').val(department);
            $('#designation_text').text(designation);
            $('#branch_text').text(branch);
            $('#department_text').text(department);
            $('#joining_date_input').val(formattedDate);
            $('#joining_date_text').text(formattedDate);

            if (formattedDate) {
              var parts = formattedDate.split('-');
              var joiningDate = new Date(parts[2], parts[1] - 1, parts[0]);
              var today = new Date();
              var diffInTime = today - joiningDate;
              var tenureDays = Math.floor(diffInTime / (1000 * 60 * 60 * 24));
              $('#tenure_text').text(tenureDays + ' days');
              $('#tenure').val(tenureDays);
            }

            $('#prev_sal').val(prevSal);
            $('#prev_sal_text').text(Math.round(prevSal));
          }
        },
        error: function() {
          $('#salary_structure_table').slideUp();
          $('#salary_structure_container').html('<div class="text-danger">Error in request.</div>');
        }
      });
    }

    // On change of structure
    function fetchTempSalaryStructure(empPkey) {
      $.ajax({
        url: livesite + 'SalaryIncrement/getGrossByEmp/' + empPkey,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
          if (response.success) {
            // ✅ Now call your original AJAX with fetched gross
            const structureId = $('#salary_structure').val();

            // Edited by Akshay on 17-12-2025
            const currentStructureId = response.structure_id;
            if (structureId == currentStructureId) {
              $.notify({
                message: 'No change in salary structure. Please select a different structure to apply.'
              }, {
                type: 'danger',
                z_index: 99999,
              });
              return;
            }
            // End

            const gross = response.gross;
            $('#gross-new-value').val(gross); // Optional update to field

            $.ajax({
              url: livesite + 'SalaryIncrement/getTempSalaryStructure/' + empPkey + '/' + structureId + '/' + gross,
              type: 'GET',
              dataType: 'json',
              success: function(response) {
                if (response.success && ((Array.isArray(response.structure) && response.structure.length > 0) || (Array.isArray(response.structure_indirect) && response.structure_indirect.length > 0) || (Array.isArray(response.emp_contribution) && response.emp_contribution.length > 0))) {
                  renderSalaryStructure(response.structure, response.structure_indirect, response.emp_contribution);
                  // Edited by Akshay on 7-10-2025
                  // if (gross == 0) {
                  //   // Columns to hide (0-based index)
                  //   var colsToHide = [0, 3, 4, 5];

                  //   $('#salary-structure').each(function() {
                  //     // Hide header cells
                  //     $(this).find('thead tr').each(function() {
                  //       colsToHide.forEach(function(i) {
                  //         $(this).find('th').eq(i).hide();
                  //       }, this);
                  //     });

                  //     // Hide corresponding td cells
                  //     $(this).find('tbody tr').each(function() {
                  //       colsToHide.forEach(function(i) {
                  //         $(this).find('td').eq(i).hide();
                  //       }, this);
                  //     });
                  //   });

                  // }
                  $('#total_direct_value').val(response.temp_monthly_gross);
                  $('#total_direct_value_permanent').val(response.temp_monthly_gross);
                  // End

                  $('#salary_structure').val(response.structure_id).trigger('change');
                  $('#salary_structure_table').slideDown();

                  $('#no-data').slideUp();
                  $('#footer-input').slideDown();
                  $('#btn-submit').slideDown();
                  $('#salary_structure_container').show();
                } else {
                  $('#no-data').slideDown();
                  $('#salary_structure_container').hide();
                  $('#footer-input').slideUp();
                  $('#btn-submit').slideUp();
                  $('#salary_structure_table').slideUp();
                }

                if (response.success && typeof response.emp === 'object' && response.emp !== null) {
                  // ... existing code to populate employee fields ...
                }
              },
              error: function() {
                $('#salary_structure_table').slideUp();
                $('#salary_structure_container').html('<div class="text-danger">Error in request.</div>');
              }
            });
          } else {
            console.error('Gross fetch failed');
          }
        },
        error: function() {
          console.error('Error fetching gross');
        }
      });

      $('#select-update, #select-item').val('').trigger('change'); // Edited by Akshay on 9-10-2025
    }

    // Edited by Akshay on 18-11-2025
    let updatingIndex = {}; // Object so multiple rows work independently

    // 1) USER CHANGES NEW AMOUNT
    $(document).on('input', '.increment-new-amt', function() {
      const index = $(this).data('index');

      if (updatingIndex[index]) return;
      updatingIndex[index] = "new_amt";

      const current = parseFloat($("#current_" + index).text()) || 0;
      const newValue = parseFloat($(this).val()) || 0;

      const incrementAmt = newValue - current;
      const incrementPct = current ? ((incrementAmt / current) * 100) : 0;

      // Update amount & percentage WITHOUT triggering handlers
      $(`.increment-amt[data-index="${index}"]`).val(incrementAmt.toFixed(2));
      $(`.increment-pct[data-index="${index}"]`).val(incrementPct.toFixed(2));

      sendIncrementData(index, this); // AJAX from NEW amount

      updatingIndex[index] = "";
    });

    // 2) USER CHANGES INCREMENT AMOUNT
    $(document).on('input', '.increment-amt', function() {
      const index = $(this).data('index');

      if (updatingIndex[index]) return;
      updatingIndex[index] = "increment_amt";

      const incrementVal = parseFloat($(this).val()) || 0;
      const currentVal = parseFloat($(`#current_${index}`).text()) || 0;

      const newVal = currentVal + incrementVal;
      const incrementPct = currentVal ? ((incrementVal / currentVal) * 100) : 0;

      // Update dependent fields
      $(`#new_value_${index}`).val(newVal.toFixed(2));
      $(`.increment-pct[data-index="${index}"]`).val(incrementPct.toFixed(2));

      sendIncrementData(index, this); // AJAX from amount

      updatingIndex[index] = "";
    });

    // 3) USER CHANGES INCREMENT PERCENTAGE
    $(document).on('input', '.increment-pct', function() {
      const index = $(this).data('index');

      if (updatingIndex[index]) return;
      updatingIndex[index] = "increment_pct";

      const incrementPerc = parseFloat($(this).val()) || 0;
      const currentVal = parseFloat($(`#current_${index}`).text()) || 0;

      const incrementAmt = currentVal * (incrementPerc / 100);
      const newVal = currentVal + incrementAmt;

      // Update dependent fields
      $(`#new_value_${index}`).val(newVal.toFixed(2));
      $(`.increment-amt[data-index="${index}"]`).val(incrementAmt.toFixed(2));

      sendIncrementData(index, this); // AJAX from percentage

      updatingIndex[index] = "";
    });

    // End

    // On change of amount
    $(document).on('change', '.indirect-increment-amt', function() {
      const index = $(this).data('index');
      const incrementVal = parseFloat($(this).val()) || 0;
      const currentVal = parseFloat($(`#indirect_current_${index}`).text()) || 0;
      const newVal = (currentVal + incrementVal).toFixed(2);
      const incrementPct = currentVal > 0 ? ((incrementVal / currentVal) * 100).toFixed(2) : '0.00';
      $(`#indirect_new_value_${index}`).val(newVal);
      $(`input.indirect-increment-pct[data-index="${index}"]`).val(incrementPct);
    });

    // On change of percentage
    $(document).on('change', '.indirect-increment-pct', function() {
      const index = $(this).data('index');
      const incrementPerc = parseFloat($(this).val()) || 0;
      const currentVal = parseFloat($(`#indirect_current_${index}`).text()) || 0;
      const incrementAmt = (currentVal * (incrementPerc / 100)).toFixed(2);
      const newVal = (currentVal + (currentVal * (incrementPerc / 100))).toFixed(2);
      $(`#indirect_new_value_${index}`).val(newVal);
      $(`input.indirect-increment-amt[data-index="${index}"]`).val(incrementAmt);
    });

    // Edited by Akshay on 18-11-2025
    let updatingField = ""; // Tracks which field started the update

    // 1) USER CHANGES NEW VALUE
    $(document).on('input', '#gross-new-value', function() {

      if (updatingField !== "") return; // Prevent recursive calls
      updatingField = "new_value";

      const totalCurrent = parseFloat($("#gross-current").text()) || 0;
      const newVal = parseFloat($(this).val()) || 0;

      const incrementAmt = newVal - totalCurrent;
      const incrementPct = totalCurrent ? ((incrementAmt / totalCurrent) * 100) : 0;

      // Update dependent fields WITHOUT triggering handlers
      $('#gross-increment-amt').val(incrementAmt.toFixed(2));
      $('#gross-increment-pct').val(incrementPct.toFixed(2));

      updateGrossSummary(newVal, incrementAmt); // monthly + yearly + recalc

      updatingField = "";
    });

    // 2) USER CHANGES INCREMENT AMOUNT
    $(document).on('input', '#gross-increment-amt', function() {

      if (updatingField !== "") return;
      updatingField = "increment_amt";

      const incrementVal = parseFloat($(this).val()) || 0;
      const currentVal = parseFloat($('#gross-current').text()) || 0;

      const newVal = currentVal + incrementVal;
      const incrementPct = currentVal ? ((incrementVal / currentVal) * 100) : 0;

      $('#gross-new-value').val(newVal.toFixed(2));
      $('#gross-increment-pct').val(incrementPct.toFixed(2));

      updateGrossSummary(newVal, incrementVal);

      updatingField = "";
    });

    // 3) USER CHANGES INCREMENT PERCENTAGE
    $(document).on('input', '#gross-increment-pct', function() {

      if (updatingField !== "") return;
      updatingField = "increment_pct";

      const incrementPct = parseFloat($(this).val()) || 0;
      const currentVal = parseFloat($('#gross-current').text()) || 0;

      const incrementAmt = (currentVal * (incrementPct / 100));
      const newVal = currentVal + incrementAmt;

      $('#gross-new-value').val(newVal.toFixed(2));
      $('#gross-increment-amt').val(incrementAmt.toFixed(2));

      updateGrossSummary(newVal, incrementAmt);

      updatingField = "";
    });


    // REUSABLE FUNCTION – Updates monthly, yearly & recalc
    function updateGrossSummary(newVal, incrementAmt) {

      const currentMonthlyGross = parseFloat($('#total_direct_value_permanent').val()) || 0;
      const newMonthlyGross = (currentMonthlyGross + incrementAmt);

      $('#total_direct_value').val(newMonthlyGross.toFixed(2));
      $('#yearly-gross-new-value').text(Math.round(newVal * 12));

      recalcSalaryStructure(); // update individual items
    }

    // End


    // On submit
    $('#incrementeuploadtable').on('submit', function(e) {

      // Edited by Akshay on 24-3-2026
      var form = this;
      if (!form.checkValidity()) {
        e.preventDefault();
        form.reportValidity(); // shows popup (including Remarks)
        return;
      }
      // End

      e.preventDefault();

      // const monthlyGross = parseFloat($('#gross-new-value').val()) || 0;
      const monthlyGross = parseFloat($('#total_direct_value').val()) || 0; // Edited by Akshay on 9-10-2025
      const anualGross = parseFloat($('#yearly-gross-new-value').text()) || 0;

      var $form = $(this); // <- store reference
      var form = $form[0];
      var formData = new FormData(form);
      var prevSal = $('#prev_sal_text').text(); // Edited by Akshay on 8-10-2025

      formData.set('emp_monthly_ctc', monthlyGross.toFixed(2));
      formData.set('emp_anual_ctc', anualGross);

      // Edited by Akshay on 8-10-2025
      // if (prevSal == 0) {
      if (false) {
        // prevSale is 0, just notify success and set 'is_item' to 'N'
        formData.set('is_item', 'N');

        // $.notify(
        //   "Form will be saved as Gross", {
        //     type: "success",
        //     z_index: 9999,
        //     allow_dismiss: true,
        //     delay: 5000,
        //     mouse_over: 'pause',
        //   }
        // );

        $.ajax({
          url: $form.attr('action'), // <- use stored $form
          type: 'POST',
          data: formData,
          contentType: false,
          processData: false,
          success: function(response) {
            alert('Form submitted successfully!');
            $('#att_table').datagrid('reload');
            $('#largeModalForm').modal('hide');
          },
          error: function(xhr, status, error) {
            alert('Something went wrong: ' + error);
            console.log(xhr.responseText);
          }
        });
      } else {
        Swal.fire({
          title: 'Save as ?',
          icon: 'question',
          showCancelButton: true,
          confirmButtonText: 'Item',
          cancelButtonText: 'Gross',
          allowEscapeKey: false,
          allowOutsideClick: false,
          customClass: {
            confirmButton: 'swal2-confirm-btn-lg',
            cancelButton: 'swal2-cancel-btn-lg'
          }
        }).then((result) => {
          formData.set('is_item', result.isConfirmed ? 'Y' : 'N');

          $.notify(
            "The changes in item values entered may affect other items", {
              type: "danger",
              z_index: 9999,
              allow_dismiss: true,
              delay: 10000,
              mouse_over: 'pause',
            }
          );

          $.ajax({
            url: $form.attr('action'), // <- use stored $form
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
              alert('Form submitted successfully!');
              $('#att_table').datagrid('reload');
              $('#largeModalForm').modal('hide');
            },
            error: function(xhr, status, error) {
              alert('Something went wrong: ' + error);
              console.log(xhr.responseText);
            }
          });
        });
      }
      // End
    });

    // Second form submit
    $(document).ready(function() {
      $('#itemIncrementForm').on('submit', function(e) {
        e.preventDefault(); // Prevent default form submission

        $.notify(
          "The changes in item values entered may affect other items", {
            type: "danger",
            z_index: 9999,
            allow_dismiss: true,
            delay: 10000,
            mouse_over: 'pause',
          }
        );

        var form = $(this);
        var url = form.attr('action');
        var formData = form.serialize();

        // Optional: disable the submit button to prevent multiple clicks
        var submitBtn = form.find('button[type="submit"]');
        submitBtn.prop('disabled', true).text('Saving...');

        $.ajax({
          type: 'POST',
          url: url,
          data: formData,
          dataType: 'json',
          success: function(response) {
            // Edited by Akshay on 27-9-2025
            // Show danger notification if rejected_emps is not empty
            // if (res.rejected_emps && res.rejected_emps.trim() !== '') {
            //   $.notify({
            //     message: "Rejected employees: " + res.rejected_emps
            //   }, {
            //     type: 'danger',
            //     z_index: 3000
            //   });
            // }
            // End

            if (response.status === 'success') {
              alert('Saved successfully!');
              // Optional: reset the form or close modal
              form[0].reset();
              $('.js-example-basic-single').val('').trigger('change');
              $('#att_table').datagrid('reload');
              $('#largeModalForm').modal('hide'); // close the modal
            } else {
              alert(response.message || 'Something went wrong. Please try again.');
            }
          },
          error: function(xhr) {
            console.error(xhr.responseText);
            alert('Server error. Please try again.');
          },
          complete: function() {
            // Re-enable the button
            submitBtn.prop('disabled', false).text('Save');
          }
        });
      });
    });


  });
</script>