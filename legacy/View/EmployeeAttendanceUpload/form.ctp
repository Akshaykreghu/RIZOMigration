<style>

    .form-horizontal .control-label{
        text-align: right;


    }
    .modal-content{
        /* new custom width */
        width: 95% !important;
        /* must be half of the width, minus scrollbar on the left (30px) */
    }

    .form-horizontal .control-label {
        text-align: left;
        /*padding-left: 76px;*/
    }
</style>
<div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
        <div class="modal-header" style="background: #00659f;color: white">
            <h4 class="modal-title">Upload Attendance</h4>  
        </div>
        <div class="modal-body">
            <!-- Form starts -->

            <form id="attendanceuploadtable" class="form-horizontal has-validation-callback" action="<?php echo $this->webroot; ?>EmployeeAttendanceUpload/attendancesave" method="POST">
                <div class="modal-body">
                    <!--<h4 class="modal-title">Upload Attendance</h4>-->  
                    <input type="hidden" id="emp_attendance_upload_pkey" name="emp_attendance_upload_pkey" value="<?php echo $data['emp_attendance_upload_pkey']; ?>" >
                    <input type="hidden" id="emp_fkey" name="emp_fkey" value="<?php echo $data['emp_fkey']; ?>" >



                    <!---------------------------------NOT USING CODES START HERE-------------------------------->

                    <!-- <div class="form-group"> 
                       <label for="attendance_type" class="col-sm-4 control-label">Attendance Type<span class="star">*</span></label>   <div class="col-sm-8"> -->
                    <select style="display: none ;"  class="form-control input-md" id="attendance_type" name="attendance_type"  >  
                        <!-- <option value="">[--Select--]</option> -->
                        <option value="1" <?php echo($data['attendance_type'] == '1') ? 'selected="selected"' : ''; ?>>Simple Attendance</option>
                       <!--   <option value="2" <?php echo($data['attendance_type'] == '2') ? 'selected="selected"' : ''; ?>>Time Attendance</option> -->

                    </select>
                    <!--   </div>  -->
                    <!-- <div class="form-group">
                        <label for="in_date" class="col-sm-4 control-label">In Date<span class="star">*</span></label>
                        <div class="col-sm-8">
                            <input type="text" required="required" class="form-control" value="<?php echo $data['in_date'] ?>  " name="in_date" id="in_date" >
                        </div>
                        <h3 id="message" style="color:white; padding: 5px;/*padding:5px 5px 4px 20px;*/ "></h3>
                    </div> -->
                    <!-- <hr> -->

                    <!-----------------NOT USING CODES ENDS HERE-------------------->



                    <!--------THE ALL FORM VIEW RE-EDITED BY ***ARUL P DAS on 29/11/2019***-------->
                    <div class="form-group">
                        <div class="col-md-12" style="text-align: left;">
                            <!-- <div class="col-md-2"></div> -->
                            <label class="col-md-4" for="attendance_type">Employee Name<span class="star">*</span></label>
                            <div class="col-md-1">:</div>
                            <div class="col-md-7" style="padding-left: 30px;">
                                <!-- class="js-example-basic-single " -->
                                     <!-- // edited by bindu 20-12-2025  -->
                                <select style="width: 266px;" id="emp_fkeys" name="emp_fkeys" onchange="filterAttendanceupload(this);">
    <option value="">Select</option>
    <?php
    foreach ($arr_employees as $value) {
        $selected = ($data['emp_fkey'] == $value['EmployeeDetails']['emp_pkey']) ? 'selected="selected"' : '';
        echo '<option value="' . $value['EmployeeDetails']['emp_pkey'] . '" ' . $selected . '>'
            . $value['EmployeeDetails']['first_name'] . ' ' . $value['EmployeeDetails']['last_name']
            . ' - ' . $value['EmployeeProfessionalDetails']['emp_company_id']
            . '</option>';
    }
    ?>
