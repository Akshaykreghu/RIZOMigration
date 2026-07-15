<form class="form-horizontal" method="post" action="<?php echo $this->webroot; ?>DbConfig/savecompletess" id="deptForm">
    <div class="modal-body">

        <fieldset>

            <!-- Form Name -->
            <legend>Edit User Access</legend>
            <!-- Text input-->
            <div class="form-group">
                <label class="col-md-4 control-label" for="Active">Password Reset </label>   
                <div class="col-md-5">
                    <input id="dept_code" name="pasword"  value="" autocomplete="off" type="text" placeholder="Enter New Password" class="form-control input-md" >

                </div>
            </div>
            <div class="form-group">
                <label  class="col-md-4 control-label" for="punch_type">Punch Type:</label>
                <div class="col-md-5">
                    <select id="punch_type" class="form-control"  name="punch_type" required="required" >
                        <option value="M" >Mobile from anywhere</option>
                        <option value="W" >Machine</option>
                        <option value="O" >Mobile from office only</option>
                        <option value="S" >Web</option>
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
    $('#deptForm').parsley();
    var options = { 
    success:       function(responseText, statusText, xhr, $form){
        $('#btn-primary').html('<li class="fa fa-spinner fa-spin"></li>Save ').prop("disabled", false);
        var response = JSON.parse(responseText);
       // alert(response.success);
            if (response.success) {
            closeModal('dpttable');}
}
    }; 
 
    // bind to the form's submit event 
    $('#deptForm').submit(function() { 
        // inside event callbacks 'this' is the DOM element so we first 
        // wrap it in a jQuery object and then invoke ajaxSubmit 
        $('#btn-primary').html('<li class="fa fa-spinner fa-spin"></li>Registering Login Details ... Please Wait ... ').prop("disabled", true);
        $(this).ajaxSubmit(options); 
 
        // !!! Important !!! 
        // always return false to prevent standard browser submit and page navigation 
        return false; 
    });
</script>