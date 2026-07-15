<style>
    .scrollable-table {
        max-height: 300px;
        /* Set the maximum height as needed */
        overflow-y: auto;
        overflow-x: auto;
        /* Add a vertical scrollbar when content exceeds the height */
    }
</style>
<?php if ($mode == '') { ?>
    <div class="modal-body" style="overflow-y:auto;">
        <h2 align="center" style="font-size: 25px;"><b><?php echo "Fixed Payment - " . $mname . "  "  . $year ?></b> </h2>
        <h2 style="font-weight: bold;text-align: center;font-size: 19px;"><?php echo  "(Report Run by " . $user_id . " at " . $date_time . ")" ?> </h2>
        <div class="fixed">
            <div class="col-md-12">
                <?php
                if (count($arr_salary_for_template) == 0) {
                    echo "<h3>No data available under the selected criteria.</h3>";
                } else { ?>
                    <?php
                    if ((isset($needBranchWiseReport) && $needBranchWiseReport == 1) || (isset($needItemWiseReport) && $needItemWiseReport == 1)) { //do branchwise listing 
                    ?>
                        <?php
                        $i = 0;
                        // debug($arr_salary_for_template); exit;
                        foreach ($arr_salary_for_template as $value) {
                            //debug($value);exit();
                            if (count($value) !== 0) {
                                $i += 1;
                        ?>

                                <div class="box-body " style="overflow-y:auto; ">
                                    <fieldset>
                                        <legend>
                                            <?php
                                            if ($str_criteria_item == 'Units') {
                                                echo $value['summary']['0']['ei']['branch'];
                                            } elseif ($str_criteria_item == 'Item') {
                                                echo $value['summary']['0']['vu']['salary_head_item_desc']; // Replace 'itemname' with the actual key of the item name in your data array
                                            }
                                            ?>
                                        </legend>

                                    </fieldset>
                                    <br>
                                    <fieldset>
                                        <table class="table table-bordered scrollable-table">
                                            <thead>

                                                <tr>

                                                    <th>Sl No</th>

                                                    <th>Employee ID</th>
                                                    <th>User ID</th>
                                                    <th>Employee Name</th>

                                                    <th>Branch</th>
                                                    <th>Department</th>
                                                    <th>Designation</th>
                                                    <th>Date of Joining</th>
                                                    <th>Fixed Payment Item</th>
                                                    <th>Amount</th>
                                                    <th>Operator</th>
                                                    <th>Start Month</th>
                                                    <th>End Month</th>
                                                    <th>Occurence</th>
                                                    <th>Remarks</th>
                                                    <th>Uploaded Date and Time</th>
                                                    <th>Uploaded By</th>


                                                </tr>
                                            </thead>

                                            <tbody>
                                                <?php $arr_data = $value; ?>

                                                <?php $arr_daata = $value['summary'];
                                                $employees = $value;

                                                if (empty($arr_daata))                        continue;
                                                if (count($arr_data) >= 0) {
                                                    $i = 0;
                                                    $tot = 0;
                                                ?>
                                                    <?php $arr_e = $employees['summary'];
                                                    // debug($arr_e);

                                                    foreach ($arr_e as $employee => $val) { ?>
                                                        <tr>
                                                            <?php $i = $i + 1;
                                                            $tot += $val['vu']['uploaded_amount'];
                                                            $status = (isset($val['ei']['emp_status'])) && $val['ei']['emp_status'] == "2" ? '(Resigned)' : ''; ?>
                                                            <td><?php echo $i;
                                                                ?></td>
                                                            <td><?php echo $val['ei']['employee_id']; ?></td>
                                                            <td><?php echo $val['uc']['user_id']; ?></td>
                                                            <td><?php echo $val[0]['empname'] . $status; ?></td>
                                                            <td><?php echo $val['ei']['branch']; ?></td>
                                                            <td><?php echo $val['ei']['department']; ?></td>
                                                            <td><?php echo $val['ei']['designation']; ?></td>
                                                            <td><?php echo isset($val['ei']['joining_date']) ? date('d-m-Y', strtotime($val['ei']['joining_date'])) : ''; ?></td>
                                                            <td><?php echo $val['vu']['salary_head_item_desc']; ?></td>

                                                            <td><?php echo $val['vu']['uploaded_amount']; ?></td>
                                                            <td><?php echo $val['vu']['head_operator']; ?></td>
                                                            <?php if (isset($val['vu']['start_date_effective']) && ($val['vu']['start_date_effective'] != '0000-00-00')) { ?>
                                                                <td><?php echo isset($val['vu']['start_date_effective']) ? date('m-Y', strtotime($val['vu']['start_date_effective'])) : ''; ?></td>
                                                            <?php } else { ?>
                                                                <td></td>
                                                            <?php } ?>
                                                            <?php if (isset($val['vu']['end_date_effective']) && ($val['vu']['end_date_effective'] != '0000-00-00')) { ?>
                                                                <td><?php echo isset($val['vu']['end_date_effective']) ? date('m-Y', strtotime($val['vu']['end_date_effective'])) : ''; ?></td>
                                                            <?php } else { ?>
                                                                <td></td>
                                                            <?php } ?>
                                                            <?php
                                                            switch ($val['vu']['occurance']) {
                                                                case 3:
                                                                    $occurance =  "Monthly";
                                                                    break;
                                                                case 4:
                                                                    $occurance = "Bi-Monthly";
                                                                    break;
                                                                case 5:
                                                                    $occurance = "Quarterly";
                                                                    break;
                                                                case 2:
                                                                    $occurance = "Half-Yearly";
                                                                    break;
                                                                case 1:
                                                                    $occurance = "Yearly";
                                                                    break;
                                                                default:
                                                                    $occurance = "";
                                                                    break;
                                                            }
                                                            ?>
                                                            <td><?php echo $occurance; ?></td>
                                                            <td><?php echo $val['vu']['remarks']; ?></td>
                                                            <td><?php $uploaded_time = isset($val['vu']['creation_date']) ? date('d-m-Y H:i:s', strtotime($val['vu']['creation_date'])) : '';
                                                                echo $uploaded_time; ?></td>
                                                            <td><?php $uploaded = isset($val[0]['created_by']) ? $val[0]['created_by'] : '';
                                                                echo $uploaded; ?></td>

                                                        </tr>
                                                    <?php  } ?>
                                                    <tr>
                                                        <th colspan="9" style="text-align:center">Total</th>
                                                        <th colspan="8" style="text-align:left;"><?php echo $tot; ?></th>
                                                    </tr>

                                                <?php } else { ?>
                                                    <tr>
                                                        <td>No data under this criteria</td>
                                                    </tr>
                                                <?php } ?>


                                            </tbody>
                                        </table>

                                    </fieldset>
                                    <br>
                                </div>


                        <?php }
                        }
                    } else { ?>
                        <?php
                        $i = 0;

                        if (count($arr_salary_for_template) !== 0) {
                            $i += 1;
                        ?>
                            <div class="box-body " style="overflow-y:auto; ">
                                <fieldset>
                                    <table class="table table-bordered">
                                        <thead>

                                            <tr>


                                                <th>Sl No</th>

                                                <th>Employee ID</th>
                                                <th>User ID</th>
                                                <th>Employee Name</th>

                                                <th>Branch</th>
                                                <th>Department</th>
                                                <th>Designation</th>
                                                <th>Date of Joining</th>
                                                <th>Fixed Payment Item</th>
                                                <th>Amount</th>
                                                <th>Operator</th>
                                                <th>Start Month</th>
                                                <th>End Month</th>
                                                <th>Occurence</th>
                                                <th>Remarks</th>
                                                <th>Uploaded Date and Time</th>
                                                <th>Uploaded By</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            <?php

                                            //  if(empty($arr_daata))                        continue;

                                            $i = 0;
                                            $tot = 0;

                                            ?>
                                            <?php
                                            foreach ($arr_salary_for_template as $value) {
                                                $arr_daata = $value['summary'];
                                                $employees = $value;
                                                if (count($value) >= 0) {
                                                    $arr_e = $employees['summary'];
                                                    //debug($arr_e);
                                                    foreach ($arr_daata as $employee => $val) { ?>

                                                        <tr>
                                                            <?php $i = $i + 1;
                                                            $tot += $val['vu']['uploaded_amount'];
                                                            $status = (isset($val['ei']['emp_status'])) && $val['ei']['emp_status'] == "2" ? '  (Resigned)' : ''; ?>
                                                            <td><?php echo $i;
                                                                ?></td>
                                                            <td><?php echo $val['ei']['employee_id']; ?></td>
                                                            <td><?php echo $val['uc']['user_id']; ?></td>
                                                            <td><?php echo $val[0]['empname'] . $status; ?></td>
                                                            <td><?php echo $val['ei']['branch']; ?></td>
                                                            <td><?php echo $val['ei']['department']; ?></td>
                                                            <td><?php echo $val['ei']['designation']; ?></td>
                                                            <td><?php echo isset($val['ei']['joining_date']) ? date('d-m-Y', strtotime($val['ei']['joining_date'])) : ''; ?></td>
                                                            <td><?php echo $val['vu']['salary_head_item_desc']; ?></td>

                                                            <td><?php echo $val['vu']['uploaded_amount']; ?></td>
                                                            <td><?php echo $val['vu']['head_operator']; ?></td>
                                                            <?php if (isset($val['vu']['start_date_effective']) && ($val['vu']['start_date_effective'] != '0000-00-00')) { ?>
                                                                <td><?php echo isset($val['vu']['start_date_effective']) ? date('m-Y', strtotime($val['vu']['start_date_effective'])) : ''; ?></td>
                                                            <?php } else { ?>
                                                                <td></td>
                                                            <?php } ?>
                                                            <?php if (isset($val['vu']['end_date_effective']) && ($val['vu']['end_date_effective'] != '0000-00-00')) { ?>
                                                                <td><?php echo isset($val['vu']['end_date_effective']) ? date('m-Y', strtotime($val['vu']['end_date_effective'])) : ''; ?></td>
                                                            <?php } else { ?>
                                                                <td></td>
                                                            <?php } ?>
                                                            <?php
                                                            switch ($val['vu']['occurance']) {
                                                                case 3:
                                                                    $occurance =  "Monthly";
                                                                    break;
                                                                case 4:
                                                                    $occurance = "Bi-Monthly";
                                                                    break;
                                                                case 5:
                                                                    $occurance = "Quarterly";
                                                                    break;
                                                                case 2:
                                                                    $occurance = "Half-Yearly";
                                                                    break;
                                                                case 1:
                                                                    $occurance = "Yearly";
                                                                    break;
                                                                default:
                                                                    $occurance = "";
                                                                    break;
                                                            }
                                                            ?>
                                                            <td><?php echo $occurance; ?></td>
                                                            <td><?php echo $val['vu']['remarks']; ?></td>
                                                            <td><?php $uploaded_time = isset($val['vu']['creation_date']) ? date('d-m-Y H:i:s', strtotime($val['vu']['creation_date'])) : '';
                                                                echo $uploaded_time; ?></td>
                                                            <td><?php $uploaded = isset($val[0]['created_by']) ? $val[0]['created_by'] : '';
                                                                echo $uploaded; ?></td>
                                                        </tr>
                                                <?php }
                                                } ?>

                                            <?php } ?>
                                            <tr>
                                                <th colspan="9" style="text-align:center">Total</th>
                                                <th colspan="8" style="text-align:left;"><?php echo $tot; ?></th>
                                            </tr><?php } else { ?>
                                            <tr>
                                                <td>No data under this criteria</td>
                                            </tr>
                                        <?php } ?>
                                        </tbody>
                                    </table>
                                </fieldset>
                                <br>
                            </div>
                    <?php }
                } ?>

            </div>
        </div>
    </div>


<?php } else { ?>
    <?php //echo '<style>'.file_get_contents("css/pdfbootstrap.css").'</style>';  
    ?>
    <!-- <style type="text/css">
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
        width: 80%;
        max-width: 80%;
        margin-bottom: 20px;
        /*background-color: transparent;*/
        border-spacing: 0;
        border-collapse: collapse;
    }

    td,
    th {
        text-align: left;
        padding: 1px;
        font-weight: normal;
        /*font-size: 11px;*/
        font-size: 11px;
        /*font-family: serif;*/
        line-height: 1.32857143;
        word-wrap: break-word;
        vertical-align: top;
        color: black;
        border: 1px solid;
    }

    .noborder th,
    .noborder td {
        border-left-style: hidden;
        border-left: 0px solid white;
        border-top-style: hidden;
        border-top: 0px solid white;
        border-bottom-style: hidden;
        border-bottom: 0px solid white;
        border-right-style: hidden;
        border-right: 0px solid white;
    }
    /* Edited by Akshay on 6-7-2023 */
    .table-no-row-borders tbody tr td {
        border-top: none;
        border-bottom: none;
    }
    .last-row td {
        border-bottom: 1px solid grey;
    }
    .table-min-height tbody {
        min-height: 40em!important;
    }
   

    
