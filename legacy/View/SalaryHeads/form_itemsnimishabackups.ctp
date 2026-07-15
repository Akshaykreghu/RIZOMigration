  <form class="form-horizontal" id="salaryitemForm" action="<?php echo $this->webroot; ?>SalaryHeads/addsalaryheaditems" method="post">
      
           <div class="modal-body">
     
<fieldset>
<?php if(isset($kid)){  //print_r($SalaryHeadItems);?>
<legend>Edit Salary Heads Items</legend>
<?php } else { ?>
<!-- Form Name -->
<legend> Add Salary Heads Items</legend>
<?php }?>
<!-- Text input-->
   <input id="ITEM_ID" name="ITEM_ID" value="<?php  echo $id;?>" type="hidden">
  <?php if(isset($kid)){ ?> <input id="SALARYHEADITEM_ID" name="SALARYHEADITEM_ID" value="<?php echo $kid;?>" type="hidden"><?php } ?>
  <div class="form-group">
 
  <div class="col-md-4">
      <label for="head_operator" style="color: blue"><?php echo $Operator ;?></label>   
  </div>
</div>
<div class="form-group">
  <label class="col-md-4 control-label" for="head_name">Item Name</label>  
  <div class="col-md-5">
      <input id="ITEM_NAME" name="ITEM_NAME" value="<?php if(isset($kid)){ echo $SalaryHeadItems['item'];}?>" type="text" placeholder="Item Name" class="form-control input-md" required="">
    
  </div>
</div>
   <?php if(isset($ocurrance) && $ocurrance!='LEAVE'){ ?> 
   <div class="form-group">
  <label class="col-md-4 control-label" for="head_name">Item Type</label>  
  <div class="col-md-5">
  <select id="ITEM_TYPE" name="ITEM_TYPE" class="form-control input-md"  >
<option value="Limit" <?php if(isset($kid)){ echo($SalaryHeadItems["item_type"] == 'Limit') ? 'selected="selected"' : ''; }?>>Limit</option>
  <option value="Fixed" <?php if(isset($kid)){ echo($SalaryHeadItems["item_type"] == 'Fixed') ? 'selected="selected"' : ''; }?>>Fixed</option>
   <option value="Manually" <?php if(isset($kid)){ echo($SalaryHeadItems["item_type"] == 'Manually') ? 'selected="selected"' : ''; }?>>Manually</option>
  <!--  <option value="Payable in" <?php if(isset($kid)){ echo($SalaryHeadItems["item_type"] == 'Payable in') ? 'selected="selected"' : ''; }?>>Payable in</option>-->
    <option value="Formula" <?php if(isset($kid)){ echo($SalaryHeadItems["item_type"] == 'Formula') ? 'selected="selected"' : ''; }?>>Formula</option>
    <option value="N/A" <?php if(isset($kid)){ echo($SalaryHeadItems["item_type"] == 'N/A') ? 'selected="selected"' : ''; }?>>N/A</option>

  </select>
  </div>
</div>
   <?php }  else { ?>
    <div class="form-group">
  <label class="col-md-4 control-label" for="head_name">Item Type</label>  
  <div class="col-md-5">
  <select id="ITEM_TYPE" name="ITEM_TYPE" class="form-control input-md"  >

    <option value="LEAVE" <?php if(isset($kid)){ echo($SalaryHeadItems["item_type"] == 'LEAVE') ? 'selected="selected"' : ''; }?>>LEAVE</option>

  </select>
  </div>
</div>
   <?php }   if(isset($ocurrance) && $ocurrance!='LEAVE'){ ?> 
  <div class="form-group">
  <label class="col-md-4 control-label" for="head_name">Item Occurance</label>  
  <div class="col-md-5">
  <select id="ITEM_OCCURANCE" name="ITEM_OCCURANCE"  class="form-control input-md">
               
     <option value="Monthly" <?php if(isset($kid)){ echo($SalaryHeadItems["occurance"] == 'Monthly') ? 'selected="selected"' : ''; }?>>Monthly</option>
  <option value="Yearly" <?php if(isset($kid)){ echo($SalaryHeadItems["occurance"] == 'Yearly') ? 'selected="selected"' : ''; }?>>Yearly</option>
   <option value="Half Yearly" <?php if(isset($kid)){ echo($SalaryHeadItems["occurance"] == 'Half Yearly') ? 'selected="selected"' : ''; }?>>Half Yearly</option>
    <option value="Quaterly" <?php if(isset($kid)){ echo($SalaryHeadItems["occurance"] == 'Quaterly') ? 'selected="selected"' : ''; }?>>Quaterly</option>
      </select>
    
  </div>
</div> 
      <?php } else { ?>
      
    <div class="form-group">
  <label class="col-md-4 control-label" for="head_name">Item Short Name</label>  
  <div class="col-md-5">
  <input id="ITEM_OCCURANCE" name="ITEM_OCCURANCE"  value="<?php if(isset($kid)){ echo $SalaryHeadItems['occurance'];} ?>" type="text" class="form-control input-md">
  
    
  </div>
</div>     
      
      <?php } if(isset($ocurrance) && $ocurrance!='LEAVE'){ ?> 
<div class="form-group">
  <label class="col-md-4 control-label" for="head_name">Item Value</label>  
  <div class="col-md-5">
  <input id="ITEM_VALUE" name="ITEM_VALUE" value="<?php if(isset($kid)){ echo $SalaryHeadItems['item_value'];} ?>" type="text" placeholder="Item Value" class="form-control input-md" required="">
    
  </div>
</div>
   
   
  <div class="form-group">
  <label class="col-md-4 control-label" for="head_name" id='datesfrm'>Item Starts From</label>  
    <label class="col-md-4 control-label" for="head_name" id='payablein' style="display:none">Payable In</label>  
  <div class="col-md-5">
  <input id="ITEM_START" name="ITEM_START" value="<?php if(isset($kid)){ echo $SalaryHeadItems['start_from'];} ?>" type="text" placeholder="yyyy-mm-dd" class="form-control input-md" required="">
    
  </div>
</div> <?php }?>
  <div class="form-group">
  <label class="col-md-4 control-label" for="head_name">Comment</label>  
  <div class="col-md-5">
      <textarea  id="ITEM_COMMENT" name="ITEM_COMMENT"  class="form-control input-md">
          <?php if(isset($kid)){ echo trim($SalaryHeadItems["comments"]);} ?>
      </textarea>
  </div>
