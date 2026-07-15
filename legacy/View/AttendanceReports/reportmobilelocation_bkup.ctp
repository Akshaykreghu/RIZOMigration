<?php if( $mode == '' ){ ?>
<div class="modal-body" style="overflow-y: auto;">
    <legend style="text-align:center; ">Employee Location Report</legend>
    <div class="row">
        <div class="col-md-12">
            <div class="box ">
                  
                
<h4>For The Month : <?php echo $date;?></h4>		

			    <table class="table table-bordered">
                            <thead>
                              <tr>
                                   <th>Date</th>
                                  <th>Employee Name</th>
                                       <th>Employee ID</th>
                                      <th>Designation</th>
                                  <th>Departments</th>
                                     <th>Branch</th>
                                   <th>Action</th>
                                  <th>Location</th>
                                  
                                       </tr>             
                              <tbody>
                                <?php 
                                $empnme="";
                                foreach ($arr_mob_location as $value) {
                                
                                                                                                
                 
                  ?>
                                        <tr> <td><?php echo $value['a']['datetimecheck']; ?></td>
                                              <td><?php if($empnme!=$value['a']['empname']) { echo $value['a']['empname'];} ?></td>
                                             <td><?php if($empnme!=$value['a']['empname']) { echo $value['a']['userid'];} ?></td>
                                              <td><?php if($empnme!=$value['ei']['designation']) { echo $value['ei']['designation']; }?></td>
                                               <td><?php if($empnme!=$value['ei']['department']) { echo $value['ei']['department']; } ?></td>
                                                 <td><?php if($empnme!=$value['ei']['branch']) { echo $value['ei']['branch']; }?></td>
                                           <td><?php echo $value['a']['in_out']; ?></td>
                                         <td><?php echo $value['a']['location']; ?></td>
                                         
                                                                 </tr>
                               <?php  
                               $empnme=$value['a']['empname'];
                               } ?> 
                            </tbody>
                        </table>
		
                                     <br>
                               </div>
                   <!-- /.box-body -->
            </div>
        </div>
    </div>    
    <div class="row">
        <div class="form-group">
            <div class="col-md-12" align="right">
                <!--a href="#" class="btn btn-default" onclick="downloadReport('MobilelocationRep','pdf');" ><i class="icon-file"></i>Download As PDF</a-->
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
'title'=>'Employee Location Report'));
?>
<h4>For The Month : <?php echo $date;?></h4>
  		    <table align="center">
                              <thead>
                              <tr>
                                  <th style="width:10%">Date</th>
                                    <th style="width:15%">Employee Name</th>
                                       <th style="width:10%">Employee ID</th>
                                      <th style="width:10%">Designation</th>
                                  <th style="width:10%">Departments</th>
                                     <th style="width:10%">Branch</th>
                              <!--    <th>LEAVEPOLICY_GROUP_NAME</th> -->
                                 
                                    <th style="width:10%">Action</th>
                                    <th style="width:20%">Location</th>
                                   
                                       </tr>               </thead>    
                        <tbody>
                                <?php 
                                $empnme="";
                                foreach ($arr_mob_location as $value) {
                                
                                                                                                
                 
                  ?>
                                       
                                        <tr> <td><?php echo $value['a']['datetimecheck']; ?></td>
                                              <td><?php if($empnme!=$value['a']['empname']) { echo $value['a']['empname'];} ?></td>
                                             <td><?php if($empnme!=$value['a']['empname']) { echo $value['a']['userid'];} ?></td>
                                              <td><?php if($empnme!=$value['ei']['designation']) { echo $value['ei']['designation']; }?></td>
                                               <td><?php if($empnme!=$value['ei']['department']) { echo $value['ei']['department']; } ?></td>
                                                 <td><?php if($empnme!=$value['ei']['branch']) { echo $value['ei']['branch']; }?></td>
                                           <td><?php echo $value['a']['in_out']; ?></td>
                                         <td><?php echo $value['a']['location']; ?></td>
                                         
                                                                 </tr>
                               <?php  
                               $empnme=$value['a']['empname'];
                               } ?> 
                            </tbody>
                        </table>

<?php } ?>