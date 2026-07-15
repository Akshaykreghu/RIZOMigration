  <style>
 	.datepicker{z-index:1151 !important;}
 </style>
 <form class="form-horizontal" id="finForm" action="<?php echo $this->webroot; ?>EmployeeConfig/savenoticeperiod" method="post" >
     <div class="modal-body">

         <fieldset>

             <!-- Form Name -->
             <legend>Notice Days</legend>

             <!-- Text input-->

             <div class="form-group">
                 <label class="col-md-4 control-label" for="Days">Days</label>  
                 <div class="col-md-5">

                     <div class="input-group date">

                         <input id="start_month" name="Days"  value="" type="text" placeholder="Days" class="form-control input-md" required="">
                         
                     </div>

                 </div>
             </div>

             <!-- Text input-->
             <div class="form-group">
                 <label class="col-md-4 control-label" for="Description">Description</label>  
                 <div class="col-md-5">


                     <div class="input-group date">

                         <input id="end_month" name="Description"  value="" type="text" placeholder="Description" class="form-control input-md" required="">
                         
                     </div>
                </div>
             </div>


         </fieldset>

     </div>
     <div class="modal-footer">
         <button type="button" class="btn btn-default" onclick="closediv();">Close</button>
         <button type="submit" class="btn btn-primary">Save</button>
     </div>
 </form>
<script type="text/javascript">
$(document).ready(function() { 
    var options = { 
 	 success:       function(responseText, statusText, xhr, $form){
	
        var response =  $.parseJSON(responseText);
        if(response.status == 1){
            $.notify(response.msg,{
                            type: response.type,
                            allow_dismiss: true

                    });
                    closediv();
        reloadtablenoticeperiod();
        }
        else{
            $.notify("Notice Period Saving error",{
                            type: 'warning',
                            allow_dismiss: true

                    });
                    closediv();
        reloadtablenoticeperiod();
        }
}
    }; 
 
    // bind to the form's submit event 
    $('#finForm').submit(function() { 
        // inside event callbacks 'this' is the DOM element so we first 
        // wrap it in a jQuery object and then invoke ajaxSubmit 
        $(this).ajaxSubmit(options); 
 
        // !!! Important !!! 
        // always return false to prevent standard browser submit and page navigation 
        return false; 
    });
    }); 
  
</script>