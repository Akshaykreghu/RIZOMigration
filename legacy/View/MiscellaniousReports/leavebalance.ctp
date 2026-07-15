<?php if ($mode == '') { ?>
    <div class="modal-body" style="overflow-y: auto;">
        <legend style="text-align: center; font-weight: bold;">Leave Balance Statement - <?php echo $from_date; ?></legend>
        <h4 align="center" style="font-weight:bold;">(<?php echo isset($user_id) ? "Report run by " . ($user_id) . " - " . $date_time : ''; ?>)</h4>
        <div class="row">
            <div class="col-md-12">
                <div class="box ">
                    <?php
                    if (count($arr_leavepolicydetails_for_template) == 0) {
                        echo "<h3>No data available under the selected criteria</h3>";
                    }?>

                    <?php
                   
                  
                    foreach ($arr_leavepolicydetails_for_template as $value) {
                        
                        //$i += 1;
                        if (count($value['summary']) > 0) {
                            ?>
                            <div class="box-body" style="overflow-x: scroll;">
                                <fieldset>
                                    <legend style="font-weight: bold ; ">  
                                        <?php
                                        
                                        if ($cr == 'EmployeeDetails') {
                                            echo isset($value['summary'][0][0][0]['EmpName']) ? $value['summary'][0][0]['0']['EmpName'] : '';
                                            echo isset($value['summary'][0][0]['ed']['status']) && $value['summary'][0][0]['ed']['status'] == "2" ? '(Resigned)' : '';
                                        } ?>  </legend>
                                   
                                </fieldset>
                                <br>
                                <fieldset>
                                    <table class="table table-bordered"  >
                                        <thead>
                                            <tr>
                                                <th>Sl No </th>
                                                <th>Employee ID</th>
                                                <!-- edited by athira on 07-07-2025 -->
                                                <?php if ($company_code =='DEMO' || $company_code=='GLET' || $company_code=='SRTS') {?>
                                                 <th>Employee ID (US Format)</th>
                                                 <?php } ?>
                                                 <!-- end -->
                                                <th>User ID</th>
                                                <th>Employee Name</th>
                                                <!-- edited by athira on 07-07-2025 --> 
                                                <?php if ($company_code =='DEMO' || $company_code=='GLET' || $company_code=='SRTS') {?>
                                                 <th>Employee Name (US Format)</th> 
                                                 <?php } ?>
                                                   <!-- end -->   
                                                <th>Date Of Joining</th>
                                                <th>Branch</th>
                                                <th>Department</th>
                                                <th>Designation</th>
                                                <th>Termination Date</th>
                                                <?php foreach ($all_leaveheads as $heads) { 
                                                    $head = isset($heads['salary_head_items']['item'])?$heads['salary_head_items']['item']:'';
                                                 ?>
                                                <th><?php echo $head; ?></th>
                                                <?php } ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $arr_data = $value['summary']; 
                                            ?>
                                            <?php if (count($arr_data) > 0) { ?>
                                                <?php $i = 1;  
                                               $val = $arr_data[0][0];?>
                                                
                        <tr>                           
                        <td><?php echo $i++; ?></td>
                        <td><?php echo $val['info']['employee_id']; ?></td>  
                        <!-- edited by athira on 07-07-2025 -->
                        <?php if ($company_code =='DEMO' || $company_code=='GLET' || $company_code=='SRTS') {?>
                              <td><?php echo $val['info']['emp_us_id']; ?></td>
                            <?php } ?>
                        <!-- end -->
                        <td><?php echo isset($val['user_credentials']['user_id']) ?  $val['user_credentials']['user_id'] : '';?></td>
                        <td><?php echo $val['0']['emp_name']; ?><?php echo isset($val['ed']['status']) && $val['ed']['status'] == "2" ? '(Resigned)' : ''; ?></td> 
                        <!-- edited by athira on 07-07-2025 -->
                         <?php if ($company_code =='DEMO' || $company_code=='GLET' || $company_code=='SRTS') {?>
                            <td><?php echo $val['info']['EmpUSName']; ?></td>
                            
                            <?php } ?>
                            <!-- end -->
                        <?php $joiningDate= $val['info']['joining_date']; 
                        $join = date('d-m-Y', strtotime($joiningDate));

$terminn = $val['termination']['last_approved_working_date'];
if (!empty($terminn)) {
    $termin = date('d-m-Y', strtotime($terminn));
} else {
    $termin = '';
}?>
                        <td><?php echo $join; ?></td>
                        <td><?php echo $val['info']['branch']; ?></td>
                        <td><?php echo $val['info']['department']; ?></td>
                        <td><?php echo $val['info']['designation']; ?></td>
                        <td><?php echo $termin;?></td>   
                      
                        <?php foreach ($all_leaveheads as $heads) { 
                            $head_pkey = isset($heads['salary_head_items']['salary_head_item_pkey'])?$heads['salary_head_items']['salary_head_item_pkey']:'';
                             $leave_bal = 0;
                            foreach ($arr_data as $val) {
                               $key = $val[0]['LeaveType']['salary_head_item_pkey'];
                               if($head_pkey == $key){ 
                                   $leave_bal = $val[0]['0']['monthlybalance'];
                               }
                            }
                        ?>
                        <td><?php echo $leave_bal; ?></td>
                        <?php } ?>
                        </tr>
                                                    
                                            <?php } else { ?>
                                                <tr>
                                                    <td colspan="10">No Data found found under this Criteria </td>
                                                </tr>  
                                            <?php } ?>
                                        </tbody>
                                    </table>

                                </fieldset>
                                <br>
                            </div>
                            <?php
                        }
                    }
                    ?> <!-- /.box-body -->
                </div>
            </div>
        </div>    
    </div>

<?php } 
?>