<style>
    .close-btn {
       margin:10px 15px;
        padding: 6px 25px;
        float: inline-end;
        background-color: #1e516e;
        border-radius: 5px;
        border: none;
        color: white;
    }
</style>
<?php ?>

<script type="text/javascript">
    //edited by athira on 14-01-2025
    $("#end_date").datepicker({
        format: 'dd-mm-yyyy',
        autoclose: true,
        onSelect: function(selectedDate) {}
    });
    // $("#end_date2").datepicker({
    //     format: 'dd-mm-yyyy',
    //     autoclose: true,
    //     onSelect: function(selectedDate) {
    //     }
    // });


    //end
    $(document).ready(function() {
        /*
         * Tax Head save
         */
        $("#emp_type").select2();
        $("#designation").select2();
        $("#emp_branch").select2();
        $("#emp_dept").select2();
        $("#shift").select2();
        $("#leave").select2();
        $("#salary").select2();
        //edited by athira on 07-01-2025
        $("#grade").select2();
        $("#holiday").select2();
        //end

        var usersoptions = {
            url: function(phrase) {
                var branch = $('#emp_pkey').val();
                return livesite + "Employee/getautocompletions_superior?username=" + phrase + "&branch=" + branch;
            },
            getValue: "emp_name",
            list: {
                onClickEvent: function() {
                    //var selectedItem = $('#filterby_employees').getSelectedItemData();
                    //var site_pkey = selectedItem.emp_pkey;
                },
                onKeyEnterEvent: function() {

                },
                onSelectItemEvent: function() {
                    var selectedItem = $('#approved_by').getSelectedItemData();
                    var site_pkey = selectedItem.emp_pkey;
                    $('#approved_by_pkey').val(site_pkey);
                }
            }
        };

        $('#approved_by').easyAutocomplete(usersoptions);

        var usersoptions1 = {
            url: function(phrase) {
                var branch = $('#emp_pkey').val();
                return livesite + "Employee/getautocompletions_superior?username=" + phrase + "&branch=" + branch;
            },
            getValue: "emp_name",
            list: {
                onClickEvent: function() {
                    //var selectedItem = $('#filterby_employees').getSelectedItemData();
                    //var site_pkey = selectedItem.emp_pkey;
                },
                onKeyEnterEvent: function() {

                },
                onSelectItemEvent: function() {
                    var selectedItem = $('#filter_superior').getSelectedItemData();
                    var site_pkey = selectedItem.emp_pkey;
                    $('#hierarch_pkey').val(site_pkey);
                }
            }
        };

        $('#filter_superior').easyAutocomplete(usersoptions1);


        $('#familys').parsley();
        var options = {
            success: function(responseText, statusText, xhr, $form) {
                $.notify("Promotion Saved Successfully", {
                    type: 'success',
                    allow_dismiss: false
                });
                $('#largeModalForm').modal('hide');
                $("#example_family").DataTable().ajax.url(livesite + "Employee/lstfamilies/" + $('#emp_pkey').val()).load();

            }
        };

        // bind to the form's submit event
        $('#familys').submit(function() {
            var flag1 = false;
            $('.validate-promotion').each(function() {
                if ($(this).val() != '') {
                    flag1 = true;

                } else {
                    //                  alert($(this).val());  
                }

            });
            //            alert(flag1);
            if (flag1 == false) {
                alert("no changes found");
                return false;
            }
            $('#familys').attr('action', livesite + 'Employee/savepromotions');

            $('#btn-submitfami').html('<li class="fa fa-spinner fa-spin"></li> saving...').attr('disabled', 'disabled');
            $(this).ajaxSubmit(options);

            return false;
        });
        //Ends  
    });
</script>

<div class="modal-header" style="background: #1e516e;color: white;padding:8px">
    <h4 class="modal-title">Employee Info History</h4> <!--edited by ASHIN on 25-09-24--->
