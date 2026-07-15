<style>
    .alert-class{
        z-index:99999 !important;
    }
</style>
<form id="lpForm" action="<?php echo $this->webroot; ?>LeavePolicy/savepolicy" method="post">

    <div class="modal-body">
        <legend>Add Leave Types</legend>
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <input id="LEAVEPOLICY_GROUP_ID" name="LEAVEPOLICY_GROUP_ID" type="hidden" value="<?php echo $data["LEAVEPOLICY_GROUP_ID"]; ?>">

                    <input id="LEAVEPOLICYID" name="LEAVEPOLICYID" type="hidden" value="<?php echo $data["LEAVEPOLICYID"]; ?>">
                    <label>Leave Type</label>

                    <select name="salary_head_item_fkey" class="form-control input-md" required="" onchange="change_leave_type();">
                        <option>[--Select--]</option>
                        <?php
                        
                        foreach ($policygroup as $key => $value) {
                            
                            $selected = "";
                            if ($data['salary_head_item_fkey'] == $value['id']) {
                                $selected = 'selected="selected"';
                            }
                        ?>

                            <option <?php echo $selected; ?> value="<?php echo $value['id']; ?>"><?php echo $value['label']; ?></option>
                        <?php }
                        ?>
                    </select>

                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Available Duration</label>

                    <select name="leave_policy_type" id="leave_policy_type" onchange="change_leave_type();" class="form-control input-md" required="">
                        <!-- <option <?php echo (isset($data['leave_policy_type']) && $data['leave_policy_type'] == 'Y') ? 'selected="selected"' : ''; ?> value="Y">Yearly</option> -->
                        <option <?php echo (isset($data['leave_policy_type']) && $data['leave_policy_type'] == 'M') ? 'selected="selected"' : ''; ?> value="M">Monthly [Attendance Cycle]</option>
                         <option <?php echo (isset($data['leave_policy_type']) && $data['leave_policy_type'] == 'Q') ? 'selected="selected"' : ''; ?> value="Q">Quarterly</option>
                         <option <?php echo (isset($data['leave_policy_type']) && $data['leave_policy_type'] == 'H') ? 'selected="selected"' : ''; ?> value="H">Half Yearly</option>
                         <option <?php echo (isset($data['leave_policy_type']) && $data['leave_policy_type'] == 'Y') ? 'selected="selected"' : ''; ?> value="Y"> Yearly</option>
                        <option <?php echo (isset($data['leave_policy_type']) && $data['leave_policy_type'] == 'P') ? 'selected="selected"' : ''; ?> value="P">Present Days</option>
                         <!-- <option <?php echo (isset($data['leave_policy_type']) && $data['leave_policy_type'] == 'D') ? 'selected="selected"' : ''; ?> value="D"> Running Days [Back Dated]</option> -->
                        <!-- Edited by Akshay on 15-4-2025 -->
                        <!-- <option <?php echo (isset($data['leave_policy_type']) && $data['leave_policy_type'] == 'Q') ? 'selected="selected"' : ''; ?> value="Q">90 Days</option>
                        <option <?php echo (isset($data['leave_policy_type']) && $data['leave_policy_type'] == 'H') ? 'selected="selected"' : ''; ?> value="H">180 Days</option> -->
                       
                        <!-- End -->
                    </select>

                </div>
            </div>
            <!-- edited by athira on 04-02-2025 -->
           
                    <!-- end -->

                    <div class="col-xs-4" id="dynamicPeriod" style="padding-right:0;">
                        <label id="no_of_days"> No of  Days <span style="color:red;">*</span> </label>
                        <input type="number"   class="form-control" id="dynamic_period" name="dynamic_period" value="<?php echo isset($data["dynamic_period"]) ? $data["dynamic_period"] : 0; ?>">
                        <!-- title="Defines how many presant days for a leave"-->
                    </div>

                    <input type="hidden" id="att_start_date" value="<?php echo $att_start_date; ?>">
<input type="hidden" id="att_end_date" value="<?php echo $att_end_date; ?>">
<?php if($data['leave_policy_type']=='Y') {?>
  <input type="hidden" id="year_start" value="<?php echo $year_start; ?>">
<input type="hidden" id="year_end" value="<?php echo $year_end; ?>">
<?php } ?>
<?php if($data['leave_policy_type']=='P') {?>
 <input type="hidden" id="present_start" value="<?php echo $present_start; ?>">
<input type="hidden" id="present_end" value="<?php echo $present_end; ?>">
<?php } ?>


                <div class="col-xs-4" id="Customstart" style="padding-right:0;">
    <label id="startLabel">Start Date <span style="color:red;">*</span></label>
    <input type="date" required class="form-control"
           id="leave_cycle_start_date"
           name="leave_cycle_start_date"
           value="<?php echo ($data['leave_cycle_start_date'] != '0000-00-00') ? $data['leave_cycle_start_date'] : ''; ?>">
</div>


