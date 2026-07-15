<?php if ($mode == '') { ?>
    <div class="modal-body" style="overflow-y: auto;">
        <h3 style="text-align:center;"><b>Salary Structure Report</b></h3>
        <div class="row">
            <div class="col-md-12">
                <div class="box ">
                    <?php
                    $i = 0;
                    $structure_iteration = 0;
                    foreach ($arr_leavepolicydetails_for_template as $value) {
                        $i += 1;
                        $policyTitle = isset($arr_salary_structure[$structure_iteration]['0']['SalaryStructures']['structure_name']) ? $arr_salary_structure[$structure_iteration]['0']['SalaryStructures']['structure_name'] : '';
                        $min_gross = isset($arr_salary_structure[$structure_iteration]['0']['SalaryStructures']['structure_eg_amt']) ? $arr_salary_structure[$structure_iteration]['0']['SalaryStructures']['structure_eg_amt'] : '';
                    ?>
                        <div class="box-body">
                            <fieldset>
                                <h3 style="font-weight:bold;">Salary Structure Summary of <?php echo $policyTitle; ?> </h3>
                                <div class="row">
                                    <div class="col-md-12">
                                        Policy Title : <?php echo $policyTitle; ?>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        Minimum Gross : <?php echo $min_gross; ?>
                                    </div>
                                </div>
                            </fieldset>

                            <?php
                            $structure_iteration++;
                            ?>
                            <!-- <br> -->
                            <fieldset>
                                <h3>Employee List</h3>
                                <?php if ($value['policytitle'] != '') {
                                    $i = 1; ?>
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
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($value['summary'] as $val) { ?>
                                                <tr>
                                                    <td><?php echo $i; ?></td>
                                                    <!--edited by ASHIN on 08-11-24--->
                                                    <td><?php echo $val['Info']['EmpName']; ?>
                                                        <?php echo (isset($val['Info']['emp_status'])) && $val['Info']['emp_status'] == "2" ? '  (Resigned)' : ''; ?></td>
                                                    <td><?php echo $val['Info']['employee_id']; ?></td>
                                                    <td><?php echo $val['Info']['designation']; ?></td>
                                                    <td><?php echo $val['Info']['joining_date']; ?></td>
                                                    <td><?php echo $val['Info']['department']; ?></td>
                                                    <td><?php echo $val['Info']['branch']; ?></td>
                                                </tr>
                                            <?php $i++;
                                            } ?>
                                        </tbody>
                                    </table>
                                <?php } else { ?>
                                    <div class="row">
                                        <div class="col-md-12">
                                            No employees found under this salary structure
                                        </div>
                                    </div>
                                <?php } ?>
                            </fieldset>
                        </div><!-- /.box-body -->
                    <?php } ?>
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

        td,
        th {
            text-align: left;
            padding: 8px;
            line-height: 1.42857143;
            vertical-align: top;
            width: 20%;
            border: 1px solid #B2B2B2;
        }
    </style>
    <!--Report code start here-->
    <?php
    echo $this->element('reportadminheader', array(
        'title' => 'Shift Policy Report'
    ));
    ?>


    <?php
    $i = 0;
    //The $arr_salary_structure created by ***ARUL P DAS. This array consist of only shift policy details.
    $structure_iteration = 0;
    foreach ($arr_leavepolicydetails_for_template as $value) {
        $i += 1;
        $policyTitle = isset($arr_salary_structure[$structure_iteration]['0']['SalaryStructures']['structure_name']) ? $arr_salary_structure[$structure_iteration]['0']['SalaryStructures']['structure_name'] : '';
    ?>
        <div>
            <h4 style="text-align: left;padding-bottom: 10px;padding-top: 10px;">
                Shift Summary of <?php echo $policyTitle; ?>
            </h4>
            <!--            <div>-->
            <bookmark>
                <h5> Policy Title : <?php echo $policyTitle; ?>
                </h5>
            </bookmark>

            <table style="border:none;">
                <tr style="border:none">
                    <td style="border:none">Working Days :</td>
                    <td style="border:none"><?php echo isset($arr_salary_structure[$structure_iteration]['0']['DayTimeProcedures']['ondays']) ? $arr_salary_structure[$structure_iteration]['0']['DayTimeProcedures']['ondays'] : ''; ?>
                    </td>
                </tr>
                <tr style="border:none">
                    <td style="border:none">Off Days :</td>
                    <td style="border:none"><?php echo isset($arr_salary_structure[$structure_iteration]['0']['DayTimeProcedures']['offdays']) ? $arr_salary_structure[$structure_iteration]['0']['DayTimeProcedures']['offdays'] : ''; ?>
                    </td>
                </tr>
                <tr style="border:none">
                    <td style="border:none">On Duty :</td>
                    <td style="border:none"><?php echo isset($arr_salary_structure[$structure_iteration]['0']['DayTimeProcedures']['on_dutty1']) ? $arr_salary_structure[$structure_iteration]['0']['DayTimeProcedures']['on_dutty1'] : ''; ?>
                    </td>
                    <td style="border:none">Off Duty :</td>
                    <td style="border:none"><?php echo isset($arr_salary_structure[$structure_iteration]['0']['DayTimeProcedures']['off_dutty1']) ? $arr_salary_structure[$structure_iteration]['0']['DayTimeProcedures']['off_dutty1'] : ''; ?>
                    </td>
                </tr>
                <tr style="border:none">
                    <td style="border:none">Working Time :</td>
                    <td style="border:none"><?php echo isset($arr_salary_structure[$structure_iteration]['0']['DayTimeProcedures']['working_time1']) ? $arr_salary_structure[$structure_iteration]['0']['DayTimeProcedures']['working_time1'] : ''; ?>
                    </td>
                </tr>
            </table>
            <hr>
        </div>
        <div>
            <h4 style="text-align: left;padding-bottom: 10px;padding-top: 10px;">Stat Rule</h4>

            <span style="width: 200px">
                Minutes calculated as per day:<?php echo isset($arr_salary_structure[$structure_iteration]['0']['DayTimeProcedures']['minuts_calc_perday']) ? $arr_salary_structure[$structure_iteration]['0']['DayTimeProcedures']['minuts_calc_perday'] : ''; ?>

            </span>
            <br> <br>
            <span>
                Minutes after On duty calculated as late:<?php echo isset($arr_salary_structure[$structure_iteration]['0']['DayTimeProcedures']['minuts_aftr_on_dutty_cal_late']) ? $arr_salary_structure[$structure_iteration]['0']['DayTimeProcedures']['minuts_aftr_on_dutty_cal_late'] : ''; ?>
            </span>
            <br> <br>
            <span>
                Minutes before Off duty calculated as early:<?php echo isset($arr_salary_structure[$structure_iteration]['0']['DayTimeProcedures']['minuts_bfr_off_dutty_cal_early']) ? $arr_salary_structure[$structure_iteration]['0']['DayTimeProcedures']['minuts_bfr_off_dutty_cal_early'] : ''; ?>
            </span>
            <br> <br>
            <?php if (isset($arr_salary_structure[$structure_iteration]['0']['DayTimeProcedures']['min_cal_late_ifnoclockin']) && $arr_salary_structure[$structure_iteration]['0']['DayTimeProcedures']['min_cal_late_ifnoclockin'] = '') { ?>

                <span>
                    Minutes calculated as late if no clock-in:<?php echo $arr_salary_structure[$structure_iteration]['0']['DayTimeProcedures']['min_cal_late_ifnoclockin']; ?>
                </span>
                <br> <br>
            <?php } ?>
            <?php if (isset($arr_salary_structure[$structure_iteration]['0']['DayTimeProcedures']['min_cal_leave_early_ifnoclockout']) && $arr_salary_structure[$structure_iteration]['0']['DayTimeProcedures']['min_cal_leave_early_ifnoclockout'] = '') { ?>

                <span>
                    Minutes calculated as leave early if no clock-out:<?php echo $arr_salary_structure[$structure_iteration]['0']['DayTimeProcedures']['min_cal_leave_early_ifnoclockout']; ?>
                </span>
                <br> <br>
            <?php } ?>
            <?php if (isset($arr_salary_structure[$structure_iteration]['0']['DayTimeProcedures']['min_aftr_off_dutty_cal_ot']) && $arr_salary_structure[$structure_iteration]['0']['DayTimeProcedures']['min_aftr_off_dutty_cal_ot'] = '') { ?>
                <span>
                    Minutes after Off duty calculated as overtime:<?php echo $arr_salary_structure[$structure_iteration]['0']['DayTimeProcedures']['min_aftr_off_dutty_cal_ot']; ?>
                </span>
                <br> <br>
            <?php } ?>
            <?php if (isset($arr_salary_structure[$structure_iteration]['0']['DayTimeProcedures']['min_bfr_on_dutty_cal_ot']) && $arr_salary_structure[$structure_iteration]['0']['DayTimeProcedures']['min_bfr_on_dutty_cal_ot'] = '') { ?>
                <span>
                    Minutes before On duty calculated as overtime:<?php echo $arr_salary_structure[$structure_iteration]['0']['DayTimeProcedures']['min_bfr_on_dutty_cal_ot']; ?>
                </span>
                <br> <br>
            <?php } ?>
            <?php if (isset($arr_salary_structure[$structure_iteration]['0']['DayTimeProcedures']['work_time_day_off_cal_ot']) && $arr_salary_structure[$structure_iteration]['0']['DayTimeProcedures']['work_time_day_off_cal_ot'] = '') { ?>
                <span>
                    Working time in day off calculated as overtime:<?php echo $arr_salary_structure[$structure_iteration]['0']['DayTimeProcedures']['work_time_day_off_cal_ot']; ?>
                </span>
                <br> <br>
            <?php } ?>
            <hr>
        </div>
        <?php
        $structure_iteration++;
        ?>
        <!--            <div>-->
        <h4 style="text-align: left;padding-bottom: 10px;padding-top: 10px;">Employee List</h4>
        <table align="center">
            <thead>
                <tr>
                    <th style="width: 8%">Sl No</th>
                    <th style="width: 15%">Employee Name</th>
                    <th style="width: 15%">Employee ID</th>
                    <th style="width: 15%">Designation</th>
                    <th style="width: 15%">Date Of Joining</th>
                    <th style="width: 15%">Department</th>
                    <th style="width: 15%">Branch</th>
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
                        </tr>
                    <?php
                        $i++;
                    }
                    ?>
                <?php } else { ?>
                    <tr>
                        <td colspan="7">No employees found under this shift</td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <!--            </div>-->
        <!--        </div>-->
    <?php } ?><!-- /.box-body -->
<?php } ?>