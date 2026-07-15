<!-- edited by athira on 10-10-2025 -->
<?php if ($mode == ''): ?>
<div class="modal-body" style="overflow-y:auto;">
    <legend style="text-align:center;font-weight:bold;">
        Monthly Leave Taken Register - <?php echo $mname1 . " " . $y1; ?>
    </legend>
    <h4 align="center" style="font-weight:bold;">
        <?php echo isset($user_id) ? "Report run by $user_id - $date_time" : ''; ?>
    </h4>

    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <?php if (empty($arr_leavepolicydetails_for_template)): ?>
                    <h3>No data available under the selected criteria</h3>
                <?php else: ?>
                    <?php foreach ($arr_leavepolicydetails_for_template as $empLeaves): ?>
                        <?php if (!empty($empLeaves['summary'])): ?>
                            <?php $empData = $empLeaves['summary'][0]; ?>
                            <div class="box-body" style="overflow-x:auto;margin-bottom:40px;">
                                <fieldset>
                                    <legend style="font-weight:bold;">
                                        <?php echo $empData['info']['EmpName']; ?>
                                        <?php echo (isset($empData['ed']['status']) && $empData['ed']['status'] == '2') ? ' (Resigned)' : ''; ?>
                                    </legend>
                                </fieldset>

                                <fieldset>
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Sl No</th>
                                                <th>Employee ID</th>
                                                <th>User ID</th>
                                                <th>Employee Name</th>
                                                <th>Date Of Joining</th>
                                                <th>Branch</th>
                                                <th>Department</th>
                                                <th>Designation</th>
                                                <th>Termination Date</th>
                                                <th>Leave Policy Type</th>
                                                <th>Leave Type</th>
                                                <th>Leave Date</th>
                                                <th>Applied Date</th>
                                                <th>Authorized Date</th>
                                                <th>Authorized Person</th>
                                                <th>Approved Date</th>
                                                <th>Approved Person</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $i = 1; ?>
                                            <?php foreach ($empLeaves['summary'] as $leave): ?>
                                                <tr>
                                                    <td><?php echo $i++; ?></td>
                                                    <td><?php echo $leave['info']['employee_id']; ?></td>
                                                    <td><?php echo $leave['user_credentials']['user_id']; ?></td>
                                                    <td><?php echo $leave['info']['EmpName']; ?></td>
                                                    <td><?php echo !empty($leave['info']['joining_date']) ? date('d-m-Y', strtotime($leave['info']['joining_date'])) : ''; ?></td>
                                                    <td><?php echo $leave['info']['branch']; ?></td>
                                                    <td><?php echo $leave['info']['department']; ?></td>
                                                    <td><?php echo $leave['info']['designation']; ?></td>
                                                    <td><?php echo !empty($leave['termination']['last_approved_working_date']) ? date('d-m-Y', strtotime($leave['termination']['last_approved_working_date'])) : ''; ?></td>
                                                    <td>
                                                        <?php 
                                                            $typeMap = ['M'=>'Monthly','Y'=>'Yearly','Q'=>'Quarterly','H'=>'Half Yearly','D'=>'Running Days','P'=>'Present Days'];
                                                            echo $typeMap[$leave['lp']['leave_policy_type']];
                                                        ?>
                                                    </td>
                                                    <td><?php echo $leave['LeaveType']['leave_type']; ?></td>
                                                    <td><?php echo !empty($leave['elt']['leave_date']) ? date('d-m-Y', strtotime($leave['elt']['leave_date'])) : ''; ?></td>
                                                    <td><?php echo !empty($leave['le']['applied_date']) ? date('d-m-Y', strtotime($leave['le']['applied_date'])) : ''; ?></td>
                                                    <td><?php echo !empty($leave['le']['Autherized_date']) ? date('d-m-Y', strtotime($leave['le']['Autherized_date'])) : ''; ?></td>
                                                    <td><?php echo $leave[0]['authorized_by_name'];?></td>
                                                    <td><?php echo !empty($leave['le']['APPROVED_date']) ? date('d-m-Y', strtotime($leave['le']['APPROVED_date'])) : ''; ?></td>
                                                    <td><?php echo $leave[0]['approved_by_name']; ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </fieldset>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
<!-- end -->