<div class="col-xs-4" id="Customend">
    <label id="endLabel">End Date <span style="color:red;">*</span></label>
    <input type="date" required  class="form-control" id="leave_cycle_end_date" name="leave_cycle_end_date"
        value="<?php echo ($data['leave_cycle_end_date'] != '0000-00-00') ? $data['leave_cycle_end_date'] : ''; ?>">
</div>
<?php 
$currentMonth = date('n');
$currentYear = date('Y');

// determine current quarter
if ($currentMonth >= 1 && $currentMonth <= 3) {
    $quarterStart = "$currentYear-01-01";
    $quarterEnd   = "$currentYear-03-31";
} elseif ($currentMonth >= 4 && $currentMonth <= 6) {
    $quarterStart = "$currentYear-04-01";
    $quarterEnd   = "$currentYear-06-30";
} elseif ($currentMonth >= 7 && $currentMonth <= 9) {
    $quarterStart = "$currentYear-07-01";
    $quarterEnd   = "$currentYear-09-30";
} else {
    $quarterStart = "$currentYear-10-01";
    $quarterEnd   = "$currentYear-12-31";
}

// pass to hidden inputs
echo "<input type='hidden' id='quarter_start_date' value='$quarterStart'>";
echo "<input type='hidden' id='quarter_end_date' value='$quarterEnd'>";

$currentMonth = date('n');
$currentYear = date('Y');

if ($currentMonth >= 1 && $currentMonth <= 6) {
    $halfStart = "$currentYear-01-01";
    $halfEnd   = "$currentYear-06-30";
} else {
    $halfStart = "$currentYear-07-01";
    $halfEnd   = "$currentYear-12-31";
}

echo "<input type='hidden' id='half_start_date' value='$halfStart'>";
echo "<input type='hidden' id='half_end_date' value='$halfEnd'>";

