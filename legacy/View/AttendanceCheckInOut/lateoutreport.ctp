<?php if( $mode== '' ){?>
<div class="modal-body" style="overflow-y: auto;">
 <h2 style="font-weight: bold;text-align: center;"><?php echo " Late OUT- " .$from_date?>  </h2>
  <h2 style="font-weight: bold;text-align: center;font-size: 19px;"><?php echo  "(Report Run by " . $user_id . " at " . $date_time . ")"?>  </h2>
    <div class="row">
        <div class="col-md-12">

                <div class=" ">
             <?php 
                    if (count($arr_lateoutdata_for_template) == 0){
                        echo "<h3>No data available under this selected criteria.</h3>";
                    } else{?>
                <?php
              
               
                $i = 0;
                foreach ($arr_lateoutdata_for_template as $value) {

                    if (count($value) !== 0) {

                        $i += 1;
                        
                         if(!empty($value['summary']))
                        {
                        
                        ?>
                     <div class="">
                        <fieldset> 

                             <?php
                                if($cr== 'Units')
                                {
                                ?>
                               <legend><?php  ?><?php echo isset($value['summary']['0']['br']['branch_name']) ? $value['summary']['0']['br']['branch_name'] : '    (No Datas Found Under This Branch)'; ?>  </legend>
                                <?php
                                }
                                else
                                {
                              $empstatus = (isset($value['summary']['0']['ed']['status'])) && $value['summary']['0']['ed']['status'] =="2" ? '  (Resigned)':'';
                                    ?>
                              
                                    <legend> <?php echo $value['summary']['0']['lateout']['EmpName'] .$empstatus; ?> </legend>
                                <?php
                                    }
                                ?>

                        </fieldset>

                        <br>

                <table class="table table-bordered">
                            <thead>
                              <tr>
                                   <th>Sl No</th>
                                  <th>Company ID</th>
                                  <th>User ID</th>
                                  <th>Employee Name</th>
                                  <th>Joining Date</th>
                                  <th>Branch</th>
                                  
                                  <th>Department</th>
                                  <th>Designation</th>

<th>Termination Date</th>
                                  <th>Date</th>
                                  <th>Location</th>
                                  <th>Shift End Time</th>
                                  <th>OUT Time</th>
                                  <th>Late OUT Time</th>
                               
                                  
                               </tr>
                            </thead>
                            <tbody>
                                <?php $arr_data  = $value['summary'];?> 
                                <?php if(count($arr_data)>=0){ ?>
                                    <?php
                                    $i = 0;
                                    foreach($arr_data as $val){
                                       $latetime=$val['lateout']['LateOutTime'];
                                        $lateouttime = date('H:i:s', strtotime($latetime));
                                         $date=$val['lateout']['LogDate'];
                                         $dateatt = date("d-m-Y", strtotime($date));
                                         ?>

                                        <tr> 
                                            <?php $i += 1; ?>
                                            <td><?php echo $i;?></td>
                                             <td><?php echo $val['ei']['employee_id'];?></td>
                                              <td><?php echo $val['user']['user_id'];?></td>
                                             <td><?php  $empstatus = isset($val['ed']['status']) && $val['ed']['status'] == "2" ? '(Resigned)' : ''; echo $val['lateout']['EmpName'] .$empstatus;?></td>
                                              <td><?php $joiningDate= $val['ei']['joining_date']; 
                                              $join = date('d-m-Y', strtotime($joiningDate)); echo $join;?></td>
                                             
                                             <td><?php echo $val['br']['branch_name'];?></td>
                                              <td><?php echo $val['ei']['department'];?></td>
                                               <td><?php echo $val['ei']['designation'];?></td>
                                                <td><?php  $terminn = $val['te']['last_approved_working_date'];
                             if (!empty($terminn)) {
                                              $termin = date('d-m-Y', strtotime($terminn));
                                          } else {
                                                   $termin = '';
                                                                  }?></td>
                                            <td><?php echo $dateatt; ?></td>
                                            <td><?php echo $val['0']['Location']; ?></td>
                                            <td><?php echo $val['lateout']['OffDutyTime']; ?></td>
                                            <td><?php echo $val['lateout']['OutTime']; ?></td>
                                            <td><?php echo $lateouttime; ?></td>
                                         </tr>


                                    <?php } ?>
                                
                                   
                            </tbody>
                        </table>
                    
                <?php }else{
                        echo "No employees found under this Criteria"; 
                   }}}} ?> <!-- /.box-body -->
        </div>
    </div>  </div>  
<!--    <div class="row">
        <div class="form-group">
            <div class="col-md-12" align="right">
                <a href="#" class="btn btn-default" onclick="downloadReport('Attendance','pdf');" ><i class="icon-file"></i>Download As PDF</a>
                <a href="#" class="btn btn-default" onclick="downloadReport('Attendance','excel');"><i class="icon-file"></i>Download As Excel</a>
            </div>
        </div>
    </div>-->
</div>
                <?php  } } else{?>
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
<!--   <div style="font-weight: bold">Attendance Check In Out Report </div>-->
<?php
echo $this->element('reportadminheader',array(
'title'=>'Late In Duration'));
?>
                   <?php $i=0; foreach ($arr_lateindata_for_template as $value){
                   
                   $i += 1; 
//                 debug($value);
                  ?>
<div>
                <?php 
                if(isset($value['summary']['0']['latein']['EmpName']))
                {?>
                       <!--  <h4 style="font-weight: bold;margin-top: 20px;">Late In of <?php  echo isset($value['summary']['0']['latein']['EmpName']) ? $value['summary']['0']['latein']['EmpName'] : '' ; ?> <?php  echo isset($value['summary']['0']['ed']['status']) && $value['summary']['0']['ed']['status']=="2" ? '(Resigned)':'' ;?></h4> -->

                    <table class="table table-bordered">
                            <thead>
                              <tr>
                                  <th>Sl. No</th>
                                  <th style="width:100px;">Employee ID</th>
                                  <th style="width:80px;">Company ID</th>
                                  <th style="width:100px;">Employee Name</th>
                                  <th style="width:50px;">Branch</th>
                                  <th style="width:650px;">Department</th>
                                  <th style="width:50px;">Designation</th>
                                  <th>Termination Date</th>
                                  <th style="width:50px;">Late In Duration</th>
                               </tr>
                            </thead>
                            <tbody>
                                <?php $arr_data  = $value['summary'];?> 
                                <?php if(count($arr_data)>=0){ ?>
                                    <?php
                                    $i = 0;
                                    foreach($arr_data as $val){
                                        $date=$val['latein']['LogDate'];
                                        $dateatt = date("d-m-Y", strtotime($date)); ?>

                                        <tr> 
                                            <?php $i += 1; ?>
                                            <td><?php echo $i;?></td>
                                           <td style="width:80px;"><?php echo  $val['ed']['emp_id'];?></td>
                                           <td style="width:80px;"><?php echo $val['ed']['employee_id'];?></td>
                                            <td style="width:100px;"><?php echo $val['latein']['EmpName'];?></td>
                                            
                                            <td style="width:100px;"><?php echo $val['br']['branch_name'];?></td>
                                            <td style="width:50px;"><?php echo $val['ed']['department']; ?></td>
                                            <td style="width:650px;"><?php echo $val['ed']['designation']; ?></td>
                                            <td style="width:50px;"><?php echo $val['edd']['last_approved_working_date']; ?></td>
                                           
                                            <td style="width:50px;"><?php echo $val['0']['outsum']; ?></td>
                                           
                                        </tr>


                                    <?php } ?>
                                
                                   
                            </tbody>
                        </table>
                    
                <?php }else{echo "No employees found under this Criteria"; 
                } }?> <!-- /.box-body -->
               </div>
                <?php  } } ?>
