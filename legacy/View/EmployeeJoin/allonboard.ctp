  <!-- <div id="tab3" class="tabcontent" style="display: none;"> -->

  <form class="form-horizontal" id="empsetuppersonal" style="margin-top:-25px;">
      <h3>Company Informations</h3> <input id="model" name="model" type="hidden" value="EmployeeProfessionalDetails"> <input id="emp_proff_pkey" name="emp_proff_pkey" type="hidden" value=""> <input type="hidden" name="emp_fkey" value='<?php echo $emp_pkey ?>' />
      <input type="hidden" name="user_group" id="user_group" value="<?php echo $user_group; ?>">


      <div class="onboarding-grid">
          <div class="form-group">


              <label for="joining_date">Joining Date &nbsp;<span style="color:red;">*</span></label>

              <input id="joining_date" required name="joining_date" required value="<?php 
        echo (isset($arr_professionalinfo['EmployeeProfessionalDetails']['joining_date']) && $arr_professionalinfo['EmployeeProfessionalDetails']['joining_date'] != '') 
        ? $arr_professionalinfo['EmployeeProfessionalDetails']['joining_date'] 
        : ''; 
    ?>" type="text" placeholder="(YYYY-MM-DD)" class="form-control input-md" required="" onchange="updateStartDate()" autocomplete="off">

          </div>
          <div class="form-group">
              <label for="emp_company_id">Employee ID</label>
              <input id="emp_company_id" name="emp_company_id" value="<?php echo isset($arr_professionalinfo['EmployeeProfessionalDetails']["emp_company_id"]) ? $arr_professionalinfo['EmployeeProfessionalDetails']["emp_company_id"] : ''; ?>" type="text" placeholder="Leave as blanks if no ID" class="form-control input-md">
          </div>

          <div class="form-group">
              <label for="emp_branch">Branch &nbsp;<span style="color:red;">*</span></label>
              <select id="emp_branch" required="required" name="emp_branch" class="form-control js-example-basic-single select2" style="width: 100%">
                  <option value="">--Select--</option>
                  <?php
                    foreach ($arr_branches as $key => $value) {
                        $selected = '';
                        if (!empty($arr_professionalinfo['EmployeeProfessionalDetails']['emp_branch'])) {
                            if ($arr_professionalinfo['EmployeeProfessionalDetails']['emp_branch'] == $value['branch_code']) {
                                $selected = 'selected';
                            }
                        }
                        echo '<option value="' . h($value['branch_code']) . '" ' . $selected . '>'
                            . h($value['branch_name']) . '</option>';
                    }
                    ?>


              </select>
          </div>

          <div class="form-group">

        
              <label for="emp_dept">Department &nbsp;
                  <span style="color:red;">*</span></label>

              <select id="emp_dept" required name="emp_dept" class="form-control js-example-basic-single select2" style="width: 100%" required="">
                  <option value="">--Select--</option>
                  <?php
                    foreach ($arr_departments as $key => $value) {
                        $selected = ($arr_professionalinfo['EmployeeProfessionalDetails']['emp_dept'] == $value['dept_code']) ? 'selected="selected"' : '';
                        echo '<option value="' . $value['dept_code'] . '" ' . $selected . '>' . $value['dept_name'] . '</option>';
                    }
                    ?>
              </select>

          </div>
          <div class="form-group">
              <label for="designation">Designation &nbsp;<span style="color:red;">*</span></label>
              <select id="designation" name="designation" required class="form-control js-example-basic-single select2" style="width: 100%" required="">
                  <option value="">--Select--</option>
                  <?php
                    foreach ($arr_designations as $key => $value) {
                        $selected = ($arr_professionalinfo['EmployeeProfessionalDetails']['designation'] == $value['desig_code']) ? 'selected="selected"' : '';
                        echo '<option value="' . $value['desig_code'] . '" ' . $selected . '>' . $value['desig_name'] . '</option>';
                    }
                    ?>
              </select>
          </div>

          <div class="form-group">

              <label for="emp_type">Employee Type &nbsp;<span style="color:red;">*</span></label>

              <select id="emp_type" name="emp_type" required class="form-control js-example-basic-single select2" style="width: 100%" required="">
                <!-- onchange="handleEmployeeTypeChange()" -->
                  <option value="">--Select--</option>
                  <option value="Permanent" <?php echo (isset($arr_professionalinfo['EmployeeProfessionalDetails']["emp_type"]) && $arr_professionalinfo['EmployeeProfessionalDetails']["emp_type"] == 'Permanent') ? 'selected="selected"' : ''; ?>>Permanent</option>
                  <option value="Contract" <?php echo (isset($arr_professionalinfo['EmployeeProfessionalDetails']["emp_type"]) && $arr_professionalinfo['EmployeeProfessionalDetails']["emp_type"] == 'Contract') ? 'selected="selected"' : ''; ?>>Contract</option>
                  <option value="Probation" <?php echo (isset($arr_professionalinfo['EmployeeProfessionalDetails']["emp_type"]) && $arr_professionalinfo['EmployeeProfessionalDetails']["emp_type"] == 'Probation') ? 'selected="selected"' : ''; ?>>Probation</option>
                  <option value="Part-Time" <?php echo (isset($arr_professionalinfo['EmployeeProfessionalDetails']["emp_type"]) && $arr_professionalinfo['EmployeeProfessionalDetails']["emp_type"] == 'Part-Time') ? 'selected="selected"' : ''; ?>>Part-Time</option>
                  <option value="Temporary" <?php echo (isset($arr_professionalinfo['EmployeeProfessionalDetails']["emp_type"]) && $arr_professionalinfo['EmployeeProfessionalDetails']["emp_type"] == 'Temporary') ? 'selected="selected"' : ''; ?>>Temporary</option>
                  <option value="Consultant" <?php echo (isset($arr_professionalinfo['EmployeeProfessionalDetails']["emp_type"]) && $arr_professionalinfo['EmployeeProfessionalDetails']["emp_type"] == 'Consultant') ? 'selected="selected"' : ''; ?>>Consultant</option>
                  <option value="DAILY WAGES" <?php echo (isset($arr_professionalinfo['EmployeeProfessionalDetails']["emp_type"]) && $arr_professionalinfo['EmployeeProfessionalDetails']["emp_type"] == 'DAILY WAGES') ? 'selected="selected"' : ''; ?>>Daily Wages</option>
                  <option value="HOURLY WAGES" <?php echo (isset($arr_professionalinfo['EmployeeProfessionalDetails']["emp_type"]) && $arr_professionalinfo['EmployeeProfessionalDetails']["emp_type"] == 'HOURLY WAGES') ? 'selected="selected"' : ''; ?>>Hourly Wages</option>
                  <option value="Provisional" <?php echo (isset($arr_professionalinfo['EmployeeProfessionalDetails']["emp_type"]) && $arr_professionalinfo['EmployeeProfessionalDetails']["emp_type"] == 'Provisional') ? 'selected="selected"' : ''; ?>>Provisional</option>
                  <option value="Deputation" <?php echo (isset($arr_professionalinfo['EmployeeProfessionalDetails']["emp_type"]) && $arr_professionalinfo['EmployeeProfessionalDetails']["emp_type"] == 'Deputation') ? 'selected="selected"' : ''; ?>>Deputation</option>
                  <option value="other" <?php echo (isset($arr_professionalinfo['EmployeeProfessionalDetails']["emp_type"]) && $arr_professionalinfo['EmployeeProfessionalDetails']["emp_type"] == 'other') ? 'selected="selected"' : ''; ?>>Other Type</option>
              </select>

          </div>
          <div class="form-group">
              <label for="notice_days">Notice Period &nbsp;<span style="color:red;">*</span></label>

              <select id="notice_days" name="notice_days" required class="form-control js-example-basic-single select2" style="width: 100%" required="">
                  <option value="">--Select--</option>

                  <?php
                    foreach ($notice_days as $key => $value) {
                        $selected = ($arr_professionalinfo['EmployeeProfessionalDetails']['notice_days'] == $value['NoticePeriod']['notice_days']) ? 'selected="selected"' : '';
                        echo '<option value="' . $value['NoticePeriod']['notice_days'] . '" ' . $selected . '>' . $value['NoticePeriod']['notice_days'] . '</option>';
                    }
                    ?>
              </select>
          </div>




          <div class="form-group">
              <div id="gradeContainer" class="">
                  <label for="emp_grade">Grade</label>
                  <select id="emp_grade" name="emp_grade" class="form-control js-example-basic-single select2" style="width: 100%">
                      <option value="">--Select--</option>
                      <?php
                        foreach ($arr_grades as $key => $value) {
                            $selected = ($arr_professionalinfo['EmployeeProfessionalDetails']['emp_grade'] == $value['grade_pkey']) ? 'selected="selected"' : '';
                            echo '<option value="' . $value['grade_pkey'] . '" ' . $selected . '>' . $value['grade_name'] . '</option>';
                        }
                        ?>
                  </select>
              </div>
          </div>





          <div id="probationDaysContainer" class="form-group"
              style="<?php echo (isset($arr_professionalinfo['EmployeeProfessionalDetails']['emp_type']) && $arr_professionalinfo['EmployeeProfessionalDetails']['emp_type'] === 'Probation') ? 'display:block;' : 'display:none;'; ?>">

              <label for="emp_company_id">Probation Days</label>
              <input id="attr2" name="probation" value="<?php echo isset($arr_professionalinfo['EmployeeProfessionalDetails']["probation"]) ? $arr_professionalinfo['EmployeeProfessionalDetails']["probation"] : ''; ?>" type="text" placeholder="Probatoin Days" class="form-control input-md"  onkeypress="return /[0-9.]/.test(event.key)">
          </div>
          <!-- Edited by Akshay on 11/7/2023 -->
          <div id="contractPeriodContainer" class="form-group"
              style="<?php echo (isset($arr_professionalinfo['EmployeeProfessionalDetails']["emp_type"]) && $arr_professionalinfo['EmployeeProfessionalDetails']["emp_type"] === 'Contract')
                            ? 'display:block;'
                            : 'display:none;'; ?>">

              <label for="emp_company_id">Contract Period&nbsp;<span style="color:red;">*</span></label>

              <input id="start_date" name="start_date" value="<?php echo (isset($arr_professionalinfo['EmployeeProfessionalDetails']["joining_date"]) && $arr_professionalinfo['EmployeeProfessionalDetails']["joining_date"] != '') ? $arr_professionalinfo['EmployeeProfessionalDetails']["joining_date"] : ''; ?>" type="text" placeholder="Enter Joining Date" class="form-control input-md" style="">
              <br>
              <input id="end_date" name="end_date" value="<?php echo (isset($arr_contract[0]['contracted_days']['contract_end_date']) && $arr_contract[0]['contracted_days']['contract_end_date'] != '') ? $arr_contract[0]['contracted_days']['contract_end_date'] : ''; ?>" type="text" placeholder="(YYYY-MM-DD)" class="form-control input-md datepicker" style=" " autocomplete="off">
               <!-- <?php
