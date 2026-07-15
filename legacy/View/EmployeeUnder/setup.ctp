<script type="text/javascript">
    $(document).ready(function() {


        $('#empsetuppersonal').parsley();
        var empsetuppersonaloptions = {
            success : function(responseText, statusText, xhr, $form) {
                var response = JSON.parse(responseText);
                if (response.success) {
$('#proffessional').addClass('active');
$('#div-empsetupprofessional').addClass("active");
$('#div-empsetupprofessional').addClass("in");
$('#proffessional').addClass("active");
$('#div-empsetuppersonal').removeClass("active");
$('#div-empsetuppersonal').removeClass("in");
$('#personal').removeClass("active");


//$('#div-empsetupprofessional').show();
                    //alert(response.message);
                    $('#empsetup-save-response').removeClass('alert-danger').addClass('alert-success').html('<strong>Success!</strong> '+response.message).fadeIn().fadeOut( 3000 );
                    $('#empsetuppersonal #emp_pkey').val(response.pkey);
                    $('#empsetupprofessional #emp_fkey').val(response.pkey);
                    $('#empsetuptaxation #emp_pkey').val(response.pkey);
                    reloadTable('emptable');
                } else {
                    //alert('Something wrong happened!');
                    $('#empsetup-save-response').removeClass('alert-success').addClass('alert-danger').html('<strong>Failed!</strong> Something wrong happened.').fadeIn().fadeOut( 3000 );
                }
            }
        };

        // bind to the form's submit event
        $('#empsetuppersonal').submit(function() {
            $(this).ajaxSubmit(empsetuppersonaloptions);

            return false;
        });
        
        $('#empsetupprofessional').parsley();
        var empsetupprofessionaloptions = {
            success : function(responseText, statusText, xhr, $form) {
                var response = JSON.parse(responseText);
                if (response.success) {
                    //alert(response.message);
                    $('#empsetup-save-response').removeClass('alert-danger').addClass('alert-success').html('<strong>Success!</strong> '+response.message).fadeIn().fadeOut( 3000 );
                    $('#empsetuppersonal #emp_pkey').val(response.pkey);
                    $('#empsetupprofessional #emp_fkey').val(response.pkey);
                    $('#empsetuptaxation #emp_pkey').val(response.pkey);
                    reloadTable('emptable');
                } else {
                    //alert('Something wrong happened!');
                    $('#empsetup-save-response').removeClass('alert-success').addClass('alert-danger').html('<strong>Failed!</strong> Something wrong happened.').fadeIn().fadeOut( 3000 );
                }
            }
        };

        // bind to the form's submit event
        $('#empsetupprofessional').submit(function() {
            if($('#empsetupprofessional #emp_pkey').val() == 0){
                alert('Please fill personal informations first!');
            }else{
                $(this).ajaxSubmit(empsetupprofessionaloptions);
            }
            return false;
        });
        
        /*
         * Tax Head save
         */
        $('#empsetuptaxation').parsley();
        var empsetuptaxationoptions = {
            success : function(responseText, statusText, xhr, $form) {
                var response = JSON.parse(responseText);
                if (response.success) {
                    //alert(response.message);
                    $('#empsetup-save-response').removeClass('alert-danger').addClass('alert-success').html('<strong>Success!</strong> '+response.message).fadeIn().fadeOut( 3000 );
                } else {
                    //alert('Something wrong happened!');
                    $('#empsetup-save-response').removeClass('alert-success').addClass('alert-danger').html('<strong>Failed!</strong> Something wrong happened.').fadeIn().fadeOut( 3000 );
                }
            }
        };

        // bind to the form's submit event
        $('#empsetuptaxation').submit(function() {
            if($('#empsetuptaxation #emp_pkey').val() == 0){
                alert('Please fill personal informations first!');
            }else{
                $(this).ajaxSubmit(empsetuptaxationoptions);
            }
            return false;
        });
               
    $('#date_of_birth').datepicker({
     format: 'yyyy-mm-dd',
    
    })
    $('#joining_date').datepicker(
            {
                format:'yyyy-mm-dd',
            })
        //Ends
    });
        
    function showTaxHeadDetails(obj){
        var taxHeadPkey = $(obj).data('tax_heads_pkey');
        var empPkey = $('#empsetuptaxation #emp_pkey').val();
        var url = livesite+'Employee/showtaxheaddetail/'+empPkey+'/'+taxHeadPkey;
        
        var container = $("#modalShowTaxHeadDetailForm #modalForm-content")
        container.load(url, function() {
            $("#modalShowTaxHeadDetailForm").modal('show');
        });
    }
