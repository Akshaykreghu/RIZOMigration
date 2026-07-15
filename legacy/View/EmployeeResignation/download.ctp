
<style type="text/css">
    body {
        line-height: 2em;
    }
    .block-container {
        width: 95%;
        padding: 20px;
    }

    .block-containers {
        width: 85%;
        text-align: center;
        padding: 20px;
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
        border: 2px solid #f4f4f4;
        width: 100%;
        max-width: 100%;
        margin-bottom: 20px;
        background-color: transparent;
        border-spacing: 0;
        border-collapse: collapse;
    }
    tr {
        max-width: 900px;
    }
    td, th {
        text-align: left;
        padding: 8px;
        font-size: 10px;
        line-height: 1.42857143;
        vertical-align: top;
        border: 1px solid #B2B2B2;
        max-width: 200px;
    }
</style>

<div>

    <h4>Full And Final Settlement Of <?php echo $details['0']['EmployeeInfo']['EmpName']; ?></h4>
    <div class="row">
        <div class="col-md-12 block-container">
            <!-- DIRECT CHAT DANGER -->
            <div style="display:block; ">
                <!-- /.box-header -->


                <hr>
                <h2 style="text-align:center; "><?php echo $arr_comp_contact_info['CompanyContactInfo']['business_name']; //$this->session->read('company_code');  ?></h2>
                <h4 style="text-align:center;">Full and Final Settlement Slip</h4>
                <br>
                <table class="table" style=" border: 0px; margin: auto;
  width: 50%;
  border: 3px solid green;
  padding: 10px;" class="block-containers" >
                    <tr style=" border: 0px; ">
                        <td style=" border: 0px"><b>Employee Name  </b></td><td style=" border: 0px"></td>
                        <td style=" border: 0px;">:<?php echo $details['0']['EmployeeInfo']['EmpName']; ?> </td><td style=" border: 0px"></td><td style=" border: 0px"></td>
                        <td style=" border: 0px"><b>Employee ID </b></td><td style=" border: 0px"></td>
                        <td style=" border: 0px">:<?php echo $details['0']['EmployeeInfo']['employee_id']; ?></td>
                    </tr>
                    <tr style=" border: 0px">	
                        <td style=" border: 0px"><b>Joining date   </b> </td><td style=" border: 0px"></td>
                        <td style=" border: 0px">:<?php echo $details['0']['EmployeeInfo']['joining_date']; ?><  </td><td style=" border: 0px"></td><td style=" border: 0px"></td>
                        <td style=" border: 0px"><b>Branch   </b></td><td style=" border: 0px"></td>
                        <td style=" border: 0px">:<?php echo $details['0']['EmployeeInfo']['branch']; ?>  </td>

                    </tr>
                    <tr style=" border: 0px">	
                        <td style=" border: 0px"><b>Designation</b>  </td><td style=" border: 0px"></td>
                        <td style=" border: 0px">:<?php echo $details['0']['EmployeeInfo']['designation']; ?>  </td><td style=" border: 0px"></td><td style=" border: 0px"></td>
                        <td style=" border: 0px"><b>Department </b></td><td style=" border: 0px"></td>
                        <td style=" border: 0px">:<?php echo $details['0']['EmployeeInfo']['department']; ?>  </td>

                    </tr>
                    <tr style=" border: 0px">	
                        <td style=" border: 0px"><b>Relieving Date </b> </td><td style=" border: 0px"></td>
                        <td style=" border: 0px">:<?php echo $details['0']['Termination']['act_last_working_day']; ?>  </td><td style=" border: 0px"></td><td style=" border: 0px"></td>
                        <td style=" border: 0px"><b>Encashed Leaves </b> </td><td style=" border: 0px"></td>
                        <td style=" border: 0px">:<?php echo $details['0']['Termination']['encashed_days']; ?>  </td>

                    </tr>
                    <tr style=" border: 0px">	
                        <td style=" border: 0px"><b>Notice Period </b> </td><td style=" border: 0px"></td>
                        <td style=" border: 0px">:<?php echo $details['0']['Termination']['notice_period']; ?>  </td><td style=" border: 0px"></td><td style=" border: 0px"></td>
                        <td style=" border: 0px"><b>Resignation Period Working days</b></td><td style=" border: 0px"></td>
                        <td style=" border: 0px">:<?php echo $details['0']['Termination']['working_days_settled']; ?>  </td>

                    </tr>
                    <tr style=" border: 0px">	
                        <td style=" border: 0px"><b>Resignation Period Present Days </b> </td><td style=" border: 0px"></td>
                        <td style=" border: 0px">:<?php echo $details['0']['Termination']['payroll_days']; ?>  </td><td style=" border: 0px"></td><td style=" border: 0px"></td>
                        <td style=" border: 0px"><b>Balance Working days</b></td><td style=" border: 0px"></td>
                        <td style=" border: 0px">:<?php echo ($details['0']['Termination']['working_days_settled'] > 0) ? $details['0']['Termination']['working_days_settled'] - $details['0']['Termination']['payroll_days'] : 0; ?>  </td>

                    </tr>
                </table>
                <br><br>
                <table class="table table-bordered" style="width: 100vh;margin: auto;
  width: 50%;
  border: 3px solid green;
  padding: 10px; border:1px solid #3C8DBC">
                    <thead>
                        <tr>
                            <th colspan="2" style="text-align: center; ">ADDITIONS</th>
                            <th colspan="2" style="text-align: center; ">DEDUCTIONS</th>
                        </tr>
                        <tr>
                        <th>Item</th>
                        <th>Amount</th>
                        <th>Item</th>
                        <th>Amount</th>
                        </tr>
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
                    <tr><?php $sum =$sum_add + $sum_ded; ?>
                        <td colspan="3" ><b>Net Salary</b></td><td><?php echo '<b>' . $sum . '</b>'; ?></td>
                    </tr> 
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>