<legend>Full And Final Settlement Of <?php echo $details['0']['EmployeeInfo']['EmpName']; ?></legend>
<div class="row">
    <div class="col-md-12"> 
        <!-- DIRECT CHAT DANGER -->
        <div class="box box-body" style="display:block; ">
            <!-- /.box-header -->
            <div class="box-body" style="display:block; ">
                <div class="col-md-12">
				
                    <h2 style="text-align:center; "><?php echo $arr_comp_contact_info['CompanyContactInfo']['business_name'];//$this->session->read('company_code'); ?></h2>
                    <h4 style="text-align:center; ">Full and Final Settlement Slip</h4>
                    <br>
                    <div class="col-md-6">
                        <div class="form-group">
                            <div class="col-md-12">
                                <label class="col-md-6 control-label">Employee Name </label>
                                <div class="col-md-6">
                                    <label class="col-md-12 control-label">:<?php echo $details['0']['EmployeeInfo']['EmpName']; ?></label>
                                </div>
                            </div>
                        </div>
                        <br>
                        <div class="form-group">
                            <div class="col-md-12">
                                <label class="col-md-6 control-label">Joining Date </label>
                                <div class="col-md-6">
                                    <label class="col-md-12 control-label">:<?php echo $details['0']['EmployeeInfo']['joining_date']; ?></label>
                                </div>
                            </div>
                        </div>
                        <br>
                        <div class="form-group">
                            <div class="col-md-12">
                                <label class="col-md-6 control-label">Department </label>
                                <div class="col-md-6">
                                    <label class="col-md-12 control-label">:<?php echo $details['0']['EmployeeInfo']['department']; ?></label>
                                </div>
                            </div>
                        </div>
                        <br>
                        <div class="form-group">
                            <div class="col-md-12">
                                <label class="col-md-6 control-label">Relieving Date </label>
                                <div class="col-md-6">
                                    <label class="col-md-12 control-label">:<?php echo $details['0']['Termination']['act_last_working_day']; ?></label>
                                </div>
                            </div>
                        </div>
                        <br>
                        <div class="form-group">
                            <div class="col-md-12">
                                <label class="col-md-6 control-label">Notice Period </label>
                                <div class="col-md-6">
                                    <label class="col-md-12 control-label" >:<?php echo $details['0']['Termination']['notice_period']; ?></label>
                                </div>
                            </div>
                        </div>
                        <br>
                        <div class="form-group">
                            <div class="col-md-12">
                                <label class="col-md-6 control-label">Resignation Period Present Days </label>
                                <div class="col-md-6">
                                    <label class="col-md-12 control-label">:<?php echo ($diff != 0) ?$diff - ($days_after_resignation_att+$offs) : 0 ; ?></label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <div class="col-md-12">
                                <label class="col-md-6 control-label">Employee ID </label>
                                <div class="col-md-6">
                                    <label class="col-md-12 control-label">:<?php echo $details['0']['EmployeeInfo']['employee_id']; ?></label>
                                </div>
                            </div>
                        </div>
                        <br>
                        <div class="form-group">
                            <div class="col-md-12">
                                <label class="col-md-6 control-label">Branch </label>
                                <div class="col-md-6">
                                    <label class="col-md-12 control-label">:<?php echo $details['0']['EmployeeInfo']['branch']; ?></label>
                                </div>
                            </div>
                        </div>
                        <br>
                        <div class="form-group">
                            <div class="col-md-12">
                                <label class="col-md-6 control-label">Designation </label>
                                <div class="col-md-6">
                                    <label class="col-md-12 control-label">:<?php echo $details['0']['EmployeeInfo']['designation']; ?></label>
                                </div>
                            </div>
                        </div>
                        <br>
                        <div class="form-group">
                            <div class="col-md-12">
                                <label class="col-md-6 control-label">Encashed Leaves </label>
                                <div class="col-md-6">
                                    <label class="col-md-12 control-label">:<?php echo $details['0']['Termination']['encashed_days']; ?></label>
                                </div>
                            </div>
                        </div>
                        <br>
                        <div class="form-group">
                            <div class="col-md-12">
                                <label class="col-md-6 control-label">Resignation Period Working Days </label>
                                <div class="col-md-6">
                                    <label class="col-md-12 control-label">:<?php echo ($diff != 0)? $diff - $offs : 0; ?></label>
                                </div>
                            </div>
                        </div>
                        <br>
                        <div class="form-group">
                            <div class="col-md-12">
                                <label class="col-md-6 control-label">Balance Working Days </label>
                                <div class="col-md-6">
                                    <label class="col-md-12 control-label">:<?php echo ($diff != 0)? ($diff - $offs) - ($diff - ($days_after_resignation_att+$offs)): 0; ?></label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- /.box-body -->
            <div class="col-md-12">
