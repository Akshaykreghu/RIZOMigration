<form class="form-horizontal" method="post" action="<?php echo $this->webroot; ?>Empattendance/submitregisterentry" id="updateregisterentryform">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Update Register</h4>
    </div>
    <div class="modal-body">
        <input type="hidden" id="hid-registerid" name="hid-registerid" value="<?php echo $registerid; ?>" />
        <div class="form-group">
        <?php $i = 0; foreach($arr_dates as $fieldno => $date){ ?>
            <!--div class="col-md-6">
                <label style="text-align:left;" class="col-md-6 control-label" for="reg-date-<?php echo $i; ?>"><?php echo date('Y-m-d D',  strtotime($date)); ?></label>
                <input type="hidden" id="hid-reg-field-<?php echo $i; ?>" name="hid-reg-field-<?php echo $i; ?>" value="<?php echo $fieldno; ?>" />
                <div class="col-md-6">
                    <select id="reg-date-<?php echo $i; ?>" name="reg-date-<?php echo $i; ?>" class="form-control">
                        <option value="">--No Change--</option>
                        <option value="LOP">Set As LOP</option>
                    </select>
                </div>
            </div-->
			<!-- By santhosh on 27 Dec 2015 -->
			<?php $day = date('j',strtotime($date)); ?>
			<div class="col-md-6">
                <label style="text-align:left;" class="col-md-6 control-label" for="reg-date-<?php echo $i; ?>"><?php echo date('Y-m-d D',  strtotime($date)); ?></label>
                <input type="hidden" id="hid-reg-field-<?php echo $i; ?>" name="hid-reg-field-<?php echo $i; ?>" value="<?php echo $day; ?>" />
                <div class="col-md-6">
                    <select id="reg-date-<?php echo $i; ?>" name="reg-date-<?php echo $i; ?>" class="form-control">
                        <option value="">--No Change--</option>
                        <option value="LOP">Set As LOP</option>
                        <option value="COFF">Comp Off </option>
                        <option value="WFH">Work From Home</option>
                        <option value="NA">Not Applicable</option>
                    </select>
                </div>
            </div>
        <?php $i++; } ?>
        </div>
        <input type="hidden" id="hid-count-missing" name="hid-count-missing" value="<?php echo $i; ?>" />
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Save & Verify</button>
    </div>
</form>
<script>
    $('#updateregisterentryform').parsley();
    var options = {
        success : function(responseText, statusText, xhr, $form) {
            var response = JSON.parse(responseText);
            if (response.success == 1) {
                //alert('Attendance verified successfully');
                $.notify("Attendance verified successfully",{
                    type: 'success',
                    allow_dismiss: false
                });
            } else if(response.success == 2){
                //alert('Some attendance verification failed');
                $.notify("Some attendance verification failed",{
                    type: 'danger',
                    allow_dismiss: false
                });
            }else {
                //alert('Something wrong happened!');
                $.notify("Something wrong happened!",{
                    type: 'danger',
                    allow_dismiss: false
                });
            }
            
            $('#modalForm').modal('hide');
            
            var branch = $('#attendanceregisterfilter #filterby_branch').val();
            var employee = $('#filterby_employee').val();
            var month = $('#attendanceregisterfilter #filterby_month').val();

            $('#attendanceregistertable').datagrid('load', {
                branch: branch,
                employee: employee,
                month: month
            });
            $('#verifiedattendanceregistertable').datagrid('load', {
                branch: branch,
                employee: employee,
                month: month
            });
        }
    };

    // bind to the form's submit event
    $('#updateregisterentryform').submit(function() {
        $(this).ajaxSubmit(options);
        return false;
    });
</script>