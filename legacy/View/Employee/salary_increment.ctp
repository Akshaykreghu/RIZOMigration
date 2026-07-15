<style>
  .salary_revision_autofill {
    background-color: #80808047;
  }

  .foggy-text {
    color: #999;
    /* Light gray */
    opacity: 0.6;
    /* Semi-transparent */
    filter: blur(0.3px);
    /* Slight blur for foggy effect */
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


  /* body {
      font-family: Arial, sans-serif;
    } */
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
    /* optional if you want top alignment too */
  }
</style>
<!-- CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<!-- JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- Modal Content -->
<div class="modal-dialog modal-lg">
  <!-- <div class="modal-content ">
    <div class="modal-body"> -->
  <div class="modal-header" style="background: #00659f; color: white">
    <h4 class="modal-title">Salary Increment Form</h4>
  </div>
  <form class="form-horizontal" id="incrementeuploadtable" action="<?php echo $this->webroot; ?>Employee/saveIncrement" method="POST">
    <br>
    <table class="increment-table">
      <tr>
        <td class="header">Employee Name</td>
        <td colspan="2">
          <select id="Employee_fkey" class="form-control" name="emp_fkey" required style="width:100%;">
            <option value="">Select</option>
            <?php
            foreach ($arr_employees as $value) {
              echo '<option value="' . $value['emp_details']['emp_pkey'] . '">' . $value['emp_details']['first_name'] . ' ' . $value['emp_details']['last_name'] . ' - ' . $value['emp_proff']['emp_company_id'] . '</option>';
            }
            ?>
          </select>
        </td>
        <td class="header">
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
            <option value="amount">Amount</option>
            <option value="percentage">Percentage</option>
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
            <th>No</th>
            <th>Salary Components</th>
            <th>Current</th>
            <th>New</th>
            <th>Increment Amt</th>
            <th>Increment %</th>
          </tr>
        </thead>
        <tbody>
          <!-- Filled dynamically -->
        </tbody>
      </table>
    </div>


    <br>

    <div id="footer-input" class="footer" style="text-align: left; padding-left:30%; display:none;">
      <input type="hidden" name="arrear_salary" value="N">
      <br><br>
      <div style="max-width: 400px;">
        <div style="margin-bottom: 15px;">
          <label for="increment_start_date_effective" style="display: inline-block; width: 160px;">
            With Effect From: <span style="color: red;">*</span>
          </label>
          <input
            type="text"
            autocomplete="off"
            id="increment_start_date_effective"
            name="start_date_effective"
            required
            placeholder="Select Date">
        </div>

        <!-- Next Increment Date Picker -->
        <div style="margin-bottom: 15px;">
          <label for="increment_next_increment_date" style="display: inline-block; width: 160px;">
            Next Increment Date: <span style="color: red;">*</span>
          </label>
          <input
            type="text"
            autocomplete="off"
            id="increment_next_increment_date"
            name="next_increment_date"
            required
            placeholder="Select Date">
        </div>

        <div>
          <label for="form_pay_out_month" style="display: inline-block; width: 160px;">
            Payout month: <span style="color: red;">*</span>
          </label>
          <input
            type="text"
            autocomplete="off"
            id="form_pay_out_month"
            name="pay_out_month"
            required
            placeholder="Select Month"">
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

