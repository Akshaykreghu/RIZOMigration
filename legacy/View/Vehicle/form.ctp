  <form class="form-horizontal" method="post" action="<?php echo $this->webroot; ?>Vehicle/save" id="vehicleform">
      <div class="modal-body">

<fieldset>

<!-- Form Name -->
<legend>Vehicle Master</legend>
<input id="id" name="id" type="hidden"  value="<?php echo $data["id"]; ?>" >
<!-- Text input-->


<!-- Text input-->
<div class="form-group">
  <label class="col-md-4 control-label" for="reg_number">Registration No.<span class="star">*</span></label>  
  <div class="col-md-5">
  <input id="reg_number" name="reg_number"  value="<?php echo $data["reg_number"]; ?>" type="text" placeholder="Registration No." class="form-control input-md" required="">
    
  </div>
</div>
<div class="form-group">
  <label class="col-md-4 control-label" for="model_dec">Model Name<span class="star">*</span></label>  
  <div class="col-md-5">
  <input id="model_dec" name="model_dec"  value="<?php echo $data["model_dec"]; ?>" type="text" placeholder="Model Name" class="form-control input-md" required="">
    
  </div>
</div>
<div class="form-group">
  <label class="col-md-4 control-label" for="make">Company<span class="star">*</span></label>  
  <div class="col-md-5">
  <input id="make" name="make"  value="<?php echo $data["make"]; ?>" type="text" placeholder="Company" class="form-control input-md" required="">
    
  </div>
</div>
<div class="form-group">
  <label class="col-md-4 control-label" for="vehicle_type">Vehicle Type<span class="star">*</span></label>  
  <div class="col-md-5">
  <input id="vehicle_type" name="vehicle_type"  value="<?php echo $data["vehicle_type"]; ?>" type="text" placeholder="Vehicle Type" class="form-control input-md" required="">
    
  </div>
</div>
<div class="form-group">
  <label class="col-md-4 control-label" for="capacity">Capacity<span class="star">*</span></label>  
  <div class="col-md-5">
  <input id="capacity" name="capacity"  value="<?php echo $data["capacity"]; ?>" type="text" placeholder="Capacity" class="form-control input-md" required="">
    
  </div>
</div>
<div class="form-group">
  <label class="col-md-4 control-label" for="make_year">Make Year<span class="star">*</span></label>  
  <div class="col-md-5">
  <input id="make_year" name="make_year"  value="<?php echo $data["make_year"]; ?>" type="text" placeholder="Make Year" class="form-control input-md" required="">
    
  </div>
</div>
<div class="form-group">
  <label class="col-md-4 control-label" for="fual_type">Fuel Type<span class="star">*</span></label>  
  <div class="col-md-5">
  <input id="fual_type" name="fual_type"  value="<?php echo $data["fual_type"]; ?>" type="text" placeholder="Fuel Type" class="form-control input-md" required="">
    
  </div>
</div>
<div class="form-group">
  <label class="col-md-4 control-label" for="tax_token_number">Tax Token No.<span class="star">*</span></label>  
  <div class="col-md-5">
  <input id="tax_token_number" name="tax_token_number"  value="<?php echo $data["tax_token_number"]; ?>" type="text" placeholder="Tax Token No." class="form-control input-md" required="">
    
  </div>
</div>
<div class="form-group">
  <label class="col-md-4 control-label" for="tax_due_date">Tax Due Date<span class="star">*</span></label>  
  <div class="col-md-5">
  <input id="tax_due_date" name="tax_due_date"  value="<?php echo $data["tax_due_date"]; ?>" type="text" placeholder="Tax Due Date" class="form-control input-md" required="">
    
  </div>
</div>
<div class="form-group">
  <label class="col-md-4 control-label" for="insurance_no">Insurance No.<span class="star">*</span></label>  
  <div class="col-md-5">
  <input id="insurance_no" name="insurance_no"  value="<?php echo $data["insurance_no"]; ?>" type="text" placeholder="Insurance No." class="form-control input-md" required="">
    
  </div>
</div>
<div class="form-group">
  <label class="col-md-4 control-label" for="insurance_due_date">Insurance Due Date<span class="star">*</span></label>  
  <div class="col-md-5">
  <input id="insurance_due_date" name="insurance_due_date"  value="<?php echo $data["insurance_due_date"]; ?>" type="text" placeholder="Insurance Due Date" class="form-control input-md" required="">
    
  </div>
</div>
<div class="form-group">
  <label class="col-md-4 control-label" for="pollution_due_date">Pollution Due Date<span class="star">*</span></label>  
  <div class="col-md-5">
  <input id="pollution_due_date" name="pollution_due_date"  value="<?php echo $data["pollution_due_date"]; ?>" type="text" placeholder="Pollution Due Date" class="form-control input-md" required="">
    
  </div>
</div>
<div class="form-group">
  <label class="col-md-4 control-label" for="remarks">Remarks<span class="star">*</span></label>  
  <div class="col-md-5">
  <input id="remarks" name="remarks"  value="<?php echo $data["remarks"]; ?>" type="text" placeholder="Remarks" class="form-control input-md" required="">
    
  </div>
</div>
</fieldset>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Save</button>
      </div>
    </form>   
<script type="text/javascript">
    
    
    $('#expense_type_code').on('change',function(){
        checkIfExpensecodeExists();
    })
    
    function checkIfExpensecodeExists(callback){        
        var expense_type_code = $('#expense_type_code').val();
        var id = $('#id').val();
        $.ajax({
            url: 'ExpenseType/checkexpensetypecodeexists/'+id,
            type: 'POST',
            data: {
                expense_type_code: expense_type_code
            },
            success: function (resp)
            {
                if(resp > 0){      
                    alert("Expense Code Already Exists!!");
                    $('#expense_type_code').val('');
                }else{
                    if(typeof callback === 'function'){
                        callback.call();
                    }
                }
            }
        });
    }
    
    
    $('#expense_type_name').on('change',function(){
        checkIfExpensenameExists();
    })
    
    function checkIfExpensenameExists(callback){        
        var expense_type_name = $('#expense_type_name').val();
        var id = $('#id').val();
        $.ajax({
            url: 'ExpenseType/checkexpensetypecodeexists'+id,
            type: 'POST',
            data: {
                expense_type_name: expense_type_name
            },
            success: function (resp)
            {
                if(resp > 0){      
                   alert("Expense Name Already Exists!!");
                    $('#expense_type_name').val('');
                }else{
                    if(typeof callback === 'function'){
                        callback.call();
                    }
                }
            }
        });
    }
    
    
    
    
    
$(document).ready(function() { 
  $('#deptForm').parsley();
    var options = { 
    success:       function(responseText, statusText, xhr, $form){
        
        var response = JSON.parse(responseText);
       // alert(response.success);
            if (response.success) {
            closeModal('dpttable');}
}
    }; 
 
    // bind to the form's submit event 
    $('#deptForm').submit(function() { 
        // inside event callbacks 'this' is the DOM element so we first 
        // wrap it in a jQuery object and then invoke ajaxSubmit 
        $(this).ajaxSubmit(options); 
 
        // !!! Important !!! 
        // always return false to prevent standard browser submit and page navigation 
        return false; 
    });
    }); 
</script>