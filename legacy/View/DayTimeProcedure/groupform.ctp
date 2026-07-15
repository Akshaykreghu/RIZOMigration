  <form class="form-horizontal" id="lpgForm" action="<?php echo $this->webroot; ?>LeavePolicy/saveleavepolicygroup" method="post" action="">
      <div class="modal-body">
     
<fieldset>

<!-- Form Name -->
<legend>Leave Policy Group</legend>

<!-- Text input-->
 <input id="LEAVEPOLICY_GROUP_ID" name="LEAVEPOLICY_GROUP_ID" value="<?php echo $data["LEAVEPOLICY_GROUP_ID"]; ?>" type="hidden" >
 
<div class="form-group">
  <label class="col-md-4 control-label" for="bank_name">Leave Policy Group Name</label>  
  <div class="col-md-5">
  <input id="LEAVEPOLICY_GROUP_NAME" name="LEAVEPOLICY_GROUP_NAME" value="<?php echo $data["LEAVEPOLICY_GROUP_NAME"]; ?>" type="text" placeholder="Leave Policy Group Name" class="form-control input-md" required="">
    
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
$(document).ready(function() { 
  $('#lpgForm').parsley();
    var options = { 
  success:       function(responseText, statusText, xhr, $form){
	closeModal('leavepolicygrouptable');
}
    }; 
 
    // bind to the form's submit event 
    $('#lpgForm').submit(function() { 
        // inside event callbacks 'this' is the DOM element so we first 
        // wrap it in a jQuery object and then invoke ajaxSubmit 
        $(this).ajaxSubmit(options); 
 
        // !!! Important !!! 
        // always return false to prevent standard browser submit and page navigation 
        return false; 
    });
    }); 
</script>