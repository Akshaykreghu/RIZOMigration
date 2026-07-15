<script type="text/javascript">
    $(document).ready(function () {
        /*
         * Tax Head save
         */
        $('#qualifi').parsley();
        var options = {
            success: function (responseText, statusText, xhr, $form) {
                $.notify("Qualification Saved Successfully", {
                    type: 'success',
                    allow_dismiss: false
                });
                $('#modalDetailForm').modal('hide');
                //$("#example1").DataTable().ajax.reload();
                $("#example1").DataTable().ajax.url(livesite + "Employee/listqualifications/" + $('#empsetuppersonal #emp_pkey').val()).load();
            }
        };

        // bind to the form's submit event
        $('#qualifi').submit(function () {
            var companyCode = "<?php echo h($company_code); ?>";
            
                $('#qualifi').attr('action', livesite + 'Employee/savequalifications');
            
            if ($('#qualifi #course').val() == '') {
                alert('Please fill personal informations first!');
            } else {
                $('#btn-submitquali').html('<li class="fa fa-spinner fa-spin"></li> saving...').attr('disabled', 'disabled');
                $(this).ajaxSubmit(options);
            }
            return false;
        });
        //Ends  
    });
</script>
<style>
    .form-horizontal .control-label {
        text-align: left;
        /*padding-left: 76px;*/
    }
</style>
<div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
        <div class="modal-header" style="background: #00659f;color: white">
            <h4 class="modal-title">Add Your Qualification Details</h4>  
        </div>
        <!--<legend>Add Your Qualification Details</legend>-->
        <form class="form-horizontal" method="post" id="qualifi">
            <div class="modal-body">
                <input id="emp_pkey" name="emp_pkey" type="hidden"  value="" >
                <input id="emp_pkey" name="emp_fkey" type="hidden"  value="<?php echo $emp_pkey; ?>" >
                <div class="form-group">
                    <div class="col-md-12">
                        <label for="course" class="col-sm-4 control-label">Course<span class="star">*</span></label>
                        <div class="col-sm-1">:</div>
                        <div class="col-sm-7">
                            <input type="text" required="required" class="form-control" value="" name="course" id="course" >
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-12">
                        <label for="university" class="col-sm-4 control-label">University/College Name<span class="star">*</span></label>
                        <div class="col-sm-1">:</div>
                        <div class="col-sm-7">
                            <input type="text" required="required" class="form-control" value="" name="university" id="university" >
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-12">
                        <label for="duration" class="col-sm-4 control-label">Duration<span class="star">*</span></label>
                        <div class="col-sm-1">:</div>
                        <div class="col-sm-7">
                            <input type="text" required="required" class="form-control" value="" name="duration" id="duration" >
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-12">
                        <label for="mark" class="col-sm-4 control-label">Gained mark<span class="star">*</span></label>
                        <div class="col-sm-1">:</div>
                        <div class="col-sm-7">
                            <input type="text" required="required" class="form-control" value="" name="mark" id="mark" >
                        </div>
                    </div>
                </div>

            </div>           
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" onclick="$('#modalDetailForm').modal('hide');">Cancel</button>
                <button type="submit" id="btn-submitquali" class="btn btn-primary">Save</button>
            </div>

        </form>
    </div>
</div>

<script>
    $(document).ready(function () {
//        $('#duration').datepicker({format: 'yyyy-mm-dd',autoclose: true})
    });
</script>    