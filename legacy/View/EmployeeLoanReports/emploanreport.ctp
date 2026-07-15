<style>
    .modal-content {
        width: 125% !important;
    }

    .no-wrap {
        white-space: nowrap;
    }
</style>

<?php if ($mode == '') { ?>
    <div class="modal-body" style="overflow-y: auto;">
        <!-- <legend>Employee Loan Report</legend> -->
        <div class="row">
            <div class="col-md-12">
                <div class="box ">
                    <h2 style="font-weight: bold;text-align: center;"> <?php echo "Employee Loan " . $from . " to " . $to; ?> </h2>
                    <h2 style="font-size:16px;text-align: center;font-weight: bold;font-size: 19px;"><?php echo "(Report Run by " . $user_id . " at "  . $date_time . ")" ?> </h2>
                    <?php if (isset($needBranchWiseReport) && $needBranchWiseReport == true) { //do branchwise listing 
                    ?>
                        <?php
                        foreach ($arr_emp_loan_template as $branch_code => $emp_loan) { ?>
                            <?php $arr_data = $emp_loan['employeeloan'];
                            if (count($arr_data) > 0) {
                            ?>
                                <div class="box-body" style="overflow-y: auto;">
                                    <fieldset>
                                        <legend><?php echo $emp_loan['branch_name']; ?></legend>
                                    </fieldset>
                                    <br>
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
                                                    <th>Joining Date</th>
                                                    <th>Termination Date</th>
                                                    <th>Loan Amount</th>
                                                    <th>Tenure(month)</th>
                                                    <th>Interest Rate(%)</th>
                                                    <th>EMI Amount</th>
                                                    <th>EMI Start Month</th>
                                                    <th>EMI End Month</th>
                                                    <th>Paid</th>
                                                    <!-- Edited by Akshay on 6-8-2024 -->
                                                    <th>Balance Amount</th>
                                                    <th>Status</th>
                                                    <!-- End -->
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                if (count($arr_data) > 0) {
                                                    $count = 0;
                                                ?>
                                                    <?php
                                                    foreach ($arr_data as $val) {
                                                        //Edited by Akshay on 6-8-2024
                                                        $loan_status = $val['loan_status'];
                                                            //End
                                                        ;
                                                    ?>
                                                        <tr>
                                                            <?php $count = $count + 1; ?>
                                                            <td><?php echo $count; ?></td>
                                                            <td><?php echo $val['employee_id']; ?></td>
                                                            <td><?php echo $val['userid']; ?></td>
                                                            <td><?php echo $val['emp_name']; ?><?php echo $val['status']; ?></td>
                                                            <td><?php echo $val['branch']; ?></td>
                                                            <td><?php echo $val['department']; ?></td>
                                                            <td><?php echo $val['designation']; ?></td>
                                                            <td class="no-wrap"><?php echo date('d-m-Y', strtotime($val['join'])); ?></td>
                                                            <td><?php echo $val['termin']; ?></td>
                                                            <td> <?php echo $val['loan_amount']; ?></td>
                                                            <td><?php echo $val['tenure']; ?></td>
                                                            <td><?php echo $val['intrest_rate']; ?></td>
                                                            <td><?php echo round($val['emi_amount']); ?></td><!-- Edited by Akshay on 12-8-2024 -->
                                                            <td class="no-wrap"><?php echo $val['start_month']; ?></td>
                                                            <td class="no-wrap"><?php echo date('M-Y', strtotime($val['emi_end_month'])); ?></td>
                                                            <td><?php echo $val['remarks']; ?></td>
                                                            <!-- Edited by Akshay on 6-8-2024 -->
                                                            <td><?php $balanceamt = $val['loan_amount'] - $val['remarks'];
                                                                echo $balanceamt; ?></td>
                                                            <td><?php echo ($loan_status == 'Y' ? 'Completed' : 'Active') ?></td>
                                                            <!-- End -->
                                                        </tr>
                                                    <?php } ?>
                                                <?php } else { ?>
                                                    <tr>
                                                        <td colspan="6">No data available under the selected criteria.</td>
                                                    </tr>
                                                <?php } ?>
                                            </tbody>
                                        </table>
                                    </fieldset>
                                </div>
                            <?php }
                        } //end foreach  
                        if (count($arr_emp_loan_template) == 0) { ?>
                            <div style="font-size: 19px;text-align:left;">
                                No data available under the selected criteria</div>
                        <?php }
                        ?>
                    <?php } else { ?>
                        <div class="box-body">
                            <fieldset>
                                <?php $arr_data = isset($arr_emp_loan_template['employeeloan']) ? $arr_emp_loan_template['employeeloan'] : array(); ?>
                                <?php if (!empty($arr_data)) {
                                ?>
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
                                                <th>Joining Date</th>
                                                <th>Termination Date</th>
                                                <th>Loan Amount</th>
                                                <th>Tenure(month)</th>
                                                <th>Interest Rate(%)</th>
                                                <th>EMI Amount</th>
                                                <th>EMI Start Month</th>
                                                <th>EMI End Month</th>
                                                <th>Paid</th>
                                                <!-- Edited by Akshay on 6-8-2024 -->
                                                <th>Balance Amount</th>
                                                <th>Status</th>
                                                <!-- End -->
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($arr_data)) {
                                            ?>
                                                <?php
                                                $i = 1;
                                                foreach ($arr_data as $val) {
                                                    //Edited by Akshay on 6-8-2024
                                                    $loan_status = $val['loan_status'];
                                                    //End
                                                ?>
                                                    <tr>
                                                        <td><?php echo $i; ?> </td>
                                                        <td><?php echo $val['employee_id']; ?></td>
                                                        <td><?php echo $val['userid']; ?></td>
                                                        <td><?php echo $val['emp_name']; ?><?php echo $val['status']; ?></td>
                                                        <td><?php echo $val['branch']; ?></td>
                                                        <td><?php echo $val['department']; ?></td>
                                                        <td><?php echo $val['designation']; ?></td>
                                                        <td class="no-wrap"><?php echo date('d-m-Y', strtotime($val['join'])); ?></td>
                                                        <td><?php echo $val['termin']; ?></td>
                                                        <td> <?php echo $val['loan_amount']; ?></td>
                                                        <td><?php echo $val['tenure']; ?></td>
                                                        <td><?php echo $val['intrest_rate']; ?></td>
                                                        <td><?php echo round($val['emi_amount']); ?></td><!-- Edited by Akshay on 12-8-2024 -->
                                                        <td class="no-wrap"><?php echo $val['start_month']; ?></td>
                                                        <td class="no-wrap"><?php echo date('M-Y', strtotime($val['emi_end_month'])); ?></td>
                                                        <td><?php echo $val['remarks']; ?></td>
                                                        <!-- Edited by Akshay on 6-8-2024 -->
                                                        <td><?php $balanceamt = $val['loan_amount'] - $val['remarks'];
                                                            echo $balanceamt; ?></td>
                                                        <td><?php echo ($loan_status == 'Y' ? 'Completed' : 'Active') ?></td>
                                                        <!-- End -->
                                                    </tr>
                                                <?php
                                                    $i++;
                                                }
                                                ?>
                                            <?php } else { ?>
                                                <tr>
                                                    <td colspan="13">No data available under the selected criteria.</td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                <?php } else { ?>
                                    <div style="font-size: 19px;text-align:left;">
                                        No data available under the selected criteria</div>
                                <?php } ?>
                            </fieldset>
                        </div>
                    <?php } ?> <!-- /.box-body -->
                </div>
            </div>
        </div>
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
    // echo $this->element('reportadminheader', array(
    //     'title' => 'Employee Loan ' . $from . " to " . $to .
    //         '<br><br>(Report Run by ' . $user_id . ' at ' . $date_time . ')'
    // ));

    ?>
    <!-- <br>
    <br> -->
    <page backtop="70mm" backbottom="20mm" backleft=2mm" backright="20mm" style="font-size: 12pt">
        <page_header>
            <div style="text-align:left; width:100%; ">
                <?php if (isset($arr_comp_contact_info['CompanyContactInfo']['logo']) && !empty($arr_comp_contact_info['CompanyContactInfo']['logo'])) { ?>
                    <div style="width: 20%; margin-left: 20px; font-size: 18px; ">
                        <img style=" margin-left: 20px; margin-top: 40px; " src="http://<?php echo $_SERVER['HTTP_HOST'] . $this->webroot . $arr_comp_contact_info['CompanyContactInfo']['logo']; ?>" height="100" width="100" class="img-circle" alt="Company Logo" />
                    </div>
                    <!--<img style=" margin-left: 70px; " src="http://<?php echo $_SERVER['HTTP_HOST'] . $this->webroot . $arr_comp_contact_info['CompanyContactInfo']['logo']; ?>" height="50" width="70" class="img-circle" alt="Company Logo" />-->
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
            <h3 style="text-align: center;padding-bottom: 20px;padding-top: 10px;"><?php echo 'Employee Loan ' . $from . " to " . $to ; ?></h3>
            <h4 style="text-align: center;padding-bottom: 20px;padding-top: 10px;"><?php echo '(Report Run by ' . $user_id . ' at ' . $date_time . ')'; ?></h4>
            <br>
        </page_header>
        <page_footer>

            <div style="width: 100%; text-align: right">
                page [[page_cu]]/[[page_nb]]
            </div>
            <div style="width: 100%; text-align: left">
                Downloaded By <?php echo $user_name; ?> <?php echo date("l, F j, Y"); ?>
            </div>
        </page_footer>
    </page>
    <?php if (isset($needBranchWiseReport) && $needBranchWiseReport == true) { //do branchwise listing      
    ?>
        <?php foreach ($arr_emp_loan_template as $branch_code => $emp_loan) { ?>
            <h4 style="text-align:left;"><?php echo str_repeat('&nbsp;', 30) . $emp_loan['branch_name']; ?></h4>
            <?php $arr_data = isset($emp_loan['employeeloan']) ? $emp_loan['employeeloan'] : array(); ?>
            <?php if (count($arr_data) > 0) {
            ?>
                <table align="center">
                    <thead>
                        <tr>
                            <th>Sl No</th>
                            <th>Employee ID</th>
                            <th>Employee Name</th>
                            <th>Branch</th>
                            <th>Loan Amount</th>
                            <th>Tenure(month)</th>
                            <th>Interest Rate(%)</th>
                            <th>EMI Amount</th>
                            <th>EMI Start Month</th>
                            <th>EMI End Month</th>
                            <th>Paid</th>
                            <!-- Edited by Akshay on 6-8-2024 -->
                            <th>Balance Amount</th>
                            <th>Status</th>
                            <!-- End -->
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($arr_data) > 0) {
                        ?>
                            <?php
                            $i = 1;
                            foreach ($arr_data as $val) {
                                //Edited by Akshay on 6-8-2024
                                $loan_status = $val['loan_status'];
                                //End
                            ?>
                                <tr>

                                    <td><?php echo $i; ?></td>
                                    <td><?php echo $val['employee_id']; ?></td>
                                    <td><?php echo $val['emp_name']; ?></td>
                                    <td><?php echo str_replace(' ', '<br>', $val['branch']); ?></td>
                                    <td> <?php echo $val['loan_amount']; ?></td>
                                    <td><?php echo $val['tenure']; ?></td>
                                    <td><?php echo $val['intrest_rate']; ?></td>
                                    <td><?php echo round($val['emi_amount']); ?></td><!-- Edited by Akshay on 12-8-2024 -->
                                    <td class="no-wrap"><?php echo $val['start_month']; ?></td>
                                    <td class="no-wrap"><?php echo date('M-Y', strtotime($val['emi_end_month'])); ?></td>
                                    <td><?php echo $val['remarks']; ?></td>
                                    <!-- Edited by Akshay on 6-8-2024 -->
                                    <td><?php $balanceamt = $val['loan_amount'] - $val['remarks'];
                                        echo $balanceamt; ?></td>
                                    <td><?php echo ($loan_status == 'Y' ? 'Completed' : 'Active') ?></td>
                                    <!-- End -->
                                </tr>
                            <?php
                                $i++;
                            }
                            ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="6">No data available under the selected criteria.</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>

            <?php }
        } //end foreach    
        if (count($arr_emp_loan_template) == 0) { ?>
            <div style="font-size: 19px;text-align:left;">
                No data available under the selected criteria</div>
        <?php }
        ?>
    <?php } else { ?>
        <br>
        <?php $arr_data = isset($arr_emp_loan_template['employeeloan']) ? $arr_emp_loan_template['employeeloan'] : array(); ?>
        <?php if (count($arr_data) > 0) { ?>
            <table align="center">
                <thead>
                    <tr>
                        <th>Sl No</th>
                        <th>Employee ID</th>
                        <th>Employee Name</th>
                        <th>Branch</th>
                        <th>Loan Amount</th>
                        <th>Tenure(month)</th>
                        <th>Interest Rate(%)</th>
                        <th>EMI Amount</th>
                        <th>EMI Start Month</th>
                        <th>EMI End Month</th>
                        <th>Paid</th>
                        <!-- Edited by Akshay on 6-8-2024 -->
                        <th>Balance Amount</th>
                        <th>Status</th>
                        <!-- End -->
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($arr_data) > 0) { ?>
                        <?php
                        $i = 1;
                        foreach ($arr_data as $val) {
                            //Edited by Akshay on 6-8-2024
                            $loan_status = $val['loan_status'];
                            //End
                        ?>
                            <tr>
                                <td><?php echo $i; ?></td>
                                <td><?php echo $val['employee_id']; ?></td>
                                <td><?php echo $val['emp_name']; ?></td>
                                <td><?php echo str_replace(' ', '<br>', $val['branch']); ?></td>
                                <td> <?php echo $val['loan_amount']; ?></td>
                                <td><?php echo $val['tenure']; ?></td>
                                <td><?php echo $val['intrest_rate']; ?></td>
                                <td><?php echo round($val['emi_amount']); ?></td><!-- Edited by Akshay on 12-8-2024 -->
                                <td class="no-wrap"><?php echo $val['start_month']; ?></td>
                                <td class="no-wrap"><?php echo date('M-Y', strtotime($val['emi_end_month'])); ?></td>
                                <td><?php echo $val['remarks']; ?></td>
                                <!-- Edited by Akshay on 6-8-2024 -->
                                <td><?php $balanceamt = $val['loan_amount'] - $val['remarks'];
                                    echo $balanceamt; ?></td>
                                <td><?php echo ($loan_status == 'Y' ? 'Completed' : 'Active') ?></td>
                                <!-- End -->
                            </tr>
                        <?php
                            $i++;
                        }
                        ?>
                    <?php } else { ?>
                        <tr>
                            <td colspan="6">No data available under the selected criteria.</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>

        <?php } else { ?>
            <div style="font-size: 19px;text-align:left;">
                No data available under the selected criteria</div>
        <?php } ?>
    <?php } ?> <!-- /.box-body -->

<?php  }
?>