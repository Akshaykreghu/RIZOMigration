<?php if ($mode == '') { ?>
    <div class="modal-body" style="overflow-y: auto;">
        <legend>Employees Leave Detailed Reports &nbsp;&nbsp; <?php echo ' ' . $dates; ?></legend>
        <div class="row">
            <div class="col-md-12">
                <div class=" ">
                    <?php
                    if (count(isset($arr_leavesummary_for_template['leaverequests']) ? $arr_leavesummary_for_template['leaverequests'] : 0) == 0) {
                        echo '<fieldset style="text-align: center; ">
                            <legend>No Record Found Under This Criteria</legend></fieldset>';
                        return false;
                    }
                    //edited by athira on 17-06-2025
                    if (empty($arr_leavesummary_for_template)) {
                        echo '<fieldset style="text-align: center; ">
                            <legend>No Record Found Under This Criteria</legend></fieldset>';
                    }
                    //end
                    ?>
                    <?php if (isset($needBranchWiseReport) && $needBranchWiseReport == true) { //do branchwise listing 
                    ?>
                        <?php foreach ($arr_leavesummary_for_template as $branch_code => $leavesummary) { ?>
                            <div class="" style="overflow-y: auto;">

                                <h2 style="font-weight: bold ; "><?php echo $leavesummary['branch_name'] . ' Branch '; ?></h2>

                                <br>
                                <fieldset>
                                    <table class="table table-bordered todayattandence" id="todayattandence">
                                        <thead>
                                            <tr>
                                                <!--                                                belonginng to branch-->
                                                <th>Sl No </th>
                                                <th>Employee Name</th>
                                                 <!-- edited by athira on 07-07-2025 -->
                                                 <?php if ($company_code =='DEMO' || $company_code=='GLET' || $company_code=='SRTS') {?>
                                                 <th>Employee Name (US Format)</th>
                                                 <?php }?>
                                                 <!-- end -->
                                                <th>Employee ID</th>
                                                <!-- edited by athira on 07-07-2025 -->
                                                 <?php if ($company_code =='DEMO' || $company_code=='GLET' || $company_code=='SRTS') {?>
                                                 <th>Employee ID (US Format)</th>
                                                 <?php } ?>
                                                 <!-- end -->
                                                <!--<th>Designation</th>-->
                                                <th>Date Of Join</th>
                                                <th>Branch</th>
                                                <th>Department </th>
                                                <th>Applied Date</th>
                                                <th>From Date</th>
                                                <th>To Date</th>
                                                <th>Reason</th>
                                                <th>Contact Person</th>
                                                <!--  remarks added by megha on 26/09/2019-->
                                                <th>Authorized By</th>
                                                <th>Authorized Person Remarks</th>
                                                <th>Authorized Date</th>
                                                <th>Approved By</th>
                                                <th>Approved Person Remarks</th>
                                                <th>Approved Date</th>
                                                <th>Rejected By</th>
                                                <th>Rejected Person Remarks</th>
                                                <th>Rejected Date</th>
                                                <!-- end  -->
                                                <th>Leave Type</th>
                                                <!-- <th>Planned days </th> -->
                                                <th>Leave Days</th>
                                                <th>Leave status</th>

                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $arr_data = $leavesummary['leaverequests']; ?>
                                            <?php if (count($arr_data) > 0) {
                                                $count = 0; ?>
                                                <?php foreach ($arr_data as $val) {  ?>
                                                    <tr>
                                                        <?php
                                                        if ($val['fromhalf'] == '1') {
                                                            $fromhalf = "First Half";
                                                        } else if ($val['fromhalf'] == '2') {
                                                            $fromhalf = "Second Half";
                                                        } else {
                                                            $fromhalf = "";
                                                        }

                                                        if ($val['tohalf'] == '1') {
                                                            $tohalf = "First Half";
                                                        } else if ($val['tohalf'] == '2') {
                                                            $tohalf = "Second Half";
                                                        } else {
                                                            $tohalf = "";
                                                        }
                                                        ?>
                                                        <?php $count = $count + 1; ?>
                                                        <td><?php echo $count; ?></td>
                                                        <td><?php echo $val['emp_name']; ?><?php echo $val['status']; ?></td>
                                                        <!-- edited by athira on 07-07-2025 -->
                                                         <?php if ($company_code =='DEMO' || $company_code=='GLET' || $company_code=='SRTS') {?>
                                                            <td><?php echo $val['EmpUSName']; ?></td>
                                                            <?php }?>
                                                        <!-- end -->
                                                        <td><?php echo $val['employee_id']; ?></td>
                                                         <!-- edited by athira on 07-07-2025 -->
                                                        <?php if ($company_code =='DEMO' || $company_code=='GLET' || $company_code=='SRTS') {?>
                                                            <td><?php echo $val['emp_us_id']; ?></td>
                                                            <?php }?>
                                                        <!-- end -->
                                                        <!--<td><?php echo $val['designation']; ?></td>-->
                                                        <td><?php echo $val['joining_date']; ?></td>
                                                        <td><?php echo $val['branch']; ?></td>
                                                        <td><?php echo $val['department']; ?></td>
                                                        <td><?php echo $val['leave_applied_on']; ?></td>
                                                        <td> <?php echo $val['leave_from'] . " " . $fromhalf; ?></td>
                                                        <td> <?php echo $val['leave_to'] . " " . $tohalf; ?></td>
                                                        <td><?php echo $val['Reason']; ?></td>
                                                        <td><?php echo $val['contact_person']; ?></td>
                                                        <!--  remarks added by megha on 26/09/2019-->

                                                        <td><?php echo $val['Authorized_name']; ?></td>
                                                        <td><?php echo $val['Authorized_remarks']; ?></td>
                                                        <td><?php echo $val['Autherized_date']; ?></td>
                                                        <td><?php echo $val['Approved_name']; ?></td>
                                                        <?php if ($val['leave_status'] != 'Rejected') { ?>
                                                            <td><?php echo $val['Approved_remarks']; ?></td>
                                                            <td><?php echo $val['APPROVED_date']; ?></td>
                                                        <?php } else { ?>
                                                            <!--                                                <td><?php echo ""; ?></td>
                                                    <td><?php echo ""; ?></td>
                                                    <td><?php echo ""; ?></td>
                                                    <td><?php echo ""; ?></td>-->
                                                            <td><?php echo ""; ?></td>
                                                            <td><?php echo ""; ?></td>
                                                        <?php  }
                                                        if ($val['leave_status'] == 'Rejected') { ?>
                                                            <td><?php if ($val['APPROVED_date'] == '') {
                                                                    echo $val['Authorized_name'];
                                                                } else {
                                                                    echo $val['Approved_name'];
                                                                } ?></td>
                                                            <td><?php if ($val['APPROVED_date'] == '') {
                                                                    echo $val['Authorized_remarks'];
                                                                } else {
                                                                    echo $val['Approved_remarks'];
                                                                } ?></td>
                                                            <td><?php if ($val['APPROVED_date'] == '') {
                                                                    echo $val['Autherized_date'];
                                                                } else {
                                                                    echo $val['APPROVED_date'];
                                                                } ?></td>
                                                        <?php } else { ?>
                                                            <td><?php echo ""; ?></td>
                                                            <td><?php echo ""; ?></td>
                                                            <td><?php echo ""; ?></td>
                                                        <?php } ?>
                                                        <!-- end  -->
                                                        <td> <?php echo $val['leave_type']; ?></td>
                                                        <!-- <td> <?php echo $val['planned_days']; ?></td> -->
                                                        <td> <?php echo $val['leavedays']; ?></td>
                                                        <td> <?php echo $val['leave_status']; ?></td>

                                                    </tr>
                                                <?php } ?>
                                            <?php } else { ?>
                                                <tr>
                                                    <td colspan="12">No employees found under this branch.</td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </fieldset>
                            </div>
                        <?php } //end foreach  
                        ?>
                    <?php } else { ?>
                        <div class="box-body">
                            <fieldset>
                                <table class="table table-bordered todayattandence" id="todayattandence">
                                    <thead>
                                        <tr>

                                            <!--                                            Belonging to leave type and leave status and employee-->
                                            <th>Sl No </th>
                                            <th>Employee Name</th>
                                            <!-- edited by athira on 07-07-2025 -->
                                            <?php if ($company_code =='DEMO' || $company_code=='GLET' || $company_code=='SRTS') {?>
                                                <th>Employee Name (US Format)</th>
                                                            <?php }?>
                                                <!-- end -->
                                            <th>Employee ID</th>
                                            <!-- edited by athira on 07-07-2025 -->
                                            <?php if ($company_code =='DEMO' || $company_code=='GLET' || $company_code=='SRTS') {?>
                                                <th>Employee ID (US Format)</th>
                                                            <?php }?>
                                                <!-- end -->
                                            <!--<th>Designation</th>-->
                                            <th>Date Of Join</th>
                                            <th>Branch</th>
                                            <th>Department </th>
                                            <th>Applied Date</th>
                                            <th>From Date</th>
                                            <th>To Date</th>
                                            <th>Reason</th>
                                            <th>Contact Person</th>
                                            <!--  remarks added by megha on 26/09/2019-->
                                            <th>Authorized By</th>
                                            <th>Authorized Person Remarks</th>
                                            <th>Authorized Date</th>
                                            <th>Approved By</th>
                                            <th>Approved Person Remarks</th>
                                            <th>Approved Date</th>
                                            <th>Rejected By</th>
                                            <th>Rejected Person Remarks</th>
                                            <th>Rejected Date</th>
                                            <!-- end  -->
                                            <th>Leave Type</th>
                                            <!-- <th>Planned days </th> -->
                                            <th>Leave Days</th>
                                            <th>Leave status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $arr_data = $arr_leavesummary_for_template['leaverequests']; ?>
                                        <?php if (count($arr_data) > 0) {
                                            $count = 0; ?>
                                            <?php foreach ($arr_data as $val) { ?>
                                                <tr>
                                                    <?php
                                                    if ($val['fromhalf'] == '1') {
                                                        $fromhalf = "First Half";
                                                    } else if ($val['fromhalf'] == '2') {
                                                        $fromhalf = "Second Half";
                                                    } else {
                                                        $fromhalf = "";
                                                    }

                                                    if ($val['tohalf'] == '1') {
                                                        $tohalf = "First Half";
                                                    } else if ($val['tohalf'] == '2') {
                                                        $tohalf = "Second Half";
                                                    } else {
                                                        $tohalf = "";
                                                    }
                                                    ?>
                                                    <?php $count = $count + 1; ?>
                                                    <td><?php echo $count; ?></td>
                                                    <td><?php echo $val['emp_name']; ?><?php echo $val['status']; ?></td>
                                                     <!-- edited by athira on 07-07-2025 -->
                                                    <?php if ($company_code =='DEMO' || $company_code=='GLET' || $company_code=='SRTS') {?>
                                                         <td><?php echo $val['EmpUSName']; ?></td>
                                                            <?php }?>
                                                        <!-- end -->
                                                    <td><?php echo $val['employee_id']; ?></td>
                                                    <!-- edited by athira on 07-07-2025 -->
                                                    <?php if ($company_code =='DEMO' || $company_code=='GLET' || $company_code=='SRTS') {?>
                                                         <td><?php echo $val['emp_us_id']; ?></td>
                                                            <?php }?>
                                                        <!-- end -->
                                                    <!--<td><?php echo $val['designation']; ?></td>-->
                                                    <td><?php echo $val['joining_date']; ?></td>
                                                    <td><?php echo $val['branch']; ?></td>
                                                    <td><?php echo $val['department']; ?></td>
                                                    <td><?php echo $val['leave_applied_on']; ?></td>
                                                    <td> <?php echo $val['leave_from'] . " " . $fromhalf; ?></td>
                                                    <td> <?php echo $val['leave_to'] . " " . $tohalf; ?></td>
                                                    <td><?php echo $val['Reason']; ?></td>
                                                    <td><?php echo $val['contact_person']; ?></td>
                                                    <!--  remarks added by megha on 26/09/2019-->

                                                    <td><?php echo $val['Authorized_name']; ?></td>
                                                    <td><?php echo $val['Authorized_remarks']; ?></td>
                                                    <td><?php echo $val['Autherized_date']; ?></td>
                                                    <td><?php echo $val['Approved_name']; ?></td>
                                                    <?php if ($val['leave_status'] != 'Rejected') { ?>
                                                        <td><?php echo $val['Approved_remarks']; ?></td>
                                                        <td><?php echo $val['APPROVED_date']; ?></td>
                                                    <?php } else { ?>
                                                        <!--                                                    <td><?php echo ""; ?></td>
                                                    <td><?php echo ""; ?></td>
                                                    <td><?php echo ""; ?></td>
                                                    <td><?php echo ""; ?></td>-->
                                                        <td><?php echo ""; ?></td>
                                                        <td><?php echo ""; ?></td>
                                                    <?php }
                                                    if ($val['leave_status'] == 'Rejected') { ?>
                                                        <td><?php if ($val['APPROVED_date'] == '') {
                                                                echo $val['Authorized_name'];
                                                            } else {
                                                                echo $val['Approved_name'];
                                                            } ?></td>
                                                        <td><?php if ($val['APPROVED_date'] == '') {
                                                                echo $val['Authorized_remarks'];
                                                            } else {
                                                                echo $val['Approved_remarks'];
                                                            } ?></td>
                                                        <td><?php if ($val['APPROVED_date'] == '') {
                                                                echo $val['Autherized_date'];
                                                            } else {
                                                                echo $val['APPROVED_date'];
                                                            } ?></td>
                                                    <?php } else { ?>
                                                        <td><?php echo ""; ?></td>
                                                        <td><?php echo ""; ?></td>
                                                        <td><?php echo ""; ?></td>
                                                    <?php } ?>
                                                    <!-- end  -->
                                                    <td> <?php echo $val['leave_type']; ?></td>
                                                    <!-- <td> <?php echo $val['planned_days']; ?></td> -->
                                                    <td> <?php echo $val['leavedays']; ?></td>
                                                    <td> <?php echo $val['leave_status']; ?></td>
                                                </tr>
                                            <?php } ?>
                                        <?php } else { ?>
                                            <tr>
                                                <td colspan="12">No employees found under this branch.</td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </fieldset>
                        </div>
                    <?php } ?> <!-- /.box-body -->
                </div>
            </div>
        </div>
        <!--    <div class="row">
                <div class="form-group">
                    <div class="col-md-12" align="right">
                        <a href="#" class="btn btn-default" onclick="downloadReport('LeaveSummary', 'pdf');" ><i class="icon-file"></i>Download As PDF</a>
                        <a href="#" class="btn btn-default" onclick="downloadReport('LeaveSummary','excel');"><i class="icon-file"></i>Download As Excel</a>
                    </div>
                </div>
            </div>-->
    </div>
    <script>
        $(document).ready(function() {

            $('.todayattandence').DataTable({
                "paging": true,
                "lengthChange": false,
                "searching": true,
                //                "ordering": true,
                //                dom: 'Bfrtip',
                //                buttons: [
                //                    {
                //                        extend: 'print',
                //                        messageTop: 'My payroll Master Leave Detailed Report - ',
                //                        messageBottom: null,
                //                        title: 'My Payroll Master - Leave Detailed Report'
                //                    },
                //                    {
                //                        extend: 'pdf',
                //                        messageTop: 'My payroll Master Leave Detailed Report - ',
                //                        messageBottom: null,
                //                        title: 'My Payroll Master - Leave Detailed Report',
                //                        orientation: 'landscape',
                //                        pageSize: 'LEGAL',
                //                        footer: true
                //                    },
                //                    {
                //                        extend: 'excel',
                //                        messageTop: 'My payroll Master Leave Detailed Report - ',
                //                        messageBottom: null,
                //                        title: 'My payroll Master - Leave Detailed Report',
                //                        customize: function(xlsx) {
                //                        var sheet = xlsx.xl.worksheets['sheet1.xml'];
                //
                //                        // Loop over the cells in column `C`
                //                        $('row c[r^="C"]', sheet).each( function () {
                //                            // Get the value
                //                            if ( $('is t', this).text() == 'Casual Leave' ) {
                //                                $(this).attr( 's', '20' );
                //                            }
                //                        });
                //                                }
                //                    }
                //                ],
                "info": true,
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
            $('.buttons-pdf').html('<li class="fa fa-file-pdf-o"></li>').addClass('btn-danger').addClass('btn');;
            $('.buttons-excel').html('<li class="fa fa-file-excel-o"></li>').addClass('btn-success').addClass('btn');
        });
    </script>
<?php } else { ?>
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
            border-collapse: collapse;
        }

        td,
        th {
            text-align: left;
            padding: 8px;
            line-height: 1.42857143;
            vertical-align: top;
            font-size: 9px;
            border: 1px solid #B2B2B2;
        }
    </style>
    <?php
    echo $this->element('reportadminheader', array(
        'title' => 'Employees Leave Detailed Reports'
    ));
    ?>
    <?php
    if (count($arr_leavesummary_for_template) == 0) {
        echo '<fieldset style="text-align: center; ">
                                            <legend>No Record Found Under This Criteria</legend></fieldset>';
    }
    ?>
    <?php if (isset($needBranchWiseReport) && $needBranchWiseReport == true) { //do branchwise listing  
    ?>
        <?php foreach ($arr_leavesummary_for_template as $branch_code => $leavesummary) { ?>
            <h4><?php echo $leavesummary['branch_name'] . ' Branch'; ?></h4>

            <table>
                <thead>
                    <tr>
                        <th>Sl. No </th>
                        <th style="width:40px;">Employee Name</th>
                        <th style="width:30px;">Employee ID</th>
                        <!--<th>Designation</th>-->
                        <th style="width:30px;">Date Of Join</th>
                        <th style="width:30px;">Branch</th>
                        <th style="width:30px;">Department</th>
                        <th style="width:30px;">Applied Date</th>
                        <th style="width:40px;">From Date</th>
                        <th style="width:40px;">To Date</th>
                        <th>Reason</th>
                        <th>Contact Person</th>
                        <!--  remarks added by megha on 26/09/2019-->
                        <th style="width:40px;">Authorized By</th>
                        <th style="width:50px;">Authorized Person Remarks</th>
                        <th style="width:40px;">Authorized Date</th>
                        <th style="width:40px;">Approved By</th>
                        <th style="width:50px;">Approved Person Remarks</th>
                        <th style="width:40px;">Approved Date</th>
                        <th style="width:40px;">Rejected By</th>
                        <th style="width:50px;">Rejected Person Remarks</th>
                        <th style="width:40px;">Rejected Date</th>
                        <!-- end  -->
                        <th>Leave Type</th>
                        <!-- <th>Planned days </th> -->
                        <th style="width:20px;">Leave Days</th>
                        <th style="width:40px;">Leave status</th>

                    </tr>
                </thead>
                <tbody>
                    <?php $arr_data = $leavesummary['leaverequests']; ?>
                    <?php if (count($arr_data) > 0) {
                        $count = 0;  ?>
                        <?php foreach ($arr_data as $val) { ?>
                            <tr>
                                <?php
                                if ($val['fromhalf'] == '1') {
                                    $fromhalf = "First Half";
                                } else if ($val['fromhalf'] == '2') {
                                    $fromhalf = "Second Half";
                                } else {
                                    $fromhalf = "";
                                }

                                if ($val['tohalf'] == '1') {
                                    $tohalf = "First Half";
                                } else if ($val['tohalf'] == '2') {
                                    $tohalf = "Second Half";
                                } else {
                                    $tohalf = "";
                                }
                                ?>

                                <?php $count = $count + 1; ?>
                                <td><?php echo $count; ?></td>
                                <td style="width:40px;"><?php echo $val['emp_name']; ?></td>
                                <td style="width:30px;"><?php echo $val['employee_id']; ?></td>
                                <!--<td><?php echo $val['designation']; ?></td>-->
                                <td style="width:30px;"><?php echo $val['joining_date']; ?></td>
                                <td style="width:30px;"><?php echo $val['branch']; ?></td>
                                <td style="width:30px;"><?php echo $val['department']; ?></td>
                                <td style="width:30px;"><?php echo $val['leave_applied_on']; ?></td>
                                <td style="width:40px;"> <?php echo $val['leave_from'] . " " . $fromhalf;; ?></td>
                                <td style="width:40px;"> <?php echo $val['leave_to'] . " " . $tohalf; ?></td>
                                <td><?php echo $val['Reason']; ?></td>
                                <td><?php echo $val['contact_person']; ?></td>
                                <!--  remarks added by megha on 26/09/2019-->
                                <?php //if($val['leave_status'] != 'Rejected'){ 
                                ?>
                                <td style="width:40px;"><?php echo $val['Authorized_name']; ?></td>
                                <td style="width:50px;"><?php echo $val['Authorized_remarks']; ?></td>
                                <td style="width:40px;"><?php echo $val['Autherized_date']; ?></td>
                                <td style="width:40px;"><?php echo $val['Approved_name']; ?></td>
                                <td style="width:50px;"><?php echo $val['Approved_remarks']; ?></td>
                                <td style="width:40px;"><?php echo $val['APPROVED_date']; ?></td>
                                <?php //} else { 
                                ?>
                                <!--                                                <td><?php echo ""; ?></td>
                                                    <td><?php echo ""; ?></td>
                                                    <td><?php echo ""; ?></td>
                                                    <td><?php echo ""; ?></td>
                                                    <td><?php echo ""; ?></td>
                                                    <td><?php echo  ""; ?></td>-->
                                <?php //}
                                if ($val['leave_status'] == 'Rejected') { ?>
                                    <td style="width:40px;"><?php if ($val['APPROVED_date'] == '') {
                                                                echo $val['Authorized_name'];
                                                            } else {
                                                                echo $val['Approved_name'];
                                                            } ?></td>
                                    <td style="width:50px;"><?php if ($val['APPROVED_date'] == '') {
                                                                echo $val['Authorized_remarks'];
                                                            } else {
                                                                echo $val['Approved_remarks'];
                                                            } ?></td>
                                    <td style="width:40px;"><?php if ($val['APPROVED_date'] == '') {
                                                                echo $val['Autherized_date'];
                                                            } else {
                                                                echo $val['APPROVED_date'];
                                                            } ?></td>
                                <?php } else { ?>
                                    <td><?php echo ""; ?></td>
                                    <td><?php echo ""; ?></td>
                                    <td><?php echo ""; ?></td>
                                <?php } ?>
                                <!-- end  -->
                                <td> <?php echo $val['leave_type']; ?></td>
                                <!-- <td> <?php echo $val['planned_days']; ?></td> -->
                                <td style="width:20px;"> <?php echo $val['leavedays']; ?></td>
                                <td style="width:40px;"> <?php echo $val['leave_status']; ?></td>
                            </tr>
                        <?php } ?>
                    <?php } else { ?>
                        <tr>
                            <td colspan="12">No employees found under this branch.</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>


        <?php } //end foreach 
        ?>
    <?php } else { ?>

        <table class="table">
            <thead>
                <tr>
                    <th>Sl no </th>
                    <th style="width:40px;">Employee Name</th>
                    <th style="width:30px;">Employee ID</th>
                    <!--<th>Designation</th>-->
                    <th style="width:30px;">Date Of Join</th>
                    <th style="width:30px;">Branch</th>
                    <th style="width:30px;">Departments</th>
                    <th style="width:30px;">Applied Date</th>
                    <th style="width:40px;">From Date</th>
                    <th style="width:40px;">To Date</th>
                    <th>Reason</th>
                    <th>Contact Person</th>
                    <!--  remarks added by megha on 26/09/2019-->
                    <th style="width:40px;">Authorized By</th>
                    <th style="width:50px;">Authorized Person Remarks</th>
                    <th style="width:40px;">Authorized Date</th>
                    <th style="width:40px;">Approved By</th>
                    <th style="width:50px;">Approved Person Remarks</th>
                    <th style="width:40px;">Approved Date</th>
                    <th style="width:40px;">Rejected By</th>
                    <th style="width:50px;">Rejected Person Remarks</th>
                    <th style="width:40px;">Rejected Date</th>
                    <!-- end  -->
                    <th>Leave Type</th>
                    <!-- <th>Planned days </th> -->
                    <th style="width:20px;">Leave Days</th>
                    <th style="width:40px;">Leave status</th>
                </tr>
            </thead>
            <tbody>
                <?php $arr_data = $arr_leavesummary_for_template['leaverequests']; ?>
                <?php if (count($arr_data) > 0) {
                    $count = 0;  ?>
                    <?php foreach ($arr_data as $val) { ?>
                        <tr>
                            <?php
                            if ($val['fromhalf'] == '1') {
                                $fromhalf = "First Half";
                            } else if ($val['fromhalf'] == '2') {
                                $fromhalf = "Second Half";
                            } else {
                                $fromhalf = "";
                            }

                            if ($val['tohalf'] == '1') {
                                $tohalf = "First Half";
                            } else if ($val['tohalf'] == '2') {
                                $tohalf = "Second Half";
                            } else {
                                $tohalf = "";
                            }
                            ?>

                            <?php $count = $count + 1; ?>
                            <td><?php echo $count; ?></td>
                            <td style="width:40px;"><?php echo $val['emp_name']; ?></td>
                            <td style="width:30px;"><?php echo $val['employee_id']; ?></td>
                            <!--<td><?php echo $val['designation']; ?></td>-->
                            <td style="width:30px;"><?php echo $val['joining_date']; ?></td>
                            <td style="width:30px;"><?php echo $val['branch']; ?></td>
                            <td style="width:30px;"><?php echo $val['department']; ?></td>
                            <td style="width:30px;"><?php echo $val['leave_applied_on']; ?></td>
                            <td style="width:40px;"> <?php echo $val['leave_from'] . " " . $fromhalf;; ?></td>
                            <td style="width:40px;"> <?php echo $val['leave_to'] . " " . $tohalf; ?></td>
                            <td><?php echo $val['Reason']; ?></td>
                            <td><?php echo $val['contact_person']; ?></td>
                            <!--  remarks added by megha on 26/09/2019-->
                            <?php if ($val['leave_status'] != 'Rejected') { ?>
                                <td style="width:40px;"><?php echo $val['Authorized_name']; ?></td>
                                <td style="width:50px;"><?php echo $val['Authorized_remarks']; ?></td>
                                <td style="width:40px;"><?php echo $val['Autherized_date']; ?></td>
                                <td style="width:40px;"><?php echo $val['Approved_name']; ?></td>
                                <td style="width:50px;"><?php echo $val['Approved_remarks']; ?></td>
                                <td style="width:40px;"><?php echo $val['APPROVED_date']; ?></td>
                            <?php } else { ?>
                                <td><?php echo ""; ?></td>
                                <td><?php echo ""; ?></td>
                                <td><?php echo ""; ?></td>
                                <td><?php echo ""; ?></td>
                                <td><?php echo ""; ?></td>
                                <td><?php echo ""; ?></td>
                            <?php }
                            if ($val['leave_status'] == 'Rejected') { ?>
                                <td style="width:40px;"><?php if ($val['APPROVED_date'] == '') {
                                                            echo $val['Authorized_name'];
                                                        } else {
                                                            echo $val['Approved_name'];
                                                        } ?></td>
                                <td style="width:50px;"><?php if ($val['APPROVED_date'] == '') {
                                                            echo $val['Authorized_remarks'];
                                                        } else {
                                                            echo $val['Approved_remarks'];
                                                        } ?></td>
                                <td style="width:40px;"><?php if ($val['APPROVED_date'] == '') {
                                                            echo $val['Autherized_date'];
                                                        } else {
                                                            echo $val['APPROVED_date'];
                                                        } ?></td>
                            <?php } else { ?>
                                <td><?php echo ""; ?></td>
                                <td><?php echo ""; ?></td>
                                <td><?php echo ""; ?></td>
                            <?php } ?>
                            <!-- end  -->
                            <td> <?php echo $val['leave_type']; ?></td>
                            <!-- <td> <?php echo $val['planned_days']; ?></td> -->
                            <td style="width:20px;"> <?php echo $val['leavedays']; ?></td>
                            <td style="width:40px;"> <?php echo $val['leave_status']; ?></td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="12">No employees found under this branch.</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>


    <?php } ?> <!-- /.box-body -->
    
<?php }  ?>
