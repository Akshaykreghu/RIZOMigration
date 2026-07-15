<div class="modal-body">
    
<legend>Full And Final Settlement Of <?php  echo $details['0']['EmployeeInfo']['EmpName']; ?></legend>
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
                                    <label class="col-md-12 control-label">:<?php echo $details['0']['Termination']['payroll_days']; ?></label>
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
                                    <label class="col-md-12 control-label">:<?php echo $details['0']['Termination']['working_days_settled']; ?></label>
                                </div>
                            </div>
                        </div>
                        <br>
                        <div class="form-group">
                            <div class="col-md-12">
                                <label class="col-md-6 control-label">Balance Working Days </label>
                                <div class="col-md-6">
                                    <label class="col-md-12 control-label">:<?php echo ($details['0']['Termination']['working_days_settled'] != 0)? $details['0']['Termination']['working_days_settled'] - $details['0']['Termination']['payroll_days']: 0; ?></label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- /.box-body -->
            <div class="col-md-12">
                
                <table class="table table-bordered" style="border:1px solid #3C8DBC">
                    <thead>
                        <tr>
                            <th colspan="2" style="text-align: center; ">ADDITIONS</th>
                            <th colspan="2" style="text-align: center; ">DEDUCTIONS</th>
                        </tr>
                        <th>Item</th>
                        <th>Amount</th>
                        <th>Item</th>
                        <th>Amount</th>
                    </thead>
                    <?php $sum_add = 0;  ?>
                    <?php $sum_ded = 0;  ?>
                     <tbody>    
                    <?php if(count($arr_emp_settle['payd_additional']) > count($arr_emp_settle['payd_deductions'])) {
                        $great = 'payd_additional';
                    } else {
                        $great = 'payd_deductions';
                    }
                    foreach ($arr_emp_settle[$great] as $key => $val) { ?>
                        <tr>
                            <td><?php echo isset($arr_emp_settle['payd_additional'][$key]['emp_settle_slip']['salary_head_item_desc'])
                            ? $arr_emp_settle['payd_additional'][$key]['emp_settle_slip']['salary_head_item_desc'] : ''; ?></td>
                            <td><?php echo isset($arr_emp_settle['payd_additional'][$key]['emp_settle_slip']['salary_amount'])? $arr_emp_settle['payd_additional'][$key]['emp_settle_slip']['salary_amount']: '' ; ?></td>

                            <td><?php echo isset($arr_emp_settle['payd_deductions'][$key]['emp_settle_slip']['salary_head_item_desc'])?
                            $arr_emp_settle['payd_deductions'][$key]['emp_settle_slip']['salary_head_item_desc']: '' ; ?></td>
                            <td><?php echo isset($arr_emp_settle['payd_deductions'][$key]['emp_settle_slip']['salary_amount'])?
                            $arr_emp_settle['payd_deductions'][$key]['emp_settle_slip']['salary_amount']: '' ; ?></td>

                            <?php $adds = isset($arr_emp_settle['payd_additional'][$key]['emp_settle_slip']['salary_amount'])?
                            $arr_emp_settle['payd_additional'][$key]['emp_settle_slip']['salary_amount']: 0; ?>

                            <?php $sum_add = $sum_add + $adds ; ?>
                            <?php if(isset($arr_emp_settle['payd_deductions'][$key])) { ?>
                            <?php $deds = isset($arr_emp_settle['payd_deductions'][$key]['emp_settle_slip']['salary_amount'])?
                            $arr_emp_settle['payd_deductions'][$key]['emp_settle_slip']['salary_amount']: 0 ; ?>
                            <?php $sum_ded = $sum_ded + $deds; ?>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                    <tr><td>Total</td><td><?php echo '<b>' .  $sum_add . '</b>'; ?></td>
                    <td>Total</td><td><?php echo '<b>' . $sum_ded . '</b>'; ?></td></tr>
                    </tbody>
                    <tbody>
                        <?php if(count($arr_emp_settle['Extra_additions']) > count($arr_emp_settle['Extra_deductions'])) {
                            $great = 'Extra_additions';
                        } else {
                            $great = 'Extra_deductions';
                        }
                        ?>
                        <tr>
                            <th colspan="4">
                                Others
                            </th>
                        </tr>
                        <tr>
                            <th colspan="2" style="text-align: center; ">ADDITIONS</th>
                            <th colspan="2" style="text-align: center; ">DEDUCTIONS</th>
                        </tr>
                        <?php foreach ($arr_emp_settle[$great] as $key => $val) { ?>
                        <tr>
                            <td><?php echo isset($arr_emp_settle['Extra_additions'][$key]['emp_settle_slip']['salary_head_item_desc'])
                            ? $arr_emp_settle['Extra_additions'][$key]['emp_settle_slip']['salary_head_item_desc'] : ''; ?></td>
                            <td><?php echo isset($arr_emp_settle['Extra_additions'][$key]['emp_settle_slip']['salary_amount'])? $arr_emp_settle['Extra_additions'][$key]['emp_settle_slip']['salary_amount']: '' ; ?></td>

                            <td><?php echo isset($arr_emp_settle['Extra_deductions'][$key]['emp_settle_slip']['salary_head_item_desc'])?
                            $arr_emp_settle['Extra_deductions'][$key]['emp_settle_slip']['salary_head_item_desc']: '' ; ?></td>
                            <td><?php echo isset($arr_emp_settle['Extra_deductions'][$key]['emp_settle_slip']['salary_amount'])?
                            $arr_emp_settle['Extra_deductions'][$key]['emp_settle_slip']['salary_amount']: '' ; ?></td>

                            <?php $adds = isset($arr_emp_settle['Extra_additions'][$key]['emp_settle_slip']['salary_amount'])?
                            $arr_emp_settle['Extra_additions'][$key]['emp_settle_slip']['salary_amount']: 0; ?>

                            <?php $sum_add = $sum_add + $adds ; ?>
                            <?php if(isset($arr_emp_settle['Extra_deductions'][$key])) { ?>
                            <?php $deds = isset($arr_emp_settle['Extra_deductions'][$key]['emp_settle_slip']['salary_amount'])?
                            $arr_emp_settle['Extra_deductions'][$key]['emp_settle_slip']['salary_amount']: 0 ; ?>
                            <?php $sum_ded = $sum_ded + $deds; ?>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                    <tr><td>Total</td><td><?php echo '<b>' . $sum_add . '</b>'; ?></td>
                    <td>Total</td><td><?php echo '<b>' . $sum_ded . '</b>'; ?></td></tr> 
                    <?php $sum =$sum_add + $sum_ded; ?>
                    <tr><td colspan="3"><b>Net Salary</b></td><td><?php echo '<b>' . $sum. '</b>'; ?></td></tr> 
                    </tbody>
                </table>
                <div class="col-md-12">
                    <button class="btn btn-warning pull-right" onclick="printslip(); " style="    margin-left: 12px; " >Print Slip</button> 
                    <!--<button class="btn btn-danger pull-right" onclick="Removeemps(); ">Submit Termination </button>-->
                </div>
            </div>
        </div>
    </div>
<!--    <button class="btn btn-primary pull-right">Approve Payroll</button>
    <button class="btn btn-warning" onclick="next(2)">I Need to Re-Work</button>-->
</div>
<form id="form-showreport" method="post" action="" ></form>
    <!--</div>-->
</div>

<script>
    
    function printslip(){
    var employee = <?php echo $details['0']['EmployeeInfo']['emp_pkey'];?> ;
//        var dayss = $('#present_days_after_resg').html();
//        var leaves = $('#encashable_leavebal').html();
//        var grativity = <?php //echo $gratuity; ?>;
        $('#form-showreport').attr('action',livesite+'EmployeeResignation/downloads/'+employee);
        $('#form-showreport').submit();
    

    }

</script>