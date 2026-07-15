<?php foreach ($arr_salary_components as $key => $value) { ?>
    <div class="form-group">
        <div class="col-md-12">
            <label for="out_time" class="col-md-4 control-label"><?= $value['ectc']['salary_head_item_desc']; ?><span class="star">*</span></label>
            <div class="col-md-1">:</div>
            <div class="col-md-7">
                <input type="number" name="salary_head_item_fkey[<?= $value['ectc']['salary_head_item_fkey']; ?>]" class="form-control" id="comp_amount" value="">
            </div>
        </div>
    </div>
<?php } ?>