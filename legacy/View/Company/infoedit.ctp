
<style>
    .box .box-primary {
        height: 763px !important;
    }
</style>
<form id="contactInfoForm" action="<?php echo $this->webroot; ?>Company/savecompanysetup" method="post">
    <div class="col-md-3">
        <br>
        <input type="file" class="form-control file" id="companylogofile" name="companylogofile">
        <br>
        <label><input type="radio" name="logosize" onclick="$('.file-preview-image').css({'height':'200px','width':'200px'})" value="1" <?php echo isset($contactinfo['logosize']) && $contactinfo['logosize'] == "1" ? 'checked="checked"' : '' ?>>Square</label>
        <label><input type="radio" name="logosize" value="0" onclick="$('.file-preview-image').css({'height':'100px','width':'200px'})" <?php echo isset($contactinfo['logosize']) && $contactinfo['logosize'] == "0" ? 'checked="checked"' : '' ?>>Rectangle</label>
    </div>
    
    <div class="col-md-9">
        <br>
        <!-- Company Name -->
        <div class="row">
            <div class="form-group">
                <label for="business_name" class="col-sm-2 control-label">Company Name<span class="star">*</span></label>
                <div class="col-sm-1">:</div>
                <div class="col-sm-9">
                    <input type="text" required class="form-control" data-validation-error-msg="Please enter Business Name" value="<?php echo isset($contactinfo['business_name']) ? $contactinfo['business_name'] : '' ?>" name="business_name" id="business_name" placeholder="Business Name">
                </div>
            </div>
        </div>
        <br>
        <!-- Business Type & Nature -->
        <div class="row">
            <div class="form-group">
                <label for="business_type" class="col-sm-2 control-label">Type Of Business<span class="star">*</span></label>
                <div class="col-sm-1">:</div>
                <div class="col-sm-3">
                    <input type="text" required class="form-control" data-validation-error-msg="Please enter Type Of Business" value="<?php echo isset($contactinfo['business_type']) ? $contactinfo['business_type'] : '' ?>" id="business_type" name="business_type" placeholder="Type Of Business">
                </div>
                <label for="business_nature" class="col-sm-2 control-label">Nature Of Business</label>
                <div class="col-sm-1">:</div>
                <div class="col-sm-3">
                    <input type="text" class="form-control" data-validation-error-msg="Please enter Nature Of Business" value="<?php echo isset($contactinfo['business_nature']) ? $contactinfo['business_nature'] : '' ?>" id="business_nature" name="business_nature" placeholder="Nature Of Business">
                </div>
            </div>
        </div>
        <br>
        <!-- Address -->
        <div class="row">
            <div class="form-group">
                <label for="address" class="col-sm-2 control-label">Address<span class="star">*</span></label>
                <div class="col-sm-1">:</div>
                <div class="col-sm-9">
                    <textarea class="form-control" style="height:30px;" id="address" required name="address" placeholder="Address" data-validation-error-msg="Please enter Address"><?php echo isset($contactinfo['address']) ? $contactinfo['address'] : '' ?></textarea> 
                </div>
            </div>  
        </div>
        <br>
        <!-- City & State -->
        <div class="row"> 
            <label for="city" class="col-sm-2 control-label">City<span class="star">*</span></label>
            <div class="col-sm-1">:</div>
            <div class="col-sm-3">
                <input type="text" class="form-control" required id="city" name="city" data-validation-error-msg="Please Enter City" value="<?php echo isset($contactinfo['city']) ? $contactinfo['city'] : '' ?>" placeholder="City">
            </div>
            <label for="state" class="col-sm-2 control-label">State<span class="star">*</span></label>
            <div class="col-sm-1">:</div>
            <div class="col-sm-3">
                <input type="text" class="form-control" required id="state" name="state" data-validation-error-msg="Please enter State" value="<?php echo isset($contactinfo['state']) ? $contactinfo['state'] : '' ?>" placeholder="State">
            </div>
        </div>
        <br>
        <!-- Pincode & Phone -->
        <div class="row">
            <div class="form-group">
                <label for="pincode" class="col-sm-2 control-label">Zip Code<span class="star">*</span></label>
                <div class="col-sm-1">:</div>
                <div class="col-sm-3">
                    <input type="number" class="form-control" required id="pincode" name="pincode" data-validation-error-msg="Please enter Zip Code" value="<?php echo isset($contactinfo['pincode']) ? $contactinfo['pincode'] : '' ?>" placeholder="Zip Code">
                </div>
                <label for="phone" class="col-sm-2 control-label">Phone<span class="star">*</span></label>
                <div class="col-sm-1">:</div>
                <div class="col-sm-3">
                    <input type="number" class="form-control" required id="phone" name="phone" data-validation-error-msg="Please enter Phone" value="<?php echo isset($contactinfo['phone']) ? $contactinfo['phone'] : '' ?>" placeholder="Phone">
                </div>
            </div>
        </div>
        <br>
        <!-- Email & Fax -->
        <div class="row">
            <div class="form-group">
                <label for="email" class="col-sm-2 control-label">Email<span class="star">*</span></label>
                <div class="col-sm-1">:</div>
                <div class="col-sm-3">
                    <input type="email" class="form-control" required id="email" name="email" data-validation-error-msg="Please enter Email" value="<?php echo isset($contactinfo['email']) ? $contactinfo['email'] : '' ?>" placeholder="Email">
                </div>
                <label for="fax" class="col-sm-2 control-label">Fax</label>
                <div class="col-sm-1">:</div>
                <div class="col-sm-3">
                    <input type="number" class="form-control" id="fax" name="fax" value="<?php echo isset($contactinfo['fax']) ? $contactinfo['fax'] : '' ?>" placeholder="Fax">
                </div>
            </div>
        </div>
        <br>
        <!-- CIN & PAN -->
        <div class="row">
            <div class="form-group">
                <label for="cinno" class="col-sm-2 control-label">CIN No.</label>
                <div class="col-sm-1">:</div>
                <div class="col-sm-3">
                    <input type="text" class="form-control" name="cinno" value="<?php echo isset($complianceInfo['cinno']) ? $complianceInfo['cinno'] : '' ?>" id="cinno" placeholder="CIN No.">
                </div>
                <label for="panno" class="col-sm-2 control-label">PAN No.</label>
                <div class="col-sm-1">:</div>
                <div class="col-sm-3">
                    <input type="text" class="form-control" id="panno" name="panno" value="<?php echo isset($complianceInfo['panno']) ? $complianceInfo['panno'] : '' ?>" placeholder="Pan No.">
                </div>
            </div>
        </div>
        <br>
        <!-- TAN & Service Tax -->
        <div class="row">
            <div class="form-group">
                <label for="tanno" class="col-sm-2 control-label">TAN No.</label>
                <div class="col-sm-1">:</div>
                <div class="col-sm-3">
                    <input type="text" class="form-control" id="tanno" name="tanno" value="<?php echo isset($complianceInfo['tanno']) ? $complianceInfo['tanno'] : '' ?>" placeholder="Tan No.">
                </div>
                <label for="servicetax" class="col-sm-2 control-label">Service Tax</label>
                <div class="col-sm-1">:</div>
                <div class="col-sm-3">
                    <input type="text" class="form-control" id="servicetax" name="servicetax" value="<?php echo isset($complianceInfo['servicetax']) ? $complianceInfo['servicetax'] : '' ?>" placeholder="Service Tax">
                </div>
            </div>
        </div>
        <br>
        <!-- PF & ESI -->
        <div class="row">
            <div class="form-group">
                <label for="pfno" class="col-sm-2 control-label">PF No</label>
                <div class="col-sm-1">:</div>
                <div class="col-sm-3">
                    <input type="text" class="form-control" id="pfno" name="pfno" value="<?php echo isset($complianceInfo['pfno']) ? $complianceInfo['pfno'] : '' ?>" placeholder="Provident Fund No">
                </div>
                <label for="empstateinsno" class="col-sm-2 control-label">ESI No</label>
                <div class="col-sm-1">:</div>
                <div class="col-sm-3">
                    <input type="text" class="form-control" id="empstateinsno" name="empstateinsno" value="<?php echo isset($complianceInfo['empstateinsno']) ? $complianceInfo['empstateinsno'] : '' ?>" placeholder="ESI No">
                </div>
            </div>
        </div>
        <br>
        <!-- Professional Tax No (Co. & Dir.) -->
        <div class="row">
            <div class="form-group">
                <label for="ptnoco" class="col-sm-2 control-label">Prof Tax No(Co.)</label>
                <div class="col-sm-1">:</div>
                <div class="col-sm-3">
                    <input type="text" class="form-control" id="ptnoco" name="ptnoco" value="<?php echo isset($complianceInfo['ptnoco']) ? $complianceInfo['ptnoco'] : '' ?>" placeholder="Prof Tax No(Co.)">
                </div>
                <label for="ptnodir" class="col-sm-2 control-label">Prof Tax No(Dir.)</label>
                <div class="col-sm-1">:</div>
                <div class="col-sm-3">
                    <input type="text" class="form-control" id="ptnodir" name="ptnodir" value="<?php echo isset($complianceInfo['ptnodir']) ? $complianceInfo['ptnodir'] : '' ?>" placeholder="Prof Tax No(Dir.)">
                </div>
            </div>
        </div>
        <br>
        <!-- Professional Tax (Emp.) -->
        <div class="row">
            <div class="form-group">
                <label for="ptnoemp" class="col-sm-2 control-label">Prof Tax No(Emp.)</label>
                <div class="col-sm-1">:</div>
                <div class="col-sm-3">
                    <input type="text" class="form-control" id="ptnoemp" name="ptnoemp" value="<?php echo isset($complianceInfo['ptnoemp']) ? $complianceInfo['ptnoemp'] : '' ?>" placeholder="Prof Tax No(Emp.)">
                </div>
            </div>
        </div>
        <br>

        <!-- NEW ADDITION: Attendance and Salary Cycle -->
        <div class="row">
            <div class="form-group">
                <label class="col-sm-2 control-label">Attendance Start Date</label>
                <div class="col-sm-1">:</div>
                <div class="col-sm-3">
                    <select id="attendance_cycle" name="attendance_cycle" class="form-control" onchange="updateEndDate()">
                        <?php 
                        $cur_att_date = isset($db_config['attendance_date']) ? $db_config['attendance_date'] : 1;
                        if (isset($db_config['attendance_format']) && $db_config['attendance_format'] == 'B' && $cur_att_date == 0) {
                            $cur_att_date = 1;
                        }
                        for ($i = 1; $i <= 28; $i++) {
                            $selected = ($cur_att_date == $i) ? 'selected' : '';
                            echo "<option value='$i' $selected>$i</option>";
                        }
                        ?>
                    </select>
                </div>
                <label class="col-sm-2 control-label">End Date</label>
                <div class="col-sm-1">:</div>
                <div class="col-sm-3">
                    <input type="text" id="attendance_end_date" class="form-control" readonly style="background-color: #eee;">
                </div>
            </div>
        </div>
        <br>
        <div class="row">
            <div class="form-group">
                <label class="col-sm-2 control-label">Payroll computation day</label>
                <div class="col-sm-1">:</div>
                <div class="col-sm-6">
                    <div style="display: flex; gap: 10px; padding-top: 7px;">
                        <?php 
                        $payroll_type = isset($db_config['payroll_type']) ? $db_config['payroll_type'] : 'T'; 
                        ?>
                        <label><input type="radio" name="salary_cycle" value="T" <?php echo ($payroll_type == 'T') ? 'checked' : ''; ?>> Attendance days</label>
                        <label><input type="radio" name="salary_cycle" value="M" <?php echo ($payroll_type == 'M') ? 'checked' : ''; ?>> Payroll days</label>
                    </div>
                </div>
            </div>
        </div>
        <br>
    </div>
    
    <button type="submit" id="cinfosave" class="btn btn-primary pull-right" style="margin-right:5px">Save</button>
    <button type="button" id="cinfocancel" class="btn btn-danger pull-right" onclick="canceledit();" style="margin-right:5px">Cancel</button>
