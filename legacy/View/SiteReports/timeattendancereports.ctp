<?php if( $mode == '' ){ ?>
<div class="modal-body" style="overflow-y: auto;">
    <h2 style="text-align:center;">Site Assignment Wise Attendance Report</h2>
   <h4 align="center" style="font-weight:bold;"><?php echo isset($user_name) ? "Report run by " . ($user_name) . " - " . $arr_date : ''; ?></h4>  
    
    <div class="row">
        <div class="col-md-12">
            <div class="box ">
                    <?php if($criterias == 'EmployeeDetails')
                        { 
                         if(!empty($arr_leavepolicydetails_for_template)){   ?>
                     <legend>Month : <?php echo $month; ?></legend> 
                      <table class="table table-bordered">
                            <thead>
                              <tr>
                                <th>SI No </th>
                                 <th>Client name </th>
                                 <th>Site Name</th>
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
                        <?php }  }?>
                    <?php $i=1; foreach ($arr_leavepolicydetails_for_template as $value) {
                   
                    if(empty($value['summary'])) continue;
                    $arr_daata  = $value['summary']; 
                    if($criterias != 'EmployeeDetails')
                        { ?>
                <div class="box-body">
                    <fieldset> 
                     <legend>Site Assignment Details of  
                        <?php
                        if($criterias != 'Units')
                        {
                        echo isset($arr_daata[0]['site']['site_name'])?$arr_daata[0]['site']['site_name'].' ('.$arr_daata[0]['site']['site_id'].')':''; 
                        }else{
                        echo isset($arr_daata[0]['branches']['branch_name'])?$arr_daata[0]['branches']['branch_name']:'';    
                        }
                        ?>
                     </legend>
                    </fieldset>
                    <br>
                    <fieldset>
			<table class="table table-bordered">
                            <thead>
                            <tr> <th>SI No </th>
                                <th>Client name </th>
                                <th>Site Name</th>
                                <th>Employee ID </th>
                                <th>Employee Name</th>
                                <th>Branch </th>
                                <th>Designation</th>
                                <th>Total Hours</th>
                                <th>Actual Working Days </th>
                                <th>Payment Mode</th>
                            </tr>
                            </thead>
                            <tbody>
                    <?php } ?>
                            <?php if($criterias != 'EmployeeDetails'){ $i = 1 ;}
                            if(count($arr_daata)>0){ ?>
                                <?php foreach($arr_daata as $val)
                               { ?>
                                    <tr>  <td><?php echo $i; ?></td>
                                            <td><?php echo $val['contacts']['company_name']; ?> </td>   
                                            <td><?php echo $val['site']['site_name'].' ('.$val['site']['site_id'].')'; ?></td>
                                            <td><?php echo $val['emp_proff']['emp_company_id']; ?></td>
                                            <td><?php echo $val['emp_details']['first_name'].' '.$val['emp_details']['last_name']; ?></td>
                                            <td><?php echo $val['branches']['branch_name']; ?></td>
                                            <td><?php echo $val['designation']['desig_name']; ?></td>
                                            <!-- <td><?php //echo round($val['0']['times']); ?></td> -->.
                                             <td><?php //edited by sinsiya on 15-09-2025
                                                         if($company_code === 'ABSG'){ echo $val['0']['times'];}else{ echo round($val['0']['times']);} ?></td>
                                            <td><?php echo $val['0']['days']; ?></td> 
                                            <td><?php if($val['site']['payment_mode']=='1'){
                         echo $payment_mode= 'Bank';
                         }
                          if($val['site']['payment_mode']=='2'){
                         echo $payment_mode= 'Cash';
                         } ?></td> 

                                    </tr>
                                    <?php  if($criterias != 'EmployeeDetails'){ $i++; } } ?>
                                <?php }else{ ?>
                                    <tr>
                                            <td colspan="7">No Records found under this Criteria</td>
                                    </tr>  
                                <?php } ?>
                    <?php if($criterias != 'EmployeeDetails'){ ?>
                            </tbody>
                        </table>
                    </fieldset>
                   </div> 
                    <?php }if($criterias == 'EmployeeDetails'){ $i++;}?>
            <?php }  if($criterias == 'EmployeeDetails'){ ?>
                       </tbody>
                    </table>
	    <?php } 
             if (empty($arr_leavepolicydetails_for_template)){?> <!-- /.box-body -->
                    <h3 style="text-align:center;color:red;">No Records found under this Criteria</h3>
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
'title'=>'Site Assignment Wise Attendance  Report : '.$month));
?>
                    <?php if($criterias == 'EmployeeDetails')
                        { 
                         if(!empty($arr_leavepolicydetails_for_template)){   ?>
                     <h4>Month : <?php echo $month; ?></h4> 
                      <table class="table table-bordered">
                            <thead>
                              <tr>
                                <th>SI No </th>
                                 <th>Client name </th>
                                 <th>Site Name</th>
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
                        <?php }  }?>
                    <?php $i=1; foreach ($arr_leavepolicydetails_for_template as $value) {
                   
                    if(empty($value['summary'])) continue;
                    $arr_daata  = $value['summary']; 
                    if($criterias != 'EmployeeDetails')
                        { ?>
                <div class="box-body">
                     <h4>Site Assignment Details of  
                        <?php
                        if($criterias != 'Units')
                        {
                        echo isset($arr_daata[0]['site']['site_name'])?$arr_daata[0]['site']['site_name'].' ('.$arr_daata[0]['site']['site_id'].')':''; 
                        }else{
                        echo isset($arr_daata[0]['branches']['branch_name'])?$arr_daata[0]['branches']['branch_name']:'';    
                        }
                        ?>
                     </h4>
                    <br>
			<table class="table table-bordered">
                            <thead>
                            <tr> <th>SI No </th>
                                <th>Client name </th>
                                <th>Site Name</th>
                                <th>Employee ID </th>
                                <th>Employee Name</th>
                                <th>Branch </th>
                                <th>Designation</th>
                                <th>Total Hours</th>
                                <th>Actual Working Days </th>
                                <th>Payment Mode</th>
                            </tr>
                            </thead>
                            <tbody>
                    <?php } ?>
                            <?php if($criterias != 'EmployeeDetails'){ $i = 1 ;}
                            if(count($arr_daata)>0){ ?>
                                <?php foreach($arr_daata as $val)
                               { ?>
                                    <tr>  <td><?php echo $i; ?></td>
                                            <td><?php echo $val['contacts']['company_name']; ?> </td>   
                                            <td><?php echo $val['site']['site_name'].' ('.$val['site']['site_id'].')'; ?></td>
                                            <td><?php echo $val['emp_proff']['emp_company_id']; ?></td>
                                            <td><?php echo $val['emp_details']['first_name'].' '.$val['emp_details']['last_name']; ?></td>
                                            <td><?php echo $val['branches']['branch_name']; ?></td>
                                            <td><?php echo $val['designation']['desig_name']; ?></td>
                                            <!-- <td><?php //echo round($val['0']['times']); ?></td> -->
                                              <td><?php //edited by sinsiya on 15-09-2025
                                                         if($company_code === 'ABSG'){ echo $val['0']['times'];}else{ echo round($val['0']['times']);}  ?></td>
                                            <td><?php echo $val['0']['days']; ?></td> 
                                            <td><?php if($val['site']['payment_mode']=='1'){
                         echo $payment_mode= 'Bank';
                         }
                          if($val['site']['payment_mode']=='2'){
                         echo $payment_mode= 'Cash';
                         } ?></td> 

                                    </tr>
                                    <?php  if($criterias != 'EmployeeDetails'){ $i++; } } ?>
                                <?php }else{ ?>
                                    <tr>
                                            <td colspan="7">No Records found under this Criteria</td>
                                    </tr>  
                                <?php } ?>
                    <?php if($criterias != 'EmployeeDetails'){ ?>
                            </tbody>
                        </table>
                   </div> 
                    <?php }if($criterias == 'EmployeeDetails'){ $i++;}?>
            <?php }  if($criterias == 'EmployeeDetails'){ ?>
                       </tbody>
                    </table>
	    <?php } 
             if (empty($arr_leavepolicydetails_for_template)){?> <!-- /.box-body -->
                    <h3 style="text-align:center;color:red;">No Records found under this Criteria</h3>
             <?php } ?>  
         
<?php } ?>