</style>
 -->
    <?php
    echo $this->element('reportadminheader', array(
        'title' => 'ESI Report'
    ));
    ?>



    <?php if (isset($needBranchWiseReport) && $needBranchWiseReport == 1) { //do branchwise listing 
    ?>
        <?php
        $i = 0;
        foreach ($arr_salary_for_template as $value) {
            if (count($value) !== 0) {
                $i += 1;
        ?>

                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th colspan="15" style="text-align:center;"><?php echo $arr_comp_contact_info['CompanyContactInfo']['business_name'] . ' - ESI - ' . $from; ?></th>
                        </tr>
                        <tr>
                            <th colspan="15" style="text-align:center;">Employer ESI No : <?php echo $eip; ?></th>
                        </tr>
                        <tr>

                            <th colspan="8" style="text-align:center">Employee Details</th>
                            <th colspan="4" style="text-align:center">ESI Details</th>
                            <th colspan="1">ESI - Employee</th>
                            <th colspan="1">ESI - Employer</th>
                            <th colspan="1">Total</th>

                        </tr>
                        <tr>

                            <th>Sl No</th>
                            <th>Employee Name</th>
                            <th>Employee ID</th>
                            <th>ESI IP No:</th>
                            <th>Joining Date</th>
                            <th>Branch</th>
                            <th>Department</th>
                            <th>Designation</th>
                            <th style="width:50px;">Days Worked</th>
                            <th>Actual Gross </th>
                            <!--<th>EPF</th>-->
                            <th>ESI Salary</th>
                            <th>Excluded Salary</th>
                            <!--<th>1.75%</th>
                                    <th>4.75%</th>-->
                            <!-- edited by megha -->
                            <?php $chg_date = '2019-07-01';
                            if ($otdate < $chg_date) {  ?>
                                <th>1.75%</th>
                                <th>4.75%</th>
                            <?php } else { ?>
                                <th>0.75%</th>
                                <th>3.25%</th>
                                <th>4%</th>
                            <?php } ?>

                        </tr>
                    </thead>

                    <tbody>
                        <?php $arr_data = $value; ?>

                        <?php $arr_daata = $value['summary'];
                        $employees = $value;
                        if (empty($arr_daata))                        continue;
                        if (count($arr_data) >= 0) {
                            $i = 0;
                            $sum = 0;
                            $gross = 0;
                            $esi_sal = 0;
                            $split1 = 0;
                            $split2 = 0;
                            $split3 = 0;
                            $pf_salary = 0;
                            $day = 0;
                            $days = 0;
                        ?>
                            <?php $arr_e = $employees['summary'];
                            foreach ($arr_e as $employee => $val) {
                                if ($val['0']['Esi'] > 0) { ?>
                                    <tr>
                                        <?php $i = $i + 1; ?>
                                        <td><?php echo $i;
                                            ?></td>
                                        <td style="width:80px;"><?php echo $val['employee_info']['EmpName']; ?></td>
                                        <td><?php echo $val['employee_info']['employee_id']; ?></td>
                                        <td><?php echo $val['emp_details']['esi']; ?></td>
                                        <td><?php echo $val['employee_info']['joining_date']; ?></td>
                                        <td style="width:80px;"><?php echo $val['employee_info']['branch']; ?></td>
                                        <td style="width:80px;"><?php echo $val['employee_info']['department']; ?></td>
                                        <td style="width:80px;"><?php echo $val['employee_info']['designation']; ?></td>
                                        <?php
                                        $esi1 = round($val['0']['Esi']);
                                        $esi = round($val['0']['EMPLOYER_ESI']);
                                        $total = round($esi1 + $esi);
                                        $sal = ($val['0']['SALARY'] != '0') ? round($val['0']['SALARY']) : 0;
                                        $gross += round($val['0']['SALARY']);
                                        $days = $val['0']['present'] + $val['0']['leaves'];
                                        $split1 += round($val['0']['Esi']);
                                        $split2 += round($val['0']['EMPLOYER_ESI']);
                                        $split3 += round($val['0']['EMPLOYER_ESI']) + round($val['0']['Esi']);
                                        $day += $days;
                                        $excluded = ($val['0']['Esi'] + $val['0']['EMPLOYER_ESI']) / .04;
                                        if ($excluded > $val['0']['SALARY']) {
                                            $excluded = $val['0']['SALARY'];
                                        }
                                        $salary = round($val['0']['SALARY'] - $excluded);
                                        $pf_salary += round($salary);
                                        $esi_sal += round($excluded);
                                        ?>
                                        <td><?php echo $days; ?></td>
                                        <td><?php echo $sal; ?></td>
                                        <td><?php echo $excluded; ?></td>
                                        <td><?php echo $salary;  ?></td>
                                        <td><?php echo $esi1; ?></td>
                                        <td><?php echo $esi; ?></td>
                                        <td><?php echo $total; ?></td>
                                    </tr>
                            <?php }
                            } ?>
                            <tr>
                                <th colspan="8" style="text-align:center">Grand Total</th>
                                <th style="width:50px;"><?php echo $day; ?></th>
                                <th><?php echo round($gross); ?></th>
                                <th><?php echo round($esi_sal); ?></th>
                                <th><?php echo round($pf_salary);  ?></th>
                                <th><?php echo round($split1); ?></th>
                                <th><?php echo round($split2); ?></th>
                                <th><?php echo round($split3); ?></th>
                            </tr>
                        <?php } else { ?>
                            <tr>
                                <td>No data under this criteria</td>
                            </tr>
                        <?php } ?>


                    </tbody>
                </table>
        <?php }
        }
    } else { ?>
        <?php
        $i = 0;

        if (count($arr_salary_for_template) !== 0) {
            $i += 1;
        ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th colspan="15" style="text-align:center;"><?php echo $arr_comp_contact_info['CompanyContactInfo']['business_name'] . ' - ESI - ' . $from; ?></th>
                    </tr>
                    <tr>
                    <tr>
                        <th colspan="15" style="text-align:center;">Employer ESI No : <?php echo $eip; ?></th>
                    </tr>
                    <tr>

                        <th colspan="8" style="text-align:center">Employee Details</th>
                        <th colspan="4" style="text-align:center">ESI Details</th>
                        <th colspan="1">ESI - Employee</th>
                        <th colspan="1">ESI - Employer</th>
                        <th colspan="1">Total</th>

                    </tr>
                    <tr>

                        <th>Sl No</th>
                        <th>Employee Name</th>
                        <th>Employee ID</th>
                        <th>ESI IP No:</th>
                        <th>Joining Date</th>
                        <th>Branch</th>
                        <th>Department</th>
                        <th>Designation</th>
                        <th style="width:50px;">Days Worked</th>
                        <th>Actual Gross </th>
                        <!--<th>EPF</th>-->
                        <th>ESI Salary</th>
                        <th>Excluded Salary</th>
                        <!--<th>1.75%</th>
                                    <th>4.75%</th>-->
                        <!-- edited by megha -->
                        <?php $chg_date = '2019-07-01';
                        if ($otdate < $chg_date) {  ?>
                            <th>1.75%</th>
                            <th>4.75%</th>
                        <?php } else { ?>
                            <th>0.75%</th>
                            <th>3.25%</th>
                            <th>4%</th>
                        <?php } ?>

                    </tr>
                </thead>

                <tbody>
                    <?php

                    //  if(empty($arr_daata))                        continue;

                    $i = 0;
                    $sum = 0;
                    $gross = 0;
                    $esi_sal = 0;
                    $split1 = 0;
                    $split2 = 0;
                    $split3 = 0;
                    $pf_salary = 0;
                    $day = 0;
                    $days = 0;
                    ?>
                    <?php foreach ($arr_salary_for_template as $value) {
                        $arr_e = $value['summary'];
                        foreach ($arr_e as $employee => $val) {
                            if ($val['0']['Esi'] > 0) { ?>
                                <tr>
                                    <?php $i = $i + 1; ?>
                                    <td><?php echo $i;
                                        ?></td>
                                    <td style="width:80px;"><?php echo $val['employee_info']['EmpName']; ?></td>
                                    <td><?php echo $val['employee_info']['employee_id']; ?></td>
                                    <td><?php echo $val['emp_details']['esi']; ?></td>
                                    <td><?php echo $val['employee_info']['joining_date']; ?></td>
                                    <td style="width:80px;"><?php echo $val['employee_info']['branch']; ?></td>
                                    <td style="width:80px;"><?php echo $val['employee_info']['department']; ?></td>
                                    <td style="width:80px;"><?php echo $val['employee_info']['designation']; ?></td>
                                    <?php
                                    $esi1 = round($val['0']['Esi']);
                                    $esi = round($val['0']['EMPLOYER_ESI']);
                                    $total = round($esi1 + $esi);
                                    $sal = ($val['0']['SALARY'] != '0') ? round($val['0']['SALARY']) : 0;
                                    $gross += round($val['0']['SALARY']);
                                    $days = $val['0']['present'] + $val['0']['leaves'];
                                    $split1 += round($val['0']['Esi']);
                                    $split2 += round($val['0']['EMPLOYER_ESI']);
                                    $split3 += round($val['0']['EMPLOYER_ESI']) + round($val['0']['Esi']);
                                    $day += $days;
                                    $excluded = ($val['0']['Esi'] + $val['0']['EMPLOYER_ESI']) / .04;
                                    if ($excluded > $val['0']['SALARY']) {
                                        $excluded = $val['0']['SALARY'];
                                    }
                                    $salary = round($val['0']['SALARY'] - $excluded);
                                    $pf_salary += round($salary);
                                    $esi_sal += round($excluded);
                                    ?>
                                    <td style="width:50px;"><?php echo $days; ?></td>
                                    <td><?php echo $sal; ?></td>
                                    <td><?php echo $excluded; ?></td>
                                    <td><?php echo $salary;  ?></td>
                                    <td><?php echo $esi1; ?></td>
                                    <td><?php echo $esi; ?></td>
                                    <td><?php echo $total; ?></td>
                                </tr>
                    <?php }
                        }
                    } ?>
                    <tr>
                        <th colspan="8" style="text-align:center">Grand Total</th>
                        <th><?php echo $day; ?></th>
                        <th><?php echo round($gross); ?></th>
                        <th><?php echo round($esi_sal); ?></th>
                        <th><?php echo round($pf_salary);  ?></th>
                        <th><?php echo round($split1); ?></th>
                        <th><?php echo round($split2); ?></th>
                        <th><?php echo round($split3); ?></th>
                    </tr>
                <?php } else { ?>
                    <tr>
                        <td>No data under this criteria</td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        <?php }  ?>

    <?php } ?>