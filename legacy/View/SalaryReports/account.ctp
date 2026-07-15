<?php

if ($mode == '') { ?>
    <style>
        #salary-modal-content {
            width: 104% !important;
        }

        #salary-ledger {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 13px;
        }

        #salary-ledger th,
        #salary-ledger td {
            padding: 5px;
            vertical-align: top;
        }

        #salary-ledger .border {
            border: 1px solid #000;
        }

        #salary-ledger .no-border {
            border: none !important;
        }

        #salary-ledger .center {
            text-align: center;
        }

        #salary-ledger .bold {
            font-weight: bold;
        }

        #salary-ledger .side-border {
            border-right: 1px solid #000;
        }
    </style>
    <div class="modal-body" style="overflow-y: auto;">
        <div class="row">
            <div class="col-md-12">
                <div class=" ">
                    <table id="salary-ledger" style="width:100%; border-collapse:collapse; font-family:Arial, sans-serif; font-size:13px;">

                        <!-- Client Name -->
                        <tr>
                            <td colspan="8" style="text-align:center; font-weight:bold; border:1px solid #000; padding:6px;">
                                <?php echo $arr_comp_contact_info['CompanyContactInfo']['business_name']; ?>
                            </td>
                        </tr>

                        <!-- Report Title -->
                        <tr>
                            <td colspan="8" style="text-align:center; font-weight:bold; border:1px solid #000; padding:6px;">
                                Salary Account Report (General Ledger) – For the
                                <?php
                                echo ($report_type == 'M')
                                    ? 'Month of ' . date('F-Y', strtotime($report_from . '-01'))
                                    : 'Months of ' . date('F-Y', strtotime($report_from . '-01')) . ' to ' . date('F-Y', strtotime($report_to . '-01'));
                                ?>
                            </td>
                        </tr>

                        <?php
                        if ($maxCount > 0) {
                        ?>

                            <!-- Dr / Cr -->
                            <tr>
                                <td colspan="4" style="font-weight:bold; border:1px solid #000; padding:6px;">Dr.</td>
                                <td colspan="4" style="font-weight:bold; border:1px solid #000; padding:6px; text-align:right;">Cr.</td>
                            </tr>

                            <!-- Header -->
                            <tr>
                                <td style="font-weight:bold; text-align:center; border:1px solid #000; padding:6px;">Date</td>
                                <td style="font-weight:bold; text-align:center; border:1px solid #000; padding:6px;">Particulars</td>
                                <td colspan="2" style="font-weight:bold; text-align:center; border:1px solid #000; padding:6px;">Amount</td>

                                <td style="font-weight:bold; text-align:center; border:1px solid #000; padding:6px;">Date</td>
                                <td style="font-weight:bold; text-align:center; border:1px solid #000; padding:6px;">Particulars</td>
                                <td colspan="2" style="font-weight:bold; text-align:center; border:1px solid #000; padding:6px;">Amount</td>
                            </tr>

                            <!-- ROWS -->
                            <?php
                            $total_l = $total_r = 0;
                            for ($i = 0; $i < $maxCount; $i++) {
                                $bold = isset($arr_left[$i]['bold']) ? "font-weight:bold;" : "";
                            ?>
                                <tr>
                                    <td style="padding:6px; <?php echo $bold; ?>" class="side-border"><?php echo ($i == 0) ? $att_enddate : ''; ?></td>
                                    <td style="padding:6px; <?php echo $bold; ?>" class="side-border">
                                        <?php echo isset($arr_left[$i]['salary_head_item_desc']) ? $arr_left[$i]['salary_head_item_desc'] : ''; ?>
                                    </td>
                                    <td style="padding:6px; text-align:right; <?php echo $bold; ?>" class="side-border">
                                        <?php echo isset($arr_left[$i]['salary_amount_l']) ? $arr_left[$i]['salary_amount_l'] : ''; ?>
                                    </td>
                                    <td style="padding:6px; text-align:right; <?php echo $bold; ?>" class="side-border">
                                        <?php
                                        if (isset($arr_left[$i]['salary_amount_r'])) {
                                            $total_l += $arr_left[$i]['salary_amount_r'];
                                            echo $arr_left[$i]['salary_amount_r'];
                                        }
                                        ?>
                                    </td>
                                    <td style="padding:6px;" class="side-border"><?php echo ($i == 0) ? $att_enddate : ''; ?></td>
                                    <td style="padding:6px;" class="side-border">
                                        <?php echo isset($arr_right[$i]['salary_head_item_desc']) ? $arr_right[$i]['salary_head_item_desc'] : ''; ?>
                                    </td>
                                    <td style="padding:6px; text-align:right;" class="side-border">
                                        <?php echo isset($arr_right[$i]['salary_amount_l']) ? $arr_right[$i]['salary_amount_l'] : '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'; ?>
                                    </td>
                                    <td style="padding:6px; text-align:right;" class="side-border">
                                        <?php
                                        if (isset($arr_right[$i]['salary_amount_r'])) {
                                            $total_r += $arr_right[$i]['salary_amount_r'];
                                            echo $arr_right[$i]['salary_amount_r'];
                                        }
                                        ?>
                                    </td>

                                </tr>
                            <?php } ?>

                            <!-- TOTAL -->
                            <tr>
                                <td style="font-weight:bold; text-align:center; border-top:1px solid #000; padding:6px;">&nbsp;</td>
                                <td colspan="2" style="font-weight:bold; text-align:center; border-top:1px solid #000; border-left:1px solid #000; padding:6px;">Total</td>
                                <td style="font-weight:bold; text-align:right; border-top:1px solid #000; border-left:1px solid #000; padding:6px;"><?php echo $total_l; ?></td>

                                <td style="font-weight:bold; text-align:center; border-top:1px solid #000; padding:6px;">&nbsp;</td>
                                <td colspan="2" style="font-weight:bold; text-align:center; border-top:1px solid #000; border-left:1px solid #000; padding:6px;">Total</td>
                                <td style="font-weight:bold; text-align:right; border-top:1px solid #000; border-left:1px solid #000; padding:6px;"><?php echo $total_r; ?></td>
                            </tr>
                        <?php } else { ?>
                            <tr>
                                <td colspan="8" style="text-align:left; border-top:1px solid #000; padding:6px;">No data available under the selected criteria.</td>
                            </tr>
                        <?php } ?>
                    </table>
                </div>
            </div>
        </div>
    </div>
