  <!-- <div id="tab3" class="tabcontent" style="display: none;"> -->

  <form class="form-horizontal" id="empsetuppersonal" style="margin-top:-25px;">
      <h3>Company Informations</h3> <input id="model" name="model" type="hidden" value="EmployeeProfessionalDetails">
      <input id="emp_proff_pkey" name="emp_proff_pkey" type="hidden" value="<?php echo isset($arr_professionalinfo['emp_proff_pkey']) ? $arr_professionalinfo['emp_proff_pkey'] : ''; ?>">
      <input id="emp_pkey" name="emp_pkey" type="hidden" value="<?php echo isset($arr_professionalinfo['emp_pkey']) ? $arr_professionalinfo['emp_pkey'] : '0'; ?>">
      <input id="emp_fkey" type="hidden" name="emp_fkey" value="<?php echo $emp_pkey ?>" />
      <div class="onboarding-grid">
          <div class="form-group">


              <div class="form-group">
                  <label for="joining_date">Joining Date &nbsp;<span style="color:red;">*</span></label>
                  <input id="joining_date" name="joining_date" type="text" placeholder="(YYYY-MM-DD)"
                      class="form-control" autocomplete="off" required>
              </div>

          </div>
          <div class="form-group">
              <label for="emp_company_id">Employee ID</label>
              <input id="emp_company_id" name="emp_company_id" value="<?php echo isset($arr_professionalinfo["emp_company_id"]) ? $arr_professionalinfo["emp_company_id"] : ''; ?>" type="text" placeholder="Leave as blanks if no ID" class="form-control input-md">
          </div>

          <div class="form-group">
              <label for="emp_branch">Branch &nbsp;<span style="color:red;">*</span></label>
              <select id="emp_branch" required="required" name="emp_branch" class="form-control js-example-basic-single select2" style="width: 100%">
                  <option value="">--Select--</option>
                  <?php

                    foreach ($arr_branches as $key => $value) {
                        $selected = ($arr_personalinfo['branch_code'] == $value['branch_code']) ? 'selected' : '';
                        echo '<option value="' . $value['branch_code'] . '" ' . $selected . '>' . $value['branch_name'] . '</option>';
                    }
                    ?>
              </select>
          </div>

          <div class="form-group">

              <!-- <li onclick="opendepartment();" class="fa fa-plus" style="cursor: pointer;"></li> -->
              <label for="emp_dept">Department &nbsp;
                  <span style="color:red;">*</span></label>

              <select id="emp_dept" required name="emp_dept" class="form-control js-example-basic-single select2" style="width: 100%" required="">
                  <option value="">--Select--</option>
                  <?php
                    foreach ($arr_departments as $key => $value) {
                        $selected = ($arr_professionalinfo['emp_dept'] == $value['dept_code']) ? 'selected="selected"' : '';
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
                        $selected = ($arr_professionalinfo['designation'] == $value['desig_code']) ? 'selected="selected"' : '';
                        echo '<option value="' . $value['desig_code'] . '" ' . $selected . '>' . $value['desig_name'] . '</option>';
                    }
                    ?>
              </select>
          </div>

          <div class="form-group">

              <label for="emp_type">Employee Type &nbsp;<span style="color:red;">*</span></label>

              <select id="emp_type" name="emp_type" required class="form-control js-example-basic-single select2" style="width: 100%" required="">
                  <option value="">--Select--</option>
                  <option value="Permanent" <?php echo (isset($arr_professionalinfo["emp_type"]) && $arr_professionalinfo["emp_type"] == 'Permanent') ? 'selected="selected"' : ''; ?>>Permanent</option>
                  <option value="Contract" <?php echo (isset($arr_professionalinfo["emp_type"]) && $arr_professionalinfo["emp_type"] == 'Contract') ? 'selected="selected"' : ''; ?>>Contract</option>
                  <option value="Probation" <?php echo (isset($arr_professionalinfo["emp_type"]) && $arr_professionalinfo["emp_type"] == 'Probation') ? 'selected="selected"' : ''; ?>>Probation</option>
                  <option value="Part-Time" <?php echo (isset($arr_professionalinfo["emp_type"]) && $arr_professionalinfo["emp_type"] == 'Part-Time') ? 'selected="selected"' : ''; ?>>Part-Time</option>
                  <option value="Temporary" <?php echo (isset($arr_professionalinfo["emp_type"]) && $arr_professionalinfo["emp_type"] == 'Temporary') ? 'selected="selected"' : ''; ?>>Temporary</option>
                  <option value="Consultant" <?php echo (isset($arr_professionalinfo["emp_type"]) && $arr_professionalinfo["emp_type"] == 'Consultant') ? 'selected="selected"' : ''; ?>>Consultant</option>
                  <option value="DAILY WAGES" <?php echo (isset($arr_professionalinfo["emp_type"]) && $arr_professionalinfo["emp_type"] == 'DAILY WAGES') ? 'selected="selected"' : ''; ?>>Daily Wages</option>
                  <option value="HOURLY WAGES" <?php echo (isset($arr_professionalinfo["emp_type"]) && $arr_professionalinfo["emp_type"] == 'HOURLY WAGES') ? 'selected="selected"' : ''; ?>>Hourly Wages</option>
                  <option value="Provisional" <?php echo (isset($arr_professionalinfo["emp_type"]) && $arr_professionalinfo["emp_type"] == 'Provisional') ? 'selected="selected"' : ''; ?>>Provisional</option>
                  <option value="Deputation" <?php echo (isset($arr_professionalinfo["emp_type"]) && $arr_professionalinfo["emp_type"] == 'Deputation') ? 'selected="selected"' : ''; ?>>Deputation</option>
                  <option value="other" <?php echo (isset($arr_professionalinfo["emp_type"]) && $arr_professionalinfo["emp_type"] == 'other') ? 'selected="selected"' : ''; ?>>Other Type</option>
              </select>

          </div>
          <div class="form-group">
              <label for="notice_days">Notice Period &nbsp;<span style="color:red;">*</span></label>

              <select id="notice_days" name="notice_days" required class="form-control js-example-basic-single select2" style="width: 100%" required="">
                  <option value="">--Select--</option>

                  <?php
                    foreach ($notice_days as $key => $value) {
                        $selected = ($arr_professionalinfo['notice_days'] == $value['NoticePeriod']['notice_days']) ? 'selected="selected"' : '';
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
                            $selected = ($arr_professionalinfo['emp_grade'] == $value['grade_pkey']) ? 'selected="selected"' : '';
                            echo '<option value="' . $value['grade_pkey'] . '" ' . $selected . '>' . $value['grade_name'] . '</option>';
                        }
                        ?>
                  </select>
              </div>
          </div>





          <div id="probationDaysContainer" class="form-group"
              style="<?php echo (isset($arr_professionalinfo['emp_type']) && $arr_professionalinfo['emp_type'] === 'Probation') ? 'display:block;' : 'display:none;'; ?>">

              <label for="emp_company_id">Probation Days</label>
              <input id="attr2" name="probation" value="<?php echo isset($arr_professionalinfo["probation"]) ? $arr_professionalinfo["probation"] : ''; ?>" type="text" placeholder="Probatoin Days" class="form-control input-md" onkeypress="return /[0-9.]/.test(event.key)">
          </div>
          <!-- Edited by Akshay on 11/7/2023 -->
          <div id="contractPeriodContainer" class="form-group"
              style="<?php echo (isset($arr_professionalinfo["emp_type"]) && $arr_professionalinfo["emp_type"] === 'Contract')
                            ? 'display:block;'
                            : 'display:none;'; ?>">

              <label for="emp_company_id">Contract Period&nbsp;<span style="color:red;">*</span></label>

              <input id="start_date" name="start_date" value="<?php echo (isset($arr_professionalinfo["joining_date"]) && $arr_professionalinfo["joining_date"] != '') ? $arr_professionalinfo["joining_date"] : ''; ?>" type="text" placeholder="Enter Joining Date" class="form-control input-md" style="" readonly>
              <br>
              <input id="end_date" name="end_date" value="<?php echo (isset($arr_professionalinfo["end_date"]) && $arr_professionalinfo["end_date"] != '') ? $arr_professionalinfo["end_date"] : ''; ?>" type="text" placeholder="(YYYY-MM-DD)" class="form-control input-md datepicker" style=" " autocomplete="off">

          </div>

          <!-- Edited by Akshay on 10-10-2023 --><!--edited by sinsiya on 11-06-2024 remove the condition $user === 'DEMO'-->
          <!-- <?php if ($user === 'DEMO' || $user === 'KWMT') { ?>
              <div id="outerCategoryContainer">
                  <div class="form-group">
                      <div id="categoryContainer" style="display: block;">
                          <label for="emp_category">Category &nbsp;<span style="color:red;">*</span></label>

                          <select id="emp_category" name="emp_category" class="form-control js-example-basic-single select2" style="width: 100%">
                              <option value="">--Select--</option>
                              <?php
                                foreach ($arr_category as $key => $value) {
                                    // if ($arr_professionalinfo['emp_type'] == 'Permanent') {
                                    $selected = ($arr_professionalinfo['emp_category'] == $value['category']['category_pkey']) ? 'selected="selected"' : '';
                                    // } else {
                                    //     $selected = '';
                                    // }
                                    //Edited by Akshay on 16-8-2024
                                    echo '<option value="' . $value['category']['category_pkey'] . '" ' . $selected . '>' . $value['category']['category_name'] . '</option>';
                                    //End
                                }
                                ?>
                          </select>
                      </div>
                  </div>

                  <div class="form-group">
                      <label for="grade_dropdown">Grade &nbsp;<span style="color:red;">*</span></label>
                      <div class="" id="grade_container">
                          <select id="grade_dropdown" name="emp_grade2" class="select2 form-control js-example-basic-single" style="width: 100%">
                              <option value=''><span>--Select Grade--</span></option>
                              Additional option elements can be included here
                              <?php
                                foreach ($arr_grades as $key => $value) {
                                    $selected = ($arr_professionalinfo['emp_grade'] == $value['grade_pkey']) ? 'selected="selected"' : '';
                                    echo '<option value="' . $value['grade_pkey'] . '" ' . $selected . '>' . $value['grade_name'] . '</option>';
                                }
                                ?>
                          </select>
                      </div>
                  </div>





                  <div class="form-group" id="showPayScale" style="display: block; ">
                                <label  for="pay_scale">Pay Scale &nbsp;<span style="color:red;">*</span></label>
                    
                                <div class="" id="pay_scale" >
                                    <span style="color: #ccc;"> Pay scale</span>
                                </div>

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
              <!-- <input type="submit" value="Save" id="save-btn" class="save-btn" /> -->
              <button type="submit" id="save-btn" class="save-btn">
                  Save
              </button>

          </div>
      </div>

  </form>
  <div>
      <h3 style="margin-top:30px;">Policies & Rules</h3>
      <form class="form-horizontal" method="post"
          action="<?php echo $this->webroot; ?>EmployeeJoin/saveconfig"
          id="empsetupconfig">
          <input type="hidden" name="emp_fkey" value="<?php echo $emp_pkey ?>" />
          <div class="employee_config">
              <!-- Shift Timings -->

              <div class="form-group" style="margin-left:0px;margin-right:0px;">
                  <label for="shift" class="label-congif">Shift Timings</label>
                  <select id="shift" name="shift" class="form-control js-example-basic-single select2" required style="width: 100%;">
                      <?php foreach ($arr_shifts as $values) { ?>
                          <option <?php echo (isset($arr_professionalinfo['day_time_seq']) && $arr_professionalinfo['day_time_seq'] == $values['working_day_time_procedures']['day_time_seq']) ? 'selected="selected"' : ''; ?>
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
                          <option <?php echo (isset($arr_professionalinfo['HOLIDAY_GROUP_ID']) && $arr_professionalinfo['HOLIDAY_GROUP_ID'] == $values['holiday_group']['HOLIDAY_GROUP_ID']) ? 'selected="selected"' : ''; ?>
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
                          <option <?php echo (isset($arr_professionalinfo['LEAVEPOLICY_GROUP_ID']) && $arr_professionalinfo['LEAVEPOLICY_GROUP_ID'] == $values['leavepolicy_group']['LEAVEPOLICY_GROUP_ID']) ? 'selected="selected"' : ''; ?>
                              value="<?php echo $values['leavepolicy_group']['LEAVEPOLICY_GROUP_ID']; ?>">
                              <?php echo $values['leavepolicy_group']['LEAVEPOLICY_GROUP_NAME']; ?>
                          </option>
                      <?php } ?>
                  </select>
              </div>
              <!-- Superior -->
              <div class="form-group" style="margin-left:0px;margin-right:0px;">
                  <label for="hierarch" class="label-congif">Superior</label>
                  <!-- <input type="text" id="hierarch" name="hierarch1"
                      value="<?php echo isset($hierarchy_set[0]['emp_details']['first_name']) ? $hierarchy_set[0]['emp_details']['first_name'] . ' ' . $hierarchy_set[0]['emp_details']['last_name'] : ''; ?>"
                      placeholder="Superior" class="form-control">
                  <input type="hidden" id="hierarch1" name="hierarch"
                      value="<?php echo isset($hierarchy_set[0]['emp_details']['emp_pkey']) ? $hierarchy_set[0]['emp_details']['emp_pkey'] : ''; ?>">
              </div> -->
                  <input type="text" placeholder="Superior" value="<?php echo isset($hierarchy_set['0']['emp_details']['first_name']) ? $hierarchy_set['0']['emp_details']['first_name'] . ' ' . $hierarchy_set['0']['emp_details']['last_name'] : ''; ?>" id="hierarch" name="hierarch1" class="form-control strict-field" style="width:205px; ">
                  <input type="hidden" id="hierarch1" name="hierarch" class="form-control strict-field" value="<?php echo isset($hierarchy_set['0']['emp_details']['emp_pkey']) ? $hierarchy_set['0']['emp_details']['emp_pkey'] : ''; ?>" style="width:234px; " style="width: 100%;">
              </div>
          </div>

          <div style="padding-bottom:40px;margin-top:20px;">
              <input type="submit" value="Complete Onboarding" class="custom-button"
                  style="border-radius: 10px;padding: 8px 20px;border: none;float:inline-end;" />
          </div>
      </form>
  </div>
  </div>



  <script>
      $(document).on("change", "#joining_date", function() {
          let joinDate = $(this).val().trim();
          $("#start_date").val(joinDate);
      });
      $(document).ready(function() {
          $('#empsetupconfig').on('submit', function(e) {
              e.preventDefault(); // Prevent normal form submission

              var formData = $(this).serialize();

              // Show loading
              $("#container").isLoading({
                  text: "Loading",
                  position: "overlay"
              });

              $.post('<?php echo $this->webroot; ?>EmployeeJoin/saveconfig', formData, function(response) {
                  // Parse JSON response
                  var res = typeof response === 'string' ? JSON.parse(response) : response;

                  if (res.success) {
                      $.notify(res.message || 'Employee onboarding successfully Completed', {
                          type: 'success',
                          z_index: 99999
                      });

                      $('#largeModalForm').modal('hide');

                      var url = '<?php echo $this->webroot; ?>EmployeeJoin/index';

                      $("#container").load(url, function() {
                          $("#container").isLoading("hide"); // hide loading when done
                      });

                  } else {
                      $("#container").isLoading("hide"); // hide loading on error
                      alert(res.message || 'Something went wrong');
                  }

              });
          });
      });


      // $(document).ready(function() {
      //     const $form = $('#empsetuppersonal');
      //     const $btn = $('#save-btn');

      //     if (!$form.length) {
      //         return;
      //     }

      //     const hasExistingRecord = () => {
      //         const proffKey = ($form.find('#emp_proff_pkey').val() || '').trim();
      //         const empKey = ($form.find('#emp_pkey').val() || '').trim();
      //         // Only consider it existing if we have a valid numeric key (not empty, not '0', not just whitespace)
      //         const hasProffKey = proffKey && proffKey !== '0' && proffKey !== '';
      //         const hasEmpKey = empKey && empKey !== '0' && empKey !== '';
      //         return hasProffKey || hasEmpKey;
      //     };

      //     // Check initial state - only set to true if we actually have saved keys
      //     let savedOnce = hasExistingRecord();

      //     // Debug initial state
      //     console.log('Initial form state:', {
      //         emp_pkey: $form.find('#emp_pkey').val(),
      //         emp_proff_pkey: $form.find('#emp_proff_pkey').val(),
      //         emp_fkey: $form.find('#emp_fkey').val(),
      //         savedOnce: savedOnce
      //     });

      //     const setIdleButtonLabel = () => {
      //         $btn.val(savedOnce ? 'Update' : 'Save');
      //     };

      //     setIdleButtonLabel();

      //     $form.off('submit');

      // //     $form.on('submit', function(e) {
      //         e.preventDefault();

      //         const wasUpdateMode = savedOnce;
      //         const endpoint = wasUpdateMode ? 'saveAllOnboard' : 'saveonboarding';
      //         const formData = new FormData(this);

      //         // 🔧 For update mode, explicitly ensure emp_pkey and emp_proff_pkey are in FormData
      //         if (wasUpdateMode) {
      //             const empPkey = $form.find('#emp_pkey').val();
      //             const empProffPkey = $form.find('#emp_proff_pkey').val();
      //             const empFkey = $form.find('#emp_fkey').val();

      //             // Ensure these keys are sent for update
      //             if (empPkey && empPkey !== '0') {
      //                 formData.set('emp_pkey', empPkey);
      //             }
      //             if (empProffPkey && empProffPkey !== '0' && empProffPkey !== '') {
      //                 formData.set('emp_proff_pkey', empProffPkey);
      //             }
      //             if (empFkey && empFkey !== '0') {
      //                 formData.set('emp_fkey', empFkey);
      //             }

      //             // Debug: log what's being sent
      //             console.log('Update mode - Sending:', {
      //                 emp_pkey: empPkey,
      //                 emp_proff_pkey: empProffPkey,
      //                 emp_fkey: empFkey,
      //                 endpoint: endpoint
      //             });

      //             // Log all FormData entries for debugging
      //             const formDataEntries = [];
      //             for (let pair of formData.entries()) {
      //                 formDataEntries.push({ key: pair[0], value: pair[1] });
      //             }
      //             console.log('All FormData entries:', formDataEntries);
      //         }

      //         $btn.prop('disabled', true).val(wasUpdateMode ? 'Updating...' : 'Saving...');

      //         $.ajax({
      //             url: '<?php echo $this->webroot; ?>EmployeeJoin/' + endpoint,
      //             type: 'POST',
      //             data: formData,
      //             contentType: false,
      //             processData: false,
      //             dataType: 'json',
      //             success: function(res) {
      //                 const response = res || {};

      //                 // Debug: log response
      //                 console.log('Response from', endpoint, ':', response);

      //                 if (response.success) {
      //                     $.notify(response.message || (wasUpdateMode ? 'Updated successfully ✅' : 'Saved successfully ✅'), {
      //                         type: 'success',
      //                         z_index: 99999
      //                     });

      //                     if (!wasUpdateMode) {
      //                         $.notify('Please complete configuration to finish onboarding.', {
      //                             type: 'info',
      //                             z_index: 99999
      //                         });
      //                     }

      //                     const newEmpPkey = response.emp_pkey || response.pkey || response.employee_pkey || response.emp_fkey;
      //                     if (newEmpPkey) {
      //                         $('#emp_fkey').val(newEmpPkey);
      //                         $('#empsetupprofessional #emp_fkey').val(newEmpPkey);
      //                         $('#empsetupconfig #emp_fkey').val(newEmpPkey);
      //                         $('#empsetuptaxation #emp_pkey').val(newEmpPkey);
      //                         $form.find('#emp_pkey').val(newEmpPkey);
      //                         savedOnce = true;
      //                     }

      //                     const newProffKey = response.emp_proff_pkey || response.emp_proffessional_pkey || response.emp_proff_id;
      //                     if (newProffKey) {
      //                         $('#emp_proff_pkey').val(newProffKey);
      //                         savedOnce = true;
      //                     }

      //                     const companyId = response.emp_company_id || response.emp_id || response.employee_id;
      //                     if (companyId) {
      //                         $('#emp_company_id').val(companyId);
      //                     }
      //                 } else {
      //                     $.notify(response.message || 'Something went wrong ❌', {
      //                         type: 'danger',
      //                         z_index: 99999
      //                     });
      //                 }
      //             },
      //             error: function(xhr) {
      //                 console.error("XHR error:", xhr.responseText);
      //                 console.error("Status:", xhr.status);
      //                 $.notify('Server error. Please try again.', {
      //                     type: 'danger',
      //                     z_index: 99999
      //                 });
      //             },
      //             complete: function() {
      //                 setIdleButtonLabel();
      //                 $btn.prop('disabled', false);
      //             }
      //         });
      //     });
      // });


      $(document).ready(function() {
          // ===============================
          // 📌 Datepickers
          // ===============================

          $('#joining_date').datepicker({
              format: 'yyyy-mm-dd',
              autoclose: true,
              todayHighlight: true
          }).on('changeDate', function() {
              $(this).datepicker('hide');
          });

          $("#end_date").datepicker({
              format: 'yyyy-mm-dd',
              autoclose: true
          });

          $('#date_of_birth_nominee').datepicker({
              format: 'yyyy-mm-dd',
              autoclose: true
          });

          // ===============================
          // 📌 Salary Structure Validation
          // ===============================
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

          // ===============================
          // 📌 Employee Type Switcher
          // ===============================
          function handleEmployeeTypeChange() {
              var empType = $("#emp_type").val();

              // Containers
              var probationDays = $("#probationDaysContainer");
              var contractPeriod = $("#contractPeriodContainer");
              var categoryContainer = $("#categoryContainer");
              var gradeContainer = $("#gradeContainer");

              // Reset all
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

          // ===============================
          // 📌 Grade Dropdown Loader
          // ===============================
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

          // ===============================
          // 📌 Bindings
          // ===============================
          handleEmployeeTypeChange(); // Run on load
          $("#emp_type").on("change", handleEmployeeTypeChange);

          $("#emp_category").on("change", function() {
              var empType = $("#emp_type").val();
              var categoryId = $(this).val();
              if (categoryId) {
                  loadGradesForCategory(categoryId, empType);
              }
          });

          //   $(".select2").select2({
          //       dropdownParent: $('#largeModalForm'),
          //       minimumResultsForSearch: 0
          //   });
          $(".select2").select2({
              dropdownParent: $('#largeModalForm'),
              minimumResultsForSearch: 0,
              closeOnSelect: true, // set false if you want to allow typing multiple times before closing
              //   allowClear: true,
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
                      //var selectedItem = $('#filterby_employees').getSelectedItemData();
                      //var site_pkey = selectedItem.emp_pkey;
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
          // ===============================
          // 📌 Form Submissions
          // ===============================
          var empsetuppersonaloptions = {
              success: function(responseText) {
                  $('#empsetuppersonal').find("button[type='submit']").prop('disabled', false).html('Update');
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
                      return;
                  }
              }
              $(this).find("button[type='submit']")
                  .html('<li class="fa fa-spinner fa-spin"></li> saving...')
                  .attr('disabled', 'disabled');
              $(this).ajaxSubmit(empsetuppersonaloptions);
              return false;
          });

          //   $('#empsetupconfig').submit(function() {
          //       $(this).find("button[type='submit']").html('<li class="fa fa-spinner fa-spin"></li> saving...').attr('disabled', 'disabled');
          //       $(this).ajaxSubmit(empsetupconfigoptions);
          //       return false;
          //   });

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
  </script>
  <!-- <script>
      $(document).ready(function() {
          const $form = $('#empsetuppersonal');
          const $submitBtn = $('#save-btn');

          $form.on('submit', function(e) {
              e.preventDefault();

              // Detect mode
              const empPkey = $('#emp_pkey').val();
              const empProffPkey = $('#emp_proff_pkey').val();
              const isUpdate = (empPkey && empPkey !== '0') || (empProffPkey && empProffPkey !== '0');
              const endpoint = isUpdate ? 'updateOnboardData' : 'saveonboarding';

              console.log('Form mode:', isUpdate ? 'UPDATE' : 'SAVE');

              // Disable submit
              $submitBtn.prop('disabled', true).html(
                  `<i class="fa fa-spinner fa-spin"></i> ${isUpdate ? 'Updating...' : 'Saving...'}`
              );

              // Contract employee validation
              const empType = $('#emp_type').val();
              if (empType === 'Contract') {
                  const startDate = new Date($('#start_date').val());
                  const endDate = new Date($('#end_date').val());

                  if (!$('#end_date').val()) {
                      alert('Contract period end date is required');
                      $submitBtn.prop('disabled', false).html('Save');
                      return;
                  }
                  if (startDate > endDate) {
                      alert('Start date must be less than end date');
                      $('#end_date').val('');
                      $submitBtn.prop('disabled', false).html('Save');
                      return;
                  }
              }

              // FormData
              const formData = new FormData(this);

              if (empPkey && empPkey !== '0') formData.set('emp_pkey', empPkey);
              if (empProffPkey && empProffPkey !== '0') formData.set('emp_proff_pkey', empProffPkey);

              console.log('Sending to:', endpoint);

              $.ajax({
                  url: '<?php echo $this->webroot; ?>EmployeeJoin/' + endpoint,
                  type: 'POST',
                  data: formData,
                  processData: false,
                  contentType: false,
                  dataType: 'json',

                  success: function(response) {
                      console.log('Server response:', response);

                      if (response.success) {
                          $.notify(response.message || (isUpdate ? 'Updated successfully ' : 'Saved successfully '), {
                              type: 'success',
                              z_index: 99999
                          });

                          if (response.emp_pkey) {
                              $('#emp_pkey').val(response.emp_pkey);
                              $('#emp_fkey').val(response.emp_pkey);
                          }
                          if (response.emp_proff_pkey) {
                              $('#emp_proff_pkey').val(response.emp_proff_pkey);
                          }

                          $form.attr('data-mode', 'update');

                      } else {
                          $.notify(response.message || 'Something went wrong', {
                              type: 'danger',
                              z_index: 99999
                          });
                      }
                  },

                  error: function(xhr) {
                      console.error('XHR error:', xhr);

                      let msg = "Server error. Please try again";

                      // JSON error message
                      if (xhr.responseJSON && xhr.responseJSON.message) {
                          msg = xhr.responseJSON.message;
                      }

                      // Clean plain text responses (remove PHP notices/warnings)
                      else if (xhr.responseText) {
                          let clean = xhr.responseText
                              .replace(/<[^>]*>?/gm, '') // remove HTML if any
                              .replace(/Notice:.*/gi, '')
                              .replace(/Warning:.*/gi, '')
                              .replace(/Deprecated:.*/gi, '')
                              .trim();

                          if (clean !== '') msg = clean;
                      }

                      $.notify(msg, {
                          type: 'danger',
                          z_index: 99999
                      });
                  },

                  complete: function() {
                      $submitBtn.prop('disabled', false).html(isUpdate ? 'Update' : 'Save');
                  }
              });

          });
      });

      // $(document).ready(function () {
      //     const $form = $('#empsetuppersonal');
      //     const $submitBtn = $('#btnempsetuppersonal');

      //     $form.on('submit', function (e) {
      //         e.preventDefault();

      //         // Detect mode
      //         const empPkey = $('#emp_pkey').val();
      //         const empProffPkey = $('#emp_proff_pkey').val();
      //         const isUpdate = (empPkey && empPkey !== '0') || (empProffPkey && empProffPkey !== '0');
      //    const endpoint = isUpdate ? 'updateOnboardData' : 'saveonboarding';



      //         console.log('Form mode:', isUpdate ? 'UPDATE' : 'SAVE');

      //         // Disable submit while processing
      //         $submitBtn.prop('disabled', true).html(
      //             `<i class="fa fa-spinner fa-spin"></i> ${isUpdate ? 'Updating...' : 'Saving...'}`
      //         );

      //         // ✅ Contract employee validation
      //         const empType = $('#emp_type').val();
      //         if (empType === 'Contract') {
      //             const startDate = new Date($('#start_date').val());
      //             const endDate = new Date($('#end_date').val());

      //             if (!$('#end_date').val()) {
      //                 alert('Contract period end date is required');
      //                 $submitBtn.prop('disabled', false).html('Save');
      //                 return;
      //             }
      //             if (startDate > endDate) {
      //                 alert('Start date must be less than end date');
      //                 $('#end_date').val(''); 
      //                 $submitBtn.prop('disabled', false).html('Save');
      //                 return;
      //             }
      //         }

      //         // ✅ Prepare FormData
      //         const formData = new FormData(this);

      //         // Always ensure keys are passed
      //         if (empPkey && empPkey !== '0') {
      //             formData.set('emp_pkey', empPkey);
      //         }
      //         if (empProffPkey && empProffPkey !== '0') {
      //             formData.set('emp_proff_pkey', empProffPkey);
      //         }

      //         // Debug: show what’s sent
      //         console.log('Sending to:', endpoint);
      //         for (let [key, value] of formData.entries()) {
      //             console.log(key, ':', value);
      //         }

      //         // ✅ AJAX call
      //         $.ajax({
      //             url: '<?php echo $this->webroot; ?>EmployeeJoin/' + endpoint,
      //             type: 'POST',
      //             data: formData,
      //             processData: false,
      //             contentType: false,
      //             dataType: 'json',
      //             success: function (response) {
      //                 console.log('Server response:', response);

      //                 if (response.success) {
      //                     $.notify(response.message || (isUpdate ? 'Updated successfully ✅' : 'Saved successfully ✅'), {
      //                         type: 'success',
      //                         z_index: 99999
      //                     });

      //                     // Set keys after successful save/update
      //                     if (response.emp_pkey) {
      //                         $('#emp_pkey').val(response.emp_pkey);
      //                         $('#emp_fkey').val(response.emp_pkey);
      //                     }
      //                     if (response.emp_proff_pkey) {
      //                         $('#emp_proff_pkey').val(response.emp_proff_pkey);
      //                     }

      //                     // Switch to update mode after first save
      //                     $form.attr('data-mode', 'update');
      //                 } else {
      //                     $.notify(response.message || 'Something went wrong ❌', {
      //                         type: 'danger',
      //                         z_index: 99999
      //                     });
      //                 }
      //             },
      //             error: function (xhr) {
      //                 console.error('XHR error:', xhr.responseText);
      //                 $.notify('Server error. Please try again ❌', {
      //                     type: 'danger',
      //                     z_index: 99999
      //                 });
      //             },
      //             complete: function () {
      //                 $submitBtn.prop('disabled', false).html(isUpdate ? 'Update' : 'Save');
      //             }
      //         });
      //     });
      // });
      
  </script> -->
  <script>
    $(document).ready(function () {
        const $form = $('#empsetuppersonal');
        const $submitBtn = $('#save-btn');

        /* ── Inject modal styles (v3) ── */
        if (!document.getElementById('onboard-modal-style')) {
            $('head').append(`<style id="onboard-modal-style">
                @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
                
                #om-overlay {
                    position: fixed; inset: 0; z-index: 999999;
                    backdrop-filter: blur(8px); background: rgba(15, 23, 42, 0.4);
                    display: flex; align-items: center; justify-content: center;
                    font-family: 'Inter', system-ui, -apple-system, sans-serif;
                    padding: 20px;
                }
                
                #om-box {
                    width: 100%; max-width: 420px; border-radius: 20px;
                    overflow-x: hidden; overflow-y: auto; max-height: 90vh; background: #fff;
                    box-shadow: 0 40px 100px rgba(15, 23, 42, 0.35);
                    animation: omFadeIn 0.4s cubic-bezier(0.16, 1, 0.3, 1);
                }

                @keyframes omFadeIn {
                    from { opacity: 0; transform: scale(0.95) translateY(30px); }
                    to { opacity: 1; transform: scale(1) translateY(0); }
                }

                #om-box .hd { 
                    background: #1e516e; padding: 1.25rem; 
                    display: flex; gap: 1rem; align-items: center;
                }
                
                #om-box .ring-wrap { position: relative; width: 62px; height: 62px; flex-shrink: 0; }
                #om-box .ring-wrap svg { width: 62px; height: 62px; transform: rotate(-90deg); }
                #om-box .ring-bg { fill: none; stroke: rgba(255, 255, 255, 0.15); stroke-width: 6; }
                #om-box .ring-fg { 
                    fill: none; stroke: #fbbf24; stroke-width: 6; stroke-linecap: round;
                    stroke-dasharray: 164; stroke-dashoffset: 164;
                    transition: stroke-dashoffset 1.5s cubic-bezier(0.34, 1.56, 0.64, 1);
                }
                
                #om-box .ring-label {
                    position: absolute; inset: 0; display: flex; flex-direction: column;
                    align-items: center; justify-content: center;
                }
                #om-box .ring-pct { font-size: 16px; font-weight: 800; color: #fff; line-height: 1; }
                #om-box .ring-tiny { font-size: 9px; color: rgba(255, 255, 255, 0.6); font-weight: 600; margin-top: 2px; text-transform: uppercase; letter-spacing: 0.5px; }

                #om-box .hd-text { flex: 1; }
                #om-box .hd-eyebrow { display: inline-flex; align-items: center; gap: 6px; margin-bottom: 6px; }
                #om-box .hd-dot { width: 7px; height: 7px; border-radius: 50%; background: #fbbf24; box-shadow: 0 0 10px rgba(251, 191, 36, 0.5); }
                #om-box .hd-tag { font-size: 10px; font-weight: 700; letter-spacing: .1em; color: rgba(255, 255, 255, 0.55); text-transform: uppercase; }
                #om-box .hd-title { font-size: 19px; font-weight: 800; color: #fff; letter-spacing: -.5px; line-height: 1.1; margin: 0; }
                #om-box .hd-sub { font-size: 12px; color: rgba(255, 255, 255, 0.45); margin-top: 4px; font-weight: 400; }

                #om-box .metric-strip { 
                    display: grid; grid-template-columns: 1fr 1fr 1fr; 
                    background: #1e516e; padding: 0 1.25rem 1.25rem; gap: 8px; 
                }
                #om-box .metric { background: rgba(255, 255, 255, 0.08); padding: 10px 8px; border-radius: 12px; text-align: center; border: 1px solid rgba(255, 255, 255, 0.05); }
                #om-box .metric-val { font-size: 17px; font-weight: 800; color: #fff; }
                #om-box .metric-key { font-size: 9px; color: rgba(255, 255, 255, 0.5); margin-top: 2px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.2px; }

                #om-box .bd { padding: 1.25rem 1.25rem 0; }
                #om-box .prog-block { margin-bottom: 12px; }
                #om-box .prog-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px; }
                #om-box .prog-left { display: flex; align-items: center; gap: 10px; }
                #om-box .prog-ico { 
                    width: 34px; height: 34px; border-radius: 8px; 
                    display: flex; align-items: center; justify-content: center; flex-shrink: 0; 
                }
                #om-box .prog-ico svg { width: 16px; height: 16px; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
                #om-box .p-bl { background: #f0f7ff; } #om-box .p-bl svg { stroke: #2563eb; }
                #om-box .p-tl { background: #f0fdf4; } #om-box .p-tl svg { stroke: #059669; }
                #om-box .prog-name { font-size: 14px; font-weight: 700; color: #0f172a; }
                #om-box .prog-fields { font-size: 11px; color: #64748b; font-weight: 500; margin-top: 0px; }
                #om-box .prog-pct { font-size: 14px; font-weight: 800; }
                #om-box .pct-bl { color: #2563eb; } #om-box .pct-tl { color: #059669; }
                #om-box .track { height: 8px; border-radius: 99px; background: #f1f5f9; overflow: hidden; }
                #om-box .fill { height: 100%; border-radius: 99px; transition: width 1.2s cubic-bezier(0.4, 0, 0.2, 1); }
                #om-box .f-bl { background: linear-gradient(90deg, #3b82f6, #60a5fa); } 
                #om-box .f-tl { background: linear-gradient(90deg, #10b981, #34d399); }
                #om-box .f-bl { box-shadow: 0 2px 10px rgba(59, 130, 246, 0.3); }
                #om-box .f-tl { box-shadow: 0 2px 10px rgba(16, 185, 129, 0.3); }

                #om-box .sep { display: flex; align-items: center; gap: 12px; margin: 16px 0; }
                #om-box .sep-line { flex: 1; height: 1px; background: #f1f5f9; }
                #om-box .sep-txt { font-size: 9px; font-weight: 800; letter-spacing: .12em; color: #94a3b8; text-transform: uppercase; white-space: nowrap; }

                #om-box .chips { display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px; margin-bottom: 1.25rem; }
                #om-box .chip { 
                    border-radius: 12px; padding: 10px 10px; 
                    display: flex; align-items: center; gap: 8px; 
                    border: 1.5px solid transparent; transition: all 0.2s;
                }
                #om-box .chip.ok { background: #f0fdf4; border-color: #dcfce7; }
                #om-box .chip.no { background: #fff1f2; border-color: #ffe4e6; }
                #om-box .chip-ico { width: 28px; height: 28px; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
                #om-box .chip.ok .chip-ico { background: #dcfce7; }
                #om-box .chip.no .chip-ico { background: #ffe4e6; }
                #om-box .chip-ico svg { width: 14px; height: 14px; stroke-width: 2.5; stroke-linecap: round; stroke-linejoin: round; fill: none; }
                #om-box .chip.ok .chip-ico svg { stroke: #16a34a; }
                #om-box .chip.no .chip-ico svg { stroke: #e11d48; }
                #om-box .chip-lbl { font-size: 12px; font-weight: 700; flex: 1; }
                #om-box .chip.ok .chip-lbl { color: #166534; }
                #om-box .chip.no .chip-lbl { color: #9f1239; }
                #om-box .chip-status { font-size: 9px; font-weight: 800; letter-spacing: .05em; text-transform: uppercase; padding: 3px 8px; border-radius: 99px; }
                #om-box .chip.ok .chip-status { background: #bbf7d0; color: #166534; }
                #om-box .chip.no .chip-status { background: #fecdd3; color: #9f1239; }

                #om-box .ft { padding: 0 1.25rem 1.25rem; }
                #om-box .alert { display: flex; align-items: center; gap: 10px; background: #fffcf0; border: 1px solid #fef3c7; border-radius: 12px; padding: 10px 12px; margin-bottom: 12px; }
                #om-box .alert-icon { width: 18px; height: 18px; flex-shrink: 0; }
                #om-box .alert-icon svg { width: 18px; height: 18px; stroke: #d97706; stroke-width: 2; fill: none; }
                #om-box .alert-txt { font-size: 12px; color: #92400e; line-height: 1.4; font-weight: 500; }
                #om-box .alert-txt strong { color: #78350f; font-weight: 800; }
                
                #om-box .btns { display: flex; gap: 10px; }
                #om-box .btn { 
                    flex: 1; height: 42px; border-radius: 12px; font-size: 13px; font-weight: 700; 
                    cursor: pointer; border: none; font-family: inherit; letter-spacing: .01em; 
                    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
                }
                #om-box .btn-cancel { background: #f8fafc; color: #475569; border: 2px solid #f1f5f9; }
                #om-box .btn-cancel:hover { background: #f1f5f9; transform: translateY(-1px); }
                #om-box .btn-save { background: #1e516e; color: #fff; box-shadow: 0 8px 25px rgba(30, 81, 110, 0.3); }
                #om-box .btn-save:hover { background: #2a6688; transform: translateY(-2px); box-shadow: 0 12px 30px rgba(30, 81, 110, 0.4); }
                #om-box .btn-save:active { transform: translateY(0); }

                @media (max-width: 640px) {
                    #om-box { border-radius: 0; height: 100%; max-height: 100vh; overflow-y: auto; }
                    #om-box .hd, #om-box .bd, #om-box .ft, #om-box .metric-strip { padding-left: 1.5rem; padding-right: 1.5rem; }
                    #om-box .chips { grid-template-columns: 1fr; }
                    #om-box .hd { flex-direction: column; text-align: center; padding-top: 3rem; }
                    #om-box .hd-text { width: 100%; }
                    #om-box .hd-eyebrow { justify-content: center; }
                }
            </style>`);
        }

        function omChip(label, filled) {
            const cls = filled ? 'filled' : 'missing';
            const tag = filled ? 'FILLED' : 'MISSING';
            const dPath = filled
                ? '<polyline points="2,6 5,9 10,3"/>'         /* tick */
                : '<path d="M6 2v4M6 8.5v.5"/>';             /* bang */
            return `
                <div class="om-chip ${cls}">
                    <div class="om-chip-icon">
                        <svg viewBox="0 0 12 12" fill="none" stroke-width="1.6"
                             stroke-linecap="round" stroke-linejoin="round">${dPath}</svg>
                    </div>
                    <span class="om-chip-label">${label}</span>
                    <span class="om-chip-tag">${tag}</span>
                </div>`;
        }

        function showOnboardModal(res) {
            return new Promise(function (resolve) {
                // 1. Precise Calculations
                const personalPct = parseInt(res.personal_percent) || 0;
                const companyPct = parseInt(res.company_percent) || 0;

                const eduOk = parseInt(res.education_records) > 0;
                const famOk = parseInt(res.family_records) > 0;
                const workOk = parseInt(res.work_records) > 0;
                const docOk = parseInt(res.document_percent) >= 100;

                const recordsMissing = (eduOk ? 0 : 1) + (famOk ? 0 : 1) + (workOk ? 0 : 1) + (docOk ? 0 : 1);
                const sectionsFilled = (personalPct === 100 ? 1 : 0) + (companyPct === 100 ? 1 : 0) + (eduOk ? 1 : 0) + (famOk ? 1 : 0) + (workOk ? 1 : 0) + (docOk ? 1 : 0);
                const fieldsLeft = (parseInt(res.personal_total) - parseInt(res.personal_filled)) + (parseInt(res.company_total) - parseInt(res.company_filled));

                // Overall average score for the ring (6 sections now)
                const totalPct = Math.round((personalPct + companyPct + (eduOk ? 100 : 0) + (famOk ? 100 : 0) + (workOk ? 100 : 0) + (parseInt(res.document_percent) || 0)) / 6);
                const circumference = 164; // Match CSS/SVG stroke-dasharray
                const ringOffset = circumference * (1 - totalPct / 100);

                const chip = (label, ok) => `
                    <div class="chip ${ok ? 'ok' : 'no'}">
                        <div class="chip-ico"><svg viewBox="0 0 14 14"><path d="${ok ? 'M3 7l3 3 5-5' : 'M7 3v4.5M7 10v.5'}"/></svg></div>
                        <div class="chip-lbl">${label}</div>
                        <div class="chip-status">${ok ? 'Filled' : 'Missing'}</div>
                    </div>`;

                const alertHtml = recordsMissing > 0
                    ? `<div class="alert">
                        <div class="alert-icon"><svg viewBox="0 0 18 18"><path d="M9 2L1.5 15.5h15L9 2z"/><path d="M9 8v3.5M9 13.5v.5"/></svg></div>
                        <div class="alert-txt"><strong>${recordsMissing} sections incomplete.</strong> You can still save and return to fill them later.</div>
                       </div>`
                    : `<div class="alert" style="background:#ecfdf5; border-color:#a7f3d0;">
                        <div class="alert-icon"><svg viewBox="0 0 18 18" style="stroke:#059669;"><path d="M3 9l4 4 8-8"/></svg></div>
                        <div class="alert-txt" style="color:#065f46;"><strong>Profile complete!</strong> Everything looks ready for the final submission.</div>
                       </div>`;

                const html = `
                <div id="om-overlay">
                  <div id="om-box">
                    <div class="hd">
                      <div class="ring-wrap">
                        <svg viewBox="0 0 62 62" style="display: block;">
                          <circle class="ring-bg" cx="31" cy="31" r="26" stroke-dasharray="164"/>
                          <circle class="ring-fg" id="om-ring" cx="31" cy="31" r="26" stroke-dasharray="164" style="stroke-dashoffset: 164; transition: stroke-dashoffset 1.5s cubic-bezier(0.34, 1.56, 0.64, 1);"/>
                        </svg>
                        <div class="ring-label"><span class="ring-pct">${totalPct}%</span><span class="ring-tiny">done</span></div>
                      </div>
                      <div class="hd-text">
                        <div class="hd-eyebrow"><div class="hd-dot"></div><span class="hd-tag">Overall Progress</span></div>
                        <p class="hd-title">Onboarding Summary</p>
                        <p class="hd-sub">Review your profile before saving</p>
                      </div>
                    </div>

                    <div class="metric-strip">
                      <div class="metric"><div class="metric-val">${sectionsFilled}</div><div class="metric-key">Sections filled</div></div>
                      <div class="metric"><div class="metric-val">${recordsMissing}</div><div class="metric-key">Records missing</div></div>
                      <div class="metric"><div class="metric-val">${fieldsLeft}</div><div class="metric-key">Fields left</div></div>
                    </div>

                    <div class="bd">
                      <div class="prog-block">
                        <div class="prog-head">
                          <div class="prog-left">
                            <div class="prog-ico p-bl"><svg viewBox="0 0 16 16"><circle cx="8" cy="5.5" r="2.5"/><path d="M2.5 14c0-3.04 2.46-5.5 5.5-5.5s5.5 2.46 5.5 5.5"/></svg></div>
                            <div><div class="prog-name">Personal Data</div><div class="prog-fields">${res.personal_filled} of ${res.personal_total} fields</div></div>
                          </div>
                          <span class="prog-pct pct-bl">${personalPct}%</span>
                        </div>
                        <div class="track"><div class="fill f-bl" style="width:${personalPct}%"></div></div>
                      </div>

                      <div class="prog-block">
                        <div class="prog-head">
                          <div class="prog-left">
                            <div class="prog-ico p-tl"><svg viewBox="0 0 16 16"><rect x="2" y="3" width="12" height="10" rx="2"/><path d="M5 7h6M5 10h4"/></svg></div>
                            <div><div class="prog-name">Company Data</div><div class="prog-fields">${res.company_filled} of ${res.company_total} fields</div></div>
                          </div>
                          <span class="prog-pct pct-tl">${companyPct}%</span>
                        </div>
                        <div class="track"><div class="fill f-tl" style="width:${companyPct}%"></div></div>
                      </div>

                      <div class="sep"><div class="sep-line"></div><span class="sep-txt">Records</span><div class="sep-line"></div></div>

                      <div class="chips">
                        ${chip('Education Record', eduOk)}
                        ${chip('Family Details', famOk)}
                        ${chip('Work Experience', workOk)}
                        ${chip('Documents', docOk)}
                      </div>
                    </div>

                    <div class="ft">
                      ${alertHtml}
                      <div class="btns">
                        <button class="btn btn-cancel" id="om-cancel">Cancel</button>
                        <button class="btn btn-save" id="om-save">Save &amp; Continue</button>
                      </div>
                    </div>
                  </div>
                </div>`;

                $('body').append(html);

                // Animate the ring after append
                setTimeout(() => {
                    $('#om-ring').css('stroke-dashoffset', ringOffset);
                }, 50);

                $('#om-save').one('click', function () {
                    $('#om-overlay').remove();
                    resolve(true);
                });

                $('#om-cancel').one('click', function () {
                    $('#om-overlay').remove();
                    resolve(false);
                });

                $('#om-overlay').one('click', function (e) {
                    if ($(e.target).is('#om-overlay')) {
                        $('#om-overlay').remove();
                        resolve(false);
                    }
                });
            });
        }



        $form.on('submit', function (e) {
            e.preventDefault();
            var $thisForm = this;
            const empFkey = $('#emp_fkey').val();
            const empPkey = $('#emp_pkey').val();
            const empProffPkey = $('#emp_proff_pkey').val();
            const isUpdate = (empPkey && empPkey !== '0') || (empProffPkey && empProffPkey !== '0');
            const endpoint = isUpdate ? 'updateOnboardData' : 'saveonboarding';

            $submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Checking Data...');

            // Serialize ALL fields from both forms into one payload
            var completionData = {};
            // Form 1: #empsetuppersonal — personal + professional fields
            $('#empsetuppersonal').serializeArray().forEach(function (item) {
                completionData[item.name] = item.value;
            });
            // Form 2: #empsetupconfig — shift, holiday, leave policy, superior
            $('#empsetupconfig').serializeArray().forEach(function (item) {
                completionData[item.name] = item.value;
            });
            // Remap: HTML element names differ from backend expected keys for holiday/leave
            completionData.HOLIDAY_GROUP_ID = $('#holidays').val();
            completionData.LEAVEPOLICY_GROUP_ID = $('#leave').val();
            completionData.hierarch = $('#hierarch1').val();


            $.ajax({
                url: '<?php echo $this->webroot; ?>EmployeeJoin/getOnboardingCompletion',
                type: 'POST',
                data: completionData,
                dataType: 'json',
                success: function (res) {
                    if (res && res.success) {
                        showOnboardModal(res).then(function (confirmed) {
                            if (confirmed) {
                                // Calculate total percentage BEFORE calling save
                                const personalPct = parseInt(res.personal_percent) || 0;
                                const companyPct = parseInt(res.company_percent) || 0;
                                const eduOk = parseInt(res.education_records) > 0;
                                const famOk = parseInt(res.family_records) > 0;
                                const workOk = parseInt(res.work_records) > 0;
                                const docPct = parseInt(res.document_percent) || 0;
                                const totalPct = Math.round((personalPct + companyPct + (eduOk ? 100 : 0) + (famOk ? 100 : 0) + (workOk ? 100 : 0) + docPct) / 6);

                                performActualSave($thisForm, isUpdate, endpoint, empPkey, empProffPkey, totalPct);
                            } else {
                                $submitBtn.prop('disabled', false).html(isUpdate ? 'Update' : 'Save');
                            }
                        });
                    } else {
                        if (confirm('Could not calculate completion. Do you want to save anyway?')) {
                            performActualSave($thisForm, isUpdate, endpoint, empPkey, empProffPkey);
                        } else {
                            $submitBtn.prop('disabled', false).html(isUpdate ? 'Update' : 'Save');
                        }
                    }
                },
                error: function () {
                    if (confirm('Could not connect to checker. Save anyway?')) {
                        performActualSave($thisForm, isUpdate, endpoint, empPkey, empProffPkey);
                    } else {
                        $submitBtn.prop('disabled', false).html(isUpdate ? 'Update' : 'Save');
                    }
                }
            });
        });

        function performActualSave($thisForm, isUpdate, endpoint, empPkey, empProffPkey, percentage = 0) {
            $submitBtn.prop('disabled', true).html(
                `<i class="fa fa-spinner fa-spin"></i> ${isUpdate ? 'Updating...' : 'Saving...'}`
            );


            // Contract employee validation
            const empType = $('#emp_type').val();
            if (empType === 'Contract') {
                const startDate = new Date($('#start_date').val());
                const endDate = new Date($('#end_date').val());

                if (!$('#end_date').val()) {
                    alert('Contract period end date is required');
                    $submitBtn.prop('disabled', false).html('Save');
                    return;
                }
                if (startDate > endDate) {
                    alert('Start date must be less than end date');
                    $('#end_date').val('');
                    $submitBtn.prop('disabled', false).html('Save');
                    return;
                }
            }

            // FormData
            const formData = new FormData($thisForm);

            if (empPkey && empPkey !== '0') formData.set('emp_pkey', empPkey);
            if (empProffPkey && empProffPkey !== '0') formData.set('emp_proff_pkey', empProffPkey);

            console.log('Sending to:', endpoint);

            $.ajax({
                url: '<?php echo $this->webroot; ?>EmployeeJoin/' + endpoint,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',

                success: function (response) {
                    console.log('Server response:', response);

                    if (response.success) {
                        $.notify(response.message || (isUpdate ? 'Updated successfully ' : 'Saved successfully '), {
                            type: 'success',
                            z_index: 99999
                        });

                        if (response.emp_pkey) {
                            $('#emp_pkey').val(response.emp_pkey);
                            $('#emp_fkey').val(response.emp_pkey);
                        }
                        if (response.emp_proff_pkey) {
                            $('#emp_proff_pkey').val(response.emp_proff_pkey);
                        }

                        $form.attr('data-mode', 'update');

                        /* ── Step 2: Trigger Async Email ── */
                        $.ajax({
                            url: '<?php echo $this->webroot; ?>EmployeeJoin/sendOnboardingMail',
                            type: 'POST',
                            data: {
                                emp_fkey: $('#emp_fkey').val() || response.emp_pkey,
                                percentage: percentage
                            },
                            dataType: 'json',
                            success: function (eRes) {
                                console.log('Mail sent status:', eRes);
                            }
                        });

                    } else {
                        $.notify(response.message || 'Something went wrong', {
                            type: 'danger',
                            z_index: 99999
                        });
                    }
                },

                error: function (xhr) {
                    console.error('XHR error:', xhr);

                    let msg = "Server error. Please try again";

                    // JSON error message
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }

                    // Clean plain text responses (remove PHP notices/warnings)
                    else if (xhr.responseText) {
                        let clean = xhr.responseText
                            .replace(/<[^>]*>?/gm, '') // remove HTML if any
                            .replace(/Notice:.*/gi, '')
                            .replace(/Warning:.*/gi, '')
                            .replace(/Deprecated:.*/gi, '')
                            .trim();

                        if (clean !== '') msg = clean;
                    }

                    $.notify(msg, {
                        type: 'danger',
                        z_index: 99999
                    });
                },

                complete: function () {
                    $submitBtn.prop('disabled', false).html(isUpdate ? 'Update' : 'Save');
                }
            });
        }
    });

    
</script>