$endDate = isset($contractData[0]['contracted_days']['contract_end_date']) 
              ? $contractData[0]['contracted_days']['contract_end_date'] 
              : '';
?>
               <input id="end_date" name="end_date" 
       value="<?php echo h($endDate); ?>" 
       type="text" placeholder="(YYYY-MM-DD)" 
       class="form-control input-md datepicker" autocomplete="off"> -->

          </div>

          <!-- Edited by Akshay on 10-10-2023 --><!--edited by sinsiya on 11-06-2024 remove the condition $user === 'DEMO'-->
       <!-- <?php if ($user === 'DEMO' || $user === 'KWMT') { ?>
<div id="outerCategoryContainer">

   <div class="form-group" id="categoryContainer">
        <label for="emp_category">Category &nbsp;<span style="color:red;">*</span></label>
        <select id="emp_category" name="emp_category" class="form-control js-example-basic-single" style="width: 100%">
            <option value="">--Select--</option>
            <?php
            foreach ($arr_category as $value) {
                // Use correspondingCategoryId for selection if emp_category is empty
                $selected = (isset($correspondingCategoryId) && $correspondingCategoryId == $value['category']['category_pkey'])
                            ? 'selected="selected"'
                            : '';
                echo '<option value="' . $value['category']['category_pkey'] . '" ' . $selected . '>'
                     . $value['category']['category_name'] .
                     '</option>';
            }
            ?>
        </select>
    </div>

    <div class="form-group" id="grade_container">
        <label for="grade_dropdown">Grade &nbsp;<span style="color:red;">*</span></label>
        <select id="grade_dropdown" name="emp_grade2" class="form-control js-example-basic-single" style="width: 100%">
            <option value=''>--Select Grade--</option>
            <?php
            foreach ($arr_grades as $value) {
                $selected = ($arr_professionalinfo['EmployeeProfessionalDetails']['emp_grade'] == $value['grade_pkey']) ? 'selected="selected"' : '';
                echo '<option value="' . $value['grade_pkey'] . '" ' . $selected . '>' . $value['grade_name'] . '</option>';
            }
            ?>
        </select>
    </div>

</div>
<?php } ?> -->

          <?php if ($user === 'VGFS' || $user === 'vgfs' || $user == 'VSFS' || $user == 'vsfs') { ?>
              <div class="form-group">

                  <label for="designation">Area &nbsp;<span style="color:red;">*</span></label>

                  <input id="attr4" name="attr4" value="<?php echo isset($arr_personalinfo["attr4"]) ? $arr_personalinfo["attr4"] : ''; ?>" type="text" placeholder="Area" class="form-control disabled input-md">

              </div>

          <?php } ?>
          <div class="button-wrapper">
              <input type="submit" value="Save" id="save-btn" class="save-btn"
              style="border-radius: 10px;padding: 6px 20px;border: none;float:inline-end;"/>
          </div>
      </div>

  </form>
  <div>
      <h3 style="margin-top:30px;">Policies & Rules</h3>
      <form class="form-horizontal" method="post"
          action="<?php echo $this->webroot; ?>EmployeeJoin/editconfig"
          id="empsetupconfig">
          <input type="hidden" name="emp_fkey" value="<?php echo $emp_pkey ?>" />
          <div class="employee_config" >
              <!-- Shift Timings -->

              <div class="form-group" style="margin-left:0px;margin-right:0px;">
                  <label for="shift" class="label-congif">Shift Timings</label>
                  <select id="shift" name="shift" class="form-control js-example-basic-single select2" required style="width: 100%;">
                      <?php foreach ($arr_shifts as $values) { ?>
                          <option <?php echo (isset($arr_professionalinfo['EmployeeProfessionalDetails']['day_time_seq']) && $arr_professionalinfo['EmployeeProfessionalDetails']['day_time_seq'] == $values['working_day_time_procedures']['day_time_seq']) ? 'selected="selected"' : ''; ?>
                              value="<?php echo $values['working_day_time_procedures']['day_time_seq']; ?>">
                              <?php echo $values['working_day_time_procedures']['day_time_desc']; ?>
                          </option>
                      <?php } ?>
                  </select>
              </div>

              <!-- Holiday Calendar -->
              <div class="form-group" style="margin-left:0px;margin-right:0px;">
                  <label for="holidays" class="label-congif">Holiday Calendar</label>
                  <select id="holidays" name="holidays" class="form-control js-example-basic-single select2" required style="width: 100%;">
                      <?php foreach ($arr_holidays as $values) { ?>
                          <option <?php echo (isset($arr_professionalinfo['EmployeeProfessionalDetails']['HOLIDAY_GROUP_ID']) && $arr_professionalinfo['EmployeeProfessionalDetails']['HOLIDAY_GROUP_ID'] == $values['holiday_group']['HOLIDAY_GROUP_ID']) ? 'selected="selected"' : ''; ?>
                              value="<?php echo $values['holiday_group']['HOLIDAY_GROUP_ID']; ?>">
                              <?php echo $values['holiday_group']['HOLIDAY_GROUP_NAME']; ?>
                          </option>
                      <?php } ?>
                  </select>
              </div>

              <!-- Leave Policy -->
              <div class="form-group" style="margin-left:0px;margin-right:0px;">
                  <label for="leave" class="label-congif">Leave Policy</label>
                  <select id="leave" name="leave" class="form-control js-example-basic-single select2" required style="width: 100%;">
                      <?php foreach ($arr_leaves as $values) { ?>
                          <option <?php echo (isset($arr_professionalinfo['EmployeeProfessionalDetails']['LEAVEPOLICY_GROUP_ID']) && $arr_professionalinfo['EmployeeProfessionalDetails']['LEAVEPOLICY_GROUP_ID'] == $values['leavepolicy_group']['LEAVEPOLICY_GROUP_ID']) ? 'selected="selected"' : ''; ?>
                              value="<?php echo $values['leavepolicy_group']['LEAVEPOLICY_GROUP_ID']; ?>">
                              <?php echo $values['leavepolicy_group']['LEAVEPOLICY_GROUP_NAME']; ?>
                          </option>
                      <?php } ?>
                  </select>
              </div>
              <!-- Superior -->
              <!-- <div class="form-group" style="margin-left:0px;margin-right:0px;">
                  <label for="hierarch" class="label-congif">Superior</label>
                  <input type="text" placeholder="Superior" value="<?php echo isset($arr_professionalinfo['EmployeeProfessionalDetails']['attr2']) 
                        ? $arr_professionalinfo['EmployeeProfessionalDetails']['attr2'] 
                        : ''; ?>" id="hierarch" name="hierarch1" class="form-control strict-field" style="width:205px; ">
                  <input type="hidden" id="hierarch1" name="hierarch" class="form-control strict-field" value="<?php echo isset($arr_professionalinfo['EmployeeProfessionalDetails']['attr2']) 
                        ? $arr_professionalinfo['EmployeeProfessionalDetails']['attr2'] 
                        : ''; ?>" style="width:234px; "style="width: 100%;">
              </div> -->
           <div class="form-group" style="margin-left:0px;margin-right:0px;">
    <label for="hierarch" class="label-congif">Superior</label>

    <input
        type="text"
        id="hierarch"
        name="hierarch1"
        class="form-control strict-field"
        style="width:205px;"
        placeholder="Superior"
        value="<?php echo h($superiorText); ?>"
    >

    <input
        type="hidden"
        id="hierarch1"
        name="hierarch"
        value="<?php echo h($superiorEmpKey); ?>"
    >