<?php } else { ?>
    <?php //echo '<style>'.file_get_contents("css/pdfbootstrap.css").'</style>';    
    ?>
    <style type=" text/css">
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
            padding: 8px;
            font-weight: normal;
            /*font-size: 11px;*/
            font-size: 11px;
            /*font-family: serif;*/
            line-height: 1.42857143;
            word-wrap: break-word;
            vertical-align: top;
            color: black;
            border: 1px solid black;
        }
    </style>

    <page backtop="50mm" backbottom="2mm" backleft=2mm" backright="20mm" style="font-size: 12pt">

        <page_header>

            <div style="text-align:left; width:100%;">
                <?php if (isset($arr_comp_contact_info['CompanyContactInfo']['logo']) && !empty($arr_comp_contact_info['CompanyContactInfo']['logo'])) { ?>
                    <div style="width: 20%; margin-left: 20px; font-size: 18px; ">
                        <img style=" margin-left: 20px; margin-top: 40px; " src="<?php echo $finalLogoPath; ?>"  height="100" width="100" class="img-circle" alt="Company Logo" />
                    </div>
                    <!--<img style=" margin-left: 70px; " src="<?php echo $finalLogoPath; ?>"  height="50" width="70" class="img-circle" alt="Company Logo" />-->
                <?php } ?>
                <div style="width: 80%; margin-left: 100px; margin-top: 40px; position : absolute ; float: left; font-size: 14px; ">
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
    if ($maxCount > 0) {
    ?>
        <table id="salary_ledger" class="table" align="center" style="margin-top: 10px; margin-left: 30px; width: 550px !important;  ">

            <!-- Report Title -->
            <tr>
                <th colspan="8" style="text-align:center; font-weight:bold; border:1px solid #000; padding:6px;">
                    Salary Account Report (General Ledger) – For the
                    <?php
                    echo ($report_type == 'M')
                        ? 'Month of ' . date('F-Y', strtotime($report_from . '-01'))
                        : 'Months of ' . date('F-Y', strtotime($report_from . '-01')) . ' to ' . date('F-Y', strtotime($report_to . '-01'));
                    ?>
                </th>
            </tr>
            <!-- Dr / Cr -->
            <tr>
                <th colspan="4" style="font-weight:bold; border:1px solid #000; padding:6px;">Dr.</th>
                <th colspan="4" style="font-weight:bold; border:1px solid #000; padding:6px; text-align:right;">Cr.</th>
            </tr>

            <!-- Header -->
            <tr>
                <th style="font-weight:bold; text-align:center; border:1px solid #000; padding:6px;">Date</th>
                <th style="font-weight:bold; text-align:center; border:1px solid #000; padding:6px;">Particulars</th>
                <th colspan="2" style="font-weight:bold; text-align:center; border:1px solid #000; padding:6px;">Amount</th>

                <th style="font-weight:bold; text-align:center; border:1px solid #000; padding:6px;">Date</th>
                <th style="font-weight:bold; text-align:center; border:1px solid #000; padding:6px;">Particulars</th>
                <th colspan="2" style="font-weight:bold; text-align:center; border:1px solid #000; padding:6px;">Amount</th>
            </tr>

            <!-- ROWS -->
            <tbody>
                <?php
                $n = 0;
                $total_l = $total_r = 0;
                for ($i = 0; $i < $maxCount; $i++) {
                    $style = ($maxCount - 1 != $i) ? "border-bottom:0px;" : '';
                    $bold = isset($arr_left[$i]['bold']) ? "font-weight:bold;" : "";
                ?>
                    <tr>
                        <td style="padding:6px;<?php echo $style; ?>"><?php echo ($i == 0) ? $att_enddate : ''; ?></td>
                        <td style="padding:6px;<?php echo $style . $bold; ?>">
                            <?php echo isset($arr_left[$i]['salary_head_item_desc']) ? $arr_left[$i]['salary_head_item_desc'] : ''; ?>
                        </td>
                        <td style="padding:6px; text-align:right;<?php echo $style . $bold; ?>">
                            <?php echo isset($arr_left[$i]['salary_amount_l']) ? $arr_left[$i]['salary_amount_l'] : ''; ?>
                        </td>
                        <td style="padding:6px; text-align:right;<?php echo $style . $bold; ?>">
                            <?php
                            if (isset($arr_left[$i]['salary_amount_r'])) {
                                $total_l += $arr_left[$i]['salary_amount_r'];
                                echo $arr_left[$i]['salary_amount_r'];
                            }
                            ?>
                        </td>
                        <td style="padding:6px;<?php echo $style; ?>"><?php echo ($i == 0) ? $att_enddate : ''; ?></td>
                        <td style="padding:6px;<?php echo $style; ?>">
                            <?php echo isset($arr_right[$i]['salary_head_item_desc']) ? $arr_right[$i]['salary_head_item_desc'] : ''; ?>
                        </td>
                        <td style="padding:6px; text-align:right;<?php echo $style; ?>">
                            <?php
                            if (isset($arr_right[$i]['salary_amount_l'])) {
                                echo $arr_right[$i]['salary_amount_l'];
                            } else {
                                echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                            }
                            ?>
                        </td>

                        <td style="padding:6px; text-align:right;<?php echo $style; ?>">
                            <?php
                            if (isset($arr_right[$i]['salary_amount_r'])) {
                                $total_r += $arr_right[$i]['salary_amount_r'];
                                echo $arr_right[$i]['salary_amount_r'];
                            }
                            ?>
                        </td>

                    </tr>
                <?php $n++;
                } ?>

                <!-- TOTAL -->
                <tr>
                    <th style="font-weight:bold; text-align:center;  padding:6px;">&nbsp;</th>
                    <th colspan="2" style="font-weight:bold; text-align:center;  padding:6px;">Total</th>
                    <th style="font-weight:bold; text-align:right;  padding:6px;"><?php echo $total_l; ?></th>

                    <th style="font-weight:bold; text-align:center;  padding:6px;">&nbsp;</th>
                    <th colspan="2" style="font-weight:bold; text-align:center;  padding:6px;">Total</th>
                    <th style="font-weight:bold; text-align:right;  padding:6px;"><?php echo $total_r; ?></th>
                </tr>
            </tbody>
        </table>
    <?php } else { ?>
        <table class="table table-bordered" align="center" style="width:1000px;">
            <thead>
                <tr>
                    <th style="text-align:center; font-weight:bold; border:1px solid #000; padding:6px;width:700px;">
                        Salary Account Report (General Ledger) – For the
                        <?php
                        echo ($report_type == 'M')
                            ? 'Month of ' . date('F-Y', strtotime($report_from . '-01'))
                            : 'Months of ' . date('F-Y', strtotime($report_from . '-01')) . ' to ' . date('F-Y', strtotime($report_to . '-01'));
                        ?>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="text-align:left; border-top:1px solid #000; padding:6px;">
                        No data available under the selected criteria.
                    </td>
                </tr>
            </tbody>
        </table>

    <?php } ?>
<?php } ?>