  <form class="form-horizontal" id="taxtypefrm" action="<?php echo $this->webroot; ?>TaxHeads/addtaxtypes" method="post" action="">
      <div class="modal-body">
     
<fieldset>

<!-- Form Name -->

<legend><?php if(isset($id)){ ?>Edit Tax Type
<?php } else {
?>
 Add Tax Type
<?php }?>
<!-- Text input-->
 </legend>
<div class="form-group">
  <label class="col-md-4 control-label" for="head_name">Tax Name</label>  
  <div class="col-md-5">
      <input id="TAX_NAME" name="TAX_NAME" value="<?php if(isset($id)){ echo $desc['name'];}?>" type="text" placeholder="Tax Name" class="form-control input-md" required="">
   <?php if(isset($id)){ ?> <input id="TAXHEAD_ID" name="TAXHEAD_ID" value="<?php echo $id;?>" type="hidden"><?php } ?>
  </div>
</div>
 <div class="form-group">
  <label class="col-md-4 control-label" for="head_name">Tax Description</label>  
  <div class="col-md-5">
      <input id="TAX_DESC" name="TAX_DESC" value="<?php if(isset($id)){ echo $desc['desc_name'];}?>" type="text" placeholder="Tax Description" class="form-control input-md" required="">
  
  </div>
</div>  
<div class="form-group">
  <label class="col-md-4 control-label" for="head_name">Operator</label>  
  <div class="col-md-5">
        <select id="ITEM_OPERATOR" name="ITEM_OPERATOR"  class="form-control input-md">
               
     <option value="Addition" <?php if(isset($id)){ echo( $desc['operator'] == 'Addition') ? 'selected="selected"' : ''; }?>>Addition</option>
  <option value="Deduction" <?php if(isset($id)){ echo($desc ['operator'] == 'Deduction') ? 'selected="selected"' : ''; }?>>Deduction</option>
     </select>
      </div>
</div>
   
 
<div class="form-group">
  <label class="col-md-4 control-label" for="head_name">Occurance</label>  
  <div class="col-md-5">
  <select id="ITEM_OCCURANCE" name="ITEM_OCCURANCE"  class="form-control input-md">
               
     <option value="Monthly" <?php if(isset($id)){ echo($desc ['occurance']== 'Monthly') ? 'selected="selected"' : ''; }?>>Monthly</option>
  <option value="Yearly" <?php if(isset($id)){ echo($desc ['occurance'] == 'Yearly') ? 'selected="selected"' : ''; }?>>Yearly</option>
   <option value="Half Yearly" <?php if(isset($id)){ echo($desc ['occurance'] == 'Half Yearly') ? 'selected="selected"' : ''; }?>>Half Yearly</option>
    <option value="Quaterly" <?php if(isset($id)){ echo($desc ['occurance']== 'Quaterly') ? 'selected="selected"' : ''; }?>>Quaterly</option>
      </select>
    
  </div>
</fieldset>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Save</button>
      </div>
      </form>
<script type="text/javascript">
$(document).ready(function() { 
  $('#taxtypefrm').parsley();
    var options = { 
  success:       function(responseText, statusText, xhr, $form){
	closeModal('sheads');
        
}
    }; 
 
    // bind to the form's submit event 
    $('#taxtypefrm').submit(function() { //alert('Your book is overdue');
        // inside event callbacks 'this' is the DOM element so we first 
        // wrap it in a jQuery object and then invoke ajaxSubmit 
        $(this).ajaxSubmit(options); 
 
        // !!! Important !!! 
        // always return false to prevent standard browser submit and page navigation 
        return false; 
    });
    }); 
</script>