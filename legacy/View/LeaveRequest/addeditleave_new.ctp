<form class="form-horizontal" method="post" action="<?php echo $this->webroot; ?>LeaveRequest/saveLeaveEntry" id="myleaverequestform">
      <div class="modal-header" style="background: #00659f;color: white">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title"><?php echo $head; ?></h4>

      </div>
      <input id="myLeaveAction" name="myLeaveAction" type="hidden" value="Save">
      <input id="LEAVEENTRYID" name="LEAVEENTRYID" type="hidden" value="<?php echo $leaveentryId; ?>">
      <input id="LEAVEENTRYID" name="LEAVESTATUS" type="hidden" value="<?php echo $arr_leave_details['LEAVESTATUS']; ?>">
      <?php if ($leaveentryId == 0) { ?>
          <div class="modal-body">

              <fieldset>
                  <!-- Form Name -->
                  <input id="model" name="model" type="hidden" value="LeaveRequests">
                  <div class="form-group">
                      <div class="col-md-6">
                          <label style="text-align:left;" class="col-md-4 control-label" for="salary_head_item_fkey">Leave Type&nbsp;<span style="color:red;">*</span></label>
                          <div class="col-md-8">
                              <select <?php echo ($leaveentryId > 0) ? 'disabled="disabled"' : 'required=""'; ?> id="salary_head_item_fkey" name="salary_head_item_fkey" class="form-control" onchange="empshow();">
                                  <option value="">--Select--</option>
                                  <?php
                                    foreach ($arr_leave_type as $key => $value) {
                                        $selected = ($arr_leave_details['salary_head_item_fkey'] == $value['SalaryHeadItems']['salary_head_item_pkey']) ? 'selected="selected"' : '';
                                        echo '<option value="' . $value['SalaryHeadItems']['salary_head_item_pkey'] . '" ' . $selected . '>' . $value['SalaryHeadItems']['item'] . '</option>';
                                    }
                                    ?>
                              </select>
                          </div>
                      </div>
                      <!-- <div class="col-md-6" id="compoff_available_dates"  style="display: none;">
                          <label style="text-align:left;" class="col-md-4 control-label" for="compoff_dates">Available dates&nbsp;<span style="color:red;">*</span></label>
                          <div class="col-md-8" id="compoff_dates" name="compoff_dates">

                          </div>
                      </div> -->

                      <!-- <div class="col-md-6" id="compoff_available_dates" style="display: none;">
    <label style="text-align:left;" class="col-md-4 control-label">
        Available Dates <span style="color:red;">*</span>
    </label>
    <div class="col-md-8">
        <div id="compoff_dates" class="list-group" style="max-height:200px; overflow-y:auto; border:1px solid #ddd; border-radius:6px;">
             Dates will be injected here 
        </div>
    </div>
</div> -->

                      <div class="col-md-12" id="showremarks" style="margin-top:  15px;">

                          <label style="text-align:left;" class="col-md-2  control-label">Leave Type Remarks :</label>
                          <div class="col-md-4  " id="remarks" style="font-weight: bolder;padding-top: 7px">
                          </div>
                          <label style="text-align:left;" id="balance" class="col-md-2 control-label"></label>
                          <div class="col-md-4" style="padding-right: 0px;padding-top: 7px; padding-left: 25px;">
                              <label style="text-align:left;" id="Leavebalance"></label>
                              <!--<input type="hidden" id="hid_yearly_balance" name="hid_yearly_balance" />-->
                              <input type="hidden" id="hid_monthly_balance" name="hid_monthly_balance" />

                              <!--input class="col-md-8" style="background: white ; text-align: left ; border: none ; " disabled="disabled" type="text" id="Leavebalance" name="Leavebalance"-->
                          </div>



                      </div>
                      <div class="col-md-6" id="div-leavestatus" <?php
                                                                    if ($leaveentryId == 0) {
                                                                        echo 'style="display: none;"';
                                                                    }
                                                                    ?>>
                          <label style="text-align:left;" class="col-md-4 control-label" for="LEAVESTATUS">Leave Status</label>
                          <label style="text-align:left;padding-top: 7px;" class="col-md-8" id="label-leavestatus"><?php echo $arr_leave_details['LEAVESTATUS']; ?></label>
                      </div>
                  </div>
                  <div class="form-group">
                      <div class="col-md-6">
                          <label style="text-align:left;" class="col-md-4 control-label" for="FROMDATE">From Date&nbsp;<span style="color:red;">*</span></label>
                          <div class="col-md-8" style="padding-left: 0px;padding-right: 0px;">
                              <div class="col-md-6">
                                  <input <?php echo ($leaveentryId > 0) ? 'disabled="disabled"' : 'required=""'; ?> class="form-control input-md strict-field" id="FROMDATE" name="FROMDATE" value="<?php echo $arr_leave_details["FROMDATE"]; ?>" type="text" required="">
                              </div>
                              <div class="col-md-6">
                                  <select <?php echo ($leaveentryId > 0) ? 'disabled="disabled"' : 'required'; ?> 
                                    class="form-control input-md strict-field" 
                                    id="FROMHALF" 
                                    name="FROMHALF"
                                    onchange="getLeaveBalance();" 
                                    >
                                <option value="">Select Session</option>
                                <option value="1" <?php echo ($arr_leave_details["FROMHALF"] == '1') ? 'selected' : ''; ?>>First Half</option>
                                <option value="2" <?php echo ($arr_leave_details["FROMHALF"] == '2') ? 'selected' : ''; ?>>Second Half</option>
                            </select>

                              </div>
                          </div>
                      </div>
                      <div class="col-md-6">
                          <label style="text-align:left;" class="col-md-4 control-label" for="TODATE">To Date&nbsp;<span style="color:red;">*</span></label>
                          <div class="col-md-8" style="padding-left: 0px;padding-right: 0px;">
                              <div class="col-md-6">
                                  <input <?php echo ($leaveentryId > 0) ? 'disabled="disabled"' : 'required=""'; ?> class="form-control input-md strict-field" id="TODATE" name="TODATE" value="<?php echo $arr_leave_details["TODATE"]; ?>" type="text" required="">
                              </div>
                              <div class="col-md-6">
                                  <select <?php echo ($leaveentryId > 0) ? 'disabled="disabled"' : 'required'; ?> 
                                        class="form-control input-md strict-field" 
                                        id="TOHALF" 
                                        name="TOHALF" 
                                        onchange="getLeaveBalance();">
                                    <option value="">Select Session</option>
                                    <option value="1" <?php echo ($arr_leave_details["TOHALF"] == '1') ? 'selected' : ''; ?>>First Half</option>
                                    <option value="2" <?php echo ($arr_leave_details["TOHALF"] == '2') ? 'selected' : ''; ?>>Second Half</option>
                                </select>

                              </div>
                          </div>
                      </div>
                  </div>



                  <div class="form-group">
                      <div class="col-md-6">
                          <label style="text-align:left;" class="col-md-4 control-label" for="Authorized">Authorize By&nbsp;<span style="color:red;">*</span></label>
                          <div class="col-md-8">
                              <!--                         <select id="ISAutherizedby" class="form-control js-example-basic-single" name="ISAutherizedby" style="width: 250px;" >
                                </select>  -->
                              <input type="text" id="Authorized" class="form-control strict-field" value="<?php echo isset($arr_leave_details['AUTHORIZEDBYNAME']) ? $arr_leave_details['AUTHORIZEDBYNAME'] : '' ?>" name="Authorized" placeholder="Search Your Employee Name" style="width: 250px;" required="" />
                              <input type="hidden" id="ISAutherizedby" name="ISAutherizedby" value="<?php echo isset($arr_leave_details['ISAutherizedby']) ? $arr_leave_details['ISAutherizedby'] : '' ?>" />
                              <span id="MouseAuth"></span>
                          </div>
                      </div>
                      <div class="col-md-6">
                          <label style="text-align:left;" class="col-md-4 control-label" for="APPROVEDBY">Approve By&nbsp;<span style="color:red;">*</span></label>
                          <div class="col-md-8">
                             <select id="APPROVEDBY" class="form-control select2-searching input-md strict-field" name="APPROVEDBY" style="width: 250px;" required class="form-control input-md">
                            <option value="" disabled selected hidden>--Select--</option>
                            
                            <?php foreach ($arr_employees as $index => $employee) { ?>
                                <option value="<?php echo $employee['ei']['emp_pkey']; ?>" <?php echo ($index === 0) ? 'selected' : ''; ?>>
                                    <?php echo ($employee['ei']['EmpName'] . ' - ' . $employee['ei']['employee_id']); ?>
                                </option>
                            <?php } ?>
                          </select>
<!--                              <input type="text" id="APPROVED" class="form-control input-md strict-field" name="APPROVED" value="<?php //echo isset($arr_leave_details['APPROVEDBYNAME']) ? $arr_leave_details['APPROVEDBYNAME'] : '' 
                                                                                                                                    ?>" placeholder="Search Your Employee Name" style="width: 250px;" required="" />
                              <input type="hidden" id="APPROVEDBY" class="form-control" name="APPROVEDBY" value="<?php echo isset($arr_leave_details['APPROVEDBY']) ? $arr_leave_details['APPROVEDBY'] : '' ?>" />
                              <span id="MouseAppr"></span>-->

                          </div>
                      </div>
                  </div>

                  <div class="form-group">
                      <div class="col-md-6">
                          <label style="text-align:left;" class="col-md-4 control-label" for="Reason">Reason&nbsp;<span style="color:red;">*</span></label>
                          <div class="col-md-8">
                              <textarea id="Reason" name="Reason" type="text" placeholder="Reason" class="form-control input-md strict-field" required=""  maxlength="50"><?php echo $arr_leave_details["Reason"]; ?></textarea>
                          </div>
                      </div>
                      <div class="col-md-6">
                          <label style="text-align:left;" class="col-md-4 control-label" for="contact_No">Contact Number&nbsp;</label>
                          <div class="col-md-8">
                              <input id="contact_No" name="contact_No" value="<?php echo $arr_leave_details["contact_No"]; ?>" type="tel" placeholder="Contact Number" class="form-control input-md strict-field">
                          </div>
                      </div>
                  </div>

                  <!-- <div class="form-group">

                      <div class="col-md-6">
                          <label style="text-align:left;" class="col-md-4 control-label" for="leave_days">CC to</label>
                          <div class="col-md-8">
                              <input id="Notified" name="Notified" value="" type="text" placeholder="CC to" class="form-control input-md" style="width: 250px;">
                              <input type="hidden" id="Notifiedby" name="Notifiedby" value="" />
                          </div>
                      </div>
                      <div class="col-md-6">
                          <label style="text-align:left;" class="col-md-4 control-label" for="cc">Notify to&nbsp;</label>
                          <div class="col-md-8">
                              <select style="width:100%;" multiple id="cc" name="cc[]" placeholder="Notify to" class="form-control select2-searching input-md strict-field">
                                  <option>Select</option>
                                  <?php foreach ($arr_users as $users) {
                                    ?>
                                      <option value="<?php echo $users['emp_details']['emp_pkey']; ?>"><?php echo $users['0']['name']; ?></option>
                                  <?php
                                    }
                                    ?>
                              </select>
                          </div>
                      </div>
                  </div> -->
                  <div class="form-group">
                      <div class="col-md-6">
                          <label style="text-align:left;" class="col-md-4 control-label" for="contact_person">Duties handed over to</label>
                          <div class="col-md-8">
                              <!--<input id="contact_person" name="contact_person" value="<?php echo $arr_leave_details["contact_person"]; ?>" type="text" placeholder="Duties handed over to" class="form-control input-md strict-field" required="" >-->
                              <select style="width:100%; " id="contact_person" name="contact_person" placeholder="Duties handed over to" class="form-control select2-searching input-md strict-field">
                                  <option>Select</option>
                                  <?php if (isset($arr_leave_details["contact_person"])) { ?>
                                      <option selected="selected" value="<?php echo $arr_leave_details["contact_person"]; ?>"><?php echo $arr_leave_details["contact_person"]; ?></option>
                                  <?php } ?>
                                  <?php foreach ($arr_users as $users) {
                                    ?>
                                      <option value="<?php echo $users['0']['name']; ?>"><?php echo $users['0']['name']; ?></option>
                                  <?php
                                    }
                                    ?>
                              </select>
                          </div>
                      </div>

                      <div class="col-md-6">
                          <label style="text-align:left;display: flex;" class="col-md-4 control-label" for="file">Upload File <span id="mandatory" style="color:red;display:none;">&nbsp;*</span></label>
                          <div class="col-md-8">
                              <input id="image" name="image" value="" type="file" accept=".pdf, image/jpeg, image/png, image/jpg" placeholder="Upload file" class="form-control input-md">
                          </div>
                      </div>
                  </div>
                  <div class="form-group">
                      <div class="col-md-6">
                          <label style="text-align:left;display: flex;" class="col-md-4 control-label" for="File">Uploaded File Name <span id="mandatoryfilename" style="color:red;display:none;">&nbsp;*</span></label>
                          <div class="col-md-8">
                              <input id="filename" name="filename" value="" type="text" placeholder="Uploaded File Name" class="form-control input-md strict-field">
                          </div>
                      </div>
                      <div class="col-md-6" style="display: none;">
                          <label style="text-align:left;" class="col-md-4 control-label" for="leave_days">Number of days</label>
                          <div class="col-md-8">
                              <input id="leave_days" name="leave_days" value="<?php echo $arr_leave_details["leave_days"]; ?>" type="text" placeholder="Number of days" class="form-control input-md">
                          </div>
                      </div>
                  </div>
          </div>
      <?php } else { ?>
          <div class="modal-body">
              <div class="box-body">
                  <input type="hidden" name="salary_head_item_fkey" value="<?php echo $arr_leave_details['salary_head_item_fkey']; ?>">
                  <input name="FROMDATE" value="<?php echo $arr_leave_details["FROMDATE"]; ?>" type="hidden" required="">
                  <input name="TODATE" value="<?php echo $arr_leave_details["TODATE"]; ?>" type="hidden" required="">
                  <input type="hidden" name="FROMHALF" value="<?php echo $arr_leave_details["FROMHALF"]; ?>">
                  <input type="hidden" value="<?php echo $arr_leave_details['TOHALF']; ?>" name="TOHALF">
                  <input type="hidden" value="<?php echo $arr_leave_details['LEAVESTATUS']; ?>" name="LEAVESTATUS">
                  <div class="row>" <span style="font-size: 28px;padding-left: 15px;margin-top: -22px;text-align: center;">Leave Details</span><span style="font-size: 16px;font-weight: lighter;padding-left: 1px;"> (<?php echo $arr_leave['0']['salary_head_items']['item'] ?>)</span> </div>
                  <hr style="margin-top: 4px;">
                  <div class="row" style="padding-left: 15px;margin-top: -15px;">
                      <div class="col-md-4 col-sm-4 col-xs-4" style="font-size: 16px;font-weight: 600;"><?php echo $arr_leave_details["FROMDATE"]; ?><span style="font-size: 15px;font-weight: 300"><?php echo isset($arr_leave_details["FROMHALF"]) && $arr_leave_details["FROMHALF"] == "1" ? '&nbsp;(First Half)' : '&nbsp;(Second Half)' ?></span></div>
                      <div class="col-md-1 col-sm-1 col-xs-1" style="padding-top: 4px;"> To</div>
                      <div class="col-md-4 col-sm-4 col-xs-4" style="font-size: 16px;font-weight: 600;"><?php echo $arr_leave_details["TODATE"]; ?><span style="font-size: 15px;font-weight: 300"><?php echo isset($arr_leave_details["TOHALF"]) && $arr_leave_details["TOHALF"] == "1" ? '&nbsp;(First Half)' : '&nbsp;(Second Half)' ?></span> </div>
                      <div class="col-md-1 col-sm-1 col-xs-1" style="padding-top: 4px;">-</div>
                      <div class="col-md-2 col-sm-2 col-xs-2" style="font-size: 16px;font-weight: 600;"><?php echo $arr_leave_details['leave_days'] ?>&nbsp;Day(s)</div>
                  </div>
                  <hr style="margin-top: 2px;margin-bottom: 9px;">
                  <div class="form-group ">
                      <div class="row" style="padding-top: 2px;padding-left: 30px;">
                          <div class="col-md-3 col-sm-2 col-xs-2">
                              <span>
                                  Applied date
                              </span>
                          </div>

                          <div class="col-md-3 col-sm-4 col-xs-4">
                              <b> : <?php echo $arr_leave_details['applied_date'] ?></b>
                          </div>
                          <div class="col-md-3 col-sm-2 col-xs-2">
                              <span>
                                  Leave Status
                              </span>
                          </div>

                          <div class="col-md-3 col-sm-4 col-xs-4">
                              : <b id="label-leavestatus"><?php echo $arr_leave_details['LEAVESTATUS']; ?></b>
                          </div>

                      </div>
                      <div class="row" style="padding-top: 12px;padding-left: 30px;">
                          <div class="col-md-3 col-sm-2 col-xs-2">
                              <span>
                                  Reason
                              </span>
                          </div>


                          <?php if (in_array($arr_leave_details['LEAVESTATUS'], array('Approved', 'Authorized'))) { ?>
                              <div class="col-md-3 col-sm-4 col-xs-4" id="showreason">
                                  :<textarea id="Reason" name="Reason" type="text" placeholder="Reason" style="margin-left: 7px;margin-top: -15px;" class="form-control input-md " maxlength="50"><?php echo $arr_leave_details["Reason"]; ?></textarea>
                              </div>
                          <?php } else { ?>
                              <div class="col-md-3 col-sm-4 col-xs-4">
                                  <b id='Reasons'> : <?php echo $arr_leave_details['Reason'] ?></b>
                                  <textarea id="Reason" name="Reason" type="text" placeholder="Reason" style="margin-left: 7px;margin-top: -15px; display:none;" class="form-control input-md "><?php echo $arr_leave_details["Reason"]; ?></textarea>
                              </div>
                          <?php   }
                            ?>

                          <div class="col-md-3 col-sm-3 col-xs-3">
                              <span>
                                  Duties Handover to
                              </span>
                          </div>

                          <div class="col-md-3 col-sm-4 col-xs-4">
                              <b> : <?php echo $arr_leave_details['contact_person'] ?></b>
                          </div>

                      </div>
                      <!--<hr style="margin-left: 17px;margin-right: 17px;border-color: silver;margin-top: 9px;margin-bottom: 2px">-->
                      <div class="row" style="padding-top: 12px;padding-left: 30px;">
                          <div class="col-md-3 col-sm-2 col-xs-2">
                              <span>
                                  Authorized By
                              </span>
                          </div>

                          <div class="col-md-3 col-sm-4 col-xs-4">
                              <b> : <?php echo $arr_leave_details['AUTHORIZEDBYNAME'] ?></b>
                          </div>
                          <div class="col-md-3 col-sm-2 col-xs-2">
                              <span>
                                  Authorized Date
                              </span>
                          </div>

                          <div class="col-md-3 col-sm-4 col-xs-4">
                              <b> : <?php echo isset($arr_leave_details['Autherized_date']) && $arr_leave_details['Autherized_date'] == '0000-00-00' ? '' : $arr_leave_details['Autherized_date'] ?></b>
                          </div>

                      </div>
                      <div class="row" style="padding-top: 12px;padding-left: 30px;">
                          <div class="col-md-3 col-sm-2 col-xs-2">
                              <span>
                                  Approved By
                              </span>
                          </div>

                          <div class="col-md-3 col-sm-4 col-xs-4">
                              <b> : <?php echo $arr_leave_details['APPROVEDBYNAME'] ?></b>
                          </div>
                          <div class="col-md-3 col-sm-2 col-xs-2">
                              <span>
                                  Approved Date
                              </span>
                          </div>

                          <div class="col-md-3 col-sm-4 col-xs-4">
                              <b> : <?php echo $arr_leave_details['APPROVED_date'] ?></b>
                          </div>

                      </div>
                      <div class="row" style="padding-top: 12px;padding-left: 30px;">
                          <div class="col-md-3 col-sm-2 col-xs-2">
                              <span>
                                  Remarks By Authorized Person
                              </span>
                          </div>

                          <div class="col-md-3 col-sm-6 col-xs-6">
                              <b> : <?php echo $arr_leave_details['AuthoriseRemarks'] ?></b>
                          </div>
                          <div class="col-md-3 col-sm-2 col-xs-2">
                              <span>
                                  Remarks By Approved Person
                              </span>
                          </div>

                          <div class="col-md-3 col-sm-6 col-xs-6">
                              <b> : <?php echo isset($arr_leave_details['ApproveRemarks']) ? $arr_leave_details['ApproveRemarks'] : ''; ?></b>
                          </div>
                      </div>
                      <div class="row" style="padding-top: 12px;padding-left: 30px;">
                          <div class="col-md-3 col-sm-2 col-xs-2">
                              <span>
                                  Documents
                              </span>
                          </div>


                          <div class="col-md-3 col-sm-6 col-xs-6">
                              <?php  
    $docs = isset($arr_leave_details['file_name']) ? $arr_leave_details['file_name'] : '';
    $name = isset($arr_leave_details['file_type']) ? $arr_leave_details['file_type'] : '';
    $fielddocs = explode(',', $docs);
    $name = explode(',', $name);
    $i = 0; 
    $leaveid = $arr_leave_details['LEAVEENTRYID'];
    $lstatus = $arr_leave_details['LEAVESTATUS'];

    foreach ($fielddocs as $doc) {
        $nm = $name[$i];

        // PDF case
        if (strpos($doc, 'pdf') !== false) { ?> 
            <ul>
                <li style="margin:2px;">: 
                    <b><?php echo $nm; ?></b>&nbsp;&nbsp; 
                    <span class="tooltiptext">
                        <a href="<?php echo $path.$doc; ?>" target="_blank">
                            <i class="fa fa-file-pdf-o tooltip1" style="font-size:15px;color:#3c8dbc;"></i>
                        </a>&nbsp;
                        <i class="fa fa-trash tooltip1" style="font-size:15px;color:red;cursor:pointer;" 
                           onclick="deleteleavedocument(<?php echo $leaveid; ?>,'<?php echo $lstatus; ?>','<?php echo $nm; ?>','<?php echo $doc; ?>');">
                        </i>
                    </span>
                </li>
            </ul>
        <?php  
        // Image case
        } else if (strpos($doc, 'g') !== false || strpos($doc, 'G') !== false) { ?>
            <ul>
                <li style="margin:2px;">: 
                    <b><?php echo $nm; ?></b>&nbsp;&nbsp; 
                    <span class="tooltiptext">
                        <a href="<?php echo $path.$doc; ?>" target="_blank">
                            <i class="fa fa-file-image-o tooltip1" style="font-size:15px;color:#3c8dbc;"></i>
                        </a>&nbsp;
                        <i class="fa fa-trash tooltip1" style="font-size:15px;color:red;cursor:pointer;" 
                           onclick="deleteleavedocument(<?php echo $leaveid; ?>,'<?php echo $lstatus; ?>','<?php echo $nm; ?>','<?php echo $doc; ?>');">
                        </i>
                    </span>
                </li>
            </ul>
        <?php } 

        $i++; 
    } ?>

                          </div>

                      </div>
                  </div>
              </div>
          </div>
      <?php } ?>
      <div class="modal-footer">
          <div id="loader" class="col-md-8" style="display:none;">
              <span class="btn btn-success">
                  <li class="fa fa-spinner fa-spin"></li>Loading Please Wait ....
              </span>
          </div>

          <!-- Need to cancel auth / approved leaves also : On 11 March 2017 -->
          <?php if (!in_array($arr_leave_details['LEAVESTATUS'], array('Cancelled', 'Cancellation Authorized', 'Rejected'))) { ?>
              <?php if (in_array($arr_leave_details['LEAVESTATUS'], array('Applied', 'Approved', 'Authorized'))) { ?>
                  <button type="button" id="btn-cancel" class="btn btn-danger" onclick="cancelLeave()" style="display: <?php
                                                                                                                        if ($leaveentryId == 0) {
                                                                                                                            echo "none";
                                                                                                                        }
                                                                                                                        ?>">
                      Cancel Leave
                  </button>
              <?php } ?>
              <?php if (!in_array($arr_leave_details['LEAVESTATUS'], array('Authorized', 'Approved', 'Applied', 'Cancelled', 'Cancellation Authorized', 'Rejected', 'Cancelled', 'Cancellation Authorized', 'Rejected', 'Cancellation Approved', 'CancellationOfApproved'))) { ?>

             <button type="submit" id="btn-submit" 
        class="btn btn-primary strict-field">
    Save
</button>


              <?php } ?>
          <?php } ?>
      </div>
  </form>
  <script>
    

