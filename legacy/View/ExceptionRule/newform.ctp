<style>
    /* // create by bindu 17-10-2025 */
    .full-content {
        font-family: Arial, sans-serif;

        border-radius: 12px !important;

        display: flex;
        justify-content: center;
        align-items: center;
        margin-bottom: 40px;
    }

    .rule-container {
        background-color: #fff;
        padding: 10px 20px;
        border-radius: 12px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        width: 750px;

    }

    .rule-container h3 {
        color: #1e516e;
        font-size: 22px;
        font-weight: bold;
        text-align: center;
        margin-bottom: 25px;

    }

    .form-row {
        display: flex;
        flex-wrap: wrap;
        gap: 25px;
        margin-bottom: 10px;
        align-items: center;
    }

    .form-group {
        flex: 1;
        min-width: 220px;
    }

    .form-group label {
        display: block;
        font-weight: bold;
        margin-bottom: 3px;
        color: #333;
    }

    .form-group input[type="text"],
    .form-group select {
        width: 100%;
        padding: 6px 8px;
        border: 1px solid #ccc;
        border-radius: 6px;
        box-sizing: border-box;
        transition: border 0.2s ease-in-out;
    }

    .form-group input[type="text"]:focus,
    .form-group select:focus {
        border-color: #1e516e;
        outline: none;
    }

    .form-group input[type="checkbox"] {
        margin-right: 6px;
    }
