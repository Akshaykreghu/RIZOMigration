<script type="text/javascript">
    $(document).ready(function () {
        /*
         * Tax Head save
         */
        $('#history').parsley();
        var options = {
            success: function (responseText, statusText, xhr, $form) {
//            alert("success");
                $.notify("Work History Saved Successfully", {
                    type: 'success',
                    allow_dismiss: false
                });
                $('#modalDetailForm').modal('hide');
                $("#example").DataTable().ajax.url(livesite + "Employee/listhistory/" + $('#empsetuppersonal #emp_pkey').val()).load();
            }
        };

        // bind to the form's submit event
        $('#history').submit(function () {
            var companyCode = "<?php echo h($company_code); ?>";
            
              $('#history').attr('action', livesite + 'Employee/savehistory');  
        
            if ($('#history #company').val() == '') {
                alert('Please fill personal informations first!');
            } else {
                $('#btn-submith').html('<li class="fa fa-spinner fa-spin"></li> saving...').attr('disabled', 'disabled');
                $(this).ajaxSubmit(options);

            }
            return false;
        });
        //Ends  
    });
</script>
<div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
        <div class="modal-header" style="background: #00659f;color: white">
            <h4 class="modal-title">Add Your history Details</h4>  
        </div>
        <!--<legend>Add Your history Details</legend>-->
        <form class="form-horizontal" method="post" action="Employee/savehistory" id="history">
            <div class="modal-body">
                <input id="emp_pkey" name="emp_pkey" type="hidden"  value="" >
                <input id="emp_pkey" name="emp_fkey" type="hidden"  value="<?php echo $emp_pkey; ?>" >
                <div class="form-group">
                    <div class="col-md-12">
                        <label for="company" class="col-sm-4 control-label">Company Name<span class="star">*</span></label>
                        <div class="col-sm-1">:</div>
                        <div class="col-sm-7">
                            <input type="text" required="required" class="form-control" value="" name="company" id="company" >
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-12">
                        <label for="department" class="col-sm-4 control-label">Department<span class="star">*</span></label>
                        <div class="col-sm-1">:</div>
                        <div class="col-sm-7">
                            <input type="text" required="required" class="form-control" value="" name="department" id="department" >
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-12">
                        <label for="designation" class="col-sm-4 control-label">Designation<span class="star">*</span></label>
                        <div class="col-sm-1">:</div>
                        <div class="col-sm-7">
                            <input type="text" required="required" class="form-control" value="" name="designation" id="designation" >
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-12">
                        <label for="from_date" class="col-sm-4 control-label">From Date<span class="star">*</span></label>
                        <div class="col-sm-1">:</div>
                        <div class="col-sm-7">
                            <input type="text" required="required" class="form-control" value="" name="from_date" id="from_date" >
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-12">
                        <label for="to_date" class="col-sm-4 control-label">To Date<span class="star">*</span></label>
                        <div class="col-sm-1">:</div>
                        <div class="col-sm-7">
                            <input type="text" required="required" class="form-control" value="" name="to_date" id="to_date" >
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-12">
                        <label for="salary" class="col-sm-4 control-label">Salary<span class="star">*</span></label>
                        <div class="col-sm-1">:</div>
                        <div class="col-sm-7">
                            <input type="text" required="required" class="form-control" value="" name="salary" id="salary" >
                        </div>
                    </div>
                </div>

            </div>           
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" onclick="$('#modalDetailForm').modal('hide');">Cancel</button>
                <button type="submit" id="btn-submith" class="btn btn-primary">Save</button>
            </div>

        </form>
    </div>
</div>
<script>
    $(document).ready(function () {
        $('#from_date').datepicker({format: 'yyyy-mm-dd', autoclose: true})
        $('#to_date').datepicker({format: 'yyyy-mm-dd', autoclose: true})
    });
</script>