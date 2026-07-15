<?php if( $mode == '' ){ ?>
<div class="modal-body" style="overflow-y: auto;">
    <legend>Assets History Report</legend>
    <div class="row">
        <div class="col-md-12">
            <div class="box ">
                   <?php 
                   $i=0; 
                      if(count($assets_array) !== 0){
                  ?>
                <div class="box-body">
			    <table class="table table-bordered">
                            <thead>
                              <tr>
                              <!--    <th>LEAVEPOLICY_GROUP_NAME</th> -->
                                 <th>Sl No</th>
                                  <th>Asset Name</th>
                                  <th style="width:5%">Specifications</th>
                                  <th>Value</th>
                                  <th>Type</th>
                                  <th>Serial No</th>
                                  <th>Warranty</th>
                                  <th>Model</th>
                                  <th>Brand</th>
<!-- //edited by amal on 16/08/2019 description field-->
                                   <th>Description</th>
                                  <th>Status</th>
                                  <th>Asset Condition</th>
                                  <th>Current Value</th>
                                   <th>Allocated To</th>
                                  <th>Allocated Date</th>
                                  <th style="width:50px; ">Retrieved Date</th>
                              </tr>
                            </thead>
                            <tbody>
                                <?php if(count($assets_array)>=0){ ?>
                                    <?php foreach($assets_array as $val){
                                     // debug($val);
                                      $i += 1; 
                                      ?>
                                     <?php   if($val['allocate']['asset_state'] == 3){
                                      $state= "Not Working";
                                      }
                                      if($val['allocate']['asset_state']  == 1){
                                      $state= "Good";
                                      }
                                      if($val['allocate']['asset_state'] == 0){
                                      $state= "Good";
                                      }
                                      if($val['allocate']['asset_state']  == 2){
                                      $state= "Damage But Working";
                                    }?>
                                        <tr>
                                            <td><?php echo $i; ?></td>
                                            <td><?php echo $val['Assets']['name']; ?></td>
                                            <td><?php echo $val['Assets']['specifications']; ?></td>
                                            <td><?php echo $val['Assets']['value']; ?></td>
                                            <!-- //edited by megha asset type name on 17/01/2020-->
                                            <td><?php echo $val['AssetType']['asset_type_name']; ?></td>
                                            <td><?php echo $val['Assets']['serial_no']; ?></td>
                                            <td><?php echo $val['Assets']['warranty']; ?></td>
                                            <td><?php echo $val['Assets']['model']; ?></td>
                                            <td><?php echo $val['Assets']['brand']; ?></td>
                                            <td><?php echo $val['allocate']['description']; ?></td>
                                            <td><?php echo $val['allocate']['status']; ?></td>
                                            <td><?php echo $state?></td>
                                            <td><?php if(!empty($val['allocate']['damaged_amout'])){echo $val['allocate']['damaged_amout'];}else{echo $val['Assets']['value'];} ?></td>
                                            <td><?php echo $val['EmployeeDetails']['first_name'].' '.$val['EmployeeDetails']['last_name'].' '.$val['EmployeeDetails']['emp_id']; ?> <?php echo isset($val['EmployeeDetails']['status']) && $val['EmployeeDetails']['status']=="2" ? '(Resigned)':'';?></td>
                                            <td><?php echo $val['allocate']['allocated_date']; ?></td>
                                            <td><?php echo $val['allocate']['retreived_date']; ?></td>
                                        </tr>
                                      
                                    <?php } ?>
                                          
                                <?php }else{ ?>
                                        <tr>
                                            <td colspan="4">No Assets found under this data Try Another</td>
                                        </tr>  
                                <?php } ?>
                                     
                              
                            </tbody>
                        </table>
		 </div>
                
                   <?php }  ?> <!-- /.box-body -->
            </div>
        </div>
    </div>    

</div>
<?php } else{ ?>
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
        border: 2px solid #f4f4f4;
        width: 100%;
        max-width: 100%;
        margin-bottom: 20px;
        background-color: transparent;
        border-spacing: 0;
        border-collapse: collapse;
    }
    td, th {
        text-align: left;
        padding: 8px;
        font-size: 8px;
        line-height: 1.42857143;
        vertical-align: top;
        border: 1px solid #B2B2B2;
    }
