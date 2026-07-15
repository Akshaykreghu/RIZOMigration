<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<style>
    .table>thead>tr>th, .table>tbody>tr>th, .table>tfoot>tr>th, .table>thead>tr>td, .table>tbody>tr>td, .table>tfoot>tr>td {
    border: 1px solid #070707;
}
</style>
<div class="modal-body" style="overflow-y: auto;">
    
    <?php  if (isset($arr_salary_for_template['0']['summary']) && count($arr_salary_for_template['0']['summary']) <= 0) { ?>
    <div style="font-size: 25px;text-align:center; background-color:#F7D3D2;">
        Payroll Not Processed For this Month </div>
    <?php } else {
    ?>
        <!-- <legend align="center" >Salary Slip - <?php echo $monthYear; ?> </legend> -->
        <?php if(isset($msgs)) { echo $msgs;  } ?>
        <div class="row">
            <div class="col-md-12">
                <div class=" ">
                    <?php
                    $i = 0;
                    foreach ($arr_salary_for_template as $value) {
                        if (count($value['summary']) != 0) {
                            $i += 1;
                ?>
                            <h3 align="center"><b><?php echo 'Salary Slip - ' . "$mname1-" . $y1; ?></b> </h3>

                            <table class="table table-bordered" align="center" style="margin-top: 10px;margin-bottom: 0px; ">
                                <tbody>
                                    <tr style="width : 120% ; ">
                                        <th style="width:100%; display: flex; justify-content: space-between;"><b>&nbsp;</b> <b>Name : <?php
                                                                                                                                        echo isset($value['summary']['0']['ed']['first_name']) ? $value['summary']['0']['ed']['first_name'] : '';
                                                                                                                                        echo ' ';
                                                                                                                                        echo isset($value['summary']['0']['ed']['middile_name']) ? $value['summary']['0']['ed']['middile_name'] : '';
                                                                                                                                        echo ' ';
                                                                                                                                        //  debug($value['summary']['0']['ed']['middle_name']);
                                                                                                                                        echo isset($value['summary']['0']['ed']['last_name']) ? $value['summary']['0']['ed']['last_name'] : '';
                                                                                                                                        echo (isset($value['summary']['0']['ed']['status'])) && $value['summary']['0']['ed']['status'] == "2" ? '  (Resigned)' : '';
                                                                                                                                        ?></b> <b>Designation : <?php echo isset($value['empdet']['0']['dd']['desig_name']) ? $value['empdet']['0']['dd']['desig_name'] : ''; ?> </b><b>&nbsp;</b></th>



                                        <!--                                              <th>Leave days</th>
        <th>Holidays</th>-->

                                    </tr>
                                </tbody>
                            </table>

                            <div class="row  " style="padding-top: 10px; margin-left: 0px; margin-right: 0px; border-right: 1px solid black; border-left: 1px solid black;">
                                <div class="col-md-3 ">
                                    Employee ID
                                </div>
                                <div class="col-md-3 ">
                                    <span style="text-align: right ; word-wrap: break-word; ">: <?php echo isset($value['empdet']['0']['ep']['emp_company_id']) ? $value['empdet']['0']['ep']['emp_company_id'] : ''; ?> </span><span style="float: right ;"></span>
                                </div>
                                <div class="col-md-3">
                                    Date of Joining
                                </div>
                                <div class="col-md-3 ">
                                    <span style="text-align: right ;  border-bottom: 0px solid white ; ">: <?php
                                                                                                            //edited by megha on 9_7_19 date format changed
                                                                                                            echo isset($value['summary']['0']['ep']['joining_date']) ? date('d-m-Y', strtotime($value['summary']['0']['ep']['joining_date'])) : '';
                                                                                                            //echo isset($value['summary']['0']['ep']['joining_date']) ? $value['summary']['0']['ep']['joining_date'] : '';
                                                                                                            ?> </span><span style="float: right ;"></span>
                                </div>
                            </div>
                            <div class="row  " style="padding-top: 10px; margin-left: 0px; margin-right: 0px; border-right: 1px solid black; border-left: 1px solid black;">

                                <div class="col-md-3">
                                    <span> Department : </span><span></span>
                                </div>
                                <div class="col-md-3 ">
                                    <span style="text-align: right ; word-wrap: break-word; ">: <?php echo  wordwrap(isset($value['empdet']['0']['d']['dept_name']) ? $value['empdet']['0']['d']['dept_name'] : '', 21, "<br>\n", TRUE); ?> </span><span style="float: right ;"></span>
                                </div>
                                <div class="col-md-3">
                                    <span> Gender : </span><span></span>
                                </div>
                                <div class="col-md-3 ">
                                    <span style="text-align: right ; word-wrap: break-word; ">: <?php echo  wordwrap(isset($value['summary']['0']['ed']['classification']) ? strtoupper($value['summary']['0']['ed']['classification']) : '', 21, "<br>\n", TRUE); ?> </span><span style="float: right ;"></span>
                                </div>

                            </div>

                            <div class="row  " style="padding-top: 10px; margin-left: 0px; margin-right: 0px; border-right: 1px solid black; border-left: 1px solid black;">
                                <div class="col-md-3">
                                    Leave Days
                                </div>
                                <div class="col-md-3">
                                    <!--           <span style="text-align: right ; ">: <?php //echo isset($value['empdet']['0']['payroll_master']['working_days']) ? $value['empdet']['0']['payroll_master']['working_days'] : ''; 
                                                                                        ?>   </span><span style="float: right ;"></span>-->
                                    <span style="text-align: right ; ">: <?php echo isset($value['summary']['0']['payroll_master']['days_leave']) ? $value['summary']['0']['payroll_master']['days_leave'] : ''; ?> </span><span style="float: right ;"></span>
                                </div>
                                <div class="col-md-3">
                                    <span style="text-align:  left ; ">Present Days </span>
                                </div>
                                <div class="col-md-3">
                                    <span style="text-align: left ;  border-right: 0px solid white; border-bottom: 0px solid white ; ">: <?php echo isset($value['summary']['0']['ar']['presant_total']) ? $value['summary']['0']['ar']['presant_total'] : ''; ?> </span><span style="float: right ;"></span>

                                </div>


                            </div>
                            <div class="row  " style="padding-top: 10px; margin-left: 0px; margin-right: 0px; border-right: 1px solid black; border-left: 1px solid black;">
                                <div class="col-md-3">
                                    <span style="text-align:  left ; ">Lop Days </span>
                                </div>
                                <div class="col-md-3">
                                    <span style="text-align: right ; ">: <?php echo isset($value['empdet']['0']['payroll_master']['loss_of_pay']) ? $value['empdet']['0']['payroll_master']['loss_of_pay'] : ''; ?> </span><span style="float: right ;"></span>

                                </div>
                                <div class="col-md-3">
                                    <span> No. of Week Off </span><span></span>
                                </div>
                                <div class="col-md-3">
                                    <span style="text-align: right ; ">: <?php echo isset($value['summary']['0']['ar']['weekoff_total']) ? $value['summary']['0']['ar']['weekoff_total'] : ''; ?> </span><span style="float: right ;"></span>

                                </div>

                            </div>
                            <div class="row  " style="padding-top: 10px; margin-left: 0px; margin-right: 0px; border-right: 1px solid black; border-left: 1px solid black;">
                                <div class="col-md-3">
                                    No. of Holiday
                                </div>
                                <div class="col-md-3">
                                    <span style="text-align: right ; ">: <?php echo isset($value['summary']['0']['ar']['holiday_total']) ? $value['summary']['0']['ar']['holiday_total'] : ''; ?> </span><span style="float: right ;"></span>

                                </div>
                                <div class="col-md-3">
                                    <span style="text-align:  left ; ">PF account No </span>
                                </div>
                                <div class="col-md-3">
                                    <span style="text-align: right ; ">: <?php

                                                                            echo isset($value['summary']['0']['ed']['company_pf']) ? $value['summary']['0']['ed']['company_pf'] : '';
                                                                            ?> </span><span style="float: right ;"></span>
                                </div>
                            </div>
                            <div class="row  " style="padding-top: 10px; margin-left: 0px; margin-right: 0px; border-right: 1px solid black; border-left: 1px solid black;">

                                <div class="col-md-3">
                                    <span style="text-align:  left ; ">ESI No </span>
                                </div>
                                <div class="col-md-3">
                                    : <?php echo isset($value['summary']['0']['ed']['esi']) ? $value['summary']['0']['ed']['esi'] : ''; ?>

                                </div>
                                <div class="col-md-3">
                                    <span style="text-align:  left ; ">UAN No </span>
                                </div>
                                <div class="col-md-3">
                                    <span style="text-align: left ;  border-right: 0px solid white; border-bottom: 0px solid white ; ">: <?php echo isset($value['summary']['0']['ed']['pf']) ? $value['summary']['0']['ed']['pf'] : ''; ?> </span><span style="float: right ;"></span>

                                </div>
                            </div>
                            <div class="row  " style="padding-top: 10px; margin-left: 0px; margin-right: 0px; border-right: 1px solid black; border-left: 1px solid black;">

                                <div class="col-md-3">

                                    <span style="text-align:  left ; ">Bank Name </span>
                                </div>
                                <?php
                                $bank_name = '';
                                $branch_name = '';
                                $ifsc_code = '';
                                $acc_number = '';
                                $bank = isset($value['empdet']['0']['payroll_master']['bank_details']) ? $value['empdet']['0']['payroll_master']['bank_details'] : '';
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
                                }
                                ?>
                                <div class="col-md-3">
                                    <span style="text-align: right ; ">:
                                        <?php
                                        echo isset($bank_name) ? $bank_name : '';
                                        ?> </span><span style="float: right ;">
                                </div>
                                <div class="col-md-3">
                                    <span style="text-align:  left ; ">Account Number </span>
                                </div>
                                <div class="col-md-3">
                                    <span style="text-align: right ; ">: <?php
                                                                            echo isset($acc_number) ? $acc_number : '';
                                                                            //echo isset($value['summary']['0']['ed']['account_no']) ? $value['summary']['0']['ed']['account_no'] : '';
                                                                            ?> </span><span style="float: right ;"></span>
                                </div>
                            </div>
                            <div class="row  " style="padding-bottom: 10px; padding-top: 10px; margin-left: 0px; margin-right: 0px; border-right: 1px solid black; border-left: 1px solid black;">

                                <div class="col-md-3">
                                    <span style="text-align:  left ; ">IFSC Code </span>
                                </div>
                                <div class="col-md-3">
                                    <span style="text-align: right ; ">: <?php
                                                                            echo isset($ifsc_code) ? $ifsc_code : '';
                                                                            //echo isset($value['summary']['0']['ed']['ifsc_code']) ? $value['summary']['0']['ed']['ifsc_code'] : '';
                                                                            ?> </span><span style="float: right ;"></span>
                                </div>
                                <div class="col-md-3">
                                    <span style="text-align:  left ; ">Branch </span>
                                </div>
                                <div class="col-md-3">
                                    : <?php
                                        echo isset($branch_name) ? $branch_name : '';
                                        //echo isset($value['summary']['0']['ed']['branch_name']) ? $value['summary']['0']['ed']['branch_name'] : ''; 
                                        ?> </span><span style="float: right ;"></span></th>
                                </div>
                            </div>
                            <table class="table table-bordered" align="center">
                                <tbody>
                                    <tr style="background: #cccccc ;width : 120% ;">

                                        <!--    <th>LEAVEPOLICY_GROUP_NAME</th> -->


                                        <th style="width:40%">Earnings</th>
                                        <th style="width:10%">Amount</th>
                                        <th style="width:41%">Deductions</th>

                                        <!--<th>Leave days</th>-->
                                        <th style="width:10%">Amount</th>

                                    </tr>
                                    <?php $arr_data = $value['summary'];
                                    $arr_withoutComponents = $value['withoutcomponent'];
                                    ?>
                                    <?php
                                    if (count($arr_data) >= 0) {
                                        $countss = count($arr_data);
                                        if (count($arr_data) < count($arr_withoutComponents)) {
                                            $countss = count($arr_withoutComponents);
                                        }
                                        $sum = 0;
                                        $tot = 0;
                                        $dd = 0;
                                        $net = 0;
                                    ?>
                                        <?php for ($i = 0; $i < $countss; $i++) {
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
                                                <!-- edited by megha on 08_07_19 '0' values removed  -->
                                                <td><?php echo isset($arr_withoutComponents[$i]['ectc']['salary_amount']) ? abs(round($arr_withoutComponents[$i]['ectc']['salary_amount'], 2)) : ''; ?></td>

                                            </tr>

                                        <?php } ?>
                                        <tr style="background: #cccccc ;">
                                            <th style="text-align :left ; ">Total Earnings</th>
                                            <th><?php echo abs(round($sum)); ?></th>
                                            <th>Total Deductions </th>
                                            <th><?php echo abs(round($dd)); ?></th>
                                        </tr>
                                        <!-- edited by megha on 16/11/19 settlement amount  -->
                                        <?php if ($value['summary']['0']['ed']['status'] == 2) { ?>
                                            <tr style="background: #cccccc ;">
                                                <th style="text-align :left ; " colspan="3">Settlement Amount</th>
                                                <th><?php echo $value['settle']; ?></th>
                                            </tr>
                                        <?php } ?>
                                        <!-- end -->
                                        <!-- edited by megha on 30_05_19 round off, edited by megha on 16/11/19 settlement amount  -->

                                        <tr style="background: #cccccc ;">
                                            <th style="text-align :left; border-right: none;" colspan="1">Net Pay</th>
                                            <th style="border-left:none;" colspan="3"><?php echo round($sum) + round($dd) + round((isset($value['settle'])?  $value['settle']:0)); ?></th>
                                        </tr>
                                        <?php $arr_withoutComponents = $value['withoutcomponent']; ?>

                                        <?php if (count($arr_withoutComponents) > 0) {
                                        ?>



                                        <?php } ?>
                                    <?php } else {
                                    ?>
                                        <tr>
                                            <td colspan="4">No Components found under this data</td>
                                        </tr>
                                    <?php } ?>

                                </tbody>
                            </table>

                        <?php
                        }
                    }
                    ?> <!-- /.box-body -->
                </div>
            </div>
        </div>  
        <?php  if (isset($arr_salary_for_template['0']['summary']) && count($arr_salary_for_template['0']['summary']) <= 0) { ?>
        <div class="row">
            <div class="form-group">
                <div class="col-md-12" align="right">
                    <a href="#" class="btn btn-default" onclick="downloadReport('Salaryslip', 'pdf');" ><i class="icon-file"></i>Download As PDF</a>
                    <!--a href="#" class="btn btn-default" onclick="downloadReport('Salaryslip','excel');"><i class="icon-file"></i>Download As Excel</a-->
                </div>
            </div>
        </div>
        <?php } ?>
        <?php } 
    ?>
    </div>
<script>
   
function downloadReport(){
         var Date = $('#reportfrom').val();
         var url = livesite+'SalarySlipReports/SalarySlipdownload/'+Date;
                               $(location).attr('href',url);  
                                      
}
</script>