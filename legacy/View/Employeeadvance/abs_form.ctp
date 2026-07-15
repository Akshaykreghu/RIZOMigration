<script>
    $.validate({
        form: '#attendanceuploadtable'
    });
    var options = {
        success: function(resp) {
            // $('#modalForm').modal('hide'); // Edited by Akshay on 28-2-2025
            $('#att_table').datagrid('reload');
            $('#attdatacsv').val('');
            $("#filterby_branch").select2("val", "");
            $("#emp_fkey").select2("val", "");
            $.notify($.parseJSON(resp).msg, {
                type: 'success',
                allow_dismiss: false
            });
        } // post-submit callback
    };


    $('#attendanceuploadtable').on('submit', function(event) {
        event.preventDefault();
        if (confirm(" Do You Want  To Save The Form")) {
            $('#modalForm').modal('hide'); // Edited by Akshay on 28-2-2025
            $('#attendanceuploadtable').ajaxSubmit(options)
        }
    });
</script>
<div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
        <div class="modal-header" style="background: #00659f;color: white">
            <h4 class="modal-title"> Employee Advance Form</h4>
        </div>
        <div class="modal-body">
            <!-- Form starts -->

            <form class="form-horizontal" id="attendanceuploadtable" action="<?php echo $this->webroot; ?>Employeeadvance/employeeloansave" method="POST">

                <div class="modal-body">
                    <div class="form-group">
                        <div class="col-md-12">
                            <label for="in_date" class="col-md-4 control-label">Choose Employee<span class="star">*</span></label>
                            <div class="col-md-1">:</div>
                            <div class="col-md-7">
                                <!--                          <select id="emp_fkey" class="form-control" name="emp_fkey" onchange="loadExistingCTCInfo();" >-->
                                <!-- Edited by Akshay on 28-2-2025 -->
                                <select id="emp_fkey1" class="form-control" name="emp_fkey" onchange="findextingsalary();" required="required" style="width: 100%;">
                                <!-- End -->
                                    <option value="">[--Select--]</option>
                                    <?php
                                    //edited by arul on 12/12/2019 Employee company id added
                                    //                                    foreach ($arr_employees as $value) {
                                    //                                        $selected = ($data['emp_fkey'] == $value['EmployeeDetails']['emp_pkey']) ? 'selected="selected"' : '';
                                    //
                                    //                                        echo '<option value="' . $value['EmployeeDetails']['emp_pkey'] . '" ' . $selected . '>' . $value['EmployeeDetails']['first_name'] . ' ' . $value['EmployeeDetails']['last_name'] . ' - ' .$value['EmployeeDetails']['emp_id']. '</option>';
                                    //                                    }
                                    foreach ($arr_employees as $value) {
                                        $selected = ($data['emp_fkey'] == $value['emp_details']['emp_pkey']) ? 'selected="selected"' : '';
                                        echo '<option value="' . $value['emp_details']['emp_pkey'] . '" ' . $selected . '>' . $value['emp_details']['first_name'] . ' ' . $value['emp_details']['last_name'] . ' - ' . $value['emp_proff']['emp_company_id'] . '</option>';
                                    }
                                    //end Employee company id added
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-12">
                            <label for="in_time" class="col-md-4 control-label">Affected Month<span class="star">*</span></label>
                            <div class="col-md-1">:</div>
                            <div class="col-md-7">
                                <!--edited by sinsiya 25-05-2024-->
                                <!-- Edited by Akshay on 28-2-2025 -->
                                <input type="text" required="required" class="form-control date-picker" autocomplete="off"
                                    value="<?php
                                            if (!empty($data['affected_month'])) {
                                                $affected_month = $data['affected_month'];
                                                $datetime = DateTime::createFromFormat('Y-m', $affected_month);
                                                if ($datetime !== false) {
                                                    echo $datetime->format('m-Y');
                                                }
                                            } ?>" name="affected_month" id="affected_month">
                                            <!-- End -->
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-12">
                            <label for="in_date" class="col-md-4 control-label">Advance Amount<span class="star">*</span></label>
                            <div class="col-md-1">:</div>
                            <div class="col-md-7">
                            <!-- Edited by Akshay on 28-2-2025 -->
                            <input type="number" required="required" class="form-control" value="<?php echo isset($data['advance_amount']) ? $data['advance_amount'] : ''; ?>" name="advance_amount" onkeyup="findextingsalary();" onblur="findamount();" id="advance_amount">
                            <!-- End -->
                            </div>
                        </div>
                    </div>
                    <!--edited by sinsiya 21-05-2024-->
                    <div class="form-group">
                        <div class="col-md-12">
                            <label for="payment_date" class="col-md-4 control-label">Payment Date<span class="star">*</span></label>
                            <div class="col-md-1">:</div>
                            <div class="col-md-7">
                                <input id="Payment_date" required="required" name="Payment_date" value="<?php echo isset($data['payment_date']) ? date('d-m-Y', strtotime($data['payment_date'])) : date('d-m-Y'); ?>" type="text" placeholder="(DD-MM-YYYY)" class="form-control input-md" autocomplete="off">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-12">
                            <label for="Remarks" class="col-md-4 control-label">Remarks</label>
                            <div class="col-md-1">:</div>
                            <div class="col-md-7">
                                <input type="text" id="remarks" name="remarks" value="<?php echo isset($data['remarks']) ? $data['remarks'] : ''; ?>" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="form-group" id="totel_salary" name style="display:none">
                        <div class="col-md-12">
                            <label for="balancE_leave" class="col-md-4 control-label" style="color:#890AEC;">Maximum Amount</label>
                            <div class="col-md-1">:</div>
                            <div class="col-md-7">
                                <input id="totel_amount" name="totel_amount" value="<?php echo $data['advance_amount'] ?>" disabled class="form-control">

                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <!--<div id="message-container" style="text-align: left;width: 200px;"></div>-->
                        <input type="hidden" required="required" class="form-control" value="<?php echo $data['emp_advance_pkey'] ?>" name="emp_advance_pkey">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
                        <button type="submit" name="btn-submit" id="btn-submit" class="btn btn-primary">Save</button>

                    </div>



                </div>



            </form>
            <!-- Tax Head Detail Form -->





            <!-- form ends-->
        </div>

    </div>

