  <form id="holidayForm" action="<?php echo $this->webroot; ?>HolidayCalendar/saveholiday" method="post">
      <div class="modal-body">
      	
    			<div class="form-group">
    					<input id="HOLIDAY_GROUP_ID" name="HOLIDAY_GROUP_ID" type="hidden"  value="<?php echo $data["HOLIDAY_GROUP_ID"]; ?>" >
               
    				<input id="HOLIDAYID" name="HOLIDAYID" type="hidden"  value="<?php echo $data["HOLIDAYID"]; ?>" >
                     	 <label>Holiday Type</label>
                   
						 <select name="HOLIDAYTYPE" class="form-control input-md" required="" >
						 	<option>[--Select--]</option>
				     		<?php foreach ($types as $key => $value) {
								 $selected = "";
								 if($data['HOLIDAYTYPE'] ==$value['id'] ){
								 	 $selected = 'selected="selected"';
								 }
								 ?>
								 
								 <option <?php echo $selected;  ?> value="<?php echo $value['id'];?>"><?php echo $value['label'];?></option>
								 <?php
							 } ?>
				     	 </select>

                    </div>
                    
                     <div class="form-group">
                     	  <label>Holiday Name</label>
                     	  <input id="HOLIDAYNAME" name="HOLIDAYNAME" value="<?php echo $data["HOLIDAYNAME"]; ?>" type="text" placeholder="Holiday Name" class="form-control input-md" required="">
  		
                     </div>
                      <div class="form-group">
                     	  <label>Date</label>
                     	  <input id="HOLIDAYDATE" name="HOLIDAYDATE" value="<?php echo $data["HOLIDAYDATE"]; ?>" type="text"  class="form-control input-md" required="">
  		
                     </div>
                        
                     <div class="form-group">
                     	  <label>Description</label>
                     	  <textarea name="DESCRIPTION" class="form-control input-md" required="" ><?php echo $data["DESCRIPTION"]; ?></textarea> 
                     	  		
                     </div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Save</button>
      </div>
      </form>
<script type="text/javascript">
$(document).ready(function() { 
	$('#HOLIDAYDATE').datepicker({
            format: 'yyyy-mm-dd',
			autoclose: true
	})
	$("#HOLIDAYDATE").inputmask("yyyy-mm-dd")
	
  $('#holidayForm').parsley();
    var options = { 
  success:function(responseText, statusText, xhr, $form){
	closeSmallModalForm('hctable');
}
    }; 
 
    // bind to the form's submit event 
    $('#holidayForm').submit(function() { 
        // inside event callbacks 'this' is the DOM element so we first 
        // wrap it in a jQuery object and then invoke ajaxSubmit 
        $(this).ajaxSubmit(options); 
 
        // !!! Important !!! 
        // always return false to prevent standard browser submit and page navigation 
        return false; 
    });
    }); 
</script>