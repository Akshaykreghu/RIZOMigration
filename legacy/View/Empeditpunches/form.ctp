<form class="form-horizontal" id="editpunchform" method="post" action="<?php echo $this->webroot; ?>Empeditpunches/savenew" >
     <div class="modal-body">
        <fieldset>
            <!-- Form Name -->
            <legend>Attendance</legend>

            <!-- Text input-->
            <input type="hidden" name="empid" id="empid" value="<?php echo $empid; ?>"  />

            <div class="form-group">
                <label class="col-md-4 control-label" for="LOGDATE">Date <span class="star">*</span></label>  
                <div class="col-md-5">
                    <input id="LOGDATE" name="LOGDATE" placeholder="YYYY-MM-DD"  value="" type="text" class="form-control input-md" required="">
                </div>
            </div>

            <div class="form-group">
                <label class="col-md-4 control-label" for="LOGTIME">Time <span class="star">*</span></label>  
                <div class="col-md-5">
                    <input id="LOGTIME" name="LOGTIME" placeholder="HH:MM" value="" type="text" class="form-control input-md" required="">
                </div>
            </div>

            <!-- Text input-->
            <div class="form-group">
                <label class="col-md-4 control-label" for="C1">Direction <span class="star">*</span></label>  
                <div class="col-md-5">
                    <select name="C1" class="form-control select2-dropdown">
                        <option value="in">In</option>
                        <option value="out">Out</option>
                    </select>
                </div>
            </div>

            <!-- Text input-->
            <div class="form-group">
                <label class="col-md-4 control-label" for="C3">Remarks <span class="star">*</span></label>  
                <div class="col-md-5">
                    <input name="C3"  value="" type="text" placeholder="Remarks" class="form-control input-md" required="">
                    <span> You must enter a valid remarks.</span>
                </div>
            </div>
        </fieldset>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal" onclick="clearForm();">Close</button>
        <button type="submit" class="btn btn-primary">Save</button>
    </div>
</form>
<script type="text/javascript">
    $(document).ready(function () {
        $('#LOGDATE').datepicker({
            format: 'yyyy-mm-dd',
             autoclose: true
        });
        $("#LOGDATE").inputmask("yyyy-mm-dd");
        
        var currentDate = new Date();
        $("#LOGDATE").datepicker("setDate", currentDate);
        
        $('#LOGTIME').timepicker({
            format: 'hh:mm:ss',
            showMeridian:false,
             autoclose: true
        });
        
        $('#editpunchform').parsley();
        var options = { 
            success:function(responseText, statusText, xhr, $form){
                clearForm();
                refreshgrid();
                $.notify("New Attandence Saved Successfully",{
                    type: 'success',
                    allow_dismiss: false
                });
            }
        }; 
 
        // bind to the form's submit event 
        $('#editpunchform').submit(function() { 
            $(this).ajaxSubmit(options);         
            return false;
        });
    });
    function clearForm() {
        $('#empid').val("")
        $('#editpunchform').form('clear');
    }
</script>