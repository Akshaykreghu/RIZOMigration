<div class="modal-body">
    <legend><strong><?php echo trim($head); ?></strong></legend>
    <?php
    $netpay = 0;
    foreach ($arr_empsalaryslip as $value) {
        $head_desc = isset($value['head_desc']) ? $value['head_desc'] : '';
        $head_pkey = isset($value['head_pkey']) ? $value['head_pkey'] : '';
    ?>
        <fieldset>
            <legend><strong><?php echo trim($head_desc); ?></strong></legend>
            <?php
            $arr_items = isset($value['items']) ? $value['items'] : array();
            if (!empty($arr_items)) {
            ?>
                <div class="row">
                    <div class="col-sm-12 col-lg-4 col-md-4"><strong>Item</strong></div>
                    <div class="col-sm-12 col-lg-4 col-md-4"><strong>Salary rate</strong></div>
                    <div class="col-sm-12 col-lg-4 col-md-4"><strong>Salary amount</strong></div>
                </div>
                <?php
                //Edited by Akshay on 29-10-2024
                $og_total = 0;
                $rnd_total = 0;
                foreach ($arr_items as $item) {
                    $amount = $item['salary_amount'];
                    $og_total += round($amount, 2);
                    $rnd_total += round($amount);
                }
                $diff = round($og_total) - $rnd_total;
                //End
                $total_rate = 0;
                $total_amount = 0;
                foreach ($arr_items as $key => $item) {
                    //Edited by Akshay on 29-10-2024
                    if ($key == 0)
                        $item['salary_amount'] = $item['salary_amount'] + $diff;
                    //End
                    if (isset($item['structure_det_value'])) {
                        $total_rate += round($item['structure_det_value']);
                    }
                    if (isset($item['salary_amount'])) {
                        $total_amount += round($item['salary_amount']);
                    }
                    if ($item['head_type'] == 'fixed' || $item['head_type'] == 'manually' || $item['head_type'] == 'limit') {
                        //Edited by Akshay on 2-12-2024
                        $det = abs(round($item['salary_amount'], 2));
                        if(trim($item['head_operator']) == 'Deduction'){
                            $netpay -= abs($det);
                        }else{
                            $netpay += $det;
                        }
                        //End
                    } else {
                        // Edited by Akshay on 2-12-2024
                        $det = round($item['salary_amount']); //Edited by Akshay on 29-10-2024
                        if(trim($item['head_operator']) == 'Deduction'){
                            $netpay -= abs($det);
                        }else{
                            $netpay += $det;
                        }
                        // End
                        
                    }
                ?>
                    <div class="row">
                        <div class="col-sm-12 col-lg-4 col-md-4"><?php echo isset($item['salary_head_item_desc']) ? trim($item['salary_head_item_desc']) : ''; ?></div>
                        <div class="col-sm-12 col-lg-4 col-md-4"><?php echo isset($item['structure_det_value']) ? abs($item['structure_det_value']) : ''; ?></div>
                        <div class="col-sm-12 col-lg-4 col-md-4"><?php echo isset($item['salary_amount']) ? $det : ''; ?></div>
                    </div>
                <?php
                }

                if ($head_pkey == 1) {
                ?>
                    <hr>
                    <div class="row">
                        <div class="col-sm-12 col-lg-4 col-md-4"><strong>Gross Salary</strong></div>
                        <div class="col-sm-12 col-lg-4 col-md-4"><strong><?php echo $total_rate; ?></strong></div>
                        <div class="col-sm-12 col-lg-4 col-md-4"><strong><?php echo $total_amount; ?></strong></div>
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
            <div class="col-sm-12 col-lg-4 col-md-4"></div>
            <div class="col-sm-12 col-lg-4 col-md-4"><strong><?php echo round($netpay); ?></strong></div>
        </div>
    </fieldset>
    <hr>
    <fieldset>
        <legend><strong>Indirect</strong></legend>
    </fieldset>
    <?php
    if (!empty($arr_indirect)) {
    ?>
        <div class="row">
            <div class="col-sm-12 col-lg-4 col-md-4"><strong>Item</strong></div>
            <div class="col-sm-12 col-lg-4 col-md-4"><strong>Salary rate</strong></div>
            <div class="col-sm-12 col-lg-4 col-md-4"><strong>Salary amount</strong></div>
        </div>
        <?php
        $total_rate = 0;
        $total_amount = 0;
        foreach ($arr_indirect as $item) {
            if ($item['EmpSalarySlip']['head_type'] == 'fixed' || $item['EmpSalarySlip']['head_type'] == 'manually' || $item['EmpSalarySlip']['head_type'] == 'limit') {
                $det = abs(round($item['EmpSalarySlip']['salary_amount'], 2));
            } else {
                $det = round($item['EmpSalarySlip']['salary_amount']);
            }
        ?>
            <div class="row">
                <div class="col-sm-12 col-lg-4 col-md-4"><?php echo isset($item['EmpSalarySlip']['salary_head_item_desc']) ? trim($item['EmpSalarySlip']['salary_head_item_desc']) : ''; ?></div>
                <div class="col-sm-12 col-lg-4 col-md-4"><?php echo isset($item['EmpSalarySlip']['structure_det_value']) ? abs($item['EmpSalarySlip']['structure_det_value']) : ''; ?></div>
                <div class="col-sm-12 col-lg-4 col-md-4"><?php echo isset($item['EmpSalarySlip']['salary_amount']) ? $det : ''; ?></div>
            </div>
    <?php
        }
    }
    ?>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-primary" data-dismiss="modal">
        OK
    </button>
</div>