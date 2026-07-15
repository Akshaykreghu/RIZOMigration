<?php if ($mode == '') { ?>
    <div class="modal-body" style="overflow-y: auto;">
        <legend style="text-align:center; font-weight: bold">Leave Policy Report</legend>
        <div class="row">
            <div class="col-md-12">
                <div class="box ">
                    <?php
                    $i = 0;
                    foreach ($arr_leavepolicydetails_for_template as $value) {
                        $i += 1;
                        ?>
                        <div class="box-body">
                            <fieldset>
                                <legend style="font-weight:bold;">leave Summary of <?php echo $value['leavepolicyname']; ?> </legend>
                                <div class="row">
                                    <div class="col-md-12">
                                        Policy Title : <?php echo $value['leavepolicyname']; ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">

                                    </div>
                                </div>


                            </fieldset>
                            <br>
                            <fieldset>

                                <table class="table table-bordered">
                                    <thead>
                                        <tr>

                                            <th>Leave Type</th>
                                        <!--    <th>LEAVEPOLICY_GROUP_NAME</th> -->

                                            <th>Remarks</th>
                                            <th>Leave For The Year</th>
                                            <th>Leave For The Month</th>
                                            <th>Carry Forwards Limit</th>
                                            <th>Applicable To</th>
                                            <th>Allow Negative</th>
                                            <th>Sandwich</th>
                                            <th>Leave EnCash</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $rownumber = 0;
                                        ?>
                                        <?php $arr_data = $value['summary']; ?>
                                        <?php if (count($arr_data) > 0) { ?>
                                            <?php foreach ($arr_data as $val) { ?>
                                                <tr> 

                                                    <td><?php echo $val['salary_head_items']['item']; ?></td>
                                                   <!-- <td><?php echo $val['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME']; ?></td> -->
                                                    <td><?php echo $val['LeavePolicy']['REMARKS']; ?></td>
                                                    <td><?php echo $val['LeavePolicy']['alloted_leave_forthe_year']; ?></td>
                                                    <td><?php echo $val['LeavePolicy']['alloted_leave_forthe_month']; ?></td>
                                                    <td><?php echo $val['LeavePolicy']['CARRY_FORWARD_LIMIT']; ?></td>
                                                    <td><?php echo $val['LeavePolicy']['APPLICABLE_TO']; ?></td>
                                                    <td><?php echo $val['LeavePolicy']['ALLOW_NEGETIVE']; ?></td>
                                                    <td><?php echo $val['LeavePolicy']['IS_SANDWICH']; ?></td>
                                                    <td><?php echo $val['LeavePolicy']['is_leave_encash']; ?></td>

                                                </tr>
                                            <?php } ?>
                                        <?php } else { ?>
                                            <tr>
                                               <td colspan="9">No leave types found under this leave policy</td>
                                            </tr>  
                                        <?php } ?>
                                    </tbody>
                                </table>

                            </fieldset>
                            <br>
                            <fieldset>
                                <legend>Employee List</legend>
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>

                                            <th>Sl No </th>
                                            <th>Employee Name</th>
                                            <th>Employee ID</th>
                                            <th>Designation</th>
                                            <th>Date Of Joining</th>
                                            <th>Department</th>
                                            <th>Branch</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $arr_data = $value['employees'];?>
                                        <?php if (count($arr_data) > 0) { ?>
                                            <?php foreach ($arr_data as $val) { ?>
                                                <tr> 
                                                    <td><?php
                                                        $rownumber++;
                                                        echo $rownumber;
                                                        ?></td>
                                             <!--edited by ASHIN on 08-11-24--->           
                                                    <td><?php echo $val['Info']['EmpName']; ?>
                                                    <?php echo (isset($val['Info']['emp_status'])) && $val['Info']['emp_status'] == "2" ? '  (Resigned)' : ''; ?></td>
                                                    <td><?php echo $val['Info']['employee_id']; ?></td>
                                                    <td><?php echo $val['Info']['designation']; ?></td>
                                                    <td><?php echo $val['Info']['joining_date']; ?></td>
                                                    <td><?php echo $val['Info']['department']; ?></td>
                                                    <td><?php echo $val['Info']['branch']; ?></td>
                                                </tr>
                                            <?php } ?>
                                        <?php } else { ?>
                                            <tr>
                                                <td colspan="7">No employees found under this leave policy</td>
                                            </tr>  
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </fieldset>
                        </div>
                    <?php } ?> <!-- /.box-body -->
                </div>
            </div>
        </div>    
        <!--    <div class="row">
                <div class="form-group">
                    <div class="col-md-12" align="right">
                        a href="#" class="btn btn-default" onclick="downloadReport('leavepolicy','pdf');" ><i class="icon-file"></i>Download As PDF</a
                        <a href="#" class="btn btn-default" onclick="downloadReport('leavepolicy','excel');"><i class="icon-file"></i>Download As Excel</a>
                    </div>
                </div>
            </div>-->
    </div>
<?php } else { ?>
    <?php //echo '<style>'.file_get_contents("css/pdfbootstrap.css").'</style>';    ?>
    <style type="text/css">
        body {
            line-height: 2em;
        }
        .block-container {
            width: auto;
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
            width: 500px;
            margin: 0 auto; 
        }
        .col-md-12
        {
            width:100%;
            float: left;
        }
        table {
            border: 1px solid #f4f4f4;
            width: 100%;
            max-width: 100%;
            margin-bottom: 20px;
            background-color: transparent;
            border-spacing: 0;
            border-collapse: collapse;
        }
        td, th {
            text-align: left;
            padding: 8px;
            line-height: 1.42857143;
            vertical-align: top;
            font-size:10px;
            width:10%;
            border: 1px solid #B2B2B2;
        }
    </style>
    <!---REport Start here-->
    <?php
echo $this->element('reportadminheader',array(
'title'=>'Leave Policy Report'));
    ?>


    <?php
    $i = 0;
    foreach ($arr_leavepolicydetails_for_template as $value) {
        $i += 1;
        ?>
        <bookmark class="box-body">

            <h4>Leave Summary of <?php echo $value['leavepolicyname']; ?> </h4>

            <h5>  Policy Title : <?php echo $value['leavepolicyname']; ?></h5>

            <hr>
            <br>
            <br>
            <table>
                <thead>
                    <tr>

                        <th style="width:15%;">Leave Type</th>
                    <!--    <th>LEAVEPOLICY_GROUP_NAME</th> -->

                        <th style="width:8%;">Remarks</th>
                        <th style="width:8%;">Leave For The Year</th>
                        <th style="width:8%;">Leave For The Month</th>
                        <th style="width:8%;">Carry Fwd Limit</th>
                        <th style="width:8%;">Appli- cable To</th>
                        <th style="width:6%;">Allow Negative</th>
                        <th style="width:6%;">Sandwich</th>
                        <th style="width:6%;">Leave EnCash</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $rownumber = 0; ?>
                    <?php $arr_data = $value['summary']; ?>
                    <?php if (count($arr_data) > 0) { ?>
                        <?php foreach ($arr_data as $val) { ?>
                            <tr> 
                                <td><?php echo $val['salary_head_items']['item']; ?></td>
                               <!-- <td><?php // echo $val['LeavePolicyGroup']['LEAVEPOLICY_GROUP_NAME'];     ?></td> -->
                                <td><?php echo $val['LeavePolicy']['REMARKS']; ?></td>
                                <td><?php echo $val['LeavePolicy']['alloted_leave_forthe_year']; ?></td>
                                <td><?php echo $val['LeavePolicy']['alloted_leave_forthe_month']; ?></td>
                                <td><?php echo $val['LeavePolicy']['CARRY_FORWARD_LIMIT']; ?></td>
                                <td><?php echo $val['LeavePolicy']['APPLICABLE_TO']; ?></td>
                                <td><?php echo $val['LeavePolicy']['ALLOW_NEGETIVE']; ?></td>
                                <td><?php echo $val['LeavePolicy']['IS_SANDWICH']; ?></td>
                                <td><?php echo $val['LeavePolicy']['is_leave_encash']; ?></td>

                            </tr>
                        <?php } ?>
                    <?php } else { ?>
                        <tr>
                            <td colspan="9">No leave types found under this leave policy</td>
                        </tr>  
                    <?php } ?>
                </tbody>
            </table>

            <h4>Employee List</h4>
            <table>
                <thead>
                    <tr> 
                        <th>Sl No </th>
                        <th style="width:15%;">Employee Name</th>
                        <th style="width:15%;">Employee ID</th>
                        <th style="width:15%;">Designation</th>
                        <th style="width:15%;">Date Of Joining</th>
                        <th style="width:15%;">Department</th>
                        <th style="width:15%;">Branch</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $arr_data = $value['employees']; ?>
                    <?php if (count($arr_data) > 0) { ?>
                        <?php foreach ($arr_data as $val) { ?>
                            <tr> 
                                <td>
                                    <?php
                                    $rownumber++;
                                    echo $rownumber;
                                    ?>
                                </td>
                                <td><?php echo $val['Info']['EmpName']; ?></td>
                                <td><?php echo $val['Info']['employee_id']; ?></td>
                                <td><?php echo $val['Info']['designation']; ?></td>
                                <td><?php echo $val['Info']['joining_date']; ?></td>
                                <td><?php echo $val['Info']['department']; ?></td>
                                <td><?php echo $val['Info']['branch']; ?></td>
                            </tr>
                        <?php } ?>
                    <?php } else { ?>
                        <tr>
                            <td colspan="7">No employees found under this leave policy</td>
                        </tr>  
                    <?php }
                    ?>
                </tbody>

            </table>
        </bookmark>
    <?php } ?> <!-- /.box-body -->


<?php } ?>