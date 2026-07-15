<style>
    .table,
    td,
    th,
    tr {
        border-style: solid;
        border-color: #d4d4de;
    }

    .modal-content {
        width: 125% !important;

    }
</style>

<?php // debug($gross);   
?>
<?php if ($mode == '') { ?>
    <div class="modal-body" style="overflow-y:auto; padding-left:3%; padding-right:3%; padding-bottom:3%;">

        <div class="row">
            <div class="col-md-12">

                <?php if (count($gross) <= 0) { ?>
                    <div style="font-size: 25px;text-align:center; background-color:#F7D3D2;">
                        There is no data available with respect to your report</div>
                <?php } else { ?>
                    <h3 align="center"><b>Gross Salary Summary Report - <?php echo $month; ?></b></h3>
                    <?php
                    if ($criteria == "Units") { //***********BRANCH WISE***********************THIS BRANCH WISE SECTION BY ARUL P DAS 16/10/2019*************
                        $loop = 0;
                        // debug($branches);
                        $all_branches = array();
                        // foreach ($branches as $key => $value) {
                        //    $all_branches[$value['branches']['branch_code']] = $value['branches']['branch_name']; //This is the all branches.which have values like, array('branch_code'=>'branch_name'). ie, array('DEMO01'=>'Forsight','DEMO02'=>'Great Leap').
                        // }
                        // debug($all_branches);
                        foreach ($gross as $emp_branch => $branch_wise_values) { //Here the emp_branch contain the branch code from gross.
                            $branches = current($branch_wise_values);
                            // debug($branches['emp_info']['branch']);
                            // echo '<h3>' .$emp_branch . '</h3>';
                    ?>


                            <legend style="border: 0;"><?php echo isset($branches['payroll_master']['branch_name']) ? $branches['payroll_master']['branch_name'] : (isset($branches['emp_info']['branch']) ? $branches['emp_info']['branch'] : ''); ?></legend>

                            <table class="table">
                                <tbody>
                                    <tr style="background-color: #f0f0ff;">
                                        <th>Sl No</th>
                                        <th>Employee ID</th>
                                        <!-- edited by athira on 08-07-2025 -->
                                        <?php  if($company_code =='DEMO' || $company_code =='SRTS' || $company_code=='GLET') { ?>
                                            <th>Employee ID (US Format)</th>
                                        <?php } ?>
                                        <!-- end -->
                                        <th>Name</th>
                                        <!-- edited by athira on 08-07-2025 -->
                                        <?php  if($company_code =='DEMO' || $company_code =='SRTS' || $company_code=='GLET') { ?>
                                            <th>Employee Name (US Format)</th>
                                        <?php } ?>
                                        <!-- end -->
                                        <th>Designation</th>
                                        <th>Department</th>
                                        <!-- Edited by Akshay on 20-6-2025 -->
                                        <th>Branch</th>
                                        <!-- End -->
                                        <th>Present Days</th>
                                        <th>Leave Days</th>
                                        <th>LOP Days </th>
                                        <th>Week Off</th>
                                        <th>Holiday</th>
                                        <!--<th>Overtime (In Hrs.)</th>-->
                                        <th>Gross Salary</th>
                                        <th>Total Deduction</th>
                                        <th>Settlement Amount</th>
                                        <th>Net Salary</th>
                                    </tr>
                                    <?php
                                    $i = 1;
                                    $temp = '';
                                    foreach ($branch_wise_values as $emp_key => $emp_wise_values) { //The emp_key contain employee id of the current selected branch
                                        // debug($emp_wise_values);
                                        // echo $emp_branch;
                                        //  if ($temp != $all_branches[$emp_branch]) {//This is for checking branchname repeats or not. If it repeats, it will not shown on output.
                                        //                            echo "<h3>".$all_branches[$emp_branch]."</h3>";
                                        //  }
                                        //                        debug($emp_wise_values);
                                    ?>
                                        <tr>
                                            <td><?php echo $i; ?></td>
                                            <td><?php echo $emp_wise_values['emp_info']['employee_id']; ?></td>
                                            <!-- edited by athira on 08-07-2025 -->
                                            <?php  if($company_code =='DEMO' || $company_code =='SRTS' || $company_code=='GLET') { ?>
                                             <td><?php echo $emp_wise_values['emp_info']['emp_us_id']; ?></td>
                                        <?php } ?>
                                        <!-- end -->
                                            <!-- edited by ASHIN on 10-01-25-->
                                            <!-- <td>
                                                </?php echo $emp_wise_values['emp_info']['EmpName']; ?>
                                                </?php echo (isset($emp_wise_values['emp_details']['status'])) && $emp_wise_values['emp_details']['status'] == "2" ? '  (Resigned)' : ''; ?>
                                            </td> -->
                                            <td><?php if (!empty($emp_wise_values['payroll_master']['emp_name'])) {
                                                    echo $emp_wise_values['payroll_master']['emp_name'];
                                                } else {
                                                    echo $emp_wise_values['emp_info']['EmpName'];
                                                }
                                                echo (isset($emp_wise_values['emp_details']['status'])) && $emp_wise_values['emp_details']['status'] == "2" ? '  (Resigned)' : ''; ?></td>

                                                <!-- edited by athira on 08-07-2025 -->
                                            <?php  if($company_code =='DEMO' || $company_code =='SRTS' || $company_code=='GLET') { ?>
                                             <td><?php echo $emp_wise_values['emp_info']['EmpUSName']; ?></td>
                                        <?php } ?>
                                        <!-- end -->

                                            <!-- <td></?php echo $emp_wise_values['emp_info']['designation']; ?></td> -->
                                            <td><?php if (!empty($emp_wise_values['payroll_master']['desig'])) {
                                                    echo $emp_wise_values['payroll_master']['desig'];
                                                } else {
                                                    echo $emp_wise_values['emp_info']['designation'];
                                                } ?></td>

                                            <!-- <td><//?php echo $emp_wise_values['emp_info']['department']; ?></td> -->
                                            <td><?php if (!empty($emp_wise_values['payroll_master']['departments'])) {
                                                    echo $emp_wise_values['payroll_master']['departments'];
                                                } else {
                                                    echo $emp_wise_values['emp_info']['department'];
                                                } ?></td>

                                            <td><?php if (!empty($emp_wise_values['payroll_master']['branch_name'])) {
                                                    echo $emp_wise_values['payroll_master']['branch_name'];
                                                } else {
                                                    echo $emp_wise_values['emp_info']['branch'];
                                                } ?></td>
                                            <td><?php echo $emp_wise_values['ectc']['presant_total']; ?></td>
                                            <td><?php echo $emp_wise_values['ectc']['leave_total']; ?></td>
                                            <td><?php echo $emp_wise_values['ectc']['lop_total']; ?></td>
                                            <td><?php echo $emp_wise_values['ar']['weekoff_total']; ?></td>
                                            <td><?php echo $emp_wise_values['ar']['holiday_total']; ?></td>

                                            <!--<td><?php // echo isset($emp_wise_values['ot']) ? round(($emp_wise_values['ot'] / 60), 2) :0;   
                                                    ?></td>-->
                                            <?php
                                            $count_addition = count($emp_wise_values['Addition']['keys']);
                                            // echo $count_addition;
                                            $grss_amt = 0;
                                            for ($m = 0; $m < $count_addition; $m++) {
                                                $number = round($emp_wise_values['Addition']['value'][$m], 2);
                                                $grss_amt = $number + $grss_amt;
                                            }
                                            ?>
                                            <td><?php echo round($grss_amt); ?></td>
                                            <?php
                                            $count_addition = count($emp_wise_values['Deduction']['keys']);
                                            // echo $count_addition;
                                            $dd_amt = 0;
                                            for ($m = 0; $m < $count_addition; $m++) {
                                                $number = round($emp_wise_values['Deduction']['value'][$m], 2);
                                                $dd_amt = $number + $dd_amt;
                                            }
                                            $netamt = $grss_amt + $dd_amt + $emp_wise_values['settle'];
                                            ?>
                                            <td><?php echo round($dd_amt, 2); ?></td>
                                            <td><?php echo round($emp_wise_values['settle']); ?></td>
                                            <td><?php echo round($netamt); ?></td>
                                        </tr>
                                    <?php
                                        //  $temp = $all_branches[$emp_branch];
                                        $i++;
                                    } //This is the closing of employee wise foreach loop.
                                    ?>
                                </tbody>
                            </table>
                        <?php
                        } //This is the closing of branch wise foreach loop.
                    } else {
                        ?>
                        <table class="table ">
                            <tbody>
                                <tr style="background-color:#f0f0ff;">
                                    <th>Sl No</th>
                                    <th>Employee ID</th>
                                    <!-- edited by athira on 08-07-2025 -->
                                    <?php  if($company_code =='DEMO' || $company_code =='SRTS' || $company_code=='GLET') { ?>
                                            <th>Employee ID (US Format)</th>
                                        <?php } ?>
                                        <!-- end -->
                                    <th>Name</th>
                                    <!-- edited by athira on 08-07-2025 -->
                                    <?php  if($company_code =='DEMO' || $company_code =='SRTS' || $company_code=='GLET') { ?>
                                            <th>Employee Name (US Format)</th>
                                        <?php } ?>
                                        <!-- end -->
                                    <th>Designation</th>
                                    <th>Department</th>
                                    <th>Branch</th>
                                    <th>Present Days</th>
                                    <th>Leave Days</th>
                                    <th>LOP Days </th>
                                    <th>Week Off</th>
                                    <th>Holiday</th>
                                    <!--<th>Overtime (In Hrs.)</th>-->
                                    <th>Gross Salary</th>
                                    <th>Total Deduction</th>
                                    <!--  //edited by megha on 13/11/2019 settlement amount 1-->
                                    <th>Settlement Amount</th>
                                    <th>Net Salary</th>



                                </tr>
                                <?php
                                $i = 1;
                                // debug($gross);
                                foreach ($gross as $val) {
                                ?>

                                    <tr>
                                        <td><?php echo $i; ?></td>
                                        <td><?php echo $val['emp_info']['employee_id']; ?></td>
                                        <!-- edited by athira on 08-07-2025 -->
                                        <?php  if($company_code =='DEMO' || $company_code =='SRTS' || $company_code=='GLET') { ?>
                                             <td><?php echo $val['emp_info']['emp_us_id']; ?></td>
                                        <?php } ?>
                                        <!-- end -->
                                        <!--edited by ashin 10-01-25--->
                                        <!-- <td><//?php echo $val['emp_info']['EmpName']; ?></?php echo (isset($val['emp_details']['status'])) && $val['emp_details']['status'] == "2" ? '  (Resigned)' : ''; ?></td> -->
                                        <td><?php if (!empty($val['payroll_master']['emp_name'])) {
                                                echo $val['payroll_master']['emp_name'];
                                            } else {
                                                echo  $val['emp_info']['EmpName'];
                                            }
                                            echo (isset($val['emp_details']['status'])) &&  $val['emp_details']['status'] == "2" ? '  (Resigned)' : ''; ?></td>

                                            <!-- edited by athira on 08-07-2025 -->

                                             <?php  if($company_code =='DEMO' || $company_code =='SRTS' || $company_code=='GLET') { ?>
                                             <td><?php echo $val['emp_info']['EmpUSName']; ?></td>
                                        <?php } ?>
                                        <!-- end -->

                                        <!-- <td></?php echo $val['emp_info']['designation']; ?></td> -->
                                        <td><?php if (!empty($val['payroll_master']['desig'])) {
                                                echo  $val['payroll_master']['desig'];
                                            } else {
                                                echo  $val['emp_info']['designation'];
                                            } ?></td>

                                        <!-- <td></?php echo $val['emp_info']['department']; ?></td> -->
                                        <td><?php if (!empty($val['payroll_master']['departments'])) {
                                                echo  $val['payroll_master']['departments'];
                                            } else {
                                                echo  $val['emp_info']['department'];
                                            } ?></td>

                                        <!-- <td></?php echo $val['emp_info']['branch']; ?></td> -->
                                        <td><?php if (!empty($val['payroll_master']['branch_name'])) {
                                                echo  $val['payroll_master']['branch_name'];
                                            } else {
                                                echo  $val['emp_info']['branch'];
                                            } ?></td>
                                        <td><?php echo $val['ectc']['presant_total']; ?></td>
                                        <td><?php echo $val['ectc']['leave_total']; ?></td>
                                        <td><?php echo $val['ectc']['lop_total']; ?></td>
                                        <td><?php echo $val['ar']['weekoff_total']; ?></td>
                                        <td><?php echo $val['ar']['holiday_total']; ?></td>
                                        <!--<td><?php // echo isset($val['ot']) ? round(($val['ot'] / 60), 2) :0;   
                                                ?></td>-->
                                        <?php
                                        $count_addition = count($val['Addition']['keys']);
                                        $grss_amt = 0;
                                        for ($m = 0; $m < $count_addition; $m++) {
                                            $number = round($val['Addition']['value'][$m], 2);
                                            $grss_amt = $number + $grss_amt;
                                        ?>

                                        <?php } ?>
                                        <td><?php echo round($grss_amt); ?></td>
                                        <?php
                                        $count_addition = count($val['Deduction']['keys']);

                                        $dd_amt = 0;
                                        for ($m = 0; $m < $count_addition; $m++) {
                                            $number = round($val['Deduction']['value'][$m], 2);
                                            $dd_amt = $number + $dd_amt;
                                        ?>


                                        <?php
                                        }


                                        $netamt = $grss_amt + $dd_amt + $val['settle'];
                                        ?>


                                        <td><?php echo round($dd_amt, 2); ?></td>
                                        <td><?php echo round($val['settle']); ?></td>
                                        <td><?php echo round($netamt); ?></td>


                                    </tr>


                                <?php
                                    $i++;
                                }
                                ?>

                            </tbody>
                        </table>
                    <?php
                    }
                    ?>

            </div>
        </div>
        <!--div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel </button>  
            </div-->
        <!-- <div class="row">
                 <div class="form-group">
                     <div class="col-md-12" align="right">
                         <a href="#" class="btn btn-default" onclick="downloadReport('Grosssalary', 'pdf');" ><i class="icon-file"></i>Download As PDF</a>
                         <a href="#" class="btn btn-default" onclick="downloadReport('Grosssalary', 'excel');"><i class="icon-file"></i>Download As Excel</a>
                     </div>
                 </div>
             </div> -->
    </div>
<?php }
?>
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
    echo $this->element('reportadminheader', array(
        'title' => 'Gross Salary Summary Report - ' . $month
    ));
    ?>
    <?php if (count($gross) <= 0) { ?>
        <div style="font-size: 25px;text-align:center; background-color:#F7D3D2;">
            There is no data available with respect to your report</div>
    <?php } else { ?>
        
        <!--        <h3 align="center"  ><b>Gross Salary Summary Report - <?php echo $month; ?></b></h3>-->
        <?php
        if ($criteria == "Units") { //***********BRANCH WISE***********************THIS BRANCH WISE SECTION BY ARUL P DAS 16/10/2019*************
            $loop = 0;
            // debug($branches);
            //edited by ashin 10-01-25---> 
            $all_branches = array();
            // foreach ($branches as $key => $value) {
            //     $all_branches[$value['payroll_master']['branch_code']] = $value['branches']['branch_name']; //This is the all branches.which have values like, array('branch_code'=>'branch_name'). ie, array('DEMO01'=>'Forsight','DEMO02'=>'Great Leap').
            // }
            // debug($all_branches);
            foreach ($gross as $emp_branch => $branch_wise_values) { //Here the emp_branch contain the branch code from gross.
              //  echo '<h3>' . $all_branches[$emp_branch] . '</h3>';
               //$branches = current($branch_wise_values);
             // echo '<h3>' . isset($branch_wise_values['0']['payroll_master']['branch_name']) ? $branch_wise_values['0']['payroll_master']['branch_name'] : (isset($branch_wise_values['0']['emp_info']['branch']) ? $branch_wise_values['0']['emp_info']['branch'] : ''). '</h3>';
        ?><br><br>
        <br><br>
                <table class="table">
                    <tbody>
                        <tr style="background-color: #f0f0ff;">
                            <th style="width: 3%;">Sl No</th>
                            <th style="width: 9%;">Employee ID</th>
                            <th style="width: 12%;">Employee Name</th>
                            <th style="width: 10%;">Designation</th>
                            <th style="width: 10%;">Department</th>
                            <!-- edited by athira on 20-06-2025 -->
                            <th style="width: 5%;">Branch</th>
                            <!-- end -->
                            <th style="width: 5%;">Present Days</th>
                            <th style="width: 5%;">Leave Days</th>
                            <th style="width: 5%;">LOP Days </th>
                            <th style="width: 5%;">Week Off</th>
                            <th style="width: 5%;">Holiday</th>
                            <!--<th style="width: 50px;">Overtime (In Hrs.)</th>-->
                            <th style="width: 7%;">Gross Salary</th>
                            <th style="width: 7%;">Total Deduction</th>
                            <!--Settlement amount field also added by ***ARUL P DAS-->
                            <th style="width: 7%;">Settlement Amount</th>
                            <th style="width: 7%;">Net Salary</th>
                        </tr>
                        <?php
                        $i = 1;
                        $temp = '';
                        foreach ($branch_wise_values as $emp_key => $emp_wise_values) { //The emp_key contain employee id of the current selected branch
                            // debug($emp_wise_values);
                            // echo $emp_branch;
                          //  if ($temp != $all_branches[$emp_branch]) { //This is for checking branchname repeats or not. If it repeats, it will not shown on output.
                                //                            echo "<h3>".$all_branches[$emp_branch]."</h3>";
                         //   }
                            //                        debug($emp_wise_values);
                        ?>
                            <tr>
                                <td><?php echo $i; ?></td>
                                <td><?php echo $emp_wise_values['emp_info']['employee_id']; ?></td>
                                <!--edited by ashin 10-01-25--->
                                <td><?php if (!empty($emp_wise_values['payroll_master']['emp_name'])) {
                                        echo $emp_wise_values['payroll_master']['emp_name'];
                                    } else {
                                        echo $emp_wise_values['emp_info']['EmpName'];
                                    }
                                    echo (isset($emp_wise_values['emp_details']['status'])) && $emp_wise_values['emp_details']['status'] == "2" ? '  (Resigned)' : ''; ?></td>

                                <!-- <td></?php echo $emp_wise_values['emp_info']['designation']; ?></td> -->
                                <td><?php if (!empty($emp_wise_values['payroll_master']['desig'])) {
                                        echo $emp_wise_values['payroll_master']['desig'];
                                    } else {
                                        echo $emp_wise_values['emp_info']['designation'];
                                    } ?></td>

                                <!-- <td></?php echo $emp_wise_values['emp_info']['department']; ?></td> -->
                                <td><?php if (!empty($emp_wise_values['payroll_master']['departments'])) {
                                        echo $emp_wise_values['payroll_master']['departments'];
                                    } else {
                                        echo $emp_wise_values['emp_info']['department'];
                                    } ?></td>

                                <td><?php if (!empty($emp_wise_values['payroll_master']['branch_name'])) {
                                        echo $emp_wise_values['payroll_master']['branch_name'];
                                    } else {
                                        echo $emp_wise_values['emp_info']['branch'];
                                    } ?></td>

                                <td><?php echo $emp_wise_values['ectc']['presant_total']; ?></td>
                                <td><?php echo $emp_wise_values['ectc']['leave_total']; ?></td>
                                <td><?php echo $emp_wise_values['ectc']['lop_total']; ?></td>
                                <td><?php echo $emp_wise_values['ar']['weekoff_total']; ?></td>
                                <td><?php echo $emp_wise_values['ar']['holiday_total']; ?></td>

                                <!--<td><?php // echo isset($emp_wise_values['ot']) ? round(($emp_wise_values['ot'] / 60), 2) :0;  
                                        ?></td>-->
                                <?php
                                $count_addition = count($emp_wise_values['Addition']['keys']);
                                // echo $count_addition;
                                $grss_amt = 0;
                                for ($m = 0; $m < $count_addition; $m++) {
                                    $number = round($emp_wise_values['Addition']['value'][$m], 2);
                                    $grss_amt = $number + $grss_amt;
                                }
                                ?>
                                <td><?php echo round($grss_amt); ?></td>
                                <?php
                                $count_addition = count($emp_wise_values['Deduction']['keys']);
                                // echo $count_addition;
                                $dd_amt = 0;
                                for ($m = 0; $m < $count_addition; $m++) {
                                    $number = round($emp_wise_values['Deduction']['value'][$m], 2);
                                    $dd_amt = $number + $dd_amt;
                                }
                                $netamt = $grss_amt + $dd_amt + $emp_wise_values['settle'];
                                ?>
                                <td><?php echo round($dd_amt, 2); ?></td>
                                <td><?php echo round($emp_wise_values['settle']); ?></td>
                                <td><?php echo round($netamt); ?></td>
                            </tr>
                        <?php
                          //  $temp = $all_branches[$emp_branch];
                            $i++;
                        } //This is the closing of employee wise foreach loop.
                        ?>
                    </tbody>
                </table>
            <?php
            } //This is the closing of branch wise foreach loop.
        } else {
            ?>
            <table class="table table-bordered">
                <tbody>
                    <tr style="background-color:#f0f0ff;">
                        <th>Sl.No</th>

                        <th>Employee ID</th>
                        <th>Name</th>
                        <th style="width:80px;">Designation</th>
                        <th style="width:60px;">Department</th>
                        <th style="width:60px;">Branch</th>
                        <th style="width:50px;">Present Days</th>
                        <th style="width:50px;">Leave Days</th>
                        <th>LOP Days </th>
                        <th>Week Off</th>
                        <th>Holiday</th>
                        <!--<th style="width:50px;">Overtime (In Hrs.)</th>-->
                        <th style="width:50px;">Gross Salary</th>
                        <th style="width:50px;">Total Deduction</th>
                        <!--  //edited by megha on 13/11/2019 settlement amount 1-->
                        <th style="width:50px;">Settlement Amount</th>
                        <th>Net Salary</th>

                    </tr>
                    <?php
                    $i = 1;
                    foreach ($gross as $val) {
                    ?>

                        <tr>
                            <td><?php echo $i; ?></td>
                            <td><?php echo $val['emp_info']['employee_id']; ?></td>
                            <!--edited by ashin 10-01-25--->
                            <!-- <td></?php echo $val['emp_info']['EmpName']; ?></td> -->
                            <td><?php if (!empty($val['payroll_master']['emp_name'])) {
                                    echo $val['payroll_master']['emp_name'];
                                } else {
                                    echo  $val['emp_info']['EmpName'];
                                }
                                echo (isset($val['emp_details']['status'])) &&  $val['emp_details']['status'] == "2" ? '  (Resigned)' : ''; ?></td>

                            <!-- <td style="width:60px;"></?php echo $val['emp_info']['designation']; ?></td> -->
                            <td><?php if (!empty($val['payroll_master']['desig'])) {
                                    echo  $val['payroll_master']['desig'];
                                } else {
                                    echo  $val['emp_info']['designation'];
                                } ?></td>

                            <!-- <td style="width:60px;"></?php echo $val['emp_info']['department']; ?></td> -->
                            <td><?php if (!empty($val['payroll_master']['departments'])) {
                                    echo  $val['payroll_master']['departments'];
                                } else {
                                    echo  $val['emp_info']['department'];
                                } ?></td>

                            <!-- <td style="width:60px;"></?php echo $val['emp_info']['branch']; ?></td> -->
                            <td><?php if (!empty($val['payroll_master']['branch_name'])) {
                                    echo  $val['payroll_master']['branch_name'];
                                } else {
                                    echo  $val['emp_info']['branch'];
                                } ?></td>
                            <td><?php echo $val['ectc']['presant_total']; ?></td>
                            <td><?php echo $val['ectc']['leave_total']; ?></td>
                            <td><?php echo $val['ectc']['lop_total']; ?></td>
                            <td><?php echo $val['ar']['weekoff_total']; ?></td>
                            <td><?php echo $val['ar']['holiday_total']; ?></td>
                            <!--<td><?php // echo isset($val['ot']) ? round(($val['ot'] / 60), 2) :0;  
                                    ?></td>-->
                            <?php
                            $count_addition = count($val['Addition']['keys']);
                            $grss_amt = 0;
                            for ($m = 0; $m < $count_addition; $m++) {
                                $number = round($val['Addition']['value'][$m], 2);
                                $grss_amt = $number + $grss_amt;
                            ?>



                            <?php } ?>
                            <td><?php echo round($grss_amt); ?></td>
                            <?php
                            $count_addition = count($val['Deduction']['keys']);

                            $dd_amt = 0;
                            for ($m = 0; $m < $count_addition; $m++) {
                                $number = round($val['Deduction']['value'][$m], 2);
                                $dd_amt = $number + $dd_amt;
                            ?>
                                <!-- //edited by megha on 13/11/2019 settlement amount 6-->
                            <?php
                            }


                            $netamt = $grss_amt + $dd_amt + $val['settle'];
                            ?>


                            <td><?php echo round($dd_amt, 2); ?></td>
                            <!-- //edited by megha on 13/11/2019 settlement amount 6-->
                            <td><?php echo round($val['settle']); ?></td>
                            <td><?php echo round($netamt); ?></td>


                        </tr>



                    <?php
                        $i++;
                    }
                    ?>

                </tbody>
            </table>
        <?php
        }
        ?>


<?php
    }
} ?>