</style>
<?php
echo $this->element('reportadminheader',array(
'title'=>'Asset History Report'));
?>
                   
           
		

			        <?php 
              $i =0;
                      if(count($assets_array) !== 0){
                  
                  ?>
                <div class="box-body">
                   
                  
                 
    

          <table class="table table-bordered">
                            <thead>
                              <tr>
                                
                              <!--    <th>LEAVEPOLICY_GROUP_NAME</th> -->
                                 
                                 <th>Sl. No.</th>
                                  <th>Asset Name</th>
                                  <th style="width:10%">Specifications</th>
                                  <th>Value</th>
                                  <th>Type</th>
                                  <th>Serial No</th>
                                  <th>Warranty</th>
                                  <th>Model</th>
                                  <th>Brand</th>
<!-- //edited by amal on 16/08/2019 description field-->
                                   <th>Description</th>
                                  <th>Status</th>
                                  <th style="width:30px;">Asset Condition</th>
                                  <th style="width:30px;">Current Value</th>
                                  <th style="width:30px;">Allocated To</th>
                                  
                                  <th style="width:30px;">Allocated Date</th>
                                  <th style="width:30px; ">Retrieved Date</th>
                                        
                         

                              </tr>
                            </thead>
                           
                            <tbody>
                                 
                                <?php if(count($assets_array)>=0){ ?>

                                    <?php foreach($assets_array as $val){ 
                                      $i += 1; 
                                      ?>
                                     <?php   if($val['allocate']['asset_state'] == 3){
                                       $state= "Not Working";
                                      }
                                      if($val['allocate']['asset_state']  == 1){
                                       $state= "Good";
                                      }
                                      if($val['allocate']['asset_state'] == 0){
                                       $state= "Good";
                                      }
                                      if($val['allocate']['asset_state']  == 2){
                                       $state= "Damaged But Working";
                                      }?>
                                        <tr>
                                            <td><?php echo $i; ?></td>
                                            <td><?php echo $val['Assets']['name']; ?></td>
                                            <td><?php echo $val['Assets']['specifications']; ?></td>
                                            <td><?php echo $val['Assets']['value']; ?></td>
                                            <!-- //edited by megha asset type name on 17/01/2020-->
                                            <td><?php echo $val['AssetType']['asset_type_name'];  ?></td>
                                            <td><?php echo $val['Assets']['serial_no']; ?></td>
                                            <td><?php echo $val['Assets']['warranty']; ?></td>
                                            <td><?php echo $val['Assets']['model']; ?></td>
                                            <td><?php echo $val['Assets']['brand']; ?></td>
                                            <td><?php echo $val['allocate']['description']; ?></td>
                                            <td><?php echo $val['allocate']['status']; ?></td>
                                            <td style="width:30px;"><?php echo $state?></td>
                                            <td style="width:30px;"><?php if(!empty($val['allocate']['damaged_amout'])){echo $val['allocate']['damaged_amout'];}else{echo $val['Assets']['value'];} ?></td>
                                            <td style="width:30px;"><?php echo $val['EmployeeDetails']['first_name'].' '.$val['EmployeeDetails']['last_name'].' '.$val['EmployeeDetails']['emp_id']; ?> <?php echo isset($val['EmployeeDetails']['status']) && $val['EmployeeDetails']['status']=="2" ? '(Resigned)':'';?></td>
                                            <td style="width:30px;"><?php echo $val['allocate']['allocated_date']; ?></td>
                                            <td style="width:30px;"><?php echo $val['allocate']['retreived_date']; ?></td>
                                        </tr>
                                      
                                    <?php } ?>
                                          
                                <?php }else{ ?>
                                        <tr>
                                            <td colspan="4">No Assets found under this data Try Another</td>
                                        </tr>  
                                <?php } ?>
                                     
                              
                            </tbody>
                        </table>
                    </div>
                   <?php }  }?> 