?>





                    <div class="col-xs-4" id="monthballimit" >
                        <!-- <label id="yearLabel"> Limit </label> -->
                        <label id="yearLabel1">Present days for one leave </label>
                        <input type="number" step="any" class="form-control" name="alloted_leave_forthe_month" id="alloted_leave_forthe_month" value="<?php echo isset($data["alloted_leave_forthe_month"]) ?  $data["alloted_leave_forthe_month"] : ''; ?>">
                        <!-- title="Defines how many presant days for a leave"-->
                    </div>

                    
                    <div class="col-xs-4" id="yearContainer" >
                        <label id="yearLabel"> Limit </label> <input type="number" step="any"   class="form-control" id="alloted_leave_forthe_year" name="alloted_leave_forthe_year" value="<?php echo isset($data["alloted_leave_forthe_year"])? $data["alloted_leave_forthe_year"] : ' '; ?>">
                    </div>

                    <!-- <div class="col-xs-4" id="limit" >
                        <label> Limit </label> <input type="number" step="any" class="form-control" name="alloted_leave_forthe_year" value="<?php echo $data["alloted_leave_forthe_year"]; ?>">
                    </div> -->

                    <!--                    <div class="col-md-4">
                    <label>Applicable To</label>
                    <select name="APPLICABLE_TO" class="form-control input-md" required="" >
                        <?php
                        // foreach ($applicableTo as $key => $value) {
                        $selected = "";
                        if ($data['APPLICABLE_TO'] == $value['id']) {
                            $selected = 'selected="selected"';
                        }
                        ?>

                            <option <?php echo $selected; ?> value="<?php echo $value['id']; ?>"><?php echo $value['label']; ?></option>
                        <?php //}
                        ?>

                    </select>
                </div>-->
                    <div class="col-xs-4" id="carry_forward">
                        <label>Carry Forward Limit </label> <input type="number"  min="0" class="form-control" id="CARRY_FORWARD_LIMIT" name="CARRY_FORWARD_LIMIT" value="<?php echo isset($data["CARRY_FORWARD_LIMIT"]) ? $data["CARRY_FORWARD_LIMIT"] : 0; ?>" >
                    </div>

                    </div>

                </div>
                
                <div class="form-group col-md-12">
                    <div class="row">
                        <!-- edited by athira on 04-02-2025 -->
                        <?php if ($plan != "basic") { ?>
                            <div class="col-sm-6">
                                <label>Sanction By</label>
                                <!--                   <select name="sanction_by" class="form-control input-md" required="" >
                        <option>[--Select--]</option>
                        <?php foreach ($emplist as $key => $value) {
                                $selected = "";
                                if ($data['sanction_by'] == $value['id']) {
                                    $selected = 'selected="selected"';
                                } ?>
                            <option <?php echo $selected; ?> value="<?php echo $value['id']; ?>"><?php echo $value['label']; ?></option>
                        <?php } ?>
                    </select>-->
                                <!--                   <select id="sanction_by" class="form-control input-md js-example-basic-single " name="sanction_by" style="width:270px;">
                   </select>-->
                                <input type="text" id="sanction" class="form-control strict-field" value="<?php echo isset($data['SanctionName']) ? $data['SanctionName'] : '' ?>" name="sanction" placeholder="Search Employee Name" style="width: 270px; " />
                                <input type="hidden" id="sanction_by" name="sanction_by" value="<?php echo isset($data['sanction_by']) ? $data['sanction_by'] : '' ?>" />
                                <span id="MouseSanction"></span>
                            </div>
                        <?php } ?>
                        <!-- end -->
                        <!-- edited by athira on 06-02-2025 -->
                        <?php if ($plan != 'basic') { ?>
                            <?php if ($company_code == 'gede' || $company_code == 'demo' || $company_code == 'mbct') {
                                $level = isset($data['leval_of_approval']) ? $data['leval_of_approval'] : '2'; ?>
                                <div class="col-sm-6">
                                    <label>Level of Approval</label>
                                    <select name="leval_of_approval" class="form-control input-md" required="">
                                        <option value='2' <?php if ($level == 2) {
                                                                echo "selected";
                                                            } ?>>2</option>
                                        <option value='3' <?php if ($level == 3) {
                                                                echo "selected";
                                                            } ?>>3</option>
                                    </select>
                                </div>
                                <div class="col-sm-6">
                                    <label>Notified By</label>
                                    <!--                   <select name="notified_by" class="form-control input-md" required="" >
                        <option>[--Select--]</option>
                        <?php foreach ($emplist as $key => $value) {
                                    $selected = "";
                                    if ($data['notified_by'] == $value['id']) {
                                        $selected = 'selected="selected"';
                                    } ?>
                            <option <?php echo $selected; ?> value="<?php echo $value['id']; ?>"><?php echo $value['label']; ?></option>
                        <?php } ?>
                    </select>-->
                                    <!--                   <select id="notified_by" class="form-control js-example-basic-single" name="notified_by" style="width:270px;">
                   </select>-->
                                    <input type="text" id="Notified" class="form-control strict-field" value="<?php echo isset($data['first_name']) ? $data['first_name'] . ' ' . $data['last_name'] . ' - ' . $data['emp_company_id'] : '' ?>" name="Notified" placeholder="Search Employee Name" style="width: 270px; " />
                                    <input type="hidden" id="notified_by" name="notified_by" value="<?php echo isset($data['notified_by']) ? $data['notified_by'] : '' ?>" />
                                    <span id="MouseNotif"></span>
                                </div>
                            <?php } ?>
                        <?php } ?>
                        <!-- end-->
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-md-12">
                        <label>Description</label>
                        <textarea name="REMARKS" class="form-control input-md" required=""><?php echo $data["REMARKS"]; ?></textarea>
                    </div>
                </div>
 <?php if ($plan != 'basic') { ?>
                <div style="padding: 14px ; " class="form-group col-md-12" id="checkContainer">
                    <!--//edited by megha on 29_05_19 sandwitch leave option display on montlhy list-->
                    <div class="col-md-4" id="sandwitchContainer">
                        <?php
                        $checked = '';
                        if ($data['IS_SANDWICH'] != null && $data['IS_SANDWICH'] == "Y")
                            $checked = 'checked="checked"';
                        ?>
                        <input type="checkbox" <?php echo $checked; ?> value="Y" id="IS_SANDWICH" name="IS_SANDWICH"> Sandwich Leave
                    </div>
                    <div class="col-md-4" id="leave_encashContainer">
                        <?php
                        $checked = '';
                        if ($data['is_leave_encash'] != null && $data['is_leave_encash'] == "Y")
                            $checked = 'checked="checked"';
                        ?>
                        <input type="checkbox" <?php echo $checked; ?> value="Y" id="is_leave_encash" name="is_leave_encash"> Allow Encashment

                    </div>
                    <div class="col-md-4" id="NEGETIVEContainer" style="padding-right:0;">

                        <?php
                        $checked = '';
                        if ($data['ALLOW_NEGETIVE'] != null && $data['ALLOW_NEGETIVE'] == "Y")
                            $checked = 'checked="checked"';
                        ?>

                        <input type="checkbox" <?php echo $checked; ?> value="Y" name="ALLOW_NEGETIVE" id="ALLOW_NEGETIVE"> Allow Negative Balance
                    </div>
                    <?php //if($company_code == 'MBCT' || $company_code == 'DEMO') { 
                    ?>
                    <div class="col-md-4" id="ExceptionsContainer">

                        <?php
                        $checkedexp = '';
                        $exp = isset($data['exceptions']) ? $data['exceptions'] : 'N';
                        if ($exp == "Y") {
                            $checkedexp = 'checked="checked"';
                        }
                        ?>

                        <input type="checkbox" <?php echo $checkedexp; ?> value="Y" name="exceptions" id="Exceptions"> Exceptions
                    </div>
                    <div class="col-md-4">
                        <?php $checked = '';
                        $mandate = isset($data['document_mandatory']) ? $data['document_mandatory'] : 'N';
                        if ($mandate != null && $mandate == "Y")
                            $checked = 'checked="checked"'; ?>
                        <input type="checkbox" name="document_mandatory" id="document_mandatory" <?php echo $checked; ?> value="Y"> Document Mandatory
                    </div>
                    <?php //} 
                    ?>
                    <!--//edited by megha on 29_05_19 sandwitch leave option display on montlhy list-->
                </div>
                <div class="form-group col-md-12">
                    <div class="row">
                        <div class="col-xs-4" id="encahsment_container1">
                            <label id="encash_label">Leave Encashment Limit </label> <input type="number" class="form-control" name="leave_encash_limit" id="encahsmentlimit" value="<?php echo isset($data["leave_encash_limit"]) ? $data["leave_encash_limit"] : ''; ?>">
                        </div>
                        <!-- <div class="col-xs-4" id="encahsment_container">

                            <label> Encashment Components </label>
                            <select name="leave_encashment_component" class="form-control" id="Otc" style="display: inline-block">
                                <option value="">--Select--</option>
                                <?php
                                foreach ($components as $key => $value) {
                                    $selected = ($data['leave_encashment_component'] == $value['salary_head_items']['salary_head_item_pkey']) ? 'selected="selected"' : '';
                                    echo '<option value="' . $value['salary_head_items']['salary_head_item_pkey'] . '" ' . $selected . '>' . $value['salary_head_items']['item'] . '</option>';
                                }
                                ?>
                            </select>
                        </div> -->
                    <?php }  ?>

                    <?php if ($plan != 'basic') { ?>
                    <div class="form-group col-md-12">
                        <!-- Max min leave added by Arul P Das on 18_11_21 -->
                        <div class="row" id="min_max_leave_container">
                            <div class="col-xs-4">
                                <label id="min_leave_label">Min. Leave </label> <input type="number" id="minimum_leave" class="form-control" name="minimum_leave" id="minimum_leave" value="<?php echo isset($data["minimum_leave"]) ? $data["minimum_leave"] : ''; ?>">
                            </div>
                            <div class="col-xs-4">
                                <label id="max_leave_label">Max. Leave </label> <input type="number" id="maximum_leave" class="form-control" name="maximum_leave" id="maximum_leave" value="<?php echo isset($data["maximum_leave"]) ? $data["maximum_leave"] : ''; ?>">
                            </div>
                            <!-- edited by athira on 01-08-2025 -->
                             
                             <div class="col-xs-4">
                                <label id="min_day_before_apply_label">Advance Notice Days </label> <input type="number" class="form-control" name="min_day_before_apply" id="min_day_before_apply" value="<?php echo isset($data["min_day_before_apply"]) ? $data["min_day_before_apply"] : ''; ?>">
                            </div>
                            <div style="align-items: center; gap: 20px; margin-top: 5px;">
                                <div class="col-xs-4">
                                    <label for="minimum_service" id="min_service_label">Min. Service (Month)</label>
                                    <input type="number" class="form-control" name="minimum_service" id="minimum_service" 
                                        value="<?php echo isset($data["minimum_service"]) ? $data["minimum_service"] : ''; ?>">
                                </div>
                                <div class="col-xs-2" style="margin-top: 25px;">
                                    <label style="white-space: nowrap;">
                                        <input type="checkbox" name="allow_all_leaves" id="allow_all_leaves"
                                            <?php echo (isset($data["allow_all_leaves"]) && $data["allow_all_leaves"] === 'Y') ? 'checked' : ''; ?>>
                                        Allow all leaves
                                    </label>
                                </div>
                            </div>

                            <!-- end -->
                            <!-- Min days before apply -->
                           
                        </div>
                        <!-- Max min leave ends here -->
                    </div>
                <?php } ?>
        </div>
        
    </div>
    <div class="modal-footer">
        <!-- edited by athira on 08-02-2025 -->
        <?php if ($plan != 'basic') { ?>
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Save</button>
        <?php } else { ?>

            <button type="button" class="btn btn-default" data-dismiss="modal" style="margin-top:10px;">Close</button>
            <button type="submit" class="btn btn-primary" style="margin-top:10px;">Save</button>
        <?php }  ?>
        <!-- end -->
    </div>