#resetCheckbox{
    margin-right: 2px !important;
}
    .form-actions {
        text-align: center;

    }
    input[type=checkbox]{
    margin: 4px 1px;
    }

    .form-actions button {
        background-color: #1e516e;
        color: white;
        padding: 6px 12px;
        border: none;
        border-radius: 6px;
        font-size: 16px;
        cursor: pointer;
        transition: all 0.3s ease-in-out;
    }

    #resetCheckbox {
        transform: scale(1.1);
        margin-right: 6px;
    }

    .save_create {
        background-color: #1e516e;

        color: white;
        border: none;
        border-radius: 8px;
        padding: 6px 1px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .save_create:hover {
        background-color: #143d52;
        /* darker on hover */
    }

    .heading {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0px 0px 10px 0;
    }
    /* .form-close-btn{
padding:0px 1px;
background: none;
border: 1px solid gray;
/* border-radius: 8px; */
/* margin: 2px; */
    
</style>
<div class="full-content">
    <div class="rule-container">
        <div class="heading" style="margin-bottom: 10px;">
            <h3 style="margin: 0;">Rule Creation</h3>
            <button type="button" class="form-close-btn" data-bs-dismiss="modal" aria-label="Close" onclick="$('#modalForm').modal('hide');">x</button>

        </div>
        <form id="ruleform">
      
            <div class="form-row">
                <div class="form-group">
                    <label for="ruleName">Rule Name: &nbsp;<span style="color:red;">*</span></label>
                    <input type="text" id="ruleName" required name="ruleName" maxlength="50"
                        value="<?php echo isset($rule['ExceptionRule']['rule_name']) ? htmlspecialchars($rule['ExceptionRule']['rule_name'], ENT_QUOTES) : ''; ?>"
                        oninput="this.value = this.value.replace(/[^A-Za-z\s0-9]/g, '');">
                        <small id="ruleNameMsg" style="color:red;"></small>
                </div>
                <div class="form-group" style="display:flex;flex-direction:row;gap:10px;align-items:center;">
                    <label>Rule Type: &nbsp;<span style="color:red;">*</span></label>
                    <div style="display:flex;flex-direction:row;gap:20px;align-items:center;">
                        <label>
                            <input type="radio" name="ruleType" value="daily" <?php echo (isset($rule['ExceptionRule']['rule_type']) && $rule['ExceptionRule']['rule_type'] == 'daily') ? 'checked' : ''; ?>> Daily
                        </label>
                        <label>
                            <input type="radio" name="ruleType" value="monthly" <?php echo (isset($rule['ExceptionRule']['rule_type']) && $rule['ExceptionRule']['rule_type'] == 'monthly') ? 'checked' : ''; ?>> Monthly
                        </label>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="dataType">Data Type: &nbsp;<span style="color:red;">*</span></label>
                    <select id="dataType" name="dataType">
                        <option value="" required>Select</option>
                        <option value="0" <?php echo (isset($rule['ExceptionRule']['data_type']) && $rule['ExceptionRule']['data_type'] == '0') ? 'selected' : ''; ?>>Early Out</option>
                        <option value="1" <?php echo (isset($rule['ExceptionRule']['data_type']) && $rule['ExceptionRule']['data_type'] == '1') ? 'selected' : ''; ?>>Late In</option>
                        <option value="2" <?php echo (isset($rule['ExceptionRule']['data_type']) && $rule['ExceptionRule']['data_type'] == '2') ? 'selected' : ''; ?>>Late In and Early Out</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="actionException">Action for Exception: &nbsp;<span style="color:red;">*</span></label>
                    <select id="actionException" name="actionException">
                        <option value="" required>Select</option>
                        <option value="0" <?php echo (isset($rule['ExceptionRule']['action_after_exception']) && $rule['ExceptionRule']['action_after_exception'] == '0') ? 'selected' : ''; ?>>Leave Deduction</option>
                        <option value="1" <?php echo (isset($rule['ExceptionRule']['action_after_exception']) && $rule['ExceptionRule']['action_after_exception'] == '1') ? 'selected' : ''; ?>>Loss of Pay</option>
                    </select>
                </div>
            </div>

     
           <div class="form-row">
  <div class="form-group" id="exceptionContainer">
    <?php if (isset($rule['ExceptionRule']['rule_type']) && $rule['ExceptionRule']['rule_type'] == 'monthly'): ?>
      <label for="exceptionTimeLimit">Exception Time Limit: &nbsp;<span style="color:red;">*</span></label>
      <input type="text" id="exceptionTimeLimit" name="exceptionTimeLimit" maxlength="3" required
          value="<?php echo isset($rule['ExceptionRule']['exception_time']) ? htmlspecialchars($rule['ExceptionRule']['exception_time'], ENT_QUOTES) : ''; ?>"
          onkeypress="return /[0-9]/.test(event.key)">
      <span id="exceptionTimeLimitError" class="text-danger" style="display:none;"></span>
    <?php else: ?>
      <label for="exceptionDays">Exception Count: &nbsp;<span style="color:red;">*</span></label>
      <input type="text" id="exceptionDays" name="exceptionDays" maxlength="3" required 
          value="<?php echo isset($rule['ExceptionRule']['exception_days']) ? htmlspecialchars($rule['ExceptionRule']['exception_days'], ENT_QUOTES) : ''; ?>"
          onkeypress="return /[0-9]/.test(event.key)">
      <span id="exceptionDaysError" class="text-danger" style="display:none;"></span>
    <?php endif; ?>
  </div>

  <div class="form-group">
    <label for="countDetection">Count of Deduction: &nbsp;<span style="color:red;">*</span></label>
    <input type="text" id="countDetection" name="countDetection" maxlength="3" required placeholder="Count of Deduction Eg: 0.5 or 1 CL,SL..."
        value="<?php echo isset($rule['ExceptionRule']['detect_count']) ? htmlspecialchars($rule['ExceptionRule']['detect_count'], ENT_QUOTES) : ''; ?>"
        onkeypress="return /[0-9.]/.test(event.key)">
    <span id="countDetectionError" class="text-danger" style="display:none;"></span>
  </div>
</div>

      
            <div class="form-row">
                <div class="form-group" id="leaveTypeGroup">
                    <label for="leaveType">Leave Type: &nbsp;<span style="color:red;">*</span></label>
                    <select id="leaveType" name="leaveType" style="width:48%;">
                        <option value="" required>Select Leave Type</option>
                        <option value="87" <?php echo (isset($rule['ExceptionRule']['leave_detect_type']) && $rule['ExceptionRule']['leave_detect_type'] == '87') ? 'selected' : ''; ?>>Casual Leave</option>
                        <option value="86" <?php echo (isset($rule['ExceptionRule']['leave_detect_type']) && $rule['ExceptionRule']['leave_detect_type'] == '86') ? 'selected' : ''; ?>>Sick Leave</option>
                        <option value="88" <?php echo (isset($rule['ExceptionRule']['leave_detect_type']) && $rule['ExceptionRule']['leave_detect_type'] == '88') ? 'selected' : ''; ?>>Earned Leave</option>
                        <option value="114" <?php echo (isset($rule['ExceptionRule']['leave_detect_type']) && $rule['ExceptionRule']['leave_detect_type'] == '114') ? 'selected' : ''; ?>>Compensatory Off</option>
                        <option value="89" <?php echo (isset($rule['ExceptionRule']['leave_detect_type']) && $rule['ExceptionRule']['leave_detect_type'] == '89') ? 'selected' : ''; ?>>Privilege Leave</option>
                    </select>

                   
                </div>
            </div>
            <div style="display: flex;justify-content: start;
    gap: 20px;
    margin-top: -18px;
    margin-bottom:10px;">
                <div style="margin-top:10px;">
                        <label style="font-weight:normal;">
                            <input type="checkbox" id="resetCheckbox" name="resetCheckbox" <?php echo (isset($rule['ExceptionRule']['reset_status']) && $rule['ExceptionRule']['reset_status'] == '1') ? 'checked' : ''; ?>>
                            Reset After Each Cycle
                        </label>
                    </div>
 <div style="margin-top:10px;">
                        <label style="font-weight:normal;">
                            <input type="checkbox" id="activateCheckbox" name="activateCheckbox" <?php echo (isset($rule['ExceptionRule']['activate_status']) && $rule['ExceptionRule']['activate_status'] == '1') ? 'checked' : ''; ?>>
                            Activate
                        </label>
                    </div>
            </div>
             
            <div class="form-actions">
               
                <button
                    type="button"
                    class="clear_form"
                    style="background-color: #fbfeffff; color: #1e516e; border:1px solid #1e516e; padding: 5px 12px; cursor: pointer;"
                    onclick="resetRuleForm()">
                    Clear
                </button>
                 <button class="save_action" type="submit">Save Rule</button>
            </div>

            <input type="hidden" id="exceptionId" name="exceptionId" value="<?php echo isset($rule['ExceptionRule']['exception_id']) ? $rule['ExceptionRule']['exception_id'] : ''; ?>">
        </form>
    </div>
</div>

<script>
    $('#modalForm').modal({
        backdrop: 'static',
        keyboard: false
    });

    document.getElementById("ruleform").addEventListener("submit", function (e) {
    e.preventDefault();

    const exceptionId = document.getElementById("exceptionId").value;
    const ruleName = document.getElementById("ruleName").value.trim();
    const ruleTypeEl = document.querySelector("input[name='ruleType']:checked");
    const dataType = document.getElementById("dataType").value;
    const actionException = document.getElementById("actionException").value.trim();
    const exceptionDaysEl = document.getElementById("exceptionDays");
    const exceptionTimeLimitEl = document.getElementById("exceptionTimeLimit");
    const countDetection = document.getElementById("countDetection").value.trim();
    const leaveType = document.getElementById("leaveType").value;
console.log(actionException);
    // 🔍 Validation logic
    if (
        !ruleName ||
        !ruleTypeEl ||
        !dataType ||
        !actionException ||
        ((!exceptionDaysEl?.value.trim()) && (!exceptionTimeLimitEl?.value.trim())) ||
        !countDetection ||
        // ❌ leaveType is required only if actionException is NOT 'lop'
        (actionException !== "1" && !leaveType)
    ) {
        $.notify("Please fill all required fields before saving.", { type: "danger", z_index: 99999 });
        return;
    }

   // ✅ Prepare leaveType value safely
let finalLeaveType = null;

// If Loss of Pay selected (1), clear leave type
if (actionException === "1" || actionException === 1) {
    finalLeaveType = null;
} else {
    // If Leave Deduct, ensure a leave type is selected
    if (!leaveType || leaveType.trim() === "") {
        $.notify("Please select a Leave Type when Action is 'Leave Deduction'.", { type: "danger", z_index: 99999 });
        return;
    }
    finalLeaveType = leaveType;
}

// ✅ Build final object for backend
const formData = {
    exception_id: exceptionId || "",
    ruleName: ruleName,
    ruleType: ruleTypeEl.value,
    dataType: dataType,
    actionException: actionException,
    exceptionDays: exceptionDaysEl ? exceptionDaysEl.value.trim() : "",
    exceptionTimeLimit: exceptionTimeLimitEl ? exceptionTimeLimitEl.value.trim() : "",
    countDetection: countDetection,
    leaveType: finalLeaveType, // ✅ use safe value
    resetCheckbox: document.getElementById("resetCheckbox").checked ? 1 : 0,
    activateCheckbox: document.getElementById("activateCheckbox").checked ? 1 : 0
};


    const url = livesite + (exceptionId ? "ExceptionRule/updateRule" : "ExceptionRule/saveRule");

    fetch(url, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(formData)
    })
    .then(res => res.json())
    .then(res => {
        if (res.status === "success") {
            $.notify(res.message, { type: "success", z_index: 99999 });
            $('#modalForm').modal('hide');
            $('#ruleTable').datagrid('reload');
        } else {
            $.notify(res.message || "Failed to save rule", { type: "warning", z_index: 99999 });
        }
    });
});


