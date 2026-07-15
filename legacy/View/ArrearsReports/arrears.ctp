<style>
    #table1 {
        border-collapse: collapse;
        overflow-y: auto;
    }
</style>
<?php if ($mode == '') { ?>
    <div>
        <h2 align="center"><b><?php echo ($heading); ?></h2>
        <h2 style="font-weight: bold;text-align: center;font-size: 19px;"><?php echo "(Report Run by " . $user_id . " at " . $datetime . ")" ?></b> </h2>
    </div>
    <?php if (!empty($processedData)) { ?>
        <?php if ($selectCriteria1 == 'EmployeeDetails') { ?>
            <div class="box-body" style="overflow-x: auto; overflow-y: auto;">
                <table class="table table-bordered" id="table1">
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
                            <th>Item</th>
                            <th>Old Salary</th>
                            <th>New Salary</th>
                            <th>Difference</th>
                            <th>Arrear</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($processedData as $data) { ?>
                            <tr>
                                <td><?= h($data['sl_no']); ?></td>
                                <td><?= h($data['employee_id']); ?></td>
                                <td><?= h($data['user_id']); ?></td>
                                <td><?= h($data['first_name']); ?></td>
                                <td><?= h($data['joining_date']); ?></td>
                                <td><?= h($data['branch']); ?></td>
                                <td><?= h($data['department']); ?></td>
                                <td><?= h($data['designation']); ?></td>
                                <td><?= h($data['last_working_date']); ?></td>
                                <td><?= h($data['item']); ?></td>
                                <td><?= h($data['old_salary']); ?></td>
                                <td><?= h($data['new_salary']); ?></td>
                                <td><?= h($data['salary_difference']); ?></td>
                                <td><?= h($data['arrear_amount']); ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

        <?php } elseif ($selectCriteria1 == 'Units') { ?>
            <?php
            // Grouping processed data by branch
            $groupedByBranch = [];
            foreach ($processedData as $data) {
                $groupKey = isset($data['branch']) ? $data['branch'] : 'Unknown';
                $groupedByBranch[$groupKey][] = $data;
            }
            ?>

            <?php foreach ($groupedByBranch as $branch => $branchData) { ?>
                <div class="box-body" style="overflow-x: auto; overflow-y: auto;">
                    <h3><?= h($branch); ?></h3>
                    <table class="table table-bordered">
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
                                <th>Item</th>
                                <th>Old Salary</th>
                                <th>New Salary</th>
                                <th>Difference</th>
                                <th>Arrear</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $slNo = 1; ?>
                            <?php foreach ($branchData as $data) { ?>
                                <tr>
                                    <td><?= h($slNo); ?></td>
                                    <td><?= h($data['employee_id']); ?></td>
                                    <td><?= h($data['user_id']); ?></td>
                                    <td><?= h($data['first_name']); ?></td>
                                    <td><?= h($data['joining_date']); ?></td>
                                    <td><?= h($data['branch']); ?></td>
                                    <td><?= h($data['department']); ?></td>
                                    <td><?= h($data['designation']); ?></td>
                                    <td><?= h($data['last_working_date']); ?></td>
                                    <td><?= h($data['item']); ?></td>
                                    <td><?= h($data['old_salary']); ?></td>
                                    <td><?= h($data['new_salary']); ?></td>
                                    <td><?= h($data['salary_difference']); ?></td>
                                    <td><?= h($data['arrear_amount']); ?></td>
                                </tr>
                                <?php $slNo++; ?>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php } ?>

        <?php } else { ?>
            <div style="font-size: 16px; text-align: left;">Invalid criteria selected.</div>
        <?php } ?>
    <?php } else { ?>
        <div style="font-size: 16px; text-align: left;">No data available under the selected criteria.</div>
    <?php } ?>

<?php } ?>