</form>
<script type="text/javascript">
    const company_code = <?php echo json_encode($company_code); ?>;

    $(document).ready(function () {

    const carryInput = document.getElementById('CARRY_FORWARD_LIMIT');
    if (!carryInput) return;

    if (company_code !== 'INFR') {
        // ❌ Block decimals
        carryInput.setAttribute('step', '1');

        carryInput.addEventListener('input', function () {
            if (this.value.includes('.')) {
                alert('Decimal values are not allowed for Carry Forward Limit');
                this.value = Math.floor(this.value);
            }
        });

        carryInput.addEventListener('keydown', function (e) {
            if (e.key === '.' || e.key === 'Decimal') {
                e.preventDefault();
            }
        });

    } else {
        // ✅ Allow decimals
        carryInput.setAttribute('step', 'any');
    }

});


// List of field IDs you want to restrict
const restrictedIds = [
  'alloted_leave_forthe_year',
  'alloted_leave_forthe_month',
  'dynamic_period',
  'CARRY_FORWARD_LIMIT',
  'minimum_leave',
  'maximum_leave',
  'min_day_before_apply',
  'minimum_service',
  'encahsmentlimit'
];

restrictedIds.forEach(id => {
  const input = document.getElementById(id);
  if (!input) return;

  // Prevent typing minus
  input.addEventListener('keydown', function (e) {
    if (e.key === '-' || e.key === 'Subtract') {
      e.preventDefault();
      alert("Negative values are not allowed");
    }
  });

  // Prevent scrolling to negative
  input.addEventListener('wheel', function () {
    if (parseInt(this.value, 10) < 0) {
      this.value = 0;
    }
  });

  // Validate on input (typing, paste, etc.)
  input.addEventListener('input', function () {
    if (this.value < 0) {
      this.value = 0;
      alert("Negative values are not allowed");
    }
  });

  // Extra safeguard when losing focus
  input.addEventListener('blur', function () {
    if (this.value < 0) {
      this.value = 0;
    }
  });
});


