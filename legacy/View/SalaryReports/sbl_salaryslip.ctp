<?php
//debug($arr_salary_for_template);
/*
* To change this template, choose Tools | Templates
* and open the template in the editor.
*/
?>

<?php if ($mode == '') { ?>
    <style>
        .model-content {
            width: 104% !important;
        }
    </style>
    <?php //debug(array_filter($arr_salary_for_template)); 
    ?>
    <div class="modal-body" style="overflow-y: auto;">
        <div class="row">
            <div class="col-md-12">
                <div class=" ">
                    <?php
                    $i = 0;
                    if ($cr == 'EmployeeDetails') {
                        foreach ($arr_salary_for_template as $value) {
                            if (count($value['summary']) != 0) {
                                $i += 1;
                                // debug($value);
                    ?>
                                <h3 align="center"><b><?php echo 'Salary Slip - ' . "$mname-" . $year; ?></b> </h3>
                            
                                <div style="border:1px solid black;">
                                <div class="row  " style="padding-top: 10px; margin-left: 0px; margin-right: 0px;">
                                    <div class="col-md-3 ">
                                        <strong>Employee Code</strong>
                                    </div>
                                    <div class="col-md-3 ">
                                        <strong style="text-align: right ; word-wrap: break-word; ">: <?php  echo isset($value['empdet']['0']['ep']['emp_company_id']) ? $value['empdet']['0']['ep']['emp_company_id'] : ''; ?> </span><span style="float: right ;"></strong>
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Employee Name</strong>
                                    </div>
                                    <div class="col-md-3">
    <strong style="text-align: right; border-bottom: 0px solid white;">:
        <?php
        if (!empty($value['summary'][0]['payroll_master']['emp_name'])) {
            echo $value['summary'][0]['payroll_master']['emp_name'];
        } else {
            echo isset($value['summary'][0]['ed']['first_name']) ? $value['summary'][0]['ed']['first_name'] : '';
            echo ' ' . (isset($value['summary'][0]['ed']['middile_name']) ? $value['summary'][0]['ed']['middile_name'] : '');
            echo ' ' . (isset($value['summary'][0]['ed']['last_name']) ? $value['summary'][0]['ed']['last_name'] : '');
        }
        ?>
    </strong>
</div>
    </div>
<div class="row  " style="padding-top: 10px; margin-left: 0px; margin-right: 0px;">
                
                                    <div class="col-md-3">
                                        DOJ
                                    </div>
                                    <div class="col-md-3 ">
                                        <span style="text-align: right ;   ">: 
                                  
                                                                                             <?php if(!empty($value['summary']['0']['payroll_master']['joining_date'])){
                                                                                  echo date('d-m-Y', strtotime($value['summary']['0']['payroll_master']['joining_date']));
                                                                                               }else{
                                                                                        echo date('d-m-Y', strtotime($value['summary']['0']['ep']['joining_date']));} ?>
                                                                                          </div> 
                                    <div class="col-md-3">
                                        DOB
                                    </div>

                                    <div class="col-md-3 ">
                                        <span style="text-align: right ;   ">: 
                                  
                                                                                             <?php if(!empty($value['summary']['0']['ed']['date_of_birth'])){
                                                                                  echo date('d-m-Y', strtotime($value['summary']['0']['ed']['date_of_birth']));
                                                                                               } ?>
                                                                                          </div> 
                            </div>
                                   <!-- edited by athira on 07-07-2025 -->
                                
                                <div class="row  " style="padding-top: 10px; margin-left: 0px; margin-right: 0px;">
                                    
                                <div class="col-md-3">
                                        <span> PF No.  </span><span></span>
                                    </div>
                                    <div class="col-md-3">
                                        <span style="text-align: right ; word-wrap: break-word; ">:
                                             
                                             <?php 
                                                 if (!empty($value['summary']['0']['ed']['company_pf'])) {
                                                    echo $value['summary']['0']['ed']['company_pf'];
                                                } 
                                                  ?></span>
                                            </div>

                                    <div class="col-md-3">
                                        <span> Designation  </span><span></span>
                                    </div>
                                    <div class="col-md-3">
                                        <span style="text-align: right ; word-wrap: break-word; ">:
                                             <?php 
                                               if (!empty($value['summary']['0']['payroll_master']['desig'])) {
            echo $value['summary']['0']['payroll_master']['desig'];
        } else {
            echo isset($value['empdet']['0']['dd']['desig_name']) ? $value['empdet']['0']['dd']['desig_name'] : '';
        }
                                                  ?></span>
                                            </div>
                                            </div>
                                   
                                    <div class="row  " style="padding-top: 10px; margin-left: 0px; margin-right: 0px;">
                                        <div class="col-md-3">
                                        <span> UAN  </span><span></span>
                                    </div>
                                    <div class="col-md-3 ">
                                        <span style="text-align: right ; word-wrap: break-word; ">: <?php echo  isset($value['summary']['0']['ed']['pf']) ? $value['summary']['0']['ed']['pf'] : ' ' ?> </span><span style="float: right ;"></span>
                                    </div>
                                        <div class="col-md-3">
                                        <span> Department </span><span></span>
                                    </div>
                                    <div class="col-md-3 ">
                                        <span style="text-align: right ; word-wrap: break-word; ">:
                                             <!-- </?php echo  wordwrap(isset($value['empdet']['0']['d']['dept_name']) ? $value['empdet']['0']['d']['dept_name'] : '', 21, "<br>\n", TRUE); ?> </span><span style="float: right ;"></span> -->
                                             <?php 
                                                 if (!empty($value['empdet']['0']['payroll_master']['departments'])) {
                                                    echo wordwrap($value['empdet']['0']['payroll_master']['departments'], 25, "<br>\n", TRUE);
                                                } else {
                                                   echo wordwrap(isset($value['empdet']['0']['d']['dept_name']) ?$value['empdet']['0']['d']['dept_name'] : '', 25, "<br>\n", TRUE);
                                                 }
                                                  ?></span>
                                            </div>
                                    </div>

                                    <div class="row  " style="padding-top: 10px; margin-left: 0px; margin-right: 0px;">
                                  <div class="col-md-3">
                                        <span> PAN No.  </span><span></span>
                                    </div>
                                    <div class="col-md-3 ">
                                        <span style="text-align: right ; word-wrap: break-word; ">: <?php echo  isset($value['summary']['0']['ed']['pan_no']) ? $value['summary']['0']['ed']['pan_no'] : ''; ?> </span><span style="float: right ;"></span>
                                    </div>    
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
                                    </div>

                                    <div class="row  " style="padding-top: 10px; margin-left: 0px; margin-right: 0px;">
                                  <div class="col-md-3">
                                        <span> Aadhaar No.  </span><span></span>
                                    </div>
                                    <div class="col-md-3 ">
                                        <span style="text-align: right ; word-wrap: break-word; ">: <?php echo  isset($value['summary']['0']['ed']['id_card']) ? $value['summary']['0']['ed']['id_card'] : ''; ?> </span><span style="float: right ;"></span>
                                    </div>    
                                     <div class="col-md-3">
                                        <span style="text-align:  left ; ">Account Number </span>
                                    </div>
                                    <div class="col-md-3">
                                        <span style="text-align: right ; ">: <?php
                                                                                echo isset($acc_number) ? $acc_number : '';
                                                                                
                                                                                ?> </span><span style="float: right ;"></span>
                                    </div>
                                    </div>

                                    <div class="row  " style="padding-top: 10px; margin-left: 0px; margin-right: 0px;">
                                  <div class="col-md-3">
                                        <span> ESIC No.  </span><span></span>
                                    </div>
                                    <div class="col-md-3 ">
                                        <span style="text-align: right ; word-wrap: break-word; ">: <?php echo  isset($value['summary']['0']['ed']['esi']) ? $value['summary']['0']['ed']['esi'] : ''; ?> </span><span style="float: right ;"></span>
                                    </div>    
                                      
                                    </div>

                                 

                                    <div class="row  " style="padding-top: 10px; margin-left: 0px; margin-right: 0px;">
                                  <div class="col-md-3">
                                        <span> Attendance Days  </span><span></span>
                                    </div>
                                    <div class="col-md-3 ">
                                        <span style="text-align: right ; word-wrap: break-word; ">: <?php echo isset($value['attendance_days']) ? $value['attendance_days'] : ''; ?> </span><span style="float: right ;"></span>
                                    </div>    

                                    <div class="col-md-3">
                                        <span> LOP Days</span><span></span>
                                    </div>
                                    <div class="col-md-3 ">
                                        <span style="text-align: right ; word-wrap: break-word; ">: <?php echo isset($value['empdet']['0']['payroll_master']['loss_of_pay']) ? $value['empdet']['0']['payroll_master']['loss_of_pay'] : '0'; ?> </span><span style="float: right ;"></span>
                                    </div> 
                                      
                                    </div>

                                     <div class="row  " style="padding-top: 10px; margin-left: 0px; margin-right: 0px;">
                                  <div class="col-md-3">
                                        <span> Leave Days  </span><span></span>
                                    </div>
                                    <div class="col-md-3 ">
                                        <span style="text-align: right ; word-wrap: break-word; ">: <?php echo isset($value['summary']['0']['payroll_master']['days_leave']) ? $value['summary']['0']['payroll_master']['days_leave'] : '0'; ?> </span><span style="float: right ;"></span>
                                    </div>    

                                    <div class="col-md-3">
                                        <span> OT Days </span><span></span>
                                    </div>
                                    <div class="col-md-3 ">
                                        <span style="text-align: right ; word-wrap: break-word; ">: <?php echo isset($value['ot_days']['0']['0']['OTcount']) ? $value['ot_days']['0']['0']['OTcount'] : '0'; ?> </span><span style="float: right ;"></span>
                                    </div> 
                                      
                                    </div>

                                        <div class="row  " style="padding-top: 10px; margin-left: 0px; margin-right: 0px;">
                                  <div class="col-md-3">
                                        <span>Allotted leave for the year </span><span></span>
                                    </div>
                                    <div class="col-md-3 ">
                                        <span style="text-align: right ; word-wrap: break-word; ">: <?php echo isset($value['ALY']['0']['0']['ALY']) ? $value['ALY']['0']['0']['ALY'] : '0'; ?> </span><span style="float: right ;"></span>
                                    </div>    
            
                                    <div class="col-md-3">
                                        <span>Total leave balance</span><span></span>
                                    </div>
                                    <div class="col-md-3 ">
                                        <span style="text-align: right ; word-wrap: break-word; ">: <?php echo isset($value['LBY']['0']['0']['LBY']) ? $value['LBY']['0']['0']['LBY'] : '0'; ?> </span><span style="float: right ;"></span>
                                    </div>    
                                    
                                    </div>
                                <table class="table table-bordered" align="center" style="margin-top:20px;">
                                    <tbody>
                                        <tr style="background: #cccccc ;width : 120% ;">



                                            <th style="width:25%">Earnings</th>
                                            <th style="width:7%">Amount</th>
                                            <th style="width:25%">Deductions</th>
                                            <th style="width:7%">Amount</th>
                                            <th style="width:25%;">Standard Salary Component</th>
                                            <th style="width:7%;">Amount</th>

                                        </tr>
                                        <?php $arr_data = $value['summary'];
                                        $arr_withoutComponents = $value['withoutcomponent'];
                                         $arr_standard_components= $value['standardcomponent'];
                                        //  debug($arr_standard_components);
                                        ?>
                                        <?php
                                       
                                    if (count($arr_data) >= 0) {
                                        $count_summary = count($arr_data);
                                        $count_without = count($arr_withoutComponents);
                                        $count_standard = count($arr_standard_components);
                                        $countss = max($count_summary, $count_without, $count_standard);

                                            $sum = 0;
                                            $tot = 0;
                                            $dd = 0;
                                            $monthly_ctc=0;
                                            $net = 0;
                                        ?>
                                            <?php for ($i = 0; $i < $countss; $i++) {
                                            ?>
                                                <tr> <?php
                                                        $sum += isset($arr_data[$i]['ectc']['salary_amount']) ? $arr_data[$i]['ectc']['salary_amount'] : 0;
                                                         $monthly_ctc += isset($arr_standard_components[$i]['ectc']['structure_det_value']) ? $arr_standard_components[$i]['ectc']['structure_det_value'] : 0; // Edited by Akshay on 6-1-2026
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
                                                    <td><?php echo isset($arr_standard_components[$i]['ectc']['salary_head_item_desc']) ? $arr_standard_components[$i]['ectc']['salary_head_item_desc'] : ''; ?></td>
                                                    <!-- Edited by Akshay on 6-1-2026 -->
                                                    <td><?php echo isset($arr_standard_components[$i]['ectc']['structure_det_value']) ? abs(round($arr_standard_components[$i]['ectc']['structure_det_value'], 2)) : ''; ?></td>
                                                    <!-- End -->
                                                </tr>

                                            <?php } ?>
                                            <tr style="background: #cccccc ;">
                                                <th>Total</th>
                                                <th><?php echo abs(round($sum)); ?></th>
                                                <th>Total </th>
                                                <th><?php echo abs(round($dd, 2)); ?></th>
                                                <th>Monthly CTC </th>
                                                <th><?php echo abs(round($monthly_ctc, 2)); ?></th>
                                            </tr>
                                            <!-- edited by megha on 16/11/19 settlement amount  -->
                                            <?php if ($value['summary']['0']['ed']['status'] == 2) { ?>
                                                <tr style="background: #cccccc ;">
                                                    <th >Settlement Amount </th>
                                                    <th colspan="5"><?php echo $value['settle']; ?></th>
                                                </tr>
                                            <?php } ?>
                                            <!-- end -->
                                            <!-- edited by megha on 30_05_19 round off, edited by megha on 16/11/19 settlement amount  -->

                                                  <?php
                                                      $netPay = round($sum + $dd + $value['settle']);
                                                   ?>
                                            <tr style="background: #cccccc ;">
                                                <th >Net Pay </th>
                                                <th> <?php echo round($netPay); ?></th>
                                                <th colspan="4">(In Words) : <?php echo numberToWords($netPay); ?> Only</th>
                                                
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
                                </div>

                            <?php
                            }
                        }
                    } else {
                        foreach ($arr_salary_for_template as $val) { ?>
                           
                            <?php foreach ($val as $value) {
                                if (count($value['summary']) != 0) {
                                    $i += 1;
                            ?>
                                    <h3 align="center"><b><?php echo 'Salary Slip - ' . "$mname-" . $year; ?></b> </h3>

                                     <div style="border:1px solid black;">
                                    <div class="row  " style="padding-top: 10px; margin-left: 0px; margin-right: 0px;">
                                    <div class="col-md-3 ">
                                        <strong>Employee Code</strong>
                                    </div>
                                    <div class="col-md-3 ">
                                        <strong style="text-align: right ; word-wrap: break-word; ">: <?php echo isset($value['empdet']['0']['ep']['emp_company_id']) ? $value['empdet']['0']['ep']['emp_company_id'] : ''; ?> </span><span style="float: right ;"></strong>
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Employee Name</strong>
                                    </div>
                                    <div class="col-md-3">
    <strong style="text-align: right; border-bottom: 0px solid white;">:
        <?php
        if (!empty($value['summary'][0]['payroll_master']['emp_name'])) {
            echo $value['summary'][0]['payroll_master']['emp_name'];
        } else {
            echo isset($value['summary'][0]['ed']['first_name']) ? $value['summary'][0]['ed']['first_name'] : '';
            echo ' ' . (isset($value['summary'][0]['ed']['middile_name']) ? $value['summary'][0]['ed']['middile_name'] : '');
            echo ' ' . (isset($value['summary'][0]['ed']['last_name']) ? $value['summary'][0]['ed']['last_name'] : '');
        }
        ?>
    </strong>
</div>
    </div>
<div class="row  " style="padding-top: 10px; margin-left: 0px; margin-right: 0px;">
                
                                    <div class="col-md-3">
                                        DOJ
                                    </div>
                                    <div class="col-md-3 ">
                                        <span style="text-align: right ;   ">: 
                                  
                                                                                             <?php if(!empty($value['summary']['0']['payroll_master']['joining_date'])){
                                                                                  echo date('d-m-Y', strtotime($value['summary']['0']['payroll_master']['joining_date']));
                                                                                               }else{
                                                                                        echo date('d-m-Y', strtotime($value['summary']['0']['ep']['joining_date']));} ?>
                                                                                          </div> 
                                    <div class="col-md-3">
                                        DOB
                                    </div>

                                    <div class="col-md-3 ">
                                        <span style="text-align: right ;   ">: 
                                  
                                                                                             <?php if(!empty($value['summary']['0']['ed']['date_of_birth'])){
                                                                                  echo date('d-m-Y', strtotime($value['summary']['0']['ed']['date_of_birth']));
                                                                                               } ?>
                                                                                          </div> 
                            </div>
                                   <!-- edited by athira on 07-07-2025 -->
                                
                                <div class="row  " style="padding-top: 10px; margin-left: 0px; margin-right: 0px;">
                                    
                                <div class="col-md-3">
                                        <span> PF No.  </span><span></span>
                                    </div>
                                    <div class="col-md-3">
                                        <span style="text-align: right ; word-wrap: break-word; ">:
                                             
                                             <?php 
                                                 if (!empty($value['summary']['0']['ed']['company_pf'])) {
                                                    echo $value['summary']['0']['ed']['company_pf'];
                                                } 
                                                  ?></span>
                                            </div>

                                    <div class="col-md-3">
                                        <span> Designation  </span><span></span>
                                    </div>
                                    <div class="col-md-3">
                                        <span style="text-align: right ; word-wrap: break-word; ">:
                                             <?php 
                                               if (!empty($value['summary']['0']['payroll_master']['desig'])) {
            echo $value['summary']['0']['payroll_master']['desig'];
        } else {
            echo isset($value['empdet']['0']['dd']['desig_name']) ? $value['empdet']['0']['dd']['desig_name'] : '';
        }
                                                  ?></span>
                                            </div>
                                            </div>
                                   
                                    <div class="row  " style="padding-top: 10px; margin-left: 0px; margin-right: 0px;">
                                        <div class="col-md-3">
                                        <span> UAN  </span><span></span>
                                    </div>
                                    <div class="col-md-3 ">
                                        <span style="text-align: right ; word-wrap: break-word; ">: <?php echo  isset($value['summary']['0']['ed']['pf']) ? $value['summary']['0']['ed']['pf'] : ' ' ?> </span><span style="float: right ;"></span>
                                    </div>
                                        <div class="col-md-3">
                                        <span> Department </span><span></span>
                                    </div>
                                    <div class="col-md-3 ">
                                        <span style="text-align: right ; word-wrap: break-word; ">:
                                             <!-- </?php echo  wordwrap(isset($value['empdet']['0']['d']['dept_name']) ? $value['empdet']['0']['d']['dept_name'] : '', 21, "<br>\n", TRUE); ?> </span><span style="float: right ;"></span> -->
                                             <?php 
                                                 if (!empty($value['empdet']['0']['payroll_master']['departments'])) {
                                                    echo wordwrap($value['empdet']['0']['payroll_master']['departments'], 25, "<br>\n", TRUE);
                                                } else {
                                                   echo wordwrap(isset($value['empdet']['0']['d']['dept_name']) ?$value['empdet']['0']['d']['dept_name'] : '', 25, "<br>\n", TRUE);
                                                 }
                                                  ?></span>
                                            </div>
                                    </div>

                                    <div class="row  " style="padding-top: 10px; margin-left: 0px; margin-right: 0px;">
                                  <div class="col-md-3">
                                        <span> PAN No.  </span><span></span>
                                    </div>
                                    <div class="col-md-3 ">
                                        <span style="text-align: right ; word-wrap: break-word; ">: <?php echo  isset($value['summary']['0']['ed']['pan_no']) ? $value['summary']['0']['ed']['pan_no'] : ''; ?> </span><span style="float: right ;"></span>
                                    </div>    
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
                                    </div>

                                    <div class="row  " style="padding-top: 10px; margin-left: 0px; margin-right: 0px;">
                                  <div class="col-md-3">
                                        <span> Aadhaar No.  </span><span></span>
                                    </div>
                                    <div class="col-md-3 ">
                                        <span style="text-align: right ; word-wrap: break-word; ">: <?php echo  isset($value['summary']['0']['ed']['id_card']) ? $value['summary']['0']['ed']['id_card'] : ''; ?> </span><span style="float: right ;"></span>
                                    </div>    
                                     <div class="col-md-3">
                                        <span style="text-align:  left ; ">Account Number </span>
                                    </div>
                                    <div class="col-md-3">
                                        <span style="text-align: right ; ">: <?php
                                                                                echo isset($acc_number) ? $acc_number : '';
                                                                                
                                                                                ?> </span><span style="float: right ;"></span>
                                    </div>
                                    </div>

                                    <div class="row  " style="padding-top: 10px; margin-left: 0px; margin-right: 0px;">
                                  <div class="col-md-3">
                                        <span> ESIC No.  </span><span></span>
                                    </div>
                                    <div class="col-md-3 ">
                                        <span style="text-align: right ; word-wrap: break-word; ">: <?php echo  isset($value['summary']['0']['ed']['esi']) ? $value['summary']['0']['ed']['esi'] : ''; ?> </span><span style="float: right ;"></span>
                                    </div>    
                                     <div class="col-md-3">

                                    </div>
                                </div>
                                    

                                    <div class="row  " style="padding-top: 10px; margin-left: 0px; margin-right: 0px;">
                                  <div class="col-md-3">
                                        <span> Attendance Days  </span><span></span>
                                    </div>
                                    <div class="col-md-3 ">
                                        <span style="text-align: right ; word-wrap: break-word; ">: <?php echo isset($value['attendance_days']) ? $value['attendance_days'] : ''; ?> </span><span style="float: right ;"></span>
                                    </div>    

                                    <div class="col-md-3">
                                        <span> LOP Days</span><span></span>
                                    </div>
                                    <div class="col-md-3 ">
                                        <span style="text-align: right ; word-wrap: break-word; ">: <?php echo isset($value['empdet']['0']['payroll_master']['loss_of_pay']) ? $value['empdet']['0']['payroll_master']['loss_of_pay'] : '0'; ?> </span><span style="float: right ;"></span>
                                    </div> 
                                      
                                    </div>

                                    <div class="row  " style="padding-top: 10px; margin-left: 0px; margin-right: 0px;">
                                  <div class="col-md-3">
                                        <span> Leave Days  </span><span></span>
                                    </div>
                                    <div class="col-md-3 ">
                                        <span style="text-align: right ; word-wrap: break-word; ">: <?php echo isset($value['summary']['0']['payroll_master']['days_leave']) ? $value['summary']['0']['payroll_master']['days_leave'] : '0'; ?> </span><span style="float: right ;"></span>
                                    </div>    

                                    <div class="col-md-3">
                                        <span> OT Days </span><span></span>
                                    </div>
                                    <div class="col-md-3 ">
                                        <span style="text-align: right ; word-wrap: break-word; ">: <?php echo isset($value['ot_days']['0']['0']['OTcount']) ? $value['ot_days']['0']['0']['OTcount'] : '0'; ?> </span><span style="float: right ;"></span>
                                    </div> 
                                      
                                    </div>

                                        <div class="row  " style="padding-top: 10px; margin-left: 0px; margin-right: 0px;">
                                  <div class="col-md-3">
                                        <span>Allotted leave for the year </span><span></span>
                                    </div>
                                    <div class="col-md-3 ">
                                        <span style="text-align: right ; word-wrap: break-word; ">: <?php echo isset($value['ALY']['0']['0']['ALY']) ? $value['ALY']['0']['0']['ALY'] : '0'; ?> </span><span style="float: right ;"></span>
                                    </div>    
            
                                    <div class="col-md-3">
                                        <span>Total leave balance</span><span></span>
                                    </div>
                                    <div class="col-md-3 ">
                                        <span style="text-align: right ; word-wrap: break-word; ">: <?php echo isset($value['LBY']['0']['0']['LBY']) ? $value['LBY']['0']['0']['LBY'] : '0'; ?> </span><span style="float: right ;"></span>
                                    </div>    
                                    
                                    </div>
                                <table class="table table-bordered" align="center" style="margin-top:20px;">
                                    <tbody>
                                        <tr style="background: #cccccc ;width : 120% ;">



                                            <th style="width:25%">Earnings</th>
                                            <th style="width:7%">Amount</th>
                                            <th style="width:25%">Deductions</th>
                                            <th style="width:7%">Amount</th>
                                            <th style="width:25%;">Standard Salary Component</th>
                                            <th style="width:7%;">Amount</th>

                                        </tr>
                                        <?php $arr_data = $value['summary'];
                                        $arr_withoutComponents = $value['withoutcomponent'];
                                         $arr_standard_components= $value['standardcomponent'];
                                        //  debug($arr_standard_components);
                                        ?>
                                        <?php
                                       
                                    if (count($arr_data) >= 0) {
                                        $count_summary = count($arr_data);
                                        $count_without = count($arr_withoutComponents);
                                        $count_standard = count($arr_standard_components);
                                        $countss = max($count_summary, $count_without, $count_standard);

                                            $sum = 0;
                                            $tot = 0;
                                            $dd = 0;
                                            $monthly_ctc=0;
                                            $net = 0;
                                        ?>
                                            <?php for ($i = 0; $i < $countss; $i++) {
                                            ?>
                                                <tr> <?php
                                                        $sum += isset($arr_data[$i]['ectc']['salary_amount']) ? $arr_data[$i]['ectc']['salary_amount'] : 0;
                                                         $monthly_ctc += isset($arr_standard_components[$i]['ectc']['structure_det_value']) ? $arr_standard_components[$i]['ectc']['structure_det_value'] : 0; // Edited by Akshay on 6-1-2026
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
                                                    <td><?php echo isset($arr_standard_components[$i]['ectc']['salary_head_item_desc']) ? $arr_standard_components[$i]['ectc']['salary_head_item_desc'] : ''; ?></td>
                                                    <!-- Edited by Akshay on 6-1-2026 -->
                                                    <td><?php echo isset($arr_standard_components[$i]['ectc']['structure_det_value']) ? abs(round($arr_standard_components[$i]['ectc']['structure_det_value'], 2)) : ''; ?></td>
                                                    <!-- End -->
                                                </tr>

                                            <?php } ?>
                                            <tr style="background: #cccccc ;">
                                                <th>Total</th>
                                                <th><?php echo abs(round($sum)); ?></th>
                                                <th>Total </th>
                                                <th><?php echo abs(round($dd, 2)); ?></th>
                                                <th>Monthly CTC </th>
                                                <th><?php echo abs(round($monthly_ctc, 2)); ?></th>
                                            </tr>
                                            <!-- edited by megha on 16/11/19 settlement amount  -->
                                            <?php if ($value['summary']['0']['ed']['status'] == 2) { ?>
                                                <tr style="background: #cccccc ;">
                                                    <th >Settlement Amount </th>
                                                    <th colspan="5"><?php echo $value['settle']; ?></th>
                                                </tr>
                                            <?php } ?>
                                            <!-- end -->
                                            <!-- edited by megha on 30_05_19 round off, edited by megha on 16/11/19 settlement amount  -->

                                                  <?php
                                                      $netPay = round($sum + $dd + $value['settle']);
                                                   ?>
                                            <tr style="background: #cccccc ;">
                                                <th >Net Pay </th>
                                                <th> <?php echo round($netPay); ?></th>
                                                <th colspan="4">(In Words) : <?php echo numberToWords($netPay); ?> Only</th>
                                                
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
                            </div>

                        <?php
                                }
                            }
                        }
                    }
                    if ($i == '0') { ?>
                        <div style="font-size: 25px;text-align:center; background-color:#F7D3D2;">
                            There is no data available</div>
                    <?php } ?>
                    <!-- /.box-body -->
                </div>
            </div>
        </div>
    
    </div>

<?php } else {
    
 

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
            /*border: #000000 solid thin;*/
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
            /*border: 1px solid #f4f4f4;*/
            width: 100%;
            max-width: 100%;
            margin-bottom: 20px;
            /*background-color: transparent;*/
            border-spacing: 0;
            border-collapse: collapse;
            /*border: 1px solid black;
       margin-left: 0px;*/
        }

        td,
        th {
            text-align: left;
            padding: 8px;
            
            font-weight: normal;
            /*font-size: 11px;*/
            font-size: 12px;
            /*font-family: serif;*/
            line-height: 1.42857143;
            /* word-wrap: break-word; */
            vertical-align: top;
            color: black;
              word-wrap: break-word;
        white-space: normal;
        page-break-inside: avoid;
        /* max-width: 200px; */
            /* border: 1px solid black; */
        }
        
 tr{
    page-break-inside: avoid;
 }
   

    .salary-content {
        margin: 20px auto;
        padding: 10px;
        border: 1px solid black;
        box-sizing: border-box;
        width: 95%;
        page-break-inside: avoid;
    }
        
    </style>


    <?php
    //                $i = 0;
    //                foreach ($arr_salary_for_template as $value) {
    //                if (count($value['summary'])!= 0 ) {
    //                $i += 1;
    ?>
    <?php
    $i = 0;
    if ($cr == 'EmployeeDetails') {
        foreach ($arr_salary_for_template as $value) {

            if (count($value['summary']) !== 0 || count($value['withoutcomponent']) !== 0) {
                $i += 1;
    ?>

                <page backtop="50mm"  backleft="4mm"  >

                    <page_header>


                        <!--        <div style="text-align:right; width:100%">

            <?php echo date("l,F j, Y"); ?> </div>-->
                        <div style="text-align:left; width:100%; ">
                            <?php if (isset($arr_comp_contact_info['CompanyContactInfo']['logo']) && !empty($arr_comp_contact_info['CompanyContactInfo']['logo'])) { ?>
                                <div style="width: 20%; margin-left: 20px; font-size: 18px; ">
                                    <img style=" margin-left: 20px; margin-top: 40px; " src="http://<?php echo $_SERVER['HTTP_HOST'] . $this->webroot . $arr_comp_contact_info['CompanyContactInfo']['logo']; ?>" height="100" width="100" class="img-circle" alt="Company Logo" />
                                </div>
                                <!--<img style=" margin-left: 70px; " src="http://<?php echo $_SERVER['HTTP_HOST'] . $this->webroot . $arr_comp_contact_info['CompanyContactInfo']['logo']; ?>" height="50" width="70" class="img-circle" alt="Company Logo" />-->
                            <?php } ?>
                            <div style="width: 80%; margin-left: 100px; margin-top: 40px; position : absolute ; float: left; font-size: 14px; ">
                                <div style=" text-align: center;">
                                    <p>FORM XIII –See Rules 29(2)</p>
                                </div>
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
                        <h3 style="text-align: center;padding-bottom: 20px;padding-top: 10px;"><?php echo 'Salary Slip - ' . "$mname-" . $year; ?></h3>
                        <br>
                      
                    </page_header>
                    <page_footer>

                        <div style="width: 100%; text-align: right;padding-right:20px;">
                            page [[page_cu]]/[[page_nb]]
                        </div>
                        <div style="width: 100%;padding-left:20px;">
                            Downloaded By <?php echo $user_name; ?> <?php echo date("l,F j, Y"); ?>
                        </div>
                    </page_footer>
                    <bookmark title="Salaryslip" level="0"></bookmark>
                </page>
                <?php
                //echo $this->element('reportadminheader', array(
                //'title' => 'Salary Slip - '.date("M Y",strtotime($value['summary']['0']['ectc']['month_year']))));
                ?>

                <div class="salary-content" style="margin: 20px auto; padding: 5px; border: 1px solid #000; box-sizing: border-box; width: 95%;">

                <table class="table" align="center" style="margin-top: 20px;padding-left:12px;padding-right:12px">
                    <tbody>

                        <tr>
                             <td style="border-right-style: hidden;  " ><strong style="text-align:  left ;width:50%; ">Employee Code &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <?php echo isset($value['empdet']['0']['ep']['emp_company_id']) ? $value['empdet']['0']['ep']['emp_company_id'] : ''; ?></strong></td>
                              <td style="border-right-style: hidden;  " ><strong style="text-align:  left ;width:50%; ">Employee Name &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <?php echo isset($value['summary']['0']['payroll_master']['emp_name']) ? $value['summary']['0']['payroll_master']['emp_name'] : ''; ?></strong></td>
                                                                                                                                                                                                                                         
                        </tr>

                        <tr>
                            <td style="border-right-style: hidden;  margin-right: 80px;" ><span style="text-align:  left ;width:50%; ">DOJ&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <?php if(!empty($value['summary']['0']['payroll_master']['joining_date'])){
                                                                                                                                                                                                                                                    echo date('d-m-Y', strtotime($value['summary']['0']['payroll_master']['joining_date']));
                                                                                                                                                                                                                                                         }else{
                                                                                                                                                                                                                                                    echo date('d-m-Y', strtotime($value['summary']['0']['ep']['joining_date']));} ?></span>
                            </td>
                            <td style="border-right-style: hidden; border-bottom-style: hidden;width:50%;" >DOB&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <span style="margin-left: 1px; text-align: center;"><?php echo date('d-m-Y', strtotime($value['summary']['0']['ed']['date_of_birth']));  ?></span></td>
                                                                                                                                                                                                                                                   
                                                                                                                                                                        
                        </tr>

                        <tr>
                             
                            <th style="border-right-style: hidden; border-bottom-style: hidden;width:50%;" >PF No.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:<span style="margin-left: 1px; text-align: center;">
                                    <?php echo isset($value['summary']['0']['ed']['company_pf']) ? $value['summary']['0']['ed']['company_pf'] : ''; ?> </span> </th>
                            <th style="border-right-style: hidden;  margin-right: 80px;" ><span style="text-align:  left ;width:50%; ">Designation&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <?php 
                                               if (!empty($value['summary']['0']['payroll_master']['desig'])) {
            echo $value['summary']['0']['payroll_master']['desig'];
        } else {
            echo isset($value['empdet']['0']['dd']['desig_name']) ? $value['empdet']['0']['dd']['desig_name'] : '';
        }
                                                  ?></span></th>
                        </tr>
                        <tr>
                            
                        <th style="border-right-style: hidden; border-bottom-style: hidden;width:50%;" >UAN&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:<span style="margin-left: 1px; text-align: center;">
                                    <?php echo isset($value['summary']['0']['ed']['pf']) ? $value['summary']['0']['ed']['pf'] : ''; ?> </span> </th>
                            <th style="border-right-style: hidden;  margin-right: 80px;width:50%;" ><span style="text-align:  left ;width:50%; ">Department&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <?php 
                                                 if (!empty($value['empdet']['0']['payroll_master']['departments'])) {
                                                    echo wordwrap($value['empdet']['0']['payroll_master']['departments'], 22, "<br>\n", TRUE);
                                                } else {
                                                   echo wordwrap(isset($value['empdet']['0']['d']['dept_name']) ?$value['empdet']['0']['d']['dept_name'] : '', 21, "<br>\n", TRUE);
                                                 }
                                                  ?></span></th>
                        </tr>
                        <?php $bank_name = '';
                        $branch_name = '';
                        $ifsc_code = '';
                        $acc_number = '';
                        $bank = isset($value['empdet']['0']['payroll_master']['bank_details']) ? $value['empdet']['0']['payroll_master']['bank_details'] : '';
                        if ($bank != '') {
                            list($bank_name, $branch_name, $ifsc_code, $acc_number) = explode(',', $bank);
                        }
                        if ($bank_name == '') {
                            $bank_name = isset($value['summary']['0']['ed']['bank_name']) ? $value['summary']['0']['ed']['bank_name'] : '';
                        }
                        if ($branch_name == '') {
                            $branch_name = isset($value['summary']['0']['ed']['branch_name']) ? $value['summary']['0']['ed']['branch_name'] : '';
                        }
                        if ($ifsc_code == '') {
                            $ifsc_code = isset($value['summary']['0']['ed']['ifsc_code']) ? $value['summary']['0']['ed']['ifsc_code'] : '';
                        }
                        if ($acc_number == '') {
                            $acc_number = isset($value['summary']['0']['ed']['account_no']) ? $value['summary']['0']['ed']['account_no'] : '';
                        } 
                        $label = 'Bank Name :';
$bank_name = isset($bank_name) ? trim($bank_name) : '';
$line_limit = 18;

if (strlen($bank_name) <= $line_limit) {
    // Case: Short name, show on same line
    $formatted_bank = '<span style="white-space: nowrap;">' . $label . ' ' . htmlspecialchars($bank_name) . '</span>';
} else {
    // Case: Long name, wrap under colon
    $first_line = substr($bank_name, 0, $line_limit);
    $remaining = substr($bank_name, $line_limit);
    
    // Indentation equal to label length
    $indent = str_repeat('&nbsp;', strlen($label) + 1);

    $formatted_bank = '<span style="white-space: nowrap;">' . $label . ' ' . htmlspecialchars($first_line) . '</span><br>';
    $formatted_bank .= '<span>' . $indent . htmlspecialchars($remaining) . '</span>';
}
                        ?>
                        <tr>
                            <th style="border-right-style: hidden; border-bottom-style: hidden;width:50%;" >PAN No.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:<span style="margin-left: 1px; text-align: center;">
                                  <?php echo  isset($value['summary']['0']['ed']['pan_no']) ? $value['summary']['0']['ed']['pan_no'] : ''; ?> </span>
                        </th>
                           
     
                         <th style="border-right-style: hidden;width:50%;   " ><span>Bank Name&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:</span><span style="margin-left: 1px; text-align: center;">
                                    <?php
                                    echo wordwrap(isset($bank_name) ? $bank_name : '', 25, "<br>\n &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;", TRUE);
                                    ?> </span> </th>

                        </tr>
                        <tr>
                            <th style="border-right-style: hidden; border-bottom-style: hidden;width:50%;" >Aadhaar No.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:<span style="margin-left: 1px; text-align: center;">
                                    <?php echo isset($value['summary']['0']['ed']['id_card']) ? $value['summary']['0']['ed']['id_card'] : ''; ?> </span> </th>
                            
                            <th style="border-right-style: hidden;width:50%;    " >Account Number&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <span style="margin-left: 1px; text-align: center;"><?php
                                                                                                                                                                                                                                    echo wordwrap(isset($acc_number) ? $acc_number : '', 25, "<br>\n", TRUE);
                                                                                                                                                                                                                                    ?></span></th>
                        </tr>
                         <tr>
                        <th style="border-right-style: hidden; border-bottom-style: hidden;width:50%;" >ESIC No.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:<span style="margin-left: 1px; text-align: center;">
                                    <?php echo  isset($value['summary']['0']['ed']['esi']) ? $value['summary']['0']['ed']['esi'] : ''; ?> </span> </th>
                     </tr>
                        <tr>
                            <th style="border-right-style: hidden; border-right: 0px solid white; width:50%;  " ><span style="margin-left: 1px;text-align:  center ; ">Attendance Days&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <?php echo isset($value['attendance_days']) ? $value['attendance_days'] : '0'; ?>
                            
                         </span></th>
                            <th style="border-right-style: hidden; border-right: 0px solid white;width:50%;   " ><span style="margin-left: 1px;text-align:  center ; ">LOP Days&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: 
                            <?php echo isset($value['empdet']['0']['payroll_master']['loss_of_pay']) ? $value['empdet']['0']['payroll_master']['loss_of_pay'] : '0'; ?>
                             </span></th>
                           
                            
                        </tr>
                        <tr>
                             <th style="border-right-style: hidden; border-right: 0px solid white;width:50%;   " ><span style="margin-left: 1px;text-align:  center ; ">Leave Days&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:
                            <?php echo isset($value['summary']['0']['payroll_master']['days_leave']) ? $value['summary']['0']['payroll_master']['days_leave'] : '0'; ?>
                        </span></th>
                            <th style="border-right-style: hidden; border-right: 0px solid white; width:50%;  " ><span style="margin-left: 1px;text-align:  center ; ">OT Days&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: 
                            <?php echo isset($value['ot_days']['0']['0']['OTcount']) ? $value['ot_days']['0']['0']['OTcount'] : '0'; ?>
                             </span></th>
                    </tr>

                         <tr>
                            <th style="border-right-style: hidden; border-bottom-style: hidden;width:50%;" >Allotted leave for the year&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:<span style="margin-left: 1px; text-align: center;">
                                    <?php echo isset($value['ALY']['0']['0']['ALY']) ? $value['ALY']['0']['0']['ALY'] : '0'; ?> </span> </th>
                            <th style="border-right-style: hidden;   width:50%; " >Total leave balance&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <span style="margin-left: 1px; text-align: center;"><?php echo isset($value['LBY']['0']['0']['LBY']) ? $value['LBY']['0']['0']['LBY'] : '0'; ?></span></th>
                        </tr>
                            </tbody>
                            </table>
                            

                           <table class="table"  align="center" border="1" style="margin-top: 10px;width:100%;">
                                
                    <tbody>

                        <tr style="background: #cccccc ;">

                            <!--    <th>LEAVEPOLICY_GROUP_NAME</th> -->


                            <th style="width:22%;">Earnings</th>
                            <th style="width:10%;">Amount</th>
                            <th style="width:22%;">Deductions</th>
                            <th style="width:10%;">Amount</th>
                            <th style="width:22%;">Standard Salary Component</th>
                            <th style="width:10%;">Amount</th>


                        </tr>
                        <?php $arr_data = $value['summary'];
                        $arr_withoutComponents = $value['withoutcomponent'];
                         $arr_standard_components= $value['standardcomponent'];
                        ?>
                        <?php
                                       
                                    if (count($arr_data) >= 0) {
                                        $count_summary = count($arr_data);
                                        $count_without = count($arr_withoutComponents);
                                        $count_standard = count($arr_standard_components);
                                        $countss = max($count_summary, $count_without, $count_standard);

                                            $sum = 0;
                                            $tot = 0;
                                            $dd = 0;
                                            $monthly_ctc=0;
                                            $net = 0;
                                        ?>
                            <?php for ($i = 0; $i < $countss; $i++) {
                                //edited by megha on 15_5_19
                            ?>

                                 <tr> <?php
                                                        $sum += isset($arr_data[$i]['ectc']['salary_amount']) ? $arr_data[$i]['ectc']['salary_amount'] : 0;
                                                         $monthly_ctc += isset($arr_standard_components[$i]['ectc']['structure_det_value']) ? $arr_standard_components[$i]['ectc']['structure_det_value'] : 0; // Edited by Akshay on 6-1-2026
                                                        if (isset($arr_withoutComponents[$i]['ectc']))
                                                            $dd += isset($arr_withoutComponents[$i]['ectc']['salary_amount']) ? $arr_withoutComponents[$i]['ectc']['salary_amount'] : 0;
                                                        ?>
                                                    <!-- edited by megha on 30_05_19 round off  -->

                                                    <td style=" word-wrap: break-word;white-space: normal;width:22%;"><?php echo isset($arr_data[$i]['ectc']['salary_head_item_desc']) ? $arr_data[$i]['ectc']['salary_head_item_desc'] : ''; ?></td>
                                                    <!--<td><?php //echo isset($arr_data[$i]['ectc']['structure_det_value'])?round($arr_data[$i]['ectc']['structure_det_value'], 2):''; 
                                                            ?></td>-->

                                                    <td style=" word-wrap: break-word;white-space: normal;width:10%;"><?php echo isset($arr_data[$i]['ectc']['salary_amount']) ? abs(round($arr_data[$i]['ectc']['salary_amount'])) : ''; ?></td>

                                                    <td style=" word-wrap: break-word;white-space: normal;width:22%;"><?php echo isset($arr_withoutComponents[$i]['ectc']['salary_head_item_desc']) ? $arr_withoutComponents[$i]['ectc']['salary_head_item_desc'] : ''; ?></td>
                                                    <!--<td><?php //echo round(isset($arr_withoutComponents[$i]['ectc']['structure_det_value']) ? $arr_withoutComponents[$i]['ectc']['structure_det_value'] : '', 2); 
                                                            ?></td>-->
                                                    <!-- edited by megha on 08_07_19 '0' values removed  -->
                                                    <td style=" word-wrap: break-word;white-space: normal;width:10%;"><?php echo isset($arr_withoutComponents[$i]['ectc']['salary_amount']) ? abs(round($arr_withoutComponents[$i]['ectc']['salary_amount'], 2)) : ''; ?></td>
                                                    <td style=" word-wrap: break-word;white-space: normal;width:22%;"><?php echo isset($arr_standard_components[$i]['ectc']['salary_head_item_desc']) ? $arr_standard_components[$i]['ectc']['salary_head_item_desc'] : ''; ?></td>
                                                    <!-- Edited by Akshay on 6-1-2026 -->
                                                    <td style=" word-wrap: break-word;white-space: normal;width:10%;"><?php echo isset($arr_standard_components[$i]['ectc']['structure_det_value']) ? abs(round($arr_standard_components[$i]['ectc']['structure_det_value'], 2)) : ''; ?></td>
                                                    <!-- End -->
                                                </tr>

                            <?php } ?>
                              <tr style="background: #cccccc ;">
                                                <th style="width:22%;">Total</th>
                                                <th style="width:10%;"><?php echo abs(round($sum)); ?></th>
                                                <th style="width:22%;">Total </th>
                                                <th style="width:10%;"><?php echo abs(round($dd, 2)); ?></th>
                                                <th style="width:22%;">Monthly CTC </th>
                                                <th style="width:10%;"><?php echo abs(round($monthly_ctc, 2)); ?></th>
                                            </tr>
                                           
                                            <?php if ($value['summary']['0']['ed']['status'] == 2) { ?>
                                                <tr style="background: #cccccc ;">
                                                    <th>Settlement Amount</th>
                                                    <th colspan="5"><?php echo $value['settle']; ?></th>
                                                </tr>
                                            <?php } ?>
                                            <?php

                                        $netPay = round($sum + $dd + $value['settle']);
                                        ?>
                                        <!-- <div style="page-break-before: always;"></div> -->

                                        <tr style="background: #cccccc;">
                                            <th >Net Pay  </th>
                                            <th><?php echo $netPay; ?></th>
                                            <th colspan="4">(In Words) : <?php echo numberToWords($netPay); ?> Only</th>
                                        </tr>

                            <?php $arr_withoutComponents = $value['withoutcomponent']; ?>

                            <?php if (count($arr_withoutComponents) > 0) { ?>

                            <?php } ?>
                        <?php } else {
                        ?>
                            <tr>
                                <td colspan="4">No Components found under this data</td>
                            </tr>
                        <?php } ?>

                    </tbody>
                </table>
                <p style="padding-left:20px;font-size:11px;">*This is a System generated pay slip and does not require signature.</p>
                </div>
                
                
            <?php  }
        }
    } else {
        foreach ($arr_salary_for_template as $val) { ?>
            <?php foreach ($val as $value) {
                if (count($value['summary']) !== 0 || count($value['withoutcomponent']) !== 0) {
                    $i += 1;
            ?>
                     <page backtop="50mm"  backleft="6mm"  >

                        <page_header>


                            <!--        <div style="text-align:right; width:100%">

            <?php echo date("l,F j, Y"); ?> </div>-->
                            <div style="text-align:left; width:100%; ">
                                <?php if (isset($arr_comp_contact_info['CompanyContactInfo']['logo']) && !empty($arr_comp_contact_info['CompanyContactInfo']['logo'])) { ?>
                                    <div style="width: 20%; margin-left: 20px; font-size: 18px; ">
                                        <img style=" margin-left: 20px; margin-top: 40px; " src="http://<?php echo $_SERVER['HTTP_HOST'] . $this->webroot . $arr_comp_contact_info['CompanyContactInfo']['logo']; ?>" height="100" width="100" class="img-circle" alt="Company Logo" />
                                    </div>
                                    <!--<img style=" margin-left: 70px; " src="http://<?php echo $_SERVER['HTTP_HOST'] . $this->webroot . $arr_comp_contact_info['CompanyContactInfo']['logo']; ?>" height="50" width="70" class="img-circle" alt="Company Logo" />-->
                                <?php } ?>
                                <div style="width: 80%; margin-left: 100px; margin-top: 40px; position : absolute ; float: left; font-size: 14px; ">
                                    <div style=" text-align: center;">
                                        <p>FORM XIII –See Rules 29(2)</p>
                                    </div>
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
                            <h3 style="text-align: center;padding-bottom: 20px;padding-top: 10px;"><?php echo 'Salary Slip of ' . $val['0']['summary']['0']['br']['branch_name'] . ' - ' . "$mname-" . $year; ?></h3>
                            <br>
                        </page_header>
                        <page_footer>

                            <div style="width: 100%; text-align: right">
                                page [[page_cu]]/[[page_nb]]
                            </div>
                            <div style="width: 100%; text-align: left">
                                Downloaded By <?php echo $user_name; ?> <?php echo date("l,F j, Y"); ?>
                            </div>
                        </page_footer>
                        <bookmark title="Salaryslip" level="0"></bookmark>
                    </page>
                    <?php
                    //echo $this->element('reportadminheader', array(
                    //'title' => 'Salary Slip - '.date("M Y",strtotime($value['summary']['0']['ectc']['month_year']))));
                    ?>

                    <div class="salary-content" style="margin: 20px auto; padding: 5px; border: 1px solid #000; box-sizing: border-box; width: 95%;">

                <table class="table" align="center" style="margin-top: 20px;padding-left:12px;padding-right:12px">
                    <tbody>

                        <tr>
                             <td style="border-right-style: hidden;  " ><strong style="text-align:  left ;width:50%; ">Employee Code &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <?php echo isset($value['empdet']['0']['ep']['emp_company_id']) ? $value['empdet']['0']['ep']['emp_company_id'] : ''; ?></strong></td>
                              <td style="border-right-style: hidden;  " ><strong style="text-align:  left ;width:50%; ">Employee Name &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <?php echo isset($value['summary']['0']['payroll_master']['emp_name']) ? $value['summary']['0']['payroll_master']['emp_name'] : ''; ?></strong></td>
                                                                                                                                                                                                                                         
                        </tr>

                        <tr>
                            <td style="border-right-style: hidden;  margin-right: 80px;" ><span style="text-align:  left ;width:50%; ">DOJ&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <?php if(!empty($value['summary']['0']['payroll_master']['joining_date'])){
                                                                                                                                                                                                                                                    echo date('d-m-Y', strtotime($value['summary']['0']['payroll_master']['joining_date']));
                                                                                                                                                                                                                                                         }else{
                                                                                                                                                                                                                                                    echo date('d-m-Y', strtotime($value['summary']['0']['ep']['joining_date']));} ?></span>
                            </td>
                            <td style="border-right-style: hidden; border-bottom-style: hidden;width:50%;" >DOB&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; :<span style="margin-left: 1px; text-align: center;"><?php echo date('d-m-Y', strtotime($value['summary']['0']['ed']['date_of_birth']));  ?></span></td>
                                                                                                                                                                                                                                                   
                                                                                                                                                                        
                        </tr>

                        <tr>
                             
                            <th style="border-right-style: hidden; border-bottom-style: hidden;width:50%;" >PF No.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:<span style="margin-left: 1px; text-align: center;">
                                    <?php echo isset($value['summary']['0']['ed']['company_pf']) ? $value['summary']['0']['ed']['company_pf'] : ''; ?> </span> </th>
                            <th style="border-right-style: hidden;  margin-right: 80px;" ><span style="text-align:  left ;width:50%; ">Designation&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <?php 
                                               if (!empty($value['summary']['0']['payroll_master']['desig'])) {
            echo $value['summary']['0']['payroll_master']['desig'];
        } else {
            echo isset($value['empdet']['0']['dd']['desig_name']) ? $value['empdet']['0']['dd']['desig_name'] : '';
        }
                                                  ?></span></th>
                        </tr>
                        <tr>
                            
                        <th style="border-right-style: hidden; border-bottom-style: hidden;width:50%;" >UAN&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:<span style="margin-left: 1px; text-align: center;">
                                    <?php echo isset($value['summary']['0']['ed']['pf']) ? $value['summary']['0']['ed']['pf'] : ''; ?> </span> </th>
                            <th style="border-right-style: hidden;  margin-right: 80px;width:50%;" ><span style="text-align:  left ;width:50%; ">Department&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <?php 
                                                 if (!empty($value['empdet']['0']['payroll_master']['departments'])) {
                                                    echo wordwrap($value['empdet']['0']['payroll_master']['departments'], 22, "<br>\n", TRUE);
                                                } else {
                                                   echo wordwrap(isset($value['empdet']['0']['d']['dept_name']) ?$value['empdet']['0']['d']['dept_name'] : '', 22, "<br>\n", TRUE);
                                                 }
                                                  ?></span></th>
                        </tr>
                        <?php $bank_name = '';
                        $branch_name = '';
                        $ifsc_code = '';
                        $acc_number = '';
                        $bank = isset($value['empdet']['0']['payroll_master']['bank_details']) ? $value['empdet']['0']['payroll_master']['bank_details'] : '';
                        if ($bank != '') {
                            list($bank_name, $branch_name, $ifsc_code, $acc_number) = explode(',', $bank);
                        }
                        if ($bank_name == '') {
                            $bank_name = isset($value['summary']['0']['ed']['bank_name']) ? $value['summary']['0']['ed']['bank_name'] : '';
                        }
                        if ($branch_name == '') {
                            $branch_name = isset($value['summary']['0']['ed']['branch_name']) ? $value['summary']['0']['ed']['branch_name'] : '';
                        }
                        if ($ifsc_code == '') {
                            $ifsc_code = isset($value['summary']['0']['ed']['ifsc_code']) ? $value['summary']['0']['ed']['ifsc_code'] : '';
                        }
                        if ($acc_number == '') {
                            $acc_number = isset($value['summary']['0']['ed']['account_no']) ? $value['summary']['0']['ed']['account_no'] : '';
                        }
                        
                        ?>
                        <tr>
                            <th style="border-right-style: hidden; border-bottom-style: hidden;width:50%;" >PAN No.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:<span style="margin-left: 1px; text-align: center;">
                                  <?php echo  isset($value['summary']['0']['ed']['pan_no']) ? $value['summary']['0']['ed']['pan_no'] : ''; ?> </span>
                        </th>
                            
                            <th style="border-right-style: hidden;width:50%;   " ><span>Bank Name&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:</span><span style="margin-left: 1px; text-align: center;">
                                    <?php
                                    echo wordwrap(isset($bank_name) ? $bank_name : '', 25, "<br>\n &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;", TRUE);
                                    ?> </span> </th>
                        </tr>
                        <tr>
                            <th style="border-right-style: hidden; border-bottom-style: hidden;width:50%;" >Aadhaar No.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:<span style="margin-left: 1px; text-align: center;">
                                    <?php echo isset($value['summary']['0']['ed']['id_card']) ? $value['summary']['0']['ed']['id_card'] : ''; ?> </span> </th>
                            
                            <th style="border-right-style: hidden;width:50%;    " >Account Number&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <span style="margin-left: 1px; text-align: center;"><?php
                                                                                                                                                                                                                                    echo wordwrap(isset($acc_number) ? $acc_number : '', 25, "<br>\n", TRUE);
                                                                                                                                                                                                                                    ?></span></th>
                        </tr>
                         <tr>
                        <th style="border-right-style: hidden; border-bottom-style: hidden;width:50%;" >ESIC No.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:<span style="margin-left: 1px; text-align: center;">
                                    <?php echo  isset($value['summary']['0']['ed']['esi']) ? $value['summary']['0']['ed']['esi'] : ''; ?> </span> </th>
                     </tr>
                        <tr>
                            <th style="border-right-style: hidden; border-right: 0px solid white; width:50%;  " ><span style="margin-left: 1px;text-align:  center ; ">Attendance Days&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <?php echo isset($value['attendance_days']) ? $value['attendance_days'] : '0'; ?>
                            
                         </span></th>
                            <th style="border-right-style: hidden; border-right: 0px solid white;width:50%;   " ><span style="margin-left: 1px;text-align:  center ; ">LOP Days&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: 
                            <?php echo isset($value['empdet']['0']['payroll_master']['loss_of_pay']) ? $value['empdet']['0']['payroll_master']['loss_of_pay'] : '0'; ?>
                             </span></th>
                           
                            
                        </tr>
                        <tr>
                             <th style="border-right-style: hidden; border-right: 0px solid white;width:50%;   " ><span style="margin-left: 1px;text-align:  center ; ">Leave Days&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:
                            <?php echo isset($value['summary']['0']['payroll_master']['days_leave']) ? $value['summary']['0']['payroll_master']['days_leave'] : '0'; ?>
                        </span></th>
                            <th style="border-right-style: hidden; border-right: 0px solid white; width:50%;  " ><span style="margin-left: 1px;text-align:  center ; ">OT Days&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: 
                            <?php echo isset($value['ot_days']['0']['0']['OTcount']) ? $value['ot_days']['0']['0']['OTcount'] : '0'; ?>
                             </span></th>
                    </tr>

                         <tr>
                            <th style="border-right-style: hidden; border-bottom-style: hidden;width:50%;" >Allotted leave for the year&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:<span style="margin-left: 1px; text-align: center;">
                                    <?php echo isset($value['ALY']['0']['0']['ALY']) ? $value['ALY']['0']['0']['ALY'] : '0'; ?> </span> </th>
                            <th style="border-right-style: hidden;   width:50%; " >Total leave balance&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <span style="margin-left: 1px; text-align: center;"><?php echo isset($value['LBY']['0']['0']['LBY']) ? $value['LBY']['0']['0']['LBY'] : '0'; ?></span></th>
                        </tr>
                            </tbody>
                            </table>
                            

                           <table class="table"  align="center" border="1" style="margin-top: 10px;width:100%;">
                                
                    <tbody>

                        <tr style="background: #cccccc ;">

                            <!--    <th>LEAVEPOLICY_GROUP_NAME</th> -->


                            <th style="width:22%;">Earnings</th>
                            <th style="width:10%;">Amount</th>
                            <th style="width:22%;">Deductions</th>
                            <th style="width:10%;">Amount</th>
                            <th style="width:22%;">Standard Salary Component</th>
                            <th style="width:10%;">Amount</th>


                        </tr>
                        <?php $arr_data = $value['summary'];
                        $arr_withoutComponents = $value['withoutcomponent'];
                         $arr_standard_components= $value['standardcomponent'];
                        ?>
                        <?php
                                       
                                    if (count($arr_data) >= 0) {
                                        $count_summary = count($arr_data);
                                        $count_without = count($arr_withoutComponents);
                                        $count_standard = count($arr_standard_components);
                                        $countss = max($count_summary, $count_without, $count_standard);

                                            $sum = 0;
                                            $tot = 0;
                                            $dd = 0;
                                            $monthly_ctc=0;
                                            $net = 0;
                                        ?>
                            <?php for ($i = 0; $i < $countss; $i++) {
                                //edited by megha on 15_5_19
                            ?>

                                 <tr> <?php
                                                        $sum += isset($arr_data[$i]['ectc']['salary_amount']) ? $arr_data[$i]['ectc']['salary_amount'] : 0;
                                                         $monthly_ctc += isset($arr_standard_components[$i]['ectc']['structure_det_value']) ? $arr_standard_components[$i]['ectc']['structure_det_value'] : 0; // Edited by Akshay on 6-1-2026
                                                        if (isset($arr_withoutComponents[$i]['ectc']))
                                                            $dd += isset($arr_withoutComponents[$i]['ectc']['salary_amount']) ? $arr_withoutComponents[$i]['ectc']['salary_amount'] : 0;
                                                        ?>
                                                    <!-- edited by megha on 30_05_19 round off  -->

                                                    <td style=" word-wrap: break-word;white-space: normal;width:22%;"><?php echo isset($arr_data[$i]['ectc']['salary_head_item_desc']) ? $arr_data[$i]['ectc']['salary_head_item_desc'] : ''; ?></td>
                                                    <!--<td><?php //echo isset($arr_data[$i]['ectc']['structure_det_value'])?round($arr_data[$i]['ectc']['structure_det_value'], 2):''; 
                                                            ?></td>-->

                                                    <td style=" word-wrap: break-word;white-space: normal;width:10%;"><?php echo isset($arr_data[$i]['ectc']['salary_amount']) ? abs(round($arr_data[$i]['ectc']['salary_amount'])) : ''; ?></td>

                                                    <td style=" word-wrap: break-word;white-space: normal;width:22%;"><?php echo isset($arr_withoutComponents[$i]['ectc']['salary_head_item_desc']) ? $arr_withoutComponents[$i]['ectc']['salary_head_item_desc'] : ''; ?></td>
                                                    <!--<td><?php //echo round(isset($arr_withoutComponents[$i]['ectc']['structure_det_value']) ? $arr_withoutComponents[$i]['ectc']['structure_det_value'] : '', 2); 
                                                            ?></td>-->
                                                    <!-- edited by megha on 08_07_19 '0' values removed  -->
                                                    <td style=" word-wrap: break-word;white-space: normal;width:10%;"><?php echo isset($arr_withoutComponents[$i]['ectc']['salary_amount']) ? abs(round($arr_withoutComponents[$i]['ectc']['salary_amount'], 2)) : ''; ?></td>
                                                    <td style=" word-wrap: break-word;white-space: normal;width:22%;"><?php echo isset($arr_standard_components[$i]['ectc']['salary_head_item_desc']) ? $arr_standard_components[$i]['ectc']['salary_head_item_desc'] : ''; ?></td>
                                                    <!-- Edited by Akshay on 6-1-2026 -->
                                                    <td style=" word-wrap: break-word;white-space: normal;width:10%;"><?php echo isset($arr_standard_components[$i]['ectc']['structure_det_value']) ? abs(round($arr_standard_components[$i]['ectc']['structure_det_value'], 2)) : ''; ?></td>
                                                    <!-- End -->
                                                </tr>

                            <?php } ?>
                              <tr style="background: #cccccc ;">
                                                <th style="width:22%;">Total</th>
                                                <th style="width:10%;"><?php echo abs(round($sum)); ?></th>
                                                <th style="width:22%;">Total </th>
                                                <th style="width:10%;"><?php echo abs(round($dd, 2)); ?></th>
                                                <th style="width:22%;">Monthly CTC </th>
                                                <th style="width:10%;"><?php echo abs(round($monthly_ctc, 2)); ?></th>
                                            </tr>
                                           
                                            <?php if ($value['summary']['0']['ed']['status'] == 2) { ?>
                                                <tr style="background: #cccccc ;">
                                                    <th colspan="2">Settlement Amount</th>
                                                    <th colspan="4"><?php echo $value['settle']; ?></th>
                                                </tr>
                                            <?php } ?>
                                            <?php

                                        $netPay = round($sum + $dd + $value['settle']);
                                        ?>
                                        <!-- <div style="page-break-before: always;"></div> -->

                                        <tr style="background: #cccccc;">
                                            <th >Net Pay  </th>
                                             <th><?php echo $netPay; ?></th>
                                            <th colspan="4">(In Words) : <?php echo numberToWords($netPay); ?> Only</th>
                                        </tr>

                            <?php $arr_withoutComponents = $value['withoutcomponent']; ?>

                            <?php if (count($arr_withoutComponents) > 0) { ?>

                            <?php } ?>
                        <?php } else {
                        ?>
                            <tr>
                                <td colspan="4">No Components found under this data</td>
                            </tr>
                        <?php } ?>

                    </tbody>
                </table>
                <p style="padding-left:20px;font-size:11px;">*This is a System generated pay slip and does not require signature.</p>
                </div>

        <?php
                }
            }
        }
    }
    if ($i == '0') { ?>
        <div style="font-size: 25px;text-align:center; background-color:#F7D3D2;">
            There is no data available</div>
    <?php } ?>
<?php } ?>