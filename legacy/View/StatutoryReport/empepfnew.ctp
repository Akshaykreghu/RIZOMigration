<?php if ($mode == '') { ?>
    <div class="modal-body" style="overflow-y:initial; padding-left:3%; padding-right:3%; padding-bottom:3%;">

        <h2 style="font-weight: bold;text-align: center;"><?php echo "  PF Summary for the month of  " . date('F-Y',strtotime($date)); ?> </h2>
        <!-- <h2 style="font-weight: bold;text-align: center;font-size: 19px;"><?php echo  "(Report Run by " . $user_id . " at " . $date_time . ")" ?>  </h2> -->

        <div class="row">
            <div class="col-md-12" style="overflow-x: auto; overflow-y: auto;"> <?php
                                    if (count($arr_salary_for_template) == 0) {

                                        echo "<h3>No data available under the selected criteria.</h3>";
                                    }


                                    $i = 0;

                                    $column4 = 0;
                                    $column5 = 0;
                                    $column6 = 0;
                                    $column7 = 0;
                                    $edli = 0;
                                    $pensiontotal = 0;
                                    foreach ($arr_salary_for_template as $value) {
                                        if (count($value) !== 0) {
                                            $i += 1;
                                    ?>
                        <div class="row">
                            <div class="col-md-12">
                                <div style="margin-top: 50px;">
                                    <div style="text-align:left; width:100%; ">
                                        <legend>
                                            <?php
                                            if ($str_criteria_item == 'Units') {
                                                echo $value['0']['employee_info']['branch'];
                                            } elseif ($str_criteria_item == 'Departments') {
                                                echo $value['0']['employee_info']['department']; // Replace 'itemname' with the actual key of the item name in your data array
                                            } elseif ($str_criteria_item == 'Designation') {
                                                echo $value['0']['employee_info']['designation'];
                                            } elseif ($str_criteria_item == 'Gender') {
                                                echo $value['0']['emp_details']['classification'];
                                            } elseif ($str_criteria_item == 'EmployeeDetails') {
                                                echo $value['0']['employee_info']['EmpName'];
                                            }
                                            ?>
                                        </legend>

                                        <div style="position: relative;">
                                            <div style="position: absolute; left: 42px; top: 0;">
                                                <b style="font-size: 14px; margin-top: 0px;"><?php echo $arr_comp_contact_info['CompanyContactInfo']['business_name']; ?></b>
                                            </div>
                                            <div style="position: absolute; right: 32px; top: 0;">
                                                <b style="font-size: 14px; margin-top: 0px;">Employer PF No.: <?php echo $emplr_pf_no; ?></b>
                                            </div>
                                        </div>



                                    </div>
                                    <br>
                                    <div style="text-align: center; border: none; width:100%; margin-top: 0px; text-align: center; margin-right: auto; height: 25px;">
                                        <b style="padding: 0px 20px; margin: 0; font-size: 14px; ">
                                            PF Summary for the month of <?php $month_year = DateTime::createFromFormat('Y-m', $month);
                                                                                $formattedDate = $month_year->format('M/Y');
                                                                                echo date('F-Y',strtotime($date)); ?>
                                        </b>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <div style="overflow-x: auto; overflow-y: auto;">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th rowspan="2" style="border-top: 1px solid black;border-bottom: #FFFFFF;"><b>Sl.<br> No.</b></th>
                                    <th rowspan="2" style="border-top: 1px solid black;border-bottom: #fff;"><b>UAN</b></th>
                                    <th rowspan="2" style="border-top: 1px solid black;border-bottom: #fff;"><b>Name of Member</b></th>
                                    <th colspan="2" style="border-top: 1px solid black;border-bottom: #fff;;padding-left: 320px;">Employee Contribution</th>
                                    <th colspan="2" style="border-top: 1px solid black;border-bottom: #fff;padding-left: 230px;">Employer Contribution</th>
                                </tr>
                                <tr>
                                    <th style="border-bottom: #fff;padding-left: 340px;">&nbsp;&nbsp;&nbsp;&nbsp;PF <br>Earnings</th>
                                    <th style="border-bottom: #fff;padding-left: 0px;">Contribution <br>&nbsp;&nbsp;&nbsp;&nbsp;EPF</th>
                                    <th style="border-bottom: #fff;padding-left: 150px;">&nbsp;&nbsp;&nbsp;&nbsp;EPF <br>Difference</th>
                                    <th style="border-bottom: #fff;padding-left: 140px;">Pension<br>&nbsp;&nbsp;8.33%</th>
                                </tr>
                                <tr>
                                    <th style="border-bottom: 1px solid black;"><b>(1)</b></th>
                                    <th style="border-bottom: 1px solid black;"><b>(2)</b></th>
                                    <th style="border-bottom: 1px solid black;"><b>(3)</b></th>
                                    <th style="border-bottom: 1px solid black;;padding-left: 340px;"><b>(4)</b></th>
                                    <th style="border-bottom: 1px solid black;"><b>(5)</b></th>
                                    <th style="border-bottom: 1px solid black;padding-left: 150px;"><b>&nbsp;&nbsp;&nbsp;&nbsp;(6)</b></th>
                                    <th style="border-bottom: 1px solid black;padding-left: 150px;"><b>&nbsp;&nbsp;&nbsp;&nbsp;(7)</b></th>
                                </tr>

                            </thead>
                            <tbody> <?php $arr_data = $value; ?>
                                <?php if (count($arr_data) >= 0) {
                                                $i = 0;
                                                $sum = 0;
                                                $gross = 0;
                                                $pf_salary = 0;
                                                $split1 = 0;
                                                $split2 = 0;
                                                $split3 = 0;
                                                $split4 = 0;
                                                $column4 = 0;
                                                $column5 = 0;
                                                $column6 = 0;
                                                $column7 = 0;
                                                //Edited by Askhay on 24-5-2024
                                                $edli = 0;
                                                $pensiontotal = 0;
                                                //End
                                ?>
                                    <?php foreach ($arr_data as $val) {
                                    ?>
                                        <?php if ($val['0']['EPF'] == 0)
                                                        continue;
                                                    $excluded1 = isset($val['0']['EPF_earning']) ? $val['0']['EPF_earning'] : '';
                                                    $normalized = preg_replace('/\s*([\*\/])\s*/', '$1', $excluded1);
                                                    $expressionWithoutPortion = str_replace(['*.12', '*12/100'], '', $normalized);
                                                    eval('$epf_earnings = ' . $expressionWithoutPortion . ';');
                                                    $sal1 = floor($epf_earnings);
                                        ?>
                                        <tr>
                                            <?php $i = $i + 1; ?>
                                            <td><?php echo $i; ?></td>
                                            <td><?php echo $val['emp_details']['pf']; ?></td>
                                            <td><?php echo $val['employee_info']['EmpName']; ?></td>
                                            <?php
                                                    $sal1 = isset($val['0']['EPF_earning']) ? $val['0']['EPF_earning'] : '';
                                                    if ($sal1 != '') {
                                                        $normalized = preg_replace('/\s*([\*\/])\s*/', '$1', $sal1);
                                                    $expressionWithoutPortion = str_replace(['*.12', '*12/100'], '', $normalized);
                                                        eval('$epf_earnings = ' . $expressionWithoutPortion . ';');
                                                        $sal1 = floor($epf_earnings);
                                                    }
                                                    //Edited by Akshay on 20-5-2024
                                                    if ($sal1 > 15000) {
                                                        $sal1 = 15000;
                                                    }
                                                    //End

                                                    $sal =  ($val['0']['EPF'] * 100 / 12);
                                                    $column4 = $column4 + $sal1;
                                            ?>
                                            <td style="padding-left: 340px;"> <?php echo ($sal1 == 0) ? '' : number_format($sal1, 2); ?></td>
                                            <?php

                                                    if ($sal1 > 15000) {
                                                        $edli = $edli + 15000;
                                                        if ($val['emp_details']['eps'] != 'N') {
                                                            $pension = round((15000) * .0833);
                                                            $column7 = $column7 + $pension;
                                                            $pensiontotal = $pensiontotal + 15000;
                                                        } else {
                                                            $pension = 0;
                                                            $pensiontotal = $pensiontotal + 0;
                                                        }
                                                    } else {
                                                        $edli = $edli + $sal1;
                                                        if ($val['emp_details']['eps'] != 'N') {
                                                            $pension = round(($sal1) * .0833);
                                                            $column7 = $column7 + $pension;
                                                            $pensiontotal = $pensiontotal + $sal1;
                                                        } else {
                                                            $pension = 0;
                                                            $pensiontotal = $pensiontotal + 0;
                                                        }
                                                    }
                                            ?>
                                            <?php $emp_contr = ($val['0']['EPF']);
                                                    $column5 = $column5 + $emp_contr; ?>
                                            <td style="text-align:right;"> <?php echo ($emp_contr == 0) ? '' : number_format(round($emp_contr), 2); ?></td>
                                            <?php
                                                    $difference = ($emp_contr - $pension);
                                                    $column6 = $difference + $column6;
                                            ?>
                                            <td style="text-align:right;"> <?php echo ($difference == 0) ? '' : number_format(round($difference), 2); ?></td>
                                            <?php if ($pension > 0) { ?>
                                                <td style="text-align:right;"> <?php echo number_format(round($pension), 2); ?></td>
                                            <?php } else { ?>
                                                <td> <?php echo ''; ?></td>
                                            <?php } ?>
                                        </tr>


                                    <?php } ?>
                                    <tr>
                                        <td colspan="7" class="heading_bottom" style=" border-bottom: .5px solid black;padding: .1px; line-height: 1;"></td>
                                    </tr>
                                    <tr>
                                        <th colspan="3" style="text-align: center; font-size: 13px; border-bottom: .5px solid black; padding-top: 0px;"><b>T&nbsp;O&nbsp;T&nbsp;A&nbsp;L</b></th>
                                        <?php
                                                if ($column4 != 0) { ?>
                                            <th style=";font-size:13px; border-bottom: .5px solid black; padding-top: 0px; text-align:left;padding-left: 340px;"><b><?php echo number_format(round($column4), 2); ?></b></th>
                                        <?php } else { ?>
                                            <th style=" border-bottom: .5px solid black;"><b><?php echo ''; ?></b></th>
                                        <?php }
                                        ?>

                                        <th style="padding-top: -5px;font-size:13px; border-bottom: .5px solid black; padding-top: 0px;  text-align:right; padding-right: 10px;">

                                            <b><?php echo ($column5 == 0) ? '' : number_format(round($column5), 2); ?></b>
                                        </th>
                                        <th style="padding-top: -5px;font-size:13px; border-bottom: .5px solid black; padding-top: 0px; text-align:right; padding-right: 10px; ">
                                            <b><?php echo ($column6 == 0) ? '' : number_format(round($column6), 2); ?></b>
                                        </th>
                                        <th style="padding-top: -5px;font-size:13px; border-bottom: .5px solid black; padding-top: 0px; text-align:right;padding-right:10px; ">
                                            <b><?php echo ($column7 == 0) ? '' : number_format(round($column7), 2); ?></b>
                                        </th>

                                    </tr>

                                    <tr>
                                        <th><b></b></th>
                                        <th style="text-align: center; font-size: 12px; text-align:left;"><b></b></th>
                                        <th style="text-align: center; text-align:right;"><b>Account No:01&nbsp;</b></th>
                                        <th style="text-align: center; font-size: 12px; text-align:left; "><b>(Column Nos.5+6)</b></th>
                                        <th></th>
                                        <th style=" text-align: right; font-size: 12px;"><b>=</b></th>
                                        <?php
                                                if (($column5 + $column6) != 0) { ?>
                                            <th style="text-align: center; font-size: 12px; text-align:right; "><b><?php echo number_format(round($column5 + $column6), 2); ?></b></th>
                                        <?php } else { ?>
                                            <th style="width:14%; text-align: center; font-size: 12px; text-align:right; padding-right:5px;"><b><?php echo ''; ?></b></th>
                                        <?php }
                                        ?>

                                    </tr>
                                    <tr style="height: 10px;">
                                        <th style=" text-align: center; font-size: 12px; text-align:left; padding-top: 0px;"><b></b></th>
                                        <th style=" text-align: center; font-size: 12px; text-align:left; padding-top: 0px;"><b></b></th>
                                        <th style=" text-align:right; padding-top: 0px;"><b>Account No:02&nbsp;</b></th>
                                        <th style=" text-align: center; font-size: 12px;padding-left: 2px; text-align:left;padding-left:6px;padding-top:0px"><b>(0.50000% of Column No.4)</b></th>
                                        <th></th>

                                        <th style=" text-align: right; font-size: 12px;"><b>=</b></th>
                                        <?php if (($column4 * .005) != 0) { ?>
                                            <th style=" text-align: center; font-size: 12px; text-align:right; padding-right:0px; padding-top: 0px;padding-right:5px;"><b><?php echo number_format(round($column4 * .005), 2); ?></b></th>
                                        <?php } else { ?>
                                            <th style="text-align: center; font-size: 12px; text-align:right; padding-right:0px; padding-top: 0px;padding-right:5px;"><b><?php echo ''; ?></b></th>
                                        <?php } ?>

                                    </tr>
                                    <tr style="height: 10px;">
                                        <th style=" text-align: center; font-size: 12px; text-align:left; padding-top: 0px;"><b></b></th>
                                        <th style=" text-align: center; font-size: 12px; text-align:left;  padding-top: 0px; "><b></b></th>
                                        <th style="width:18%;text-align: center; text-align:right; padding-top: 0px;"><b>Account No:10 </b></th>
                                        <th style="width:33%; text-align: center; font-size: 12px;padding-left: 2px; text-align:left; padding-top: 0px;padding-left:7px;"><b>(Column No. 7)</b></th>
                                        <th></th>

                                        <th style=" text-align: right; font-size: 12px;"><b>=</b></th>
                                        <?php
                                                if ($column7 != 0) { ?>
                                            <th style="width:14%; text-align: center; font-size: 12px; text-align:right; padding-right:0px; padding-top: 0px;padding-right:5px;"><b><?php echo number_format(round($column7), 2); ?></b></th>
                                        <?php } else { ?>
                                            <th style="width:14%; text-align: center; font-size: 12px; text-align:right; padding-right:0px; padding-top: 0px;padding-right:5px;"><b><?php echo ''; ?></b></th>
                                        <?php }
                                        ?>
                                    </tr>
                                    <tr style="height: 10px;">
                                        <th style="width:19%;  font-size: 12px; text-align:left; padding-left:15px; padding-top: 0px; padding-bottom: -5px;"><b>E&nbsp; D&nbsp; L&nbsp; I Wages :</b></th>
                                        <th style="  font-size: 12px; text-align:right;  padding-top: 0px; "><b><?php echo number_format(round($edli), 2); ?></b></th>
                                        <th style="text-align: center; text-align:right; padding-top: 0px;"><b>Account No: 21</b></th>
                                        <th style="text-align: center; font-size: 12px;padding-left: 2px; text-align:left; padding-top: 0px;padding-left:7px;"><b>E D L I WAGES * 0.50000%</b></th>
                                        <th></th>
                                        <th style=" text-align: right; font-size: 12px;"><b>=</b></th>
                                        <?php
                                                if (($edli * 0.5 / 100) != 0) { ?>
                                            <th style="text-align: center; font-size: 12px; text-align:right; padding-right:0px; padding-top: 0px;padding-right:5px;"><b><?php echo number_format(round($edli * 0.5 / 100), 2); ?></b></th>
                                        <?php } else {
                                        ?>
                                            <th style="width:14%; text-align: center; font-size: 12px; text-align:right; padding-right:0px; padding-top: 0px;padding-right:5px;"><b><?php echo ''; ?></b></th>
                                        <?php } ?>
                                    </tr>
                                    <tr style="height: 10px;">
                                        <th style="text-align: center; font-size: 12px; text-align:left;border-bottom:.5px solid black; padding-left:15px; padding-top: px;padding-bottom:-5px;"><b>Pension Wages :</b></th>
                                        <th style=" font-size: 12px;border-bottom:.5px solid black;text-align:right; padding-top: 0px;padding-bottom:10px; "><b><?php echo number_format(round($pensiontotal), 2); ?></b></th>
                                        <th style="text-align: center; text-align:right;border-bottom:.5px solid black; padding-top: 0px;padding-bottom:10px;"><b>Account No: 22</b></th>
                                        <th style="text-align: center; font-size: 12px;padding-left: 2px; text-align:left;border-bottom:.5px solid black; padding-top: 0px;padding-bottom:10px;padding-left:7px;"><b>E D L I WAGES * 0.00000%</b></th>
                                        <th></th>

                                        <th style=" text-align: right; font-size: 12px;border-bottom: .5px solid black;"><b>=</b></th>
                                        <!-- Edited by Akshay on 20-5-2024 -->
                                        <th style="text-align: center; font-size: 12px; text-align:right;border-bottom:.5px solid black; padding-right:5px; padding-top: 0px;padding-bottom:10px;"><b>0.00</b></th>
                                        <!-- End -->
                                    </tr>

                                    <tr>
                                        <th colspan="5" style="text-align: center; border-bottom: .5px solid black; border-top: .5px solid black;   padding: 4px 90px 4px 130px; font-size: 13px;"><b>T&nbsp;O&nbsp;T&nbsp;A&nbsp;L</b></th>
                                        <?php
                                                $sum_total = round($column5 + $column6 + ($column4 * .005) + $column7 + round(($edli * 0.5 / 100)));
                                                if ($sum_total != 0) {
                                                } else {
                                                    $sum_total = '';
                                                }
                                        ?>
                                        <?php setlocale(LC_MONETARY, 'en_IN');
                                                $saltot1 = money_format('%!i', ($sum_total)); ?>
                                        <th style="border-bottom: .5px solid black;"></th>


                                        <th style=" border-bottom:.5px solid black;  border-top:.5px solid black; text-align: right;padding-right:5px;padding: 4px 5px; padding-top: -2px; padding-right:5px; "><b><?php echo $saltot1; ?></b></th>
                                    </tr>

                            </tbody>
                        </table>


                    <?php } ?><tr>
                        <!-- <p style="text-align:left;font-size: 12px; padding-left:5px;"><b>Total No. of Employees in the Month:&nbsp;<?php echo isset($empcount) ? $empcount : ''; ?></b></p> -->
                        <p style="text-align:left;font-size: 12px; padding-left:5px;"><b>Total No. of Employees in the Month:&nbsp;<?php echo $empcount; ?></b></p>
                        <!-- <p style="text-align:left;font-size: 12px; padding-left:5px; margin-top:-10px;"><b>No. of Excluded Employees:&nbsp;<?php echo isset($included_empcount) ? ($empcount - $included_empcount) : ''; ?></b></p> -->
                        <!-- Edited by Akshay on 21-5-2024 -->
                        <p style="text-align:left;font-size: 12px; padding-left:5px; margin-top:-10px;"><b>No. of Excluded Employees:&nbsp;<?php echo isset($excluded_key) ? ($excluded_key) : 0; ?></b></p>
                        <!-- End -->
                        <p style="text-align:left; font-size: 12px; padding-left: 5px; margin-top: -10px;">
                            <b>Gross Wages of Excluded Employees:
                                  
                                <?php 
                                // Edited by Akshay on 13-8-2025
                                echo isset($excluded_sum) && is_numeric($excluded_sum) ? number_format(max(0, $excluded_sum), 2) : '0.00'; ?>
                            </b>
                        </p>
                    </tr>



                    </tbody>
                    </table>
                </div>
            <?php }
                                    } ?> <!-- /.box-body -->

            </div>
        </div>

    </div>

<?php } else { ?>

    <style type="text/css">
        table {
            width: 80%;
            max-width: 80%;
            margin-bottom: 20px;
            /*background-color: transparent;*/
            border-spacing: 0;
            border-collapse: collapse;
            table-layout: fixed;
        }

        th,
        td {
            border: 1px solid black;
            padding: 8px;
            text-align: center;
            font-size: 12px;
            word-break: break-all;
            /* Allow long words to break */
            white-space: nowrap;
            /* Prevent text wrapping */
            overflow: hidden;
            /* Hide overflowing text */
            text-overflow: ellipsis;
            /* Show ellipsis (...) for overflow */
            border: none;
        }

        th[colspan="2"],
        th[rowspan="2"] {
            background-color: #f2f2f2;
        }

        th:nth-child(1) {
            width: 2%;
        }

        th:nth-child(2) {
            width: 18%;
        }

        th:nth-child(3) {
            width: 40%;
        }

        th:nth-child(4),
        th:nth-child(5) {
            width: 10%;
        }

        th[colspan="2"]:nth-child(6),
        th[colspan="2"]:nth-child(7) {
            width: 10%;
        }
    </style>

    <?php
    $arr_employeepf = array();
    foreach ($arr_salary_for_template as $value) {
        if (count($value) > 0) {
            $arr_employeepf[] = $value;
        }
    }
    //    debug($arr_employeepf);
    // $month_year = DateTime::createFromFormat('m-Y', $month);
    //Edited by Akshay on 20-5-2024
    $month_year = date('m-Y', strtotime($month));
    $formattedDate =  date('M/Y', strtotime($month));
    // debug(count($arr_employeepf) );exit;
    //End

    ?>

    <page backtop="30mm" backbottom="10mm" backleft="10mm" backright="10mm" style="font-size: 12pt">
        <page_header>
            <div style="margin-top: 50px;">
                <div style="text-align:left; width:100%; ">

                    <div style="position: relative;">
                        <div style="position: absolute; left: 42px; top: 0;">
                            <b style="font-size: 14px; margin-top: 0px;"><?php echo $arr_comp_contact_info['CompanyContactInfo']['business_name']; ?></b>
                        </div>
                        <div style="position: absolute; right: 32px; top: 0;">
                            <b style="font-size: 14px; margin-top: 0px;">Employer PF No.: <?php echo $emplr_pf_no; ?></b>
                        </div>
                    </div>



                </div>
                <br>
                <div style="text-align: center; border: none; width:450px; margin-top: 0px; margin-left: 160px; margin-right: auto; height: 25px;">
                    <b style="padding: 0px 20px; margin: 0; font-size: 14px; ">
                        PF Summary for the month of <?php echo date('F-Y',strtotime($date)); ?>
                    </b>
                </div>
            </div>
        </page_header>
        <?php
        if (count($arr_employeepf) > 0) {
        ?>


            <table class="table table-no-row-borders table-min-height" align="center" style="margin-top: -15px;border:1px solid grey;  width: 565px !important; border: 1px solid grey;">
                <thead>
                    <tr>
                        <th rowspan="2" style="border-top: .5px solid black; padding: 16px 4px 2px; line-height: 1.4;"><b>Sl.<br> No.</b></th>
                        <th rowspan="2" style="width: 16%; text-align: right; border-top: .5px solid black; padding: 8px 20px 8px 8px; line-height: 1.4;"><b>UAN</b></th>
                        <th rowspan="2" style="width: 33%; border-top: .5px solid black; padding-left: 55px; padding: 8px 4px; line-height: 1.4;"><b>Name of Member</b></th>
                        <th colspan="2" style="width: 22.5%; border-top: .5px solid black; padding: 6px 4px; line-height: 1.4; text-align: right;">Employee Contribution</th>
                        <th colspan="2" style="width: 22.5%; border-top: .5px solid black; padding: 6px 4px; line-height: 1.4;">Employer Contribution</th>
                    </tr>
                    <tr>
                        <th style="padding-right: -9px; padding-top:-5px;">PF <br>Earnings</th>
                        <th style="padding-right: 8px; padding-top:-5px;padding-right:0px;">Contribution<br> EPF</th>
                        <th style="padding-top:-5px; padding-right:0px;">EPF<br> Difference</th>
                        <th style="padding-top:0px; text-align: left; padding-left:0px; vertical-align: top; line-height: 1.6;">Pension<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 8.33%</th>
                    </tr>
                    <tr>
                        <th style=" text-align: center;  vertical-align: top; padding-top:0px;"><b>(1)</b></th>
                        <th style=" text-align: center; text-align:right;padding-right:24px; vertical-align: top;padding-top:0px;"><b>(2)</b></th>
                        <th style=" text-align: center; padding-right: -48px; vertical-align: top;padding-top:0px;"><b>(3)</b></th>
                        <th style=" text-align: center; padding-right: -9px; vertical-align: top;padding-top:0px;"><b>(4)</b></th>
                        <th style=" text-align: center; padding-right: -1px;  vertical-align: top; padding-top:0px;"><b>(5)</b></th>
                        <th style=" text-align: center;  vertical-align: top; padding-top:0px; padding-right: 0px;"><b>(6)</b></th>
                        <th style=" text-align: center;  vertical-align: top; padding-top:0px;"><b>(7)</b></th>
                    </tr>
                    <tr style="height:1px; ">
                        <td colspan="7" style="border-bottom: .5px solid black; padding-bottom:1px; padding-top:1px;"></td>
                    </tr>
                </thead>
                <tbody>

                    <?php
                    $i = 0;
                    $column4 = 0;
                    $column5 = 0;
                    $column6 = 0;
                    $column7 = 0;
                    $edli = 0;
                    $pensiontotal = 0;

                    foreach ($arr_employeepf as $branch) {
                        foreach ($branch as $employee) {

                            $i++; ?>
                            <tr style="margin-bottom:5px;">
                                <td style="width: 3%; text-align: right; padding: 2px;vertical-align: top;"> <?php echo $i; ?></td>
                                <td style="width:16%;text-align: left; padding: 2px 10px 2px 12px ;vertical-align: top;"><?php echo isset($employee['emp_details']['pf']) ? $employee['emp_details']['pf'] : ''; ?></td>
                                <td style="width: 33%; text-align: left; padding: 2px 0px 2px 25px; word-break: break-all;white-space:normal; vertical-align: top;"> <?php echo $employee['employee_info']['EmpName']; ?></td>
                                <?php
                                $sal1 = isset($employee['0']['EPF_earning']) ? $employee['0']['EPF_earning'] : '';
                                if ($sal1 != '') {
                                      $normalized = preg_replace('/\s*([\*\/])\s*/', '$1', $sal1);
                                                    $expressionWithoutPortion = str_replace(['*.12', '*12/100'], '', $normalized);
                                    //$expressionWithoutPortion = str_replace('* .12', '', $sal1);
                                    eval('$epf_earnings = ' . $expressionWithoutPortion . ';');
                                    $sal1 = floor($epf_earnings);
                                }
                                //Edited by Akshay on 20-5-2024
                                if ($sal1 > 15000) {
                                    $sal1 = 15000;
                                }
                                //End
                                $sal =  ($employee['0']['EPF'] * 100 / 12);
                                $column4 = $column4 + $sal1;
                                ?>
                                <td style="width:11.25%;word-break: break-all; text-align: right;padding: 2px; padding-right: -2px;vertical-align: top;"> <?php echo ($sal1 == 0) ? '' : number_format($sal1, 2); ?></td>
                                <?php
                                if ($sal1 > 15000) {
                                    $edli = $edli + 15000;
                                    if ($employee['emp_details']['eps'] != 'N') {
                                        $pension = round((15000) * .0833);
                                        $column7 = $column7 + $pension;
                                        $pensiontotal = $pensiontotal + 15000;
                                    } else {
                                        $pension = 0;
                                        $pensiontotal = $pensiontotal + 0;
                                    }
                                } else {
                                    $edli = $edli + $sal1;
                                    if ($employee['emp_details']['eps'] != 'N') {
                                        $pension = round(($sal1) * .0833);
                                        $column7 = $column7 + $pension;
                                        $pensiontotal = $pensiontotal + $sal1;
                                    } else {
                                        $pension = 0;
                                        $pensiontotal = $pensiontotal + 0;
                                    }
                                }
                                ?>
                                <?php $emp_contr = ($employee['0']['EPF']);
                                $column5 = $column5 + $emp_contr; ?>
                                <td style="width:11.25%;word-break: break-all; text-align: right; padding: 2px 0;vertical-align: top;"> <?php echo ($emp_contr == 0) ? '' : number_format(round($emp_contr), 2); ?></td>
                                <?php
                                $difference = ($emp_contr - $pension);
                                $column6 = $difference + $column6;
                                ?>
                                <td style="width:11.25%;word-break: break-all; text-align: right; padding: 2px 5px;vertical-align: top;"> <?php echo ($difference == 0) ? '' : number_format(round($difference), 2); ?></td>
                                <?php if ($pension > 0) { ?>
                                    <td style="width:11.25%;word-break: break-all;text-align:right; padding: 2px;padding-right:5px;vertical-align: top;"> <?php echo number_format(round($pension), 2); ?></td>
                                <?php } else { ?>
                                    <td style="width:11.25%;word-break: break-all; text-align: right; padding: 2px;padding-right:5px;vertical-align: top;"> <?php echo ''; ?></td>
                                <?php } ?>
                            </tr>

                    <?php
                        }
                    }
                    ?>

                    <tr>
                        <td colspan="7" class="heading_bottom" style=" border-bottom: .5px solid black;padding: .1px; line-height: 1;"></td>
                    </tr>
                    <tr>
                        <th colspan="3" style="text-align: right; font-size: 13px; border-bottom: .5px solid black; padding-top: 0px; padding-right: 25px;"><b>T&nbsp;O&nbsp;T&nbsp;A&nbsp;L</b></th>
                        <?php
                        if ($column4 != 0) { ?>
                            <th style=";font-size:13px; border-bottom: .5px solid black; padding-top: 0px; text-align:right;padding-right:-2px;"><b><?php echo number_format(round($column4), 2); ?></b></th>
                        <?php } else { ?>
                            <th style=" border-bottom: .5px solid black;"><b><?php echo ''; ?></b></th>
                        <?php }
                        ?>

                        <th style="padding-top: -5px;font-size:13px; border-bottom: .5px solid black; padding-top: 0px;  text-align:right; padding-right: 0px;">
                            <b><?php echo ($column5 == 0) ? '' : number_format(round($column5), 2); ?></b>
                        </th>
                        <th style="padding-top: -5px;font-size:13px; border-bottom: .5px solid black; padding-top: 0px; text-align:right; padding-right: 5px; ">
                            <b><?php echo ($column6 == 0) ? '' : number_format(round($column6), 2); ?></b>
                        </th>
                        <th style="padding-top: -5px;font-size:13px; border-bottom: .5px solid black; padding-top: 0px; text-align:right;padding-right:5px; ">
                            <b><?php echo ($column7 == 0) ? '' : number_format(round($column7), 2); ?></b>
                        </th>

                    </tr>
                </tbody>
            </table>

            <table class="table table-no-row-borders table-min-height" align="center" style="margin-top: -5px;border:1px solid grey; width: 565px !important; border: 1px solid grey;  table-layout:fixed">
                <tbody>
                    <tr style="height: 10px;">
                        <th style="width:19%; text-align: center; font-size: 12px; text-align:left; padding-top: 10px;"><b></b></th>
                        <th style="width:13%; text-align: center; font-size: 12px; text-align:left;"><b></b></th>
                        <th style="width:18%;text-align: center; text-align:right;"><b>Account No:01&nbsp;</b></th>
                        <th style="width:33%; text-align: center; font-size: 12px;padding-left: 2px; text-align:left; padding-left:7px;"><b>(Column Nos.5+6)</b></th>
                        <th style="width:2%; text-align: center; font-size: 12px; padding-left:0px;"><b>=</b></th>
                        <?php
                        if (($column5 + $column6) != 0) { ?>
                            <th style="width:14%; text-align: center; font-size: 12px; text-align:right; padding-right:5px;"><b><?php echo number_format(round($column5 + $column6), 2); ?></b></th>
                        <?php } else { ?>
                            <th style="width:14%; text-align: center; font-size: 12px; text-align:right; padding-right:5px;"><b><?php echo ''; ?></b></th>
                        <?php }
                        ?>

                    </tr>
                    <tr style="height: 10px;">
                        <th style="width:19%; text-align: center; font-size: 12px; text-align:left; padding-top: 0px;"><b></b></th>
                        <th style="width:13%; text-align: center; font-size: 12px; text-align:left; padding-top: 0px;"><b></b></th>
                        <th style="width:18%; text-align:right; padding-top: 0px;"><b>Account No:02&nbsp;</b></th>
                        <th style="width:33%; text-align: center; font-size: 12px;padding-left: 2px; text-align:left;padding-left:6px;padding-top:0px"><b>(0.50000% of Column No.4)</b></th>
                        <th style="width:2%; text-align: center; font-size: 12px; padding-top: 0px; padding-left:0px;"><b>=</b></th>
                        <?php if (($column4 * .005) != 0) { ?>
                            <th style="width:14%; text-align: center; font-size: 12px; text-align:right; padding-right:0px; padding-top: 0px;padding-right:5px;"><b><?php echo number_format(round($column4 * .005), 2); ?></b></th>
                        <?php } else { ?>
                            <th style="width:14%; text-align: center; font-size: 12px; text-align:right; padding-right:0px; padding-top: 0px;padding-right:5px;"><b><?php echo ''; ?></b></th>
                        <?php } ?>

                    </tr>
                    <tr style="height: 10px;">
                        <th style="width:19%; text-align: center; font-size: 12px; text-align:left; padding-top: 0px;"><b></b></th>
                        <th style="width:13%; text-align: center; font-size: 12px; text-align:left;  padding-top: 0px; "><b></b></th>
                        <th style="width:18%;text-align: center; text-align:right; padding-top: 0px;"><b>Account No:10 </b></th>
                        <th style="width:33%; text-align: center; font-size: 12px;padding-left: 2px; text-align:left; padding-top: 0px;padding-left:7px;"><b>(Column No. 7)</b></th>
                        <th style="width:2%; font-size: 12px; padding-top: 0px;  padding-left:0px;"><b>=</b></th>
                        <?php
                        if ($column7 != 0) { ?>
                            <th style="width:14%; text-align: center; font-size: 12px; text-align:right; padding-right:0px; padding-top: 0px;padding-right:5px;"><b><?php echo number_format(round($column7), 2); ?></b></th>
                        <?php } else { ?>
                            <th style="width:14%; text-align: center; font-size: 12px; text-align:right; padding-right:0px; padding-top: 0px;padding-right:5px;"><b><?php echo ''; ?></b></th>
                        <?php }
                        ?>
                    </tr>
                    <tr style="height: 10px;">
                        <th style="width:19%;  font-size: 12px; text-align:left; padding-left:15px; padding-top: 0px; padding-bottom: -5px;"><b>E&nbsp; D&nbsp; L&nbsp; I Wages :</b></th>
                        <th style="width:13%;  font-size: 12px; text-align:right;  padding-top: 0px; "><b><?php echo number_format(round($edli), 2); ?></b></th>
                        <th style="width:18%;text-align: center; text-align:right; padding-top: 0px;"><b>Account No: 21</b></th>
                        <th style="width:33%; text-align: center; font-size: 12px;padding-left: 2px; text-align:left; padding-top: 0px;padding-left:7px;"><b>E D L I WAGES * 0.50000%</b></th>
                        <th style="width:2%; text-align: center; font-size: 12px; padding-top: 0px; padding-left:0px;"><b>=</b></th>
                        <?php
                        if (($edli * 0.5 / 100) != 0) { ?>
                            <th style="width:14%; text-align: center; font-size: 12px; text-align:right; padding-right:0px; padding-top: 0px;padding-right:5px;"><b><?php echo number_format(round($edli * 0.5 / 100), 2); ?></b></th>
                        <?php } else {
                        ?>
                            <th style="width:14%; text-align: center; font-size: 12px; text-align:right; padding-right:0px; padding-top: 0px;padding-right:5px;"><b><?php echo ''; ?></b></th>
                        <?php } ?>
                    </tr>
                    <tr style="height: 10px;">
                        <th style="width:19%; text-align: center; font-size: 12px; text-align:left;border-bottom:.5px solid black; padding-left:15px; padding-top: px;padding-bottom:-5px;"><b>Pension Wages :</b></th>
                        <th style="width:13%;  font-size: 12px;border-bottom:.5px solid black;text-align:right; padding-top: 0px;padding-bottom:10px; "><b><?php echo number_format(round($pensiontotal), 2); ?></b></th>
                        <th style="width:18%;text-align: center; text-align:right;border-bottom:.5px solid black; padding-top: 0px;padding-bottom:10px;"><b>Account No: 22</b></th>
                        <th style="width:33%; text-align: center; font-size: 12px;padding-left: 2px; text-align:left;border-bottom:.5px solid black; padding-top: 0px;padding-bottom:10px;padding-left:7px;"><b>E D L I WAGES * 0.00000%</b></th>
                        <th style="width:2%; text-align: center; font-size: 12px; border-bottom:.5px solid black; padding-top: 0px;padding-bottom:10px; padding-left:0px;"><b>=</b></th>
                        <!-- Edited by Akshay on 20-5-2024 -->
                        <th style="width:14%; text-align: center; font-size: 12px; text-align:right;border-bottom:.5px solid black; padding-right:5px; padding-top: 0px;padding-bottom:10px;"><b>0.00</b></th>
                        <!-- End -->
                    </tr>

                    <tr>
                        <th colspan="5" style="text-align: center; border-bottom: .5px solid black; border-top: .5px solid black;   padding: 4px 90px 4px 130px; font-size: 13px;"><b>T&nbsp;O&nbsp;T&nbsp;A&nbsp;L</b></th>
                        <?php
                        $sum_total = round($column5 + $column6 + ($column4 * .005) + $column7 + round(($edli * 0.5 / 100)));
                        if ($sum_total != 0) {
                        } else {
                            $sum_total = '';
                        }
                        ?>
                        <?php setlocale(LC_MONETARY, 'en_IN');
                        $saltot1 = money_format('%!i', ($sum_total)); ?>

                        <th style=" border-bottom:.5px solid black;  border-top:.5px solid black; text-align: right;padding-right:5px;padding: 4px 5px; padding-top: -2px; padding-right:5px; "><b><?php echo $saltot1; ?></b></th>
                    </tr>
                </tbody>
            </table>


            <!-- <p style="text-align:left;font-size: 12px; padding-left:5px;"><b>Total No. of Employees in the Month:&nbsp;<?php echo isset($empcount) ? $empcount : ''; ?></b></p>
<p style="text-align:left;font-size: 12px; padding-left:5px; margin-top:-10px;"><b>No. of Excluded Employees:&nbsp;<?php echo isset($included_empcount) ? ($empcount - $included_empcount) : ''; ?></b></p>
<p style="text-align:left;font-size: 12px; padding-left:5px; margin-top:-10px;"><b>Gross Wages of Excluded Employees:&nbsp;<?php echo isset($exc_gross) && $exc_gross != 0 ? number_format($exc_gross, 2) : '0.00' ?></b></p> -->

            <p style="text-align:left;font-size: 12px; padding-left:5px;"><b>Total No. of Employees in the Month:&nbsp;<?php echo $empcount; ?></b></p>

            <!-- <p style="text-align:left;font-size: 12px; padding-left:5px; margin-top:-10px;"><b>No. of Excluded Employees:&nbsp;<?php echo isset($included_empcount) ? ($empcount - $included_empcount) : ''; ?></b></p> -->
            <!-- Edited by Akshay on 21-5-2024 -->
            <p style="text-align:left;font-size: 12px; padding-left:5px; margin-top:-10px;"><b>No. of Excluded Employees:&nbsp;<?php echo isset($excluded_key) ? ($excluded_key) : 0; ?></b></p>
            <!-- End -->

            <p style="text-align:left;font-size: 12px; padding-left:5px; margin-top:-10px;"><b>Gross Wages of Excluded Employees:&nbsp;<?php echo isset($excluded_sum) && $excluded_sum != 0 ? number_format(max(0, $excluded_sum), 2) : '0.00' ?></b></p>


        <?php } else { ?>
            <p style="text-align:center;font-size: 14px; padding-left:12px;">No data available under the selected criteria</p>
        <?php } ?>
    </page>

<?php }
?>

<?php  ?>