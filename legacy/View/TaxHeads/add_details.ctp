  <form class="form-horizontal" id="salaryitemForm" action="<?php echo $this->webroot; ?>TaxHeads/addtaxheaddetails" method="post">
      
           <div class="modal-body">
     
<fieldset>
    <?php if(isset($kid)){?>
    <legend>Edit Tax Heads Details</legend>
    <?php } else{ ?>
<legend>Add Tax Heads Details</legend>
<?php }?>
<input id="ITEM_HID" name="ITEM_HID" value="<?php echo $id; ?>" type="hidden">
<?php if(isset($kid)){?><input id="TAX_ITEM_ID" name="TAX_ITEM_ID" value="<?php echo $kid; ?>" type="hidden">
<?php }?>
<div class="form-group">
  <label class="col-md-4 control-label" for="head_name">Tax head Details</label>  
  <div class="col-md-5">
     <textarea  id="TAX_DESC" name="TAX_DESC" class="form-control input-md"><?php if(isset($kid)){ echo $TaxHeadDetail['detail']; } ?></textarea>
  </div>
</div>
   
  <div class="form-group">
  <label class="col-md-4 control-label" for="Tax_description">Tax head Details first</label>  
  <div class="col-md-5">
      <textarea  id="TAX_DESC1" name="TAX_DESC1" class="form-control input-md"><?php if(isset($kid)){ echo $TaxHeadDetail['detail1']; } ?></textarea>
  </div>
</div>
  <div class="form-group">
  <label class="col-md-4 control-label" for="Tax_description">Limit</label>  
  <div class="col-md-5">
      <textarea  id="TAX_DESC12" name="limit" class="form-control input-md"><?php if(isset($kid)){ echo $TaxHeadDetail['detail2']; } ?></textarea>
  </div>
</div>
<div class="form-group">
  <label class="col-md-4 control-label" for="head_name">Tax Detail Active</label>  
  
  <div class="col-md-5">
      <select id="TAX_ACTIVE" name="TAX_ACTIVE" class="form-control input-md">
          
     <option value="1" <?php if(isset($kid)){ echo( $TaxHeadDetail['active'] == '1') ? 'selected="selected"' : ''; }?>>YES</option>
  <option value="0" <?php if(isset($kid)){ echo( $TaxHeadDetail['active'] == '0') ? 'selected="selected"' : ''; }?>>NO</option>
 
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
	closeModal('shitems1');
}
    }; 
      
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
    
    }); 
</script>