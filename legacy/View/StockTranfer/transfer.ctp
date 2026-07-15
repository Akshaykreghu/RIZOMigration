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
        <form class="form-horizontal" id="form_edit_order" method="post" action="<?php echo $this->webroot; ?>StockTranfer/editorder_save" method="POST" >
            <div class="modal-body">
                <?php // debug($arr_material_master);?>

                    <div class="form-group">
                        <div class="col-md-12">
                            <label style="text-align:left;" class="col-md-3 control-label" >Item Name<label style="color:red;">*</label></label>

                            <div class="col-md-6">
                                <select id="item_fkey"  class="form-control" name="item_fkey"  required="reduired" autofocus="autofocus" onchange="findgritems();" readonly="readonly">
                                     <option value="<?php echo $arr_store_master['0']['stockitem']['stock_item_pkey']; ?>"><?php echo $arr_store_master['0']['stockitem']['item_desc']; ?></option> 
                                </select>
                            </div>
                        </div>
                        
                    </div>
                    <div class="form-group">
                        <div class="col-md-12">
                            <label style="text-align:left;" class="col-md-3 control-label" >Available Qty<label style="color:red;">*</label></label>

                            <div class="col-md-6">
                            <input type="number" id="sumdata1" value="<?php echo isset($arr_store_master["0"]["stockitem"]["available_qty"]) ? $arr_store_master["0"]["stockitem"]["available_qty"] : ""; ?>" class="form-control" name="available_qty" required="required"  readonly="readonly" >         
                            </div>
                        </div>
                        
                    </div>
                    <div class="form-group">
                         <div class="col-md-12">
                            <label style="text-align:left;" class="col-md-3 control-label" >Transferring Qty<label style="color:red;">*</label></label>
                            <div class="col-md-6">
                                <input type="number" min="1" onchange="valuechecks();" id="return_qty1" class="form-control" name="return_qty1" required="required" value="<?php echo isset($arr_store_master['0']['stockitem']['required_qty']) ? $arr_store_master['0']['stockitem']['required_qty'] : ""; ?>" />   
                            </div>
                         </div>
                    </div>
            </div>
             <div id="errormsgs" style="color: red;text-align: center;"></div>
            <div class="modal-footer">
                <input type="hidden" id="stock_tranfer_fkey" class="form-control" value="<?php echo $arr_store_master['0']['stockitem']['stock_tranfer_fkey']; ?>" name="stock_tranfer_fkey"/>
                <input type="hidden" id="stock_item_pkey" class="form-control" value="<?php echo $arr_store_master['0']['stockitem']['stock_item_pkey']; ?>" name="stock_item_pkey"/> 
                <button type="submit" id="btn-submit" class="btn btn-primary">Save</button>
                <button type="button" class="btn btn-danger" data-dismiss="modal">Cancel</button>
            </div>
        </form>

        <!-- Tax Head Detail Form -->

    </div>

</div>



<!-- form ends-->

<script>
   function  valuechecks()
    {
        var avil = parseInt($('#sumdata').val());
        var req = parseInt($('#return_qty1').val());
//        if (avil < req)
//        {
//            alert("Please Check Available Quantity ");
//            $("#required_qty").val('');
//        }
        var item_pkey = $('#item_master_pkey').val();
        var date_allocated = $('#adjustment_date').val();
        var store_fkey = $('#from_store').val();
      
        $.ajax({
            url: livesite +'StockTranfer/finditem_qty/' + date_allocated + '/' + store_fkey+'/'+item_pkey,
            success: function (response) {
             var data = $.parseJSON(response);
            // alert(response);
             if(parseInt(req) > data){
               //alert("Insufficient Stock");
               $("#errormsgs").html("You don't have enough stock for this transaction. Please select a lesser quantity and try again!!!");
               $('#return_qty1').val('');
             }else{
               $("#errormsgs").html("");
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