</script>
<div class="modal-body">
    <!-- Form Name -->
    <legend><?php echo $head; ?></legend>

    <div id="empsetup-save-response" class="alert alert-success" style="display: none;">
        
    </div>
    <!-- Nav tabs -->
    <ul class="nav nav-tabs" role="tablist">
        <li role="presentation" id="personal" class="active">
            <a href="#div-empsetuppersonal" aria-controls="div-empsetuppersonal" role="tab" data-toggle="tab">Personal Details</a>
        </li>
        <li id="proffessional" role="presentation">
            <a href="#div-empsetupprofessional" id="atr" aria-controls="div-empsetupprofessional" role="tab" data-toggle="tab">Professional Details</a>
        </li>
        <li role="presentation">
            <a href="#div-empsetuptaxation" aria-controls="div-empsetuptaxation" role="tab" data-toggle="tab">Taxation Details</a>
        </li>
    </ul>
<input type="hidden" id="head" name="head" value="<?php echo $user_group ; ?>">
    <!-- Tab panes -->
    <div class="tab-content">
        <div role="tabpanel" class="tab-pane active" id="div-empsetuppersonal">
                <form class="form-horizontal" method="post" action="<?php echo $this->webroot; ?>EmployeeUnder/saveemployeesetup" id="empsetuppersonal">
                    <div class="modal-body">
                        <input id="model" name="model" type="hidden"  value="EmployeeDetails" >
                        <input id="emp_pkey" name="emp_pkey" type="hidden"  value="<?php echo $emp_pkey; ?>" >
                        
                        <div class="form-group">
                            <div class="col-md-6">
                            <label style="text-align:left;" class="col-md-4 control-label" for="first_name">First Name</label>
                                <div class="col-md-8">
                                    <input id="first_name" name="first_name" value="<?php echo isset($arr_personalinfo["first_name"]) ? $arr_personalinfo["first_name"] : ''; ?>" type="text" placeholder="First Name" class="form-control input-md" required="">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label style="text-align:left;" class="col-md-4 control-label" for="middile_name">Middle Name</label>
                                <div class="col-md-8">
                                    <input id="middile_name" name="middile_name" value="<?php echo isset($arr_personalinfo["middile_name"]) ? $arr_personalinfo["middile_name"] : ''; ?>" type="text" placeholder="Middle Name" class="form-control input-md" >
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <div class="col-md-6">
                                <label style="text-align:left;" class="col-md-4 control-label" for="last_name">Last Name</label>
                                <div class="col-md-8">
                                    <input id="last_name" name="last_name" value="<?php echo isset($arr_personalinfo["last_name"]) ? $arr_personalinfo["last_name"] : ''; ?>" type="text" placeholder="Last Name" class="form-control input-md" required="">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label style="text-align:left;" class="col-md-4 control-label" for="classification">Gender</label>
                                <div class="col-md-8">
                                    <select id="classification" name="classification" class="form-control" required="">
                                        <option value="">--Select--</option>
                                        <option value="male" <?php echo($arr_personalinfo["classification"] == 'male') ? 'selected="selected"' : ''; ?>>Male</option>
                                        <option value="female" <?php echo($arr_personalinfo["classification"] == 'female') ? 'selected="selected"' : ''; ?>>Female</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <div class="col-md-6">
                                <label style="text-align:left;" class="col-md-4 control-label" for="address">Address</label>
                                <div class="col-md-8">
                                    <input id="address" name="address" value="<?php echo isset($arr_personalinfo["address"]) ? $arr_personalinfo["address"] :''; ?>" type="text" placeholder="address" class="form-control input-md" >
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label style="text-align:left;" class="col-md-4 control-label" for="city">City</label>
                                <div class="col-md-8">
                                    <input id="city" name="city" value="<?php echo isset($arr_personalinfo["city"]) ? $arr_personalinfo["city"] :''; ?>" type="text" placeholder="City" class="form-control input-md" >
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <div class="col-md-6">
                                <label style="text-align:left;" class="col-md-4 control-label" for="pincode">Zip</label>
                                <div class="col-md-8">
                                    <input id="pincode" name="pincode" value="<?php echo isset($arr_personalinfo["pincode"]) ? $arr_personalinfo["pincode"] :''; ?>" type="text" placeholder="Zip" class="form-control input-md" >
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label style="text-align:left;" class="col-md-4 control-label" for="state">State</label>
                                <div class="col-md-8">
                                    <input id="state" name="state" value="<?php echo isset($arr_personalinfo['state']) ? $arr_personalinfo['state'] :''; ?>" type="text" placeholder="State" class="form-control input-md" >
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <div class="col-md-6">
                                <label style="text-align:left;" class="col-md-4 control-label" for="mobile_no">Mobile No</label>
                                <div class="col-md-8">
                                    <input id="mobile_no" name="mobile_no" value="<?php echo isset($arr_personalinfo["mobile_no"]) ? $arr_personalinfo["mobile_no"] :''; ?>" type="text" placeholder="Mobile No" class="form-control input-md" >
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label style="text-align:left;" class="col-md-4 control-label" for="email">Email</label>
                                <div class="col-md-8">
                                    <input id="email" name="email" value="<?php echo isset($arr_personalinfo["email"]) ? $arr_personalinfo["email"] :''; ?>" type="text" placeholder="Email" class="form-control input-md" >
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <div class="col-md-6">
                                <label style="text-align:left;" class="col-md-4 control-label" for="maritual_status">Martial Status</label>
                                <div class="col-md-8">
                                    <select id="maritual_status" name="maritual_status" class="form-control" >
                                        <option value="">--Select--</option>
                                        <option value="single" <?php echo($arr_personalinfo["maritual_status"] == 'single') ? 'selected="selected"' : ''; ?>>Single</option>
                                        <option value="married" <?php echo($arr_personalinfo["maritual_status"] == 'married') ? 'selected="selected"' : ''; ?>>Married</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label style="text-align:left;" class="col-md-4 control-label" for="education">Education</label>
                                <div class="col-md-8">
                                    <select id="education" name="education" class="form-control" >
                                        <option value="">--Select--</option>
                                        <option value="Graduate" <?php echo($arr_personalinfo["education"] == 'Graduate') ? 'selected="selected"' : ''; ?>>Graduate</option>
                                        <option value="Post-Graduate" <?php echo($arr_personalinfo["education"] == 'Post-Graduate') ? 'selected="selected"' : ''; ?>>Post-Graduate</option>
                                        <option value="Under-Graduate" <?php echo($arr_personalinfo["education"] == 'Under-Graduate') ? 'selected="selected"' : ''; ?>>Under-Graduate</option>
                                        <option value="No Education" <?php echo($arr_personalinfo["education"] == 'No Education') ? 'selected="selected"' : ''; ?>>No Education</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <div class="col-md-6">
                                <label style="text-align:left;" class="col-md-4 control-label" for="date_of_birth">Birth Date</label>
                                <div class="col-md-8">
                                    <input id="date_of_birth" name="date_of_birth" value="<?php echo isset($arr_personalinfo["date_of_birth"]) ? $arr_personalinfo["date_of_birth"] :''; ?>" type="text" placeholder="(YYYY-MM-DD)" class="form-control input-md" >
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label style="text-align:left;" class="col-md-4 control-label" for="bank_name">Bank Name</label>
                                <div class="col-md-8">
                                    <input id="bank_name" name="bank_name" value="<?php echo isset($arr_personalinfo["bank_name"]) ? $arr_personalinfo["bank_name"] :''; ?>" type="text" placeholder="Bank Name" class="form-control input-md" >
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                           <!-- <div class="col-md-6">
                                <label style="text-align:left;" class="col-md-4 control-label" for="branch_name">Branch Name</label>
                                <div class="col-md-8">
                                    <input id="branch_name" name="branch_name" value="<?php echo isset($arr_personalinfo["branch_name"]) ? $arr_personalinfo["branch_name"] :''; ?>" type="text" placeholder="Branch Name" class="form-control input-md" >
                                </div>
                            </div>-->
                            <div class="col-md-6">
                                <label style="text-align:left;" class="col-md-4 control-label" for="PAN No">PAN No</label>
                                <div class="col-md-8">
                                    <input id="pan_no" name="pan_no" value="<?php echo isset($arr_personalinfo["pan_no"]) ? $arr_personalinfo["pan_no"] :''; ?>" type="text" placeholder="PAN No" class="form-control input-md" >
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label style="text-align:left;" class="col-md-4 control-label" for="ifsc_code">Bank IFSC Code</label>
                                <div class="col-md-8">
                                    <input id="ifsc_code" name="ifsc_code" value="<?php echo isset($arr_personalinfo["ifsc_code"]) ? $arr_personalinfo["ifsc_code"] :''; ?>" type="text" placeholder="Bank IFSC Code" class="form-control input-md" >
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <div class="col-md-6">
                                <label style="text-align:left;" class="col-md-4 control-label" for="account_no">Account No</label>
                                <div class="col-md-8">
                                    <input id="account_no" name="account_no" value="<?php echo isset($arr_personalinfo["account_no"]) ? $arr_personalinfo["account_no"] :''; ?>" type="text" placeholder="Account No" class="form-control input-md" >
                                </div>
                            </div>
                           
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" onclick="$('#largeModalForm').modal('hide');">Close</button>
                        <button type="submit" id="btn-submit" class="btn btn-primary">Next</button>
                    </div>
                </form>
        </div>
	
        <div role="tabpanel" class="tab-pane" id="div-empsetupprofessional">
            <form class="form-horizontal" method="post" action="<?php echo $this->webroot; ?>Employee/saveemployeesetup" id="empsetupprofessional">
                    <div class="modal-body">
                        <input id="model" name="model" type="hidden"  value="EmployeeProfessionalDetails" >
                        <input id="emp_fkey" name="emp_fkey" type="hidden"  value="<?php echo $emp_pkey; ?>" >
                        <input id="emp_proff_pkey" name="emp_proff_pkey" type="hidden"  value="<?php echo $arr_professionalinfo['emp_proff_pkey']; ?>" >
                        
                        <div class="form-group">
                            <div class="col-md-6">
                            <label style="text-align:left;" class="col-md-4 control-label" for="joining_date">Joining Date</label>
                                <div class="col-md-8">
                                    <input id="joining_date" name="joining_date" value="<?php echo isset($arr_professionalinfo["joining_date"]) ? $arr_professionalinfo["joining_date"] :''; ?>" type="text" placeholder="Joining Date" class="form-control input-md" required="">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label style="text-align:left;" class="col-md-4 control-label" for="emp_company_id">Company Employee ID</label>
                                <div class="col-md-8">
                                    <input id="emp_company_id" name="emp_company_id" value="<?php echo isset($arr_professionalinfo["emp_company_id"]) ? $arr_professionalinfo["emp_company_id"] :''; ?>" type="text" placeholder="Company Employee ID" class="form-control input-md" >
                                </div>
                            </div>
                        </div>
                       
                        <div class="form-group">
                            <div class="col-md-6">
                            <label style="text-align:left;" class="col-md-4 control-label" for="emp_type">Employee Type</label>
                                <div class="col-md-8">
                                    <select id="emp_type" name="emp_type" class="form-control" >
                                        <option value="">--Select--</option>
                                        <option value="Permanent" <?php echo($arr_professionalinfo["emp_type"] == 'Permanent') ? 'selected="selected"' : ''; ?>>Permanent</option>
                                        <option value="Contract" <?php echo($arr_professionalinfo["emp_type"] == 'Contract') ? 'selected="selected"' : ''; ?>>Contract</option>
                                        <option value="Probation" <?php echo($arr_professionalinfo["emp_type"] == 'Probation') ? 'selected="selected"' : ''; ?>>Probation</option>
                                        <option value="Part-Time" <?php echo($arr_professionalinfo["emp_type"] == 'Part-Time') ? 'selected="selected"' : ''; ?>>Part-Time</option>
                                        <option value="Temporary" <?php echo($arr_professionalinfo["emp_type"] == 'Temporary') ? 'selected="selected"' : ''; ?>>Temporary</option>
                                        <option value="Consultant" <?php echo($arr_professionalinfo["emp_type"] == 'Consultant') ? 'selected="selected"' : ''; ?>>Consultant</option>
                                        <option value="other" <?php echo($arr_professionalinfo["emp_type"] == 'other') ? 'selected="selected"' : ''; ?>>Other Type</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label style="text-align:left;" class="col-md-4 control-label" for="designation">Designation</label>
                                <div class="col-md-8">
                                 <select id="designation" name="designation" class="form-control" >
                                        <option value="">--Select--</option>
                                        <?php
                                        foreach ($arr_designations as $key => $value) {
                                            $selected = ($arr_professionalinfo['designation'] == $value['desig_code']) ? 'selected="selected"' : '';
                                            echo '<option value="' . $value['desig_code'] . '" ' . $selected . '>' . $value['desig_name'] . '</option>';
                                        }
                                        ?>
                                    </select> </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <div class="col-md-6">
                            <label style="text-align:left;" class="col-md-4 control-label" for="emp_dept">Department</label>
                                <div class="col-md-8">
                                    <select id="emp_dept" name="emp_dept" class="form-control" >
                                        <option value="">--Select--</option>
                                        <?php
                                        foreach ($arr_departments as $key => $value) {
                                            $selected = ($arr_professionalinfo['emp_dept'] == $value['dept_code']) ? 'selected="selected"' : '';
                                            echo '<option value="' . $value['dept_code'] . '" ' . $selected . '>' . $value['dept_name'] . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                           <!-- <div class="col-md-6">
                                <label style="text-align:left;" class="col-md-4 control-label" for="emp_grade">Grade</label>
                                <div class="col-md-8">
                                    <select id="emp_grade" name="emp_grade" class="form-control" >
                                        <option value="">--Select--</option>
                                        <?php
                                        foreach ($arr_grades as $key => $value) {
                                            $selected = ($arr_professionalinfo['emp_grade'] == $value['grade_code']) ? 'selected="selected"' : '';
                                            echo '<option value="' . $value['grade_code'] . '" ' . $selected . '>' . $value['grade_name'] . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>-->
                        
                        <div class="form-group">
                           <!-- <div class="col-md-6">
                            <label style="text-align:left;" class="col-md-4 control-label" for="emp_vertical">Vertical</label>
                                <div class="col-md-8">
                                    <select id="emp_vertical" name="emp_vertical" class="form-control" >
                                        <option value="">--Select--</option>
                                        <?php
                                        foreach ($arr_verticals as $key => $value) {
                                            $selected = ($arr_professionalinfo['emp_vertical'] == $value['vert_code']) ? 'selected="selected"' : '';
                                            echo '<option value="' . $value['vert_code'] . '" ' . $selected . '>' . $value['vertical_name'] . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>-->
                            <div class="col-md-6">
                                <label style="text-align:left;" class="col-md-4 control-label" for="emp_branch">Branch</label>
                                <div class="col-md-8">
                                    <select id="emp_branch" name="emp_branch" class="form-control" >
                                        <option value="">--Select--</option>
                                        <?php
                                        foreach ($arr_branches as $key => $value) {
                                            $selected = ($arr_professionalinfo['emp_branch'] == $value['branch_code']) ? 'selected="selected"' : '';
                                            echo '<option value="' . $value['branch_code'] . '" ' . $selected . '>' . $value['branch_name'] . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" onclick="$('#largeModalForm').modal('hide');">Close</button>
                        <button type="submit" id="btn-submit" class="btn btn-primary">Save</button>
                    </div>
            </form>
        </div>
        </div>
        <div role="tabpanel" class="tab-pane" id="div-empsetuptaxation">
            <form class="form-horizontal" method="post" action="<?php echo $this->webroot; ?>Taxation/saveemployeetaxheads" id="empsetuptaxation">
                    <div class="modal-body">
                        <input id="emp_pkey" name="emp_pkey" type="hidden"  value="<?php echo $emp_pkey; ?>" >
                        <?php 
                            $tax_types = array_keys($arr_taxheadfields);
                            if(!empty($tax_types)){
                        ?>
                            <!-- Nav tabs -->
                            <ul class="nav nav-tabs" role="tablist">
                                <?php $i = 0; ?>
                                <?php foreach($tax_types as $key => $value){ ?>
                                    <li role="presentation" <?php echo($i == 0) ? 'class="active"' : ''; ?>>
                                        <a href="#div-<?php echo $value; ?>" aria-controls="div-<?php echo $value; ?>" role="tab" data-toggle="tab"><?php echo $value; ?></a>
                                    </li>
                                <?php 
                                    $i++;
                                }
                                ?>
                            </ul>
                        <?php } ?>
                        
                        <?php 
                            if(!empty($arr_taxheadfields)){
                        ?>
                            <!-- Tab panes -->
                            <div class="tab-content">
                                <?php $j = 0; ?>
                                <?php foreach($arr_taxheadfields as $key => $value){ ?>
                                    <div role="tabpanel" <?php echo($j == 0) ? 'class="tab-pane active"' : 'class="tab-pane"'; ?> id="div-<?php echo $key; ?>">
                                        <div class="modal-body">
                                        <?php if(!empty($value['tax_heads'])){
                                            $arr_taxheads = $value['tax_heads'];
                                            $index = 0;
                                            foreach($arr_taxheads as $key => $value){
                                                $index++;
                                                
                                                $fieldid = 'tax_heads_'.$value['tax_heads_pkey'];
                                                $fieldname = $value['tax_name'];
                                                $fieldvalue = isset($arr_emptaxtransactions[$value['tax_heads_pkey']])?$arr_emptaxtransactions[$value['tax_heads_pkey']]:'';
                                                    
                                                if($index % 2 != 0){
                                                    //start new row
                                                    echo '<div class="form-group">
                                                            <div class="col-md-6">
                                                            <label style="text-align:left;" class="col-md-4 control-label" for="'.$fieldid.'">'.$fieldname.'</label>
                                                            <div class="col-md-6">
                                                                <input id="'.$fieldid.'" name="'.$fieldid.'" value="'.$fieldvalue.'" type="text" placeholder="'.$fieldname.'" class="form-control input-md">
                                                            </div>
                                                            <a class="col-md-2 btn-show-head-details" onclick="showTaxHeadDetails(this);" data-tax_heads_pkey="'.$value['tax_heads_pkey'].'"><i class="fa fa-info-circle"></i></a>
                                                            </div>';
                                                    if($index == count($arr_taxheads)){
                                                        //End last row
                                                        echo '</div>'; 
                                                    }
                                                }else{
                                                    //End current row
                                                    echo '<div class="col-md-6">
                                                            <label style="text-align:left;" class="col-md-4 control-label" for="'.$fieldid.'">'.$fieldname.'</label>
                                                            <div class="col-md-6">
                                                                <input id="'.$fieldid.'" name="'.$fieldid.'" value="'.$fieldvalue.'" type="text" placeholder="'.$fieldname.'" class="form-control input-md">
                                                            </div>
                                                            <a class="col-md-2 btn-show-head-details" onclick="showTaxHeadDetails(this);" data-tax_heads_pkey="'.$value['tax_heads_pkey'].'"><i class="fa fa-info-circle"></i></a>
                                                            </div>
                                                        </div>';
                                                }
                                            }
                                        }else{
                                            echo 'No heads found';
                                        } ?>
                                    </div>
                                    </div>
                                <?php 
                                    $j++;
                                }
                                ?>
                            </ul>
                        <?php } ?>
                        <!--div class="form-group">
                            <div class="col-md-6">
                            <label style="text-align:left;" class="col-md-4 control-label" for="joining_date">Joining Date</label>
                                <div class="col-md-8">
                                    <input id="joining_date" name="joining_date" value="<?php echo $arr_professionalinfo["joining_date"]; ?>" type="text" placeholder="Joining Date" class="form-control input-md" required="">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label style="text-align:left;" class="col-md-4 control-label" for="emp_company_id">Company Employee ID</label>
                                <div class="col-md-8">
                                    <input id="emp_company_id" name="emp_company_id" value="<?php echo $arr_professionalinfo["emp_company_id"]; ?>" type="text" placeholder="Leave as blank if no ID" class="form-control input-md" >
                                </div>
                            </div>
                        </div-->
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" onclick="$('#largeModalForm').modal('hide');">Close</button>
                        <button type="submit" id="btn-submit" class="btn btn-primary">Save</button>
                    </div>
            </form>
        </div>
    </div>
    
    <!-- Tax Head Detail Form -->
    <div id="modalShowTaxHeadDetailForm" class="modal fade">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" id="modalForm-content">
                <!-- Content will be loaded here from "remote.php" file -->
            </div>
        </div>
    </div>
</div>