</div>
<!--<legend>Promotion </legend>-->
<form class="form-horizontal" method="post" id="familys">

    <input id="emp_pkey" name="emp_fkey" type="hidden" value="<?php echo $emp_pkey; ?>">
    <div class="col-xs-12">
        <div class="col-xs-12" style="text-align: center;"> <!--edited by ASHIN on 25-09-24--->
            <h3 class="box-title"><b> <?php echo isset($arr_employee[0][0]['name']) ? $arr_employee[0][0]['name'] : 'N/A';
 ?></b></h3> <!--edited by ASHIN on 24-09-24--->
            <!--Edited by ASHIN on 26-09-24--->
        
            <div style="margin-bottom: 10px; text-align: left;"> <!-- edited by ASHIN on 30-09-24 -->
    <div><strong>Joining Date:</strong> <?php
                                $joining_date = $arr_info[0]['employee_info']['joining_date'];
                                $formatted_date = date('d-m-Y', strtotime($joining_date));
                                echo ($formatted_date);
                                ?></div>
    <div><strong>Employee ID:</strong> <?php echo isset($arr_proff[0]['emp_proff']['emp_company_id']) ? $arr_proff[0]['emp_proff']['emp_company_id'] : 'N/A'; ?></div>
    <div><strong>Designation:</strong> <?php echo isset($arr_info[0]['employee_info']['designation']) ? $arr_info[0]['employee_info']['designation'] : 'N/A'; ?></div>
