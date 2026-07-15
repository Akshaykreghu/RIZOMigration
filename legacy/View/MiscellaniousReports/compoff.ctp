<?php if ($mode == '') { ?>


    <div class="modal-body myDivToPrint" style="overflow-y: auto;" id="toPrint">
        <legend>Compensatory Off Report</legend>
        <div class="row">
            <div class="col-md-12">
                <div class="box ">
                    <?php
                    $i = 0;
                    foreach ($arr_leavepolicydetails_for_template as $value) {
                        if (!empty($value['summary'])) {
                            $i += 1;
                    ?>
                            <div class="box-body">
                                <fieldset>
                                    <legend style="    font-weight: bold ; ">Report of <?php echo $value['emp_dets']['0']['employee_info']['EmpName']; ?><?php echo isset($value['status']['0']['emp_details']['status']) && $value['status']['0']['emp_details']['status'] == "2" ? '(Resigned)' : ''; ?>
                                        <?php
                                        //                     if($cr == 'Departments')
                                        //                        {
                                        //                        echo isset($value['summary']['0']['Departments']['dept_name'])?$value['summary'][0]['Departments']['dept_name']:''; 
                                        //                        }
                                        //                        else  if($cr == 'Units')
                                        //                        {
                                        //                        echo isset($value['summary'][0]['Units']['branch_name'])?$value['summary'][0]['Units']['branch_name']:''; 
                                        //                        }
                                        //                        else  if($cr == 'EmployeeDetails')
                                        //                        {
                                        //                        echo isset($value['summary'][0]['attendance_register']['emp_name'])?$value['summary'][0]['attendance_register']['emp_name']:''; 
                                        //                        }
                                        //                        else
                                        //                        {
                                        //                        echo isset($value['summary'][0]['sh']['leave_type'])?$value['summary'][0]['sh']['leave_type']:''; 
                                        //                        
                                        //                        }
                                        ?>
                                    </legend>
                                    <div class="row">
                                        <div class="col-md-12">

                                        </div>
                                    </div>


                                </fieldset>
                                <br>
                                <fieldset>



                                    <?php
                                    $arr_data = $value['summary'];
                                    $emp_dets = $value['emp_dets'];
                                    //                                debug($value);
                                    ?>
                                    <?php if (count($arr_data) > 0) { ?>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <h4>Employee Details</h4>
                                                <table class="table table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>Employee Name</th>
                                                            <!-- edited by athira on 07-07-2025 -->
                                                             <?php if ($company_code =='DEMO' || $company_code=='GLET' || $company_code=='SRTS') {?>
                                                            <th>Employee Name (US Format)</th>
                                                            <?php } ?>
                                                            <!-- end -->
                                                            <th>Employee ID</th>
                                                            <!-- edited by athira on 07-07-2025 -->
                                                             <?php if ($company_code =='DEMO' || $company_code=='GLET' || $company_code=='SRTS') {?>
                                                            <th>Employee ID (US Format)</th>
                                                            <?php } ?>
                                                            <!-- end -->
                                                            <th>Date Of Join</th>
                                                            <th>Branch</th>
                                                            <th>Department</th>
                                                            <th>Designation</th>






                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td><?php echo $emp_dets['0']['employee_info']['EmpName']; ?><?php echo isset($value['status']['0']['emp_details']['status']) && $value['status']['0']['emp_details']['status'] == "2" ? '(Resigned)' : ''; ?></td>
                                                            <!-- edited by athira on 07-07-2025 -->
                                                             <?php if ($company_code =='DEMO' || $company_code=='GLET' || $company_code=='SRTS') {?>
                                                            <td><?php echo $emp_dets['0']['employee_info']['EmpUSName']; ?></td>
                                                            <?php } ?>
                                                            <!-- end -->
                                                            <td><?php 
                                                            $joinn = $emp_dets['0']['employee_info']['employee_id'];
                                                            $join = date('d-m-Y', strtotime($joinn)); ?></td>
                                                             <!-- edited by athira on 07-07-2025 -->
                                                             <?php if ($company_code =='DEMO' || $company_code=='GLET' || $company_code=='SRTS') {?>
                                                             <td><?php echo $emp_dets['0']['employee_info']['emp_us_id']; ?></td>
                                                             <?php } ?>
                                                            
                                                            <td><?php echo $join; ?></td>
                                                            <td><?php echo $emp_dets['0']['employee_info']['branch']; ?></td>
                                                            <td><?php echo $emp_dets['0']['employee_info']['department']; ?></td>
                                                            <td><?php echo $emp_dets['0']['employee_info']['designation']; ?></td>

                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>

                                            <?php
                                            // debug($arr_data); 
                                            ?>

                                            <h4 style="margin-left:14px;">Compensatory Accrued Details </h4>
                                            <div class="col-md-12">
                                                <table class="table table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>Sl No. </th>
                                                            <th>Accrued Date</th>
                                                            <th>Duration</th>
                                                            <th>Day Type</th>


                                                        </tr>
                                                    </thead>
                                                    <tbody>

                                                        <?php
                                                        $used = 0;
                                                        foreach ($arr_data as $val) {
                                                            //debug($val);exit();
                                                        ?>
                                                            <tr>
                                                                <?php $used = $used + 1;   
                                                               $att_date = $val['0']['att_date'];
                                                               $duration = $val['0']['duration'];
                                                               $dys = $val['0']['weekoff'] . ' ' . $val['0']['holiday'];?>
                                                                <td><?php echo $used; ?></td>
                                                               <td><?php echo (new DateTime($val['0']['att_date']))->format('d-m-Y'); ?></td>

                                                                <td><?php echo $duration; ?></td>
                                                                <td><?php echo $dys; ?></td>
                                                            </tr>
                                                        <?php
                                                        }
                                                        ?>
                                                        <tr>
                                                            <td colspan="4">Available Comp Off Leave Balance : <?php echo isset($value['eligibility']['0']['0']['blnce']) ? $value['eligibility']['0']['0']['blnce'] : 0; ?></td>

                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>




                                        </div>
                                </fieldset>
                                <br>
                                <fieldset>
                                    <legend>Comp Off Leave List</legend>
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>From Date</th>
                                                <th>To Date</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $arr_data = $value['leaves']; ?>
                                            <?php if (count($value['leaves']) > 0) { ?>
                                                <?php foreach ($arr_data as $val) { ?>
                                                    <?php
                                                    if ($val['leaveentries']['FROMHALF'] == '1') {
                                                        $fromhalf = "First Half";
                                                    } else if ($val['leaveentries']['FROMHALF'] == '2') {
                                                        $fromhalf = "Second Half";
                                                    } else {
                                                        $fromhalf = "";
                                                    }

                                                    if ($val['leaveentries']['TOHALF'] == '1') {
                                                        $tohalf = "First Half";
                                                    } else if ($val['leaveentries']['TOHALF'] == '2') {
                                                        $tohalf = "Second Half";
                                                    } else {
                                                        $tohalf = "";
                                                    }
                                                    ?>
                                                    <tr>
                                                        <td><?php echo $val['leaveentries']['FROMDATE'] . ' ' . $fromhalf; ?></td>
                                                        <td><?php echo $val['leaveentries']['TODATE'] . ' ' . $tohalf; ?></td>
                                                        <td><?php echo $val['leaveentries']['LEAVESTATUS']; ?></td>
                                                    </tr>
                                                <?php } ?>
                                            <?php } else { ?>
                                                <tr>
                                                    <td colspan="3">No Comp Off Leaves Taken by this employee </td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </fieldset>

                            <?php } ?>
                            </div>
                    <?php }
                    } ?> <!-- /.box-body -->
                </div>
            </div>
        </div>
        <div class="row">
            <div class="form-group">
                <div class="col-md-12" align="right">

                    <!--a href="#" class="btn btn-default" onclick="downloadReport('LeaveBalance','excel');"><i class="icon-file"></i>Download As Excel</a-->
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
            // debug($arr_data);exit;
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
                                <td><?php echo $val['emp_detail_timeattandance']['att_date']; ?></td>
                                <td><?php echo $val['emp_detail_timeattandance']['duration']; ?></td>
                                <td><?php echo $val['emp_detail_timeattandance']['weekoff'] . ' ' . $val['emp_detail_timeattandance']['holiday']; ?></td>
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
                                <?php
                                if ($val['leaveentries']['FROMHALF'] == '1') {
                                    $fromhalf = "First Half";
                                } else if ($val['leaveentries']['FROMHALF'] == '2') {
                                    $fromhalf = "Second Half";
                                } else {
                                    $fromhalf = "";
                                }

                                if ($val['leaveentries']['TOHALF'] == '1') {
                                    $tohalf = "First Half";
                                } else if ($val['leaveentries']['TOHALF'] == '2') {
                                    $tohalf = "Second Half";
                                } else {
                                    $tohalf = "";
                                }
                                ?>
                                <tr>
                                    <td><?php echo $val['leaveentries']['FROMDATE'] . ' ' . $fromhalf; ?></td>
                                    <td><?php echo $val['leaveentries']['TODATE'] . ' ' . $tohalf; ?></td>
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

<?php
} ?>