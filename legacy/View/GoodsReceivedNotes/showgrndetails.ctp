<div class="modal-dialog" style="width: 100%  ; ">

    <!-- Modal content-->
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">GR Details </h4>
        </div>
        <div class="modal-body">
            <!-- Form starts -->
            <div id="" class="">
                <div class="col-md-4">
                    <b>Gr Number</b>
                    <p><?php echo $arr_att['0']['goods_receved_notes']['gr_number']; ?></p>
                </div>
                <div class="col-md-4">
                    <b>Gr Date</b>
                    <p><p><?php echo $arr_att['0']['goods_receved_notes']['gr_date']; ?></p></p>
                </div>
                <div class="col-md-4">
                    <b>Creation Date</b>
                    <p><p><?php echo $arr_att['0']['goods_receved_notes']['creation_date']; ?></p></p>
                </div>
                <table class="table-responsive table" >
                    <tr>
                        <th>Item</th>
                        <th>Ordering Qty</th>
                        <th>Received Qty</th>
                        <th>Remarks </th>
                    </tr>
                    <?php foreach ($arr_att as $val) { ?>
                    <tr>
                        <td><?php echo $val['item_master']['item_desc']; ?></td>
                        <td><?php echo $val['gr_item_details']['ordering_qty']; ?></td>
                        <td><?php echo $val['gr_item_details']['received_qty']; ?></td>
                        <td><?php echo $val['goods_receved_notes']['remark']; ?></td>
                    </tr>
                    <?php } ?>

                </table>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>

        </div>
    </div>
</div>