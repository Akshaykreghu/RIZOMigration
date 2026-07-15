<?php
if ($mode == '') { ?>
    <div class="modal-body" style="overflow-y: auto;">
        <h2 style="font-weight: bold;text-align: center;"><?php echo "Attendance Status- " .$mname."  "  .$year?> </h2>
       
        <div class="row">
            <div class="col-md-12">
                <?php // debug($arr_statusdata_for_template); 
                ?>
                <?php
                if (count($arr_statusdata_for_template)) {
                    foreach ($arr_statusdata_for_template as $key => $value) {

                        $i = 0;
                        // debug($value);
                        $name = ($value['summary'][0]['ed']['first_name']) ? $value['summary'][0]['ed']['first_name'] : '';
                        $name .= ($value['summary'][0]['ed']['middile_name']) ? " " . $value['summary'][0]['ed']['middile_name'] : '';
                        $name .= ($value['summary'][0]['ed']['last_name']) ? " " . $value['summary'][0]['ed']['last_name'] : '';
                        $resign_status = isset($value['summary'][0]['ed']['status']) && ($value['summary'][0]['ed']['status'] == "2") ? '(Resigned)' : '';
                ?>
                        <!-- <h4 style="font-weight: bold">Attendance Status of <?php echo ($name) ? $name : ''; ?> <?php echo $resign_status ?></h4> -->
                         <fieldset> 

                             <?php
                                if($cr== 'Units')
                                {
                                ?>
                               <legend> <?php echo isset($value['summary']['0']['ei']['branch']) ? $value['summary']['0']['ei']['branch'] : '    (No Datas Found Under This Branch)'; ?>  </legend>
                                <?php
                                }
                                else
                                {
                              $empstatus = (isset($value['summary']['0']['ed']['status'])) && $value['summary']['0']['ed']['status'] =="2" ? '  (Resigned)':'';
                                    ?>
                              
                    <legend>  <?php echo $name;?>  </legend>
                                <?php
                                    }
                                ?>

                        </fieldset>


                        <table class="table table-bordered">
                            <thead>

                                <tr><th colspan="9" style="text-align: center;">Employee Details</th>
                                 <th colspan="4" style="text-align: center;">Allocated Policies</th>
                                  <th colspan="10" style="text-align: center;">Attendance Details</th></tr>
                                <tr>

                                    <th>Sl No</th>
                                    <th>Employee ID</th>
                                     <th>Company ID</th>
                                      <th>Employee Name</th>
                                       <th>Joining Date</th>
                                      <th>Branch</th>
                                    <th>Department</th>
                                     <th>Designation</th>
                                      <th>Termination Date</th>
                                       <th>Shift Policy</th>
                                      <th>Leave Policy</th>
                                    <th>Holiday</th>
                                     <th>Salary Structure</th>
                                    <th>Attendance Date</th>
                                    <th>In Time</th>
                                    <th>Out Time</th>
                                    <th>Duration</th>
                                    <th>Present</th>
                                    <th>Weekoff</th>
                                    <th>Leaves</th>
                                    <th>Holiday</th>
                                    <th>Other</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $arr_data  = $value['summary']; ?>
                                <?php if (count($arr_data) >= 0) { ?>
                                    <?php

                                    foreach ($arr_data as $val) {

                                        // debug($val);
                                        $i++; // sl no increment

                                        $att_date = $val['AD']['att_date'];
                                        $att_in_time = $val['AD']['att_in_time'];
                                        $att_out_time = $val['AD']['att_out_time'];

                                    $emp_id=$val['ei']['emp_id'];
                                    $company_id=$val['ei']['employee_id'];
                                    $branch=$val['ei']['branch'];
                                    $dep=$val['ei']['department'];
                                    $join=$val['ei']['joining_date'];
                                    $designation=$val['ei']['designation'];
                                    $termin = $val['te']['last_approved_working_date'];

                                    $shift=$val['wp']['day_time_desc'];
                                    $lev=$val['lg']['LEAVEPOLICY_GROUP_NAME'];
                                    $holi=$val['hg']['HOLIDAY_GROUP_NAME'];
                                    $salstructure = $val['ss']['structure_name'];

                                        $attendance_date = date("d-m-Y", strtotime($att_date));
                                        $attendance_in_time = ($att_in_time) ? date("d-m-Y H:i:s", strtotime($att_in_time)) : '';
                                        $attendance_out_time = ($att_out_time) ? date("d-m-Y H:i:s", strtotime($att_out_time)) : '';

                                        $all_status = ($val['AD']['present']) ? $val['AD']['present'] : '';
                                        $all_status .= ($val['AD']['weekoff']) ? " ".$val['AD']['weekoff'] : '';
                                        $all_status .= ($val['AD']['leaves']) ? " ".$val['AD']['leaves'] : '';
                                        $all_status .= ($val['AD']['holiday']) ? " ".$val['AD']['holiday'] : '';
                                        $all_status .= ($val['AD']['others']) ? " ".$val['AD']['others'] : '';
                                    ?>

                                        <tr>
                                            <td><?php echo $i; ?></td>
                                            <td><?php echo $emp_id; ?></td>
                                             <td><?php echo $company_id; ?></td>
                                            <td><?php echo $name; ?></td>
                                             <td><?php echo $join; ?></td>
                                            <td><?php echo $branch; ?></td>
                                             <td><?php echo $dep; ?></td>
                                            <td><?php echo $designation; ?></td>
                                             <td><?php echo $termin; ?></td>
                                            <td><?php echo $shift; ?></td>
                                            <td><?php echo $lev; ?></td>
                                             <td><?php echo $holi; ?></td>
                                            <td><?php echo $salstructure; ?></td>
                                            <td><?php echo $attendance_date; ?></td>
                                            <td><?php echo $attendance_in_time; ?></td>
                                            <td><?php echo $attendance_out_time; ?></td>
                                            <td><?php echo $val['AD']['duration']; ?></td>
                                            <td><?php echo $val['AD']['present']; ?></td>
                                            <td><?php echo $val['AD']['weekoff']; ?></td>
                                            <td><?php echo $val['AD']['leaves']; ?></td>
                                            <td><?php echo $val['AD']['holiday']; ?></td>
                                            <td><?php echo $val['AD']['others']; ?></td>
                                            <td><?php echo $all_status; ?></td>
                                           

                                        </tr>


                                    <?php } ?>


                            </tbody>
                        </table>
            <?php
                                } else {
                                   echo '<div style="font-size: 25px;text-align:left; ">
                   There is no data available under the selected criteria.</div>';
                                }
                            } // endforeach
                        } else {
                            echo '<div style="font-size: 25px;text-align:left;">
                   There is no data available under the selected criteria.</div>';
                        } // endif
            ?>

            </div>
        </div>

    </div>
<?php
} else {
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
    <!--   <legend style="font-weight: bold">Attendance Check In Out Report </legend>-->
    <?php
    echo $this->element('reportadminheader', array('title' => 'Attendance Status Report '));
    ?>
    <?php
    if (count($arr_statusdata_for_template)) {
        foreach ($arr_statusdata_for_template as $value) {

            $i = 0;

            $name = ($value['summary'][0]['ed']['first_name']) ? $value['summary'][0]['ed']['first_name'] : '';
            $name .= ($value['summary'][0]['ed']['middile_name']) ? " " . $value['summary'][0]['ed']['middile_name'] : '';
            $name .= ($value['summary'][0]['ed']['last_name']) ? " " . $value['summary'][0]['ed']['last_name'] : '';
            $resign_status = isset($value['summary'][0]['ed']['status']) && ($value['summary'][0]['ed']['status'] == "2") ? '(Resigned)' : '';
    ?>
            <div>
                <?php
                ?>

                <h4 style="font-weight: bold">Attendance Status Report of <?php echo ($name) ? $name : ''; ?> <?php echo $resign_status ?></h4>

                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Sl No.</th>
                            <th style="width:100px;">Employee Name</th>
                            <th style="width:100px;">Attendance Date</th>
                            <th style="width:100px;">In Time</th>
                            <th style="width:100px;">Out Time</th>
                            <th style="width:100px;">Duration</th>
                            <th style="width:100px;">Present</th>
                            <th style="width:100px;">Weekoff</th>
                            <th style="width:100px;">Leaves</th>
                            <th style="width:100px;">Holiday</th>
                            <th style="width:100px;">Other</th>
                            <th style="width:100px;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $arr_data  = $value['summary']; ?>
                        <?php if (count($arr_data) >= 0) { ?>
                            <?php

                            foreach ($arr_data as $val) {
                                $i++; // sl no increment

                                $att_date = $val['AD']['att_date'];
                                $att_in_time = $val['AD']['att_in_time'];
                                $att_out_time = $val['AD']['att_out_time'];

                                $attendance_date = date("d-m-Y", strtotime($att_date));
                                $attendance_in_time = date("d-m-Y H:i:s", strtotime($att_in_time));
                                $attendance_out_time = date("d-m-Y H:i:s", strtotime($att_out_time));

                                $all_status = ($val['AD']['present']) ? $val['AD']['present'] : '';
                                $all_status .= ($val['AD']['weekoff']) ? " ".$val['AD']['weekoff'] : '';
                                $all_status .= ($val['AD']['leaves']) ? " ".$val['AD']['leaves'] : '';
                                $all_status .= ($val['AD']['holiday']) ? " ".$val['AD']['holiday'] : '';
                                $all_status .= ($val['AD']['others']) ? " ".$val['AD']['others'] : '';

                            ?>

                                <tr>
                                    <td><?php echo $i; ?></td>
                                    <td style="width:100px;"><?php echo $name; ?></td>
                                    <td style="width:100px;"><?php echo $attendance_date; ?></td>
                                    <td style="width:100px;"><?php echo $attendance_in_time; ?></td>
                                    <td style="width:100px;"><?php echo $attendance_out_time; ?></td>
                                    <td style="width:100px;"><?php echo $val['AD']['duration']; ?></td>
                                    <td style="width:100px;"><?php echo $val['AD']['present']; ?></td>
                                    <td style="width:100px;"><?php echo $val['AD']['weekoff']; ?></td>
                                    <td style="width:100px;"><?php echo $val['AD']['leaves']; ?></td>
                                    <td style="width:100px;"><?php echo $val['AD']['holiday']; ?></td>
                                    <td style="width:100px;"><?php echo $val['AD']['others']; ?></td>
                                    <td style="width:100px;"><?php echo $all_status; ?></td>

                                </tr>

                            <?php } ?>

                    </tbody>
                </table>
            <?php
                        } else {
                            echo "No employees found under this Criteria";
                        }
            ?>
            </div>

<?php
        } // endforeach
    } else {
        echo "No records found under this Criteria";
    } // endif
}
?>