function validateLeaveLimits() {
    var minLeave = document.getElementById("minimum_leave").value;
    var maxLeave = document.getElementById("maximum_leave").value;

    if (minLeave !== '' && maxLeave !== '') {
        minLeave = parseInt(minLeave, 10);
        maxLeave = parseInt(maxLeave, 10);

        if (minLeave > maxLeave) {
            alert("Minimum leave cannot be greater than maximum leave.");
            document.getElementById("minimum_leave").value = '';
            document.getElementById("maximum_leave").value = '';
            return false;
        }
    }
    return true;
}


var plan= <?php  echo json_encode($plan);?>;
if(plan !='basic'){
document.getElementById("minimum_leave").addEventListener("change", validateLeaveLimits);
document.getElementById("maximum_leave").addEventListener("change", validateLeaveLimits);
}


    //    $('#Notified').keyup(function() {
    //                $('#notified_by').val('');
    //            }); 

//     document.getElementById("leave_cycle_start_date").addEventListener("change", function () {
//     let leaveType = document.getElementById("leave_policy_type").value;

//     if (leaveType === "Y") {  // only run if Custom Yearly
//         let startDate = new Date(this.value);
//         if (!isNaN(startDate.getTime())) {
//             let endDateField = document.getElementById("leave_cycle_end_date");

//             // Set minimum = start date
//             endDateField.min = this.value;

//             // Calculate maximum = start date + 1 year
//             let maxDate = new Date(startDate);
//             maxDate.setFullYear(maxDate.getFullYear() + 1);

//             let yyyy = maxDate.getFullYear();
//             let mm = String(maxDate.getMonth() + 1).padStart(2, "0");
//             let dd = String(maxDate.getDate()).padStart(2, "0");

//             // Set max limit
//             endDateField.max = `${yyyy}-${mm}-${dd}`;

//             // Reset if outside range
//             if (endDateField.value && (endDateField.value < endDateField.min || endDateField.value > endDateField.max)) {
//                 endDateField.value = "";
//             }
//         }
//     }
// });


    var usersoptions = {
        url: function(phrase) {
            return livesite + 'LeavePolicy/jsons?q=' + phrase;
        },
        getValue: "full_name",
        list: {
            onClickEvent: function() {
                console.log('onClickEvent');
                var selectedItem = $("#Notified").getSelectedItemData();
                var site_fkey = selectedItem.emp_pkey;
                $("#notified_by").val(site_fkey);
                $('#MouseNotif').html('');

            },
            onKeyEnterEvent: function() {
                console.log('onKeyEnterEvent');
            },
            onMouseOverEvent: function() {
                console.log('onMouseOverEvent');
            }
        }
    };

    $('#Notified').easyAutocomplete(usersoptions);

    var usersoptions_sanction = {
        url: function(phrase) {
            return livesite + 'LeavePolicy/jsons?q=' + phrase;
        },
        getValue: "full_name",
        list: {
            onClickEvent: function() {
                console.log('onClickEvent');
                var selectedItem = $("#sanction").getSelectedItemData();
                var site_fkey = selectedItem.emp_pkey;
                $("#sanction_by").val(site_fkey);
                $('#MouseSanction').html('');

            },
            onKeyEnterEvent: function() {
                console.log('onKeyEnterEvent');
            },
            onMouseOverEvent: function() {
                console.log('onMouseOverEvent');
            }
        }
    };

    $('#sanction').easyAutocomplete(usersoptions_sanction);



var occurrences = <?php echo json_encode($occurrence_map); ?>;

function handleCOFFLimit() {
    var selectedLeave = $('select[name="salary_head_item_fkey"]').val();
    var occ = occurrences[selectedLeave];

    if (occ === 'COFF') {
        $('#yearContainer').hide(); // hide all limit fields
        $('#ALLOW_NEGETIVE').hide();
        $('#NEGETIVEContainer').hide();
        $('[name="alloted_leave_forthe_month"]').attr("required", false);
        $('[name="alloted_leave_forthe_year"]').val('0').attr("required", false);
        $('[name="ALLOW_NEGETIVE"]').prop('required', false);
        
    }
}