//    document.getElementById("ruleform").addEventListener("submit", function(e) {
//     e.preventDefault();

//     const exceptionId = document.getElementById("exceptionId").value;
//     const ruleName = document.getElementById("ruleName").value.trim();
//     const ruleTypeEl = document.querySelector("input[name='ruleType']:checked");
//     const dataType = document.getElementById("dataType").value;
//     const actionException = document.getElementById("actionException").value;
//     const exceptionDaysEl = document.getElementById("exceptionDays");
//     const exceptionTimeLimitEl = document.getElementById("exceptionTimeLimit");
//     const countDetection = document.getElementById("countDetection").value.trim();
//     const leaveType = document.getElementById("leaveType").value;
    
//     if (!ruleName || !ruleTypeEl || !dataType || !actionException || 
//         (exceptionDaysEl && !exceptionDaysEl.value.trim()) && 
//         (exceptionTimeLimitEl && !exceptionTimeLimitEl.value.trim()) ||
//         !countDetection || !leaveType) {
//         $.notify("Please fill all required fields before saving.", { type: "danger", z_index: 99999 });
//         return; 
//     }

//     const formData = {
//         exception_id: exceptionId || "", 
//         ruleName: ruleName,
//         ruleType: ruleTypeEl.value,
//         dataType: dataType,
//         actionException: actionException,
//         exceptionDays: exceptionDaysEl ? exceptionDaysEl.value.trim() : "",
//         exceptionTimeLimit: exceptionTimeLimitEl ? exceptionTimeLimitEl.value.trim() : "",
//         countDetection: countDetection,
//         leaveType: leaveType,
//         resetCheckbox: document.getElementById("resetCheckbox").checked ? 1 : 0
//     };

