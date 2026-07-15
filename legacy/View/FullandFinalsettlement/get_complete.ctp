<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

?>
<legend>Full And Final Settlement Of <?php echo $details['0']['EmployeeInfo']['EmpName']; ?></legend>
<div class="row">
    <div class="col-md-12">
        <!-- DIRECT CHAT DANGER -->
        <div class="box box-body">
            <!-- /.box-header -->
            <div class="box-body">
                <div class="col-md-12">
                    <h2 style="text-align:center; ">Forsight Group</h2>
                    <h4 style="text-align:center; ">Full and Final Settlement Slip</h4>
                    <br>
                    <div class="col-md-6">
                        <div class="form-group">
                            <div class="col-md-12">
                                <label class="col-md-6 control-label">Employee Code </label>
                                <div class="col-md-6">
                                    <label class="col-md-12 control-label" style="font-weight:100; ">:<?php echo $details['0']['EmployeeInfo']['employee_id']; ?></label>
                                </div>
                            </div>
                        </div>
                        <br>
                        <div class="form-group">
                            <div class="col-md-12">
                                <label class="col-md-6 control-label">Join Date </label>
                                <div class="col-md-6">
                                    <label class="col-md-12 control-label" style="font-weight:100; ">:<?php echo $details['0']['EmployeeInfo']['joining_date']; ?></label>
                                </div>
                            </div>
                        </div>
                        <br>
                        <div class="form-group">
                            <div class="col-md-12">
                                <label class="col-md-6 control-label">Employee Designation </label>
                                <div class="col-md-6">
                                    <label class="col-md-12 control-label" style="font-weight:100; ">:<?php echo $details['0']['EmployeeInfo']['designation']; ?></label>
                                </div>
                            </div>
                        </div>
                        <br>
                        <div class="form-group">
                            <div class="col-md-12">
                                <label class="col-md-6 control-label">Relieving Date </label>
                                <div class="col-md-6">
                                    <label class="col-md-12 control-label" style="font-weight:100; ">:<?php echo $details['0']['Termination']['act_last_working_day']; ?></label>
                                </div>
                            </div>
                        </div>
                        <br>
                        <div class="form-group">
                            <div class="col-md-12">
                                <label class="col-md-6 control-label">Settlement Days </label>
                                <div class="col-md-6">
                                    <label class="col-md-12 control-label" style="font-weight:100; ">:<?php echo $details['0']['Termination']['payroll_days']; ?></label>
                                </div>
                            </div>
                        </div>
                        <br>
                        <div class="form-group">
                            <div class="col-md-12">
                                <label class="col-md-6 control-label">Notice Days </label>
                                <div class="col-md-6">
                                    <label class="col-md-12 control-label" style="font-weight:100; ">:<?php echo $details['0']['Termination']['payroll_days']; ?></label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <div class="col-md-12">
                                <label class="col-md-6 control-label">Employee Name </label>
                                <div class="col-md-6">
                                    <label class="col-md-12 control-label" style="font-weight:100; ">:<?php echo $details['0']['EmployeeInfo']['EmpName']; ?></label>
                                </div>
                            </div>
                        </div>
                        <br>
                        <div class="form-group">
                            <div class="col-md-12">
                                <label class="col-md-6 control-label">Branch </label>
                                <div class="col-md-6">
                                    <label class="col-md-12 control-label" style="font-weight:100; ">:<?php echo $details['0']['EmployeeInfo']['branch']; ?></label>
                                </div>
                            </div>
                        </div>
                        <br>
                        <div class="form-group">
                            <div class="col-md-12">
                                <label class="col-md-6 control-label">Employee Department </label>
                                <div class="col-md-6">
                                    <label class="col-md-12 control-label" style="font-weight:100; ">:<?php echo $details['0']['EmployeeInfo']['department']; ?></label>
                                </div>
                            </div>
                        </div>
                        <br>
                        <div class="form-group">
                            <div class="col-md-12">
                                <label class="col-md-6 control-label">Encashed Days </label>
                                <div class="col-md-6">
                                    <label class="col-md-12 control-label" style="font-weight:100; ">:<?php echo $details['0']['Termination']['encashed_days']; ?></label>
                                </div>
                            </div>
                        </div>
                        <br>
                        <div class="form-group">
                            <div class="col-md-12">
                                <label class="col-md-6 control-label">Settlement Days </label>
                                <div class="col-md-6">
                                    <label class="col-md-12 control-label" style="font-weight:100; ">:<?php echo $details['0']['Termination']['payroll_days']; ?></label>
                                </div>
                            </div>
                        </div>
                        <br>
                        <div class="form-group">
                            <div class="col-md-12">
                                <label class="col-md-6 control-label">Notice Days </label>
                                <div class="col-md-6">
                                    <label class="col-md-12 control-label" style="font-weight:100; ">:<?php echo $details['0']['Termination']['notice_period']; ?></label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- /.box-body -->
            <div class="col-md-12">
                <table class="table table-bordered" style="border:1px solid #3C8DBC">
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

                                    <!--<th colspan="2"><?php echo $tot + $sum; ?></th>-->
                                    <th colpsan="2"  ><?php echo $dd + $net; ?></th>
                                </tr>     

                            </tbody>
                </table>
            </div>
        </div>
    </div>
    <button class="btn btn-primary pull-right">Approve Payroll</button>
    <button class="btn btn-warning" onclick="next(2)">I Need to Re-Work</button>
</div>
