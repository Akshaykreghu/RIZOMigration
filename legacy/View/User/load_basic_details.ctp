<form class="form-horizontal" id="basicdetails">

    <div class="col-md-12" style="margin-bottom: 10px; ">
        <button type="button" class="btn btn-primary pull-right" onclick="loadsettings();">Edit</button>    
    </div>
    <!--<legend class="pull-right" style="text-align: center ; ">Basic Details</legend>-->
    <div class="form-group">
        <div class="col-md-6">
            <label style="text-align:left; font-weight: 500; " class="col-md-4 control-label " for="first_name">Employee Type </label>
            <div class="col-md-8">
                <input readonly="true" id="type" name="first_name" value="<?php echo $arr_data['0']['EmployeeProfessionalDetails']['emp_type']; ?>" type="text" placeholder="Employee Type" class="form-control input-md" required="" data-parsley-id="2411"><ul class="parsley-errors-list" id="parsley-id-2411"></ul>
            </div>
        </div>
        <div class="col-md-6">
            <label style="text-align:left; font-weight: 500; " class="col-md-4 control-label" for="middile_name">Notice Period</label>
            <div class="col-md-8">
                <input readonly="true" id="notice" name="middile_name" value="<?php echo $arr_data['0']['EmployeeProfessionalDetails']['notice_days']; ?>" type="text" placeholder="Notice Period" class="form-control input-md" data-parsley-id="1513"><ul class="parsley-errors-list" id="parsley-id-1513"></ul>
            </div>
        </div>
    </div>
    <div class="form-group">
        <div class="col-md-6">
            <label style="text-align:left; font-weight: 500; " class="col-md-4 control-label" for="first_name">Address </label>
            <div class="col-md-8">
                <input readonly="true" id="address" name="first_name" value="<?php echo $arr_data['0']['EmployeeDetails']['address']; ?>" type="text" placeholder="Address" class="form-control input-md" required="" data-parsley-id="2411"><ul class="parsley-errors-list" id="parsley-id-2411"></ul>
            </div>
        </div>
        <div class="col-md-6">
            <label style="text-align:left; font-weight: 500; " class="col-md-4 control-label" for="middile_name">City </label>
            <div class="col-md-8">
                <input readonly="true" id="city" name="middile_name" value="<?php echo $arr_data['0']['EmployeeDetails']['city']; ?>" type="text" placeholder="City" class="form-control input-md" data-parsley-id="1513"><ul class="parsley-errors-list" id="parsley-id-1513"></ul>
            </div>
        </div>
    </div>
    <div class="form-group">
        <div class="col-md-6">
            <label style="text-align:left; font-weight: 500; " class="col-md-4 control-label" for="first_name">State </label>
            <div class="col-md-8">
                <input readonly="true" id="state" name="first_name" value="<?php echo $arr_data['0']['EmployeeDetails']['state']; ?>" type="text" placeholder="State" class="form-control input-md" required="" data-parsley-id="2411"><ul class="parsley-errors-list" id="parsley-id-2411"></ul>
            </div>
        </div>
        <div class="col-md-6">
            <label style="text-align:left; font-weight: 500; " class="col-md-4 control-label" for="middile_name">ZIP </label>
            <div class="col-md-8">
                <input readonly="true" id="zip" name="middile_name" value="<?php echo $arr_data['0']['EmployeeDetails']['pincode']; ?>" type="text" placeholder="ZIP" class="form-control input-md" data-parsley-id="1513"><ul class="parsley-errors-list" id="parsley-id-1513"></ul>
            </div>
        </div>
    </div>
    <div class="form-group">
        <div class="col-md-6">
            <label style="text-align:left; font-weight: 500; " class="col-md-4 control-label" for="first_name">Mobile Number </label>
            <div class="col-md-8">
                <input readonly="true" id="mobile" name="first_name" value="<?php echo $arr_data['0']['EmployeeDetails']['mobile_no']; ?>" type="text" placeholder="Mobile Number" class="form-control input-md" required="" data-parsley-id="2411"><ul class="parsley-errors-list" id="parsley-id-2411"></ul>
            </div>
        </div>
        <div class="col-md-6">
            <label style="text-align:left; font-weight: 500; " class="col-md-4 control-label" for="middile_name">Email </label>
            <div class="col-md-8">
                <input readonly="true" id="email" name="middile_name" value="<?php echo $arr_data['0']['EmployeeDetails']['email']; ?>" type="text" placeholder="Email" class="form-control input-md" data-parsley-id="1513"><ul class="parsley-errors-list" id="parsley-id-1513"></ul>
            </div>
        </div>
    </div>
    <div class="form-group">
        <div class="col-md-6">
            <label style="text-align:left; font-weight: 500; " class="col-md-4 control-label" for="first_name">Marital Status </label>
            <div class="col-md-8">
                <input readonly="true" id="first_name" name="first_name" value="<?php echo $arr_data['0']['EmployeeDetails']['maritual_status']; ?>" type="text" placeholder="Martial Status" class="form-control input-md" required="" data-parsley-id="2411"><ul class="parsley-errors-list" id="parsley-id-2411"></ul>
            </div>
        </div>
        <div class="col-md-6">
            <label style="text-align:left; font-weight: 500; " class="col-md-4 control-label" for="middile_name">Education</label>
            <div class="col-md-8">
                <input readonly="true" id="education" name="middile_name" value="<?php echo $arr_data['0']['EmployeeDetails']['education']; ?>" type="text" placeholder="Education" class="form-control input-md" data-parsley-id="1513"><ul class="parsley-errors-list" id="parsley-id-1513"></ul>
            </div>
        </div>
    </div>
    <div class="form-group">
        <div class="col-md-6">
            <label style="text-align:left; font-weight: 500; " class="col-md-4 control-label" for="first_name"><?php
                if ($arr_data['0']['EmployeeDetails']['relation_guardian'] == 'Father') {
                    echo "Father's Name";
                } else {
                    echo "Husband's Name";
                }
                ?> </label>
            <div class="col-md-8">
                <input readonly="true" id="guardian" name="first_name" value="<?php echo $arr_data['0']['EmployeeDetails']['guradian']; ?>" type="text" placeholder="Guardian" class="form-control input-md" required="" data-parsley-id="2411"><ul class="parsley-errors-list" id="parsley-id-2411"></ul>
            </div>
        </div>
        <div class="col-md-6">
            <label style="text-align:left; font-weight: 500; " class="col-md-4 control-label" for="middile_name">Date Of Birth </label>
            <div class="col-md-8">
                <input readonly="true" id="date" name="middile_name" value="<?php echo $arr_data['0']['EmployeeDetails']['date_of_birth']; ?>" type="text" placeholder="Date Of Birth" class="form-control input-md" data-parsley-id="1513"><ul class="parsley-errors-list" id="parsley-id-1513"></ul>
            </div>
        </div>
    </div>
    <div class="form-group">
        <div class="col-md-6">
            <label style="text-align:left; font-weight: 500; " class="col-md-4 control-label" for="first_name">Blood Group </label>
            <div class="col-md-8">
                <input readonly="true" id="blood" name="first_name" value="<?php echo isset($arr_data['0']['EmployeeDetails']['blood']) ? $arr_data['0']['EmployeeDetails']['blood'] : ''; ?>" type="text" placeholder="Blood group" class="form-control input-md" required="" data-parsley-id="2411"><ul class="parsley-errors-list" id="parsley-id-2411"></ul>
            </div>
        </div>
    </div>
    <div class="form-group">
        <div class="col-md-6">
            <label style="text-align:left; font-weight: 500; " class="col-md-4 control-label" for="first_name">Shift Policy </label>
            <div class="col-md-8">
                <input readonly="true" id="shift" name="first_name" value="" type="text" placeholder="Shift Policy" class="form-control input-md" required="" data-parsley-id="2411"><ul class="parsley-errors-list" id="parsley-id-2411"></ul>
            </div>
        </div>
        <div class="col-md-6">
            <label style="text-align:left; font-weight: 500; " class="col-md-4 control-label" for="middile_name">Leave Policy </label>
            <div class="col-md-8">
                <input readonly="true" id="leave" name="middile_name" value="" type="text" placeholder="Leave Policy" class="form-control input-md" data-parsley-id="1513"><ul class="parsley-errors-list" id="parsley-id-1513"></ul>
            </div>
        </div>
    </div>
    <div class="form-group">
        <div class="col-md-6">
            <label style="text-align:left; font-weight: 500; " class="col-md-4 control-label" for="first_name">Superior </label>
            <div class="col-md-8">
                <input readonly="true" id="superior" name="first_name" value="" type="text" placeholder="Superior" class="form-control input-md" required="" data-parsley-id="2411"><ul class="parsley-errors-list" id="parsley-id-2411"></ul>
            </div>
        </div>
        <div class="col-md-6">
            <label style="text-align:left; font-weight: 500; " class="col-md-4 control-label" for="middile_name">Holiday Policy </label>
            <div class="col-md-8">
                <input readonly="true" id="holiday" name="middile_name" value="" type="text" placeholder="Holiday Policy" class="form-control input-md" data-parsley-id="1513"><ul class="parsley-errors-list" id="parsley-id-1513"></ul>
            </div>
        </div>
    </div>
    <div class="form-group">
        <div class="col-md-6">
            <label style="text-align:left; font-weight: 500; " class="col-md-4 control-label" for="first_name">Salary Policy </label>
            <div class="col-md-8">
                <input readonly="true" id="salarys" name="first_name" value="" type="text" placeholder="Salary Policy" class="form-control input-md" required="" data-parsley-id="2411"><ul class="parsley-errors-list" id="parsley-id-2411"></ul>
            </div>
        </div>
        <div class="col-md-6">
            <label style="text-align:left; font-weight: 500; " class="col-md-4 control-label" for="middile_name">Annual Salary </label>
            <div class="col-md-8">
                <input readonly="true" id="middile_name" name="middile_name" value="" type="text" placeholder="Annual Salary" class="form-control input-md" data-parsley-id="1513"><ul class="parsley-errors-list" id="parsley-id-1513"></ul>
            </div>
        </div>
    </div>


</form>