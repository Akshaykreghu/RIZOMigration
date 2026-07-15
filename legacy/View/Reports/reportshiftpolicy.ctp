<?php if ($mode == '') { ?>
    <div class="modal-body" style="overflow-y: auto;">
        <h3 style="text-align:center;"><b>Shift Policy Report</b></h3>
        <div class="row">
            <div class="col-md-12">
                <div class="box ">
                    <?php
                    // debug($arr_leavepolicydetails_for_template);
                    // debug($arr_shiftpolicy);
                    //The $arr_shiftpolicy array created by ***ARUL P DAS on 20/1/2020 to show the Shift policy header details separately
                    $i = 0;
                    $shift_iteration = 0;
                    foreach ($arr_leavepolicydetails_for_template as $value) {
                        $i += 1;
                        ?>
                        <div class="box-body">
                            <fieldset>
                                <!-- <h3>Shift Summary of <?php echo isset($value['policytitle']) ? $value['policytitle'] : ''; ?> </h3> -->
                                <h3 style="font-weight:bold;">Shift Summary of <?php echo isset($arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['day_time_desc']) ? $arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['day_time_desc'] : ''; ?> </h3>
                                <div class="row">
                                    <div class="col-md-12">
                                        <!-- Policy Title : <?php echo isset($value['policytitle']) ? $value['policytitle'] : ''; ?> -->
                                        Policy Title : <?php echo isset($arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['day_time_desc']) ? $arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['day_time_desc'] : ''; ?>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <!-- Working Days :<?php echo isset($value['summary']['0']['DayTimeProcedures']['ondays']) ? $value['summary']['0']['DayTimeProcedures']['ondays'] : ''; ?> -->
                                        Working Days :<?php echo isset($arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['ondays']) ? $arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['ondays'] : ''; ?>

                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <!-- Off Days :<?php echo isset($value['summary']['0']['DayTimeProcedures']['offdays']) ? $value['summary']['0']['DayTimeProcedures']['offdays'] : ''; ?> -->
                                        Off Days :<?php echo isset($arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['offdays']) ? $arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['offdays'] : ''; ?>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <!-- On Duty: <?php echo isset($value['summary']['0']['DayTimeProcedures']['on_dutty1']) ? $value['
                                        summary']['0']['DayTimeProcedures']['on_dutty1'] : ''; ?> -->
                                        On Duty: <?php echo isset($arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['on_dutty1']) ? $arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['on_dutty1'] : ''; ?>
                                    </div>
                                    <div class="col-md-4">
                                        <!-- Off Duty :<?php echo isset($value['summary']['0']['DayTimeProcedures']['off_dutty1']) ? $value['summary']['0']['DayTimeProcedures']['off_dutty1'] : ''; ?> -->
                                        Off Duty :<?php echo isset($arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['off_dutty1']) ? $arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['off_dutty1'] : ''; ?>
                                    </div>
                                    <div class="col-md-4">
                                        <!-- Working Time :<?php echo isset($value['summary']['0']['DayTimeProcedures']['working_time1']) ? $value['summary']['0']['DayTimeProcedures']['working_time1'] : ''; ?> -->
                                        Working Time :<?php echo isset($arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['working_time1']) ? $arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['working_time1'] : ''; ?>
                                    </div>

                                </div>

                            </fieldset>
                            <br>
                            <fieldset>
                                <h3>Stat Rule</h3>

                                <div class="row ">
                                    <div class="col-sm-12">
                                        <!-- <span style="width: 200px">Minutes calculated as per day:<?php echo isset($value['summary']['0']['DayTimeProcedures']['minuts_calc_perday']) ? $value['summary']['0']['DayTimeProcedures']['minuts_calc_perday'] : ''; ?></span> -->
                                        <span style="width: 200px">Minutes calculated as per day:<?php echo isset($arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['minuts_calc_perday']) ? $arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['minuts_calc_perday'] : ''; ?></span>
                                    </div>
                                </div>
                                <div class="row ">
                                    <div class="col-sm-12">
                                        <!-- <span>Minutes after On duty calculated as late:<?php echo isset($value['summary']['0']['DayTimeProcedures']['minuts_aftr_on_dutty_cal_late']) ? $value['summary']['0']['DayTimeProcedures']['minuts_aftr_on_dutty_cal_late'] : ''; ?></span> -->
                                        <span>Minutes after On duty calculated as late:<?php echo isset($arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['minuts_aftr_on_dutty_cal_late']) ? $arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['minuts_aftr_on_dutty_cal_late'] : ''; ?></span>
                                    </div>
                                </div>
                                <div class="row ">
                                    <div class="col-sm-12">
                                        <!-- <span>Minutes before Off duty calculated as early:<?php echo isset($value['summary']['0']['DayTimeProcedures']['minuts_bfr_off_dutty_cal_early']) ? $value['summary']['0']['DayTimeProcedures']['minuts_bfr_off_dutty_cal_early'] : ''; ?></span> -->
                                        <span>Minutes before Off duty calculated as early:<?php echo isset($arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['minuts_bfr_off_dutty_cal_early']) ? $arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['minuts_bfr_off_dutty_cal_early'] : ''; ?></span>
                                    </div>
                                </div>
                                <?php if (isset($arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['min_cal_late_ifnoclockin']) && $arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['min_cal_late_ifnoclockin'] = '') { ?>
                                    <div class="row ">
                                        <div class="col-sm-12">
                                            <!-- <span>Minutes calculated as late if no clock-in:<?php echo $value['summary']['0']['DayTimeProcedures']['min_cal_late_ifnoclockin']; ?></span> -->
                                            <span>Minutes calculated as late if no clock-in:<?php echo $arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['min_cal_late_ifnoclockin']; ?></span>
                                        </div>
                                    </div>
                                <?php } ?>
                                <?php if (isset($arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['min_cal_leave_early_ifnoclockout']) && $arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['min_cal_leave_early_ifnoclockout'] = '') { ?>
                                    <div class="row ">
                                        <div class="col-sm-12">
                                            <!-- <span>Minutes calculated as leave early if no clock-out:<?php echo $value['summary']['0']['DayTimeProcedures']['min_cal_leave_early_ifnoclockout']; ?></span> -->
                                            <span>Minutes calculated as leave early if no clock-out:<?php echo $arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['min_cal_leave_early_ifnoclockout']; ?></span>
                                        </div>
                                    </div>
                                <?php } ?>
                                <?php if (isset($arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['min_aftr_off_dutty_cal_ot']) && $arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['min_aftr_off_dutty_cal_ot'] = '') { ?>
                                    <div class="row ">
                                        <div class="col-sm-12">
                                            <!-- <span>Minutes after Off duty calculated as overtime:<?php echo $value['summary']['0']['DayTimeProcedures']['min_aftr_off_dutty_cal_ot']; ?></span> -->
                                            <span>Minutes after Off duty calculated as overtime:<?php echo $arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['min_aftr_off_dutty_cal_ot']; ?></span>
                                        </div>
                                    </div>
                                <?php } ?>
                                <?php if (isset($value['summary']['0']['DayTimeProcedures']['min_bfr_on_dutty_cal_ot']) && $value['summary']['0']['DayTimeProcedures']['min_bfr_on_dutty_cal_ot'] = '') { ?>
                                    <div class="row ">
                                        <div class="col-sm-12">
                                            <!-- <span>Minutes before On duty calculated as overtime:<?php echo $value['summary']['0']['DayTimeProcedures']['min_bfr_on_dutty_cal_ot']; ?></span> -->
                                            <span>Minutes before On duty calculated as overtime:<?php echo $arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['min_bfr_on_dutty_cal_ot']; ?></span>
                                        </div>
                                    </div>
                                <?php } ?>
                                <?php if (isset($arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['work_time_day_off_cal_ot']) && $arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['work_time_day_off_cal_ot'] = '') { ?>
                                    <div class="row ">
                                        <div class="col-sm-12">
                                            <!-- <span>Working time in day off calculated as overtime:<?php echo $value['summary']['0']['DayTimeProcedures']['work_time_day_off_cal_ot']; ?></span> -->
                                            <span>Working time in day off calculated as overtime:<?php echo $arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['work_time_day_off_cal_ot']; ?></span>
                                        </div>
                                    </div>
                                <?php } ?>
                            </fieldset>
                            <?php
                            $shift_iteration++;
                            ?>
                            <br>
                            <fieldset>
                                <h3>Employee List</h3>
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Sl No</th>
                                            <th>Employee Name</th>
                                            <th>Employee ID</th>
                                            <th>Designation</th>
                                            <th>Date Of Joining</th>
                                            <th>Department</th>
                                            <th>Branch</th>
	<th>Grade</th>													  
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- If no employees in under a shift policy the query returns policy title as null. Checked by ***ARUL P DAS -->
                                        <?php if ($value['policytitle'] != '') {
                                            $i = 1; ?>
											  
                                            <?php foreach ($value['summary'] as $val) { ?>
                                                <tr> 
                                                    <td><?php echo $i; ?></td>
                                                    <td><?php echo $val['Info']['EmpName']; ?>
                                                    <?php echo (isset($val['Info']['emp_status'])) && $val['Info']['emp_status'] == "2" ? '  (Resigned)' : ''; ?></td>
                                                    <td><?php echo $val['Info']['employee_id']; ?></td>
                                                    <td><?php echo $val['Info']['designation']; ?></td>
                                                    <td><?php echo $val['Info']['joining_date']; ?></td>
                                                    <td><?php echo $val['Info']['department']; ?></td>
                                                    <td><?php echo $val['Info']['branch']; ?></td>
<td><?php echo $val['Info']['grade']; ?></td>																							 
                                                </tr>
                                                <?php $i++;
                                            } ?>
											  
        <?php } else { ?>
                                            <tr>
                                                <td colspan="8">No employees found under this shift</td>
                                            </tr>  
        <?php } ?>
                                    </tbody>
                                </table>
                            </fieldset>
                        </div><!-- /.box-body -->
    <?php } ?>
                </div>
            </div>
        </div>    
        <!--    <div class="row">
                <div class="form-group">
                    <div class="col-md-12" align="right">
                        <a href="#" class="btn btn-default" onclick="downloadReport('shiftpolicy','pdf');" ><i class="icon-file"></i>Download As PDF</a>
                        <a href="#" class="btn btn-default" onclick="downloadReport('shiftpolicy','excel');"><i class="icon-file"></i>Download As Excel</a>
                    </div>
                </div>
            </div>-->
    </div>
<?php } else { ?>
    <?php //echo '<style>'.file_get_contents("css/pdfbootstrap.css").'</style>';  ?>
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
            width: 500px;
            margin: 0 auto; 

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
        .table_border {
            border: 0px solid #fff;
        }
        td, th {
            text-align: left;
            padding: 8px;
            line-height: 1.42857143;
            vertical-align: top;
            width:20%;
            border: 1px solid #B2B2B2;
        }
    </style>
    <!--Report code start here-->
    <?php
    echo $this->element('reportadminheader',array(
   'title'=>'Shift Policy Report'));
    ?>


    <?php
    $i = 0;
    //The $arr_shiftpolicy created by ***ARUL P DAS. This array consist of only shift policy details.
    $shift_iteration = 0;
    foreach ($arr_leavepolicydetails_for_template as $value) {
        $i += 1;
        ?>
        <div>
            <h4 style="text-align: left;padding-bottom: 10px;padding-top: 10px;">
                Shift Summary of <?php echo isset($arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['day_time_desc']) ? $arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['day_time_desc'] : ''; ?>
            </h4>
<!--            <div>-->
                <bookmark>
                    <h5> Policy Title : <?php echo isset($arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['day_time_desc']) ? $arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['day_time_desc'] : ''; ?>                          
                    </h5>
                </bookmark>

                <table style="border:none;"> 
                    <tr style="border:none">
                        <td style="border:none">Working Days :</td>
                        <td style="border:none"><?php echo isset($arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['ondays']) ? $arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['ondays'] : ''; ?>
                        </td>
                    </tr>
                    <tr style="border:none">
                        <td style="border:none">Off Days :</td>
                        <td style="border:none"><?php echo isset($arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['offdays']) ? $arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['offdays'] : ''; ?>
                        </td> 
                    </tr>
                    <tr style="border:none">
                        <td style="border:none">On Duty :</td>
                        <td style="border:none"><?php echo isset($arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['on_dutty1']) ? $arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['on_dutty1'] : ''; ?>
                        </td> 
                        <td style="border:none">Off Duty :</td>
                        <td style="border:none"><?php echo isset($arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['off_dutty1']) ? $arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['off_dutty1'] : ''; ?>
                        </td>
                    </tr>
                    <tr style="border:none">
                        <td style="border:none">Working Time :</td>
                        <td style="border:none"><?php echo isset($arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['working_time1']) ? $arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['working_time1'] : ''; ?>
                        </td>
                    </tr>
                </table>
                <hr>
            </div>
            <div>
                <h4 style="text-align: left;padding-bottom: 10px;padding-top: 10px;">Stat Rule</h4>

                <span style="width: 200px">
                    Minutes calculated as per day:<?php echo isset($arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['minuts_calc_perday']) ? $arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['minuts_calc_perday'] : ''; ?>

                </span>
                <br>  <br>
                <span>
                    Minutes after On duty calculated as late:<?php echo isset($arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['minuts_aftr_on_dutty_cal_late']) ? $arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['minuts_aftr_on_dutty_cal_late'] : ''; ?>
                </span>
                <br>  <br>
                <span>
                    Minutes before Off duty calculated as early:<?php echo isset($arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['minuts_bfr_off_dutty_cal_early']) ? $arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['minuts_bfr_off_dutty_cal_early'] : ''; ?>
                </span>
                <br>  <br>
                <?php if (isset($arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['min_cal_late_ifnoclockin']) && $arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['min_cal_late_ifnoclockin'] = '') { ?>

                    <span>
                        Minutes calculated as late if no clock-in:<?php echo $arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['min_cal_late_ifnoclockin']; ?>
                    </span>
                    <br>  <br>
                <?php } ?>
                <?php if (isset($arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['min_cal_leave_early_ifnoclockout']) && $arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['min_cal_leave_early_ifnoclockout'] = '') { ?>

                    <span>
                        Minutes calculated as leave early if no clock-out:<?php echo $arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['min_cal_leave_early_ifnoclockout']; ?>
                    </span>
                    <br>  <br>
                <?php } ?>
                <?php if (isset($arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['min_aftr_off_dutty_cal_ot']) && $arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['min_aftr_off_dutty_cal_ot'] = '') { ?>
                    <span>
                        Minutes after Off duty calculated as overtime:<?php echo $arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['min_aftr_off_dutty_cal_ot']; ?>
                    </span>
                    <br>  <br>
                <?php } ?>
                <?php if (isset($arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['min_bfr_on_dutty_cal_ot']) && $arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['min_bfr_on_dutty_cal_ot'] = '') { ?>
                    <span>
                        Minutes before On duty calculated as overtime:<?php echo $arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['min_bfr_on_dutty_cal_ot']; ?>
                    </span>
                    <br>  <br>
                <?php } ?>
                <?php if (isset($arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['work_time_day_off_cal_ot']) && $arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['work_time_day_off_cal_ot'] = '') { ?>
                    <span>
                        Working time in day off calculated as overtime:<?php echo $arr_shiftpolicy[$shift_iteration]['0']['DayTimeProcedures']['work_time_day_off_cal_ot']; ?>
                    </span>
                    <br>  <br>
                <?php } ?>
                <hr>
            </div>
			<?php
            $shift_iteration++;
            ?>
<!--            <div>-->
                <h4 style="text-align: left;padding-bottom: 10px;padding-top: 10px;">Employee List</h4>
                <table align="center">
                    <thead>
                        <tr>
                            <th style="width: 8%">Sl No</th>
                            <th style="width: 12%">Employee Name</th>
                            <th style="width: 12%">Employee ID</th>
                            <th style="width: 12%">Designation</th>
                            <th style="width: 12%">Date Of Joining</th>
                            <th style="width: 12%">Department</th>
                            <th style="width: 12%">Branch</th>
                            <th style="width: 12%">Grade</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- If no employees in under a shift policy the query returns policy title as null. Checked by ***ARUL P DAS -->
                        <?php
                        if ($value['policytitle'] != '') {
                            $i = 1;
                            ?>
                            <?php foreach ($value['summary'] as $val) { ?>
                                <tr> 
                                    <td style="width: 8%"><?php echo $i; ?></td>
                                    <td style="width: 10%"><?php echo $val['Info']['EmpName']; ?></td>
                                    <td style="width: 10%"><?php echo $val['Info']['employee_id']; ?></td>
                                    <td style="width: 10%"><?php echo $val['Info']['designation']; ?></td>
                                    <td style="width: 10%"><?php echo $val['Info']['joining_date']; ?></td>
                                    <td style="width: 10%"> <?php echo $val['Info']['department']; ?></td>
                                    <td style="width: 10%"> <?php echo $val['Info']['branch']; ?></td>
	 <td style="width: 10%"> <?php echo $val['Info']['grade']; ?></td>
                                </tr>
                                <?php
                                $i++;
                            }
                            ?>
                        <?php } else { ?>
                            <tr>
                                <td colspan="8">No employees found under this shift</td>
                            </tr>  
                        <?php } ?>
                    </tbody>
                </table>
<!--            </div>-->
<!--        </div>-->
    <?php } ?><!-- /.box-body -->
<?php } ?>

