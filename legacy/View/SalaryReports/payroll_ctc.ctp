<style>
    .table,
    td,
    th {
        border-style: solid;
        border-color: #d4d4de;
    }
</style>

<?php  //debug($gross);   
?>
<?php if ($mode == '') { ?>
    <div class="modal-body" style="overflow-y:auto; ">

        <div class="row">
            <div class="col-md-12">

                <?php if (count($gross) <= 0) { ?>
                    <div style="font-size: 25px;text-align:center; background-color:#F7D3D2;">
                        There is no data available with respect to your report</div>
                <?php } else { ?>
                    <h2 align="center">Salary Combined Report - <?php echo $month; ?></h2>
                    <h4 align="center" style="font-weight:bold;">(<?php echo isset($user_id) ? "Report run by " . ($user_id) . " - " . $date_time : ''; ?>)</h4>
                    <?php $count1 = isset($array_key['Addition']) ? count($array_key['Addition']) : 0;
                    $count2 = isset($array_key['Deduction']) ? count($array_key['Deduction']) : 0;
                    $count3 = isset($variable_key['VAddition']) ? count($variable_key['VAddition']) : 0;
                    $count4 = isset($standard_key['SAddition']) ? count($standard_key['SAddition']) : 0;
                    $count5 = isset($standard_key['SDeduction']) ? count($standard_key['SDeduction']) : 0;
                    $count6 = $count4 + 1 + $count5;
                    $count7 = $count3 + 1;
                    $count8 = $count1 + 4 + $count2;
                    $count9 = isset($stdctc_key['stdctc']) ? count($stdctc_key['stdctc']) : 0;
                    $count10 = isset($actualctc_key['actualctc']) ? count($actualctc_key['actualctc']) : 0;
                    ?>
                    <!--belongs to branch section added by megha end... view section-->
                    <?php if (isset($needBranchWiseReport) && $needBranchWiseReport == 1) { //do branchwise listing 
                    ?>
                        <?php foreach ($gross as $branch => $brnch) {
                            //debug($brnch); 
                            $branches = current($brnch);  ?>
                            <div class="box-body " style="overflow-y:auto; ">
                                <fieldset>
                                    <legend><?php echo $branches['emp_info']['branch']; ?></legend>
                                </fieldset>
                                <br>
                                <fieldset>
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th colspan="14">Employee Details</th>
                                                <th colspan="<?php echo $count9 + 1; ?>">Standard CTC</th>
                                                <th colspan="<?php echo $count10 + 1; ?>">Actual CTC</th>
                                                <th colspan="<?php echo $count6; ?>">Standard Salary</th>
                                                <?php if ($count3 > 0) { ?>
                                                    <th colspan="<?php echo $count7; ?>">Other Salary</th>
                                                <?php } ?>
                                                <th colspan="<?php echo $count8; ?>">Actual Salary</th>
                                                <!--                            <th></th>-->

                                            </tr>
                                            <tr style="background-color:#f0f0ff;">
                                                <th>Sl No</th>
                                                <th>Employee ID</th>
                                                <th>Name</th>
                                                <th>Gender</th>
                                                <th>Month</th>
                                                <th>Designation</th>
                                                <th>Department</th>
                                                <th>Branch</th>
                                                <th>Present Days</th>
                                                <th>Overtime (In Hrs.)</th>
                                                <th>LOP Days</th>
                                                <th>Leave Days</th>
                                                <th>Week Off</th>
                                                <th>Holiday</th>


                                                <?php
                                                $stdctc = $stdctc_key['stdctc'];
                                                foreach ($stdctc as $value) { ?>
                                                    <th><?php echo $value; ?></th>
                                                <?php } ?>
                                                <th>Total</th>
                                                <?php
                                                $actualctc = $actualctc_key['actualctc'];
                                                foreach ($actualctc as $value) { ?>
                                                    <th><?php echo $value; ?></th>
                                                <?php } ?>
                                                <th>Total</th>
                                                <?php
                                                $addition = $standard_key['SAddition'];
                                                foreach ($addition as $value) { ?>
                                                    <th><?php echo $value; ?></th>
                                                <?php } ?>
                                                <th>Gross Salary</th>
                                                <?php
                                                if (isset($standard_key['SDeduction'])) {
                                                    $deduction = $standard_key['SDeduction'];
                                                    foreach ($deduction as $valu) { ?>
                                                        <th><?php echo $valu; ?></th>
                                                <?php }
                                                } ?>


                                                <?php if ($count3 > 0) {
                                                    $var_addition = $variable_key['VAddition'];
                                                    foreach ($var_addition as $value) { ?>
                                                        <th><?php echo $value; ?></th>
                                                    <?php } ?>
                                                    <th>Total</th>
                                                <?php } ?>
                                                <?php
                                                $addition = $array_key['Addition'];
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
                                                <th>Settlement Amount</th>
                                                <th>Net Salary</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $i = 1;
                                            foreach ($brnch as $val) {
                                                if ($val['emp_info']['branch'] == $val['emp_info']['branch']) {

                                                    if (isset($val['emp_details']['classification'])) {
                                                        switch (strtolower($val['emp_details']['classification'])) {
                                                            case 'male':
                                                                $val['emp_details']['classification'] = 'Male';
                                                                break;
                                                            case 'female':
                                                                $val['emp_details']['classification'] = 'Female';
                                                                break;
                                                            case 'other':
                                                                $val['emp_details']['classification'] = 'Transgender';
                                                                break;
                                                        }
                                                    }
                                            ?>

                                                    <tr>
                                                        <td><?php echo $i; ?></td>
                                                        <td><?php echo $val['emp_info']['employee_id']; ?></td>
                                                        <td><?php echo $val['emp_info']['EmpName']; ?><?php echo (isset($val['emp_details']['status'])) && $val['emp_details']['status'] == "2" ? '  (Resigned)' : ''; ?></td>
                                                        <td><?php echo $val['emp_details']['classification']; ?></td>
                                                        <td><?php echo $val['payroll_master']['month_year']; ?></td>
                                                        <td><?php echo $val['emp_info']['designation']; ?></td>
                                                        <td><?php echo $val['emp_info']['department']; ?></td>
                                                        <td><?php echo $val['emp_info']['branch']; ?></td>
                                                        <td><?php echo $val['ectc']['presant_total']; ?></td>
                                                        <td><?php echo isset($val['ot']) ? round(($val['ot'] / 60), 2) : 0; ?></td>
                                                        <td><?php echo isset($val['ectc']['lop_total']) ? $val['ectc']['lop_total'] : ''; ?></td>
                                                        <td><?php echo $val['ar']['leave_total']; ?></td>
                                                        <td><?php echo $val['ar']['weekoff_total']; ?></td>
                                                        <td><?php echo $val['ar']['holiday_total']; ?></td>

                                                        <?php $count_ctc = count($val['stdctc']['keys']);
                                                        $g_amt = 0;
                                                        for ($m = 0; $m < $count_ctc; $m++) {
                                                            $number = $val['stdctc']['actual'][$m];
                                                            $g_amt = $number + $g_amt;
                                                        ?>
                                                            <td><?php echo $number; ?></td>
                                                        <?php }  ?>
                                                        <td><?php echo round($g_amt); ?></td>

                                                        <?php $count_actualctc = count($val['actualctc']['keys']);
                                                        $gr_amt = 0;
                                                        for ($m = 0; $m < $count_actualctc; $m++) {
                                                            $number = $val['actualctc']['actual'][$m];
                                                            $gr_amt = $number + $gr_amt;
                                                        ?>
                                                            <td><?php echo $number; ?></td>
                                                        <?php }  ?>
                                                        <td><?php echo round($gr_amt); ?></td>


                                                        <?php $count_addition = count($val['SAddition']['keys']);
                                                        $grss_amt = 0;
                                                        for ($m = 0; $m < $count_addition; $m++) {
                                                            $number = round($val['SAddition']['actual'][$m]);
                                                            $grss_amt = $number + $grss_amt;
                                                        ?>
                                                            <td><?php echo round($number, 2); ?></td>
                                                        <?php }  ?>
                                                        <td><?php echo round($grss_amt, 2); ?></td>
                                                        <?php if (isset($standard_key['SDeduction'])) {
                                                            $count_addition = count($val['SDeduction']['keys']);
                                                            for ($m = 0; $m < $count_addition; $m++) {
                                                                $number = $val['SDeduction']['actual'][$m];
                                                        ?>
                                                                <td><?php echo round($number, 2); ?></td>
                                                        <?php }
                                                        } ?>

                                                        <?php if ($count3 > 0) {
                                                            $count_addition1 = count($val['VAddition']['keys']);
                                                            $grss_amt1 = 0;
                                                            for ($m = 0; $m < $count_addition1; $m++) {
                                                                $number1 = round($val['VAddition']['actual'][$m]);
                                                                $grss_amt1 = $number1 + $grss_amt1;
                                                        ?>
                                                                <td><?php echo round($number1, 2); ?></td>
                                                            <?php }  ?>
                                                            <td><?php echo round($grss_amt1, 2); ?></td>
                                                        <?php } ?>



                                                        <?php $count_addition = count($val['Addition']['keys']);
                                                        $grss_amt = 0;
                                                        for ($m = 0; $m < $count_addition; $m++) {
                                                            $number = round($val['Addition']['value'][$m]);
                                                            $grss_amt = $number + $grss_amt; ?>
                                                            <td><?php echo round($number); ?></td>
                                                        <?php } ?>
                                                        <td><?php echo round($grss_amt); ?></td>
                                                        <?php $dd_amt = 0;
                                                        if (isset($array_key['Deduction'])) { ?>
                                                            <?php $count_addition = count($val['Deduction']['keys']);

                                                            for ($m = 0; $m < $count_addition; $m++) {
                                                                $number = round($val['Deduction']['value'][$m], 2);
                                                                $dd_amt = $number + $dd_amt;
                                                            ?>
                                                                <td><?php echo round($number, 2); ?></td>
                                                                <!--                            //edited by megha on 13/11/2019 settlement amount 2-->
                                                        <?php }
                                                        }
                                                        $netamt = $grss_amt + $dd_amt + $val['settle']; ?>
                                                        <td><?php echo round($dd_amt, 2); ?></td>
                                                        <!--                            //edited by megha on 13/11/2019 settlement amount 3-->
                                                        <td><?php echo round($val['settle']); ?></td>
                                                        <td><?php echo round($netamt); ?></td>
                                                    </tr>


                                                <?php $i++;
                                                } ?>

                                            <?php  } ?>
                                        </tbody>
                                    </table>
                                </fieldset>
                            </div>

                        <?php }
                    } else { ?>
                        <!--belongs to branch section added by megha end...-->

                        <!--belongs to employee section start...-->
                        <div class="box-body">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th colspan="14">Employee Details</th>
                                        <th colspan="<?php echo $count9 + 1; ?>">Standard CTC</th>
                                        <th colspan="<?php echo $count10 + 1; ?>">Actual CTC</th>
                                        <th colspan="<?php echo $count6; ?>">Standard Salary</th>
                                        <?php if ($count3 > 0) { ?>
                                            <th colspan="<?php echo $count7; ?>">Other Salary</th>
                                        <?php } ?>
                                        <th colspan="<?php echo $count8; ?>">Actual Salary</th>
                                        <!--                            <th></th>-->

                                    </tr>
                                    <tr style="background-color:#f0f0ff;">
                                        <th>Sl No</th>
                                        <th>Employee ID</th>
                                        <th>Name</th>
                                        <th>Gender</th>
                                        <th>Month</th>
                                        <th>Designation</th>
                                        <th>Department</th>
                                        <th>Branch</th>
                                        <th>Present Days</th>
                                        <th>Overtime (In Hrs.)</th>
                                        <th>LOP Days</th>
                                        <th>Leave Days</th>
                                        <th>Week Off</th>
                                        <th>Holiday</th>

                                        <!--standard ctc...-->
                                        <?php
                                        $stdctc = $stdctc_key['stdctc'];
                                        foreach ($stdctc as $value) { ?>
                                            <th><?php echo $value; ?></th>
                                        <?php } ?>
                                        <th>Total</th>
                                        <?php
                                        $actualctc = $actualctc_key['actualctc'];
                                        foreach ($actualctc as $value) { ?>
                                            <th><?php echo $value; ?></th>
                                        <?php } ?>
                                        <th>Total</th>

                                        <!--std sal...-->
                                        <?php
                                        $addition = $standard_key['SAddition'];
                                        foreach ($addition as $value) { ?>
                                            <th><?php echo $value; ?></th>
                                        <?php } ?>
                                        <th>Gross Salary</th>
                                        <?php
                                        if (isset($standard_key['SDeduction'])) {
                                            $deduction = $standard_key['SDeduction'];
                                            foreach ($deduction as $valu) { ?>
                                                <th><?php echo $valu; ?></th>
                                        <?php }
                                        } ?>

                                        <!--other sal...-->
                                        <?php if ($count3 > 0) {
                                            $var_addition = $variable_key['VAddition'];
                                            foreach ($var_addition as $value) { ?>
                                                <th><?php echo $value; ?></th>
                                            <?php } ?>
                                            <th>Total</th>
                                        <?php } ?>

                                        <!--actual sal...-->
                                        <?php
                                        $addition = $array_key['Addition'];
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
                                        <!--  //edited by megha on 13/11/2019 settlement amount 1-->
                                        <th>Settlement Amount</th>
                                        <th>Net Salary</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $i = 1;
                                    //debug($gross);
                                    foreach ($gross as $val) {
                                        if (isset($val['emp_details']['classification'])) {
                                            switch (strtolower($val['emp_details']['classification'])) {
                                                case 'male':
                                                    $val['emp_details']['classification'] = 'Male';
                                                    break;
                                                case 'female':
                                                    $val['emp_details']['classification'] = 'Female';
                                                    break;
                                                case 'other':
                                                    $val['emp_details']['classification'] = 'Transgender';
                                                    break;
                                            }
                                        }
                                    ?>
                                        <tr>
                                            <td><?php echo $i; ?></td>
                                            <td><?php echo $val['emp_info']['employee_id']; ?></td>
                                            <td><?php echo $val['emp_info']['EmpName']; ?><?php echo (isset($val['emp_details']['status'])) && $val['emp_details']['status'] == "2" ? '  (Resigned)' : ''; ?></td>
                                            <td><?php echo $val['emp_details']['classification']; ?></td>
                                            <td><?php echo $val['payroll_master']['month_year']; ?></td>
                                            <td><?php echo $val['emp_info']['designation']; ?></td>
                                            <td><?php echo $val['emp_info']['department']; ?></td>
                                            <td><?php echo $val['emp_info']['branch']; ?></td>
                                            <td><?php echo $val['ectc']['presant_total']; ?></td>
                                            <td><?php echo isset($val['ot']) ? round(($val['ot'] / 60), 2) : 0; ?></td>
                                            <td><?php echo isset($val['ectc']['lop_total']) ? $val['ectc']['lop_total'] : ''; ?></td>
                                            <td><?php echo $val['ar']['leave_total']; ?></td>
                                            <td><?php echo $val['ar']['weekoff_total']; ?></td>
                                            <td><?php echo $val['ar']['holiday_total']; ?></td>
                                            <?php $count_ctc = count($val['stdctc']['keys']);
                                            $g_amt = 0;
                                            for ($m = 0; $m < $count_ctc; $m++) {
                                                $number = $val['stdctc']['actual'][$m];
                                                $g_amt = $number + $g_amt;
                                            ?>
                                                <td><?php echo $number; ?></td>
                                            <?php }  ?>
                                            <td><?php echo round($g_amt); ?></td>

                                            <?php $count_actualctc = count($val['actualctc']['keys']);
                                            $gr_amt = 0;
                                            for ($m = 0; $m < $count_actualctc; $m++) {
                                                $number = $val['actualctc']['actual'][$m];
                                                $gr_amt = $number + $gr_amt;
                                            ?>
                                                <td><?php echo $number; ?></td>
                                            <?php }  ?>
                                            <td><?php echo round($gr_amt); ?></td>




                                            <?php $count_addition = count($val['SAddition']['keys']);
                                            $grss_amt = 0;
                                            for ($m = 0; $m < $count_addition; $m++) {
                                                $number = round($val['SAddition']['actual'][$m]);
                                                $grss_amt = $number + $grss_amt;
                                            ?>
                                                <td><?php echo round($number, 2); ?></td>
                                            <?php }  ?>
                                            <td><?php echo round($grss_amt, 2); ?></td>
                                            <?php if (isset($standard_key['SDeduction'])) {
                                                $count_addition = count($val['SDeduction']['keys']);
                                                for ($m = 0; $m < $count_addition; $m++) {
                                                    $number = $val['SDeduction']['actual'][$m];
                                            ?>
                                                    <td><?php echo round($number, 2); ?></td>
                                            <?php }
                                            } ?>



                                            <?php if ($count3 > 0) {
                                                $count_addition1 = count($val['VAddition']['keys']);
                                                $grss_amt1 = 0;
                                                for ($m = 0; $m < $count_addition1; $m++) {
                                                    $number1 = round($val['VAddition']['actual'][$m]);
                                                    $grss_amt1 = $number1 + $grss_amt1;
                                            ?>
                                                    <td><?php echo round($number1, 2); ?></td>
                                                <?php }  ?>
                                                <td><?php echo round($grss_amt1, 2); ?></td>
                                            <?php } ?>



                                            <?php $count_addition = count($val['Addition']['keys']);
                                            $grss_amt = 0;
                                            for ($m = 0; $m < $count_addition; $m++) {
                                                $number = round($val['Addition']['value'][$m]);
                                                $grss_amt = $number + $grss_amt; ?>
                                                <td><?php echo round($number); ?></td>
                                            <?php } ?>
                                            <td><?php echo round($grss_amt); ?></td>
                                            <?php $dd_amt = 0;
                                            if (isset($array_key['Deduction'])) { ?>
                                                <?php $count_addition = count($val['Deduction']['keys']);

                                                for ($m = 0; $m < $count_addition; $m++) {
                                                    $number = round($val['Deduction']['value'][$m], 2);
                                                    $dd_amt = $number + $dd_amt;
                                                ?>
                                                    <td><?php echo round($number, 2); ?></td>
                                                    <!--                            //edited by megha on 13/11/2019 settlement amount 2-->
                                            <?php }
                                            }
                                            $netamt = $grss_amt + $dd_amt + $val['settle']; ?>
                                            <td><?php echo round($dd_amt, 2); ?></td>
                                            <!--                            //edited by megha on 13/11/2019 settlement amount 3-->
                                            <td><?php echo round($val['settle']); ?></td>
                                            <td><?php echo round($netamt); ?></td>
                                        </tr>


                                    <?php $i++;
                                    } ?>
                                </tbody>
                            </table>
                        </div>
                    <?php   } ?>
            </div>
        </div>
        <!--div class="modal-footer">
        <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel </button>  
    </div-->
        <!----<div class="row">
        <div class="form-group">
            <div class="col-md-12" align="right">
                <a href="#" class="btn btn-default" onclick="downloadReport('Grosssalary', 'pdf');" ><i class="icon-file"></i>Download As PDF</a>
                <a href="#" class="btn btn-default" onclick="downloadReport('Grosssalary', 'excel');"><i class="icon-file"></i>Download As Excel</a>
            </div>
        </div>
    </div> --->
    </div>
<?php }
?>
<?php } ?>