<!--                <table class="table table-bordered" style="border:1px solid #3C8DBC">
                    <thead>
                    <th>Salary</th>
                    <th>Rate</th>
                    <th>Amount</th>
                    </thead>
                    <tbody>
                        <?php $value = $arr_salary_for_template; 
                              $arr_data = $value['0']['summary']; ?>
                                <?php
                                if (count($arr_data) >= 0) {
                                $sum = 0;
                                $tot = 0;
                                $dd = 0;
                                $net = 0;
                                ?>
                                <?php foreach ($arr_data as $val) {
                                ?>
                                <tr> <?php
                                    $sum = $sum + $val['ectc']['salary_rate'];
                                    $dd = $dd + $val['ectc']['salary_amount'];
                                    ?>
                                    <td><?php echo $val['ectc']['salary_head_item_desc']; ?></td>
                                    <td><?php echo $val['ectc']['salary_rate']; ?></td>
                                    <td><?php echo $val['ectc']['salary_amount']; ?></td>

                                </tr>

                                <?php } ?>
                                <tr>
                                    <th style="text-align :center ; ">Gross Salary Total</h><th><?php echo $sum; ?></th><th><?php echo $dd; ?></th>
                                </tr>
                                <?php $arr_withoutComponents = $value['0']['withoutcomponent']; ?>

                                <?php if (count($arr_withoutComponents) > 0) { ?>
                                <tr><th colspan="3" style="text-align :center ;">Deductions</th></tr>
                                <?php foreach ($arr_withoutComponents as $vals) {
                                ?>

                                <tr> <?php
                                    $tot = $tot + $vals['ectc']['salary_rate'];
                                    $net = $net + $vals['ectc']['salary_amount'];
                                    ?>
                                    <td><?php echo $vals['ectc']['salary_head_item_desc']; ?></td>
                                    <td><?php echo $vals['ectc']['salary_rate']; ?></td>
                                    <td><?php echo $vals['ectc']['salary_amount']; ?></td>

                                </tr>

                                <?php } ?>
                                <tr>
                                    <th style="text-align :center ; ">Deductions Total</h><th><?php echo $tot; ?></th><th><?php echo $net; ?></th>
                                </tr> 
                                <?php } ?>
                                <?php } else {
                                ?>
                                <tr>
                                    <td colspan="4">No Components found under this data</td>
                                </tr>  
                                <?php } ?>
                                <tr>
                                    <th></th>
                                    <th style="text-align :center ; ">Net Salary</th>

                                    <th colspan="2"><?php echo $tot + $sum; ?></th>
                                    <th colpsan="2"  ><?php echo $dd + $net; ?></th>
                                </tr>     

                            </tbody>
                </table>-->
                
                <table class="table table-bordered" style="border:1px solid #3C8DBC">
                    <thead>
                        <th>Payroll Month</th>
                        <th>Gross Salary</th>
                        <th>Total Deductions</th>
                        <th>Net Pay</th>
                    </thead>
                    <tbody>
                        <?php $sdal = 0; ?>
                        <?php foreach($arr_emp_salaries as $salary) { ?>
                        <tr>
                            <td><?php echo $salary['payroll_master']['month_year']; ?></td>
                            <td><?php echo $salary['payroll_master']['gross_salary']; ?></td>
                            <td><?php echo $salary['payroll_master']['total_deduction']; ?></td>
                            <td><?php echo $salary['payroll_master']['net_salary']; ?></td>
                            <?php $sdal = $sdal + $salary['payroll_master']['net_salary']; ?>
                        </tr>
                        <?php } ?>
                        <tr>
                            <td style="font-weight: bold; ">Total Salary : </td><td></td><td></td><td style="font-weight: bold; "><?php echo $sdal; ?></td>
                        </tr>
                    </tbody>
                    <th colspan="4" style="border-bottom: 1px solid;
    text-align: center; " >Loan and Other Recovery  </th>
                    <thead>
                    <th>Recover type</th>
                    <th colspan="2">Recover Details</th>
                    <th>Recover Amount</th>
                    </thead>
                    <tbody>
                        <?php $loan_ded = 0; ?>
                        <?php if(count($loans) > 0) { ?>
                        <?php foreach ($loans as $val) { ?>
                            <tr>
                                <td><?php echo "Loan - " . $val['LoanDetails']['emp_loan']['loan_amount']; ?></td>
                                <td colspan="2" ><?php echo "Loan Amount - " . $val['LoanDetails']['emp_loan']['loan_amount'] . ", EMI - " . $val['LoanDetails']['emp_loan']['emi_amount']; ?></td>
                                <td><?php echo $val['BalanceOutstanding']; ?></td>
                            </tr>
                            <?php $loan_ded+=$val['BalanceOutstanding']; ?>
                        <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="4">Loans,Advance Expenses : NIL</td>
                            </tr>
                        <?php } ?>
                            <tr>
                                <td style="font-weight: bold; ">Total Amount : </td><td colspan="2"></td><td style="font-weight: bold; "><?php echo $loan_ded; ?></td>
                            </tr>
                    </tbody>
                    <th colspan="4" style="border-bottom: 1px solid;
    text-align: center; " >Encashment Leaves     </th>
                    <thead>
                    <th colspan="2">Leave type</th>
                    <th>Rate</th>
                    <th>Amount</th>
                    </thead>
                    <tbody>
                        <?php $encash_ded = 0;
                            if(count($leaveenmcashs) > 0){ 
                        ?> 
                        <?php foreach ($leaveenmcashs as $val) { ?>
                            <tr>
                                <td colspan="2"><?php echo $val['emp_encash_slip']['salary_head_item_desc']; ?></td>
                                <td><?php echo $val['emp_encash_slip']['salary_rate']; ?></td>
                                <td><?php echo $val['emp_encash_slip']['salary_amount']; ?></td>
                            </tr>
                            <?php $encash_ded+= $val['emp_encash_slip']['salary_amount']; ?>
                            <?php } } else { ?>
                            
                            <tr>
                                <td colspan="4">No Encashable Leave</td>
                            </tr>
                            
                            <?php } ?>
                    </tbody>
                    <th colspan="4" style="border-bottom: 1px solid;
    text-align: center; " >Other Items     </th>
                    <tbody>
                        <tr>
                            <td>Excess Leaves</td><td colspan="2"><?php echo $excess_leave; ?></td><td><?php echo '-'.$excess_leav_amt; ?></td>
                        </tr>
                        <?php if($gratuity == "1"){ ?>
                        <tr>
                            <td>Gratuity</td><td colspan="2">0</td><td><?php echo $grativity; ?></td>
                        </tr>
                        <?php } else { $grativity = 0; } ?>
                    <td colspan="2" style="font-weight: bold; ">Total Amount To Be Paid</td><td></td>
                        <td style="font-weight: bold; "><?php echo $sdal + $encash_ded - $loan_ded - $excess_leav_amt + $grativity; ?></td>
                    </tbody>
                </table>
<!--                <h4>Loan and Other Recovery  </h4>	
                <table class="table table-bordered" style="border:1px solid #3C8DBC">
                    <thead>
                    <th>Recover type</th>
                    <th>Recover Details</th>
                    <th>Recover Amount</th>
                    </thead>
                    <tbody>
                        <?php $loan_ded = 0; ?>
                        <?php foreach ($loans as $val) { ?>
                            <tr>
                                <td><?php echo "Loan - " . $val['LoanDetails']['emp_loan']['loan_amount']; ?></td>
                                <td><?php echo "Loan Amount - " . $val['LoanDetails']['emp_loan']['loan_amount'] . ", EMI - " . $val['LoanDetails']['emp_loan']['emi_amount']; ?></td>
                                <td><?php echo $val['BalanceOutstanding']; ?></td>
                            </tr>
                            <?php $loan_ded+=$val['BalanceOutstanding']; ?>
                        <?php } ?>
                            <tr>
                                <td style="font-weight: bold; ">Total Amount : </td><td></td><td style="font-weight: bold; "><?php echo $loan_ded; ?></td>
                            </tr>
                    </tbody>
                </table>-->
<!--                <h4>Encashment Leaves   </h4>
                <table class="table table-bordered" style="border:1px solid #3C8DBC">
                    <thead>
                    <th>Leave type</th>
                    <th>Rate</th>
                    <th>Amount</th>
                    </thead>
                    <tbody>
                        <?php $encash_ded = 0; ?> 
                        <?php foreach ($leaveenmcashs as $val) { ?>
                            <tr>
                                <td><?php echo $val['emp_encash_slip']['salary_head_item_desc']; ?></td>
                                <td><?php echo $val['emp_encash_slip']['salary_rate']; ?></td>
                                <td><?php echo $val['emp_encash_slip']['salary_amount']; ?></td>
                            </tr>
                            <?php $encash_ded+= $val['emp_encash_slip']['salary_amount']; ?>
                        <?php } ?>
                    </tbody>
                </table>-->
                <table class="table table-bordered" style="border:1px solid #3C8DBC">
<!--                    <tbody>
                        <td>Total Amount To Be Paid</td>
                        <td><?php echo $dd + $net - $loan_ded - $encash_ded; ?></td>
                    </tbody>-->
                </table>
                
                <div class="col-md-12">
                    <button class="btn btn-warning pull-right" onclick="printslip(); " style="    margin-left: 12px; " >Print Slip</button> 
                    <button class="btn btn-danger pull-right" onclick="Removeemps(); ">Submit Termination </button>
                </div>
            </div>
        </div>
    </div>
<!--    <button class="btn btn-primary pull-right">Approve Payroll</button>
    <button class="btn btn-warning" onclick="next(2)">I Need to Re-Work</button>-->
</div>
<script>
    
    function printslip(){
    var employee = $('#employee').val();
        var dayss = $('#present_days_after_resg').html();
        var leaves = $('#encashable_leavebal').html();
        var grativity = <?php echo $gratuity; ?>;
        $('#form-showreport').attr('action',livesite+'EmployeeResignation/downloads/'+employee+'/'+dayss+'/'+leaves+'/'+grativity);
        $('#form-showreport').submit();
    

    }

</script>