</select>
<!-- // edited by bindu 20-12-2025 end -->
                                <span id="error_item"></span>
                            </div>
                            <!--<div class="col-md-2"></div>-->
                        </div> 
                    </div>
                    <span>&nbsp;</span>
                    <div class="row" style="text-align: left;">
                        <div class="col-md-2"><h3 style="text-align: center;"><b>In</b></h3></div>
                        <div class="col-md-10">
                            <div class="col-12 form-group">
                                <label for="in_date" class="col-sm-3 control-label">Date<span class="star">*</span></label>
                                <div class="col-sm-1" style="margin-left: -17px;">:</div>
                                <div class="col-sm-8" style="margin-left: 17px;">
                                    <input type="text" required="required" class="form-control" value="<?php echo $data['in_date'] ?>  " name="in_date" id="in_date" >
                                    <span id="message"><!-- <h3  style="color:red; background: white; padding: 2px;/*padding:5px 5px 4px 20px;*/ "></h3> --></span>
                                </div>
                            </div>
                            <div class="col-12 form-group">
                                <label for="in_time" class="col-sm-3 control-label" id="lindate">Time<span class="star">*</span></label>
                                <div class="col-sm-1" style="margin-left: -17px;">:</div>
                                <div class="col-sm-8" style="margin-left: 17px;">
                                    <input type="text" placeholder="Please type Hour And Time" class="form-control" value="<?php echo $data['in_time'] ?> " name="in_time" id="in_time" >
                                    <input type="hidden" name="on" id="on">
                                    <input type="hidden" name="shift" id="shift" value="">
                                    <span id="warning_in_time"></span>
                                </div>
                            </div>
                        </div>
                        <!--<div class="col-md-2"></div>-->
                    </div>

                    <!-- <div class="form-group"><hr></div> -->
                    <!-- <hr> -->

                    <div class="row" style="text-align: left;">
                        <div class="col-md-2"><h3 style="text-align: center;"><b>Out</b></h3></div>
                        <div class="col-md-10">
                            <div class="form-group col-12">
                                <label for="out_date" class="col-sm-3 control-label" >Date<span class="star">*</span></label>
                                <div class="col-sm-1" style="margin-left: -17px;">:</div>
                                <div class="col-sm-8" style="margin-left: 17px;">
                                    <input type="text" class="form-control" value="<?php echo $data['out_date'] ?>  " name="out_date" id="out_date" disabled>
                                </div>
                            </div>
                            <div class="form-group col-12">
                                <label for="out_time" class="col-sm-3 control-label" id="outime">Time<span class="star">*</span></label>
                                <div class="col-sm-1" style="margin-left: -17px;">:</div>
                                <div class="col-sm-8" style="margin-left: 17px;">
                                    <input type="text" class="form-control" value="<?php echo $data['out_time'] ?>  " name="out_time" id="out_time" >
                                    <input type="hidden" name="off" id="off">
                                    <span id="warning_out_time"></span>
                                </div>
                            </div> 
                        </div>
                        <!--<div class="col-md-2"></div>-->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary " id="btn_save">Save</button>
                </div>

            </form>
            <!-- Tax Head Detail Form -->





            <!-- form ends-->
        </div>
    </div>

</div>

<script type="text/javascript">
///////////////////////////////////OLD CODE. BACKUP ON 9/12/2019. BEFORE ADDING FH OR SH LEAVE APPLIED., THEN WE CAN ADD ATTENDANCE ON REST HALF.AND ALSO 
//CHECKING THE SHIFT TIME. BY ***ARUL P DAS
    ///THE BELOW BUTTON CLICK FUNCTION IS BY ***ARUL P DAS on 29/11/2019*********
