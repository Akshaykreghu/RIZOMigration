<form class="form-horizontal" method="POST" id="changebasics" action="<?php echo $this->webroot ?>User/savebasics">
    <div class="modal-body">
        <input id="model" name="model" type="hidden" value="EmployeeDetails">
        <input id="emp_pkey" name="emp_pkey" type="hidden" value="<?php echo isset($arr_data['0']['EmployeeDetails']['emp_pkey'])?$arr_data['0']['EmployeeDetails']['emp_pkey']:''; ?>">
        <div> 
            <h3>Edit My Basic Informations</h3>
            <hr style="border-top: 1px solid #cec1c1; ">
            <div class="form-group">
                 <div class="col-md-6">
                    <?php $md = isset($arr_data['0']['EmployeeDetails']['middile_name'])?$arr_data['0']['EmployeeDetails']['middile_name']:''; 
                          $lst = isset($arr_data['0']['EmployeeDetails']['last_name'])?$arr_data['0']['EmployeeDetails']['last_name']:''; 
                    ?>
                    <label style="text-align:left;" class="col-md-4 control-label" for="first_name">Name &nbsp;<span style="color:red;">*</span></label>
                    <div class="col-md-8">
                        <input id="first_name" name="first_name" value="<?php echo isset($arr_data['0']['EmployeeDetails']['first_name'])?$arr_data['0']['EmployeeDetails']['first_name'].' '.$md.' '.$lst:''; ?>" disabled type="text" placeholder="Name" class="form-control input-md" required="" data-parsley-id="6919"><ul class="parsley-errors-list" id="parsley-id-6919"></ul>
                    </div>
                </div>
