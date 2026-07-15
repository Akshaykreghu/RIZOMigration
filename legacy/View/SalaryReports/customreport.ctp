<style>
    #table1 {
        border-collapse: collapse;
        overflow-y: auto;
    }

    /* #table1 tr td {
    border: 1px solid gray;
    } */

    #table2 {
        border-collapse: collapse;
        overflow-y: auto;
    }

    /* #table2 tr td {
        border: 1px solid gray;
    } */

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

    /* Edited by Akshay on 8-8-2024 */
    .no-wrap {
        white-space: nowrap;
    }

    .break-word-on-space {
        white-space: normal;
        /* Allows natural line breaks and word wrapping */
        word-break: normal;
        /* Avoid breaking words on arbitrary characters */
        hyphens: auto;
        /* Automatically adds hyphens where possible */
    }

    .align-left {
        text-align: left;
    }

    .align-right {
        text-align: right;
    }

    .width {
        width: 10px;
    }

    /* End */
</style>

<?php  //debug($gross);  
function breakWordOnSpace($text)
{
    // Replace spaces with a <br> tag for breaking the line
    return preg_replace('/\s+/', ' <br> ', trim($text));
}
$company_name = $arr_comp_contact_info['CompanyContactInfo']['business_name'];
?>
<?php if ($mode == '') { ?>
    <div class="modal-body" style="overflow-y:auto;">

        <div class="row">
            <div>
                <h2 align="center"><b><?php echo $company_name; ?></b> </h2>
                <h2 style="font-weight: bold;text-align: center;font-size: 19px;"><?php echo  "STAFF REPORT" ?> </h2>

                <?php $coun1 = isset($array_key['Addition']) ? count($array_key['Addition']) : 0;

                $cont = count($arr_sal_heads);
                $cont2 = count($arr_custom_report);


                if (count($arr_custom_report) <= 0) { ?>
                    <div style="font-size: 16px;text-align:left; background-color:;" id='printReportSynthite'>
                        No data available under the selected criteria.</div>
                <?php } else {
                    $total_val = array();
                    $total_val = array_fill(0, $cont, 0);
                ?>
                    <!--belongs to branch section added by megha end... view section-->
                    <?php if (isset($needBranchWiseReport) && $needBranchWiseReport == 1) { //do branchwise listing 
                    ?>

                        <div class="box-body" style="overflow-x: auto; overflow-y:auto;" id='printReportSynthite'>
                            <fieldset>
                                <legend style="border: 0;"><?php (isset($branches['info']['branch']) ? $branches['info']['branch'] : ''); ?></legend>
                                <!-- <br> -->
                                <table class="table table-bordered" id="table1">
                                    <thead>
                                        <!-- <tr>
                                            <th colspan="4" style="text-align: center;">Employee Details</th>
                                            <th colspan="<?php echo $cont; ?> " style="text-align: center;"><?php echo ($item_type == 'standard') ? 'Standard Salary' : 'Actual Salary'; ?></th>
                                        </tr> -->
                                        <tr style="background-color:;">
                                            <th class="align-left width">Sl No</th>
                                            <th class="align-left width">Pay<br>Period</th>
                                            <th class="align-left">Staff ID</th>
                                            <th class="align-left">Alias<br>Name</th>
                                            <?php
                                            foreach ($arr_sal_heads as $sal_head) {
                                                $item = $sal_head['shi']['item'];
                                                // $item = substr(trim($item), 0, 10);
                                                // Apply the breakWordOnSpace function to the item
                                                $item = breakWordOnSpace($item);
                                            ?>
                                                <th class="align-right"><?php echo $item; ?></th>
                                            <?php }
                                            ?>

                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $i = 1;
                                        foreach ($arr_custom_report as $branch => $brnch) {
                                            $branches = current($brnch);
                                            // debug(current($branches)); 

                                            foreach ($brnch as $val) {
                                                $eid = isset($val['info']['employee_id']) ? $val['info']['employee_id'] : '';
                                                $empName = isset($val['info']['EmpName']) ? $val['info']['EmpName'] : '';
                                                $status = isset($val['info']['emp_status']) ? $val['info']['emp_status'] : '';
                                                $status = ($status == 1) ? '' : ' (Resigned)';
                                                $empName = $empName . $status;
                                                $branch = isset($val['info']['branch']) ? $val['info']['branch'] : '';
                                                $month_year = isset($val['ectc']['month_year']) ? $val['ectc']['month_year'] : '';
                                                $month_year = $month_year ? date('Y/m', strtotime($month_year)) : '';

                                                if (isset($val['info'])) {     //To check if 'info' is set
                                                    if ($val['info']['branch'] == $val['info']['branch']) { ?>

                                                        <tr>
                                                            <td><?php echo $i; ?></td>
                                                            <td><?php echo $month_year; ?></td>
                                                            <td><?php echo $eid; ?></td>
                                                            <td><?php echo $empName; ?></td>

                                                            <?php
                                                            foreach ($arr_sal_heads as $key => $sal_head) {
                                                                $head_pkey = $sal_head['shi']['salary_head_item_pkey'];
                                                                $amount = isset($val[0][$head_pkey]) ? round($val[0][$head_pkey], $round_value) : 0;
                                                                $total_val[$key] += $amount;
                                                            ?>
                                                                <td class="align-right"><?php echo $amount; ?></td>
                                                            <?php }
                                                            ?>
                                                        </tr>




                                                <?php $i++;
                                                    }
                                                }

                                                ?>



                                            <?php  } ?>
                                        <?php } ?>
                                        <!-- Total -->
                                        <tr>
                                            <th colspan="4" style="text-align: center; ">TOTAL</th>
                                            <?php foreach ($total_val as $total) { ?>
                                                <th class="align-right"><?php echo abs($total); ?></th>
                                            <?php } ?>
                                        </tr>
                                    </tbody>
                                </table>

                            </fieldset>


                        </div>
                        <br>


                        <!-- Edited by Askshay on 27-7-2023 -->
                        <?php
                        if ($company_code == 'DEMO' || $company_code == 'GEDE' || $company_code == 'KWMT') {
                            $status = '';
                            $closure = array();
                            foreach ($arr_payroll_details as $key) {
                                $status = trim($key['pt']['payroll_status']);
                                $emp_name = isset($key[0]['EmpName']) ? $key[0]['EmpName'] : '';
                                if ($emp_name == '') {
                                    $emp_name = 'ADMIN';
                                }
                                $designation = isset($key[0]['designation']) ? $key[0]['designation'] : '';
                                if ($designation == '') {
                                    $designation = 'Administrator';
                                }
                                $closure[$status] = $emp_name . " - " . $designation;
                            }
                        ?>

                        <?php } ?>
                    <?php } else { ?>
                        <!--belongs to branch section added by megha end...-->
                        <div class="box-body" id='printReportSynthite'>
                            <!-- <div style="overflow-x: auto;"> -->
                            <fieldset>

                                <legend style="border: 0;"> <?php echo isset($value[0]['info']['EmpName']) ? $value[0]['info']['EmpName'] : ''; ?><?php echo (isset($value[0]['info']['emp_status'])) && $value[0]['info']['emp_status'] != "1" ? '  (Resigned)' : ''; ?></legend>

                                <table class="table table-bordered" id="table2">
                                    <thead>
                                        <tr style="background-color:;">
                                            <th class="align-left width">Sl No</th>
                                            <th class="align-left width">Pay<br>Period</th>
                                            <th class="align-left">Staff ID</th>
                                            <th class="align-left">Alias<br>Name</th>
                                            <?php
                                            foreach ($arr_sal_heads as $sal_head) {
                                                $item = $sal_head['shi']['item'];
                                                // $item = substr(trim($item), 0, 10);
                                                // Apply the breakWordOnSpace function to the item
                                                $item = breakWordOnSpace($item);
                                            ?>
                                                <th class="align-right"><?php echo $item; ?></th>
                                            <?php }
                                            ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $i = 1;
                                        foreach ($arr_custom_report as $value) {
                                        ?>
                                            <?php
                                            foreach ($value as $val) {
                                                $eid = isset($val['info']['employee_id']) ? $val['info']['employee_id'] : '';
                                                $empName = isset($val['info']['EmpName']) ? $val['info']['EmpName'] : '';
                                                $status = isset($val['info']['emp_status']) ? $val['info']['emp_status'] : '';
                                                $status = ($status == 1) ? '' : ' (Resigned)';
                                                $empName = $empName . $status;
                                                $branch = isset($val['info']['branch']) ? $val['info']['branch'] : '';
                                                $month_year = isset($val['ectc']['month_year']) ? $val['ectc']['month_year'] : '';
                                                $month_year = $month_year ? date('Y/m', strtotime($month_year)) : '';
                                                if (isset($val['info'])) {     //To check if 'info' is set
                                                    if ($val['info']['branch'] == $val['info']['branch']) { ?>

                                                        <tr>
                                                            <td><?php echo $i; ?></td>
                                                            <td><?php echo $month_year; ?></td>
                                                            <td><?php echo $eid; ?></td>
                                                            <td><?php echo $empName; ?></td>

                                                            <?php
                                                            foreach ($arr_sal_heads as $key => $sal_head) {
                                                                $head_pkey = $sal_head['shi']['salary_head_item_pkey'];
                                                                $amount = isset($val[0][$head_pkey]) ? round($val[0][$head_pkey], $round_value) : 0;
                                                                $total_val[$key] += $amount;
                                                            ?>
                                                                <td class="align-right"><?php echo $amount; ?></td>
                                                            <?php }
                                                            ?>
                                                        </tr>



                                            <?php $i++;
                                                    }
                                                }
                                            }
                                            ?>

                                        <?php } ?>



                                        <!-- Total -->
                                        <tr>
                                            <th colspan="4" style="text-align: center; ">TOTAL</th>
                                            <?php foreach ($total_val as $total) { ?>
                                                <th class="align-right"><?php echo abs($total); ?></th>
                                            <?php } ?>
                                        </tr>
                                    </tbody>
                                </table>

                            </fieldset>
                            <!-- </div> -->
                        </div>

                    <?php   } ?>

            </div>
        </div>
    </div>
<?php }
?>
<?php } else { ?>
    <?php
    ?>
    <style type="text/css">
        body {
            line-height: 1em;
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
            /* Horizontal border for the table */
        }

        td,
        th {
            text-align: left;
            font-weight: normal;
            padding: 8px;
            font-size: 10px;
            line-height: 1.42857143;
            word-wrap: break-word;
            /* Allow long words to break to the next line */
            word-break: break-word;
            /* Force break for long words that won't fit */
            vertical-align: top;
            color: black;
            border-top: 1px solid black;
            /* Horizontal border at the top of each cell */
            border-bottom: 1px solid black;
            /* Horizontal border at the bottom of each cell */
            border-left: none;
            /* Remove vertical left border */
            border-right: none;
            /* Remove vertical right border */
            box-shadow: inset 0.5px 0 #FF0000, inset 1px 0 #00FF00, inset 1.5px 0 #0000FF;
            max-width: 150px;
            /* Set max-width for the cells (adjust as necessary) */
            overflow-wrap: break-word;
            /* Additional word wrap handling */
            white-space: normal;
            /* Ensure the text wraps inside the cell */
        }

        th {
            font-weight: bold;
        }

        td{
            padding-top: 2px;
            padding-bottom: 2px;
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
    if (count($arr_custom_report) > 0) {
        $cont = count($arr_sal_heads);
        $total_val = array();
        $total_val = array_fill(0, $cont, 0);

        if ($cr == 'EmployeeDetails') {

    ?>

            <page backtop="30mm" backbottom="10mm" backleft="2mm" backright="2mm" style="font-size: 10pt;">

                <page_header>
                    <div style="width: 50%; text-align: center;">
                        <!-- Company Name on Top -->
                        <div style="font-size: 18px; font-weight: bold;">
                            <?php echo $company_name; ?>
                        </div>

                        <!-- Report Title Below -->
                        <div style="font-size: 14px; ">
                            STAFF REPORT
                        </div>
                    </div>
                    <div style="position: fixed; top: 200px; left: 0; width: 100%; font-size: 12px; display: flex; justify-content: space-between; padding: 0 10px; box-sizing: border-box;">
                        <table style="width: 100%; border: none; margin-top: 0px;">
                            <tr>
                                <!-- Left-aligned date and time -->
                                <td style="font-size: 12px; text-align: left; padding-left: 5px; border: none;width: 50%;">
                                    Dated <?php echo date("d/m/Y") . " " . date("h:i A"); ?>
                                </td>
                                <!-- Right-aligned page number -->
                                <td style="font-size: 12px; text-align: right; padding-right: 5px; border: none;width: 50%;">
                                    [[page_cu]]
                                </td>
                            </tr>
                        </table>
                    </div>
                </page_header>

                <bookmark title="Sommaire" level="0"></bookmark>

                <!-- Table Section -->
                <table class="table" align="center" style="width: 100%; margin-top: 10px;">
                    <thead>
                        <tr>
                            <th class="align-left" style="border-bottom:none;">Sl No</th>
                            <th class="align-left" style="border-bottom:none;">Pay Period</th>
                            <th class="align-left" style="border-bottom:none;">Staff ID</th>
                            <th class="align-left" style="width: 20px;border-bottom:none;">Alias Name</th>
                            <th class="align-left" style="width: 20px;border-bottom:none;">&nbsp;</th>
                            <?php
                            foreach ($arr_sal_heads as $sal_head) {
                                $item = $sal_head['shi']['item'];
                                // $item = substr(trim($item), 0, 10);
                                $item = breakWordOnSpace($item);
                            ?>
                                <th style="text-align: right;border-bottom:none;"><?php echo $item; ?></th>
                            <?php } ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $i = 1;
                        foreach ($arr_custom_report as $value) {
                            foreach ($value as $val) {
                                $eid = isset($val['info']['employee_id']) ? $val['info']['employee_id'] : '';
                                $empName = isset($val['info']['EmpName']) ? $val['info']['EmpName'] : '';
                                $status = isset($val['info']['emp_status']) ? ($val['info']['emp_status'] == 1 ? '' : ' (Resigned)') : '';
                                $empName .= $status;
                                $branch = isset($val['info']['branch']) ? $val['info']['branch'] : '';
                                $month_year = isset($val['ectc']['month_year']) ? date('Y/m', strtotime($val['ectc']['month_year'])) : '';

                                if (isset($val['info'])) { ?>
                                    <tr>
                                        <td><?php echo $i; ?></td>
                                        <td><?php echo $month_year; ?></td>
                                        <td><?php echo $eid; ?></td>
                                        <td style="width: 150px;"><?php echo $empName; ?></td>
                                        <td style="width: 20px;">&nbsp;</td>
                                        <?php
                                        foreach ($arr_sal_heads as $key => $sal_head) {
                                            $head_pkey = $sal_head['shi']['salary_head_item_pkey'];
                                            $amount = isset($val[0][$head_pkey]) ? round($val[0][$head_pkey], $round_value) : 0;
                                            $total_val[$key] += $amount;
                                        ?>
                                            <td style="text-align: right;"><?php echo $amount; ?></td>
                                        <?php } ?>
                                    </tr>
                        <?php $i++;
                                }
                            }
                        }
                        ?>

                        <!-- Total Row -->
                        <tr>
                            <th colspan="5" style="text-align: center;">TOTAL</th>
                            <?php foreach ($total_val as $total) { ?>
                                <th class="align-right"><?php echo abs($total); ?></th>
                            <?php } ?>
                        </tr>
                    </tbody>
                </table>

            </page>

        <?php
        } else { ?>
            <page backtop="30mm" backbottom="10mm" backleft="2mm" backright="2mm" style="font-size: 10pt; width:100%;">

                <page_header>
                    <div style="width: 50%; text-align: center;">
                        <!-- Company Name on Top -->
                        <div style="font-size: 18px; font-weight: bold;">
                            <?php echo $company_name; ?>
                        </div>

                        <!-- Report Title Below -->
                        <div style="font-size: 14px; ">
                            STAFF REPORT
                        </div>
                    </div>
                    <div style="position: fixed; top: 200px; left: 0; width: 100%; font-size: 12px; display: flex; justify-content: space-between; padding: 0 10px; box-sizing: border-box;">
                        <table style="width: 100%; border: none; margin-top: 0px;">
                            <tr>
                                <!-- Left-aligned date and time -->
                                <td style="font-size: 12px; text-align: left; padding-left: 5px; border: none;width: 50%;">
                                    Dated <?php echo date("d/m/Y") . " " . date("h:i A"); ?>
                                </td>
                                <!-- Right-aligned page number -->
                                <td style="font-size: 12px; text-align: right; padding-right: 5px; border: none;width: 50%;">
                                    [[page_cu]]
                                </td>
                            </tr>
                        </table>
                    </div>
                </page_header>

                <bookmark title="Sommaire" level="0"></bookmark>

                <!-- Table Section -->
                <table class="table" align="center" style="width: 100%; margin-top: 10px;">
                    <thead>
                        <tr>
                            <th class="align-left" style="border-bottom:none;">Sl No</th>
                            <th class="align-left" style="border-bottom:none;">Pay Period</th>
                            <th class="align-left" style="border-bottom:none;">Staff ID</th>
                            <th class="align-left" style="width: 20px;border-bottom:none;">Alias Name</th>
                            <th class="align-left" style="width: 20px;border-bottom:none;">&nbsp;</th>
                            <?php
                            foreach ($arr_sal_heads as $sal_head) {
                                $item = $sal_head['shi']['item'];
                                // $item = substr(trim($item), 0, 10);
                                $item = breakWordOnSpace($item);
                            ?>
                                <th style="text-align: right;border-bottom:none;"><?php echo $item; ?></th>
                            <?php } ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $i = 1;
                        foreach ($arr_custom_report as $value) {
                            foreach ($value as $val) {
                                $eid = isset($val['info']['employee_id']) ? $val['info']['employee_id'] : '';
                                $empName = isset($val['info']['EmpName']) ? $val['info']['EmpName'] : '';
                                $status = isset($val['info']['emp_status']) ? ($val['info']['emp_status'] == 1 ? '' : ' (Resigned)') : '';
                                $empName .= $status;
                                $branch = isset($val['info']['branch']) ? $val['info']['branch'] : '';
                                $month_year = isset($val['ectc']['month_year']) ? date('Y/m', strtotime($val['ectc']['month_year'])) : '';

                                if (isset($val['info'])) { ?>
                                    <tr>
                                        <td><?php echo $i; ?></td>
                                        <td><?php echo $month_year; ?></td>
                                        <td><?php echo $eid; ?></td>
                                        <td style="width: 150px;"><?php echo $empName; ?></td>
                                        <td style="width: 20px;">&nbsp;</td>
                                        <?php
                                        foreach ($arr_sal_heads as $key => $sal_head) {
                                            $head_pkey = $sal_head['shi']['salary_head_item_pkey'];
                                            $amount = isset($val[0][$head_pkey]) ? round($val[0][$head_pkey], $round_value) : 0;
                                            $total_val[$key] += $amount;
                                        ?>
                                            <td style="text-align: right;"><?php echo $amount; ?></td>
                                        <?php } ?>
                                    </tr>
                        <?php $i++;
                                }
                            }
                        }
                        ?>

                        <!-- Total Row -->
                        <tr>
                            <th colspan="5" style="text-align: center;">TOTAL</th>
                            <?php foreach ($total_val as $total) { ?>
                                <th class="align-right"><?php echo abs($total); ?></th>
                            <?php } ?>
                        </tr>
                    </tbody>
                </table>

            </page>
        <?php }
        ?>
    <?php } else { ?>
        <page backtop="30mm" backbottom="10mm" backleft="2mm" backright="2mm" style="font-size: 10pt; width:100%;">

            <page_header>
                <div style="width: 50%; text-align: center;">
                    <!-- Company Name on Top -->
                    <div style="font-size: 18px; font-weight: bold;">
                        <?php echo $company_name; ?>
                    </div>

                    <!-- Report Title Below -->
                    <div style="font-size: 14px; ">
                        STAFF REPORT
                    </div>
                </div>
                <div style="position: fixed; top: 200px; left: 0; width: 100%; font-size: 12px; display: flex; justify-content: space-between; padding: 0 10px; box-sizing: border-box;">
                    <table style="width: 100%; border: none; margin-top: 0px;">
                        <tr>
                            <!-- Left-aligned date and time -->
                            <td style="font-size: 12px; text-align: left; padding-left: 5px; border: none;width: 50%;">
                                Dated <?php echo date("d/m/Y") . " " . date("h:i A"); ?>
                            </td>
                            <!-- Right-aligned page number -->
                            <td style="font-size: 12px; text-align: right; padding-right: 5px; border: none;width: 50%;">
                                [[page_cu]]
                            </td>
                        </tr>
                    </table>
                </div>
                <div style="font-size: 16px;text-align:left;padding-left:100px;">
                    No data available under the selected criteria.</div>
            </page_header>

            <bookmark title="Sommaire" level="0"></bookmark>


        </page>
<?php }
}
// exit;
?>