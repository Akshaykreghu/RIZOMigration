<div class="modal-content">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">x</span></button>
        <h4 class="modal-title" id="myModalLabel">MR Details - <span><?php echo $arr_material_master['0']['storemaster']['store_location']; ?></span> </h4>
    </div>
    <div class="modal-body">
        <div class="container_fluid">
            <span><em style="font-size: 28px;"><?php echo $arr_att['0']['MaterialRequest']['mr_code']; ?></em></span> <span class="pull-right">MR Date : <b><?php echo $arr_att['0']['MaterialRequest']['mr_date']; ?></b></span>
            <span> 
                <p>
                    Remarks : <?php echo $arr_att['0']['MaterialRequest']['remarks']; ?>
                </p>
            </span>
        </div>
        <hr>
        <div>
            <h4>MR Items</h4>
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Sl No</th>
                        <th>Item Name</th>
                        <th>Qty</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i= 1; foreach ($arr_material_master as $val) { ?>
                    <tr>
                        <td><?php echo $i; $i++; ?></td>
                        <td><?php echo $val['itemmaster']['item_desc']; ?></td>
                        <td><?php echo $val['mrdetails']['required_qty']; ?></td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
    </div>
</div>
<script>
        $(document).ready(function () {
            
            
        });
</script>