</div>
   <?php if(isset($ocurrance) && $ocurrance!='LEAVE'){ ?> 
<div class="form-group">
 <label class="col-md-4 control-label" for="head_name">Is In Salary Slip</label>  
  
  <div class="col-md-5"> 
     <select id="ITEM_IN_SAL" name="ITEM_IN_SAL" class="form-control input-md">
  <option value="Y" <?php if(isset($kid)){ echo($SalaryHeadItems["is_show_salslip"] == 'Y') ? 'selected="selected"' : ''; }?> >YES</option>
<option value="N" <?php if(isset($kid)){ echo($SalaryHeadItems["is_show_salslip"] == 'N') ? 'selected="selected"' : ''; }?> >NO</option> 
</select> 

  </div>
</div> <?php } ?>
  <div class="form-group">
 <label class="col-md-4 control-label" for="itm_part">Item Part</label>  
  
  <div class="col-md-5"> 
     <select id="ITEM_PART" name="ITEM_PART" class="form-control input-md">
  <option value="Direct" <?php if(isset($kid)){ echo($SalaryHeadItems["item_part"] == 'Direct') ? 'selected="selected"' : ''; }?> >DIRECT</option>
<option value="Indirect" <?php if(isset($kid)){ echo($SalaryHeadItems["item_part"] == 'Indirect') ? 'selected="selected"' : ''; }?> >INDIRECT</option> 
</select> 

  </div>
</div>
</fieldset>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="submit" id="btnSave" name="btnSave" class="btn btn-primary">Save</button>
      </div>
      </form>
<script type="text/javascript">
$(document).ready(function() { 
    $('#salaryitemForm').parsley();
      var options = { 
  success:       function(responseText, statusText, xhr, $form){
	closeModal('shitems');
}
    }; 
  $('#ITEM_START').datepicker(
            {
                format:'yyyy-mm-dd',
            });
                 
      // bind to the form's submit event 
    $('#salaryitemForm').submit(function() { 
        // inside event callbacks 'this' is the DOM element so we first 
        // wrap it in a jQuery object and then invoke ajaxSubmit 
        $(this).ajaxSubmit(options);
 //console.log(options);
        // !!! Important !!! 
        // always return false to prevent standard browser submit and page navigation 
        return false; 
    });
    
       $('#ITEM_OCCURANCE').change( function() {
         $('#datesfrm').show(); 
          $('#payablein').hide();
            var type = $('#ITEM_OCCURANCE').val();  
            if(type=='Yearly')
            {
               $('#datesfrm').hide(); 
               $('#payablein').show();
            }
          
           // empleaverequeststable.search( this.value ).draw();
        });  
    
    }); 
</script>