//     const url = livesite + (exceptionId ? "ExceptionRule/updateRule" : "ExceptionRule/saveRule");

//     fetch(url, {
//         method: "POST",
//         headers: { "Content-Type": "application/json" },
//         body: JSON.stringify(formData)
//     })
//     .then(res => res.json())
//     .then(res => {
//         if (res.status === "success") {
//             $.notify(res.message, { type: "success", z_index: 99999 });
//             $('#modalForm').modal('hide');
//             $('#ruleTable').datagrid('reload');
//         } else {
//             $.notify(res.message || "Failed to save rule", { type: "warning", z_index: 99999 });
//         }
//     })
// });

    const ruleTypeRadios = document.querySelectorAll('input[name="ruleType"]');
    const exceptionContainer = document.getElementById('exceptionContainer');
    const actionExceptionEl = document.getElementById('actionException');
    const leaveTypeGroup = document.getElementById('leaveTypeGroup');

    ruleTypeRadios.forEach(radio => {
        radio.addEventListener('change', () => {
            if (radio.value === 'monthly' && radio.checked) {
                exceptionContainer.innerHTML = `
                    <label for="exceptionTimeLimit">Exception Time Limit (Minutes): &nbsp;<span style="color:red;">*</span></label>
                    <input type="text" id="exceptionTimeLimit" name="exceptionTimeLimit" required placeholder="Enter Time Limit in minutes" maxlength="3" onkeypress="return /[0-9]/.test(event.key)">
                    <span id="exceptionTimeLimitError" class="text-danger" style="display:none;"></span>
                `;
            } else {
                exceptionContainer.innerHTML = `
                    <label for="exceptionDays">Exception Count: &nbsp;<span style="color:red;">*</span></label>
                    <input type="text" id="exceptionDays" name="exceptionDays" required placeholder="Enter count" maxlength="3" onkeypress="return /[0-9]/.test(event.key)">
                    <span id="exceptionDaysError" class="text-danger" style="display:none;"></span>
                `;
            }
            attachZeroValidation();
        });
    });

    function toggleLeaveTypeVisibility() {
        if (!actionExceptionEl || !leaveTypeGroup) return;
        // Hide Leave Type when Loss of Pay selected (value === '1')
        if (actionExceptionEl.value === '1') {
            leaveTypeGroup.style.display = 'none';
            // clear selection to avoid accidental submit
            const leaveSel = document.getElementById('leaveType');
            if (leaveSel) leaveSel.selectedIndex = 0;
        } else {
            leaveTypeGroup.style.display = '';
        }
    }

    if (actionExceptionEl) {
        actionExceptionEl.addEventListener('change', toggleLeaveTypeVisibility);
        // initialize on load (for edit mode too)
        toggleLeaveTypeVisibility();
    }

 function resetRuleForm() {
 
    document.getElementById("ruleName").value = "";
    document.getElementById("countDetection").value = "";

    const radios = document.querySelectorAll("input[name='ruleType']");
    radios.forEach(radio => radio.checked = false); 

    document.getElementById("dataType").selectedIndex = 0;
    document.getElementById("actionException").selectedIndex = 0;
    document.getElementById("leaveType").selectedIndex = 0;

    const exceptionDaysEl = document.getElementById("exceptionDays");
    if (exceptionDaysEl) exceptionDaysEl.value = "";
    const exceptionTimeLimitEl = document.getElementById("exceptionTimeLimit");
    if (exceptionTimeLimitEl) exceptionTimeLimitEl.value = "";

    document.getElementById("resetCheckbox").checked = false;

    document.getElementById("exceptionId").value = "";
}

