<form  id="contactInfoForm" action="<?php echo $this->webroot; ?>CompanyNew/savecompanysetup" method="post">
    <div class="col-md-3" >
        <br>
                        <input type="file" class="form-control file"  id="companylogofile" name="companylogofile" >
                        <br>
                        <label><input type="radio" name="logosize" onclick="$('.file-preview-image').css({'height':'200px','width':'200px'})" value="1" <?php echo isset($contactinfo['logosize']) && $contactinfo['logosize'] == "1" ? 'checked="checked"' : '' ?> >Square</label>
                        <label><input type="radio" name="logosize" value="0" onclick="$('.file-preview-image').css({'height':'100px','width':'200px'})" <?php echo isset($contactinfo['logosize']) && $contactinfo['logosize'] == "0" ? 'checked="checked"' : '' ?> >Rectangle</label>
    </div>
    
    <div class="col-md-9">
        <br>
        <div class="row">
                    <div class="form-group">
                      <label for="business_name" class="col-sm-2 control-label">Company Name<span class="star">*</span></label>
                       <div class="col-sm-1">:</div>
                      <div class="col-sm-9">
                        <input type="text" required class="form-control"   data-validation-error-msg=" Please enter Business Name"  value="<?php echo isset($contactinfo['business_name']) ? $contactinfo['business_name'] : '' ?>" name="business_name" id="business_name" placeholder="Business Name">
                      </div>
                    </div>
        </div>
        <br>
        <div class="row">
                    <div class="form-group">
                      <label for="business_type" class="col-sm-2 control-label">Type Of Business<span class="star">*</span></label>
                       <div class="col-sm-1">:</div>
                      <div class="col-sm-3">
                        <input type="text" required class="form-control"   data-validation-error-msg="Please enter Type Of Business"   value="<?php echo isset($contactinfo['business_type']) ? $contactinfo['business_type'] : '' ?>" id="business_type" name="business_type" placeholder="Type Of Business">
                      </div>
                    
                      <label for="business_nature" required class="col-sm-2 control-label">Nature Of Business</label>
                       <div class="col-sm-1">:</div>
                      <div class="col-sm-3">
                        <input type="text" class="form-control"   data-validation-error-msg=" Please enter Nature Of Business"    value="<?php echo isset($contactinfo['business_nature']) ? $contactinfo['business_nature'] : '' ?>" id="business_nature" name="business_nature" placeholder="Nature Of Business">
                      </div>
                    </div>
        </div>
        <br>
        <div class="row">
                    <div class="form-group">
                      <label for="address" class="col-sm-2 control-label">Address<span class="star">*</span></label>
                       <div class="col-sm-1">:</div>
                      <div class="col-sm-9">
                        <textarea class="form-control" style="height:30px;" id="address"  required name="address" placeholder="Address"  data-validation-error-msg="Please enter Address" ><?php echo isset($contactinfo['address']) ? $contactinfo['address'] : '' ?></textarea> 
                      </div>
                    </div>  
        </div> <br>               
           <div class="row"> 
               <label for="city" class="col-sm-2 control-label">City<span class="star">*</span></label>
                       <div class="col-sm-1">:</div>
                      <div class="col-sm-3">
                        <input type="text" class="form-control" required  id="city" name="city" data-validation-error-msg="Please Enter City" value="<?php echo isset($contactinfo['city']) ? $contactinfo['city'] : '' ?>" placeholder="city">
                      </div>
                      <label for="state" class="col-sm-2 control-label">State<span class="star">*</span></label>
                       <div class="col-sm-1">:</div>
                      <div class="col-sm-3">
                        <input type="text" class="form-control"  required id="state" name="state"  data-validation-error-msg="Please enter State" value="<?php echo isset($contactinfo['state']) ? $contactinfo['state'] : '' ?>" placeholder="State">
                      </div>
                       
                    
            </div>
        
        <br>
        <div class="row">
                    <div class="form-group">
                      <label for="pincode" class="col-sm-2 control-label">Zip Code<span class="star">*</span></label>
                       <div class="col-sm-1">:</div>
                      <div class="col-sm-3">
                        <input type="number" class="form-control"  required id="pincode"  name="pincode"   data-validation-error-msg="Please enter Zip Code" value="<?php echo isset($contactinfo['pincode']) ? $contactinfo['pincode'] : '' ?>" placeholder="Zip Code">
                      </div>
                      <label for="phone" class="col-sm-2  control-label" >Phone<span class="star">*</span></label>
                       <div class="col-sm-1">:</div>
                      <div class="col-sm-3">
                        <input type="number" class="form-control"  required id="phone" name="phone"  data-validation-error-msg="Please enter Phone" value="<?php echo isset($contactinfo['phone']) ? $contactinfo['phone'] : '' ?>" placeholder="Phone">
                      </div>
                      
                    </div>
        </div>
        <br>
        <div class="row">
                    <div class="form-group">
                       <label for="email" class="col-sm-2  control-label">Email<span class="star">*</span></label>
                       <div class="col-sm-1">:</div>
                      <div class="col-sm-3">
                        <input type="email" class="form-control"  required id="email" name="email" data-validation-error-msg="Please enter Email" value="<?php echo isset($contactinfo['email']) ? $contactinfo['email'] : '' ?>" placeholder="Email">
                      </div>
                    
                      <label for="fax" class="col-sm-2 control-label">Fax</label>
                       <div class="col-sm-1">:</div>
                      <div class="col-sm-3">
                        <input type="number" class="form-control"  id="fax" name="fax" value="<?php echo isset($contactinfo['fax']) ? $contactinfo['fax'] : '' ?>" placeholder="Fax">
                      </div>
                    </div>
        </div>
        <br>
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
        <div class="row">
                     <div class="form-group">
                      <label for="panno" class="col-sm-2 control-label">TAN No.</label>
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
                        <input type="text" class="form-control" id="empstateinsno" name="empstateinsno" value="<?php echo isset($complianceInfo['empstateinsno']) ? $complianceInfo['empstateinsno'] : '' ?>" placeholder="Emp State Ins No">
                      </div>
                    </div>
        </div>
        <br>
        <div class="row">
                    <div class="form-group">
                      <label for="ptnoco" class="col-sm-2 control-label">Prof Tax No(Co.)</label>
                       <div class="col-sm-1">:</div>
                      <div class="col-sm-3">
                        <input type="text" class="form-control" id="ptnoco"  name="ptnoco" value="<?php echo isset($complianceInfo['ptnoco']) ? $complianceInfo['ptnoco'] : '' ?>" placeholder="Prof Tax No(Co.)">
                      </div>
                    
                      <label for="ptnodir" class="col-sm-2 control-label">Prof Tax No(Dir.)</label>
                       <div class="col-sm-1">:</div>
                      <div class="col-sm-3">
                        <input type="text" class="form-control" id="ptnodir" value="<?php echo isset($complianceInfo['ptnodir']) ? $complianceInfo['ptnodir'] : '' ?>" name="ptnodir" placeholder="Prof Tax No(Dir.)">
                      </div>
                    </div>
        </div>
        <br>
        <div class="row">
                    <div class="form-group">
                      <label for="ptnoemp" class="col-sm-2 control-label">Prof Tax No(Emp.)</label>
                       <div class="col-sm-1">:</div>
                      <div class="col-sm-3">
                        <input type="text" class="form-control" id="ptnoemp" value="<?php echo isset($complianceInfo['ptnoemp']) ? $complianceInfo['ptnoemp'] : '' ?>" name="ptnoemp" placeholder="Prof Tax No(Emp.)">
                      </div>
                    </div>
        </div> 
