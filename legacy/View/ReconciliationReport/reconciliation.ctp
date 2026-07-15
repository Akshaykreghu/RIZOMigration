<style>
    .table , td, th,tr {
        border-style: solid;
        border-color: #d4d4de;    
    }
</style>
<?php // debug($arr_salary_for_esi); ?>
<?php if( $mode == '' ){ ?>
 <?php $i = 0;
if(count($arr_salary_for_template) == 0){ 
  echo '<div style="font-size: 25px;text-align:center; background-color:#F7D3D2;">
        There is no data available</div>';
        die();
} 
?>
<div class="modal-body" style="overflow-y:initial; padding-left:3%; padding-right:3%; padding-bottom:3%;" >
    <h3 align="center" >Reconciliation Report</h3>
    <div class="row">
        <div class="col-md-12">
                   <?php $i=0; foreach ($arr_salary_for_template as $value) {
                     if(count($value) !== 0){
                     $i += 1; 
                   ?>
                             <?php
                       foreach ($value as $key => $values) { 
                           $brch =isset($value['summary']['0']['br']['branch_code'])?$value['summary']['0']['br']['branch_code'] : '' ;
                           $data= isset($arr_salary_for_bank_data[$brch])?$arr_salary_for_bank_data[$brch]:'';
                           $esidata= isset($arr_salary_for_esi[$brch])?$arr_salary_for_esi[$brch]:'';
                           $bank= isset($arr_salary_for_bank[$brch])?$arr_salary_for_bank[$brch]:'';
                           $loan= isset($arr_salary_for_loan[$brch])?$arr_salary_for_loan[$brch]:'';
                           $advance= isset($arr_salary_for_advance[$brch])?$arr_salary_for_advance[$brch]:'';
                           $data_hold= isset($arr_salary_for_bank_hold[$brch])?$arr_salary_for_bank_hold[$brch]:'';           ?>
                        
                            <div class="col-md-6">Period :  <b><?php echo $period; ?></b></div>
                            <div class="col-md-6">Branch : <b><?php echo isset($value['summary']['0']['br']['branch_name'])?$value['summary']['0']['br']['branch_name'] : '' ; ?></b>  </div>              
                     
          <table class="table">
                            <thead>
                              <tr> 
                                  <th>Heads</th>
                                  <?php foreach ($arr_div as $div) { ?>
                                  <th><?php echo $div['division']['div_name']; ?></th>
                                  <?php } ?> 
                                  <th>Count</th>
                                  <th>Total</th>
                              </tr>
                            </thead>
                           
                            <tbody>
                                <?php $arr_items=array();
                                foreach($additions as $val){ 
                                    $count = 0; $subtotals = 0; ?>
                                <tr>
                                <td><?php echo $val['0']['item']; ?></td>
                                <?php foreach ($arr_div as $div) { $div1 =$div['division']['div_name'];
                                        $arr_items[$div1][] = 0;?>
                                <td>
                                    <?php foreach($values as $row){  ?>
                                        <?php if($div['division']['div_name'] == $row['divn']['div_name'] && $val['salhead']['salary_head_item_pkey'] == $row['ectc']['salary_head_item_fkey']) {
                                    echo round($row['0']['totalamount']);
                                    $count += $row['0']['vcount'];
                                    $subtotals += $row['0']['totalamount'];
                                    $arr_items[$div1][] = $row['0']['totalamount'];
                                        } } ?></td>
                                        
                                <?php } ?>
                                <td><?php echo round($count); ?></td>
                                <td><?php echo round($subtotals); ?></td>
                                </tr>
                                <?php } ?> 
                                <tr>
                                              <th>GROSS SALARY</th>
                                              <?php $grdtotals = 0;
                                              foreach ($arr_div as $div) {  $grtotals = 0;?>
                                              <th><?php foreach($values as $row){ ?>
                                        <?php if($div['division']['div_name'] == $row['divn']['div_name'] && $row['ectc']['head_operator'] == 'Addition' 
                                                && $row['0']['salary_head_item_desc'] !='ESI' && $row['0']['salary_head_item_desc'] !='PF' && $row['0']['salary_head_item_desc'] !='LWF'
                                                && $row['0']['salary_head_item_desc'] !='WWF') {
                                              $grtotals += round($row['0']['totalamount']);
                                              $grdtotals += round($row['0']['totalamount']);
                                        }} echo round($grtotals);?>
                                              </th>
                                              <?php } ?> 
                                              <th></th>
                                              <th><?php echo round($grdtotals); ?></th>
                                        </tr>
                                        <?php $cnt=count($arr_div) + 3; ?>
                                                
                                        <tr><th colspan="<?php echo $cnt;?>">DEDUCTIONS</th></tr>
                                        <?php 
                                foreach($deductions as $val){ 
                                    $count1 = 0; $subtotals1 = 0; ?>
                                <tr>
                                <td><?php echo $val['0']['item']; ?></td>
                                <?php foreach ($arr_div as $div) { $div1 =$div['division']['div_name'];?>
                                <td>
                                    <?php foreach($values as $row){ ?>
                                        <?php if($div['division']['div_name'] == $row['divn']['div_name'] && $val['0']['salary_head_item_pkey'] == $row['ectc']['salary_head_item_fkey']) {
                                            if($row['0']['salary_head_item_desc'] =='LWF'){
                                            echo abs($row['0']['totalamount']);
                                            }else{
                                            echo abs(round($row['0']['totalamount']));    
                                            }
                                    $count1 += $row['0']['vcount'];
                                    $subtotals1 += abs($row['0']['totalamount']);
                                    $arr_items[$div1][] = $row['0']['totalamount'];
                                        }}?></td>
                                        
                                <?php } ?>
                                <td><?php echo round($count1); ?></td>
                                <td><?php echo round($subtotals1); ?></td>
                                </tr>
                                <?php } ?> 
                                <tr>
                                              <th>TOTAL DEDUCTIONS</th>
                                              <?php $grdtotals1 = 0;
                                              foreach ($arr_div as $div) {  $grtotals1 = 0;?>
                                              <th><?php foreach($values as $row){ ?>
                                        <?php if($div['division']['div_name'] == $row['divn']['div_name'] && $row['ectc']['head_operator'] == 'Deduction') {
                                              $grtotals1 += abs($row['0']['totalamount']);
                                              $grdtotals1 += abs($row['0']['totalamount']);
                                        }} echo round($grtotals1);?>
                                              </th>
                                              <?php } ?> 
                                              <th></th>
                                              <th><?php echo round($grdtotals1); ?></th>
                                        </tr>
                                        <?php } ?>
                                        <tr><th colspan="<?php echo $cnt;?>">LOAN & ADVANCE</th></tr>
                                           
                                        <tr>
                                        <td><?php echo "Salary Advance"; ?></td>  
                                              <?php  $adsum = 0; $adv = 0; $advcnt = 0;
                                         foreach ($arr_div as $div) { $adv = 0; $div1 =$div['division']['div_name'];
                                         if(!empty($advance)){
                                         foreach ($advance['0']['advance'] as $ad) {
                                            if($div['division']['div_name'] == $ad['divn']['div_name']) { 
                                                $adv = abs(round($ad['0']['totalamount'])); 
                                                $adsum += $adv ; 
                                                $advcnt += $ad['0']['vcount'];
                                                $arr_items[$div1][] = round($ad['0']['totalamount']);
                                         } } } ?>
                                       <td><?php echo $adv; ?></td>
                                        <?php } ?>
                                        <td><?php echo $advcnt; ?></td>  
                                        <td><?php echo round($adsum); ?></td> 
                                             
                                        </tr>
                                        <tr>
                                              <td><?php echo "Employee Loan"; ?></td>  
                                              <?php 
                                              $lnsum = 0; $loancnt = 0;
                                         foreach ($arr_div as $div) {  $ln = 0; 
                                         $div1 =$div['division']['div_name'];
                                         if(!empty($loan)){
                                         foreach ($loan['0']['loan'] as $lon) { 
                                            if($div['division']['div_name'] == $lon['divn']['div_name'] ) {  
                                                $ln = abs(round($lon['0']['totalamount']));
                                         $lnsum += $ln; 
                                         $loancnt += $lon['0']['vcount'];
                                         $arr_items[$div1][] = round($lon['0']['totalamount']);
                                         } } } ?>
                                              <td><?php echo $ln; ?></td>
                                         <?php  }?>
                                        <td><?php  echo $loancnt; ?></td>  
                                        <td><?php echo round($lnsum); ?></td> 
                                        </tr>
                                        <tr><th>Total </th>
                                             <?php $grndsum1 = 0;
                                            foreach ($arr_div as $div) { $grndsum = 0;
                                            if(!empty($loan)){
                                            foreach ($loan['0']['loan'] as $lon) { 
                                            if($div['division']['div_name'] == $lon['divn']['div_name'] ) {  
                                                $grndsum += abs($lon['0']['totalamount']);
                                                $grndsum1 += abs($lon['0']['totalamount']);
                                            }} }
                                            if(!empty($advance)){
                                            foreach ($advance['0']['advance'] as $ad) {
                                            if($div['division']['div_name'] == $ad['divn']['div_name']) { 
                                                $grndsum += abs($ad['0']['totalamount']);
                                                $grndsum1 += abs($ad['0']['totalamount']);
                                            }}}
                                                ?>
                                            <th><?php echo abs(round($grndsum));?></th><?php } ?>
                                            <th></th>
                                            <th><?php echo round($lnsum+$adsum);?></th></tr>
                                        <tr><th>NET SALARY</th>
                                        <?php $grand = 0; 
                                            foreach ($arr_div as $div) { 
                                            $div1 =$div['division']['div_name'];
                                            $subtot = array_sum($arr_items[$div1]); 
                                            $grand += $subtot;?>
                                            <th><?php echo round($subtot); ?></th><?php } ?>
                                            <th></th>
                                            <th><?php echo round($grand); ?></th></tr>
                                        <tr><th colspan="<?php echo $cnt;?>">BANK DETAILS</th></tr>
                                        
                                        <?php $grsum = 0; $arr_bank = array(); if(!empty($bank)){ 
                                        foreach ($bank['0']['bank'] as $bk) { $sum = 0; 
                                            if($bk['ed']['bank_name'] !== ''){ 
                                                $sum += abs(round($bk['0']['totalamount']));
                                              $grsum += abs(round($bk['0']['totalamount']));
                                             ?>
                                        <tr>
                                              <td><?php echo $bk['ed']['bank_name']; ?></td>  
                                        <?php foreach ($arr_div as $div) { $div1 =$div['division']['div_name'];
                                        $arr_bank[$div1][] = 0;
                                            if($div['division']['div_name'] == $bk['divn']['div_name']) { 
                                                $arr_bank[$div1][] = abs($bk['0']['totalamount']); ?>
                                              <td><?php echo abs(round($bk['0']['totalamount'])); ?></td>
                                        <?php } else{ ?>
                                              <td>0</td>
                                        <?php  } }?>
                                        <td><?php echo $bk['0']['vcount']; ?></td>  
                                        <td><?php echo round($sum); ?></td> 
                                        </tr>
                                        <?php } } } ?>
                                        <?php  if(!empty($data)){ ?>
                                        <tr>
                                        <td><?php echo "CASH"; ?></td> 
                                        <?php $amttot = 0; $cn=0; 
                                        foreach ($arr_div as $div) {  $amt = 0; $arr_bank[$div1][] = 0;
                                        if(!empty($data)){
                                            foreach ($data['0']['bank'] as $bk) {  ?>
                                        <?php 
                                        $div1 =$div['division']['div_name'];
                                        
                                            if($div['division']['div_name'] == $bk['divn']['div_name']) { 
                                                $arr_bank[$div1][] = abs($bk['0']['totalamount']); 
                                                $grsum += abs(round($bk['0']['totalamount']));
                                                $amttot += abs(round($bk['0']['totalamount']));
                                            $amt = isset($bk['0']['totalamount'])?$bk['0']['totalamount']:'';
                                            $cn +=$bk['0']['vcount'];
                                        ?><?php } } }?>
                                        <td><?php echo abs(round($amt)); ?></td> <?php } ?>
                                        <td><?php echo $cn; ?></td>  
                                        <td><?php echo $amttot; ?></td> 
                                        </tr>
                                        <?php  } ?> 
                                        <tr><th>Salary Hold</th>
                                            <?php  $cntt=0; $holdsum = 0; foreach ($arr_div as $div) { $hold = 0;
                                            if(!empty($data_hold)){
                                                foreach ($data_hold['0']['bank'] as $bk) {  
                                           
                                            if($div['division']['div_name'] == $bk['divn']['div_name'] && $bk['payroll_master']['action'] == 'Processed') {
                                                $hold += abs(round($bk['0']['totalamount'])); 
                                                $holdsum += abs(round($bk['0']['totalamount'])); 
                                                $cntt +=$bk['0']['vcount'];
                                            } }}?>
                                                <th><?php echo round($hold); ?> </th>
                                            <?php } ?> 
                                            
                                            
                                            <td><?php echo $cntt; ?></td> 
                                            <th><?php echo round($holdsum); ?> </th>
                                          
                                        </tr>
                                        <tr><th>GRAND TOTAL</th>
                                            <?php $grand = 0; 
                                            foreach ($arr_div as $div) { 
                                            $div1 =$div['division']['div_name'];
                                            $subtot = isset($arr_bank[$div1])?array_sum($arr_bank[$div1]):''; 
                                            $grand += $subtot;?>
                                            <th><?php echo round($subtot); ?></th><?php } ?>
                                            <th></th>
                                            <th><?php echo round($grand); ?></th></tr>
                                        <tr><th colspan="<?php echo $cnt;?>">EPF AND ESI Summary</th></tr>
                                        
                                        
                                        <tr>
                                            <td>ESI</td>
                                            <?php $cnt=0; $totalsum=0; $arr_total = array();
                                            foreach ($arr_div as $div) { $esisum = 0; $div1 =$div['division']['div_name'];
                                            $arr_total[$div1][] = 0;
                                            if(!empty($esidata['0']['esi'])){
                                            foreach ($esidata['0']['esi'] as $esi) { 
                                        if($esi['ectc']['head_operator'] == 'Addition' && $esi['0']['salary_head_item_desc'] == 'ESI' && $esi['divn']['div_name'] == $div['division']['div_name']){  
                                            $esisum += $esi['0']['totalamount'];
                                            $totalsum += $esi['0']['totalamount'];
                                            $cnt += $esi['0']['vcount'];
                                            $arr_total[$div1][] = abs($esi['0']['totalamount']);
                                            }}} ?>
                                            <td><?php echo round($esisum); ?> </td>
                                         <?php  }?>
                                            <td><?php echo $cnt; ?> </td>
                                            <td><?php echo round($totalsum); ?> </td>
                                        </tr> 
                                        <tr>
                                            <td>PF </td>
                                            <?php $cnt=0;$totalsum=0; foreach ($arr_div as $div) { $esisum = 0; $div1 =$div['division']['div_name'];
                                            $arr_total[$div1][] = 0;
                                            if(!empty($esidata['0']['esi'])){
                                            foreach ($esidata['0']['esi'] as $esi) { 
                                        if($esi['ectc']['head_operator'] == 'Addition' && $esi['0']['salary_head_item_desc'] == 'PF' && $esi['divn']['div_name'] == $div['division']['div_name']){  
                                            $esisum += $esi['0']['totalamount']; 
                                            $totalsum += $esi['0']['totalamount'];
                                            $cnt += $esi['0']['vcount'];
                                            $arr_total[$div1][] = abs($esi['0']['totalamount']);
                                            }} } ?>
                                            <td><?php echo round($esisum); ?> </td>
                                         <?php  }?>
                                            <td><?php echo $cnt; ?> </td>
                                            <td><?php echo round($totalsum); ?> </td>
                                        </tr> 
                                        <tr>
                                            <td>LWF</td>
                                            <?php $cnt=0;$totalsum=0; foreach ($arr_div as $div) { $esisum = 0; $div1 =$div['division']['div_name'];
                                            $arr_total[$div1][] = 0;
                                            if(!empty($esidata['0']['esi'])){
                                            foreach ($esidata['0']['esi'] as $esi) { 
                                        if($esi['ectc']['head_operator'] == 'Addition' && $esi['0']['salary_head_item_desc'] == 'LWF' && $esi['divn']['div_name'] == $div['division']['div_name']){  
                                            
                                            $esisum += $esi['0']['totalamount'];
                                            
                                            $totalsum += abs($esi['0']['totalamount']);
                                            $cnt += $esi['0']['vcount']; 
                                            $arr_total[$div1][] = abs($esi['0']['totalamount']);
                                            }} } ?>
                                            <td><?php echo $esisum; ?> </td>
                                         <?php  }?>
                                            <td><?php echo $cnt; ?> </td>
                                            <td><?php echo round($totalsum); ?> </td>
                                        </tr> 
                                        <tr>
                                            <td>WWF</td>
                                            <?php $cnt=0; $totalsum=0; 
                                            foreach ($arr_div as $div) { $esisum = 0; $div1 =$div['division']['div_name']; 
                                            $arr_total[$div1][] = 0;
                                            if(!empty($esidata['0']['esi'])){
                                            foreach ($esidata['0']['esi'] as $esi) { 
                                        if($esi['ectc']['head_operator'] == 'Addition' && $esi['0']['salary_head_item_desc'] == 'WWF' && $esi['divn']['div_name'] == $div['division']['div_name']){ 
                                            $esisum += abs($esi['0']['totalamount']); 
                                            $totalsum += abs($esi['0']['totalamount']); 
                                            $cnt += $esi['0']['vcount'];
                                            $arr_total[$div1][] = abs($esi['0']['totalamount']);
                                            }}} ?>
                                            <td><?php echo round($esisum); ?> </td>
                                         <?php  }?>
                                            <td><?php echo $cnt; ?> </td>
                                            <td><?php echo round($totalsum); ?> </td>
                                        </tr> 
                                        
                                        
                                        <tr><th>TOTAL</th><?php $grand = 0; 
                                            foreach ($arr_div as $div) { 
                                            $div1 =$div['division']['div_name'];
                                            $subtot = array_sum($arr_total[$div1]); 
                                            $grand += $subtot;?>
                                            <th><?php echo round($subtot); ?></th><?php } ?>
                                            <th></th>
                                            <th><?php echo round($grand); ?></th>
                                        </tr>
                                        <?php }else{ ?>
                                        <tr>
                                            <td colspan="4" align='center'>No employees found under this criteria</td>
                                        </tr>  
                                <?php } ?>
                                     
                              
                            </tbody>
                        </table>
    
                    <br>
                              
                <?php } ?>
                    <!-- /.box-body -->
            
        </div>
    </div>  
</div>
                             <?php  }else{ ?>
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
    .col-md-6 {
        width: 50%;
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
        text-align: right;
        padding: 8px;
        line-height: 1.42857143;
        vertical-align: top;
        border: 1px solid #B2B2B2;
    }
</style>
<?php
    echo $this->element('reportadminheader', array(
       'title' => 'Reconciliation Report'));
    ?>
<?php 
if(count($arr_salary_for_template) == 0){   echo '<div style="font-size: 25px;text-align:center;margin-top:30px; background-color:#F7D3D2;">
        There is no data available</div>';
      //  die();
 } 
?>
                   <?php $i=0; foreach ($arr_salary_for_template as $value) {
                     if(count($value) !== 0){
                     $i += 1; 
                   ?>
                             <?php
                       foreach ($value as $key => $values) { 
                           $brch =isset($value['summary']['0']['br']['branch_code'])?$value['summary']['0']['br']['branch_code'] : '' ;
                           $data= isset($arr_salary_for_bank_data[$brch])?$arr_salary_for_bank_data[$brch]:'';
                           $esidata= isset($arr_salary_for_esi[$brch])?$arr_salary_for_esi[$brch]:'';
                           $bank= isset($arr_salary_for_bank[$brch])?$arr_salary_for_bank[$brch]:'';
                           $loan= isset($arr_salary_for_loan[$brch])?$arr_salary_for_loan[$brch]:'';
                           $advance= isset($arr_salary_for_advance[$brch])?$arr_salary_for_advance[$brch]:'';
                           $data_hold= isset($arr_salary_for_bank_hold[$brch])?$arr_salary_for_bank_hold[$brch]:'';
                        ?>
                
																																																																																																									
			 				 
            <table style="border: 1px solid #fff;margin-top:10px;"><tr><th style="border: 1px solid #fff;width: 40%;text-align: left;">Period :  <b><?php echo $period; ?></b></th>
                    <th style="border: 1px solid #fff;width: 60%;text-align: left;">Branch : <b><?php echo isset($value['summary']['0']['br']['branch_name'])?$value['summary']['0']['br']['branch_name'] : '' ; ?></b></th>             
    </tr></table>  
							   
          <table class="table">
                            <thead>
                              <tr> 
                                  <th style="text-align: left;">Heads</th>
                                  <?php foreach ($arr_div as $div) { ?>
                                  <th><?php echo $div['division']['div_name']; ?></th>
                                  <?php } ?> 
                                  <th>Count</th>
                                  <th>Total</th>
                              </tr>
                            </thead>
                           
                            <tbody>
                                <?php $arr_items=array();
                                foreach($additions as $val){ 
                                    $count = 0; $subtotals = 0; ?>
                                <tr>
                                <td style="text-align: left;"><?php echo $val['0']['item']; ?></td>
                                <?php foreach ($arr_div as $div) { $div1 =$div['division']['div_name'];
                                        $arr_items[$div1][] = 0;?>
                                <td>
                                    <?php foreach($values as $row){  ?>
                                        <?php if($div['division']['div_name'] == $row['divn']['div_name'] && $val['salhead']['salary_head_item_pkey'] == $row['ectc']['salary_head_item_fkey']) {
                                    echo round($row['0']['totalamount']);
                                    $count += $row['0']['vcount'];
                                    $subtotals += $row['0']['totalamount'];
                                    $arr_items[$div1][] = $row['0']['totalamount'];
                                        } } ?></td>
                                        
                                <?php } ?>
                                <td><?php echo round($count); ?></td>
                                <td><?php echo round($subtotals); ?></td>
                                </tr>
                                <?php } ?> 
                                <tr>
                                              <th style="text-align: left;">GROSS SALARY</th>
                                              <?php $grdtotals = 0;
                                              foreach ($arr_div as $div) {  $grtotals = 0;?>
                                              <th><?php foreach($values as $row){ ?>
                                        <?php if($div['division']['div_name'] == $row['divn']['div_name'] && $row['ectc']['head_operator'] == 'Addition' 
                                                && $row['0']['salary_head_item_desc'] !='ESI' && $row['0']['salary_head_item_desc'] !='PF' && $row['0']['salary_head_item_desc'] !='LWF'
                                                && $row['0']['salary_head_item_desc'] !='WWF') {
                                              $grtotals += round($row['0']['totalamount']);
                                              $grdtotals += round($row['0']['totalamount']);
                                        }} echo round($grtotals);?>
                                              </th>
                                              <?php } ?> 
                                              <th></th>
                                              <th><?php echo round($grdtotals); ?></th>
                                        </tr>
                                        <?php $cnt=count($arr_div) + 3; ?>
                                                
                                        <tr><th colspan="<?php echo $cnt;?>" style="text-align: left;">DEDUCTIONS</th></tr>
                                        <?php 
                                foreach($deductions as $val){ 
                                    $count1 = 0; $subtotals1 = 0; ?>
                                <tr>
                                <td style="text-align: left;"><?php echo $val['0']['item']; ?></td>
                                <?php foreach ($arr_div as $div) { $div1 =$div['division']['div_name'];?>
                                <td>
                                    <?php foreach($values as $row){ ?>
                                        <?php if($div['division']['div_name'] == $row['divn']['div_name'] && $val['0']['salary_head_item_pkey'] == $row['ectc']['salary_head_item_fkey']) {
                                            if($row['0']['salary_head_item_desc'] =='LWF'){
                                            echo abs($row['0']['totalamount']);
                                            }else{
                                            echo abs(round($row['0']['totalamount']));    
                                            }
                                    $count1 += $row['0']['vcount'];
                                    $subtotals1 += abs($row['0']['totalamount']);
                                    $arr_items[$div1][] = $row['0']['totalamount'];
                                        }}?></td>
                                        
                                <?php } ?>
                                <td><?php echo round($count1); ?></td>
                                <td><?php echo round($subtotals1); ?></td>
                                </tr>
                                <?php } ?> 
                                <tr>
                                              <th style="text-align: left;">TOTAL DEDUCTIONS</th>
                                              <?php $grdtotals1 = 0;
                                              foreach ($arr_div as $div) {  $grtotals1 = 0;?>
                                              <th><?php foreach($values as $row){ ?>
                                        <?php if($div['division']['div_name'] == $row['divn']['div_name'] && $row['ectc']['head_operator'] == 'Deduction') {
                                              $grtotals1 += abs($row['0']['totalamount']);
                                              $grdtotals1 += abs($row['0']['totalamount']);
                                        }} echo round($grtotals1);?>
                                              </th>
                                              <?php } ?> 
                                              <th></th>
                                              <th><?php echo round($grdtotals1); ?></th>
                                        </tr>
                                        <?php } ?>
                                        <tr><th colspan="<?php echo $cnt;?>" style="text-align: left;">LOAN & ADVANCE</th></tr>
                                           
                                        <tr>
                                        <td style="text-align: left;"><?php echo "Salary Advance"; ?></td>  
                                              <?php  $adsum = 0; $adv = 0; $advcnt = 0;
                                         foreach ($arr_div as $div) { $adv = 0; $div1 =$div['division']['div_name'];
                                         if(!empty($advance)){
                                         foreach ($advance['0']['advance'] as $ad) {
                                            if($div['division']['div_name'] == $ad['divn']['div_name']) { 
                                                $adv = abs(round($ad['0']['totalamount'])); 
                                                $adsum += $adv ; 
                                                $advcnt += $ad['0']['vcount'];
                                                $arr_items[$div1][] = round($ad['0']['totalamount']);
                                         } } } ?>
                                       <td><?php echo $adv; ?></td>
                                        <?php } ?>
                                        <td><?php echo $advcnt; ?></td>  
                                        <td><?php echo round($adsum); ?></td> 
                                             
                                        </tr>
                                        <tr>
                                              <td style="text-align: left;"><?php echo "Employee Loan"; ?></td>  
                                              <?php 
                                              $lnsum = 0; $loancnt = 0;
                                         foreach ($arr_div as $div) {  $ln = 0; 
                                         $div1 =$div['division']['div_name'];
                                         if(!empty($loan)){
                                         foreach ($loan['0']['loan'] as $lon) { 
                                            if($div['division']['div_name'] == $lon['divn']['div_name'] ) {  
                                                $ln = abs(round($lon['0']['totalamount']));
                                         $lnsum += $ln; 
                                         $loancnt += $lon['0']['vcount'];
                                         $arr_items[$div1][] = round($lon['0']['totalamount']);
                                         } } } ?>
                                              <td><?php echo $ln; ?></td>
                                         <?php  }?>
                                        <td><?php  echo $loancnt; ?></td>  
                                        <td><?php echo round($lnsum); ?></td> 
                                        </tr>
                                        <tr><th style="text-align: left;">Total </th>
                                             <?php $grndsum1 = 0;
                                            foreach ($arr_div as $div) { $grndsum = 0;
                                            if(!empty($loan)){
                                            foreach ($loan['0']['loan'] as $lon) { 
                                            if($div['division']['div_name'] == $lon['divn']['div_name'] ) {  
                                                $grndsum += abs($lon['0']['totalamount']);
                                                $grndsum1 += abs($lon['0']['totalamount']);
                                            }} }
                                            if(!empty($advance)){
                                            foreach ($advance['0']['advance'] as $ad) {
                                            if($div['division']['div_name'] == $ad['divn']['div_name']) { 
                                                $grndsum += abs($ad['0']['totalamount']);
                                                $grndsum1 += abs($ad['0']['totalamount']);
                                            }}}
                                                ?>
                                            <th><?php echo abs(round($grndsum));?></th><?php } ?>
                                            <th></th>
                                            <th><?php echo round($lnsum+$adsum);?></th></tr>
                                        <tr><th style="text-align: left;">NET SALARY</th>
                                        <?php $grand = 0; 
                                            foreach ($arr_div as $div) { 
                                            $div1 =$div['division']['div_name'];
                                            $subtot = array_sum($arr_items[$div1]); 
                                            $grand += $subtot;?>
                                            <th><?php echo round($subtot); ?></th><?php } ?>
                                            <th></th>
                                            <th><?php echo round($grand); ?></th></tr>
                                        <tr><th colspan="<?php echo $cnt;?>" style="text-align: left;">BANK DETAILS</th></tr>
                                        
                                        <?php $grsum = 0; $arr_bank = array(); if(!empty($bank)){ 
                                        foreach ($bank['0']['bank'] as $bk) { $sum = 0; 
                                            if($bk['ed']['bank_name'] !== ''){ 
                                                $sum += abs(round($bk['0']['totalamount']));
                                              $grsum += abs(round($bk['0']['totalamount']));
                                             ?>
                                        <tr>
                                              <td style="text-align: left;"><?php echo $bk['ed']['bank_name']; ?></td>  
                                        <?php foreach ($arr_div as $div) { $div1 =$div['division']['div_name'];
                                        $arr_bank[$div1][] = 0;
                                            if($div['division']['div_name'] == $bk['divn']['div_name']) { 
                                                $arr_bank[$div1][] = abs($bk['0']['totalamount']); ?>
                                              <td><?php echo abs(round($bk['0']['totalamount'])); ?></td>
                                        <?php } else{ ?>
                                              <td>0</td>
                                        <?php  } }?>
                                        <td><?php echo $bk['0']['vcount']; ?></td>  
                                        <td><?php echo round($sum); ?></td> 
                                        </tr>
                                        <?php } } } ?>
                                        <?php  if(!empty($data)){ ?>
                                        <tr>
                                        <td style="text-align: left;"><?php echo "CASH"; ?></td> 
                                        <?php $amttot = 0; $cn=0; 
                                        foreach ($arr_div as $div) {  $amt = 0; $arr_bank[$div1][] = 0;
                                        if(!empty($data)){
                                            foreach ($data['0']['bank'] as $bk) {  ?>
                                        <?php 
                                        $div1 =$div['division']['div_name'];
                                        
                                            if($div['division']['div_name'] == $bk['divn']['div_name']) { 
                                                $arr_bank[$div1][] = abs($bk['0']['totalamount']); 
                                                $grsum += abs(round($bk['0']['totalamount']));
                                                $amttot += abs(round($bk['0']['totalamount']));
                                            $amt = isset($bk['0']['totalamount'])?$bk['0']['totalamount']:'';
                                            $cn +=$bk['0']['vcount'];
                                        ?><?php } } }?>
                                        <td><?php echo abs(round($amt)); ?></td> <?php } ?>
                                        <td><?php echo $cn; ?></td>  
                                        <td><?php echo $amttot; ?></td> 
                                        </tr>
                                        <?php  } ?> 
                                        <tr><th>Salary Hold</th>
                                            <?php  $cntt=0; $holdsum = 0; foreach ($arr_div as $div) { $hold = 0;
                                            if(!empty($data_hold)){
                                                foreach ($data_hold['0']['bank'] as $bk) {  
                                           
                                            if($div['division']['div_name'] == $bk['divn']['div_name'] && $bk['payroll_master']['action'] == 'Processed') {
                                                $hold += abs(round($bk['0']['totalamount'])); 
                                                $cntt +=$bk['0']['vcount'];
                                                $holdsum += abs(round($bk['0']['totalamount'])); 
                                            } }}?>
                                                <th><?php echo round($hold); ?> </th>
                                            <?php } ?> 
                                            
                                            
                                            <td><?php echo $cntt; ?></td> 
                                            <th><?php echo round($holdsum); ?> </th>
                                          
                                        </tr>
                                        <tr><th style="text-align: left;">GRAND TOTAL</th>
                                            <?php $grand = 0;
                                            foreach ($arr_div as $div) { 
                                            $div1 =$div['division']['div_name'];
                                            $subtot = isset($arr_bank[$div1])?array_sum($arr_bank[$div1]):''; 
                                            $grand += $subtot;?>
                                            <th><?php echo round($subtot); ?></th><?php } ?>
                                            <th></th>
                                            <th><?php echo round($grand); ?></th></tr>
                                        <tr><th colspan="<?php echo $cnt;?>" style="text-align: left;">EPF AND ESI Summary</th></tr>
                                        
                                        
                                        <tr>
                                            <td style="text-align: left;">ESI</td>
                                            <?php $cnt=0; $totalsum=0; $arr_total = array();
                                            foreach ($arr_div as $div) { $esisum = 0; $div1 =$div['division']['div_name'];
                                            $arr_total[$div1][] = 0;
                                            if(!empty($esidata['0']['esi'])){
                                            foreach ($esidata['0']['esi'] as $esi) { 
                                        if($esi['ectc']['head_operator'] == 'Addition' && $esi['0']['salary_head_item_desc'] == 'ESI' && $esi['divn']['div_name'] == $div['division']['div_name']){  
                                            $esisum += $esi['0']['totalamount'];
                                            $totalsum += $esi['0']['totalamount'];
                                            $cnt += $esi['0']['vcount'];
                                            $arr_total[$div1][] = abs($esi['0']['totalamount']);
                                        }}}?>
                                            <td><?php echo round($esisum); ?> </td>
                                         <?php  }?>
                                            <td><?php echo $cnt; ?> </td>
                                            <td><?php echo round($totalsum); ?> </td>
                                        </tr> 
                                        <tr>
                                            <td style="text-align: left;">PF </td>
                                            <?php $cnt=0;$totalsum=0; foreach ($arr_div as $div) { $esisum = 0; $div1 =$div['division']['div_name'];
                                            $arr_total[$div1][] = 0;
                                            if(!empty($esidata['0']['esi'])){
                                            foreach ($esidata['0']['esi'] as $esi) { 
                                        if($esi['ectc']['head_operator'] == 'Addition' && $esi['0']['salary_head_item_desc'] == 'PF' && $esi['divn']['div_name'] == $div['division']['div_name']){  
                                            $esisum += $esi['0']['totalamount']; 
                                            $totalsum += $esi['0']['totalamount'];
                                            $cnt += $esi['0']['vcount'];
                                            $arr_total[$div1][] = abs($esi['0']['totalamount']);
                                        } }}?>
                                            <td><?php echo round($esisum); ?> </td>
                                         <?php  }?>
                                            <td><?php echo $cnt; ?> </td>
                                            <td><?php echo round($totalsum); ?> </td>
                                        </tr> 
                                        <tr>
                                            <td style="text-align: left;">LWF</td>
                                            <?php $cnt=0;$totalsum=0; foreach ($arr_div as $div) { $esisum = 0; $div1 =$div['division']['div_name'];
                                            $arr_total[$div1][] = 0;
                                            if(!empty($esidata['0']['esi'])){
                                            foreach ($esidata['0']['esi'] as $esi) { 
                                        if($esi['ectc']['head_operator'] == 'Addition' && $esi['0']['salary_head_item_desc'] == 'LWF' && $esi['divn']['div_name'] == $div['division']['div_name']){  
                                            
                                            $esisum += $esi['0']['totalamount'];
                                            
                                            $totalsum += abs($esi['0']['totalamount']);
                                            $cnt += $esi['0']['vcount']; 
                                            $arr_total[$div1][] = abs($esi['0']['totalamount']);
                                        }}}?>
                                            <td><?php echo $esisum; ?> </td>
                                         <?php  }?>
                                            <td><?php echo $cnt; ?> </td>
                                            <td><?php echo round($totalsum); ?> </td>
                                        </tr> 
                                        <tr>
                                            <td style="text-align: left;">WWF</td>
                                            <?php $cnt=0; $totalsum=0; 
                                            foreach ($arr_div as $div) { $esisum = 0; $div1 =$div['division']['div_name']; 
                                            $arr_total[$div1][] = 0;
                                            if(!empty($esidata['0']['esi'])){
                                            foreach ($esidata['0']['esi'] as $esi) { 
                                        if($esi['ectc']['head_operator'] == 'Addition' && $esi['0']['salary_head_item_desc'] == 'WWF' && $esi['divn']['div_name'] == $div['division']['div_name']){ 
                                            $esisum += abs($esi['0']['totalamount']); 
                                            $totalsum += abs($esi['0']['totalamount']); 
                                            $cnt += $esi['0']['vcount'];
                                            $arr_total[$div1][] = abs($esi['0']['totalamount']);
                                        } }}?>
                                            <td><?php echo round($esisum); ?> </td>
                                         <?php  }?>
                                            <td><?php echo $cnt; ?> </td>
                                            <td><?php echo round($totalsum); ?> </td>
                                        </tr> 
                                        
                                        
                                        <tr><th style="text-align: left;">TOTAL</th><?php $grand = 0; 
                                            foreach ($arr_div as $div) { 
                                            $div1 =$div['division']['div_name'];
                                            $subtot = array_sum($arr_total[$div1]); 
                                            $grand += $subtot;?>
                                            <th><?php echo round($subtot); ?></th><?php } ?>
                                            <th></th>
                                            <th><?php echo round($grand); ?></th>
                                        </tr>
                                       
                                     
                              
                            </tbody>
                        </table>
					 <?php  } ?>
									 
							  
									
								
	
						
							  
                <?php } ?>
		
             <?php } ?>