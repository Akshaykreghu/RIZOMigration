<?php
//debug($arr_salary_for_template);
/*
* To change this template, choose Tools | Templates
* and open the template in the editor.
*/
?>

<?php if ($mode == '') { ?>

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
            padding-left: 10px;
            font-weight: normal;
            padding: 2px;
            /*font-size: 11px;*/
            font-size: 14px;
            /*font-family: serif;*/
            line-height: 1.42857143;
            word-wrap: break-word;
            vertical-align: top;
            color: black;
            border: 1px solid black;
        }

        .move-right {
            padding-left: 10px;
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

                <page backtop="35mm" backbottom="1mm" backleft=2mm" backright="2mm" style="font-size: 12pt">

                    <page_header>


                        <!--        <div style="text-align:right; width:100%">

            <?php echo date("l,F j, Y"); ?> </div>-->
                        <div style="text-align:left; width:100%; ">
                            <?php if (isset($arr_comp_contact_info['CompanyContactInfo']['logo']) && !empty($arr_comp_contact_info['CompanyContactInfo']['logo'])) { ?>
                                <div style="width: 20%; margin-left: 0px; font-size: 18px; ">
                                    <img style=" margin-left: 20px; margin-top: 10px; " src="https://<?php echo $_SERVER['HTTP_HOST'] . $this->webroot . $arr_comp_contact_info['CompanyContactInfo']['logo']; ?>" height="110" width="auto" class="img-circle" alt="Company Logo" />
                                </div>
                                <!--<img style=" margin-left: 70px; " src="http://<?php echo $_SERVER['HTTP_HOST'] . $this->webroot . $arr_comp_contact_info['CompanyContactInfo']['logo']; ?>" height="50" width="70" class="img-circle" alt="Company Logo" />-->
                            <?php } ?>
                            <div style="width: 80%; margin-left: 100px; margin-top: 10px; position : absolute ; float: left; font-size: 14px; ">
                                <div style=" text-align: center;">
                                    <p>FORM XIII –See Rules 29(2)</p>
                                </div>
                                <div style="text-align: center;font-weight: bold;font-size: 14px; ; padding-top: 4px; "><?php echo $arr_comp_contact_info['CompanyContactInfo']['business_name']; ?>

                                </div>
                                <div style="text-align: center ; margin-left: 20px;padding-top: 4px; word-break: break-all;font-size: 11px; "><?php echo $arr_comp_contact_info['CompanyContactInfo']['address']; ?>

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
                                <hr>
                                <h3 style="text-align: center;padding-bottom: 0px;padding-top: 0px; margin-top: 0px;"><?php echo 'Salary Slip - ' . "$mname1-" . $y1; ?></h3>
                            </div>

                        </div>



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
                $bank = isset($value['summary']['0']['payroll_master']['bank_details']) ? $value['summary']['0']['payroll_master']['bank_details'] : '';
                if ($bank != '') {
                    list($bank_name, $branch_name, $ifsc_code, $acc_number) = explode(',', $bank);
                }
                if ($bank_name == '') {
                    $bank_name = isset($value['summary']['0']['ed']['bank_name'])?$value['summary']['0']['ed']['bank_name']:'';
                }
                if ($branch_name == '') {
                    $branch_name = isset($value['summary']['0']['ed']['branch_name'])?$value['summary']['0']['ed']['branch_name']:'';
                }
                if ($ifsc_code == '') {
                    $ifsc_code = isset($value['summary']['0']['ed']['ifsc_code'])?$value['summary']['0']['ed']['ifsc_code']:'';
                }
                if ($acc_number == '') {
                    $acc_number = isset($value['summary']['0']['ed']['account_no'])?$value['summary']['0']['ed']['account_no']:'';
                } ?>
                <table class="table" align="center" style="margin-top: 5px;  width: 550px !important;  ">
                    <tbody>

                        <tr style="width : 100px ; ">

                            <!--<th>LEAVEPOLICY_GROUP_NAME</th>-->


                            <th style="width:51%; border-bottom: 0px solid white ; border-right: 0px solid white ; " colspan="2"></th>
                            <th style="width:50%; border-bottom:  0px solid white ; border-left: 0px solid white ; " colspan="2"> </th>

                            <!--<th>Leave days</th>-->
                            <!--<th>Holidays</th>-->

                        </tr>

                        <tr>

                            <!--<th>LEAVEPOLICY_GROUP_NAME</th>-->


                            <th colspan="4" style="padding-top: 5px; padding-bottom: 5px;text-align:center;"> <b>Name : <?php
                                                                                                                        echo isset($value['summary']['0']['ed']['first_name']) ? $value['summary']['0']['ed']['first_name'] : '';
                                                                                                                        echo ' ';
                                                                                                                        echo isset($value['summary']['0']['ed']['middile_name']) ? $value['summary']['0']['ed']['middile_name'] : '';
                                                                                                                        echo ' ';
                                                                                                                        echo isset($value['summary']['0']['ed']['last_name']) ? $value['summary']['0']['ed']['last_name'] : '';
                                                                                                                        echo (isset($value['summary']['0']['ed']['status'])) && $value['summary']['0']['ed']['status'] == "2" ? '  (Resigned)' : '';

                                                                                                                        ?></b><b>&nbsp;&nbsp;&nbsp;</b> <b>Designation : <?php echo isset($value['summary']['0']['desg']['desig_name']) ? $value['summary']['0']['desg']['desig_name'] : ''; ?></b><b>&nbsp;</b></th>




                            <!--                                              <th>Leave days</th>
            <th>Holidays</th>-->

                        </tr>
                        <tr>
                            <th style="padding-top:10px;border-right-style: hidden; border-right: 0px solid white; border-bottom: 0px solid white ;  " colspan="2"><span style="text-align:  left ; ">Employee ID &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <?php echo isset($value['summary']['0']['ep']['emp_company_id']) ? $value['summary']['0']['ep']['emp_company_id'] : ''; ?></span></th>

                            <th style="padding-top:10px;border-right-style: hidden; border-right: 1px solid black; border-bottom: 0px solid white ; margin-right: 80px;" colspan="2"><span style="text-align:  left ; ">Date of Joining &nbsp;&nbsp;&nbsp;&nbsp;: <?php
                                                                                                                                                                                                                                                                    //edited by megha on 9_7_19 date format changed
                                                                                                                                                                                                                                                                    echo isset($value['summary']['0']['ep']['joining_date']) ? date('d-m-Y', strtotime($value['summary']['0']['ep']['joining_date'])) : '';
                                                                                                                                                                                                                                                                    //echo isset($value['summary']['0']['ep']['joining_date']) ? $value['summary']['0']['ep']['joining_date'] : '';
                                                                                                                                                                                                                                                                    ?></span></th>

                        </tr>
                        <tr>
                            <th style="border-right-style: hidden; border-bottom-style: hidden; border-right: 0px solid white; border-bottom: 0px solid white ;" colspan="2">Department&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <span style="margin-left: 1px; text-align: center;">
                                    <?php echo  wordwrap(isset($value['summary']['0']['d']['dept_name']) ? $value['summary']['0']['d']['dept_name'] : '', 30, "<br>\n", TRUE); ?> </span> </th>
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
                            <th style="border-right-style: hidden; border-bottom-style: hidden; border-right: 0px solid white; border-bottom: 0px solid white ;" colspan="2">Lop Days&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <span style="margin-left: 1px; text-align: center;">
                                    <?php echo  isset($value['summary']['0']['payroll_master']['loss_of_pay']) ? $value['summary']['0']['payroll_master']['loss_of_pay'] : ''; ?> </span> </th>
                            <th style="border-right-style: hidden; border-right: 1px solid black; border-bottom: 0px solid white ; margin-right: 80px;" colspan="2"><span style="text-align:  left ; ">No. of Week Off&nbsp;&nbsp;&nbsp;: <?php
                                                                                                                                                                                                                                            echo isset($value['summary']['0']['ar']['weekoff_total']) ? $value['summary']['0']['ar']['weekoff_total'] : ''; ?></span></th>
                        </tr>
                        <tr>
                            <th style="border-right-style: hidden; border-bottom-style: hidden; border-right: 0px solid white; border-bottom: 0px solid white ;" colspan="2">No. of Holiday&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <span style="margin-left: 1px; text-align: center;">
                                    <?php echo  isset($value['summary']['0']['ar']['holiday_total']) ? $value['summary']['0']['ar']['holiday_total'] : ''; ?> </span> </th>
                            <th style="border-right-style: hidden; border-right: 1px solid black; border-bottom: 0px solid white ; margin-right: 80px;" colspan="2"><span style="text-align:  left ; ">PF account No&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <?php
                                                                                                                                                                                                                                                    echo isset($value['summary']['0']['ed']['company_pf']) ? $value['summary']['0']['ed']['company_pf'] : ''; ?></span></th>
                        </tr>
                        <tr>
                            <th style="border-right-style: hidden; border-bottom-style: hidden; border-right: 0px solid white; border-bottom: 0px solid white ; " colspan="2">ESI No&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <span style="margin-left: 1px; text-align: center;">
                                    <?php echo  isset($value['summary']['0']['ed']['esi']) ? $value['summary']['0']['ed']['esi'] : ''; ?> </span> </th>
                            <th style="border-right-style: hidden; border-right: 1px solid black; border-bottom: 0px solid white ; margin-right: 80px;" colspan="2"><span style="text-align:  left ; ">UAN No&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <?php
                                                                                                                                                                                                                                                                                                                echo isset($value['summary']['0']['ed']['pf']) ? $value['summary']['0']['ed']['pf'] : ''; ?></span></th>
                        </tr>


                        <?php $bank_name = '';
                        $branch_name = '';
                        $ifsc_code = '';
                        $acc_number = '';
                        $bank = isset($value['summary']['0']['payroll_master']['bank_details']) ? $value['summary']['0']['payroll_master']['bank_details'] : '';
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
                            <th style="border-right-style: hidden; border-right: 0px solid white; border-bottom: 0px solid white ;   " colspan="2"><span>Bank Name&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; : </span><span style="margin-left: 1px; text-align: left;padding-left:50px; ">
                                    <?php
                                    echo wordwrap(isset($bank_name) ? $bank_name : '', 30, "<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n", TRUE);
                                    ?> </span> </th>
                            <th style="border-right-style: hidden; border-right: 1px solid black; border-bottom: 0px solid white ;   " colspan="2">Account Number&nbsp;&nbsp;: <span style="margin-left: 1px; text-align: center;"><?php
                                                                                                                                                                                                                                    echo wordwrap(isset($acc_number) ? $acc_number : '', 25, "<br>\n", TRUE);
                                                                                                                                                                                                                                    ?></span></th>
                        </tr>
                        <tr>
                            <th style="padding-bottom:10px;border-right-style: hidden; border-right: 0px solid white;   " colspan="2"><span style="text-align:  left ; ">IFSC Code &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <?php
                                                                                                                                                                                                                                                    echo isset($ifsc_code) ? $ifsc_code : '';
                                                                                                                                                                                                                                                    ?> </span></th>
                            <th style="padding-bottom:10px;border-right-style: hidden; border-right: 1px solid black;    " colspan="2"><span style="text-align:  left ; ">Branch&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;: <?php
                                                            $branch_name = isset($branch_name) ? $branch_name : '';
                                                            $wrapped_branch_name = wordwrap($branch_name, 10, "<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;", true);
                            echo isset($wrapped_branch_name) ? $wrapped_branch_name : ''; ?> </span></th>
                        </tr>


                        <tr style="background: #cccccc ;">

                            <!--    <th>LEAVEPOLICY_GROUP_NAME</th> -->


                            <th style="width:40%"><b>Earnings</b></th>
                            <th style="width:10%"><b>Amount</b></th>
                            <th style="width:41%"><b>Deductions</b></th>

                            <!--<th>Leave days</th>-->
                            <th style="width:10%"><b>Amount</b></th>

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
                            //edited by megha on 15_5_19
                            $countss = count($arr_data);
                            if (count($arr_data) < count($arr_withoutComponents)) {
                                $countss = count($arr_withoutComponents);
                            }
                        ?>
                            <?php for ($i = 0; $i < $countss; $i++) {
                                //edited by megha on 15_5_19
                            ?>

                                <tr> <?php
                                        $sum += isset($arr_data[$i]['ectc']['salary_amount']) ? $arr_data[$i]['ectc']['salary_amount'] : 0;
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
                                <th style="text-align: left; border: 2px solid black;"><b>Total Earnings</b></th>
                                <th style="border: 2px solid black;"><?php echo abs(round($sum)); ?></th>
                                <th style="border: 2px solid black;"><b>Total Deductions</b></th>
                                <th style="border: 2px solid black;"><?php echo abs(round($dd)); ?></th>
                            </tr>
                            <!-- edited by megha on 16/11/19 settlement amount  -->
                            <?php if ($value['summary']['0']['ed']['status'] == 2) { ?>
                                <tr style="background: #cccccc ;">
                                    <th style="text-align :left ; " colspan="3">Settlement Amount</th>
                                    <th><?php echo $value['settle']; ?></th>
                                </tr>
                            <?php } ?>
                            <!-- end -->
                            <!-- edited by megha on 30_05_19 round off ,on 16/11/19 settlement amount -->
                            <tr style="background: #cccccc ;">
                                <th style="text-align :left ; border-right:none ;" colspan="1"><b>Net Pay</b></th>
                                <th colspan="3"><?php echo round($sum) + round($dd) + round($value['settle']); ?></th>
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
                <p style="padding-left: 20px; border:1px solid black;">*This is a System generated pay slip and does not require signature.</p>





<?php
            }
        }
    } else {
        foreach ($arr_salary_for_template as $val) { ?>
            <!-- <legend><?php echo $val['0']['summary']['0']['br']['branch_name']; ?> </legend> -->
            <?php foreach ($val as $value) {
                if (count($value['summary']) !== 0 || count($value['withoutcomponent']) !== 0) {
                    $i += 1;
            ?>

                    <page backtop="35mm" backbottom="1mm" backleft=2mm" backright="2mm" style="font-size: 12pt">

                        <page_header>


                            <!--        <div style="text-align:right; width:100%">
    
                <?php echo date("l,F j, Y"); ?> </div>-->
                            <div style="text-align:left; width:100%; ">
                                <?php if (isset($arr_comp_contact_info['CompanyContactInfo']['logo']) && !empty($arr_comp_contact_info['CompanyContactInfo']['logo'])) { ?>
                                    <div style="width: 20%; margin-left: 0px; font-size: 18px; ">
                                        <img style=" margin-left: 20px; margin-top: 10px; " src="https://<?php echo $_SERVER['HTTP_HOST'] . $this->webroot . $arr_comp_contact_info['CompanyContactInfo']['logo']; ?>" height="110" width="auto" class="img-circle" alt="Company Logo" />
                                    </div>
                                    <!--<img style=" margin-left: 70px; " src="http://<?php echo $_SERVER['HTTP_HOST'] . $this->webroot . $arr_comp_contact_info['CompanyContactInfo']['logo']; ?>" height="50" width="70" class="img-circle" alt="Company Logo" />-->
                                <?php } ?>
                                <div style="width: 80%; margin-left: 100px; margin-top: 10px; position : absolute ; float: left; font-size: 14px; ">
                                    <div style=" text-align: center;">
                                        <p>FORM XIII –See Rules 29(2)</p>
                                    </div>
                                    <div style="text-align: center;font-weight: bold;font-size: 14px; ; padding-top: 4px; "><?php echo $arr_comp_contact_info['CompanyContactInfo']['business_name']; ?>

                                    </div>
                                    <div style="text-align: center ; margin-left: 20px;padding-top: 4px; word-break: break-all;font-size: 11px; "><?php echo $arr_comp_contact_info['CompanyContactInfo']['address']; ?>

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
                                    <hr>
                                    <h3 style="text-align: center;padding-bottom: 0px;padding-top: 0px; margin-top: 0px;"><?php echo 'Salary Slip - ' . "$mname1-" . $y1; ?></h3>
                                </div>

                            </div>



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
                    $bank = isset($value['summary']['0']['payroll_master']['bank_details']) ? $value['summary']['0']['payroll_master']['bank_details'] : '';
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
                    } ?>
                    <table class="table" align="center" style="margin-top: 5px;  width: 550px !important;  ">
                        <tbody>

                            <tr style="width : 100px ; ">

                                <!--<th>LEAVEPOLICY_GROUP_NAME</th>-->


                                <th style="width:51%; border-bottom: 0px solid white ; border-right: 0px solid white ; " colspan="2"></th>
                                <th style="width:50%; border-bottom:  0px solid white ; border-left: 0px solid white ; " colspan="2"> </th>

                                <!--<th>Leave days</th>-->
                                <!--<th>Holidays</th>-->

                            </tr>

                            <tr>

                                <!--<th>LEAVEPOLICY_GROUP_NAME</th>-->


                                <th colspan="4" style="padding-top: 5px; padding-bottom: 5px;text-align:center;"> <b>Name : <?php
                                                                                                                            echo isset($value['summary']['0']['ed']['first_name']) ? $value['summary']['0']['ed']['first_name'] : '';
                                                                                                                            echo ' ';
                                                                                                                            echo isset($value['summary']['0']['ed']['middile_name']) ? $value['summary']['0']['ed']['middile_name'] : '';
                                                                                                                            echo ' ';
                                                                                                                            echo isset($value['summary']['0']['ed']['last_name']) ? $value['summary']['0']['ed']['last_name'] : '';
                                                                                                                            echo (isset($value['summary']['0']['ed']['status'])) && $value['summary']['0']['ed']['status'] == "2" ? '  (Resigned)' : '';

                                                                                                                            ?></b><b>&nbsp;&nbsp;&nbsp;</b> <b>Designation : <?php echo isset($value['summary']['0']['dd']['desig_name']) ? $value['summary']['0']['dd']['desig_name'] : ''; ?></b><b>&nbsp;</b></th>




                                <!--                                              <th>Leave days</th>
                <th>Holidays</th>-->

                            </tr>
                            <tr>
                                <th style="padding-top:10px;border-right-style: hidden; border-right: 0px solid white; border-bottom: 0px solid white ;  " colspan="2"><span style="text-align:  left ; ">Employee ID &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <?php echo isset($value['summary']['0']['ep']['emp_company_id']) ? $value['summary']['0']['ep']['emp_company_id'] : ''; ?></span></th>

                                <th style="padding-top:10px;border-right-style: hidden; border-right: 1px solid black; border-bottom: 0px solid white ; margin-right: 80px;" colspan="2"><span style="text-align:  left ; ">Date of Joining &nbsp;&nbsp;&nbsp;&nbsp;: <?php
                                                                                                                                                                                                                                                                        //edited by megha on 9_7_19 date format changed
                                                                                                                                                                                                                                                                        echo isset($value['summary']['0']['ep']['joining_date']) ? date('d-m-Y', strtotime($value['summary']['0']['ep']['joining_date'])) : '';
                                                                                                                                                                                                                                                                        //echo isset($value['summary']['0']['ep']['joining_date']) ? $value['summary']['0']['ep']['joining_date'] : '';
                                                                                                                                                                                                                                                                        ?></span></th>

                            </tr>
                            <tr>
                                <th style="border-right-style: hidden; border-bottom-style: hidden; border-right: 0px solid white; border-bottom: 0px solid white ;" colspan="2">Department&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <span style="margin-left: 1px; text-align: center;">
                                        <?php echo  wordwrap(isset($value['summary']['0']['d']['dept_name']) ? $value['summary']['0']['d']['dept_name'] : '', 30, "<br>\n", TRUE); ?> </span> </th>
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
                                <th style="border-right-style: hidden; border-bottom-style: hidden; border-right: 0px solid white; border-bottom: 0px solid white ;" colspan="2">Lop Days&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <span style="margin-left: 1px; text-align: center;">
                                        <?php echo  isset($value['summary']['0']['payroll_master']['loss_of_pay']) ? $value['summary']['0']['payroll_master']['loss_of_pay'] : ''; ?> </span> </th>
                                <th style="border-right-style: hidden; border-right: 1px solid black; border-bottom: 0px solid white ; margin-right: 80px;" colspan="2"><span style="text-align:  left ; ">No. of Week Off&nbsp;&nbsp;&nbsp;: <?php
                                                                                                                                                                                                                                                echo isset($value['summary']['0']['ar']['weekoff_total']) ? $value['summary']['0']['ar']['weekoff_total'] : ''; ?></span></th>
                            </tr>
                            <tr>
                                <th style="border-right-style: hidden; border-bottom-style: hidden; border-right: 0px solid white; border-bottom: 0px solid white ;" colspan="2">No. of Holiday&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <span style="margin-left: 1px; text-align: center;">
                                        <?php echo  isset($value['summary']['0']['ar']['holiday_total']) ? $value['summary']['0']['ar']['holiday_total'] : ''; ?> </span> </th>
                                <th style="border-right-style: hidden; border-right: 1px solid black; border-bottom: 0px solid white ; margin-right: 80px;" colspan="2"><span style="text-align:  left ; ">PF account No&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <?php
                                                                                                                                                                                                                                                        echo isset($value['summary']['0']['ed']['company_pf']) ? $value['summary']['0']['ed']['company_pf'] : ''; ?></span></th>
                            </tr>
                            <tr>
                                <th style="border-right-style: hidden; border-bottom-style: hidden; border-right: 0px solid white; border-bottom: 0px solid white ; " colspan="2">ESI No&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <span style="margin-left: 1px; text-align: center;">
                                        <?php echo  isset($value['summary']['0']['ed']['esi']) ? $value['summary']['0']['ed']['esi'] : ''; ?> </span> </th>
                                <th style="border-right-style: hidden; border-right: 1px solid black; border-bottom: 0px solid white ; margin-right: 80px;" colspan="2"><span style="text-align:  left ; ">UAN No&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <?php
                                                                                                                                                                                                                                                                                                                    echo isset($value['summary']['0']['ed']['pf']) ? $value['summary']['0']['ed']['pf'] : ''; ?></span></th>
                            </tr>


                            <?php $bank_name = '';
                            $branch_name = '';
                            $ifsc_code = '';
                            $acc_number = '';
                            $bank = isset($value['summary']['0']['payroll_master']['bank_details']) ? $value['summary']['0']['payroll_master']['bank_details'] : '';
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
                                <th style="border-right-style: hidden; border-right: 0px solid white; border-bottom: 0px solid white ;   " colspan="2"><span>Bank Name&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; : </span><span style="margin-left: 1px; text-align: left;padding-left:50px; ">
                                        <?php
                                        echo wordwrap(isset($bank_name) ? $bank_name : '', 30, "<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\n", TRUE);
                                        ?> </span> </th>
                                <th style="border-right-style: hidden; border-right: 1px solid black; border-bottom: 0px solid white ;   " colspan="2">Account Number&nbsp;&nbsp;: <span style="margin-left: 1px; text-align: center;"><?php
                                                                                                                                                                                                                                        echo wordwrap(isset($acc_number) ? $acc_number : '', 25, "<br>\n", TRUE);
                                                                                                                                                                                                                                        ?></span></th>
                            </tr>
                            <tr>
                                <th style="padding-bottom:10px;border-right-style: hidden; border-right: 0px solid white;   " colspan="2"><span style="text-align:  left ; ">IFSC Code &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <?php
                                                                                                                                                                                                                                                        echo isset($ifsc_code) ? $ifsc_code : '';
                                                                                                                                                                                                                                                        ?> </span></th>
                                <th style="padding-bottom:10px;border-right-style: hidden; border-right: 1px solid black;    " colspan="2"><span style="text-align:  left ; ">Branch&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;: <?php
                                                                                                                                                                                                                                                                                        $branch_name = isset($branch_name) ? $branch_name : '';
                                                                                                                                                                                                                                                                                        $wrapped_branch_name = wordwrap($branch_name, 10, "<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;", true);
                                                                                                                                                                                                                                                                                        echo isset($wrapped_branch_name) ? $wrapped_branch_name : ''; ?> </span></th>
                            </tr>


                            <tr style="background: #cccccc ;">

                                <!--    <th>LEAVEPOLICY_GROUP_NAME</th> -->


                                <th style="width:40%"><b>Earnings</b></th>
                                <th style="width:10%"><b>Amount</b></th>
                                <th style="width:41%"><b>Deductions</b></th>

                                <!--<th>Leave days</th>-->
                                <th style="width:10%"><b>Amount</b></th>

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
                                //edited by megha on 15_5_19
                                $countss = count($arr_data);
                                if (count($arr_data) < count($arr_withoutComponents)) {
                                    $countss = count($arr_withoutComponents);
                                }
                            ?>
                                <?php for ($i = 0; $i < $countss; $i++) {
                                    //edited by megha on 15_5_19
                                ?>

                                    <tr> <?php
                                            $sum += isset($arr_data[$i]['ectc']['salary_amount']) ? $arr_data[$i]['ectc']['salary_amount'] : 0;
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
                                    <th style="text-align: left; border: 2px solid black;"><b>Total Earnings</b></th>
                                    <th style="border: 2px solid black;"><?php echo abs(round($sum)); ?></th>
                                    <th style="border: 2px solid black;"><b>Total Deductions</b></th>
                                    <th style="border: 2px solid black;"><?php echo abs(round($dd)); ?></th>
                                </tr>
                                <!-- edited by megha on 16/11/19 settlement amount  -->
                                <?php if ($value['summary']['0']['ed']['status'] == 2) { ?>
                                    <tr style="background: #cccccc ;">
                                        <th style="text-align :left ; " colspan="3">Settlement Amount</th>
                                        <th><?php echo $value['settle']; ?></th>
                                    </tr>
                                <?php } ?>
                                <!-- end -->
                                <!-- edited by megha on 30_05_19 round off ,on 16/11/19 settlement amount -->
                                <tr style="background: #cccccc ;">
                                    <th style="text-align :left ; border-right:none ;" colspan="1"><b>Net Pay</b></th>
                                    <th colspan="3"><?php echo round($sum) + round($dd) + round($value['settle']); ?></th>
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
                    <p style="padding-left: 20px; border:1px solid black;">*This is a System generated pay slip and does not require signature.</p>





    <?php
                }
            }
        }
    }
    ?> <!-- /.box-body -->
    <?php
    //                }
    //                }
    if ($i == '0') { ?>
        <div style="font-size: 25px;text-align:center; background-color:#F7D3D2;">
            There is no data available</div>
    <?php } ?>
<?php } ?>