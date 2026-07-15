<style>
    th{
        border-spacing: 0 0;
    }
</style>

<?php if ($mode == '') { ?>


    <div class="modal-body myDivToPrint" style="overflow-y: auto;"  id="toPrint">
    <legend style="text-align: center;font-size: 25px; "><b><?php echo 'Leave Comp Off New - '.$year; ?></b></legend>
    <h2 style="font-weight: bold;text-align: center;font-size: 19px;"><?php echo  "(Report Run by " . $user_id . " at " . $date_time . ")"?>  </h2>
        <div class="row">
            <div class="col-md-12" >
                <div class="box ">
                    <?php
                    $i = 0;
                    $empcount = 0;
                    foreach ($arr_leavepolicydetails_for_template as $value) {
                        if (count($value['compoff']) > 0) {
                            $empcount ++;
                            $i += 1;
                    ?>
                            <div class="">
                                    <h4 style="    font-weight: bold ; ">Report of <?php echo $value['emp_dets']['0']['employee_info']['EmpName']; ?><?php echo isset($value['status']['0']['emp_details']['status']) && $value['status']['0']['emp_details']['status'] == "2" ? '(Resigned)' : ''; ?>
                                        <?php

                                        ?>
                                    </h4>
                                  
                              
                                    <?php
                                    $arr_data = $value['summary'];
                                    $emp_dets = $value['emp_dets'];
                                    $arr_compoff = $value['compoff'];
                                    // $termination = isset(!empty($value['termin']))? $value['termin'] : '';
                                    if(!empty($value['termin'])){
                                        $termination = $value['termin']['0']['termination']['last_approved_working_date'];
                                    }else{
                                        $termination = ' ';
                                    }
                                   // debug($emp_dets);
                                    ?>
                                    <?php if (count($arr_compoff) > 0) { ?>
                                        <div class="row">
                                            <div class="col-md-12" style="overflow-x: auto;">
                                                <h4>Employee Details</h4>
                                                <table class="table table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>Employee Name</th>
                                                            <th>Employee ID</th>
                                                            <th>Company ID</th>
                                                            <th>Joining date</th>
                                                            <th>Branch</th>
                                                            <th>Department</th>
                                                            <th>Designation</th>
                                                            <th>Termination date</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td><?php echo $emp_dets['0']['employee_info']['EmpName']; ?><?php echo isset($value['status']['0']['emp_details']['status']) && $value['status']['0']['emp_details']['status'] == "2" ? '(Resigned)' : ''; ?></td>
                                                            <td><?php echo $emp_dets['0']['employee_info']['emp_id']; ?></td>
                                                            <td><?php echo $emp_dets['0']['employee_info']['employee_id']; ?></td>
                                                            <td><?php echo $emp_dets['0']['employee_info']['joining_date']; ?></td>
                                                            <td><?php echo $emp_dets['0']['employee_info']['branch']; ?></td>
                                                            <td><?php echo $emp_dets['0']['employee_info']['department']; ?></td>
                                                            <td><?php echo $emp_dets['0']['employee_info']['designation']; ?></td>
                                                            <td><?php echo $termination;?></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>

                                        </div>
                                                        <!-- New compoff table -->
                    <div style="overflow-x: auto;">
                            <h4>Comp Off Leave List</h4>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Sl No</th>
                                        <th> Accrued day </th>
                                        <th>Half day/Full day</th>
                                        
                                        <th>Corresponding date</th>
                                        <th>Status</th>
                                        <th>Leave taken count</th>
                                    </tr>

                                </thead>
                                <tbody>
                                    <?php $j = 1;
                                          $compoff = 0;
                                          $leavedays = 0;
                                         // debug($arr_compoff);
                                    ?>
                                  
                                    <?php foreach($arr_compoff as $value){?>
                                            <tr>
                            
                                                <td><?php echo $j;?></td>
                                                <td><?php echo isset($value['sb']['break_off_date'])? $value['sb']['break_off_date']:'';?></td>
                                                <td><?php if($value['sb']['first_half'] == 'N'){echo 'Full day'; $compoff = $compoff+1;}elseif($value['sb']['first_half'] == 'Y'){echo 'Half day'; $compoff = $compoff +.5; }else{ echo ''; }?></td>
                                                <td><?php echo $value['le']['FROMDATE'];?></td>
                                                <!-- <td><?php echo $value['le']['TODATE'];?></td> -->
                                                <td><?php echo $value['le']['LEAVESTATUS'];?></td>
                                                <td><?php echo $value['le']['leave_days'];?></td>
                                                <?php if($value['le']['leave_days'] != null){
                                                    $leavedays = $leavedays + $value['le']['leave_days'];
                                                }?>
                                            </tr>
                                        <?php $j++; }?>
                                        <tr>
                                                         <!--EDITED BY SINSIYA ON 06-06-2025-->
                                                       
                                                       <td colspan="6">Earned Comp Off Leave : <?php echo $compoff - $leavedays; ?></td>
                                                        
                                                    </tr>
                                </tbody>
                            </table>
                    </div>
                        <!-- New compoff table -->

                            <?php } ?>


                            </div>
                    <?php }
                    } ?> <!-- /.box-body -->
                        <?php if($empcount == 0){
                            echo '<div style="font-size: 16px;text-align:center; background-color:;">
                            No data available under the selected criteria.</div>';
                                die();
                        }?>

                 
                </div>
            </div>
        </div>
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
            width: auto;
            vertical-align: top;
            font-size: 10px;
            border: 1px solid #B2B2B2;
        }
    </style>

    <?php
    echo $this->element('reportadminheader', array(
        'title' => 'Compensatory Report'
    ));
    ?>

    <?php
    $i = 0;
    foreach ($arr_leavepolicydetails_for_template as $value) {
        if (!empty($value['summary'])) {
            $i += 1;
    ?>

            <h4>Reports of <?php echo $value['emp_dets']['0']['employee_info']['EmpName']; ?><?php echo isset($value['status']['0']['emp_details']['status']) && $value['status']['0']['emp_details']['status'] == "2" ? '(Resigned)' : ''; ?>
            </h4>

            <?php $arr_data = $value['summary'];
            $emp_dets = $value['emp_dets'];
            ?>
            <?php if (count($arr_data) > 0) { ?>

                <h4>Employee Details</h4>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Employee Name</th>
                            <th>Employee ID</th>
                            <th>Date Of Join</th>
                            <th>Branch</th>
                            <th>Department</th>
                            <th>Designation</th>





                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?php echo $emp_dets['0']['employee_info']['EmpName']; ?><?php echo isset($value['status']['0']['emp_details']['status']) && $value['status']['0']['emp_details']['status'] == "2" ? '(Resigned)' : ''; ?></td>
                            <td><?php echo $emp_dets['0']['employee_info']['employee_id']; ?></td>
                            <td><?php echo $emp_dets['0']['employee_info']['joining_date']; ?></td>
                            <td><?php echo $emp_dets['0']['employee_info']['branch']; ?></td>
                            <td><?php echo $emp_dets['0']['employee_info']['department']; ?></td>
                            <td><?php echo $emp_dets['0']['employee_info']['designation']; ?></td>

                        </tr>
                    </tbody>
                </table>

                <h4>Compensatory Usage Details</h4>

                <table class="table">
                    <thead>
                        <tr>
                            <th>Sl No. </th>
                            <th>Used Date</th>
                            <th>Duration</th>
                            <th>Day Type</th>


                        </tr>
                    </thead>
                    <tbody>

                        <?php
                        $used = 0;
                        foreach ($arr_data as $val) {
                        ?>
                            <tr>
                                <?php $used = $used + 1; ?>
                                <td><?php echo $used; ?></td>
                                <td><?php echo $val['0']['att_date']; ?></td>
                                <td><?php echo $val['0']['duration']; ?></td>
                                <td><?php echo $val['0']['weekoff'] . ' ' . $val['0']['holiday']; ?></td>
                            </tr>
                        <?php
                        }
                        ?>
                        <tr>
                            <td colspan="4">Available Comp off Leave Balance : <?php echo isset($value['eligibility']['0']['0']['blnce']) ? $value['eligibility']['0']['0']['blnce'] : 0; ?></td>
                        </tr>
                    </tbody>
                </table>

                <h4>Comp off Leave List</h4>
                <table class="table">
                    <thead>
                        <tr>
                            <th>From Date</th>
                            <th>To Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $arr_data = $value['leaves']; ?>
                        <?php if (count($arr_data) >= 0) { ?>
                            <?php foreach ($arr_data as $val) { ?>
                                <tr>
                                    <td><?php echo $val['leaveentries']['FROMDATE'] . ' ' . ($val['leaveentries']['FROMHALF'] == 1) ? 'First Half' : 'Second Half'; ?></td>
                                    <td><?php echo $val['leaveentries']['TODATE'] . ' ' . $val['leaveentries']['TOHALF']; ?></td>
                                    <td><?php echo $val['leaveentries']['LEAVESTATUS']; ?></td>
                                </tr>
                            <?php } ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="4">No Comp Off Leaves Taken by this employee </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>


            <?php } ?>


    <?php }
    } ?> <!-- /.box-body -->

<?php } ?>