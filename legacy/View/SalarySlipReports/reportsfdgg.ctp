<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>

<div class="modal-body" style="overflow-y: auto;">
        <legend>Salary Slip - <?php echo $date ; ?> </legend>
        <div class="row">
            <div class="col-md-12">
                <div class=" ">
                    <?php
                    $i = 0;
                    foreach ($arr_salary_for_template as $value) {
                        if (count($value['summary']) !== 0 || count($value['withoutcomponent']) !== 0) {
                            $i += 1;
                            ?>
                            <div class="box-body">
                                <fieldset> 
                                    
                                        <legend>Name : <?php
                                            echo isset($value['summary']['0']['ed']['first_name']) ? $value['summary']['0']['ed']['first_name'] : '';
                                            echo ' ';
                                            echo isset($value['summary']['0']['ed']['last_name']) ? $value['summary']['0']['ed']['last_name'] : '';
                                            ?>  
                                        </legend>
                                        
                                    <div class="row">
                                        <div class="col-md-12">

                                        </div>
                                    </div>
                                    <div class="col-md-4">Branch : <?php echo isset($value['summary']['0']['br']['branch_name']) ? $value['summary']['0']['br']['branch_name'] : ''; ?>  </div>      
                                    <div class="col-md-4">Calender Days : <?php echo isset($value['empdet']['0']['payroll_master']['calander_days']) ? $value['empdet']['0']['payroll_master']['calander_days'] : ''; ?>  </div>            
                                    <div class="col-md-4">Days On Leave : <?php echo isset($value['empdet']['0']['payroll_master']['days_leave']) ? $value['empdet']['0']['payroll_master']['days_leave'] : ''; ?>  </div>            
                                    <div class="col-md-4">Designation : <?php echo isset($value['empdet']['0']['dd']['desig_name']) ? $value['empdet']['0']['dd']['desig_name'] : ''; ?>  </div>            
                                    <div class="col-md-4">Working Days : <?php echo isset($value['empdet']['0']['payroll_master']['working_days']) ? $value['empdet']['0']['payroll_master']['working_days'] : ''; ?>  </div>
                                    <div class="col-md-4">Loss Of Pay : <?php echo isset($value['empdet']['0']['payroll_master']['loss_of_pay']) ? $value['empdet']['0']['payroll_master']['loss_of_pay'] : ''; ?>  </div>
                                    <div class="col-md-4">Department : <?php echo isset($value['empdet']['0']['d']['dept_name']) ? $value['empdet']['0']['d']['dept_name'] : ''; ?>  </div>
                                    <div class="col-md-4">Present Days : <?php echo isset($value['empdet']['0']['payroll_master']['days_presant']) ? $value['empdet']['0']['payroll_master']['days_presant'] : ''; ?>  </div> 
                                    <div class="col-md-8">EMP ID: <?php echo isset($value['empdet']['0']['ep']['emp_company_id']) ? $value['empdet']['0']['ep']['emp_company_id'] : ''; ?>  </div> 
                                </fieldset>

                                <br>
                                <fieldset>


                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>

                                    <!--    <th>LEAVEPOLICY_GROUP_NAME</th> -->


                                                <th>Salary</th>
                                                <th>Rate</th>
                                                <th>Amount</th>

                                    <!--                                  <th>Leave days</th>
                                    <th>Holidays</th>-->

                                            </tr>
                                        </thead>

                                        <tbody>
                                            <?php $arr_data = $value['summary']; ?>
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
                                                <?php $arr_withoutComponents = $value['withoutcomponent']; ?>

                                                <?php if (count($arr_withoutComponents) > 0) { ?>
                                                    <tr><th colspan="3">Deductions</th></tr>
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
                                                <th style="text-align :center ; ">Net Salary</th>
                                                <th></th>
           
<!--<th colspan="2"><?php echo $tot + $sum; ?></th>--><th colpsan="2"  ><?php echo $dd + $net; ?></th>
                                            </tr>     

                                        </tbody>
                                    </table>

                                </fieldset>
                                <br>
                            </div>

                        <?php
                        }
                    }
                    ?> <!-- /.box-body -->
                </div>
            </div>
        </div>    
        <div class="row">
            <div class="form-group">
                <div class="col-md-12" align="right">
                    <a href="#" class="btn btn-default" onclick="downloadReport('Salaryslip', 'pdf');" ><i class="icon-file"></i>Download As PDF</a>
                    <!--a href="#" class="btn btn-default" onclick="downloadReport('Salaryslip','excel');"><i class="icon-file"></i>Download As Excel</a-->
                </div>
            </div>
        </div>
    </div>
<script>
   
function downloadReport(){
         var Date = $('#reportfrom').val();
         var url = livesite+'SalarySlipReports/SalarySlipdownload/'+Date;
                               $(location).attr('href',url);  
                                      
}
</script>