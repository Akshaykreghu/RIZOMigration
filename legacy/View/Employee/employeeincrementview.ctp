<style>
    #table1 {
        border-collapse: collapse;
        overflow-y: auto;
    }
</style>
<?php if ($mode == '') { ?>
    <div>
        <h2 style="font-weight: bold;text-align: center;"><?php echo " Employee Increment - " . $mname . "  "  . $yname ?></h2>
        <h2 style="font-weight: bold;text-align: center;font-size: 19px;"><?php echo  "(Report Run by " . $user_id . " at " . $date_time . ")" ?></h2>
    </div>

    <?php if (!empty($processedData)) { ?>
        <?php if ($selectCriteria1 == 'EmployeeDetails') { ?>
            <div class="box-body" style="overflow-x: auto; overflow-y:auto;">
                <table class="table table-bordered" id="table1">
                    <thead>
                        <tr>
                            <th>Sl No</th>
                            <th>Employee ID</th>
                            <th>User ID</th>
                            <th>Employee Name</th>
                            <th>Branch</th>
                            <th>Department</th>
                            <th>Designation</th>
                            <th>Joining Date</th>
                            <!-- edited by athira on 13-06-2025 -->
                            <th>Termination Date</th>
                            <!-- end -->
                            <!-- Edited by Akshay on 12-7-2025 -->
                            <th>Annual CTC</th>
                            <!-- End -->
                            <th>Increment Date</th>
                            <th>Created Date</th>
                            <!-- edited by athira on 13-06-2025 -->
                            <th>New Gross Salary</th>
                            <!-- end -->
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($processedData as $data) { ?>
                            <tr>
                                <td><?php echo ($data['SlNo']); ?></td>
                                <td><?php echo ($data['EmployeeID']); ?></td>
                                <td><?php echo ($data['user_id']); ?></td>
                                <td><?php echo ($data['EmployeeName']); ?></td>
                                <td><?php echo ($data['Branch']); ?></td>
                                <td><?php echo ($data['Department']); ?></td>
                                <td><?php echo ($data['Designation']); ?></td>
                                <td><?php echo ($data['JoiningDate']); ?></td>
                                <!-- edited by athira on 13-06-2025 -->
                                <td><?php echo ($data['TerminationDate']); ?></td>
                                <!-- end -->
                                <td><?php echo ($data['Amount']); ?></td>
                                <td><?php echo ($data['IncrementDate']); ?></td>
                                <td><?php echo ($data['create_date']); ?></td>
                                <!-- edited by athira on 13-06-2025 -->
                                <td><?php echo ($data['NewGrossSalary']); ?></td>
                                <!-- end -->
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        <?php } elseif ($selectCriteria1 == 'Units') { ?>
            <?php
            $groupedByBranch = [];
            foreach ($processedData as $data) {
                $groupedByBranch[$data['branch_head']][] = $data;
            }
            ?>
            <!-- <?php var_dump($groupedByBranch); ?> -->
            <?php foreach ($groupedByBranch as $branch => $branchData) { ?>
                <div class="box-body" style="overflow-x: auto; overflow-y:auto;">
                    <h3><?php echo h($branch); ?></h3>
                    <table class="table table-bordered" id="table1">
                        <thead>
                            <tr>
                                <th>Sl No</th>
                                <th>Employee ID</th>
                                <th>User ID</th>
                                <th>Employee Name</th>
                                <th>Branch</th>
                                <th>Department</th>
                                <th>Designation</th>
                                <th>Joining Date</th>
                                <!-- edited by athira on 13-06-2025 -->
                                <th>Termination Date</th>
                                <!-- end -->
                                <!-- Edited by Akshay on 12-7-2025 -->
                                <th>Annual CTC</th>
                                <!-- End -->
                                <th>Increment Date</th>
                                <th>Created Date</th>
                                <!-- edited by athira on 13-06-2025 -->
                                <th>New Gross Salary</th>
                                <!-- end -->
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $slNo = 1;
                            ?>
                            <?php foreach ($branchData as $data) { ?>
                                <tr>
                                    <td><?php echo ($slNo); ?></td>
                                    <td><?php echo ($data['EmployeeID']); ?></td>
                                    <td><?php echo ($data['user_id']); ?></td>
                                    <td><?php echo ($data['EmployeeName']); ?></td>
                                    <td><?php echo ($data['Branch']); ?></td>
                                    <td><?php echo ($data['Department']); ?></td>
                                    <td><?php echo ($data['Designation']); ?></td>
                                    <td><?php echo ($data['JoiningDate']); ?></td>
                                    <!-- edited by athira on 13-06-2025 -->
                                    <td><?php echo ($data['TerminationDate']); ?></td>
                                    <!-- end -->
                                    <td><?php echo ($data['Amount']); ?></td>
                                    <td><?php echo ($data['IncrementDate']); ?></td>
                                    <td><?php echo ($data['create_date']); ?></td>
                                    <!-- edited by athira on 13-06-2025 -->
                                    <td><?php echo ($data['NewGrossSalary']); ?></td>
                                    <!-- end -->
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