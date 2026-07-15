<script>
    $('.datepicker').datepicker();
    var options = {
        success: function (resp) {
            var pid = $.parseJSON(resp).pk;
            $('#modalForm').modal('hide');
            
            loadtable(pid, 1);
            $.notify("Success", {
                type: 'success',
                allow_dismiss: false
            });
        }  // post-submit callback
    };
    $('#form_edit_order').on('submit', function (event) { //alert('aaa');
        event.preventDefault();
        if (confirm("Do You Want To Save The Form")) {
            $('#form_edit_order').ajaxSubmit(options);
        }
    });
    

</script>

<div class="modal-dialog modal-md">
    <!-- Modal content-->
    <div class="modal-content" >
        <div class="modal-header" >
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">Edit</h4>


        </div>
        <form class="form-horizontal" id="form_edit_order" method="post" action="<?php echo $this->webroot; ?>Store/editorder_save" method="POST" >
            <div class="modal-body">
                <?php // debug($arr_material_master);?>

                    <div class="form-group">
                        <div class="col-md-6">
                            <label style="text-align:left;" class="col-md-5 control-label" >Item Name<label style="color:red;">*</label></label>

                            <div class="col-md-7">
                                <select id="item_fkey"  class="form-control" name="item_fkey"  required="reduired" autofocus="autofocus" onchange="findgritems();" readonly="readonly">
                                     <option value="<?php echo $arr_por_master['0']['return_gr_items']['item_fkey']; ?>"><?php echo $arr_por_master['0']['itemmaster']['item_desc']; ?></option> 
                                   // <?php
                                    //foreach ($all_item as $value) {

                                       // $selected = ($arr_por_master['0']['return_gr_items']['item_fkey'] == $value['itemmaster']['item_master_pkey']) ? 'selected="selected"' : '';
                                      //  echo '<option value="' . $value['itemmaster']['item_master_pkey'] . '" ' . $selected . '>' . $value['itemmaster']['item_desc'] . '   </option>';
                                    //}
                                   // ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                         <label style="text-align:left;" class="col-md-4 control-label" >GR Number<label style="color:red;">*</label></label>
                        <div class="col-md-8">
                            
                           <select id="grn_fkey" name="grn_fkey" class="form-control js-example-basic-single" style="width:140px;" onchange="loadqty(this);" readonly="readonly">
<!--                             <option value="" >-select-</option>-->
                             <option value="<?php echo $arr_por_master['0']['return_gr_items']['grn_fkey']; ?>"><?php echo $arr_por_master['0']['goods_receved_notes']['gr_number']; ?></option> 
                                    //<?php //foreach ($all_item as $value) {

                                        //$selected = ($arr_por_master['0']['goods_receved_notes']['gr_number'] == $value['goodsrecevednotes']['gr_number']) ? 'selected="selected"' : '';
                                       // echo '<option value="' . $arr_por_master['0']['return_gr_items']['grn_fkey'] . '" ' . $selected . '>' . $value['goodsrecevednotes']['gr_number'] . '   </option>';
                                   // }
                                   // ?>
                           </select>
                       </div> 
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-6">
                            <label style="text-align:left;" class="col-md-5 control-label" >Available Qty<label style="color:red;">*</label></label>

                            <div class="col-md-7">
                            <input type="number" id="sumdata1" value="<?php echo isset($arr_por_master["0"]["return_gr_items"]["available_qty"]) ? $arr_por_master["0"]["return_gr_items"]["available_qty"] : ""; ?>" class="form-control" name="available_qty" required="required"  readonly="readonly" style="width:140px;">         
                            </div>
                        </div>
                    
                         <div class="col-md-6">
                            <label style="text-align:left;" class="col-md-6 control-label" >Return Qty<label style="color:red;">*</label></label>
                            <div class="col-md-6">
                                <input type="number" min="1" onchange="valuecheck();" id="return_qty1" class="form-control" name="return_qty1" required="required" value="<?php echo isset($arr_por_master['0']['return_gr_items']['return_qty']) ? $arr_por_master['0']['return_gr_items']['return_qty'] : ""; ?>" />   
                            </div>
                         </div>
                    </div>
                 <div id="errormsg1" style="color: red;text-align: center;"></div>
            </div>
            <div class="modal-footer">
                <input type="hidden" id="po_fkey" class="form-control" value="<?php echo $arr_por_master[0]['return_gr_items']['po_fkey']; ?>" name="po_fkey"/>
                <input type="hidden" id="po_details_pkey" class="form-control" value="<?php echo $arr_por_master[0]['return_gr_items']['rtn_pkey']; ?>" name="po_details_pkey"/>  
                <input type="hidden" id="stk_fkey" class="form-control" value="<?php echo $arr_por_master[0]['return_gr_items']['stk_fkey']; ?>" name="stk_fkey"/>
                <button type="submit" id="btn-submit" class="btn btn-primary">Save</button>
                <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
            </div>
        </form>

        <!-- Tax Head Detail Form -->

    </div>

</div>



<!-- form ends-->

<script>
//   function  valuecheck()
//    {
//        var avil = parseInt($('#sumdata1').val());
//        var req = parseInt($('#return_qty1').val());
//        if (avil < req || isNaN(avil))
//        {
//            alert("Please Check Available Qty ");
//            $("#return_qty1").val('');
//        }
//    }
function  valuecheck()
    {
        
        var avil = parseInt($('#sumdata1').val());
        var req = parseInt($('#return_qty1').val());
//        if (avil < req || isNaN(avil))
//        {
//            alert("Please Check Available Qty ");
//            $("#return_qty").val('');
//        }
        var item_fkey = $('#item_fkey').val();
        var date_allocated = $('#return_date').val();
        var store_fkey = $('#store_code').val();
    //alert(date_allocated);
      
        $.ajax({
            url: 'Store/finditem_qtyvalue_po/'+store_fkey+'/'+item_fkey+'/'+date_allocated,
            success: function (response) {
             var data = $.parseJSON(response);
             //alert(response);
             if(parseInt(req) > data){
               //alert("Insufficient Stock");
               $("#errormsg1").html("You don't have enough stock for this transaction. Please select a lesser quantity and try again!!!");
               $('#return_qty1').val('');
             }else{
               $("#errormsg1").html("");
             }
            }
        });
    }
    function  itemedit()
    {
        var number = $('#required_qtynumber').val();
        //alert(number);
        if (number < 1)
        {
            alert("Please Check Enter Number");
            $("#required_qtynumber").val('');
        }
    }



    $(document).ready(function () {
        $('#exp_date').datepicker({
            dateFormat: 'yyyy-mm-dd'
        });
    });
</script>
