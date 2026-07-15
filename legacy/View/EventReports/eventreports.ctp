<?php if( $mode == '' ){ ?>
<div class="modal-body" style="overflow-y: auto;">
   <!-- <h2 style="text-align:center;">Birthday & Annivesary Report</h2>-->
     <h2 style="font-weight: bold;text-align: center;"><?php echo " Events- " .$mname."  "  .$year?>  </h2> 
      <h2 style="font-weight: bold;text-align: center;font-size: 19px;"><?php echo  "(Report Run by " . $user_id . " at " . $date_time . ")"?>  </h2>
    <div class="row">
        <div class="col-md-12">
            <div class="">
               
                            <?php 
                            ini_set('memory_limit', '512M');
                            if (empty($arr_leavepolicydetails_for_template)){?> <!-- /.box-body -->
                   <div style="font-size: 16px;text-align:left;">
        No data available under the selected criteria</div>
             <?php } 
                             if($criterias == 'Types'){
                                if(isset($arr_leavepolicydetails_for_template['summary'])){
                                 $arr_daata  = $arr_leavepolicydetails_for_template['summary']; 
                                }
                                 if(isset($arr_leavepolicydetails_for_template['workanniversary'])){
                                 $arr_work=$arr_leavepolicydetails_for_template['workanniversary'];
                                 }
                                 if(isset($arr_leavepolicydetails_for_template['probation'])){
                                 $arr_prob=$arr_leavepolicydetails_for_template['probation'];
                                 }
                                if(isset($arr_daata)||isset($arr_work)||isset($arr_prob)){
                                
                                ?>
                             <?php if(isset($arr_work)){?>
                     <fieldset> 
                     <legend> 
                       Anniversary
                     </legend>
                    </fieldset>
                            <table class="table table-bordered">
                            <thead>
                              <tr>
                                <th>SI No </th>
                                 <th>Employee ID</th>
                                 <th>User ID</th>
                                 <th>Employee Name </th>
                                 <th>Branch</th>
                                 <th>Designation</th>
                                 <th>Department</th>
                                 <th>Date of Joining </th>
                                 <th>Termination Date</th>
                                 <th>Type</th>
                                 <th>Events/Probation End Date</th>
                                 
                              </tr>
                            </thead>
                            <tbody>
                             <?php $i=1;
                              foreach($arr_work as $val)
                               { ?>
                                    <tr>  <td><?php echo $i++; ?></td>
                                            <td><?php echo $val['employee_info']['employee_id']; ?> </td>   
                                            <td><?php echo $val['user_credentials']['user_id']; ?></td>
                                            <td><?php echo $val['emp_details']['first_name'].' '.$val['emp_details']['last_name']; echo (isset($val['emp_details']['status'])) && $val['emp_details']['status'] == "2" ? '  (Resigned)' : '';?></td>
                                            <td><?php echo $val['branches']['branch_name']; ?></td>
                                            <td><?php echo $val['desg']['desig_name']; ?></td>
                                             <td><?php echo $val['employee_info']['department']; ?></td>
                                            <td><?php echo date('d-m-Y', strtotime($val['emp_proff']['joining_date'])); ?></td>
                                            <td><?php echo isset($val['termination']['last_approved_working_date']) ? date('d-m-Y', strtotime($val['termination']['last_approved_working_date'])) : '';?></td>
                                            
                                            <td><?php if(!empty($val['emp_proff']['joining_date'])){ echo 'Anniversary';} ?></td>
                                           <!-- <td><?php  // echo date('d-m-Y', strtotime($val['emp_proff']['joining_date'])); ?></td> -->
                                              <td> <?php if (!empty($val['emp_proff']['joining_date'])) {
                                                            $joining_date = new DateTime($val['emp_proff']['joining_date']);
                                                            $currentYear = date('Y');
                                                            $joining_date->setDate($currentYear, $joining_date->format('m'), $joining_date->format('d'));
                                                            echo $joining_date->format('d-m-Y');
                                                        }
                                                      ?>
                                            </td>
                                            

                                    </tr>
                                    <?php    }?>  </tbody></table><?php }?>
                                <?php if(isset($arr_daata)){?>
                                <fieldset> 
                     <legend> 
                       Birthday
                     </legend>
                    </fieldset>
                            <table class="table table-bordered">
                            <thead>
                              <tr>
                                <th>SI No </th>
                                 <th>Employee ID</th>
                                 <th>User ID</th>
                                 <th>Employee Name </th>
                                 <th>Branch</th>
                                 <th>Designation</th>
                                 <th>Department</th>
                                 <th>Date of Joining </th>
                                 <th>Termination Date</th>
                                 <th>Type</th>
                                 <th>Events/Probation End Date</th>
                                 
                              </tr>
                            </thead>
                            <tbody>
                                <?php
                                  $i=1;  foreach($arr_daata as $val)
                               { ?>
                                   
                                    <tr>  <td><?php echo $i++; ?></td>
                                            <td><?php echo $val['employee_info']['employee_id']; ?> </td>   
                                            <td><?php echo $val['user_credentials']['user_id']; ?></td>
                                            <td><?php echo $val['emp_details']['first_name'].' '.$val['emp_details']['last_name']; echo (isset($val['emp_details']['status'])) && $val['emp_details']['status'] == "2" ? '  (Resigned)' : '';?></td>
                                            <td><?php echo $val['branches']['branch_name']; ?></td>
                                            <td><?php echo $val['desg']['desig_name']; ?></td>
                                            <td><?php echo $val['employee_info']['department']; ?></td>
                                            <td><?php echo date('d-m-Y', strtotime($val['emp_proff']['joining_date'])); ?></td>
                                            <td><?php echo isset($val['termination']['last_approved_working_date']) ? date('d-m-Y', strtotime($val['termination']['last_approved_working_date'])) : '';?></td>
                                            <td><?php if(!empty($val['emp_details']['date_of_birth'])){ echo 'Birthday';} ?></td>
                                            <!--<td><?php //echo date('d-m-Y', strtotime($val['emp_details']['date_of_birth'])); ?></td> -->
                                              <td><?php if (!empty($val['emp_details']['date_of_birth'])) {
                                                     $dob = new DateTime($val['emp_details']['date_of_birth']);
                                                     $currentYear = date('Y');
                                                     $dob->setDate($currentYear, $dob->format('m'), $dob->format('d'));
                                                      echo $dob->format('d-m-Y');}?></td>
                                            

                                    </tr>
                                <?php } ?> </tbody></table> <?php } ?>
                               
                                  <?php if(isset($arr_prob)){ ?>
                                      <fieldset> 
                     <legend> 
                       Probation
                     </legend>
                    </fieldset>
                            <table class="table table-bordered">
                            <thead>
                              <tr>
                                <th>SI No </th>
                                 <th>Employee ID</th>
                                 <th>User ID</th>
                                 <th>Employee Name </th>
                                 <th>Branch</th>
                                 <th>Designation</th>
                                 <th>Department</th>
                                 <th>Date of Joining </th>
                                 <th>Termination Date</th>
                                 <th>Type</th>
                                 <th>Events/Probation End Date</th>
                                 
                              </tr>
                            </thead>
                            <tbody>
                              <?php  $i=1; foreach($arr_prob as $val)
                               { ?>
                                    <tr>  <td><?php echo $i++; ?></td>
                                            <td><?php echo $val['employee_info']['employee_id']; ?> </td>   
                                            <td><?php echo $val['user_credentials']['user_id']; ?></td>
                                            <td><?php echo $val['emp_details']['first_name'].' '.$val['emp_details']['last_name']; echo (isset($val['emp_details']['status'])) && $val['emp_details']['status'] == "2" ? '  (Resigned)' : '';?></td>
                                            <td><?php echo $val['branches']['branch_name']; ?></td>
                                            <td><?php echo $val['desg']['desig_name']; ?></td>
                                             <td><?php echo $val['employee_info']['department']; ?></td>
                                            <td><?php echo date('d-m-Y', strtotime($val['emp_proff']['joining_date'])); ?></td>
                                            <td><?php echo isset($val['termination']['last_approved_working_date']) ? date('d-m-Y', strtotime($val['termination']['last_approved_working_date'])) : '';?></td>
                                           
                                            <td><?php  if (!empty($val['emp_proff']['emp_type']) && !empty($val['emp_proff']['attr2'])) {
                                                         echo 'Probation-' . $val['emp_proff']['attr2'] . ' Days';
                                                        } else {
                                                        echo 'Probation- 0 Days'; 
                                               } ?></td>
                                           <td>
                            
                            <?php
                            if (!empty($val['emp_proff']['attr2'])) {
                                $probationEndDate = new DateTime($val['emp_proff']['joining_date']);
                                $probationEndDate->add(new DateInterval('P' . $val['emp_proff']['attr2'] . 'D'));
                                echo $probationEndDate->format('d-m-Y');
                            } else {
                                echo '';
                            }
                            ?>
                         
                        </td>
                                            

                                    </tr>
                                  <?php    }?></tbody></table><?php }?>

                            <?php }else{ ?>
<!--                                    <tr>
                                            <td colspan="7">No Records found under this Criteria</td>
                                    </tr>  -->
                            <?php } }?>        
                    <?php if($criterias == 'EmployeeDetails')
                        { 
                         if(!empty($arr_leavepolicydetails_for_template)){$i=1;   ?>
                 <!--    <legend>Month : <?php //echo $month; ?></legend> -->
                      <table class="table table-bordered">
                            <thead>
                              <tr>
                                <th>SI No </th>
                                 <th>Employee ID</th>
                                 <th>User ID</th>
                                 <th>Employee Name </th>
                                 <th>Branch</th>
                                 <th>Designation</th>
                                 <th>Department</th>
                                 <th>Date of Joining </th>
                                 <th>Termination Date</th>
                                 <th>Type</th>
                                 <th>Events/Probation End Date</th>
                                 
                              </tr>
                            </thead>
                            <tbody>
                        <?php }  }?>
                              
                    <?php $i=1; foreach ($arr_leavepolicydetails_for_template as $value) {
                  //  $i += 1; 
                 // debug($value);
                   // if(empty($value['summary'])) continue;
                       // if(isset($value['summary'])||isset($value['workanniversary'])||isset($value['probation'])){
                        if(isset($value['summary'])){
                    $arr_daata  = $value['summary']; 
                   // debug($arr_daata);
                   // $arr_work=$value['workanniversary'];
                  //  $arr_prob=$value['probation'];
                    }
                    if($criterias == 'Units')
                        {  $i=1; ?>
               <!-- <div class="box-body">-->
                    <fieldset> 
                     <legend> 
                        <?php
                       if (!empty($arr_daata)) {
                            $printedBranches = []; // Array to keep track of printed branch names
    
                                foreach ($arr_daata as $val) {
                                     // Check if branch_name is set and not already printed
                                     if (isset($val[0]['branch_name']) && !in_array($val[0]['branch_name'], $printedBranches)) {
                                        echo $val[0]['branch_name'];
                                        $printedBranches[] = $val[0]['branch_name']; // Add to printed list
                                  }
                            }
                            }
//                        elseif(!empty($arr_work)){
//                           echo isset($arr_work[0]['branches']['branch_name'])?$arr_work[0]['branches']['branch_name']:'';  
//                        }else{
//                            echo isset($arr_prob[0]['branches']['branch_name'])?$arr_prob[0]['branches']['branch_name']:''; 
//                        }
                        ?>
                     </legend>
                    </fieldset>
                   <!-- <br>-->
                   <!-- <fieldset>-->
			<table class="table table-bordered">
                            <thead>
                            <tr>  <th>SI No </th>
                                 <th>Employee ID</th>
                                 <th>User ID</th>
                                 <th>Employee Name </th>
                                 <th>Branch</th>
                                 <th>Designation</th>
                                 <th>Department</th>
                                 <th>Date of Joining </th>
                                 <th>Termination Date</th>
                                 <th>Type</th>
                                 <th>Events/Probation End Date</th>
                            </tr>
                            </thead>
                            <tbody>
                    <?php } ?>


                                    
                            <?php 
                            if($criterias == 'Units'||$criterias == 'EmployeeDetails'){
                               // if(count($arr_daata)>0||count($arr_work)>0||count($arr_prob)>0){
                               if(count($arr_daata)>0){
                                ?>
                                <?php foreach($arr_daata as $val)
                               { //debug($val); 
                               $eventType = isset($val[0]['event_type']) ? $val[0]['event_type'] : '';?>
                                    <tr>  <td><?php echo $i++; ?></td>
                                            <td><?php echo $val[0]['employee_id']; ?> </td>   
                                            <td><?php echo $val[0]['user_id']; ?></td>
                                            <td><?php echo $val[0]['first_name'].' '.$val[0]['last_name'];echo (isset($val[0]['status'])) && $val[0]['status'] == "2" ? '  (Resigned)' : ''; ?></td>
                                            <td><?php echo $val[0]['branch_name']; ?></td>
                                            <td><?php echo $val[0]['desig_name']; ?></td>
                                            <td><?php echo $val[0]['department']; ?></td>
                                            <td><?php echo date('d-m-Y', strtotime($val[0]['joining_date'])); ?></td>
                                            <td><?php echo isset($val[0]['last_approved_working_date']) ? date('d-m-Y', strtotime($val[0]['last_approved_working_date'])) : '';?></td>
                                            <td><?php if ($eventType == 'Birthday') {
            echo 'Birthday';
        } elseif ($eventType == 'Anniversary') {
            echo 'Anniversary';
        } elseif ($eventType == 'Probation') {
            if(!empty($val[0]['attr2'])){
           echo 'Probation-' . $val[0]['attr2'] . ' Days';
            }else{
                echo 'Probation- 0 Days';
            }
            
        } ?></td>
                                            <!--<td><?php //echo date('d-m-Y', strtotime($val['emp_details']['date_of_birth'])); ?></td> -->
                                            <td><?php if (!empty($val[0]['date_of_birth'])&& $eventType == 'Birthday') {
                                                     $dob = new DateTime($val[0]['date_of_birth']);
                                                     $currentYear = date('Y');
                                                     $dob->setDate($currentYear, $dob->format('m'), $dob->format('d'));
                                                      echo $dob->format('d-m-Y');}
                                                      elseif (!empty($val[0]['joining_date'])&&$eventType == 'Anniversary') {
                                                            $joining_date = new DateTime($val[0]['joining_date']);
                                                            $currentYear = date('Y');
                                                            $joining_date->setDate($currentYear, $joining_date->format('m'), $joining_date->format('d'));
                                                            echo $joining_date->format('d-m-Y');
                                                        }elseif (!empty($val[0]['attr2'])) {
                                $probationEndDate = new DateTime($val[0]['joining_date']);
                                $probationEndDate->add(new DateInterval('P' . $val[0]['attr2'] . 'D'));
                                echo $probationEndDate->format('d-m-Y');
                            } else {
                                echo '';
                            }?></td>
                                          </tr>
                                    <?php    } ?>
                               

                            <?php }else{ ?>
<!--                                    <tr>
                                            <td colspan="7">No Records found under this Criteria</td>
                                    </tr>  -->
                            <?php } }?>
                    <?php if($criterias != 'EmployeeDetails'){ ?>
                            </tbody>
                        </table>
                    
                    <?php } ?>
                            <?php }  if($criterias == 'EmployeeDetails'){ ?>
                       </tbody>
                    </table>
	    <?php } ?>
             
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
<?php
echo $this->element('reportadminheader',array(
'title'=>'Site Assignment Wise Attendance : '.$month));
?>

                    <?php if($criterias == 'EmployeeDetails')
                        { if(!empty($arr_leavepolicydetails_for_template)){?>
                      <table class="table table-bordered">
                            <thead>
                              <tr>
                                <th>SI No</th>
                                 <th style="width:20%;">Client name </th>
                                 <th style="width:20%;">Site Name</th>
                                 <th>Employee ID </th>
                                 <th>Employee Name</th>
                                 <th>Branch </th>
                                 <th>Designation</th>
                                 <th>Total Hours</th>
                                 <th> Actual Working Days </th>
                                 <th>Payment Mode </th>
                              </tr>
                            </thead>
                            <tbody>
                        <?php } }?>
                    <?php $i=0; foreach ($arr_leavepolicydetails_for_template as $value) {
                    $i += 1; 
                    if(empty($value['summary'])) continue;
                    $arr_daata  = $value['summary']; 
                    if($criterias != 'EmployeeDetails')
                        { ?>
                     <h3>Site Assignment Details of  
                        <?php
                        if($criterias != 'Units')
                        {
                        echo isset($arr_daata[0]['site']['site_name'])?$arr_daata[0]['site']['site_name']:''; 
                        }else{
                        echo isset($arr_daata[0]['branches']['branch_name'])?$arr_daata[0]['branches']['branch_name']:'';    
                        }
                        ?>
                     </h3>
                 
			<table class="table table-bordered">
                            <thead>
                            <tr><th>SI No </th>
                                <th style="width:20%;">Client name </th>
                                <th style="width:20%;">Site Name</th>
                                <th>Employee ID </th>
                                <th>Employee Name</th>
                                <th>Branch </th>
                                <th>Designation</th>
                                <th>Total Hours</th>
                                <th>Actual Working Days </th>
                                <th>Payment Mode </th>
                            </tr>
                            </thead>
                            <tbody>
                    <?php } ?>
                            <?php if(count($arr_daata)>0){ ?>
                                <?php foreach($arr_daata as $val){ ?>
                                    <tr> 
                                          <td><?php echo $i; ?></td>
                                            <td style="width:20%;"><?php echo $val['contacts']['company_name']; ?> </td>   
                                            <td style="width:20%;"><?php echo $val['site']['site_name']; ?></td>
                                            <td><?php echo $val['emp_proff']['emp_company_id']; ?></td>
                                            <td><?php echo $val['emp_details']['first_name'].' '.$val['emp_details']['last_name']; ?></td>
                                            <td><?php echo $val['branches']['branch_name']; ?></td>
                                            <td><?php echo $val['designation']['desig_name']; ?></td>
                                            <td><?php echo round($val['0']['times'],2); ?></td>
                                            <td><?php echo $val['0']['days']; ?></td> 
                                            <td><?php if($val['site']['payment_mode']=='1'){
                         echo $payment_mode= 'Bank';
                         }
                          if($val['site']['payment_mode']=='2'){
                         echo $payment_mode= 'Cash';
                         } ?></td> 
                                    </tr>
                                    <?php } ?>
                                <?php }else{ ?>
                                    <tr>
                                            <td colspan="7">No employees found under this Site</td>
                                    </tr>  
                                <?php } ?>
                   <?php if($criterias != 'EmployeeDetails'){ ?>
                            </tbody>
                        </table>
                    
                    <?php } ?>
            <?php }  if($criterias == 'EmployeeDetails'){ 
                if(!empty($arr_leavepolicydetails_for_template)){ ?>
                       </tbody>
                    </table>
            <?php } }
             if (empty($arr_leavepolicydetails_for_template)){?> <!-- /.box-body -->
                    <h3 style="text-align:center;color:red;">No Records found under this Criteria</h3>
             <?php } ?> 
         
<?php } ?> 