// Run on page load


// Run every time leave type changes
$('select[name="salary_head_item_fkey"]').on('change', function() {
    handleCOFFLimit();
});



    function change_leave_type() {

        var company_code= <?php  echo json_encode($company_code);?>;
        
        
        if ($('#leave_policy_type').val() == 'M') {

            //$('#checkContainer').hide();
            //edited by megha on 29_05_19 sandwitch leave option display on montlhy list
            $('#checkContainer').show();
            $('#sandwitchContainer').show();
            $('#NEGETIVEContainer').show();
            if(company_code == 'DYGL' || company_code == 'ELSL' || company_code == 'ELLI' || company_code == 'TRSN'){
                $('#leave_encashContainer').show();
            }
            else{
              $('#leave_encashContainer').hide();
            }
            //edited by megha on 29_05_19 sandwitch leave option display on montlhy list
            $('#encahsment_container').hide();
            $('#encahsment_container1').hide();
            $('[name="alloted_leave_forthe_year"]').attr("required", true);
            $('[name="alloted_leave_forthe_month"]').attr("required", false);
            $('#monthballimit').hide();
            $('#yearContainer').show();
            $('#yearLabel').show();
            //  $('#limit').hide();
            $('#yearLabel1').hide();
            $('#Customstart').show();
             $('#Customend').show();
            // $('#IS_SANDWICH').prop('checked', false); 
            // $('#ALLOW_NEGETIVE').prop('checked', false);
            $('#is_leave_encash').prop('checked', false);
            $('#encahsmentlimit').hide(); //.val('');
            $('#encash_label').hide();
            $('#encahsment_container').css("display", "none");
            $('#dynamicPeriod').hide();
             $('[name="dynamic_period"]').attr("required", false);

    $('#leave_cycle_start_date')
    .val($('#att_start_date').val())
    .prop("readonly", true);

$('#leave_cycle_end_date')
    .val($('#att_end_date').val())
    .prop("readonly", true);
            
        }
        
        else if ($('#leave_policy_type').val() == 'Y') {
    $('#checkContainer').show();
    $('#NEGETIVEContainer').show();
    $('#dynamicPeriod').hide();
    $('#leave_encashContainer').show();
    $('#encahsment_container').hide();
    $('#encahsment_container1').hide();
    $('#sandwitchContainer').show();
    $('#yearLabel').show();
    $('#yearLabel1').hide();
    $('#monthballimit').hide();
    $('#yearContainer').show();
    $('[name="alloted_leave_forthe_month"]').val('0').attr("required", false);
    $('[name="alloted_leave_forthe_year"]').attr("required", false);
    $('[name="dynamic_period"]').attr("required", false);

     $('#leave_cycle_start_date')
    .val($('#year_start').val())
    .prop("readonly", false);

$('#leave_cycle_end_date')
    .val($('#year_end').val())
    .prop("readonly", true);


    // Validation when user manually changes start date
$('#leave_cycle_start_date').off('change').on('change', function () {
    let date = new Date(this.value);

    if (!isNaN(date.getTime())) {

        // Keep selected date exactly as user chooses
        let yyyy = date.getFullYear();
        let mm = String(date.getMonth() + 1).padStart(2, '0');
        let dd = String(date.getDate()).padStart(2, '0');

        let startStr = `${yyyy}-${mm}-${dd}`;
        $(this).val(startStr);

        // End date = 1 year minus 1 day from the selected start date
        let end = new Date(date);
        end.setFullYear(end.getFullYear() + 1);
        end.setDate(end.getDate() - 1);

        let endStr =
            `${end.getFullYear()}-${String(end.getMonth() + 1).padStart(2, '0')}-${String(end.getDate()).padStart(2, '0')}`;

        $('#leave_cycle_end_date').val(endStr);
    }
});

}


        
        else if ($('#leave_policy_type').val() == 'D') {

    $('[name="dynamic_period"]').prop("required", true);
    $('#checkContainer').show();
    $('#sandwitchContainer').show();
    $('#NEGETIVEContainer').show();
    $('#leave_encashContainer').show();
    $('#encahsment_container').hide();
    $('#encahsment_container1').hide();
    $('#Customstart').show();
    $('#Customend').show();

    $('[name="alloted_leave_forthe_year"]').attr("required", true);
    $('[name="alloted_leave_forthe_month"]').attr("required", false);

    $('#monthballimit').hide();
    $('#yearContainer').show();
    $('#yearLabel').show();
    $('#yearLabel1').hide();
    // $('#ALLOW_NEGETIVE').prop('checked', false);
    $('#is_leave_encash').prop('checked', false);
    $('#encahsmentlimit').hide();
    $('#encash_label').hide();
    $('#encahsment_container').css("display", "none");
    $('#dynamicPeriod').show();
    $('#carry_forward').hide();

    // 🔹 Set end date = today, readonly
    let today = new Date();
    let todayStr = today.toISOString().split('T')[0];
    $('#leave_cycle_end_date').val(todayStr).prop("readonly", true);

    // 🔹 Start date readonly too
    $('#leave_cycle_start_date').prop('readonly', true);

    // 🔹 Remove old event before binding new one
    $('[name="dynamic_period"]').off('input.dynamic').on('input.dynamic', function () {
        calculateStartDate();
    });

    // 🔹 Function to calculate start date
    function calculateStartDate() {
        let dynamicDays = parseInt($('input[name="dynamic_period"]').val());
        let endDateStr = $('#leave_cycle_end_date').val();

        if (dynamicDays > 0 && endDateStr) {
            let endDate = new Date(endDateStr);
            endDate.setDate(endDate.getDate() - (dynamicDays)); // go backwards

            let startDateStr = endDate.toISOString().split('T')[0];
            $('#leave_cycle_start_date').val(startDateStr);
        } else {
            $('#leave_cycle_start_date').val('');
        }
    }

    // 🔹 Run once on load in case dynamic_period already has a value
    calculateStartDate();
}


        else if ($('#leave_policy_type').val() == 'Q') {
 $('#leave_cycle_start_date')
        .prop("readonly", false)
        .val($('#quarter_start_date').val())
        .prop("readonly", true);

    $('#leave_cycle_end_date')
        .prop("readonly", false)
        .val($('#quarter_end_date').val())
        .prop("readonly", true);
        $('#yearLabel1').hide();
            
            //edited by megha on 29_05_19 sandwitch leave option display on montlhy list
            $('#checkContainer').show();
            $('#sandwitchContainer').show();
            $('#NEGETIVEContainer').show();
            $('#leave_encashContainer').show();
            $('#encahsment_container').hide();
            $('#encahsment_container1').hide();
             $('#Customstart').show();
             $('#Customend').show();
            //$('[name="alloted_leave_forthe_year"]').val('0');
            //$('[name="alloted_leave_forthe_month"]').val('0');
            $('[name="alloted_leave_forthe_year"]').attr("required", true);
            $('[name="alloted_leave_forthe_month"]').attr("required", false);
            $('#monthballimit').hide();
            $('#yearContainer').show();
            $('#yearLabel').show();
            $('#yearLabel1').hide();
            // $('#limit').show();
            $('[name="dynamic_period"]').attr("required", false);
            
            // $('#IS_SANDWICH').prop('checked', false); 
            // $('#ALLOW_NEGETIVE').prop('checked', false);
            $('#is_leave_encash').prop('checked', false);
            $('#encahsmentlimit').hide(); //.val('');
            $('#encash_label').hide();
            $('#encahsment_container').css("display", "none");
             $('#dynamicPeriod').hide();
            
        }
         else if ($('#leave_policy_type').val() == 'H') {
 $('#leave_cycle_start_date')
        .prop("readonly", false)
        .val($('#half_start_date').val())
        .prop("readonly", true);

    $('#leave_cycle_end_date')
        .prop("readonly", false)
        .val($('#half_end_date').val())
        .prop("readonly", true);
            
            //edited by megha on 29_05_19 sandwitch leave option display on montlhy list
            $('#checkContainer').show();
            $('#sandwitchContainer').show();
            $('#NEGETIVEContainer').show();
            $('#leave_encashContainer').show();
            $('#encahsment_container').hide();
            $('#encahsment_container1').hide();
             $('#Customstart').show();
             $('#Customend').show();
            //$('[name="alloted_leave_forthe_year"]').val('0');
            //$('[name="alloted_leave_forthe_month"]').val('0');
            $('[name="alloted_leave_forthe_year"]').attr("required", true);
            $('[name="alloted_leave_forthe_month"]').attr("required", false);
            $('[name="dynamic_period"]').attr("required", false);
            
            $('#monthballimit').hide();
            $('#yearContainer').show();
            $('#yearLabel').show();
             $('#yearLabel1').hide();
            // $('#limit').show();

            // $('#IS_SANDWICH').prop('checked', false); 
            // $('#ALLOW_NEGETIVE').prop('checked', false);
            $('#is_leave_encash').prop('checked', false);
            $('#encahsmentlimit').hide(); //.val('');
            $('#encash_label').hide();
            $('#encahsment_container').css("display", "none");
             $('#dynamicPeriod').hide();
            
        }
         else {
          
            
            //edited by megha on 29_05_19 sandwitch leave option display on montlhy list

            $('#checkContainer').show();
            $('#sandwitchContainer').show();
            $('#NEGETIVEContainer').hide();
            $('#leave_encashContainer').show();
            $('#encahsment_container').hide();
            $('#encahsment_container1').hide();
             $('#Customstart').show();
             $('#Customend').show();
            $('[name="alloted_leave_forthe_year"]').val('0');
            // $('[name="alloted_leave_forthe_month"]').val('0');
            $('[name="alloted_leave_forthe_year"]').attr("required", false);
            $('[name="alloted_leave_forthe_month"]').attr("required", true);
            $('[name="dynamic_period"]').attr("required", false);
            $('#monthballimit').show();
            $('#yearContainer').hide();
            $('#yearLabel').show();
            $('#yearLabel1').show();
            // $('#IS_SANDWICH').prop('checked', false); 
            // $('#ALLOW_NEGETIVE').prop('checked', false);
            $('#is_leave_encash').prop('checked', false);
            $('#encahsmentlimit').hide(); //.val('');
            $('#encash_label').hide();
            $('#encahsment_container').css("display", "none");
             $('#dynamicPeriod').hide();
             
              $('#leave_cycle_start_date')
    .val($('#present_start').val())
    .prop("readonly", false);

$('#leave_cycle_end_date')
    .val($('#present_end').val())
    .prop("readonly", true);

           $('#leave_cycle_start_date').off('change').on('change', function () {
    let date = new Date(this.value);
    if (!isNaN(date.getTime())) {
        if (date.getDate() !== 1) {
            alert("Please select the 1st day of the month only.");
        }
        // Force day to 1
        date.setDate(1);
        let yyyy = date.getFullYear();
        let mm = String(date.getMonth() + 1).padStart(2, '0');
        let dd = '01';
        let startStr = `${yyyy}-${mm}-${dd}`;
        $(this).val(startStr);

        // End date = Dec 31 of same year if Jan 1 start
        let end = new Date(date);
        end.setFullYear(end.getFullYear() + 1);
        end.setDate(0); // last day of previous month => Dec 31 if Jan 1 start
        let endStr = `${end.getFullYear()}-${String(end.getMonth() + 1).padStart(2, '0')}-${String(end.getDate()).padStart(2, '0')}`;
        $('#leave_cycle_end_date').val(endStr);
    }
});            
        }
        handleCOFFLimit();
    }

    

    $(document).ready(function() {
        
        $('[data-toggle="tooltip"]').tooltip();
        change_leave_type();
        <?php
        $exp = isset($data['exceptions']) ? $data['exceptions'] : 'N';
        if ($exp == "Y") { ?>
            $('#min_max_leave_container').show();

        <?php } else { ?>
            $('#min_max_leave_container').hide();
        <?php } ?>
        <?php if (isset($data['is_leave_encash']) and $data['is_leave_encash'] == "Y") { ?>
            $('#encash_label').show();
            $('#encahsmentlimit').show();
            $('#encahsment_container').css("display", "block");
            $('#encahsment_container1').css("display", "block");
            $('#is_leave_encash').prop("checked", true);
        <?php
        } else {
        ?>
            $('#encahsmentlimit').hide();
            $('#encash_label').hide();
            $('#encahsment_container').css("display", "none");
            $('#encahsment_container1').css("display", "none");
            $('#is_leave_encash').prop("checked", false);
        <?php } ?>


        $('#is_leave_encash').click(function() {
            if ($(this).prop("checked") == true) {
                $('#encahsmentlimit').show();
                $('#encash_label').show();
                $('#encahsment_container').css("display", "block");
                $('#encahsment_container1').css("display", "block");

            } else if ($(this).prop("checked") == false) {
                $('#encahsmentlimit').hide().val('');
                $('#encash_label').hide();
                $('#encahsment_container').css("display", "none");
                $('#encahsment_container1').css("display", "none");
            }
        });

        $('#Exceptions').click(function() {
            if ($(this).prop("checked") == true) {
                $('#min_leave_label').show();
                $('#max_leave_label').show();
                $('#min_service_label').show();
                $('#min_max_leave_container').css("display", "block");
                // $('#NEGETIVEContainer').hide();
                //$('#ALLOW_NEGETIVE').prop('checked', true);
                //$('#NEGETIVEContainer').show();
            } else if ($(this).prop("checked") == false) {
                $('#min_leave_label').hide().val('');
                $('#max_leave_label').hide();
                $('#min_service_label').hide();
                $('#min_max_leave_container').css("display", "none");
                //$('#ALLOW_NEGETIVE').prop('checked', false);
            }
        });
        $('#ALLOW_NEGETIVE').click(function() {
            if ($(this).prop("checked") == false) {
                // $('#Exceptions').prop('checked', false);
                $('#min_leave_label').hide().val('');
                $('#max_leave_label').hide();
                $('#min_service_label').hide();
                $('#min_max_leave_container').css("display", "none");
            }
        });
        // $('#lpForm').parsley();
        var options = {
            success: function(responseText, statusText, xhr, $form) {
                closeSmallModalForm('leavepolicytable');
            }
        };

        // bind to the form's submit event 
        $('#lpForm').submit(function() {
            // inside event callbacks 'this' is the DOM element so we first 
            // wrap it in a jQuery object and then invoke ajaxSubmit 
            $(this).ajaxSubmit(options);

            // !!! Important !!! 
            // always return false to prevent standard browser submit and page navigation 
            return false;
        });
    });

    
</script>