</script>
<script>
$('#ruleName').on('blur', function() {
    var ruleName = $(this).val().trim();
    if(ruleName.length === 0) return;

    $.ajax({
        url: 'ExceptionRule/checkRuleName',
        type: 'POST',
        data: { ruleName: ruleName },
        dataType: 'json',
        success: function(response) {
            $('#ruleNameMsg').text(response.message);
            if(response.exists) {
                $('#ruleName').val(''); // optional: clear input
            }
        },
        error: function() {
            $('#ruleNameMsg').text('Error checking rule name.');
        }
    });
});


</script>
<script>
$(document).ready(function () {

    $('#countDetection').on('input', function () {

    let $input = $(this);
    let $error = $('#countDetectionError');
    let val = $input.val().trim();

    // Allow empty
    if (val === '') {
        $error.text('').hide();
        return;
    }

    // Allow typing stage: "0" or "0."
    if (val === '0' || val === '0.') {
        $error.text('').hide();
        return;
    }

    let num = parseFloat(val);

    // Accept ONLY 0.5 or 1
    if (num === 0.5 || num === 1) {
        $error.text('').hide();
    } else {
        $error.text('Only 0.5 or 1 allowed')
              .css('color', 'red')
              .show();
        $input.val('');
    }
});

  const submitBtn = $('.save_action'); // your submit button

  // track validity
  let numericValid = {
    exceptionTimeLimit: true,
    exceptionDays: true,
    countDetection: true
  };

 function validateField(fieldId) {
    const $input = $('#' + fieldId);
    const $error = $('#' + fieldId + 'Error');
    if ($input.length === 0) return;

    let val = $input.val().trim();

    // Replace multiple dots with a single dot
    val = val.replace(/\.{2,}/g, '.');
    $input.val(val);

    // Validation flags
    let invalid = false;

    // ❌ Invalid if starts with multiple zeros (except "0" or "0.x")
    if (/^0\d+/.test(val)) {
        invalid = true;
    }

    // ❌ Invalid if all zeros (like 0.00, 00, etc.)
    else if (/^0\.0+$/.test(val)) {
        invalid = true;
    }

    if (invalid) {
        $error.text('Invalid input').css('color', 'red').show();
        alert('Invalid Input [cannot start with 0 like 00, 000, 078 or be all zeros]');
        $input.val('');
        numericValid[fieldId] = false;
        return;
    }

    // Convert to number for next check
    const numVal = parseFloat(val);

    if (!isNaN(numVal)) {
        // ❌ Invalid if less than 0.5 (but allow 0)
        if (numVal < 0.5 && numVal !== 0) {
            alert('Value cannot be less than 0.5');
            $input.val('');
            numericValid[fieldId] = false;
            return;
        }
    }

    // ✅ Passed all checks
    $error.text('').hide();
    numericValid[fieldId] = true;

    // Enable or disable Save button
    submitBtn.prop('disabled', !Object.values(numericValid).every(v => v === true));
}


  // Validate on typing or losing focus
  ['exceptionTimeLimit', 'exceptionDays', 'countDetection'].forEach(id => {
    $(document).on('input blur', '#' + id, function () {
      validateField(id);
    });
  });

  // On form submit, block invalid
  $('form').on('submit', function (e) {
    let invalid = false;
    ['exceptionTimeLimit', 'exceptionDays', 'countDetection'].forEach(id => {
      const $input = $('#' + id);
      if ($input.length) validateField(id);
      if (!numericValid[id]) invalid = true;
    });

    // if (invalid) {
    //   e.preventDefault();
    //   alert('Please fix invalid numeric values before submitting.');
    // }
  });

  // Initially enable Save button (will be disabled only if validation fails)
  submitBtn.prop('disabled', false);
});


