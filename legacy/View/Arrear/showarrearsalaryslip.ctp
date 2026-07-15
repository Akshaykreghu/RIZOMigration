<div class="modal-body">
    <legend><strong><?php echo trim($head); ?></strong></legend>
    <?php 
        $netpay = 0;
        $net_arrear_pay = 0;
        foreach ($arr_emparrearsalaryslip as $value){
            // debug($value);
        $head_desc = isset($value['head_desc'])?$value['head_desc']:'';
        // debug($head_desc);
        $head_pkey = isset($value['head_pkey'])?$value['head_pkey']:'';
    ?>
        <fieldset>
            <legend><strong><?php echo trim($head_desc); ?></strong></legend>
            <?php 
                $arr_items = isset($value['items'])?$value['items']:array();
                if(!empty($arr_items)){
            ?>
                    <div class="row">
                        <div class="col-sm-12 col-lg-4 col-md-4"><strong>Item</strong></div>
                        <!-- <div class="col-sm-12 col-lg-4 col-md-4"><strong>Salary rate</strong></div> -->
                        <div class="col-sm-12 col-lg-4 col-md-4"><strong>Salary amount</strong></div>
                        <div class="col-sm-12 col-lg-4 col-md-4"><strong>Arrear amount</strong></div>
                    </div>
            <?php
                    $total_rate = 0;
                    $total_amount = 0;
                    $total_arrear_amount = 0;
                    foreach ($arr_items as $item){
                        if(isset($item['structure_det_value'])){
                            $total_rate += round($item['structure_det_value']);
                        }
                        if(isset($item['salary_amount'])){
                            $total_amount += round($item['salary_amount']);
                             if($item['head_operator'] == 'Addition'){
                            $netpay += abs(round($item['salary_amount']));
                            }else{
                               $netpay -= abs(round($item['salary_amount']));   
                              }
                        }
                          if(isset($item['arrear_amount'])){ 
                            $total_arrear_amount += round($item['arrear_amount']);
                    
                            if($item['head_operator'] == 'Addition'){
                                $net_arrear_pay += abs(round($item['arrear_amount']));
                            }else{
                                $net_arrear_pay -= abs(round($item['arrear_amount']));
                            }
                        }
            ?>
                        <div class="row">
                            <div class="col-sm-12 col-lg-4 col-md-4"><?php echo isset($item['salary_head_item_desc'])?trim($item['salary_head_item_desc']):''; ?></div>
                          <!--   <div class="col-sm-12 col-lg-4 col-md-4"><?php echo isset($item['structure_det_value'])?trim($item['structure_det_value']):''; ?></div> -->
                            <div class="col-sm-12 col-lg-4 col-md-4"><?php echo isset($item['salary_amount'])?trim(round($item['salary_amount'])):'0'; ?></div>
                            <div class="col-sm-12 col-lg-4 col-md-4"><?php echo isset($item['arrear_amount'])?trim(round($item['arrear_amount'])):'0'; ?></div>
                        </div>
            <?php
                    }
                    
                    if($head_pkey == 1){
            ?>
                <hr>
                <div class="row">
                    <div class="col-sm-12 col-lg-4 col-md-4"><strong>Gross Salary</strong></div>
                    <!-- <div class="col-sm-12 col-lg-4 col-md-4"><strong><?php echo $total_rate; ?></strong></div> -->
                    <div class="col-sm-12 col-lg-4 col-md-4"><strong><?php echo $total_amount; ?></strong></div>
                    <div class="col-sm-12 col-lg-4 col-md-4"><strong><?php echo $total_arrear_amount; ?></strong></div>
                </div>
            <?php
                    }
                    
                }
            ?>
            <?php ?>
            <hr>
        </fieldset>
    <?php } ?>
    <fieldset>
        <div class="row">
            <div class="col-sm-12 col-lg-4 col-md-4"><strong>Net Pay</strong></div>
            <div class="col-sm-12 col-lg-4 col-md-4"><?php echo round($netpay); ?></div>
            <div class="col-sm-12 col-lg-4 col-md-4"><strong><?php echo round($net_arrear_pay); ?></strong></div>
        </div>
    </fieldset>
    <hr>
    <fieldset>
            <legend><strong>Indirect</strong></legend>
	</fieldset>
    <?php
	if(!empty($arr_indirect)){
            ?>
                    <div class="row">
                        <div class="col-sm-12 col-lg-4 col-md-4"><strong>Item</strong></div>
                        <!-- <div class="col-sm-12 col-lg-4 col-md-4"><strong>Salary rate</strong></div> -->
                        <div class="col-sm-12 col-lg-4 col-md-4"><strong>Salary amount</strong></div>
                        <div class="col-sm-12 col-lg-4 col-md-4"><strong>Arrear amount</strong></div>
                    </div>
            <?php
                    $total_rate = 0;
                    $total_amount = 0;
                    foreach ($arr_indirect as $item){
                       // debug($item);
            ?>
                        <div class="row">
                            <div class="col-sm-12 col-lg-4 col-md-4"><?php echo isset($item['EmpArrearSalarySlip']['salary_head_item_desc'])?trim($item['EmpArrearSalarySlip']['salary_head_item_desc']):''; ?></div>
                            <!-- <div class="col-sm-12 col-lg-4 col-md-4"><?php echo isset($item['EmpArrearSalarySlip']['structure_det_value'])?trim($item['EmpArrearSalarySlip']['structure_det_value']):''; ?></div> -->
                            <div class="col-sm-12 col-lg-4 col-md-4"><?php echo isset($item['EmpArrearSalarySlip']['salary_amount'])?trim($item['EmpArrearSalarySlip']['salary_amount']):'0'; ?></div>
                            <div class="col-sm-12 col-lg-4 col-md-4"><?php echo isset($item['EmpArrearSalarySlip']['arrear_amount'])?trim($item['EmpArrearSalarySlip']['arrear_amount']):'0'; ?></div>
                        </div>
            <?php
                    }
	}
	?>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-primary" data-dismiss="modal">
        Ok
    </button>
</div>