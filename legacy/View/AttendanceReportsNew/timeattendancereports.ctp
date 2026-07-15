<?php if( $mode == '' ){ ?>
<div class="modal-body" style="overflow-y: auto;">
    <legend>Employees Attendance  Report</legend>
    <div class="row">
        <div class="col-md-12">
            <div class="box ">
                   <?php $i=0; foreach ($arr_leavepolicydetails_for_template as $value) {
                   $i += 1; 
                  ?>
                <div class="box-body">
                    <fieldset>  <?php $arr_daata  = $value['summary']; ?>
                       <legend>Attedance Details of  
                    <?php  
                    
                   $pp= isset($arr_daata[0]['branches']['branch_name'])?$arr_daata[0]['branches']['branch_name']:''; 
                        if($pp != '')
                        {
                        echo isset($arr_daata[0]['branches']['branch_name'])?$arr_daata[0]['branches']['branch_name']:''; 
                        }
                        else
                        {
                        echo isset($arr_daata[0]['department']['dept_name'])?$arr_daata[0]['department']['dept_name']:''; 
                        
                        }
                        
                        ?>
                        </legend>
                      
                        <div class="row">
                            <div class="col-md-12">
                                
                            </div>
                        </div>
                       
                      
                    </fieldset>
                    <br>
                    <fieldset>
			<legend>Stat Rule</legend>

			    <table class="table table-bordered">
                            <thead>
                            <tr>
                     <th>Employee NAME</th>
                                  <th>Date</th>
                                  <th>in/out</th>
                                  <th>Location</th>
              </tr>
                            </thead>
                            <tbody>
                                <?php $arr_data  = $value['summary']; ?>
                                <?php if(count($arr_data)>0){ ?>
                                    <?php foreach($arr_data as $val){ ?>
                                    <tr> 
                                               
                                            <td><?php echo $val['EmployeeDetails']['first_name'].$val['EmployeeDetails']['last_name']; ?></td>
                                            <td><?php echo $val['DeviceAttendance']['LOGDATE']; ?></td>
                                            <td><?php echo $val['DeviceAttendance']['C1']; ?></td>
                                            <td><?php echo $val['DeviceAttendance']['C3']; ?></td> </tr>
                                    </tr>
                                    <?php } ?>
                                <?php }else{ ?>
                                        <tr>
                                            <td colspan="4">No employees found under this shift</td>
                                        </tr>  
                                <?php } ?>
                            </tbody>
                        </table>
		
                    </fieldset>
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
                                <?php $arr_data  = $value['employees']; ?>
                                <?php if(count($arr_data)>=0){ ?>
                                    <?php foreach($arr_data as $val){ ?>
                                        <tr> 
                                            <td><?php echo $val['EmployeeDetails']['first_name'].' '.$val['EmployeeDetails']['last_name']; ?></td>
                                            <td><?php echo $val['EmployeeProfessionalDetails']['designation']; ?></td>
                                            <td><?php echo $val['Units']['branch_name']; ?></td>
                                        </tr>
                                    <?php } ?>
                                <?php }else{ ?>
                                        <tr>
                                            <td colspan="4">No employees found under this shift</td>
                                        </tr>  
                                <?php } ?>
                            </tbody>
                        </table>
                    </fieldset> -->
                </div>
                   <?php  } ?> <!-- /.box-body -->
            </div>
        </div>
    </div>    
    <div class="row">
        <div class="form-group">
            <div class="col-md-12" align="right">
                <a href="#" class="btn btn-default" onclick="downloadReport('shiftpolicy','pdf');" ><i class="icon-file"></i>Download As PDF</a>
                <!--a href="#" class="btn btn-default" onclick="downloadReport('shiftpolicy','excel');"><i class="icon-file"></i>Download As Excel</a-->
            </div>
        </div>
    </div>
</div>
<?php }else{ ?>
<?php //echo '<style>'.file_get_contents("css/pdfbootstrap.css").'</style>'; ?>
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
    td, th {
        text-align: left;
        padding: 8px;
        line-height: 1.42857143;
        vertical-align: top;
        border: 1px solid #B2B2B2;
    }
</style>
<div class="block-container">
    <h2 style="text-align: center;">Shift Policy Report</h2>
    <div class="block-container">
        <h3 class="sub-head">Shift Summary</h3>
        <div class="row">
            <div class="col-md-12">
                Policy Title : <?php echo $policytitle; ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                Working Days : <?php echo $str_workingdays; ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                Off Days : <?php echo $str_offdays; ?>
            </div>
        </div>
        <?php if(count($arr_duty)){ ?>
        <table class="table table-bordered">
            <tbody>
            <?php foreach($arr_duty as $key=>$duty){ ?>
                <tr>
                    <td style="width: 33.33%;text-align: left;">
                        On Duty <?php echo $key+1; ?> : <?php echo $duty['on_dutty']; ?>
                    </td>
                    <td style="width: 33.33%;text-align: left;">
                        Off Duty <?php echo $key+1; ?> : <?php echo $duty['off_dutty']; ?>
                    </td>
                    <td style="width: 33.33%;text-align: left;">
                        Working Time <?php echo $key+1; ?> : <?php echo $duty['working_time']; ?>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
        <?php } ?>
    </div>
    <br>
    <div class="block-container">
        <h3 class="sub-head">Stat Rule</h3>

        <div class="row ">
            <div class="col-sm-12">
                <span style="width: 200px">Minutes calculated as per day:<?php echo isset($arr_shiftpolicy_summary['minuts_calc_perday'])?$arr_shiftpolicy_summary['minuts_calc_perday']:'' ?></span>
            </div>
        </div>
        <div class="row ">
            <div class="col-sm-12">
                <span>Minutes after On duty calculated as late:<?php echo isset($arr_shiftpolicy_summary['minuts_aftr_on_dutty_cal_late'])?$arr_shiftpolicy_summary['minuts_aftr_on_dutty_cal_late']:'' ?></span>
            </div>
        </div>
        <div class="row ">
            <div class="col-sm-12">
                <span>Minutes before Off duty calculated as early:<?php echo isset($arr_shiftpolicy_summary['minuts_bfr_off_dutty_cal_early'])?$arr_shiftpolicy_summary['minuts_bfr_off_dutty_cal_early']:'' ?></span>
            </div>
        </div>
        <?php if(isset($arr_shiftpolicy_summary['min_cal_late_ifnoclockin']) && $arr_shiftpolicy_summary['min_cal_late_ifnoclockin'] = ''){ ?>
        <div class="row ">
            <div class="col-sm-12">
                <span>Minutes calculated as late if no clock-in:<?php echo $arr_shiftpolicy_summary['min_cal_late_ifnoclockin']; ?></span>
            </div>
        </div>
        <?php } ?>
        <?php if(isset($arr_shiftpolicy_summary['min_cal_leave_early_ifnoclockout']) && $arr_shiftpolicy_summary['min_cal_leave_early_ifnoclockout'] = ''){ ?>
        <div class="row ">
            <div class="col-sm-12">
                <span>Minutes calculated as leave early if no clock-out:<?php echo $arr_shiftpolicy_summary['min_cal_leave_early_ifnoclockout']; ?></span>
            </div>
        </div>
        <?php } ?>
        <?php if(isset($arr_shiftpolicy_summary['min_aftr_off_dutty_cal_ot']) && $arr_shiftpolicy_summary['min_aftr_off_dutty_cal_ot'] = ''){ ?>
        <div class="row ">
            <div class="col-sm-12">
                <span>Minutes after Off duty calculated as overtime:<?php echo $arr_shiftpolicy_summary['min_aftr_off_dutty_cal_ot']; ?></span>
            </div>
        </div>
        <?php } ?>
        <?php if(isset($arr_shiftpolicy_summary['min_bfr_on_dutty_cal_ot']) && $arr_shiftpolicy_summary['min_bfr_on_dutty_cal_ot'] = ''){ ?>
        <div class="row ">
            <div class="col-sm-12">
                <span>Minutes before On duty calculated as overtime:<?php echo $arr_shiftpolicy_summary['min_bfr_on_dutty_cal_ot']; ?></span>
            </div>
        </div>
        <?php } ?>
        <?php if(isset($arr_shiftpolicy_summary['work_time_day_off_cal_ot']) && $arr_shiftpolicy_summary['work_time_day_off_cal_ot'] = ''){ ?>
        <div class="row ">
            <div class="col-sm-12">
                <span>Working time in day off calculated as overtime:<?php echo $arr_shiftpolicy_summary['work_time_day_off_cal_ot']; ?></span>
            </div>
        </div>
        <?php } ?>
    </div>
    <br>
    <div class="block-container">
        <h3 class="sub-head">Employee List</h3>
        <table class="table table-bordered">
            <thead>
              <tr>
                  <th>Employee ID</th>
                  <th>Employee name</th>
                  <th>Designation</th>
                  <th>Branch</th>
              </tr>
            </thead>
            <tbody>
                <?php if(count($arr_employee_details)>=0){ ?>
                    <?php foreach($arr_employee_details as $value){ ?>
                        <tr> 
                            <td><?php echo $value['companyemployeeid']; ?></td>
                            <td><?php echo $value['fullname']; ?></td>
                            <td><?php echo $value['designation']; ?></td>
                            <td><?php echo $value['branch']; ?></td>
                        </tr>
                    <?php } ?>
                <?php }else{ ?>
                        <tr>
                            <td colspan="4">No employees found under this shift</td>
                        </tr>  
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>
<?php } ?>