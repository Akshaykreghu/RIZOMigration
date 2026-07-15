  <form class="form-horizontal" id="hgForm" action="<?php echo $this->webroot; ?>HolidayCalendar/saveholidaygroup" method="post" action="">
      <div class="modal-body">
     
<fieldset>

<!-- Form Name -->
<legend>Holiday Group</legend>

<!-- Text input-->
 <input id="HOLIDAY_GROUP_ID" name="HOLIDAY_GROUP_ID" value="<?php echo $data["HOLIDAY_GROUP_ID"]; ?>" type="hidden" >
 
<div class="form-group">
  <label class="col-md-4 control-label" for="bank_name">Holiday Group Name</label>  
  <div class="col-md-5">
  <input id="HOLIDAY_GROUP_NAME" name="HOLIDAY_GROUP_NAME" value="<?php echo $data["HOLIDAY_GROUP_NAME"]; ?>" type="text" placeholder="Holiday Group Name" class="form-control input-md" required="">
    
  </div>
</div>


</fieldset>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Save</button>
      </div>
      </form>
<script type="text/javascript">
$(document).ready(function() { 
  $('#hgForm').parsley();
    var options = { 
  success:       function(responseText, statusText, xhr, $form){
	closeModal('hcgrouptable');
}
    }; 
 
    // bind to the form's submit event 
    $('#hgForm').submit(function() { 
        // inside event callbacks 'this' is the DOM element so we first 
        // wrap it in a jQuery object and then invoke ajaxSubmit 
        $(this).ajaxSubmit(options); 
 
        // !!! Important !!! 
        // always return false to prevent standard browser submit and page navigation 
        return false; 
    });
    }); 
</script>