</form>

<script type="text/javascript">
    function canceledit(){
        $("#infoid").load(livesite+"Company/viewinfo")
    }
    
    $(document).ready(function() { 
        $.validate({ modules : 'location, date, security, file'});
        
        var options = { 
            success: function(resp){ 
                $("#infoid").load(livesite+"Company/viewinfo");
                $.notify($.parseJSON(resp).msg, { type: 'success', allow_dismiss: false });
            }
        }; 
        
        // bind to the form's submit event 
        $('#contactInfoForm').submit(function() { 
            $(this).ajaxSubmit(options); 
            return false; 
        });
        
        // File input initialization
        $("#companylogofile").fileinput({
            <?php if(isset($contactinfo['logo']) && $contactinfo['logo'] != null){ ?>
                initialPreview: [
                    '<img src="<?php echo $this->webroot.$contactinfo['logo']; ?>" class="file-preview-image" alt="logo">',
                ],
            <?php } ?>
            showCaption: false,
            showUpload: false,
            showRemove: false
        });
        
            if(<?php echo isset($contactinfo['logosize']) ? $contactinfo['logosize'] : 0 ?> == "1")
                $('.file-preview-image').css({'height':'200px','width':'200px'});
            else
                $('.file-preview-image').css({'height':'100px','width':'200px'});
        });
        
        function updateEndDate() {
            var val = document.getElementById("attendance_cycle").value;
            var start = parseInt(val, 10);
            var endInput = document.getElementById("attendance_end_date");
            if (start === 1) {
                endInput.value = "28/29/30/31";
            } else {
                endInput.value = (start - 1).toString();
            }
        }
        
        // Initialize end date on page load
        updateEndDate();
    </script>
