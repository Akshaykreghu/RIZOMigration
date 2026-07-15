<!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous"> -->
<style>
    #report_table1 td,
    th {
        border-style: solid;
        border-color: #d4d4de;
        overflow-x: auto;
    }

    #report_table2 td,
    th {
        border-style: solid;
        border-color: #d4d4de;
        overflow-x: auto;
    }

    #report_table1 th,
    #report_table2 th {
        width: 10%;
    }

    #report_table1 td,
    #report_table2 td {
        width: 10%;
    }

    #report_table1 th:nth-child(1),
    #report_table2 th:nth-child(1) {
        width: 7%;
    }

    #report_table1 th:nth-child(2),
    #report_table2 th:nth-child(2) {
        width: 7%
    }

    #report_table1 th:nth-child(3),
    #report_table2 th:nth-child(3) {
        width: 13%
    }

    #report_table1 th:nth-child(4),
    #report_table2 th:nth-child(4) {
        width: 17%
    }

    #report_table1 th:nth-child(5),
    #report_table2 th:nth-child(5) {
        width: 17%
    }

    #report_table1 th:nth-child(6),
    #report_table2 th:nth-child(6) {
        width: 5%
    }

    #report_table1 th:nth-child(7),
    #report_table2 th:nth-child(7) {
        width: 15%;
    }

    .modal-content {
        width: 125% !important;
    }