</div>
    
                <button type="submit" id="cinfosave" class="btn btn-primary pull-right "   style="margin-right:5px">Save</button>
    <button type="button" id="cinfocancel" class="btn btn-danger pull-right "  onclick="canceledit();" style="margin-right:5px">Cancel</button>
                
                   
                </form>
                  
                  


<script type="text/javascript">
        function canceledit(){
        $("#infoid").load(livesite+"CompanyNew/viewinfo")
    }
   function saveedit(){
         $("#infoid").load(livesite+"CompanyNew/viewinfo")
    }
          $("#companylogofile").fileinput({
            <?php if(isset($contactinfo['logo']) && $contactinfo['logo'] != null){ ?>
                initialPreview: [
                    '<img src="<?php echo   $this->webroot.$contactinfo['logo']; ?>" class="file-preview-image" alt="<?php echo $contactinfo['business_name']; ?>" title="<?php echo $contactinfo['business_name']; ?>">',
                ],
            <?php } ?>
                showCaption: false,
                showUpload: false,
		showRemove:false,		
    removeIcon: '<i class="glyphicon glyphicon-remove"></i>',
    removeTitle: 'Cancel or reset changes',
	
            });
            
    $("#cinfoedit").on("click",function(){

            if($("#contactInfoForm").hasClass("infomode")){

                    $('#companylogofile').fileinput('enable');
                    $("#contactInfoForm").find("input,textarea").each(function(){
            //	console.log(this)
                    $(this).prop("readonly",false);
            })

                    $("#contactInfoForm").removeClass("infomode");
                    $("#contactInfoForm").addClass("editmode");
                    $("#cinfosave").prop('disabled', false);
                    $(this).html("Cancel Edit");
            }else{

                    $('#companylogofile').fileinput('enable');
                            $("#contactInfoForm").find("input,textarea").each(function(){
                    console.log(this)
                    $(this).prop("readonly",true);

            })
                    $("#cinfosave").prop('disabled', true);
                    $("#contactInfoForm").removeClass("editmode");
                    $("#contactInfoForm").addClass("infomode");
            $("#cinfoedit").html("Edit");
            }

    });
   $(document).ready(function() { 
	$.validate({
    modules : 'location, date, security, file'});
    var options = { 
       success:       function(resp){ $("#infoid").load(livesite+"CompanyNew/viewinfo");
       	$.notify($.parseJSON(resp).msg,{
											type: 'success',
                                                                                       
											allow_dismiss: false
																									
										});
       }  // post-submit callback 
 
    }; 
    if(<?php echo $contactinfo['logosize'] ?> == "1")
        $('.file-preview-image').css({'height':'200px','width':'200px'});
    else
        $('.file-preview-image').css({'height':'100px','width':'200px'});    
    
    
    // bind to the form's submit event 
    $('#contactInfoForm').submit(function() { 
        // inside event callbacks 'this' is the DOM element so we first 
        // wrap it in a jQuery object and then invoke ajaxSubmit 
        $(this).ajaxSubmit(options); 
 
        // !!! Important !!! 
        // always return false to prevent standard browser submit and page navigation 
        return false; 
    });
    
    });

</script>