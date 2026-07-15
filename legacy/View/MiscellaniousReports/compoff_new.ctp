<!-- edited by athira on 10-10-2025 -->
<?php if ($mode == '') { ?>
<div class="modal-body myDivToPrint" style="overflow-y:auto;overflow-x:auto;" id="toPrint">
    <legend style="margin-bottom:0px;text-align:center;border:none;"><b>Compensatory Off Details Report</b></legend>
    <div class="row">
        <div class="col-md-12">
            <div class="box" style="border:none;">
                <?php
                $i = 0;
                if(empty($arr_leavepolicydetails_for_template)){ ?>
                    <p>No data under this criteria</p>
                <?php }
                foreach ($arr_leavepolicydetails_for_template as $value) {
                     // edited by athira on 07-04-2026
                    if (!empty($value['summary']) || !empty($value['leaves'])) {
                    // ended by athira on 07-04-2026
                        $i++;
                        $emp = $value['emp_dets'][0]['employee_info'];
                ?>
                <div class="box-body">
                    <!-- <fieldset> -->
                        <div style="display:flex;justify-content:left;align-items:center;margin-bottom:20px;"> 
                            <span><b>Employee Name : <?php echo $emp['EmpName']; ?>
                            <?php echo isset($value['status'][0]['emp_details']['status']) && $value['status'][0]['emp_details']['status']=="2" ? '(Resigned)' : ''; ?></b> </span>
                            <span style="margin-left:20px;"><b>Leave Policy Type :
                            <?php
                            $type = $value['leavepolicy'];
                            switch ($type) {
                                case 'M': echo 'Monthly'; break;
                                case 'Y': echo 'Yearly'; break;
                                case 'Q': echo 'Quarterly'; break;
                                case 'H': echo 'Half Yearly'; break;
                                case 'D': echo 'Running Days'; break;
                                case 'P': echo 'Present Days'; break;
                                default: echo $type; break;
                            }
                            ?>
                        </b></span>

                    </div>
                    <!-- </fieldset> -->

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Employee Name</th>
                                <?php if ($company_code=='GLET' || $company_code=='SRTS') { ?>
                                    <th>Employee Name (US Format)</th>
                                <?php } ?>
                                <th>Employee ID</th>
                                <?php if ($company_code=='GLET' || $company_code=='SRTS') { ?>
                                    <th>Employee ID (US Format)</th>
                                <?php } ?>
                                <th>Date Of Join</th>
                                <th>Branch</th>
                                <th>Department</th>
                                <th>Designation</th>
                                <!-- <th>Leave Policy Type</th> -->
                                <th>Transaction Type</th>
                                <th>Accrued / Utilized Date</th>
                                <th>Day</th>
                                <th>Duration</th>
                                <th>Day Type</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Leave Policy Type
                            $type = isset($value['lp']['leave_policy_type']) ? $value['lp']['leave_policy_type'] : '';
                            $type_label = '';
                            switch ($type) {
                                case 'M': $type_label = 'Monthly'; break;
                                case 'Y': $type_label = 'Yearly'; break;
                                case 'Q': $type_label = 'Quarterly'; break;
                                case 'H': $type_label = 'Half Yearly'; break;
                                case 'D': $type_label = 'Running Days'; break;
                                case 'P': $type_label = 'Present Days'; break;
                                default: $type_label = ''; break;
                            }

                            // --- 1️⃣ ACCRUED ---
                            $arr_data = $value['summary'];
                            $count = 0;
                            foreach ($arr_data as $val) {
                                $count++;
                                echo '<tr>';
                                echo '<td>'.$emp['EmpName'].'</td>';
                                if ($company_code=='GLET' || $company_code=='SRTS') echo '<td>'.$emp['EmpUSName'].'</td>';
                                echo '<td>'.$emp['employee_id'].'</td>';
                                if ($company_code=='GLET' || $company_code=='SRTS') echo '<td>'.$emp['emp_us_id'].'</td>';
                                echo '<td>' . date('d-m-Y', strtotime($emp['joining_date'])) . '</td>';
                                echo '<td>'.$emp['branch'].'</td>';
                                echo '<td>'.$emp['department'].'</td>';
                                echo '<td>'.$emp['designation'].'</td>';
                                // echo '<td>'.$type_label.'</td>';
                                echo '<td>Accrued</td>';
                                echo '<td>' . date('d-m-Y', strtotime($val[0]['att_date'])) . '</td>';
                                echo '<td></td>';
                                echo '<td>'.$val[0]['duration'].'</td>';
                                echo '<td>'.$val[0]['weekoff'].' '.$val[0]['holiday'].'</td>';
                                echo '<td>Accrued</td>';
                                echo '</tr>';
                            }

                            // --- 2️⃣ UTILIZED ---
                            $leaves = $value['leaves'];
                           
                            foreach ($leaves as $lv) {
                                if($lv['emp_leave_transactions']['leave_session']==1){
                                    $day=0.5 . " (First Half) ";
                                }
                                if($lv['emp_leave_transactions']['leave_session']==2){
                                    $day=0.5 . " (Second Half) ";
                                }
                                else if ($lv['emp_leave_transactions']['leave_session']==3){
                                    $day=1 . " (Full Day) ";
                                }
                                echo '<tr>';
                                echo '<td>'.$emp['EmpName'].'</td>';
                                if ($company_code=='GLET' || $company_code=='SRTS') echo '<td>'.$emp['EmpUSName'].'</td>';
                                echo '<td>'.$emp['employee_id'].'</td>';
                                if ($company_code=='GLET' || $company_code=='SRTS') echo '<td>'.$emp['emp_us_id'].'</td>';
                                echo '<td>' . date('d-m-Y', strtotime($emp['joining_date'])) . '</td>';
                                echo '<td>'.$emp['branch'].'</td>';
                                echo '<td>'.$emp['department'].'</td>';
                                echo '<td>'.$emp['designation'].'</td>';
                                // echo '<td>'.$type_label.'</td>';
                                echo '<td>Utilized</td>';
                                echo '<td>' . date('d-m-Y', strtotime($lv['emp_leave_transactions']['leave_date'])) . '</td>';
                                echo '<td>'.$day.'</td>';
                                echo '<td></td>'; // no duration
                                echo '<td></td>'; // no day type
                                echo '<td>'.$lv['emp_leave_transactions']['Leavestatus'].'</td>';
                                echo '</tr>';
                            }
                            ?>
                            <!-- <tr style="background-color: #b8b8b8;">
                                <td colspan=""><b>Balance As On Today (<?php echo date('d-m-Y'); ?>)</b>  </td>
                                <td colspan="11"><?php echo isset($value['eligibility'][0][0]['blnce']) ? $value['eligibility'][0][0]['blnce'] : 0; ?></td>
                            <tr> -->
                        </tbody>
                    </table>
                     <?php ?>
                    
                    

                </div>
                <?php
                    }
                }
                ?>
            </div>
        </div>
    </div>
</div>
<?php } 
//end