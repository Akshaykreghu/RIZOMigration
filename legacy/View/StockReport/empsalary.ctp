<style>
   
    .table , td, th,tr {
        border-style: solid;
        border-color: #d4d4de;
        
        
    }
  
</style>

<?php // debug($arr_salary_for_template); ?>
<?php if( $mode == '' ){ ?>
<div class="modal-body" style="overflow-y:initial; padding-left:3%; padding-right:3%; padding-bottom:3%;" >
     <h3 align="center" style="font-weight:bold; font-size: 30px;">Stock Summary Report :<?php echo $from;?></h3>
        <h4 align="center" style="font-weight:bold;">(<?php echo isset($user_id) ? "Report run by " . ($user_id) . " - " . $date_time : ''; ?>)</h4>		
    <div class="row">
        <div class="col-md-12">
            
				<?php
                $i = 0;
                if (count($arr_stocksummary_for_template) == 0) {
                    echo "<h2>No Data Available With The Selected Criteria</h2> ";
                } else {
                    foreach ($arr_stocksummary_for_template as $value) {
                      if(count($value) !== 0){
                   $i += 1; 
                  ?>
               
            <fieldset> 
                

											  
                            <legend> <?php echo isset($value['0']['a']['store_location']) ? "Store - ".$value['0']['a']['store_location'] : '';
            echo ' ';
             ?> 
                            </legend>

                        </fieldset>
                   
                    <br>
                    <fieldset>
		

			    <table class="table">
                            <thead>
                              <tr>
                                
                              <!--    <th>LEAVEPOLICY_GROUP_NAME</th> -->
                                 
                                  <th>Sl No</th>
                                  <th>ITEM CODE </th>
                                  <th>ITEM NAME</th>
                                  <th>STORE</th>
                                  <th>QUANTITY</th>
                                  <th>RATE</th>
                                  <th>AMOUNT (as on <?php echo $from; ?>)</th>
                                
                              </tr>
                            </thead>
                           
                            <tbody>
                                 <?php $arr_data  = $value;  ?>
											 
                                <?php if(count($arr_data)>=0){ $i = 0; $sum = 0; ?>
                                    <?php foreach($arr_data as $val){ 
					$qty = $val['0']['qtyuptodate'];  if($qty>0){
									?>
																																				 
                                <tr>
                                    <?php $i = $i+1; $sum +=  $val['0']['closingvalue']; ?>
																
																					  
													  
                                    <td><?php echo $i; ?></td>
                                    <td><?php echo $val['a']['item_code']; ?></td>
                                    <td><?php echo $val['a']['item_desc']; ?></td>
                                    <td><?php echo $val['a']['store_location']; ?></td>
                                    <td><?php echo $val['0']['qtyuptodate']; ?></td>
                                    <td><?php echo $val['b']['po_rate']; ?></td>
                                    <td><?php echo $val['0']['closingvalue']; ?></td>
                                    
                                </tr>





                                    <?php   } 
									} ?>
                                          <tr>
                                              <th colspan="6">Grand Total</th><th><?php echo $sum ; ?></th>
                                        </tr>
                                <?php }else{ ?>
                                        <tr>
                                            <td colspan="6">No Stocks found under this data</td>
                                        </tr>  
                                <?php } ?>
                                     
                              
                            </tbody>
                        </table>
		
                    </fieldset>
                    <br>
                              
                
								 
						 
					 
				 
                   <?php } } } ?> <!-- /.box-body -->
            
        </div>
    </div>  
    <!--div class="modal-footer">
        <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel </button>  
    </div-->
  <!---<div class="row">
        <div class="form-group">
            <div class="col-md-12" align="right">
                <a href="#" class="btn btn-default" onclick="downloadReport('salarystructure','pdf');" ><i class="icon-file"></i>Download As PDF</a>
                <a href="#" class="btn btn-default" onclick="downloadReport('salarystructure','excel');"><i class="icon-file"></i>Download As Excel</a>
            </div>
        </div>
    </div> -->
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
        line-height: 1.42857143;
        vertical-align: top;
        border: 1px solid #B2B2B2;
    }
</style>

<?php
echo $this->element('reportadminheader',array(
'title'=>'Stock Summary Report'));
						
?>
    <h3 style="text-align: center;"><?php echo isset($user_id) ? "Report run by " . ($user_id) . " - " . $date_time : ''; ?></h3>
    <?php
    $i = 0;
    if (count($arr_stocksummary_for_template) == 0) {
        echo "<h2>No Data Available With The Selected Criteria</h2> ";
    } else {
        foreach ($arr_stocksummary_for_template as $value) {
            if (count($value) !== 0) {
                $i += 1;
                ?>   
                                <h3 style="text-align: left;padding-bottom: 0px;padding-top: 10px;">Store  : <?php echo isset($value['0']['a']['store_location']) ?$value['0']['a']['store_location'] : ''; ?>  
                                               </h3>

                       
                        <hr>
                                
                    <br>
           
		

			    <table class="table" align="center">
                            <thead>
                              <tr>
                                
                              <!--    <th>LEAVEPOLICY_GROUP_NAME</th> -->
                                 
                                  <th style="width: 13%;">Sl No</th>
	                          <th style="width: 13%;">ITEM CODE </th>
                                  <th style="width: 13%;">ITEM NAME</th>
                                  <th style="width: 13%;">STORE</th>
                                  <th style="width: 13%;">QUANTITY</th>
                                  <th style="width: 13%;">RATE</th>
                                  <th style="width: 13%;">AMOUNT (as on <?php echo $from; ?>)</th>
                                  
                                
                              </tr>
                            </thead>
                           
                            <tbody>
                                 <?php $arr_data  = $value;  ?>
							 
                                <?php if(count($arr_data)>=0){ $i = 0; $sum = 0; ?>
                                    <?php foreach($arr_data as $val){ 
					$qty = $val['0']['qtyuptodate'];  if($qty>0){
									?>
																	  
                                <tr>
                                    <?php $i = $i+1; $sum +=  $val['0']['closingvalue']; ?>
												
																	  
									  
                                    <td><?php echo $i; ?></td>
                                    <td><?php echo $val['a']['item_code']; ?></td>
                                    <td><?php echo $val['a']['item_desc']; ?></td>
                                    <td><?php echo $val['a']['store_location']; ?></td>
                                    <td><?php echo $val['0']['qtyuptodate']; ?></td>
                                    <td><?php echo $val['b']['po_rate']; ?></td>
                                    <td><?php echo $val['0']['closingvalue']; ?></td>
                                </tr>





                                   <?php   } 
								   } ?>
                                   <tr>
                                              <th colspan="6">Grand Total</th><th><?php echo $sum ; ?></th>
                                        </tr>
                                <?php }else{ ?>
                                        <tr>
                                            <td colspan="6">No Stocks found under this data</td>
                                        </tr>  
                                <?php } ?>
                                     
                              
                            </tbody>
                        </table>
		
              
                    <br>
                              
                
				 
			 
		 
	 
                   <?php } } } ?> <!-- /.box-body -->
          
<?php } ?>