</style>
<?php
if ($mode == '') {
?>
    <div class="modal-body" style="overflow-y:auto; padding-left:3%; padding-right:3%; padding-bottom:3%;" id="printovertimeContent">
        <h2 align="center"><b><?php echo "Over Time Bank Wise - " . $mname . "  "  . $year ?></b> </h2>
        <h2 style="font-weight: bold;text-align: center;font-size:20px;"> <?php echo "(Report Run by " . $user_id . " at " . $date_time . ")" ?></h2>

        <?php $i = 0;
        $table_count = 0;
        foreach ($arr_salary_for_template as $value) {
            foreach ($value as $valuees) {
                if (!empty($valuees)) {
                    $i++;
                }
            }
        }
        if ($i == '0') { ?>
            <div style="font-size: 16px;text-align:left; background-color:;">
                No data available under the selected criteria</div>
        <?php } else { ?>
            <div class="col-md-12">
                <?php if ($criteria != 'Banks') {
                      //debug($criteria); 
                    foreach ($arr_salary_for_template as $value) {
                        //debug($arr_salary_for_template);
                         ?>

                        <?php if (count($value['leavepolicyname']) <= 0) { ?>
                            <?php } else {
                            $bk = isset($value['leavepolicyname']['0']['0']['BANK_NAME']) ? $value['leavepolicyname']['0']['0']['BANK_NAME'] : '';
                            if ($criteria = 'LeavePolicyGroup' || $bk != '') {         //edited by ASHIN on 08-08-24
                               // debug($value);
                            ?>
                                <legend style="border: 0;font-size: 18px;">
                                    <?php if ($cr == 'EmployeeDetails') {
                                        echo $value['leavepolicyname']['0']['Info']['EmpName'];
                                        echo (isset($value['leavepolicyname']['0']['EmployeeDetails']['status'])) && $value['leavepolicyname']['0']['EmployeeDetails']['status'] == "2" ? '  (Resigned)' : '';
                                    } elseif ($cr == 'Units') {
                                        echo $value['leavepolicyname']['0']['Info']['branch'];
                                    } else {
                                        $bk = isset($value['leavepolicyname']['0']['EmployeeDetails']['bank_name']) ? $value['leavepolicyname']['0']['EmployeeDetails']['bank_name'] : '';     //edited by ASHIN on 08-08-24
                                                 //debug($value['leavepolicyname']['0']['EmployeeDetails']['bank_name']);

                                        echo ($bk != '') ? $bk : 'N/A';
                                    } ?></legend>
                                <div class="">
                                    <div style="overflow-x: auto;">
                                        <table class="table table-bordered" id="report_table1" style="overflow-x: auto;">
                                            <thead>
                                                <tr>

                                                    <th> ACCOUNT NO</th>
                                                    <th>AMOUNT</th>
                                                    <th>IFSC CODE</th>
                                                    <th>BENEFICIARY ACC</th>
                                                    <th>BENEFICIARY NAME</th>
                                                    <th>ADDRESS</th>
                                                    <th>ACCOUNT NAME</th>
                                                </tr>
                                            </thead>
                                            <?php
                                            $i = 1;
                                            $total = 0;
                                            $table_count = 0;
                                                               
                                            foreach ($value['leavepolicyname'] as $val) {
                                                //debug($val);
                                                $bank_name = '';
                                                $branch_name = '';
                                                $acc_number = '';
                                               // $bank = isset($val['payroll_master']['bank_details']) ? $val['payroll_master']['bank_details'] : '';
                                               // if ($bank != '') {
                                                 //   list($bank_name, $branch_name, $acc_number) = explode(',', $bank);
                                             //   }
                                                $ifsc_code = $val['EmployeeDetails']['ifsc_code'];

                                            ?>
                                                <tbody>

                                                    <?php if (true) { ?>

                                                        <?php $table_count = 0; ?>
                                                        <tr>
                                                            <!--<td><?php //echo $account_number;
                                                                    ?></td>-->
                                                            <td> <?php echo '338505040050011'; ?></td>
                                                            <!-- edited by ASHIN on 26-07-24-->
                                                            <!-- <td> <//?php
                        if (isset($val['eot']['ot_amount']) && !empty($val['eot']['ot_amount'])) {
                        echo round($val['eot']['ot_amount']);
                        $amt = $val['eot']['ot_amount'];
                        $total = $total + $amt;
                        } else {
                        echo '0';
                        } 
                        debug($amt);
                        ?>
                        </td> -->
                                                            <!-- edited by ASHIN on 30-07-24-->
                                                            <td> <?php
                                                                    echo round($val['eot']['ot_amount']);
                                                                    $amt = $val['eot']['ot_amount'];
                                                                    $total = round($total + $amt);        //edited by ASHIN on 08-08-24
                                                                    //debug($amt);
                                                                    ?>
                                                            </td>

                                                            <td><?php echo $ifsc_code; ?></td>
                                                            <td><?php echo $val['EmployeeDetails']['account_no']; ?></td>
                                                            <td><?php echo $val['Info']['EmpName'];
                                                                echo (isset($val['EmployeeDetails']['status'])) && $val['EmployeeDetails']['status'] == "2" ? '  (Resigned)' : ''; ?></td>
                                                            <!-- <td><? php // echo $bank_branch;
                                                                        ?></td>-->
                                                            <td><?php echo 'PANCODE'; ?></td>
                                                            <td><?php echo 'HERBAL ISOLATES P LTD'; ?></td>
                                                        </tr>
                                                    <?php $i++;
                                                    } else { ?>
                                                        <!-- <tr><td colspan="11" style="text-align:center;">
                    No data available under the selected criteria
</td></tr> -->
                                                <?php }
                                                } ?>
                                                <tr>
                                                    <td><b>Total</b></td>
                                                    <td><b><?php echo $total; ?></b></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                </tr>
                                                </tbody>
                                                <?php $table_count++;
                                                ?>
                                        </table>
                                    </div>
                                </div>
                            <?php  } ?>
                        <?php  } ?>

                    <?php } ?>
                    <?php if ($table_count == 0) { ?>
                        <div style="font-size: 16px;text-align:left; background-color:;">
                            No data available under the selected criteria</div>
                    <?php } ?>
                    <?php } else {
                    $i = 0;
                    foreach ($arr_salary_for_template as $value) {

                    ?>


                        <?php if (count($value['leavepolicyname']) > 0) { ?>

                            <legend style="border: 0;font-size: 18px;">
                                <?php $bk = isset($value['leavepolicyname']['0']['EmployeeDetails']['bank_name']) ? $value['leavepolicyname']['0']['EmployeeDetails']['bank_name'] : '';            //edited by ASHIN on 08-08-24

                                echo ($bk != '') ? $bk : 'N/A';  ?><?php ?></legend>
                            <div class="">
                                <div style="overflow-x: auto;">
                                    <table class="table table-bordered" id="report_table2" style="overflow-x: auto;">
                                        <thead>
                                            <tr>
                                                <th> ACCOUNT NO</th>
                                                <th>AMOUNT</th>
                                                <th>IFSC CODE</th>
                                                <th>BENEFICIARY ACC</th>
                                                <th>BENEFICIARY NAME</th>
                                                <th>ADDRESS</th>
                                                <th>ACCOUNT NAME</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $i = 1;
                                            $total = 0;
                                            foreach ($value['leavepolicyname'] as $val) {
                                                $bank_name = '';
                                                $acc_number = '';
                                                $branch_name = '';
                                              //  $bank = isset($val['payroll_master']['bank_details']) ? $val['payroll_master']['bank_details'] : '';
                                              //  if ($bank != '') {
                                              //      list($bank_name, $branch_name, $acc_number) = explode(',', $bank);
                                             //   }
                                                $ifsc_code = $val['EmployeeDetails']['ifsc_code'];
                                                $settle = 0;
                                                // foreach($arr_settle as $values){
                                                //   $pkey = isset($values['0']['info']['emp_pkey'])?$values['0']['info']['emp_pkey']:0;

                                                // if($val['EmployeeDetails']['emp_pkey'] == $pkey){
                                                //   $settle = $values[0][0]['sum(salary_amount)'];
                                                //}

                                                //} 
                                            ?>
                                                <?php if (true) { ?>
                                                    <tr>
                                                        <!-- <td ><?php // echo $account_number;
                                                                    ?></td>-->
                                                        <td> <?php echo '338505040050011'; ?></td>
                                                        <!-- edited by ASHIN on 30-07-24-->
                                                        <td><?php
                                                            echo round($val['eot']['ot_amount']);
                                                            $amt = $val['eot']['ot_amount'];
                                                            $total = round($total + $amt);       //edited by ASHIN on 08-08-24
                                                            ?>
                                                        </td>

                                                        <td><?php echo $ifsc_code; ?></td>
                                                        <td><?php echo $val['EmployeeDetails']['account_no']; ?></td>
                                                        <td><?php echo $val['Info']['EmpName'];
                                                            echo (isset($val['EmployeeDetails']['status'])) && $val['EmployeeDetails']['status'] == "2" ? '  (Resigned)' : ''; ?></td>
                                                        <!--<td><?php //echo $bank_branch;
                                                                ?></td>-->
                                                        <td><?php echo 'PANCODE'; ?></td>
                                                        <td><?php echo 'HERBAL ISOLATES P LTD'; ?></td>




                                                    </tr>
                                                <?php $i++;
                                                    $total = $total + $netamt;
                                                } else { ?>
                                                    <tr>
                                                        <td colspan="5" style="text-align:center;">
                                                            No data available under the selected criteria
                                                        </td>
                                                    </tr>
                                            <?php }
                                            } ?>
                                            <tr>
                                                <td><b>Total</b></td>
                                                <td><b><?php echo $total; ?></b></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                                <td></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php } ?>
                    <?php } ?>

                <?php  }  ?>
                <?php if ($i == 0) { ?>
                    <div style="font-size: 16px;text-align:left;">
                        No data available under the selected criteria</div>
                <?php } ?>
            </div> <?php }  ?>
    </div>
<?php  } else { ?>
    <?php //echo '<style>'.file_get_contents("css/pdfbootstrap.css").'</style>'; 
    ?>
    <style type="text/css">
        body {
            line-height: 1em;
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
            margin-bottom: 20px;
            background-color: transparent;
            border-spacing: 0;
            border-collapse: collapse;
        }

        td,
        th {
            text-align: center;
            padding: 8px;
            line-height: 1.42857143;
            vertical-align: top;
            border: 1px solid #B2B2B2;
            word-wrap: break-word;
            max-width: 120px;
            /* edited by ASHIN on 26-07-24 */
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /*edited by ASHIN on 26-07-24*/
        th.acnt-no,
        td.acnt-no {
            width: 17%;
        }

        th.amnt,
        td.amnt {
            width: 9%;
        }

        th.ifsc-code,
        td.ifsc-code {
            width: 17%;
        }

        th.acnt,
        td.acnt {
            width: 23%;
        }

        th.name,
        td.name {
            width: 20%;
        }

        th.adrs,
        td.adrs {
            width: 10%;
        }

        th.bnk-name,
        td.bnk-name {
            width: 25%;
        }
    </style>
    <?php
    echo $this->element('reportadminheader', array(
        'title' => 'Over Time Bank Wise - ' . $mname . "  "  . $year . '<br> Report Run by ' . $user_id . ' at ' . $date_time
    ));
    ?>

    <?php $i = 0;
    $table_count = 0;
    foreach ($arr_salary_for_template as $value) {
       // debug($arr_salary_for_template);
        foreach ($value as $valuees) {
           // debug($value);
            if (!empty($valuees)) {
                
                $i++;
            }
        }
    }
    if ($i == '0') {
         ?>
        <div style="font-size: 16px;text-align:left; ">
            No data available under the selected criteria</div>
    <?php } else { ?>

        <?php if ($criteria != 'Banks') {
            foreach ($arr_salary_for_template as $value) { ?>
                <div class="box-body">
                    <?php if (count($value['leavepolicyname']) <= 0) { ?>
                        <?php } else {
                        $bk = isset($value['leavepolicyname']['0']['0']['BANK_NAME']) ? $value['leavepolicyname']['0']['0']['BANK_NAME'] : '';
                        if ($criteria = 'LeavePolicyGroup' || $bk != '') {        //edited by ASHIN on 09-08-24
                        ?>
                            <h3>
                                <?php if ($cr == 'EmployeeDetails') {
                                    echo $value['leavepolicyname']['0']['Info']['EmpName'];
                                    echo (isset($value['leavepolicyname']['0']['EmployeeDetails']['status'])) && $value['leavepolicyname']['0']['EmployeeDetails']['status'] == "2" ? '  (Resigned)' : '';
                                } elseif ($cr == 'Units') {
                                    echo $value['leavepolicyname']['0']['Info']['branch'];
                                } else {
                                    $bk = isset($value['leavepolicyname']['0']['EmployeeDetails']['bank_name']) ? $value['leavepolicyname']['0']['EmployeeDetails']['bank_name'] : '';     //edited by ASHIN on 08-08-24


                                    echo ($bk != '') ? $bk : 'N/A';
                                } ?></h3>

                            <table class="table">
                                <thead>
                                    <tr>

                                        <th class="acnt-no"> ACCOUNT NO</th>
                                        <th class="amnt">AMOUNT</th>
                                        <th class="ifsc-code">IFSC CODE</th>
                                        <th class="acnt">BENEFICIARY ACC</th>
                                        <th class="name">BENEFICIARY NAME</th>
                                        <th class="adrs">ADDRESS</th>
                                        <th class="bnk-name">ACCOUNT NAME</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $i = 1;
                                    $total = 0;
                                    $table_count = 0;            //edited by ASHIN on 09-08-24
                                    foreach ($value['leavepolicyname'] as $val) {

                                        $bank_name = '';
                                        $branch_name = '';
                                        $acc_number = '';
                               //hided by ASHIN on 09-08-24         
                                        // $bank = isset($val['EmployeeDetails']['bank_name']) ? $val['EmployeeDetails']['bank_name'] : '';                //edited by ASHIN on 08-08-24
                                        // if ($bank != '') {
                                        //     list($bank_name, $branch_name, $ifsc_code, $acc_number) = explode(',', $bank);
                                        // }
                                        $ifsc_code = $val['EmployeeDetails']['ifsc_code'];
                                      
                                    ?>


                                        <?php if (true) { ?>
                                            <?php $table_count = 0; ?>
                                            <tr>
                                                <!-- <td class="acnt-no"><?php //echo $account_number;
                                                                            ?></td>-->
                                                <td class="acnt-no"> <?php echo '338505040050011'; ?></td>
                                                <!--edited by ASHIN on 30-07-24--->
                                                <td class="amnt"><?php
                                                                    echo round($val['eot']['ot_amount']);
                                                                    $amt = $val['eot']['ot_amount'];
                                                                    $total = round($total + $amt);
                                                                    ?> </td>

                                                <!-- edited by ASHIN on 26-07-24-->
                       

                                                <td class="ifsc-code"><?php echo $ifsc_code; ?></td>
                                                <td class="acnt"><?php echo $val['EmployeeDetails']['account_no']; ?></td>
                                                <td class="name"><?php echo $val['Info']['EmpName'];
                                                                    echo (isset($val['EmployeeDetails']['status'])) && $val['EmployeeDetails']['status'] == "2" ? '  (Resigned)' : ''; ?></td>
                                                <!-- <td class="adrs"><?php echo $bank_branch; ?></td>-->
                                                <td class="adrs"><?php echo 'PANCODE'; ?></td>
                                                <td class="bnk-name"><?php echo 'HERBAL ISOLATES P LTD'; ?></td>

                                            </tr>
                                        <?php $i++;
                                            //  $total = $total +$amt;
                                        } else { ?>

                                    <?php }
                                    } ?>
                                    <tr>
                                        <td><b>Total</b></td>
                                        <td><b><?php echo $total; ?></b></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>

                                    <?php $table_count++;
                                    ?>
                                </tbody>
                            </table>

                        <?php  } ?>
                    <?php  } ?>
                </div>
            <?php } ?>
            <?php if ($table_count == 0) { 
                //debug($table_count);?>
                <div style="font-size: 16px;text-align:left;">
                    No data available under the selected criteria</div>
            <?php } ?>
            <?php } else {
            $i = 0;
           // $table_count = 0;
            foreach ($arr_salary_for_template as $value) {
                //debug($value);
            ?>
                <div class="box-body">
                    <?php if (count($value['leavepolicyname']) > 0) { ?>


                        <h2>
                            <?php $bk = isset($value['leavepolicyname']['0']['EmployeeDetails']['bank_name']) ? $value['leavepolicyname']['0']['EmployeeDetails']['bank_name'] : '';            //edited by ASHIN on 08-08-24

                            echo ($bk != '') ? $bk : 'N/A';  ?><?php ?></h2>

                        <table class="table">
                            <thead>
                                <tr>
                                    <th class="acnt-no"> ACCOUNT NO</th>
                                    <th class="amnt">AMOUNT</th>
                                    <th class="ifsc-code">IFSC CODE</th>
                                    <th class="acnt">BENEFICIARY ACC</th>
                                    <th class="name">BENEFICIARY NAME</th>
                                    <th class="adrs">ADDRESS</th>
                                    <th class="bnk-name">ACCOUNT NAME</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $i = 1;
                                $total = 0;
                                foreach ($value['leavepolicyname'] as $val) {
                                    $bank_name = '';

                                    $acc_number = '';
                                    $branch_name = '';
                //hided by ASHIN on 09-08-24                    
                                    // $bank = isset($val['EmployeeDetails']['bank_name']) ? $val['EmployeeDetails']['bank_name'] : '';     //edited by ASHIN on 08-08-24
                                    // if ($bank = '') {
                                    //     list($bank_name, $branch_name, $ifsc_code, $acc_number) = explode(',', $bank);
                                    // }
                                    $ifsc_code = $val['EmployeeDetails']['ifsc_code'];
                                   
                                   
                                ?>
                                    <?php if (true) {
                                         ?>
                                        <tr>
                                            <!-- <td class="acnt-no"><?php // echo $account_number;
                                                                        ?></td>-->
                                            <td class="acnt-no"> <?php echo '338505040050011'; ?></td>
                                           

                                          
                                            <!-- edited by ASHIN on 26-07-24-->
                                            <td class="amnt">
                                                <?php
                                                if (isset($val['eot']['ot_amount']) && is_numeric($val['eot']['ot_amount'])) {
                                                    $amount = $val['eot']['ot_amount'];
                                                    $total = $total + $amount;
                                                    if (floor($amount) != $amount) {
                                                        echo number_format($amount, 2);
                                                    } else {
                                                        echo number_format($amount, 0);
                                                    }
                                                } else {
                                                    echo '0';
                                                }     ?>
                                            </td>

                                            <td class="ifsc-code"><?php echo $ifsc_code; ?></td>
                                            <td class="acnt"><?php echo $val['EmployeeDetails']['account_no']; ?></td>
                                            <td class="name"><?php echo $val['Info']['EmpName'];
                                                                echo (isset($val['EmployeeDetails']['status'])) && $val['EmployeeDetails']['status'] == "2" ? '  (Resigned)' : ''; ?></td>
                                            <!-- <td class="adrs"><?php //echo $bank_branch;
                                                                    ?></td>-->
                                            <td class="adrs"><?php echo 'PANCODE'; ?></td>
                                            <td class="bnk-name"><?php echo 'HERBAL ISOLATES P LTD'; ?></td>



                                        </tr>
                                    <?php $i++;
                                        //$total = $total +$netamt;
                                    } ?>

                                <?php   } ?>
                                <tr>
                                    <td><b>Total</b></td>
                                    <td><b><?php echo $total; ?></b></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <?php $table_count++;   ?>
                            </tbody>
                        </table>
                    <?php } ?>
                </div>
            <?php } ?>
            <?php if ($table_count == 0) { ?>
                <div style="font-size: 16px;text-align:left;">
                    No data available under the selected criteria</div>
            <?php } ?>
        <?php  }  ?>




<?php }
} ?>