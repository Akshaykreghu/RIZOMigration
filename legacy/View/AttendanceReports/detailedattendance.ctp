<style>
    td,th,table
    {
        border-style: double;
    }
	table.table-bordered th:last-child, table.table-bordered td:last-child{
        border-right-width:1px;
    }
</style>
<?php if ($mode == '') { ?>
    <div class="modal-body" style="overflow-y: auto;">
        <legend class="text-center">Employee Attendance  Report</legend>
        <div class="row">
            <div class="col-md-12">
                 <?php
                   //edited by athira on 11-06-2025
                if (empty($arr_leavepolicydetails_for_template)) {
                    echo '<div style="font-size: 20px;text-align:left; background-color:;">
                   There is no data available under the selected criteria.</div>';
                }
                //end
                    $i = 0;
                    foreach ($arr_leavepolicydetails_for_template as $branch=> $employees) {
                        //debug(current(current($employees)));
                        $i += 1;
                        ?>
                <div class="box ">
                   
                    <h2><?php echo "Branch: ".$branch; ?></h2>
                    <?php
                        foreach ($employees as $employee =>$date)
                        {
                            //debug($date);
                        ?>
                        <div class="box-body">
                           <fieldset> 
                                <legend>Name:
                                    <?php $info =  current($date);
                                    echo $info['0']['info']['EmpName'];
                                    ?><?php echo (isset($info['0']['EmployeeDetails']['status'])) && $info['0']['EmployeeDetails']['status'] =="2" ? '  (Resigned)':'';?>
                                </legend>
                               <div class="col-md-12"> 
                               <div class="col-md-3">
                                    <label>Employee ID : &nbsp;</label><?php  echo $info['0']['info']['employee_id']; ?>
                                </div>
                               <div class="col-md-3">
                                    <label>Branch : &nbsp;</label><?php  echo $info['0']['info']['branch']; ?>
                                </div>
                               <div class="col-md-3">
                                    <label>Department : &nbsp;</label><?php  echo $info['0']['info']['department']; ?>
                                </div>
                               <div class="col-md-3">
                                    <label>Designation : &nbsp;</label><?php  echo $info['0']['info']['designation']; ?>
                                </div>
                               </div>
                                <div class="row">
                                    <div class="col-md-12">
                                            
                                    </div>
                                </div>


                            </fieldset>
                            <br>
                            <fieldset>
                                <table class="table table-bordered">
                                    <?php //debug($date); ?>
                                    
                                        <?php foreach($date as $key => $val)
                                        {
                                           //debug($val); 
                                           
                                        ?>
                                    <tr >
                                        <th rowspan="2">
                                            <?php echo date("M d",strtotime($key)); ?>
                                        </th>
                                        
                                        <?php foreach($val as $value)
                                        {
                                        ?>
                                        <td><?php echo $value['DeviceAttendance']['C1']; ?></td>
                                        <?php
                                        }
                                        ?>
                                        </tr>
                                        <tr style="border-bottom:1px solid #b8b8b8;">
                                        <?php foreach($val as $value)
                                        {
                                        ?>
                                        <td><?php echo $value['DeviceAttendance']['LOGDATE']; ?></td>
                                        <?php
                                        }
                                    
                                        ?>
                                        </tr>
                                        <?php
                                        }
                                        ?>
                                    
                                </table>

                            </fieldset>
                            <br>
                            
                        </div>
                    <?php
                        }
                        ?>
     <!-- /.box-body -->
                </div>
                <?php } ?>
            </div>
        </div>
        <div class="row">
<!--        <div class="form-group">
            <div class="col-md-12" align="right">
                <a href="#" class="btn btn-default" onclick="downloadReport('DetailedAttendance','pdf');" ><i class="icon-file"></i>Download As PDF</a>
                <a href="#" class="btn btn-default" onclick="downloadReport('DetailedAttendance','excel');"><i class="icon-file"></i>Download As Excel</a>
            </div>
        </div>-->
    </div>
        
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
            width: 33.33%;
            float: left;
        }
        #normal{
            border: 0px;
            width: 100%;
            max-width: 100%;
            margin-top: 20px;
            margin-bottom: 25px;
            background-color: transparent;
            border-spacing: 0;
            border-collapse: collapse;
        }

        #normal td,
        #normal th {
            font-size: 15px;
            text-align: left;
            padding: 10px;
            line-height: 1.42857143;
            vertical-align: top;
            border: 0px solid #B2B2B2;
        }

        #attendancetable {
            border: 1px solid #f4f4f4;
            width: 80%;
            max-width: 80%;
            margin-bottom: 20px;
            background-color: transparent;
            border-spacing: 0;
            border-collapse: collapse;
        }

        #attendancetable td,
        #attendancetable th {
            text-align: left;
            padding: 8px;
            line-height: 1.42857143;
            vertical-align: top;
            border: 1px solid #B2B2B2;
        }
    </style>

    <?php
//    echo $this->element('reportadminheader', array(
//        'title' => 'Employees Attendance  Report'));
    ?>

     <?php
         //edited by athira on 11-06-2025
     if(empty($arr_leavepolicydetails_for_template)){
         echo '<div style="font-size: 20px;text-align:left; background-color:;">
                   There is no data available under the selected criteria.</div>';
     }
     //end
    $i = 0;
    foreach ($arr_leavepolicydetails_for_template as $branch => $employees) {
        //debug(current(current($employees)));
        $i += 1;
    ?>
        <bookmark class="box-body">
            <!-- <h2><?php echo "Branch: " . $branch; ?></h2> -->
            <div class="box ">

                <h2><?php echo "Branch: " . $branch; ?></h2>
                <?php
                foreach ($employees as $employee => $date) {
                ?>
                    <table id="normal">
                        <tr>
                            <th>
                                Name :
                                <?php
                                $info =  current($date);
                                echo $info['0']['info']['EmpName'];
                                ?>
                                <?php echo (isset($info['0']['EmployeeDetails']['status'])) && $info['0']['EmployeeDetails']['status'] == "2" ? '  (Resigned)' : ''; ?>
                            </th>
                            <th>Employee ID:<?php echo $info['0']['info']['employee_id']; ?></th>
                            <th>Branch :<?php echo $info['0']['info']['branch']; ?></th>
                            <th>Department :<?php echo $info['0']['info']['department']; ?></th>
                            <th>Designation :<?php echo $info['0']['info']['designation']; ?></th>
                        </tr>
                    </table>
                    <table class="table table-bordered" id="attendancetable">
                        <?php //debug($date); 
                        ?>

                        <?php foreach ($date as $key => $val) {
                            //debug($val); 

                        ?>
                            <tr>
                                <th rowspan="2">
                                    <?php echo date("M d", strtotime($key)); ?>
                                </th>

                                <?php foreach ($val as $value) {
                                ?>
                                    <td><?php echo $value['DeviceAttendance']['C1']; ?></td>
                                <?php
                                }
                                ?>
                            </tr>
                            <tr style="border-bottom:1px solid #b8b8b8;">
                                <?php foreach ($val as $value) {
                                ?>
                                    <td><?php echo $value['DeviceAttendance']['LOGDATE']; ?></td>
                                <?php
                                }

                                ?>
                            </tr>
                        <?php
                        }
                        ?>

                    </table>
                <?php
                }
                ?>
                <!-- /.box-body -->
            </div>

        </bookmark>
    <?php } ?>
    <!-- /.box-body -->

<?php } ?>