function attachZeroValidation() {
  const submitBtn = document.querySelector('.save_action'); // submit button
  const numericValid = { 
    exceptionTimeLimit: true,
    exceptionDays: true
  };

  ['exceptionTimeLimit', 'exceptionDays'].forEach(id => {
    const input = document.getElementById(id);
    const error = document.getElementById(id + 'Error');

    if (input) {
      input.addEventListener('input', function() {
        let val = this.value;

        // Replace multiple consecutive dots with a single dot
        if (val.includes('..')) {
          val = val.replace(/\.{2,}/g, '.');
          this.value = val;
        }

        // ❌ Invalid patterns
        const invalid = 
          val ===
          /^0\d+/.test(val) ||        // starts with 0 followed by digits (00, 000, 078, 098)
        //   /^0\.$/.test(val) ||        // 0. without decimals
          /^0\.0+$/.test(val);        // 0.0, 0.00

        if (invalid) {
          // show error text
          if (error) error.textContent = 'Invalid input';
          if (error) error.style.color = 'red';
          if (error) error.style.display = 'block';

          // 🔴 show alert and clear field
          alert('Invalid Input [cannot be all zeros, starting with 0, or 0.00...]');
          input.value = ''; 

          numericValid[id] = false;
        }
        else {
          if (error) error.textContent = '';
          if (error) error.style.display = 'none';
          numericValid[id] = true;
        }

        // enable/disable submit button
        submitBtn.disabled = !Object.values(numericValid).every(v => v === true);
      });
    }
  });

  // initialize button state - enable by default
  if (submitBtn) submitBtn.disabled = false;
}


</script>
