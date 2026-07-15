<style>
    #gross_salary_period1 td,
    th {
        border-style: solid;
        border-color: #d4d4de;
    }

    #gross_salary_period2 td,
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
            <div>

                <h2 style="font-weight: bold;text-align: center;"> <?php echo "Gross Salary Period Wise - " . $month ?> </h2>
                <h2 style="font-size:16px;text-align: center;font-weight: bold;font-size: 19px;"><?php echo "(Report Run by " . $user_id . " at "  . $date_time . ")" ?> </h2>
                <?php if (count($gross) <= 0) { ?>
                    <div style="font-size: 16px;text-align:left;">
                        No data available under the selected criteria</div>
                    <?php
                } else {
                    if ($criteria == "Units") //***********BRANCH WISE***********************THIS BRANCH WISE SECTION BY ARUL P DAS 26/10/2019*************
                    {
                    ?>
                        <!-- <h3 align="center"><b>Gross Salary Period Wise : <?php echo $month; ?></b></h3> -->
                        <!-- <h2 style="font-weight: bold;text-align: center;"> <?php echo "Gross Salary Period Wise - " . $month ?> </h2>
                    <h2 style="font-size:16px;text-align: center;font-weight: bold;font-size: 19px;"><?php echo "(Report Run by " . $user_id . " at "  . $date_time . ")" ?> </h2> -->
                        <?php $coun1 = count($array_key['Addition']);
                        $coun2 = count($array_key['Deduction']);
                        $cont = $coun1 + $coun2;
                        $cont2 = $cont + 4;

                        $branch_array = array();
                        // debug($branches);
                        foreach ($branches as $key1 => $branch) {
                            foreach ($branch as $key2 => $each_branch) {
                                // echo $each_branch['branch_name']."<br>";
                                $branch_array[$each_branch['branch_code']] = $each_branch['branch_name'];
                            }
                        }

                        foreach ($gross as $key3 => $branch_code) {
                            // echo "<legend>".$branch_array[$key3]."</legend><br>";

                        ?>
                            <div style="overflow-x: auto; overflow-y:auto">
                                <legend style="border: 0;"><?php echo $branch_array[$key3]; ?></legend>
                                <fieldset>
                                    <table class="table table-bordered" id="gross_salary_period1">
                                        <thead>
                                            <tr>
                                               <!-- edited by athira on 08-07-2025 -->
                                                 <?php if($company_code =='DEMO' || $company_code =='GLET' || $company_code=='SRTS'){ ?> 
                                                    <th style="text-align: center;" colspan="13">Employee Details</th>  
                                                    <?php } else { ?>
                                                        <th style="text-align: center;" colspan="11">Employee Details</th>
                                                        <?php } ?>
                                                <!-- end -->
                                                <!--<th colspan="<?php echo $cont; ?>">Standard Salary</th>-->
                                                <th style="text-align: center;" colspan="<?php echo $cont2; ?>">Actual Salary</th>
                                            </tr>
                                            <tr>
                                                <th>Sl No</th>
                                                <th>Employee ID</th>
                                                <!-- edited by athira on 08-07-2025 -->
                                                 <?php if($company_code =='DEMO' || $company_code =='GLET' || $company_code=='SRTS'){ ?>   
                                                    <th>Employee ID (US Format)</th>
                                                    <?php } ?>
                                                    <!-- end -->
                                                <th>Company ID</th>
                                                <th>Employee Name</th>
                                                <!-- edited by athira on 08-07-2025 -->
                                                 <?php if($company_code =='DEMO' || $company_code =='GLET' || $company_code=='SRTS'){ ?>   
                                                    <th>Employee Name (US Format)</th>
                                                    <?php } ?>
                                                    <!-- end -->
                                                <th> Gender</th>
                                                <th>Joining Date</th>
                                                <th>Branch</th>

                                                <th>Department</th>
                                                <th>Designation</th>

                                                <th>Termination Date</th>
                                                <th>Present Day Count</th>
                                                <!-- <th>Branch</th>     -->
                                                <?php
                                                $addition = $array_key['Addition'];
                                                $addition_count = count($addition);
                                                foreach ($addition as $value) { ?>
                                                    <th><?php echo $value; ?></th>
                                                <?php
                                                } ?>
                                                <th>Gross Salary</th>
                                                <?php $deduction = $array_key['Deduction'];
                                                $deduction_count = count($deduction);
                                                foreach ($deduction as $value) {
                                                ?>
                                                    <th><?php echo $value; ?></th>
                                                <?php
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
                                            // debug($gross);
                                            // debug($branch_code);
                                            // exit();
                                            foreach ($branch_code as $val) {  ?>
                                                <tr>
                                                    <td><?php echo $i; ?></td>
                                                    <td><?php echo $val['emp_info']['emp_id']; ?></td>
                                                    <!-- edited by athira on 08-07-2025 -->
                                                    <?php  if($company_code =='DEMO' || $company_code =='SRTS' || $company_code=='GLET') { ?>
                                             <td><?php echo $val['emp_info']['emp_us_id']; ?></td>
                                        <?php } ?>
                                        <!-- end -->
                                                    <td><?php echo $val['emp_info']['employee_id']; ?></td>
                                                    <!-- edited by athira on 20-02-2025 -->
                                                    <td><?php if (!empty($val['payroll_master']['emp_name'])) {
                                                            echo $val['payroll_master']['emp_name'];
                                                        } else {
                                                            echo $val['emp_info']['EmpName'];
                                                        } ?>
                                                        <?php echo (isset($val['emp_details']['status'])) && $val['emp_details']['status'] == "2" ? '  (Resigned)' : ''; ?></td>
                                                       
                                                        <!-- edited by athira on 08-07-2025 -->
                                                        <?php  if($company_code =='DEMO' || $company_code =='SRTS' || $company_code=='GLET') { ?>
                                             <td><?php echo $val['emp_info']['EmpUSName']; ?></td>
                                        <?php } ?>
                                        <!-- end -->


                                                    <td><?php echo strtoupper($val['emp_details']['classification']); ?></td>

                                                    <td><?php if (!empty($val['payroll_master']['joining_date']) && $val['payroll_master']['joining_date'] != '0000-00-00') {
                                                            echo date('d-m-Y', strtotime($val['payroll_master']['joining_date']));
                                                        } else {
                                                            echo date('d-m-Y', strtotime($val['emp_info']['joining_date']));
                                                        } ?> </td>
                                                    <td><?php if (!empty($val['payroll_master']['branch_name'])) {
                                                            echo $val['payroll_master']['branch_name'];
                                                        } else {
                                                            echo $val['emp_info']['branch'];
                                                        } ?></td>
                                                    <td><?php if (!empty($val['payroll_master']['departments'])) {
                                                            echo $val['payroll_master']['departments'];
                                                        } else {
                                                            echo $val['emp_info']['department'];
                                                        } ?></td>
                                                    </td>
                                                    <td><?php if (!empty($val['payroll_master']['desig'])) {
                                                            echo $val['payroll_master']['desig'];
                                                        } else {
                                                            echo $val['emp_info']['designation'];
                                                        } ?></td>
                                                    <!-- end -->
                                                    <td><?php echo $val['termination']['last_approved_working_date']; ?></td>
                                                    <td><?php echo $val['presentcount']; ?></td>
                                                    <!-- <td><?php echo $val['emp_info']['branch']; ?></td>                                 -->
                                                    <?php $count_addition = count($val['Addition']['keys']);
                                                    $grss_amt = 0;
                                                    for ($m = 0; $m < $addition_count; $m++) {
                                                        $number = round($val['Addition']['value'][$m]);
                                                        $grss_amt = $number + $grss_amt;
                                                    ?>
                                                        <td><?php echo round($number); ?></td>
                                                    <?php
                                                    } ?>
                                                    <td><?php echo round($grss_amt, 2); ?></td>
                                                    <?php $count_addition = count($val['Deduction']['keys']);

                                                    $dd_amt = 0;
                                                    for ($m = 0; $m < $deduction_count; $m++) {
                                                        $number = round($val['Deduction']['value'][$m], 2);
                                                        $dd_amt = $number + $dd_amt;
                                                    ?>
                                                        <td><?php echo round($number, 2); ?></td>
                                                    <?php
                                                    }
                                                    $netamt = $grss_amt + $dd_amt + $val['settle']; //Added settlement amount by **ARUL P DAS on 11/12/2019
                                                    ?>
                                                    <td><?php echo round($dd_amt, 2); ?></td>
                                                    <td><?php echo round($val['settle']); ?></td>
                                                    <td><?php echo round($netamt); ?></td>
                                                </tr>
                                            <?php
                                                $i++;
                                            } ?>
                                        </tbody>
                                    </table>
                                </fieldset>
                            </div>
                            <br>
                        <?php
                            //  $i++;
                        } //Closing of foreach loop
                        // debug($gross);
                    } else { //Ending of Branch wise and Starting of Employee wise.
                        ?>
                        <!-- <h3 align="center"><b>Gross Salary Period Wise : <?php echo $month; ?></b></h3> -->
                        <!-- <h2 style="font-weight: bold;text-align: center;"> <?php echo "Gross Salary Period Wise - " . $month ?> </h2>
                    <h2 style="font-size:16px;text-align: center;font-weight: bold;font-size: 19px;"><?php echo "(Report Run by " . $user_id . " at "  . $date_time . ")" ?> </h2> -->
                        <?php $coun1 = count($array_key['Addition']);
                        $coun2 = count($array_key['Deduction']);
                        $cont = $coun1 + $coun2;
                        $cont2 = $cont + 4;
                        // $i=1;
                        // debug($gross);
                        foreach ($gross as $val) {
                            $i = 1;
                        ?>
                            <div style="overflow-x: auto;">
                                <legend style="border: 0;"><?php echo $val['emp_info']['EmpName']; ?><?php echo (isset($val['emp_details']['status'])) && $val['emp_details']['status'] == "2" ? '  (Resigned)' : ''; ?></legend>
                                <fieldset style="display: inline-block; width: auto;">
                                    <table class="table table-bordered" id="gross_salary_period2">
                                        <thead>
                                            <tr>
                                               <!-- edited by athira on 08-07-2025 -->
                                                 <?php if($company_code =='DEMO' || $company_code =='GLET' || $company_code=='SRTS'){ ?> 
                                                    <th style="text-align: center;" colspan="13">Employee Details</th>  
                                                    <?php } else { ?>
                                                        <th style="text-align: center;" colspan="11">Employee Details</th>
                                                        <?php } ?>
                                                <!-- end -->
                                                <!--<th colspan="<?php echo $cont; ?>">Standard Salary</th>-->
                                                <th style="text-align: center;" colspan="<?php echo $cont2; ?>">Actual Salary</th>
                                            </tr>
                                            <tr>
                                                <th>Sl No</th>
                                                <th>Employee ID</th>
                                                 <!-- edited by athira on 08-07-2025 -->
                                                 <?php if($company_code =='DEMO' || $company_code =='GLET' || $company_code=='SRTS'){ ?>   
                                                    <th>Employee ID (US Format)</th>
                                                    <?php } ?>
                                                    <!-- end -->
                                                <th>Company ID</th>
                                                <th>Employee Name</th>
                                                 <!-- edited by athira on 08-07-2025 -->
                                                 <?php if($company_code =='DEMO' || $company_code =='GLET' || $company_code=='SRTS'){ ?>   
                                                    <th>Employee Name (US Format)</th>
                                                    <?php } ?>
                                                    <!-- end -->
                                                <th>Gender</th>
                                                <th>Joining Date</th>
                                                <th>Branch</th>
                                                <th>Department</th>
                                                <th>Designation</th>

                                                <th>Termination Date</th>
                                                <th>Present Day Count</th>
                                                <?php
                                                $addition = $array_key['Addition'];
                                                //debug($addition);
                                                foreach ($addition as $value) { ?>
                                                    <th><?php echo $value; ?></th>
                                                <?php
                                                } ?>
                                                <th>Gross Salary</th>
                                                <?php $deduction = $array_key['Deduction'];
                                                foreach ($deduction as $value) {
                                                ?>
                                                    <th><?php echo $value; ?></th>
                                                <?php
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
                                            // $i=1;
                                            // // debug($gross);
                                            // foreach($gross as $val)
                                            // {
                                            ?>
                                            <tr>
                                                <td><?php echo $i; ?></td>
                                                <td><?php echo $val['emp_info']['emp_id']; ?></td>
                                                <!-- edited by athira on 08-07-2025 -->
                                                <?php  if($company_code =='DEMO' || $company_code =='SRTS' || $company_code=='GLET') { ?>
                                             <td><?php echo $val['emp_info']['emp_us_id']; ?></td>
                                        <?php } ?>
                                        <!-- end -->
                                                <td><?php echo $val['emp_info']['employee_id']; ?></td>
                                                <!-- edited by athira on 20-02-2025 -->
                                                <td><?php if (!empty($val['payroll_master']['emp_name'])) {
                                                        echo $val['payroll_master']['emp_name'];
                                                    } else {
                                                        echo $val['emp_info']['EmpName'];
                                                    } ?><?php echo (isset($val['emp_details']['status'])) && $val['emp_details']['status'] == "2" ? '  (Resigned)' : ''; ?></td>

                                                  <!-- edited by athira on 08-07-2025 -->
                                                    <?php  if($company_code =='DEMO' || $company_code =='SRTS' || $company_code=='GLET') { ?>
                                             <td><?php echo $val['emp_info']['EmpUSName']; ?></td>
                                        <?php } ?>
                                        <!-- end -->

                                                <td><?php echo strtoupper($val['emp_details']['classification']); ?></td>
                                                <td><?php if (!empty($val['payroll_master']['joining_date']) && $val['payroll_master']['joining_date'] != '0000-00-00') {
                                                        echo date('d-m-Y', strtotime($val['payroll_master']['joining_date']));
                                                    } else {
                                                        echo date('d-m-Y', strtotime($val['emp_info']['joining_date']));
                                                    } ?></td>
                                                <td><?php if (!empty($val['payroll_master']['branch_name'])) {
                                                        echo $val['payroll_master']['branch_name'];
                                                    } else {
                                                        echo $val['emp_info']['branch'];
                                                    } ?></td>
                                                <td><?php if (!empty($val['payroll_master']['departments'])) {
                                                        echo $val['payroll_master']['departments'];
                                                    } else {
                                                        echo $val['emp_info']['department'];
                                                    } ?></td>
                                                <td><?php if (!empty($val['payroll_master']['desig'])) {
                                                        echo $val['payroll_master']['desig'];
                                                    } else {
                                                        echo $val['emp_info']['designation'];
                                                    } ?></td>
                                                <!-- end -->
                                                <td><?php echo $val['termination']['last_approved_working_date']; ?></td>
                                                <td><?php echo $val['presentcount']; ?></td>


                                                <?php $count_addition = count($val['Addition']['keys']);
                                                $grss_amt = 0;
                                                for ($m = 0; $m < $count_addition; $m++) {
                                                    $number = round($val['Addition']['value'][$m], 2);
                                                    $grss_amt = $number + $grss_amt;
                                                ?>
                                                    <td><?php echo round($number); ?></td>
                                                <?php
                                                } ?>
                                                <td><?php echo round($grss_amt); ?></td>
                                                <?php $count_addition = count($val['Deduction']['keys']);

                                                $dd_amt = 0;
                                                for ($m = 0; $m < $count_addition; $m++) {
                                                    $number = round($val['Deduction']['value'][$m], 2);
                                                    $dd_amt = $number + $dd_amt;
                                                ?>
                                                    <td><?php echo round($number, 2); ?></td>

                                                    <!-- //edited by megha on 13/11/2019 settlement amount 6-->
                                                <?php }


                                                $netamt = $grss_amt + $dd_amt + $val['settle'];
                                                ?>


                                                <td><?php echo round($dd_amt, 2); ?></td>
                                                <!-- //edited by megha on 13/11/2019 settlement amount 6-->
                                                <td><?php echo round($val['settle']); ?></td>
                                                <td><?php echo round($netamt); ?></td>
                                            </tr>
                                            <?php
                                            //     $i++;
                                            // }

                                            ?>
                                        </tbody>
                                    </table>
                                </fieldset>
                            </div>
                            <br>
                    <?php
                            $i++;
                        }
                    } //Closing of Employee wise condition
                    ?>
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
<?php
                }
            } else { //This is the else case that the mode is not null. That is it can be pdf 
?>
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
    }
</style>

<?php
                echo $this->element('reportadminheader', array('title' => 'Gross Salary Period Wise Report'));
?>
<?php if (count($gross) <= 0) {
?>
    <div style="font-size: 25px;text-align:center; background-color:#F7D3D2;">
        There is no data available with respect to your report</div>
    <?php
                } else { //This is the else case of gross 
                    if ($criteria == "Units") //*********THIS BRANCH WISE SECTION BY ARUL P DAS 26/10/2019*************
                    {
    ?>
        <h3 align="center"><b>Gross Salary Period Wise: <?php echo $month; ?></b></h3>
        <?php $coun1 = count($array_key['Addition']);
                        $coun2 = count($array_key['Deduction']);
                        $cont = $coun1 + $coun2;
                        $cont2 = $cont + 3;

                        $branch_array = array();
                        // debug($branches);
                        foreach ($branches as $key1 => $branch) {
                            foreach ($branch as $key2 => $each_branch) {
                                // echo $each_branch['branch_name']."<br>";
                                $branch_array[$each_branch['branch_code']] = $each_branch['branch_name'];
                            }
                        }
                        foreach ($gross as $key3 => $branch_code) {
                            echo "<h3>" . $branch_array[$key3] . "</h3><br>";
        ?>
            <table class="table">
                <thead>
                    <tr>
                        <th colspan="5">Employee Details</th>
                        <!--<th colspan="<?php echo $cont; ?>">Standard Salary</th>-->
                        <th colspan="<?php echo $cont2; ?>">Actual Salary</th>
                    </tr>
                    <tr style="background-color:#f0f0ff;">
                        <th>Sl No</th>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Designation</th>
                        <th>Department</th>
                        <!-- <th>Branch</th>     -->
                        <?php
                            $addition = $array_key['Addition'];
                            //debug($addition);
                            foreach ($addition as $value) { ?>
                            <th><?php echo $value; ?></th>
                        <?php
                            } ?>
                        <th>Gross Salary</th>
                        <?php $deduction = $array_key['Deduction'];
                            foreach ($deduction as $value) {
                        ?>
                            <th><?php echo $value; ?></th>
                        <?php
                            }
                        ?>
                        <th>Total Deduction</th>
                        <th>Net Salary</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                            $i = 1;
                            // debug($gross);
                            foreach ($branch_code as $val) { ?>
                        <tr>
                            <td><?php echo $i; ?></td>
                            <td><?php echo $val['emp_info']['employee_id']; ?></td>
                            <td><?php echo $val['emp_info']['EmpName']; ?><?php echo (isset($val['emp_details']['status'])) && $val['emp_details']['status'] == "2" ? '  (Resigned)' : ''; ?></td>
                            <td><?php echo $val['emp_info']['designation']; ?></td>
                            <td><?php echo $val['emp_info']['department']; ?></td>
                            <!-- <td><?php echo $val['emp_info']['branch']; ?></td>                                 -->
                            <?php $count_addition = count($val['Addition']['keys']);
                                $grss_amt = 0;
                                for ($m = 0; $m < $count_addition; $m++) {
                                    $number = round($val['Addition']['value'][$m], 2);
                                    $grss_amt = $number + $grss_amt;
                            ?>
                                <td><?php echo round($number); ?></td>
                            <?php
                                } ?>
                            <td><?php echo round($grss_amt); ?></td>
                            <?php $count_addition = count($val['Deduction']['keys']);

                                $dd_amt = 0;
                                for ($m = 0; $m < $count_addition; $m++) {
                                    $number = round($val['Deduction']['value'][$m], 2);
                                    $dd_amt = $number + $dd_amt;
                            ?>
                                <td><?php echo round($number); ?></td>
                            <?php
                                }
                                $netamt = $grss_amt + $dd_amt;
                            ?>
                            <td><?php echo $dd_amt; ?></td>
                            <td><?php echo round($netamt); ?></td>
                        </tr>
                    <?php
                                $i++;
                            } ?>
                </tbody>
            </table>
        <?php
                        } //Closing of foreach loop
                    } else //This is the else case of Unit or Employee checking
                    {
        ?>
        <br><br>
        <br><br>
        <h3><?php echo $month; ?></h3>
        <?php $coun1 = count($array_key['Addition']);
                        $coun2 = count($array_key['Deduction']);
                        $cont = $coun1 + $coun2 + 1;
                        $cont2 = $cont + 3;
        ?>
        <table class="table table-bordered">
            <tr>
                <th colspan="6">Employee Details</th>
                <th colspan="<?php // echo $cont;
                                ?>">Standard Salary</th>
                <th colspan="<?php echo $cont2; ?>">Actual Salary</th>

            </tr>
            <tr style="background-color:#f0f0ff;">
                <th>Sl No</th>
                <th>ID</th>
                <th>Name</th>
                <th>Designation</th>
                <th>Department</th>
                <th>Branch</th>
                <?php
                        $addition = $array_key['Addition'];
                        //debug($addition);
                        foreach ($addition as $value) { ?>
                    <th><?php echo $value; ?></th>
                <?php
                        } ?>
                <th>Gross Salary</th>
                <?php $deduction = $array_key['Deduction'];
                        foreach ($deduction as $value) {
                ?>
                    <th><?php echo $value; ?></th>
                <?php
                        }
                ?>
                <th>Total Deduction</th>
                <th>Net Salary</th>
            </tr>
            <tbody>
                <?php
                        $i = 1;
                        // debug($gross);
                        foreach ($gross as $val) { ?>
                    <tr>
                        <td><?php echo $i; ?></td>
                        <td><?php echo $val['emp_info']['employee_id']; ?></td>
                        <td><?php echo $val['emp_info']['EmpName']; ?><?php // echo (isset($val['emp_details']['status'])) && $val['emp_details']['status'] =="2" ? '  (Resigned)':'';
                                                                        ?></td>
                        <td><?php echo $val['emp_info']['designation']; ?></td>
                        <td><?php echo $val['emp_info']['department']; ?></td>
                        <td><?php echo $val['emp_info']['branch']; ?></td>


                        <?php $count_addition = count($val['Addition']['keys']);
                            $grss_amt = 0;
                            for ($m = 0; $m < $count_addition; $m++) {
                                $number = round($val['Addition']['value'][$m], 2);
                                $grss_amt = $number + $grss_amt;
                        ?>
                            <td><?php echo round($number); ?></td>
                        <?php
                            } ?>
                        <td><?php echo round($grss_amt); ?></td>
                        <?php $count_addition = count($val['Deduction']['keys']);
                            $dd_amt = 0;
                            for ($m = 0; $m < $count_addition; $m++) {
                                $number = round($val['Deduction']['value'][$m], 2);
                                $dd_amt = $number + $dd_amt;
                        ?>
                            <td><?php echo round($number); ?></td>
                        <?php
                            }
                            $netamt = $grss_amt + $dd_amt;
                        ?>
                        <td><?php echo $dd_amt; ?></td>
                        <td><?php echo round($netamt); ?></td>
                    </tr>
                <?php
                            $i++;
                        }
                ?>
            </tbody>
        </table>
<?php
                    } //This is the ending of Employee wise.
                } //This is the checking of gross NULL or not.
            } //This is the closing of checking mode is null of pdf
?>