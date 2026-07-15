<style>
    .right-align {
        text-align: right;
    }
</style>
<?php if ($mode == '') {  ?>
    <div>
        <h2 style="text-align:center; font-weight: bold;"><?php echo 'Overtime Summary Month Wise - ' . $month . " " . $year . ' to ' . $toMonth . ' ' . $toYear; ?></h2>
        <h2 style="font-weight: bold;text-align: center;font-size: 19px;"><?php echo  "(Report Run by " . $user_id . " at " . $date_time . ")" ?> </h2>
        <?php // Check if all summaries are empty
        $all_summaries_empty = true;

        foreach ($arr_leavepolicydetails_for_template as $val) {
            if (!empty($val['summary'])) {
                $all_summaries_empty = false;
                break;
            }
        }

        if ($all_summaries_empty) { ?>
            <div style="font-size: 16px;text-align:left; ">
                No data available under the selected criteria</div>
            <?php } else {
            if ($reporttype == 'Departments') {
                $total = 0; ?>
                <div style="overflow-x: auto;" id="printOTSummarySynthite">
                    <table class="table table-bordered table-responsive" cellspacing="0" border="0" class="fixed-table">
                        <tr style="border: 1px solid black;">
                            <th style="border: 1px solid black; background-color: #C8C8C8; text-align:center;">OT Analysis&nbsp;<?php echo $year;?></th>
                            <?php foreach ($monthYearList as $date) { ?>
                                <th style="border: 1px solid black; background-color: #C8C8C8;">&nbsp;</th>
                            <?php } ?>
                        </tr>


                        <tr style="border: 1px solid black;">
                            <td style="width: 30px !important;border: 1px solid black;">Cost Center</td>
                            <?php foreach ($monthYearList as $date) { ?>
                                <td style="width: 30px !important;border: 1px solid black; white-space: nowrap;" class="right-align"><?php echo $date; ?></td>
                            <?php } ?>
                        </tr>
                        <tr style="border: 1px solid black;">
                            <td style="width: 30px !important; border: 1px solid black;"> </td>
                            <?php foreach ($monthYearList as $date) { ?>
                                <td style="width: 30px !important; border: 1px solid black;" class="right-align"><?php echo 'RS'; ?></td>
                            <?php } ?>
                        </tr>
                        <?php
                        $printed_departments = []; // Array to keep track of printed departments

                        // Initialize arrays to store department-wise overtime amounts, durations, and employee counts for each month
                        $department_overtime = [];
                        $department_duration = [];
                        $department_employees = [];

                        // Initialize arrays to store total overtime amounts, durations, and employee counts for each month
                        $total_overtime = array_fill_keys(array_map(function ($date) {
                            return date('M-y', strtotime($date));
                        }, $monthYearList), 0);
                        $total_duration = array_fill_keys(array_map(function ($date) {
                            return date('M-y', strtotime($date));
                        }, $monthYearList), 0);
                        $total_employees = array_fill_keys(array_map(function ($date) {
                            return date('M-y', strtotime($date));
                        }, $monthYearList), 0);

                        // Populate department-wise overtime amounts, durations, and employee counts
                        foreach ($arr_leavepolicydetails_for_template as $val) :
                            $arr_leavepolicy_details = $val['summary'];

                            foreach ($arr_leavepolicy_details as $value) :
                                $department = $value['Info']['department'];
                                $month = date('M-y', strtotime($value['OTMASTER']['month']));
                                $overtime_amount = $value['0']['total_salary_amount'];
                                $duration = round(($value['OTMASTER']['set_duration'] / 60), 2); // Assuming there's a field for total duration
                                $employee_count = $value['0']['employee_count']; // Assuming there's a field for total employees

                                // Initialize the arrays for the department if not already set
                                if (!isset($department_overtime[$department])) {
                                    $department_overtime[$department] = [];
                                }
                                if (!isset($department_duration[$department])) {
                                    $department_duration[$department] = [];
                                }
                                if (!isset($department_employees[$department])) {
                                    $department_employees[$department] = [];
                                }

                                //Edited by Akshay on 14-8-2024
                                if (isset($department_overtime[$department][$month])) {
                                    $department_overtime[$department][$month] += removeUnnecessaryDecimals($overtime_amount);
                                } else {
                                    $department_overtime[$department][$month] = removeUnnecessaryDecimals($overtime_amount);
                                }
                                //End                                
                                $department_duration[$department][$month] = $duration;
                                $department_employees[$department][$month] = $employee_count;

                                // Add overtime amount, duration, and employee count to the total for the month
                                if (isset($total_overtime[$month])) {
                                    $total_overtime[$month] += round($overtime_amount);
                                }
                                if (isset($total_duration[$month])) {
                                    $total_duration[$month] += $duration;
                                }
                                if (isset($total_employees[$month])) {
                                    $total_employees[$month] += $employee_count;
                                }
                            endforeach;
                        endforeach;

                        // Output table rows for each department
                        foreach ($department_overtime as $department => $month_data) :
                            // Check if the department has already been printed
                            if (!in_array($department, $printed_departments)) :
                                // Add department to printed list
                                $printed_departments[] = $department;
                        ?>
                                <tr style="border: 1px solid black;">
                                    <td style="border: 1px solid black;"><?php echo $department; ?></td>

                                    <?php foreach ($monthYearList as $date) :
                                        // Get the month-year format from $date
                                        $formatted_date = date('M-y', strtotime($date));

                                        // Display the overtime amount if it exists for the current month
                                        if (isset($month_data[$formatted_date])) {
                                            $overtime_amount = $month_data[$formatted_date];
                                        } else {
                                            $overtime_amount = 0;
                                        }
                                    ?>
                                        <td style="border: 1px solid black;" class="right-align"><?php echo round($overtime_amount); ?></td>
                                    <?php endforeach; ?>
                                </tr>


                        <?php
                            endif;
                        endforeach;

                        // Output the total row for overtime, duration, and employee count
                        ?>
                        <tr style="border: 1px solid black; font-weight: bold;">
                            <td style="border: 1px solid black; background-color: #C8C8C8;">Total Amount</td>
                            <?php foreach ($monthYearList as $date) :
                                $formatted_date = date('M-y', strtotime($date));
                                $total_amount = isset($total_overtime[$formatted_date]) ? $total_overtime[$formatted_date] : 0;
                            ?>
                                <td style="border: 1px solid black; background-color: #C8C8C8;" class="right-align"><?php echo ($total_amount); ?></td>
                            <?php endforeach; ?>
                        </tr>

                        <tr>
                            <td style="border: 1px solid black;"><b>Total working hrs</b></td>
                            <?php foreach ($monthYearList as $date) :
                                $formatted_date = date('M-y', strtotime($date));
                            ?>
                                <td style="border: 1px solid black;" class="right-align"><?php echo ($total_duration[$formatted_date]); ?></td>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <td style="border: 1px solid black;"><b>Total no.of duty</b></td>
                            <?php foreach ($monthYearList as $date) :
                                $formatted_date = date('M-y', strtotime($date));
                            ?>
                                <!-- <td style="border: 1px solid black;" class="right-align"><?php echo $total_employees[$formatted_date]; ?></td> -->
                                <!-- Edited by Akshay on 12-8-2024 -->
                                <td style="border: 1px solid black;" class="right-align"><?php echo isset($total_duration[$formatted_date]) ? round((($total_duration[$formatted_date]) / 8)) : 0; ?></td>
                                <!-- End -->
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <td style="border: 1px solid black;"><b>Operator loss/Month</b></td>
                            <?php foreach ($monthYearList as $date) :
                                $formatted_date = date('M-y', strtotime($date));
                                $operator_loss = isset($total_duration[$formatted_date]) ? ((round(($total_duration[$formatted_date]) / 8)) / 26) : 0;
                            ?>
                                <td style="border: 1px solid black;" class="right-align"><?php echo round($operator_loss, 1); ?></td>
                            <?php endforeach; ?>
                        </tr>


                    </table>
                </div>
        <?php } else {
            }
        } ?>








        <!--        <div class="row">
                        <div class="form-group">
                            <div class="col-md-12" align="right">
                                <a href="#" class="btn btn-default" onclick="downloadReport('Overtime', 'pdf');" ><i class="icon-file"></i>Download As PDF</a>
                                a href="#" class="btn btn-default" onclick="downloadReport('Overtime', 'excel');"><i class="icon-file"></i>Download As Excel</a
                            </div>
                        </div>
                    </div>-->
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
            border: 1px solid black;
            width: 80%;
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
            border: 1px solid black;
        }
    </style>

    <?php
    // echo $this->element('reportadminheader', array(
    //     'title' => 'Overtime Summary Month Wise - ' . $month . " " . $year .' to '.$toMonth.'-'.$toYear. '<br> Report Run by ' . $user_id . ' at ' . $date_time
    // ));
    echo $this->element('reportadminheader', array(
        'title' => ''
    ));
    ?>
    <!-- <page backtop="30mm" backbottom="0mm" backleft="10mm" backright="10mm" style="font-size: 12pt">
        <page_header>

            <div style="text-align:left; width:100%; ">



                <img style=" margin-left: 40px; " src="https://<?php echo $_SERVER['HTTP_HOST'] . $this->webroot . $arr_comp_contact_info['CompanyContactInfo']['logo']; ?>" height="60" width="70" class="img-circle" alt="Company Logo" />

                <div style="text-align:center; width:75%;">
                    <b style="font-size: 19px; margin-top: 0px; color: #000066; "><?php echo $arr_comp_contact_info['CompanyContactInfo']['business_name']; ?></b>
                    <p style="font-size: 13px; margin-top: -40px; ">(A Govt. of Kerala Public Sector Undertaking)</p>
                    <p style="font-size: 13px; margin-top: -10px; "><?php echo $arr_comp_contact_info['CompanyContactInfo']['address'] . ', ' . $arr_comp_contact_info['CompanyContactInfo']['city'] . ', ' . $arr_comp_contact_info['CompanyContactInfo']['pincode'] . ', ' . $arr_comp_contact_info['CompanyContactInfo']['state']; ?></p>
                </div>

            </div>
            <div style="text-align: center; border: 1px solid black; width:450px; margin-top: 0px; margin-left: 160px; margin-right: auto; height: 25px;">
                <h3 style="padding: 0px 20px; margin: 0; font-size: 16px;">
                Overtime Summary Month Wise <?php echo $month . '-' . $year . ' to ' . $toMonth . '-' . $toYear; ?> 
                </h3>
            </div>
        </page_header>
        <page_footer>
        </page_footer>
    </page> -->
    <div style="display: flex;justify-content: center; align-items: center;">
        <h2 style="text-align:center; font-weight: bold;"><?php echo 'Overtime Summary Month Wise - ' . $month . " " . $year . ' to ' . $toMonth . ' ' . $toYear; ?></h2>
        <h2 style="font-weight: bold;text-align: center;font-size: 19px;"><?php echo  "(Report Run by " . $user_id . " at " . $date_time . ")" ?> </h2>
        <?php
        // Check if all summaries are empty
        $all_summaries_empty = true;
        foreach ($arr_leavepolicydetails_for_template as $val) {
            if (!empty($val['summary'])) {
                $all_summaries_empty = false;
                break;
            }
        }

        if ($all_summaries_empty) { ?>
            <div style="font-size: 20px; text-align: left;">
                No data available under the selected criteria
            </div>
            <?php } else {
            if ($reporttype == 'Departments') {
                $total = 0; ?>

                <table class="table table-bordered table-responsive" cellspacing="0" style="border: 2px solid black; width: 100%; margin: auto">
                    <thead>
                        <tr>
                            <th style="border: 2px solid black; background-color: #C8C8C8; text-align:center;">OT Analysis&nbsp;<?php echo $year;?></th>
                            <?php foreach ($monthYearList as $date) { ?>
                                <th style="border: 2px solid black; background-color: #C8C8C8;">&nbsp;</th>
                            <?php } ?>
                        </tr>
                        <tr>
                            <td style="border: 2px solid black; width: 30px !important;">Cost Center</td>
                            <?php foreach ($monthYearList as $date) { ?>
                                <td style="border: 2px solid black; width: 30px !important;text-align:right; white-space: nowrap;"><?php echo $date; ?></td>
                            <?php } ?>
                        </tr>
                        <tr>
                            <td style="border: 2px solid black; width: 30px !important;"></td>
                            <?php foreach ($monthYearList as $date) { ?>
                                <td style="border: 2px solid black; width: 30px !important; text-align:right;"><?php echo 'RS'; ?></td>
                            <?php } ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $printed_departments = []; // Array to keep track of printed departments

                        // Initialize arrays to store department-wise overtime amounts, durations, and employee counts for each month
                        $department_overtime = [];
                        $department_duration = [];
                        $department_employees = [];

                        // Initialize arrays to store total overtime amounts, durations, and employee counts for each month
                        $total_overtime = array_fill_keys(array_map(function ($date) {
                            return date('M-y', strtotime($date));
                        }, $monthYearList), 0);
                        $total_duration = array_fill_keys(array_map(function ($date) {
                            return date('M-y', strtotime($date));
                        }, $monthYearList), 0);
                        $total_employees = array_fill_keys(array_map(function ($date) {
                            return date('M-y', strtotime($date));
                        }, $monthYearList), 0);

                        // Populate department-wise overtime amounts, durations, and employee counts
                        foreach ($arr_leavepolicydetails_for_template as $val) {
                            $arr_leavepolicy_details = $val['summary'];

                            foreach ($arr_leavepolicy_details as $value) {
                                $department = $value['Info']['department'];
                                $month = date('M-y', strtotime($value['OTMASTER']['month']));
                                $overtime_amount = $value['0']['total_salary_amount'];
                                $duration = round(($value['OTMASTER']['set_duration'] / 60), 2); // Assuming there's a field for total duration
                                $employee_count = $value['0']['employee_count']; // Assuming there's a field for total employees

                                // Initialize the arrays for the department if not already set
                                if (!isset($department_overtime[$department])) {
                                    $department_overtime[$department] = [];
                                }
                                if (!isset($department_duration[$department])) {
                                    $department_duration[$department] = [];
                                }
                                if (!isset($department_employees[$department])) {
                                    $department_employees[$department] = [];
                                }

                                // Store overtime amount, duration, and employee count in the corresponding department and month slot
                                // $department_overtime[$department][$month] = $overtime_amount;
                                //Edited by Akshay on 14-8-2024
                                if (isset($department_overtime[$department][$month])) {
                                    $department_overtime[$department][$month] += removeUnnecessaryDecimals($overtime_amount);
                                } else {
                                    $department_overtime[$department][$month] = removeUnnecessaryDecimals($overtime_amount);
                                }
                                //End     
                                $department_duration[$department][$month] = $duration;
                                $department_employees[$department][$month] = $employee_count;

                                // Add overtime amount, duration, and employee count to the total for the month
                                if (isset($total_overtime[$month])) {
                                    $total_overtime[$month] += round($overtime_amount);
                                }
                                if (isset($total_duration[$month])) {
                                    $total_duration[$month] += $duration;
                                }
                                if (isset($total_employees[$month])) {
                                    $total_employees[$month] += $employee_count;
                                }
                            }
                        }

                        // Output table rows for each department
                        foreach ($department_overtime as $department => $month_data) {
                            // Check if the department has already been printed
                            if (!in_array($department, $printed_departments)) {
                                // Add department to printed list
                                $printed_departments[] = $department;
                        ?>
                                <tr>
                                    <td style="border: 2px solid black; padding: 5px;"><?php echo $department; ?></td>
                                    <?php foreach ($monthYearList as $date) {
                                        // Get the month-year format from $date
                                        $formatted_date = date('M-y', strtotime($date));

                                        // Display the overtime amount if it exists for the current month
                                        if (isset($month_data[$formatted_date])) {
                                            $overtime_amount = $month_data[$formatted_date];
                                        } else {
                                            $overtime_amount = 0;
                                        }
                                    ?>
                                        <td style="border: 2px solid black; padding: 5px; text-align:right;"><?php echo round($overtime_amount); ?></td>
                                    <?php } ?>
                                </tr>
                        <?php
                            }
                        }

                        // Output the total row for overtime, duration, and employee count
                        ?>
                        <tr style="font-weight: bold;">
                            <td style="border: 2px solid black; padding: 5px; background-color: #C8C8C8;">Total Amount</td>
                            <?php foreach ($monthYearList as $date) {
                                $formatted_date = date('M-y', strtotime($date));
                                $total_amount = isset($total_overtime[$formatted_date]) ? $total_overtime[$formatted_date] : 0;
                            ?>
                                <td style="border: 2px solid black; padding: 5px; text-align:right; background-color: #C8C8C8;"><?php echo ($total_amount); ?></td>
                            <?php } ?>
                        </tr>

                        <tr>
                            <td style="border: 2px solid black; padding: 5px;"><b>Total working hrs</b></td>
                            <?php foreach ($monthYearList as $date) {
                                $formatted_date = date('M-y', strtotime($date));
                            ?>
                                <td style="border: 2px solid black; padding: 5px; text-align:right;"><?php echo ($total_duration[$formatted_date]); ?></td>
                            <?php } ?>
                        </tr>
                        <tr>
                            <td style="border: 2px solid black; padding: 5px;"><b>Total no.of duty</b></td>
                            <?php foreach ($monthYearList as $date) {
                                $formatted_date = date('M-y', strtotime($date));
                            ?>
                                <!-- <td style="border: 2px solid black; padding: 5px; text-align:right;"><?php echo $total_employees[$formatted_date]; ?></td> -->
                                <!-- Edited by Akshay on 12-8-2024 -->
                                <td style="border: 1px solid black; text-align:right;" class="right-align"><?php echo isset($total_duration[$formatted_date]) ? round((($total_duration[$formatted_date]) / 8)) : 0; ?></td>
                                <!-- End -->
                            <?php } ?>
                        </tr>
                        <tr>
                            <td style="border: 2px solid black; padding: 5px;"><b>Operator loss/Month</b></td>
                            <?php foreach ($monthYearList as $date) {
                                $formatted_date = date('M-y', strtotime($date));
                                $operator_loss = isset($total_duration[$formatted_date]) ? (round((($total_duration[$formatted_date]) / 8)) / 26) : 0;
                            ?>
                                <td style="border: 2px solid black; padding: 5px; text-align:right;"><?php echo round($operator_loss, 1); ?></td>
                            <?php } ?>
                        </tr>
                    </tbody>
                </table>
        <?php } else {
                // Other logic for different report types
            }
        } ?>
    </div>

<?php }
//die();
?>