</div>

            <hr style="border-top: 1px solid #cec1c1; ">
            <!--                <div class="box-header">
                                <div class="box-tools">
                                    <div class="input-group input-group-sm" style="width: 150px;">
                                        <input type="text" name="table_search" class="form-control pull-right" placeholder="Search">
                                    </div>
                                </div>
                            </div>-->
            <!-- /.box-header -->
            <!--edited by ASHIN on 26-09-24--->
            <div class="box-body table-responsive no-padding">
                <!-- <table class="table table-hover"> -->
                <table class="table table-hover" border="1" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background-color: #f2f2f2;">
                            <th style="padding: 8px; text-align: center;">Sl No</th>
                            <!-- <th>Type</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Approval By</th> -->
                            <th style="padding: 4px; text-align: center;">Date</th>
                            <th style="padding: 4px; text-align: center;">Data Type</th>
                            <th style="padding: 4px; text-align: center;">Changed To</th>
                            <th style="padding: 4px; text-align: center;">Status</th>
                            <th style="padding: 4px; text-align: center;">Approved By</th>
                            <!--                                    <th>Status</th>
                            <th>Reason</th>-->
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Row to display the employee's joining date -->
                        <!-- <tr>
                            <td style="padding: 8px; text-align: center;" colspan="6">
                                <strong>Joining Date:</strong>
                                <?php
                                $joining_date = $arr_info[0]['employee_info']['joining_date'];
                                $formatted_date = date('d-m-Y', strtotime($joining_date));
                                echo ($formatted_date);
                                ?>
                            </td>
                        </tr> -->
                        <!--edited by ASHIN on 10-10-24-->
                        <?php
                        $i = 1;
                        foreach ($arr_history as $val) { ?>
                            <tr>
                                <td style="padding: 8px; text-align: center;"><?php echo $i++; ?></td>
                                <?php
                                $datetime = $val['emp_config_history']['creation_date'];
                                $date_obj = new DateTime($datetime);
                                $formatted_date = $date_obj->format('d-m-Y');
                                $time = $date_obj->format('H:i:s');
                                $formatted_datetime = $formatted_date . ' &nbsp;&nbsp; ' . $time;
                                //  $datetime_array = explode(' ', $datetime); // Split the datetime string using a single space
                                //  $formatted_datetime = $datetime_array[0] . ' &nbsp;&nbsp; ' . $datetime_array[1]; // Add extra space between date and time
                                ?>
                                <td style="padding: 8px; text-align: center;"><?php echo $formatted_datetime; ?></td>
                                <!-- <td style="padding: 8px; text-align: center;"><//?php echo $val['emp_config_history']['type']; ?></td> -->
                                <td style="padding: 8px; text-align: center;">
                                    <?php
                                    // Check the value of type and display custom labels
                                    if ($val['emp_config_history']['type'] == 'HOLIDAY') {
                                        echo 'Holiday List';
                                    } elseif ($val['emp_config_history']['type'] == 'SALARY') {
                                        echo 'Salary Structure';
                                    } elseif ($val['emp_config_history']['type'] == 'LEAVE') {
                                        echo 'Leave Policy';
                                    } elseif ($val['emp_config_history']['type'] == 'HIERARCHY') {
                                        echo 'Hierarchy';
                                    } elseif ($val['emp_config_history']['type'] == 'SHIFT') {
                                        echo 'Shift Policy';
                                    } else {
                                        // If none of the types match, display the original type
                                        echo $val['emp_config_history']['type'];
                                    }
                                    ?>
                                </td>
                                <td style="padding: 8px; text-align: center;"><?php echo $val['emp_config_history']['day_time_desc']; ?></td>
                                <td style="padding: 8px; text-align: center;"><?php echo $val['emp_config_history']['status']; ?></td>
                                <td style="padding: 8px; text-align: center;"><?php echo $val['emp_config_history']['created_by']; ?></td>
                            </tr>
                        <?php } ?>
                        <?php
                        // Check if $arr_termination is not empty and contains the date
                        if (!empty($arr_termination) && !empty($arr_termination[0]['t']['last_approved_working_date'])):
                            $last_working_date = $arr_termination[0]['t']['last_approved_working_date'];
                            $formatted_date = date('d-m-Y', strtotime($last_working_date));
                            $reason = $arr_termination[0]['t']['reason'];
                            $relieving = $arr_termination[0]['t']['submitted_date'];
                            $created_by = $arr_termination[0]['t']['created_by'];
                            $relieving_date = date('d-m-Y', strtotime($relieving));
                            //edited by athira on 17-01-2025
                            //edited by athira on 23-01-2025 
                            $created_by_name = $arr_termination[0][0]['created_by_name'];
                            $approved_by_name = $arr_termination[0][0]['approved_by_name'];
                            $is_approved = $arr_termination[0]['t']['is_approved'];
                            $user_group = $this->Session->read("user_group");
                            $approved_by = $arr_termination[0]['t']['approved_by'];
                            if ($user_group != '2') {
                                $approved_admin = $this->Session->read('login_user_id');
                            }
                            //end
                            //end

                        ?>
                            <tr>
                                <td style="padding: 8px; text-align: left;" colspan="6">
                                    <table width="100%" align="center" style="margin: 0 auto;">
                                        <!-- Single line: Resignation Date, Reason, Relieving Date -->
                                        <tr>
                                            <td style="text-align: center; padding-right: 100px;">
                                                <strong>Resignation Date:</strong> <?php echo $relieving_date; ?>
                                            </td>
                                            <td style="text-align: center; padding-right: 50px;">
                                                <strong>Reason:</strong> <?php echo $reason; ?>
                                            </td>
                                            <td style="text-align: left; padding-left: 40px;">
                                                <strong>Relieving Date:</strong> <?php echo $formatted_date; ?>
                                            </td>
                                        </tr>
                                        <!-- Second line: Approved By, Initiated By -->
                                        <tr>
                                            <!-- edited by athira on 07-01-2025 -->
                                            <!-- edited by athira on 23-01-2025 -->
                                            <!-- edited by athira on 24-01-2025 -->
                                            <td style="text-align: left; padding-left: 35px;padding-top:20px;">
                                                <!-- edited by athira on 24-01-2025  end -->
                                                <!-- end -->
                                                <!-- <strong>Approved By:</strong> <//?php echo $approved_by; ?> -->
                                                <?php
                                                if ($is_approved == "Y") {
                                                    if ($approved_by == 0) {
                                                        echo '<strong>Approved By:</strong> ' . h($approved_admin);
                                                    } else {
                                                        echo '<strong>Approved By:</strong> ' . h($approved_by_name);
                                                    }
                                                } else {
                                                    echo '<strong>Approved By:</strong> ';
                                                }

                                                ?>
                                            </td>
                                            <!-- edited by athira on 07-01-2025 -->
                                            <!-- edited by athira on 18-01-2025 -->
                                            <!-- edited by athira on 24-01-2025 -->
                                            <td style=" padding-left: 28px;padding-top:20px;">
                                                <!-- edited by athira on 24-01-2025  end -->
                                                <!-- end -->
                                                <?php

                                                // Logic for displaying Initiated By
                                                if (!empty($created_by_name)) {
                                                    echo '<strong>Initiated By:</strong> ' . h($created_by_name) . ' - ' . h($created_by);
                                                } else {
                                                    $created_by_name = $approved_admin;
                                                    echo '<strong>Initiated By:</strong> ' . h($created_by_name);
                                                }
                                                ?>


                                                <!-- <strong>Initiated By:</strong> <//?php echo $created_by_name ."-" .$created_by; ?> -->
                                            </td>
                                            <!--end----->
                                            <!-- edited by athira on 23-01-2025   end--- -->
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        <?php endif; ?>

                    </tbody>

                </table>
            </div>
            <!-- /.box-body -->
        </div>



        <script>
            $(document).ready(function() {
                // Initialize datepicker with DD-MM-YYYY format
                //edited by athira on 14-01-2025
                $('#end_date').datepicker({
                    dateFormat: 'dd-mm-yy', // Sets display format to DD-MM-YYYY
                    changeMonth: true,
                    changeYear: true
                });
                // $('#end_date2').datepicker({
                //     dateFormat: 'dd-mm-yy',  // Sets display format to DD-MM-YYYY
                //     changeMonth: true,
                //     changeYear: true
                // });
                //end

                //edited by athira on 12-02-2025
                // Get the joining date from PHP
                let joiningDate = "<?php echo date('d-m-Y', strtotime($joining_date)); ?>";

                // Function to check if end date is valid
                function validateEndDate() {
                    let endDate = $('#end_date').val();
                    // let endDate2=$('#end_date2').val();

                    if (endDate !== '' || endDate2 !== '') {
                        let joinDateObj = new Date(joiningDate.split('-').reverse().join('-')); // Convert to Date object
                        let endDateObj = new Date(endDate.split('-').reverse().join('-')); // Convert to Date object
                        // let endDateObj2 = new Date(endDate2.split('-').reverse().join('-')); // Convert to Date object

                        if (endDateObj < joinDateObj || endDateObj2 < joinDateObj) {
                            alert("End date cannot be earlier than the joining date (" + joiningDate + ")");
                            $('#end_date').val(''); // Clear the input field
                            // $('#end_date2').val('');
                        }
                    }
                }

                // Validate end date when the field loses focus
                $('#end_date').on('change blur', function() {
                    validateEndDate();
                });
                // $('#end_date2').on('change blur', function () {
                //     validateEndDate();
                // });
            });
            //end
        </script>

        <script>
            // JavaScript function to show or hide the "Contract Period" row based on the selected value
            function toggleContractPeriod() {
                var empType = document.getElementById('emp_type').value;
                var contractPeriodRow = document.getElementById('contract-period-row');
                //edited by athira on 07-01-2025
                var probationPeriodRow = document.getElementById('probation-period-row');

                if (empType === 'Contract') {
                    contractPeriodRow.style.display = 'table-row';
                    probationPeriodRow.style.display = 'none';
                } else if (empType === 'Probation') {
                    probationPeriodRow.style.display = 'table-row';
                    contractPeriodRow.style.display = 'none';
                } else {
                    contractPeriodRow.style.display = 'none';
                    probationPeriodRow.style.display = 'none';
                }
                //end
            }

            // Call the function on page load to handle pre-selected value
            window.onload = function() {
                toggleContractPeriod();
            };
        </script>


   
    </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="close-btn"  onclick="$('#largeModalForm').modal('hide');">Close</button>
    </div>

</form>