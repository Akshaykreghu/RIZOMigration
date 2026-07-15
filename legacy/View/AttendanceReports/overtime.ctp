<?php if ($mode == '') {  ?>
    <div>
        <h2 style="text-align:center; font-weight: bold;">Approved Over Time - <?php echo $month . " " . $year; ?></h2>
        <h2 style="font-weight: bold;text-align: center;font-size: 19px;"><?php echo  "(Report Run by " . $user_id . " at " . $date_time . ")" ?> </h2>
        <?php if ($reporttype != 'EmployeeDetails') { ?>
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
                                                    <?php  if ($reporttype != 'EmployeeDetails') { ?>
                                                        <legend><?php echo $val['summary']['0']['Info']['branch']; ?> </legend>
                                                    <?php } else { ?>
                                                        <legend> <?php $status = (isset($val['summary']['0']['Info']['emp_status'])) && $val['summary']['0']['Info']['emp_status'] == "2" ? '  (Resigned)' : '';
                                                                    echo $val['summary']['0']['Info']['EmpName'] . $status; ?> </legend>

                                                    <?php } ?>

                                                </div>
                                            </div>
                                        <!-- </fieldset> -->


                                        <table class="table table-bordered" style="overflow-y:auto;">

                                            <tr>
                                                <td><b>Sl No</b></td>
                                                <td><b>Employee ID</b></td>
                                                <td><b>Company ID</b></b></td>
                                                <td><b>Employee Name</b></td>
                                                <td><b>Joining Date</b></td>
                                                <td><b>Branch</b></td>
                                                <td><b>Department</b></td>
                                                <td><b>Designation</b></td>

                                                <td><b>Termination Date</b></td>
                                                <td><b>Total Duration(In Hrs)</b></td>
                                                <td><b>Approved Duration(In Hrs)</b></td>
                                                <!-- <td><b>Approved/Rejected</b></td> -->
                                                <td><b>Remarks</b></td>
                                            </tr>
                                            </thead>
                                            <tbody>


                                                <?php
                                                // debug($arr_leavepolicy_details); exit();
                                                $k = 1;
                                                $arr_leavepolicy_details = $val['summary'];

                                                // debug($arr_leavepolicy_details); exit();
                                                if (count($arr_leavepolicy_details) > 0) {

                                                    foreach ($arr_leavepolicy_details as $value) { ?>

                                                        <tr>

                                                            <td><?php echo $k; ?></td>
                                                            <td><?php echo $value['Info']['employee_id']; ?></td>
                                                            <td><?php echo isset($value['uc']['user_id'])? $value['uc']['user_id']:''; ?></td>
                                                            <td><?php echo $value['OTMASTER']['emp_name']; ?><?php echo (isset($value['EmployeeDetails']['status'])) && $value['EmployeeDetails']['status'] == "2" ? '  (Resigned)' : ''; ?></td>
                                                            <td><?php echo $value['Info']['joining_date']; ?></td>
                                                            <td><?php echo $value['Info']['branch']; ?></td>

                                                            <td><?php echo $value['Info']['department']; ?></td>
                                                            <td><?php echo $value['Info']['designation']; ?></td>

                                                            <td><?php echo $value['termination']['last_approved_working_date']; ?></td>
                                                            <td><?php echo round(($value['OTMASTER']['total_duration'] / 60), 2); ?></td>
                                                            <td><?php echo isset($value['OTMASTER']['set_duration']) ? round(($value['OTMASTER']['set_duration'] / 60), 2) : round(($value['OTMASTER']['total_duration'] / 60), 2); ?></td>
                                                            <!--                                                    <td><?php //echo isset($value['OTMASTER']['is_verified'])== "Y" ? "Yes": "NO"; 
                                                                                                                        ?></td>-->
                                                            <!--  <?php if ($value['OTMASTER']['is_verified'] == 'Y') {
                                                                        $approved = 'Yes';
                                                                    } else {
                                                                        $approved = 'No';
                                                                    }
                                                                    ?> -->
                                                            <!-- <td><?php echo $approved; ?></td> -->
                                                            <td><?php echo $value['OTMASTER']['remarks']; ?></td>
                                                        </tr>

                                                <?php
                                                        $k++;
                                                        $no_data = 1;
                                                    }
                                                } ?>
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
            //workingdebug
            //debug($arr_leavepolicydetails_for_template);exit;
            // if (count($arr_leavepolicydetails_for_template[0]['summary'])> 0) { 

         //   if (true) { ?>


                <?php
                //debug($arr_leavepolicy_details); exit();
                $k = 1;
                //debug(($arr_leavepolicydetails_for_template));
                $empty = 0;
                foreach ($arr_leavepolicydetails_for_template as $val) {
                    $arr_leavepolicy_details = $val['summary'];

                    // debug($arr_leavepolicy_details); exit();
                    // foreach($arr_leavepolicydetails_for_template as $val){
                    if (count($arr_leavepolicy_details) > 0) {
                        $empty = 1;
                        foreach ($arr_leavepolicy_details as $value) { ?>

                            <table class="table table-bordered" style="overflow-y: auto;">
                                <legend> <?php $status = (isset($val['summary']['0']['Info']['emp_status'])) && $val['summary']['0']['Info']['emp_status'] == "2" ? '  (Resigned)' : '';
                                            echo $val['summary']['0']['Info']['EmpName'] . $status; ?> </legend>
                                <thead>
                                    <tr>
                                        <td><b>Sl No</b></td>
                                        <td><b>Employee ID</b></td>
                                        <td><b>Company ID</b></b></td>
                                        <td><b>Employee Name</b></td>
                                        <td><b>Joining Date</b></td>
                                        <td><b>Branch</b></td>
                                        <td><b>Department</b></td>
                                        <td><b>Designation</b></td>
                                        <td><b>Termination Date</b></td>
                                        <td><b>Total Duration(In Hrs)</b></td>
                                        <td><b>Approved Duration(In Hrs)</b></td>
                                        <!-- <td><b>Approved/Rejected</b></td> -->
                                        <td><b>Remarks</b></td>
                                    </tr>
                                </thead>
                                <tbody>

                                    <tr>

                                        <td><?php echo $k; ?></td>
                                        <td><?php echo $value['Info']['employee_id']; ?></td>
                                        <td><?php echo isset($value['uc']['user_id'])? $value['uc']['user_id']:''; ?></td>
                                        <td><?php echo $value['OTMASTER']['emp_name']; ?><?php echo (isset($value['EmployeeDetails']['status'])) && $value['EmployeeDetails']['status'] == "2" ? '  (Resigned)' : ''; ?></td>
                                        <td><?php echo $value['Info']['branch']; ?></td>
                                        <td><?php echo $value['Info']['joining_date']; ?></td>
                                        <td><?php echo $value['Info']['department']; ?></td>
                                        <td><?php echo $value['Info']['designation']; ?></td>

                                        <td><?php echo $value['termination']['last_approved_working_date']; ?></td>
                                        <td><?php echo round(($value['OTMASTER']['total_duration'] / 60), 2); ?></td>
                                        <td><?php echo isset($value['OTMASTER']['set_duration']) ? round(($value['OTMASTER']['set_duration'] / 60), 2) : round(($value['OTMASTER']['total_duration'] / 60), 2); ?></td>
                                        <!--                                                    <td><?php //echo isset($value['OTMASTER']['is_verified'])== "Y" ? "Yes": "NO"; 
                                                                                                    ?></td>-->
                                        <!-- <?php
                                                if ($value['OTMASTER']['is_verified'] == 'Y') {
                                                    $approved = 'Yes';
                                                } else {
                                                    $approved = 'No';
                                                }
                                                ?>
                                                                <td><?php echo $approved; ?></td> -->
                                        <td><?php echo $value['OTMASTER']['remarks']; ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        <?php
                            $k++;
                        }
                    } else { ?>
                        <?php $empty += 0 ?>
                    <?php   }
                }
                if ($empty == 0) { ?>
                    <div style="font-size: 16px;text-align:left; ">
                        No data available under the selected criteria</div>
                <?php }
                ?>




            <?php// } else { ?>
<!--                <td style="font-weight:bold;font-size:28px !important;"><?php echo '<div style="font-size: 20px;text-align:left; background-color:;">
                       There is no data available under the selected criteria.</div>'; ?></td>-->
            <?php //} ?>
        <?php } ?>
        <!--        <div class="row">
                        <div class="form-group">
                            <div class="col-md-12" align="right">
                                <a href="#" class="btn btn-default" onclick="downloadReport('Overtime', 'pdf');" ><i class="icon-file"></i>Download As PDF</a>
                                a href="#" class="btn btn-default" onclick="downloadReport('Overtime', 'excel');"><i class="icon-file"></i>Download As Excel</a
                            </div>
                        </div>
                    </div>-->
    </div>
<?php } else { ?>
    <?php //echo '<style>'.file_get_contents("css/pdfbootstrap.css").'</style>';  
    ?>
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
            width: 80%;
            max-width: 80%;
            margin-bottom: 20px;
            background-color: transparent;
            border-spacing: 0;
            border-collapse: collapse;
        }

        td,
        th {
            text-align: left;
            padding: 8px;
            line-height: 1.42857143;
            vertical-align: top;
            border: 1px solid #B2B2B2;
        }
    </style>

    <?php
    echo $this->element('reportadminheader', array(
        'title' => 'Employees OT Attendance  Report - ' . $date
    ));
    ?>



    <!--        <h3>Employee OT Attendance  Report</h3>-->
    <div class="row">
        <div class="col-md-12">
            <div class="box ">
                <?php if (count($arr_leavepolicydetails_for_template) > 0) { ?>
                    <?php
                    foreach ($arr_leavepolicydetails_for_template as $val) {
                        if (count($val['summary']) > 0) {
                            $i = 0;

                            $i += 1;
                    ?>
                            <div class="box-body">


                                <div class="row">
                                    <div class="col-md-12">
                                        <h3>Branch :<?php echo $val['summary']['0']['Info']['branch']; ?> </h3>
                                    </div>
                                </div>



                                <br>

                                <table class="table table-bordered" align="center">
                                    <thead>
                                        <tr>
                                            <th>Sl No</th>
                                            <th>Employee Name</th>
                                            <th>Employee ID</th>
                                            <th>Branch</th>
                                            <th>Designation</th>
                                            <th>Department</th>
                                            <th>Joining Date</th>
                                            <th>Total Duration(In Hrs)</th>
                                            <th>Verified Duration(In Hrs)</th>
                                            <!--<td>Approved</td>-->
                                            <th>Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody>


                                        <?php
                                        $k = 1;
                                        $arr_leavepolicy_details = $val['summary'];
                                        foreach ($arr_leavepolicy_details as $value) {             ?>
                                            <tr>
                                                <td><?php echo $k; ?></td>
                                                <td><?php echo $value['OTMASTER']['emp_name']; ?></td>
                                                <td><?php echo $value['Info']['employee_id']; ?></td>
                                                <td><?php echo $value['Info']['branch']; ?></td>

                                                <td><?php echo $value['Info']['designation']; ?></td>
                                                <td><?php echo $value['Info']['department']; ?></td>
                                                <td><?php echo $value['Info']['joining_date']; ?></td>
                                                <td><?php echo round(($value['OTMASTER']['total_duration'] / 60), 2); ?></td>
                                                <td><?php echo isset($value['OTMASTER']['set_duration']) ? round(($value['OTMASTER']['set_duration'] / 60), 2) : round(($value['OTMASTER']['total_duration'] / 60), 2); ?></td>
                                                <!--                                                    <td><?php echo isset($value['OTMASTER']['is_verified']) == "Y" ? "Yes" : "NO"; ?></td>-->
                                                <td><?php echo $value['OTMASTER']['remarks']; ?></td>
                                            </tr>
                                        <?php
                                            $k++;
                                        }
                                        ?>

                                    </tbody>
                                </table>


                                <br>
                                <!-- <fieldset>
                                             <legend>Employee List</legend>
                                             <table class="table table-bordered">
                                                 <thead>
                                                   <tr>
                                                       <th>Name</th>
                                                       <th>Designation</th>
                                                       <th>Branch</th>
                                                   </tr>
                                                 </thead>
                                                 <tbody>
                    
                                                 </tbody>
                                             </table>
                                         </fieldset> -->
                            </div>
                            <!-- /.box-body -->
                    <?php
                        }
                    }
                } else { ?>
                    <tr>
                        <td colspan="4">No employees found</td>
                    </tr>
                <?php } ?>
            </div>
        </div>
    </div>


    <!-- /.box-body -->

<?php }
//die();
?>