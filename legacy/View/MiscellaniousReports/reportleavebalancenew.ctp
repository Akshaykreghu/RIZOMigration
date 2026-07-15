<!-- edited by athira on 10-10-2025 -->
<?php if ($mode == '') { ?>
    <div class="modal-body" style="overflow-y: auto;">
        <legend style="text-align: center; font-weight: bold;">Employees Leave Balance Report - <?php echo $dates; ?></legend>
        <h4 align="center" style="font-weight:bold;">(<?php echo isset($user_id) ? "Report run by " . ($user_id) . " - " . $date_time : ''; ?>)</h4>
        <div class="row">
            <div class="col-md-12">
                <div class="box ">
                    <?php // if ($cur_year != $from || $cur_year == '') {
                    ?>

                    <!--<h3>The leave year is not opened yet</h3>-->

                    <?php
                    //                    } else {
                    if (count($arr_leavepolicydetails_for_template) == 0) {
                        echo "<h3>No Data Available With The Selected Criteria</h3>";
                    }
                    //                    }
                    ?>
                    <?php
                    $i = 0;
                    foreach ($arr_leavepolicydetails_for_template as $value) {
                        $i += 1;
                        if (count($value['summary']) > 0) {
                    ?>
                            <div class="box-body" style="overflow-x: scroll;">
                                <fieldset>
                                    <legend style="font-weight: bold ; "> Leave Balance Report of
                                        <?php
                                        if ($cr == 'Departments') {
                                            echo isset($value['summary']['0']['Departments']['dept_name']) ? $value['summary'][0]['Departments']['dept_name'] : '';
                                        } else if ($cr == 'Units') {
                                            echo isset($value['summary'][0]['Units']['branch_name']) ? $value['summary'][0]['Units']['branch_name'] : '';
                                        } else if ($cr == 'EmployeeDetails') {
                                            echo isset($value['summary'][0]['0']['emp_name']) ? $value['summary'][0]['0']['emp_name'] : '';
                                            echo isset($value['summary'][0]['ed']['status']) && $value['summary'][0]['ed']['status'] == "2" ? '(Resigned)' : '';
                                        } else {
                                            echo isset($value['summary'][0][0]['LeaveType']['leave_type']) ? $value['summary'][0][0]['LeaveType']['leave_type'] : '';
                                        }
                                        ?> </legend>
                                    <div class="row">
                                        <div class="col-md-12">

                                        </div>
                                    </div>
                                </fieldset>
                                <br>
                                <fieldset>
                                    <table class="table table-bordered todayattandence" id="todayattandence">
                                        <thead>
                                            <tr>
                                                <th>Sl No </th>
                                                <th>Employee Name</th>
                                                <!-- edited by athira on 07-07-2025 -->
                                                <?php if ($company_code =='DEMO' || $company_code=='GLET' || $company_code=='SRTS') {?>
                                                    <th>Employee Name (US Format)</th>
                                                <?php } ?>
                                                <!-- end -->
                                                <th>Employee ID</th>
                                                <!-- edited by athira on 07-07-2025 -->
                                                <?php if ($company_code =='DEMO' || $company_code=='GLET' || $company_code=='SRTS') {?>
                                                    <th>Employee ID (US Format)</th>
                                                <?php }?>
                                                <!-- end -->
                                                <th>Date Of Joining</th>
                                                <th>Branch</th>
                                                <th>Designation</th>
                                                <th>Department</th>
                                                <!--    <th>LEAVEPOLICY_GROUP_NAME</th> -->

                                                <th>Leave Type</th>
                                                <th>Leave Policy</th>
                                                <th>Allotted Leave For The year</th>
                                                <th>Carry Forwarded</th>
                                                <th>Leave Taken</th>
                                                <th>Encashed Leaves</th>
                                                <th>Eligibility For Selected Date</th>
                                                <th>Leave Balance (End Of Period)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $arr_data = $value['summary'];  ?>
                                            <?php if (count($arr_data) > 0) { ?>
                                                <?php $i = 1; ?>
                                                <?php
                                                foreach ($arr_data as $val) {
                                                    if ($cr != "LeaveType") {
                                                ?>

                                                        <tr>
                                                            <?php
                                                            $leavetaken = $val['lp']['alloted_leave_forthe_year'] - $val['0']['leavebalance'];
                                                            if ($val['lp']['leave_policy_type'] == 'Y') {
                                                                $type = 'Yearly';
                                                            } else if ($val['lp']['leave_policy_type'] == 'M') {
                                                                $type = 'Monthly';
                                                            } else {
                                                                $type = 'Present Days';
                                                            }
                                                            $terminate = isset($val['0']['terminate']) ? $val['0']['terminate'] : 0;
                                                            $limit = isset($val['0']['encashed_leave']) ? $val['0']['encashed_leave'] : 0;
                                                            ?>
                                                            <td><?php echo $i++; ?></td>
                                                            <td><?php echo $val['0']['emp_name']; ?><?php echo isset($val['ed']['status']) && $val['ed']['status'] == "2" ? '(Resigned)' : ''; ?></td>
                                                            <!-- edited by athira on 07-07-2025 -->
                                                            <?php if ($company_code =='DEMO' || $company_code=='GLET' || $company_code=='SRTS') {?>
                                                                <td><?php echo $val['info']['EmpUSName']; ?></td>
                                                            <?php }?>
                                                            <!-- end -->
                                                            <td><?php echo $val['info']['employee_id']; ?></td>
                                                            <!-- edited by athira on 07-07-2025 -->
                                                             <?php if ($company_code =='DEMO' || $company_code=='GLET' || $company_code=='SRTS') {?>
                                                                <td><?php echo $val['info']['emp_us_id']; ?></td>
                                                            <?php }?>
                                                            <!-- end -->
                                                            <?php $joiningDate = $val['info']['joining_date'];
                                                            $join = date('d-m-Y', strtotime($joiningDate)); ?>
                                                            <td><?php echo $join; ?></td>
                                                            <td><?php echo $val['info']['branch']; ?></td>
                                                            <td><?php echo $val['info']['designation']; ?></td>
                                                            <td><?php echo $val['info']['department']; ?></td>
                                                            <td><?php echo $val['LeaveType']['leave_type']; ?></td>
                                                            <td><?php echo $type; ?></td>
                                                            <td> <?php if ($val['lp']['leave_policy_type'] == 'P') {
                                                                        echo '0';
                                                                    } else {
                                                                        echo $val['lp']['alloted_leave_forthe_year'];
                                                                    } ?></td>

                                                            <?php
                                                            $carry_forward_limit = isset($val['lp']['CARRY_FORWARD_LIMIT']) ? round($val['lp']['CARRY_FORWARD_LIMIT'],1) : '';
                                                            $carry = isset($val['0']['carryforwarded']) ? round($val['0']['carryforwarded'],1) : 0;
                                                            $carry_to_show = min($carry, $carry_forward_limit); ?>
                                                            <td> <?php echo $carry_to_show;  ?></td>
                                                            <td> <?php echo $val['0']['leavetaken']; ?></td>
                                                            <td> <?php echo $limit; ?></td>
                                                            <!--                                                            <td> <?php if ($terminate != NULL && $terminate > 0 && $limit != NULL && $limit > 0) {
                                                                                                                                        echo $limit - $val['0']['leavetaken'];
                                                                                                                                    } else {
                                                                                                                                        echo isset($val['0']['encashed_leave']) ? $val['0']['encashed_leave'] : '0';
                                                                                                                                    } ?></td>-->
                                                            <td> <?php if ($terminate != NULL && $terminate > 0 && $limit != NULL && $limit > 0) {
                                                                        echo '0';
                                                                    } else {
                                                                        echo round($val['0']['leavebalance'], 1);
                                                                    } ?></td>

                                                                    <td> <?php if ($terminate != NULL && $terminate > 0 && $limit != NULL && $limit > 0) {
                                                                        echo '0';
                                                                    } else {
                                                                        // debug($val);
                                                                         //edited by athira on 24-10-2025
                                                                        echo round($val['yearlybalance'], 1);
                                                                        //end
                                                                    } ?></td>

                                                        </tr>
                                                        <?php
                                                    } else {
                                                        foreach ($val as $subval) {
                                                        ?>
                                                            <tr>
                                                                <?php
                                                                $leavetaken = $subval['lp']['alloted_leave_forthe_year'] - $subval['0']['leavebalance'];
                                                                if ($subval['lp']['leave_policy_type'] == 'Y') {
                                                                    $type = 'Yearly';
                                                                } else if ($subval['lp']['leave_policy_type'] == 'M') {
                                                                    $type = 'Monthly';
                                                                } else {
                                                                    $type = 'Present Days';
                                                                }
                                                                $terminate = isset($subval['0']['terminate']) ? $subval['0']['terminate'] : 0;
                                                                $limit = isset($subval['0']['encashed_leave']) ? $subval['0']['encashed_leave'] : 0;

                                                                ?>
                                                                <td><?php echo $i++; ?></td>
                                                                <td><?php echo $subval['0']['emp_name']; ?><?php echo isset($subval['ed']['status']) && $subval['ed']['status'] == "2" ? '(Resigned)' : ''; ?></td>
                                                                <!-- edited by athira on 07-07-2025 -->
                                                                 <?php if ($company_code =='DEMO' || $company_code=='GLET' || $company_code=='SRTS') {?>
                                                                    <td><?php echo $subval['info']['EmpUSName']; ?></td>
                                                            <?php }?>
                                                            <!-- end -->
                                                                <td><?php echo $subval['info']['employee_id']; ?></td>
                                                                <!-- edited by athira on 07-07-2025 -->
                                                                 <?php if ($company_code =='DEMO' || $company_code=='GLET' || $company_code=='SRTS') {?>
                                                                    <td><?php echo $subval['info']['emp_us_id']; ?></td>
                                                            <?php }?>
                                                            <!-- end -->
                                                                <?php $joiningDate = $subval['info']['joining_date'];
                                                                $join = date('d-m-Y', strtotime($joiningDate)); ?>
                                                                <td><?php echo $join; ?></td>
                                                                <td><?php echo $subval['info']['branch']; ?></td>
                                                                <td><?php echo $subval['info']['designation']; ?></td>
                                                                <td><?php echo $subval['info']['department']; ?></td>
                                                                <td><?php echo $subval['LeaveType']['leave_type']; ?></td>
                                                                <td><?php echo $type; ?></td>
                                                                <td> <?php if ($subval['lp']['leave_policy_type'] == 'P') {
                                                                            echo '0';
                                                                        } else {
                                                                            echo $subval['lp']['alloted_leave_forthe_year'];
                                                                        } ?></td>
                                                                <td> <?php echo round($subval['0']['carryforwarded'], 1); ?></td>
                                                                <td> <?php echo $subval['0']['leavetaken']; ?></td>
                                                                <td> <?php echo $limit; ?></td>
                                                                <!--                                                                <td> <?php if ($terminate != NULL && $terminate > 0 && $limit != NULL && $limit > 0) {
                                                                                                                                                echo $limit - $subval['0']['leavetaken'];
                                                                                                                                            } else {
                                                                                                                                                echo isset($subval['0']['encashed_leave']) ? $subval['0']['encashed_leave'] : '0';
                                                                                                                                            } ?></td>-->
                                                                <td> <?php if ($terminate != NULL && $terminate > 0 && $limit != NULL && $limit > 0) {
                                                                            echo '0';
                                                                        } else {
                                                                            echo round($subval['0']['leavebalance'], 1);
                                                                        } ?></td>

                                                                <td> <?php if ($terminate != NULL && $terminate > 0 && $limit != NULL && $limit > 0) {
                                                                            echo '0';
                                                                        } else {
                                                                            //edited by athira on 24-10-2025
                                                                            echo round($subval['yearlybalance'], 1);
                                                                            //end
                                                                        } ?></td>


                                                            </tr>
                                                <?php
                                                        }
                                                    }
                                                }
                                                ?>
                                            <?php } else { ?>
                                                <tr>
                                                    <td colspan="10">No Data found found under this Criteria </td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>

                                </fieldset>
                                <br>
                                <!-- <fieldset>
                                     <legend>Employee List</legend>
                                     <table class="table table-bordered">
                                         <thead>
                                           <tr>
                                               <th>Name</th>
                                               <th>Designation</th>
                                               <th>Branch</th>
                                           </tr>
                                         </thead>
                                         <tbody>
                                <?php $arr_data = $value['employees']; ?>
                                <?php if (count($arr_data) >= 0) { ?>
                                    <?php foreach ($arr_data as $val) { ?>
                                                <tr> 
                                                    <td><?php echo $val['EmployeeDetails']['first_name'] . ' ' . $val['EmployeeDetails']['last_name']; ?></td>
                                                    <td><?php echo $val['EmployeeProfessionalDetails']['designation']; ?></td>
                                                    <td><?php echo $val['Units']['branch_name']; ?></td>
                                                </tr>
                                    <?php } ?>
                                <?php } else { ?>
                                                        <tr>
                                                            <td colspan="4">No employees found under this shift</td>
                                                        </tr>  
                                <?php } ?>
                                         </tbody>
                                     </table>
                                 </fieldset> -->
                            </div>
                    <?php
                        }
                    }
                    ?> <!-- /.box-body -->
                </div>
            </div>
        </div>
        <!--    <div class="row">
                <div class="form-group">
                    <div class="col-md-12" align="right">
                        <a href="#" class="btn btn-default" onclick="downloadReport('LeaveBalance','pdf');" ><i class="icon-file"></i>Download As PDF</a>
                        <a href="#" class="btn btn-default" onclick="downloadReport('LeaveBalance','excel');"><i class="icon-file"></i>Download As Excel</a>
                    </div>
                </div>
            </div>-->
    </div>
    <script>
        $(document).ready(function() {

            $('.todayattandence').DataTable({
                "paging": false,
                "lengthChange": false,
                "searching": false,
                "ordering": true,
                //            dom: 'Bfrtip',
                //            buttons: [
                //                {
                //                    extend: 'print',
                //                    messageTop: 'My payroll Master Leave Detailed Report - <?php echo $dates; ?>',
                //                    messageBottom: null,
                //                    title: 'My Payroll Master - Leave Detailed Report'
                //                },
                //                {
                //                    extend: 'pdf',
                //                    messageTop: 'My payroll Master Leave Detailed Report - <?php echo $dates; ?>',
                //                    messageBottom: null,
                //                    title: 'My Payroll Master - Leave Detailed Report',
                //                    orientation: 'landscape',
                //                    pageSize: 'LEGAL',
                //                    footer: true
                //                },
                //                {
                //                    extend: 'excel',
                //                    messageTop: 'My payroll Master Leave Detailed Report - <?php echo $dates; ?>',
                //                    messageBottom: null,
                //                    title: 'My payroll Master - Leave Detailed Report',
                //                    customize: function(xlsx) {
                //                    var sheet = xlsx.xl.worksheets['sheet1.xml'];
                //
                //                    // Loop over the cells in column `C`
                //                    $('row c[r^="C"]', sheet).each( function () {
                //                        // Get the value
                //                        if ( $('is t', this).text() == 'Casual Leave' ) {
                //                            $(this).attr( 's', '20' );
                //                        }
                //                    });
                //                            }
                //                }
                //            ],
                "info": false,
                "autoWidth": false,
                "initComplete": function() {
                    //actions
                },
                //             "createdRow": function ( row, data, index ) {
                //                if ( data[5].replace(/[\$,]/g, '') * 1 > 150000 ) {
                //                    $('td', row).eq(5).addClass('highlight');
                //                }
                //            },
                "scrollCollapse": true,
            });


            $('.buttons-print').ready(function() {
                $('.buttons-print').html('<li class="fa fa-print"></li>').addClass('btn-primary').addClass('btn');;
            });
            $('.buttons-pdf').html('<li class="fa fa-file-pdf-o"></li>').addClass('btn-danger').addClass('btn');
            $('.buttons-excel').html('<li class="fa fa-file-excel-o"></li>').addClass('btn-success').addClass('btn');
        });
    </script>
<?php } else { ?>
    <!-- edited by athira 10-10-2025 end -->
    <?php //echo '<style>'.file_get_contents("css/pdfbootstrap.css").'</style>';    
    ?>
    <style type="text/css">
        body {
            line-height: 2em;
        }

        .block-container {
            width: 95%;
            padding: 20px;
            border: #000000 solid thin;
        }

        .sub-head {
            border-bottom: #000000 solid thin;
        }

        .row {
            height: 32px;
        }

        .col-md-4 {
            width: 33.33%;
            float: left;
        }

        table {
            border: 1px solid #f4f4f4;
            width: 80%;
            max-width: 80%;
            margin-bottom: 20px;
            background-color: transparent;
            border-spacing: 0;
            /*width: 100%;*/
            table-layout: fixed;
            border-collapse: collapse;
        }

        td,
        th {
            text-align: left;
            padding: 8px;
            line-height: 1.42857143;
            vertical-align: top;
            font-size: 10px;
            border: 1px solid #B2B2B2;



        }

        .break {
            word-break: break-word;
            width: 40px;
        }
    </style>

    <?php
    echo $this->element('reportadminheader', array(
        'title' => 'Employees Leave Balance Report - ' . $dates
    ));
    ?>
    <h4 align="center" style="font-weight:bold;">(<?php echo isset($user_id) ? "Report run by " . ($user_id) . " - " . $date_time : ''; ?>)</h4>
    <?php
    $i = 0;
    if (count($arr_leavepolicydetails_for_template) != 0) {
        foreach ($arr_leavepolicydetails_for_template as $value) {
            $i += 1;
            if (count($value['summary']) > 0) {
    ?>
                <bookmark>

                    <h4> Employees Leave Balance Report
                        <?php
                        if ($cr == 'Departments') {
                            echo isset($value['summary']['0']['Departments']['dept_name']) ? $value['summary'][0]['Departments']['dept_name'] : '';
                        } else if ($cr == 'Units') {
                            echo isset($value['summary'][0]['Units']['branch_name']) ? $value['summary'][0]['Units']['branch_name'] : '';
                        } else if ($cr == 'EmployeeDetails') {
                            echo isset($value['summary'][0]['0']['emp_name']) ? $value['summary'][0]['0']['emp_name'] : '';
                        } else {
                            echo isset($value['summary'][0][0]['LeaveType']['leave_type']) ? $value['summary'][0][0]['LeaveType']['leave_type'] : '';
                        }
                        ?> </h4>

                    <table>
                        <thead>
                            <tr>
                                <th>Sl No </th>
                                <th>Employee Name</th>
                                <th>Employee ID</th>
                                <th>Date Of Joining</th>
                                <th>Branch</th>
                                <th>Designation</th>
                                <th>Department</th>
                                <!--    <th>LEAVEPOLICY_GROUP_NAME</th> -->

                                <th>Leave Type</th>
                                <th><?php echo str_replace(' ', '<br>', 'Leave Policy'); ?></th>
                                <!--edited by sinsiya-->
                                <th>
                                    <?php echo str_replace(' ', '<br>', 'Alloted Leave for the year'); ?>
                                </th>

                                <th><?php echo str_replace(' ', '<br>', 'Carry Forwarded'); ?></th>
                                <th><?php echo str_replace(' ', '<br>', 'Leave Taken'); ?></th>
                                <th><?php echo str_replace(' ', '<br>', 'Encashed Leaves'); ?></th>
                                <th><?php echo str_replace(' ', '<br>', 'Leave Balance'); ?></th>



                            </tr>
                        </thead>
                        <tbody>
                            <?php $arr_data = $value['summary']; ?>
                            <?php if (count($arr_data) > 0) { ?>
                                <?php $i = 1; ?>
                                <?php
                                foreach ($arr_data as $val) {
                                    //                                                    debug($val);
                                    if ($cr != "LeaveType") {
                                ?>

                                        <tr>
                                            <?php
                                            $leavetaken = $val['lp']['alloted_leave_forthe_year'] - $val['0']['leavebalance'];
                                            if ($val['lp']['leave_policy_type'] == 'Y') {
                                                $type = 'Yearly';
                                            } else if ($val['lp']['leave_policy_type'] == 'M') {
                                                $type = 'Monthly';
                                            } else {
                                                $type = 'Present Days';
                                            }
                                            $terminate = isset($val['0']['terminate']) ? $val['0']['terminate'] : 0;
                                            $limit = isset($val['0']['encashed_leave']) ? $val['0']['encashed_leave'] : 0;
                                            ?>
                                            <td><?php echo $i++; ?></td>
                                            <td class="break"><?php echo $val['0']['emp_name']; ?><?php echo isset($val['ed']['status']) && $val['ed']['status'] == "2" ? '(Resigned)' : ''; ?></td>
                                            <td><?php echo $val['info']['employee_id']; ?></td>
                                            <?php $joiningDate = $val['info']['joining_date'];
                                            $join = date('d-m-Y', strtotime($joiningDate)); ?>
                                            <td><?php echo $join; ?></td>
                                            <td class="break" style="width:70px;"><?php echo $val['info']['branch']; ?></td>
                                            <td class="break"><?php echo $val['info']['designation']; ?></td>
                                            <td class="break"><?php echo $val['info']['department']; ?></td>
                                            <td class="break" style="width:50px;"><?php echo $val['LeaveType']['leave_type']; ?></td>
                                            <td><?php echo $type; ?></td>
                                            <td> <?php if ($val['lp']['leave_policy_type'] == 'P') {
                                                        echo '0';
                                                    } else {
                                                        echo $val['lp']['alloted_leave_forthe_year'];
                                                    } ?></td>
                                            <td> <?php echo round($val['0']['carryforwarded'], 1); ?></td>
                                            <td> <?php echo $val['0']['leavetaken']; ?></td>
                                            <td> <?php echo $limit; ?></td>
                                            <!--                                            <td> <?php if ($terminate != NULL && $terminate > 0 && $limit != NULL && $limit > 0) {
                                                                                                        echo $limit - $val['0']['leavetaken'];
                                                                                                    } else {
                                                                                                        echo isset($val['0']['encashed_leave']) ? $val['0']['encashed_leave'] : '0';
                                                                                                    } ?></td>-->
                                            <td> <?php if ($terminate != NULL && $terminate > 0 && $limit != NULL && $limit > 0) {
                                                        echo '0';
                                                    } else {
                                                        echo round($val['0']['leavebalance'], 1);
                                                    } ?></td>

                                        </tr>
                                        <?php
                                    } else {
                                        foreach ($val as $subval) {
                                        ?>
                                            <tr>
                                                <?php
                                                $leavetaken = $subval['lp']['alloted_leave_forthe_year'] - $subval['0']['leavebalance'];
                                                if ($subval['lp']['leave_policy_type'] == 'Y') {
                                                    $type = 'Yearly';
                                                } else if ($subval['lp']['leave_policy_type'] == 'M') {
                                                    $type = 'Monthly';
                                                } else {
                                                    $type = 'Present Days';
                                                }
                                                $terminate = isset($subval['0']['terminate']) ? $subval['0']['terminate'] : 0;
                                                $limit = isset($subval['0']['encashed_leave']) ? $subval['0']['encashed_leave'] : 0;
                                                ?>
                                                <td><?php echo $i++; ?></td>
                                                <td style="word-break: break-word; width:20px;">
                                                    <?php echo $subval['0']['emp_name']; ?><?php echo isset($subval['ed']['status']) && $subval['ed']['status'] == "2" ? '(Resigned)' : ''; ?>
                                                </td>


                                                <td><?php echo $subval['info']['employee_id']; ?></td>
                                                <?php $joiningDate = $subval['info']['joining_date'];
                                                $join = date('d-m-Y', strtotime($joiningDate)); ?>
                                                <td><?php echo $join; ?></td>
                                                <td><?php echo $subval['info']['branch']; ?></td>
                                                <td><?php echo $subval['info']['designation']; ?></td>
                                                <td><?php echo $subval['info']['department']; ?></td>
                                                <td><?php echo $subval['LeaveType']['leave_type']; ?></td>
                                                <td><?php echo $type; ?></td>
                                                <td> <?php if ($subval['lp']['leave_policy_type'] == 'P') {
                                                            echo '0';
                                                        } else {
                                                            echo $subval['lp']['alloted_leave_forthe_year'];
                                                        } ?></td>
                                                <td> <?php echo round($subval['0']['carryforwarded'], 1); ?></td>
                                                <td> <?php echo $subval['0']['leavetaken']; ?></td>
                                                <td> <?php echo $limit; ?></td>
                                                <!--                                                <td> <?php if ($terminate != NULL && $terminate > 0 && $limit != NULL && $limit > 0) {
                                                                                                                echo $limit - $subval['0']['leavetaken'];
                                                                                                            } else {
                                                                                                                echo isset($subval['0']['encashed_leave']) ? $subval['0']['encashed_leave'] : '0';
                                                                                                            } ?></td>-->
                                                <td> <?php if ($terminate != NULL && $terminate > 0 && $limit != NULL && $limit > 0) {
                                                            echo '0';
                                                        } else {
                                                            echo round($subval['0']['leavebalance'], 1);
                                                        } ?></td>

                                            </tr>
                                <?php
                                        }
                                    }
                                }
                                ?>
                            <?php } else { ?>
                                <tr>
                                    <td colspan="10">No Data found found under this Criteria </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                    <br>
                </bookmark>
    <?php
            }
        }
    } else {
        echo "<h3>No Data Available With The Selected Criteria</h3>";
    }
    ?> <!-- /.box-body -->

<?php } ?>