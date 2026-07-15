<script type="text/javascript">
$(document).ready(function(){
    /*
     * Tax Head save
     */
    $('#familys').parsley();
    var options = {
        success : function(responseText, statusText, xhr, $form) {
            alert("success");
            $('#modalDetailForm').modal('hide');
            
            $("#example_passport").DataTable().ajax.url( livesite + "Employee/listpassports/" + $('#emp_pkey').val() ).load();
        }
    };
$('#dob').datepicker({
     format: 'yyyy-mm-dd',
     autoclose: true
    
    })
    $('#valid_till').datepicker({
     format: 'yyyy-mm-dd',
     autoclose: true
    
    })
    // bind to the form's submit event
    $('#familys').submit(function() {
	$('#familys').attr('action',livesite+'Employee/savepassport');
        
            $('#btn-submitfami').html('<li class="fa fa-spinner fa-spin"></li> saving...').attr('disabled','disabled');
            $(this).ajaxSubmit(options);
        
        return false;
    });
    //Ends  
});
</script>
<div class="modal-body">
<!-- Form Name -->
<legend>Add Your Passport Details</legend>
<form class="form-horizontal" method="post" id="familys">
    <div class="modal-body">
        <input id="emp_pkey" name="emp_pkey" type="hidden"  value="" >
        <input id="emp_pkey" name="emp_fkey" type="hidden"  value="<?php echo $emp_pkey ; ?>" >
        <div class="form-group">
                         <label for="in_date" class="col-sm-4 control-label">Name<span class="star">*</span></label>
                         <div class="col-sm-8">
                          <input type="text" required="required" class="form-control" value="" name="name" id="name" >
                         </div>
                      </div>
        <div class="form-group">
                         <label for="in_date" class="col-sm-4 control-label">Document Type<span class="star">*</span></label>
                         <div class="col-sm-8">
                          <select required="required" class="form-control" value="" name="document_type" id="document_type">
                                 <option value="Passport">Passport</option>
                                 <option value="visa">Visa</option>
                                 <option value="Labour Card">Labour card</option>
                             </select>
                         </div>
                      </div>
        <div class="form-group">
                         <label for="in_date" class="col-sm-4 control-label">Document number<span class="star">*</span></label>
                         <div class="col-sm-8">
                             <input type="text" required="required" class="form-control" value="" name="document_number" id="document_number" >
                         </div>
                      </div>
        <div class="form-group">
                         <label for="in_date" class="col-sm-4 control-label">Relation<span class="star">*</span></label>
                         <div class="col-sm-8">
                             <select required="required" class="form-control" value="" name="relation" id="relation">
                                 <option value="Self">Self</option>
                                 <option value="Mother">Mother</option>
                                 <option value="Father">Father</option>
                                 <option value="Sister">Sister</option>
                                 <option value="Brother">Brother</option>
                                 <option value="Cousin">Cousin</option>
                                 <option value="Other">Other</option>
                             </select>
                         </div>
                      </div>
        <div class="form-group">
                         <label for="in_date" class="col-sm-4 control-label">Valid From<span class="star">*</span></label>
                         <div class="col-sm-8">
                          <input type="text" required="required" class="form-control" value="" name="valid_from" id="dob" >
                         </div>
                      </div>
        <div class="form-group">
                         <label for="in_date" class="col-sm-4 control-label">Valid To<span class="star">*</span></label>
                         <div class="col-sm-8">
                          <input type="text" required="required" class="form-control" value="" name="valid_till" id="valid_till" >
                         </div>
                      </div>
        <div class="form-group">
                         <label for="in_date" class="col-sm-4 control-label">Gender<span class="star">*</span></label>
                         <div class="col-sm-8">
                             <select required="required" class="form-control" name="classification" id="gender">
                                 <option value="Male">Male</option>
                                 <option value="Female">Female</option>
                             </select>
                         </div>
                      </div>
    <div class="form-group">
                         <label for="in_date" class="col-sm-4 control-label">Nationality<span class="star">*</span></label>
                         <div class="col-sm-8">
                          <input type="text" required="required" class="form-control" value="" name="nationality" id="nationality" >
                         </div>
                      </div>
    </div>           
    <div class="modal-footer">
        <button type="button" class="btn btn-default" onclick="$('#modalDetailForm').modal('hide');">Cancel</button>
        <button type="submit" id="btn-submitfami" class="btn btn-primary">Save</button>
    </div>

</form>
</div>

