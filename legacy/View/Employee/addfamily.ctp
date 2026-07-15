<script type="text/javascript">
    $(document).ready(function () {
        /*
         * Tax Head save
         */
        //$("#relation").select2();
       // $("#gender").select2();
        $('#familys').parsley();
        var options = {
            success: function (responseText, statusText, xhr, $form) {
                $.notify("Family Details Saved Successfully", {
                    type: 'success',
                    allow_dismiss: false
                });
                $('#modalDetailForm').modal('hide');
                $("#example_family").DataTable().ajax.url(livesite + "Employee/lstfamilies/" + $('#emp_pkey').val()).load();

            }
        };
        //$('#dob').datepicker({
        //    format: 'yyyy-mm-dd',
         //   autoclose: true,
        //}).change(function () {
         //   var start = new Date($(this).val());
         //   var date2 = new Date();
        //    var timeDiff = Math.abs(date2.getTime() - start.getTime());
         //   var diffDays = Math.ceil(timeDiff / (1000 * 3600 * 24));
         //   $('#age').val(Math.ceil(diffDays / 365));
       // });

        // bind to the form's submit event
        $('#familys').submit(function () {
            var companyCode = "<?php echo h($company_code); ?>";
            
           
              $('#familys').attr('action', livesite + 'Employee/savefamily');
            

            $('#btn-submitfami').html('<li class="fa fa-spinner fa-spin"></li> saving...').attr('disabled', 'disabled');
            $(this).ajaxSubmit(options);

            return false;
        });
        //Ends  
    });
</script>
<div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
        <div class="modal-header" style="background: #00659f;color: white">
            <h4 class="modal-title">Add Your Family Details</h4>  
        </div>
        <!--    <legend>Add Your Family Details</legend>-->
        <form class="form-horizontal" method="post" id="familys">
            <div class="modal-body">
               <!-- <input id="emp_pkey" name="emp_pkey" type="hidden"  value="" > -->
                <input id="emp_pkey" name="emp_fkey" type="hidden"  value="<?php echo $emp_pkey; ?>" >
                <div class="form-group">
                    <div class="col-md-12">
                        <label for="name" class="col-sm-4 control-label">Name<span class="star">*</span></label>
                        <div class="col-sm-1">:</div>
                        <div class="col-sm-7">
                            <input type="text" required="required" class="form-control" value="" name="name" id="name" >
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-12">
                        <label for="relation" class="col-sm-4 control-label">Relation<span class="star">*</span></label>
                        <div class="col-sm-1">:</div>
                        <div class="col-sm-7">
                            <select required="required" class="form-control js-example-basic-single" style="width: 100%" value="" name="relation" id="relation">
                                <option value="Self">Self</option>
                                <option value="Mother">Mother</option>
                                <option value="Father">Father</option>
                                <option value="Sister">Sister</option>
                                <option value="Brother">Brother</option>
                                <option value="Cousin">Cousin</option>
                                <option value="Son">Son</option>
                                <option value="Daughter">Daughter</option>
                                <option value="Spouse">Spouse</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-12">
                        <label for="dob" class="col-sm-4 control-label">DOB<span class="star">*</span></label>
                        <div class="col-sm-1">:</div>
                        <div class="col-sm-7">
                            <input type="date" class="form-control" value="" name="DOB" id="dob" onblur="getage()">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-12">
                        <label for="age" class="col-sm-4 control-label">Age<span class="star">*</span></label>
                        <div class="col-sm-1">:</div>
                        <div class="col-sm-7">
                            <input type="text" required="required" class="form-control" value="" name="age" id="age" >
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-12">
                        <label for="blood_group" class="col-sm-4 control-label">Blood Group</label>
                        <div class="col-sm-1">:</div>
                        <div class="col-sm-7">
                            <input type="text" class="form-control" value="" name="blood_group" id="blood_group" >
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-12">
                        <label for="gender" class="col-sm-4 control-label">Gender<span class="star">*</span></label>
                        <div class="col-sm-1">:</div>
                        <div class="col-sm-7">
                            <select required="required" class="form-control js-example-basic-single" style="width: 100%" name="gender" id="gender">
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-12">
                        <label for="nationality" class="col-sm-4 control-label">Nationality<span class="star">*</span></label>
                        <div class="col-sm-1">:</div>
                        <div class="col-sm-7">
                            <input type="text" class="form-control" value="" name="nationality" id="nationality" >
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-12">
                        <label for="contact_number" class="col-sm-4 control-label">Contact Number</label>
                        <div class="col-sm-1">:</div>
                        <div class="col-sm-7">
                            <input type="tel" class="form-control" name="contact_number" id="contact_number" pattern="[6789][0-9]{9}" maxlength="10"/>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-12">
                        <label for="alternate_number" class="col-sm-4 control-label">Alternate Number</label>
                        <div class="col-sm-1">:</div>
                        <div class="col-sm-7">
                            <input type="tel" class="form-control" name="alternate_number" id="alternate_number" pattern="[6789][0-9]{9}" maxlength="10"/>
                        </div>
                    </div>
                </div>
            </div>           
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" onclick="$('#modalDetailForm').modal('hide');">Cancel</button>
                <button type="submit" id="btn-submitfami" class="btn btn-primary">Save</button>
            </div>

        </form>
    </div>
</div>
<script type="text/javascript">
 function getage() {
            var start = new Date($('#dob').val());
            var date2 = new Date();
            var timeDiff = Math.abs(date2.getTime() - start.getTime());
            var diffDays = Math.ceil(timeDiff / (1000 * 3600 * 24));
            $('#age').val(Math.ceil(diffDays / 365));
        }
</script>
