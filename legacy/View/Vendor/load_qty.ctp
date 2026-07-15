<div class="form-group parents">
    <div class="col-md-6">
        <label for="in_time" class="col-sm-4 control-label">Item Name<span class="star">*</span></label>
        <div class="col-md-8">
            <select class="form-control" name="item_name[]">
                <?php foreach($arr_att as $val){ ?>
                <?php $bal = isset($val['item_view']['balance'])?$val['item_view']['balance']:0; ?>
                    <option value="<?php echo $val['item_view']['item_pkey']; ?>"><?php echo $val['item_view']['item_name'].' bal - '.$bal; ?></option>
                <?php } ?>
            </select>
        </div> 
    </div>
    <div class="col-md-4">
        <label for="in_time" class="col-sm-2 control-label">Qty<span class="star">*</span></label>
        <div class="col-md-6">
            <input type="text"  class="form-control" value="<?php echo isset($item_details['0']['item']['item_code']) ? $item_details['0']['item']['item_code'] : ''; ?>" name="item_qty[]" id="item_qty" placeholder="Enter item code"  required="required" >
        </div> 
        <div class="col-md-2 pull-right">
            <a class="btn btn-primary" onclick="removei(this);"><li class="fa fa-minus"></li></a>
        </div>
    </div>
</div>