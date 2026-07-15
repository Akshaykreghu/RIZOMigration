<?php if ($mode == '') {  ?>
    <div>
        <h2 style="text-align:center; font-weight: bold;">Overtime Department Wise Summary - <?php echo $month . " " . $year; ?></h2> <!--edited by ASHIN 04-07-24-->
        <h2 style="font-weight: bold;text-align: center;font-size: 19px;"><?php echo  "(Report Run by " . $user_id . " at " . $date_time . ")" ?> </h2>
        <?php if ($reporttype != 'Departments') { ?>
            <div class="">
                <div class="col-md-13">
                    <div class=" ">


                        <?php

                        if (empty($arr_leavepolicydetails_for_template)) {
                            echo "no data";
                        }
                        if (!empty($arr_leavepolicydetails_for_template)) { ?>
                            <?php

                            $no_data = 0;

                            // //edited by ASHIN on 13-07-24                     
                            //                 $total_val = array(
                            //                     'total_drtn' => 0,
                            //                     'total_aprvd_drtn' => 0,
                            //                     'ot_rate' => 0,
                            //                     'ot_amount' => 0
                            //                 );


                            foreach ($arr_leavepolicydetails_for_template as $val) {
                                // debug($val);
                                if (count($val['summary']) > 0) {
                                    $i = 0;

                                    $i += 1;
                            ?>
                                    <div class="box-body" style="overflow-y: auto;">

                                        <!-- <fieldset> -->
                                        <div class="row">

                                            <div class="col-md-12">
                                                <?php if ($reporttype != 'Departments') { ?>
                                                    <legend><?php echo $val['summary']['0']['Info']['branch']; ?> </legend>
                                                <?php } else { ?>
                                                    <legend> <?php //$status = (isset($val['summary']['0']['Info']['emp_status'])) && $val['summary']['0']['Info']['emp_status'] == "2" ? '  (Resigned)' : '';
                                                                echo $val['summary']['0']['Info']['department']; ?> </legend>

                                                <?php } ?>

                                            </div>
                                        </div>
                                        <!-- </fieldset> -->


                                        <table class="table table-bordered" style="overflow-y:auto;">

                                            <tr>
                                                <td><b>Sl No</b></td>
                                                <td><b>Employee ID</b></td>
                                                <td><b>User ID</b></b></td>
                                                <td><b>Employee Name</b></td>
                                                <td><b>Joining Date</b></td>
                                                <td><b>Branch</b></td>
                                                <td><b>Department</b></td>
                                                <td><b>Designation</b></td>

                                                <td><b>Termination Date</b></td>
                                                <td><b>Total Duration(In Hrs)</b></td>
                                                <td><b>Approved Duration(In Hrs)</b></td>
                                                <td><b>Overtime Rate</b></td>
                                                <td><b>Overtime Amount</b></td>
                                                <td><b>Remarks</b></td>
                                                <!--edited by ASHIN on 19-07-24-->
                                                <?php $total_val['total_dur'] = 0; ?>
                                                <?php $total_val['aprvd_dur'] = 0; ?>
                                                <!--edited by ASHIN on 20-07-24-->
                                                <?php $total_val['ovrtm_rate'] = 0; ?>
                                                <?php $total_val['ovrtm_amount'] = 0; ?>

                                            </tr>
                                            </thead>
                                            <tbody>

                                                <!--edited by ASHIN on 11-07-24-->
                                                <?php
                                                // debug($arr_leavepolicy_details); exit();
                                                $k = 1;
                                                $arr_leavepolicy_details = $val['summary'];

                                                // debug($arr_leavepolicy_details); exit();
                                                if (count($arr_leavepolicy_details) > 0) {

                                                    foreach ($arr_leavepolicy_details as $value) {

                                                ?>

                                                        <tr>

                                                            <td><?php echo $k; ?></td>
                                                            <td><?php echo $value['Info']['employee_id']; ?></td>
                                                            <td><?php echo isset($value['uc']['user_id']) ? $value['uc']['user_id'] : ''; ?></td>
                                                            <td><?php echo $value['OTMASTER']['emp_name']; ?><?php echo (isset($value['EmployeeDetails']['status'])) && $value['EmployeeDetails']['status'] == "2" ? '  (Resigned)' : ''; ?></td>
                                                            <td><?php echo date('d-m-Y', strtotime($value['Info']['joining_date'])); ?></td>
                                                            <td><?php echo $value['Info']['branch']; ?></td>

                                                            <td><?php echo $value['Info']['department']; ?></td>
                                                            <td><?php echo $value['Info']['designation']; ?></td>

                                                            <td><?php echo  isset($value['termination']['last_approved_working_date']) ? date('d-m-Y', strtotime($value['termination']['last_approved_working_date'])) : ''; ?></td>       <!--edited by ASHIN on 09-08-24-->
                                                            <td><?php echo round(($value['OTMASTER']['total_duration'] / 60), 2); ?></td>
                                                            <td><?php echo isset($value['OTMASTER']['set_duration']) ? round(($value['OTMASTER']['set_duration'] / 60), 2) : round(($value['OTMASTER']['total_duration'] / 60), 2); ?></td>
                                                            <!--edited by ASHIN on 20-07-24-->
                                                            <td><?php echo $value['eot']['ot_rate']; ?></td>
                                                            <td><?php echo round($value['eot']['ot_amount']); ?></td> <!--edited by ASHIN on 30-07-24-->
                                                            <td><?php echo $value['OTMASTER']['remarks']; ?></td> <!--edited by ASHIN on 30-07-24--->
                                                            <!--edited by ASHIN on 19-07-24-->
                                                            <?php $total_val['total_dur'] += round($value['OTMASTER']['total_duration'] / 60, 2); ?>
                                                            <?php $total_val['aprvd_dur'] += round($value['OTMASTER']['set_duration'] / 60, 2); ?>
                                                            <!--edited by ASHIN on 20-07-24-->
                                                            <?php $total_val['ovrtm_rate'] += $value['eot']['ot_rate']; ?>
                                                            <?php $total_val['ovrtm_amount'] += round($value['eot']['ot_amount']); ?> <!--edited by ASHIN on 30-07-24-->
                                                        </tr>

                                                <?php
                                                        $k++;
                                                        $no_data = 1;
                                                    }
                                                } ?>
                                                <!-- edited by ASHIN on 13-07-24 -->
                                                <!-- <tr>
    <td colspan="9" style="text-align: center; font-weight: bold;">TOTAL</td>
    <td><//?php echo round($total_val['total_drtn'] / 60, 2);?></td>
    <td><//?php echo round($total_val['total_aprvd_drtn'] / 60, 2);?></td>
    <td><//?php echo $total_val['ot_rate'];?></td>
    <td><//?php echo $total_val['ot_amount'];?></td>
    <td></td>
</tr> -->

                                                <!--edited by ASHIN on 19-07-24-->

                                                <tr>
                                                    <th colspan="9" style="text-align: center;">TOTAL</th>
                                                    <?php
                                                    foreach ($total_val as $key => $comp) { ?>
                                                        <th><?php echo $comp ?></th>
                                                    <?php }

                                                    ?>
                                                    <th></th>
                                                </tr>
                                            </tbody>
                                        </table>
                                    <?php } else {
                                    $no_data += 0;
                                    ?>
                                        <!-- <div style="font-size: 16px;text-align:left; ">
                                                            No data available under the selected criteria</div> -->

                                    <?php  }
                                    ?>

                                    <!-- </fieldset> -->
                                    <?php if ($no_data != 0) {
                                        // debug($no_data);
                                    ?>

                                    <?php } ?>
                                    </div>
                                <?php
                            }
                            if ($no_data == 0) { ?>
                                    <div style="font-size: 16px;text-align:left; ">
                                        No data available under the selected criteria</div>
                                <?php }
                        } else { ?>
                                <br>
                                <td style="font-weight:bold;font-size:28px !important;"><?php echo '<div style="font-size: 20px;text-align:left; background-color:;">
                       There is no data available under the selected criteria.</div>'; ?></td>
                            <?php } ?>
                    </div>
                </div>
            </div>
        <?php } else { ?>

            <?php
            if (true) { ?>

                <?php

                // //edited by ASHIN on 13-07-24                     
                //               $total_val = array(
                //                 'total_drtn' => 0,
                //                 'total_aprvd_drtn' => 0,
                //                 'ot_rate' => 0,
                //                 'ot_amount' => 0
                //             );
              
                //debug(($arr_leavepolicydetails_for_template));
                $empty = 0;
                foreach ($arr_leavepolicydetails_for_template as $val) {

                    $arr_leavepolicy_details = $val['summary'];
                    if (isset($val['summary']['0']['Info']['department'])) { ?>
                        <legend> <?php echo $val['summary']['0']['Info']['department']; ?> </legend>

                    <?php
                    }

                    if (count($arr_leavepolicy_details) > 0) {
                        $empty = 1; ?>
                        <div class="box-body" style="overflow-y: auto;"> <!--Edited by ASHIN on 20-07-24-->
                            <table class="table table-bordered" style="overflow-y: auto;">

                                <thead>
                                    <tr>
                                        <td><b>Sl No</b></td>
                                        <td><b>Employee ID</b></td>
                                        <td><b>User ID</b></b></td>
                                        <td><b>Employee Name</b></td>
                                        <td><b>Joining Date</b></td>
                                        <td><b>Branch</b></td>
                                        <td><b>Department</b></td>
                                        <td><b>Designation</b></td>
                                        <td><b>Termination Date</b></td>
                                        <td><b>Total Duration(In Hrs)</b></td>
                                        <td><b>Approved Duration(In Hrs)</b></td>
                                        <td><b>Overtime Rate</b></td>
                                        <td><b>Overtime Amount</b></td>
                                        <!-- <td><b>Approved/Rejected</b></td> -->
                                        <td><b>Remarks</b></td>

                                        <!--edited by ASHIN on 19-07-24-->
                                        <?php $total_val['total_dur'] = 0; ?>
                                        <?php $total_val['aprvd_dur'] = 0; ?>
                                        <!--edited by ASHIN on 20-07-24-->
                                        <?php $total_val['ovrtm_rate'] = 0; ?>
                                        <?php $total_val['ovrtm_amount'] = 0; ?>
                                    </tr>
                                </thead>
                                <?php
                                  $k = 1;
                                foreach ($arr_leavepolicy_details as $value) { ?>

                                    <tbody>
                                        <tr>
                                            <td><?php echo $k; ?></td>
                                            <td><?php echo $value['Info']['employee_id']; ?></td>
                                            <td><?php echo isset($value['uc']['user_id']) ? $value['uc']['user_id'] : ''; ?></td>
                                            <td><?php echo $value['OTMASTER']['emp_name']; ?><?php echo (isset($value['EmployeeDetails']['status'])) && $value['EmployeeDetails']['status'] == "2" ? '  (Resigned)' : ''; ?></td>
                                            <td><?php echo date('d-m-Y', strtotime($value['Info']['joining_date'])); ?></td> <!--edited by ASHIN on 05-07-24-->
                                            <td><?php echo $value['Info']['branch']; ?></td>

                                            <td><?php echo $value['Info']['department']; ?></td>
                                            <td><?php echo $value['Info']['designation']; ?></td>

                                            <td><?php echo  isset($value['termination']['last_approved_working_date']) ? date('d-m-Y', strtotime($value['termination']['last_approved_working_date'])) : ''; ?></td>       <!--edited by ASHIN on 09-08-24-->
                                            <td><?php echo round(($value['OTMASTER']['total_duration'] / 60), 2); ?></td>
                                            <td><?php echo isset($value['OTMASTER']['set_duration']) ? round(($value['OTMASTER']['set_duration'] / 60), 2) : round(($value['OTMASTER']['total_duration'] / 60), 2); ?></td>

                                            <!--edited by ASHIN on 20-07-24-->
                                            <td><?php echo $value['eot']['ot_rate']; ?></td>
                                            <td><?php echo round($value['eot']['ot_amount']); ?></td> <!--edited by ASHIN on 30-07-24-->
                                            <td><?php echo $value['OTMASTER']['remarks']; ?></td> <!--edited by ASHIN on 30-07-24--->

                                            <!--edited by ASHIN on 19-07-24-->

                                            <?php $total_val['total_dur'] += round($value['OTMASTER']['total_duration'] / 60, 2); ?>
                                            <?php $total_val['aprvd_dur'] += round($value['OTMASTER']['set_duration'] / 60, 2); ?>
                                            <!--edited by ASHIN on 20-07-24-->
                                            <?php $total_val['ovrtm_rate'] += $value['eot']['ot_rate']; ?>
                                            <?php $total_val['ovrtm_amount'] += round($value['eot']['ot_amount']); ?> <!--edited by ASHIN on 30-07-24-->
                                        </tr>
                                        <!--edited by ASHIN on 19-07-24-->

                                    <?php
                                    $k++;
                                } ?>
                                    <tr>
                                        <th colspan="9" style="text-align: center;">TOTAL</th>
                                        <?php
                                        foreach ($total_val as $key => $comp) { ?>
                                            <th><?php echo $comp ?></th>
                                        <?php }

                                        ?>
                                        <th></th>
                                    </tr>

                                    </tbody>
                            </table>
                        </div> <!--edited by ASHIN on 20-07-24-->
                    <?php   } else { ?>
                        <?php $empty += 0 ?>
                    <?php   }
                }
                if ($empty == 0) { ?>
                    <div style="font-size: 16px;text-align:left; ">
                        No data available under the selected criteria</div>
                <?php }
                ?>
    </div> <!--edited by Ashin on 13-07-24-->

<?php } else { ?>
    <td style="font-weight:bold;font-size:28px !important;"><?php echo '<div style="font-size: 20px;text-align:left; background-color:;">
                       There is no data available under the selected criteria.</div>'; ?></td>
<?php } ?>

<?php } ?>

<?php } else { ?>
  <style type="text/css">
    body {
        line-height: 1em; /* Adjusted line height to fit more content */
    }
/*edited by ASHIN on 08-08-24*/
    .block-container {
        width: 100%;
        padding: 10px; /* Reduced padding */
        border: #000000 solid thin;
    }

    .sub-head {
        border-bottom: #000000 solid thin;
    }

    .row {
        height: auto; /* Adjusted height for auto adjustment */      /*edited by ASHIN on 09-08-24*/
    }

    .col-md-4 {
        width: 33.33%;
        float: left;
    }

    table {
        border: 1px solid #000000;
        width: 80%;
        margin-bottom: 20px; /* Reduced margin-bottom */
        background-color: transparent;
        border-spacing: 0;
        border-collapse: collapse;
    }

    td,
    th {
        text-align: center;
        padding: 2px; /* Reduced padding */
        line-height: 1; /* Adjusted line height */
        vertical-align: top;
        border: 1px solid #000000;
    }
    
    /* edited by ASHIN on 08-08-24 */   
        th.remrk, td.remrk {
        width: 18%; 
        }
        th.sl-no, td.sl-no {
        width: 2%; 
        }
        th.emp-name, td.emp-name {
        width: 12%; 
        }
        th.emp-id, td.emp-id {
        width: 11%; 
        }
        th.cmp-id, td.cmp-id {
        width: 11%; 
        }
        th.brnch, td.brnch {
        width: 11%; 
        }
        th.desig, td.desig {
        width: 9%; 
        }
        th.dprtmnt, td.dprtmnt {
        width: 9%; 
        }
        th.termin-date, td.termin-date{
        width: 7.5%;
        }
        th.ot-rate, td.ot-rate {
        width: 6%; 
        }
        th.ot-amnt, td.ot-amnt {
        width: 6%; 
        }
        th.total-dur, td.total-dur {
        width: 6%; 
        }
        th.aprvd-dur, td.aprvd-dur {
        width: 7%; 
        }

</style>


    <?php
    echo $this->element('reportadminheader', array(
        'title' => 'Overtime Department Wise Summary - ' . $month . " " . $year . '<br> (Report Run by ' . $user_id . ' at ' . $date_time.')'              //edited by ASHIN on 04-07-24
    ));
    ?>
    <?php if (count($arr_leavepolicydetails_for_template) == 0) { ?>
        <div style="font-size: 16px;text-align:left;">
            No Data Available under the selected criteria
        </div>
    <?php } else { ?>
        <?php if (count($arr_leavepolicydetails_for_template) > 0) { ?>
            <div class="box-body">
                <?php $hasData = false; foreach ($arr_leavepolicydetails_for_template as $val) {
                    if (count($val['summary']) > 0) {
                        $hasData = true;
                        $i = 0;
                        $i += 1;
                ?>
                        <div class="row">
                            <div class="col-md-12">
                                <?php if ($reporttype != 'Departments') { ?>
                                    <h3><?php echo $val['summary']['0']['Info']['branch']; ?> </h3>
                                <?php } else { ?>
                                    <h3><?php echo $val['summary']['0']['Info']['department']; ?> </h3>
                                <?php } ?>
                            </div>
                        </div>
                        <br>
                        <table class="table table-bordered" align="center">
                            <thead>
                                <tr>
                                    <!--edited by ASHIN on 04-07-24-->
                                    <th class="sl-no">Sl No</th>
                                    <th class="emp-id">Employee ID</th>
                                    <th class="cmp-id">User ID</th>
                                    <th class="emp-name">Employee Name</th>
                                    <th>Joining Date</th>
                                    <th class="brnch">Branch</th>
                                    <th class="dprtmnt">Department</th>
                                    <th class="desig">Designation</th>
                                    <th class="termin-date">Termination Date</th>
                                    <th class="total-dur">Total Duration (In Hrs)</th>
                                    <th class="aprvd-dur">Approved Duration (In Hrs)</th>
                                    <th class="ot-rate">Overtime Rate</th>
                                    <th class="ot-amnt">Overtime Amount</th>
                                    <th class="remrk">Remarks</th> <!--edited by ASHIN on 04-07-24-->
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $k = 1;
                                $arr_leavepolicy_details = $val['summary'];
                                //edited by ASHIN on 20-07-24       
                                $total_val = array(
                                    'total_drtn' => 0,
                                    'total_aprvd_drtn' => 0,
                                    'ovrtm_rate' => 0,
                                    'ovrtm_amount' => 0
                                );
                                foreach ($arr_leavepolicy_details as $value) {
                                    //edited by ASHIN on 20-07-24             
                                    $total_val['total_drtn'] += $value['OTMASTER']['total_duration'];
                                    $total_val['total_aprvd_drtn'] += isset($value['OTMASTER']['set_duration']) ? $value['OTMASTER']['set_duration'] : $value['OTMASTER']['total_duration'];
                                    $total_val['ovrtm_rate'] += $value['eot']['ot_rate'];
                                    $total_val['ovrtm_amount'] += $value['eot']['ot_amount'];
                                ?>
                                    <tr>
                                        <!--edited by ASHIN on 04-07-24-->
                                        <td class="sl-no"><?php echo $k; ?></td>
                                        <td class="emp-id"><?php echo $value['Info']['employee_id']; ?></td>
                                        <td class="cmp-id"><?php echo $value['uc']['user_id']; ?></td>
                                        <td class="emp-name"><?php echo $value['OTMASTER']['emp_name']; ?><?php echo (isset($value['EmployeeDetails']['status'])) && $value['EmployeeDetails']['status'] == "2" ? '  (Resigned)' : ''; ?></td>         <!--edited by ASHIN on 12-08-24--->
                                        <td><?php echo date('d-m-Y', strtotime($value['Info']['joining_date'])); ?></td>
                                        <td class="brnch"><?php echo $value['Info']['branch']; ?></td>
                                        <td class="dprtmnt"><?php echo $value['Info']['department']; ?></td>
                                        <td class="desig"><?php echo $value['Info']['designation']; ?></td>
                                        <td class="termin-date"><?php echo isset($value['termination']['last_approved_working_date']) ? date('d-m-Y', strtotime($value['termination']['last_approved_working_date'])) : ''; ?></td>       <!--edited by ASHIN on 09-08-24-->

                                        <td class="total-dur"><?php echo round(($value['OTMASTER']['total_duration'] / 60), 2); ?></td>
                                        <td class="aprvd-dur"><?php echo isset($value['OTMASTER']['set_duration']) ? round(($value['OTMASTER']['set_duration'] / 60), 2) : round(($value['OTMASTER']['total_duration'] / 60), 2); ?></td>
                                        <!--edited by ASHIN on 20-07-24-->
                                        <td class="ot-rate"><?php echo $value['eot']['ot_rate']; ?></td>
                                        <td class="ot-amnt"><?php echo round($value['eot']['ot_amount']); ?></td> <!--edited by ASHIN on 30-07-24-->
                                        <td class="remrk"><?php echo $value['OTMASTER']['remarks']; ?></td> <!--edited by ASHIN on 30-07-24--->
                                    </tr>
                                <?php
                                    $k++;
                                }
                                //edited by ASHIN on 20-07-24         
                                $total_drtn = round($total_val['total_drtn'] / 60, 2);
                                $total_aprvd_drtn = round($total_val['total_aprvd_drtn'] / 60, 2);
                                $total_ot_rate = $total_val['ovrtm_rate'];
                                $total_ot_amnt = round($total_val['ovrtm_amount']);           //<!--edited by ASHIN on 30-07-24-->
                                ?>
                                <!--edited by ASHIN on 15-07-24-->
                                <tr>
                                    <td colspan="9" style="text-align: center; font-weight: bold;">TOTAL</td>
                                    <td><?php echo $total_drtn; ?></td>
                                    <td><?php echo $total_aprvd_drtn; ?></td>
                                    <td><?php echo $total_ot_rate; ?></td>
                                    <td><?php echo $total_ot_amnt; ?></td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                        <br>
                <?php
                    }
                }
                
        if (!$hasData) { // Display message only if no data is found
    ?>
        <div style="font-size: 16px; text-align:left;">
            No Data Available under the selected criteria
        </div>
    <?php
    }
    ?>
            </div>
        <?php } else { ?>
            <div style="font-size: 16px;text-align:left;">
            No Data Available under the selected criteria
        </div>

        <?php } ?>
<?php }
} ?>