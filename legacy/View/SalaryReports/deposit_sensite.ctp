<?php

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

        .heading {
            text-align: center;
        }

        .amounts {
            text-align: right;
        }

        .rightbottom {
            border-right: 0px solid white;
            border-bottom: 0px solid;
        }
    </style>
    <?php //debug(array_filter($arr_salary_for_template)); 
    ?>
    <div class="modal-body" style="overflow-y: auto;">
        <div class="row">
            <h2 align="center"><b><?php echo 'Salary Slip - ' . "$mname1 " . $y1; ?></b> </h2>
            <h2 style="font-weight: bold;text-align: center;font-size: 19px;"><?php echo  "(Report Run by " . $user_id . " at " . $date_time . ")" ?> </h2>
            <div class="col-md-12">
                <div class=" ">
                    <?php
                    $i = 0;
                    if ($cr == 'EmployeeDetails') {

                        foreach ($arr_salary_for_template as $value) {
                            if (count($value['summary']) != 0) {
                                $i += 1;
                    ?>


                                <div class="row  " style="padding-top: 10px; margin-left: 0px; margin-right: 0px; border-right: 1px solid black; border-left: 1px solid black;">




                                    <table class="table table-bordered" align="center">
                                        <tbody>
                                            <!-- <tr style="background: #cccccc ;width : 120% ;"> -->
                                            <tr style="width : 120% ;">

                                                <!--    <th>LEAVEPOLICY_GROUP_NAME</th> -->


                                                <th style="width:40%">Earnings(Rs.)</th>
                                                <th style="width:10%">Actual Amount</th>
                                                <th style="width:10%">Earned Amount</th>
                                                <th style="width:41%">Deductions (Rs.)</th>

                                                <!--<th>Leave days</th>-->
                                                <th style="width:10%">Amount</th>

                                            </tr>
                                            <?php $arr_data = $value['summary'];
                                            $arr_withoutComponents = $value['withoutcomponent'];
                                            $variables = isset($value['variables']) ? $value['variables'] : 0;
                                            // debug($variables);
                                            if ($variables > 0) {
                                                $j = count($arr_data);
                                                $arr_data[$j]['ectc']['salary_amount'] = $variables;
                                                $arr_data[$j]['ectc']['structure_det_value'] = 0;
                                                $arr_data[$j]['ectc']['salary_head_item_desc'] = 'Variable Pay';
                                            }
                                            ?>
                                            <?php
                                            if (count($arr_data) >= 0) {
                                                $countss = count($arr_data);
                                                if (count($arr_data) < count($arr_withoutComponents)) {
                                                    $countss = count($arr_withoutComponents);
                                                }
                                                $sum = 0;
                                                $tot = 0;
                                                $dd = 0;
                                                $net = 0;
                                                $actual_sum = 0;

                                            ?>
                                                <?php for ($i = 0; $i < $countss; $i++) {
                                                ?>
                                                    <tr> <?php
                                                            $sum += isset($arr_data[$i]['ectc']['salary_amount']) ? $arr_data[$i]['ectc']['salary_amount'] : 0;
                                                            $actual_sum += isset($arr_data[$i]['ectc']['structure_det_value']) ? $arr_data[$i]['ectc']['structure_det_value'] : 0;
                                                            if (isset($arr_withoutComponents[$i]['ectc']))
                                                                $dd += isset($arr_withoutComponents[$i]['ectc']['salary_amount']) ? $arr_withoutComponents[$i]['ectc']['salary_amount'] : 0;
                                                            ?>

                                                        <td><?php echo isset($arr_data[$i]['ectc']['salary_head_item_desc']) ? trim($arr_data[$i]['ectc']['salary_head_item_desc']) : ''; ?></td>
                                                        <td class="amounts"><?php echo isset($arr_data[$i][0]['total_actual_salary']) ? abs(round($arr_data[$i][0]['total_actual_salary'])) : ''; ?></td>
                                                        <td class="amounts"><?php echo isset($arr_data[$i][0]['total_earned_salary']) ? abs(round($arr_data[$i][0]['total_earned_salary'])) : ''; ?></td>

                                                        <td><?php echo isset($arr_withoutComponents[$i]['ectc']['salary_head_item_desc']) ? trim($arr_withoutComponents[$i]['ectc']['salary_head_item_desc']) : ''; ?></td>

                                                        <td class="amounts"><?php echo isset($arr_withoutComponents[$i]['ectc']['salary_amount']) ? abs(round($arr_withoutComponents[$i]['ectc']['salary_amount'], 2)) : ''; ?></td>

                                                    </tr>

                                                <?php
                                                }

                                                ?>
                                                <!-- <tr style="background: #cccccc ;"> -->
                                                <tr>
                                                    <th style="text-align :left; border-right:none; ">Gross Salary:</th>
                                                    <?php $comma_actual_sum =  (formatIndianNumber(abs(round($actual_sum)))) ?>
                                                    <th class="amounts" style="border-right:none;"><?php echo $comma_actual_sum; ?></th>
                                                    <?php $comma_sum = formatIndianNumber(abs(round($sum))); ?>
                                                    <th class="amounts" style="border-right:none;"><?php echo $comma_sum; ?></th>
                                                    <th style="border-right:none;"></th>
                                                    <?php $comma_dd = formatIndianNumber(abs(round($dd, 2))); ?>
                                                    <th class="amounts"><?php echo $comma_dd; ?></th>
                                                </tr>
                                                <!-- edited by megha on 16/11/19 settlement amount  -->
                                                <?php if ($value['summary']['0']['ed']['status'] == 2) { ?>
                                                    <!-- <tr style="background: #cccccc ;"> -->
                                                    <tr>
                                                        <th style="text-align :left;border-top:none; border-right: none; " colspan="3"></th>
                                                        <th style="text-align :left;border-top:none;  border-right: none;">Settlement Amount</th>
                                                        <th class="amounts" style="border-top: none;"><?php echo formatIndianNumber((round($value['settle']))); ?></th>
                                                    </tr>
                                                <?php } ?>
                                                <!-- end -->
                                                <!-- edited by megha on 30_05_19 round off, edited by megha on 16/11/19 settlement amount  -->

                                                <!-- <tr style="background: #cccccc ;"> -->
                                                <?php $net_amount = $sum + $dd + (isset($value['settle']) ? $value['settle'] : 0); ?>
                                                <tr>
                                                    <th style="text-align :center ;border-right:0px solid white;border-top:none; " colspan="3"></th>
                                                    <th style="text-align :left ;border-right:0px solid white;border-top:none; "><b>Net Salary:</b></th>
                                                    <th class="amounts" style="border-top:none;"><b><?php echo formatIndianNumber($net_amount); ?></b></th>
                                                </tr>
                                                <?php
                                                if ($net_amount >= 0) {
                                                    $amountInWords = '';
                                                } else {
                                                    $amountInWords = ' Negative ';
                                                }
                                                $amountInWords .= convertToWords(round($net_amount));
                                                ?>
                                                <!-- <tr style="background: #cccccc ;"> -->
                                                <tr>
                                                    <td style="text-align :center; border-top:none;" colspan="5"><?php echo "Amount In Words:- Rupees " . $amountInWords . " Only"; ?></td>
                                                </tr>
                                                <?php $arr_withoutComponents = $value['withoutcomponent']; ?>

                                                <?php if (count($arr_withoutComponents) > 0) {
                                                ?>



                                                <?php } ?>
                                            <?php } else {
                                            ?>
                                                <tr>
                                                    <td colspan="4" style="font-size: 20px;">No data available under the selected criteria.</td>
                                                </tr>
                                            <?php } ?>

                                        </tbody>
                                    </table>

                                <?php
                            }
                        }
                    } else {
                        foreach ($arr_salary_for_template as $val) { ?>
                                <legend><?php echo $val['0']['summary']['0']['br']['branch_name']; ?> </legend>
                                <?php foreach ($val as $value) {
                                    if (count($value['summary']) != 0) {
                                        $i += 1;
                                ?>


                                        <table class="table table-bordered" align="center" style="margin-top: 10px;margin-bottom: 0px; ">
                                            <tbody>
                                                <tr>
                                                    <th><?php echo isset($arr_comp_contact_info['CompanyContactInfo']['business_name']) ? $arr_comp_contact_info['CompanyContactInfo']['business_name'] : ''; ?></th>
                                                </tr>
                                                <tr>
                                                    <td class="heading">[FORM XIII See rule 29(2)]</td>
                                                </tr>
                                                <tr>
                                                    <td class="heading"><?php echo 'Pay Slip - ' . "$mname1 " . $y1; ?></td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        <div class="row  " style="padding-top: 10px; margin-left: 0px; margin-right: 0px; border-right: 1px solid black; border-left: 1px solid black;">
                                            <div class="col-md-3 ">
                                                Emp Code:
                                            </div>
                                            <div class="col-md-3 ">
                                                <span style="text-align: right ; word-wrap: break-word; "> <?php echo isset($value['empdet']['0']['ep']['emp_company_id']) ? $value['empdet']['0']['ep']['emp_company_id'] : ''; ?> </span><span style="float: right ;"></span>
                                            </div>
                                            <div class="col-md-3">
                                                Bank:
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
                                                <span style="text-align: right ; ">
                                                    <?php
                                                    echo isset($bank_name) ? $bank_name : '';
                                                    ?> </span><span style="float: right ;">
                                            </div>
                                        </div>
                                        <div class="row  " style="padding-top: 10px; margin-left: 0px; margin-right: 0px; border-right: 1px solid black; border-left: 1px solid black;">

                                            <div class="col-md-3">
                                                <span> Name: </span><span></span>
                                            </div>
                                            <div class="col-md-3 ">
                                                <?php
                                                $first_name = isset($value['summary']['0']['ed']['first_name']) ? $value['summary']['0']['ed']['first_name'] : '';
                                                $middle_name = isset($value['summary']['0']['ed']['middile_name']) ? $value['summary']['0']['ed']['middile_name'] : '';
                                                $last_name = isset($value['summary']['0']['ed']['last_name']) ? $value['summary']['0']['ed']['last_name'] : '';
                                                $status = isset($value['summary']['0']['ed']['status']) ? $value['summary']['0']['ed']['status'] : '';
                                                if ($status == 1) {
                                                    $status = '';
                                                } else {
                                                    $status = '(Resigned)';
                                                }
                                                ?>
                                                <span style="text-align: right ; word-wrap: break-word; "> <?php echo  wordwrap($first_name . ' ' . $middle_name . ' ' . $last_name . ' ' . $status); ?> </span><span style="float: right ;"></span>
                                            </div>
                                            <div class="col-md-3">
                                                <span> A/C NO: </span><span></span>
                                            </div>
                                            <div class="col-md-3">
                                                <span style="text-align: right ; "> <?php
                                                                                    echo isset($acc_number) ? $acc_number : '';
                                                                                    //echo isset($value['summary']['0']['ed']['account_no']) ? $value['summary']['0']['ed']['account_no'] : '';
                                                                                    ?> </span><span style="float: right ;"></span>
                                            </div>
                                        </div>
                                        <div class="row  " style="padding-top: 10px; margin-left: 0px; margin-right: 0px; border-right: 1px solid black; border-left: 1px solid black;">

                                            <div class="col-md-3">
                                                <span> Department: </span><span></span>
                                            </div>
                                            <div class="col-md-3 ">
                                                <span style="text-align: right ; word-wrap: break-word; "> <?php echo  wordwrap(isset($value['empdet']['0']['d']['dept_name']) ? $value['empdet']['0']['d']['dept_name'] : '', 21, "<br>\n", TRUE); ?> </span><span style="float: right ;"></span>
                                            </div>
                                            <div class="col-md-3">
                                                <span> UAN NO: </span><span></span>
                                            </div>
                                            <div class="col-md-3">
                                                <span style="text-align: left ;  border-right: 0px solid white; border-bottom: 0px solid white ; "> <?php echo isset($value['summary']['0']['ed']['pf']) ? $value['summary']['0']['ed']['pf'] : ''; ?> </span><span style="float: right ;"></span>

                                            </div>

                                        </div>

                                        <div class="row  " style="padding-top: 10px; margin-left: 0px; margin-right: 0px; border-right: 1px solid black; border-left: 1px solid black;">
                                            <div class="col-md-3">
                                                Designation:
                                            </div>
                                            <div class="col-md-3">
                                                <span style="text-align: right ; "> <?php echo isset($value['empdet']['0']['dd']['desig_name']) ? $value['empdet']['0']['dd']['desig_name'] : ''; ?> </span><span style="float: right ;"></span>
                                            </div>
                                            <div class="col-md-3">
                                                <span style="text-align:  left ; ">ESI NO: </span>
                                            </div>
                                            <div class="col-md-3">
                                                <?php echo isset($value['summary']['0']['ed']['esi']) ? $value['summary']['0']['ed']['esi'] : ''; ?>

                                            </div>


                                        </div>
                                        <div class="row  " style="padding-top: 10px; margin-left: 0px; margin-right: 0px; border-right: 1px solid black; border-left: 1px solid black;">
                                            <div class="col-md-3">
                                                <span style="text-align:  left ; ">Pay Mode: </span>
                                            </div>
                                            <div class="col-md-3">
                                                <?php
                                                $pay_mode = '';
                                                $payment_type = isset($value['empdet'][0]['ed']['payment_type']) ? $value['empdet'][0]['ed']['payment_type'] : '';

                                                if ($payment_type == 'bank') {
                                                    $pay_mode = 'Bank transfer';
                                                } else if ($payment_type == 'neft') {
                                                    $pay_mode = 'NEFT';
                                                } else {
                                                    $pay_mode = ucfirst($payment_type);
                                                }
                                                $calender_days = isset($value['empdet']['0']['payroll_master']['calander_days']) ? $value['empdet']['0']['payroll_master']['calander_days'] : '';
                                                $loss_offp = isset($value['empdet']['0']['payroll_master']['loss_of_pay']) ? $value['empdet']['0']['payroll_master']['loss_of_pay'] : '';
                                                $days_present = isset($value['empdet']['0']['payroll_master']['days_presant']) ? $value['empdet']['0']['payroll_master']['days_presant'] : '';
                                                ?>
                                                <span style="text-align: right ; "> <?php echo $pay_mode; ?></span><span style="float: right ;"></span>

                                            </div>
                                        </div>

                                        <div class="row  " style="padding-top: 10px; margin-left: 0px; margin-right: 0px; border-right: 1px solid black; border-left: 1px solid black; border-top: 1px solid black; border-bottom: 1px solid black;">
                                            <div class="col-md-3">
                                                <span style="text-align:  left ; "><b>Paid days: </b></span>
                                            </div>
                                            <div class="col-md-3">
                                                <?php
                                                $prodate = isset($value['empdet']['prodate_type']) ? $value['empdet']['prodate_type'] : '';

                                                if ($prodate == 'Calender Days') {
                                                ?>
                                                    <span style="text-align: right ; "></span><b> <?php echo abs($calender_days - $loss_offp); ?></b><span style="float: right ;"></span>
                                                <?php } else {
                                                    $days_present = isset($value['empdet']['0']['payroll_master']['days_presant']) ? $value['empdet']['0']['payroll_master']['days_presant'] : '';
                                                ?>
                                                    <span style="text-align: right ; "></span><b> <?php echo abs($days_present); ?></b><span style="float: right ;"></span>
                                                <?php } ?>
                                            </div>
                                        </div>

                                        <table class="table table-bordered" align="center">
                                            <tbody>
                                                <!-- <tr style="background: #cccccc ;width : 120% ;"> -->
                                                <tr style="width : 120% ;">

                                                    <!--    <th>LEAVEPOLICY_GROUP_NAME</th> -->


                                                    <th style="width:40%">Earnings(Rs.)</th>
                                                    <th style="width:10%">Actual Amount</th>
                                                    <th style="width:10%">Earned Amount</th>
                                                    <th style="width:41%">Deductions (Rs.)</th>
                                                    <th style="width:10%">Amount</th>

                                                </tr>
                                                <?php $arr_data = $value['summary'];
                                                $arr_withoutComponents = $value['withoutcomponent'];
                                                $variables = isset($value['variables']) ? $value['variables'] : 0;
                                                // debug($variables);
                                                if ($variables > 0) {
                                                    $j = count($arr_data);
                                                    $arr_data[$j]['ectc']['salary_amount'] = $variables;
                                                    $arr_data[$j]['ectc']['structure_det_value'] = 0;
                                                    $arr_data[$j]['ectc']['salary_head_item_desc'] = 'Variable Pay';
                                                }
                                                ?>
                                                <?php
                                                if (count($arr_data) >= 0) {
                                                    $countss = count($arr_data);
                                                    if (count($arr_data) < count($arr_withoutComponents)) {
                                                        $countss = count($arr_withoutComponents);
                                                    }
                                                    $sum = 0;
                                                    $tot = 0;
                                                    $dd = 0;
                                                    $net = 0;
                                                    $actual_sum = 0;
                                                ?>
                                                    <?php for ($i = 0; $i < $countss; $i++) {
                                                    ?>
                                                        <tr> <?php
                                                                $sum += isset($arr_data[$i]['ectc']['salary_amount']) ? $arr_data[$i]['ectc']['salary_amount'] : 0;
                                                                $actual_sum += isset($arr_data[$i]['ectc']['structure_det_value']) ? $arr_data[$i]['ectc']['structure_det_value'] : 0;
                                                                if (isset($arr_withoutComponents[$i]['ectc']))
                                                                    $dd += isset($arr_withoutComponents[$i]['ectc']['salary_amount']) ? $arr_withoutComponents[$i]['ectc']['salary_amount'] : 0;
                                                                ?>
                                                            <!-- edited by megha on 30_05_19 round off  -->

                                                            <td><?php echo isset($arr_data[$i]['ectc']['salary_head_item_desc']) ? trim($arr_data[$i]['ectc']['salary_head_item_desc']) : ''; ?></td>
                                                            <!--<td><?php //echo isset($arr_data[$i]['ectc']['structure_det_value'])?round($arr_data[$i]['ectc']['structure_det_value'], 2):''; 
                                                                    ?></td>-->
                                                            <td class="amounts"><?php echo isset($arr_data[$i]['ectc']['structure_det_value']) ? abs(round($arr_data[$i]['ectc']['structure_det_value'])) : ''; ?></td>

                                                            <td class="amounts"><?php echo isset($arr_data[$i]['ectc']['salary_amount']) ? abs(round($arr_data[$i]['ectc']['salary_amount'])) : ""; ?></td>

                                                            <td><?php echo isset($arr_withoutComponents[$i]['ectc']['salary_head_item_desc']) ? trim($arr_withoutComponents[$i]['ectc']['salary_head_item_desc']) : ''; ?></td>
                                                            <!--<td><?php //echo round(isset($arr_withoutComponents[$i]['ectc']['structure_det_value']) ? $arr_withoutComponents[$i]['ectc']['structure_det_value'] : '', 2); 
                                                                    ?></td>-->
                                                            <!-- edited by megha on 08_07_19 '0' values removed  -->
                                                            <td class="amounts"><?php echo isset($arr_withoutComponents[$i]['ectc']['salary_amount']) ? abs(round($arr_withoutComponents[$i]['ectc']['salary_amount'], 2)) : ''; ?></td>

                                                        </tr>

                                                    <?php } ?>
                                                    <!-- <tr style="background: #cccccc ;"> -->
                                                    <tr>
                                                        <th style="text-align :left; border-right:none;">Gross Salary:</th>
                                                        <th class="amounts" style="border-right:none;"><?php echo formatIndianNumber(abs(round($actual_sum))); ?></th>
                                                        <th class="amounts" style="border-right:none;"><?php echo formatIndianNumber(abs(round($sum))); ?></th>
                                                        <th style="border-right:none;"></th>
                                                        <th class="amounts"><?php echo formatIndianNumber(abs(round($dd, 2))); ?></th>
                                                    </tr>
                                                    <!-- edited by megha on 16/11/19 settlement amount  -->
                                                    <?php if ($value['summary']['0']['ed']['status'] == 2) { ?>
                                                        <!-- <tr style="background: #cccccc ;"> -->
                                                        <tr>
                                                            <th style="text-align :left;border-top:none; border-right: none; " colspan="3"></th>
                                                            <th style="text-align :left;border-top:none; border-right: none; ">Settlement Amount</th>
                                                            <th class="amounts" style="border-top: none;"><?php echo formatIndianNumber(round($value['settle'])); ?></th>
                                                        </tr>
                                                    <?php } ?>
                                                    <!-- end -->
                                                    <!-- edited by megha on 30_05_19 round off, edited by megha on 16/11/19 settlement amount  -->

                                                    <!-- <tr style="background: #cccccc ;"> -->
                                                    <?php $net_amount = $sum + $dd + (isset($value['settle']) ? $value['settle'] : 0); ?>
                                                    <tr>
                                                        <th style="text-align :center ;border-right:0px solid white; border-top: none; " colspan="3"></th>
                                                        <th style="text-align :left ;border-right:0px solid white;border-top: none; "><b>Net Salary:</b></th>
                                                        <th class="amounts" style="border-top: none;"><b><?php echo formatIndianNumber($net_amount); ?></b></th>
                                                    </tr>
                                                    <?php
                                                    if ($net_amount >= 0) {
                                                        $amountInWords = '';
                                                    } else {
                                                        $amountInWords = ' Negative ';
                                                    }
                                                    $amountInWords .= convertToWords(round($net_amount));
                                                    ?>
                                                    <!-- <tr style="background: #cccccc ;"> -->
                                                    <tr>
                                                        <td style="text-align :center; border-top: none;" colspan="5"><?php echo "Amount In Words:- Rupees " . $amountInWords . " Only"; ?></td>
                                                    </tr>
                                                    <?php $arr_withoutComponents = $value['withoutcomponent']; ?>

                                                    <?php if (count($arr_withoutComponents) > 0) {
                                                    ?>



                                                    <?php } ?>
                                                <?php } else {
                                                ?>
                                                    <tr>
                                                        <td colspan="4" style="font-size: 20px;">No data available under the selected criteria.</td>
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
                            <div style="font-size: 20px;">
                                No data available under the selected criteria.</div>
                        <?php } ?>

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
        <?php
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
                border: 1px solid #000000;
            }

            td,
            th {
                text-align: left;
                padding: 8px;
                font-weight: normal;
                /*font-size: 11px;*/
                font-size: 12px;
                /*font-family: serif;*/
                line-height: 1.42857143;
                /* line-height: 1; */
                word-wrap: break-word;
                word-break: break-all;
                vertical-align: top;
                color: black;
                border: 1px solid black;
                box-shadow: inset 0.5px 0 #FF0000, inset 1px 0 #00FF00, inset 1.5px 0 #0000FF;
                /* border-left: .2px white;
                border-right: .2px white; */
            }

            .amounts {
                text-align: right;
            }

            @media print {
                .table {
                    page-break-inside: avoid;
                    /* Avoid breaking inside the table */
                }

                .table:after {
                    content: '';
                    display: block;
                    border-bottom: 1.5px solid #989898;
                    /* Your border style */
                }
            }
        </style>


        <?php

        ?>
        <?php
        $i = 0;
        if (count($arr_salary_for_template) > 0) {

            if ($cr == 'EmployeeDetails') {
                // debug($arr_salary_for_template); exit;
                foreach ($arr_salary_for_template as $value) {

                    if (count($value['summary']) !== 0 || count($value['withoutcomponent']) !== 0) {
                        $i += 1;
        ?>

                        <page backtop="30mm" backbottom="10mm" backleft="2mm" backright="2mm" style="font-size: 12pt; ">

                            <page_header>
                                <div style=" padding-left: 20px;">
                                    <table style="margin-left: 50px; border:1px solid white;">
                                        <tr>
                                            <td style="border:1px solid white;"><img style="width: auto; height: 80px;" src="https://<?php echo $_SERVER['HTTP_HOST'] . $this->webroot . $arr_comp_contact_info['CompanyContactInfo']['logo']; ?>" alt="Company Logo" /></td>
                                            <td style="padding-bottom: 0px;border:1px solid white;padding-top:20px; ">
                                                <h3 style="text-align: center;"><?php echo isset($arr_comp_contact_info['CompanyContactInfo']['business_name']) ? $arr_comp_contact_info['CompanyContactInfo']['business_name'] : ''; ?></h3>
                                            </td>
                                        </tr>
                                    </table>
                                </div>

                                <div style="text-align: center;  width:100%;">
                                    <span style="float: left; font-size: 12px;text-align:left;"><?php echo (date("d/m/Y") . date("h:i A")); ?></span>
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    <b>ED Summary - <?php echo "$y1" . '/' . "$month"; ?> To <?php echo "$y1 " . '/' . "$month"; ?></b>
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                    <span style="float: right; font-size: 12px;text-align:right;">page [[page_cu]]/[[page_nb]]</span>
                                </div>
                            </page_header>

                            <bookmark title="Sommaire" level="0"></bookmark>
                        </page>
                        <?php
                        //echo $this->element('reportadminheader', array(
                        //'title' => 'Salary Slip - '.date("M Y",strtotime($value['summary']['0']['ectc']['month_year']))));
                        ?>

                        <?php
                        $bank_name = '';
                        $branch_name = '';
                        $ifsc_code = '';
                        $acc_number = '';
                        $lopdeduction = $value['lopdeduction'];
                        $bank = isset($value['empdet']['0']['payroll_master']['bank_details']) ? $value['empdet']['0']['payroll_master']['bank_details'] : '';
                        ?>
                        <!-- <div style="border: 1.5px solid #989898;display: inline-block; vertical-align: top;"> -->
                        <table class="table" align="center" style="margin-top: 10px;;  ">
                            <tbody>

                                <?php $emp_no = $emp_count;
                                //debug($value['lopdeduction']);exit;
                                ?>

                                <tr>
                                    <th style=" font-weight: bold; ">Sl.No</th>
                                    <th style=" font-weight: bold; ">Items</th>
                                    <th style="width:30px;font-weight: bold; text-align: right;;">Standard Amount</th>
                                    <th style="width:30px;font-weight: bold;">Actual Amount</th>
                                    <th style="font-weight: bold; ">Difference</th>
                                    <!-- <th style=" font-weight: bold;">Standard Amount</th> -->
                                    <!-- <th style=" font-weight: bold;">Actual Amount</th> -->
                                </tr>
                                <tr>
                                    <td colspan="5" style=" font-weight: bold; text-align: center;">Earnings</td>
                                </tr>
                                <?php $arr_data = $value['summary'];
                                $arr_withoutComponents = $value['withoutcomponent'];
                                $variables = isset($value['variables']) ? $value['variables'] : 0;
                                // debug($variables); exit;
                                if ($variables > 0) {
                                    $j = count($arr_data);
                                    $arr_data[$j]['ectc']['salary_amount'] = $variables;
                                    $arr_data[$j]['ectc']['structure_det_value'] = 0;
                                    $arr_data[$j]['ectc']['salary_head_item_desc'] = 'Variable Pay';
                                }
                                ?>
                                <?php
                                if (count($arr_data) >= 0) {
                                    $sum = 0;
                                    $tot = 0;
                                    $dd = 0;
                                    $st_dd = 0;
                                    $net = 0;
                                    $structure = 0;
                                    $total_diff = 0;

                                    $lop_amount = 0;
                                    //    debug(($lopdeduction));
                                    if (count($lopdeduction) > 0) {
                                        foreach ($lopdeduction as $values) {
                                            foreach ($values as $vals) {
                                                if ($vals['calander_days'] == 'Working Days') {
                                                    $days = $vals['pm']['working_days'];
                                                    $present_days = $vals['pm']['days_presant'];
                                                    $leave_days = $vals['pm']['days_leave'];
                                                    $total_days = $vals['pm']['working_days'];
                                                } else {
                                                    $days = $vals['pm']['working_days'];
                                                    $present_days = $vals['pm']['days_presant'];
                                                    $leave_days = $vals['pm']['days_leave'];
                                                    $total_days = $vals['pm']['calander_days'];
                                                }

                                                if (($days) != null) {

                                                    // $loss_offp = $vals['pm']['loss_of_pay'];
                                                    $loss_offp = $days - ($present_days + $leave_days);
                                                    $tot = $vals['0']['total_salary_amount'];
                                                    // debug($tot);exit();
                                                    $perday = $tot / $days;


                                                    $lop_amount += (($perday * $loss_offp));
                                                }
                                            }
                                        }
                                        // debug($tot);
                                        // debug($perday);
                                        // debug($loss_offp);
                                        // debug($lop_amount);
                                        // exit;
                                    }

                                    $arr_withoutComponents[]['ectc'] = array(

                                        'salary_head_item_desc' => 'LOP',
                                        'head_type' => 'fixed'
                                    );
                                    $wp_comp_count = count($arr_withoutComponents);
                                    $arr_withoutComponents[($wp_comp_count - 1)][0] = array(
                                        'total_actual_salary'  => '0',
                                        'total_not_round_earned_salary' => ($lop_amount)
                                    );
                                    // debug($arr_data);exit;
                                    //edited by megha on 15_5_19
                                    $countss = count($arr_data);
                                    $countss2 = count($arr_withoutComponents);
                                    // if (count($arr_data) < count($arr_withoutComponents)) {
                                    //     $countss = count($arr_withoutComponents);
                                    // }
                                ?>
                                    <?php for ($i = 0; $i < $countss; $i++) {
                                        //edited by megha on 15_5_19
                                    ?>

                                        <tr> <?php
                                                $type = isset($arr_data[$i]['ectc']['head_type']) ? $arr_data[$i]['ectc']['head_type'] : '';


                                                if ($type == 'fixed' || $type == 'manually' || $type == 'limit') {
                                                    $sum += isset($arr_data[$i][0]['total_not_round_earned_salary']) ? abs(round($arr_data[$i][0]['total_not_round_earned_salary'], 2)) : 0;
                                                } else {
                                                    $sum += isset($arr_data[$i][0]['total_earned_salary']) ? abs(round($arr_data[$i][0]['total_earned_salary'])) : 0;
                                                }


                                                $structure += isset($arr_data[$i][0]['total_actual_salary']) ? abs(round($arr_data[$i][0]['total_actual_salary'])) : 0;

                                                $no = $i + 1;
                                                ?>
                                            <!-- edited by megha on 30_05_19 round off  -->
                                            <td style="width:auto; word-wrap: break-word;overflow: hidden;"><?php echo $no; ?></td>
                                            <td style="width:200px;word-wrap: break-word;overflow: hidden;  "><?php echo isset($arr_data[$i]['ectc']['salary_head_item_desc']) ? trim($arr_data[$i]['ectc']['salary_head_item_desc']) : ''; ?></td>

                                            <td style="width:60px;word-wrap: break-word; overflow: hidden; " class="amounts"><?php echo isset($arr_data[$i][0]['total_actual_salary']) ? sprintf("%.2f", abs(round($arr_data[$i][0]['total_actual_salary']))) : ''; ?></td>
                                            <?php
                                            $standard = isset($arr_data[$i][0]['total_actual_salary']) ? sprintf("%.2f", abs(round($arr_data[$i][0]['total_actual_salary']))) : 0;
                                            if ($type == 'fixed' || $type == 'manually' || $type == 'limit') {
                                                $actual = isset($arr_data[$i][0]['total_not_round_earned_salary']) ? sprintf("%.2f", abs(round($arr_data[$i][0]['total_not_round_earned_salary'], 2))) : 0;
                                            ?>
                                                <td style="width:60px;word-wrap: break-word; overflow: hidden; " class="amounts"><?php echo isset($arr_data[$i][0]['total_not_round_earned_salary']) ? sprintf("%.2f", abs(round($arr_data[$i][0]['total_not_round_earned_salary'], 2))) : ''; ?></td>
                                            <?php } else {
                                                $actual = isset($arr_data[$i][0]['total_earned_salary']) ? sprintf("%.2f", abs(round($arr_data[$i][0]['total_earned_salary']))) : '';
                                            ?>
                                                <td style="width:60px;word-wrap: break-word; overflow: hidden; " class="amounts"><?php echo isset($arr_data[$i][0]['total_earned_salary']) ? sprintf("%.2f", abs(round($arr_data[$i][0]['total_earned_salary']))) : 0; ?></td>
                                            <?php }
                                            $total_diff += abs($standard - $actual);
                                            ?>
                                            <td style="width:60px;word-wrap: break-word; overflow: hidden; "><?php echo sprintf("%.2f", abs($standard - $actual)); ?></td>
                                        </tr>

                                    <?php }
                                    // $total_diff = 0;
                                    ?>

                                    <!-- Total -->
                                    <tr>
                                        <td colspan="2" style=" font-weight: bold;">TOTAL</td>
                                        <th class="amounts" style=""><b><?php echo formatIndianNumber(sprintf("%.2f", abs(round($structure)))); ?></b></th>
                                        <th class="amounts" style=""><b><?php echo formatIndianNumber(sprintf("%.2f", abs(round($sum)))); ?></b></th>
                                        <td style=" font-weight: bold; text-align:left;"><?php echo formatIndianNumber(sprintf("%.2f", abs(round($total_diff)))); ?></td>
                                    </tr>
                                    <!-- Deductions -->
                                    <tr>
                                        <td colspan="5" style=" font-weight: bold; text-align: center;">Deductions</td>
                                    </tr>
                                    <?php
                                    $total_diff = 0;
                                    $stdlic = 0;
                                    $actuallic = 0;
                                    $total_lic = 0;
                                    $lic = '';
                                    // debug($arr_withoutComponents);exit;
                                    for ($i = 0; $i < $countss2; $i++) {
                                        //edited by megha on 15_5_19
                                        if (isset($arr_withoutComponents[$i][0]['total_earned_salary']) && ($arr_withoutComponents[$i][0]['total_earned_salary'] != '0')) {
                                            $type = isset($arr_data[$i]['ectc']['head_type']) ? $arr_data[$i]['ectc']['head_type'] : '';
                                            $ded_type = isset($arr_withoutComponents[$i]['ectc']['head_type']) ? $arr_withoutComponents[$i]['ectc']['head_type'] : '';

                                         $string =  isset($arr_withoutComponents[$i]['ectc']['salary_head_item_desc']) ? trim($arr_withoutComponents[$i]['ectc']['salary_head_item_desc']) : '';
                                         $substring = "LIC Policy";
                                         if (strpos($string, $substring) !== false) {
                                                    $lic = "LIC Policy";
                                                    $stdlic +=  isset($arr_withoutComponents[$i][0]['total_actual_salary']) ? sprintf("%.2f", abs(round($arr_withoutComponents[$i][0]['total_actual_salary'], 2))) : '';
                                                    if ($ded_type == 'fixed' || $ded_type == 'manually' || $ded_type == 'limit') {
                                                    $actuallic += isset($arr_withoutComponents[$i][0]['total_not_round_earned_salary']) ? sprintf("%.2f", abs(round($arr_withoutComponents[$i][0]['total_not_round_earned_salary'], 2))) : 0;
                                                    }else{
                                                    $actuallic += isset($arr_withoutComponents[$i][0]['total_earned_salary']) ? sprintf("%.2f", abs(round($arr_withoutComponents[$i][0]['total_earned_salary']))) : ''; 
                                                    }
                                                    $total_lic = sprintf("%.2f", abs(round($stdlic - $actuallic, 2)));
                                          } else {
                                         ?>
                                            <tr> <?php
                                                    if (isset($arr_withoutComponents[$i]['ectc']))
                                                        $st_dd += isset($arr_withoutComponents[$i][0]['total_actual_salary']) ? $arr_withoutComponents[$i][0]['total_actual_salary'] : 0;

                                                    if ($ded_type == 'fixed' || $ded_type == 'manually' || $ded_type == 'limit') {
                                                        $dd += isset($arr_withoutComponents[$i][0]['total_not_round_earned_salary']) ? abs(round($arr_withoutComponents[$i][0]['total_not_round_earned_salary'], 2)) : 0;
                                                    } else {
                                                        $dd += isset($arr_withoutComponents[$i][0]['total_earned_salary']) ? abs(round($arr_withoutComponents[$i][0]['total_earned_salary'])) : 0;
                                                    }

                                                    $no = $no + 1;
                                                    ?>

                                                <!-- Deductions -->
                                                <td style="width:60px;word-wrap: break-word;overflow: hidden;"><?php echo $no; ?></td>
                                                <td style="width:100px;word-wrap: break-word; overflow: hidden; "><?php echo isset($arr_withoutComponents[$i]['ectc']['salary_head_item_desc']) ? trim($arr_withoutComponents[$i]['ectc']['salary_head_item_desc']) : ''; ?></td>

                                                <!--  //edited by megha on 6_7_19 remove 0 values from deductions-->
                                                <td style="width:60px;word-wrap: break-word; overflow: hidden; " class="amounts"><?php echo isset($arr_withoutComponents[$i][0]['total_actual_salary']) ? sprintf("%.2f", abs(round($arr_withoutComponents[$i][0]['total_actual_salary'], 2))) : ''; ?></td>

                                                <?php
                                                $standard = isset($arr_withoutComponents[$i][0]['total_actual_salary']) ? sprintf("%.2f", abs(round($arr_withoutComponents[$i][0]['total_actual_salary'], 2))) : 0;

                                                if ($ded_type == 'fixed' || $ded_type == 'manually' || $ded_type == 'limit') {
                                                    $actual = isset($arr_withoutComponents[$i][0]['total_not_round_earned_salary']) ? sprintf("%.2f", abs(round($arr_withoutComponents[$i][0]['total_not_round_earned_salary'], 2))) : 0;
                                                ?>
                                                    <td style="width:60px;word-wrap: break-word; overflow: hidden; " class="amounts"><?php echo isset($arr_withoutComponents[$i][0]['total_not_round_earned_salary']) ? sprintf("%.2f", abs(round($arr_withoutComponents[$i][0]['total_not_round_earned_salary'], 2))) : ''; ?></td>
                                                <?php } else { ?>
                                                    <td style="width:60px;word-wrap: break-word; overflow: hidden; " class="amounts"><?php echo isset($arr_withoutComponents[$i][0]['total_earned_salary']) ? sprintf("%.2f", abs(round($arr_withoutComponents[$i][0]['total_earned_salary']))) : ''; ?></td>
                                                <?php
                                                    $actual = isset($arr_withoutComponents[$i][0]['total_earned_salary']) ? sprintf("%.2f", abs(round($arr_withoutComponents[$i][0]['total_earned_salary']))) : 0;
                                                }
                                                $total_diff += abs(round(($standard - $actual), 2));
                                                // debug(sprintf("%.2f", abs(round($standard - $actual, 2)))); exit;
                                                ?>
                                                <td><?php echo sprintf("%.2f", abs(round($standard - $actual, 2))); ?></td>
                                            </tr>
                                          <?php }
                                        }
                                    } if($lic != ''){?>
                                    <tr>
                                        <td style="width:60px;word-wrap: break-word;overflow: hidden;"><?php echo $no+1; ?></td>
                                        <td style="width:100px;word-wrap: break-word; overflow: hidden; "><?php echo $lic; ?></td>
                                        <td style="width:60px;word-wrap: break-word; overflow: hidden; " class="amounts"><?php echo $stdlic; ?></td>
                                        <td style="width:60px;word-wrap: break-word; overflow: hidden; " class="amounts"><?php echo $actuallic; ?></td>
                                        <td><?php echo $total_lic; ?></td>
                                    </tr>
                                    <?php 
                                    $st_dd += $stdlic;
                                    $dd += $actuallic;
                                    $total_diff += $total_lic;
                                    } ?>
                                    <tr>
                                        <th colspan="2"><b>TOTAL</b></th>
                                        <th class="amounts" style=""><b><?php echo formatIndianNumber(sprintf("%.2f", abs(round($st_dd)))); ?></b></th>
                                        <th class="amounts"><b><?php echo formatIndianNumber(sprintf("%.2f", abs(round($dd)))); ?></b></th>
                                        <th class="amounts" style="text-align:left;"><b><?php echo formatIndianNumber(sprintf("%.2f", abs(round($total_diff)))); ?></b></th>
                                    </tr>
                                    <!-- edited by megha on 16/11/19 settlement amount  -->
                                    <?php if ($value['summary']['0']['ed']['status'] == 2) { ?>
                                        <!-- <tr style="background: #cccccc ;"> -->

                                        <tr>
                                            <th style="text-align :center ; " colspan="3"></th>
                                            <th style="text-align :left ; "><b>Settlement Amount</b></th>
                                            <th class="amounts" style=""><b><?php echo formatIndianNumber(sprintf("%.2f", round($value['settle']))); ?></b></th>
                                        </tr>
                                    <?php }
                                    // debug($structure);
                                    // debug($st_dd); exit;
                                    $net_amount = (abs(round($structure)) - abs(round($st_dd)));
                                    $net_amount2 = round(abs($sum)) - round(abs($dd));
                                    ?>

                                    <tr>
                                        <th colspan="2" style="text-align :left ; "><b>NET PAY</b></th>
                                        <th class="amounts" style=""><b><?php echo formatIndianNumber(sprintf("%.2f", $net_amount)); ?></b></th>
                                        <th class="amounts"><b><?php echo formatIndianNumber(sprintf("%.2f", $net_amount2)); ?></b></th>
                                        <th style="text-align :left ; "><b></b></th>
                                    </tr>

                                    <!-- 
                                    <tr>
                                        <th style="text-align :left ; "><b>DIFFERENCE</b></th>
                                        <th class="amounts" style="border-right:.2px solid white;"><b><?php echo formatIndianNumber(sprintf("%.2f", abs(round($structure - $sum)))); ?></b></th>
                                        <th colspan="3" style="text-align :left ; "><b></b></th>
                                    </tr> -->

                                    <tr>
                                        <th colspan="2" style="text-align :left ; "><b>NO:OF EMPLOYEES</b></th>
                                        <th class="amounts" style="border-right:.2px solid white;"><b><?php echo $emp_no; ?></b></th>
                                        <th colspan="2" style="text-align :left ; "><b></b></th>
                                    </tr>
                                    <?php

                                    if ($net_amount >= 0) {
                                        $amountInWords = '';
                                    } else {
                                        $amountInWords = ' Negative ';
                                    }
                                    $amountInWords .= convertToWords(round($net_amount));
                                    ?>
                                    <!-- <tr style="background: #cccccc ;"> -->
                                    <!-- <tr>
                                    <td style="text-align :center;border-top:.2px solid white;border-bottom:.2px solid white; " colspan="6"><?php echo "Amount In Words:- Rupees " . $amountInWords . " Only"; ?></td>
                                </tr> -->
                                    <?php $arr_withoutComponents = $value['withoutcomponent']; ?>

                                    <?php if (count($arr_withoutComponents) > 0) { ?>



                                    <?php } ?>
                                <?php } else {
                                ?>
                                    <!-- <tr>
                                    <td colspan="6" style="font-size: 20px;border-bottom:.2px solid white;">No data available under the selected criteria.</td>
                                </tr> -->
                                <?php } ?>
                                <!-- <tr>
                                    <td colspan="6" style="text-align:center;">&nbsp;</td>
                                </tr> -->
                            </tbody>
                        </table>
                        <!-- </div> -->





                        <?php
                    }
                }
            } else {
                //debug($arr_salary_for_template);exit;
                foreach ($arr_salary_for_template as $values) {
                    foreach ($values as $value) {
                        if (count($value['summary']) !== 0 || count($value['withoutcomponent']) !== 0) {
                            $i += 1;
                        ?>

                            <page backtop="30mm" backbottom="10mm" backleft="2mm" backright="2mm" style="font-size: 12pt; ">

                                <page_header>
                                    <div style=" padding-left: 20px;">
                                        <table style="margin-left: 50px; border:1px solid white;">
                                            <tr>
                                                <td style="border:1px solid white;"><img style="width: auto; height: 80px;" src="https://<?php echo $_SERVER['HTTP_HOST'] . $this->webroot . $arr_comp_contact_info['CompanyContactInfo']['logo']; ?>" alt="Company Logo" /></td>
                                                <td style="padding-bottom: 0px;border:1px solid white;padding-top:20px; ">
                                                    <h3 style="text-align: center;"><?php echo isset($arr_comp_contact_info['CompanyContactInfo']['business_name']) ? $arr_comp_contact_info['CompanyContactInfo']['business_name'] : ''; ?></h3>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>

                                    <div style="text-align: center;  width:100%;">
                                        <span style="float: left; font-size: 12px;text-align:left;"><?php echo (date("d/m/Y") . date("h:i A")); ?></span>
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                        <b>ED Summary - <?php echo "$y1" . '/' . "$month"; ?> To <?php echo "$y1 " . '/' . "$month"; ?></b>
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                        <span style="float: right; font-size: 12px;text-align:right;">page [[page_cu]]/[[page_nb]]</span>
                                    </div>
                                </page_header>

                                <bookmark title="Sommaire" level="0"></bookmark>
                            </page>
                            <?php
                            //echo $this->element('reportadminheader', array(
                            //'title' => 'Salary Slip - '.date("M Y",strtotime($value['summary']['0']['ectc']['month_year']))));
                            ?>

                            <?php
                            $bank_name = '';
                            $branch_name = '';
                            $ifsc_code = '';
                            $acc_number = '';
                            $lopdeduction = $value['lopdeduction'];
                            $bank = isset($value['empdet']['0']['payroll_master']['bank_details']) ? $value['empdet']['0']['payroll_master']['bank_details'] : '';
                            ?>
                            <!-- <div style="border: 1.5px solid #989898;display: inline-block; vertical-align: top;"> -->
                            <table class="table" align="center" style="margin-top: 10px;;  ">
                                <tbody>

                                    <?php $emp_no = $emp_count;
                                    //debug($value['lopdeduction']);exit;
                                    ?>

                                    <tr>
                                        <th style=" font-weight: bold; ">Sl.No</th>
                                        <th style=" font-weight: bold; ">Items</th>
                                        <th style="width:30px;font-weight: bold; text-align: right;;">Standard Amount</th>
                                        <th style="width:30px;font-weight: bold;">Actual Amount</th>
                                        <th style="font-weight: bold; ">Difference</th>
                                        <!-- <th style=" font-weight: bold;">Standard Amount</th> -->
                                        <!-- <th style=" font-weight: bold;">Actual Amount</th> -->
                                    </tr>
                                    <tr>
                                        <td colspan="5" style=" font-weight: bold; text-align: center;">Earnings</td>
                                    </tr>
                                    <?php $arr_data = $value['summary'];
                                    $arr_withoutComponents = $value['withoutcomponent'];
                                    $variables = isset($value['variables']) ? $value['variables'] : 0;
                                    // debug($variables); exit;
                                    if ($variables > 0) {
                                        $j = count($arr_data);
                                        $arr_data[$j]['ectc']['salary_amount'] = $variables;
                                        $arr_data[$j]['ectc']['structure_det_value'] = 0;
                                        $arr_data[$j]['ectc']['salary_head_item_desc'] = 'Variable Pay';
                                    }
                                    ?>
                                    <?php
                                    if (count($arr_data) >= 0) {
                                        $sum = 0;
                                        $tot = 0;
                                        $dd = 0;
                                        $st_dd = 0;
                                        $net = 0;
                                        $structure = 0;
                                        $total_diff = 0;

                                        $lop_amount = 0;
                                        //    debug(($lopdeduction));
                                        if (count($lopdeduction) > 0) {
                                            foreach ($lopdeduction as $values) {
                                                foreach ($values as $vals) {
                                                    if ($vals['calander_days'] == 'Working Days') {
                                                        $days = $vals['pm']['working_days'];
                                                        $present_days = $vals['pm']['days_presant'];
                                                        $leave_days = $vals['pm']['days_leave'];
                                                        $total_days = $vals['pm']['working_days'];
                                                    } else {
                                                        $days = $vals['pm']['working_days'];
                                                        $present_days = $vals['pm']['days_presant'];
                                                        $leave_days = $vals['pm']['days_leave'];
                                                        $total_days = $vals['pm']['calander_days'];
                                                    }

                                                    if (($days) != null) {

                                                        // $loss_offp = $vals['pm']['loss_of_pay'];
                                                        $loss_offp = $days - ($present_days + $leave_days);
                                                        $tot = $vals['0']['total_salary_amount'];
                                                        // debug($tot);exit();
                                                        $perday = $tot / $days;


                                                        $lop_amount += (($perday * $loss_offp));
                                                    }
                                                }
                                            }
                                            // debug($tot);
                                            // debug($perday);
                                            // debug($loss_offp);
                                            // debug($lop_amount);
                                            // exit;
                                        }

                                        $arr_withoutComponents[]['ectc'] = array(

                                            'salary_head_item_desc' => 'LOP',
                                            'head_type' => 'fixed'
                                        );
                                        $wp_comp_count = count($arr_withoutComponents);
                                        $arr_withoutComponents[($wp_comp_count - 1)][0] = array(
                                            'total_actual_salary'  => '0',
                                            'total_not_round_earned_salary' => ($lop_amount)
                                        );
                                        // debug($arr_data);exit;
                                        //edited by megha on 15_5_19
                                        $countss = count($arr_data);
                                        $countss2 = count($arr_withoutComponents);
                                        // if (count($arr_data) < count($arr_withoutComponents)) {
                                        //     $countss = count($arr_withoutComponents);
                                        // }
                                    ?>
                                        <?php for ($i = 0; $i < $countss; $i++) {
                                            //edited by megha on 15_5_19
                                        ?>

                                            <tr> <?php
                                                    $type = isset($arr_data[$i]['ectc']['head_type']) ? $arr_data[$i]['ectc']['head_type'] : '';


                                                    if ($type == 'fixed' || $type == 'manually' || $type == 'limit') {
                                                        $sum += isset($arr_data[$i][0]['total_not_round_earned_salary']) ? abs(round($arr_data[$i][0]['total_not_round_earned_salary'], 2)) : 0;
                                                    } else {
                                                        $sum += isset($arr_data[$i][0]['total_earned_salary']) ? abs(round($arr_data[$i][0]['total_earned_salary'])) : 0;
                                                    }


                                                    $structure += isset($arr_data[$i][0]['total_actual_salary']) ? abs(round($arr_data[$i][0]['total_actual_salary'])) : 0;

                                                    $no = $i + 1;
                                                    ?>
                                                <!-- edited by megha on 30_05_19 round off  -->
                                                <td style="width:auto; word-wrap: break-word;overflow: hidden;"><?php echo $no; ?></td>
                                                <td style="width:200px;word-wrap: break-word;overflow: hidden;  "><?php echo isset($arr_data[$i]['ectc']['salary_head_item_desc']) ? trim($arr_data[$i]['ectc']['salary_head_item_desc']) : ''; ?></td>

                                                <td style="width:60px;word-wrap: break-word; overflow: hidden; " class="amounts"><?php echo isset($arr_data[$i][0]['total_actual_salary']) ? sprintf("%.2f", abs(round($arr_data[$i][0]['total_actual_salary']))) : ''; ?></td>
                                                <?php
                                                $standard = isset($arr_data[$i][0]['total_actual_salary']) ? sprintf("%.2f", abs(round($arr_data[$i][0]['total_actual_salary']))) : 0;
                                                if ($type == 'fixed' || $type == 'manually' || $type == 'limit') {
                                                    $actual = isset($arr_data[$i][0]['total_not_round_earned_salary']) ? sprintf("%.2f", abs(round($arr_data[$i][0]['total_not_round_earned_salary'], 2))) : 0;
                                                ?>
                                                    <td style="width:60px;word-wrap: break-word; overflow: hidden; " class="amounts"><?php echo isset($arr_data[$i][0]['total_not_round_earned_salary']) ? sprintf("%.2f", abs(round($arr_data[$i][0]['total_not_round_earned_salary'], 2))) : ''; ?></td>
                                                <?php } else {
                                                    $actual = isset($arr_data[$i][0]['total_earned_salary']) ? sprintf("%.2f", abs(round($arr_data[$i][0]['total_earned_salary']))) : '';
                                                ?>
                                                    <td style="width:60px;word-wrap: break-word; overflow: hidden; " class="amounts"><?php echo isset($arr_data[$i][0]['total_earned_salary']) ? sprintf("%.2f", abs(round($arr_data[$i][0]['total_earned_salary']))) : 0; ?></td>
                                                <?php }
                                                $total_diff += abs($standard - $actual);
                                                ?>
                                                <td style="width:60px;word-wrap: break-word; overflow: hidden; "><?php echo sprintf("%.2f", abs($standard - $actual)); ?></td>
                                            </tr>

                                        <?php }
                                        // $total_diff = 0;
                                        ?>

                                        <!-- Total -->
                                        <tr>
                                            <td colspan="2" style=" font-weight: bold;">TOTAL</td>
                                            <th class="amounts" style=""><b><?php echo formatIndianNumber(sprintf("%.2f", abs(round($structure)))); ?></b></th>
                                            <th class="amounts" style=""><b><?php echo formatIndianNumber(sprintf("%.2f", abs(round($sum)))); ?></b></th>
                                            <td style=" font-weight: bold; text-align:left;"><?php echo formatIndianNumber(sprintf("%.2f", abs(round($total_diff)))); ?></td>
                                        </tr>
                                        <!-- Deductions -->
                                        <tr>
                                            <td colspan="5" style=" font-weight: bold; text-align: center;">Deductions</td>
                                        </tr>
                                        <?php
                                        $total_diff = 0;
                                        $stdlic = 0;
                                    $actuallic = 0;
                                    $total_lic = 0;
                                    $lic = '';
                                        // debug($arr_withoutComponents);exit;
                                        for ($i = 0; $i < $countss2; $i++) {
                                            //edited by megha on 15_5_19
                                            if (isset($arr_withoutComponents[$i][0]['total_earned_salary']) && ($arr_withoutComponents[$i][0]['total_earned_salary'] != '0')) {
                                        $string =  isset($arr_withoutComponents[$i]['ectc']['salary_head_item_desc']) ? trim($arr_withoutComponents[$i]['ectc']['salary_head_item_desc']) : '';
                                         $substring = "LIC Policy";
                                         if (strpos($string, $substring) !== false) {
                                                    $lic = "LIC Policy";
                                                    $stdlic +=  isset($arr_withoutComponents[$i][0]['total_actual_salary']) ? sprintf("%.2f", abs(round($arr_withoutComponents[$i][0]['total_actual_salary'], 2))) : '';
                                                    //edited by sinsiya on 19-11-2024
                                                    $ded_type = isset($arr_withoutComponents[$i]['ectc']['head_type']) ? $arr_withoutComponents[$i]['ectc']['head_type'] : '';
                                                    if ($ded_type == 'fixed' || $ded_type == 'manually' || $ded_type == 'limit') {
                                                    $actuallic += isset($arr_withoutComponents[$i][0]['total_not_round_earned_salary']) ? sprintf("%.2f", abs(round($arr_withoutComponents[$i][0]['total_not_round_earned_salary'], 2))) : 0;
                                                    }else{
                                                    $actuallic += isset($arr_withoutComponents[$i][0]['total_earned_salary']) ? sprintf("%.2f", abs(round($arr_withoutComponents[$i][0]['total_earned_salary']))) : ''; 
                                                    }
                                                    $total_lic = sprintf("%.2f", abs(round($stdlic - $actuallic, 2)));
                                          } else {?>

                                                <tr> <?php
                                                        $type = isset($arr_data[$i]['ectc']['head_type']) ? $arr_data[$i]['ectc']['head_type'] : '';
                                                        $ded_type = isset($arr_withoutComponents[$i]['ectc']['head_type']) ? $arr_withoutComponents[$i]['ectc']['head_type'] : '';


                                                        if (isset($arr_withoutComponents[$i]['ectc']))
                                                            $st_dd += isset($arr_withoutComponents[$i][0]['total_actual_salary']) ? $arr_withoutComponents[$i][0]['total_actual_salary'] : 0;

                                                        if ($ded_type == 'fixed' || $ded_type == 'manually' || $ded_type == 'limit') {
                                                            $dd += isset($arr_withoutComponents[$i][0]['total_not_round_earned_salary']) ? abs(round($arr_withoutComponents[$i][0]['total_not_round_earned_salary'], 2)) : 0;
                                                        } else {
                                                            $dd += isset($arr_withoutComponents[$i][0]['total_earned_salary']) ? abs(round($arr_withoutComponents[$i][0]['total_earned_salary'])) : 0;
                                                        }

                                                        $no = $no + 1;
                                                        ?>

                                                    <!-- Deductions -->
                                                    <td style="width:60px;word-wrap: break-word;overflow: hidden;"><?php echo $no; ?></td>
                                                    <td style="width:100px;word-wrap: break-word; overflow: hidden; "><?php echo isset($arr_withoutComponents[$i]['ectc']['salary_head_item_desc']) ? trim($arr_withoutComponents[$i]['ectc']['salary_head_item_desc']) : ''; ?></td>

                                                    <!--  //edited by megha on 6_7_19 remove 0 values from deductions-->
                                                    <td style="width:60px;word-wrap: break-word; overflow: hidden; " class="amounts"><?php echo isset($arr_withoutComponents[$i][0]['total_actual_salary']) ? sprintf("%.2f", abs(round($arr_withoutComponents[$i][0]['total_actual_salary'], 2))) : ''; ?></td>

                                                    <?php
                                                    $standard = isset($arr_withoutComponents[$i][0]['total_actual_salary']) ? sprintf("%.2f", abs(round($arr_withoutComponents[$i][0]['total_actual_salary'], 2))) : 0;

                                                    if ($ded_type == 'fixed' || $ded_type == 'manually' || $ded_type == 'limit') {
                                                        $actual = isset($arr_withoutComponents[$i][0]['total_not_round_earned_salary']) ? sprintf("%.2f", abs(round($arr_withoutComponents[$i][0]['total_not_round_earned_salary'], 2))) : 0;
                                                    ?>
                                                        <td style="width:60px;word-wrap: break-word; overflow: hidden; " class="amounts"><?php echo isset($arr_withoutComponents[$i][0]['total_not_round_earned_salary']) ? sprintf("%.2f", abs(round($arr_withoutComponents[$i][0]['total_not_round_earned_salary'], 2))) : ''; ?></td>
                                                    <?php } else { ?>
                                                        <td style="width:60px;word-wrap: break-word; overflow: hidden; " class="amounts"><?php echo isset($arr_withoutComponents[$i][0]['total_earned_salary']) ? sprintf("%.2f", abs(round($arr_withoutComponents[$i][0]['total_earned_salary']))) : ''; ?></td>
                                                    <?php
                                                        $actual = isset($arr_withoutComponents[$i][0]['total_earned_salary']) ? sprintf("%.2f", abs(round($arr_withoutComponents[$i][0]['total_earned_salary']))) : 0;
                                                    }
                                                    $total_diff += abs(round(($standard - $actual), 2));
                                                    // debug(sprintf("%.2f", abs(round($standard - $actual, 2)))); exit;
                                                    ?>
                                                    <td><?php echo sprintf("%.2f", abs(round($standard - $actual, 2))); ?></td>

                                                </tr>

                                        <?php
                                            }}
                                        } if($lic != ''){?>
                                    <tr>
                                        <td style="width:60px;word-wrap: break-word;overflow: hidden;"><?php echo $no+1; ?></td>
                                        <td style="width:100px;word-wrap: break-word; overflow: hidden; "><?php echo $lic; ?></td>
                                        <td style="width:60px;word-wrap: break-word; overflow: hidden; " class="amounts"><?php echo $stdlic; ?></td>
                                        <td style="width:60px;word-wrap: break-word; overflow: hidden; " class="amounts"><?php echo $actuallic; ?></td>
                                        <td><?php echo $total_lic; ?></td>
                                    </tr>
                                    <?php 
                                    $st_dd += $stdlic;
                                    $dd += $actuallic;
                                    $total_diff += $total_lic;
                                    } ?>
                                        <tr>
                                            <th colspan="2"><b>TOTAL</b></th>
                                            <th class="amounts" style=""><b><?php echo formatIndianNumber(sprintf("%.2f", abs(round($st_dd)))); ?></b></th>
                                            <th class="amounts"><b><?php echo formatIndianNumber(sprintf("%.2f", abs(round($dd)))); ?></b></th>
                                            <th class="amounts" style="text-align:left;"><b><?php echo formatIndianNumber(sprintf("%.2f", abs(round($total_diff)))); ?></b></th>
                                        </tr>
                                        <?php if ($value['summary']['0']['ed']['status'] == 2) { ?>

                                            <tr>
                                                <th style="text-align :center ; " colspan="3"></th>
                                                <th style="text-align :left ; "><b>Settlement Amount</b></th>
                                                <th class="amounts" style=""><b><?php echo formatIndianNumber(sprintf("%.2f", round($value['settle']))); ?></b></th>
                                            </tr>
                                        <?php }
                                        // debug($structure);
                                        // debug($st_dd); exit;
                                        $net_amount = (abs(round($structure)) - abs(round($st_dd)));
                                        $net_amount2 = round(abs($sum)) - round(abs($dd));
                                        ?>

                                        <tr>
                                            <th colspan="2" style="text-align :left ; "><b>NET PAY</b></th>
                                            <th class="amounts" style=""><b><?php echo formatIndianNumber(sprintf("%.2f", $net_amount)); ?></b></th>
                                            <th class="amounts"><b><?php echo formatIndianNumber(sprintf("%.2f", $net_amount2)); ?></b></th>
                                            <th style="text-align :left ; "><b></b></th>
                                        </tr>

                                        <!-- 
                                        <tr>
                                            <th style="text-align :left ; "><b>DIFFERENCE</b></th>
                                            <th class="amounts" style="border-right:.2px solid white;"><b><?php echo formatIndianNumber(sprintf("%.2f", abs(round($structure - $sum)))); ?></b></th>
                                            <th colspan="3" style="text-align :left ; "><b></b></th>
                                        </tr> -->

                                        <tr>
                                            <th colspan="2" style="text-align :left ; "><b>NO:OF EMPLOYEES</b></th>
                                            <th class="amounts" style="border-right:.2px solid white;"><b><?php echo $emp_no; ?></b></th>
                                            <th colspan="2" style="text-align :left ; "><b></b></th>
                                        </tr>
                                        <?php

                                        if ($net_amount >= 0) {
                                            $amountInWords = '';
                                        } else {
                                            $amountInWords = ' Negative ';
                                        }
                                        $amountInWords .= convertToWords(round($net_amount));
                                        ?>
                                        <!-- <tr style="background: #cccccc ;"> -->
                                        <!-- <tr>
                                        <td style="text-align :center;border-top:.2px solid white;border-bottom:.2px solid white; " colspan="6"><?php echo "Amount In Words:- Rupees " . $amountInWords . " Only"; ?></td>
                                    </tr> -->
                                        <?php $arr_withoutComponents = $value['withoutcomponent']; ?>

                                        <?php if (count($arr_withoutComponents) > 0) { ?>



                                        <?php } ?>
                                    <?php } else {
                                    ?>
                                        <!-- <tr>
                                        <td colspan="6" style="font-size: 20px;border-bottom:.2px solid white;">No data available under the selected criteria.</td>
                                    </tr> -->
                                    <?php } ?>
                                    <!-- <tr>
                                        <td colspan="6" style="text-align:center;">&nbsp;</td>
                                    </tr> -->
                                </tbody>
                            </table>
                            <!-- </div> -->





            <?php
                        }
                    }
                }
            }
        } else { ?>
            <page backtop="30mm" backbottom="10mm" backleft="2mm" backright="2mm" style="font-size: 12pt; ">

                <page_header>
                    <div style=" padding-left: 20px;">
                        <table style="margin-left: 50px; border:1px solid white;">
                            <tr>
                                <td style="border:1px solid white;"><img style="width: auto; height: 80px;" src="https://<?php echo $_SERVER['HTTP_HOST'] . $this->webroot . $arr_comp_contact_info['CompanyContactInfo']['logo']; ?>" alt="Company Logo" /></td>
                                <td style="padding-bottom: 0px;border:1px solid white;padding-top:20px; ">
                                    <h3 style="text-align: center;"><?php echo isset($arr_comp_contact_info['CompanyContactInfo']['business_name']) ? $arr_comp_contact_info['CompanyContactInfo']['business_name'] : ''; ?></h3>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div style="text-align: center;  width:100%;">
                        <span style="float: left; font-size: 12px;text-align:left;"><?php echo (date("d/m/Y") . date("h:i A")); ?></span>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <b>ED Summary - <?php echo "$y1" . '/' . "$month"; ?> To <?php echo "$y1 " . '/' . "$month"; ?></b>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <span style="float: right; font-size: 12px;text-align:right;">page [[page_cu]]/[[page_nb]]</span>
                    </div>
                </page_header>

                <bookmark title="Sommaire" level="0"></bookmark>
            </page>
            <div style="padding-left: 100px; font-size: 20px;margin-top:20px;">No data available under the selected criteria.</div>
            <!-- <div style="text-align:center; margin-top:50px;font-size: 16px;padding-left: 100px;">This is a System generated pay slip and does not require signature.</div> -->
        <?php }


        ?>
    <?php }
// exit;
    ?>