//    $('#btn_save').click(function(){
//        $in=$('#in_time').val();
//        $out=$('#out_time').val();
//        $emp=$('#emp_fkeys').val();
//        if($emp==""){
//            $('#error_item').html('This value is required').css('color','red');
//            return false;
//        }else{
//            $('#error_item').html('');
//        }
//        if($in=='0:00:00'){
//            // alert('Please ')
//            $('#warning_in_time').html('Please enter in time').css("color","red");
//            return false;
//        }else{
//            $('#warning_in_time').html('');
//        }
//        if($out=='0:00:00'){
//            // alert('Please ')
//            $('#warning_out_time').html('Please enter out time').css("color","red");
//            return false;
//        }else{
//            $('#warning_out_time').html('');
//        }
//    });



    $('#btn_save').click(function () {
        $in = $('#in_time').val();
        $out = $('#out_time').val();
        $emp = $('#emp_fkeys').val();
        $on = parseInt($('#on').val());//On dutty means, shift starting time. 9:00 or 9:30 ....**ARUL P DAS on 30/11/2019
        $off = parseInt($('#off').val());//Off dutty means, shift ending time. 17:00 or 17:30....**ARUL P DAS on 30/11/2019
        $shift = $('#shift').val();//Shift means, the leave taken shift. It can be First Hald(FH) or Second Half(SH).**ARUL P DAS on 30/11/2019
        if ($emp == "") {
            $('#error_item').html('This value is required').css('color', 'red');
            return false;
        } else {
            $('#error_item').html('');
        }
        if ($in == '0:00:00') {
            // alert('Please ')
            $('#warning_in_time').html('Please enter in time').css("color", "red");
            return false;
        } else {
            $('#warning_in_time').html('');
        }
        if ($out == '0:00:00') {
            // alert('Please ')
            $('#warning_out_time').html('Please enter out time').css("color", "red");
            return false;
        } else {
            $('#warning_out_time').html('');
        }
        $result1 = $off - $on;//This is differencing off_dutty from on_dutty. eg: 17-9=8
        $result2 = $result1 / 2;//This is calculating the half of the time shift. 8/2=4
        $result3 = $result2 + $on;//This is to add the half time with on_dutty time. 9+4=13. The train time 13 is the half time.
        $in_time = parseInt($in);//This is the entering in time from the form.
        $out_time = parseInt($out);//This is the entering out time from the form.
//        if($shift=="FH"){//First half applied for leave
//            if($in_time<$result3){//In time should > 13. Because the first half is applied for leave.
//                $('#warning_in_time').html('In time Should greater than '+$result3+':00').css("color","red");
//                // alert('In time and Out time Should greater than '+$result3);
//                return false;
//            }else{
//                $('#warning_in_time').html('');
//            }
//
//            if($out_time<$result3){
//                $('#warning_out_time').html('Out time Should greater than '+$result3+':00').css("color","red");
//                // alert('In time and Out time Should greater than '+$result3);
//                return false;
//            }else{
//                $('#warning_out_time').html('');
//            }
//        }
//        if($shift=="SH"){//Second half applied for leave
//            if($in_time>$result3){//In time should < 13. Because the second half is applied for leave.
//                $('#warning_in_time').html('In time Should less than '+$result3+':00').css("color","red");
//                // alert('In time and Out time Should less than '+$result3);
//                return false;
//            }else{
//                $('#warning_in_time').html('');
//            }
//
//            if($in_time>$result3 || $out_time>$result3){
//                $('#warning_out_time').html('Out time Should less than '+$result3+':00').css("color","red");
//                // alert('In time and Out time Should less than '+$result3);
//                return false;
//            }else{
//                $('#warning_out_time').html('');
//            }
//        }
        // return false;
    });
    $('#emp_fkeys').on("change", function () {
        if ($(this).val() != "" || $(this).val() != null) {
            // $('#error_item').css({"border-color": "white","border-width":"0px","border-style":"solid","margin-left":"0px","right":"0px"})
            $('#error_item').html('');
            $('#message').html('');
        }
    })
    $('#in_date').on('change', function () {
        var ins = $('#in_date').val();
        var out = $('#out_date').val();
        var inTime = $('#in_time').val();
        var outTime = $('#out_time').val();
        var d = outTime.substring(0, outTime.indexOf(':'));


        var sid = $(this).val();
        $('#out_date').val(sid);
        $('#out_date').datepicker('setStartDate', sid);

        //The below function is to check wether the date of upload attendance is already applied for leave or approved leave. By ***ARUL P DAS on 19/11/2019***
        var in_date = $('#in_date').val();
        var emp_fkey = $('#emp_fkeys').val();
        // alert(in_date);
        if (emp_fkey == "" || emp_fkey == null) {
            // alert('Please select Employee first');
            // $('#message').html("Please select employee first").css("background","#cf042a").css("height","32px");
            // $('#error_item').css({"border-color": "red","border-width":"1px","border-style":"solid","margin-left":"12px","right":"-10px"})
            $('#error_item').html('Please select Employee first').css('color', 'red');
            $('#in_date').val("");
            $('#emp_fkeys').focus();
            return false;
        } else {
            // $('#message').html("").css("background","white").css("height","0px");
            // $('#error_item').css({"border-color": "white","border-width":"0px","border-style":"solid","margin-left":"0px","right":"0px"})
            $('#error_item').html('').css('color', 'red');
        }
        $.ajax({
            url: livesite + 'EmployeeAttendanceUpload/checkleave/' + emp_fkey + '/' + in_date,
            success: function (resp) {
                var status = $.parseJSON(resp).status;
                var msg = $.parseJSON(resp).msg;
                var shift = $.parseJSON(resp).policy;
                if (shift['status'] == 0) {
                    alert(shift['msg']);
                } else {
                    $('#on').val(shift['on']);
                    $('#off').val(shift['off']);
                    $('#shift').val(shift['shift']);
                }
                if (msg == 1) {//1 means full day leave applied/approved. By ARUL P DAS
                    $('#btn_save').attr("disabled", true);
                    // $('#message').html(status).css("background","#cf042a").css("height","32px");
                    $('#message').html(status).css("color", "red");
                    // alert(status);
                    return false;
                    //  $.notify($.parseJSON(resp).status, {
                    //     type: 'danger',
                    //     allow_dismiss: false
                    // });
                } else if (msg == 2) {
                    $('#btn_save').attr("disabled", false);
                    // $('#message').html(status).css("background","#cf042a").css("height","32px");
                    $('#message').html(status).css("color", "red");
                    // alert(status);
                    return false;
                } else {
                    $('#btn_save').attr("disabled", false);
                    // $('#message').html(status).css("background","white").css("height","0px"); 
                    $('#message').html(status).css("color", "red");
                }
            }
        });
    });

    $('#in_time').on('change', function () {
        var ins = $('#in_date').val();
        var out = $('#out_date').val();
        var inTime = $('#in_time').val();
        var outTime = $('#out_time').val();
        var d = outTime.substring(0, outTime.indexOf(':'));
        if (d < 10 && d != '00')
        {
            var ne = '0' + outTime;
        } else
        {
            var ne = outTime;
        }
        var inForSwap = inTime.substring(0, inTime.indexOf(':'));
        if (inForSwap < 10 && inForSwap != '00')
        {
            var ine = '0' + inTime;
        } else
        {
            var ine = inTime;
        }
        var newtime = new Date(out + "T" + ne);
        var newin = new Date(ins + "T" + ine);

        //$('#out_time').val($('#in_time').val()); //This is hided because Ayswarya said "Out time is no need to automatically updated" By ***ARUL P DAS on 29/11/2019***
    });



    // $('#out_time').on('change', function () {
    //     var ins = $('#in_date').val();
    //     var out = $('#out_date').val();
    //     var inTime = $('#in_time').val();
    //     var outTime = $('#out_time').val();
    //     var d = outTime.substring(0, outTime.indexOf(':'));
    //     if (d < 10 && d != '00')
    //     {
    //         var ne = '0' + outTime;
    //     } else
    //     {
    //         var ne = outTime;
    //     }
    //     var inForSwap = inTime.substring(0, inTime.indexOf(':'));
    //     if (inForSwap < 10 && inForSwap != '00')
    //     {
    //         var ine = '0' + inTime;
    //     } else
    //     {
    //         var ine = inTime;
    //     }
    //     var newtime = new Date(out + "T" + ne);
    //     var newin = new Date(ins + "T" + ine);
    // });

    $('#out_date').on('change', function () {
        var ins = $('#in_date').val();
        var out = $('#out_date').val();
        var inTime = $('#in_time').val();
        var outTime = $('#out_time').val();
        var d = outTime.substring(0, outTime.indexOf(':'));
        if (d < 10 && d != '00')
        {
            var ne = '0' + outTime;
        } else
        {
            var ne = outTime;
        }
        var inForSwap = inTime.substring(0, inTime.indexOf(':'));
        if (inForSwap < 10 && inForSwap != '00')
        {
            var ine = '0' + inTime;
        } else
        {
            var ine = inTime;
        }
        var newtime = new Date(out + "T" + ne);
        var newin = new Date(ins + "T" + ine);

        if (newin > newtime)
        {

            alert("in date cannot be greater than out date");

        }

        // var emp_fkey=$('#emp_fkeys').val();
        // // alert(in_date);
        // if(emp_fkey=="" || emp_fkey==null){
        //     // alert('Please select Employee first');
        //     $('#message').html("Please select employee first").css("background","#cf042a").css("height","32px");
        //     $('#out_date').val("");
        //     $('#emp_fkeys').focus();
        //     return false;
        // }else{
        //     $('#message').html("").css("background","white").css("height","0px");
        // }
        // var in_date=$('#in_date').val();
        // var out_date=$('#out_date').val();
        // // alert(in_date);
        // if(in_date=="" || in_date==null){
        //     // alert('Please select Employee first');
        //     $('#message').html("Please select In Date first").css("background","#cf042a").css("height","32px");
        //     $('#out_date').val("");
        //     $('#in_date').focus();
        //     return false;
        // }else{
        //     $('#message').html("").css("background","white").css("height","0px");
        // }
        // $.ajax({
        //     url:livesite+'EmployeeAttendanceUpload/checkleave/' + emp_fkey +'/'+ in_date +'/'+out_date ,
        //     success: function(resp){
        //         var status=$.parseJSON(resp).status;
        //         var msg=$.parseJSON(resp).msg;
        //         var count=$.parseJSON(resp).count;
        //         if(msg==1){
        //             if (count>1) {
        //                 $('#message').html(status).css("background","#cf042a").css("height","45px").css("overflow-y","scroll");;
        //                 // $('#message_div').css("overflow-y","scroll");
        //             }else{
        //                 $('#message').html(status).css("background","#cf042a").css("height","32px");
        //             }
        //             $('#btn_save').attr("disabled", true);

        //             // alert(status);
        //             return false;
        //             //  $.notify($.parseJSON(resp).status, {
        //             //     type: 'danger',
        //             //     allow_dismiss: false
        //             // });
        //         }else if(msg==2) {
        //             $('#btn_save').attr("disabled", false);
        //             if (count>1) {
        //                 $('#message').html(status).css("background","#cf042a").css("height","45px").css("overflow-y","scroll");;
        //                 // $('#message_div').css("overflow-y","scroll");
        //             }else{
        //                 $('#message').html(status).css("background","#cf042a").css("height","32px");
        //             }
        //             // alert(status);
        //             return false;
        //         }else{
        //             $('#btn_save').attr("disabled", false);
        //             $('#message').html(status).css("background","white").css("height","0px"); 
        //         }
        //     }
        // });
    });

    $('#timepicker3').timepicker({
        minuteStep: 1,
        showInputs: true,
        disableFocus: true
    });


    $('#out_date').on('click', function () {

        var sd = $('#in_date').val();
        $('#out_date').val(sd);
    });
    $(document).ready(function () {

        $('#emp_fkeys').select2();

        $('#attendance_type').change(function () {
            if ($(this).val() == '1') {
                // $status='y';
                // $("#site_id").removeAttr("disabled"); 
                // $("#site_name").removeAttr("disabled");
                $("#in_time").hide();
                $("#out_date").hide();
                $("#out_time").hide();
                $("#lindate").hide();
                $("#lodate").hide();
                $("#outime").hide();
            }
        });


        $('#attendance_type').change(function () {
            if ($(this).val() == '2') {
                // $status='y';
                // $("#site_id").removeAttr("disabled"); 
                // $("#site_name").removeAttr("disabled");
                $("#in_time").show();
                $("#out_date").show();
                $("#out_time").show();
                $("#lindate").show();
                $("#lodate").show();
                $("#outime").show();
            }
        });


        //$("#pincode").inputmask("999");
        $('#attendanceuploadtable').parsley();
        var options = {
            success: function (responseText, statusText, xhr, $form) {
                //alert("Attendance Uploaded Successfully");
                alert($.parseJSON(responseText).msg);
                closeModal('att_table');
            }
        };

        // bind to the form's submit event 
        $('#attendanceuploadtable').submit(function () {
            $(this).ajaxSubmit(options);
            return false;
        });

        $('#in_date').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true,
            onSelect: function (selected) {
                var esdt = new Date(selected);

                var selectedenddate = $("#out_date").val();
                var ecdt = new Date(selectedenddate);
                if (esdt > ecdt) {
                    alert('Expected In Date Should Be Less Than Expected Out Date');
                    $("#in_date").val('');
                }

            }
        });

        $("#in_date").inputmask("yyyy-mm-dd");
        $('#out_date').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true,
            onSelect: function (selected) {
                var ecdt = new Date(selected);
                var selectedstartdate = $("#in_date").val();
                var esdt = new Date(selectedstartdate);
                $('#out_date').val(esdt);
                if (esdt > ecdt) {
                    alert('Expected Out Date Should Be Greater Than Expected In Date');
                    $("#out_date").val('');
                }

            }
        });


        $("#out_date").inputmask("yyyy-mm-dd");
        $('#in_time').timepicker({
            format: 'hh:mm:ss',
            showMeridian: false,
            showSeconds: true,
        });
        $('#out_time').timepicker({
            format: 'HH:MM:SS',
            showMeridian: false,
            explicitMode: false,
            showSeconds: true,
        });
    });
</script>



