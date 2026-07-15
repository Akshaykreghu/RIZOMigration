    <style>
    
    .modal-content {width: 116% !important;     }

    
    </style>

<?php if ($mode == '') { ?>
    <div class="modal-body" >
       <h3 align="center"><b><?php echo 'Employee Expense - '."$mname ".$year; ?></b> </h3>
        <div class="row">
            <div class="col-md-12">
                <div class=" ">
                    <?php  if (isset($needBranchWiseReport) && $needBranchWiseReport == true) { //do branchwise listing ?>
                        <?php   if (count($arr_emp_expenses_template) > 0) {
                         foreach ($arr_emp_expenses_template as $branch_code => $emp_advance) { ?>
                            <div class="box-body" style="overflow-y: auto;">
                                <fieldset>
                                    <legend><?php echo $emp_advance['branch_name']; ?></legend>
                                </fieldset>
                                <br>
                                <fieldset>
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Sl No</th>
                                                <th>Employee ID</th>
                                                <th>Company ID</th>
                                                <th>Employee Name</th>
                                                <th>Joining Date</th>
                                                <th>Branch</th>
                                                <th>Department</th>
                                                <th>Designation</th>
                                                <th>Termination Date</th>
                                                <th>Expense Type</th>
<!--                                                //edited by amal heading change on 15/08/2019-->
                                                <th>Amount</th>
                                                <th>Affected Month</th>
                                                <th>Vendor</th>
                                                <th>Purpose</th>
                                                <th>Remark</th>

                                                <!-- Approved person added by ARUL P DAS on 21_6_21 -->
                                                <th>Authorized/ Rejected By</th>
                                                <th>Authorized/ Rejected Date </th>
                                                <th>Authorized/ Rejected Person Remarks</th>
                                                <th>Approved/ Rejected By</th>
                                                <th>Approved/ Rejected Date </th>
                                                <th>Approved/ Rejected Person Remarks</th>
                                                <th>Expense Status</th>

                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $arr_data = $emp_advance['employeeexpenses'];
                                            // debug($arr_data);
                                            ?>
                                            <?php
                                            if (count($arr_data) > 0) {
                                                $count = 0;
                                                ?>
                                                <?php
                                                foreach ($arr_data as $val) {
                                                    
                                                    ?>
                                                    <tr> 
                                                        <?php $count = $count + 1; ?> 
                                                        <td><?php echo $count; ?></td>	
                                                        <td><?php echo $val['emp_id']; ?></td>
                                                        <td><?php echo $val['employee_id']; ?></td>
                                                        <td><?php echo $val['emp_name']; ?> <?php echo $val['status']; ?></td>
                                                        <td><?php echo $val['join']; ?></td>
                                                         <td><?php echo $val['branch']; ?></td>
                                                        <td><?php echo $val['department']; ?></td>
                                                         <td><?php echo $val['designation']; ?></td>
                                                        <!--   <td><?php echo $val['join']; ?></td> -->
                                                        <td><?php isset($val['termin'])?$val['termin']:'--'; ?></td>
                                                         <td><?php echo $val['expense_type']; ?></td>
                                                        <td><?php echo $val['expenses_amount']; ?></td>
                                                        <td><?php echo $val['affected_month']; ?></td>
                                                        <td><?php echo $val['vendor']; ?></td>
                                                        <td><?php echo $val['purpose']; ?></td>
                                                        <td><?php echo $val['remarks']; ?></td>
                                                        <td><?php  $au= !empty($val['authorized_by'])?$val['authorized_by']:''; echo $au; ?></td>
                                                        <td><?php echo $val['authorized_date']; ?></td>
                                                        <td><?php echo $val['remarks_auth']; ?></td>
                                                       <td><?php $ap= !empty($val['approved_by'])?$val['approved_by']:''; echo $ap; ?></td>
                                                        <td><?php echo $val['approved_date']; ?></td>
                                                        <td><?php echo $val['remarks_approved']; ?></td>
                                                        <td><?php echo $val['expense_status']; ?></td>
                                                    </tr>
                                                <?php } ?>
                                            <?php } else { ?>
                                                <tr>
                                                    <td colspan="14">No employees found under  branch.</td>
                                                </tr>  
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </fieldset>
                            </div>
                        <?php } } else {//end foreach    ?>
                    <h4>No employees found under this criteria.</h4>
                        <?php } ?>
                    <?php } else { ?>
                        <div class="box-body" style="overflow-y: auto;">
                            <fieldset>
                                <?php $arr_data = $arr_emp_expenses_template['employeeexpenses']; ?>
                                        <?php if (count($arr_data) > 0) {
                                            ?>
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Sl No</th>
                                                <th>Employee ID</th>
                                                <th>Company ID</th>
                                                <th>Employee Name</th>
                                                <th>Joining Date</th>
                                                <th>Branch</th>
                                                <th>Department</th>
                                                <th>Designation</th>
                                                <th>Termination Date</th>
<!--                                                //edited by amal heading change on 15/08/2019-->
                                                <th>Expense Type</th>
                                                <th>Amount</th>
                                                <th>Affected Month</th>
                                                <th>Vendor</th>
                                                <th>Purpose</th>
                                                <th>Remark</th>

                                                <!-- Approved person added by ARUL P DAS on 21_6_21 -->
                                                <th>Authorized/ Rejected By</th>
                                                <th>Authorized/ Rejected Date </th>
                                                <th>Authorized/ Rejected Person Remarks</th>
                                                <th>Approved/ Rejected By</th>
                                                <th>Approved/ Rejected Date </th>
                                                <th>Approved/ Rejected Person Remarks</th>
                                                <th>Expense Status</th>

                                        </tr>
                                    </thead>
                                    <tbody>
                                        
                                            <?php
                                            $i = 1;
                                            foreach ($arr_data as $val) {

                                                // debug($val);
                                                ?>
                                                <tr> 
                                                    <td><?php echo $i; ?> </td>
                                                     <td><?php echo $val['emp_id']; ?></td>
                                                        <td><?php echo $val['employee_id']; ?></td>
                                                        <td><?php echo $val['emp_name']; ?> <?php echo $val['status']; ?></td>
                                                         <td><?php echo $val['join']; ?></td>
                                                         <td><?php echo $val['branch']; ?></td>
                                                        <td><?php echo $val['department']; ?></td>
                                                         <td><?php echo $val['designation']; ?></td>
                                                        <!--   <td><?php echo $val['join']; ?></td> -->
                                                        <td><?php isset($val['termin'])?$val['termin']:'--'; ?></td>
                                                        <td><?php echo $val['expense_type']; ?></td>
                                                        <td><?php echo $val['expenses_amount']; ?></td>
                                                        <td><?php echo $val['affected_month']; ?></td>
                                                        <td><?php echo $val['vendor']; ?></td>
                                                        <td><?php echo $val['purpose']; ?></td>
                                                        <td><?php echo $val['remarks']; ?></td>
                                                        <td><?php $au= !empty($val['authorized_by'])?$val['authorized_by']:''; echo $au; ?></td>
                                                        <td><?php echo $val['authorized_date']; ?></td>
                                                        <td><?php echo $val['remarks_auth']; ?></td>
                                                       <td><?php $ap= !empty($val['approved_by'])?$val['approved_by']:''; echo $ap; ?></td>
                                                        <td><?php echo $val['approved_date']; ?></td>
                                                        <td><?php echo $val['remarks_approved']; ?></td>
                                                        <td><?php echo $val['expense_status']; ?></td>
                                                </tr>
                                                <?php
                                                $i++;
                                            }
                                            ?>
                                        <?php } else { ?>
                                            <h4 style="text-align: left;color: black">There is no data available in this criteria</h4>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </fieldset>
                        </div>

                    <?php } ?> <!-- /.box-body -->
                </div>
            </div>
        </div>    
<!--        <div class="row">
            <div class="form-group">
                <div class="col-md-12" align="right">
                    <a href="#" class="btn btn-default" onclick="downloadReport('Expense', 'pdf');" ><i class="icon-file"></i>Download As PDF</a>
                    <a href="#" class="btn btn-default" onclick="downloadReport('Expense', 'excel');"><i class="icon-file"></i>Download As Excel</a>
                </div>
            </div>
        </div>-->
    </div>
<?php } else { ?>
    <?php //echo '<style>'.file_get_contents("css/pdfbootstrap.css").'</style>';     ?>
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
            width: 70%;
            max-width: 70%;
            margin-bottom: 20px;
            background-color: transparent;
            border-spacing: 0;
            border-collapse: collapse;
        }
        td, th {
            text-align: left;
            padding: 6px;
            line-height: 1.42857143;
            vertical-align: top;
            border: 1px solid #B2B2B2;
        }
    </style>
    <?php
//    echo $this->element('reportadminheader', array(
//        'title' => 'Employee Expense'));
    ?>
    <br>
    <?php if (isset($needBranchWiseReport) && $needBranchWiseReport == true) { //do branchwise listing      ?>
        <?php if (count($arr_emp_expenses_template) > 0) {  
            foreach ($arr_emp_expenses_template as $branch_code => $emp_advance) { ?>
            <h4><?php echo $emp_advance['branch_name']; ?></h4>
            <table  align="center">
                <thead>
                    <tr>
                        <th>Sl<br>No</th>
                        <th style="width:50px;">Employee Name</th>
                        <th>Employee ID</th>
                        <th style="width:55px;">Designation</th>
                        <th>Department</th>
                        <!-- //edited by amal heading change on 15/08/2019 2-->
                        <th>Amount</th>
                        <th>Affected<br>Month</th>
                        <th style="width:55px;">Vendor</th>
                        <th style="width:55px;">Purpose</th>
                        <th style="width:55px;">Remark</th>

                        <!-- Approved person details added by Arul P Das on 21_6_21 -->
                        <th style="width:50px;">Approved/ Rejected Person Name</th>
                        <th style="width:50px;">Approved/ Rejected Date </th>
                        <th style="width:55px;">Approved/ Rejected person remarks</th>
                        <th style="width:50px;">Expense Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $arr_data = $emp_advance['employeeexpenses']; ?>
                    <?php if (count($arr_data) > 0) {
                        ?>
                        <?php
                        $i = 1;
                        foreach ($arr_data as $val) {
                            ?>
                            <tr> 
                                <td><?php echo $i; ?></td>	
                                <td style="width:50px;"><?php echo $val['emp_name']; ?> <?php echo $val['status']; ?></td>
                                <td><?php echo $val['employee_id']; ?></td>
                                <td style="width:55px;"><?php echo $val['designation']; ?></td>
                                <td><?php echo $val['department']; ?></td>
                                <td> <?php echo $val['expenses_amount']; ?></td>
                                <td style="width:50px;"><?php echo $val['affected_month']; ?></td>
                                <td style="width:55px;"><?php echo $val['vendor']; ?></td>
                                <td style="width:55px;"><?php echo $val['purpose']; ?></td>
                                <td style="width:55px;"><?php echo $val['remarks']; ?></td>

                                <td style="width:50px;"><?php echo $val['ApprovedBy']; ?></td>
                                <td style="width:50px;"><?php echo $val['authorized_date']; ?></td>
                                <td style="width:55px;"><?php echo $val['remarks_auth']; ?></td>
                                <td style="width:50px;"><?php echo $val['expense_status']; ?></td>
                            </tr>
                            <?php
                            $i++;
                        }
                        ?>
                    <?php } else { ?>
                        <tr>
                            <td colspan="14">No employees found under this branch.</td>
                        </tr>  
                    <?php } ?>
                </tbody>
            </table>
 <?php } } else {//end foreach    ?>
                    <h4>No employees found under this criteria.</h4>
                        <?php } ?>
    <?php } else { ?>
        <br>
        <table  align="center">
            <thead>
                <tr>
                    <th>Sl<br>No</th>
                    <th style="width:50px;">Employee Name</th>
                    <th>Employee ID</th>
                    <th style="width:55px;">Designation</th>
                    <th>Department</th>
                    <th style="width:50px;">Branch</th>
                    <!-- //edited by amal heading change on 15/08/2019 2-->
                    <th>Amount</th>
                    <th>Affected<br>Month</th>
                    <th style="width:55px;">Vendor</th>
                    <th style="width:55px;">Purpose</th>
                    <th style="width:55px;">Remark</th>

                    <!-- Approved person details added by Arul P Das on 21_6_21 -->
                    <th style="width:50px;">Approved/ Rejected Person Name</th>
                    <th style="width:50px;">Approved/ Rejected Date </th>
                    <th style="width:55px;">Approved/ Rejected person remarks</th>
                    <th style="width:50px;">Expense Status</th>
                </tr>
            </thead>
            <tbody>
                <?php $arr_data = $arr_emp_expenses_template['employeeexpenses']; ?>
                <?php if (count($arr_data) > 0) { ?>
                    <?php
                    $i = 1;
                    foreach ($arr_data as $val) {
                        ?>
                        <tr> 
                            <td><?php echo $i; ?></td>
                            <td style="width:50px;"><?php echo $val['emp_name']; ?> <?php echo $val['status']; ?></td>
                            <td><?php echo $val['employee_id']; ?></td>
                            <td style="width:55px;"><?php echo $val['designation']; ?></td>
                            <td><?php echo $val['department']; ?></td>
                            <td style="width:50px;"><?php echo $val['branch']; ?></td>
                            <td> <?php echo $val['expenses_amount']; ?></td>
                            <td style="width:50px;"><?php echo $val['affected_month']; ?></td>
                            <td style="width:55px;"><?php echo $val['vendor']; ?></td>
                            <td style="width:55px;"><?php echo $val['purpose']; ?></td>
                            <td style="width:55px;"><?php echo $val['remarks']; ?></td>

                            <td style="width:50px;"><?php echo $val['ApprovedBy']; ?></td>
                            <td style="width:50px;"><?php echo $val['authorized_date']; ?></td>
                            <td style="width:55px;"><?php echo $val['remarks_auth']; ?></td>
                            <td style="width:50px;"><?php echo $val['expense_status']; ?></td>
                        </tr>
                        <?php
                        $i++;
                    }
                    ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="15">No employees found under this criteria.</td>
                    </tr>  
                <?php } ?>
            </tbody>
        </table>


    <?php } ?> <!-- /.box-body -->

<?php } ?>