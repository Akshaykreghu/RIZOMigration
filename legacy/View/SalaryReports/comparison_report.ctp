<style>
    #table1 {
        border-collapse: collapse;
        overflow-y: auto;
        overflow-x: auto;

    }



    #table2 {
        border-collapse: collapse;
        overflow-y: auto;
        overflow-x: auto;

    }



    #table1 td,
    th {
        border-style: solid;
        border-color: #d4d4de;

    }

    #table2 td,
    th {
        border-style: solid;
        border-color: #d4d4de;

    }





    #scroll_bar {
        display: block;
        overflow-x: auto;
        white-space: nowrap;
    }

    #scroll_bar2 {
        display: block;
        overflow-x: auto;
        white-space: nowrap;
    }

    .change-identified {
        color: white;
        background-color: red;
    }
</style>


<?php if ($mode == '') { ?>
    <div class="modal-body" style="overflow-y:auto;">

        <div class="row">
            <div>

                <h2 align="center"><b><?php echo "Salary Previous Month Comparison - " . date('F Y', strtotime($prev_month)) . " - " . date('F Y', strtotime($month)); ?></b> </h2>
                <h2 style="font-weight: bold;text-align: center;font-size: 19px;"><?php echo  "(Report Run by " . $user_id . " at " . $date_time . ")" ?> </h2>

                <?php $coun1 = isset($array_key['Addition']) ? count($array_key['Addition']) : 0;
                $coun2 = isset($array_key['Deduction']) ? count($array_key['Deduction']) : 0;
                $cont = $coun1 + 1 + $coun2;
                $cont2 = $cont + 3;

                // debug($gross1); exit;
                if (count($gross) <= 0 && count($gross1) <= 0) { ?>
                    <div style="font-size: 16px;text-align:left; background-color:;">
                        No data available under the selected criteria.</div>
                <?php } else { ?>


                    <!--belongs to branch section added by megha end... view section-->
                    <?php if (isset($needBranchWiseReport) && $needBranchWiseReport == 1) { //do branchwise listing 
                    ?>

                        <?php
                        // debug($gross);
                        // debug($gross1);
                        foreach ($gross as $branch => $brnch) {
                            foreach ($brnch as $key => $val) {
                                $monthly_data1[$branch][$key]['current'] = $val;
                            }
                        }

                        foreach ($gross1 as $branch => $brnch) {
                            foreach ($brnch as $key => $val) {
                                $monthly_data1[$branch][$key]['prev'] = $val;
                            }
                        }
                        // debug(($monthly_data1)); exit;
                        foreach ($arr_emp_pkey as $fkey) {
                            $pkey = isset($fkey['pm']['emp_fkey']) ? $fkey['pm']['emp_fkey'] : 0;
                            $branch_code = isset($fkey['ei']['branch_code']) ? $fkey['ei']['branch_code'] : '';
                            if ($pkey != 0 && $branch_code != '') {
                                $temp_arr = isset($monthly_data1[$branch_code][$pkey]) ? $monthly_data1[$branch_code][$pkey] : '';
                                if ($temp_arr != '') {
                                    $monthly_data[$branch_code][$pkey] = $temp_arr;
                                }
                            }
                        }
                        // debug($monthly_data);
                        // debug($monthly_data1);
                        ?>

                        <?php foreach ($monthly_data as $branch => $brnch) {
                            $branches = current($brnch);
                            $branch = isset($branches['current']['emp_info']['branch']) ? $branches['current']['emp_info']['branch'] : (isset($branches['prev']['emp_info']['branch']) ? $branches['prev']['emp_info']['branch'] : '');
                            $j = 1;
                            $total = array();
                            for ($num = 10; $num < 200; $num++) {
                                $total[$num] = 0;
                            }
                            $branches1 = isset($branches['current']) ? $branches['current'] : (isset($branches['prev']) ? $branches['prev'] : '');
                        ?>
                            <div class="box-body" style="overflow-y:auto;">
                                <legend style="border: 0;"><?php echo $branch; ?></legend>
                                <table class="table table-bordered" id="table1" style="border-collapse: collapse;">
                                    <thead>
                                        <tr>
                                            <!-- edited by athira on 08-07-2025 -->
                                            <?php  if($company_code =='DEMO' || $company_code =='SRTS' || $company_code=='GLET') { ?>
                                            <th colspan="12" style="text-align: center;">Employee Details</th>
                                            <?php } else { ?>
                                                <th colspan="10" style="text-align: center;">Employee Details</th>
                                                <?php } ?>
                                            <!-- end -->
                                            <th colspan='3' style="text-align: center;">CTC (Standard)</th>
                                            <th colspan='3' style="text-align: center;">Gross Salary (Standard)</th>
                                            <!-- Standard additions -->
                                            <?php foreach ($items as $val) { ?>
                                                <th colspan='3' style="text-align:center;"><?php echo $val; ?></th>
                                            <?php } ?>

                                            <th colspan='3' style="text-align: center;">Gross Salary (Actual)</th>
                                            <th colspan='3' style="text-align: center;">Net Salary (Actual)</th>
                                            <th colspan='3' style="text-align: center;">CTC (Actual)</th>
                                            <th colspan='3' style="text-align: center;">Pay Days</th>
                                            <th colspan='3' style="text-align: center;">Variable Additions (Count)</th>
                                            <th colspan='3' style="text-align: center;">Variable Deductions (Count)</th>
                                            <th colspan='3' style="text-align: center;">Bank Account</th>

                                        </tr>
                                        <tr style="margin: auto;">
                                            <th>Sl No</th>
                                            <th>Employee ID</th>
                                            <!-- edited by athira on 08-07-2025 -->
                                            <?php  if($company_code =='DEMO' || $company_code =='SRTS' || $company_code=='GLET') { ?>
                                            <th>Employee ID (US Format)</th>
                                        <?php } ?>
                                        <!-- end -->
                                            <th>User ID</th>
                                            <th>Employee Name</th>
                                            <!-- edited by athira on 08-07-2025 -->
                                            <?php  if($company_code =='DEMO' || $company_code =='SRTS' || $company_code=='GLET') { ?>
                                            <th>Employee Name (US Format)</th>
                                        <?php } ?>
                                        <!-- end -->
                                            <th>Joining Date</th>
                                            <th>Branch</th>
                                            <th>Department</th>
                                            <th>Designation</th>
                                            <th>Termination Date</th>
                                            <th>Status</th>
                                            <th> <?php echo date('F', strtotime($prev_month)); ?></th>
                                            <th> <?php echo date('F', strtotime($month)); ?></th>
                                            <th>Difference</th>
                                            <th> <?php echo date('F', strtotime($prev_month)); ?></th>
                                            <th> <?php echo date('F', strtotime($month)); ?></th>
                                            <th>Difference</th>
                                            <!-- Standard additions -->
                                            <?php
                                            foreach ($items as $val) { ?>
                                                <th> <?php echo date('F', strtotime($prev_month)); ?></th>
                                                <th> <?php echo date('F', strtotime($month)); ?></th>
                                                <th>Difference</th>
                                            <?php }
                                            ?>

                                            <th> <?php echo date('F', strtotime($prev_month)); ?></th>
                                            <th> <?php echo date('F', strtotime($month)); ?></th>
                                            <th>Difference</th>
                                            <th> <?php echo date('F', strtotime($prev_month)); ?></th>
                                            <th> <?php echo date('F', strtotime($month)); ?></th>
                                            <th>Difference</th>
                                            <th> <?php echo date('F', strtotime($prev_month)); ?></th>
                                            <th> <?php echo date('F', strtotime($month)); ?></th>
                                            <th>Difference</th>
                                            <th> <?php echo date('F', strtotime($prev_month)); ?></th>
                                            <th> <?php echo date('F', strtotime($month)); ?></th>
                                            <th>Difference</th>
                                            <th> <?php echo date('F', strtotime($prev_month)); ?></th>
                                            <th> <?php echo date('F', strtotime($month)); ?></th>
                                            <th>Difference</th>
                                            <th> <?php echo date('F', strtotime($prev_month)); ?></th>
                                            <th> <?php echo date('F', strtotime($month)); ?></th>
                                            <th>Difference</th>
                                            <th> <?php echo date('F', strtotime($prev_month)); ?></th>
                                            <th> <?php echo date('F', strtotime($month)); ?></th>



                                            <th>Difference</th>


                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        // debug($brnch);
                                        foreach ($brnch as $key => $val) {
                                            // debug($val);continue;
                                            $empstatus = isset($val['current']['emp_info']['emp_status']) ? $val['current']['emp_info']['emp_status'] : (isset($val['prev']['emp_info']['emp_status']) ? $val['prev']['emp_info']['emp_status'] : '');
                                            if ($empstatus == 2) {
                                                $empstatus = ' (Resigned)';
                                            } else {
                                                $empstatus = '';
                                            }
                                            //   if(trim(isset($val['current']['emp_info']['branch']) ? $val['current']['emp_info']['branch'] : (isset($val['prev']['emp_info']['branch']) ? $val['prev']['emp_info']['branch'] : '')) == trim($branch)){
                                        ?>


                                            <tr>
                                                <td><?php echo ($j); ?></td>
                                                <td><?php echo (isset($val['current']['emp_info']['employee_id']) ? $val['current']['emp_info']['employee_id'] : (isset($val['prev']['emp_info']['employee_id']) ? $val['prev']['emp_info']['employee_id'] : '')); ?></td>
                                                <!-- edited by athira on 08-07-2025 -->
                                                <?php  if($company_code =='DEMO' || $company_code =='SRTS' || $company_code=='GLET') { ?>
                                                <td><?php echo (isset($val['current']['emp_info']['emp_us_id']) ? $val['current']['emp_info']['emp_us_id'] : (isset($val['prev']['emp_info']['emp_us_id']) ? $val['prev']['emp_info']['emp_us_id'] : '')); ?></td>
                                                <?php } ?>
                                                <!-- end -->
                                                <td><?php echo (isset($val['current']['user_credentials']['user_id']) ? $val['current']['user_credentials']['user_id'] : (isset($val['prev']['user_credentials']['user_id']) ? $val['prev']['user_credentials']['user_id'] : '')); ?></td>
                                                <td><?php echo (isset($val['current']['emp_info']['EmpName']) ? $val['current']['emp_info']['EmpName'] . $empstatus : (isset($val['prev']['emp_info']['EmpName']) ? $val['prev']['emp_info']['EmpName'] . $empstatus : '')); ?></td>
                                                <!-- edited by athira on 08-07-2025 -->
                                                <?php  if($company_code =='DEMO' || $company_code =='SRTS' || $company_code=='GLET') { ?>
                                                <td><?php echo (isset($val['current']['emp_info']['EmpUSName']) ? $val['current']['emp_info']['EmpUSName'] : (isset($val['prev']['emp_info']['EmpUSName']) ? $val['prev']['emp_info']['EmpUSName'] : '')); ?></td>
                                                <?php } ?>
                                                <!-- end -->
                                                <td><?php
                                                    echo (isset($val['current']['emp_info']['joining_date']) ? date('d-m-Y', strtotime($val['current']['emp_info']['joining_date'])) : (isset($val['prev']['emp_info']['joining_date']) ? date('d-m-Y', strtotime($val['prev']['emp_info']['joining_date'])) : ''));
                                                    ?></td>

                                                <td><?php echo (isset($val['current']['pm']['branch_name']) ? $val['current']['pm']['branch_name'] : (isset($val['current']['emp_info']['branch']) ? $val['current']['emp_info']['branch'] : (isset($val['prev']['emp_info']['branch']) ? $val['prev']['emp_info']['branch'] : ''))); ?></td>
                                                <td><?php echo (isset($val['current']['pm']['departments']) ? $val['current']['pm']['departments'] : (isset($val['current']['emp_info']['department']) ? $val['current']['emp_info']['department'] : (isset($val['prev']['emp_info']['department']) ? $val['prev']['emp_info']['department'] : ''))); ?></td>
                                                <td><?php echo (isset($val['current']['pm']['desig']) ? $val['current']['pm']['desig'] : (isset($val['current']['emp_info']['designation']) ? $val['current']['emp_info']['designation'] : (isset($val['prev']['emp_info']['designation']) ? $val['prev']['emp_info']['designation'] : ''))); ?></td>
                                                <td><?php
                                                    $lastApprovedWorkingDate = '';
                                                    if (isset($val['current']['termination']['last_approved_working_date']) && trim($val['current']['termination']['last_approved_working_date']) !== '') {
                                                        $lastApprovedWorkingDate = date('d-m-Y', strtotime($val['current']['termination']['last_approved_working_date']));
                                                    } elseif (isset($val['prev']['termination']['last_approved_working_date']) && trim($val['prev']['termination']['last_approved_working_date']) !== '') {
                                                        $lastApprovedWorkingDate = date('d-m-Y', strtotime($val['prev']['termination']['last_approved_working_date']));
                                                    }
                                                    echo $lastApprovedWorkingDate;
                                                    ?></td>

                                                <?php
                                                $prev_var_add = isset($val['prev']['pm']['var_add_count']) ? abs(($val['prev']['pm']['var_add_count'])) : 0;
                                                $current_var_add = isset($val['current']['pm']['var_add_count']) ? abs(($val['current']['pm']['var_add_count'])) : 0;

                                                $prev_var_ded = isset($val['prev']['pm']['var_ded_count']) ? abs(($val['prev']['pm']['var_ded_count'])) : 0;
                                                $current_var_ded = isset($val['current']['pm']['var_ded_count']) ? abs(($val['current']['pm']['var_ded_count'])) : 0;

                                                isset($val['prev']['pm']['bank_details']) ? $prev_bank = ($val['prev']['pm']['bank_details']) : $prev_bank = '';
                                                isset($val['current']['pm']['bank_details']) ? $curr_bank = ($val['current']['pm']['bank_details']) : $curr_bank = '';

                                                isset($val['prev']['pm']['ctc_rate']) ? $prev_ctc_rate = intval($val['prev']['pm']['ctc_rate']) : $prev_ctc_rate = 0;
                                                isset($val['current']['pm']['ctc_rate']) ? $current_ctc_rate = intval($val['current']['pm']['ctc_rate']) : $current_ctc_rate = 0;

                                                isset($val['prev']['pm']['gross_salary_standard']) ? $prev_gross_sal_std = intval($val['prev']['pm']['gross_salary_standard']) : $prev_gross_sal_std = 0;
                                                isset($val['current']['pm']['gross_salary_standard']) ? $curr_gross_sal_std = intval($val['current']['pm']['gross_salary_standard']) : $curr_gross_sal_std = 0;

                                                $item_value_change = 0;
                                                foreach ($items as $key => $val1) {
                                                    isset($val['prev']['items'][$key]['value']) ? $prev_value = round(abs($val['prev']['items'][$key]['value'])) : $prev_value = 0;
                                                    isset($val['current']['items'][$key]['value']) ? $current_value = round(abs($val['current']['items'][$key]['value'])) : $current_value = 0;
                                                    if ($prev_value != $current_value) {
                                                        $item_value_change++;
                                                    }
                                                }

                                                isset($val['prev']['pm']['gross_salary']) ? $prev_gross_sal = intval($val['prev']['pm']['gross_salary']) : $prev_gross_sal = 0;
                                                isset($val['current']['pm']['gross_salary']) ? $curr_gross_sal = intval($val['current']['pm']['gross_salary']) : $curr_gross_sal = 0;

                                                $prev_total_ded = isset($val['prev']['pm']['total_deduction']) ? abs(intval($val['prev']['pm']['total_deduction'])) : 0;
                                                $prev_total_sal = isset($val['prev']['pm']['total_salary']) ? abs(intval($val['prev']['pm']['total_salary'])) : 0;
                                                $prev_netsalary = ($prev_total_sal - $prev_total_ded);
                                                $curr_total_ded = isset($val['current']['pm']['total_deduction']) ? abs(intval($val['current']['pm']['total_deduction'])) : 0;
                                                $curr_total_sal = isset($val['current']['pm']['total_salary']) ? abs(intval($val['current']['pm']['total_salary'])) : 0;
                                                $curr_netsalary = ($curr_total_sal - $curr_total_ded);

                                                isset($val['prev']['pm']['ctc_amount']) ? $prev_ctc_amt = intval($val['prev']['pm']['ctc_amount']) : $prev_ctc_amt = 0;
                                                isset($val['current']['pm']['ctc_amount']) ? $current_ctc_amt = intval($val['current']['pm']['ctc_amount']) : $current_ctc_amt = 0;

                                                if (isset($val['prev']['prodata']['type']))
                                                    if ($val['prev']['prodata']['type'] == 'Working Days') {
                                                        $leave_total = isset($val['prev']['ectc']['leave_total'])? floatval($val['prev']['ectc']['leave_total']): 0;
                                                        $prev_pay_days = floatval($val['prev']['prodata']['present']) + $leave_total;
                                                    } elseif ($val['prev']['prodata']['type'] == 'Calender Days') {

                                                        $pres_days = isset($val['prev']['ar']['presant_total']) ? floatval($val['prev']['ar']['presant_total']) : 0;
                                                        $leave_days = isset($val['prev']['ar']['leave_total']) ? floatval($val['prev']['ar']['leave_total']) : 0;
                                                        $holiday = isset($val['prev']['ar']['holiday_total']) ? floatval($val['prev']['ar']['holiday_total']) : 0;
                                                        $week_off = isset($val['prev']['ar']['weekoff_total']) ? floatval($val['prev']['ar']['weekoff_total']) : 0;
                                                        
                                                        $prev_pay_days = $pres_days + $leave_days + $holiday + $week_off;
                                                     } else {
                                                        $prev_pay_days = floatval($val['prev']['prodata']['days']);
                                                    }

                                                if (isset($val['current']['prodata']['type']))
                                                    if ($val['current']['prodata']['type'] == 'Working Days') {
                                                        $leave_total = isset($val['current']['ectc']['leave_total'])? floatval($val['current']['ectc']['leave_total']): 0;
                                                        $current_pay_days = isset($val['current']['prodata']['present']) ? floatval($val['current']['prodata']['present']) : 0;
                                                        $current_pay_days += $leave_total;
                                                    } elseif ($val['current']['prodata']['type'] == 'Calender Days') {
                                                        $pres_days = isset($val['current']['ar']['presant_total']) ? floatval($val['current']['ar']['presant_total']) : 0;
                                                        $leave_days = isset($val['current']['ar']['leave_total']) ? floatval($val['current']['ar']['leave_total']) : 0;
                                                        $holiday = isset($val['current']['ar']['holiday_total']) ? floatval($val['current']['ar']['holiday_total']) : 0;
                                                        $week_off = isset($val['current']['ar']['weekoff_total']) ? floatval($val['current']['ar']['weekoff_total']) : 0;
                                                        
                                                        $current_pay_days = $pres_days + $leave_days + $holiday + $week_off;

                                                        // $current_pay_days = (isset($val['current']['prodata']['days']) ? floatval($val['current']['prodata']['days']) : 0) - (isset($val['current']['ectc']['lop_total']) ? floatval($val['current']['ectc']['lop_total']) : 0);
                                                    } else {
                                                        $current_pay_days = isset($val['current']['prodata']['days']) ? floatval($val['current']['prodata']['days']) : 0;
                                                    }
                                                $prev_pay_days = isset($prev_pay_days) ? $prev_pay_days : 0;
                                                $current_pay_days = isset($current_pay_days) ? $current_pay_days : 0;

                                                if (($prev_var_add ==  $current_var_add) && ($prev_var_ded == $current_var_ded) && ($prev_bank == $curr_bank) && ($prev_ctc_rate == $current_ctc_rate) && ($prev_gross_sal_std == $curr_gross_sal_std) && ($item_value_change == 0) && ($prev_gross_sal == $curr_gross_sal) && ($prev_netsalary == $curr_netsalary) && ($current_ctc_amt == $prev_ctc_amt) && ($prev_pay_days == $current_pay_days)) { ?>
                                                    <td>No change</td>
                                                <?php } else { ?>
                                                    <td style="color: white; background-color: red;">Change identified</td>
                                                <?php }
                                                ?>
                                                <!-- CTC Standard -->
                                                <td><?php echo isset($val['prev']['pm']['ctc_rate']) ? $prev_ctc_rate = intval($val['prev']['pm']['ctc_rate']) : $prev_ctc_rate = 0; ?></td>
                                                <?php $total[10] += $prev_ctc_rate; ?>
                                                <td><?php echo isset($val['current']['pm']['ctc_rate']) ? $current_ctc_rate = intval($val['current']['pm']['ctc_rate']) : $current_ctc_rate = 0; ?></td>
                                                <?php $total[11] += $current_ctc_rate; ?>
                                                <td><?php echo abs($current_ctc_rate - $prev_ctc_rate); ?></td>
                                                <?php $total[12] += abs($current_ctc_rate - $prev_ctc_rate); ?>

                                                <td><?php echo isset($val['prev']['pm']['gross_salary_standard']) ? $prev_gross_sal_std = intval($val['prev']['pm']['gross_salary_standard']) : $prev_gross_sal_std = 0; ?></td>
                                                <?php $total[13] += $prev_gross_sal_std; ?>

                                                <td><?php echo isset($val['current']['pm']['gross_salary_standard']) ? $curr_gross_sal_std = intval($val['current']['pm']['gross_salary_standard']) : $curr_gross_sal_std = 0; ?></td>
                                                <?php $total[14] += $curr_gross_sal_std; ?>

                                                <td><?php echo abs($curr_gross_sal_std - $prev_gross_sal_std); ?></td>
                                                <?php $total[15] += abs($curr_gross_sal_std - $prev_gross_sal_std); ?>

                                                <!-- Standard additions -->
                                                <?php
                                                $l = 15;
                                                foreach ($items as $key => $val1) {
                                                    // debug($val);
                                                ?>
                                                    <td><?php echo isset($val['prev']['items'][$key]['value']) ? $prev_value = round(abs($val['prev']['items'][$key]['value'])) : $prev_value = 0; ?></td>
                                                    <?php $l++;
                                                    $total[$l] +=  $prev_value; ?>
                                                    <td><?php echo isset($val['current']['items'][$key]['value']) ? $current_value = round(abs($val['current']['items'][$key]['value'])) : $current_value = 0; ?></td>
                                                    <?php $l++;
                                                    $total[$l] +=   $current_value; ?>
                                                    <td><?php echo abs($current_value - $prev_value) ?></td>
                                                    <?php $l++;
                                                    $total[$l] +=  abs($current_value - $prev_value); ?>
                                                <?php

                                                }
                                                // debug($total);
                                                ?>


                                                <td><?php echo isset($val['prev']['pm']['gross_salary']) ? $prev_gross_sal = intval($val['prev']['pm']['gross_salary']) : $prev_gross_sal = 0; ?></td>
                                                <?php $l++;
                                                $total[$l] += $prev_gross_sal; ?>
                                                <td><?php echo isset($val['current']['pm']['gross_salary']) ? $curr_gross_sal = intval($val['current']['pm']['gross_salary']) : $curr_gross_sal = 0; ?></td>
                                                <?php $l++;
                                                $total[$l] += $curr_gross_sal; ?>
                                                <td><?php echo abs($curr_gross_sal - $prev_gross_sal); ?></td>
                                                <?php $l++;
                                                $total[$l] += abs($curr_gross_sal - $prev_gross_sal); ?>
                                                <?php $prev_total_ded = isset($val['prev']['pm']['total_deduction']) ? abs(intval($val['prev']['pm']['total_deduction'])) : 0; ?>
                                                <?php $prev_total_sal = isset($val['prev']['pm']['total_salary']) ? abs(intval($val['prev']['pm']['total_salary'])) : 0; ?>
                                                <td><?php echo ($prev_netsalary = ($prev_total_sal - $prev_total_ded)); ?></td>
                                                <?php $l++;
                                                $total[$l] += $prev_netsalary; ?>
                                                <?php $curr_total_ded = isset($val['current']['pm']['total_deduction']) ? abs(intval($val['current']['pm']['total_deduction'])) : 0; ?>
                                                <?php $curr_total_sal = isset($val['current']['pm']['total_salary']) ? abs(intval($val['current']['pm']['total_salary'])) : 0; ?>
                                                <td><?php echo ($curr_netsalary = ($curr_total_sal - $curr_total_ded)); ?></td>
                                                <?php $l++;
                                                $total[$l] += $curr_netsalary; ?>
                                                <td><?php echo abs($curr_netsalary - $prev_netsalary); ?></td>
                                                <?php $l++;
                                                $total[$l] += abs($curr_netsalary - $prev_netsalary); ?>
                                                <!-- CTC Amount -->
                                                <td><?php echo isset($val['prev']['pm']['ctc_amount']) ? $prev_ctc_amt = intval($val['prev']['pm']['ctc_amount']) : $prev_ctc_amt = 0; ?></td>
                                                <?php $l++;
                                                $total[$l] += $prev_ctc_amt; ?>
                                                <td><?php echo isset($val['current']['pm']['ctc_amount']) ? $current_ctc_amt = intval($val['current']['pm']['ctc_amount']) : $current_ctc_amt = 0; ?></td>
                                                <?php $l++;
                                                $total[$l] += $current_ctc_amt; ?>
                                                <td><?php echo abs($current_ctc_amt - $prev_ctc_amt); ?></td>
                                                <?php $l++;
                                                $total[$l] += abs($current_ctc_amt - $prev_ctc_amt);

                                                if (isset($val['prev']['prodata']['type']))
                                                    if ($val['prev']['prodata']['type'] == 'Working Days') {
                                                        $leave_total = isset($val['prev']['ectc']['leave_total'])? floatval($val['prev']['ectc']['leave_total']): 0;
                                                        $prev_pay_days = floatval($val['prev']['prodata']['present']) + $leave_total;
                                                    } elseif ($val['prev']['prodata']['type'] == 'Calender Days') {
                                                        $pres_days = isset($val['prev']['ar']['presant_total']) ? floatval($val['prev']['ar']['presant_total']) : 0;
                                                        $leave_days = isset($val['prev']['ar']['leave_total']) ? floatval($val['prev']['ar']['leave_total']) : 0;
                                                        $holiday = isset($val['prev']['ar']['holiday_total']) ? floatval($val['prev']['ar']['holiday_total']) : 0;
                                                        $week_off = isset($val['prev']['ar']['weekoff_total']) ? floatval($val['prev']['ar']['weekoff_total']) : 0;

                                                        $prev_pay_days = $pres_days + $leave_days + $holiday + $week_off;
                                                    } else {
                                                        $prev_pay_days = floatval($val['prev']['prodata']['days']);
                                                    }

                                                if (isset($val['current']['prodata']['type']))
                                                    if ($val['current']['prodata']['type'] == 'Working Days') {
                                                        $leave_total = isset($val['current']['ectc']['leave_total'])? floatval($val['current']['ectc']['leave_total']): 0;
                                                        $current_pay_days = isset($val['current']['prodata']['present']) ? floatval($val['current']['prodata']['present']) : 0;
                                                        $current_pay_days += $leave_total;
                                                    } elseif ($val['current']['prodata']['type'] == 'Calender Days') {
                                                        $pres_days = isset($val['current']['ar']['presant_total']) ? floatval($val['current']['ar']['presant_total']) : 0;
                                                        $leave_days = isset($val['current']['ar']['leave_total']) ? floatval($val['current']['ar']['leave_total']) : 0;
                                                        $holiday = isset($val['current']['ar']['holiday_total']) ? floatval($val['current']['ar']['holiday_total']) : 0;
                                                        $week_off = isset($val['current']['ar']['weekoff_total']) ? floatval($val['current']['ar']['weekoff_total']) : 0;

                                                        $current_pay_days = $pres_days + $leave_days + $holiday + $week_off;
                                                        
                                                        //$current_pay_days = (isset($val['current']['prodata']['days']) ? floatval($val['current']['prodata']['days']) : 0) - (isset($val['current']['ectc']['lop_total']) ? floatval($val['current']['ectc']['lop_total']) : 0);
                                                    } else {
                                                        $current_pay_days = isset($val['current']['prodata']['days']) ? floatval($val['current']['prodata']['days']) : 0;
                                                    }
                                                $prev_pay_days = isset($prev_pay_days) ? $prev_pay_days : 0;
                                                $current_pay_days = isset($current_pay_days) ? $current_pay_days : 0;
                                                ?>

                                                <td><?php echo isset($prev_pay_days) ? floatval($prev_pay_days) :  0; ?></td>
                                                <?php $l++;
                                                $total[$l] += isset($prev_pay_days) ? floatval($prev_pay_days) :  0; ?>
                                                <td><?php echo isset($current_pay_days) ? floatval($current_pay_days) : 0; ?></td>
                                                <?php $l++;
                                                $total[$l] += isset($current_pay_days) ? floatval($current_pay_days) : 0; ?>
                                                <td><?php echo abs($current_pay_days - $prev_pay_days); ?></td>
                                                <?php $l++;
                                                $total[$l] += abs($current_pay_days - $prev_pay_days);  ?>

                                                <?php $prev_var_add = isset($val['prev']['pm']['var_add_count']) ? abs(($val['prev']['pm']['var_add_count'])) : 0; ?>
                                                <?php $current_var_add = isset($val['current']['pm']['var_add_count']) ? abs(($val['current']['pm']['var_add_count'])) : 0; ?>
                                                <td><?php echo $prev_var_add; ?></td>
                                                <?php $l++;
                                                $total[$l] += $prev_var_add; ?>
                                                <td><?php echo $current_var_add; ?></td>
                                                <?php $l++;
                                                $total[$l] += $current_var_add; ?>
                                                <td class="<?php echo ($current_var_add == $prev_var_add) ? '' : 'change-identified'; ?>">
                                                    <?php echo ($current_var_add == $prev_var_add) ? 'No change' : 'Change identified'; ?>
                                                </td>
                                                <?php $l++;
                                                $total[$l] = ''; ?>

                                                <?php $prev_var_ded = isset($val['prev']['pm']['var_ded_count']) ? abs(($val['prev']['pm']['var_ded_count'])) : 0; ?>
                                                <?php $current_var_ded = isset($val['current']['pm']['var_ded_count']) ? abs(($val['current']['pm']['var_ded_count'])) : 0; ?>
                                                <td><?php echo $prev_var_ded; ?></td>
                                                <?php $l++;
                                                $total[$l] += $prev_var_ded; ?>
                                                <td><?php echo $current_var_ded; ?></td>
                                                <?php $l++;
                                                $total[$l] += $current_var_ded; ?>
                                                <td class="<?php echo ($current_var_ded == $prev_var_ded) ? '' : 'change-identified'; ?>">
                                                    <?php echo ($current_var_ded == $prev_var_ded) ? 'No change' : 'Change identified'; ?>
                                                </td>
                                                <?php $l++;
                                                $total[$l] = ''; ?>


                                                <!-- Bank details -->
                                                <td><?php echo isset($val['prev']['pm']['bank_details']) ? $prev_bank = preg_replace("/^,|,$/", "", preg_replace("/,(\s*,)+/", ",", $val['prev']['pm']['bank_details'])) : $prev_bank = ''; ?></td>
                                                <?php
                                                // $prev_bank = preg_replace("/^,|,$/", "", $prev_bank);
                                                $l++;
                                                $total[$l] = ''; ?>
                                                <td><?php echo isset($val['current']['pm']['bank_details']) ? $curr_bank = preg_replace("/^,|,$/", "", preg_replace("/,(\s*,)+/", ",", $val['current']['pm']['bank_details'])) : $curr_bank = ''; ?></td>
                                                <?php
                                                // $curr_bank = preg_replace("/^,|,$/", "", $curr_bank);
                                                $l++;
                                                $total[$l] = ''; ?>
                                                <td class="<?php echo ($curr_bank == $prev_bank) ? 'no-change' : 'change-identified'; ?>">
                                                    <?php echo ($curr_bank == $prev_bank) ? 'No change' : 'Change identified'; ?>
                                                </td>
                                                <?php $l++;
                                                $total[$l] = ''; ?>




                                                <?php $prev_gross_sal = isset($val['prev']['pm']['gross_salary']) ? intval($val['prev']['pm']['gross_salary']) : 0; ?>
                                                <?php $prev_total_ded = isset($val['prev']['pm']['total_deduction']) ? abs(intval($val['prev']['pm']['total_deduction'])) : 0; ?>
                                                <?php $prev_total_salary = isset($val['prev']['pm']['total_salary']) ? abs(intval($val['prev']['pm']['total_salary'])) : 0; ?>
                                                <?php $prev_netsalary = ($prev_total_salary - $prev_total_ded); ?>


                                                <?php $curr_gross_sal = isset($val['current']['pm']['gross_salary']) ? intval($val['current']['pm']['gross_salary']) : 0; ?>
                                                <?php $curr_total_ded = isset($val['current']['pm']['total_deduction']) ? abs(intval($val['current']['pm']['total_deduction'])) : 0; ?>
                                                <?php $curr_total_sal = isset($val['current']['pm']['total_salary']) ? abs(intval($val['current']['pm']['total_salary'])) : 0; ?>
                                                <?php $curr_netsalary = ($curr_total_sal - $curr_total_ded); ?>

                                                <!-- <td></td> -->

                                                <?php $j++; ?>
                                            </tr>
                                        <?php
                                            // }
                                        } ?>
                                        <tr>
                                            <?php
                                            // for($num=0;$num<9;$num++){
                                            ?>
                                            <!-- edited by athira on 08-07-2025 -->
                                             <?php  if($company_code =='DEMO' || $company_code =='SRTS' || $company_code=='GLET') { ?>
                                            <th colspan="12" style="text-align: center;">TOTAL</th>
                                            <?php } else { ?>
                                                <th colspan="10" style="text-align: center;">TOTAL</th>
                                                <?php } ?>
                                            <!-- end -->
                                            <?php
                                            foreach ($total as $key => $total1) { ?>
                                                <?php if ($key > $l) {
                                                    break; // Stop the loop if $key > $l
                                                } ?>
                                                <th><?php echo isset($total1) ? $total1 : 0; ?></th>

                                            <?php  }
                                            ?>
                                            <!-- <th></th> -->
                                        </tr>
                                    </tbody>



                                </table>
                            </div>
                            <br>


                        <?php }
                    } else { //Employee wise listing 
                        ?>

                        <?php

                        foreach ($gross as $key => $val) {
                            $monthly_data1[$key]['current'] = $val;
                        }

                        foreach ($gross1 as $key => $val) {
                            $monthly_data1[$key]['prev'] = $val;
                        }
                        // debug(($monthly_data1)); exit;
                        foreach ($arr_emp_pkey as $fkey) {
                            $pkey = isset($fkey['pm']['emp_fkey']) ? $fkey['pm']['emp_fkey'] : 0;
                            if ($pkey != 0) {
                                $temp_arr = isset($monthly_data1[$pkey]) ? $monthly_data1[$pkey] : '';
                                if ($temp_arr != '') {
                                    $monthly_data[$pkey] = $temp_arr;
                                }
                            }
                        }

                        ?>

                        <?php
                        //    debug($monthly_data);exit;
                        foreach ($monthly_data1 as  $val) {
                            //$branches = current($brnch);
                            $name = '';

                            $total = array();
                            for ($num = 10; $num < 200; $num++) {
                                $total[$num] = 0;
                            }

                            if (isset($val['current']['emp_info']['EmpName'])) {
                                $name = $val['current']['emp_info']['EmpName'];
                            } elseif (isset($val['prev']['emp_info']['EmpName'])) {
                                $name = $val['prev']['emp_info']['EmpName'];
                            }

                            $empstatus = '';

                            if (isset($val['current']['emp_details']['status']) && $val['current']['emp_details']['status'] == "2") {
                                $empstatus = ' (Resigned)';
                            } elseif (isset($val['prev']['emp_details']['status']) && $val['prev']['emp_details']['status'] == "2") {
                                $empstatus = ' (Resigned)';
                            }

                            $j = 1; ?>
                            <div class="box-body" style="overflow-y:auto;">
                                <legend style="border: 0;"><?php echo $name . $empstatus; ?></legend>
                                <table class="table table-bordered" id="table1" style="border-collapse: collapse;">
                                    <thead>
                                        <tr>
                                            <!-- edited by athira on 08-07-2025 -->
                                             <?php  if($company_code =='DEMO' || $company_code =='SRTS' || $company_code=='GLET') { ?>
                                            <th colspan="12" style="text-align: center;">Employee Details</th>
                                            <?php } else { ?>
                                                <th colspan="10" style="text-align: center;">Employee Details</th>
                                                <?php } ?>
                                            <!-- end -->
                                            <th colspan='3' style="text-align: center;">CTC (Standard)</th>
                                            <th colspan='3' style="text-align: center;">Gross Salary (Standard)</th>
                                            <!-- Standard additions -->
                                            <?php foreach ($items as $val1) { ?>
                                                <th colspan='3' style="text-align:center;"><?php echo $val1; ?></th>
                                            <?php } ?>

                                            <th colspan='3' style="text-align: center;">Gross Salary (Actual)</th>
                                            <th colspan='3' style="text-align: center;">Net Salary (Actual)</th>
                                            <th colspan='3' style="text-align: center;">CTC (Actual)</th>
                                            <th colspan='3' style="text-align: center;">Pay Days</th>
                                            <th colspan='3' style="text-align: center;">Variable Additions (Count)</th>
                                            <th colspan='3' style="text-align: center;">Variable Deductions (Count)</th>
                                            <th colspan='3' style="text-align: center;">Bank Account</th>


                                            <!-- <th colspan="9" style="text-align: center;">Salary Details <?php echo date('F Y', strtotime($prev_month)); ?></th>
                                            <th colspan="9" style="text-align: center;">Salary Details <?php echo date('F Y', strtotime($month)); ?></th>
                                            <th rowspan="2">Gross Salary Difference</th>
                                            <th rowspan="2">Total Deduction Difference</th>
                                            <th rowspan="2">Net Salary Difference</th> -->
                                            <!-- <th rowspan="2" style="border-right: none;">Reason</th> -->
                                        </tr>
                                        <tr style="margin: auto;">
                                            <th>Sl No</th>
                                            <th>Employee ID</th>
                                             <!-- edited by athira on 08-07-2025 -->
                                            <?php  if($company_code =='DEMO' || $company_code =='SRTS' || $company_code=='GLET') { ?>
                                            <th>Employee ID (US Format)</th>
                                        <?php } ?>
                                        <!-- end -->
                                            <th>User ID</th>
                                            <th>Employee Name</th>
                                            <!-- edited by athira on 08-07-2025 -->
                                            <?php  if($company_code =='DEMO' || $company_code =='SRTS' || $company_code=='GLET') { ?>
                                            <th>Employee Name (US Format)</th>
                                        <?php } ?>
                                        <!-- end -->
                                            <th>Joining Date</th>
                                            <th>Branch</th>
                                            <th>Department</th>
                                            <th>Designation</th>
                                            <th>Termination Date</th>
                                            <th>Status</th>
                                            <th> <?php echo date('F', strtotime($prev_month)); ?></th>
                                            <th> <?php echo date('F', strtotime($month)); ?></th>
                                            <th>Difference</th>
                                            <th> <?php echo date('F', strtotime($prev_month)); ?></th>
                                            <th> <?php echo date('F', strtotime($month)); ?></th>
                                            <th>Difference</th>
                                            <!-- Standard additions -->
                                            <?php
                                            foreach ($items as $val1) { ?>
                                                <th> <?php echo date('F', strtotime($prev_month)); ?></th>
                                                <th> <?php echo date('F', strtotime($month)); ?></th>
                                                <th>Difference</th>
                                            <?php }
                                            ?>

                                            <th> <?php echo date('F', strtotime($prev_month)); ?></th>
                                            <th> <?php echo date('F', strtotime($month)); ?></th>
                                            <th>Difference</th>
                                            <th> <?php echo date('F', strtotime($prev_month)); ?></th>
                                            <th> <?php echo date('F', strtotime($month)); ?></th>
                                            <th>Difference</th>
                                            <th> <?php echo date('F', strtotime($prev_month)); ?></th>
                                            <th> <?php echo date('F', strtotime($month)); ?></th>
                                            <th>Difference</th>
                                            <th> <?php echo date('F', strtotime($prev_month)); ?></th>
                                            <th> <?php echo date('F', strtotime($month)); ?></th>
                                            <th>Difference</th>
                                            <th> <?php echo date('F', strtotime($prev_month)); ?></th>
                                            <th> <?php echo date('F', strtotime($month)); ?></th>
                                            <th>Difference</th>
                                            <th> <?php echo date('F', strtotime($prev_month)); ?></th>
                                            <th> <?php echo date('F', strtotime($month)); ?></th>
                                            <th>Difference</th>
                                            <th> <?php echo date('F', strtotime($prev_month)); ?></th>
                                            <th> <?php echo date('F', strtotime($month)); ?></th>



                                            <th>Difference</th>



                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        // debug($brnch);
                                        // foreach ($brnch as $key => $val) {
                                        // debug($val);continue;
                                        $empstatus = isset($val['current']['emp_info']['emp_status']) ? $val['current']['emp_info']['emp_status'] : (isset($val['prev']['emp_info']['emp_status']) ? $val['prev']['emp_info']['emp_status'] : '');
                                        if ($empstatus == 2) {
                                            $empstatus = ' (Resigned)';
                                        } else {
                                            $empstatus = '';
                                        }
                                        //   if(trim(isset($val['current']['emp_info']['branch']) ? $val['current']['emp_info']['branch'] : (isset($val['prev']['emp_info']['branch']) ? $val['prev']['emp_info']['branch'] : '')) == trim($branch)){
                                        // debug($val);
                                        ?>


                                        <tr>
                                            <td><?php echo ($j); ?></td>
                                            <td><?php echo (isset($val['current']['emp_info']['employee_id']) ? $val['current']['emp_info']['employee_id'] : (isset($val['prev']['emp_info']['employee_id']) ? $val['prev']['emp_info']['employee_id'] : '')); ?></td>
                                             <!-- edited by athira on 08-07-2025 -->
                                            <?php  if($company_code =='DEMO' || $company_code =='SRTS' || $company_code=='GLET') { ?>
                                                <td><?php echo (isset($val['current']['emp_info']['emp_us_id']) ? $val['current']['emp_info']['emp_us_id'] : (isset($val['prev']['emp_info']['emp_us_id']) ? $val['prev']['emp_info']['emp_us_id'] : '')); ?></td>
                                                <?php } ?>
                                                <!-- end -->
                                            <td><?php echo (isset($val['current']['user_credentials']['user_id']) ? $val['current']['user_credentials']['user_id'] : (isset($val['prev']['user_credentials']['user_id']) ? $val['prev']['user_credentials']['user_id'] : '')); ?></td>
                                            <td><?php echo (isset($val['current']['emp_info']['EmpName']) ? $val['current']['emp_info']['EmpName'] . $empstatus : (isset($val['prev']['emp_info']['EmpName']) ? $val['prev']['emp_info']['EmpName'] . $empstatus : '')); ?></td>
                                            <!-- edited by athira on 08-07-2025 -->
                                            <?php  if($company_code =='DEMO' || $company_code =='SRTS' || $company_code=='GLET') { ?>
                                                <td><?php echo (isset($val['current']['emp_info']['EmpUSName']) ? $val['current']['emp_info']['EmpUSName'] : (isset($val['prev']['emp_info']['EmpUSName']) ? $val['prev']['emp_info']['EmpUSName'] : '')); ?></td>
                                                <?php } ?>
                                                <!-- end -->
                                            <td><?php
                                                echo (isset($val['current']['emp_info']['joining_date']) ? date('d-m-Y', strtotime($val['current']['emp_info']['joining_date'])) : (isset($val['prev']['emp_info']['joining_date']) ? date('d-m-Y', strtotime($val['prev']['emp_info']['joining_date'])) : ''));
                                                ?></td>

                                            <td><?php echo (isset($val['current']['pm']['branch_name']) ? $val['current']['pm']['branch_name'] : (isset($val['current']['emp_info']['branch']) ? $val['current']['emp_info']['branch'] : (isset($val['prev']['emp_info']['branch']) ? $val['prev']['emp_info']['branch'] : ''))); ?></td>
                                            <td><?php echo (isset($val['current']['pm']['departments']) ? $val['current']['pm']['departments'] : (isset($val['current']['emp_info']['department']) ? $val['current']['emp_info']['department'] : (isset($val['prev']['emp_info']['department']) ? $val['prev']['emp_info']['department'] : ''))); ?></td>
                                            <td><?php echo (isset($val['current']['pm']['desig']) ? $val['current']['pm']['desig'] : (isset($val['current']['emp_info']['designation']) ? $val['current']['emp_info']['designation'] : (isset($val['prev']['emp_info']['designation']) ? $val['prev']['emp_info']['designation'] : ''))); ?></td>
                                            <td><?php
                                                $lastApprovedWorkingDate = '';
                                                if (isset($val['current']['termination']['last_approved_working_date']) && trim($val['current']['termination']['last_approved_working_date']) !== '') {
                                                    $lastApprovedWorkingDate = date('d-m-Y', strtotime($val['current']['termination']['last_approved_working_date']));
                                                } elseif (isset($val['prev']['termination']['last_approved_working_date']) && trim($val['prev']['termination']['last_approved_working_date']) !== '') {
                                                    $lastApprovedWorkingDate = date('d-m-Y', strtotime($val['prev']['termination']['last_approved_working_date']));
                                                }
                                                echo $lastApprovedWorkingDate;
                                                ?></td>

                                            <?php
                                            $prev_var_add = isset($val['prev']['pm']['var_add_count']) ? abs(($val['prev']['pm']['var_add_count'])) : 0;
                                            $current_var_add = isset($val['current']['pm']['var_add_count']) ? abs(($val['current']['pm']['var_add_count'])) : 0;

                                            $prev_var_ded = isset($val['prev']['pm']['var_ded_count']) ? abs(($val['prev']['pm']['var_ded_count'])) : 0;
                                            $current_var_ded = isset($val['current']['pm']['var_ded_count']) ? abs(($val['current']['pm']['var_ded_count'])) : 0;

                                            isset($val['prev']['pm']['bank_details']) ? $prev_bank = ($val['prev']['pm']['bank_details']) : $prev_bank = '';
                                            isset($val['current']['pm']['bank_details']) ? $curr_bank = ($val['current']['pm']['bank_details']) : $curr_bank = '';

                                            isset($val['prev']['pm']['ctc_rate']) ? $prev_ctc_rate = intval($val['prev']['pm']['ctc_rate']) : $prev_ctc_rate = 0;
                                            isset($val['current']['pm']['ctc_rate']) ? $current_ctc_rate = intval($val['current']['pm']['ctc_rate']) : $current_ctc_rate = 0;

                                            isset($val['prev']['pm']['gross_salary_standard']) ? $prev_gross_sal_std = intval($val['prev']['pm']['gross_salary_standard']) : $prev_gross_sal_std = 0;
                                            isset($val['current']['pm']['gross_salary_standard']) ? $curr_gross_sal_std = intval($val['current']['pm']['gross_salary_standard']) : $curr_gross_sal_std = 0;

                                            $item_value_change = 0;
                                            foreach ($items as $key => $val1) {
                                                isset($val['prev']['items'][$key]['value']) ? $prev_value = round(abs($val['prev']['items'][$key]['value'])) : $prev_value = 0;
                                                isset($val['current']['items'][$key]['value']) ? $current_value = round(abs($val['current']['items'][$key]['value'])) : $current_value = 0;
                                                if ($prev_value != $current_value) {
                                                    $item_value_change++;
                                                }
                                            }

                                            isset($val['prev']['pm']['gross_salary']) ? $prev_gross_sal = intval($val['prev']['pm']['gross_salary']) : $prev_gross_sal = 0;
                                            isset($val['current']['pm']['gross_salary']) ? $curr_gross_sal = intval($val['current']['pm']['gross_salary']) : $curr_gross_sal = 0;

                                            $prev_total_ded = isset($val['prev']['pm']['total_deduction']) ? abs(intval($val['prev']['pm']['total_deduction'])) : 0;
                                            $prev_total_sal = isset($val['prev']['pm']['total_salary']) ? abs(intval($val['prev']['pm']['total_salary'])) : 0;
                                            $prev_netsalary = ($prev_total_sal - $prev_total_ded);
                                            $curr_total_ded = isset($val['current']['pm']['total_deduction']) ? abs(intval($val['current']['pm']['total_deduction'])) : 0;
                                            $curr_total_sal = isset($val['current']['pm']['total_salary']) ? abs(intval($val['current']['pm']['total_salary'])) : 0;
                                            $curr_netsalary = ($curr_total_sal - $curr_total_ded);

                                            isset($val['prev']['pm']['ctc_amount']) ? $prev_ctc_amt = intval($val['prev']['pm']['ctc_amount']) : $prev_ctc_amt = 0;
                                            isset($val['current']['pm']['ctc_amount']) ? $current_ctc_amt = intval($val['current']['pm']['ctc_amount']) : $current_ctc_amt = 0;

                                            if (isset($val['prev']['prodata']['type']))
                                                if ($val['prev']['prodata']['type'] == 'Working Days') {
                                                    $leave_total = isset($val['prev']['ectc']['leave_total'])? floatval($val['prev']['ectc']['leave_total']): 0;
                                                    $prev_pay_days = floatval($val['prev']['prodata']['present']) +  $leave_total;
                                                } elseif ($val['prev']['prodata']['type'] == 'Calender Days') {
                                                    $pres_days = isset($val['prev']['ar']['presant_total']) ? floatval($val['prev']['ar']['presant_total']) : 0;
                                                    $leave_days = isset($val['prev']['ar']['leave_total']) ? floatval($val['prev']['ar']['leave_total']) : 0;
                                                    $holiday = isset($val['prev']['ar']['holiday_total']) ? floatval($val['prev']['ar']['holiday_total']) : 0;
                                                    $week_off = isset($val['prev']['ar']['weekoff_total']) ? floatval($val['prev']['ar']['weekoff_total']) : 0;

                                                    $prev_pay_days = $pres_days + $leave_days + $holiday + $week_off;
                                                } else {
                                                    $prev_pay_days = floatval($val['prev']['prodata']['days']);
                                                }

                                            if (isset($val['current']['prodata']['type']))
                                                if ($val['current']['prodata']['type'] == 'Working Days') {
                                                    $leave_total = isset($val['current']['ectc']['leave_total'])? floatval($val['current']['ectc']['leave_total']): 0;
                                                    $current_pay_days = isset($val['current']['prodata']['present']) ? floatval($val['current']['prodata']['present']) : 0;
                                                    $current_pay_days += $leave_total;
                                                } elseif ($val['current']['prodata']['type'] == 'Calender Days') {
                                                    $pres_days = isset($val['current']['ar']['presant_total']) ? floatval($val['current']['ar']['presant_total']) : 0;
                                                    $leave_days = isset($val['current']['ar']['leave_total']) ? floatval($val['current']['ar']['leave_total']) : 0;
                                                    $holiday = isset($val['current']['ar']['holiday_total']) ? floatval($val['current']['ar']['holiday_total']) : 0;
                                                    $week_off = isset($val['current']['ar']['weekoff_total']) ? floatval($val['current']['ar']['weekoff_total']) : 0;

                                                    $current_pay_days = $pres_days + $leave_days + $holiday + $week_off;

                                                    // $current_pay_days = (isset($val['current']['prodata']['days']) ? floatval($val['current']['prodata']['days']) : 0) - (isset($val['current']['ectc']['lop_total']) ? floatval($val['current']['ectc']['lop_total']) : 0);
                                                } else {
                                                    $current_pay_days = isset($val['current']['prodata']['days']) ? floatval($val['current']['prodata']['days']) : 0;
                                                }
                                            $prev_pay_days = isset($prev_pay_days) ? $prev_pay_days : 0;
                                            $current_pay_days = isset($current_pay_days) ? $current_pay_days : 0;

                                            if (($prev_var_add ==  $current_var_add) && ($prev_var_ded == $current_var_ded) && ($prev_bank == $curr_bank) && ($prev_ctc_rate == $current_ctc_rate) && ($prev_gross_sal_std == $curr_gross_sal_std) && ($item_value_change == 0) && ($prev_gross_sal == $curr_gross_sal) && ($prev_netsalary == $curr_netsalary) && ($current_ctc_amt == $prev_ctc_amt) && ($prev_pay_days == $current_pay_days)) { ?>
                                                <td>No change</td>
                                            <?php } else { ?>
                                                <td style="color: white; background-color: red;">Change identified</td>
                                            <?php }
                                            ?>
                                            <!-- CTC Standard -->
                                            <td><?php echo isset($val['prev']['pm']['ctc_rate']) ? $prev_ctc_rate = intval($val['prev']['pm']['ctc_rate']) : $prev_ctc_rate = 0; ?></td>
                                            <?php $total[10] += $prev_ctc_rate; ?>
                                            <td><?php echo isset($val['current']['pm']['ctc_rate']) ? $current_ctc_rate = intval($val['current']['pm']['ctc_rate']) : $current_ctc_rate = 0; ?></td>
                                            <?php $total[11] += $current_ctc_rate; ?>
                                            <td><?php echo abs($current_ctc_rate - $prev_ctc_rate); ?></td>
                                            <?php $total[12] += abs($current_ctc_rate - $prev_ctc_rate); ?>

                                            <td><?php echo isset($val['prev']['pm']['gross_salary_standard']) ? $prev_gross_sal_std = intval($val['prev']['pm']['gross_salary_standard']) : $prev_gross_sal_std = 0; ?></td>
                                            <?php $total[13] += $prev_gross_sal_std; ?>

                                            <td><?php echo isset($val['current']['pm']['gross_salary_standard']) ? $curr_gross_sal_std = intval($val['current']['pm']['gross_salary_standard']) : $curr_gross_sal_std = 0; ?></td>
                                            <?php $total[14] += $curr_gross_sal_std; ?>

                                            <td><?php echo abs($curr_gross_sal_std - $prev_gross_sal_std); ?></td>
                                            <?php $total[15] += abs($curr_gross_sal_std - $prev_gross_sal_std); ?>

                                            <!-- Standard additions -->
                                            <?php
                                            $l = 15;
                                            foreach ($items as $key => $val1) {
                                                // debug($val);
                                            ?>
                                                <td><?php echo isset($val['prev']['items'][$key]['value']) ? $prev_value = round(abs($val['prev']['items'][$key]['value'])) : $prev_value = 0; ?></td>
                                                <?php $l++;
                                                $total[$l] +=  $prev_value; ?>
                                                <td><?php echo isset($val['current']['items'][$key]['value']) ? $current_value = round(abs($val['current']['items'][$key]['value'])) : $current_value = 0; ?></td>
                                                <?php $l++;
                                                $total[$l] +=   $current_value; ?>
                                                <td><?php echo abs($current_value - $prev_value) ?></td>
                                                <?php $l++;
                                                $total[$l] +=  abs($current_value - $prev_value); ?>
                                            <?php

                                            }
                                            // debug($total);
                                            ?>


                                            <td><?php echo isset($val['prev']['pm']['gross_salary']) ? $prev_gross_sal = intval($val['prev']['pm']['gross_salary']) : $prev_gross_sal = 0; ?></td>
                                            <?php $l++;
                                            $total[$l] += $prev_gross_sal; ?>
                                            <td><?php echo isset($val['current']['pm']['gross_salary']) ? $curr_gross_sal = intval($val['current']['pm']['gross_salary']) : $curr_gross_sal = 0; ?></td>
                                            <?php $l++;
                                            $total[$l] += $curr_gross_sal; ?>
                                            <td><?php echo abs($curr_gross_sal - $prev_gross_sal); ?></td>
                                            <?php $l++;
                                            $total[$l] += abs($curr_gross_sal - $prev_gross_sal); ?>
                                            <?php $prev_total_ded = isset($val['prev']['pm']['total_deduction']) ? abs(intval($val['prev']['pm']['total_deduction'])) : 0; ?>
                                            <?php $prev_total_sal = isset($val['prev']['pm']['total_salary']) ? abs(intval($val['prev']['pm']['total_salary'])) : 0; ?>
                                            <td><?php echo ($prev_netsalary = ($prev_total_sal - $prev_total_ded)); ?></td>
                                            <?php $l++;
                                            $total[$l] += $prev_netsalary; ?>
                                            <?php $curr_total_ded = isset($val['current']['pm']['total_deduction']) ? abs(intval($val['current']['pm']['total_deduction'])) : 0; ?>
                                            <?php $curr_total_sal = isset($val['current']['pm']['total_salary']) ? abs(intval($val['current']['pm']['total_salary'])) : 0; ?>
                                            <td><?php echo ($curr_netsalary = ($curr_total_sal - $curr_total_ded)); ?></td>
                                            <?php $l++;
                                            $total[$l] += $curr_netsalary; ?>
                                            <td><?php echo abs($curr_netsalary - $prev_netsalary); ?></td>
                                            <?php $l++;
                                            $total[$l] += abs($curr_netsalary - $prev_netsalary); ?>
                                            <!-- CTC Amount -->
                                            <td><?php echo isset($val['prev']['pm']['ctc_amount']) ? $prev_ctc_amt = intval($val['prev']['pm']['ctc_amount']) : $prev_ctc_amt = 0; ?></td>
                                            <?php $l++;
                                            $total[$l] += $prev_ctc_amt; ?>
                                            <td><?php echo isset($val['current']['pm']['ctc_amount']) ? $current_ctc_amt = intval($val['current']['pm']['ctc_amount']) : $current_ctc_amt = 0; ?></td>
                                            <?php $l++;
                                            $total[$l] += $current_ctc_amt; ?>
                                            <td><?php echo abs($current_ctc_amt - $prev_ctc_amt); ?></td>
                                            <?php $l++;
                                            $total[$l] += abs($current_ctc_amt - $prev_ctc_amt);
                                            if (isset($val['prev']['prodata']['type']))
                                                if ($val['prev']['prodata']['type'] == 'Working Days') {
                                                    $leave_total = isset($val['prev']['ectc']['leave_total'])? floatval($val['prev']['ectc']['leave_total']): 0;
                                                    $prev_pay_days = floatval($val['prev']['prodata']['present']) + $leave_total;
                                                } elseif ($val['prev']['prodata']['type'] == 'Calender Days') {
                                                    $pres_days = isset($val['prev']['ar']['presant_total']) ? floatval($val['prev']['ar']['presant_total']) : 0;
                                                    $leave_days = isset($val['prev']['ar']['leave_total']) ? floatval($val['prev']['ar']['leave_total']) : 0;
                                                    $holiday = isset($val['prev']['ar']['holiday_total']) ? floatval($val['prev']['ar']['holiday_total']) : 0;
                                                    $week_off = isset($val['prev']['ar']['weekoff_total']) ? floatval($val['prev']['ar']['weekoff_total']) : 0;

                                                    $prev_pay_days = $pres_days + $leave_days + $holiday + $week_off;

                                                } else {
                                                    $prev_pay_days = floatval($val['prev']['prodata']['days']);
                                                }

                                            if (isset($val['current']['prodata']['type']))
                                                if ($val['current']['prodata']['type'] == 'Working Days') {
                                                    $leave_total = isset($val['current']['ectc']['leave_total'])? floatval($val['current']['ectc']['leave_total']): 0;
                                                    $current_pay_days = isset($val['current']['prodata']['present']) ? floatval($val['current']['prodata']['present']) : 0;
                                                    $current_pay_days += $leave_total;
                                                } elseif ($val['current']['prodata']['type'] == 'Calender Days') {
                                                    $pres_days = isset($val['current']['ar']['presant_total']) ? floatval($val['current']['ar']['presant_total']) : 0;
                                                    $leave_days = isset($val['current']['ar']['leave_total']) ? floatval($val['current']['ar']['leave_total']) : 0;
                                                    $holiday = isset($val['current']['ar']['holiday_total']) ? floatval($val['current']['ar']['holiday_total']) : 0;
                                                    $week_off = isset($val['current']['ar']['weekoff_total']) ? floatval($val['current']['ar']['weekoff_total']) : 0;

                                                    $current_pay_days = $pres_days + $leave_days + $holiday + $week_off;

                                                   // $current_pay_days = (isset($val['current']['prodata']['days']) ? floatval($val['current']['prodata']['days']) : 0) - (isset($val['current']['ectc']['lop_total']) ? floatval($val['current']['ectc']['lop_total']) : 0);
                                                } else {
                                                    $current_pay_days = isset($val['current']['prodata']['days']) ? floatval($val['current']['prodata']['days']) : 0;
                                                }
                                            $prev_pay_days = isset($prev_pay_days) ? $prev_pay_days : 0;
                                            $current_pay_days = isset($current_pay_days) ? $current_pay_days : 0;
                                            ?>
                                            <td><?php echo isset($prev_pay_days) ? floatval($prev_pay_days) : 0; ?></td>
                                            <?php $l++;
                                            $total[$l] += $prev_pay_days; ?>
                                            <td><?php echo isset($current_pay_days) ? floatval($current_pay_days) : 0; ?></td>
                                            <?php $l++;
                                            $total[$l] += $current_pay_days; ?>
                                            <td><?php echo abs($current_pay_days - $prev_pay_days); ?></td>
                                            <?php $l++;
                                            $total[$l] += abs($current_pay_days - $prev_pay_days);  ?>

                                            <?php $prev_var_add = isset($val['prev']['pm']['var_add_count']) ? abs(($val['prev']['pm']['var_add_count'])) : 0; ?>
                                            <?php $current_var_add = isset($val['current']['pm']['var_add_count']) ? abs(($val['current']['pm']['var_add_count'])) : 0; ?>
                                            <td><?php echo $prev_var_add; ?></td>
                                            <?php $l++;
                                            $total[$l] += $prev_var_add; ?>
                                            <td><?php echo $current_var_add; ?></td>
                                            <?php $l++;
                                            $total[$l] += $current_var_add; ?>
                                            <td class="<?php echo ($current_var_add == $prev_var_add) ? '' : 'change-identified'; ?>">
                                                <?php echo ($current_var_add == $prev_var_add) ? 'No change' : 'Change identified'; ?>
                                            </td>
                                            <?php $l++;
                                            $total[$l] = ''; ?>

                                            <?php $prev_var_ded = isset($val['prev']['pm']['var_ded_count']) ? abs(($val['prev']['pm']['var_ded_count'])) : 0; ?>
                                            <?php $current_var_ded = isset($val['current']['pm']['var_ded_count']) ? abs(($val['current']['pm']['var_ded_count'])) : 0; ?>
                                            <td><?php echo $prev_var_ded; ?></td>
                                            <?php $l++;
                                            $total[$l] += $prev_var_ded; ?>
                                            <td><?php echo $current_var_ded; ?></td>
                                            <?php $l++;
                                            $total[$l] += $current_var_ded; ?>
                                            <td class="<?php echo ($current_var_ded == $prev_var_ded) ? '' : 'change-identified'; ?>">
                                                <?php echo ($current_var_ded == $prev_var_ded) ? 'No change' : 'Change identified'; ?>
                                            </td>
                                            <?php $l++;
                                            $total[$l] = ''; ?>


                                            <!-- Bank details -->
                                            <td><?php echo isset($val['prev']['pm']['bank_details']) ? $prev_bank = preg_replace("/^,|,$/", "", preg_replace("/,(\s*,)+/", ",", $val['prev']['pm']['bank_details'])) : $prev_bank = ''; ?></td>
                                            <?php
                                            // $prev_bank = preg_replace("/^,|,$/", "", $prev_bank);
                                            $l++;
                                            $total[$l] = ''; ?>
                                            <td><?php echo isset($val['current']['pm']['bank_details']) ? $curr_bank = preg_replace("/^,|,$/", "", preg_replace("/,(\s*,)+/", ",", $val['current']['pm']['bank_details'])) : $curr_bank = ''; ?></td>
                                            <?php
                                            // $curr_bank = preg_replace("/^,|,$/", "", $curr_bank);
                                            $l++;
                                            $total[$l] = ''; ?>
                                            <td class="<?php echo ($curr_bank == $prev_bank) ? 'no-change' : 'change-identified'; ?>">
                                                <?php echo ($curr_bank == $prev_bank) ? 'No change' : 'Change identified'; ?>
                                            </td>
                                            <?php $l++;
                                            $total[$l] = ''; ?>




                                            <?php $prev_gross_sal = isset($val['prev']['pm']['gross_salary']) ? intval($val['prev']['pm']['gross_salary']) : 0; ?>
                                            <?php $prev_total_ded = isset($val['prev']['pm']['total_deduction']) ? abs(intval($val['prev']['pm']['total_deduction'])) : 0; ?>
                                            <?php $prev_total_salary = isset($val['prev']['pm']['total_salary']) ? abs(intval($val['prev']['pm']['total_salary'])) : 0; ?>
                                            <?php $prev_netsalary = ($prev_total_salary - $prev_total_ded); ?>


                                            <?php $curr_gross_sal = isset($val['current']['pm']['gross_salary']) ? intval($val['current']['pm']['gross_salary']) : 0; ?>
                                            <?php $curr_total_ded = isset($val['current']['pm']['total_deduction']) ? abs(intval($val['current']['pm']['total_deduction'])) : 0; ?>
                                            <?php $curr_total_sal = isset($val['current']['pm']['total_salary']) ? abs(intval($val['current']['pm']['total_salary'])) : 0; ?>
                                            <?php $curr_netsalary = ($curr_total_sal - $curr_total_ded); ?>

                                            <!-- <td></td> -->

                                            <?php $j++; ?>
                                        </tr>
                                        <?php
                                        ?>
                                        <tr>
                                            <?php
                                            // for($num=0;$num<9;$num++){
                                            ?>
                                          <!-- edited by athira on 08-07-2025 -->
                                             <?php  if($company_code =='DEMO' || $company_code =='SRTS' || $company_code=='GLET') { ?>
                                            <th colspan="12" style="text-align: center;">TOTAL</th>
                                            <?php } else { ?>
                                                <th colspan="10" style="text-align: center;">TOTAL</th>
                                                <?php } ?>
                                            <!-- end -->
                                            <?php
                                            foreach ($total as $key => $total1) { ?>
                                                <?php if ($key > $l) {
                                                    break; // Stop the loop if $key > $l
                                                } ?>
                                                <th><?php echo isset($total1) ? $total1 : 0; ?></th>

                                            <?php  }
                                            ?>
                                            <!-- <th></th> -->
                                        </tr>
                                    </tbody>



                                </table>
                            </div>
                            <br>


                    <?php }
                    } ?>

            </div>
        </div>

    </div>
<?php }
?>
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
            border: 1px solid #B2B2B2;
            width: 55px;
        }
    </style>

    <?php
    echo $this->element('reportadminheader', array(
        'title' => 'Gross Salary Detailed - ' . $month
    ));
    ?>
    <?php if (count($gross) <= 0) { ?>
        <div style="font-size: 25px;text-align:left; background-color:;">
            No data available under the selected criteria</div>
    <?php } else { ?>
        <br><br>
        <!--<h3><?php //echo $month;
                ?></h3>-->

        <?php $coun1 = isset($array_key['Addition']) ? count($array_key['Addition']) : 0;
        $coun2 = isset($array_key['Deduction']) ? count($array_key['Deduction']) : 0;
        $cont = $coun1 + $coun2 + 1;
        $cont2 = $cont + 3;

        ?>
        <!--                belongs to branch section added by megha start... pdf view section-->
        <?php if (isset($needBranchWiseReport) && $needBranchWiseReport == 1) { //do branchwise listing 
        ?>
            <?php foreach ($gross as $branch => $brnch) {
                $branches = current($brnch);  ?>
                <div class="box-body " style="overflow-y:auto; ">
                    <br>
                    <h2> <?php echo $branches['emp_info']['branch']; ?></h2>
                    <br>
                    <!--                    <fieldset>-->
                    <table class="table">
                        <thead>
                            <tr>
                                <th colspan="3">Employee Details</th>
                                <th colspan="<?php echo $cont2; ?>">Actual Salary</th>

                            </tr>
                            <tr style="background-color:#f0f0ff;">
                                <th>Sl No</th>
                                <th>Employee ID</th>
                                <th>Name</th>
                                <!--                            <th>Designation</th>
                            <th>Department</th>
                            <th>Branch</th>
                            <th>Total Days</th>
                            <th>Days Type</th>
                            <th>Present Days</th>
                            <th>Overtime (In Hrs.)</th>
                            <th>LOP Days</th>-->

                                <?php

                                $addition = $array_key['Addition'];
                                //debug($addition);
                                foreach ($addition as $value) { ?>
                                    <th><?php echo $value; ?></th>
                                <?php } ?>
                                <th>Gross Salary</th>
                                <?php if (isset($array_key['Deduction'])) { ?>
                                    <?php $deduction = $array_key['Deduction'];
                                    foreach ($deduction as $value) {
                                    ?>
                                        <th><?php echo $value; ?></th>
                                <?php }
                                }
                                ?>
                                <th>Total Deduction</th>
                                <th>Net Salary</th>



                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 1;
                            //                         debug($gross);
                            foreach ($brnch as $val) {
                                //debug($val);
                                if ($val['emp_info']['branch'] == $val['emp_info']['branch']) { ?>

                                    <tr>
                                        <td><?php echo $i; ?></td>
                                        <td><?php echo $val['emp_info']['employee_id']; ?></td>
                                        <td><?php echo $val['emp_info']['EmpName']; ?><?php echo (isset($val['emp_details']['status'])) && $val['emp_details']['status'] == "2" ? '  (Resigned)' : ''; ?></td>

                                        <?php $count_addition = count($val['Addition']['keys']);
                                        $grss_amt = 0;
                                        for ($m = 0; $m < $count_addition; $m++) {
                                            $number = round($val['Addition']['value'][$m], 2);
                                            $grss_amt = $number + $grss_amt; ?>
                                            <td><?php echo round($number); ?></td>
                                        <?php } ?>
                                        <td><?php echo round($grss_amt); ?></td>
                                        <?php $dd_amt = 0;
                                        if (isset($array_key['Deduction'])) { ?>
                                            <?php $count_addition = count($val['Deduction']['keys']);

                                            for ($m = 0; $m < $count_addition; $m++) {
                                                $number = round($val['Deduction']['value'][$m], 2);
                                                $dd_amt = abs($number) + $dd_amt;
                                            ?>
                                                <td><?php echo round($number); ?></td>
                                        <?php }
                                        }
                                        $netamt = $grss_amt + $dd_amt; ?>
                                        <td><?php echo round($dd_amt); ?></td>
                                        <td><?php echo round($netamt); ?></td>
                                    </tr>


                                <?php $i++;
                                }


                                ?>



                            <?php  } ?>
                        </tbody>
                    </table>
                    <!--                    </fieldset>-->
                </div>

            <?php }
        } else { ?>
            <!--                belongs to branch section added by megha end...-->
            <div class="box-body">
                <table class="table table-bordered">

                    <tr>
                        <th colspan="3">Employee Details</th>
                        <th colspan="<?php echo $cont2; ?>">Actual Salary</th>

                    </tr>
                    <tr style="background-color:#f0f0ff;">
                        <th style="width:20px;">Sl No</th>
                        <th style="width:70px;">Employee ID</th>
                        <th style="width:100px;">Name</th>


                        <?php

                        $addition = $array_key['Addition'];
                        //debug($addition);
                        foreach ($addition as $value) { ?>
                            <th><?php echo $value; ?></th>
                        <?php } ?>
                        <th>Gross <br>Salary</th>
                        <?php if (isset($array_key['Deduction'])) { ?>
                            <?php $deduction = $array_key['Deduction'];
                            foreach ($deduction as $value) {
                            ?>
                                <th><?php echo $value; ?></th>
                        <?php }
                        }
                        ?>
                        <th>Total <br> Deduction</th>
                        <th>Net <br>Salary</th>



                    </tr>

                    <tbody>
                        <?php
                        $i = 1;
                        // debug($gross);
                        foreach ($gross as $val) { ?>

                            <tr>
                                <td><?php echo $i; ?></td>
                                <td><?php echo $val['emp_info']['employee_id']; ?></td>
                                <td><?php echo $val['emp_info']['EmpName']; ?> </td>

                                <?php $count_addition = count($val['Addition']['keys']);
                                $grss_amt = 0;
                                for ($m = 0; $m < $count_addition; $m++) {
                                    $number = round($val['Addition']['value'][$m], 2);
                                    $grss_amt = $number + $grss_amt;


                                ?>
                                    <td><?php echo round($number); ?></td>


                                <?php } ?>
                                <td><?php echo round($grss_amt); ?></td>
                                <?php $dd_amt = 0;
                                if (isset($array_key['Deduction'])) { ?>
                                    <?php $count_addition = count($val['Deduction']['keys']);

                                    for ($m = 0; $m < $count_addition; $m++) {
                                        $number = round($val['Deduction']['value'][$m], 2);
                                        $dd_amt = abs($number) + $dd_amt;


                                    ?>
                                        <td><?php echo round($number); ?></td>
                                <?php }
                                }
                                $netamt = $grss_amt + $dd_amt;
                                ?>


                                <td><?php echo $dd_amt; ?></td>
                                <td><?php echo round($netamt); ?></td>


                            </tr>


                        <?php $i++;
                        }


                        ?>

                    </tbody>
                </table>
            </div>

<?php }
    }
} ?>