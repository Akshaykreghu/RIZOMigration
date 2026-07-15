  <form class="form-horizontal" id="salaryheadForm" action="<?php echo $this->webroot; ?>SalaryHeads/addsalaryheads" method="post" action="">
      <div class="modal-body">
     
<fieldset>

<legend><?php if(isset($id)){ ?>Edit Salary/Leave Heads
<?php } else {
?>
Add Salary/Leave Heads
<?php }?>
 </legend>
<div class="form-group">
  <label class="col-md-4 control-label" for="head_name">Salary/Leave Head Name</label>  
  <div class="col-md-5">
      <input id="SALARYHEAD_NAME" name="SALARYHEAD_NAME" value="<?php if(isset($id)){ echo $desc['name'];}?>" type="text" placeholder="Salary Head Name" class="form-control input-md" required="">
   <?php if(isset($id)){ ?> <input id="SALARYHEAD_ID" name="SALARYHEAD_ID" value="<?php echo $id;?>" type="hidden"><?php } ?>
  </div>
</div>
  
<div class="form-group">
  <label class="col-md-4 control-label" for="head_name">Operator</label>  
  <div class="col-md-5">
        <select id="ITEM_OPERATOR" name="ITEM_OPERATOR"  class="form-control input-md">
               
     <option value="Addition" <?php if(isset($id)){ echo($desc ['operator'] == 'Addition') ? 'selected="selected"' : ''; }?>>Addition</option>
  <option value="Deduction" <?php if(isset($id)){ echo($desc ['operator'] == 'Deduction') ? 'selected="selected"' : ''; }?>>Deduction</option>
     </select>
      </div>
</div>
   
 
<div class="form-group">
<label class="col-md-4 control-label" for="head_name">Head Type</label>  
<div class="col-md-5">
<select id="ITEM_OCCURANCE" name="ITEM_OCCURANCE"  class="form-control input-md">
 <option value="" >Select </option>
 <option value="FIXED" <?php if(isset($id)){ echo($desc ['head_occurance']== 'FIXED	') ? 'selected="selected"' : ''; }?>>FIXED</option>
<option value="VARIABLE" <?php if(isset($id)){ echo($desc ['head_occurance'] == 'VARIABLE') ? 'selected="selected"' : ''; }?>>VARIABLE</option>
<option value="REIMBURSEMENTS" <?php if(isset($id)){ echo($desc ['head_occurance'] == 'REIMBURSEMENTS') ? 'selected="selected"' : ''; }?>> REIMBURSEMENTS</option>
<option value="CONTRIBUTIONS" <?php if(isset($id)){ echo($desc ['head_occurance']== 'CONTRIBUTIONS') ? 'selected="selected"' : ''; }?>>CONTRIBUTIONS</option>
<option value="LEAVE" <?php if(isset($id)){ echo($desc ['head_occurance']== 'LEAVE') ? 'selected="selected"' : ''; }?>>LEAVE</option>
<option value="NA" <?php if(isset($id)){ echo($desc ['head_occurance']== 'NA') ? 'selected="selected"' : ''; }?>>NA</option>

  </select>

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
    
    
    
    
      $('#SALARYHEAD_NAME').on('change',function(){
        checkIfSalaryheadExists();
    })
    
    function checkIfSalaryheadExists(callback){        
        var SALARYHEAD_NAME = $('#SALARYHEAD_NAME').val();
     
        $.ajax({
            url: 'SalaryHeads/checksalaryheadexists',
            type: 'POST',
            data: {
                SALARYHEAD_NAME: SALARYHEAD_NAME
            },
            success: function (resp)
            {
                if(resp > 0){      
                    alert("Salary Head Already Exists!!");
                    $('#SALARYHEAD_NAME').val('');
                }else{
                    if(typeof callback === 'function'){
                        callback.call();
                    }
                }
            }
        });
    }
    
    
    
    
    
    
$(document).ready(function() { 
  $('#salaryheadForm').parsley();
    var options = { 
  success:       function(responseText, statusText, xhr, $form){
      var response = JSON.parse(responseText);
       // alert(response.success);
            if (response.success) {
            closeModal('sheads');}
	//closeModal('sheads');
}
    }; 
 
    // bind to the form's submit event 
    $('#salaryheadForm').submit(function() { //alert('Your book is overdue');
        // inside event callbacks 'this' is the DOM element so we first 
        // wrap it in a jQuery object and then invoke ajaxSubmit 
        $(this).ajaxSubmit(options); 
 
        // !!! Important !!! 
        // always return false to prevent standard browser submit and page navigation 
        return false; 
    });
    }); 
</script>