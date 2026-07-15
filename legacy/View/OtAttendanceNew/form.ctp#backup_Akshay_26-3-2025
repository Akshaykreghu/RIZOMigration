<style>
    .form-group {
        padding-top: 23px;
    }
</style>

<div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
        <div class="modal-header" style="background: #00659f;color: white">
            <h4 class="modal-title">Edit Overtime</h4>
        </div>

        <div class="modal-body">


            <form id="OtAttendanceNew" action="<?php echo $this->webroot; ?>OtAttendanceNew/save" method="POST">
                <input type="hidden" value="<?php echo $month; ?>" id="month" name="month" />
                <input type="hidden" value="<?php echo $emp; ?>" id="emp" name="emp" />
                <div class="form-group" style="line-height: 2">
                    <label for="in_amt" class="col-sm-4 control-label">Duration In Hrs</label>
                    <div class="col-sm-1">:</div>
                    <div class="col-sm-7">
                        <label for="in_amt" id="hours" class="col-sm-4 control-label" style="padding-left: 6px;"><?php echo round(($duration / 60), 2); ?></label>
                    </div>
                </div>
                <div class="form-group" style="line-height: 2">
                    <label for="duration" class="col-sm-4 control-label">Duration In Mins<span class="star">*</span></label>
                    <div class="col-sm-1">:</div>
                    <div class="col-sm-7">
                        <input type="text" required="required" class="form-control" value="<?php echo $duration; ?>" name="duration" id="duration" onchange="roundtohour(this);">
                    </div>
                </div>
                <div class="form-group" style="line-height: 2">
                    <label for="remarks" class="col-sm-4 control-label">Remarks</label>
                    <div class="col-sm-1">:</div>
                    <div class="col-sm-7">
                        <textarea name="remarks" class="form-control" id="remarks"></textarea>
                    </div>
                </div>

                <div class="modal-footer" style="padding-top: 40px;">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                    <!--edited by sinsiya on 12-07-2024-->
                    <button type="submit" class="btn btn-primary" id="saveButton">Save</button>
                    <button type="submit" class="btn btn-primary">Approve</button>
                </div>
            </form>
        </div>
    </div>

</div>
<script type="text/javascript">
    function roundtohour(duration) {
        var duration = $('#duration').val() / 60;
        var hours = Math.round(duration * 100) / 100;
        document.getElementById("hours").innerHTML = hours;
    }
    $('#saveButton').click(function(event) {
        event.preventDefault();
        setsave();
    });

    function setsave() {
        // alert('hi');
        var month = $('#month').val(); // Get month value from hidden input
        var emp = $('#emp').val(); // Get emp value from hidden input
        var duration = $('#duration').val(); // Get duration value from input field
        var remarks = $('#remarks').val(); // Get remarks value from textarea
        var empPkey = $('#emp_fkey').val() || 0;
        var branch = $('#filterby_branch').val() || '0';
        $.ajax({
            type: 'POST',
            url: livesite + 'OtAttendanceNew/savedata',
            data: {
                month: month,
                emp: emp,
                duration: duration,
                remarks: remarks
            },
            success: function(data) {
                console.log(data);
                if (data) {

                    $('#modalForm').modal('hide');
                    $('#load').load(livesite + 'OtAttendanceNew/Approved/' + month + '/' + empPkey + '/' + 1 + '/' + branch, function() {
                        $('#loader').hide();
                        console.log('data-pws-tab="tab5"');
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                // Handle error scenarios if needed
            }
        });
    }


    $(document).ready(function() {
        //$("#pincode").inputmask("999");
        var month = $('#month').val();
        var emp = $('#emp').val();
        //Edited by Akshay on 12-8-2024
        var empPkey = $('#emp_fkey').val() || 0;
        var branch = $('#filterby_branch').val() || '0';
        //End
        $('#OtAttendanceNew').parsley();
        var options = {
            success: function(responseText, statusText, xhr, $form) {
                closeModal('att_table');
                $.notify("Attendance Approved Successfully", { //Edited by Akshay on 30-7-2024
                    type: 'success',
                    allow_dismiss: false
                });
                $('#load').load(livesite + 'OtAttendanceNew/Approved/' + month + '/' + empPkey + '/' + 1 + '/' + branch, function() {
                    $('#loader').hide();
                    console.log('data-pws-tab="tab7"');
                });
            }
        };

        // bind to the form's submit event 
        // $('#OtAttendanceNew').submit(function() {
        //     var month = $('#month').val(); // Get month value from hidden input
        //     var formattedMonth = month.substring(0, 7);
        //     var emp = $('#emp').val(); // Get emp value from hidden input
        //     $.ajax({
        //         url: livesite + 'OtAttendanceNew/counting/' + formattedMonth + '/' + emp,
        //         success: function(response) {
        //             var response = $.parseJSON(response);
        //             row = response.count;
        //             if (row > 0) {
        //                 alert("Attendance for the month is not verified. Verify attendance before OT approval");
        //                 return false;

        //             }
        //         }
        //     });


        //     $(this).ajaxSubmit(options);
        //     return false;
        // });

        $('#OtAttendanceNew').submit(function(event) {
            event.preventDefault(); // Prevent form submission

            var month = $('#month').val(); // Get month value from hidden input
            var formattedMonth = month.substring(0, 7);
            var emp = $('#emp').val(); // Get emp value from hidden input

            $.ajax({
                url: livesite + 'OtAttendanceNew/counting/' + formattedMonth + '/' + emp,
                success: function(response) {
                    var response = $.parseJSON(response);
                    var row = response.count;

                    if (row > 0) {
                        alert("Attendance for the month is not verified. Verify attendance before OT approval");
                    } else {
                        $('#OtAttendanceNew').ajaxSubmit({
                            success: function(responseText, statusText, xhr, $form) {
                                closeModal('att_table');
                                $.notify("Attendance Approved Successfully", {
                                    type: 'success',
                                    allow_dismiss: false
                                });
                                var empPkey = $('#emp_fkey').val() || 0;
                                var branch = $('#filterby_branch').val() || '0';
                                $('#load').load(livesite + 'OtAttendanceNew/Approved/' + month + '/' + empPkey + '/' + 1 + '/' + branch, function() {
                                    $('#loader').hide();
                                    console.log('data-pws-tab="tab7"');
                                });
                            }
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    // Handle error scenarios if needed
                }
            });

            return false; // Prevent form from submitting the default way
        });


    });
</script>