$('#TODATE').datepicker({
    format: "yyyy-mm-dd",
    autoclose: true
});


    $.ajax({
       url: livesite + 'LeaveRequest/getEmployeeDates/',
        type: 'GET',
        dataType: 'json',
        success: function(resp) {
            if (resp.joining_date) {
                var joiningDate = resp.joining_date;
                var terminationDate = resp.termination_date;

                // FROMDATE picker
                $('#FROMDATE').datepicker('setStartDate', joiningDate);
                if (terminationDate) {
                    $('#FROMDATE').datepicker('setEndDate', terminationDate);
                } else {
                    $('#FROMDATE').datepicker('setEndDate', null);
                }

                // TODATE picker
                $('#TODATE').datepicker('setStartDate', joiningDate);
                if (terminationDate) {
                    $('#TODATE').datepicker('setEndDate', terminationDate);
                } else {
                    $('#TODATE').datepicker('setEndDate', null);
                }

                // Optional: clear previous selections
                $('#FROMDATE').val('');
                $('#TODATE').val('');
            }
        },
        error: function(err) {
            alert('Error fetching employee dates!');
            console.log(err);
        }
    });


      function deleteleavedocument(id, status, name, doc) {

          if (status !== "Applied") {
              alert("Document can't delete.Leave is not in Applied status");
          } else {
              if (confirm("Do you want to delete the selected document?")) {
                  $.ajax({
                      url: livesite + 'LeaveRequest/deletedoc/' + id + '/' + name + '/' + doc,
                      success: function(resp) {
                          var response = $.parseJSON(resp);
                          //console.log(response);
                          alert(response.message);
                          if (response.status == 1) {
                              $.notify(response.message, {
                                  type: 'success',
                                  allow_dismiss: true

                              });
                              //  closediv(response.taxHeadKeyValue);
                              showLargeModalForm(livesite + 'LeaveRequest/addeditleave_new/' + id);
                              //  showTaxHeadDetailsshow(response.taxHeadKey,response.emp_pkey);
                          }
                      }
                  });
              }
          }
      }
      // function listEmployees(){
      //          $("#ISAutherizedby").select2(
      //                {
      //                    placeholder: "Select",
      //                    allowClear: true,
      //                    ajax: {
      //                        url: livesite + "LeaveRequest/emplist/",
      //                        dataType: 'json',
      //                        delay: 250,
      //                        data: function (params) {
      //                            return {
      //                                q: params.term, // search term
      //                                page: params.page
      //                            };
      //                        },
      //                        processResults: function (data, params) {
      //                            // parse the results into the format expected by Select2
      //                            // since we are using custom formatting functions we do not need to
      //                            // alter the remote JSON data, except to indicate that infinite
      //                            // scrolling can be used
      //                            params.page = params.page || 1;
      //
      //                            return {
      //                                results: data.items,
      //                                pagination: {
      //                                    more: (params.page * 30) < data.total_count
      //                                }
      //                            };
      //                        }
      //                    },
      //                    escapeMarkup: function (markup)
      //                    {
      //                        return markup;
      //                    }
      //                });
      //      }
      //      function listaprEmployees(){
      //          $("#APPROVEDBY").select2(
      //                {
      //                    placeholder: "Select",
      //                    allowClear: true,
      //                    ajax: {
      //                        url: livesite + "LeaveRequest/apremplist/",
      //                        dataType: 'json',
      //                        delay: 250,
      //                        data: function (params) {
      //                            return {
      //                                q: params.term, // search term
      //                                page: params.page
      //                            };
      //                        },
      //                        processResults: function (data, params) {
      //                            // parse the results into the format expected by Select2
      //                            // since we are using custom formatting functions we do not need to
      //                            // alter the remote JSON data, except to indicate that infinite
      //                            // scrolling can be used
      //                            params.page = params.page || 1;
      //
      //                            return {
      //                                results: data.items,
      //                                pagination: {
      //                                    more: (params.page * 30) < data.total_count
      //                                }
      //                            };
      //                        }
      //                    },
      //                    escapeMarkup: function (markup)
      //                    {
      //                        return markup;
      //                    }
      //                });
      //      }


      var leaverequestoptions = {};
      jQuery(document).ready(function() {
          $('#mandatory').hide();
          $('#mandatoryfilename').hide();
          //listEmployees();
          //listaprEmployees();
          <?//php if (isset($leaveentryId) && $leaveentryId != 0) { ?>
            //   getLeaveBalance();
          <?//php } else { ?>
              $('#Leavebalance').hide();
              $('#balance').hide();
              $('#showremarks').hide();
          <?//php } ?>
          var usersoptions = {
              url: function(phrase) {
                  //return livesite + 'LeaveRequest/getusers?username=' + phrase;
                  //edited by megha on 11_6_19 authorised by hierarchy persons only
                  return livesite + 'LeaveRequest/getusers?username=' + phrase + '&action=auth';
              },
              getValue: "full_name",
              list: {
                  onClickEvent: function() {
                      console.log('onClickEvent');
                      var selectedItem = $("#Authorized").getSelectedItemData();
                      var site_fkey = selectedItem.emp_pkey;
                      $("#ISAutherizedby").val(site_fkey);
                      $('#MouseAuth').html('');

                  },
                  onKeyEnterEvent: function() {
                      console.log('onKeyEnterEvent');
                  },
                  onMouseOverEvent: function() {
                      console.log('onMouseOverEvent');
                  }
              }
          };

          $('#Authorized').easyAutocomplete(usersoptions);


          var usersoptionsappr = {
              url: function(phrase) {
                  //return livesite + 'LeaveRequest/getusers?username=' + phrase;
                  //edited by megha on 11_6_19 approved by hierarchy persons only
                  return livesite + 'LeaveRequest/getusers?username=' + phrase + '&action=apr&uname=' + $("#ISAutherizedby").val();
              },
              getValue: "full_name",
              list: {
                  onClickEvent: function() {
                      var selectedItem = $("#APPROVED").getSelectedItemData();
                      var site_fkey = selectedItem.emp_pkey;
                      //  alert(site_fkey); 

                      $('#MouseAppr').html('');
                      $("#APPROVEDBY").val(site_fkey);

                  }
              }
          };

          $('#APPROVED').easyAutocomplete(usersoptionsappr);

          var usersoptionsnotify = {
              url: function(phrase) {
                  return livesite + 'LeaveRequest/getusers_notify?username=' + phrase;
              },
              getValue: "full_name",
              list: {
                  onClickEvent: function() {
                      console.log('onClickEvent');
                      var selectedItem = $("#Notified").getSelectedItemData();
                      console.log(selectedItem);
                      var site_fkey = selectedItem.emp_pkey;
                      $("#Notifiedby").val(site_fkey);
                      $('#MouseAuth').html('');

                  },
                  onKeyEnterEvent: function() {
                      console.log('onKeyEnterEvent');
                  },
                  onMouseOverEvent: function() {
                      console.log('onMouseOverEvent');
                  }
              }
          };
          $('#Notified').easyAutocomplete(usersoptionsnotify);

          $('#salary_head_item_fkey').on('change', function() {
              //                $('#loader').fadeIn(1000);
            //   getLeaveBalance();
            $('#FROMDATE').val('');
            $('#Leavebalance').hide();
            $('#balance').hide();
            
            
          })
          $('#contact_person').select2();
          $('#cc').select2();
        //   $('#FROMDATE').datepicker({
        //       format: 'yyyy-mm-dd',
        //       autoclose: true
        //   }).on('changeDate', function(e) {
        //       // `e` here contains the extra attributes
        //       var selected = $("#FROMDATE").val();
        //       var sdt = new Date(selected);
        //       var selectenddate = $("#TODATE").val();
        //       var edt = new Date(selectenddate);
        //       if (edt < sdt) {
        //           alert("From date should be less than To date");
        //           $("#FROMDATE").val('');
        //       }
        //       getLeaveBalance();
        //   });

        $('#FROMDATE').datepicker({
    format: 'yyyy-mm-dd',
    autoclose: true
}).on('changeDate', function (e) {
    var startDate = $("#FROMDATE").val();
    var endDate   = $("#TODATE").val();
    var leaveType = $('#salary_head_item_fkey').val();

    if (!leaveType || !startDate) return;

    $.ajax({
        url: livesite + 'LeaveRequest/GetLeaveBalanceNew/' + leaveType + '/' + startDate + '/' + endDate,
        type: 'POST',
        data: {
            start_date: startDate,
            end_date: endDate,
            start_sess: $('#FROMHALF').val(),
            end_sess: $('#TOHALF').val()
        },
        success: function (resp) {
            var data = $.parseJSON(resp);

            // 🔹 Min Service
            if (data.min_service_flag === false) {
                alert(data.min_service_msg);
                // $('.disable_first').attr('disabled', true);
                $('#FROMDATE').val('');
                $('#btn-submit').prop("disabled", true);
              $("#FROMDATE").val('').prop("disabled", true);
                $("#TODATE").val('');
                return;
            }

            // 🔹 Advance Notice
            if (data.adv_notice_flag === false) {
                alert(data.adv_notice);
                // $('.disable_first').attr('disabled', true);
                $('#FROMDATE').val('');
                $('#btn-submit').prop("disabled", true);
                 $("#FROMDATE").val('').prop("disabled", true);
                $("#TODATE").val('');
                return;
            }

            // ✅ If eligible → then proceed to get balance
            getLeaveBalance();
        }
    });
});

          $("#FROMDATE").inputmask("yyyy-mm-dd");

          function validateLeave(trigger) {
    var endDate   = $("#TODATE").val();
    var startDate = $("#FROMDATE").val();

    if (!startDate || !endDate) return;

    var edt = new Date(endDate);
    var sdt = new Date(startDate);

    if (edt < sdt) {
        alert("To date should be greater than or equal to From date");
        $("#TODATE").val('');
        $("#btn-submit").prop("disabled", true);
        return false;
    }

    var salary_head_item_fkey = $('#salary_head_item_fkey').val();

    $.ajax({
        url: livesite + 'LeaveRequest/GetLeaveBalanceNew/' + salary_head_item_fkey + '/' + startDate + '/' + endDate,
        type: 'POST',
        data: { 
            start_date: startDate, 
            end_date: endDate,
            start_sess: $('#FROMHALF').val(),
            end_sess: $('#TOHALF').val()
        },
        success: function (resp) {
            var data = $.parseJSON(resp);
            var leave_balance = parseFloat(data.yearly_balance);
            
            var policyType = data.leave_policy_type;
                    var attStart   = data.att_start_date; // "01-09-2025"
                    var attEnd     = data.att_end_date;   // "30-09-2025"

            // if (policyType === 'M' && attStart && attEnd) {
    let attStartDate = new Date(attStart + "T00:00:00");
    let attEndDate   = new Date(attEnd + "T23:59:59");

    if (sdt < attStartDate || sdt > attEndDate) {
        alert("From Date is outside the range (" + attStart + " to " + attEnd + ")");
        $('#FROMDATE').val('');
        return;
    }

    if (edt && (edt < attStartDate || edt > attEndDate)) {
        alert("To Date is outside the range (" + attStart + " to " + attEnd + ")");
        $('#TODATE').val('');
        return;
    }
// } 


            // 🔹 Calculate number of days (with half-day handling)
//             var diffDays = Math.floor((edt - sdt) / (1000 * 60 * 60 * 24)) + 1;

//             if (sdt.getTime() === edt.getTime()) {
//     let fromHalf = $('#FROMHALF').val();
//     let toHalf   = $('#TOHALF').val();

//     if (fromHalf === toHalf) {
//         diffDays = 0.5;
//     } else if (fromHalf === '1' && toHalf === '2') {
//         diffDays = 1;
//     } else {
//         diffDays = 0; // not valid until both halves selected
//     }
// }

// CALCULATE LEAVE DAYS GENERICALLY
// ------------------------------
let startSess = $('#FROMHALF').val(); // 1 = First Half, 2 = Second Half
let endSess   = $('#TOHALF').val();
let totalDays = 0;

if (sdt.getTime() === edt.getTime()) {

    // SAME DAY LEAVE
    if (startSess === '1' && endSess === '1') totalDays = 0.5;
    else if (startSess === '2' && endSess === '2') totalDays = 0.5;
    else if (startSess === '1' && endSess === '2') totalDays = 1;
    else totalDays = 0; // invalid until both selected

} else {

    // MULTI-DAY LEAVE
    let daysBetween = Math.floor((edt - sdt) / (1000 * 60 * 60 * 24)) + 1;

    // First day
    totalDays += (startSess === '1') ? 1 : 0.5;

    // Middle days (if any)
    if (daysBetween > 2) {
        totalDays += (daysBetween - 2); // full days
    }

    // Last day
    totalDays += (endSess === '2') ? 1 : 0.5;
}

// final diffDays result
diffDays = totalDays;

// ✅ only check balance if diffDays > 0
if (diffDays > 0 && leave_balance < diffDays) {
    alert("You do not have enough leave balance");
    $("#TODATE").val('').prop("disabled", true);
    $("#btn-submit").prop("disabled", true);
    return false;
}

            if (trigger === 'sessionChange') {
                if (data.min_leave_limit && diffDays < parseFloat(data.min_leave_limit)) {
                    alert("Minimum " + data.min_leave_limit + " day(s) leave required.");
                    $("#TODATE").val('').prop("disabled", false);
                    $("#TODATE").val('').prop("disabled", true);
                    
                    $("#btn-submit").prop("disabled", true);
                    // $("#emp_fkey1").val('');       // no trigger
                    // $("#leave_type").val('');      // no trigger


                    return false;
                }
            }

            // ✅ Max limit check (only on date change)
            if (trigger === 'dateChange') {
                if (data.max_leave_limit && diffDays > parseFloat(data.max_leave_limit)) {
                    alert("Maximum allowed leave is " + data.max_leave_limit + " day(s).");
                    $("#TODATE").val('').prop("disabled", true);
                    $("#btn-submit").prop("disabled", true);
                    return false;
                }
            }

            // ✅ If all checks passed → enable submit
            $("#TODATE, #TOHALF").prop("disabled", false);
            $("#btn-submit").prop("disabled", false);
        }
    });
}


// 🔹 Validate max on end date change
$('#TODATE').on('changeDate', function () {
    $("#TODATE").prop("disabled", false); // re-enable for retry
    validateLeave('dateChange');
});

// 🔹 Validate min on session change
$('#TOHALF').on('change', function () {
    $("#TOHALF").prop("disabled", false); // re-enable for retry
    if ($("#TODATE").val()) {
        validateLeave('sessionChange');
    }
});


          $("#TODATE").inputmask("yyyy-mm-dd");

          $('#myleaverequestform').parsley();

          leaverequestoptions = {
              //  target:        '#output2',   // target element(s) to be updated with server response 
              //  beforeSubmit:  showRequest,  // pre-submit callback 
              success: function(resp) {
                  var success = $.parseJSON(resp).success;
                  var message = $.parseJSON(resp).message;
                  if (success == false) {
                      alert(message);
                      $('#btn-submit').html('Save').prop('disabled', false);
                      $('#btn-cancel').html('Cancel Leave...').attr('disabled', 'false');
                      return;
                  }

                  var warning = $.parseJSON(resp).warningmessage;
                  var leaveentryid = $.parseJSON(resp).leaveentryId;
                  var leavestatus = $.parseJSON(resp).leavestatus;
                  $('#myleaverequestform #LEAVEENTRYID').val(leaveentryid);
                  $('#myleaverequestform #btn-cancel').show();
                  $('#label-leavestatus').html(leavestatus);
                  $('#div-leavestatus').show();
                  console.log(leavestatus);
                  /*
                   * Show Leave dates here
                   * On 03 Aug 2015
                   */
                  reloadTable('myleaverequeststable');
                  if (warning != '') {
                      alert(warning);
                      $('#btn-submit').html('Save').prop('disabled', false);
                  } else {
                      //Calculate no of leave days here
                      $('#largeModalForm').modal('hide');
                      // showSmallModalForm(livesite + 'LeaveRequest/showleavedays/' + leaveentryid);
                  }
                  //Ends
              }, // post-submit callback
              error: function() {
                  $('#largeModalForm').modal('hide');
                  alert('Sorry for the inconvenience, please contact support');
              }

              // other available options: 
              //url:       url         // override for form's 'action' attribute 
              //type:      type        // 'get' or 'post', override for form's 'method' attribute 
              //dataType:  null        // 'xml', 'script', or 'json' (expected server response type) 
              //clearForm: true        // clear all form fields after successful submit 
              //resetForm: true        // reset the form after successful submit 

              // $.ajax options can be used here too, for example: 
              //timeout:   3000 
          };

          $('#APPROVED').keyup(function() {
              $('#APPROVEDBY').val('');
          });

          $('#Authorized').keyup(function() {
              $('#ISAutherizedby').val('');
          });

          // bind to the form's submit event 
          $('#myleaverequestform').submit(function(event) {
            //   event.preventDefault();
              var yearly_balance = $('#hid_yearly_balance').val();
            //   var monthly_balance = $('#hid_monthly_balance').val();

              if ($('#ISAutherizedby').val() == '') {
                  var texts = "Please select by Mouse click";
                  $('#MouseAuth').html(texts).css("color", "red");
                  return false;
              }
              if ($('#APPROVEDBY').val() == '') {
                  var texts = "Please select by Mouse click";
                  $('#MouseAppr').html(texts).css("color", "red");
                  return false;
              }
              if (yearly_balance == 0 && monthly_balance == 0 && $('#myleaverequestform #LEAVEENTRYID').val() <= 0) {
                  alert("You don't have sufficient leave balance!");
                  return false;
              }
              var start = $('#FROMDATE').datepicker('getDate');
              var end = $('#TODATE').datepicker('getDate');
              var days = (end - start) / 1000 / 60 / 60 / 24;
              $('#leave_days').val(days + 1);
              if ($('#myleaverequestform #LEAVEENTRYID').val() > 0) {
                  var r = confirm("Do you want to resubmit leave ?");
                  if (r == true) {
                      $('#btn-submit').html('<li class="fa fa-spinner fa-spin"></li> saving...').prop('disabled', 'disabled');
                      // inside event callbacks 'this' is the DOM element so we first 
                      // wrap it in a jQuery object and then invoke ajaxSubmit 
                      $(this).ajaxSubmit(leaverequestoptions);
                  } else {
                      return false;
                  }
              } else {
                  $('#btn-submit').html('<li class="fa fa-spinner fa-spin"></li> saving...').prop('disabled', 'disabled');
                  $(this).ajaxSubmit(leaverequestoptions);
                  $('#largeModalForm').modal('hide');
              }
              // !!! Important !!! 
              // always return false to prevent standard browser submit and page navigation 
              return false;
          });
      });

      function cancelLeave() {
          if (confirm("Really you want to cancel leave?")) {

              if ($.trim($('#label-leavestatus').html()) == 'Applied')
                  $('#myLeaveAction').val('Cancelled');
              else if ($.trim($('#label-leavestatus').html()) == 'Authorized')
                  $('#myLeaveAction').val('Cancellation Applied');
              else if ($.trim($('#label-leavestatus').html()) == 'Approved')
                  $('#myLeaveAction').val('Cancellation Applied');

              //Edited by Akshay on 19-2-2024
              var reasonValue = <?php echo json_encode($arr_leave_details['Reason']); ?>;
              $('#Reason').val(reasonValue);
              //                alert($.trim($('#label-leavestatus').html()));
              $('#btn-cancel').html('<li class="fa fa-spinner fa-spin"></li> Cancelling Leave...').attr('disabled', 'disabled');
              console.log('leaverequestoptions', leaverequestoptions);
              $('#myleaverequestform').ajaxSubmit(leaverequestoptions);
          }
      }

      function getLeaveBalance() {
          var salary_head_item_fkey = $('#salary_head_item_fkey').val();
          var end_date = $('#TODATE').val();
          var start_date = $('#FROMDATE').val();
          var start_sess = $('#FROMHALF').val();
          var end_sess = $('#TOHALF').val();
          
            var edt = new Date(end_date);
    var sdt = new Date(start_date);
          
          if (salary_head_item_fkey == '') {
              alert('Select any leave type');
              $('#showremarks').hide();
          } else {
              $.ajax({
                  url: livesite + 'LeaveRequest/GetLeaveBalanceNew/' + salary_head_item_fkey + '/' + start_date + '/' + end_date,
                  type: 'POST',
                  data: {
                      end_date: end_date,
                      start_date: start_date,
                      start_sess: start_sess,
                      end_sess: end_sess

                  },
                  success: function(resp) {
                      var yearly_balance = $.parseJSON(resp).yearly_balance;
                      var monthly_balance = $.parseJSON(resp).monthly_balance;
                      var remarks = $.parseJSON(resp).remarks;
                      var APPROVEDBY = $.parseJSON(resp).APPROVEDBY;
                      var APPROVEDBYNAME = $.parseJSON(resp).APPROVEDBYNAME;
                      var NotifiedBY = $.parseJSON(resp).NotifiedBY;
                      var NotifiedBYNAME = $.parseJSON(resp).NotifiedBYNAME;
                      var document_mandatory = $.parseJSON(resp).document_mandatory;
                      var compoff_available_dates = $.parseJSON(resp).compoff_available_dates;
                
                      var policyType = $.parseJSON(resp).leave_policy_type;
                    var attStart   = $.parseJSON(resp).att_start_date; // "01-09-2025"
                    var attEnd     = $.parseJSON(resp).att_end_date;   // "30-09-2025"

//                     if (compoff_available_dates && compoff_available_dates.length > 0) {
//     $("#compoff_available_dates").show();

//     let html = "<div class='d-flex flex-wrap gap-2'>";
//     $.each(compoff_available_dates, function (i, arr) {
//         $.each(arr, function (j, obj) {
//             html += `
//                 <span class="badge bg-primary p-2" style="font-size:14px;">
//                     ${obj.att_date} 
//                     <span class="badge bg-light text-dark ms-1">${obj.credit} day(s)</span>
//                 </span>
//             `;
//         });
//     });
//     html += "</div>";

//     $("#compoff_dates").html(html);

// } else {
//     $("#compoff_available_dates").hide();
//     $("#compoff_dates").html("");
// }


                      if (remarks === 'sltest') { //Edited by Akshay on 1-11-2023
                          remarks = 'Need document proof for two or more sick leaves';
                      }

                      $('#Notified').val(NotifiedBYNAME);
                      $('#Notifiedby').val(NotifiedBY);
                      if (document_mandatory == 'Y') {
                          $('#mandatory').show();
                          $('#mandatoryfilename').show();
                          $("#image").prop('required', true);
                          $("#filename").prop('required', true);
                      } else {
                          $('#mandatory').hide();
                          $('#mandatoryfilename').hide();
                          $("#image").prop('required', false);
                          $("#filename").prop('required', false);
                      }
                      //var result='<option value="'+NotifiedBY+'">'+NotifiedBYNAME+'</option>';

                      //$('#cc').html(result);
                      //$('#cc').append($('<option>', { value : NotifiedBY }).text(NotifiedBYNAME));
                      //var leavedays = $.parseJSON(resp).leavedays;
                      var leaverule = $.parseJSON(resp).leaverule;
                      //var appliedleaves = $.parseJSON(resp).appliedleaves;
                      $('#hid_yearly_balance').val(yearly_balance);
                      $('#hid_monthly_balance').val(monthly_balance);
                      $('#notified').val(NotifiedBY);
                      $('#Leavebalance').html('Available Leave Balance : ' + yearly_balance);
                      $('#showremarks').show();
                      $('#remarks').html(' ' + remarks);
                      $('#remarks').css('color', 'red');
                      $('#balance').show();
                      $('#Leavebalance').show();
                      //$('#Leavebalance').val(resp);
                      $("#APPROVEDBY").val(APPROVEDBY);
                      //  $("#APPROVED").val(APPROVEDBYNAME);
                      $("#APPROVED").val(function() {
                          return APPROVEDBYNAME;
                      });
                      var allow_negative = $.parseJSON(resp).allow_negative;
                        
                      if ( yearly_balance == 0) {
                          $('.strict-field').attr('disabled', true);
                      } else {
                          $('.strict-field').attr('disabled', false);
                      }
                      //                    if(monthly_balance < leavedays){
                      //                        alert("You have only '"+monthly_balance+"' balance in this leave type. Either you cancel other leaves or try with another leave type !!!");
                      //                        $('.strict-field').prop("disabled", true);
                      //                    }
                      if (yearly_balance == 0) {
                          $('.strict-field').attr('disabled', false).val('');
                          //alert msg added when No leave balance --- Added by Nimisha 20-03-2019ss
                          alert("You Have No Leave Balance!!!");
                      } 
                      else {
    // Enable only leave-type-related fields, NOT date/session yet
    $('#salary_head_item_fkey, #FROMDATE, #FROMHALF').attr('disabled', false);

    // Keep TO fields disabled until FROMDATE + FROMHALF are chosen
    if ($("#FROMDATE").val() && $("#FROMHALF").val()) {
        $("#TODATE, #TOHALF").prop("disabled", false);
    } else {
        $("#TODATE, #TOHALF").prop("disabled", true);
    }
}
                      if (leaverule != 'Success') {
                          alert(leaverule);
                          $('.strict-field').prop("disabled", true);
                          $('#TODATE').val('');
                          $('#FROMDATE').val('');
                      }
                      // var availablebalance = monthly_balance - appliedleaves; 
                      //                    if(monthly_balance < leavedays && appliedleaves !=0.0){
                      //                        alert("You have already applied "+appliedleaves+" leaves from your current available balance. You can either cancel previously applied leaves or try with another leave type !!!");
                      //                        $('.strict-field').prop("disabled", true);
                      //                    }


                      // compoff available dates by arul on 27-01-23
                    //   if (compoff_available_dates != "") {
                    //       $("#compoff_available_dates").show();
                    //       $("#compoff_dates").html(extractCompoffDates(compoff_available_dates, $.parseJSON(resp).leavedays));
                    //   } else {
                    //       $("#compoff_available_dates").hide();
                    //       $("#compoff_dates").html("");
                    //   }
                      ///
                  }
              });
          }
          $('#loader').fadeOut(1000);
      }


      $('#APPROVED').on('click keydown', function() {
          if ($('#Authorized').val() === '' || $('#Authorized').val() === 'NULL') {
              alert('Please select Authorize By Person');
              $('#Authorized').focus();
              return false;
          }
      });

    //   function extractCompoffDates(dates, leavedays) {
    //       if (dates) {
    //           let select = $("<select class='form-control' id='compoff_id' name='compoff_id' onchange='changeLeaveBalance(this)'></select>");
    //           $.each(dates, function(index, value) {
    //               let compoff_date = value[0];
    //               let first_half = value[1];
    //               let break_off_id = value[2];
    //               select.append("<option value='" + break_off_id + "' data-leave='" + first_half + "'>" + compoff_date + " (" + first_half + " leave)</option>");
    //               if (index == 0) {
    //                   $('#hid_monthly_balance').val(first_half);
    //               }
    //           });

    //           // The below code is to check whether leavedays exeeds compoff leave balance.
    //           if (leavedays && leavedays > $('#hid_monthly_balance').val()) {
    //               alert('Cannot apply more than ' + $('#hid_monthly_balance').val() + ' days');
    //               // $('.strict-field').prop("disabled", true);
    //               $('#TODATE').val('');
    //               $('#FROMDATE').val('');
    //           }
    //           ////

    //           return select;
    //       } else {
    //           return '';
    //       }
    //   }

    function extractCompoffDates(dates, leavedays) {
    if (dates && dates.length > 0) {
        let select = $("<select class='form-control' readonly id='compoff_id' name='compoff_id' onchange='changeLeaveBalance(this)'></select>");
        
        $.each(dates, function(index, value) {
            // value here is an ARRAY like: [ { att_date: "2025-09-21", credit: "1.0" } ]
            let obj = value[0];  

            let compoff_date   = obj.att_date;      // ✅ date
            let first_half     = obj.credit;        // ✅ 1.0 or 0.5
            let break_off_id   = index;             // ❓ backend didn’t send ID, so use index (or add break_off_id in PHP)

            select.append(
                "<option value='" + break_off_id + "' data-leave='" + first_half + "'>" +
                    compoff_date + " (" + first_half + " leave)" +
                "</option>"
            );

            if (index === 0) {
                $('#hid_yearly_balance').val(first_half);
            }
        });

        let availableBalance = parseFloat($('#hid_yearly_balance').val());
        if (leavedays && parseFloat(leavedays) > availableBalance) {
            alert('Cannot apply more than ' + availableBalance + ' days');
            $('#TODATE').val('');
            $('#FROMDATE').val('');
        }

        return select;
    } else {
        return '';
    }
}

    

      function changeLeaveBalance(e) {
          let selected_leave_balance = $("#compoff_id").find(':selected').data('leave');
          $('#hid_yearly_balance').val(selected_leave_balance);
      }

      function empshow() {
          return true; // this function is already calling from leave type change. but never declared the function.
      }
  </script>