<!--                <div class="col-md-6">
                    <label style="text-align:left;" class="col-md-4 control-label" for="middile_name">Middle Name</label>
                    <div class="col-md-8">
                        <input id="middile_name" name="middile_name" value="<?php echo isset($arr_data['0']['EmployeeDetails']['middile_name'])?$arr_data['0']['EmployeeDetails']['middile_name']:''; ?>" type="text" placeholder="Middle Name" class="form-control input-md" data-parsley-id="3261"><ul class="parsley-errors-list" id="parsley-id-3261"></ul>
                    </div>
                </div>-->
 <div class="col-md-6">
                    <label style="text-align:left;" class="col-md-4 disabled control-label" for="classification">Gender &nbsp;<span style="color:red;">*</span></label>
                    <div class="col-md-8">
                        <select id="classification" name="classification" class="form-control" required="" data-parsley-id="2084">
                            <option value="">--Select--</option>
                            <option <? if($arr_data['0']['EmployeeDetails']['classification'] == 'male') { echo 'selected="selected" '; } ?> value="male">Male</option>
                            <option <? if($arr_data['0']['EmployeeDetails']['classification'] == 'female') { echo 'selected="selected" '; } ?> value="female">Female</option>
                            <option <? if($arr_data['0']['EmployeeDetails']['classification'] == 'Other') { echo 'selected="selected" '; } ?> value="Other">Other</option>
                        </select>
                    </div>
                </div>
            </div>

     <!--       <div class="form-group">
                <div class="col-md-6">
                    <label style="text-align:left;" class="col-md-4 control-label" for="last_name">Last Name &nbsp;<span style="color:red;">*</span></label>
                    <div class="col-md-8">
                        <input id="last_name" name="last_name" value="<?php echo isset($arr_data['0']['EmployeeDetails']['last_name'])?$arr_data['0']['EmployeeDetails']['last_name']:''; ?>" type="text" placeholder="Last Name" class="form-control input-md" required="" data-parsley-id="3716"><ul class="parsley-errors-list" id="parsley-id-3716"></ul>
                    </div>
                </div>
               
            </div> 
        </div>-->


        <div class="form-group">
            <div class="col-md-6">
                <label style="text-align:left;" class="col-md-4 control-label" for="address">Address</label>
                <div class="col-md-8">
                    <input id="address" name="address" value="<?php echo isset($arr_data['0']['EmployeeDetails']['address'])?$arr_data['0']['EmployeeDetails']['address']:''; ?>" type="text" placeholder="address" class="form-control input-md" data-parsley-id="3452"><ul class="parsley-errors-list" id="parsley-id-3452"></ul>
                </div>
            </div>
            <div class="col-md-6">
                <label style="text-align:left;" class="col-md-4 control-label" for="city">City</label>
                <div class="col-md-8">
                    <input id="city" name="city" value="<?php echo isset($arr_data['0']['EmployeeDetails']['city'])?$arr_data['0']['EmployeeDetails']['city']:''; ?>" type="text" placeholder="City" class="form-control input-md" data-parsley-id="6550"><ul class="parsley-errors-list" id="parsley-id-6550"></ul>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="col-md-6">
                <label style="text-align:left;" class="col-md-4 control-label" for="pincode">Zip</label>
                <div class="col-md-8">
                    <input id="pincode" name="pincode" value="<?php echo isset($arr_data['0']['EmployeeDetails']['pincode'])?$arr_data['0']['EmployeeDetails']['pincode']:''; ?>" type="text" placeholder="Zip" class="form-control input-md" data-parsley-id="4762"><ul class="parsley-errors-list" id="parsley-id-4762"></ul>
                </div>
            </div>
            <div class="col-md-6">
                <label style="text-align:left;" class="col-md-4 control-label" for="state">State</label>
                <div class="col-md-8">
                    <input id="state" name="state" value="<?php echo isset($arr_data['0']['EmployeeDetails']['state'])?$arr_data['0']['EmployeeDetails']['state']:''; ?>" type="text" placeholder="State" class="form-control input-md" data-parsley-id="0241"><ul class="parsley-errors-list" id="parsley-id-0241"></ul>
                </div>
            </div>
        </div> 

        <div class="form-group">
            <div class="col-md-6">
                <label style="text-align:left;" class="col-md-4 control-label" for="mobile_no">Mobile No</label>
                <div class="col-md-8">
                    <input id="mobile_no" name="mobile_no" value="<?php echo isset($arr_data['0']['EmployeeDetails']['mobile_no'])?$arr_data['0']['EmployeeDetails']['mobile_no']:''; ?>" type="text" placeholder="Mobile No" class="form-control input-md" data-parsley-id="0842"><ul class="parsley-errors-list" id="parsley-id-0842"></ul>
                </div>
            </div>
            <div class="col-md-6">
                <label style="text-align:left;" class="col-md-4 control-label" for="email">Email &nbsp;</label>
                <div class="col-md-8">
                   
               <!--edited by sinsiya on 30-11-2024-->
                  <?php if($company_code == 'HRBL'){?>
                     
                          <input id="email" name="email" value="<?php echo isset($arr_data['0']['EmployeeDetails']['email'])?$arr_data['0']['EmployeeDetails']['email']:''; ?>"  type="email" placeholder="Email" class="form-control input-md" data-parsley-id="6262"><ul class="parsley-errors-list" id="parsley-id-6262"></ul>  
                 <?php }else{  ?>
                        <input id="email" name="email" value="<?php echo isset($arr_data['0']['EmployeeDetails']['email'])?$arr_data['0']['EmployeeDetails']['email']:''; ?>" disabled  type="email" placeholder="Email" class="form-control input-md" data-parsley-id="6262"><ul class="parsley-errors-list" id="parsley-id-6262"></ul>
                 <?php
                   }
              ?>
                </div>
            </div>
        </div>
        <hr style="border-top: 1px solid #cec1c1; ">

        <div class="form-group">
            <div class="col-md-6">
                <label style="text-align:left;" class="col-md-4 control-label" for="maritual_status">Marital Status</label>
                <div class="col-md-8">
                    <select id="maritual_status" name="maritual_status" class="form-control" data-parsley-id="4152">
                        <!--<option value="">--Select--</option>-->
                        <option <? if($arr_data['0']['EmployeeDetails']['relation_guardian'] == 'single') { echo 'selected="selected" '; } ?> value="single">Single</option>
                        <option <? if($arr_data['0']['EmployeeDetails']['relation_guardian'] == 'married') { echo 'selected="selected" '; } ?>  value="married">Married</option>
                    </select><ul class="parsley-errors-list" id="parsley-id-4152"></ul>
                </div>
            </div>
            <div class="col-md-6">
                <label style="text-align:left;" class="col-md-4 control-label" for="education">Education</label>
                <div class="col-md-8">
                    <select id="education" name="education" class="form-control" data-parsley-id="9501">
                        <!--<option value="">--Select--</option>-->
                        <option value="Graduate" <? if($arr_data['0']['EmployeeDetails']['relation_guardian'] == 'Graduate') { echo 'selected="selected" '; } ?> >Graduate</option>
                        <option value="Post-Graduate" <? if($arr_data['0']['EmployeeDetails']['relation_guardian'] == 'Post-Graduate') { echo 'selected="selected" '; } ?> >Post-Graduate</option>
                        <option value="Under-Graduate" <? if($arr_data['0']['EmployeeDetails']['relation_guardian'] == 'Under-Graduate') { echo 'selected="selected" '; } ?> >Under-Graduate</option>
                        <option value="No Education" <? if($arr_data['0']['EmployeeDetails']['relation_guardian'] == 'No Education') { echo 'selected="selected" '; } ?> >No Education</option>
                    </select><ul class="parsley-errors-list" id="parsley-id-9501"></ul>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="col-md-6">
                <label style="text-align:left;" class="col-md-4 control-label" for="guradian">Father/Husband Name</label>
                <div class="col-md-8">
                    <input id="guardian" name="guradian" value="<?php echo isset($arr_data['0']['EmployeeDetails']['guradian'])?$arr_data['0']['EmployeeDetails']['guradian']:''; ?>" type="text" placeholder="Enter Name" class="form-control input-md" data-parsley-id="2433"><ul class="parsley-errors-list" id="parsley-id-2433"></ul>
                </div>
            </div>
            <div class="col-md-6">
                <label style="text-align:left;" class="col-md-4 control-label" for="relation_guardian">Relationship</label>
                <div class="col-md-8">
                    <select id="relationship" name="relation_guardian" class="form-control" >
                        <option value="Father" <? if($arr_data['0']['EmployeeDetails']['relation_guardian'] == 'Father') { echo 'selected="selected" '; } ?> >Father</option>
                        <option value="Husband"  <? if($arr_data['0']['EmployeeDetails']['relation_guardian'] == 'Husband') { echo 'selected="selected" '; } ?> >Husband</option>
                    </select>
                </div>
            </div>
        </div>


        <!--    <div class="form-group">
                <div class="col-md-6">
                    <label style="text-align:left;" class="col-md-4 control-label" for="date_of_birth">Birth Date</label>
                    <div class="col-md-8">
                        <input id="date_of_birth" name="date_of_birth" value="" type="text" placeholder="(YYYY-MM-DD)" class="form-control input-md" required="required" data-parsley-id="7439"><ul class="parsley-errors-list" id="parsley-id-7439"></ul>
                    </div>
                </div>
                <div class="col-md-6">
                    <label style="text-align:left;" class="col-md-4 control-label" for="blood">Blood Group</label>
                    <div class="col-md-8">
                        <input id="blood" name="blood" value="" type="text" placeholder="BLOOD GROUP" class="form-control input-md" data-parsley-id="1834"><ul class="parsley-errors-list" id="parsley-id-1834"></ul>
                    </div>
                </div>
        
            </div>-->

        <hr style="border-top: 1px solid #cec1c1; ">
        <div class="form-group">

            <div class="col-md-6">
                <label style="text-align:left;" class="col-md-4 control-label" for="account_no">Bank Account No</label>
                <div class="col-md-8">
                    <input id="account_no" name="account_no" value="<?php echo isset($arr_data['0']['EmployeeDetails']['account_no'])?$arr_data['0']['EmployeeDetails']['account_no']:''; ?>" type="text" placeholder="Account No" class="form-control input-md" data-parsley-id="5330"><ul class="parsley-errors-list" id="parsley-id-5330"></ul>
                </div>
            </div>
        </div>


        <div class="form-group">
            <div class="col-md-6">
                <label style="text-align:left;" class="col-md-4 control-label" for="bank_name">Bank Name</label>
                <div class="col-md-8">
                    <input id="bank_name" name="bank_name" value="<?php echo isset($arr_data['0']['EmployeeDetails']['bank_name'])?$arr_data['0']['EmployeeDetails']['bank_name']:''; ?>" type="text" placeholder="Bank Name" class="form-control input-md" data-parsley-id="6851"><ul class="parsley-errors-list" id="parsley-id-6851"></ul>
                </div>
            </div>
            <div class="col-md-6">
                <label style="text-align:left;" class="col-md-4 control-label" for="ifsc_code">Bank IFSC Code</label>
                <div class="col-md-8">
                    <input id="ifsc_code" name="ifsc_code" value="<?php echo isset($arr_data['0']['EmployeeDetails']['ifsc_code'])?$arr_data['0']['EmployeeDetails']['ifsc_code']:''; ?>" type="text" placeholder="Bank IFSC Code" class="form-control input-md" data-parsley-id="5812"><ul class="parsley-errors-list" id="parsley-id-5812"></ul>
                </div>
            </div>
        </div>

        <div class="form-group">

            <div class="col-md-6">
                <label style="text-align:left;" class="col-md-4 control-label" for="PAN No">PAN No</label>
                <div class="col-md-8">
                    <input id="pan_no" name="pan_no" value="<?php echo isset($arr_data['0']['EmployeeDetails']['pan_no'])?$arr_data['0']['EmployeeDetails']['pan_no']:''; ?>" type="text" placeholder="PAN No" class="form-control input-md" data-parsley-id="9414"><ul class="parsley-errors-list" id="parsley-id-9414"></ul>
                </div>
            </div>
            <div class="col-md-6">
                <label style="text-align:left;" class="col-md-4 disabled control-label" for="id_card">ID/AADHAR No</label>
                <div class="col-md-8">
                    <input id="id_card" name="id_card" value="<?php echo isset($arr_data['0']['EmployeeDetails']['id_card'])?$arr_data['0']['EmployeeDetails']['id_card']:''; ?>" type="text" placeholder="ID Card No" class="form-control disabled input-md" data-parsley-id="6964"><ul class="parsley-errors-list" id="parsley-id-6964"></ul>
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="col-md-6">
                <label style="text-align:left;" class="col-md-4 disabled control-label" for="esi">ESI - No</label>
                <div class="col-md-8">
                    <input id="esi" name="esi" value="<?php echo isset($arr_data['0']['EmployeeDetails']['esi'])?$arr_data['0']['EmployeeDetails']['esi']:''; ?>" type="text" placeholder="ESI" class="form-control disabled input-md" data-parsley-id="4663"><ul class="parsley-errors-list" id="parsley-id-4663"></ul>
                </div>
            </div>
            <div class="col-md-6">
                <label style="text-align:left;" class="col-md-4 disabled control-label" for="account_no"> UAN </label>
                <div class="col-md-8">
                    <input id="pf" name="pf" value="<?php echo isset($arr_data['0']['EmployeeDetails']['pf'])?$arr_data['0']['EmployeeDetails']['pf']:''; ?>" type="text" placeholder="PF" class="form-control disabled input-md" data-parsley-id="1739"><ul class="parsley-errors-list" id="parsley-id-1739"></ul>
                </div>
            </div>

        </div>

        <div class="form-group">
            <div class="col-md-6">
                <label style="text-align:left;" class="col-md-4 disabled control-label" for="company_pf">PF - No</label>
                <div class="col-md-8">
                    <input id="company_pf" name="company_pf" value="<?php echo isset($arr_data['0']['EmployeeDetails']['company_pf'])?$arr_data['0']['EmployeeDetails']['company_pf']:''; ?>" type="text" placeholder="PF" class="form-control disabled input-md" data-parsley-id="1737"><ul class="parsley-errors-list" id="parsley-id-1737"></ul>
                </div>
            </div>
            <div class="col-md-6">
                <label style="text-align:left;" class="col-md-4 disabled control-label" for="esi_dispensary"> ESI Dispensary </label>
                <div class="col-md-8">
                    <input id="esi_dispensary" name="esi_dispensary" value="<?php echo isset($arr_data['0']['EmployeeDetails']['esi_dispensary'])?$arr_data['0']['EmployeeDetails']['esi_dispensary']:''; ?>" type="text" placeholder="ESI DISPENSARY" class="form-control disabled input-md" data-parsley-id="1193"><ul class="parsley-errors-list" id="parsley-id-1193"></ul>
                </div>
            </div>

        </div>
        <hr>
        <div class="form-group">
            <div class=" col-md-12">
                <button class="btn btn-primary pull-right">Save</button>
                <button type="button" class="btn btn-warning pull-left" onclick="cancel_edit();">Cancel</button>
            </div>
        </div>
    </div>
</form>
<script>
    $(document).ready(function() {
        
        
            var options_basics = { 
                success:       function(resp){
//                    alert(resp);
                    resp = $.parseJSON(resp);
                    var errorMsg = resp.msg;
                    loadbasicsettings();
                    $.notify(resp.msg,{
                       type: 'success',
                       allow_dismiss: false
                    });
                $('#changebasics').trigger("reset");
                 }
       
            };
            
        $('#changebasics').submit(function() { 
                
                $(this).ajaxSubmit(options_basics); 


                return false; 
            });
    });
</script>