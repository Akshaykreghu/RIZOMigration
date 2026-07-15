  <form class="form-horizontal" id="salaryitemForm" action="<?php echo $this->webroot; ?>TaxHeads/addtaxheads" method="post">
      
           <div class="modal-body">
     
<fieldset>

<!-- Form Name -->
<?php if(isset($kid)){?>
<legend>Edit Tax Heads Items</legend>
<?php }else { ?>
<legend>Add Tax Heads Items</legend>
<?php } ?>
   <input id="ITEM_ID" name="ITEM_ID" value="<?php  echo $id;?>" type="hidden">

<div class="form-group">
  <label class="col-md-4 control-label" for="head_name">Tax Name</label>  
  <div class="col-md-5">
  <input id="TAX_NAME" name="TAX_NAME" value="<?php if(isset($kid)){ echo $TaxHead['tax_name'];}?>" type="text" placeholder="Tax Name" class="form-control input-md" required="">
    
  </div>
</div>
   <?php if(isset($kid)){?> <div class="form-group"> <input id="TAX_ITEM_ID" name="TAX_ITEM_ID" value="<?php  echo $kid;?>" type="hidden">
 <label class="col-md-4 control-label" for="head_name">Tax Type</label>  
    <div class="col-md-5">
    <select style="display:none;" id="TAX_TYPE" name="TAX_TYPE"  class="form-control input-md">
 <option value="">--Select--</option>
 
 <?php
                                        foreach ($taxnamelist as $key => $value) {
                                    
                                            $selected = ($value['tax_type'] == $TaxHead['tax_type']) ? 'selected="selected"' : '';
                                            echo '<option value="' . $value['tax_type_pkey'] . '" ' . $selected . '>' . $value['tax_type'] . '</option>';
                                        }
                                        ?>
                                       </select>
    </div>
    
    
    </div>
   <?php } ?>
  <div class="form-group">
  <label class="col-md-4 control-label" for="Tax_description">Tax Description</label>  
  <div class="col-md-5">
      <textarea  id="TAX_DESC" name="TAX_DESC" class="form-control input-md"><?php if(isset($kid)){ echo $TaxHead['tax_details'];}?></textarea>
  </div>
</div>
   <div class="form-group">
  <label class="col-md-4 control-label" for="head_name">Limit</label>  
  <div class="col-md-5">
  <input id="limit" name="limit" value="<?php echo isset($TaxHead['attr1'])? $TaxHead['attr1']: '' ; ?>" type="text" placeholder="Tax Limit" class="form-control input-md">
    
  </div>
</div>
<div class="form-group">
  <label class="col-md-4 control-label" for="head_name">Tax Active</label>  
  
  <div class="col-md-5">
      <select id="TAX_ACTIVE" name="TAX_ACTIVE" class="form-control input-md">
          
          
  <option value="Y" <?php if(isset($kid)){ echo( $TaxHead['tax_active'] == 'Y') ? 'selected="selected"' : ''; }?>>YES</option>
  <option value="N" <?php if(isset($kid)){ echo( $TaxHead['tax_active'] == 'N') ? 'selected="selected"' : ''; }?>>NO</option>
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
    
    }); 
</script>