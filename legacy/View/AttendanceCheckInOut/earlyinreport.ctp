<?php if( $mode== '' ){?>
<div class="modal-body" style="overflow-y: auto;">
 <h2 style="font-weight: bold;text-align: center;"><?php echo "Early IN- " .$mname."  "  .$year?>  </h2>
  <h2 style="font-weight: bold;text-align: center;font-size: 19px;"><?php echo  "(Report Run by " . $user_id . " at " . $date_time . ")"?>  </h2>
    <div class="row">
        <div class="col-md-12">
             <div class="box ">
             <?php
                     if (empty($arr_earlyindata_for_template[0]['summary'])){
                        echo "<h3>No Data Available With The Selected Criteria</h3>";
                    } else{?>
                
                   <?php $i=0; foreach ($arr_earlyindata_for_template as $value){
                   // debug($value);
                   $i += 1; 
                  ?>
                <?php 
                if(isset($value['summary']['0']['earlyin']['EmpName']))
                {?>
                    
                        <!-- <h4 style="font-weight: bold">Early In of <?php  echo isset($value['summary']['0']['earlyin']['EmpName']) ? $value['summary']['0']['earlyin']['EmpName'] : '' ; ?> <?php  echo isset($value['summary']['0']['ed']['status']) && $value['summary']['0']['ed']['status']=="2" ? '(Resigned)':'' ;?></h4> -->

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
                              
                <legend> <?php $name= $value['summary']['0']['earlyin']['EmpName'] .$empstatus; echo $name; ?>  </legend>
                                <?php
                                    }
                                ?>

                        </fieldset>

			    <table class="table table-bordered">
                            <thead>
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
                                  <th>Date</th>
                                  <th>Location</th>
                                  <th>Shift Start Time</th>
                                  <th> IN Time</th>
                                  <th>Early IN Time</th>
                               </tr>
                            </thead>
                            <tbody>
                                <?php $arr_data  = $value['summary'];?> 
                                <?php if(count($arr_data)>=0){ ?>
                                    <?php
                                    $i = 0;
                                    foreach($arr_data as $val){
                                        $date=$val['earlyin']['LogDate'];
                                        $dateatt = date("d-m-Y", strtotime($date));
                                        ?>

                                        <tr> 
                                            <?php $i += 1; ?>
                                             <td><?php echo $i;?></td>
                                              <td><?php echo $val['ei']['emp_id'];?></td>
                                              <td><?php echo $val['ei']['employee_id'];?></td>
                                             <td><?php
                                              $empstatus = isset($val['ed']['status']) && $val['ed']['status'] == "2" ? '(Resigned)' : ''; echo $val['earlyin']['EmpName'].$empstatus;?></td>
                                              <td><?php echo $val['ei']['joining_date'];?></td>
                                             
                                             <td><?php echo $val['br']['branch_name'];?></td>
                                              <td><?php echo $val['ei']['department'];?></td>
                                               <td><?php echo $val['ei']['designation'];?></td>
                                                <td><?php echo $val['te']['last_approved_working_date'];?></td>
                                            <td><?php echo $dateatt; ?></td>
                                            <td><?php echo $val['0']['Location']; ?></td>
                                            <td><?php echo $val['earlyin']['SharpInTime']; ?></td>
                                            <td><?php echo $val['earlyin']['InTime']; ?></td>
                                            <td><?php echo $val['earlyin']['EarlyInTime']; ?></td>
                                         </tr>


                                    <?php } ?>
                                
                                   
                            </tbody>
                        </table>
                    
                <?php }else{
                        echo "No employees found under this Criteria"; 
                   }}}} ?> <!-- /.box-body -->
        </div>
    </div>    
<!--    <div class="row">
        <div class="form-group">
            <div class="col-md-12" align="right">
                <a href="#" class="btn btn-default" onclick="downloadReport('Attendance','pdf');" ><i class="icon-file"></i>Download As PDF</a>
                <a href="#" class="btn btn-default" onclick="downloadReport('Attendance','excel');"><i class="icon-file"></i>Download As Excel</a>
            </div>
        </div>
    </div>-->
</div>
                <?php   } else{?>
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
'title'=>'Employee Early In'));
?>
                   <?php $i=0; foreach ($arr_earlyindata_for_template as $value){
                   
                   $i += 1; 
//                 debug($value);
                  ?>
<div>
                <?php 
                if(isset($value['summary']['0']['earlyin']['EmpName']))
                {?>
                        <h4 style="font-weight: bold;margin-top: 20px;">Early In of <?php  echo isset($value['summary']['0']['earlyin']['EmpName']) ? $value['summary']['0']['earlyin']['EmpName'] : '' ; ?> <?php  echo isset($value['summary']['0']['ed']['status']) && $value['summary']['0']['ed']['status']=="2" ? '(Resigned)':'' ;?></h4>

                    <table class="table table-bordered">
                            <thead>
                              <tr>
                                  <th>Sl. No</th>
                                  <th style="width:100px;">Employee Name</th>
                                  <th style="width:80px;">User ID</th>
                                  <th style="width:100px;">Branch</th>
                                  <th style="width:50px;">Date</th>
                                  <th style="width:650px;">Location</th>
                                  <th style="width:50px;">Shift Start Time</th>
                                  <th>Employee In Time</th>
                                  <th style="width:50px;">Early In Time</th>
                               </tr>
                            </thead>
                            <tbody>
                                <?php $arr_data  = $value['summary'];?> 
                                <?php if(count($arr_data)>=0){ ?>
                                    <?php
                                    $i = 0;
                                    foreach($arr_data as $val){
                                        $date=$val['earlyin']['LogDate'];
                                        $dateatt = date("d-m-Y", strtotime($date)); ?>

                                        <tr> 
                                            <?php $i += 1; ?>
                                            <td><?php echo $i;?></td>
                                            <td style="width:100px;"><?php echo $val['earlyin']['EmpName'];?></td>
                                            <td style="width:80px;"><?php echo $val['uc']['user_id'];?></td>
                                            <td style="width:100px;"><?php echo $val['br']['branch_name'];?></td>
                                            <td style="width:50px;"><?php echo $dateatt; ?></td>
                                            <td style="width:650px;"><?php echo $val['0']['Location']; ?></td>
                                            <td style="width:50px;"><?php echo $val['earlyin']['SharpInTime']; ?></td>
                                            <td><?php echo $val['earlyin']['InTime']; ?></td>
                                            <td style="width:50px;"><?php echo $val['earlyin']['EarlyInTime']; ?></td>
                                           
                                        </tr>


                                    <?php } ?>
                                
                                   
                            </tbody>
                        </table>
                    
                <?php }else{echo "No employees found under this Criteria"; 
                } }?> <!-- /.box-body -->
               </div>
                <?php  } } ?>
         