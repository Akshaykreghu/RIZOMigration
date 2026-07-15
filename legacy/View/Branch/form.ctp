<form class="form-vertical" method="post" action="<?php echo $this->webroot; ?>Branch/savebranch" id="bankForm">
    <div class="modal-body">
        <fieldset>

            <!-- Form Name -->
            <legend>Branch</legend>
            <input id="id" name="id" type="hidden"  value="<?php echo $data["id"]; ?>" >
            <!-- Text input-->
            <div class="form-group">
                <div class="col-md-12">
                    <label class="col-md-2 control-label" for="branch_name">Branch Name<span class="star">*</span></label>
                    <div class="col-md-1">:</div>
                    <div class="col-md-9">
                        <input id="branch_name" name="branch_name" value="<?php echo $data['branch_name']; ?>" type="text" placeholder="Branch Name" class="form-control input-md" required="">
                    </div>
                </div>
            </div>
            <br>
            <br>
            <!-- Textarea -->
            <div class="form-group">
                <div class="col-md-12">
                    <label class="col-md-2 control-label" for="address">Address</label>
                    <div class="col-md-1">:</div>
                    <div class="col-md-3">
                        <textarea class="form-control" id="address" name="address"> <?php echo $data["address"]; ?></textarea>
                    </div>
                    <label class="col-md-2 control-label" for="city">City<span class="star">*</span></label>  
                    <div class="col-md-1">:</div>
                    <div class="col-md-3">
                        <input id="city" name="city" type="text" placeholder="City"  value="<?php echo $data['city']; ?>" class="form-control input-md" required="">

                    </div>
                </div>
            </div>

            <br>
            <br>
            <div class="form-group">
                <div class="col-md-12">
                    <label class="col-md-2 control-label" for="state">State<span class="star">*</span></label>  
                    <div class="col-md-1">:</div>
                    <div class="col-md-3">
                        <input id="state" name="state" type="text" placeholder="State"  value="<?php echo $data['state']; ?>" class="form-control input-md" required="">

                    </div>
                    <label class="col-md-2 control-label" for="pincode">Pin Code<span class="star">*</span></label>  
                    <div class="col-md-1">:</div>
                    <div class="col-md-3">
                        <input id="pincodee" name="pincode" type="number" placeholder="Pincode"  value="<?php echo $data['pincode']; ?>" class="form-control input-md" required="">
                    </div>
                </div>
            </div>
            <br>
            <br>
            <div class="form-group">
                <div class="col-md-12">
                    <label class="col-md-2 control-label" for="state">Latitude<span class="star">*</span></label>  
                    <div class="col-md-1">:</div>
                    <div class="col-md-3">
                        <input id="lat" name="lat" type="number" step="any" placeholder="Latitude"  value="<?php echo $data['latitude']; ?>" class="form-control input-md" >

                    </div>
                    <label class="col-md-2 control-label" for="pincode">Longitude<span class="star">*</span></label>  
                    <div class="col-md-1">:</div>
                    <div class="col-md-3">
                        <input id="longt" name="longt" type="number" step="any" placeholder="Longtitude"  value="<?php echo $data['longitude']; ?>" class="form-control input-md" >
                    </div>
                </div>
            </div><br>
            <legend class="col-md-12 control-label">Leave Year</legend>
            <div class="form-group">
                <div class="col-md-12">
                    <input type="hidden" value="<?php echo isset($data['leave_year']['Fin_year_seq']) ? $data['leave_year']['Fin_year_seq'] : ''; ?>" id="leave_sequence_fkey" name="Fin_year_seq_leave" >
                    <label class="col-md-2 control-label">Start Date<span class="star">*</span></label>
                    <div class="col-md-1">:</div>
                    <div class="col-md-3">
                        <input id="leavestartdate" name="leavestartdate" value="<?php echo isset($data['leave_year']['start_month']) ? $data['leave_year']['start_month'] : ''; ?>" type="text"  class="col-md-3 control-label dateget form-control input-md" required="" placeholder="Select date">
                    </div>
                    <label class="col-md-2 control-label">End Date<span class="star">*</span></label>
                    <div class="col-md-1">:</div>
                    <div class="col-md-3">
                        <input id="leaveenddate" name="leaveenddate" value="<?php echo isset($data['leave_year']['start_month']) ? $data['leave_year']['end_month'] : ''; ?>" type="text"  class="col-md-3 control-label dateget form-control input-md" required="" placeholder="Select date">
                    </div>
                </div>

            </div><br>
            <legend class="col-md-12 control-label">Financial Year</legend>
            <div class="form-group">
                <div class="col-md-12">
                    <label class="col-md-2 control-label">Start Date<span class="star">*</span></label>
                    <div class="col-md-1">:</div>
                    <div class="col-md-3">
                        <input type="hidden" value="<?php echo isset($data['fin_year']['Fin_year_seq']) ? $data['fin_year']['Fin_year_seq'] : ''; ?>" id="fin_sequence_fkey" name="Fin_year_seq" >
                        <input id="finstartdate" name="finstartdate" value="<?php echo isset($data['fin_year']['start_month']) ? $data['fin_year']['start_month'] : ''; ?>" type="text"  class="col-md-3 control-label dateget form-control input-md" required="" placeholder="Select date">
                    </div>
                    <label class="col-md-2 control-label">End Date<span class="star">*</span></label>
                    <div class="col-md-1">:</div>
                    <div class="col-md-3">
                        <input id="finenddate" name="finenddate" value="<?php echo isset($data['fin_year']['start_month']) ? $data['fin_year']['end_month'] : ''; ?>" type="text"  class="col-md-3 control-label dateget form-control input-md" required="" placeholder="Select date">
                    </div>
                </div>
            </div>

            <!-- Text input-->

        </fieldset>

    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
        <button id="submit" type="submit" class="btn btn-primary">Save</button>
    </div>

</form>

<script type="text/javascript">

    $('#branch_name').on('change', function () {
        checkIfBranchExists();
    })

    function checkIfBranchExists(callback) {
        var branch_name = $('#branch_name').val();
        var id = $('#id').val();
        $.ajax({
            url: livesite + 'Branch/checkbranchexists/' + id,
            type: 'POST',
            data: {
                branch_name: branch_name
            },
            success: function (resp)
            {
                if (resp > 0) {
                    alert("Branch Already Exists!!");
                    $('#branch_name').val('');
                } else {
                    if (typeof callback === 'function') {
                        callback.call();
                    }
                }
            }
        });
    }





    $(document).ready(function () {
        $('.dateget').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true
        })
        $(".dateget").inputmask("yyyy-mm-dd")
        $("#pincode").inputmask("999");
        $('#bankForm').parsley();
        var options = {
            success: function (responseText, statusText, xhr, $form) {
//                closeModal('brtable');
                $('#largeModalForm').modal('hide');
                reloadTable('brtable');
                $.notify("Branch Saved Successfully ", {
                    type: 'success',
                    allow_dismiss: false
                });
            }
        };

        // bind to the form's submit event 
        $('#bankForm').submit(function () {
            $('#submit').html('<li class="fa fa-spin fa-spinner"></li> Saving ');
            $(this).ajaxSubmit(options);


            return false;
        });
    });
</script>