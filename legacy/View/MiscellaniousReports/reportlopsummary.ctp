<?php if ($mode == '') { ?>
    <div class="modal-body" style="overflow-y: auto;">
        <legend style="text-align:center;border:none;font-weight:bold;font-size:25px;margin-bottom:0px;">LOP Report &nbsp;&nbsp; -  &nbsp;&nbsp; <?php echo $dates; ?></legend>
        <div class="row">
            <div class="col-md-12">
                <div class=" ">
                    <?php
                    if (empty($arr_lopreport_for_template)) {
                        echo '<fieldset style="text-align: center; ">
                            <legend>No Record Found Under This Criteria</legend></fieldset>';
                        return false;
                    }
                    ?>
                    <?php if (isset($needBranchWiseReport) && $needBranchWiseReport == true) { ?>
                        <?php foreach ($arr_lopreport_for_template as $branch_code => $lopsummary) { ?>
                            <div class="" style="overflow-y: auto;">
                                <h2 style="font-weight: bold ;font-size:20px;"><?php echo $lopsummary['branch_name'] . ' Branch '; ?></h2>
                                <br>
                                <fieldset style="margin-top:-50px;">
                                    <table class="table table-bordered todayattandence">
                                        <thead>
                                            <tr>
                                                <th>Sl No </th>
                                                <th>Employee Name</th>
                                                <th>Employee ID</th>
                                                <th>Date Of Join</th>
                                                <th>Branch</th>
                                                <th>Department </th>
                                                <th>LOP Type</th>
                                                <th>Leave Type</th>
                                                <th>Applied Date</th>
                                                <th>From Date</th>
                                                <th>To Date</th>
                                                <th>LOP Days</th>
                                                <th>Reason</th>
                                                <th>Contact Person</th>
                                                <th>Authorized By</th>
                                                <th>Authorized Remarks</th>
                                                <th>Authorized Date</th>
                                                <th>Approved By</th>
                                                <th>Approved Remarks</th>
                                                <th>Approved Date</th>
                                                <th>Rejected By</th>
                                                <th>Rejected Remarks</th>
                                                <th>Rejected Date</th>
                                                <th>Status</th>
                                                <!-- <th>Source</th> -->
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $arr_data = $lopsummary['loprequests']; 
                                            $count = 0;
                                            foreach ($arr_data as $val) { 
                                                $count++;
                                                $empstatus = ($val['status'] == '2') ? ' (Resigned)' : '';
                                                $from_half = ($val['fromhalf'] == '1') ? "First Half" : (($val['fromhalf'] == '2') ? "Second Half" : "");
                                                $to_half = ($val['tohalf'] == '1') ? "First Half" : (($val['tohalf'] == '2') ? "Second Half" : "");
                                                
                                                $rej_by = ''; $rej_rem = ''; $rej_date = '';
                                                if ($val['leave_status'] == 'Rejected') {
                                                    $rej_by = empty($val['APPROVED_date']) ? $val['Authorized_name'] : $val['Approved_name'];
                                                    $rej_rem = empty($val['APPROVED_date']) ? $val['Authorized_remarks'] : $val['Approved_remarks'];
                                                    $rej_date = empty($val['APPROVED_date']) ? $val['Autherized_date'] : $val['APPROVED_date'];
                                                }
                                                $appr_rem = ($val['leave_status'] != 'Rejected') ? $val['Approved_remarks'] : '';
                                                $appr_date = ($val['leave_status'] != 'Rejected') ? $val['APPROVED_date'] : '';
                                            ?>
                                                <tr>
                                                    <td><?php echo $count; ?></td>
                                                    <td><?php echo h($val['emp_name']) . $empstatus; ?></td>
                                                    <td><?php echo h($val['employee_id']); ?></td>
                                                    <td><?php echo h($val['joining_date']); ?></td>
                                                    <td><?php echo h($val['branch']); ?></td>
                                                    <td><?php echo h($val['department']); ?></td>
                                                    <td>
                                                        <?php 
                                                        if ($val['lop_source'] == 'attendance') echo 'Indirect (Attendance)';
                                                        elseif ($val['occurance'] == 'LOP' && $val['item_part'] != 'Indirect') echo 'Direct / Policy LOP';
                                                        else echo 'Indirect LOP';
                                                        ?>
                                                    </td>
                                                    <td><?php echo h($val['leave_type']); ?></td>
                                                    <td><?php echo h($val['leave_applied_on']); ?></td>
                                                    <td><?php echo h($val['leave_from']) . " " . $from_half; ?></td>
                                                    <td><?php echo h($val['leave_to']) . " " . $to_half; ?></td>
                                                    <td><?php echo h($val['leavedays']); ?></td>
                                                    <td><?php echo h($val['Reason']); ?></td>
                                                    <td><?php echo h($val['contact_person']); ?></td>
                                                    <td><?php echo h($val['Authorized_name']); ?></td>
                                                    <td><?php echo h($val['Authorized_remarks']); ?></td>
                                                    <td><?php echo h($val['Autherized_date']); ?></td>
                                                    <td><?php echo h($val['Approved_name']); ?></td>
                                                    <td><?php echo h($appr_rem); ?></td>
                                                    <td><?php echo h($appr_date); ?></td>
                                                    <td><?php echo h($rej_by); ?></td>
                                                    <td><?php echo h($rej_rem); ?></td>
                                                    <td><?php echo h($rej_date); ?></td>
                                                    <td><?php echo h($val['leave_status']); ?></td>
                                                    <!-- <td><?php echo ($val['lop_source'] == 'attendance') ? 'Attendance Register' : 'Leave Entry'; ?></td> -->
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </fieldset>
                            </div>
                        <?php } ?>
                    <?php } else { ?>
                        <div class="box-body">
                            <fieldset>
                                <table class="table table-bordered todayattandence">
                                    <thead>
                                        <tr>
                                            <th>Sl No </th>
                                            <th>Employee Name</th>
                                            <th>Employee ID</th>
                                            <th>Date Of Join</th>
                                            <th>Branch</th>
                                            <th>Department </th>
                                            <th>LOP Type</th>
                                            <th>Leave Type</th>
                                            <th>Applied Date</th>
                                            <th>From Date</th>
                                            <th>To Date</th>
                                            <th>LOP Days</th>
                                            <th>Reason</th>
                                            <th>Contact Person</th>
                                            <th>Authorized By</th>
                                            <th>Authorized Remarks</th>
                                            <th>Authorized Date</th>
                                            <th>Approved By</th>
                                            <th>Approved Remarks</th>
                                            <th>Approved Date</th>
                                            <th>Rejected By</th>
                                            <th>Rejected Remarks</th>
                                            <th>Rejected Date</th>
                                            <th>Status</th>
                                            <!-- <th>Source</th> -->
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $arr_data = $arr_lopreport_for_template['loprequests']; 
                                        $count = 0;
                                        foreach ($arr_data as $val) { 
                                            $count++;
                                            $empstatus = ($val['status'] == '2') ? ' (Resigned)' : '';
                                            $from_half = ($val['fromhalf'] == '1') ? "First Half" : (($val['fromhalf'] == '2') ? "Second Half" : "");
                                            $to_half = ($val['tohalf'] == '1') ? "First Half" : (($val['tohalf'] == '2') ? "Second Half" : "");
                                            
                                            $rej_by = ''; $rej_rem = ''; $rej_date = '';
                                            if ($val['leave_status'] == 'Rejected') {
                                                $rej_by = empty($val['APPROVED_date']) ? $val['Authorized_name'] : $val['Approved_name'];
                                                $rej_rem = empty($val['APPROVED_date']) ? $val['Authorized_remarks'] : $val['Approved_remarks'];
                                                $rej_date = empty($val['APPROVED_date']) ? $val['Autherized_date'] : $val['APPROVED_date'];
                                            }
                                            $appr_rem = ($val['leave_status'] != 'Rejected') ? $val['Approved_remarks'] : '';
                                            $appr_date = ($val['leave_status'] != 'Rejected') ? $val['APPROVED_date'] : '';
                                        ?>
                                            <tr>
                                                <td><?php echo $count; ?></td>
                                                <td><?php echo h($val['emp_name']) . $empstatus; ?></td>
                                                <td><?php echo h($val['employee_id']); ?></td>
                                                <td><?php echo h($val['joining_date']); ?></td>
                                                <td><?php echo h($val['branch']); ?></td>
                                                <td><?php echo h($val['department']); ?></td>
                                                <td>
                                                    <?php 
                                                    if ($val['lop_source'] == 'attendance') echo 'Indirect (Attendance)';
                                                    elseif ($val['occurance'] == 'LOP' && $val['item_part'] != 'Indirect') echo 'Direct / Policy LOP';
                                                    else echo 'Indirect LOP';
                                                    ?>
                                                </td>
                                                <td><?php echo h($val['leave_type']); ?></td>
                                                <td><?php echo h($val['leave_applied_on']); ?></td>
                                                <td><?php echo h($val['leave_from']) . " " . $from_half; ?></td>
                                                <td><?php echo h($val['leave_to']) . " " . $to_half; ?></td>
                                                <td><?php echo h($val['leavedays']); ?></td>
                                                <td><?php echo h($val['Reason']); ?></td>
                                                <td><?php echo h($val['contact_person']); ?></td>
                                                <td><?php echo h($val['Authorized_name']); ?></td>
                                                <td><?php echo h($val['Authorized_remarks']); ?></td>
                                                <td><?php echo h($val['Autherized_date']); ?></td>
                                                <td><?php echo h($val['Approved_name']); ?></td>
                                                <td><?php echo h($appr_rem); ?></td>
                                                <td><?php echo h($appr_date); ?></td>
                                                <td><?php echo h($rej_by); ?></td>
                                                <td><?php echo h($rej_rem); ?></td>
                                                <td><?php echo h($rej_date); ?></td>
                                                <td><?php echo h($val['leave_status']); ?></td>
                                                <!-- <td><?php echo ($val['lop_source'] == 'attendance') ? 'Attendance Register' : 'Leave Entry'; ?></td> -->
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </fieldset>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            $('.todayattandence').DataTable({
                "paging": true,
                "lengthChange": false,
                "searching": true,
                "info": true,
                "autoWidth": false,
                "scrollCollapse": true,
            });
        });
    </script>
<?php } else { ?>
    <style type="text/css">
        body { line-height: 2em; }
        .block-container { width: 95%; padding: 20px; border: #000000 solid thin; }
        .sub-head { border-bottom: #000000 solid thin; }
        .row { height: 32px; }
        .col-md-4 { width: 33.33%; float: left; }
        table { border: 1px solid #f4f4f4; width: 100%; margin-bottom: 20px; border-collapse: collapse; }
        td, th { text-align: left; padding: 5px; line-height: 1.4; vertical-align: top; font-size: 9px; border: 1px solid #B2B2B2; }
        thead th { background-color: #86bfe0; }
    </style>
    <?php echo $this->element('reportadminheader', array('title' => 'LOP Report')); ?>
    <?php if (empty($arr_lopreport_for_template)) { echo '<fieldset style="text-align: center; "><legend>No Record Found Under This Criteria</legend></fieldset>'; } ?>
    <?php if (isset($needBranchWiseReport) && $needBranchWiseReport == true) { ?>
        <?php foreach ($arr_lopreport_for_template as $branch_code => $lopsummary) { ?>
            <h4><?php echo h($lopsummary['branch_name']) . ' Branch'; ?></h4>
            <table>
                <thead>
                    <tr>
                        <th>Sl. No</th>
                        <th>Employee Name</th>
                        <th>Employee ID</th>
                        <th>Date Of Join</th>
                        <th>Branch</th>
                        <th>Department</th>
                        <th>LOP Type</th>
                        <th>Leave Type</th>
                        <th>Applied Date</th>
                        <th>From Date</th>
                        <th>To Date</th>
                        <th>LOP Days</th>
                        <th>Reason</th>
                        <th>Contact Person</th>
                        <th>Auth. By</th>
                        <th>Auth. Remarks</th>
                        <th>Auth. Date</th>
                        <th>Appr. By</th>
                        <th>Appr. Remarks</th>
                        <th>Appr. Date</th>
                        <th>Rej. By</th>
                        <th>Rej. Remarks</th>
                        <th>Rej. Date</th>
                        <th>Status</th>
                        <th>Source</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $count = 0;
                    foreach ($lopsummary['loprequests'] as $val) { 
                        $count++;
                        $from_half = ($val['fromhalf'] == '1') ? "1st Half" : (($val['fromhalf'] == '2') ? "2nd Half" : "");
                        $to_half = ($val['tohalf'] == '1') ? "1st Half" : (($val['tohalf'] == '2') ? "2nd Half" : "");
                        $rej_by = ''; $rej_rem = ''; $rej_date = '';
                        if ($val['leave_status'] == 'Rejected') {
                            $rej_by = empty($val['APPROVED_date']) ? $val['Authorized_name'] : $val['Approved_name'];
                            $rej_rem = empty($val['APPROVED_date']) ? $val['Authorized_remarks'] : $val['Approved_remarks'];
                            $rej_date = empty($val['APPROVED_date']) ? $val['Autherized_date'] : $val['APPROVED_date'];
                        }
                    ?>
                        <tr>
                            <td><?php echo $count; ?></td>
                            <td><?php echo h($val['emp_name']); ?></td>
                            <td><?php echo h($val['employee_id']); ?></td>
                            <td><?php echo h($val['joining_date']); ?></td>
                            <td><?php echo h($val['branch']); ?></td>
                            <td><?php echo h($val['department']); ?></td>
                            <td>
                                <?php 
                                if ($val['lop_source'] == 'attendance') echo 'Indirect (Att)';
                                elseif ($val['occurance'] == 'LOP' && $val['item_part'] != 'Indirect') echo 'Direct';
                                else echo 'Indirect';
                                ?>
                            </td>
                            <td><?php echo h($val['leave_type']); ?></td>
                            <td><?php echo h($val['leave_applied_on']); ?></td>
                            <td><?php echo h($val['leave_from']) . " " . $from_half; ?></td>
                            <td><?php echo h($val['leave_to']) . " " . $to_half; ?></td>
                            <td><?php echo h($val['leavedays']); ?></td>
                            <td><?php echo h($val['Reason']); ?></td>
                            <td><?php echo h($val['contact_person']); ?></td>
                            <td><?php echo h($val['Authorized_name']); ?></td>
                            <td><?php echo h($val['Authorized_remarks']); ?></td>
                            <td><?php echo h($val['Autherized_date']); ?></td>
                            <td><?php echo h($val['Approved_name']); ?></td>
                            <td><?php echo h(($val['leave_status'] != 'Rejected') ? $val['Approved_remarks'] : ''); ?></td>
                            <td><?php echo h(($val['leave_status'] != 'Rejected') ? $val['APPROVED_date'] : ''); ?></td>
                            <td><?php echo h($rej_by); ?></td>
                            <td><?php echo h($rej_rem); ?></td>
                            <td><?php echo h($rej_date); ?></td>
                            <td><?php echo h($val['leave_status']); ?></td>
                            <td><?php echo ($val['lop_source'] == 'attendance') ? 'Att. Reg' : 'Leave Entry'; ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } ?>
    <?php } else { ?>
        <table>
            <thead>
                <tr>
                    <th>Sl. No</th>
                    <th>Employee Name</th>
                    <th>Employee ID</th>
                    <th>Date Of Join</th>
                    <th>Branch</th>
                    <th>Department</th>
                    <th>LOP Type</th>
                    <th>Leave Type</th>
                    <th>Applied Date</th>
                    <th>From Date</th>
                    <th>To Date</th>
                    <th>LOP Days</th>
                    <th>Reason</th>
                    <th>Contact Person</th>
                    <th>Auth. By</th>
                    <th>Auth. Remarks</th>
                    <th>Auth. Date</th>
                    <th>Appr. By</th>
                    <th>Appr. Remarks</th>
                    <th>Appr. Date</th>
                    <th>Rej. By</th>
                    <th>Rej. Remarks</th>
                    <th>Rej. Date</th>
                    <th>Status</th>
                    <th>Source</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $count = 0;
                foreach ($arr_lopreport_for_template['loprequests'] as $val) { 
                    $count++;
                    $from_half = ($val['fromhalf'] == '1') ? "1st Half" : (($val['fromhalf'] == '2') ? "2nd Half" : "");
                    $to_half = ($val['tohalf'] == '1') ? "1st Half" : (($val['tohalf'] == '2') ? "2nd Half" : "");
                    $rej_by = ''; $rej_rem = ''; $rej_date = '';
                    if ($val['leave_status'] == 'Rejected') {
                        $rej_by = empty($val['APPROVED_date']) ? $val['Authorized_name'] : $val['Approved_name'];
                        $rej_rem = empty($val['APPROVED_date']) ? $val['Authorized_remarks'] : $val['Approved_remarks'];
                        $rej_date = empty($val['APPROVED_date']) ? $val['Autherized_date'] : $val['APPROVED_date'];
                    }
                ?>
                    <tr>
                        <td><?php echo $count; ?></td>
                        <td><?php echo h($val['emp_name']); ?></td>
                        <td><?php echo h($val['employee_id']); ?></td>
                        <td><?php echo h($val['joining_date']); ?></td>
                        <td><?php echo h($val['branch']); ?></td>
                        <td><?php echo h($val['department']); ?></td>
                        <td>
                            <?php 
                            if ($val['lop_source'] == 'attendance') echo 'Indirect (Att)';
                            elseif ($val['occurance'] == 'LOP' && $val['item_part'] != 'Indirect') echo 'Direct';
                            else echo 'Indirect';
                            ?>
                        </td>
                        <td><?php echo h($val['leave_type']); ?></td>
                        <td><?php echo h($val['leave_applied_on']); ?></td>
                        <td><?php echo h($val['leave_from']) . " " . $from_half; ?></td>
                        <td><?php echo h($val['leave_to']) . " " . $to_half; ?></td>
                        <td><?php echo h($val['leavedays']); ?></td>
                        <td><?php echo h($val['Reason']); ?></td>
                        <td><?php echo h($val['contact_person']); ?></td>
                        <td><?php echo h($val['Authorized_name']); ?></td>
                        <td><?php echo h($val['Authorized_remarks']); ?></td>
                        <td><?php echo h($val['Autherized_date']); ?></td>
                        <td><?php echo h($val['Approved_name']); ?></td>
                        <td><?php echo h(($val['leave_status'] != 'Rejected') ? $val['Approved_remarks'] : ''); ?></td>
                        <td><?php echo h(($val['leave_status'] != 'Rejected') ? $val['APPROVED_date'] : ''); ?></td>
                        <td><?php echo h($rej_by); ?></td>
                        <td><?php echo h($rej_rem); ?></td>
                        <td><?php echo h($rej_date); ?></td>
                        <td><?php echo h($val['leave_status']); ?></td>
                        <td><?php echo ($val['lop_source'] == 'attendance') ? 'Att. Reg' : 'Leave Entry'; ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    <?php } ?>
<?php } ?>