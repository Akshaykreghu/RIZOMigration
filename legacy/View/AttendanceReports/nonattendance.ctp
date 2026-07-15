<style>
    #table1 {
        border-collapse: collapse;
        overflow-y: auto;
    }
</style>
<?php if ($mode == '') { ?>
    <div>
        <h2 style="text-align:center; font-weight: bold;"><?php echo h($heading); ?></h2>
        <h2 style="font-weight: bold;text-align: center;font-size: 19px;"><?php echo "(Report Run by " . $user_id . " at " . $datetime . ")" ?></b> </h2>
    </div>
    
    <?php if (!empty($processedData)) { ?>
        <?php if ($selectCriteria1 == 'EmployeeDetails') { ?>
            <div class="box-body" style="overflow-x: auto; overflow-y:auto;">
                <table class="table table-bordered" style="overflow-y:auto;">
                    <thead>
                        <tr>
                            <th>Sl No</th>
                            <th>Employee ID</th>
                            <th>User ID</th>
                            <th>Employee Name</th>
                            <th>Joining Date</th>
                            <th>Branch</th>
                            <th>Department</th>
                            <th>Designation</th>
                            <th>Termination Date</th>
                            <th>Non-Attendance Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($processedData as $data) { ?>
                            <tr>
                                <td><?php echo h($data['sl_no']); ?></td>
                                <td><?php echo h($data['employee_id']); ?></td>
                                <td><?php echo h($data['user_id']); ?></td>
                                <td><?php echo h($data['first_name']); ?></td>
                                <td><?php echo h($data['joining_date']); ?></td>
                                <td><?php echo h($data['branch']); ?></td>
                                <td><?php echo h($data['department']); ?></td>
                                <td><?php echo h($data['designation']); ?></td>
                                <td><?php echo h($data['last_working_date']); ?></td>
                                <td><?php echo h($data['att_date']); ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } elseif ($selectCriteria1 == 'Units') { ?>
            <?php 
                $groupedByBranch = [];
                foreach ($processedData as $data) {
                    $groupedByBranch[$data['branch']][] = $data;
                }
            ?>
            <?php foreach ($groupedByBranch as $branch => $branchData) { ?>
                <div class="box-body" style="overflow-x: auto; overflow-y:auto;">
                    <h3><?php echo h($branch); ?></h3>
                    <table class="table table-bordered" style="overflow-y:auto;">
                        <thead>
                            <tr>
                                <th>Sl No</th>
                                <th>Employee ID</th>
                                <th>User ID</th>
                                <th>Employee Name</th>
                                <th>Joining Date</th>
                                <th>Branch</th>
                                <th>Department</th>
                                <th>Designation</th>
                                <th>Termination Date</th>
                                <th>Non-Attendance Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                                $slNo = 1; 
                            ?>
                            <?php foreach ($branchData as $data) { ?>
                                <tr>
                                    <td><?php echo h($slNo); ?></td>
                                    <td><?php echo h($data['employee_id']); ?></td>
                                    <td><?php echo h($data['user_id']); ?></td>
                                    <td><?php echo h($data['first_name']); ?></td>
                                    <td><?php echo h($data['joining_date']); ?></td>
                                    <td><?php echo h($data['branch']); ?></td>
                                    <td><?php echo h($data['department']); ?></td>
                                    <td><?php echo h($data['designation']); ?></td>
                                    <td><?php echo h($data['last_working_date']); ?></td>
                                    <td><?php echo h($data['att_date']); ?></td>
                                </tr>
                            <?php 
                                $slNo++; 
                            ?>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php } ?>
        <?php } else { ?>
            <div style="font-size: 16px; text-align: left;">Invalid criteria selected.</div>
        <?php } ?>
    <?php } else { ?>
        <div style="font-size: 16px;text-align:left; background-color:;">No data available under the selected criteria.</div>
    <?php } ?>
<?php } ?>