</div>
<script type="text/javascript">
    $('#emp_fkey1').select2(); //This is to search employee and company id. By ***ARUL P DAS on 20/12/2019
    //salary check 
    function findsalary() {
        var month = $('#affected_month').val();
        var emp_fkey = $('#emp_fkey1').val();
        var url = 'Employeeadvance/salary';
        //value passiing ajax   
        $.ajax({
            url: url,
            type: 'post',
            data: {
                month_year: month,
                empid: emp_fkey
            },
            success: function(resp) {
                var salary = $.parseJSON(resp);
                $("#totel_salary").show();
                $("#totel_amount").val(salary);
            }
        });
    }


    //check salaryslip
    var isMessageDisplayed = false; // Flag to control the message display

    function findextingsalary() {
        findsalary();

        var month = $('#affected_month').val();
        var emp_fkey = $('#emp_fkey1').val();

        var url = 'Employeeadvance/salarycheck';

        $.ajax({
            url: url,
            type: 'post',
            data: {
                month_year: month,
                empid: emp_fkey
            },
            success: function(resp) {
                var json_obj;
                try {
                    json_obj = $.parseJSON(resp);
                } catch (e) {
                    console.error("Error parsing JSON response:", e);
                    return;
                }

                if (json_obj.rows.length > 0 && !isMessageDisplayed) {
                    $.notify({
                        message: json_obj.msg
                    }, {
                        type: 'danger',
                        allow_dismiss: false,
                        placement: {
                            from: "bottom",
                            align: "right"
                        },
                        z_index: 9999 // Adjust the z-index value as needed
                    });

                    // $('#message-container').html(
                    //   '<div class="alert alert-danger alert-left">' + json_obj.msg + '</div>'
                    //).fadeIn(); // Fade in the message container

                    $("#btn-submit").hide();
                    $("#advance_amount").val('');
                    $('#advance_amount').attr('readonly', true);
                    isMessageDisplayed = true; // Set the flag to indicate the message has been displayed
                    // setTimeout(function() {
                    //     $('#message-container').fadeOut();
                    // }, 3000);
                } else if (json_obj.rows.length === 0) {
                    $('#message-container').html(''); // Clear the message if no issues
                    $("#btn-submit").show();
                    $('#advance_amount').attr('readonly', false);
                    isMessageDisplayed = false; // Reset the flag if no message needs to be displayed
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX error:", status, error);
            },
            complete: function() {
                // Additional cleanup or actions can be performed here if necessary
            }
        });
    }

    //validation form amount
    function findamount() {
        var companyCode = <?php echo json_encode($company_code); ?>;
        var rate = $('#advance_amount').val();
        var emp_salary = parseInt($('#totel_amount').val());

        // Edited by Akshay on 27-1-2025
        if (companyCode == 'GLET' || companyCode == 'ABSG') {
            // Check if emp_salary is NaN
            if (isNaN(emp_salary)) {
                alert("Please wait for the maximum amount to load.");
                $("#advance_amount").val(''); // Clear the advance amount field
            }
        }
        // End

        // var one_amonu = parseInt(1);
        //var salary = emp_salary + one_amonu;
        if (rate > emp_salary) {
            alert("Amount exceeds the maximum limit");
            // $("#advance_amount").val('');
        }
        //checking validation on enter number        
        ///^[1-9][0-9\.]{0,15}$/
        if (rate.match(/^[1-9][0-9\.]{0,15}$/)) {
            if (rate < 1) {
                alert('Please Enter A Valid Amount')
                $("#advance_amount").val('');
            }
        } else {
            $("#advance_amount").val('');
        }

    }
    $(document).ready(function() {

        //$("#pincode").inputmask("999");
        // $('#attendanceuploadtable').parsley();
        var options = {
            //      success:function(responseText, statusText, xhr, $form){
            //        alert("Employee Uploaded Successfully");
            //	closeModal('att_table');
            //}
        };

        // bind to the form's submit event 
        //    $('#attendanceuploadtable').submit(function() { 
        //        $(this).ajaxSubmit(options);         
        //        return false;
        //    });
        //edited by sinsiya 25-05-2024
        // Edited by Akshay on 28-2-2025
        $('#affected_month').datepicker({
            format: 'mm-yyyy',
            autoclose: true,
            startView: "months",
            minViewMode: "months",
        }).on('changeDate', function() {
            findextingsalary();
        });
        // End

        $("#yin_time").datepicker({
            changeMonth: true,
            changeYear: true,
            showButtonPanel: true,
            dateFormat: 'MM yy',
            onClose: function(dateText, inst) {
                var month = $("#ui-datepicker-div .ui-datepicker-month :selected").val();
                var year = $("#ui-datepicker-div .ui-datepicker-year :selected").val();
                $(this).datepicker('setDate', new Date(year, month, 1));
            },
            beforeShow: function(input, inst) {
                if ((datestr = $(this).val()).length > 0) {
                    year = datestr.substring(datestr.length - 4, datestr.length);
                    month = jQuery.inArray(datestr.substring(0, datestr.length - 5), $(this).datepicker('option', 'monthNames'));
                    $(this).datepicker('option', 'defaultDate', new Date(year, month, 1));
                    $(this).datepicker('setDate', new Date(year, month, 1));
                }

                var option = this.id == "in_time" ? "maxDate" : "minDate";
                if ((selectedDate = $(other).val()).length > 0) {
                    year = selectedDate.substring(selectedDate.length - 4, selectedDate.length);
                    month = jQuery.inArray(selectedDate.substring(0, selectedDate.length - 5), $(this).datepicker('option', 'monthNames'));
                    $(this).datepicker("option", option, new Date(year, month, 1));
                }
            }
        });
        //       $("#btnShow").click(function(){ 
        //       if ($("#in_time").val().length == 0 ){
        //           alert('All fields are required');
        //       }
        //       else{
        //           alert('Selected Month Range :'+ $("#in_time").val();
        //           }
        //       })
        //    $(function() {
        //$('.date-picker').datepicker( {
        //    changeMonth: true,
        //    changeYear: true,
        //    showButtonPanel: true,
        //    dateFormat: 'MM yy',
        //    onClose: function(dateText, inst) { 
        //        var month = $("#ui-datepicker-div .ui-datepicker-month :selected").val();
        //        var year = $("#ui-datepicker-div .ui-datepicker-year :selected").val();
        //        $(this).datepicker('setDate', new Date(year, month, 1));
        //    }
        //});
        //});
        //
        //        var currentTime = new Date();
        //// First Date Of the month 
        //        var startDateFrom = new Date(currentTime.getFullYear(), currentTime.getMonth(), 1);
        //// Last Date Of the Month 
        //        var startDateTo = new Date(currentTime.getFullYear(), currentTime.getMonth() + 1, 0);
        //        var d = new Date();
        //d.setMonth(1);
        //        $("#in_time").datepicker({
        //            dateFormat: 'YYYY.mm',
        //            minDate: startDateFrom,
        //            maxDate: startDateTo,
        //              defaultDate: d,
        //        });

        //        $('#in_time').datepicker({
        //            format: 'yyyy-mm-dd',
        //            onSelect: function (selected) {
        //                var esdt = new Date(selected);
        //                var selectedenddate = $("#out_date").val();
        //                var ecdt = new Date(selectedenddate);
        //                if (esdt > ecdt) {
        //                    alert('Expected In Date Should Be Less Than Expected Out Date');
        //                    $("#in_date").val('');
        //                }
        //
        //            }
        //        })
        $('#out_time').datepicker({
            format: 'yyyy-mm-dd',
            onSelect: function(selected) {
                var ecdt = new Date(selected);
                var selectedstartdate = $("#in_date").val();
                var esdt = new Date(selectedstartdate);
                if (esdt > ecdt) {
                    alert('Expected Out Date Should Be Greater Than Expected In Date');
                    $("#out_date").val('');
                }

            }
        })
        $("#out_date").inputmask("yyyy-mm-dd");
    });
    //edited by sinsiya 25-05-2024

    $(document).ready(function() {
        var today = new Date();
        var formattedToday = ("0" + today.getDate()).slice(-2) + '-' +
            ("0" + (today.getMonth() + 1)).slice(-2) + '-' +
            today.getFullYear();

        // Calculate the date 30 days ago
        var pastDate = new Date();
        pastDate.setDate(today.getDate() - 30);
        var formattedPastDate = ("0" + pastDate.getDate()).slice(-2) + '-' +
            ("0" + (pastDate.getMonth() + 1)).slice(-2) + '-' +
            pastDate.getFullYear();

        $('#Payment_date').datepicker({
            format: 'dd-mm-yyyy',
            autoclose: true,
            startDate: formattedPastDate, // Set the start date to 30 days ago

            //endDate: '0d'  // Allow selection up to today
        });
    });
</script>