<script>
  function changeItem() {
    const update = $('#select-update').val();
    const item = $('#select-item').val();

    // Always reset all fields to readonly first
    $('#gross-increment-amt, #gross-increment-pct, .increment-amt, .increment-pct, .indirect-increment-amt, .indirect-increment-pct').attr('readonly', true);

    if (update === "1") {
      if (item === 'amount') {
        $('#gross-increment-amt').removeAttr('readonly');
      } else if (item === 'percentage') {
        $('#gross-increment-pct').removeAttr('readonly');
      } else {
        // If item not valid, make sure all are readonly
        $('#gross-increment-amt, #gross-increment-pct').attr('readonly', true);
        // $('.increment-amt, .increment-pct, .indirect-increment-amt, .indirect-increment-pct').attr('readonly', true);
        $('.increment-amt, .increment-pct').attr('readonly', true);
        console.warn("Invalid item type selected for update 1", item);
      }

    } else if (update === "2") {
      if (item === 'amount') {
        // $('.increment-amt, .indirect-increment-amt').removeAttr('readonly');
        $('.increment-amt').removeAttr('readonly');
      } else if (item === 'percentage') {
        // $('.increment-pct, .indirect-increment-pct').removeAttr('readonly');
        $('.increment-pct').removeAttr('readonly');
      } else {
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
      const val = parseFloat($(this).val()) || 0;
      totalNewVal += val;
    });

    var avgPct = count > 0 ? (totalPct / count).toFixed(2) : '0.00';

    // Update input fields
    $('#gross-increment-amt').val(totalAmt.toFixed(2));

    $('#gross-increment-pct').val(avgPct);
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
    const gross = $('#gross-new-value').val();
    const ctcNewVal = Math.round(parseFloat($('#ctc_new_value').text()));
    $.ajax({
      url: livesite + 'Employee/calcSalaryStructure/',
      method: 'POST',
      data: {
        emp_fkey: empFkey,
        monthly_gross: gross,
        monthly_ctc: ctcNewVal
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
        });

        // Calculate total increment %
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

    // Define livesite variable
    var livesite = "<?php echo $this->webroot; ?>";

    // Initialize Select2
    $('#Employee_fkey').select2();
    $('#salary_structure').select2();

    // On employee select
    $('#Employee_fkey').on('change', function() {
      // Reset select
      $('#select-update').val('');
      $('#select-item').val('');


      var empPkey = $(this).val();

      if (empPkey !== "") {
        fetchSalaryStructure(empPkey);
      } else {
        $('#salary_structure_table').slideUp();
        $('#no-data').slideUp(); // hide it
        $('#salary_structure_container').slideUp(); // hide it
        $('#footer-input').slideUp(); // hide it
        $('#btn-submit').slideUp(); // hide it
        // $('#salary_structure_container').empty();

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
        // alert("Please select a salary structure.");
        $.notify("Please select a salary structure.", {
          type: "danger",
          z_index: 9999,
          allow_dismiss: true
        });
        return;
      }

      $.ajax({
        url: livesite + 'Employee/alterSalaryStructure',
        type: 'POST',
        dataType: 'json', // 👈 this makes jQuery parse response as JSON
        data: {
          action: 'update_structure',
          structure_id: structureId,
          emp_fkey: empPkey,

        },
        success: function(response) {
          // handle response from PHP
          // console.log(response);
          // alert(response.message);
          $.notify(response.message, {
            type: "success",
            z_index: 9999,
            allow_dismiss: true
          });
          fetchSalaryStructure(empPkey);
        },
        error: function() {
          // alert("An error occurred while updating salary structure.");
          $.notify("An error occurred while updating salary structure.", {
            type: "danger",
            z_index: 9999,
            allow_dismiss: true
          });
        }
      });
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
            url: livesite + 'Employee/onEffectiveDateChange',
            type: 'POST',
            data: {
              empPkey: empPkey,
              start_date_effective: startDate
            },
            dataType: 'json',
            success: function(response) {
              if (response.status === 'success') {
                $('input[name="arrear_salary"]').val(response.is_processed ? 'Y' : 'N');
                if (response.is_processed)
                  // alert(response.message);
                  $.notify(response.message, {
                    type: "success",
                    z_index: 9999,
                    allow_dismiss: true
                  });
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
    function renderSalaryStructure(structure, indirectStructure) {
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
        const current = isNaN(parseFloat(item.value)) ? "0.00" : parseFloat(item.value).toFixed(2);
        const newVal = isNaN(parseFloat(item.new_value || item.value)) ? "0.00" : parseFloat(item.new_value || item.value).toFixed(2);
        const incrementAmt = (newVal - current).toFixed(2);
        const incrementPct = current > 0 ? (((newVal - current) / current) * 100).toFixed(2) : '';

        totalCurrent += parseFloat(current);
        totalNewVal += parseFloat(newVal);
        totalIncrementAmt += parseFloat(incrementAmt);

        const row = `
                      <tr>
                        <td>${index + 1}</td>
                        <td>${item.desc}<input type="hidden" name="desc_${item.key}" value="${item.desc}"></td>
                        <td id="current_${index}">${current}<input type="hidden" name="current_${item.key}" value="${current}"></td>
                        <td><input type="number" id="new_value_${index}" name="new_value_${item.key}" class="form-control" value="${newVal}" data-index="${index}" min="0" readonly></td>
                        <td><input type="number" name="new_value_amt${item.key}" class="form-control increment-amt" data-index="${index}" value="${incrementAmt}" min="0" readonly></td>
                        <td><input type="number" name="new_value_pct${item.key}" class="form-control increment-pct" data-index="${index}" value="${incrementPct}" min="0" readonly></td>
                      </tr>
                    `;
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
          <td>${totalIndex + 1}</td>
          <td>Gross Salary</td>
          <td id="gross-current">${totalCurrent.toFixed(2)}</td>
          <td><input id="gross-new-value" class="form-control" value="${totalNewVal.toFixed(2)}" min="0" readonly></td>
          <td><input id="gross-increment-amt" type="number" class="form-control" value="${totalIncrementAmt.toFixed(2)}" min="0" readonly></td>
          <td><input id="gross-increment-pct" type="number" class="form-control" value="${((totalIncrementAmt / totalCurrent) * 100).toFixed(2)}" min="0" readonly></td>
        </tr>
      `;

      $tbody.append(grossRow);



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
        <td>${totalIndex + index + 1}</td>
        <td>${item.desc}<input type="hidden" name="desc_${item.key}" value="${item.desc}"></td>
        <td id="indirect_current_${index}">${indirectCurrent}<input type="hidden" name="indirect_current_${item.key}" value="${indirectCurrent}"></td>
        <td><input type="number" id="indirect_new_value_${index}" name="indirect_new_value_${item.key}" class="form-control" value="${indirectNewVal}" data-index="${index}" min="0" readonly></td>
        <td><input type="number" name="indirect_new_value_amt${item.key}" class="form-control indirect-increment-amt" data-index="${index}" value="${indirectIncrementAmt}" min="0" readonly></td>
        <td><input type="number" name="indirect_new_value_pct${item.key}" class="form-control indirect-increment-pct" data-index="${index}" value="${indirectIncrementPct}" min="0" readonly></td>
      </tr>
    `;
        $tbody.append(indirectRow);
      });

      const ctcRow = `
        <tr style="font-weight: bold;">
          <td>${totalIndex + 2}</td>
          <td>CTC</td>
          <td>${totalCurrent.toFixed(2)}<input type="hidden" name="emp_cur_monthly_ctc" value="${totalCurrent.toFixed(2)}"></td>
          <td id="ctc_new_value">${totalNewVal.toFixed(2)}
          <input type="hidden" name="emp_monthly_ctc" class="emp_monthly_ctc" value ="">
          </td>
          <td id="ctc_increment_amt">${totalIncrementAmt.toFixed(2)}</td>
          <td id="ctc_increment_pct">${((totalIncrementAmt/totalCurrent) * 100).toFixed(2)}</td>
        </tr>

        <tr style="font-weight: bold;">
          <td></td>
          <td>Yearly Gross Salary</td>
          <td>${(grossTotal * 12).toFixed(2)}<input type="hidden" name="emp_current_anual_ctc" value="${(grossTotal * 12).toFixed(2)}"></td>
          <td id="yearly-gross-new-value"><input type="hidden" name="emp_anual_ctc" class="emp_anual_ctc"  value =""></td>
          <td></td>
          <td></td>
        </tr>

        <tr style="font-weight: bold;">
          <td></td>
          <td>Yearly CTC</td>
          <td>${(totalCurrent * 12).toFixed(2)}</td>
          <td id="yearly_ctc_new_value"></td>
          <td></td>
          <td></td>
        </tr>
      `;

      $tbody.append(ctcRow);

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
        url: livesite + 'Employee/getSalaryStructure/' + empPkey,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
          if (response.success && ((Array.isArray(response.structure) && response.structure.length > 0) || (Array.isArray(response.structure_indirect) && response.structure_indirect.length > 0))) {
            renderSalaryStructure(response.structure, response.structure_indirect);

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
            } else {
              $('#prev_hike_text').text('');
              $('#prev_hike').val('');
              prevSal = '';
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

    // On change of amount
    $(document).on('change', '.increment-amt', function() {
      const index = $(this).data('index');
      const incrementVal = parseFloat($(this).val()) || 0;
      const currentVal = parseFloat($(`#current_${index}`).text()) || 0;
      const newVal = (currentVal + incrementVal).toFixed(2);
      const incrementPct = currentVal > 0 ? ((incrementVal / currentVal) * 100).toFixed(2) : '0.00';
      $(`#new_value_${index}`).val(newVal);
      $(`input.increment-pct[data-index="${index}"]`).val(incrementPct);
    });

    // On change of percentage
    $(document).on('change', '.increment-pct', function() {
      const index = $(this).data('index');
      const incrementPerc = parseFloat($(this).val()) || 0;
      const currentVal = parseFloat($(`#current_${index}`).text()) || 0;
      const incrementAmt = (currentVal * (incrementPerc / 100)).toFixed(2);
      const newVal = (currentVal + (currentVal * (incrementPerc / 100))).toFixed(2);
      $(`#new_value_${index}`).val(newVal);
      $(`input.increment-amt[data-index="${index}"]`).val(incrementAmt);
    });

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

    $(document).on('change', '#gross-increment-amt', function() {
      const incrementVal = parseFloat($(this).val()) || 0;
      const currentVal = parseFloat($('#gross-current').text()) || 0;
      const newVal = (currentVal + incrementVal).toFixed(2);
      const incrementPct = currentVal > 0 ? ((incrementVal / currentVal) * 100).toFixed(2) : '0.00';

      // console.log('Gross From Amt → current:', currentVal, 'increment:', incrementVal, '→ newVal:', newVal, '→ pct:', incrementPct);

      $('#gross-new-value').val(newVal);
      $('#yearly-gross-new-value').text(Math.round(newVal * 12));
      $('#gross-increment-pct').val(incrementPct);
      recalcSalaryStructure(); // To find individal item value
    });

    $(document).on('change', '#gross-increment-pct', function() {
      const incrementPct = parseFloat($(this).val()) || 0;
      const currentVal = parseFloat($('#gross-current').text()) || 0;
      const incrementAmt = (currentVal * (incrementPct / 100)).toFixed(2);
      const newVal = (currentVal + parseFloat(incrementAmt)).toFixed(2);

      // console.log('Gross From Pct → current:', currentVal, 'percent:', incrementPct, '→ amt:', incrementAmt, '→ newVal:', newVal);

      $('#gross-new-value').val(newVal);
      $('#yearly-gross-new-value').text(Math.round(newVal * 12));
      $('#gross-increment-amt').val(incrementAmt);
      recalcSalaryStructure(); // To find individal item value
    });



    // On submit
    $('#incrementeuploadtable').on('submit', function(e) {
      console.log('submit triggered');
      e.preventDefault(); // prevent default form submission

      // Get the monthly CTC from the displayed value
      const monthlyGross = parseFloat($('#gross-new-value').val()) || 0;
      console.log('monthlyGross', monthlyGross);

      // Compute yearly CTC
      const anualGross = parseFloat($('#yearly-gross-new-value').text()) || 0;
      console.log('anualGross', anualGross);

      // 🚫 Prevent submit if anualGross is 0
      if (anualGross === 0) {
        $.notify("New CTC cannot be 0.", {
          type: "danger",
          z_index: 9999,
          allow_dismiss: true
        });
        return; // stop further execution
      }

      var form = $(this)[0]; // pure DOM object
      var formData = new FormData(form); // handles file input if needed
      // Override or add fields in FormData directly
      formData.set('emp_monthly_ctc', monthlyGross.toFixed(2));
      formData.set('emp_anual_ctc', anualGross);

      $.ajax({
        url: $(this).attr('action'),
        type: 'POST',
        data: formData,
        contentType: false, // important for FormData
        processData: false, // important for FormData
        success: function(response) {
          // You can customize this part
          // alert('Form submitted successfully!');
          $.notify('Form submitted successfully!', {
            type: "success",
            z_index: 9999,
            allow_dismiss: true
          });
          $('#att_table').datagrid('reload');
          $('#largeModalForm').modal('hide'); // close the modal
          // Optionally reload data table or update UI
        },
        error: function(xhr, status, error) {
          $.notify("Something went wrong: " + error, {
            type: "danger",
            z_index: 9999,
            allow_dismiss: true
          });

          console.log(xhr.responseText);
        }
      });
    });


  });
</script>