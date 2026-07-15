<?php
//debug($arr_salary_for_template);
/*
* To change this template, choose Tools | Templates
* and open the template in the editor.
*/
?>

<?php if ($mode == '') { ?>
    <style>
        .model-content {
            width: 104% !important;
        }

        .table,
        td,
        th,
        tr {
            border-style: 1px solid;
            border-color: black;


        }
    </style>
    <?php //debug(array_filter($arr_salary_for_template)); 
    ?>
    <div class="modal-body" style="overflow-y: auto;">
        <div class="row">
            <div class="col-md-12">
                <div class=" ">
                    <?php
                    $i = 0;
                    if ($cr == 'EmployeeDetails') {
                        foreach ($arr_salary_for_template as $value) {
                            if (count($value['summary']) != 0) {
                                $i += 1;
                    ?>
                                <h3 align="center"><b><?php echo 'Salary Slip - ' . "$mname-" . $year; ?></b> </h3>

                                <table class="table table-bordered" align="center" style="margin-top: 10px;margin-bottom: 0px; ">
                                    <tbody>
                                        <tr style="width : 120% ; ">
                                            <th style="width:101%"> <b><?php
                                                                        echo isset($value['summary']['0']['ed']['first_name']) ? $value['summary']['0']['ed']['first_name'] : '';
                                                                        echo ' ';
                                                                        echo isset($value['summary']['0']['ed']['middile_name']) ? $value['summary']['0']['ed']['middile_name'] : '';
                                                                        echo ' ';
                                                                        //  debug($value['summary']['0']['ed']['middle_name']);
                                                                        echo isset($value['summary']['0']['ed']['last_name']) ? $value['summary']['0']['ed']['last_name'] : '';
                                                                        echo (isset($value['summary']['0']['ed']['status'])) && $value['summary']['0']['ed']['status'] == "2" ? '  (Resigned)' : '';
                                                                        ?></b> - <?php echo isset($value['empdet']['0']['dd']['desig_name']) ? $value['empdet']['0']['dd']['desig_name'] : ''; ?> - <?php echo isset($value['summary']['0']['br']['branch_name']) ? $value['summary']['0']['br']['branch_name'] : ''; ?></th>



                                            <!--                                              <th>Leave days</th>
            <th>Holidays</th>-->

                                        </tr>
                                    </tbody>
                                </table>

                                <!--
        

    
</table>-->
                                <div class="row  " style="padding-top: 10px; margin-left: 0px; margin-right: 0px; border-right: 1px solid black; border-left: 1px solid black;">
                                    <div class="col-md-3 ">
                                        Employee ID
                                    </div>
                                    <div class="col-md-3 ">
                                        <span style="text-align: right ; word-wrap: break-word; ">: <?php echo isset($value['empdet']['0']['ep']['emp_company_id']) ? $value['empdet']['0']['ep']['emp_company_id'] : ''; ?> </span><span style="float: right ;"></span>
                                    </div>
                                    <div class="col-md-3">
                                        Date of Joining
                                    </div>
                                    <div class="col-md-3 ">
                                        <span style="text-align: right ;  border-bottom: 0px solid white ; ">: <?php
                                                                                                                //edited by megha on 9_7_19 date format changed
                                                                                                                echo isset($value['summary']['0']['ep']['joining_date']) ? date('d-m-Y', strtotime($value['summary']['0']['ep']['joining_date'])) : '';
                                                                                                                //echo isset($value['summary']['0']['ep']['joining_date']) ? $value['summary']['0']['ep']['joining_date'] : '';
                                                                                                                ?> </span><span style="float: right ;"></span>
                                    </div>
                                </div>

                                <!-- edited by athira on 07-07-2025 -->
                                <?php if ($company_code == 'DEMO' || $company_code == 'GLET' || $company_code == 'SRTS') { ?>
                                    <div class="row  " style="padding-top: 10px; margin-left: 0px; margin-right: 0px; border-right: 1px solid black; border-left: 1px solid black;">

                                        <div class="col-md-3">
                                            <span> Employee ID (US Format) : </span><span></span>
                                        </div>
                                        <div class="col-md-3">
                                            <span style="text-align: right ; word-wrap: break-word; ">:
                                                <!-- </?php echo  wordwrap(isset($value['empdet']['0']['d']['dept_name']) ? $value['empdet']['0']['d']['dept_name'] : '', 21, "<br>\n", TRUE); ?> </span><span style="float: right ;"></span> -->
                                                <?php
                                                if (!empty($value['summary']['0']['ed']['emp_us_company_id'])) {
                                                    echo $value['summary']['0']['ed']['emp_us_company_id'];
                                                }
                                                ?></span>
                                        </div>

                                        <div class="col-md-3">
                                            <span> Employee Name (US Format) : </span><span></span>
                                        </div>
                                        <div class="col-md-3">
                                            <span style="text-align: right ; word-wrap: break-word; ">:
                                                <!-- </?php echo  wordwrap(isset($value['empdet']['0']['d']['dept_name']) ? $value['empdet']['0']['d']['dept_name'] : '', 21, "<br>\n", TRUE); ?> </span><span style="float: right ;"></span> -->
                                                <?php
                                                if (!empty($value['summary']['0']['ed']['emp_us_name'])) {
                                                    echo $value['summary']['0']['ed']['emp_us_name'];
                                                }
                                                ?></span>
                                        </div>
                                    </div>
                                <?php } ?>
                                <!-- end -->

                                <div class="row  " style="padding-top: 10px; margin-left: 0px; margin-right: 0px; border-right: 1px solid black; border-left: 1px solid black;">

                                    <div class="col-md-3">
                                        <span> Department : </span><span></span>
                                    </div>
                                    <div class="col-md-3 ">
                                        <span style="text-align: right ; word-wrap: break-word; ">: <?php echo  wordwrap(isset($value['empdet']['0']['d']['dept_name']) ? $value['empdet']['0']['d']['dept_name'] : '', 21, "<br>\n", TRUE); ?> </span><span style="float: right ;"></span>
                                    </div>
                                    <div class="col-md-3">
                                        <span> Gender : </span><span></span>
                                    </div>
                                    <div class="col-md-3 ">
                                        <span style="text-align: right ; word-wrap: break-word; ">: <?php echo  wordwrap(isset($value['summary']['0']['ed']['classification']) ? strtoupper($value['summary']['0']['ed']['classification']) : '', 21, "<br>\n", TRUE); ?> </span><span style="float: right ;"></span>
                                    </div>

                                </div>

                                <div class="row  " style="padding-top: 10px; margin-left: 0px; margin-right: 0px; border-right: 1px solid black; border-left: 1px solid black;">
                                    <div class="col-md-3">
                                        Leave Days
                                    </div>
                                    <div class="col-md-3">
                                        <!--           <span style="text-align: right ; ">: <?php //echo isset($value['empdet']['0']['payroll_master']['working_days']) ? $value['empdet']['0']['payroll_master']['working_days'] : ''; 
                                                                                            ?>   </span><span style="float: right ;"></span>-->
                                        <span style="text-align: right ; ">: <?php echo isset($value['summary']['0']['payroll_master']['days_leave']) ? $value['summary']['0']['payroll_master']['days_leave'] : ''; ?> </span><span style="float: right ;"></span>
                                    </div>
                                    <div class="col-md-3">
                                        <span style="text-align:  left ; ">Present Days </span>
                                    </div>
                                    <div class="col-md-3">
                                        <span style="text-align: left ;  border-right: 0px solid white; border-bottom: 0px solid white ; ">:
                                            <?php echo isset($value['summary']['0']['ar']['presant_total']) ? $value['summary']['0']['ar']['presant_total'] : ''; ?> </span><span style="float: right ;"></span>

                                    </div>


                                </div>
                                <div class="row  " style="padding-top: 10px; margin-left: 0px; margin-right: 0px; border-right: 1px solid black; border-left: 1px solid black;">
                                    <div class="col-md-3">
                                        <span style="text-align:  left ; ">Non Paying Days </span>
                                    </div>
                                    <div class="col-md-3">
                                        <span style="text-align: right ; ">: <?php echo isset($value['empdet']['0']['payroll_master']['loss_of_pay']) ? $value['empdet']['0']['payroll_master']['loss_of_pay'] : ''; ?> </span><span style="float: right ;"></span>

                                    </div>
                                    <div class="col-md-3">
                                        <span> No. of Week Off </span><span></span>
                                    </div>
                                    <div class="col-md-3">
                                        <span style="text-align: right ; ">: <?php echo isset($value['summary']['0']['ar']['weekoff_total']) ? $value['summary']['0']['ar']['weekoff_total'] : ''; ?> </span><span style="float: right ;"></span>

                                    </div>

                                </div>
                                <div class="row  " style="padding-top: 10px; margin-left: 0px; margin-right: 0px; border-right: 1px solid black; border-left: 1px solid black;">
                                    <div class="col-md-3">
                                        LOP Days
                                    </div>
                                    <div class="col-md-3">
                                        <span style="text-align: right ; ">: <?php echo isset($value['empdet']['0']['ar']['lop_only']) ? $value['empdet']['0']['ar']['lop_only'] : ''; ?> </span><span style="float: right ;"></span>

                                    </div>
                                    <div class="col-md-3">
                                        No. of Holiday
                                    </div>
                                    <div class="col-md-3">
                                        <span style="text-align: right ; ">: <?php echo isset($value['summary']['0']['ar']['holiday_total']) ? $value['summary']['0']['ar']['holiday_total'] : ''; ?> </span><span style="float: right ;"></span>

                                    </div>
                                </div>
                                <div class="row  " style="padding-top: 10px; margin-left: 0px; margin-right: 0px; border-right: 1px solid black; border-left: 1px solid black;">
                                    <div class="col-md-3">
                                        <span style="text-align:  left ; ">PF account No </span>
                                    </div>
                                    <div class="col-md-3">
                                        <span style="text-align: right ; ">: <?php

                                                                                echo isset($value['summary']['0']['ed']['company_pf']) ? $value['summary']['0']['ed']['company_pf'] : '';
                                                                                ?> </span><span style="float: right ;"></span>
                                    </div>
                                    <div class="col-md-3">
                                        <span style="text-align:  left ; ">ESI No </span>
                                    </div>
                                    <div class="col-md-3">
                                        : <?php echo isset($value['summary']['0']['ed']['esi']) ? $value['summary']['0']['ed']['esi'] : ''; ?>

                                    </div>
                                </div>
                                <div class="row  " style="padding-top: 10px; margin-left: 0px; margin-right: 0px; border-right: 1px solid black; border-left: 1px solid black;">
                                    <div class="col-md-3">
                                        <span style="text-align:  left ; ">UAN No </span>
                                    </div>
                                    <div class="col-md-3">
                                        <span style="text-align: left ;  border-right: 0px solid white; border-bottom: 0px solid white ; ">: <?php echo isset($value['summary']['0']['ed']['pf']) ? $value['summary']['0']['ed']['pf'] : ''; ?> </span><span style="float: right ;"></span>

                                    </div>
                                    <div class="col-md-3">

                                        <span style="text-align:  left ; ">Bank Name </span>
                                    </div>
                                    <?php
                                    $bank_name = '';
                                    $branch_name = '';
                                    $ifsc_code = '';
                                    $acc_number = '';
                                    $bank = isset($value['empdet']['0']['payroll_master']['bank_details']) ? $value['empdet']['0']['payroll_master']['bank_details'] : '';
                                    if ($bank != '') {
                                        list($bank_name, $branch_name, $ifsc_code, $acc_number) = explode(',', $bank);
                                    }
                                    if ($bank_name == '') {
                                        $bank_name = $value['summary']['0']['ed']['bank_name'];
                                    }
                                    if ($branch_name == '') {
                                        $branch_name = $value['summary']['0']['ed']['branch_name'];
                                    }
                                    if ($ifsc_code == '') {
                                        $ifsc_code = $value['summary']['0']['ed']['ifsc_code'];
                                    }
                                    if ($acc_number == '') {
                                        $acc_number = $value['summary']['0']['ed']['account_no'];
                                    }
                                    ?>
                                    <div class="col-md-3">
                                        <span style="text-align: right ; ">:
                                            <?php
                                            echo isset($bank_name) ? $bank_name : '';
                                            ?> </span><span style="float: right ;">
                                    </div>
                                </div>

                                <div class="row  " style="padding-bottom: 10px; padding-top: 10px; margin-left: 0px; margin-right: 0px; border-right: 1px solid black; border-left: 1px solid black;">
                                    <div class="col-md-3">
                                        <span style="text-align:  left ; ">Account Number </span>
                                    </div>
                                    <div class="col-md-3">
                                        <span style="text-align: right ; ">: <?php
                                                                                echo isset($acc_number) ? $acc_number : '';
                                                                                //echo isset($value['summary']['0']['ed']['account_no']) ? $value['summary']['0']['ed']['account_no'] : '';
                                                                                ?> </span><span style="float: right ;"></span>
                                    </div>
                                    <div class="col-md-3">
                                        <span style="text-align:  left ; ">IFSC Code </span>
                                    </div>
                                    <div class="col-md-3">
                                        <span style="text-align: right ; ">: <?php
                                                                                echo isset($ifsc_code) ? $ifsc_code : '';
                                                                                //echo isset($value['summary']['0']['ed']['ifsc_code']) ? $value['summary']['0']['ed']['ifsc_code'] : '';
                                                                                ?> </span><span style="float: right ;"></span>
                                    </div>
                                </div>

                                <div class="row  " style="padding-bottom: 10px; padding-top: 10px; margin-left: 0px; margin-right: 0px; border-right: 1px solid black; border-left: 1px solid black;">
                                    <div class="col-md-3">
                                        <span style="text-align:  left ; ">Branch </span>
                                    </div>
                                    <div class="col-md-3">
                                        : <?php
                                            echo isset($branch_name) ? $branch_name : '';
                                            //echo isset($value['summary']['0']['ed']['branch_name']) ? $value['summary']['0']['ed']['branch_name'] : ''; 
                                            ?> </span><span style="float: right ;"></span></th>
                                    </div>
                                </div>
                                <table class="table table-bordered" align="center">
                                    <tbody>
                                        <tr style="background: #cccccc ;width : 120% ;">

                                            <!--    <th>LEAVEPOLICY_GROUP_NAME</th> -->


                                            <th style="width:40%">Earnings</th>
                                            <th style="width:10%">Amount</th>
                                            <th style="width:41%">Deductions</th>

                                            <!--<th>Leave days</th>-->
                                            <th style="width:10%">Amount</th>

                                        </tr>
                                        <?php $arr_data = $value['summary'];
                                        $arr_withoutComponents = $value['withoutcomponent'];
                                        ?>
                                        <?php
                                        if (count($arr_data) >= 0) {
                                            //Edited by Akshay on 29-10-2024
                                            $og_total = 0;
                                            $rnd_total = 0;
                                            foreach ($arr_data as $item) {
                                                $amount = $item['ectc']['salary_amount'];
                                                $og_total += round($amount, 2);
                                                $rnd_total += round($amount);
                                            }
                                            $diff = round($og_total) - $rnd_total;
                                            //End
                                            $countss = count($arr_data);
                                            if (count($arr_data) < count($arr_withoutComponents)) {
                                                $countss = count($arr_withoutComponents);
                                            }
                                            $sum = 0;
                                            $tot = 0;
                                            $dd = 0;
                                            $net = 0;
                                        ?>
                                            <?php for ($i = 0; $i < $countss; $i++) {
                                                //Edited by Akshay on 29-10-2024
                                                if ($i == 0)
                                                    $arr_data[$i]['ectc']['salary_amount'] = (isset($arr_data[$i]['ectc']['salary_amount']) ? round($arr_data[$i]['ectc']['salary_amount']) : 0) + $diff;
                                                //End
                                            ?>
                                                <tr> <?php
                                                        $sum += isset($arr_data[$i]['ectc']['salary_amount']) ? round($arr_data[$i]['ectc']['salary_amount']) : 0;
                                                        if (isset($arr_withoutComponents[$i]['ectc']))
                                                            $dd += isset($arr_withoutComponents[$i]['ectc']['salary_amount']) ? $arr_withoutComponents[$i]['ectc']['salary_amount'] : 0;
                                                        ?>
                                                    <!-- edited by megha on 30_05_19 round off  -->

                                                    <td><?php echo isset($arr_data[$i]['ectc']['salary_head_item_desc']) ? $arr_data[$i]['ectc']['salary_head_item_desc'] : ''; ?></td>
                                                    <!--<td><?php //echo isset($arr_data[$i]['ectc']['structure_det_value'])?round($arr_data[$i]['ectc']['structure_det_value'], 2):''; 
                                                            ?></td>-->

                                                    <td><?php echo isset($arr_data[$i]['ectc']['salary_amount']) ? abs(round($arr_data[$i]['ectc']['salary_amount'])) : ''; ?></td>

                                                    <td><?php echo isset($arr_withoutComponents[$i]['ectc']['salary_head_item_desc']) ? $arr_withoutComponents[$i]['ectc']['salary_head_item_desc'] : ''; ?></td>
                                                    <!--<td><?php //echo round(isset($arr_withoutComponents[$i]['ectc']['structure_det_value']) ? $arr_withoutComponents[$i]['ectc']['structure_det_value'] : '', 2); 
                                                            ?></td>-->
                                                    <!-- edited by megha on 08_07_19 '0' values removed  -->
                                                    <td><?php echo isset($arr_withoutComponents[$i]['ectc']['salary_amount']) ? abs(round($arr_withoutComponents[$i]['ectc']['salary_amount'], 2)) : ''; ?></td>

                                                </tr>

                                            <?php } ?>
                                            <tr style="background: #cccccc ;">
                                                <th style="text-align :center ; ">Total Earnings</th>
                                                <th><?php echo abs(round($sum)); ?></th>
                                                <th>Total Deductions </th>
                                                <th><?php echo abs(round($dd, 2)); ?></th>
                                            </tr>
                                            <!-- edited by megha on 16/11/19 settlement amount  -->
                                            <?php if ($value['summary']['0']['ed']['status'] == 2) { ?>
                                                <tr style="background: #cccccc ;">
                                                    <th style="text-align :center ; " colspan="3">Settlement Amount</th>
                                                    <th><?php echo $value['settle']; ?></th>
                                                </tr>
                                            <?php } ?>
                                            <!-- end -->
                                            <!-- edited by megha on 30_05_19 round off, edited by megha on 16/11/19 settlement amount  -->

                                            <tr style="background: #cccccc ;">
                                                <th style="text-align :center ; " colspan="3">Net Pay</th>
                                                <th><?php
                                                    $total = round($sum) + round($dd, 2) + $value['settle'];
                                                    echo abs($total) == 0 ? 0 : $total; ?></th>
                                            </tr>
                                            <?php $arr_withoutComponents = $value['withoutcomponent']; ?>

                                            <?php if (count($arr_withoutComponents) > 0) {
                                            ?>



                                            <?php } ?>
                                        <?php } else {
                                        ?>
                                            <tr>
                                                <td colspan="4">No Components found under this data</td>
                                            </tr>
                                        <?php } ?>

                                    </tbody>
                                </table>

                            <?php
                            }
                        }
                    } else {
                        foreach ($arr_salary_for_template as $val) { ?>
                            <legend><?php echo isset($val['0']['summary']['0']['br']['branch_name']) ? $val['0']['summary']['0']['br']['branch_name'] : (isset($val['0']['summary']['0']['branches']['branch_name']) ? $val['0']['summary']['0']['branches']['branch_name'] : ''); ?> </legend>
                            <?php foreach ($val as $value) {
                                if (count($value['summary']) != 0) {
                                    $i += 1;
                            ?>
                                    <h3 align="center"><b><?php echo 'Salary Slip - ' . "$mname-" . $year; ?></b> </h3>

                                    <table class="table table-bordered" align="center" style="margin-top: 10px;margin-bottom: 0px; ">
                                        <tbody>
                                            <tr style="width : 120% ; ">
                                                <th style="width:101%"> <b><?php
                                                                            echo isset($value['summary']['0']['ed']['first_name']) ? $value['summary']['0']['ed']['first_name'] : '';
                                                                            echo ' ';
                                                                            echo isset($value['summary']['0']['ed']['middile_name']) ? $value['summary']['0']['ed']['middile_name'] : '';
                                                                            echo ' ';
                                                                            //  debug($value['summary']['0']['ed']['middle_name']);
                                                                            echo isset($value['summary']['0']['ed']['last_name']) ? $value['summary']['0']['ed']['last_name'] : '';
                                                                            echo (isset($value['summary']['0']['ed']['status'])) && $value['summary']['0']['ed']['status'] == "2" ? '  (Resigned)' : '';
                                                                            ?></b> - <?php echo isset($value['empdet']['0']['dd']['desig_name']) ? $value['empdet']['0']['dd']['desig_name'] : ''; ?> - <?php echo isset($value['summary']['0']['br']['branch_name']) ? $value['summary']['0']['br']['branch_name'] : ''; ?></th>



                                                <!--                                              <th>Leave days</th>
            <th>Holidays</th>-->

                                            </tr>
                                        </tbody>
                                    </table>

                                    <!--
        

    
</table>-->
                                    <div class="row  " style="padding-top: 10px; margin-left: 0px; margin-right: 0px; border-right: 1px solid black; border-left: 1px solid black;">
                                        <div class="col-md-3 ">
                                            Employee ID
                                        </div>
                                        <div class="col-md-3 ">
                                            <span style="text-align: right ; word-wrap: break-word; ">: <?php echo isset($value['empdet']['0']['ep']['emp_company_id']) ? $value['empdet']['0']['ep']['emp_company_id'] : ''; ?> </span><span style="float: right ;"></span>
                                        </div>
                                        <div class="col-md-3">
                                            Date of Joining
                                        </div>
                                        <div class="col-md-3 ">
                                            <span style="text-align: right ;  border-bottom: 0px solid white ; ">: <?php
                                                                                                                    //edited by megha on 9_7_19 date format changed
                                                                                                                    echo isset($value['summary']['0']['ep']['joining_date']) ? date('d-m-Y', strtotime($value['summary']['0']['ep']['joining_date'])) : '';
                                                                                                                    //echo isset($value['summary']['0']['ep']['joining_date']) ? $value['summary']['0']['ep']['joining_date'] : '';
                                                                                                                    ?> </span><span style="float: right ;"></span>
                                        </div>
                                    </div>

                                    <!-- edited by athira on 07-07-2025 -->
                                    <?php if ($company_code == 'DEMO' || $company_code == 'GLET' || $company_code == 'SRTS') { ?>
                                        <div class="row " style="padding-top:10px; margin-left: 0px; margin-right: 0px; border-right: 1px solid black; border-left: 1px solid black;">

                                            <div class="col-md-3">
                                                <span> Employee ID (US Format) : </span><span></span>
                                            </div>
                                            <div class="col-md-3">
                                                <span style="text-align: right ; word-wrap: break-word; ">:
                                                    <!-- </?php echo  wordwrap(isset($value['empdet']['0']['d']['dept_name']) ? $value['empdet']['0']['d']['dept_name'] : '', 21, "<br>\n", TRUE); ?> </span><span style="float: right ;"></span> -->
                                                    <?php
                                                    if (!empty($value['summary']['0']['ed']['emp_us_company_id'])) {
                                                        echo $value['summary']['0']['ed']['emp_us_company_id'];
                                                    }
                                                    ?></span>
                                            </div>

                                            <div class="col-md-3">
                                                <span> Employee Name (US Format) : </span><span></span>
                                            </div>
                                            <div class="col-md-3">
                                                <span style="text-align: right ; word-wrap: break-word; ">:
                                                    <!-- </?php echo  wordwrap(isset($value['empdet']['0']['d']['dept_name']) ? $value['empdet']['0']['d']['dept_name'] : '', 21, "<br>\n", TRUE); ?> </span><span style="float: right ;"></span> -->
                                                    <?php
                                                    if (!empty($value['summary']['0']['ed']['emp_us_name'])) {
                                                        echo $value['summary']['0']['ed']['emp_us_name'];
                                                    }
                                                    ?></span>
                                            </div>
                                        </div>
                                    <?php } ?>
                                    <!-- end -->
                                    <div class="row  " style="padding-top: 10px; margin-left: 0px; margin-right: 0px; border-right: 1px solid black; border-left: 1px solid black;">

                                        <div class="col-md-3">
                                            <span> Department : </span><span></span>
                                        </div>
                                        <div class="col-md-3 ">
                                            <span style="text-align: right ; word-wrap: break-word; ">: <?php echo  wordwrap(isset($value['empdet']['0']['d']['dept_name']) ? $value['empdet']['0']['d']['dept_name'] : '', 21, "<br>\n", TRUE); ?> </span><span style="float: right ;"></span>
                                        </div>
                                        <div class="col-md-3">
                                            <span> Gender : </span><span></span>
                                        </div>
                                        <div class="col-md-3 ">
                                            <span style="text-align: right ; word-wrap: break-word; ">: <?php echo  wordwrap(isset($value['summary']['0']['ed']['classification']) ? strtoupper($value['summary']['0']['ed']['classification']) : '', 21, "<br>\n", TRUE); ?> </span><span style="float: right ;"></span>
                                        </div>

                                    </div>

                                    <div class="row  " style="padding-top: 10px; margin-left: 0px; margin-right: 0px; border-right: 1px solid black; border-left: 1px solid black;">
                                        <div class="col-md-3">
                                            Leave Days
                                        </div>
                                        <div class="col-md-3">
                                            <!--           <span style="text-align: right ; ">: <?php //echo isset($value['empdet']['0']['payroll_master']['working_days']) ? $value['empdet']['0']['payroll_master']['working_days'] : ''; 
                                                                                                ?>   </span><span style="float: right ;"></span>-->
                                            <span style="text-align: right ; ">: <?php echo isset($value['summary']['0']['payroll_master']['days_leave']) ? $value['summary']['0']['payroll_master']['days_leave'] : ''; ?> </span><span style="float: right ;"></span>
                                        </div>
                                        <div class="col-md-3">
                                            <span style="text-align:  left ; ">Present Days </span>
                                        </div>
                                        <div class="col-md-3">
                                            <span style="text-align: left ;  border-right: 0px solid white; border-bottom: 0px solid white ; ">:
                                                <?php echo isset($value['summary']['0']['ar']['presant_total']) ? $value['summary']['0']['ar']['presant_total'] : ''; ?> </span><span style="float: right ;"></span>

                                        </div>


                                    </div>
                                    <div class="row  " style="padding-top: 10px; margin-left: 0px; margin-right: 0px; border-right: 1px solid black; border-left: 1px solid black;">
                                        <div class="col-md-3">
                                            <span style="text-align:  left ; ">Non Paying Days </span>
                                        </div>
                                        <div class="col-md-3">
                                            <span style="text-align: right ; ">: <?php echo isset($value['empdet']['0']['payroll_master']['loss_of_pay']) ? $value['empdet']['0']['payroll_master']['loss_of_pay'] : ''; ?> </span><span style="float: right ;"></span>

                                        </div>
                                        <div class="col-md-3">
                                            <span> No. of Week Off </span><span></span>
                                        </div>
                                        <div class="col-md-3">
                                            <span style="text-align: right ; ">: <?php echo isset($value['summary']['0']['ar']['weekoff_total']) ? $value['summary']['0']['ar']['weekoff_total'] : ''; ?> </span><span style="float: right ;"></span>

                                        </div>

                                    </div>
                                    <div class="row  " style="padding-top: 10px; margin-left: 0px; margin-right: 0px; border-right: 1px solid black; border-left: 1px solid black;">
                                        <div class="col-md-3">
                                            LOP Days
                                        </div>
                                        <div class="col-md-3">
                                            <span style="text-align: right ; ">: <?php echo isset($value['empdet']['0']['ar']['lop_only']) ? $value['empdet']['0']['ar']['lop_only'] : ''; ?> </span><span style="float: right ;"></span>
                                        </div>
                                        <div class="col-md-3">
                                            No. of Holiday
                                        </div>
                                        <div class="col-md-3">
                                            <span style="text-align: right ; ">: <?php echo isset($value['summary']['0']['ar']['holiday_total']) ? $value['summary']['0']['ar']['holiday_total'] : ''; ?> </span><span style="float: right ;"></span>
                                        </div>
                                    </div>
                                    <div class="row  " style="padding-top: 10px; margin-left: 0px; margin-right: 0px; border-right: 1px solid black; border-left: 1px solid black;">
                                        <div class="col-md-3">
                                            <span style="text-align:  left ; ">PF account No </span>
                                        </div>
                                        <div class="col-md-3">
                                            <span style="text-align: right ; ">: <?php

                                                                                    echo isset($value['summary']['0']['ed']['company_pf']) ? $value['summary']['0']['ed']['company_pf'] : '';
                                                                                    ?> </span><span style="float: right ;"></span>
                                        </div>
                                        <div class="col-md-3">
                                            <span style="text-align:  left ; ">ESI No </span>
                                        </div>
                                        <div class="col-md-3">
                                            : <?php echo isset($value['summary']['0']['ed']['esi']) ? $value['summary']['0']['ed']['esi'] : ''; ?>

                                        </div>
                                    </div>
                                    <div class="row  " style="padding-top: 10px; margin-left: 0px; margin-right: 0px; border-right: 1px solid black; border-left: 1px solid black;">
                                        <div class="col-md-3">
                                            <span style="text-align:  left ; ">UAN No </span>
                                        </div>
                                        <div class="col-md-3">
                                            <span style="text-align: left ;  border-right: 0px solid white; border-bottom: 0px solid white ; ">: <?php echo isset($value['summary']['0']['ed']['pf']) ? $value['summary']['0']['ed']['pf'] : ''; ?> </span><span style="float: right ;"></span>

                                        </div>
                                        <div class="col-md-3">

                                            <span style="text-align:  left ; ">Bank Name </span>
                                        </div>
                                        <?php
                                        $bank_name = '';
                                        $branch_name = '';
                                        $ifsc_code = '';
                                        $acc_number = '';
                                        $bank = isset($value['empdet']['0']['payroll_master']['bank_details']) ? $value['empdet']['0']['payroll_master']['bank_details'] : '';
                                        if ($bank != '') {
                                            list($bank_name, $branch_name, $ifsc_code, $acc_number) = explode(',', $bank);
                                        }
                                        if ($bank_name == '') {
                                            $bank_name = $value['summary']['0']['ed']['bank_name'];
                                        }
                                        if ($branch_name == '') {
                                            $branch_name = $value['summary']['0']['ed']['branch_name'];
                                        }
                                        if ($ifsc_code == '') {
                                            $ifsc_code = $value['summary']['0']['ed']['ifsc_code'];
                                        }
                                        if ($acc_number == '') {
                                            $acc_number = $value['summary']['0']['ed']['account_no'];
                                        }
                                        ?>
                                        <div class="col-md-3">
                                            <span style="text-align: right ; ">:
                                                <?php
                                                echo isset($bank_name) ? $bank_name : '';
                                                ?> </span><span style="float: right ;">
                                        </div>
                                    </div>
                                    <div class="row  " style="padding-bottom: 10px; padding-top: 10px; margin-left: 0px; margin-right: 0px; border-right: 1px solid black; border-left: 1px solid black;">
                                        <div class="col-md-3">
                                            <span style="text-align:  left ; ">Account Number </span>
                                        </div>
                                        <div class="col-md-3">
                                            <span style="text-align: right ; ">: <?php
                                                                                    echo isset($acc_number) ? $acc_number : '';
                                                                                    //echo isset($value['summary']['0']['ed']['account_no']) ? $value['summary']['0']['ed']['account_no'] : '';
                                                                                    ?> </span><span style="float: right ;"></span>
                                        </div>
                                        <div class="col-md-3">
                                            <span style="text-align:  left ; ">IFSC Code </span>
                                        </div>
                                        <div class="col-md-3">
                                            <span style="text-align: right ; ">: <?php
                                                                                    echo isset($ifsc_code) ? $ifsc_code : '';
                                                                                    //echo isset($value['summary']['0']['ed']['ifsc_code']) ? $value['summary']['0']['ed']['ifsc_code'] : '';
                                                                                    ?> </span><span style="float: right ;"></span>
                                        </div>
                                    </div>

                                    <div class="row  " style="padding-bottom: 10px; padding-top: 10px; margin-left: 0px; margin-right: 0px; border-right: 1px solid black; border-left: 1px solid black;">
                                        <div class="col-md-3">
                                            <span style="text-align:  left ; ">Branch </span>
                                        </div>
                                        <div class="col-md-3">
                                            : <?php
                                                echo isset($branch_name) ? $branch_name : '';
                                                //echo isset($value['summary']['0']['ed']['branch_name']) ? $value['summary']['0']['ed']['branch_name'] : ''; 
                                                ?> </span><span style="float: right ;"></span></th>
                                        </div>
                                    </div>

                                    <table class="table table-bordered" align="center">
                                        <tbody>
                                            <tr style="background: #cccccc ;width : 120% ;">

                                                <!--    <th>LEAVEPOLICY_GROUP_NAME</th> -->


                                                <th style="width:40%">Earnings</th>
                                                <th style="width:10%">Amount</th>
                                                <th style="width:41%">Deductions</th>

                                                <!--<th>Leave days</th>-->
                                                <th style="width:10%">Amount</th>

                                            </tr>
                                            <?php $arr_data = $value['summary'];
                                            $arr_withoutComponents = $value['withoutcomponent'];
                                            ?>
                                            <?php
                                            if (count($arr_data) >= 0) {
                                                $countss = count($arr_data);
                                                if (count($arr_data) < count($arr_withoutComponents)) {
                                                    $countss = count($arr_withoutComponents);
                                                }
                                                //Edited by Akshay on 29-10-2024
                                                $og_total = 0;
                                                $rnd_total = 0;
                                                foreach ($arr_data as $item) {
                                                    $amount = $item['ectc']['salary_amount'];
                                                    $og_total += round($amount, 2);
                                                    $rnd_total += round($amount);
                                                }
                                                $diff = round($og_total) - $rnd_total;
                                                //End
                                                $sum = 0;
                                                $tot = 0;
                                                $dd = 0;
                                                $net = 0;
                                            ?>
                                                <?php for ($i = 0; $i < $countss; $i++) {
                                                    //Edited by Akshay on 29-10-2024
                                                    if ($i == 0)
                                                        $arr_data[$i]['ectc']['salary_amount'] = isset($arr_data[$i]['ectc']['salary_amount']) ? ($arr_data[$i]['ectc']['salary_amount'] + $diff) : $arr_data[$i]['ectc']['salary_amount'];
                                                    //End
                                                ?>
                                                    <tr> <?php
                                                            $sum += isset($arr_data[$i]['ectc']['salary_amount']) ? round($arr_data[$i]['ectc']['salary_amount']) : 0;
                                                            if (isset($arr_withoutComponents[$i]['ectc']))
                                                                $dd += isset($arr_withoutComponents[$i]['ectc']['salary_amount']) ? $arr_withoutComponents[$i]['ectc']['salary_amount'] : 0;
                                                            ?>
                                                        <!-- edited by megha on 30_05_19 round off  -->

                                                        <td><?php echo isset($arr_data[$i]['ectc']['salary_head_item_desc']) ? $arr_data[$i]['ectc']['salary_head_item_desc'] : ''; ?></td>
                                                        <!--<td><?php //echo isset($arr_data[$i]['ectc']['structure_det_value'])?round($arr_data[$i]['ectc']['structure_det_value'], 2):''; 
                                                                ?></td>-->

                                                        <td><?php echo isset($arr_data[$i]['ectc']['salary_amount']) ? abs(round($arr_data[$i]['ectc']['salary_amount'])) : ''; ?></td>

                                                        <td><?php echo isset($arr_withoutComponents[$i]['ectc']['salary_head_item_desc']) ? $arr_withoutComponents[$i]['ectc']['salary_head_item_desc'] : ''; ?></td>
                                                        <!--<td><?php //echo round(isset($arr_withoutComponents[$i]['ectc']['structure_det_value']) ? $arr_withoutComponents[$i]['ectc']['structure_det_value'] : '', 2); 
                                                                ?></td>-->
                                                        <!-- edited by megha on 08_07_19 '0' values removed  -->
                                                        <td><?php echo isset($arr_withoutComponents[$i]['ectc']['salary_amount']) ? abs(round($arr_withoutComponents[$i]['ectc']['salary_amount'], 2)) : ''; ?></td>

                                                    </tr>

                                                <?php } ?>
                                                <tr style="background: #cccccc ;">
                                                    <th style="text-align :center ; ">Total Earnings</th>
                                                    <th><?php echo abs(round($sum)); ?></th>
                                                    <th>Total Deductions </th>
                                                    <th><?php echo abs(round($dd, 2)); ?></th>
                                                </tr>
                                                <!-- edited by megha on 16/11/19 settlement amount  -->
                                                <?php if ($value['summary']['0']['ed']['status'] == 2) { ?>
                                                    <tr style="background: #cccccc ;">
                                                        <th style="text-align :center ; " colspan="3">Settlement Amount</th>
                                                        <th><?php echo $value['settle']; ?></th>
                                                    </tr>
                                                <?php } ?>
                                                <!-- end -->
                                                <!-- edited by megha on 30_05_19 round off, edited by megha on 16/11/19 settlement amount  -->

                                                <tr style="background: #cccccc ;">
                                                    <th style="text-align :center ; " colspan="3">Net Pay</th>
                                                    <th><?php
                                                        $total = round($sum) + round($dd, 2) + $value['settle'];
                                                        echo abs($total) == 0 ? 0 : $total; ?></th>
                                                </tr>
                                                <?php $arr_withoutComponents = $value['withoutcomponent']; ?>

                                                <?php if (count($arr_withoutComponents) > 0) {
                                                ?>



                                                <?php } ?>
                                            <?php } else {
                                            ?>
                                                <tr>
                                                    <td colspan="4">No Components found under this data</td>
                                                </tr>
                                            <?php } ?>

                                        </tbody>
                                    </table>

                        <?php
                                }
                            }
                        }
                    }
                    if ($i == '0') { ?>
                        <div style="font-size: 25px;text-align:center; background-color:#F7D3D2;">
                            There is no data available</div>
                    <?php } ?>
                    <!-- /.box-body -->
                </div>
            </div>
        </div>
        <!--div class="modal-footer">
        <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel </button>  
    </div-->
        <!--<div class="row">
        <div class="form-group">
            <div class="col-md-12" align="right">
                <a href="#" class="btn btn-default" onclick="downloadReport('Salaryslip', 'pdf');" ><i class="icon-file"></i>Download As PDF</a>
    <!--a href="#" class="btn btn-default" onclick="downloadReport('Salaryslip','excel');"><i class="icon-file"></i>Download As Excel</a>
</div>
</div>
</div> -->
    </div>

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
            /*border: #000000 solid thin;*/
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
            /*border: 1px solid #f4f4f4;*/
            width: 100%;
            max-width: 100%;
            margin-bottom: 20px;
            /*background-color: transparent;*/
            border-spacing: 0;
            border-collapse: collapse;
            /*border: 1px solid black;
       margin-left: 0px;*/
        }

        td,
        th {
            text-align: left;
            padding: 6px;
            font-weight: normal;
            /*font-size: 11px;*/
            font-size: 14px;
            /*font-family: serif;*/
            line-height: 1.42857143;
            word-wrap: break-word;
            vertical-align: top;
            color: black;
            border: 1px solid black;
        }
    </style>


    <?php
    //                $i = 0;
    //                foreach ($arr_salary_for_template as $value) {
    //                if (count($value['summary'])!= 0 ) {
    //                $i += 1;
    ?>
    <?php
    $i = 0;
    if ($cr == 'EmployeeDetails') {
        foreach ($arr_salary_for_template as $value) {

            if (count($value['summary']) !== 0 || count($value['withoutcomponent']) !== 0) {
                $i += 1;
    ?>

                <page backtop="50mm" backbottom="2mm" backleft=2mm" backright="20mm" style="font-size: 12pt">

                    <page_header>


                        <!--        <div style="text-align:right; width:100%">

            <?php echo date("l,F j, Y"); ?> </div>-->
                        <div style="text-align:left; width:100%; ">
                            <?php if (isset($arr_comp_contact_info['CompanyContactInfo']['logo']) && !empty($arr_comp_contact_info['CompanyContactInfo']['logo'])) { ?>
                                <div style="width: 20%; margin-left: 0px; font-size: 18px; ">
                                    <img style=" margin-top: 40px; " src="http://<?php echo $_SERVER['HTTP_HOST'] . $this->webroot . $arr_comp_contact_info['CompanyContactInfo']['logo']; ?>" height="100" width="200" class="img-circle" alt="Company Logo" />
                                </div>
                                <!--<img style=" margin-left: 70px; " src="http://<?php echo $_SERVER['HTTP_HOST'] . $this->webroot . $arr_comp_contact_info['CompanyContactInfo']['logo']; ?>" height="50" width="70" class="img-circle" alt="Company Logo" />-->
                            <?php } ?>
                            <div style="width: 80%; margin-left: 100px; margin-top: 40px; position : absolute ; float: left; font-size: 14px; ">
                                <div style=" text-align: center;">
                                    <p>FORM XIII –See Rules 29(2)</p>
                                </div>
                                <div style="text-align: center;font-weight: bold;font-size: 14px; ; padding-top: 4px; "><?php echo $arr_comp_contact_info['CompanyContactInfo']['business_name']; ?>

                                </div>
                                <div style="text-align: center ;  margin-left: 20px;padding-top: 4px; word-break: break-all;font-size: 11px; "><?php echo $arr_comp_contact_info['CompanyContactInfo']['address']; ?>

                                </div>
                                <div style="text-align: center ; padding-top: 4px;"><?php echo $arr_comp_contact_info['CompanyContactInfo']['city']; ?>
                                    ,PIN - <?php echo $arr_comp_contact_info['CompanyContactInfo']['pincode']; ?>
                                    ,<?php echo $arr_comp_contact_info['CompanyContactInfo']['state']; ?>
                                </div>
                                <div style="text-align: center ; padding-top: 4px;"> <?php echo "Phone : " . $arr_comp_contact_info['CompanyContactInfo']['phone']; ?>
                                    <?php // echo ' Fax : ' . $arr_comp_contact_info['CompanyContactInfo']['fax']; 
                                    ?>
                                    <?php echo ' Email : ' . $arr_comp_contact_info['CompanyContactInfo']['email']; ?>
                                </div>
                            </div>

                        </div>


                        <hr>
                        <h3 style="text-align: center;padding-bottom: 20px;padding-top: 10px;"><?php echo 'Salary Slip - ' . "$mname-" . $year; ?></h3>
                        <br>
                    </page_header>
                    <page_footer>

                        <div style="width: 100%; text-align: right">
                            page [[page_cu]]/[[page_nb]]
                        </div>
                        <div style="width: 100%; text-align: left">
                            Downloaded By <?php echo $user_name; ?> <?php echo date("l,F j, Y"); ?>
                        </div>
                    </page_footer>
                    <bookmark title="Salaryslip" level="0"></bookmark>
                </page>
                <?php
                //echo $this->element('reportadminheader', array(
                //'title' => 'Salary Slip - '.date("M Y",strtotime($value['summary']['0']['ectc']['month_year']))));
                ?>


                <table class="table" align="center" style="margin-top: 10px; margin-left: 30px; width: 550px !important;  ">
                    <tbody>

                        <tr style="width : 100px ; ">
                            <th style="width:51%; border-bottom: 0px solid white ; border-right: 0px solid white ; " colspan="2"></th>
                            <th style="width:50%; border-bottom:  0px solid white ; border-left: 0px solid white ; " colspan="2"> </th>
                        </tr>

                        <tr>
                            <th colspan="4" style="padding-top: -2px;text-align: center;"> <b><?php
                                                                                                echo isset($value['summary']['0']['ed']['first_name']) ? $value['summary']['0']['ed']['first_name'] : '';
                                                                                                echo ' ';
                                                                                                echo isset($value['summary']['0']['ed']['middile_name']) ? $value['summary']['0']['ed']['middile_name'] : '';
                                                                                                echo ' ';
                                                                                                echo isset($value['summary']['0']['ed']['last_name']) ? $value['summary']['0']['ed']['last_name'] : '';

                                                                                                ?></b> - <?php echo isset($value['empdet']['0']['dd']['desig_name']) ? $value['empdet']['0']['dd']['desig_name'] : ''; ?> - <?php echo isset($value['summary']['0']['br']['branch_name']) ? $value['summary']['0']['br']['branch_name'] : ''; ?></th>
                        </tr>
                        <tr>
                            <th style="border-right-style: hidden; border-right: 0px solid white; border-bottom: 0px solid white ;  " colspan="2"><span style="text-align:  left ; ">Employee ID &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <?php echo isset($value['empdet']['0']['ep']['emp_company_id']) ? $value['empdet']['0']['ep']['emp_company_id'] : ''; ?></span></th>

                            <th style="border-right-style: hidden; border-right: 1px solid black; border-bottom: 0px solid white ; margin-right: 80px;" colspan="2"><span style="text-align:  left ; ">Date of Joining &nbsp;&nbsp;&nbsp;&nbsp;: <?php
                                                                                                                                                                                                                                                    //edited by megha on 9_7_19 date format changed
                                                                                                                                                                                                                                                    echo isset($value['summary']['0']['ep']['joining_date']) ? date('d-m-Y', strtotime($value['summary']['0']['ep']['joining_date'])) : '';
                                                                                                                                                                                                                                                    //echo isset($value['summary']['0']['ep']['joining_date']) ? $value['summary']['0']['ep']['joining_date'] : '';
                                                                                                                                                                                                                                                    ?></span></th>

                        </tr>
                        <tr>
                            <th style="border-right-style: hidden; border-bottom-style: hidden; border-right: 0px solid white; border-bottom: 0px solid white ;" colspan="2">Department&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <span style="margin-left: 1px; text-align: center;">
                                    <?php echo  wordwrap(isset($value['empdet']['0']['d']['dept_name']) ? $value['empdet']['0']['d']['dept_name'] : '', 30, "<br>\n", TRUE); ?> </span> </th>
                            <th style="border-right-style: hidden; border-right: 1px solid black; border-bottom: 0px solid white ; margin-right: 80px;" colspan="2"><span style="text-align:  left ; ">Gender&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <?php
                                                                                                                                                                                                                                                                                                                        echo isset($value['summary']['0']['ed']['classification']) ? strtoupper($value['summary']['0']['ed']['classification']) : ''; ?></span></th>
                        </tr>
                        <tr>
                            <th style="border-right-style: hidden; border-bottom-style: hidden; border-right: 0px solid white; border-bottom: 0px solid white ;" colspan="2">Leave Days&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <span style="margin-left: 1px; text-align: center;">
                                    <?php echo  isset($value['summary']['0']['payroll_master']['days_leave']) ? $value['summary']['0']['payroll_master']['days_leave'] : ''; ?> </span> </th>
                            <th style="border-right-style: hidden; border-right: 1px solid black; border-bottom: 0px solid white ; margin-right: 80px;" colspan="2"><span style="text-align:  left ; ">Present Days&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <?php
                                                                                                                                                                                                                                                                echo isset($value['summary']['0']['ar']['presant_total']) ? $value['summary']['0']['ar']['presant_total'] : ''; ?></span></th>
                        </tr>
                        <tr>
                            <th style="border-right-style: hidden; border-bottom-style: hidden; border-right: 0px solid white; border-bottom: 0px solid white ;" colspan="2">Non Paying Days&nbsp;: <span style="margin-left: 1px; text-align: center;">
                                    <?php echo  isset($value['empdet']['0']['payroll_master']['loss_of_pay']) ? $value['empdet']['0']['payroll_master']['loss_of_pay'] : ''; ?> </span> </th>
                            <th style="border-right-style: hidden; border-right: 1px solid black; border-bottom: 0px solid white ; margin-right: 80px;" colspan="2"><span style="text-align:  left ; ">No. of Week Off&nbsp;&nbsp;&nbsp;: <?php
                                                                                                                                                                                                                                            echo isset($value['summary']['0']['ar']['weekoff_total']) ? $value['summary']['0']['ar']['weekoff_total'] : ''; ?></span></th>
                        </tr>
                        <tr>
                            <th style="border-right-style: hidden; border-bottom-style: hidden; border-right: 0px solid white; border-bottom: 0px solid white ;" colspan="2">LOP Days&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <span style="margin-left: 1px; text-align: center;">
                                    <?php echo  isset($value['empdet']['0']['ar']['lop_only']) ? $value['empdet']['0']['ar']['lop_only'] : ''; ?> </span> </th>

                            <th style="border-right-style: hidden; border-bottom-style: hidden; border-right: 1px solid black; border-bottom: 0px solid white ;" colspan="2">No. of Holiday&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <span style="margin-left: 1px; text-align: center;">
                                     <?php echo  isset($value['summary']['0']['ar']['holiday_total']) ? $value['summary']['0']['ar']['holiday_total'] : ''; ?></span> </th>

                        </tr>
                        <tr>
                            <th style="border-right-style: hidden; border-bottom-style: hidden; border-right: 0px solid white; border-bottom: 0px solid white ;" colspan="2"><span style="text-align:  left ; ">PF account No&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <?php
                                                                                                                                                                                                                                                                echo isset($value['summary']['0']['ed']['company_pf']) ? $value['summary']['0']['ed']['company_pf'] : ''; ?></span></th>
                            <th style="border-right-style: hidden; border-bottom-style: hidden; border-right: 1px solid black; border-bottom: 0px solid white ; " colspan="2">ESI No&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <span style="margin-left: 1px; text-align: center;">
                                    <?php echo  isset($value['summary']['0']['ed']['esi']) ? $value['summary']['0']['ed']['esi'] : ''; ?> </span> </th>

                        </tr>


                        <?php $bank_name = '';
                        $branch_name = '';
                        $ifsc_code = '';
                        $acc_number = '';
                        $bank = isset($value['empdet']['0']['payroll_master']['bank_details']) ? $value['empdet']['0']['payroll_master']['bank_details'] : '';
                        if ($bank != '') {
                            list($bank_name, $branch_name, $ifsc_code, $acc_number) = explode(',', $bank);
                        }
                        if ($bank_name == '') {
                            $bank_name = isset($value['summary']['0']['ed']['bank_name']) ? $value['summary']['0']['ed']['bank_name'] : '';
                        }
                        if ($branch_name == '') {
                            $branch_name = isset($value['summary']['0']['ed']['branch_name']) ? $value['summary']['0']['ed']['branch_name'] : '';
                        }
                        if ($ifsc_code == '') {
                            $ifsc_code = isset($value['summary']['0']['ed']['ifsc_code']) ? $value['summary']['0']['ed']['ifsc_code'] : '';
                        }
                        if ($acc_number == '') {
                            $acc_number = isset($value['summary']['0']['ed']['account_no']) ? $value['summary']['0']['ed']['account_no'] : '';
                        } ?>
                        <tr>
                            <th style="border-right-style: hidden; border-bottom-style: hidden; border-right: 0px solid white; border-bottom: 0px solid white ;" colspan="2"><span style="text-align:  left ; ">UAN No&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <?php
                                                                                                                                                                                                                                                                                                                        echo isset($value['summary']['0']['ed']['pf']) ? $value['summary']['0']['ed']['pf'] : ''; ?></span></th>
                            <th style="border-right-style: hidden; border-right: 1px solid black; border-bottom: 0px solid white ;   " colspan="2"><span>Bank Name&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; : </span><span style="margin-left: 1px; text-align: center;">
                                    <?php
                                    echo wordwrap(isset($bank_name) ? $bank_name : '', 16, "<br>\n &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;", TRUE);
                                    ?> </span> </th>

                        </tr>
                        <tr>
                            <th style="border-right-style: hidden; border-bottom-style: hidden; border-right: 0px solid white; border-bottom: 0px solid white ;"" colspan=" 2">Account Number&nbsp;&nbsp;: <span style="margin-left: 1px; text-align: center;"><?php
                                                                                                                                                                                                                                                                echo wordwrap(isset($acc_number) ? $acc_number : '', 25, "<br>\n", TRUE);
                                                                                                                                                                                                                                                                ?></span></th>
                            <th style="border-right-style: hidden;border-bottom-style: hidden; 1px solid black; border-bottom: 0px solid white ;" colspan="2"><span style="text-align:  left ; ">IFSC Code &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <?php
                                                                                                                                                                                                                                                                            echo isset($ifsc_code) ? $ifsc_code : '';
                                                                                                                                                                                                                                                                            ?> </span></th>

                        </tr>

                        <tr>
                            <!-- Edited by Akshay on 4-4-2024 -->
                            <?php
                            $branch_name = isset($branch_name) ? $branch_name : '';
                            $wrapped_branch_name = wordwrap($branch_name, 16, "<br> &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;", true);
                            ?>
                            <th style="border-right-style: hidden; border-right: 1px solid black; white-space: normal;    " colspan="4"><span style="text-align:  left ; ">Branch&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;: </span><span style="text-align:center;"><?php echo $wrapped_branch_name; ?> </span></th>
                        </tr>


                        <tr style="background: #cccccc ;">

                            <!--    <th>LEAVEPOLICY_GROUP_NAME</th> -->


                            <th style="width:40%">Earnings</th>
                            <th style="width:10%">Amount</th>
                            <th style="width:41%">Deductions</th>

                            <!--<th>Leave days</th>-->
                            <th style="width:10%">Amount</th>

                        </tr>
                        <?php $arr_data = $value['summary'];
                        $arr_withoutComponents = $value['withoutcomponent'];
                        ?>
                        <?php
                        if (count($arr_data) >= 0) {
                            //Edited by Akshay on 29-10-2024
                            $og_total = 0;
                            $rnd_total = 0;
                            foreach ($arr_data as $item) {
                                $amount = $item['ectc']['salary_amount'];
                                $og_total += round($amount, 2);
                                $rnd_total += round($amount);
                            }
                            $diff = round($og_total) - $rnd_total;
                            //End
                            $sum = 0;
                            $tot = 0;
                            $dd = 0;
                            $net = 0;
                            //edited by megha on 15_5_19
                            $countss = count($arr_data);
                            if (count($arr_data) < count($arr_withoutComponents)) {
                                $countss = count($arr_withoutComponents);
                            }
                        ?>
                            <?php for ($i = 0; $i < $countss; $i++) {
                                //Edited by Akshay on 29-10-2024
                                if ($i == 0)
                                    $arr_data[$i]['ectc']['salary_amount'] = isset($arr_data[$i]['ectc']['salary_amount']) ? round($arr_data[$i]['ectc']['salary_amount']) + $diff : 0;
                                //End
                                //edited by megha on 15_5_19
                            ?>

                                <tr> <?php
                                        $sum += isset($arr_data[$i]['ectc']['salary_amount']) ? round($arr_data[$i]['ectc']['salary_amount']) : 0;
                                        if (isset($arr_withoutComponents[$i]['ectc']))
                                            $dd += isset($arr_withoutComponents[$i]['ectc']['salary_amount']) ? $arr_withoutComponents[$i]['ectc']['salary_amount'] : 0;
                                        ?>
                                    <!-- edited by megha on 30_05_19 round off  -->
                                    <td><?php echo isset($arr_data[$i]['ectc']['salary_head_item_desc']) ? $arr_data[$i]['ectc']['salary_head_item_desc'] : ''; ?></td>
                                    <!--<td><?php //echo isset($arr_data[$i]['ectc']['structure_det_value'])?round($arr_data[$i]['ectc']['structure_det_value'], 2):''; 
                                            ?></td>-->

                                    <td><?php echo isset($arr_data[$i]['ectc']['salary_amount']) ? abs(round($arr_data[$i]['ectc']['salary_amount'])) : ''; ?></td>

                                    <td><?php echo isset($arr_withoutComponents[$i]['ectc']['salary_head_item_desc']) ? $arr_withoutComponents[$i]['ectc']['salary_head_item_desc'] : ''; ?></td>
                                    <!--<td><?php //echo round(isset($arr_withoutComponents[$i]['ectc']['structure_det_value']) ? $arr_withoutComponents[$i]['ectc']['structure_det_value'] : '', 2); 
                                            ?></td>-->
                                    <!--  //edited by megha on 6_7_19 remove 0 values from deductions-->
                                    <td><?php echo isset($arr_withoutComponents[$i]['ectc']['salary_amount']) ? abs(round($arr_withoutComponents[$i]['ectc']['salary_amount'], 2)) : ''; ?></td>

                                </tr>

                            <?php } ?>
                            <tr style="background: #cccccc ;">
                                <th style="text-align :center ; ">Total Earnings</th>
                                <th><?php echo abs(round($sum)); ?></th>
                                <th>Total Deductions </th>
                                <th><?php echo abs(round($dd, 2)); ?></th>
                            </tr>
                            <!-- edited by megha on 16/11/19 settlement amount  -->
                            <?php if ($value['summary']['0']['ed']['status'] == 2) { ?>
                                <tr style="background: #cccccc ;">
                                    <th style="text-align :center ; " colspan="3">Settlement Amount</th>
                                    <th><?php echo $value['settle']; ?></th>
                                </tr>
                            <?php } ?>
                            <!-- end -->
                            <!-- edited by megha on 30_05_19 round off ,on 16/11/19 settlement amount -->
                            <tr style="background: #cccccc ;">
                                <th style="text-align :center ; " colspan="3">Net Pay</th>
                                <th><?php
                                    $total = round($sum) + round($dd, 2) + ($value['settle']);
                                    echo abs($total) == 0 ? 0 : $total; ?></th>
                            </tr>
                            <?php $arr_withoutComponents = $value['withoutcomponent']; ?>

                            <?php if (count($arr_withoutComponents) > 0) { ?>



                            <?php } ?>
                        <?php } else {
                        ?>
                            <tr>
                                <td colspan="4">No Components found under this data</td>
                            </tr>
                        <?php } ?>

                    </tbody>
                </table>
                <p style="padding-left: 20px; ">*This is a System generated pay slip and does not require signature.</p>
            <?php  }
        }
    } else {
        foreach ($arr_salary_for_template as $val) { ?>
            <?php foreach ($val as $value) {
                if (count($value['summary']) !== 0 || count($value['withoutcomponent']) !== 0) {
                    $i += 1;
            ?>
                    <page backtop="50mm" backbottom="2mm" backleft=2mm" backright="20mm" style="font-size: 12pt">

                        <page_header>


                            <!--        <div style="text-align:right; width:100%">

            <?php echo date("l,F j, Y"); ?> </div>-->
                            <div style="text-align:left; width:100%; ">
                                <?php if (isset($arr_comp_contact_info['CompanyContactInfo']['logo']) && !empty($arr_comp_contact_info['CompanyContactInfo']['logo'])) { ?>
                                    <div style="width: 20%; margin-left: 20px; font-size: 18px; ">
                                        <img style=" margin-left: 20px; margin-top: 40px; " src="http://<?php echo $_SERVER['HTTP_HOST'] . $this->webroot . $arr_comp_contact_info['CompanyContactInfo']['logo']; ?>" height="100" width="200" class="img-circle" alt="Company Logo" />
                                    </div>
                                    <!--<img style=" margin-left: 70px; " src="http://<?php echo $_SERVER['HTTP_HOST'] . $this->webroot . $arr_comp_contact_info['CompanyContactInfo']['logo']; ?>" height="50" width="70" class="img-circle" alt="Company Logo" />-->
                                <?php } ?>
                                <div style="width: 80%; margin-left: 100px; margin-top: 40px; position : absolute ; float: left; font-size: 14px; ">
                                    <div style=" text-align: center;">
                                        <p>FORM XIII –See Rules 29(2)</p>
                                    </div>
                                    <div style="text-align: center;font-weight: bold;font-size: 14px; ; padding-top: 4px; "><?php echo $arr_comp_contact_info['CompanyContactInfo']['business_name']; ?>

                                    </div>
                                    <div style="text-align: center ;  margin-left: 20px;padding-top: 4px; word-break: break-all;font-size: 11px; "><?php echo $arr_comp_contact_info['CompanyContactInfo']['address']; ?>

                                    </div>
                                    <div style="text-align: center ; padding-top: 4px;"><?php echo $arr_comp_contact_info['CompanyContactInfo']['city']; ?>
                                        ,PIN - <?php echo $arr_comp_contact_info['CompanyContactInfo']['pincode']; ?>
                                        ,<?php echo $arr_comp_contact_info['CompanyContactInfo']['state']; ?>
                                    </div>
                                    <div style="text-align: center ; padding-top: 4px;"> <?php echo "Phone : " . $arr_comp_contact_info['CompanyContactInfo']['phone']; ?>
                                        <?php // echo ' Fax : ' . $arr_comp_contact_info['CompanyContactInfo']['fax']; 
                                        ?>
                                        <?php echo ' Email : ' . $arr_comp_contact_info['CompanyContactInfo']['email']; ?>
                                    </div>
                                </div>

                            </div>


                            <hr>
                            <h3 style="text-align: center; padding-bottom: 20px; padding-top: 10px;">
                                <?php
                                $branchName = isset($val[0]['summary'][0]['branches']['branch_name']) ? $val[0]['summary'][0]['branches']['branch_name'] : (isset($val[0]['summary'][0]['br']['branch_name']) ?  $val[0]['summary'][0]['br']['branch_name'] : '');
                                echo 'Salary Slip of ' . $branchName . ' - ' . "$mname-" . $year;
                                ?>
                            </h3>
                            <br>
                        </page_header>
                        <page_footer>

                            <div style="width: 100%; text-align: right">
                                page [[page_cu]]/[[page_nb]]
                            </div>
                            <div style="width: 100%; text-align: left">
                                Downloaded By <?php echo $user_name; ?> <?php echo date("l,F j, Y"); ?>
                            </div>
                        </page_footer>
                        <bookmark title="Salaryslip" level="0"></bookmark>
                    </page>
                    <?php
                    //echo $this->element('reportadminheader', array(
                    //'title' => 'Salary Slip - '.date("M Y",strtotime($value['summary']['0']['ectc']['month_year']))));
                    ?>


                    <table class="table" align="center" style="margin-top: 10px; margin-left: 30px; width: 550px !important;  ">
                        <tbody>

                            <tr style="width : 100px ; ">
                                <th style="width:51%; border-bottom: 0px solid white ; border-right: 0px solid white ; " colspan="2"></th>
                                <th style="width:50%; border-bottom:  0px solid white ; border-left: 0px solid white ; " colspan="2"> </th>
                            </tr>

                            <tr>
                                <th colspan="4" style="padding-top: -2px;text-align: center;"> <b><?php
                                                                                                    echo isset($value['summary']['0']['ed']['first_name']) ? $value['summary']['0']['ed']['first_name'] : '';
                                                                                                    echo ' ';
                                                                                                    echo isset($value['summary']['0']['ed']['middile_name']) ? $value['summary']['0']['ed']['middile_name'] : '';
                                                                                                    echo ' ';
                                                                                                    echo isset($value['summary']['0']['ed']['last_name']) ? $value['summary']['0']['ed']['last_name'] : '';

                                                                                                    ?></b> - <?php echo isset($value['empdet']['0']['dd']['desig_name']) ? $value['empdet']['0']['dd']['desig_name'] : ''; ?> - <?php echo isset($value['summary']['0']['br']['branch_name']) ? $value['summary']['0']['br']['branch_name'] : ''; ?></th>
                            </tr>
                            <tr>
                                <th style="border-right-style: hidden; border-right: 0px solid white; border-bottom: 0px solid white ;  " colspan="2"><span style="text-align:  left ; ">Employee ID &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <?php echo isset($value['empdet']['0']['ep']['emp_company_id']) ? $value['empdet']['0']['ep']['emp_company_id'] : ''; ?></span></th>

                                <th style="border-right-style: hidden; border-right: 1px solid black; border-bottom: 0px solid white ; margin-right: 80px;" colspan="2"><span style="text-align:  left ; ">Date of Joining &nbsp;&nbsp;&nbsp;&nbsp;: <?php
                                                                                                                                                                                                                                                        //edited by megha on 9_7_19 date format changed
                                                                                                                                                                                                                                                        echo isset($value['summary']['0']['ep']['joining_date']) ? date('d-m-Y', strtotime($value['summary']['0']['ep']['joining_date'])) : '';
                                                                                                                                                                                                                                                        //echo isset($value['summary']['0']['ep']['joining_date']) ? $value['summary']['0']['ep']['joining_date'] : '';
                                                                                                                                                                                                                                                        ?></span></th>

                            </tr>
                            <tr>
                                <th style="border-right-style: hidden; border-bottom-style: hidden; border-right: 0px solid white; border-bottom: 0px solid white ;" colspan="2">Department&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <span style="margin-left: 1px; text-align: center;">
                                        <?php echo  wordwrap(isset($value['empdet']['0']['d']['dept_name']) ? $value['empdet']['0']['d']['dept_name'] : '', 30, "<br>\n", TRUE); ?> </span> </th>
                                <th style="border-right-style: hidden; border-right: 1px solid black; border-bottom: 0px solid white ; margin-right: 80px;" colspan="2"><span style="text-align:  left ; ">Gender&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <?php
                                                                                                                                                                                                                                                                                                                            echo isset($value['summary']['0']['ed']['classification']) ? strtoupper($value['summary']['0']['ed']['classification']) : ''; ?></span></th>
                            </tr>
                            <tr>
                                <th style="border-right-style: hidden; border-bottom-style: hidden; border-right: 0px solid white; border-bottom: 0px solid white ;" colspan="2">Leave Days&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <span style="margin-left: 1px; text-align: center;">
                                        <?php echo  isset($value['summary']['0']['payroll_master']['days_leave']) ? $value['summary']['0']['payroll_master']['days_leave'] : ''; ?> </span> </th>
                                <th style="border-right-style: hidden; border-right: 1px solid black; border-bottom: 0px solid white ; margin-right: 80px;" colspan="2"><span style="text-align:  left ; ">Present Days&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <?php
                                                                                                                                                                                                                                                                    echo isset($value['summary']['0']['ar']['presant_total']) ? $value['summary']['0']['ar']['presant_total'] : ''; ?></span></th>
                            </tr>
                            <tr>
                                <th style="border-right-style: hidden; border-bottom-style: hidden; border-right: 0px solid white; border-bottom: 0px solid white ;" colspan="2">Non Paying Days&nbsp;: <span style="margin-left: 1px; text-align: center;">
                                        <?php echo  isset($value['empdet']['0']['payroll_master']['loss_of_pay']) ? $value['empdet']['0']['payroll_master']['loss_of_pay'] : ''; ?> </span> </th>
                                <th style="border-right-style: hidden; border-right: 1px solid black; border-bottom: 0px solid white ; margin-right: 80px;" colspan="2"><span style="text-align:  left ; ">No. of Week Off&nbsp;&nbsp;&nbsp;: <?php
                                                                                                                                                                                                                                                echo isset($value['summary']['0']['ar']['weekoff_total']) ? $value['summary']['0']['ar']['weekoff_total'] : ''; ?></span></th>
                            </tr>
                            <tr>
                                <th style="border-right-style: hidden; border-bottom-style: hidden; border-right: 0px solid white; border-bottom: 0px solid white ;" colspan="2">LOP Days&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <span style="margin-left: 1px; text-align: center;">
                                        <?php echo  isset($value['empdet']['0']['ar']['lop_only']) ? $value['empdet']['0']['ar']['lop_only'] : ''; ?></span> </th>
                                <th style="border-right-style: hidden; border-bottom-style: hidden; border-right: 1px solid black; border-bottom: 0px solid white ;" colspan="2">No. of Holiday&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <span style="margin-left: 1px; text-align: center;">
                                        <?php echo  isset($value['summary']['0']['ar']['holiday_total']) ? $value['summary']['0']['ar']['holiday_total'] : ''; ?> </span> </th>

                            </tr>
                            <tr>
                                <th style="border-right-style: hidden; border-bottom-style: hidden; border-right: 0px solid white; border-bottom: 0px solid white ;" colspan="2"><span style="text-align:  left ; ">PF account No&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <?php
                                                                                                                                                                                                                                                                    echo isset($value['summary']['0']['ed']['company_pf']) ? $value['summary']['0']['ed']['company_pf'] : ''; ?></span></th>
                                <th style="border-right-style: hidden; border-bottom-style: hidden; border-right: 1px solid black; border-bottom: 0px solid white ; " colspan="2">ESI No&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <span style="margin-left: 1px; text-align: center;">
                                        <?php echo  isset($value['summary']['0']['ed']['esi']) ? $value['summary']['0']['ed']['esi'] : ''; ?> </span> </th>

                            </tr>


                            <?php $bank_name = '';
                            $branch_name = '';
                            $ifsc_code = '';
                            $acc_number = '';
                            $bank = isset($value['empdet']['0']['payroll_master']['bank_details']) ? $value['empdet']['0']['payroll_master']['bank_details'] : '';
                            if ($bank != '') {
                                list($bank_name, $branch_name, $ifsc_code, $acc_number) = explode(',', $bank);
                            }
                            if ($bank_name == '') {
                                $bank_name = isset($value['summary']['0']['ed']['bank_name']) ? $value['summary']['0']['ed']['bank_name'] : '';
                            }
                            if ($branch_name == '') {
                                $branch_name = isset($value['summary']['0']['ed']['branch_name']) ? $value['summary']['0']['ed']['branch_name'] : '';
                            }
                            if ($ifsc_code == '') {
                                $ifsc_code = isset($value['summary']['0']['ed']['ifsc_code']) ? $value['summary']['0']['ed']['ifsc_code'] : '';
                            }
                            if ($acc_number == '') {
                                $acc_number = isset($value['summary']['0']['ed']['account_no']) ? $value['summary']['0']['ed']['account_no'] : '';
                            } ?>
                            <tr>
                                <th style="border-right-style: hidden; border-bottom-style: hidden; border-right: 0px solid white; border-bottom: 0px solid white ;" colspan="2"><span style="text-align:  left ; ">UAN No&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <?php
                                                                                                                                                                                                                                                                                                                            echo isset($value['summary']['0']['ed']['pf']) ? $value['summary']['0']['ed']['pf'] : ''; ?></span></th>
                                <th style="border-right-style: hidden; border-right: 1px solid black; border-bottom: 0px solid white ;   " colspan="2"><span>Bank Name&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; : </span><span style="margin-left: 1px; text-align: center;">
                                        <?php
                                        echo wordwrap(isset($bank_name) ? $bank_name : '', 16, "<br>\n &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;", TRUE);
                                        ?> </span> </th>

                            </tr>
                            <tr>
                                <th style="border-right-style: hidden; border-bottom-style: hidden; border-right: 0px solid white; border-bottom: 0px solid white ;" colspan="2">Account Number&nbsp;&nbsp;: <span style="margin-left: 1px; text-align: center;"><?php
                                                                                                                                                                                                                                                                    echo wordwrap(isset($acc_number) ? $acc_number : '', 25, "<br>\n", TRUE);
                                                                                                                                                                                                                                                                    ?></span></th>
                                <th style="border-right-style: hidden; border-bottom-style: hidden; border-right: 1px solid black;border-bottom: 0px solid white ;" colspan="2"><span style="text-align:  left ; ">IFSC Code &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <?php
                                                                                                                                                                                                                                                                                            echo isset($ifsc_code) ? $ifsc_code : '';
                                                                                                                                                                                                                                                                                            ?> </span></th>
                                <?php
                                $branch_name = isset($branch_name) ? $branch_name : '';
                                $wrapped_branch_name = wordwrap($branch_name, 16, "<br> &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;", true);
                                ?>

                            </tr>

                            <tr>
                                <th style="border-right-style: hidden; border-right: 1px solid black;    " colspan="4"><span style="text-align:  left ; ">Branch&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;: </span><span style="text-align:center;"><?php echo $wrapped_branch_name; ?> </span></th>
                            </tr>


                            <tr style="background: #cccccc ;">

                                <!--    <th>LEAVEPOLICY_GROUP_NAME</th> -->


                                <th style="width:40%">Earnings</th>
                                <th style="width:10%">Amount</th>
                                <th style="width:41%">Deductions</th>

                                <!--<th>Leave days</th>-->
                                <th style="width:10%">Amount</th>

                            </tr>
                            <?php $arr_data = $value['summary'];
                            $arr_withoutComponents = $value['withoutcomponent'];
                            ?>
                            <?php
                            if (count($arr_data) >= 0) {
                                $sum = 0;
                                $tot = 0;
                                $dd = 0;
                                $net = 0;
                                //Edited by Akshay on 29-10-2024
                                $og_total = 0;
                                $rnd_total = 0;
                                foreach ($arr_data as $item) {
                                    $amount = $item['ectc']['salary_amount'];
                                    $og_total += round($amount, 2);
                                    $rnd_total += round($amount);
                                }
                                $diff = round($og_total) - $rnd_total;
                                //End
                                //edited by megha on 15_5_19
                                $countss = count($arr_data);
                                if (count($arr_data) < count($arr_withoutComponents)) {
                                    $countss = count($arr_withoutComponents);
                                }
                            ?>
                                <?php for ($i = 0; $i < $countss; $i++) {
                                    //Edited by Akshay on 29-10-2024
                                    if ($i == 0)
                                        $arr_data[$i]['ectc']['salary_amount'] = isset($arr_data[$i]['ectc']['salary_amount']) ? round($arr_data[$i]['ectc']['salary_amount']) + $diff : 0;
                                    //End
                                    //edited by megha on 15_5_19
                                ?>

                                    <tr> <?php
                                            $sum += isset($arr_data[$i]['ectc']['salary_amount']) ? round($arr_data[$i]['ectc']['salary_amount']) : 0;
                                            if (isset($arr_withoutComponents[$i]['ectc']))
                                                $dd += isset($arr_withoutComponents[$i]['ectc']['salary_amount']) ? $arr_withoutComponents[$i]['ectc']['salary_amount'] : 0;
                                            ?>
                                        <!-- edited by megha on 30_05_19 round off  -->
                                        <td><?php echo isset($arr_data[$i]['ectc']['salary_head_item_desc']) ? $arr_data[$i]['ectc']['salary_head_item_desc'] : ''; ?></td>
                                        <!--<td><?php //echo isset($arr_data[$i]['ectc']['structure_det_value'])?round($arr_data[$i]['ectc']['structure_det_value'], 2):''; 
                                                ?></td>-->

                                        <td><?php echo isset($arr_data[$i]['ectc']['salary_amount']) ? abs(round($arr_data[$i]['ectc']['salary_amount'])) : ''; ?></td>

                                        <td><?php echo isset($arr_withoutComponents[$i]['ectc']['salary_head_item_desc']) ? $arr_withoutComponents[$i]['ectc']['salary_head_item_desc'] : ''; ?></td>
                                        <!--<td><?php //echo round(isset($arr_withoutComponents[$i]['ectc']['structure_det_value']) ? $arr_withoutComponents[$i]['ectc']['structure_det_value'] : '', 2); 
                                                ?></td>-->
                                        <!--  //edited by megha on 6_7_19 remove 0 values from deductions-->
                                        <td><?php echo isset($arr_withoutComponents[$i]['ectc']['salary_amount']) ? abs(round($arr_withoutComponents[$i]['ectc']['salary_amount'], 2)) : ''; ?></td>

                                    </tr>

                                <?php } ?>
                                <tr style="background: #cccccc ;">
                                    <th style="text-align :center ; ">Total Earnings</th>
                                    <th><?php echo abs(round($sum)); ?></th>
                                    <th>Total Deductions </th>
                                    <th><?php echo abs(round($dd, 2)); ?></th>
                                </tr>
                                <!-- edited by megha on 16/11/19 settlement amount  -->
                                <?php if ($value['summary']['0']['ed']['status'] == 2) { ?>
                                    <tr style="background: #cccccc ;">
                                        <th style="text-align :center ; " colspan="3">Settlement Amount</th>
                                        <th><?php echo $value['settle']; ?></th>
                                    </tr>
                                <?php } ?>
                                <!-- end -->
                                <!-- edited by megha on 30_05_19 round off ,on 16/11/19 settlement amount -->
                                <tr style="background: #cccccc ;">
                                    <th style="text-align :center ; " colspan="3">Net Pay</th>
                                    <th><?php
                                        $total = round($sum) + round($dd, 2) + $value['settle'];
                                        echo abs($total) == 0 ? 0 : $total; ?></th>
                                </tr>
                                <?php $arr_withoutComponents = $value['withoutcomponent']; ?>

                                <?php if (count($arr_withoutComponents) > 0) { ?>



                                <?php } ?>
                            <?php } else {
                            ?>
                                <tr>
                                    <td colspan="4">No Components found under this data</td>
                                </tr>
                            <?php } ?>

                        </tbody>
                    </table>
                    <p style="padding-left: 20px; ">*This is a system generated pay slip and does not require signature.</p>

        <?php
                }
            }
        }
    }
    if ($i == '0') { ?>
        <div style="font-size: 25px;text-align:center; background-color:#F7D3D2;">
            There is no data available</div>
    <?php } ?>
<?php }
?>