<script type="text/javascript">
    $(document).ready(function() {
        // edited by athira on 31-03-2025
        $('#modalDetailForm').on('hidden.bs.modal', function() {
            $('#largeModalForm').css('overflow', 'auto');
        });
        // end
        /*
         * Tax Head save
         */
        $("#reccuring").select2();
        $("#gender").select2();
        $("#relation").select2();
        $("#document_type").select2();

        $('#familys').parsley();
        var options = {
            success: function(responseText, statusText, xhr, $form) {
                alert("success");
                $('#modalDetailForm').modal('hide');

                $("#example_passport").DataTable().ajax.url(livesite + "Employee/listpassports/" + $('#emp_pkey').val()).load();
            }
        };

        //edited by athira on 27-05-2025


        $('#dob').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true
        }).on('changeDate', function() {
            var validFrom = $('#dob').datepicker('getDate');

            if (validFrom) {
                $('#valid_till').datepicker('setStartDate', validFrom);
                $('#valid_till').prop('disabled', false); // Enable only after selection
            } else {
                $('#valid_till').prop('disabled', true); // Keep disabled if no date is selected
            }
        });

        $('#valid_till').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true
        }).prop('disabled', true); // Initially disabled

        //end

        // bind to the form's submit event
        $('#familys').submit(function() {
            //        if($('#dob').val() > $('#valid_till').val()){
            //            alert('Does Card Expired Before it Taken ? Please Check Expiry date ');
            //            return false;
            //        }
            //added by megha on 7_6_19 warning for document upload
            var file = $('#avatarfile').val();
            if (file) {
                alert('Please Complete the Document Upload process.');
                return false;
            }
            //end warning for document upload
            //alert(file);
             var companyCode = "<?php echo h($company_code); ?>";
        
            $('#familys').attr('action', livesite + 'Employee/savepassport');
         

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
            <h4 class="modal-title">Add Your Document Details</h4>
        </div>
        <br>
        <!--    <legend>Add Your Document Details</legend>-->
        <form class="form-horizontal" method="post" enctype="multipart/form-data" id="familys" style="margin-top: -15px; ">
            <div class="modal-body">
                <input id="emp_pkey" name="emp_pkey" type="hidden" value="">
                <input id="emp_pkey" name="emp_fkey" type="hidden" value="<?php echo $emp_pkey; ?>">
                <div class="form-group">
                    <div class="col-md-12">
                        <label for="name" class="col-sm-3 control-label">Document Name<span class="star">*</span></label>
                        <div class="col-sm-1">:</div>
                        <div class="col-sm-8">
                            <input type="text" required="required" class="form-control" value="" name="name" id="name">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-12">
                        <label for="document_type" class="col-sm-3 control-label">Document Type<span class="star">*</span></label>
                        <div class="col-sm-1">:</div>
                        <div class="col-sm-8">
                            <select required="required" class="form-control js-example-basic-single" style="width: 100%" value="" name="document_type" id="document_type">
                                <option value="Passport">Passport</option>
                                <option value="visa">Visa</option>
                                <option value="Labour Card">Labour Card</option>
                                <option value="Health Card">Health Card</option>
                                <option value="Driving License">Driving License</option>
                                <option value="Insurance">Insurance</option>
                                <option value="ID Card">ID Card</option>
                                <option value="Aadhar Card">Aadhar Card</option>
                                <option value="Others">Others</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-12">
                        <label for="document_number" class="col-sm-3 control-label">Document number<span class="star">*</span></label>
                        <div class="col-sm-1">:</div>
                        <div class="col-sm-8">
                            <input type="text" required="required" class="form-control" value="" name="document_number" id="document_number">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-12">
                        <label for="relation" class="col-sm-3 control-label">Relation<span class="star">*</span></label>
                        <div class="col-sm-1">:</div>
                        <div class="col-sm-8">
                            <select required="required" class="form-control js-example-basic-single" style="width: 100%" value="" name="relation" id="relation">
                                <option value="Self">Self</option>
                                <option value="Mother">Mother</option>
                                <option value="Father">Father</option>
                                <option value="Sister">Sister</option>
                                <option value="Brother">Brother</option>
                                <option value="Cousin">Cousin</option>
                                <option value="Spouse">Spouse</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-12">
                        <label for="in_date" class="col-sm-3 control-label">Valid From<span class="star">*</span></label>
                        <div class="col-sm-1">:</div>
                        <div class="col-sm-8">
                            <input type="text" required="required" class="form-control" value="" name="valid_from" id="dob">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-12">
                        <label for="in_date" class="col-sm-3 control-label">Valid To</label>
                        <div class="col-sm-1">:</div>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" value="" name="valid_till" id="valid_till">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-12">
                        <label for="in_date" class="col-sm-3 control-label">Gender<span class="star">*</span></label>
                        <div class="col-sm-1">:</div>
                        <div class="col-sm-8">
                            <select required="required" class="form-control js-example-basic-single" style="width: 100%" name="classification" id="gender">
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-12">
                        <label for="in_date" class="col-sm-3 control-label">Nationality<span class="star">*</span></label>
                        <div class="col-sm-1">:</div>
                        <div class="col-sm-8">
                            <input type="text" required="required" class="form-control" value="" name="nationality" id="nationality">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-12">
                        <label class="col-sm-3 control-label" for="empctccsv">Choose file</label>
                        <div class="col-sm-1">:</div>
                        <div class="col-sm-8">
                            <!--                                    <button type="button" id="btn-uploademployeectc" class="btn btn-primary" onclick="uploadEmployeeCTC();">Upload Gross Salary</button>-->
                            <input type="file" id="avatarfile" name="avatarfile" />
                            <input type="hidden" name="files" id="files">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-12">
                        <label for="remind" class="col-sm-3 control-label">Remind Before (Days )</label>
                        <div class="col-sm-1">:</div>
                        <div class="col-sm-8 row">
                            <div class="col-sm-4">
                                <input type="number" class="form-control" value="1" name="remind" id="remind" min="0">
                            </div>
                            <label for="reccuring" class="col-sm-2 control-label">Reccuring</label>
                            <div class="col-sm-1">:</div>
                            <div class="col-sm-4">
                                <select required="required" class="form-control js-example-basic-single" style="width: 100%" name="reccuring" id="reccuring">
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="margin-top: -23px; ">
                <button type="button" class="btn btn-danger" onclick="$('#modalDetailForm').modal('hide');">Cancel</button>
                <button type="submit" id="btn-submitfami" class="btn btn-primary">Save</button>
            </div>

        </form>
    </div>
</div>
<script>
    $(document).ready(function() {
        $("#avatarfile").fileinput({
            uploadUrl: livesite + "Employee/savefile/",
            showCaption: true,
            showClose: true,
            //                        allowedFileTypes:['jpg', 'gif', 'png'],
            //			allowedPreviewTypes:['jpg', 'gif', 'png'],
            maxFileCount: 1,
            showRemove: true,
            dropZoneEnabled: false,
        });
        $('#avatarfile').on('fileuploaderror', function(event, data, previewId, index, jqXHR) {
            // do your validation and return an error like below
            //            $('.img-circle').attr("src","second.jpg");
            alert(data.avatar);
            $('.box-profile').load(livesite + 'User/loadImage', function() {
                $('.box-profile').fadeIn('slow');
                //                $('#avatarfile').fileinput('reset');
            });

        });

        $('#avatarfile').on('fileuploaded', function(event, data, previewId, index) {
            var form = data.form,
                files = data.files,
                extra = data.extra,
                response = data.response,
                reader = data.reader;
            console.log(response.data.avatar);
            $('#files').val(response.data.avatar);
            $('#btn-submitfami').html('Save').attr('disabled', false);
            if (response.error == "") {
                alert("Document Uploaded");
            } else {
                alert(response.error);
            }
        });

        $('#avatarfile').on('filepreupload', function(event, data, previewId, index) {
            var form = data.form,
                files = data.files,
                extra = data.extra,
                response = data.response,
                reader = data.reader;

            console.log('File pre upload triggered');
            $('#imgsloader').attr("src", livesite + "img/loaders.gif");
            $('#btn-submitfami').html('<li class="fa fa-spinner fa-spin"></li> Document Uploading...').attr('disabled', 'disabled');
        });
    });
</script>
<!-- edited by megha on 6/6/2019 design issue of avatar upload file button -->
<style>
    .file-caption {
        height: 34px;
        padding: 6px 12px;
    }
</style>
<!-- end -->