</div>

          </div>

      <div style="padding-bottom:40px;margin-top:20px;">
              <input type="submit" value="Save" class="custom-button" id="saveconfig"
                  style="border-radius: 10px;padding: 6px 20px;border: none;float:inline-end;margin-right:25px;" />
          </div>
          
          </form>
      
  </div>
  </div>



  <script>

  
      $(document).ready(function() {
          $('#empsetupconfig').on('submit', function(e) {
              e.preventDefault(); 

               const isDisabled = $('#empsetupconfig :input:not(:button):not([type="submit"]):not([type="reset"])').is(':disabled');

     
        if (isDisabled) {
            var user_group = $("#user_group").val(); 

   
            $('#largeModalForm').modal('hide');
$('#emptable2').datagrid('reload');
             if (user_group == "2"){
       location.reload();
    }
            // setTimeout(() => {
        
    // }, 200);
            return; // Stop execution — don't save
        }
//  const allDisabled = $('#empsetupconfig :input')
//             .not(':button, [type="submit"], [type="reset"]')
//             .filter(':enabled').length === 0;

//         // ✔ If form not editable → close modal
//         if (allDisabled) {
//             $('#largeModalForm').modal('hide');
//             return;
//         }

              var formData = $(this).serialize();

              // Show loading
              $("#container").isLoading({
                  text: "Loading",
                  position: "overlay"
              });

              $.post('<?php echo $this->webroot; ?>EmployeeJoin/editconfig', formData, function(response) {
                  // Parse JSON response
                  var res = typeof response === 'string' ? JSON.parse(response) : response;

                  if (res.success) {
                   
                          $.notify(res.message || 'configuration saved successfully', {
                              type: 'success',
                              z_index: 99999
                          });
                     
                      $('#largeModalForm').modal('hide');
                      // Load new page into #container instead of full redirect
                      var url = '<?php echo $this->webroot; ?>EmployeeJoin/index';
                      $("#container").load(url, function() {
                          $("#container").isLoading("hide"); // hide loading when done
                      });
                  } else {
                      $("#container").isLoading("hide");
                      $('#emptable2').datagrid('reload'); // hide loading on error
                      alert(res.message || 'Something went wrong');
                  }
              });
          });
      });


      $(document).ready(function() {
          $('#empsetuppersonal').on('submit', function(e) {
              e.preventDefault();

              let formData = new FormData(this);
              let $btn = $('#save-btn');

              $btn.prop('disabled', true).val('Saving...');

              $.ajax({
                  url: '<?php echo $this->webroot; ?>EmployeeJoin/saveAllOnboard',
                  type: 'POST',
                  data: formData,
                  contentType: false,
                  processData: false,
                  dataType: 'json', 
                  success: function(res) {
                      if (res.success) {
                          $.notify(res.message || 'Company details saved successfully ', {
                              type: 'success',
                              z_index: 99999
                          });
                      } else {
                          $.notify(res.message || 'Something went wrong ', {
                              type: 'danger',
                              z_index: 99999
                          });
                      }
                  },
                  error: function(xhr) {
                      console.error("XHR error:", xhr.responseText);
                      $.notify('Server error. Please try again.', {
                          type: 'danger',
                          z_index: 99999
                      });
                  },
                  complete: function() {
                      $btn.prop('disabled', false).val('Save');
                  }
              });
          });
      });



      $(document).ready(function() {
       
          $('#joining_date').datepicker({
              format: 'yyyy-mm-dd',
              autoclose: true,
              <?php if ($user == 'VGFS' || $user == 'vgfs' || $user == 'VSFS' || $user == 'vsfs') { ?>
                  endDate: '+4d',
                  startDate: '-4d'
              <?php } ?>
          });

          $("#end_date").datepicker({
              format: 'yyyy-mm-dd',
              autoclose: true
          });

          $('#date_of_birth_nominee').datepicker({
              format: 'yyyy-mm-dd',
              autoclose: true
          });

        
          $('#annual_gross').change(function() {
              $('#salary').val('');
          });

          $('#salary').change(function() {
              var salary_structure_id = $(this).val();
              var annual_gross = $('#annual_gross').val();
              var monthly_salary = Math.round(annual_gross / 12, 1);
              var selected = $(this).find('option:selected');
              var extra = parseInt(selected.data('foo'));
              var type = $('#emp_type').val();

              if (type == 'DAILY WAGES' || type == 'HOURLY WAGES') {
                  monthly_salary = Math.round(annual_gross, 1);
              }

              if (annual_gross == '0') {
                  alert("Please Enter the Annual Gross Salary First ");
                  $('#annual_gross').focus();
                  $(this).val('');
              } else if (monthly_salary < extra) {
                  alert("Cannot allocate this salary structure. Please choose another");
                  $(this).val('');
                  return false;
              }
          });


          function handleEmployeeTypeChange() {
              var empType = $("#emp_type").val();

        
              var probationDays = $("#probationDaysContainer");
              var contractPeriod = $("#contractPeriodContainer");
              var categoryContainer = $("#categoryContainer");
              var gradeContainer = $("#gradeContainer");

              probationDays.hide();
              contractPeriod.hide();
              categoryContainer.hide();
              gradeContainer.hide();

              if (empType === "Permanent") {
                  categoryContainer.show();
                  gradeContainer.show();
                  $("#emp_category").prop("required", true).select2({
                      dropdownParent: $('#largeModalForm')
                  });
              } else if (empType === "Contract") {
                  contractPeriod.show();
                  $("#emp_category").prop("required", false);
              } else if (empType === "Probation") {
                  probationDays.show();
                  $("#emp_category").prop("required", false);
              } else {
                  $("#emp_category").prop("required", false);
              }
          }

     
          function loadGradesForCategory(categoryId, empType) {
              $.ajax({
                  type: "POST",
                  url: livesite + "Employee/getGrade/" + categoryId,
                  success: function(response) {
                      var data = JSON.parse(response);

                      var newDropdown = $("<select id='grade_dropdown' name='emp_grade2' " +
                          "class='form-control select2' style='width: 100%'></select>");

                      if (empType === "Permanent") {
                          newDropdown.prop("required", true);
                      }

                      newDropdown.append("<option value=''>--Select Grade--</option>");

                      $.each(data, function(index, item) {
                          var grade = item.grade;
                          var option = $("<option value='" + grade.grade_pkey + "'>" + grade.grade_name + "</option>");
                          newDropdown.append(option);
                      });

                      $("#grade_container").html(newDropdown);
                      newDropdown.select2({
                          dropdownParent: $('#largeModalForm')
                      });

                      // Pay scale display
                      $("#grade_dropdown").on("change", function() {
                          var selectedValue = $(this).val();
                          var selectedGrade = data.find(function(item) {
                              return item.grade.grade_pkey == selectedValue;
                          });

                          if (selectedGrade && selectedGrade.grade.pay_scale) {
                              $("#showPayScale").show();
                              $("#pay_scale").text(selectedGrade.grade.pay_scale).show();
                          } else {
                              $("#showPayScale").hide();
                              $("#pay_scale").text("").hide();
                          }
                      });
                  }
              });
          }

       
          handleEmployeeTypeChange(); // Run on load
          $("#emp_type").on("change", handleEmployeeTypeChange);

          $("#emp_category").on("change", function() {
              var empType = $("#emp_type").val();
              var categoryId = $(this).val();
              if (categoryId) {
                  loadGradesForCategory(categoryId, empType);
              }
          });

       
            $(".select2").select2({
                dropdownParent: $('#largeModalForm'),
                minimumResultsForSearch: 0,
                closeOnSelect: true, 
                allowClear: true,
                dropdownPosition: 'below'
            }).on('select2:opening select2:closing', function(e) {
                var $searchfield = $(this).parent().find('.select2-search__field');
                setTimeout(function() {
                    $searchfield.focus();
                }, 0);
            });



          var usershierarchyoptions = {
              url: function(phrase) {
                  var emp = $('#empsetupconfig #emp_pkey').val();
                  return livesite + "Employee/getautohierarchycompletions?username=" + phrase + "&emp=" + emp;
              },
              getValue: "emp_name",
              list: {
                  onClickEvent: function() {
                      
                      filterAttendanceautocomplete($('#hid_filterby_employees').val());
                  },
                  onKeyEnterEvent: function() {
                      filterAttendanceautocomplete($('#hid_filterby_employees').val());
                  },
                  onSelectItemEvent: function() {
                      var selectedItem = $('#hierarch').getSelectedItemData();
                      var site_pkey = selectedItem.emp_pkey;
                      $('#hierarch1').val(site_pkey);
                  }
              }
          };

          $('#hierarch').easyAutocomplete(usershierarchyoptions);
        
          var empsetuppersonaloptions = {
              success: function(responseText) {
                  $('#empsetuppersonal').find("button[type='submit']").prop('disabled', false).html('Next');
                  var response = JSON.parse(responseText);

                  if (response.success) {
                      $('#config').addClass('active');
                      $('#div-empsetupconfig').addClass("active in");
                      $('#div-empsetupprofessional, #div-empsetuppersonal').removeClass("active in");
                      $('#empsetup-save-response').removeClass('alert-danger').addClass('alert-success')
                          .html('<strong>Success!</strong> ' + response.message).fadeIn().fadeOut(3000);

                      $('#empsetuppersonal #emp_pkey').val(response.pkey);
                      $('#empsetupprofessional #emp_fkey').val(response.pkey);
                      $('#empsetupconfig #emp_fkey').val(response.pkey);
                      $('#empsetuptaxation #emp_pkey').val(response.pkey);
                      $('#emp_companyd').html(response.name + ' - ' + response.emp_companyd);
                      reloadTable('emptable');
                  } else {
                      $('#empsetup-save-response').removeClass('alert-success').addClass('alert-danger')
                          .html('<strong>Failed!</strong> ' + response.message).fadeIn().fadeOut(3000);
                  }
              }
          };

          $('#empsetuppersonal').submit(function(event) {
              var empType = $('#emp_type').val();
              if (empType == 'Contract') {
                  event.preventDefault();
                  var startDate = new Date($('#start_date').val());
                  var endDate = new Date($('#end_date').val());
                  if (!$('#end_date').val() || isNaN(endDate)) {
                      alert('Contract period end date is required');
                      return;
                  }
                  if (startDate > endDate) {
                      alert('Start date must be less than end date');
                       $('#end_date').val(''); 
                      return;
                  }
              }
              $(this).find("button[type='submit']")
                  .html('<li class="fa fa-spinner fa-spin"></li> saving...')
                  .attr('disabled', 'disabled');
              $(this).ajaxSubmit(empsetuppersonaloptions);
              return false;
          });



          var eeoptions = {
              success: function() {
                  $('#empsetuptaxform').find("button[type='submit']").html('Next').prop('disabled', false);
              }
          };

          $('#empsetuptaxform').submit(function() {
              $(this).ajaxSubmit(eeoptions);
              return false;
          });
      });
     $(document).ready(function() {
    var emp_fkey = $("input[name='emp_fkey']").val();
    var user_group = $("#user_group").val(); // get from hidden input

    if (user_group == "2" && emp_fkey != "0" && emp_fkey !== "") {
        $("#empsetuppersonal :input").prop("disabled", true);
        $("#empsetupconfig :input:not(:button):not([type='submit']):not([type='reset'])").prop("disabled", true);
    }
});

  </script>
 